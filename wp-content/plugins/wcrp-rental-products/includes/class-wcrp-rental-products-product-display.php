<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Product_Display' ) ) {

	class WCRP_Rental_Products_Product_Display {

		public function __construct() {

			add_filter( 'body_class', array( $this, 'body_classes' ) );
			add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'loop' ), PHP_INT_MAX, 2 );
			add_filter( 'woocommerce_blocks_product_grid_item_html', array( $this, 'block' ), PHP_INT_MAX, 3 );
			add_filter( 'woocommerce_get_price_html', array( $this, 'rental_price_html' ), PHP_INT_MAX, 2 );
			add_filter( 'woocommerce_admin_stock_html', array( $this, 'rental_stock_html' ), PHP_INT_MAX, 2 );

			if ( 'high' == get_option( 'wcrp_rental_products_rental_purchase_toggle_position' ) ) {

				add_action( 'woocommerce_single_product_summary', array( $this, 'rental_purchase_toggle' ), 11 ); // 11 so after woocommerce_template_single_price

			} else {

				add_action( 'woocommerce_single_product_summary', array( $this, 'rental_purchase_toggle' ), 31 ); // 31 so after woocommerce_template_single_add_to_cart

			}

			add_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'grouped_product_add_to_cart_text' ), PHP_INT_MAX, 2 );
			add_filter( 'woocommerce_grouped_product_list_column_quantity', array( $this, 'grouped_product_list_items_button' ), PHP_INT_MAX, 2 );
			add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'grouped_product_list_add_to_cart_before' ), 0 );
			add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'grouped_product_list_add_to_cart_after' ), 0 );

			add_filter( 'woocommerce_product_tabs', array( $this, 'rental_information_product_tab' ) );

		}

		public function body_classes( $classes ) {

			global $post;

			if ( !empty( $post ) ) {

				if ( is_product() ) {

					// Is rental / is rental only / is rental purchase body classes

					if ( wcrp_rental_products_is_rental_only( $post->ID ) || wcrp_rental_products_is_rental_purchase( $post->ID ) ) {

						$classes[] = 'wcrp-rental-products-is-rental';

					}

					if ( wcrp_rental_products_is_rental_only( $post->ID ) ) {

						$classes[] = 'wcrp-rental-products-is-rental-only';

					}

					if ( wcrp_rental_products_is_rental_purchase( $post->ID ) ) {

						$classes[] = 'wcrp-rental-products-is-rental-purchase';

						if ( isset( $_GET['rent'] ) ) {

							if ( '1' == sanitize_text_field( $_GET['rent'] ) ) {

								$classes[] = 'wcrp-rental-products-is-rental-purchase-rental';

							} else {

								$classes[] = 'wcrp-rental-products-is-rental-purchase-purchase';

							}

						} else {

							$classes[] = 'wcrp-rental-products-is-rental-purchase-purchase';

						}

					}

					// Rental form layout body class

					if ( wcrp_rental_products_is_rental_only( $post->ID ) || wcrp_rental_products_is_rental_purchase( $post->ID ) ) {

						$rental_form_layout_class = str_replace( '_', '-', get_option( 'wcrp_rental_products_rental_form_layout' ) );

						if ( 'light-boxed' == $rental_form_layout_class ) {

							$classes[] = 'wcrp-rental-products-rental-form-layout-light';
							$classes[] = 'wcrp-rental-products-rental-form-layout-boxed';

						} elseif ( 'dark-boxed' == $rental_form_layout_class ) {

							$classes[] = 'wcrp-rental-products-rental-form-layout-dark';
							$classes[] = 'wcrp-rental-products-rental-form-layout-boxed';

						} else {

							$classes[] = 'wcrp-rental-products-rental-form-layout-' . esc_html( $rental_form_layout_class );

						}

					}

				}

			}

			return $classes;

		}

		public function loop( $html, $product ) {

			$availability_checker_mode = get_option( 'wcrp_rental_products_availability_checker_mode' );
			$availability_checker_status = ( 'ajax' == $availability_checker_mode ? WCRP_Rental_Products_Availability_Checker::status_ajax_placeholder( $product ) : WCRP_Rental_Products_Availability_Checker::status( $product ) );
			$product_id = $product->get_id();
			$product_type = $product->get_type();

			if ( 'grouped' == $product_type ) {

				$html .= $availability_checker_status;

			} else {

				if ( wcrp_rental_products_is_rental_only( $product_id ) ) {

					$html = ''; // Note the removal of existing html here instead of concatenation
					$html .= $this->select_dates_button_html( 'loop', $product );

					if ( 'yes' == get_option( 'wcrp_rental_products_availability_checker_status_rental' ) ) {

						$html .= $availability_checker_status;

					}

				} elseif ( wcrp_rental_products_is_rental_purchase( $product_id ) ) {

					if ( 'yes' == get_option( 'wcrp_rental_products_rental_purchase_toggle_loops_blocks_display' ) ) {

						$toggle_type_class = '';

						if ( 'button' == get_option( 'wcrp_rental_products_rental_purchase_toggle_type' ) ) {

							$toggle_type_class = 'class="button ' . wc_wp_theme_get_element_class_name( 'button' ) . '"';

						}

						$html .= '<div class="wcrp-rental-products-rental-purchase-toggle"><a href="' . esc_html( add_query_arg( 'rent', '1', get_permalink( $product_id ) ) ) . '"' . wp_kses_post( $toggle_type_class ) . '>' . apply_filters( 'wcrp_rental_products_text_view_rental_options', get_option( 'wcrp_rental_products_text_view_rental_options' ) ) . '</a></div>';

					}

					if ( 'yes' == get_option( 'wcrp_rental_products_availability_checker_status_rental_purchase' ) ) {

						$html .= $availability_checker_status;

					}

				}

			}

			return $html;

		}

		public function block( $block, $data, $product ) {

			$availability_checker_mode = get_option( 'wcrp_rental_products_availability_checker_mode' );
			$availability_checker_status = ( 'ajax' == $availability_checker_mode ? WCRP_Rental_Products_Availability_Checker::status_ajax_placeholder( $product ) : WCRP_Rental_Products_Availability_Checker::status( $product ) );
			$block_wrapper_class = 'wp-block-button wc-block-grid__product-add-to-cart';
			$product_id = $product->get_id();
			$product_type = $product->get_type();

			if ( 'grouped' == $product_type ) {

				$block = preg_replace( '/<div class="' . preg_quote( $block_wrapper_class ) . '">(.+?)<\/div>/s', '<div class="' . $block_wrapper_class . '">$1</div>' . $availability_checker_status, $block );

			} else {

				if ( wcrp_rental_products_is_rental_only( $product_id ) ) {

					$block = preg_replace( '/<div class="' . preg_quote( $block_wrapper_class ) . '">(.+?)<\/div>/s', '<div class="' . $block_wrapper_class . '">' . $this->select_dates_button_html( 'block', $product ) . ( 'yes' == get_option( 'wcrp_rental_products_availability_checker_status_rental' ) ? $availability_checker_status : '' ) . '</div>', $block );

				} elseif ( wcrp_rental_products_is_rental_purchase( $product_id ) ) {

					$toggle_type_class = '';

					if ( 'button' == get_option( 'wcrp_rental_products_rental_purchase_toggle_type' ) ) {

						$toggle_type_class = 'class="button ' . wc_wp_theme_get_element_class_name( 'button' ) . '"';

					}

					$block = preg_replace( '/<div class="' . preg_quote( $block_wrapper_class ) . '">(.+?)<\/div>/s', '<div class="' . $block_wrapper_class . '">$1</div>' . ( 'yes' == get_option( 'wcrp_rental_products_rental_purchase_toggle_loops_blocks_display' ) ? '<div class="wcrp-rental-products-rental-purchase-toggle"><a href="' . esc_html( add_query_arg( 'rent', '1', get_permalink( $product_id ) ) ) . '"' . wp_kses_post( $toggle_type_class ) . '>' . apply_filters( 'wcrp_rental_products_text_view_rental_options', get_option( 'wcrp_rental_products_text_view_rental_options' ) ) . '</a></div>' : '' ) . ( 'yes' == get_option( 'wcrp_rental_products_availability_checker_status_rental_purchase' ) ? $availability_checker_status : '' ), $block );

				}

			}

			return $block;

		}

		public function select_dates_button_html( $button_type, $product ) {

			$html = '';

			if ( !empty( $button_type ) && !empty( $product ) ) {

				$text = esc_html( apply_filters( 'wcrp_rental_products_text_select_dates', get_option( 'wcrp_rental_products_text_select_dates' ) ) );
				$aria_label = $text . ' ' . esc_html__( 'for', 'wcrp-rental-products' ) . ' ' . $product->get_title();
				$class = 'wcrp-rental-products-select-dates-button';

				if ( 'block' == $button_type ) {

					$class .= ' button wp-block-button__link ' . wc_wp_theme_get_element_class_name( 'button' );

				} else {

					$class .= ' button product_type_' . $product->get_type() . ' ' . wc_wp_theme_get_element_class_name( 'button' );

				}

				if ( 'block' == $button_type ) {

					$html .= '<div class="wp-block-button wc-block-grid__product-add-to-cart">';

				}

				$html .= '<a href="' . $product->get_permalink() . '" class="' . $class . '" aria-label="' . $aria_label . '">' . $text . '</a>';

				if ( 'block' == $button_type ) {

					$html .= '</div>';

				}

				$html = apply_filters( 'wcrp_rental_products_select_dates_button_html', $html );

			}

			return $html;

		}

		public function rental_price_html( $price, $product ) {

			$product_id = $product->get_id();
			$product_price = wc_get_price_to_display( $product ); // Inc or exc vat depending on settings, this is the price in format 0.00 regardless of decimal separator setting
			$product_type = $product->get_type();

			if ( !empty( $product_id ) && !empty( $product_price ) ) {

				$display_price = 'public';

				if ( is_admin() ) {

					if ( !wp_doing_ajax() ) {

						// If it is the admin dashboard this !wp_doing_ajax condition exists because we found frontend AJAX calls (e.g. which can be used by some form of quick view functionality from a theme) trigger is_admin() to be true as they are admin-ajax based, these AJAX calls need to return the public price as used for the frontend, this therefore considers any admin-ajax based call return the public price due to this scenario

						$display_price = 'admin';

					} else {

						// If it is the admin dashboard and admin-ajax call we ensure the admin price is used for the scenarios we know of, e.g. when using products list quick edit and editing the price there is an admin-ajax call to show the updated price

						if ( isset( $_POST['woocommerce_quick_edit_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['woocommerce_quick_edit_nonce'] ), 'woocommerce_quick_edit_nonce' ) ) {

							if ( isset( $_POST['woocommerce_quick_edit'] ) ) {

								if ( '1' == $_POST['woocommerce_quick_edit'] ) {

									$display_price = 'admin';

								}

							}

						}

					}

				}

				if ( 'public' == $display_price ) {

					if ( wcrp_rental_products_is_rental_only( $product_id ) || wcrp_rental_products_is_rental_purchase( $product_id ) ) {

						$do_rental_price_amends = false;

						$default_rental_options = wcrp_rental_products_default_rental_options();

						$pricing_type = get_post_meta( $product_id, '_wcrp_rental_products_pricing_type', true );
						$pricing_type = ( '' !== $pricing_type ? $pricing_type : $default_rental_options['_wcrp_rental_products_pricing_type'] );

						$pricing_period = get_post_meta( $product_id, '_wcrp_rental_products_pricing_period', true );
						$pricing_period = (int) ( '' !== $pricing_period ? $pricing_period : $default_rental_options['_wcrp_rental_products_pricing_period'] );

						$pricing_period_multiples = get_post_meta( $product_id, '_wcrp_rental_products_pricing_period_multiples', true );
						$pricing_period_multiples = ( '' !== $pricing_period_multiples ? $pricing_period_multiples : $default_rental_options['_wcrp_rental_products_pricing_period_multiples'] );

						$pricing_tiers = get_post_meta( $product_id, '_wcrp_rental_products_pricing_tiers', true );
						$pricing_tiers = ( '' !== $pricing_tiers ? $pricing_tiers : $default_rental_options['_wcrp_rental_products_pricing_tiers'] );

						$price_additional_periods_percent = get_post_meta( $product_id, '_wcrp_rental_products_price_additional_periods_percent', true );
						$price_additional_periods_percent = ( '' !== $price_additional_periods_percent ? $price_additional_periods_percent : $default_rental_options['_wcrp_rental_products_price_additional_periods_percent'] );

						$price_additional_period_percent = get_post_meta( $product_id, '_wcrp_rental_products_price_additional_period_percent', true );
						$price_additional_period_percent = (float) ( '' !== $price_additional_period_percent ? $price_additional_period_percent : $default_rental_options['_wcrp_rental_products_price_additional_period_percent'] );

						$price_display_override = get_post_meta( $product_id, '_wcrp_rental_products_price_display_override', true );
						$price_display_override = ( '' !== $price_display_override ? $price_display_override : $default_rental_options['_wcrp_rental_products_price_display_override'] );
						$price_display_override_prefix_suffix = get_option( 'wcrp_rental_products_rental_price_display_override_prefix_suffix' );

						$rental_price_display_prefix = wp_kses_post( apply_filters( 'wcrp_rental_products_rental_price_display_prefix', get_option( 'wcrp_rental_products_rental_price_display_prefix' ) . ' ' ) );
						$rental_price_display_suffix = wp_kses_post( apply_filters( 'wcrp_rental_products_rental_price_display_suffix', ' ' . get_option( 'wcrp_rental_products_rental_price_display_suffix' ) ) );
						$rental_price_display_rent_text = get_option( 'wcrp_rental_products_rental_price_display_rent_text' );

						$minimum_days = get_post_meta( $product_id, '_wcrp_rental_products_minimum_days', true );
						$minimum_days = (int) ( '' !== $minimum_days ? $minimum_days : $default_rental_options['_wcrp_rental_products_minimum_days'] );

						$rental_purchase_rental_tax_override = get_post_meta( $product_id, '_wcrp_rental_products_rental_purchase_rental_tax_override', true );

						if ( 'variable' == $product_type ) {

							$from_for = apply_filters( 'wcrp_rental_products_text_rent_from', get_option( 'wcrp_rental_products_text_rent_from' ) );

						} else {

							$from_for = apply_filters( 'wcrp_rental_products_text_rent_for', get_option( 'wcrp_rental_products_text_rent_for' ) );

						}

						if ( wcrp_rental_products_is_rental_purchase( $product_id ) ) {

							if ( isset( $_GET['rent'] ) ) {

								if ( '1' == $_GET['rent'] ) {

									if ( 'yes' == $rental_purchase_rental_tax_override ) {

										// This has to be done as wc_product_and_variants() don't seem to effect $product in this context, without this there are scenarios where the price display can be wrong, e.g. if the purchasable product has a stock status of taxable and the tax class is standard, if the override tax status is reduced rate then wc_get_price_to_display() uses the standard rate to display the price, so would be incorrect

										$tax_status_override = get_post_meta( $product_id, '_wcrp_rental_products_rental_purchase_rental_tax_override_status', true );
										$product->set_tax_status( $tax_status_override );

										$tax_class_override = get_post_meta( $product_id, '_wcrp_rental_products_rental_purchase_rental_tax_override_class', true );
										$product->set_tax_class( $tax_class_override );

									}

									if ( 'variable' == $product_type ) {

										$variations = $product->get_children();

										if ( !empty( $variations ) ) {

											$variation_prices = array();

											foreach ( $variations as $variation ) {

												if ( '' !== get_post_meta( $variation, '_wcrp_rental_products_rental_purchase_price', true ) ) {

													// str_replace happens incase the store uses a different decimal separator and this is the _wcrp_rental_products_rental_purchase_price field, unlike normal price fields which even though entered as 1,00 get saved as 1.00, this field saves using the decimal separator in the value, so we have to set it to the . character, otherwise the calculations later on would not work correctly, do not need to worry about the thousand separator as this cannot be entered on the field itself and is only used during display

													$variation_prices[] = str_replace( wc_get_price_decimal_separator(), '.', get_post_meta( $variation, '_wcrp_rental_products_rental_purchase_price', true ) );

												}

											}

											if ( !empty( $variation_prices ) ) {

												$product_price = wc_get_price_to_display( $product, array( 'price' => min( $variation_prices ) ) );

											} else {

												return ''; // If no prices set don't display

											}

										}

									} else {

										// str_replace happens incase the store uses a different decimal separator and this is the _wcrp_rental_products_rental_purchase_price field, unlike normal price fields which even though entered as 1,00 get saved as 1.00, this field saves using the decimal separator in the value, so we have to set it to the . character, otherwise the calculations later on would not work correctly. Do not need to worry about the thousand separator as this cannot be entered on the field itself and is only used during display

										$product_price = wc_get_price_to_display( $product, array( 'price' => str_replace( wc_get_price_decimal_separator(), '.', get_post_meta( $product_id, '_wcrp_rental_products_rental_purchase_price', true ) ) ) );

									}

									$do_rental_price_amends = true;

								}

							}

						} elseif ( wcrp_rental_products_is_rental_only( $product_id ) ) {

							$do_rental_price_amends = true;

						}

						if ( true == $do_rental_price_amends ) {

							$product_price = (float) $product_price; // Set as float here as used in calculations next, not done earlier as could be empty and wouldn't meet empty conditions

							if ( 'fixed' == $pricing_type ) {

								if ( 'yes' == $pricing_tiers ) {

									$from_for = apply_filters( 'wcrp_rental_products_text_rent_from', get_option( 'wcrp_rental_products_text_rent_from' ) );

								}

							} elseif ( 'period' == $pricing_type ) {

								if ( $pricing_period > 1 ) {

									if ( 'no' == $pricing_period_multiples ) {

										$from_for = apply_filters( 'wcrp_rental_products_text_rent_for', get_option( 'wcrp_rental_products_text_rent_for' ) );

									} else {

										$from_for = apply_filters( 'wcrp_rental_products_text_rent_from', get_option( 'wcrp_rental_products_text_rent_from' ) );

									}

									$product_price = ( $product_price * $minimum_days ) / $pricing_period;

								} else {

									$from_for = apply_filters( 'wcrp_rental_products_text_rent_from', get_option( 'wcrp_rental_products_text_rent_from' ) );

									if ( 'yes' == $price_additional_periods_percent && $price_additional_period_percent > 0 && $minimum_days > 1 ) {

										$product_price = $product_price + ( $product_price * $price_additional_period_percent / 100 );

									} else {

										$product_price = $product_price * $minimum_days;

									}

								}

							} elseif ( 'period_selection' == $pricing_type ) {

								$from_for = apply_filters( 'wcrp_rental_products_text_rent_from', get_option( 'wcrp_rental_products_text_rent_from' ) );

							}

							// Prefix/suffix string substitutions

							$rental_price_display_prefix = str_replace( '{rental_price_including_tax}', wc_price( wc_get_price_including_tax( $product, array( 'qty' => 1, 'price' => $product_price ) ) ), $rental_price_display_prefix );
							$rental_price_display_prefix = str_replace( '{rental_price_excluding_tax}', wc_price( wc_get_price_excluding_tax( $product, array( 'qty' => 1, 'price' => $product_price ) ) ), $rental_price_display_prefix );
							$rental_price_display_suffix = str_replace( '{rental_price_including_tax}', wc_price( wc_get_price_including_tax( $product, array( 'qty' => 1, 'price' => $product_price ) ) ), $rental_price_display_suffix );
							$rental_price_display_suffix = str_replace( '{rental_price_excluding_tax}', wc_price( wc_get_price_excluding_tax( $product, array( 'qty' => 1, 'price' => $product_price ) ) ), $rental_price_display_suffix );

							// Rental price display override

							if ( '' !== $price_display_override ) {

								if ( 'yes' == $price_display_override_prefix_suffix ) {

									$price = apply_filters( 'wcrp_rental_products_rental_price_html', $rental_price_display_prefix . $price_display_override . $rental_price_display_suffix, $product_price, $product ); // Note this filter only applies to the frontend

								} else {

									$price = apply_filters( 'wcrp_rental_products_rental_price_html', $price_display_override, $product_price, $product ); // Note this filter only applies to the frontend

								}

							} else { // No rental price display override

								$rental_price_html = $rental_price_display_prefix . ( 'yes' == $rental_price_display_rent_text ? $from_for . ' ' : '' ) . wc_price( $product_price ) . $rental_price_display_suffix; // str_replace sets the decimal separator back to the display option as was changed earlier for the calculations to work if separator changed

								$price = apply_filters( 'wcrp_rental_products_rental_price_html', $rental_price_html, $product_price, $product ); // Note this filter only applies to the frontend

							}

						}

					}

				} elseif ( 'admin' == $display_price ) {

					// Below does not run through the wcrp_rental_products_rental_price_html filter hook as only for frontend display

					$admin_stock_price_display_start = '<div class="wcrp-rental-products-stock-price-display"><small>';
					$admin_stock_price_display_end = '</small></div>';

					// Rental price HTML

					if ( wcrp_rental_products_is_rental_only( $product_id ) ) {

						$price = $admin_stock_price_display_start . esc_html__( 'Purchase', 'wcrp-rental-products' ) . '<br><strong>' . esc_html__( 'No', 'wcrp-rental-products' ) . '</strong><hr>' . esc_html__( 'Rental', 'wcrp-rental-products' ) . '<br><strong>' . $price . '</strong>' . $admin_stock_price_display_end;

					} elseif ( wcrp_rental_products_is_rental_purchase( $product_id ) ) {

						$rental_purchase_price = get_post_meta( $product_id, '_wcrp_rental_products_rental_purchase_price', true );
						$rental_purchase_price = ( '' !== $rental_purchase_price ? wc_price( $rental_purchase_price ) : esc_html__( 'Not set', 'wcrp-rental-products' ) );

						$price = $admin_stock_price_display_start . esc_html__( 'Purchase', 'wcrp-rental-products' ) . '<br><strong>' . $price . '</strong><hr>' . esc_html__( 'Rental', 'wcrp-rental-products' ) . '<br><strong>' . ( 'simple' == $product_type ? $rental_purchase_price : '<a href="' . get_edit_post_link( $product_id ) . '" title="' . esc_html__( 'Rental price is displayed on each variation', 'wcrp-rental-products' ) . '">' . esc_html__( 'Variations', 'wcrp-rental-products' ) . '</a>' ) . '</strong>' . $admin_stock_price_display_end;

					} else {

						$price = $admin_stock_price_display_start . esc_html__( 'Purchase', 'wcrp-rental-products' ) . '<br><strong>' . $price . '</strong><hr>' . esc_html__( 'Rental', 'wcrp-rental-products' ) . '<br><strong>' . esc_html__( 'No', 'wcrp-rental-products' ) . '</strong>' . $admin_stock_price_display_end;

					}

				}

			}

			return $price;

		}

		public function rental_stock_html( $stock_html, $product ) {

			// This function does not need any is_admin() or admin-ajax conditions like rental_price_html() as it is hooked via woocommerce_admin_stock_html filter hook which is only used for the admin dashboard

			$product_id = $product->get_id();
			$product_type = $product->get_type();

			$admin_stock_price_display_start = '<div class="wcrp-rental-products-stock-price-display"><small>';
			$admin_stock_price_display_end = '</small></div>';

			if ( wcrp_rental_products_is_rental_only( $product_id ) || wcrp_rental_products_is_rental_purchase( $product_id ) ) {

				$rental_stock = get_post_meta( $product->get_id(), '_wcrp_rental_products_rental_stock', true );

				if ( 'variable' !== $product_type ) {

					if ( (int) $rental_stock > 0 || '' == $rental_stock ) {

						$rental_stock_display = '<mark class="instock">' . esc_html__( 'In stock', 'wcrp-rental-products' ) . '</mark> ' . esc_html__( '(', 'wcrp-rental-products' ) . ( '' == $rental_stock ? '<span title="' . esc_html__( 'Unlimited', 'wcrp-rental-products' ) . '">&#8734;</span>' : $rental_stock ) . esc_html__( ')', 'wcrp-rental-products' ); // WooCommerce textdomain gets used as core WooCommerce string

					} else {

						$rental_stock_display = '<mark class="outofstock">' . esc_html__( 'Out of stock', 'woocommerce' ) . '</mark> ' . esc_html__( '(', 'wcrp-rental-products' ) . $rental_stock . esc_html__( ')', 'wcrp-rental-products' ); // WooCommerce textdomain gets used as core WooCommerce string

					}

				} else {

					// translators: %s: edit post link

					$rental_stock_display = sprintf( wp_kses_post( '<a href="%s" title="' . esc_html__( 'Rental stock is displayed on each variation', 'wcrp-rental-products' ) . '">' . esc_html__( 'Variations', 'wcrp-rental-products' ) . '</a>', 'wcrp-rental-products' ), get_edit_post_link( $product_id ) );

				}

				if ( true == wcrp_rental_products_is_rental_only( $product_id ) ) {

					$stock_html = $admin_stock_price_display_start . esc_html__( 'Purchase', 'wcrp-rental-products' ) . '<br><strong>' . esc_html__( 'No', 'wcrp-rental-products' ) . '</strong><hr>' . esc_html__( 'Rental', 'wcrp-rental-products' ) . '<br><strong>' . $rental_stock_display . '</strong>' . $admin_stock_price_display_end;

				} elseif ( true == wcrp_rental_products_is_rental_purchase( $product_id ) ) {

					$stock_html = $admin_stock_price_display_start . esc_html__( 'Purchase', 'wcrp-rental-products' ) . '<br><strong>' . $stock_html . '</strong><hr>' . esc_html__( 'Rental', 'wcrp-rental-products' ) . '<br><strong>' . $rental_stock_display . '</strong>' . $admin_stock_price_display_end;

				}

			} else {

				$stock_html = $admin_stock_price_display_start . esc_html__( 'Purchase', 'wcrp-rental-products' ) . '<br><strong>' . $stock_html . '</strong><hr>' . esc_html__( 'Rental', 'wcrp-rental-products' ) . '<br><strong>' . esc_html__( 'No', 'wcrp-rental-products' ) . '</strong>' . $admin_stock_price_display_end;

			}

			return $stock_html;

		}

		public static function rental_purchase_toggle() {

			global $post;

			if ( !empty( $post ) ) {

				$product = wc_get_product( $post );

				if ( !empty( $product ) ) {

					$product_id = $product->get_id();
					$product_type = $product->get_type();

					if ( 'simple' == $product_type || 'variable' == $product_type ) {

						if ( wcrp_rental_products_is_rental_purchase( $product_id ) ) {

							$toggle_type_class = '';

							if ( 'button' == get_option( 'wcrp_rental_products_rental_purchase_toggle_type' ) ) {

								$toggle_type_class = 'class="button ' . wc_wp_theme_get_element_class_name( 'button' ) . '"';

							}

							if ( !isset( $_GET['rent'] ) ) {

								echo '<div class="wcrp-rental-products-rental-purchase-toggle"><a href="' . esc_html( add_query_arg( 'rent', '1' ) ) . '"' . wp_kses_post( $toggle_type_class ) . '>' . esc_html( apply_filters( 'wcrp_rental_products_text_view_rental_options', get_option( 'wcrp_rental_products_text_view_rental_options' ) ) ) . '</a></div>';

							} else {

								if ( '1' == $_GET['rent'] ) {

									echo '<div class="wcrp-rental-products-rental-purchase-toggle"><a href="' . esc_html( remove_query_arg( 'rent' ) ) . '"' . wp_kses_post( $toggle_type_class ) . '>' . esc_html( apply_filters( 'wcrp_rental_products_text_view_purchase_options', get_option( 'wcrp_rental_products_text_view_purchase_options' ) ) ) . '</a></div>';

								}

							}

						}

					}

				}

			}

		}

		public function grouped_product_add_to_cart_text( $text, $product ) {

			if ( !empty( $product ) ) {

				$product_type = $product->get_type();

				if ( 'grouped' == $product_type ) {

					if ( true == $this->grouped_product_check_all_products_rental_only( $product ) ) {

						// Set the add to cart text for the grouped product if only contains rental only products (rental or purchase is not conditioned here as the default state for these is purchase not rental so the CTA here would not be select dates)

						$text = esc_html( apply_filters( 'wcrp_rental_products_text_select_dates', get_option( 'wcrp_rental_products_text_select_dates' ) ) );

					}

				}

			}

			return $text;

		}

		public function grouped_product_list_items_button( $value, $grouped_product_child ) {

			if ( wcrp_rental_products_is_rental_only( $grouped_product_child->get_id() ) ) {

				$value = $this->loop( '', $grouped_product_child );

			}

			return $value;

		}

		public function grouped_product_list_add_to_cart_before() {

			$this->grouped_product_list_add_to_cart_wrapper( 'before' );

		}

		public function grouped_product_list_add_to_cart_after() {

			$this->grouped_product_list_add_to_cart_wrapper( 'after' );

		}

		public function grouped_product_list_add_to_cart_wrapper( $before_after ) {

			if ( !empty( $before_after ) ) {

				if ( 'before' == $before_after  || 'after' == $before_after ) {

					global $post;
					$product = wc_get_product( $post->ID );

					if ( !empty( $product ) ) {

						$product_type = $product->get_type();

						if ( 'grouped' == $product_type ) {

							// Hides the add to cart button for the grouped product if only contains rental only products (rental or purchase is not conditioned here as the default state for these is purchase not rental so these can be added to cart in this scenario)

							if ( true == $this->grouped_product_check_all_products_rental_only( $product ) ) {

								if ( 'before' == $before_after ) {

									echo '<div style="display: none;">';

								} elseif ( 'after' == $before_after ) {

									echo '</div>';

								}

							}

						}

					}

				}

			}

		}

		public static function grouped_product_check_all_products_rental_only( $product ) {

			$return = false;

			if ( !empty( $product ) ) {

				$product_type = $product->get_type();

				if ( 'grouped' == $product_type ) { // Should already be checked in calling function but just here for completeness

					$children = $product->get_children();
					$total_children = count( $children );
					$total_rental_children = 0;

					foreach ( $children as $child ) {

						if ( wcrp_rental_products_is_rental_only( $child ) ) {

							++$total_rental_children;

						}

					}

					if ( $total_children == $total_rental_children ) {

						$return = true;

					}

				}

			}

			return $return;

		}

		public function rental_information_product_tab( $tabs ) {

			global $post;
			$product_id = $post->ID;

			if ( wcrp_rental_products_is_rental_only( $product_id ) || wcrp_rental_products_is_rental_purchase( $product_id ) ) {

				$rental_information = get_option( 'wcrp_rental_products_rental_information' );
				$rental_information_product = get_post_meta( $product_id, '_wcrp_rental_products_rental_information', true );
				$display_rental_information_tab = false;
				$rental_information_title = apply_filters( 'wcrp_rental_products_rental_information_title', get_option( 'wcrp_rental_products_rental_information_title' ) );

				if ( '' !== $rental_information_product ) {

					$display_rental_information_tab = true;

				} else {

					if ( '' !== $rental_information ) {

						$display_rental_information_tab = true;

					}

				}

				if ( wcrp_rental_products_is_rental_purchase( $product_id ) ) {

					if ( !isset( $_GET['rent'] ) ) {

						$display_rental_information_tab = false;

					}

				}

				if ( true == $display_rental_information_tab ) {

					$tabs['wcrp_rental_products_rental_information'] = array(
						'title' 	=> esc_html( $rental_information_title ),
						'priority' 	=> 20,
						'callback' 	=> array( $this, 'rental_information_product_tab_content' ),
					);

				}

			}


			return $tabs;

		}

		public function rental_information_product_tab_content() {

			global $post;
			$product_id = $post->ID;
			$rental_information_title = apply_filters( 'wcrp_rental_products_rental_information_title', get_option( 'wcrp_rental_products_rental_information_title' ) );
			$rental_information_heading = get_option( 'wcrp_rental_products_rental_information_heading' );

			if ( 'yes' == $rental_information_heading ) {

				echo '<h2>' . esc_html( $rental_information_title ) . '</h2>';

			}

			echo wp_kses_post( wpautop( get_option( 'wcrp_rental_products_rental_information' ) ) );
			echo wp_kses_post( wpautop( get_post_meta( $product_id, '_wcrp_rental_products_rental_information', true ) ) );

		}

	}

}
