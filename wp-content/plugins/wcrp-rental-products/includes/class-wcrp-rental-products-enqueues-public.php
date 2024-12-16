<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Enqueues_Public' ) ) {

	class WCRP_Rental_Products_Enqueues_Public {

		public function __construct() {

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueues' ) );

		}

		public function enqueues() {

			wp_enqueue_script( 'jquery' );

			WCRP_Rental_Products_Enqueues_Assets::css_public();
			WCRP_Rental_Products_Enqueues_Assets::litepicker(); // Litepicker enqueued globally for availability checker and additionally due to issues with themes/plugins using quick view and attempting to get litepicker on non-product pages

			if ( 'ajax' == get_option( 'wcrp_rental_products_availability_checker_mode' ) ) {

				WCRP_Rental_Products_Enqueues_Assets::js_public_availability_checker_ajax();

			}

		}

	}

}
