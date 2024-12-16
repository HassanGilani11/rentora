<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Cart_Redirects' ) ) {

	class WCRP_Rental_Products_Cart_Redirects {

		public function __construct() {

			add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'rental_or_purchase_add_to_cart_redirect' ), PHP_INT_MAX );

		}

		public function rental_or_purchase_add_to_cart_redirect( $url ) {

			// If redirect to cart after add is disabled

			if ( get_option( 'woocommerce_cart_redirect_after_add' ) !== 'yes' ) {

				$referer = wp_get_referer(); // Must use this and not got from $_GET['rent']

				// If add to cart came from rental part of a rental or purchase product

				if ( WCRP_Rental_Products_Misc::string_contains( $referer, 'rent=1' ) ) { // We look for rent=1 and not ?rent=1 as it could be ? or & depending on add_query_arg()

					// Redirect back to the referer (the product page URL with ?rent=1)

					$url = wp_get_referer();

				}

			}

			return $url;

		}

	}

}
