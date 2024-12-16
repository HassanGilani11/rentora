<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Deactivation' ) ) {

	class WCRP_Rental_Products_Deactivation {

		public function __construct() {

			register_deactivation_hook( plugin_dir_path( __DIR__ ) . 'wcrp-rental-products.php', array( $this, 'clear_scheduled_hooks' ) );

		}

		public function clear_scheduled_hooks() {

			wp_clear_scheduled_hook( 'wcrp_rental_products_archive_rentals' );
			wp_clear_scheduled_hook( 'wcrp_rental_products_emails_rental_return_reminders' );
			wp_clear_scheduled_hook( 'wcrp_rental_products_feeds_calendar' );

		}

	}

}
