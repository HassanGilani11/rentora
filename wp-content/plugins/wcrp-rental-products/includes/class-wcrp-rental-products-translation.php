<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Translation' ) ) {

	class WCRP_Rental_Products_Translation {

		public function __construct() {

			add_action( 'init', array( $this, 'textdomain' ) );

		}

		public function textdomain() {

			load_plugin_textdomain( 'wcrp-rental-products', false, dirname( plugin_basename( __DIR__ ) ) . '/languages' );

		}

	}

}
