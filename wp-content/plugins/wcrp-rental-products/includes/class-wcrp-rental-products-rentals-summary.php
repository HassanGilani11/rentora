<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Rentals_Summary' ) ) {

	class WCRP_Rental_Products_Rentals_Summary {

		public static function latest_rental_orders() {

			global $wpdb;

			// This query gets the latest rental orders ordered by date, it also unions the rentals archive just to cover scenarios where one of the 50 latest orders has all its rentals archived and therefore no longer in the rentals table

			if ( true == wcrp_rental_products_hpos_enabled() ) {

				return $wpdb->get_results(
					$wpdb->prepare(
						"SELECT `order_id` FROM ( SELECT DISTINCT( `order_id` ) FROM `{$wpdb->prefix}wcrp_rental_products_rentals` UNION SELECT DISTINCT( `order_id` ) FROM `{$wpdb->prefix}wcrp_rental_products_rentals_archive` ) AS rentals INNER JOIN `{$wpdb->prefix}wc_orders` AS orders ON rentals.order_id = orders.id ORDER BY orders.date_created_gmt DESC LIMIT %d;",
						self::orders_limit()
					)
				);

			} else {

				return $wpdb->get_results(
					$wpdb->prepare(
						"SELECT `order_id` FROM ( SELECT DISTINCT( `order_id` ) FROM `{$wpdb->prefix}wcrp_rental_products_rentals` UNION SELECT DISTINCT( `order_id` ) FROM `{$wpdb->prefix}wcrp_rental_products_rentals_archive` ) AS rentals INNER JOIN `{$wpdb->prefix}posts` AS posts ON rentals.order_id = posts.ID ORDER BY posts.post_date_gmt DESC LIMIT %d;",
						self::orders_limit()
					)
				);

			}

		}

		public static function flagged_rental_orders() {

			global $wpdb;

			// This query gets all rentals which aren't marked as returned and the final rent to date, which includes return days as those are rows, these are used to determine if the rental should be flagged as not returned and rent to date is greater than the current date, note that cancelled rentals don't matter as they aren't in the db table, archived rentals don't matter as returned so wouldn't be getting flagged

			$flagged_rental_orders = $wpdb->get_results(
				$wpdb->prepare(
					"
					SELECT rentals.order_id, rentals.order_item_id, rentals.reserved_date
					FROM `{$wpdb->prefix}wcrp_rental_products_rentals` AS rentals
					INNER JOIN ( SELECT order_id, order_item_id, MAX( reserved_date ) AS rent_to_inc_return_days FROM `{$wpdb->prefix}wcrp_rental_products_rentals` GROUP BY order_item_id ) AS rentals_grouped
					ON rentals.order_item_id = rentals_grouped.order_item_id
					AND rentals.reserved_date = rentals_grouped.rent_to_inc_return_days
					WHERE NOT EXISTS ( SELECT 1 FROM `{$wpdb->prefix}woocommerce_order_itemmeta` WHERE `meta_key` = 'wcrp_rental_products_returned' AND `meta_value` = 'yes' AND `order_item_id` = rentals.`order_item_id` )
					AND `reserved_date` < %s
					ORDER BY `rentals`.`reserved_date` ASC;
					",
					wp_date( 'Y-m-d' )
				)
			);

			// Create and return a new array using data above in a usable format, as $flagged_rental_orders isn't ordered by order id, we want an array in order ids with inner arrays of order items with reserved dates, so it can be iterated through easily for the flagged rental orders summary

			$return = array();

			foreach ( $flagged_rental_orders as $flagged_rental_order ) {

				$order_id = $flagged_rental_order->order_id;
				$order_item_id = $flagged_rental_order->order_item_id;
				$reserved_date = $flagged_rental_order->reserved_date;
				$return[$order_id][$order_item_id] = $reserved_date;

			}

			return $return;

		}


		public static function orders_limit() {

			// This is the max limit of latest rental orders, and where the pagination splits for flagged rental orders, it isn't possible to have 2 different limits for latest vs flagged, as would require the 2 DataTables to be instantiated separately with different options

			return 50;

		}

	}

}
