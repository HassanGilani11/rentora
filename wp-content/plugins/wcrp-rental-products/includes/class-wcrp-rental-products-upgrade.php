<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Upgrade' ) ) {

	class WCRP_Rental_Products_Upgrade {

		public function __construct() {

			add_action( 'wp_loaded', array( $this, 'upgrade' ) );

		}

		public static function upgrade() {

			$version = get_option( 'wcrp_rental_products_version' );

			if ( WCRP_RENTAL_PRODUCTS_VERSION !== $version ) {

				global $wpdb;

				if ( version_compare( $version, '1.0.0', '<' ) ) {

					if ( get_option( 'wcrp_rental_products_date_format' ) === false ) {

						update_option( 'wcrp_rental_products_date_format', 'mm/dd/yy' );

					}

					if ( get_option( 'wcrp_rental_products_start_days_threshold' ) === false ) {

						update_option( 'wcrp_rental_products_start_days_threshold', '3' );

					}

					if ( get_option( 'wcrp_rental_products_return_days_threshold' ) === false ) {

						update_option( 'wcrp_rental_products_return_days_threshold', '3' );

					}

				}

				if ( version_compare( $version, '2.0.1', '<' ) ) {

					$wpdb->query("
						CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wcrp_rental_products_rentals (
						rental_id bigint(20) AUTO_INCREMENT,
						reserved_date date NOT NULL default '0000-00-00',
						order_id bigint(20) NOT NULL,
						order_item_id bigint(20) NOT NULL,
						product_id bigint(20) NOT NULL,
						quantity bigint(20) NOT NULL,
						PRIMARY KEY (rental_id)
					)");

					$wpdb->query( "DELETE FROM {$wpdb->prefix}postmeta WHERE `meta_key` = '_wcrp_rental_products_rental_status';" );
					$wpdb->query( "DELETE FROM {$wpdb->prefix}postmeta WHERE `meta_key` = '_wcrp_rental_products_start_days_threshold';" );
					$wpdb->query( "DELETE FROM {$wpdb->prefix}postmeta WHERE `meta_key` = '_wcrp_rental_products_return_days_threshold';" );

				}

				if ( version_compare( $version, '2.0.2', '<' ) ) {

					delete_option( 'wcrp_rental_products_date_format' );

					if ( get_option( 'wcrp_rental_products_minimum_days' ) === false ) {

						update_option( 'wcrp_rental_products_minimum_days', '0' );

					}

					if ( get_option( 'wcrp_rental_products_maximum_days' ) === false ) {

						update_option( 'wcrp_rental_products_maximum_days', '0' );

					}

					if ( get_option( 'wcrp_rental_products_months' ) === false ) {

						update_option( 'wcrp_rental_products_months', '1' );

					}

					if ( get_option( 'wcrp_rental_products_columns' ) === false ) {

						update_option( 'wcrp_rental_products_columns', '1' );

					}

					if ( get_option( 'wcrp_rental_products_inline' ) === false ) {

						update_option( 'wcrp_rental_products_inline', 'no' );

					}

					self::woocommerce_sessions_carts_clear();

					$order_item_rent_dates = $wpdb->get_results(
						"SELECT * FROM `{$wpdb->prefix}woocommerce_order_itemmeta` WHERE `meta_key` IN( 'wcrp_rental_products_rent_from', 'wcrp_rental_products_rent_to' );"
					);

					if ( !empty( $order_item_rent_dates ) ) {

						foreach ( $order_item_rent_dates as $order_item_rent_date ) {

							if ( !empty( $order_item_rent_date->meta_value ) ) {

								$new_value = gmdate( 'Y-m-d', strtotime( $order_item_rent_date->meta_value ) );

								if ( !empty( $new_value ) ) {

									$wpdb->query(
										$wpdb->prepare(
											"UPDATE `{$wpdb->prefix}woocommerce_order_itemmeta` SET `meta_value` = %s WHERE `order_item_id` = %d AND `meta_key` = %s;",
											$new_value,
											$order_item_rent_date->order_item_id,
											$order_item_rent_date->meta_key
										)
									);

								}

							}

						}

					}

					$order_item_return_day_thresholds = $wpdb->get_results(
						"SELECT * FROM `{$wpdb->prefix}woocommerce_order_itemmeta` WHERE `meta_key` = 'wcrp_rental_products_return_days_threshold' AND `meta_value` LIKE '% %';"
					);

					if ( !empty( $order_item_return_day_thresholds ) ) {

						foreach ( $order_item_return_day_thresholds as $order_item_return_day_threshold ) {

							$new_value = explode( ' ', $order_item_return_day_threshold->meta_value )[0];

							if ( !empty( $new_value ) ) {

								$wpdb->query(
									$wpdb->prepare(
										"UPDATE `{$wpdb->prefix}woocommerce_order_itemmeta` SET `meta_value` = %s WHERE `order_item_id` = %d AND `meta_key` = %s;",
										$new_value,
										$order_item_return_day_threshold->order_item_id,
										$order_item_return_day_threshold->meta_key
									)
								);

							}

						}

					}

				}

				if ( version_compare( $version, '2.1.0', '<' ) ) {

					$rental_products = $wpdb->get_results( "SELECT post_id FROM `{$wpdb->prefix}postmeta` WHERE `meta_key` = '_wcrp_rental_products_rental' AND meta_value IN ( 'yes', 'yes_purchase' );" );

					if ( !empty( $rental_products ) ) {

						foreach ( $rental_products as $rental_product ) {

							update_post_meta( $rental_product->post_id, '_manage_stock', 'no' );
							update_post_meta( $rental_product->post_id, '_stock_status', 'instock' );
							update_post_meta( $rental_product->post_id, '_backorders', 'no' );

						}

					}

				}

				if ( version_compare( $version, '2.1.1', '<' ) ) {

					if ( get_option( 'wcrp_rental_products_non_day_price_display' ) === false ) {

						update_option( 'wcrp_rental_products_non_day_price_display', 'no' );

					}

				}

				if ( version_compare( $version, '2.2.0', '<' ) ) {

					if ( get_option( 'wcrp_rental_products_disable_rental_dates' ) === false ) {

						update_option( 'wcrp_rental_products_disable_rental_dates', '' );

					}

					if ( get_option( 'wcrp_rental_products_rental_information' ) === false ) {

						update_option( 'wcrp_rental_products_rental_information', '' );

					}

				}

				if ( version_compare( $version, '2.3.0', '<' ) ) {

					if ( get_option( 'wcrp_rental_products_minimum_days' ) == '0' ) {

						$wpdb->query( "UPDATE `{$wpdb->prefix}postmeta` SET `meta_value` = '1' WHERE `meta_key` = '_wcrp_rental_products_minimum_days' AND `meta_value` = '';" );

					} else {

						$wpdb->query(
							$wpdb->prepare(
								"UPDATE `{$wpdb->prefix}postmeta` SET `meta_value` = %s WHERE `meta_key` = '_wcrp_rental_products_minimum_days' AND `meta_value` = '';",
								get_option( 'wcrp_rental_products_minimum_days' )
							)
						);

					}

					$wpdb->query(
						$wpdb->prepare(
							"UPDATE `{$wpdb->prefix}postmeta` SET `meta_value` = %s WHERE `meta_key` = '_wcrp_rental_products_maximum_days' AND `meta_value` = '';",
							get_option( 'wcrp_rental_products_maximum_days' )
						)
					);

					$rental_products = $wpdb->get_results( "SELECT post_id FROM `{$wpdb->prefix}postmeta` WHERE `meta_key` = '_wcrp_rental_products_rental';" );

					if ( !empty( $rental_products ) ) {

						foreach ( $rental_products as $rental_product ) {

							$minimum_days = get_post_meta( $rental_product->post_id, '_wcrp_rental_products_minimum_days', true );
							$maximum_days = get_post_meta( $rental_product->post_id, '_wcrp_rental_products_maximum_days', true );

							if ( $minimum_days == $maximum_days ) {

								update_post_meta( $rental_product->post_id, '_wcrp_rental_products_pricing_period', $minimum_days );

							} else {

								update_post_meta( $rental_product->post_id, '_wcrp_rental_products_pricing_period', '1' );

							}

						}

					}

					$wpdb->query(
						$wpdb->prepare(
							"UPDATE `{$wpdb->prefix}postmeta` SET `meta_value` = %s WHERE `meta_key` = '_wcrp_rental_products_start_days_threshold' AND `meta_value` = '';",
							get_option( 'wcrp_rental_products_start_days_threshold' )
						)
					);

					$wpdb->query(
						$wpdb->prepare(
							"UPDATE `{$wpdb->prefix}postmeta` SET `meta_value` = %s WHERE `meta_key` = '_wcrp_rental_products_return_days_threshold' AND `meta_value` = '';",
							get_option( 'wcrp_rental_products_return_days_threshold' )
						)
					);

					$wpdb->query(
						$wpdb->prepare(
							"UPDATE `{$wpdb->prefix}postmeta` SET `meta_value` = %s WHERE `meta_key` = '_wcrp_rental_products_months' AND `meta_value` = '';",
							get_option( 'wcrp_rental_products_months' )
						)
					);

					$wpdb->query(
						$wpdb->prepare(
							"UPDATE `{$wpdb->prefix}postmeta` SET `meta_value` = %s WHERE `meta_key` = '_wcrp_rental_products_columns' AND `meta_value` = '';",
							get_option( 'wcrp_rental_products_columns' )
						)
					);

					$wpdb->query(
						$wpdb->prepare(
							"UPDATE `{$wpdb->prefix}postmeta` SET `meta_value` = %s WHERE `meta_key` = '_wcrp_rental_products_inline' AND `meta_value` = '';",
							get_option( 'wcrp_rental_products_inline' )
						)
					);

					$rental_products = $wpdb->get_results( "SELECT post_id FROM `{$wpdb->prefix}postmeta` WHERE `meta_key` = '_wcrp_rental_products_non_day_price_display' AND `meta_value` = 'yes';" );

					if ( !empty( $rental_products ) ) {

						foreach ( $rental_products as $rental_product ) {

							$product = wc_get_product( $rental_product->post_id );
							$minimum_days = get_post_meta( $rental_product->post_id, '_wcrp_rental_products_minimum_days', true );
							$maximum_days = get_post_meta( $rental_product->post_id, '_wcrp_rental_products_maximum_days', true );

							if ( $minimum_days == $maximum_days ) {

								$rental_type = get_post_meta( $rental_product->post_id, '_wcrp_rental_products_rental', true );

								if ( 'yes' == $rental_type ) {

									$regular_price = $product->get_regular_price();
									$sale_price = $product->get_sale_price();

									if ( !empty( $regular_price ) ) {

										$new_regular_price = (float) $regular_price * (int) $minimum_days;
										$product->set_regular_price( $new_regular_price );

									}

									if ( !empty( $sale_price ) ) {

										$new_sale_price = (float) $sale_price * (int) $minimum_days;
										$product->set_sale_price( $new_sale_price );

									}

									if ( $product->is_on_sale() ) {

										if ( !empty( $new_sale_price ) ) {

											$product->set_price( $new_sale_price );

										}

									} else {

										if ( !empty( $new_regular_price ) ) {

											$product->set_price( $new_regular_price );

										}

									}

									$product->save();

								} elseif ( 'yes_purchase' ) {

									$price = get_post_meta( $rental_product->post_id, '_wcrp_rental_products_rental_purchase_price', true );

									if ( !empty( $price ) ) {

										$new_price = (float) $price * (int) $minimum_days;
										update_post_meta( $rental_product->post_id, '_wcrp_rental_products_rental_purchase_price', $new_price );

									}

								}

							}

						}

					}

					if ( 'yes' == get_option( 'wcrp_rental_products_non_day_price_display' ) ) {

						$rental_products = $wpdb->get_results( "SELECT post_id FROM `{$wpdb->prefix}postmeta` WHERE `meta_key` = '_wcrp_rental_products_non_day_price_display' AND `meta_value` = '';" );

						if ( !empty( $rental_products ) ) {

							foreach ( $rental_products as $rental_product ) {

								$product = wc_get_product( $rental_product->post_id );
								$minimum_days = get_post_meta( $rental_product->post_id, '_wcrp_rental_products_minimum_days', true );
								$maximum_days = get_post_meta( $rental_product->post_id, '_wcrp_rental_products_maximum_days', true );

								if ( $minimum_days == $maximum_days ) {

									$rental_type = get_post_meta( $rental_product->post_id, '_wcrp_rental_products_rental', true );

									if ( 'yes' == $rental_type ) {

										$regular_price = $product->get_regular_price();
										$sale_price = $product->get_sale_price();

										if ( !empty( $regular_price ) ) {

											$new_regular_price = (float) $regular_price * (int) $minimum_days;
											$product->set_regular_price( $new_regular_price );

										}

										if ( !empty( $sale_price ) ) {

											$new_sale_price = (float) $sale_price * (int) $minimum_days;
											$product->set_sale_price( $new_sale_price );

										}

										if ( $product->is_on_sale() ) {

											if ( !empty( $new_sale_price ) ) {

												$product->set_price( $new_sale_price );

											}

										} else {

											if ( !empty( $new_regular_price ) ) {

												$product->set_price( $new_regular_price );

											}

										}

										$product->save();

									} elseif ( 'yes_purchase' ) {

										$price = get_post_meta( $rental_product->post_id, '_wcrp_rental_products_rental_purchase_price', true );

										if ( !empty( $price ) ) {

											$new_price = (float) $price * (int) $minimum_days;
											update_post_meta( $rental_product->post_id, '_wcrp_rental_products_rental_purchase_price', $new_price );

										}

									}

								}

							}

						}

					}

					$wpdb->query( "DELETE FROM {$wpdb->prefix}postmeta WHERE `meta_key` = '_wcrp_rental_products_non_day_price_display';" );

					delete_option( 'wcrp_rental_products_minimum_days' );
					delete_option( 'wcrp_rental_products_maximum_days' );
					delete_option( 'wcrp_rental_products_start_days_threshold' );
					delete_option( 'wcrp_rental_products_return_days_threshold' );
					delete_option( 'wcrp_rental_products_months' );
					delete_option( 'wcrp_rental_products_columns' );
					delete_option( 'wcrp_rental_products_inline' );
					delete_option( 'wcrp_rental_products_non_day_price_display' );

					if ( get_option( 'wcrp_rental_products_rental_date_format' ) === false ) {

						add_option( 'wcrp_rental_products_rental_date_format', '' );

					}

					self::woocommerce_sessions_carts_clear();

				}

				if ( version_compare( $version, '2.3.1', '<' ) ) {

					$rental_products = $wpdb->get_results( "SELECT post_id FROM `{$wpdb->prefix}postmeta` WHERE `meta_key` = '_wcrp_rental_products_rental' AND meta_value != '';" );

					if ( !empty( $rental_products ) ) {

						foreach ( $rental_products as $rental_product ) {

							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_pricing_type', 'period' );

						}

					}

				}

				if ( version_compare( $version, '2.4.0', '<' ) ) {

					$rental_products = $wpdb->get_results( "SELECT post_id FROM `{$wpdb->prefix}postmeta` WHERE `meta_key` = '_wcrp_rental_products_rental' AND meta_value != '';" );

					if ( !empty( $rental_products ) ) {

						$pricing_tiers_data = array(
							'days' => array(
								'0' => '1'
							),
							'percent' => array(
								'0' => '0'
							),
						);

						foreach ( $rental_products as $rental_product ) {

							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_pricing_tiers', 'no' );
							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_pricing_tiers_data', $pricing_tiers_data );

						}

					}

					self::woocommerce_sessions_carts_clear();

				}

				if ( version_compare( $version, '2.6.0', '<' ) ) {

					$rental_products = $wpdb->get_results( "SELECT post_id FROM `{$wpdb->prefix}postmeta` WHERE `meta_key` = '_wcrp_rental_products_rental' AND meta_value != '';" );

					if ( !empty( $rental_products ) ) {

						foreach ( $rental_products as $rental_product ) {

							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_disable_rental_days', '' );

						}

					}

				}

				if ( version_compare( $version, '2.7.0', '<' ) ) {

					if ( get_option( 'wcrp_rental_products_rental_form_date_format' ) === false ) {

						add_option( 'wcrp_rental_products_rental_form_date_format', '' );

					}

					if ( get_option( 'wcrp_rental_products_rental_form_after_quantity' ) === false ) {

						add_option( 'wcrp_rental_products_rental_form_after_quantity', 'yes' );

					}

				}

				if ( version_compare( $version, '3.0.0', '<' ) ) {

					if ( get_option( 'wcrp_rental_products_rental_purchase_toggle_position' ) === false ) {

						add_option( 'wcrp_rental_products_rental_or_purchase_toggle_position', 'low' );

					}

					if ( get_option( 'wcrp_rental_products_rental_purchase_toggle_type' ) === false ) {

						add_option( 'wcrp_rental_products_rental_purchase_toggle_type', 'link' );

					}

					if ( get_option( 'wcrp_rental_products_rental_price_display_prefix' ) === false ) {

						add_option( 'wcrp_rental_products_rental_price_display_prefix', '' );

					}

					if ( get_option( 'wcrp_rental_products_rental_price_display_suffix' ) === false ) {

						add_option( 'wcrp_rental_products_rental_price_display_suffix', '' );

					}

					if ( get_option( 'wcrp_rental_products_rental_price_display_rent_text' ) === false ) {

						add_option( 'wcrp_rental_products_rental_price_display_rent_text', 'yes' );

					}

					if ( get_option( 'wcrp_rental_products_rental_information_title' ) === false ) {

						add_option( 'wcrp_rental_products_rental_information_title', __( 'Rental information', 'wcrp-rental-products' ) );

					}

					if ( get_option( 'wcrp_rental_products_rental_information_heading' ) === false ) {

						add_option( 'wcrp_rental_products_rental_information_heading', 'yes' );

					}

					if ( get_option( 'wcrp_rental_products_text_select_dates' ) === false ) {

						add_option( 'wcrp_rental_products_text_select_dates', __( 'Select dates', 'wcrp-rental-products' ) );

					}

					if ( get_option( 'wcrp_rental_products_text_rental_dates' ) === false ) {

						add_option( 'wcrp_rental_products_text_rental_dates', __( 'Rental dates', 'wcrp-rental-products' ) );

					}

					if ( get_option( 'wcrp_rental_products_text_rent_from' ) === false ) {

						add_option( 'wcrp_rental_products_text_rent_from', __( 'Rent from', 'wcrp-rental-products' ) );

					}

					if ( get_option( 'wcrp_rental_products_text_rent_to' ) === false ) {

						add_option( 'wcrp_rental_products_text_rent_to', __( 'Rent to', 'wcrp-rental-products' ) );

					}

					if ( get_option( 'wcrp_rental_products_text_rent_for' ) === false ) {

						add_option( 'wcrp_rental_products_text_rent_for', __( 'Rent for', 'wcrp-rental-products' ) );

					}

					if ( get_option( 'wcrp_rental_products_text_rental_return_within' ) === false ) {

						add_option( 'wcrp_rental_products_text_rental_return_within', __( 'Rental return within', 'wcrp-rental-products' ) );

					}

					if ( get_option( 'wcrp_rental_products_text_rental_returned' ) === false ) {

						add_option( 'wcrp_rental_products_text_rental_returned', __( 'Rental returned', 'wcrp-rental-products' ) );

					}

					if ( get_option( 'wcrp_rental_products_text_rental_cancelled' ) === false ) {

						add_option( 'wcrp_rental_products_text_rental_cancelled', __( 'Rental cancelled', 'wcrp-rental-products' ) );

					}

					$rental_products = $wpdb->get_results( "SELECT post_id FROM `{$wpdb->prefix}postmeta` WHERE `meta_key` = '_wcrp_rental_products_rental' AND meta_value != '';" );

					if ( !empty( $rental_products ) ) {

						foreach ( $rental_products as $rental_product ) {

							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_pricing_period_multiples', 'no' );

						}

					}

					self::woocommerce_sessions_carts_clear();

				}

				if ( version_compare( $version, '3.0.3', '<' ) ) {

					delete_option( 'wcrp_rental_products_rental_or_purchase_toggle_position' );

					if ( get_option( 'wcrp_rental_products_rental_purchase_toggle_position' ) === false ) {

						add_option( 'wcrp_rental_products_rental_purchase_toggle_position', 'low' );

					}

				}

				if ( version_compare( $version, '3.0.5', '<' ) ) {

					if ( get_option( 'wcrp_rental_products_rental_form_first_day' ) === false ) {

						add_option( 'wcrp_rental_products_rental_form_first_day', '1' );

					}

				}

				if ( version_compare( $version, '3.1.0', '<' ) ) {

					$rental_products = $wpdb->get_results( "SELECT post_id FROM `{$wpdb->prefix}postmeta` WHERE `meta_key` = '_wcrp_rental_products_rental' AND meta_value != '';" );

					if ( !empty( $rental_products ) ) {

						foreach ( $rental_products as $rental_product ) {

							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_pricing_period_multiples_maximum', '0' );
							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_price_display_override', '' );
							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_total_overrides', '' );
							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_disable_rental_start_end_days', '' );
							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_security_deposit_amount', '' );
							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_security_deposit_calculation', 'quantity' );
							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_security_deposit_tax_status', 'taxable' );
							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_security_deposit_tax_class', '' );
							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_security_deposit_non_refundable', 'no' );
							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_rental_purchase_rental_tax_override', 'no' );
							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_rental_purchase_rental_tax_override_status', 'taxable' );
							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_rental_purchase_rental_tax_override_class', '' );
							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_rental_purchase_rental_shipping_override', 'no' );
							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_rental_purchase_rental_shipping_override_class', '' );

						}

					}

					if ( get_option( 'wcrp_rental_products_text_availability_checker_applied' ) === false ) {

						add_option( 'wcrp_rental_products_text_availability_checker_applied', __( 'Rental products will now show availability for your selected dates.', 'wcrp-rental-products' ) );

					}

					if ( get_option( 'wcrp_rental_products_availability_checker_status_rental' ) === false ) {

						add_option( 'wcrp_rental_products_availability_checker_status_rental', 'yes' );

					}

					if ( get_option( 'wcrp_rental_products_availability_checker_status_rental_purchase' ) === false ) {

						add_option( 'wcrp_rental_products_availability_checker_status_rental_purchase', 'no' );

					}

					if ( get_option( 'wcrp_rental_products_text_check_availability' ) === false ) {

						add_option( 'wcrp_rental_products_text_check_availability', __( 'Check availability', 'wcrp-rental-products' ) );

					}

					if ( get_option( 'wcrp_rental_products_text_disable_rental_start_end_days_notice_text' ) === false ) {

						add_option( 'wcrp_rental_products_text_disable_rental_start_end_days_notice_text', __( 'Rentals cannot start/end on', 'wcrp-rental-products' ) );

					}

					if ( get_option( 'wcrp_rental_products_text_non_refundable' ) === false ) {

						add_option( 'wcrp_rental_products_text_non_refundable', __( 'Non-refundable', 'wcrp-rental-products' ) );

					}

					if ( get_option( 'wcrp_rental_products_text_refundable' ) === false ) {

						add_option( 'wcrp_rental_products_text_refundable', __( 'Refundable', 'wcrp-rental-products' ) );

					}

					if ( get_option( 'wcrp_rental_products_text_rental_available' ) === false ) {

						add_option( 'wcrp_rental_products_text_rental_available', __( 'Rental available', 'wcrp-rental-products' ) );

					}

					if ( get_option( 'wcrp_rental_products_rental_purchase_toggle_loops_blocks_display' ) === false ) {

						add_option( 'wcrp_rental_products_rental_purchase_toggle_loops_blocks_display', 'yes' );

					}

					if ( get_option( 'wcrp_rental_products_rental_price_display_override_prefix_suffix' ) === false ) {

						add_option( 'wcrp_rental_products_rental_price_display_override_prefix_suffix', 'yes' );

					}

					if ( get_option( 'wcrp_rental_products_text_rental_unavailable' ) === false ) {

						add_option( 'wcrp_rental_products_text_rental_unavailable', __( 'Rental unavailable', 'wcrp-rental-products' ) );

					}

					if ( get_option( 'wcrp_rental_products_text_reset_dates' ) === false ) {

						add_option( 'wcrp_rental_products_text_reset_dates', __( 'Reset dates', 'wcrp-rental-products' ) );

					}

					if ( get_option( 'wcrp_rental_products_text_security_deposit' ) === false ) {

						add_option( 'wcrp_rental_products_text_security_deposit', __( 'Security deposit', 'wcrp-rental-products' ) );

					}

					if ( get_option( 'wcrp_rental_products_text_view_purchase_options' ) === false ) {

						add_option( 'wcrp_rental_products_text_view_purchase_options', __( 'View purchase options', 'wcrp-rental-products' ) );

					}

					if ( get_option( 'wcrp_rental_products_text_view_rental_options' ) === false ) {

						add_option( 'wcrp_rental_products_text_view_rental_options', __( 'View rental options', 'wcrp-rental-products' ) );

					}

				}

				if ( version_compare( $version, '3.1.1', '<' ) ) {

					if ( get_option( 'wcrp_rental_products_return_days_display' ) === false ) {

						add_option( 'wcrp_rental_products_return_days_display', 'yes' );

					}

				}

				if ( version_compare( $version, '3.2.0', '<' ) ) {

					$rental_products = $wpdb->get_results( "SELECT post_id FROM `{$wpdb->prefix}postmeta` WHERE `meta_key` = '_wcrp_rental_products_rental' AND meta_value != '';" );

					if ( !empty( $rental_products ) ) {

						foreach ( $rental_products as $rental_product ) {

							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_price_additional_periods_percent', 'no' );
							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_price_additional_period_percent', '' );

						}

					}

				}

				if ( version_compare( $version, '3.3.0', '<' ) ) {

					self::woocommerce_sessions_carts_clear();

				}

				if ( version_compare( $version, '3.5.0', '<' ) ) {

					$rental_products = $wpdb->get_results( "SELECT post_id FROM `{$wpdb->prefix}postmeta` WHERE `meta_key` = '_wcrp_rental_products_rental' AND meta_value != '';" );

					if ( !empty( $rental_products ) ) {

						foreach ( $rental_products as $rental_product ) {

							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_disable_addons_rental_purchase_rental', 'no' );
							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_disable_addons_rental_purchase_purchase', 'no' );

						}

					}

				}

				if ( version_compare( $version, '4.0.0', '<' ) ) {

					$rental_products = $wpdb->get_results( "SELECT post_id FROM `{$wpdb->prefix}postmeta` WHERE `meta_key` = '_wcrp_rental_products_rental' AND meta_value != '';" );

					if ( !empty( $rental_products ) ) {

						foreach ( $rental_products as $rental_product ) {

							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_disable_rental_start_end_dates', '' );
							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_pricing_period_additional_selections', '' );
							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_start_day', '' );

						}

					}

					if ( get_option( 'wcrp_rental_products_advanced_configuration' ) === false ) {

						add_option( 'wcrp_rental_products_advanced_configuration', '' );

					}

					if ( get_option( 'wcrp_rental_products_availability_checker_minimum_days' ) === false ) {

						add_option( 'wcrp_rental_products_availability_checker_minimum_days', '1' ); // This was 0 until the minimum allowed was changed to 1 in 5.1.0, so was updated here at that time

					}

					if ( get_option( 'wcrp_rental_products_availability_checker_maximum_days' ) === false ) {

						add_option( 'wcrp_rental_products_availability_checker_maximum_days', '0' );

					}

					if ( get_option( 'wcrp_rental_products_availability_checker_period_multiples' ) === false ) {

						add_option( 'wcrp_rental_products_availability_checker_period_multiples', 'no' );

					}

					if ( get_option( 'wcrp_rental_products_availability_checker_quantity' ) === false ) {

						add_option( 'wcrp_rental_products_availability_checker_quantity', 'yes' );

					}

					if ( get_option( 'wcrp_rental_products_cancel_rentals_in_failed_orders' ) === false ) {

						add_option( 'wcrp_rental_products_cancel_rentals_in_failed_orders', 'no' );

					}

					if ( get_option( 'wcrp_rental_products_disable_rental_start_end_dates' ) === false ) {

						add_option( 'wcrp_rental_products_disable_rental_start_end_dates', '' );

					}

					if ( get_option( 'wcrp_rental_products_text_disable_rental_start_end_dates' ) === false ) {

						add_option( 'wcrp_rental_products_text_disable_rental_start_end_dates', '' );

					}

					if ( get_option( 'wcrp_rental_products_text_disable_rental_start_end_dates_notice_text' ) === false ) {

						add_option( 'wcrp_rental_products_text_disable_rental_start_end_dates_notice_text', __( 'Rentals cannot start/end on highlighted days.', 'wcrp-rental-products' ) );

					}

					if ( get_option( 'wcrp_rental_products_rental_form_auto_apply' ) === false ) {

						add_option( 'wcrp_rental_products_rental_form_auto_apply', 'yes' );

					}

					if ( get_option( 'wcrp_rental_products_rental_form_maximum_date_days' ) === false ) {

						add_option( 'wcrp_rental_products_rental_form_maximum_date_days', '730' );

					}

					if ( get_option( 'wcrp_rental_products_rental_form_maximum_date_specific' ) === false ) {

						add_option( 'wcrp_rental_products_rental_form_maximum_date_specific', '' );

					}

					if ( get_option( 'wcrp_rental_products_rental_form_period_selection_option_labels' ) === false ) {

						add_option( 'wcrp_rental_products_rental_form_period_selection_option_labels', 'days' );

					}

					if ( get_option( 'wcrp_rental_products_rental_form_reset_button' ) === false ) {

						add_option( 'wcrp_rental_products_rental_form_reset_button', 'no' );

					}

					if ( get_option( 'wcrp_rental_products_rental_form_start_end_notices' ) === false ) {

						add_option( 'wcrp_rental_products_rental_form_start_end_notices', 'yes' );

					}

					if ( get_option( 'wcrp_rental_products_text_rental_period' ) === false ) {

						add_option( 'wcrp_rental_products_text_rental_period', __( 'Rental period', 'wcrp-rental-products' ) );

					}

					if ( get_option( 'wcrp_rental_products_same_rental_dates_required' ) === false ) {

						add_option( 'wcrp_rental_products_same_rental_dates_required', 'no' );

					}

					self::woocommerce_sessions_carts_clear();

				}

				if ( version_compare( $version, '4.1.0', '<' ) ) {

					$new_uploads_root_dir = WP_CONTENT_DIR . '/uploads/wcrp-rental-products/';

					if ( !file_exists( $new_uploads_root_dir ) ) {

						wp_mkdir_p( $new_uploads_root_dir );

					}

					$new_uploads_feeds_dir = WP_CONTENT_DIR . '/uploads/wcrp-rental-products/feeds/';

					if ( !file_exists( $new_uploads_feeds_dir ) ) {

						wp_mkdir_p( $new_uploads_feeds_dir );

					}

					if ( get_option( 'wcrp_rental_products_return_rentals_in_completed_orders' ) === false ) {

						add_option( 'wcrp_rental_products_return_rentals_in_completed_orders', 'yes' );

					}

					if ( get_option( 'wcrp_rental_products_managing_rental_orders_information' ) === false ) {

						add_option( 'wcrp_rental_products_managing_rental_orders_information', 'yes' );

					}

					if ( get_option( 'wcrp_rental_products_calendar_feed' ) === false ) {

						add_option( 'wcrp_rental_products_calendar_feed', 'no' );

					}

					if ( get_option( 'wcrp_rental_products_calendar_feed_id' ) === false ) {

						add_option( 'wcrp_rental_products_calendar_feed_id', wp_rand( 1000000 ) );

					}

				}

				if ( version_compare( $version, '4.2.0', '<' ) ) {

					if ( get_option( 'wcrp_rental_products_immediate_rental_stock_replenishment' ) === false ) {

						add_option( 'wcrp_rental_products_immediate_rental_stock_replenishment', 'yes' ); // This was set to no when introduced in 4.2.0, in 5.0.0 we changed this to yes, so new installations have it enabled by default, it was previously no as existing users previously didn't have the setting so their expectation would be for it to be disabled to keep the functionality the same, however the majority of existing users should have upgraded by now and have the option set to their preference

					}

				}

				if ( version_compare( $version, '4.3.0', '<' ) ) {

					$wpdb->query( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wcrp_rental_products_rentals_archive LIKE {$wpdb->prefix}wcrp_rental_products_rentals;" );

					$rental_products = $wpdb->get_results( "SELECT post_id FROM `{$wpdb->prefix}postmeta` WHERE `meta_key` = '_wcrp_rental_products_rental' AND meta_value != '';" );

					if ( !empty( $rental_products ) ) {

						foreach ( $rental_products as $rental_product ) {

							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_in_person_pick_up_return', 'no' );
							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_in_person_return_date', 'default' );
							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_in_person_pick_up_times_fees_same_day', '' );
							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day', '' );
							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_in_person_return_times_fees_same_day', '' );
							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_in_person_return_times_fees_single_day_same_day', '' );
							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_in_person_pick_up_times_fees_next_day', '' );
							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_in_person_return_times_fees_next_day', '' );
							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_multiply_addons_total_by_number_of_days_selected', 'no' );

						}

					}

					if ( get_option( 'wcrp_rental_products_rental_time_format' ) === false ) {

						add_option( 'wcrp_rental_products_rental_time_format', '' );

					}

					if ( get_option( 'wcrp_rental_products_rental_form_auto_select_end_date' ) === false ) {

						add_option( 'wcrp_rental_products_rental_form_auto_select_end_date', 'no' );

					}

					if ( get_option( 'wcrp_rental_products_in_person_return_date' ) === false ) {

						add_option( 'wcrp_rental_products_in_person_return_date', 'same_day' );

					}

					if ( get_option( 'wcrp_rental_products_in_person_pick_up_times_fees_same_day' ) === false ) {

						add_option( 'wcrp_rental_products_in_person_pick_up_times_fees_same_day', '' );

					}

					if ( get_option( 'wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day' ) === false ) {

						add_option( 'wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day', '' );

					}

					if ( get_option( 'wcrp_rental_products_in_person_return_times_fees_same_day' ) === false ) {

						add_option( 'wcrp_rental_products_in_person_return_times_fees_same_day', '' );

					}

					if ( get_option( 'wcrp_rental_products_in_person_return_times_fees_single_day_same_day' ) === false ) {

						add_option( 'wcrp_rental_products_in_person_return_times_fees_single_day_same_day', '' );

					}

					if ( get_option( 'wcrp_rental_products_in_person_pick_up_times_fees_next_day' ) === false ) {

						add_option( 'wcrp_rental_products_in_person_pick_up_times_fees_next_day', '' );

					}

					if ( get_option( 'wcrp_rental_products_in_person_return_times_fees_next_day' ) === false ) {

						add_option( 'wcrp_rental_products_in_person_return_times_fees_next_day', '' );

					}

					if ( get_option( 'wcrp_rental_products_archive_rentals' ) === false ) {

						add_option( 'wcrp_rental_products_archive_rentals', '0' );

					}

					if ( get_option( 'wcrp_rental_products_text_in_person_pick_up_return' ) === false ) {

						add_option( 'wcrp_rental_products_text_in_person_pick_up_return', __( 'In person pick up/return', 'wcrp-rental-products' ) );

					}

					if ( get_option( 'wcrp_rental_products_text_pick_up_date' ) === false ) {

						add_option( 'wcrp_rental_products_text_pick_up_date', __( 'Pick up date', 'wcrp-rental-products' ) );

					}

					if ( get_option( 'wcrp_rental_products_text_pick_up_time' ) === false ) {

						add_option( 'wcrp_rental_products_text_pick_up_time', __( 'Pick up time', 'wcrp-rental-products' ) );

					}

					if ( get_option( 'wcrp_rental_products_text_return_date' ) === false ) {

						add_option( 'wcrp_rental_products_text_return_date', __( 'Return date', 'wcrp-rental-products' ) );

					}

					if ( get_option( 'wcrp_rental_products_text_return_time' ) === false ) {

						add_option( 'wcrp_rental_products_text_return_time', __( 'Return time', 'wcrp-rental-products' ) );

					}

				}

				if ( version_compare( $version, '5.0.0', '<' ) ) {

					$rental_products = $wpdb->get_results( "SELECT post_id FROM `{$wpdb->prefix}postmeta` WHERE `meta_key` = '_wcrp_rental_products_rental' AND meta_value != '';" );

					if ( !empty( $rental_products ) ) {

						foreach ( $rental_products as $rental_product ) {

							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_advanced_pricing', 'default' );
							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_in_person_pick_up_return_time_restrictions', 'default' );
							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_earliest_available_date', '' );
							update_post_meta( $rental_product->post_id, '_wcrp_rental_products_disable_rental_start_end_days_type', 'start_end' );

						}

					}

					delete_option( 'wcrp_rental_products_debug_log' ); // Structure changed so needs regeneration
					delete_option( 'wcrp_rental_products_text_disable_rental_start_end_dates' ); // This was included in 4.0.0 by error and not used so being removed
					delete_option( 'wcrp_rental_products_text_disable_rental_start_end_dates_notice_text' ); // This and setting below removed as replaced by newer related text settings below, note these old names were techinically incorrect unlike all other text settings as they had _text suffix when not consistent with other text settings, hence why the replacement text settings do not include this
					delete_option( 'wcrp_rental_products_text_disable_rental_start_end_days_notice_text' );

					if ( get_option( 'wcrp_rental_products_rental_form_layout' ) === false ) {

						add_option( 'wcrp_rental_products_rental_form_layout', 'theme' );

					}

					if ( get_option( 'wcrp_rental_products_rental_form_available_rental_stock_totals' ) === false ) {

						add_option( 'wcrp_rental_products_rental_form_available_rental_stock_totals', 'no' );

					}

					if ( get_option( 'wcrp_rental_products_rental_form_calendar_custom_styling' ) === false ) {

						add_option( 'wcrp_rental_products_rental_form_calendar_custom_styling', 'no' );

					}

					if ( get_option( 'wcrp_rental_products_rental_form_calendar_custom_styling_code' ) === false ) {

						add_option( 'wcrp_rental_products_rental_form_calendar_custom_styling_code', WCRP_Rental_Products_Product_Rental_Form::rental_form_calendar_styling_defaults() );

					}

					if ( get_option( 'wcrp_rental_products_availability_checker_mode' ) === false ) {

						add_option( 'wcrp_rental_products_availability_checker_mode', 'standard' );

					}

					if ( get_option( 'wcrp_rental_products_availability_checker_status_display' ) === false ) {

						add_option( 'wcrp_rental_products_availability_checker_status_display', 'standard' );

					}

					if ( get_option( 'wcrp_rental_products_availability_checker_apply_dates_redirect' ) === false ) {

						add_option( 'wcrp_rental_products_availability_checker_apply_dates_redirect', '' );

					}

					if ( get_option( 'wcrp_rental_products_advanced_pricing' ) === false ) {

						add_option( 'wcrp_rental_products_advanced_pricing', 'off' );

					}

					if ( get_option( 'wcrp_rental_products_in_person_pick_up_return_time_restrictions' ) === false ) {

						add_option( 'wcrp_rental_products_in_person_pick_up_return_time_restrictions', 'restricted' );

					}

					if ( get_option( 'wcrp_rental_products_checkout_draft_restrictions' ) === false ) {

						add_option( 'wcrp_rental_products_checkout_draft_restrictions', 'yes' );

					}

					if ( get_option( 'wcrp_rental_products_product_updated_restrictions' ) === false ) {

						add_option( 'wcrp_rental_products_product_updated_restrictions', 'yes' );

					}

					if ( get_option( 'wcrp_rental_products_rentals_dashboard_default_tab' ) === false ) {

						add_option( 'wcrp_rental_products_rentals_dashboard_default_tab', 'summary' );

					}

					if ( get_option( 'wcrp_rental_products_text_available_rental_stock_totals' ) === false ) {

						add_option( 'wcrp_rental_products_text_available_rental_stock_totals', __( 'Available', 'wcrp-rental-products' ) );

					}

					if ( get_option( 'wcrp_rental_products_text_disable_rental_start_end_notice' ) === false ) {

						add_option( 'wcrp_rental_products_text_disable_rental_start_end_notice', __( 'Rentals cannot start/end on highlighted days.', 'wcrp-rental-products' ) );

					}

					if ( get_option( 'wcrp_rental_products_text_disable_rental_start_notice' ) === false ) {

						add_option( 'wcrp_rental_products_text_disable_rental_start_notice', __( 'Rentals cannot start on days highlighted with a dotted border.', 'wcrp-rental-products' ) );

					}

					if ( get_option( 'wcrp_rental_products_text_disable_rental_end_notice' ) === false ) {

						add_option( 'wcrp_rental_products_text_disable_rental_end_notice', __( 'Rentals cannot end on days highlighted with a dotted border.', 'wcrp-rental-products' ) );

					}

					self::woocommerce_sessions_carts_clear();

				}

				if ( version_compare( $version, '5.1.0', '<' ) ) {

					if ( get_option( 'wcrp_rental_products_rental_form_notices_use_woocommerce_classes' ) === false ) {

						add_option( 'wcrp_rental_products_rental_form_notices_use_woocommerce_classes', 'yes' );

					}

					if ( '0' == get_option( 'wcrp_rental_products_availability_checker_minimum_days' ) ) {

						update_option( 'wcrp_rental_products_availability_checker_minimum_days', '1' ); // If this was set to 0 then change it to to 1, as the minimum allowed was set to 1 in this version, the previous 0 didn't make sense as minimum days will always be atleast 1, see related 4.0.0 condition

					}

				}

				update_option( 'wcrp_rental_products_version', WCRP_RENTAL_PRODUCTS_VERSION );

			}

		}

		public static function woocommerce_sessions_carts_clear() {

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
