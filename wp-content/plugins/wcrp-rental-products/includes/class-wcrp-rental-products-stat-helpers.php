<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Stat_Helpers' ) ) {

	class WCRP_Rental_Products_Stat_Helpers {

		public static function total_current_rentals() {

			global $wpdb;

			// Total current rentals is each rental order item from and including the current date unless returned, excludes rentals which have been removed from the rentals table e.g. refunded, cancelled, archived, etc

			return (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT( DISTINCT( order_item_id ) ) FROM `{$wpdb->prefix}wcrp_rental_products_rentals` AS rentals WHERE NOT EXISTS ( SELECT 1 FROM `{$wpdb->prefix}woocommerce_order_itemmeta` WHERE `meta_key` = 'wcrp_rental_products_returned' AND `meta_value` = 'yes' AND `order_item_id` = rentals.`order_item_id` ) AND `reserved_date` >= %s ORDER BY `reserved_date` ASC",
					wp_date( 'Y-m-d' ) // This is wp_date() as we want to get the total as per rentals >= the local WordPress date
				)
			);

		}

		public static function total_current_rentals_description() {

			return esc_html__( 'Total number of current rentals across all orders except where rental dates past, or the rental has been marked as returned or cancelled. This total is not quantity based.', 'wcrp-rental-products' );

		}

		public static function total_rental_orders( $days ) {

			global $wpdb;

			// Get the local WordPress date to use for database query

			if ( 1 == $days ) {

				$date = wp_date( 'Y-m-d' );

			} else {

				$date = wp_date( 'Y-m-d', strtotime( '- ' . $days . ' days' ) ); // Note that minus x days covers the full period from a calendar view standpoint

			}

			/*
			Query total rental orders based on post_date, not post_date_gmt, as $date is local WordPress date

			We previously used to use get_gmt_from_date() on the wp_date() above, then have 2 different queries, one for HPOS, one for non-HPOS, as the HPOS order table currently doesn't have local created/modified dates/times stored, we attempted to convert the local WordPress date to GMT, and for both queries we used the date_created_gmt (HPOS) and post_date_gmt (non-HPOS), but it caused several issues when dealing with timezones, an example of this is when attempting to get todays orders when in a non-GMT timezone, if the local WordPress date is 2024-05-02, but the order GMT date is 2024-05-01, todays date would be determined as 2024-05-01 GMT, and then the day befores orders are included in the today count even though the local date is 2024-05-02, there are many other examples, and we've attempted to get this to work using a variety of methods, but none were reliable, therefore the safest, most accurate solution here is to use the wp_posts table which has the local WordPress date/time for the posts, even for HPOS orders due to the shop_order_placehold row, it is strongly recommended to not refactor this in future and continue using this method only

			Important to note that currently the post_date in wp_posts does not get updated for HPOS based orders if the order created date is updated, due to a bug in WooCommerce raised here: https://github.com/woocommerce/woocommerce/issues/45783, this is an edge case as users wouldn't generally change the order created date, hopefully this bug will be fixed in WooCommerce core in future
			*/

			return (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT( * ) FROM ( SELECT DISTINCT ( order_id ) FROM `{$wpdb->prefix}wcrp_rental_products_rentals` UNION SELECT DISTINCT ( order_id ) FROM `{$wpdb->prefix}wcrp_rental_products_rentals_archive` ) AS rentals INNER JOIN `{$wpdb->prefix}posts` AS posts ON rentals.order_id = posts.ID WHERE posts.post_date >= %s;",
					$date
				)
			);

		}

		public static function total_rental_orders_description( $days ) {

			if ( 1 == $days ) {

				return esc_html__( 'Total number of rental orders today, excluding orders where all rentals cancelled.', 'wcrp-rental-products' );

			} else {

				// translators: %s: last days
				return sprintf( esc_html__( 'Total number of rental orders in the last %s days, excluding orders where all rentals cancelled.', 'wcrp-rental-products' ), $days );

			}

		}

		public static function total_rental_products() {

			global $wpdb;

			return (int) $wpdb->get_var(
				"SELECT COUNT( * ) FROM `{$wpdb->prefix}postmeta` WHERE `meta_key` = '_wcrp_rental_products_rental' AND `meta_value` IN ( 'yes', 'yes_purchase' );"
			);

		}

		public static function total_rental_products_description() {

			return esc_html__( 'Total number of rental products.', 'wcrp-rental-products' );

		}

	}

}
