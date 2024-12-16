<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Enqueues_Admin' ) ) {

	class WCRP_Rental_Products_Enqueues_Admin {

		public function __construct() {

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueues' ) );

		}

		public function enqueues() {

			global $pagenow;
			global $post;

			wp_enqueue_script( 'jquery' );

			WCRP_Rental_Products_Enqueues_Assets::css_admin();
			WCRP_Rental_Products_Enqueues_Assets::js_admin();

			if ( 'admin.php' == $pagenow ) {

				if ( isset( $_GET['page'] ) ) {

					if ( 'wcrp-rental-products-rentals' == $_GET['page'] ) { // Rentals dashboard

						// We do not enqueue here based off of the tab as the $_GET wouldn't exist if clicking from the WooCommerce child menu link and therefore wouldn't know it's the calendar tab (default view if no tab in $_GET)

						WCRP_Rental_Products_Enqueues_Assets::datatables();
						WCRP_Rental_Products_Enqueues_Assets::datepicker();
						WCRP_Rental_Products_Enqueues_Assets::fullcalendar();
						WCRP_Rental_Products_Enqueues_Assets::select2();
						WCRP_Rental_Products_Enqueues_Assets::thickbox();

					} elseif ( 'wc-settings' == $_GET['page'] ) { // WooCommerce > Settings

						if ( isset( $_GET['tab'] ) && isset( $_GET['section'] ) ) {

							if ( 'products' == $_GET['tab'] && 'wcrp-rental-products' == $_GET['section'] ) { // WooCommerce Settings > Products > Rental products

								WCRP_Rental_Products_Enqueues_Assets::datepicker();
								WCRP_Rental_Products_Enqueues_Assets::thickbox();

							}

						}

					}

				}

			} elseif ( 'post.php' == $pagenow || 'post-new.php' == $pagenow ) {

				if ( !empty( $post ) ) { // This condition is here as some page builders e.g. UX Builder included with Flatsome theme do not appear to have global $post on the above $pagenow pages and then shows an attempt to read property ID on null PHP warning if PHP warnings display enabled

					if ( 'product' == get_post_type( $post->ID ) ) { // Edit or new Product

						WCRP_Rental_Products_Enqueues_Assets::datepicker();

					}

				}

			}

		}

	}

}
