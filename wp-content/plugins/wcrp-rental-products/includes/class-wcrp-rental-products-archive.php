<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Archive' ) ) {

	class WCRP_Rental_Products_Archive {

		public function __construct() {

			add_action( 'init', array( $this, 'schedule_events' ) );
			add_action( 'wcrp_rental_products_archive_rentals', array( $this, 'archive_rentals' ) );

		}

		public function schedule_events() {

			if ( false == wp_get_scheduled_event( 'wcrp_rental_products_archive_rentals' ) ) {

				wp_schedule_event( strtotime( '02:00:00' ), 'daily', 'wcrp_rental_products_archive_rentals' );

			}

		}

		public function archive_rentals() {

			global $wpdb;

			$archive_rentals = (int) get_option( 'wcrp_rental_products_archive_rentals' );

			if ( $archive_rentals > 0 ) {

				$order_date_older_than = gmdate( 'Y-m-d', strtotime( '-' . $archive_rentals . ' days', time() ) );

				if ( !empty( $order_date_older_than ) ) {

					// Copy rentals marked as returned in orders older than x days from the rentals table to the archive table

					// Note that to change from WordPress posts tables to WooCommerce orders tables and vice versa it requires the data to be synced, therefore there isn't a scenario below where the order wouldn't be there to join the posts/orders table to the rentals table order id, the wcrp_rental_products_returned is order item meta that remains stored as it always was

					if ( wcrp_rental_products_hpos_enabled() ) {

						$copy_rows_to_archive = $wpdb->query(
							$wpdb->prepare(
								"INSERT INTO `{$wpdb->prefix}wcrp_rental_products_rentals_archive` SELECT `rentals`.`rental_id`, `rentals`.`reserved_date`, `rentals`.`order_id`, `rentals`.`order_item_id`, `rentals`.`product_id`, `rentals`.`quantity` FROM `{$wpdb->prefix}wcrp_rental_products_rentals` as `rentals` INNER JOIN `{$wpdb->prefix}woocommerce_order_itemmeta` as `oim` ON `rentals`.`order_item_id` = `oim`.`order_item_id` INNER JOIN `{$wpdb->prefix}wc_orders` as `orders` ON `rentals`.`order_id` = `orders`.`id` WHERE `orders`.`date_created_gmt` < %s AND `oim`.`meta_key` = 'wcrp_rental_products_returned' AND `oim`.`meta_value` = 'yes';",
								$order_date_older_than
							)
						);

					} else {

						$copy_rows_to_archive = $wpdb->query(
							$wpdb->prepare(
								"INSERT INTO `{$wpdb->prefix}wcrp_rental_products_rentals_archive` SELECT `rentals`.`rental_id`, `rentals`.`reserved_date`, `rentals`.`order_id`, `rentals`.`order_item_id`, `rentals`.`product_id`, `rentals`.`quantity` FROM `{$wpdb->prefix}wcrp_rental_products_rentals` as `rentals` INNER JOIN `{$wpdb->prefix}woocommerce_order_itemmeta` as `oim` ON `rentals`.`order_item_id` = `oim`.`order_item_id` INNER JOIN `{$wpdb->prefix}posts` as `posts` ON `rentals`.`order_id` = `posts`.`ID` WHERE `posts`.`post_date_gmt` < %s AND `oim`.`meta_key` = 'wcrp_rental_products_returned' AND `oim`.`meta_value` = 'yes';",
								$order_date_older_than
							)
						);

					}

					if ( $copy_rows_to_archive > 0 ) { // More than zero rows effected (and not false error)

						// Delete rows from rentals table which exist in archive

						$wpdb->query(
							"DELETE FROM `{$wpdb->prefix}wcrp_rental_products_rentals` WHERE `rental_id` IN ( SELECT `rental_id` FROM `{$wpdb->prefix}wcrp_rental_products_rentals_archive` );"
						);

					}

				}

			}

		}

	}

}
