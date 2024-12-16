<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Stock_Manipulation' ) ) {

	class WCRP_Rental_Products_Stock_Manipulation {

		// This class includes items which would normally be specific to other classes, e.g. WCRP_Rental_Products_Cart_Items but we wanted all stock manipulation related functionality together under one class to avoid having to write comments in each separate class that functionality relates to functionality in another class

		public function __construct() {

			add_action( 'woocommerce_cart_updated', array( $this, 'cart_item_stock' ), PHP_INT_MAX );
			add_action( 'woocommerce_before_calculate_totals', array( $this, 'cart_item_stock' ), PHP_INT_MAX ); // Same function hooked as on woocommerce_cart_updated, for reason why see code __construct comments in WCRP_Rental_Products_Cart_Items for functions hooked to both woocommerce_cart_updated and woocommerce_before_calculate_totals
			add_filter( 'woocommerce_order_hold_stock_minutes', array( $this, 'order_disable_reserve_stock_if_rental' ), PHP_INT_MAX, 2 );
			add_filter( 'option_woocommerce_hold_stock_minutes', array( $this, 'order_disable_reserve_stock_if_rental_legacy' ) );
			add_action( 'admin_notices', array( $this, 'order_disable_reserve_stock_if_rental_legacy_notice' ) );
			add_action( 'woocommerce_update_order', array( $this, 'order_disable_purchasable_stock_increase_decrease_if_rental' ), 10, 2 );
			add_filter( 'woocommerce_prevent_adjust_line_item_product_stock', array( $this, 'order_item_disable_purchasable_stock_increase_decrease_if_rental' ), 10, 3 );
			// disableRestockRefundedItems() JS function in WCRP_Rental_Products_Order_Line_Items::order_line_item_scripts() is also related to stock manipulation

		}

		public function cart_item_stock() {

			// This function targets rental cart items and ensures standard stock is manipulated to allow the rental to occur, e.g. for a rental or purchase, where the standard stock (for a purchase) is out of stock/less than rental stock, it manipulates this to allow the rental through, this would also do the same for rental only products although they should already have standard stock management disabled, the conditions below purely disregard the standard (purchase) stock if it is a rental, the actual checks for whether the rental is available are done via WCRP_Rental_Products_Cart_Checks

			$cart = WC()->cart;

			if ( !empty( $cart ) ) {

				if ( isset( $cart->cart_contents ) ) {

					if ( !empty( $cart->cart_contents ) ) {

						foreach ( $cart->cart_contents as $cart_content_key => $cart_content_value ) {

							if ( isset( $cart_content_value['wcrp_rental_products_cart_item_price'] ) ) { // If a rental cart item (rental only or rental part of a rental or purchase)

								// Get rental stock

								if ( isset( $cart_content_value['product_id'] ) ) {

									if ( (int) $cart_content_value['product_id'] > 0 ) {

										$rental_stock = get_post_meta( $cart_content_value['product_id'], '_wcrp_rental_products_rental_stock', true );

									}

								}

								if ( isset( $cart_content_value['variation_id'] ) ) {

									if ( (int) $cart_content_value['variation_id'] > 0 ) {

										$rental_stock = get_post_meta( $cart_content_value['variation_id'], '_wcrp_rental_products_rental_stock', true );

									}

								}

								// Set cart item product object parent data if a variation

								if ( 'variation' == $cart_content_value['data']->get_type() ) {

									// If a variation has manage stock disabled then when WooCommerce's validation uses get_manage_stock() on the variation cart item product it will return 'parent' and the parent data is used instead, so we need to ensure the parent data manage stock is set to no to ensure the rental stock is being used not purchasable, this covers scenarios where e.g. it is a variable rental or purchase product where the inventory tab manage stock (parent) is on, purchsable stock is 1, on the rental we are allowing > 1 stock, the parent needs to be manage stock disabled to allow the > 1 rental through

									$parent_data = $cart_content_value['data']->get_parent_data();
									$parent_data['manage_stock'] = 'no'; // This is correct, it gets converted to false via wc_string_to_bool() where WooCommerce uses it
									$cart_content_value['data']->set_parent_data( $parent_data );

								}

								// Set cart item product object data

								$cart_content_value['data']->set_manage_stock( false ); // Manage stock is always false for both rental only and rental or purchase (remember the cart item is one of these, at this point it is not the purchasable part of a rental or purchase product)
								$cart_content_value['data']->set_stock_status( 'instock' ); // Stock status is always in stock, if rental is out of stock our normal cart checks pick this up and stop any further progress

								if ( '' == $rental_stock || (int) $rental_stock > 0 ) {

									$cart_content_value['data']->set_stock_quantity( PHP_INT_MAX ); // Rental stock is unlimited, or is a specific level but we set to unlimited, our cart checks validate if enough rental stock, this just ensures it is deemed in stock

								} else {

									$cart_content_value['data']->set_stock_quantity( 0 ); // No rental stock

								}

							}

						}

					}

				}

			}

		}

		public function order_disable_reserve_stock_if_rental( $minutes, $order ) {

			/*
			This function is hooked from the woocommerce_order_hold_stock_minutes filter hook which has existed since WooCommerce 8.8.0, for versions before see order_disable_reserve_stock_if_rental_legacy()

			Reserve stock must be disabled, as without doing this it can cause cart/checkout errors in some scenarios, such as when attempting to order a rental or purchase product where the rental stock exceeds the purchasable stock, this occurs because the core reserve stock functionality (which does not occur if $minutes here are returned 0) gets the overall product object from order items and in this example scenario determines the product (purchasable part) to have insufficient stock so the cheeckout process fails
			*/

			$order_items = $order->get_items();

			if ( !empty( $order_items ) ) {

				foreach ( $order_items as $order_item ) {

					if ( !empty( $order_item->get_meta( 'wcrp_rental_products_rent_from' ) ) ) {

						// If order contains a rental, disable reserve stock

						$minutes = 0;

						break;

					}

				}

			}

			return $minutes;

		}

		public function order_disable_reserve_stock_if_rental_legacy( $minutes ) {

			/*
			This is a legacy function used when WooCommerce version is less than 8.8.0, as order_disable_reserve_stock_if_rental() would not be used as no woocommerce_order_hold_stock_minutes filter hook before 8.8.0

			If WooCommerce version is less than 8.8.0 we still need to disable reserve stock for the reasons commented in order_disable_reserve_stock_if_rental()

			To do this we filter the woocommerce_hold_stock_minutes setting if the cart contains rental products, this disables reserve stock in all scenarios except when draft orders get generated for cart/checkout blocks, this is because a hardcoded 10 minute is passed to reserve_stock_for_order() rather than using woocommerce_hold_stock_minutes, the latter means that there is no way to disable reserve stock on WooCommerce versions less than 8.8.0 and means checkout fails if using < 8.8.0 with cart/checkout blocks for rental or purchase products where the rental stock exceeds the purchasable stock

			Because of the issue above we add a notice if these conditions are met to highlight issue and to recommend updating WooCommerce or using classic cart/checkout, see order_disable_reserve_stock_if_rental_legacy_notice()

			This function can be removed when minimum WooCommerce version for the extension is >= 8.8.0
			*/

			if ( version_compare( WC_VERSION, '8.8.0', '<' ) ) {

				$cart = WC()->cart;

				if ( !empty( $cart ) ) { // As $cart is null in the dashboard, this doesn't cause the hold stock setting in the dashboard to be 0 if the user has rentals in cart

					if ( isset( $cart->cart_contents ) ) {

						if ( !empty( $cart->cart_contents ) ) {

							foreach ( $cart->cart_contents as $cart_content_key => $cart_content_value ) {

								if ( isset( $cart_content_value['wcrp_rental_products_rent_from'] ) ) {

									// If order contains a rental, disable reserve stock

									$minutes = 0;

									break;

								}

							}

						}

					}

				}

			}

			return $minutes;

		}

		public function order_disable_reserve_stock_if_rental_legacy_notice() {

			/*
			This notice is added for the reasons described in the order_disable_reserve_stock_if_rental_legacy() comments

			This function can be removed when minimum WooCommerce version for the extension is >= 8.8.0
			*/

			global $wpdb;

			if ( version_compare( WC_VERSION, '8.8.0', '<' ) ) {

				// If using cart/checkout blocks

				$cart_page_id = wc_get_page_id( 'cart' );
				$checkout_page_id = wc_get_page_id( 'checkout' );

				if ( has_block( 'woocommerce/cart', $cart_page_id ) || has_block( 'woocommerce/checkout', $checkout_page_id ) ) {

					// Get total of rental or purchase products

					$rental_or_purchase_products = $wpdb->get_results(
						"SELECT COUNT( * ) AS `total` FROM `{$wpdb->prefix}postmeta` WHERE `meta_key` = '_wcrp_rental_products_rental' AND `meta_value` = 'yes_purchase';"
					);

					// Display notice if there are rental or purchase products

					if ( !empty( $rental_or_purchase_products ) ) {

						if ( $rental_or_purchase_products[0]->total > 0 ) {

							// translators: %1$s: WooCommerce current version, %2$s: WooCommerce fix version
							echo '<div class="notice notice-error"><p>' . wp_kses_post( sprintf( __( 'You are using WooCommerce %1$s with cart/checkout blocks and have rental or purchase based products. In some availability scenarios for these products you may experience cart/checkout issues. This is fixed in WooCommerce %2$s, update to this version, or use classic cart/checkout.', 'wcrp-rental-products' ), WC_VERSION, '8.8.0' ) ) . '</p></div>';

						}

					}

				}

			}

		}

		public function order_disable_purchasable_stock_increase_decrease_if_rental( $order_id, $order ) {

			/*
			This function stops purchasable stock from being increased/reduced when it is a rental order item, as rental order items should not have any effect on purchasable stock

			It does one of these for rental items in an order:

			1. If the order status is cancelled or pending, it deletes the _reduced_stock meta, this stops purchasable rental stock from getting increased when being changed to one of these statuses, as thinks nothing previously reduced to increase by, as per wc_increase_stock_levels()

			2. If the order status is not cancelled or pending, it adds _reduced_stock meta (if it doesn't already exist) to rental order items, this stops purchasable stock from getting reduced, as thinks it has already been reduced, as per wc_reduce_stock_levels()
			*/

			$order_items = $order->get_items();
			$order_status = $order->get_status();

			if ( !empty( $order_items ) ) {

				foreach ( $order_items as $order_item ) {

					$order_item_id = $order_item->get_id();
					$order_item_meta_data = $order_item->get_meta_data();

					if ( !empty( $order_item_meta_data ) ) {

						foreach ( $order_item_meta_data as $order_item_meta_data_object ) {

							if ( isset( $order_item_meta_data_object->key ) ) {

								if ( 'wcrp_rental_products_rent_from' == $order_item_meta_data_object->key ) {

									if ( in_array( $order_status, array( 'cancelled', 'pending' ) ) ) {

										wc_delete_order_item_meta( $order_item_id, '_reduced_stock' );

									} else {

										if ( empty( wc_get_order_item_meta( $order_item_id, '_reduced_stock', true ) ) ) {

											wc_add_order_item_meta( $order_item_id, '_reduced_stock', $order_item->get_quantity(), true );

										}

									}

									break;

								}

							}

						}

					}

				}

			}

		}

		public function order_item_disable_purchasable_stock_increase_decrease_if_rental( $disable, $order_item, $order_item_quantity ) {

			// This function is hooked on the woocommerce_prevent_adjust_line_item_product_stock filter hook which is used by wc_maybe_adjust_line_item_product_stock() when order items are saved or removed, if the wcrp_rental_products_rent_from key exists on the order item we prevent purchasable stock changes, e.g. order contains a rental or purchase product (rental only has manage stock disabled so is uneffected), order is set to on hold, order item is removed, without the conditions below the order item would have its _reduced_stock meta got which will exist due to order_disable_purchasable_stock_increase_decrease_if_rental() and if exists and > 0 then it would adjust stock accordingly, we do not want this to occur because its a rental and therefore purchasable stock should be unaffected

			$order_item_id = $order_item->get_id();

			if ( !empty( $order_item_id ) ) {

				if ( !empty( wc_get_order_item_meta( $order_item_id, 'wcrp_rental_products_rent_from', true ) ) ) {

					$disable = true;

				}

			}

			return $disable;

		}

	}

}
