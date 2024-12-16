<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Product_Data_Returns' ) ) {

	class WCRP_Rental_Products_Product_Data_Returns {

		public function __construct() {

			add_filter( 'woocommerce_product_is_in_stock', array( $this, 'wc_product_and_variants' ), PHP_INT_MAX, 2 );
			add_filter( 'woocommerce_product_variation_is_in_stock', array( $this, 'wc_product_and_variants' ), PHP_INT_MAX, 2 );

			add_filter( 'woocommerce_product_get_stock_quantity', array( $this, 'wc_product_and_variants' ), PHP_INT_MAX, 2 );
			add_filter( 'woocommerce_product_variation_get_stock_quantity', array( $this, 'wc_product_and_variants' ), PHP_INT_MAX, 2 );

			add_filter( 'woocommerce_product_get_manage_stock', array( $this, 'wc_product_and_variants' ), PHP_INT_MAX, 2 );
			add_filter( 'woocommerce_product_variation_get_manage_stock', array( $this, 'wc_product_and_variants' ), PHP_INT_MAX, 2 );

			add_filter( 'woocommerce_product_get_tax_status', array( $this, 'wc_product_and_variants' ), PHP_INT_MAX, 2 );
			add_filter( 'woocommerce_product_variation_get_tax_status', array( $this, 'wc_product_and_variants' ), PHP_INT_MAX, 2 );

			add_filter( 'woocommerce_product_get_tax_class', array( $this, 'wc_product_and_variants' ), PHP_INT_MAX, 2 );
			add_filter( 'woocommerce_product_variation_get_tax_class', array( $this, 'wc_product_and_variants' ), PHP_INT_MAX, 2 );

			add_filter( 'woocommerce_product_get_shipping_class_id', array( $this, 'wc_product_and_variants' ), PHP_INT_MAX, 2 );
			add_filter( 'woocommerce_product_variation_get_shipping_class_id', array( $this, 'wc_product_and_variants' ), PHP_INT_MAX, 2 );

		}

		public function wc_product_and_variants( $value, $product ) {

			// If a rental product and meets specific conditions for rentals change WC_Product (and variants such as WC_Product_Variation) based return values conditionally, this is mostly used to ensure the correct stock changes occur for both purchasable and rental stocks depending on the rental type and stock management options used

			if ( !empty( $product ) ) {

				$product_id = $product->get_id();
				$product_type = $product->get_type();

				$rental_stock = get_post_meta( $product_id, '_wcrp_rental_products_rental_stock', true ); // This covers both simple products and variations, for variations this is because the woocommerce_product_variation_ based filters are run through and the $product_id is the variation ID

				if ( 'variation' == $product_type ) {

					$parent_product_id = $product->get_parent_id();

				} else {

					$parent_product_id = false;

				}

				if ( wcrp_rental_products_is_rental_only( ( 'variation' !== $product_type ? $product_id : $parent_product_id ) ) ) {

					// Stock based returns

					if ( '' == $rental_stock ) {

						$is_in_stock = true;
						$stock_quantity = PHP_INT_MAX; // Rental stock is unlimited

					} else {

						if ( (int) $rental_stock > 0 ) {

							$is_in_stock = true;
							$stock_quantity = PHP_INT_MAX; // Rental stock is certain level but set to unlimited as cart checks manage if enough stock, this just ensures it is deemed in stock

						} else {

							$is_in_stock = false;
							$stock_quantity = 0; // No rental stock

						}

					}

					$manage_stock = false;

				} elseif ( wcrp_rental_products_is_rental_purchase( ( 'variation' !== $product_type ? $product_id : $parent_product_id ) ) ) {

					if ( isset( $_GET['rent'] ) || ( isset( $_POST['wcrp_rental_products_rental_form_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['wcrp_rental_products_rental_form_nonce'] ), 'wcrp_rental_products_rental_form' ) ) ) { // Nonce condition used to ensure the product is deemed in stock when adding to cart (as that is passed to the cart and therefore on reload of product page/redirect to cart that condition will trigger)

						if ( '1' == ( isset( $_GET['rent'] ) ? sanitize_text_field( $_GET['rent'] ) : '' ) || isset( $_POST['wcrp_rental_products_rent_from'] ) ) { // Ternary used as $_GET['rent'] may not be set depending on OR conditions above and would cause a PHP notice

							// Stock based returns

							if ( '' == $rental_stock ) {

								$is_in_stock = true;
								$stock_quantity = PHP_INT_MAX; // Rental stock is unlimited

							} else {

								if ( (int) $rental_stock > 0 ) {

									$is_in_stock = true;
									$stock_quantity = PHP_INT_MAX; // Rental stock is certain level but set to unlimited as cart checks manage if enough stock, this just ensures it is deemed in stock

								} else {

									$is_in_stock = false;
									$stock_quantity = 0; // No rental stock

								}

							}

							$manage_stock = false;

							// Rental or purchase rental override returns

							$rental_purchase_rental_tax_override = get_post_meta( ( 'variation' !== $product_type ? $product_id : $parent_product_id ), '_wcrp_rental_products_rental_purchase_rental_tax_override', true );
							$rental_purchase_rental_shipping_override = get_post_meta( ( 'variation' !== $product_type ? $product_id : $parent_product_id ), '_wcrp_rental_products_rental_purchase_rental_shipping_override', true );

							if ( 'yes' == $rental_purchase_rental_tax_override ) {

								$tax_status = get_post_meta( ( 'variation' !== $product_type ? $product_id : $parent_product_id ), '_wcrp_rental_products_rental_purchase_rental_tax_override_status', true );
								$tax_class = get_post_meta( ( 'variation' !== $product_type ? $product_id : $parent_product_id ), '_wcrp_rental_products_rental_purchase_rental_tax_override_class', true );

							}

							if ( 'yes' == $rental_purchase_rental_shipping_override ) {

								$shipping_class_id = get_post_meta( ( 'variation' !== $product_type ? $product_id : $parent_product_id ), '_wcrp_rental_products_rental_purchase_rental_shipping_override_class', true ); // Also means get_shipping_class will return correctly too as that calls get_shipping_class_id

							}

						}

					}

				}

			}

			if ( ( 'woocommerce_product_is_in_stock' == current_filter() || 'woocommerce_product_variation_is_in_stock' == current_filter() ) && isset( $is_in_stock ) ) {

				return $is_in_stock;

			} elseif ( ( 'woocommerce_product_get_stock_quantity' == current_filter() || 'woocommerce_product_variation_get_stock_quantity' == current_filter() ) && isset( $stock_quantity ) ) {

				return $stock_quantity;

			} elseif ( ( 'woocommerce_product_get_manage_stock' == current_filter() || 'woocommerce_product_variation_get_manage_stock' == current_filter() ) && isset( $manage_stock ) ) {

				return $manage_stock;

			} elseif ( ( 'woocommerce_product_get_tax_status' == current_filter() || 'woocommerce_product_variation_get_tax_status' == current_filter() ) && isset( $tax_status ) ) {

				return $tax_status;

			} elseif ( ( 'woocommerce_product_get_tax_class' == current_filter() || 'woocommerce_product_variation_get_tax_class' == current_filter() ) && isset( $tax_class ) ) {

				return $tax_class;

			} elseif ( ( 'woocommerce_product_get_shipping_class_id' == current_filter() || 'woocommerce_product_variation_get_shipping_class_id' == current_filter() ) && isset( $shipping_class_id ) ) {

				return $shipping_class_id;

			} else {

				return $value;

			}

		}

	}

}
