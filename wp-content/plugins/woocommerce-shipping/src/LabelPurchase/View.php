<?php

namespace Automattic\WCShipping\LabelPurchase;

use Automattic\WCShipping\Connect\WC_Connect_Account_Settings;
use Automattic\WCShipping\Connect\WC_Connect_Service_Settings_Store;
use Automattic\WCShipping\Connect\WC_Connect_Service_Schemas_Store;
use Automattic\WCShipping\Connect\WC_Connect_API_Client;
use Automattic\WCShipping\Connect\WC_Connect_Continents;
use Automattic\WCShipping\Connect\WC_Connect_Payment_Methods_Store;
use Automattic\WCShipping\Connect\WC_Connect_Utils;
use Automattic\WCShipping\Connect\WC_Connect_Functions;
use Automattic\WCShipping\Connect\WC_Connect_Compatibility;
use Automattic\WCShipping\Connect\WC_Connect_Package_Settings;
use Automattic\WCShipping\Connect\WC_Connect_Jetpack;
use Automattic\WCShipping\OriginAddresses\OriginAddressService;
use Automattic\WCShipping\Shipments\ShipmentsService;
use Automattic\WCShipping\DOM\Manipulation as DOM_Manipilation;
use Automattic\WCShipping\Utils;
use WC_Order;
use WC_Order_Item;
use WC_Order_Item_Shipping;
use WP_Post;
use WC_Product;
use WP_Error;

class View {

	/**
	 * @var WC_Connect_API_Client
	 */
	protected $api_client;

	/**
	 * @var WC_Connect_Service_Settings_Store
	 */
	protected $settings_store;

	/**
	 * @var WC_Connect_Service_Schemas_Store
	 */
	protected $service_schemas_store;

	/**
	 * @var WC_Connect_Account_Settings
	 */
	protected $account_settings;

	/**
	 * @var WC_Connect_Package_Settings
	 */
	protected $package_settings;

	/**
	 * @var WC_Connect_Continents
	 */
	protected $continents;

	/**
	 * @var array Supported countries by USPS, see: https://webpmt.usps.gov/pmt010.cfm
	 */
	private $supported_countries = array( 'US', 'AS', 'PR', 'VI', 'GU', 'MP', 'UM', 'FM', 'MH' );

	/**
	 * @var array Supported currencies
	 */
	private $supported_currencies = array( 'USD' );

	/**
	 * @var ShipmentsService $shipments_service
	 */
	private $shipments_service;

	/**
	 * @var OriginAddressService $origin_address_service
	 */
	private $origin_address_service;

	/**
	 * @var ViewService
	 */
	private $view_service;

	public function __construct(
		WC_Connect_API_Client $api_client,
		WC_Connect_Service_Settings_Store $settings_store,
		WC_Connect_Service_Schemas_Store $service_schemas_store,
		WC_Connect_Payment_Methods_Store $payment_methods_store,
		ShipmentsService $shipments_service,
		OriginAddressService $origin_address_service,
		ViewService $view_service
	) {
		$this->api_client             = $api_client;
		$this->settings_store         = $settings_store;
		$this->service_schemas_store  = $service_schemas_store;
		$this->account_settings       = new WC_Connect_Account_Settings(
			$settings_store,
			$payment_methods_store
		);
		$this->package_settings       = new WC_Connect_Package_Settings(
			$settings_store,
			$service_schemas_store
		);
		$this->continents             = new WC_Connect_Continents();
		$this->shipments_service      = $shipments_service;
		$this->origin_address_service = $origin_address_service;
		$this->view_service           = $view_service;
	}

	/**
	 * Check whether the given order is eligible for shipping label creation - the order has at least one product that is:
	 * - Shippable.
	 * - Non-refunded.
	 *
	 * @param WC_Order $order The order to check for shipping label creation eligibility.
	 * @return bool Whether the given order is eligible for shipping label creation.
	 */
	public function is_order_eligible_for_shipping_label_creation( WC_Order $order ): bool {
		// Set up a dictionary from product ID to quantity in the order, which will be updated by refunds and existing labels later.
		$quantities_by_product_id = array();
		foreach ( $order->get_items() as $item ) {
			$product = WC_Connect_Utils::get_item_product( $order, $item );
			if ( $product && $product->needs_shipping() ) {
				$product_id                              = $product->get_id();
				$current_quantity                        = array_key_exists( $product_id, $quantities_by_product_id ) ? $quantities_by_product_id[ $product_id ] : 0;
				$quantities_by_product_id[ $product_id ] = $current_quantity + $item->get_quantity();
			}
		}

		// A shipping label cannot be created without a shippable product.
		if ( empty( $quantities_by_product_id ) ) {
			return false;
		}

		// Update the quantity for each refunded product ID in the order.
		foreach ( $order->get_refunds() as $refund ) {
			foreach ( $refund->get_items() as $refunded_item ) {
				$product = WC_Connect_Utils::get_item_product( $order, $refunded_item );
				if ( ! is_a( $product, 'WC_Product' ) ) {
					continue;
				}

				$product_id = $product->get_id();
				if ( array_key_exists( $product_id, $quantities_by_product_id ) ) {
					$current_count                           = $quantities_by_product_id[ $product_id ];
					$quantities_by_product_id[ $product_id ] = $current_count - abs( $refunded_item->get_quantity() );
				}
			}
		}

		// The order is eligible for shipping label creation when there is at least one product with positive quantity.
		foreach ( $quantities_by_product_id as $product_id => $quantity ) {
			if ( $quantity > 0 ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check whether the store is eligible for shipping label creation:
	 * - Store currency is supported.
	 * - Store country is supported.
	 *
	 * @return bool Whether the WC store is eligible for shipping label creation.
	 */
	public function is_store_eligible_for_shipping_label_creation(): bool {
		$base_currency = get_woocommerce_currency();
		if ( ! $this->is_supported_currency( $base_currency ) ) {
			return false;
		}

		$base_location = wc_get_base_location();
		if ( ! $this->is_supported_country( $base_location['country'] ) ) {
			return false;
		}

		return true;
	}

	public function is_dhl_express_available(): bool {
		$dhl_express = $this->service_schemas_store->get_service_schema_by_id( 'dhlexpress' );

		return (bool) $dhl_express;
	}

	public function is_order_dhl_express_eligible(): bool {
		if ( ! $this->is_dhl_express_available() ) {
			return false;
		}

		global $post;

		$order = WC_Connect_Compatibility::instance()->init_theorder_object( $post );
		if ( ! $order ) {
			return false;
		}

		$origin      = $this->get_origin_address();
		$destination = $this->get_destination_address( $order );

		return $origin['country'] !== $destination['country'];
	}

	/**
	 * @param $post_order_or_id
	 *
	 * @return array|false
	 */
	public function get_label_payload( $post_order_or_id ): array {
		$order = wc_get_order( $post_order_or_id );
		if ( ! is_a( $order, 'WC_Order' ) ) {
			return array();
		}

		$order_id         = $order->get_id();
		$purchased_labels = $this->settings_store->get_label_order_meta_data( $order_id );
		return array(
			'orderId'            => $order_id,
			'paperSize'          => $this->settings_store->get_preferred_paper_size(),
			'storedData'         => $this->get_stored_data( $order, $purchased_labels ),
			'currentOrderLabels' => $purchased_labels,
			'storeOptions'       => $this->settings_store->get_store_options(),
			// for backwards compatibility, still disable the country dropdown for calypso users with older plugin versions.
			'canChangeCountries' => true,
		);
	}

	/**
	 * Check if order meta boxes should be displayed.
	 *
	 * @param \WC_Order $order The order object.
	 * @return bool|WP_Error
	 */
	public function throw_error_or_show_order_meta_box( $order ) {
		// Not all users have the permission to manage shipping labels.
		// If a request is made to the JS backend and the user doesn't have permission, an error would be displayed.
		if ( ! WC_Connect_Functions::user_can_manage_labels() ) {
			return new WP_Error(
				'wcshipping_banner_permission_denied',
				__( 'You do not have permission to view this banner, please contact your administrator.', 'woocommerce-shipping' )
			);
		}

		if ( ! $order instanceof \WC_Order ) {
			return new WP_Error(
				'wcshipping_banner_order_not_found',
				__( 'Order not found.', 'woocommerce-shipping' )
			);
		}

		// If Jetpack connection manager failed to retrieve data.
		if ( ! WC_Connect_Jetpack::get_connection_owner_wpcom_data() ) {
			return new WP_Error(
				'wcshipping_banner_jetpack_connection_failed',
				__( 'Unable to retrieve WordPress connection data. Please try reconnecting your WordPress connection.', 'woocommerce-shipping' )
			);
		}

		// Return an error if shipping labels has been disabled for the account.
		// This could e.g. be used to differentiate between showing errors or just hiding the box.
		if ( ! $this->is_shipping_label_enabled() ) {
			return new WP_Error(
				'wcshipping_banner_disabled',
				__( 'Shipping labels has been disabled for your account.', 'woocommerce-shipping' )
			);
		}

		// If the order already has purchased labels, show the meta-box no matter what.
		if ( $order->get_meta( 'wcshipping_labels', true ) ) {
			return true;
		}

		// Restrict showing the metabox to supported store countries and currencies.
		if ( ! $this->is_store_eligible_for_shipping_label_creation() ) {
			return new WP_Error(
				'wcshipping_banner_store_ineligible',
				__( 'The origin country of this store is not supported by WooCommerce Shipping yet.', 'woocommerce-shipping' )
			);
		}

		// If the order was created using WCS checkout rates, show the meta-box regardless of the products' state.
		if ( $this->get_packaging_metadata( $order ) ) {
			return true;
		}

		// At this point (no packaging data), only show if there's at least one existing and shippable product.
		foreach ( $order->get_items() as $item ) {
			$product = WC_Connect_Utils::get_item_product( $order, $item );
			if ( $product && $product->needs_shipping() ) {
				return true;
			}
		}

		return new WP_Error(
			'wcshipping_banner_no_shippable_products',
			__( 'The order does not contain any shippable products.', 'woocommerce-shipping' )
		);
	}

	public function get_meta_boxes_payload( $order, $args ) {
		$items       = array_filter( $order->get_items(), array( $this, 'filter_items_needing_shipping' ) );
		$items_count = array_reduce(
			$items,
			array(
				$this,
				'reducer_items_quantity',
			),
			0
		) - absint( $order->get_item_count_refunded() );
		$payload     = apply_filters(
			'wcshipping_meta_box_payload',
			array(
				'order'                   => $this->view_service->get_order_data( $order ),
				'accountSettings'         => $this->account_settings->get( true ),
				'packagesSettings'        => array(
					'schema'   => array(
						'custom'     => $this->service_schemas_store->get_packages_schema(),
						'predefined' => $this->service_schemas_store->get_predefined_packages_schema(),
					),
					'packages' => array(
						'custom'     => $this->settings_store->get_packages(),
						'predefined' => $this->settings_store->get_predefined_packages(),
					),
				),
				'shippingLabelData'       => $this->get_label_payload( $order->get_id() ),
				'continents'              => $this->continents->get(),
				'eu_countries'            => WC()->countries->get_european_union_countries(),
				'items'                   => $items_count,
				'is_destination_verified' => (bool) $this->settings_store->is_destination_address_normalized( $order->get_id() ),
				'is_origin_verified'      => (bool) $this->settings_store->is_origin_address_normalized(),
				'shipments'               => $this->shipments_service->get_order_shipments_json( $order->get_id() ),
				'origin_addresses'        => $this->origin_address_service->get_origin_addresses(),
				'constants'               => Utils::get_constants_for_js(),
			),
			$args,
			$order,
			$this
		);

		return $payload;
	}

	public function meta_boxes( $post, $args ) {
		$order                = WC_Connect_Compatibility::instance()->init_theorder_object( $post );
		$should_show_meta_box = $this->throw_error_or_show_order_meta_box( $order );

		if ( is_wp_error( $should_show_meta_box ) ) {
			// Error messages are already translated.
			echo esc_html( $should_show_meta_box->get_error_message() );
			return;
		}

		// We pass context on via an additional arg as part of payload it gets overwritten by each meta box.
		$context = $args['args']['context'];
		unset( $args['args']['context'] );

		// Output entry points for the JS scripts.
		$payload = $this->get_meta_boxes_payload( $order, $args );

		switch ( $context ) {
			case 'shipping_label':
				DOM_Manipilation::create_root_script_element( 'woocommerce-shipping-shipping-label', $context );
				do_action( 'enqueue_woocommerce_shipping_script', 'woocommerce-shipping-create-shipping-label', $payload, $context );
				break;
			case 'shipment_tracking':
				DOM_Manipilation::create_root_script_element( 'woocommerce-shipping-shipping-label', $context );
				do_action( 'enqueue_woocommerce_shipping_script', 'woocommerce-shipping-shipment-tracking', $payload, $context );
				break;
		}
	}

	private function get_item_data( WC_Order $order, $item ) {
		$product = WC_Connect_Utils::get_item_product( $order, $item );
		if ( ! $product || ! $product->needs_shipping() ) {
			return null;
		}
		$height = 0;
		$length = 0;
		$weight = $product->get_weight();
		$width  = 0;

		if ( $product->has_dimensions() ) {
			$height = $product->get_height();
			$length = $product->get_length();
			$width  = $product->get_width();
		}
		$parent_product_id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
		$product_data      = array(
			'height'     => (float) $height,
			'product_id' => $product->get_id(),
			'length'     => (float) $length,
			'quantity'   => 1,
			'weight'     => (float) $weight,
			'width'      => (float) $width,
			'name'       => $this->get_name( $product ),
			'url'        => get_edit_post_link( $parent_product_id, null ),
		);

		if ( $product->is_type( 'variation' ) ) {
			$product_data['attributes'] = wc_get_formatted_variation( $product, true );
		}

		return $product_data;
	}

	/**
	 * @param WC_Order_Item_Shipping|false $shipping_method
	 */
	private function get_packaging_from_shipping_method( $shipping_method ): array {
		if ( ! $shipping_method || ! isset( $shipping_method['wcshipping_packages'] ) ) {
			return array();
		}

		$packages_data = $shipping_method['wcshipping_packages'];
		if ( ! $packages_data ) {
			return array();
		}

		// WC3 retrieves metadata as non-scalar values.
		if ( is_array( $packages_data ) ) {
			return $packages_data;
		}

		// WC2.6 stores non-scalar values as string, but doesn't deserialize it on retrieval.
		$packages = maybe_unserialize( $packages_data );
		if ( is_array( $packages ) ) {
			return $packages;
		}

		// legacy WCS stored the labels as JSON.
		$packages = json_decode( $packages_data, true );
		if ( $packages ) {
			return $packages;
		}

		$packages_data = $this->settings_store->try_recover_invalid_json_string( 'box_id', $packages_data );
		$packages      = json_decode( $packages_data, true );
		if ( $packages ) {
			return $packages;
		}

		return array();
	}

	private function get_packaging_metadata( WC_Order $order ): array {
		$shipping_methods = $order->get_shipping_methods();
		$shipping_method  = reset( $shipping_methods );
		$packaging        = $this->get_packaging_from_shipping_method( $shipping_method );

		if ( is_array( $packaging ) ) {
			return array_filter( $packaging );
		}

		return array();
	}

	private function get_name( WC_Product $product ): string {
		if ( $product->get_sku() ) {
			$identifier = $product->get_sku();
		} else {
			$identifier = '#' . $product->get_id();
		}
		return sprintf( '%s - %s', $identifier, $product->get_title() );
	}

	private function get_selected_packages( WC_Order $order ): array {
		$packages = $this->get_packaging_metadata( $order );
		if ( ! $packages ) {
			$items  = $this->get_all_items( $order );
			$weight = array_sum( wp_list_pluck( $items, 'weight' ) );

			$packages = array(
				'default_box' => array(
					'id'     => 'default_box',
					'box_id' => 'not_selected',
					'height' => 0,
					'length' => 0,
					'weight' => $weight,
					'width'  => 0,
					'items'  => $items,
				),
			);
		}

		$formatted_packages = array();

		foreach ( $packages as $package_obj ) {
			$package                           = (array) $package_obj;
			$package_id                        = $package['id'];
			$formatted_packages[ $package_id ] = $package;

			foreach ( $package['items'] as $item_index => $item ) {
				$product_data = (array) $item;
				$product      = WC_Connect_Utils::get_item_product( $order, $product_data );

				if ( $product ) {
					$parent_product_id    = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
					$product_data['name'] = $this->get_name( $product );
					$product_data['url']  = get_edit_post_link( $parent_product_id, null );
					if ( $product->is_type( 'variation' ) ) {
						$product_data['attributes'] = wc_get_formatted_variation( $product, true );
					}

					$customs_info = Utils::get_product_customs_data( $product );
					if ( is_array( $customs_info ) ) {
						$product_data = array_merge( $product_data, $customs_info );
					}
				} else {
					$product_data['name'] = WC_Connect_Utils::get_product_name_from_order( $product_data['product_id'], $order );
				}
				$product_data['value'] = WC_Connect_Utils::get_product_price_from_order( $product_data['product_id'], $order );
				if ( ! isset( $product_data['value'] ) ) {
					$product_data['value'] = 0;
				}

				$formatted_packages[ $package_id ]['items'][ $item_index ] = $product_data;
			}
		}

		return $formatted_packages;
	}

	private function get_all_items( WC_Order $order ): array {
		if ( $this->get_packaging_metadata( $order ) ) {
			return array();
		}

		$items = array();
		foreach ( $order->get_items() as $item ) {
			$item_data = $this->get_item_data( $order, $item );
			if ( null === $item_data ) {
				continue;
			}

			$refunded_qty = $order->get_qty_refunded_for_item( $item->get_id() );

			for ( $i = 0; $i < ( $item['qty'] - absint( $refunded_qty ) ); $i++ ) {
				$items[] = $item_data;
			}
		}

		return $items;
	}

	private function get_selected_rates( WC_Order $order ): array {
		$shipping_methods = $order->get_shipping_methods();
		$shipping_method  = reset( $shipping_methods );
		$packages         = $this->get_packaging_from_shipping_method( $shipping_method );
		$rates            = array();

		foreach ( $packages as $idx => $package_obj ) {
			$package = (array) $package_obj;
			// Abort if the package data is malformed
			if ( ! isset( $package['id'] ) || ! isset( $package['service_id'] ) ) {
				return array();
			}

			$rates[ $package['id'] ] = $package['service_id'];
		}

		return $rates;
	}

	private function format_address_for_api( $address ): array {
		// Combine first and last name.
		if ( ! isset( $address['name'] ) ) {
			$first_name = isset( $address['first_name'] ) ? trim( $address['first_name'] ) : '';
			$last_name  = isset( $address['last_name'] ) ? trim( $address['last_name'] ) : '';

			$address['name'] = $first_name . ' ' . $last_name;
		}

		// Rename address_1 to address.
		if ( ! isset( $address['address'] ) && isset( $address['address_1'] ) ) {
			$address['address'] = $address['address_1'];
		}

		// Remove now defunct keys.
		unset( $address['first_name'], $address['last_name'], $address['address_1'] );

		return $address;
	}

	private function get_origin_address(): array {
		return $this->format_address_for_api( $this->settings_store->get_origin_address() );
	}

	private function get_destination_address( WC_Order $order ): array {
		$order_address = $order->get_address( 'shipping' );
		return $this->format_address_for_api( $order_address );
	}

	private function get_stored_data( WC_Order $order, array $purchased_labels ): array {
		$order_id             = $order->get_id();
		$is_packed            = ( false !== $this->get_packaging_metadata( $order ) );
		$origin               = $this->get_origin_address();
		$selected_rates       = $order->get_meta( LabelPurchaseService::SELECTED_RATES_KEY );
		$selected_hazmat      = $order->get_meta( LabelPurchaseService::SELECTED_HAZMAT_KEY );
		$selected_origin      = $order->get_meta( LabelPurchaseService::SELECTED_ORIGIN_KEY );
		$selected_destination = $order->get_meta( LabelPurchaseService::SELECTED_DESTINATION_KEY );
		$customs_information  = $order->get_meta( LabelPurchaseService::CUSTOMS_INFORMATION );
		$destination          = $this->get_destination_address( $order );

		if ( ! $destination['country'] ) {
			$destination['country'] = $origin['country'];
		}

		if ( is_array( $selected_rates ) ) {
			/**
			 * Selected rate for an errored purchase is not relevant, so it's removed to prevent issues with the UI.
			 * But these data are kept for usages in the backend.
			 */
			$selected_rates = $this->view_service->remove_meta_for_purchase_error( $purchased_labels, $selected_rates );

			/*
			 * Selected rate for a refunded label is not relevant and all relevant refund information can be found
			 * on the label entity itself, so we're removing it to prevent issues with the UI.
			 */
			$selected_rates = $this->view_service->remove_meta_for_refunds( $purchased_labels, $selected_rates );
		}

		if ( is_array( $customs_information ) ) {
			/*
			 * We store a snapshot of the customs form when a label is purchased.
			 * If the merchant chooses to refund a label, then we should no longer rely on that snapshot since the order
			 * might have changed (e.g.: the order could have been split into multiple shipments), so the snapshot is no
			 * longer reliable, and we should therefor just reset the experience.
			 *
			 * The customs information should still be the same - or updated to something more correct - since we're
			 * storing and populating the customs form with product customs meta-data.
			 */
			$customs_information = $this->view_service->remove_customs_information_for_refunds( $purchased_labels, $customs_information );
		}

		$destination_normalized = (bool) $this->settings_store->is_destination_address_normalized( $order_id );

		$data = compact(
			'is_packed',
			'selected_rates',
			'destination',
			'destination_normalized',
			'selected_hazmat',
			'selected_origin',
			'selected_destination',
			'customs_information'
		);

		$data['order_id'] = $order_id;

		return $data;
	}

	/**
	 * Check whether the given country code is supported for shipping labels.
	 */
	public function is_supported_country( string $country_code ): bool {
		return in_array( $country_code, $this->supported_countries, true );
	}

	/**
	 * Check whether the given currency code is supported for shipping labels.
	 */
	public function is_supported_currency( string $currency_code ): bool {
		return in_array( $currency_code, $this->supported_currencies, true );
	}

	/**
	 * Check whether shipping label feature is enabled from WC Services setting.
	 */
	private function is_shipping_label_enabled(): bool {
		$account_settings = $this->account_settings->get( true );

		if ( isset( $account_settings['purchaseSettings']['enabled'] ) && is_bool( $account_settings['purchaseSettings']['enabled'] ) ) {
			return $account_settings['purchaseSettings']['enabled'];
		}

		return true;
	}


	private function filter_items_needing_shipping( WC_Order_Item $item ): bool {
		$product = $item->get_product();
		return $product && $product->needs_shipping();
	}

	/**
	 * Reduce items to sum their quantities.
	 */
	private function reducer_items_quantity( int $sum, WC_Order_Item $item ): int {
		return $sum + $item->get_quantity();
	}
}