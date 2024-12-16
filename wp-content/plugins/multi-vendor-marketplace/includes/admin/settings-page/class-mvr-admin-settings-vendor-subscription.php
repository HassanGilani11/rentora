<?php
/**
 * Vendor Subscription Section
 *
 * @package Multi Vendor Marketplace/Setting Tab/Vendor Subscription Section
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Admin_Settings_Vendor_Subscription' ) ) {
	/**
	 * Vendor Subscription Tab.
	 *
	 * @class MVR_Admin_Settings_Vendor_Subscription
	 * @package Class
	 */
	class MVR_Admin_Settings_Vendor_Subscription extends MVR_Abstract_Settings {
		/**
		 * MVR_Admin_Settings_Vendor_Subscription constructor.
		 */
		public function __construct() {
			$this->id                   = 'vendor_subscription';
			$this->label                = __( 'Vendor Subscription', 'multi-vendor-marketplace' );
			$this->custom_fields        = array( 'subscription_product_create_btn', 'subscription_product' );
			$this->custom_fields_option = array( 'mvr_settings_subscription_product' => array() );
			$this->settings             = $this->get_settings();
			$this->init();
		}

		/**
		 * Get settings array.
		 *
		 * @since 1.0.0
		 * @return Array
		 */
		public function get_settings() {
			/**
			 * Vendor Subscription Settings Fields.
			 *
			 * @since 1.0.0
			 */
			return apply_filters(
				'mvr_get_' . $this->id . '_settings',
				array(
					array(
						'type'  => 'title',
						'title' => esc_html__( 'Vendor Subscription', 'multi-vendor-marketplace' ),
						'id'    => 'mvr_vendor_subscription_options',
					),
					array(
						'title'   => esc_html__( 'Enable Vendor Subscription', 'multi-vendor-marketplace' ),
						'type'    => 'checkbox',
						'default' => 'no',
						'id'      => $this->get_option_key( 'enable_vendor_subscription' ),
						'class'   => 'mvr-settings-enable-vendor-subscription',
					),
					array(
						'type' => $this->get_custom_field_type( 'subscription_product' ),
					),
					array(
						'type' => 'sectionend',
						'id'   => 'mvr_vendor_subscription_options',
					),
				)
			);
		}

		/**
		 * Product Create Button
		 *
		 * @since 1.0.0
		 */
		public function subscription_product_create_btn() {
			?>
			<tr style="" valign="top">
				<th></th>
				<td>
					<input type="submit" class="mvr-new-subscription-product-btn button-primary" value="<?php esc_html_e( 'Create New Subscription Product', 'multi-vendor-marketplace' ); ?>">
				</td>
			</tr>
			<?php
		}

		/**
		 * Product Create Button
		 *
		 * @since 1.0.0
		 */
		public function subscription_product() {
			$product_types = wc_get_product_types();

			if ( ! isset( $product_types['subscription'] ) ) {
				return;
			}

			$subscription_product = get_option( 'mvr_settings_subscription_product', array() );
			?>
			<tr style="" valign="top">
				<th>
					<?php esc_html_e( 'Select Product', 'multi-vendor-marketplace' ); ?>
				</th>
				<td>
					<?php
					$product_types = wc_get_product_types();

					if ( isset( $product_types['subscription'] ) ) {
						unset( $product_types['subscription'] );
					}

					mvr_select2_html(
						array(
							'id'           => 'mvr_settings_subscription_product',
							'class'        => 'wc-product-search mvr-settings-subscription-product',
							'placeholder'  => esc_html__( 'Search Product(s)', 'multi-vendor-marketplace' ),
							'options'      => $subscription_product,
							'type'         => 'products',
							'action'       => 'woocommerce_json_search_products',
							'exclude_type' => array_keys( $product_types ),
							'multiple'     => false,
						)
					);
					?>
				</td>
			</tr>
			<?php
		}

		/**
		 * Save custom settings.
		 *
		 * @since 1.0.0
		 * @param Array $posted Posted Data.
		 */
		public function custom_types_save( $posted ) {
			foreach ( $this->custom_fields_option as $key => $default_value ) {
				if ( ! isset( $posted[ "{$key}" ] ) ) {
					continue;
				}

				update_option( "{$key}", $posted[ "{$key}" ] );
			}
		}

		/**
		 * Save the custom options once.
		 *
		 * @since 1.0.0
		 * @param Array $posted Posted Data.
		 */
		public function custom_types_add_options( $posted = null ) {
			foreach ( $this->custom_fields_option as $key => $default_value ) {
				add_option( $key, $default_value );
			}
		}
	}

	return new MVR_Admin_Settings_Vendor_Subscription();
}
