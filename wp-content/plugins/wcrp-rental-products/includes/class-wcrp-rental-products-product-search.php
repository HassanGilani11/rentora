<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Product_Search' ) ) {

	class WCRP_Rental_Products_Product_Search {

		public function __construct() {

			add_filter( 'woocommerce_json_search_found_products', array( $this, 'json_product_search_restrictions' ) );

		}

		public function json_product_search_restrictions( $products ) {

			// This function restricts the product search results used by WooCommerce when adding or editing an order in the dashboard and it restricts rental only products from the results. In the context of the add/edit order screen this is used by the core "Add product(s)" functionality, the rental only products are restricted from the results as if they were included it would add rental products without the necessary order item meta, availability would have not been checked, and the pricing wouldn't be correct. For rental only products or the rental part of rental or purchase products then the "Add rental product(s)" functionality is used instead to add the product to an order. Rental or purchase based products are not excluded from these searches as these can be added to an order as a means of adding the purchasable part of the product, this is referenced in the alert which appears when adding a rental product. Note that as the core "Add product(s)" search doesn't include any data that the AJAX being called is from that specific functionality we can only restrict for any use of the product search on the orders page, however there aren't any scenarios in core WooCommerce where a product search occurs via this method on the add/edit order page other than when adding a product to an order

			$referer = wp_get_referer();

			if ( !empty( $referer ) ) {

				$maybe_restrict_rentals = false;

				if ( wcrp_rental_products_hpos_enabled() ) {

					// We don't just look for page=wc-orders as this would also be the orders list

					if ( WCRP_Rental_Products_Misc::string_contains( $referer, 'page=wc-orders&action=edit' ) ) { // Is an order edit

						$maybe_restrict_rentals = true;

					} elseif ( WCRP_Rental_Products_Misc::string_contains( $referer, 'page=wc-orders&action=new' ) ) { // Is a new order

						$maybe_restrict_rentals = true;

					}

				} else {

					if ( WCRP_Rental_Products_Misc::string_contains( $referer, 'post=' ) ) { // If an existing post, but at this point we don't know if post is a shop_order post type until next condition

						$post_id = explode( 'post=', $referer );
						$post_id = explode( '&', $post_id[1] );
						$post_id = $post_id[0];

						if ( 'shop_order' == get_post_type( $post_id ) ) { // Is an order edit

							$maybe_restrict_rentals = true;

						}

					} elseif ( WCRP_Rental_Products_Misc::string_contains( $referer, 'post-new.php' ) ) { // If new post, but at this point we don't know if post is a shop_order post type until next condition

						if ( WCRP_Rental_Products_Misc::string_contains( $referer, 'post_type=shop_order' ) ) { // Is a new order

							$maybe_restrict_rentals = true;

						}

					}

				}

				if ( true == $maybe_restrict_rentals ) {

					if ( !empty( $products ) ) {

						foreach ( $products as $product_id => $product ) {

							if ( wcrp_rental_products_is_rental_only( $product_id ) ) { // Not unset if rental or purchase, so user can add the purchasable part of the rental if required

								unset( $products[$product_id] );

							}

						}

					}

				}

			}

			return $products;

		}

	}

}
