<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Order_Save' ) ) {

	class WCRP_Rental_Products_Order_Save {

		public function __construct() {

			add_action( 'delete_post', array( $this, 'order_delete' ) ); // If HPOS order tables disabled
			add_action( 'woocommerce_delete_order', array( $this, 'order_delete' ) ); // If HPOS order tables enabled
			add_action( 'woocommerce_order_status_completed', array( $this, 'order_status_completed' ), 10, 2 );
			add_action( 'woocommerce_new_order', array( $this, 'rentals_add_update' ), 10, 2 );
			add_action( 'woocommerce_update_order', array( $this, 'rentals_add_update' ), 10, 2 );
			add_action( 'woocommerce_update_order', array( $this, 'rentals_remove' ), 10, 2 );

		}

		public function order_delete( $order_id ) {

			global $wpdb;

			// When an order is deleted (after trashing) this function should remove the rentals data from the rentals database tables as the order no longer exists, it should have been possible to solely hook this function from woocommerce_delete_order but instead it has had to be hooked through both before_delete_post and woocommerce_delete_order, this is because there is a bug in WooCommerce (https://github.com/woocommerce/woocommerce/issues/32543), when on a shop_order post type (HPOS disabled) the woocommerce_delete_order hook does not fire as stated in the bug details so we need to cover both scenarios and use 2 hooks, the bug fix is potentially due in WooCommerce 8.1.0 but because users can still be using an older version of WooCommerce with HPOS disabled it isn't possible to hook this solely off woocommerce_delete_order, at least not until the minimum requried WooCommerce version of this extension exceeds the version the bug gets fixed in, once users are on that version then woocommerce_delete_order will be getting fired for both non-HPOS and HPOS scenarios

			$delete_rentals = false;

			if ( 'delete_post' == current_action() ) {

				if ( !wcrp_rental_products_hpos_enabled() && 'shop_order' == get_post_type( $order_id ) ) {

					$delete_rentals = true;

				}

			} else {

				if ( 'woocommerce_delete_order' == current_action() ) {

					if ( wcrp_rental_products_hpos_enabled() ) {

						$delete_rentals = true;

					}

				}

			}

			if ( true == $delete_rentals ) {

				$wpdb->query(
					$wpdb->prepare(
						"DELETE FROM `{$wpdb->prefix}wcrp_rental_products_rentals` WHERE `order_id` = %d;",
						$order_id
					)
				);

				$wpdb->query(
					$wpdb->prepare(
						"DELETE FROM `{$wpdb->prefix}wcrp_rental_products_rentals_archive` WHERE `order_id` = %d;",
						$order_id
					)
				);

			}

		}

		public function order_status_completed( $order_id, $order ) {

			$order_items = $order->get_items();

			if ( !empty( $order_items ) ) {

				foreach ( $order_items as $order_item ) {

					$order_item_id = $order_item->get_id();

					if ( !empty( wc_get_order_item_meta( $order_item_id, 'wcrp_rental_products_rent_from', true ) ) ) { // If it is a rental and not cancelled (as if cancelled would not have it)

						if ( 'yes' == get_option( 'wcrp_rental_products_return_rentals_in_completed_orders' ) ) { // If completed order status should return all rentals in order

							if ( empty( wc_get_order_item_meta( $order_item_id, 'wcrp_rental_products_returned', true ) ) ) {

								WCRP_Rental_Products_Order_Line_Items::order_line_item_mark_as_returned( $order_item_id );

							}

						}

					}

				}

			}

		}

		public static function rentals_add_update( $order_id, $order ) {

			global $wpdb;

			$cancel_rentals_in_failed_orders = get_option( 'wcrp_rental_products_cancel_rentals_in_failed_orders' );

			if ( 'yes' == $cancel_rentals_in_failed_orders ) {

				$add_update_order_statuses = array( 'pending', 'processing', 'on-hold' ); // Only reserves for these order statuses, does not reserve rental for failed as these get rentals cancelled, see rentals_remove()

			} else {

				$add_update_order_statuses = array( 'pending', 'processing', 'on-hold', 'failed' );

			}

			require_once ABSPATH . 'wp-admin/includes/plugin.php';

			if ( is_plugin_active( 'deposits-partial-payments-for-woocommerce/start.php' ) || is_plugin_active( 'deposits-partial-payments-for-woocommerce-pro/start.php' ) ) {

				$add_update_order_statuses[] = 'partially-paid'; // If Deposits & Partial Payments for WooCommerce is active then partially paid order status is included to ensure rentals are reserved (see managing rental orders meta box for details), this has been added for completeness, however without this it would reserve anyway because created orders always initially get a pending status

			}

			if ( in_array( $order->get_status(), $add_update_order_statuses ) ) {

				$order_items = $order->get_items();

				if ( !empty( $order_items ) ) {

					foreach ( $order_items as $order_item ) {

						$order_item_id = $order_item->get_id();

						if ( !empty( $order_item_id ) ) { // This condition is here because when testing with WooCommerce 8.0.0-beta2 release we found that during place order of checkout the order items can contain an order item with no ID causing rows to be added to the rentals table for the order with 0 order_item_id, filtering this out with this condition stops that happening, assumedly later on in the checkout order creation this function is run again and the order items are fully populated and therefore the rentals database table gets added

							$order_item_meta_data = $order_item->get_meta_data();
							$rent_from = '';
							$rent_to = '';
							$return_days_threshold = '';
							$returned = '';
							$cancelled = '';

							if ( !empty( $order_item_meta_data ) ) {

								foreach ( $order_item_meta_data as $order_item_meta_data_object ) {

									if ( isset( $order_item_meta_data_object->key ) ) {

										if ( 'wcrp_rental_products_rent_from' == $order_item_meta_data_object->key ) {

											$rent_from = $order_item_meta_data_object->value;

										} elseif ( 'wcrp_rental_products_rent_to' == $order_item_meta_data_object->key ) {

											$rent_to = $order_item_meta_data_object->value;

										} elseif ( 'wcrp_rental_products_return_days_threshold' == $order_item_meta_data_object->key ) {

											$return_days_threshold = $order_item_meta_data_object->value;

										} elseif ( 'wcrp_rental_products_returned' == $order_item_meta_data_object->key ) {

											$returned = $order_item_meta_data_object->value;

										} elseif ( 'wcrp_rental_products_cancelled' == $order_item_meta_data_object->key ) {

											$cancelled = $order_item_meta_data_object->value;

										}

									}

								}

							}

							if ( 'yes' == $returned || 'yes' == $cancelled ) {

								// Rental not reserved for order item if has been returned or cancelled, if returned the database table data remains until archived, if cancelled it has already been removed
								// No check for if rent from/to exists because cancelled do not have it

								continue; // Continue to next order item iteration

							} else {

								// Reserve rental for order item, deletes existing data for this order item then add (adds or updates), the deletion ensures duplicate rows are not inserted, note that the code below is not done for the archive table, as the archive table only includes rentals marked as returned

								if ( '' !== $rent_from && '' !== $rent_to ) { // Rental order items only, without this non-rentals would get reserved

									if ( !empty( $order_item->get_variation_id() ) ) {

										$product_id = $order_item->get_variation_id();

									} else {

										$product_id = $order_item->get_product_id();

									}

									$quantity = (int) $order_item->get_quantity();

									$wpdb->query(
										$wpdb->prepare(
											"DELETE FROM `{$wpdb->prefix}wcrp_rental_products_rentals` WHERE `order_id` = %d AND `order_item_id` = %d;",
											$order_id,
											$order_item_id
										)
									);

									$begin = new DateTime( $rent_from );
									$end_modifier_days = (int) $return_days_threshold + 1; // Adds extra for return days plus 1 as 1 day short without
									$end = new DateTime( gmdate( 'Y-m-d', strtotime( $rent_to . ' +' . $end_modifier_days . ' days' ) ) ); // Specifically not translatable as part of PHP date calculation
									$interval = DateInterval::createFromDateString( '1 day' );
									$period = new DatePeriod( $begin, $interval, $end );

									foreach ( $period as $reserved_date ) {

										$wpdb->query(
											$wpdb->prepare(
												"INSERT INTO `{$wpdb->prefix}wcrp_rental_products_rentals` ( `reserved_date`, `order_id`, `order_item_id`, `product_id`, `quantity` ) VALUES ( %s, %d, %d, %d, %d );",
												$reserved_date->format( 'Y-m-d' ),
												$order_id,
												$order_item_id,
												$product_id,
												$quantity
											)
										);

									}

								}

							}

						}

					}

				}

			}

		}

		public function rentals_remove( $order_id, $order ) {

			$cancel_rentals_in_failed_orders = get_option( 'wcrp_rental_products_cancel_rentals_in_failed_orders' );

			if ( 'yes' == $cancel_rentals_in_failed_orders ) {

				$removal_order_statuses = array( 'cancelled', 'refunded', 'failed' );

			} else {

				$removal_order_statuses = array( 'cancelled', 'refunded' );

			}

			if ( in_array( $order->get_status(), $removal_order_statuses ) ) {

				global $wpdb;

				$wpdb->query(
					$wpdb->prepare(
						"DELETE FROM `{$wpdb->prefix}wcrp_rental_products_rentals` WHERE `order_id` = %d;",
						$order_id
					)
				);

				$wpdb->query(
					$wpdb->prepare(
						"DELETE FROM `{$wpdb->prefix}wcrp_rental_products_rentals_archive` WHERE `order_id` = %d;",
						$order_id
					)
				);

				$order_items = $order->get_items();

				if ( !empty( $order_items ) ) {

					// Rental order item data must be removed, this ensures that if the order status is changed to a status which would attempt to rebook the rental it does not happen (as the stock may have since been used by another order and there would be an overlap)

					foreach ( $order_items as $order_item_id => $order_item ) {

						// If it is a rental line item then add cancelled meta (without this non rentals would get the cancelled meta)

						if ( !empty( wc_get_order_item_meta( $order_item_id, 'wcrp_rental_products_rent_from', true ) ) ) { // If line item is a rental

							if ( empty( wc_get_order_item_meta( $order_item_id, 'wcrp_rental_products_cancelled', true ) ) ) {

								WCRP_Rental_Products_Order_Line_Items::order_line_item_cancel_rental( $order_item_id, true );

							}

						}

					}

				}

			}

		}

	}

}
