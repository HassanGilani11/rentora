<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Cart_Items' ) ) {

	class WCRP_Rental_Products_Cart_Items {

		public function __construct() {

			add_filter( 'woocommerce_add_cart_item_data', array( $this, 'cart_item_data_add' ), PHP_INT_MAX, 2 );
			add_filter( 'woocommerce_get_item_data', array( $this, 'cart_item_data' ), 10, 2 );
			add_filter( 'woocommerce_cart_item_class', array( $this, 'cart_item_row_class' ), 10, 3 );
			add_action( 'woocommerce_cart_updated', array( $this, 'cart_item_rental_purchase_overrides' ), PHP_INT_MAX - 1 ); // -1 as ensures tax class override gets used if present before cart_item_prices
			add_action( 'woocommerce_before_calculate_totals', array( $this, 'cart_item_rental_purchase_overrides' ), PHP_INT_MAX - 1 ); // Same function hooked as on woocommerce_cart_updated, this is also added on the woocommerce_before_calculate_totals hook as the mini cart total does not update just on woocommerce_cart_updated, so if this wasn't also on woocommerce_before_calculate_totals the subtotal in minicart would not be correct, woocommerce_before_calculate_totals cannot be used alone as then the 1 x $xx.xx product price would be incorrect in mini cart
			add_action( 'woocommerce_cart_updated', array( $this, 'cart_item_prices' ), PHP_INT_MAX );
			add_action( 'woocommerce_before_calculate_totals', array( $this, 'cart_item_prices' ), PHP_INT_MAX ); // Same function hooked as on woocommerce_cart_updated, same reason as more detailed comment above
			add_filter( 'woocommerce_cart_item_quantity', array( $this, 'cart_item_quantity' ), PHP_INT_MAX, 3 ); // If classic cart/checkout
			add_filter( 'woocommerce_store_api_product_quantity_minimum', array( $this, 'cart_item_quantity_block_cart_checkout' ), PHP_INT_MAX, 3 ); // If block cart/checkout
			add_filter( 'woocommerce_store_api_product_quantity_maximum', array( $this, 'cart_item_quantity_block_cart_checkout' ), PHP_INT_MAX, 3 ); // If block cart/checkout
			add_action( 'woocommerce_tax_rate_added', array( $this, 'cart_items_flush_tax_rates_changes' ), PHP_INT_MAX );
			add_action( 'woocommerce_tax_rate_updated', array( $this, 'cart_items_flush_tax_rates_changes' ), PHP_INT_MAX );
			add_action( 'woocommerce_tax_rate_deleted', array( $this, 'cart_items_flush_tax_rates_changes' ), PHP_INT_MAX );
			add_action( 'updated_option', array( $this, 'cart_items_flush_tax_settings_changes' ), PHP_INT_MAX );

		}

		public function cart_item_data_add( $cart_item_data, $product_id ) {

			// Note that passed $product_id is the product id, for variations the variable contains the parent product id, so when the wcrp_rental_products_is_rental_only() and wcrp_rental_products_is_rental_purchase() are used below there is no need to pass the variation id due to this to determine if a rental

			if ( isset( $_POST['wcrp_rental_products_rental_form_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['wcrp_rental_products_rental_form_nonce'] ), 'wcrp_rental_products_rental_form' ) ) {

				if ( false == wcrp_rental_products_is_rental_only( $product_id ) && false == wcrp_rental_products_is_rental_purchase( $product_id ) ) {

					/*
					If the cart item is not a rental then do not set any rental cart item meta, the nonce verification condition above alone should have been enough for this, however we were made aware that when a plugin like WPC Force Sells for WooCommerce is used, this adds cart items (additional products that are forced to be added to cart when the main product is added to cart), but it seems to clone the cart item meta from the main product to the additional product cart items, however that would mean that without this condition rental cart item meta would get added to cart items which are not rentals and therefore is very incorrect data that would cause several issues (and the cart also cannot continue due to the cart checks for availability which get run as it has the cloned rent from/to meta, etc but it will then trigger wcrp_rental_products_is_rental_only or wcrp_rental_products_is_rental_purchase to be false in those cart check conditions)

					It is NOT recommended to use that plugin for forcing additional rental products to be added to cart from a rental product due to pricing issues, e.g. if the main rental product was 5 days * $10, the main rental product in cart would be $50, but the additional rentals are going to remain at $10 due to that plugin not being rental price aware as hasn't been added via the rental form

					If using the plugin referenced above to add additional non-rentals to a rental upon add to cart it is recommended to hide the additional total shown on the product page as it is not rental total aware
					*/

					return $cart_item_data;

				}

				// Some checks are '' !== checks not !empty used due to some of these potentially being a valid 0 e.g. return days threshold could be 0 (not false)

				if ( isset( $_POST['wcrp_rental_products_cart_item_validation'] ) && '' !== $_POST['wcrp_rental_products_cart_item_validation'] ) {

					$cart_item_data['wcrp_rental_products_cart_item_validation'] = sanitize_text_field( $_POST['wcrp_rental_products_cart_item_validation'] );

				}

				if ( isset( $_POST['wcrp_rental_products_cart_item_timestamp'] ) && '' !== $_POST['wcrp_rental_products_cart_item_timestamp'] ) {

					$cart_item_data['wcrp_rental_products_cart_item_timestamp'] = sanitize_text_field( $_POST['wcrp_rental_products_cart_item_timestamp'] );

				}

				if ( isset( $_POST['wcrp_rental_products_cart_item_price'] ) && '' !== $_POST['wcrp_rental_products_cart_item_price'] ) {

					$cart_item_data['wcrp_rental_products_cart_item_price'] = sanitize_text_field( $_POST['wcrp_rental_products_cart_item_price'] );

				}

				if ( isset( $_POST['wcrp_rental_products_rent_from'] ) && '' !== $_POST['wcrp_rental_products_rent_from'] ) {

					$cart_item_data['wcrp_rental_products_rent_from'] = sanitize_text_field( $_POST['wcrp_rental_products_rent_from'] );

				}

				if ( isset( $_POST['wcrp_rental_products_rent_to'] ) && '' !== $_POST['wcrp_rental_products_rent_to'] ) {

					$cart_item_data['wcrp_rental_products_rent_to'] = sanitize_text_field( $_POST['wcrp_rental_products_rent_to'] );

				}

				if ( isset( $_POST['wcrp_rental_products_start_days_threshold'] ) && '' !== $_POST['wcrp_rental_products_start_days_threshold'] ) {

					$cart_item_data['wcrp_rental_products_start_days_threshold'] = sanitize_text_field( $_POST['wcrp_rental_products_start_days_threshold'] );

				}

				if ( isset( $_POST['wcrp_rental_products_return_days_threshold'] ) && '' !== $_POST['wcrp_rental_products_return_days_threshold'] ) {

					$cart_item_data['wcrp_rental_products_return_days_threshold'] = sanitize_text_field( $_POST['wcrp_rental_products_return_days_threshold'] );

				}

				if ( isset( $_POST['wcrp_rental_products_advanced_pricing'] ) && '' !== $_POST['wcrp_rental_products_advanced_pricing'] ) {

					$cart_item_data['wcrp_rental_products_advanced_pricing'] = sanitize_text_field( $_POST['wcrp_rental_products_advanced_pricing'] );

				}

				// In person pick up/return

				if ( isset( $_POST['wcrp_rental_products_in_person_pick_up_return'] ) && 'yes' == $_POST['wcrp_rental_products_in_person_pick_up_return'] ) {

					$cart_item_data['wcrp_rental_products_in_person_pick_up_return'] = sanitize_text_field( $_POST['wcrp_rental_products_in_person_pick_up_return'] );

					if ( isset( $_POST['wcrp_rental_products_in_person_pick_up_date'] ) && '' !== $_POST['wcrp_rental_products_in_person_pick_up_date'] ) {

						$cart_item_data['wcrp_rental_products_in_person_pick_up_date'] = sanitize_text_field( $_POST['wcrp_rental_products_in_person_pick_up_date'] );

					}

					if ( isset( $_POST['wcrp_rental_products_in_person_pick_up_time'] ) && '' !== $_POST['wcrp_rental_products_in_person_pick_up_time'] ) {

						$cart_item_data['wcrp_rental_products_in_person_pick_up_time'] = sanitize_text_field( $_POST['wcrp_rental_products_in_person_pick_up_time'] );

					}

					if ( isset( $_POST['wcrp_rental_products_in_person_pick_up_fee'] ) && '' !== $_POST['wcrp_rental_products_in_person_pick_up_fee'] ) {

						$cart_item_data['wcrp_rental_products_in_person_pick_up_fee'] = sanitize_text_field( $_POST['wcrp_rental_products_in_person_pick_up_fee'] );

					}

					if ( isset( $_POST['wcrp_rental_products_in_person_return_date'] ) && '' !== $_POST['wcrp_rental_products_in_person_return_date'] ) {

						$cart_item_data['wcrp_rental_products_in_person_return_date'] = sanitize_text_field( $_POST['wcrp_rental_products_in_person_return_date'] );

					}

					if ( isset( $_POST['wcrp_rental_products_in_person_return_date_type'] ) && '' !== $_POST['wcrp_rental_products_in_person_return_date_type'] ) {

						$cart_item_data['wcrp_rental_products_in_person_return_date_type'] = sanitize_text_field( $_POST['wcrp_rental_products_in_person_return_date_type'] );

					}

					if ( isset( $_POST['wcrp_rental_products_in_person_return_time'] ) && '' !== $_POST['wcrp_rental_products_in_person_return_time'] ) {

						$cart_item_data['wcrp_rental_products_in_person_return_time'] = sanitize_text_field( $_POST['wcrp_rental_products_in_person_return_time'] );

					}

					if ( isset( $_POST['wcrp_rental_products_in_person_return_fee'] ) && '' !== $_POST['wcrp_rental_products_in_person_return_fee'] ) {

						$cart_item_data['wcrp_rental_products_in_person_return_fee'] = sanitize_text_field( $_POST['wcrp_rental_products_in_person_return_fee'] );

					}

				}

			}

			return $cart_item_data;

		}

		public function cart_item_data( $cart_item_data, $cart_item ) {

			if ( isset( $cart_item['wcrp_rental_products_rent_from'] ) && isset( $cart_item['wcrp_rental_products_rent_to'] ) && isset( $cart_item['wcrp_rental_products_return_days_threshold'] ) ) {

				// Only meta we want to display is used here, the other meta set above is used during availability checks but not displayed as not relevant to customer

				// '' !== checks not !empty as value could be 0 see similar comment in cart_item_data_add()

				if ( '' !== $cart_item['wcrp_rental_products_rent_from'] && '' !== $cart_item['wcrp_rental_products_rent_to'] && '' !== $cart_item['wcrp_rental_products_return_days_threshold'] ) {

					$cart_item_data[] = array(
						'key'     => apply_filters( 'wcrp_rental_products_text_rent_from', get_option( 'wcrp_rental_products_text_rent_from' ) ),
						'value'   => date_i18n( wcrp_rental_products_rental_date_format(), strtotime( $cart_item['wcrp_rental_products_rent_from'] ) ),
						'display' => '',
					);

					$cart_item_data[] = array(
						'key'     => apply_filters( 'wcrp_rental_products_text_rent_to', get_option( 'wcrp_rental_products_text_rent_to' ) ),
						'value'   => date_i18n( wcrp_rental_products_rental_date_format(), strtotime( $cart_item['wcrp_rental_products_rent_to'] ) ),
						'display' => '',
					);

					if ( (int) $cart_item['wcrp_rental_products_return_days_threshold'] > 0 && 'yes' == get_option( 'wcrp_rental_products_return_days_display' ) ) { // Return days threshold only shown if greater than 0 and if return days display enabled, when this becomes an order it is also conditionally displayed/hidden via WCRP_Rental_Products_Order_Line_Items::order_line_item_formatted_meta_data()

						$cart_item_data[] = array(
							'key'     => apply_filters( 'wcrp_rental_products_text_rental_return_within', get_option( 'wcrp_rental_products_text_rental_return_within' ) ),
							'value'   => $cart_item['wcrp_rental_products_return_days_threshold'] . ' ' . esc_html__( 'days', 'wcrp-rental-products' ) . ' ' . esc_html__( '(', 'wcrp-rental-products' ) . date_i18n( wcrp_rental_products_rental_date_format(), strtotime( gmdate( 'Y-m-d', strtotime( $cart_item['wcrp_rental_products_rent_to'] . ' + ' . $cart_item['wcrp_rental_products_return_days_threshold'] . 'days' ) ) ) ) . esc_html__( ')', 'wcrp-rental-products' ), // Adds days and date to end of day number, same as order line items
							'display' => '',
						);

					}

					// In person pick up/return

					if ( isset( $cart_item['wcrp_rental_products_in_person_pick_up_return'] ) ) {

						if ( 'yes' == $cart_item['wcrp_rental_products_in_person_pick_up_return'] && ( isset( $cart_item['wcrp_rental_products_in_person_pick_up_time'] ) && isset( $cart_item['wcrp_rental_products_in_person_return_date'] ) && isset( $cart_item['wcrp_rental_products_in_person_return_time'] ) ) ) {

							if ( '' !== $cart_item['wcrp_rental_products_in_person_pick_up_time'] && '' !== $cart_item['wcrp_rental_products_in_person_return_date'] && '' !== $cart_item['wcrp_rental_products_in_person_return_time'] ) {

								$cart_item_data[] = array(
									'key'     => apply_filters( 'wcrp_rental_products_text_in_person_pick_up_return', get_option( 'wcrp_rental_products_text_in_person_pick_up_return' ) ),
									'value'   => ucfirst( $cart_item['wcrp_rental_products_in_person_pick_up_return'] ),
									'display' => '',
								);

								// Pick up date not included here as it's always the same as rent from date which is already displayed

								$cart_item_data[] = array(
									'key'     => apply_filters( 'wcrp_rental_products_text_pick_up_time', get_option( 'wcrp_rental_products_text_pick_up_time' ) ),
									'value'   => WCRP_Rental_Products_Misc::four_digit_time_formatted( $cart_item['wcrp_rental_products_in_person_pick_up_time'] ),
									'display' => '',
								);

								if ( $cart_item['wcrp_rental_products_in_person_return_date'] !== $cart_item['wcrp_rental_products_rent_to'] ) {

									// If the return date is different to the rent to date then display it so customer aware it's a different return date to the rent to date

									$cart_item_data[] = array(
										'key'     => apply_filters( 'wcrp_rental_products_text_return_date', get_option( 'wcrp_rental_products_text_return_date' ) ),
										'value'   => date_i18n( wcrp_rental_products_rental_date_format(), strtotime( $cart_item['wcrp_rental_products_in_person_return_date'] ) ),
										'display' => '',
									);

								}

								$cart_item_data[] = array(
									'key'     => apply_filters( 'wcrp_rental_products_text_return_time', get_option( 'wcrp_rental_products_text_return_time' ) ),
									'value'   => WCRP_Rental_Products_Misc::four_digit_time_formatted( $cart_item['wcrp_rental_products_in_person_return_time'] ),
									'display' => '',
								);

							}

						}

					}

				}

			}

			return $cart_item_data;

		}

		public function cart_item_row_class( $class, $values, $values_key ) {

			// Note this also adds the class on checkout too, it does not work for block cart/checkout as does not take the filter hook this function is on into account, and extremely difficult to implement at current time due to lack of extensibility

			if ( isset( $values['wcrp_rental_products_rent_from'] ) ) { // Any rental will have this, but importantly if a rental or purchase and the purchasable product is in cart it won't have this

				$class .= ' wcrp-rental-products-cart-item-is-rental '; // Spaces added around incase other extensions use this and lead on without spaces, would give it an incorrect class

			}

			return $class;

		}

		public function cart_item_rental_purchase_overrides() {

			$cart = WC()->cart;

			if ( !empty( $cart ) ) {

				if ( isset( $cart->cart_contents ) ) {

					if ( !empty( $cart->cart_contents ) ) {

						foreach ( $cart->cart_contents as $cart_content_key => $cart_content_value ) {

							if ( isset( $cart_content_value['wcrp_rental_products_cart_item_price'] ) ) {

								if ( wcrp_rental_products_is_rental_purchase( $cart_content_value['product_id'] ) ) { // We know it's a rental or purchase based rental due to this and condition above

									$rental_purchase_rental_tax_override = get_post_meta( $cart_content_value['product_id'], '_wcrp_rental_products_rental_purchase_rental_tax_override', true );
									$rental_purchase_rental_shipping_override = get_post_meta( $cart_content_value['product_id'], '_wcrp_rental_products_rental_purchase_rental_shipping_override', true );

									if ( 'yes' == $rental_purchase_rental_tax_override ) {

										$rental_purchase_rental_tax_override_status = get_post_meta( $cart_content_value['product_id'], '_wcrp_rental_products_rental_purchase_rental_tax_override_status', true );
										$rental_purchase_rental_tax_override_class = get_post_meta( $cart_content_value['product_id'], '_wcrp_rental_products_rental_purchase_rental_tax_override_class', true );

										$cart_content_value['data']->set_tax_status( $rental_purchase_rental_tax_override_status );
										$cart_content_value['data']->set_tax_class( $rental_purchase_rental_tax_override_class );

									}

									if ( 'yes' == $rental_purchase_rental_shipping_override ) {

										$rental_purchase_rental_shipping_override_class = get_post_meta( $cart_content_value['product_id'], '_wcrp_rental_products_rental_purchase_rental_shipping_override_class', true );

										$cart_content_value['data']->set_shipping_class_id( $rental_purchase_rental_shipping_override_class );

									}

								}

							}

						}

					}

				}

			}

		}

		public function cart_item_prices() {

			$cart = WC()->cart;

			if ( !empty( $cart ) ) {

				if ( isset( $cart->cart_contents ) ) {

					if ( !empty( $cart->cart_contents ) ) {

						foreach ( $cart->cart_contents as $cart_content_key => $cart_content_value ) {

							if ( isset( $cart_content_value['wcrp_rental_products_cart_item_price'] ) ) {

								if ( isset( $cart_content_value['product_id'] ) ) {

									$product_id = $cart_content_value['product_id']; // If a variation this is not the variation id, that would be $cart_content_value['variation_id']

								} else {

									$product_id = false;

								}

								$cart_item_price = (float) $cart_content_value['wcrp_rental_products_cart_item_price'];

								// If in person pick up/return add fees

								if ( isset( $cart_content_value['wcrp_rental_products_in_person_pick_up_return'] ) ) {

									if ( 'yes' == $cart_content_value['wcrp_rental_products_in_person_pick_up_return'] ) {

										$cart_item_price = $cart_item_price + (float) $cart_content_value['wcrp_rental_products_in_person_pick_up_fee'] + (float) $cart_content_value['wcrp_rental_products_in_person_return_fee'];

									}

								}

								// Tax

								$taxes_enabled = get_option( 'woocommerce_calc_taxes' );

								if ( 'yes' == $taxes_enabled ) {

									$tax_status = $cart_content_value['data']->get_tax_status(); // May have been overridden earlier via cart_item_rental_purchase_overrides()

									if ( 'taxable' == $tax_status ) {

										$prices_include_tax = get_option( 'woocommerce_prices_include_tax' );
										$tax_class = $cart_content_value['data']->get_tax_class(); // May have been overridden earlier via cart_item_rental_purchase_overrides()
										$tax_display_shop = get_option( 'woocommerce_tax_display_shop' );

										$taxes = WC_Tax::get_rates( $tax_class );

										if ( !empty( $taxes ) ) { // This ensures array_shift does not cause fatal error if empty, WooCommerce Tax extension can return this empty when the automated taxes option is enabled

											$tax_rates = array_shift( $taxes );
											$tax_rate = array_shift( $tax_rates );

											if ( 'no' == $prices_include_tax && 'incl' == $tax_display_shop ) {

												$cart_item_price = $cart_item_price / ( 1 + ( $tax_rate / 100 ) );

											} elseif ( 'yes' == $prices_include_tax && 'excl' == $tax_display_shop ) {

												$cart_item_price = $cart_item_price * ( 1 + ( $tax_rate / 100 ) );

											}

										}

									}

								}

								// Add-ons

								$addons_total_non_flat_fees = 0;
								$addons_total_flat_fees = 0;

								require_once ABSPATH . 'wp-admin/includes/plugin.php';

								if ( is_plugin_active( 'woocommerce-product-addons/woocommerce-product-addons.php' ) ) {

									if ( isset( $cart_content_value['addons'] ) ) {

										$addons = $cart_content_value['addons'];

										if ( !empty( $addons ) ) {

											foreach ( $addons as $addon ) {

												if ( isset( $addon['price'] ) && isset( $addon['price_type'] ) ) {

													if ( 'flat_fee' == $addon['price_type'] ) {

														// Flat fees don't get added to $addons_total_non_flat_fees, the total of all flat fees is already stored in $cart_content_value['addons_flat_fees_sum'] and this gets added to the price at the end after $addons_total_non_flat_fees is added on

														$addons_total_non_flat_fees = $addons_total_non_flat_fees; // This is just here to stop the empty if statement detected codesniff, we want to keep this else for the comment above

													} elseif ( 'quantity_based' == $addon['price_type'] ) {

														$addons_total_non_flat_fees = $addons_total_non_flat_fees + (float) $addon['price'];

													} elseif ( 'percentage_based' == $addon['price_type'] ) {

														$addons_total_non_flat_fees = $addons_total_non_flat_fees + ( $cart_item_price * ( (float) $addon['price'] / 100 ) );

													}

												}

											}

										}

									}

									if ( isset( $cart_content_value[ 'addons_flat_fees_sum' ] ) ) {

										$addons_total_flat_fees = (float) $cart_content_value[ 'addons_flat_fees_sum' ];

									}

									if ( 'yes' == get_post_meta( $product_id, '_wcrp_rental_products_multiply_addons_total_by_number_of_days_selected', true ) ) {

										if ( isset( $cart_content_value['wcrp_rental_products_rent_from'] ) && isset( $cart_content_value['wcrp_rental_products_rent_to'] ) ) {

											$addons_multiply_by_days = floor( ( strtotime( $cart_content_value['wcrp_rental_products_rent_to'] ) - strtotime( $cart_content_value['wcrp_rental_products_rent_from'] ) ) / ( 60 * 60 * 24 ) ) + 1;
											$addons_total_non_flat_fees = $addons_total_non_flat_fees * $addons_multiply_by_days;

											if ( true == apply_filters( 'wcrp_rental_products_multiply_addons_total_by_number_of_days_selected_flat_fees', true ) ) {

												$addons_total_flat_fees = $addons_total_flat_fees * $addons_multiply_by_days;

											}

										}

									}

									if ( isset( $cart_content_value['addons_flat_fees_sum'] ) ) {

										$price = $cart_item_price + $addons_total_non_flat_fees + $addons_total_flat_fees;

									} else {

										$price = $cart_item_price + $addons_total_non_flat_fees;

									}

								} else {

									$price = $cart_item_price;

								}

								// Set price, no check if !empty( $price ) as $price could be 0

								$price_to_set = apply_filters( 'wcrp_rental_products_cart_item_price', $price, $addons_total_non_flat_fees, $cart_item = $cart_content_value ); // Note that $addons_total_non_flat_fees does not include the total of flat fee add-ons, if needed this can be got from $cart_item['addons_flat_fees_sum']

								// All these have to be set or in some scenarios, like when a rental or purchase and then rental price is lower than the purchase price, otherwise in that scenario it would show a sale price with strikethrough on cart/checkout blocks experience, the classic cart/checkout experience did not have this issue as just showed the price no strikethrough or sale price

								$cart_content_value['data']->set_price( $price_to_set );
								$cart_content_value['data']->set_regular_price( $price_to_set );
								$cart_content_value['data']->set_sale_price( '' ); // No sale price for rentals

							}

						}

					}

				}

			}

		}

		public function cart_item_quantity( $quantity, $cart_item_key, $cart_item ) {

			if ( isset( $cart_item['wcrp_rental_products_advanced_pricing'] ) ) {

				if ( 'on' == $cart_item['wcrp_rental_products_advanced_pricing'] ) {

					$quantity = $cart_item['quantity']; // Product quantity input field changed to quantity text as quantity should not be changed at cart level in this scenario

				}

			}

			return $quantity;

		}

		public function cart_item_quantity_block_cart_checkout( $quantity, $product, $cart_item ) {

			if ( isset( $cart_item['wcrp_rental_products_advanced_pricing'] ) ) {

				if ( 'on' == $cart_item['wcrp_rental_products_advanced_pricing'] ) {

					$quantity = (int) $cart_item['quantity']; // Product quantity min/max set to the existing quantity as quantity should not be changed at cart level in this scenario

				}

			}

			return $quantity;

		}

		public function cart_items_flush_tax_rates_changes() {

			// If changed then carts are flushed, this is because existing rentals and security deposits in carts may now have incorrect prices/totals/tax calculations in several scenrios due to how the cart item price meta is stored/calculated in relation to tax, these would only get changed rarely and probably only during intial store setup, it's very unlikely a store would be changing these a lot, and if they did it is likely to be done during downtime as scheduled maintenance

			$this->cart_items_flush();

		}

		public function cart_items_flush_tax_settings_changes( $option ) {

			// Same comment as shown in cart_items_flush_tax_rates_changes() applies here too

			$flush_options = array(
				'woocommerce_prices_include_tax',
				'woocommerce_tax_based_on',
				'woocommerce_shipping_tax_class',
				'woocommerce_tax_round_at_subtotal',
				'woocommerce_tax_display_shop',
				'woocommerce_tax_display_cart',
			);

			if ( in_array( $option, $flush_options ) ) {

				$this->cart_items_flush();

			}

		}

		public function cart_items_flush() {

			global $wpdb;

			$wpdb->query( "TRUNCATE {$wpdb->prefix}woocommerce_sessions;" );

			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$wpdb->usermeta} WHERE meta_key = '_woocommerce_persistent_cart_%d';",
					get_current_blog_id()
				)
			);

			wp_cache_flush();

		}

	}

}
