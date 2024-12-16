<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Cart_Fees' ) ) {

	class WCRP_Rental_Products_Cart_Fees {

		// This class includes functions related to cart fees e.g. fees which result in a fee being added to cart as opposed to other fees such as in person pick up/return fees, those are simply added to the item total via WCRP_Rental_Products_Cart_Items::cart_item_prices()

		public function __construct() {

			add_action( 'woocommerce_cart_calculate_fees', array( $this, 'security_deposits' ), PHP_INT_MAX );

		}

		public function security_deposits( $cart ) {

			global $woocommerce;

			if ( !empty( $cart ) ) {

				if ( isset( $cart->cart_contents ) ) {

					if ( !empty( $cart->cart_contents ) ) {

						if ( false == apply_filters( 'wcrp_rental_products_cart_fees_disable_security_deposits', false, $cart ) ) {

							$security_deposits = array();

							foreach ( $cart->cart_contents as $cart_content_key => $cart_content_value ) {

								// If is rental (We don't use wcrp_rental_products_is_rental_only() or wcrp_rental_products_is_rental_purchase() here as wcrp_rental_products_is_rental_purchase would cause security deposits to be added to the purchasable part when not needed, so we condition off the wcrp_rental_products_rent_from meta that rental cart items will have)

								if ( isset( $cart_content_value['wcrp_rental_products_rent_from'] ) ) {

									$security_deposits = $this->prepare_security_deposits_array( $security_deposits, $cart_content_value['quantity'], $cart_content_value['product_id'], $cart_content_value['variation_id'] );

								}

							}

							if ( !empty( $security_deposits ) ) {

								$taxes_enabled = get_option( 'woocommerce_calc_taxes' );

								foreach ( $security_deposits as $security_deposit ) {

									$woocommerce->cart->add_fee( $security_deposit['name'], $security_deposit['amount'], ( 'yes' == $taxes_enabled && 'taxable' == $security_deposit['tax_status'] ? true : false ), $security_deposit['tax_class'] );

								}

							}

						}

					}

				}

			}

		}

		public static function prepare_security_deposits_array( $security_deposits, $product_quantity, $product_id, $variation_id = false ) {

			if ( is_array( $security_deposits ) && !empty( $product_quantity ) && !empty( $product_id ) ) {

				// Get general data

				$default_rental_options = wcrp_rental_products_default_rental_options();
				$price_decimal_separator = wc_get_price_decimal_separator();
				$prices_include_tax = get_option( 'woocommerce_prices_include_tax' );
				$taxes_enabled = get_option( 'woocommerce_calc_taxes' );

				// Get security deposit amount initially

				$security_deposit_amount = get_post_meta( $product_id, '_wcrp_rental_products_security_deposit_amount', true );

				// If a variation use the variation security deposit amount if set instead of parent amount

				if ( (int) $variation_id > 0 ) {

					$variation_security_deposit_amount = get_post_meta( $variation_id, '_wcrp_rental_products_security_deposit_amount', true );

					if ( '' !== $variation_security_deposit_amount ) {

						$security_deposit_amount = $variation_security_deposit_amount;

					}

				}

				// If there is a security deposit to be added

				if ( !empty( $security_deposit_amount ) ) {

					$security_deposit_amount = (float) str_replace( $price_decimal_separator, '.', $security_deposit_amount );

					$security_deposit_calculation = get_post_meta( $product_id, '_wcrp_rental_products_security_deposit_calculation', true );
					$security_deposit_calculation = ( '' !== $security_deposit_calculation ? $security_deposit_calculation : $default_rental_options['_wcrp_rental_products_security_deposit_calculation'] );

					$security_deposit_tax_status = get_post_meta( $product_id, '_wcrp_rental_products_security_deposit_tax_status', true );
					$security_deposit_tax_status = ( '' !== $security_deposit_tax_status ? $security_deposit_tax_status : $default_rental_options['_wcrp_rental_products_security_deposit_tax_status'] );

					$security_deposit_tax_class = get_post_meta( $product_id, '_wcrp_rental_products_security_deposit_tax_class', true );
					$security_deposit_tax_class = ( '' !== $security_deposit_tax_class ? $security_deposit_tax_class : $default_rental_options['_wcrp_rental_products_security_deposit_tax_class'] );

					$security_deposit_non_refundable = get_post_meta( $product_id, '_wcrp_rental_products_security_deposit_non_refundable', true );
					$security_deposit_non_refundable = ( '' !== $security_deposit_non_refundable ? $security_deposit_non_refundable : $default_rental_options['_wcrp_rental_products_security_deposit_non_refundable'] );

					if ( 'yes' == $taxes_enabled ) {

						if ( 'taxable' == $security_deposit_tax_status ) {

							$security_deposit_taxes = WC_Tax::get_rates( $security_deposit_tax_class );

							if ( !empty( $security_deposit_taxes ) ) { // This ensures array_shift does not cause fatal error if empty, WooCommerce Tax extension can return this empty when the automated taxes option is enabled

								$security_deposit_taxes = array_shift( $security_deposit_taxes );
								$security_deposit_tax_rate = array_shift( $security_deposit_taxes );

							} else {

								$security_deposit_tax_rate = 0;

							}

							if ( 'yes' == $prices_include_tax ) {

								$security_deposit_amount = $security_deposit_amount / ( 1 + ( $security_deposit_tax_rate / 100 ) ); // If taxable and prices include tax the amount is set to the exc tax total, this ensures correct calculation in all tax scenarios inconjunction with the $taxable parameter of add_fee in all scenarios

							}

						}

					}

					if ( 'quantity' == $security_deposit_calculation ) {

						$security_deposit_amount = (int) $product_quantity * $security_deposit_amount;

					}

					$fee_name = apply_filters( 'wcrp_rental_products_text_security_deposit', get_option( 'wcrp_rental_products_text_security_deposit' ) );

					if ( 'woocommerce_cart_calculate_fees' == current_action() ) {

						// When fees are added to the cart (but not when adding via the add/edit order screens via adding a product ) the name MUST be unique or if multiple cart items of same product then only one would be added hence the count being included in the fee name, this isn't done for the add/edit order screen when adding a product as the fees are added individually upon adding to order not iteratively after, so a count isn't possible, but in that context the name doesn't need to be unique to be added - the name being unique is a requirement of the WC_Cart's add_fee() function which the cart uses in security_deposits() function above

						$fee_name .= ' ' . esc_html__( '#', 'wcrp-rental-products' ) . ( count( $security_deposits ) + 1 );

					}

					if ( 'yes' == $security_deposit_non_refundable ) {

						$non_refundable_text = apply_filters( 'wcrp_rental_products_text_non_refundable', get_option( 'wcrp_rental_products_text_non_refundable' ) );

						if ( '' !== $non_refundable_text ) {

							$fee_name .= ' ' . esc_html__( '(', 'wcrp-rental-products' ) . $non_refundable_text . esc_html__( ')', 'wcrp-rental-products' );

						}

					} else {

						$refundable_text = apply_filters( 'wcrp_rental_products_text_refundable', get_option( 'wcrp_rental_products_text_refundable' ) );

						if ( '' !== $refundable_text ) {

							$fee_name .= ' ' . esc_html__( '(', 'wcrp-rental-products' ) . $refundable_text . esc_html__( ')', 'wcrp-rental-products' );

						}

					}

					$fee_name .= ' ' . esc_html__( '-', 'wcrp-rental-products' ) . ' ' . get_the_title( $product_id ) . ( (int) $variation_id > 0 ? ' ' . get_the_excerpt( $variation_id ) : '' );

					// Note that when these get added from this array they get sorted by woocommerce_sort_fees_callback filter which by default is by the amount not name

					$security_deposits[] = array(
						'name'			=> $fee_name,
						'amount'		=> $security_deposit_amount, // This can be any number of decimal places, cart deals with any rounding required
						'tax_status'	=> $security_deposit_tax_status,
						'tax_class'		=> $security_deposit_tax_class,
					);

				}

			} else {

				$security_deposits = array();

			}

			return $security_deposits;

		}

	}

}
