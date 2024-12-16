<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Rentals_Calendar' ) ) {

	class WCRP_Rental_Products_Rentals_Calendar {

		public static function events() {

			global $wpdb;

			$events = array();
			$colors = WCRP_Rental_Products_Rentals_Dashboard::colors();
			$current_timestamp_in_timezone = strtotime( wp_date( 'Y-m-d H:i:s' ) );
			$order_item_ids = $wpdb->get_results( "SELECT DISTINCT( order_item_id ) FROM `{$wpdb->prefix}wcrp_rental_products_rentals`;" );

			if ( isset( $_GET['calendar_archive'] ) ) {

				if ( 'include' == $_GET['calendar_archive'] ) {

					$order_item_ids_archived = $wpdb->get_results( "SELECT DISTINCT( order_item_id ) FROM `{$wpdb->prefix}wcrp_rental_products_rentals_archive`;" );

					if ( !empty( $order_item_ids_archived ) ) {

						$order_item_ids = (object) array_merge( (array) $order_item_ids, (array) $order_item_ids_archived );

					}

				}

			}

			foreach ( $order_item_ids as $order_item_id ) {

				$order_item_id = $order_item_id->order_item_id;

				try {

					// Try to get order item, if order item is invalid (e.g. order item id passed is invalid for some reason such as old invalid/manipulated data) then skip as causes fatal error uncaught exception: invalid order item

					$order_item = new WC_Order_Item_Product( $order_item_id ); // As these order item ids are from the rentals database tables we already know they are WC_Order_Item_Product

				} catch ( Exception $e ) {

					continue;

				}

				$event_data = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT MIN(`reserved_date`) as min_date, MAX(`reserved_date`) as max_date, order_id, product_id, quantity FROM `{$wpdb->prefix}wcrp_rental_products_rentals` WHERE order_item_id = %d;",
						$order_item_id
					)
				);

				if ( isset( $_GET['calendar_archive'] ) ) {

					if ( 'include' == $_GET['calendar_archive'] ) {

						if ( empty( $event_data[0]->min_date ) ) { // If no event data in rentals event data, it is in the archive event data

							$event_data = $wpdb->get_results(
								$wpdb->prepare(
									"SELECT MIN(`reserved_date`) as min_date, MAX(`reserved_date`) as max_date, order_id, product_id, quantity FROM `{$wpdb->prefix}wcrp_rental_products_rentals_archive` WHERE order_item_id = %d;",
									$order_item_id
								)
							);

						}

					}

				}

				if ( !empty( $event_data ) ) {

					$order_id = esc_html( $event_data[0]->order_id );
					$order = wc_get_order( $order_id );

					// If no order object then it's likely an order used to exist but has been manually deleted via the database or similar and therefore not triggered any of the order deletion hook functions which remove from the rentals database table, the rentals for that order do not get added to the calendar in this scenario, if the code below did run it would cause fatal errors as there are order object functions included

					if ( !empty( $order ) ) {

						$order_item_in_person_pick_up_return = wc_get_order_item_meta( $order_item_id, 'wcrp_rental_products_in_person_pick_up_return', true );
						$order_item_in_person_pick_up_time = wc_get_order_item_meta( $order_item_id, 'wcrp_rental_products_in_person_pick_up_time', true );
						$order_item_in_person_return_date = wc_get_order_item_meta( $order_item_id, 'wcrp_rental_products_in_person_return_date', true );
						$order_item_in_person_return_time = wc_get_order_item_meta( $order_item_id, 'wcrp_rental_products_in_person_return_time', true );
						$order_item_return_days = wc_get_order_item_meta( $order_item_id, 'wcrp_rental_products_return_days_threshold', true );
						$order_item_returned = wc_get_order_item_meta( $order_item_id, 'wcrp_rental_products_returned', true );

						$pa_meta = $wpdb->get_results(
							$wpdb->prepare(
								"SELECT meta_key, meta_value FROM `{$wpdb->prefix}woocommerce_order_itemmeta` WHERE `order_item_id` = %d AND `meta_key` LIKE %s;",
								$order_item_id,
								$wpdb->esc_like( 'pa_' ) . '%'
							)
						);

						if ( !empty( $pa_meta ) ) {

							$pa_meta_string = '';

							foreach ( $pa_meta as $pm ) {

								$pa_meta_string .= ucwords( str_replace( '_', ' ', str_replace( 'pa_', '', $pm->meta_key ) ) ) . ': ' . ucwords( $pm->meta_value ) . ', ';

							}

							$pa_meta_string = rtrim( $pa_meta_string, ', ' );

						} else {

							$pa_meta_string = '';

						}

						if ( 'yes' !== $order_item_in_person_pick_up_return ) {

							// If not an in person pick up/return - set rental start/end date variables

							$rental_start_date = esc_html( $event_data[0]->min_date );
							$rental_end_date = gmdate( 'Y-m-d', strtotime( esc_html( $event_data[0]->max_date ) . ' -' . $order_item_return_days . ' days' ) );
							$rental_end_date = gmdate( 'Y-m-d', strtotime( $rental_end_date . ' + 1 days' ) ); // Adds a day to the date as if 2020-01-05 this is at 00:00 and FullCalendar would stop the event row on 2020-01-04, this would also occur with calendar applications via the calendar feed if not done

							// If not an in person pick up/return - set return start/end date variables

							$return_start_date = $rental_end_date;
							$return_end_date = esc_html( $event_data[0]->max_date );
							$return_end_date = gmdate( 'Y-m-d', strtotime( $return_end_date . ' + 1 days' ) ); // Adds a day to the date as if 2020-01-05 this is at 00:00 and FullCalendar would stop the event row on 2020-01-04, this would also occur with calendar applications via the calendar feed if not done

						} else {

							// If an in person pick up/return - set rental start/end date variables

							$rental_start_date = esc_html( $event_data[0]->min_date );
							$rental_end_date = esc_html( $event_data[0]->max_date );// No $order_item_return_days to be subtracted as an in person pick up/returns have 0 return days

							if ( $event_data[0]->max_date !== $order_item_in_person_return_date ) { // Without this condition in person pick up/return rentals with an in person return date of next day would not span the correct number of days

								$rental_end_date = gmdate( 'Y-m-d', strtotime( $rental_end_date . ' + 1 days' ) ); // Adds a day to the date as if 2020-01-05 this is at 00:00 and FullCalendar would stop the event row on 2020-01-04, this would also occur with calendar applications via the calendar feed if not done

							}

							// If an in person pick up/return - set return start/end date variables

							$return_start_date = $order_item_in_person_return_date;
							$return_end_date = $order_item_in_person_return_date; // No extra day added like the above because in person return dates are singular, they do not span multiple days

						}

						$product_title = $order_item->get_name(); // We previously got the product title from the product, however if the product was updated to a different name it would be confusing, we also found if the product was deleted it causes the product title here to be empty, so we use the name from the order item, the only issue with this is for variations as it includes, for example, ' - Blue' on the end, but if there are multiple options for the product it doesn't include them all only the first, hence why we display them all in the event using $pa_meta from earlier in this code
						$product_title = ( $order_item->get_variation_id() > 0 ? substr( $product_title, 0, strrpos( $product_title, '-' ) ) : $product_title ); // If it is a variation we need remove everything after the last - character, because this is the singular variation option described above, which we don't want including because pa_meta includes it, and that allows for multiple options to be displayed not just one, this is consistent with similar display in inventory

						$product_id = esc_html( $event_data[0]->product_id );
						$order_status = $order->get_status();
						$order_status_name = ucfirst( wc_get_order_status_name( $order_status ) );// ucfirst as can be a trash status and as not a WooCommerce order status is all lowercase
						$order_status_no_prefix = str_replace( 'wc-', '', $order_status );
						$quantity = esc_html( $event_data[0]->quantity );

						$customer_name = WCRP_Rental_Products_Rentals_Dashboard::order_customer_name( $order );

						// Set color/key class

						if ( 'yes' == $order_item_returned ) {

							$color = esc_html( $colors['green'] );
							$color_class = 'wcrp-rental-products-rentals-fc-event-key-returned';

						} elseif ( 'yes' !== $order_item_in_person_pick_up_return && $current_timestamp_in_timezone > strtotime( $return_end_date . ' - 1 days' ) ) { // Has to be - 1 days to ensure comparison correct due to the extra day added to $return_end_date earlier, see comments where $return_end_date set above

							$color = esc_html( $colors['red'] );
							$color_class = 'wcrp-rental-products-rentals-fc-event-key-not-returned';

						} elseif ( 'yes' == $order_item_in_person_pick_up_return && $current_timestamp_in_timezone > strtotime( $return_end_date . ' ' . WCRP_Rental_Products_Misc::four_digit_time_convert_to_timestamp_string( $order_item_in_person_return_time ) ) ) {

							$color = esc_html( $colors['red'] );
							$color_class = 'wcrp-rental-products-rentals-fc-event-key-not-returned';

						} elseif ( 'yes' !== $order_item_in_person_pick_up_return && $current_timestamp_in_timezone > strtotime( $rental_start_date ) && $current_timestamp_in_timezone < strtotime( $return_end_date . ' - 1 days' ) ) { // Has to be - 1 days to ensure comparison correct due to the extra day added to $return_end_date earlier, see comments where $return_end_date set above

							$color = esc_html( $colors['blue_dark'] );
							$color_class = 'wcrp-rental-products-rentals-fc-event-key-current';

						} elseif ( 'yes' == $order_item_in_person_pick_up_return && $current_timestamp_in_timezone > strtotime( $rental_start_date . ' ' . WCRP_Rental_Products_Misc::four_digit_time_convert_to_timestamp_string( $order_item_in_person_pick_up_time ) ) && $current_timestamp_in_timezone < strtotime( $return_end_date . ' ' . WCRP_Rental_Products_Misc::four_digit_time_convert_to_timestamp_string( $order_item_in_person_return_time ) ) ) { // Does not have to be - 1 days because a day not subtracted for in person pick up return, see comments where $return_end_date set above

							$color = esc_html( $colors['blue_dark'] );
							$color_class = 'wcrp-rental-products-rentals-fc-event-key-current';

						} else {

							$color = esc_html( $colors['blue'] );
							$color_class = 'wcrp-rental-products-rentals-fc-event-key-future';

						}

						// Event - rental

						$events[] = array(
							'class'				=> 'wcrp-rental-products-rentals-fc-event wcrp-rental-products-rentals-fc-event-type-rental ' . $color_class . ' wcrp-rental-products-rentals-fc-event-order-status-' . $order_status_no_prefix,
							'color'				=> $color,
							'customer_name'		=> $customer_name,
							'end'				=> $rental_end_date,
							'order_id'			=> $order_id,
							'order_status'		=> $order_status,
							'order_status_name'	=> $order_status_name,
							'pa_meta'			=> $pa_meta_string,
							'product_id'		=> $product_id,
							'product_title'		=> $product_title,
							'quantity'			=> $quantity,
							'start'				=> $rental_start_date . ( 'yes' == $order_item_in_person_pick_up_return ? WCRP_Rental_Products_Misc::four_digit_time_convert_to_iso_string( $order_item_in_person_pick_up_time ) : '' ),
							'type'				=> 'rental'
						);

						if ( (int) $order_item_return_days > 0 || 'yes' == $order_item_in_person_pick_up_return ) {

							// Event - return

							$events[] = array(
								'class'				=> 'wcrp-rental-products-rentals-fc-event wcrp-rental-products-rentals-fc-event-type-return ' . $color_class . ' wcrp-rental-products-rentals-fc-event-order-status-' . $order_status_no_prefix,
								'color'				=> $color,
								'customer_name'		=> $customer_name,
								'end'				=> $return_end_date . ( 'yes' == $order_item_in_person_pick_up_return ? WCRP_Rental_Products_Misc::four_digit_time_convert_to_iso_string( $order_item_in_person_return_time ) : '' ),
								'order_id'			=> $order_id,
								'order_status'		=> $order_status,
								'order_status_name'	=> $order_status_name,
								'pa_meta'			=> $pa_meta_string,
								'product_id'		=> $product_id,
								'product_title'		=> $product_title,
								'quantity'			=> $quantity,
								'start'				=> $return_start_date . ( 'yes' == $order_item_in_person_pick_up_return ? WCRP_Rental_Products_Misc::four_digit_time_convert_to_iso_string( $order_item_in_person_return_time ) : '' ),
								'type'				=> 'return'
							);

						}

					}

				}

			}

			return $events;

		}

		public static function event_name( $event, $public ) {

			$name = '';

			if ( !empty( $event ) ) {

				$name = $event['quantity'] . ' ' . esc_html__( 'x', 'wcrp-rental-products' ) . ' ' . esc_html( $event['product_title'] ) . ( !empty( $event['pa_meta'] ) ? ' ' . esc_html__( '(', 'wcrp-rental-products' ) . esc_html( $event['pa_meta'] ) . esc_html__( ')', 'wcrp-rental-products' ) : '' ) . ' ' . esc_html__( '#', 'wcrp-rental-products' ) . esc_html( $event['product_id'] ) . ' ' . esc_html__( '/', 'wcrp-rental-products' ) . ' ' . esc_html__( 'Order #', 'wcrp-rental-products' ) . esc_html( $event['order_id'] ) . ' ' . ( false == $public ? wp_kses_post( !empty( $event['customer_name'] ) ? ' ' . $event['customer_name'] . ' ' : ' ' ) : '' ) . esc_html__( '(', 'wcrp-rental-products' ) . esc_html( $event['order_status_name'] ) . esc_html__( ')', 'wcrp-rental-products' ) . ( 'return' == $event['type'] ? ' ' . esc_html__( '-', 'wcrp-rental-products' ) . ' ' . esc_html__( 'Return expected', 'wcrp-rental-products' ) : '' );

			}

			return $name;

		}

	}

}
