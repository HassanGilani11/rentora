<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Rentals_Tools' ) ) {

	class WCRP_Rental_Products_Rentals_Tools {

		public function __construct() {

			add_action( 'admin_head', array( $this, 'clone_rental_product_options' ) );
			add_action( 'wp_ajax_wcrp_rental_products_select2_ajax_rentals_tools_clone_rental_product_options_from', array( $this, 'clone_rental_product_options_from' ) );
			add_action( 'wp_ajax_wcrp_rental_products_select2_ajax_rentals_tools_clone_rental_product_options_to_categories_select', array( $this, 'clone_rental_product_options_to_categories_select' ) );
			add_action( 'wp_ajax_wcrp_rental_products_select2_ajax_rentals_tools_clone_rental_product_options_to_products_select', array( $this, 'clone_rental_product_options_to_products_select' ) );

		}

		public function clone_rental_product_options() {

			global $wpdb;

			if ( isset( $_POST['wcrp_rental_products_rentals_tools_clone_rental_product_options_nonce'] ) ) {

				if ( wp_verify_nonce( sanitize_key( $_POST['wcrp_rental_products_rentals_tools_clone_rental_product_options_nonce'] ), 'wcrp_rental_products_rentals_tools_clone_rental_product_options' ) ) {

					if ( isset( $_POST['wcrp_rental_products_rentals_tools_clone_rental_product_options_from'] ) && isset( $_POST['wcrp_rental_products_rentals_tools_clone_rental_product_options_which'] ) && isset( $_POST['wcrp_rental_products_rentals_tools_clone_rental_product_options_to'] ) ) {

						$from = sanitize_text_field( $_POST['wcrp_rental_products_rentals_tools_clone_rental_product_options_from'] );
						$which = map_deep( $_POST['wcrp_rental_products_rentals_tools_clone_rental_product_options_which'], 'sanitize_text_field' );
						$to = sanitize_text_field( $_POST['wcrp_rental_products_rentals_tools_clone_rental_product_options_to'] );

						$rental_product = get_post_meta( $from, '_wcrp_rental_products_rental', true );
						$pricing_type = get_post_meta( $from, '_wcrp_rental_products_pricing_type', true );
						$pricing_period = get_post_meta( $from, '_wcrp_rental_products_pricing_period', true );
						$pricing_period_multiples = get_post_meta( $from, '_wcrp_rental_products_pricing_period_multiples', true );
						$pricing_period_multiples_maximum = get_post_meta( $from, '_wcrp_rental_products_pricing_period_multiples_maximum', true );
						$pricing_period_additional_selections = get_post_meta( $from, '_wcrp_rental_products_pricing_period_additional_selections', true );
						$pricing_tiers = get_post_meta( $from, '_wcrp_rental_products_pricing_tiers', true );
						$pricing_tiers_data = get_post_meta( $from, '_wcrp_rental_products_pricing_tiers_data', true );
						$price_additional_periods_percent = get_post_meta( $from, '_wcrp_rental_products_price_additional_periods_percent', true );
						$price_additional_period_percent = get_post_meta( $from, '_wcrp_rental_products_price_additional_period_percent', true );
						$price_display_override = get_post_meta( $from, '_wcrp_rental_products_price_display_override', true );
						$total_overrides = get_post_meta( $from, '_wcrp_rental_products_total_overrides', true );
						$advanced_pricing = get_post_meta( $from, '_wcrp_rental_products_advanced_pricing', true );
						$in_person_pick_up_return = get_post_meta( $from, '_wcrp_rental_products_in_person_pick_up_return', true );
						$in_person_pick_up_return_time_restrictions = get_post_meta( $from, '_wcrp_rental_products_in_person_pick_up_return_time_restrictions', true );
						$in_person_return_date = get_post_meta( $from, '_wcrp_rental_products_in_person_return_date', true );
						$in_person_pick_up_times_fees_same_day = get_post_meta( $from, '_wcrp_rental_products_in_person_pick_up_times_fees_same_day', true );
						$in_person_pick_up_times_fees_single_day_same_day = get_post_meta( $from, '_wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day', true );
						$in_person_return_times_fees_same_day = get_post_meta( $from, '_wcrp_rental_products_in_person_return_times_fees_same_day', true );
						$in_person_return_times_fees_single_day_same_day = get_post_meta( $from, '_wcrp_rental_products_in_person_return_times_fees_single_day_same_day', true );
						$in_person_pick_up_times_fees_next_day = get_post_meta( $from, '_wcrp_rental_products_in_person_pick_up_times_fees_next_day', true );
						$in_person_return_times_fees_next_day = get_post_meta( $from, '_wcrp_rental_products_in_person_return_times_fees_next_day', true );
						$minimum_days = get_post_meta( $from, '_wcrp_rental_products_minimum_days', true );
						$maximum_days = get_post_meta( $from, '_wcrp_rental_products_maximum_days', true );
						$start_day = get_post_meta( $from, '_wcrp_rental_products_start_day', true );
						$start_days_threshold = get_post_meta( $from, '_wcrp_rental_products_start_days_threshold', true );
						$return_days_threshold = get_post_meta( $from, '_wcrp_rental_products_return_days_threshold', true );
						$earliest_available_date = get_post_meta( $from, '_wcrp_rental_products_earliest_available_date', true );
						$disable_rental_dates = get_post_meta( $from, '_wcrp_rental_products_disable_rental_dates', true );
						$disable_rental_days = get_post_meta( $from, '_wcrp_rental_products_disable_rental_days', true );
						$disable_rental_start_end_dates = get_post_meta( $from, '_wcrp_rental_products_disable_rental_start_end_dates', true );
						$disable_rental_start_end_days = get_post_meta( $from, '_wcrp_rental_products_disable_rental_start_end_days', true );
						$disable_rental_start_end_days_type = get_post_meta( $from, '_wcrp_rental_products_disable_rental_start_end_days_type', true );
						$security_deposit_amount = get_post_meta( $from, '_wcrp_rental_products_security_deposit_amount', true );
						$security_deposit_calculation = get_post_meta( $from, '_wcrp_rental_products_security_deposit_calculation', true );
						$security_deposit_tax_status = get_post_meta( $from, '_wcrp_rental_products_security_deposit_tax_status', true );
						$security_deposit_tax_class = get_post_meta( $from, '_wcrp_rental_products_security_deposit_tax_class', true );
						$security_deposit_non_refundable = get_post_meta( $from, '_wcrp_rental_products_security_deposit_non_refundable', true );
						$multiply_addons_total_by_number_of_days_selected = get_post_meta( $from, '_wcrp_rental_products_multiply_addons_total_by_number_of_days_selected', true );
						$disable_addons_rental_purchase_rental = get_post_meta( $from, '_wcrp_rental_products_disable_addons_rental_purchase_rental', true );
						$disable_addons_rental_purchase_purchase = get_post_meta( $from, '_wcrp_rental_products_disable_addons_rental_purchase_purchase', true );
						$months = get_post_meta( $from, '_wcrp_rental_products_months', true );
						$columns = get_post_meta( $from, '_wcrp_rental_products_columns', true );
						$inline = get_post_meta( $from, '_wcrp_rental_products_inline', true );
						$rental_information = get_post_meta( $from, '_wcrp_rental_products_rental_information', true );
						$rental_purchase_rental_tax_override = get_post_meta( $from, '_wcrp_rental_products_rental_purchase_rental_tax_override', true );
						$rental_purchase_rental_tax_override_status = get_post_meta( $from, '_wcrp_rental_products_rental_purchase_rental_tax_override_status', true );
						$rental_purchase_rental_tax_override_class = get_post_meta( $from, '_wcrp_rental_products_rental_purchase_rental_tax_override_class', true );
						$rental_purchase_rental_shipping_override = get_post_meta( $from, '_wcrp_rental_products_rental_purchase_rental_shipping_override', true );
						$rental_purchase_rental_shipping_override_class = get_post_meta( $from, '_wcrp_rental_products_rental_purchase_rental_shipping_override_class', true );

						if ( 'all_products' == $to ) {

							$products = get_posts(
								array(
									'posts_per_page'	=> -1,
									'post_type'			=> 'product',
									'post_status'		=> get_post_stati(), // Ensures auto-drafts are included ('any' wouldn't include this)
									'fields'			=> 'ids',
								)
							);

						} elseif ( 'all_rental_products' == $to ) {

							$products = get_posts(
								array(
									'posts_per_page'	=> -1,
									'post_type'			=> 'product',
									'post_status'		=> get_post_stati(), // Ensures auto-drafts are included ('any' wouldn't include this)
									'fields'			=> 'ids',
									'meta_query'		=> array(
										array(
											'key'		=> '_wcrp_rental_products_rental',
											'value'		=> array(
												'yes',
												'yes_purchase',
											),
											'compare'	=> 'IN',
									   )
									),
								)
							);

						} elseif ( 'all_products_in_specific_categories' == $to ) {

							if ( isset( $_POST['wcrp_rental_products_rentals_tools_clone_rental_product_options_to_categories_select'] ) ) {

								$categories = map_deep( $_POST['wcrp_rental_products_rentals_tools_clone_rental_product_options_to_categories_select'], 'sanitize_text_field' );

								if ( !empty( $categories ) ) {

									$products = get_posts(
										array(
											'posts_per_page'	=> -1,
											'post_type'			=> 'product',
											'post_status'		=> get_post_stati(), // Ensures auto-drafts are included ('any' wouldn't include this)
											'fields'			=> 'ids',
											'tax_query'			=> array(
												array(
													'taxonomy'	=> 'product_cat',
													'field'		=> 'term_id',
													'terms'		=> $categories,
													'operator'  => 'IN',
												)
											),
										)
									);

								}

							}

						} elseif ( 'all_rental_products_in_specific_categories' == $to ) {

							if ( isset( $_POST['wcrp_rental_products_rentals_tools_clone_rental_product_options_to_categories_select'] ) ) {

								$categories = map_deep( $_POST['wcrp_rental_products_rentals_tools_clone_rental_product_options_to_categories_select'], 'sanitize_text_field' );

								if ( !empty( $categories ) ) {

									$products = get_posts(
										array(
											'posts_per_page'	=> -1,
											'post_type'			=> 'product',
											'post_status'		=> get_post_stati(), // Ensures auto-drafts are included ('any' wouldn't include this)
											'fields'			=> 'ids',
											'meta_query'		=> array(
												array(
													'key'		=> '_wcrp_rental_products_rental',
													'value'		=> array(
														'yes',
														'yes_purchase',
													),
													'compare'	=> 'IN',
											   )
											),
											'tax_query'			=> array(
												array(
													'taxonomy'	=> 'product_cat',
													'field'		=> 'term_id',
													'terms'		=> $categories,
													'operator'  => 'IN',
												)
											),
										)
									);

								}

							}

						} elseif ( 'products' == $to ) {

							if ( isset( $_POST['wcrp_rental_products_rentals_tools_clone_rental_product_options_to_products_select'] ) ) {

								$products = map_deep( $_POST['wcrp_rental_products_rentals_tools_clone_rental_product_options_to_products_select'], 'sanitize_text_field' );

							}

						}

						if ( !empty( $products ) && !empty( $from ) && !empty( $which ) && !empty( $to ) ) {

							foreach ( $products as $product ) {

								$product_type = wp_get_post_terms( $product, 'product_type', array( 'fields' => 'slugs' ) );

								if ( !empty( $product_type ) ) {

									$product_type = $product_type[0];

									if ( in_array( $product_type, array( 'simple', 'variable' ) ) ) { // Clone only if the products being cloned to are simple or variable products, grouped products not included during cloning as rental options are not set on them, they are just a collection of other products

										if ( in_array( 'rental_product', $which ) ) {

											update_post_meta( $product, '_wcrp_rental_products_rental', $rental_product );

											if ( 'yes' == $rental_product ) {

												update_post_meta( $product, '_manage_stock', 'no' );
												update_post_meta( $product, '_stock_status', 'instock' );
												update_post_meta( $product, '_backorders', 'no' );

												$variations = $wpdb->get_results(
													$wpdb->prepare(
														"SELECT ID FROM {$wpdb->prefix}posts WHERE post_parent = %d AND post_type = 'product_variation'",
														$product
													)
												);

												if ( !empty( $variations ) ) {

													foreach ( $variations as $variation ) {

														update_post_meta( $variation->ID, '_manage_stock', 'no' );
														update_post_meta( $variation->ID, '_stock_status', 'instock' );
														update_post_meta( $variation->ID, '_backorders', 'no' );

													}

												}

											}

										}

										if ( in_array( 'pricing', $which ) ) {

											update_post_meta( $product, '_wcrp_rental_products_pricing_type', $pricing_type );
											update_post_meta( $product, '_wcrp_rental_products_pricing_period', $pricing_period );
											update_post_meta( $product, '_wcrp_rental_products_pricing_period_multiples', $pricing_period_multiples );
											update_post_meta( $product, '_wcrp_rental_products_pricing_period_multiples_maximum', $pricing_period_multiples_maximum );
											update_post_meta( $product, '_wcrp_rental_products_pricing_period_additional_selections', $pricing_period_additional_selections );
											update_post_meta( $product, '_wcrp_rental_products_pricing_tiers', $pricing_tiers );
											update_post_meta( $product, '_wcrp_rental_products_pricing_tiers_data', $pricing_tiers_data );
											update_post_meta( $product, '_wcrp_rental_products_price_additional_periods_percent', $price_additional_periods_percent );
											update_post_meta( $product, '_wcrp_rental_products_price_additional_period_percent', $price_additional_period_percent );
											update_post_meta( $product, '_wcrp_rental_products_price_display_override', $price_display_override );
											update_post_meta( $product, '_wcrp_rental_products_total_overrides', $total_overrides );
											update_post_meta( $product, '_wcrp_rental_products_advanced_pricing', $advanced_pricing );
											update_post_meta( $product, '_wcrp_rental_products_minimum_days', $minimum_days );
											update_post_meta( $product, '_wcrp_rental_products_maximum_days', $maximum_days );

										}

										if ( in_array( 'pick_up_return', $which ) ) {

											update_post_meta( $product, '_wcrp_rental_products_return_days_threshold', $return_days_threshold );
											update_post_meta( $product, '_wcrp_rental_products_in_person_pick_up_return', $in_person_pick_up_return );
											update_post_meta( $product, '_wcrp_rental_products_in_person_pick_up_return_time_restrictions', $in_person_pick_up_return_time_restrictions );
											update_post_meta( $product, '_wcrp_rental_products_in_person_return_date', $in_person_return_date );
											update_post_meta( $product, '_wcrp_rental_products_in_person_pick_up_times_fees_same_day', $in_person_pick_up_times_fees_same_day );
											update_post_meta( $product, '_wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day', $in_person_pick_up_times_fees_single_day_same_day );
											update_post_meta( $product, '_wcrp_rental_products_in_person_return_times_fees_same_day', $in_person_return_times_fees_same_day );
											update_post_meta( $product, '_wcrp_rental_products_in_person_return_times_fees_single_day_same_day', $in_person_return_times_fees_single_day_same_day );
											update_post_meta( $product, '_wcrp_rental_products_in_person_pick_up_times_fees_next_day', $in_person_pick_up_times_fees_next_day );
											update_post_meta( $product, '_wcrp_rental_products_in_person_return_times_fees_next_day', $in_person_return_times_fees_next_day );

										}

										if ( in_array( 'start_day', $which ) ) {

											update_post_meta( $product, '_wcrp_rental_products_start_day', $start_day );

										}

										if ( in_array( 'start_days_threshold', $which ) ) {

											update_post_meta( $product, '_wcrp_rental_products_start_days_threshold', $start_days_threshold );

										}

										if ( in_array( 'earliest_available_date', $which ) ) {

											update_post_meta( $product, '_wcrp_rental_products_earliest_available_date', $earliest_available_date );

										}

										if ( in_array( 'disable_rental_dates', $which ) ) {

											update_post_meta( $product, '_wcrp_rental_products_disable_rental_dates', $disable_rental_dates );

										}

										if ( in_array( 'disable_rental_days', $which ) ) {

											update_post_meta( $product, '_wcrp_rental_products_disable_rental_days', $disable_rental_days );

										}

										if ( in_array( 'disable_rental_start_end_dates', $which ) ) {

											update_post_meta( $product, '_wcrp_rental_products_disable_rental_start_end_dates', $disable_rental_start_end_dates );

										}

										if ( in_array( 'disable_rental_start_end_days', $which ) ) {

											update_post_meta( $product, '_wcrp_rental_products_disable_rental_start_end_days', $disable_rental_start_end_days );

										}

										if ( in_array( 'disable_rental_start_end_days_type', $which ) ) {

											update_post_meta( $product, '_wcrp_rental_products_disable_rental_start_end_days_type', $disable_rental_start_end_days_type );

										}

										if ( in_array( 'security_deposit_amount', $which ) ) {

											update_post_meta( $product, '_wcrp_rental_products_security_deposit_amount', $security_deposit_amount );

										}

										if ( in_array( 'security_deposit_calculation', $which ) ) {

											update_post_meta( $product, '_wcrp_rental_products_security_deposit_calculation', $security_deposit_calculation );

										}

										if ( in_array( 'security_deposit_tax_status', $which ) ) {

											update_post_meta( $product, '_wcrp_rental_products_security_deposit_tax_status', $security_deposit_tax_status );

										}

										if ( in_array( 'security_deposit_tax_class', $which ) ) {

											update_post_meta( $product, '_wcrp_rental_products_security_deposit_tax_class', $security_deposit_tax_class );

										}

										if ( in_array( 'security_deposit_non_refundable', $which ) ) {

											update_post_meta( $product, '_wcrp_rental_products_security_deposit_non_refundable', $security_deposit_non_refundable );

										}

										if ( in_array( 'multiply_addons_total_by_number_of_days_selected', $which ) ) {

											update_post_meta( $product, '_wcrp_rental_products_multiply_addons_total_by_number_of_days_selected', $multiply_addons_total_by_number_of_days_selected );

										}

										if ( in_array( 'disable_addons_rental_purchase_rental', $which ) ) {

											update_post_meta( $product, '_wcrp_rental_products_disable_addons_rental_purchase_rental', $disable_addons_rental_purchase_rental );

										}

										if ( in_array( 'disable_addons_rental_purchase_purchase', $which ) ) {

											update_post_meta( $product, '_wcrp_rental_products_disable_addons_rental_purchase_purchase', $disable_addons_rental_purchase_purchase );

										}

										if ( in_array( 'months', $which ) ) {

											update_post_meta( $product, '_wcrp_rental_products_months', $months );

										}

										if ( in_array( 'columns', $which ) ) {

											update_post_meta( $product, '_wcrp_rental_products_columns', $columns );

										}

										if ( in_array( 'inline', $which ) ) {

											update_post_meta( $product, '_wcrp_rental_products_inline', $inline );

										}

										if ( in_array( 'rental_information', $which ) ) {

											update_post_meta( $product, '_wcrp_rental_products_rental_information', $rental_information );

										}

										if ( in_array( 'rental_purchase_rental_tax_override', $which ) ) {

											update_post_meta( $product, '_wcrp_rental_products_rental_purchase_rental_tax_override', $rental_purchase_rental_tax_override );

										}

										if ( in_array( 'rental_purchase_rental_tax_override_status', $which ) ) {

											update_post_meta( $product, '_wcrp_rental_products_rental_purchase_rental_tax_override_status', $rental_purchase_rental_tax_override_status );

										}

										if ( in_array( 'rental_purchase_rental_tax_override_class', $which ) ) {

											update_post_meta( $product, '_wcrp_rental_products_rental_purchase_rental_tax_override_class', $rental_purchase_rental_tax_override_class );

										}

										if ( in_array( 'rental_purchase_rental_shipping_override', $which ) ) {

											update_post_meta( $product, '_wcrp_rental_products_rental_purchase_rental_shipping_override', $rental_purchase_rental_shipping_override );

										}

										if ( in_array( 'rental_purchase_rental_shipping_override_class', $which ) ) {

											update_post_meta( $product, '_wcrp_rental_products_rental_purchase_rental_shipping_override_class', $rental_purchase_rental_shipping_override_class );

										}

									}

								}

							}

							add_action( 'admin_notices', function() {

								?>

								<div class="notice notice-success is-dismissible">
									<p><?php esc_html_e( 'Clone rental product options completed successfully.', 'wcrp-rental-products' ); ?></p>
								</div>

								<?php

							} );

						} else {

							add_action( 'admin_notices', function() {

								?>

								<div class="notice notice-error is-dismissible">
									<p><?php esc_html_e( 'Clone rental product options could not be completed.', 'wcrp-rental-products' ); ?></p>
								</div>

								<?php

							} );

						}

					} else {

						add_action( 'admin_notices', function() {

							?>

							<div class="notice notice-error is-dismissible">
								<p><?php esc_html_e( 'Clone rental product options could not be completed.', 'wcrp-rental-products' ); ?></p>
							</div>

							<?php

						} );

					}

				} else {

					add_action( 'admin_notices', function() {

						?>

						<div class="notice notice-error is-dismissible">
							<p><?php esc_html_e( 'Clone rental product options could not be completed.', 'wcrp-rental-products' ); ?></p>
						</div>

						<?php

					} );

				}

			}

		}

		public function clone_rental_product_options_from() {

			$products = get_posts(
				array(
					'orderby'			=> 'title',
					'order'				=> 'asc',
					'fields'			=> 'ids',
					'post_type'			=> 'product',
					'posts_per_page'	=> -1,
					'meta_query'		=> array(
						array(
							'key'		=> '_wcrp_rental_products_rental',
							'value'		=> array(
								'yes',
								'yes_purchase',
							),
							'compare'	=> 'IN',
					   )
					),
				)
			);

			$json = [];

			if ( !empty( $products ) ) {

				foreach ( $products as $product_id ) {

					$text = wp_kses_post( get_the_title( $product_id ) ) . ' ' . esc_html__( '-', 'wcrp-rental-products' ) . ' ' . esc_html__( 'ID:', 'wcrp-rental-products' ) . ' ' . esc_html( $product_id );

					if ( isset( $_GET['search_term'] ) && '' !== $_GET['search_term'] ) {

						if ( stristr( $text, sanitize_text_field( $_GET['search_term'] ) ) ) {

							$json[] = array(
								'id'	=> $product_id,
								'text'	=> $text,
							);

						}

					} else {

						$json[] = array(
							'id'	=> $product_id,
							'text'	=> $text,
						);

					}

				}

			}

			echo wp_json_encode( $json );

			exit;

		}

		public function clone_rental_product_options_to_categories_select() {

			$categories = get_terms(
				array(
					'hide_empty'	=> false,
					'order'			=> 'asc',
					'orderby'		=> 'title',
					'taxonomy'		=> 'product_cat',
				)
			);

			$json = [];

			if ( !empty( $categories ) ) {

				foreach ( $categories as $category ) {

					$text = wp_kses_post( $category->name ) . ' ' . esc_html__( '-', 'wcrp-rental-products' ) . ' ' . esc_html__( 'ID:', 'wcrp-rental-products' ) . ' ' . esc_html( $category->term_id );

					if ( isset( $_GET['search_term'] ) && '' !== $_GET['search_term'] ) {

						if ( stristr( $text, sanitize_text_field( $_GET['search_term'] ) ) ) {

							$json[] = array(
								'id'	=> $category->term_id,
								'text'	=> $text,
							);

						}

					} else {

						$json[] = array(
							'id'	=> $category->term_id,
							'text'	=> $text,
						);

					}

				}

			}

			echo wp_json_encode( $json );

			exit;

		}

		public function clone_rental_product_options_to_products_select() {

			$products = get_posts(
				array(
					'orderby'			=> 'title',
					'order'				=> 'asc',
					'fields'			=> 'ids',
					'post_type'			=> 'product',
					'posts_per_page'	=> -1,
					'meta_query'		=> array(
						array(
							'key'		=> '_wcrp_rental_products_rental',
							'value'		=> array(
								'yes',
								'yes_purchase',
							),
							'compare'	=> 'IN',
					   )
					),
				)
			);

			$json = [];

			if ( !empty( $products ) ) {

				foreach ( $products as $product_id ) {

					$text = wp_kses_post( get_the_title( $product_id ) ) . ' ' . esc_html__( '-', 'wcrp-rental-products' ) . ' ' . esc_html__( 'ID:', 'wcrp-rental-products' ) . ' ' . esc_html( $product_id ) . ' ' . esc_html__( '-', 'wcrp-rental-products' ) . ' ' . esc_html__( 'Rental', 'wcrp-rental-products' );

					if ( isset( $_GET['search_term'] ) && '' !== $_GET['search_term'] ) {

						if ( stristr( $text, sanitize_text_field( $_GET['search_term'] ) ) ) {

							$json[] = array(
								'id'	=> $product_id,
								'text'	=> $text,
							);

						}

					} else {

						$json[] = array(
							'id'	=> $product_id,
							'text'	=> $text,
						);

					}

				}

			}

			$products = get_posts(
				array(
					'orderby'			=> 'title',
					'order'				=> 'asc',
					'fields'			=> 'ids',
					'post_type'			=> 'product',
					'posts_per_page'	=> -1,
					'meta_query'		=> array(
						array(
							'relation' => 'OR',
							array(
								'key' => '_wcrp_rental_products_rental',
								'compare' => 'NOT EXISTS', // This must happen before the EXISTS array or it won't sort correctly https://wordpress.stackexchange.com/questions/102447/sort-on-meta-value-but-include-posts-that-dont-have-one
							),
							array(
								'key'		=> '_wcrp_rental_products_rental',
								'value'		=> array(
									'yes',
									'yes_purchase',
								),
								'compare'	=> 'NOT IN',
							),
						)
					),
				)
			);

			if ( !empty( $products ) ) {

				foreach ( $products as $product_id ) {

					$text = wp_kses_post( get_the_title( $product_id ) ) . ' ' . esc_html__( '-', 'wcrp-rental-products' ) . ' ' . esc_html__( 'ID:', 'wcrp-rental-products' ) . ' ' . esc_html( $product_id ) . ' ' . esc_html__( '-', 'wcrp-rental-products' ) . ' ' . esc_html__( 'Non-rental', 'wcrp-rental-products' );

					if ( isset( $_GET['search_term'] ) && '' !== $_GET['search_term'] ) {

						if ( stristr( $text, sanitize_text_field( $_GET['search_term'] ) ) ) {

							$json[] = array(
								'id'	=> $product_id,
								'text'	=> $text,
							);

						}

					} else {

						$json[] = array(
							'id'	=> $product_id,
							'text'	=> $text,
						);

					}

				}

			}

			echo wp_json_encode( $json );

			exit;

		}

	}

}
