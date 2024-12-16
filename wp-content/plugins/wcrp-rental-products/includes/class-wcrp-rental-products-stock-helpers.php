<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Stock_Helpers' ) ) {

	class WCRP_Rental_Products_Stock_Helpers {

		public static function total_rented_on_date( $args ) {

			global $wpdb;

			if ( isset( $args['date'] ) && isset( $args['product_id'] ) ) {

				$date = $args['date'];
				$product_id = $args['product_id'];

				// Archive database table data not included as this has all been returned and therefore available

				$total_rented_on_date = (int) $wpdb->get_results(
					$wpdb->prepare(
						"SELECT SUM( quantity ) AS total FROM `{$wpdb->prefix}wcrp_rental_products_rentals` WHERE `product_id` = %s AND `reserved_date` = %s;",
						$product_id,
						$date
					)
				)[0]->total;

				$total_rented_on_date = self::maybe_subtract_from_date_rented_total(
					array(
						'date'			=> $date,
						'product_id'	=> $product_id,
						'total'			=> $total_rented_on_date,
					)
				);

				return (int) $total_rented_on_date;

			}

		}

		public static function maybe_subtract_from_date_rented_total( $args ) {

			global $wpdb;

			if ( isset( $args['date'] ) && isset( $args['product_id'] ) && isset( $args['total'] ) ) {

				$date = $args['date'];
				$product_id = $args['product_id'];
				$total = (int) $args['total']; // This is the quantity already rented on the date, it then may get subtracted below
				$immediate_rental_stock_replenishment = get_option( 'wcrp_rental_products_immediate_rental_stock_replenishment' );
				$in_person_checks_product_id_meta_key = ( 'product_variation' !== get_post_type( $product_id ) ? '_product_id' : '_variation_id' );
				$already_subtracted = array();

				// Subtract based on immediate rental stock replenishment

				if ( 'yes' == $immediate_rental_stock_replenishment ) {

					$immediate_rental_stock_replenishment_returned_checks = $wpdb->get_results(
						$wpdb->prepare(
							"SELECT reserved_date, quantity, order_item_id FROM `{$wpdb->prefix}wcrp_rental_products_rentals` WHERE `product_id` = %d AND `reserved_date` = %s;",
							$product_id,
							$date
						)
					);

					if ( !empty( $immediate_rental_stock_replenishment_returned_checks ) ) {

						foreach ( $immediate_rental_stock_replenishment_returned_checks as $immediate_rental_stock_replenishment_returned_check ) {

							if ( 'yes' == wc_get_order_item_meta( $immediate_rental_stock_replenishment_returned_check->order_item_id, 'wcrp_rental_products_returned', true ) ) {

								$total = $total - (int) $immediate_rental_stock_replenishment_returned_check->quantity;
								$already_subtracted[] = $immediate_rental_stock_replenishment_returned_check->order_item_id; // The quantity for this order item has been subtracted, so add here to condition off so doesn't get subtracted again later on

							}

						}

					}

				}

				// Subtract based on in person return dates

				/*
				This makes same day return date rentals be available on the return date (e.g. returned at 10am, can be rented again from 11am), the total rented on date gets subtracted when it's a multi day rental to allow the overlap
				If it's a single day it doesn't subtract as single day rentals should remain unavailable from being rented on the date (when rental stock limited, see complexities info on in person return date product field)
				Doesn't apply to next day return date rentals as there is no row in the DB for the next day return, so nothing to subtract off
				If in person pick up/return is unrestricted there maybe overlaps in times, but this is warned

				Note that we do not have equivalent in person pick up date checks which make rentals be available on the pick up date as if doing this it would allow rentals to be returned on a date where a rental is starting, but in doing this there is potential of a single day rental being rented on the date a multi day rental starts, causing an overlap in pickup/return times (when rental stock limited, see complexities info on in person return date product field)
				*/

				$in_person_return_date_checks = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT `oim`.`order_item_id` FROM `{$wpdb->prefix}woocommerce_order_itemmeta` AS `oim` WHERE `meta_key` = 'wcrp_rental_products_in_person_return_date' AND `meta_value` = %s AND EXISTS ( SELECT 1 FROM `{$wpdb->prefix}woocommerce_order_itemmeta` WHERE `order_item_id` = `oim`.`order_item_id` AND `meta_key` = %s AND `meta_value` = %s );",
						$date,
						$in_person_checks_product_id_meta_key,
						$product_id
					)
				);

				if ( !empty( $in_person_return_date_checks ) ) {

					foreach ( $in_person_return_date_checks as $in_person_return_date_check ) {

						$order = wc_get_order( wc_get_order_id_by_order_item_id( $in_person_return_date_check->order_item_id ) );

						if ( !empty( $order ) ) {

							// The $in_person_return_date_checks query can include draft orders, these shouldn't be used as the quantity of them are not reserved rentals (not in rentals table)

							if ( 'checkout-draft' !== $order->get_status() ) { // Note that we do not include auto-draft here as an order has to be created before rental products can be added to an order (and in doing so no longer auto-draft status)

								if ( !in_array( $in_person_return_date_check->order_item_id, $already_subtracted ) ) { // If already subtracted don't subtract again

									if ( 'same_day' == wc_get_order_item_meta( $in_person_return_date_check->order_item_id, 'wcrp_rental_products_in_person_return_date_type', true ) && ( wc_get_order_item_meta( $in_person_return_date_check->order_item_id, 'wcrp_rental_products_in_person_pick_up_date', true ) !== wc_get_order_item_meta( $in_person_return_date_check->order_item_id, 'wcrp_rental_products_in_person_return_date', true ) ) ) {

										$total = $total - (int) wc_get_order_item_meta( $in_person_return_date_check->order_item_id, '_qty', true );

									}

								}

							}

						}

					}

				}

			}

			// For debugging use var_dump( $total ); here and view the cart page, the total displayed is the amount which is going to get subtracted, it is going to be subtracted from the total rented on the date from the calling functions, the total shown is not the overall stock total after the subtraction

			// Return total

			return (int) $total;

		}

	}

}
