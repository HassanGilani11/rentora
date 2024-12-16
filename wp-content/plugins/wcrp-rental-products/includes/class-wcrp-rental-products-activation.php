<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Activation' ) ) {

	class WCRP_Rental_Products_Activation {

		public function __construct() {

			register_activation_hook( plugin_dir_path( __DIR__ ) . 'wcrp-rental-products.php', array( $this, 'transients' ) );

		}

		public function transients() {

			set_transient( 'wcrp_rental_products_activation_notice', true, 604800 );

		}

	}

}
