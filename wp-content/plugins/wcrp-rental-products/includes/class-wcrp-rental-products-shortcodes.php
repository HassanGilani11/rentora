<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Shortcodes' ) ) {

	class WCRP_Rental_Products_Shortcodes {

		public function __construct() {

			add_shortcode( 'wcrp_rental_products_availability_checker', array( $this, 'availability_checker' ) );
			add_shortcode( 'wcrp_rental_products_rental_purchase_toggle', array( $this, 'rental_purchase_toggle' ) );

		}

		public function availability_checker( $atts ) {

			$availability_checker_mode = get_option( 'wcrp_rental_products_availability_checker_mode' );
			$availability_checker = ( 'ajax' == $availability_checker_mode ? WCRP_Rental_Products_Availability_Checker::display_ajax_placeholder() : WCRP_Rental_Products_Availability_Checker::display() );

			return $availability_checker;

		}

		public function rental_purchase_toggle( $atts ) {

			// Displays the rental or purchase toggle - the toggle is normally automatically added via the woocommerce_single_product_summary action hook, however when using some themes/page builders like Elementor this core hook is not used, so this shortcode exists purely to manually include the rental or purchase toggle if it doesn't get displayed automatically due to the missing hook

			ob_start();
			WCRP_Rental_Products_Product_Display::rental_purchase_toggle();
			return ob_get_clean();

		}

	}

}
