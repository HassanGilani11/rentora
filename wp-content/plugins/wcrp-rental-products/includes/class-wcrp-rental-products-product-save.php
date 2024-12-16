<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Product_Save' ) ) {

	class WCRP_Rental_Products_Product_Save {

		public function __construct() {

			add_action( 'woocommerce_process_product_meta', array( $this, 'product_data_save' ) );
			add_action( 'wp_ajax_wcrp_rental_products_save_rental_product_option_ajax', array( $this, 'save_rental_product_option_ajax' ) );
			add_action( 'woocommerce_save_product_variation', array( $this, 'product_data_variations_save' ), 10, 2 );
			add_action( 'updated_post_meta', array( $this, 'force_stock_meta' ), PHP_INT_MAX, 4 );
			add_action( 'woocommerce_save_product_variation', array( $this, 'force_stock_meta_variation' ), PHP_INT_MAX, 2 );

		}

		public function product_data_save( $post_id ) {

			if ( isset( $_POST['woocommerce_meta_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['woocommerce_meta_nonce'] ), 'woocommerce_save_data' ) ) {

				$default_rental_options = wcrp_rental_products_default_rental_options();

				// Stock

				if ( isset( $_POST['_wcrp_rental_products_rental'] ) ) {

					if ( 'yes' == sanitize_text_field( $_POST['_wcrp_rental_products_rental'] ) ) { // If rental only

						update_post_meta( $post_id, '_manage_stock', 'no' );
						update_post_meta( $post_id, '_stock_status', 'instock' );
						update_post_meta( $post_id, '_backorders', 'no' );

					}

				}

				// Rental panel

				if ( isset( $_POST['_wcrp_rental_products_rental'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_rental', sanitize_text_field( $_POST['_wcrp_rental_products_rental'] ) );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_rental', $default_rental_options['_wcrp_rental_products_rental'] );

				}

				if ( isset( $_POST['_wcrp_rental_products_pricing_type'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_pricing_type', sanitize_text_field( $_POST['_wcrp_rental_products_pricing_type'] ) );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_pricing_type', $default_rental_options['_wcrp_rental_products_pricing_type'] );

				}

				if ( isset( $_POST['_wcrp_rental_products_pricing_period'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_pricing_period', sanitize_text_field( $_POST['_wcrp_rental_products_pricing_period'] ) );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_pricing_period', '' );

				}

				if ( isset( $_POST['_wcrp_rental_products_pricing_period_multiples'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_pricing_period_multiples', 'yes' );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_pricing_period_multiples', 'no' );

				}

				if ( isset( $_POST['_wcrp_rental_products_pricing_period_multiples_maximum'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_pricing_period_multiples_maximum', sanitize_text_field( $_POST['_wcrp_rental_products_pricing_period_multiples_maximum'] ) );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_pricing_period_multiples_maximum', '' );

				}

				if ( isset( $_POST['_wcrp_rental_products_pricing_period_additional_selections'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_pricing_period_additional_selections', sanitize_text_field( $_POST['_wcrp_rental_products_pricing_period_additional_selections'] ) ); // Not sanitize_text_area even though a textarea because new lines should not be included and do not want to be preserved

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_pricing_period_additional_selections', $default_rental_options['_wcrp_rental_products_pricing_period_additional_selections'] );

				}

				if ( isset( $_POST['_wcrp_rental_products_pricing_tiers'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_pricing_tiers', 'yes' );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_pricing_tiers', 'no' );

				}

				if ( isset( $_POST['_wcrp_rental_products_pricing_tiers_data'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_pricing_tiers_data', map_deep( $_POST['_wcrp_rental_products_pricing_tiers_data'], 'sanitize_text_field' ) );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_pricing_tiers_data', $default_rental_options['_wcrp_rental_products_pricing_tiers_data'] );

				}

				if ( isset( $_POST['_wcrp_rental_products_price_additional_periods_percent'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_price_additional_periods_percent', 'yes' );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_price_additional_periods_percent', 'no' );

				}

				if ( isset( $_POST['_wcrp_rental_products_price_additional_period_percent'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_price_additional_period_percent', sanitize_text_field( $_POST['_wcrp_rental_products_price_additional_period_percent'] ) );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_price_additional_period_percent', '' );

				}

				if ( isset( $_POST['_wcrp_rental_products_price_display_override'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_price_display_override', wp_kses_post( $_POST['_wcrp_rental_products_price_display_override'] ) );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_price_display_override', $default_rental_options['_wcrp_rental_products_price_display_override'] );

				}

				if ( isset( $_POST['_wcrp_rental_products_total_overrides'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_total_overrides', sanitize_text_field( $_POST['_wcrp_rental_products_total_overrides'] ) ); // Not sanitize_text_area even though a textarea because new lines should not be included and do not want to be preserved

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_total_overrides', $default_rental_options['_wcrp_rental_products_total_overrides'] );

				}

				if ( isset( $_POST['_wcrp_rental_products_advanced_pricing'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_advanced_pricing', sanitize_text_field( $_POST['_wcrp_rental_products_advanced_pricing'] ) );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_advanced_pricing', 'default' );

				}

				if ( isset( $_POST['_wcrp_rental_products_in_person_pick_up_return'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_in_person_pick_up_return', 'yes' );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_in_person_pick_up_return', 'no' );

				}

				if ( isset( $_POST['_wcrp_rental_products_in_person_pick_up_return_time_restrictions'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_in_person_pick_up_return_time_restrictions', sanitize_text_field( $_POST['_wcrp_rental_products_in_person_pick_up_return_time_restrictions'] ) );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_in_person_pick_up_return_time_restrictions', 'default' );

				}

				if ( isset( $_POST['_wcrp_rental_products_in_person_return_date'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_in_person_return_date', sanitize_text_field( $_POST['_wcrp_rental_products_in_person_return_date'] ) );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_in_person_return_date', 'default' );

				}

				if ( isset( $_POST['_wcrp_rental_products_in_person_pick_up_times_fees_same_day'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_in_person_pick_up_times_fees_same_day', sanitize_text_field( $_POST['_wcrp_rental_products_in_person_pick_up_times_fees_same_day'] ) ); // Not sanitize_text_area even though a textarea because new lines should not be included and do not want to be preserved

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_in_person_pick_up_times_fees_same_day', '' );

				}

				if ( isset( $_POST['_wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day', sanitize_text_field( $_POST['_wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day'] ) ); // Not sanitize_text_area even though a textarea because new lines should not be included and do not want to be preserved

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day', '' );

				}

				if ( isset( $_POST['_wcrp_rental_products_in_person_return_times_fees_same_day'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_in_person_return_times_fees_same_day', sanitize_text_field( $_POST['_wcrp_rental_products_in_person_return_times_fees_same_day'] ) ); // Not sanitize_text_area even though a textarea because new lines should not be included and do not want to be preserved

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_in_person_return_times_fees_same_day', '' );

				}

				if ( isset( $_POST['_wcrp_rental_products_in_person_return_times_fees_single_day_same_day'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_in_person_return_times_fees_single_day_same_day', sanitize_text_field( $_POST['_wcrp_rental_products_in_person_return_times_fees_single_day_same_day'] ) ); // Not sanitize_text_area even though a textarea because new lines should not be included and do not want to be preserved

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_in_person_return_times_fees_single_day_same_day', '' );

				}

				if ( isset( $_POST['_wcrp_rental_products_in_person_pick_up_times_fees_next_day'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_in_person_pick_up_times_fees_next_day', sanitize_text_field( $_POST['_wcrp_rental_products_in_person_pick_up_times_fees_next_day'] ) ); // Not sanitize_text_area even though a textarea because new lines should not be included and do not want to be preserved

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_in_person_pick_up_times_fees_next_day', '' );

				}

				if ( isset( $_POST['_wcrp_rental_products_in_person_return_times_fees_next_day'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_in_person_return_times_fees_next_day', sanitize_text_field( $_POST['_wcrp_rental_products_in_person_return_times_fees_next_day'] ) ); // Not sanitize_text_area even though a textarea because new lines should not be included and do not want to be preserved

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_in_person_return_times_fees_next_day', '' );

				}

				if ( isset( $_POST['_wcrp_rental_products_minimum_days'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_minimum_days', sanitize_text_field( $_POST['_wcrp_rental_products_minimum_days'] ) );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_minimum_days', '' );

				}

				if ( isset( $_POST['_wcrp_rental_products_maximum_days'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_maximum_days', sanitize_text_field( $_POST['_wcrp_rental_products_maximum_days'] ) );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_maximum_days', '' );

				}

				if ( isset( $_POST['_wcrp_rental_products_start_day'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_start_day', sanitize_text_field( $_POST['_wcrp_rental_products_start_day'] ) );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_start_day', $default_rental_options['_wcrp_rental_products_start_day'] );

				}

				if ( isset( $_POST['_wcrp_rental_products_start_days_threshold'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_start_days_threshold', sanitize_text_field( $_POST['_wcrp_rental_products_start_days_threshold'] ) );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_start_days_threshold', '' );

				}

				if ( isset( $_POST['_wcrp_rental_products_return_days_threshold'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_return_days_threshold', sanitize_text_field( $_POST['_wcrp_rental_products_return_days_threshold'] ) );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_return_days_threshold', '' );

				}

				if ( isset( $_POST['_wcrp_rental_products_earliest_available_date'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_earliest_available_date', sanitize_text_field( $_POST['_wcrp_rental_products_earliest_available_date'] ) );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_earliest_available_date', $default_rental_options['_wcrp_rental_products_earliest_available_date'] );

				}

				if ( isset( $_POST['_wcrp_rental_products_disable_rental_dates'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_disable_rental_dates', sanitize_text_field( $_POST['_wcrp_rental_products_disable_rental_dates'] ) );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_disable_rental_dates', $default_rental_options['_wcrp_rental_products_disable_rental_dates'] );

				}

				if ( isset( $_POST['_wcrp_rental_products_disable_rental_days'] ) ) {

					$disable_rental_days_comma_string = '';

					$disable_rental_days = map_deep( $_POST['_wcrp_rental_products_disable_rental_days'], 'sanitize_text_field' );

					if ( !empty( $disable_rental_days ) ) {

						foreach ( $disable_rental_days as $disable_rental_day ) {

							$disable_rental_days_comma_string .= $disable_rental_day . ',';

						}

						$disable_rental_days_comma_string = rtrim( $disable_rental_days_comma_string, ',' );

					}

					update_post_meta( $post_id, '_wcrp_rental_products_disable_rental_days', $disable_rental_days_comma_string );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_disable_rental_days', $default_rental_options['_wcrp_rental_products_disable_rental_days'] );

				}

				if ( isset( $_POST['_wcrp_rental_products_disable_rental_start_end_dates'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_disable_rental_start_end_dates', sanitize_text_field( $_POST['_wcrp_rental_products_disable_rental_start_end_dates'] ) );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_disable_rental_start_end_dates', $default_rental_options['_wcrp_rental_products_disable_rental_start_end_dates'] );

				}

				if ( isset( $_POST['_wcrp_rental_products_disable_rental_start_end_days'] ) ) {

					$disable_rental_start_end_days_comma_string = '';

					$disable_rental_start_end_days = map_deep( $_POST['_wcrp_rental_products_disable_rental_start_end_days'], 'sanitize_text_field' );

					if ( !empty( $disable_rental_start_end_days ) ) {

						foreach ( $disable_rental_start_end_days as $disable_rental_day ) {

							$disable_rental_start_end_days_comma_string .= $disable_rental_day . ',';

						}

						$disable_rental_start_end_days_comma_string = rtrim( $disable_rental_start_end_days_comma_string, ',' );

					}

					update_post_meta( $post_id, '_wcrp_rental_products_disable_rental_start_end_days', $disable_rental_start_end_days_comma_string );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_disable_rental_start_end_days', $default_rental_options['_wcrp_rental_products_disable_rental_start_end_days'] );

				}

				if ( isset( $_POST['_wcrp_rental_products_disable_rental_start_end_days_type'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_disable_rental_start_end_days_type', sanitize_text_field( $_POST['_wcrp_rental_products_disable_rental_start_end_days_type'] ) );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_disable_rental_start_end_days_type', $default_rental_options['_wcrp_rental_products_disable_rental_start_end_days_type'] );

				}

				if ( isset( $_POST['_wcrp_rental_products_security_deposit_amount'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_security_deposit_amount', sanitize_text_field( $_POST['_wcrp_rental_products_security_deposit_amount'] ) );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_security_deposit_amount', '' );

				}

				if ( isset( $_POST['_wcrp_rental_products_security_deposit_calculation'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_security_deposit_calculation', sanitize_text_field( $_POST['_wcrp_rental_products_security_deposit_calculation'] ) );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_security_deposit_calculation', $default_rental_options['_wcrp_rental_products_security_deposit_calculation'] );

				}

				if ( isset( $_POST['_wcrp_rental_products_security_deposit_tax_status'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_security_deposit_tax_status', sanitize_text_field( $_POST['_wcrp_rental_products_security_deposit_tax_status'] ) );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_security_deposit_tax_status', $default_rental_options['_wcrp_rental_products_security_deposit_tax_status'] );

				}

				if ( isset( $_POST['_wcrp_rental_products_security_deposit_tax_class'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_security_deposit_tax_class', sanitize_text_field( $_POST['_wcrp_rental_products_security_deposit_tax_class'] ) );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_security_deposit_tax_class', $default_rental_options['_wcrp_rental_products_security_deposit_tax_class'] );

				}

				if ( isset( $_POST['_wcrp_rental_products_security_deposit_non_refundable'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_security_deposit_non_refundable', 'yes' );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_security_deposit_non_refundable', 'no' );

				}

				if ( isset( $_POST['_wcrp_rental_products_multiply_addons_total_by_number_of_days_selected'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_multiply_addons_total_by_number_of_days_selected', 'yes' );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_multiply_addons_total_by_number_of_days_selected', 'no' );

				}

				if ( isset( $_POST['_wcrp_rental_products_disable_addons_rental_purchase_rental'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_disable_addons_rental_purchase_rental', 'yes' );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_disable_addons_rental_purchase_rental', 'no' );

				}

				if ( isset( $_POST['_wcrp_rental_products_disable_addons_rental_purchase_purchase'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_disable_addons_rental_purchase_purchase', 'yes' );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_disable_addons_rental_purchase_purchase', 'no' );

				}

				if ( isset( $_POST['_wcrp_rental_products_months'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_months', sanitize_text_field( $_POST['_wcrp_rental_products_months'] ) );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_months', '' );

				}

				if ( isset( $_POST['_wcrp_rental_products_columns'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_columns', sanitize_text_field( $_POST['_wcrp_rental_products_columns'] ) );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_columns', '' );

				}

				if ( isset( $_POST['_wcrp_rental_products_inline'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_inline', 'yes' );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_inline', 'no' );

				}

				if ( isset( $_POST['_wcrp_rental_products_rental_information'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_rental_information', wp_kses_post( $_POST['_wcrp_rental_products_rental_information'] ) );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_rental_information', $default_rental_options['_wcrp_rental_products_rental_information'] );

				}

				if ( isset( $_POST['_wcrp_rental_products_rental_purchase_rental_tax_override'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_rental_purchase_rental_tax_override', 'yes' );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_rental_purchase_rental_tax_override', 'no' );

				}

				if ( isset( $_POST['_wcrp_rental_products_rental_purchase_rental_tax_override_status'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_rental_purchase_rental_tax_override_status', sanitize_text_field( $_POST['_wcrp_rental_products_rental_purchase_rental_tax_override_status'] ) );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_rental_purchase_rental_tax_override_status', $default_rental_options['_wcrp_rental_products_rental_purchase_rental_tax_override_status'] );

				}

				if ( isset( $_POST['_wcrp_rental_products_rental_purchase_rental_tax_override_class'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_rental_purchase_rental_tax_override_class', sanitize_text_field( $_POST['_wcrp_rental_products_rental_purchase_rental_tax_override_class'] ) );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_rental_purchase_rental_tax_override_class', $default_rental_options['_wcrp_rental_products_rental_purchase_rental_tax_override_class'] );

				}

				if ( isset( $_POST['_wcrp_rental_products_rental_purchase_rental_shipping_override'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_rental_purchase_rental_shipping_override', 'yes' );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_rental_purchase_rental_shipping_override', 'no' );

				}

				if ( isset( $_POST['_wcrp_rental_products_rental_purchase_rental_shipping_override_class'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_rental_purchase_rental_shipping_override_class', sanitize_text_field( $_POST['_wcrp_rental_products_rental_purchase_rental_shipping_override_class'] ) );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_rental_purchase_rental_shipping_override_class', $default_rental_options['_wcrp_rental_products_rental_purchase_rental_shipping_override_class'] );

				}

				// Rental price/stock

				// Important to note that if a variable product the $_POSTs in the below return an array of variations price/stock data in an array, the santiize_text_field makes them an empty string and the meta on the parent is saved as empty, which is fine as price/stock shouldn't be set on the parent product in this scenario and only only variations, however be aware that the other metas in this function which have equivalent variation fields don't return an array in the same way for some reason and get saved, but thats okay too because those should get their data saved on the parent, the differences can be seen by doing wp_die( var_dump( $_POST['xxx'] ) ) in this function, at time of writing can't uncover a reason for the difference

				if ( isset( $_POST['_wcrp_rental_products_rental_purchase_price'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_rental_purchase_price', sanitize_text_field( $_POST['_wcrp_rental_products_rental_purchase_price'] ) );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_rental_purchase_price', '' );

				}

				if ( isset( $_POST['_wcrp_rental_products_rental_stock'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_rental_stock', sanitize_text_field( $_POST['_wcrp_rental_products_rental_stock'] ) );

				} else {

					update_post_meta( $post_id, '_wcrp_rental_products_rental_stock', '' );

				}

			}

		}

		public function save_rental_product_option_ajax() {

			// See saveRentalProductOptionAjax() function in JS for why this is used

			global $wpdb;

			$return = '0';

			if ( isset( $_POST['nonce'] ) ) {

				if ( wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'wcrp_rental_products_save_rental_product_option_ajax' ) ) {

					if ( isset( $_POST['post_id'] ) && isset( $_POST['rental_product'] ) ) {

						$post_id = sanitize_text_field( $_POST['post_id'] );
						$rental_product = sanitize_text_field( $_POST['rental_product'] );

						update_post_meta( $post_id, '_wcrp_rental_products_rental', $rental_product );

						if ( 'yes' == $rental_product ) { // If rental only

							// Update _manage_stock, _stock_status, _backorders as required for rentals

							update_post_meta( $post_id, '_manage_stock', 'no' );
							update_post_meta( $post_id, '_stock_status', 'instock' );
							update_post_meta( $post_id, '_backorders', 'no' );

							$variations = $wpdb->get_results(
								$wpdb->prepare(
									"SELECT ID FROM {$wpdb->prefix}posts WHERE post_parent = %d AND post_type = 'product_variation'",
									$post_id
								)
							);

							if ( !empty( $variations ) ) {

								foreach ( $variations as $variation ) {

									update_post_meta( $variation->ID, '_manage_stock', 'no' );
									update_post_meta( $variation->ID, '_stock_status', 'instock' );
									update_post_meta( $variation->ID, '_backorders', 'no' );

								}

							}

							$return = '1';

						} else {

							$return = '2';

						}

					}

				}

			}

			echo esc_html( $return );

			exit;

		}

		public function product_data_variations_save( $variation_id, $i ) {

			if ( isset( $_POST['security'] ) && wp_verify_nonce( sanitize_key( $_POST['security'] ), 'save-variations' ) ) {

				// Rental variation options

				if ( isset( $_POST['_wcrp_rental_products_rental_purchase_price'][$i] ) ) {

					update_post_meta( $variation_id, '_wcrp_rental_products_rental_purchase_price', sanitize_text_field( $_POST['_wcrp_rental_products_rental_purchase_price'][$i] ) );

				} else {

					update_post_meta( $variation_id, '_wcrp_rental_products_rental_purchase_price', '' );

				}

				if ( isset( $_POST['_wcrp_rental_products_rental_stock'][$i] ) ) {

					update_post_meta( $variation_id, '_wcrp_rental_products_rental_stock', sanitize_text_field( $_POST['_wcrp_rental_products_rental_stock'][$i] ) );

				} else {

					update_post_meta( $variation_id, '_wcrp_rental_products_rental_stock', '' );

				}

				if ( isset( $_POST['_wcrp_rental_products_pricing_period_additional_selections'][$i] ) ) {

					update_post_meta( $variation_id, '_wcrp_rental_products_pricing_period_additional_selections', sanitize_text_field( $_POST['_wcrp_rental_products_pricing_period_additional_selections'][$i] ) );

				} else {

					update_post_meta( $variation_id, '_wcrp_rental_products_pricing_period_additional_selections', '' );

				}

				if ( isset( $_POST['_wcrp_rental_products_total_overrides'][$i] ) ) {

					update_post_meta( $variation_id, '_wcrp_rental_products_total_overrides', sanitize_text_field( $_POST['_wcrp_rental_products_total_overrides'][$i] ) );

				} else {

					update_post_meta( $variation_id, '_wcrp_rental_products_total_overrides', '' );

				}

				if ( isset( $_POST['_wcrp_rental_products_security_deposit_amount'][$i] ) ) {

					update_post_meta( $variation_id, '_wcrp_rental_products_security_deposit_amount', sanitize_text_field( $_POST['_wcrp_rental_products_security_deposit_amount'][$i] ) );

				} else {

					update_post_meta( $variation_id, '_wcrp_rental_products_security_deposit_amount', '' );

				}

			}

		}

		public function force_stock_meta( $meta_id, $post_id, $meta_key, $meta_value ) {

			// Forces stock meta to the correct values upon saving product or if these specific meta keys are changed (e.g. via bulk edit), parent product id conditions used as this could be meta being updated on a variation

			$stock_meta_keys = array(
				'_manage_stock',
				'_stock_status',
				'_backorders',
			);

			if ( in_array( $meta_key, $stock_meta_keys ) ) {

				$is_rental_only = false;
				$parent_product_id = wp_get_post_parent_id( $post_id );

				if ( $parent_product_id > 0 ) {

					$is_rental_only = wcrp_rental_products_is_rental_only( $parent_product_id );

				} else {

					$is_rental_only = wcrp_rental_products_is_rental_only( $post_id );

				}

				// If is rental only

				if ( true == $is_rental_only ) {

					if ( '_backorders' == $meta_key ) {

						if ( 'no' !== $meta_value ) { // Stops infinite loop

							update_post_meta( $post_id, '_backorders', 'no' );

						}

					} elseif ( '_manage_stock' == $meta_key ) {

						if ( 'no' !== $meta_value ) { // Stops infinite loop

							update_post_meta( $post_id, '_manage_stock', 'no' );

						}

					} elseif ( '_stock_status' == $meta_key ) {

						if ( 'instock' !== $meta_value ) { // Stops infinite loop

							update_post_meta( $post_id, '_stock_status', 'instock' );

						}

					}

				}

			}

		}

		public function force_stock_meta_variation( $variation_id, $i ) {

			// Forces variation stock meta to the correct values upon saving variation product if rental only (from parent's meta)
			// Note that this is related to the save_rental_product_option_ajax() function which saves the rental product option immediately, that has to be done to ensure the meta got below (_wcrp_rental_products_rental) is up to date even if the entire product has not been saved. This is because variations can be saved before the product gets saved (and subsquently _wcrp_rental_products_rental)

			$parent_product_id = wp_get_post_parent_id( $variation_id );

			if ( $parent_product_id > 0 ) {

				if ( true == wcrp_rental_products_is_rental_only( $parent_product_id ) ) {

					update_post_meta( $variation_id, '_backorders', 'no' );
					update_post_meta( $variation_id, '_manage_stock', 'no' );
					update_post_meta( $variation_id, '_stock_status', 'instock' );

				}

			}

		}

	}

}
