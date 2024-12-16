<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Product_Rental_Form' ) ) {

	class WCRP_Rental_Products_Product_Rental_Form {

		public $rental_form_id;

		public function __construct() {

			add_action( 'send_headers', array( $this, 'rental_form_redirects' ) );
			add_action( 'woocommerce_before_add_to_cart_form', array( $this, 'rental_form_wrap_start' ), 0 );
			add_action( 'woocommerce_after_add_to_cart_form', array( $this, 'rental_form_wrap_end' ), PHP_INT_MAX );
			add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'rental_form' ), 11 ); // woocommerce_before_add_to_cart_button is used over woocommerce_before_add_to_cart_quantity as Product Add-ons uses woocommerce_before_add_to_cart_button with 10 priority to display add-on fields, so if rental form after quantity is disabled using this hook and the 11 priority ensures the rental dates are selected before the add-ons, same flow as if rental form after quantity is enabled via the hook below, note that the rental form should not be repositioned from this or hook below, see related FAQ in documentation why
			add_action( 'woocommerce_after_add_to_cart_quantity', array( $this, 'rental_form' ) );
			add_action( 'wp_ajax_wcrp_rental_products_rental_form_update', array( $this, 'rental_form_update' ) );
			add_action( 'wp_ajax_nopriv_wcrp_rental_products_rental_form_update', array( $this, 'rental_form_update' ) );
			add_action( 'wp_ajax_wcrp_rental_products_rental_form_advanced_pricing', array( $this, 'rental_form_advanced_pricing' ) );
			add_action( 'wp_ajax_nopriv_wcrp_rental_products_rental_form_advanced_pricing', array( $this, 'rental_form_advanced_pricing' ) );
			add_action( 'wp_ajax_wcrp_rental_products_rental_form_availability_checker_population', array( $this, 'rental_form_availability_checker_population' ) );
			add_action( 'wp_ajax_nopriv_wcrp_rental_products_rental_form_availability_checker_population', array( $this, 'rental_form_availability_checker_population' ) );
			add_filter( 'get_product_addons_fields', array( $this, 'rental_form_disable_addons' ), PHP_INT_MAX, 2 ); // Note we don't use the additional get_parent_product_addons_fields filter hook here, see comment in the hooked function why
			add_action( 'wp_ajax_wcrp_rental_products_rental_form_add_to_order', array( $this, 'rental_form_add_to_order' ) );
			add_action( 'wp_ajax_nopriv_wcrp_rental_products_rental_form_add_to_order', array( $this, 'rental_form_add_to_order' ) );
			add_filter( 'woocommerce_ajax_variation_threshold', array( $this, 'ajax_variation_threshold' ), PHP_INT_MAX, 2 );
			add_filter( 'run_wptexturize', '__return_false' ); // Temporary fix to an issue found with (assumedly) block themes such as Twenty Twenty Three, it was found that the URLs dynamically generated via inline JS for the pricing period selection option values get the &example=1 to #038;example=1, this appears to be a core bug and occurs when there are certain characters in inline <script> tags and potentially something to do with block themes running all the code output through wptexturize, see: https://core.trac.wordpress.org/ticket/45387#comment:11, returning false for run_wptexturize stops this occuring, however this filter removed if this core issue gets resolved in future, non-block themes seem to be unaffected hence why it wasn't picked up until recently

			$this->rental_form_id = uniqid(); // Ensures if multiple rental forms on page (e.g. quick view of other products) the elements within are targeted instead of the other instances - form names do not matter as submitted

		}

		public function rental_form_redirects() {

			// This function is hooked to send_headers not wp_loaded, as is_product() not available on wp_loaded

			global $post;

			if ( !is_admin() ) {

				if ( is_product() ) {

					$product_id = $post->ID;

					// Remove rent query arg if exists and not a rental or purchase product

					if ( false == wcrp_rental_products_is_rental_purchase( $product_id ) ) {

						if ( isset( $_GET['rent'] ) ) {

							wp_safe_redirect( esc_url_raw( remove_query_arg( 'rent' ) ), 302 ); // esc_url_raw over esc_url as the former replaces entities like & when in a redirect, https://developer.wordpress.org/reference/functions/esc_url_raw/ - states to use esc_url_raw for redirects, 302 temporary redirect as this URL may be a valid URL again in future so not permanent
							exit;

						}

					}

					// Remove rent_period and rent_period_qty query args if exists and not a period selection pricing type

					if ( true == wcrp_rental_products_is_rental_only( $product_id ) || ( true == wcrp_rental_products_is_rental_purchase( $product_id ) ) ) {

						// The above condition means if a rental, this also includes purchasable part of rental or purchase products, purely because the rent query vars we remove conditionally below shouldn't exist on the purchase part either, so if they happen to be there for some reason they get removed

						$default_rental_options = wcrp_rental_products_default_rental_options();

						$pricing_type = get_post_meta( $product_id, '_wcrp_rental_products_pricing_type', true );
						$pricing_type = ( '' !== $pricing_type ? $pricing_type : $default_rental_options['_wcrp_rental_products_pricing_type'] );

						// If not a period selection pricing type then rent_period and rent_period_qty query vars shouldn't exist as only for period selection pricing type, if they do (e.g. if someone had it bookmarked or a store manager has page open, then since the product pricing type was changed to a non-period selection pricing type) we remove them so $rent_period and $rent_period_qty in rental_form() isn't getting set inaccurately, although technically where these get used they should have a period_selection condition before use anyway

						if ( 'period_selection' !== $pricing_type ) {

							if ( isset( $_GET['rent_period'] ) || isset( $_GET['rent_period_qty'] ) ) {

								wp_safe_redirect( esc_url_raw( remove_query_arg( array( 'rent_period', 'rent_period_qty' ) ) ), 302 ); // esc_url_raw and 302 done for the same reasons as other redirects in this function
								exit;

							}

						}

					}

					// Remove rent_period_qty query arg if exists, greater than 1 and product is sold individually, no need to check if a period selection pricing type as will have already been redirected if not above

					if ( isset( $_GET['rent_period_qty'] ) ) {

						if ( (int) sanitize_text_field( $_GET['rent_period_qty'] ) > 1 && 'yes' == get_post_meta( $product_id, '_sold_individually', true ) ) {

							// This is done as someone could have bookmarked a URL like ?rent_period=14&rent_period_qty=2 and since then the product has been updated to be only sold individually, so the 2 no longer applies, if it didn't redirect the total on the product page would be multiplied by 2 as the hidden quantity field gets set to it, however if they addded to cart the cart would only show as a quantity of 1, but we do the redirect to ensure there are no misunderstandings here

							wp_safe_redirect( esc_url_raw( remove_query_arg( array( 'rent_period_qty' ) ) ), 302 ); // esc_url_raw and 302 done for the same reasons as other redirects in this function
							exit;

						}

					}

				}

			}

		}

		public function rental_form_wrap_start() {

			global $post;

			$rent = false;

			if ( isset( $_GET['rent'] ) ) {

				if ( '1' == $_GET['rent'] ) {

					$rent = true;

				}

			}

			if ( wcrp_rental_products_is_rental_only( $post->ID ) || ( wcrp_rental_products_is_rental_purchase( $post->ID ) && true == $rent ) ) {

				// This is used around the cart form so when targeting elements like .cart input[name="quantity"] within it then it is based on this parent wrap, if the cart form is included in the page multiple times (e.g. for quick view functionality) the fields for this specific form are targetted not others. Input fields within the form also include this ID, we target off these individually not from this wrap for leaner code and to ensure those elements have an ID alongside a less specific class so they can potentially be styled (can't style an element using a unique ID that changes)

				echo '<div id="wcrp-rental-products-rental-form-wrap-' . esc_html( $this->rental_form_id ) . '" class="wcrp-rental-products-rental-form-wrap">';

			}

		}

		public function rental_form_wrap_end() {

			global $post;

			$rent = false;

			if ( isset( $_GET['rent'] ) ) {

				if ( '1' == $_GET['rent'] ) {

					$rent = true;

				}

			}

			if ( wcrp_rental_products_is_rental_only( $post->ID ) || ( wcrp_rental_products_is_rental_purchase( $post->ID ) && true == $rent ) ) {

				echo '</div>';

			}

		}

		public function rental_form() {

			global $post;

			$product = wc_get_product( $post );

			if ( !empty( $product ) ) {

				// Bail if rental form action hook isn't the one matching the after quantity setting

				$display_after_quantity = get_option( 'wcrp_rental_products_rental_form_after_quantity' );

				if ( 'yes' == $display_after_quantity ) {

					if ( 'woocommerce_after_add_to_cart_quantity' !== current_action() ) {

						return;

					}

				} else {

					if ( 'woocommerce_after_add_to_cart_quantity' == current_action() ) {

						return;

					}

				}

				// Get initial product data used to determine if a rental, these are needed early as used for the immediate conditions below before all the product variables are set based on those conditions later

				$product_id = $product->get_id();
				$product_type = $product->get_type();

				// Rent - this flags if the rent=1 query var exists, used for rental or purchase products, it's not a means of flagging if a product is a rental, it simply flags that the rent=1 query var is present on the current page to help with conditions differentiating between the rental or pruchase part of a rental or purchase product

				$rent = false;

				if ( true == wcrp_rental_products_is_rental_purchase( $product_id ) ) {

					if ( isset( $_GET['rent'] ) ) {

						if ( '1' == $_GET['rent'] ) {

							$rent = true;

						}

					}

				}

				// If a rental

				if ( wcrp_rental_products_is_rental_only( $product_id ) || ( wcrp_rental_products_is_rental_purchase( $product_id ) && true == $rent ) ) {

					// If a compatible product type

					if ( 'simple' == $product_type || 'variable' == $product_type ) {

						// Hide stock level and add-ons totals for rental products, simple products and variation price/availability for variations, as overridden by rental stock/price

						?>

						<style>
							<?php // Hide core WooCommerce stock level for simple products and variation price/availability for variations as overridden by rental stock/price, if rental form available rental stock totals is enabled these will not effect it due to more targeted classes of .stock that overrides this display none ?>
							.product .stock,
							.product .variations_form .woocommerce-variation-price,
							.product .variations_form .woocommerce-variation-availability {
								display: none !important;
							}
							<?php
							require_once ABSPATH . 'wp-admin/includes/plugin.php';
							if ( is_plugin_active( 'woocommerce-product-addons/woocommerce-product-addons.php' ) ) {
								// Hide the product add-ons total as it displays the product price as a non rental price as it isn't aware of the rental pricing
								?>
								#product-addons-total {
									display: none !important;
								}
							<?php } ?>
						</style>

						<?php

						// Rent period - this flags if the rent_period=x query var exists and sets it to the value, solely used for period selection pricing type

						$rent_period = false;

						if ( isset( $_GET['rent_period'] ) ) {

							$rent_period = sanitize_text_field( $_GET['rent_period'] );

						}

						// Rent period qty - this flags if the rent_period_qty=x query var exists and sets it to the value, solely used for period selection pricing type

						$rent_period_qty = false;

						if ( isset( $_GET['rent_period_qty'] ) ) {

							$rent_period_qty = sanitize_text_field( $_GET['rent_period_qty'] );

						}

						// Override $product tax class/status early for rental or purchase products if overrides enabled

						if ( wcrp_rental_products_is_rental_purchase( $product_id ) && true == $rent ) {

							$rental_purchase_rental_tax_override = get_post_meta( $product_id, '_wcrp_rental_products_rental_purchase_rental_tax_override', true );

							if ( 'yes' == $rental_purchase_rental_tax_override ) {

								// The tax status/class must be updated on the $product early, before things like $product->get_tax_class(), $product->get_tax_status(), and wc_get_price_to_display

								$tax_status_override = get_post_meta( $product_id, '_wcrp_rental_products_rental_purchase_rental_tax_override_status', true );
								$product->set_tax_status( $tax_status_override );

								$tax_class_override = get_post_meta( $product_id, '_wcrp_rental_products_rental_purchase_rental_tax_override_class', true );
								$product->set_tax_class( $tax_class_override );

							}

						}

						// Set initial config variables

						$advanced_configuration = wcrp_rental_products_advanced_configuration();
						$auto_apply = get_option( 'wcrp_rental_products_rental_form_auto_apply' );
						$auto_select_end_date = get_option( 'wcrp_rental_products_rental_form_auto_select_end_date' );
						$available_rental_stock_totals = get_option( 'wcrp_rental_products_rental_form_available_rental_stock_totals' );
						$currency_position = get_option( 'woocommerce_currency_pos' );
						$currency_symbol = get_woocommerce_currency_symbol();
						$default_rental_options = wcrp_rental_products_default_rental_options();
						$notices_use_woocommerce_classes = get_option( 'wcrp_rental_products_rental_form_notices_use_woocommerce_classes' );
						$period_selection_option_labels = get_option( 'wcrp_rental_products_rental_form_period_selection_option_labels' );
						$prices_include_tax = get_option( 'woocommerce_prices_include_tax' );
						$price_decimals = wc_get_price_decimals();
						$price_decimal_separator = wc_get_price_decimal_separator();
						$reset_button = get_option( 'wcrp_rental_products_rental_form_reset_button' );
						$return_days_display = get_option( 'wcrp_rental_products_return_days_display' );
						$start_end_notices = get_option( 'wcrp_rental_products_rental_form_start_end_notices' );
						$taxes_enabled = get_option( 'woocommerce_calc_taxes' );
						$tax_display_shop = get_option( 'woocommerce_tax_display_shop' );

						// Set product data variables

						$product_price = wc_get_price_to_display( $product ); // This must occur after set_tax_status/class of rental or purchase overrides, it is the WooCommerce standard price inc or exc tax depending on tax settings, if a rental only product this is the rental price, if this is a rental or purchase product this is the purchasable price
						$product_sold_individually = $product->get_sold_individually();
						$product_tax_class = $product->get_tax_class();
						$product_tax_status = $product->get_tax_status();
						$product_url = $product->get_permalink();
						$product_url_preview = get_preview_post_link( $product_id );

						$product_taxes = WC_Tax::get_rates( $product_tax_class );

						if ( !empty( $product_taxes ) ) { // This ensures array_shift does not cause fatal error if empty, WooCommerce Tax extension can return this empty when the automated taxes option is enabled

							$product_tax_rates = array_shift( $product_taxes );
							$product_tax_rate = array_shift( $product_tax_rates );

						} else {

							$product_tax_rate = 0;

						}

						// Most rental based product data variables below are checked against '' as a meta value could potentially be 0 which would trigger an empty condition and use the default instead of 0 (e.g. start days can be 0), if get_post_meta is empty or does not exist it returns ''

						$pricing_type = get_post_meta( $product_id, '_wcrp_rental_products_pricing_type', true );
						$pricing_type = ( '' !== $pricing_type ? $pricing_type : $default_rental_options['_wcrp_rental_products_pricing_type'] );

						$pricing_period = get_post_meta( $product_id, '_wcrp_rental_products_pricing_period', true );
						$pricing_period = ( '' !== $pricing_period ? $pricing_period : $default_rental_options['_wcrp_rental_products_pricing_period'] );

						$pricing_period_multiples = get_post_meta( $product_id, '_wcrp_rental_products_pricing_period_multiples', true );
						$pricing_period_multiples = ( '' !== $pricing_period_multiples ? $pricing_period_multiples : $default_rental_options['_wcrp_rental_products_pricing_period_multiples'] );

						$pricing_period_multiples_maximum = get_post_meta( $product_id, '_wcrp_rental_products_pricing_period_multiples_maximum', true );
						$pricing_period_multiples_maximum = ( '' !== $pricing_period_multiples_maximum ? $pricing_period_multiples_maximum : $default_rental_options['_wcrp_rental_products_pricing_period_multiples_maximum'] );

						$pricing_period_additional_selections = get_post_meta( $product_id, '_wcrp_rental_products_pricing_period_additional_selections', true );
						$pricing_period_additional_selections = ( '' !== $pricing_period_additional_selections ? $pricing_period_additional_selections : $default_rental_options['_wcrp_rental_products_pricing_period_additional_selections'] );
						$pricing_period_additional_selections_array = WCRP_Rental_Products_Misc::value_colon_price_pipe_explode( $pricing_period_additional_selections, false );

						$pricing_tiers = get_post_meta( $product_id, '_wcrp_rental_products_pricing_tiers', true );
						$pricing_tiers = ( '' !== $pricing_tiers ? $pricing_tiers : $default_rental_options['_wcrp_rental_products_pricing_tiers'] );

						$pricing_tiers_data = get_post_meta( $product_id, '_wcrp_rental_products_pricing_tiers_data', true );
						$pricing_tiers_data = ( '' !== $pricing_tiers_data ? $pricing_tiers_data : $default_rental_options['_wcrp_rental_products_pricing_tiers_data'] );

						$price_additional_periods_percent = get_post_meta( $product_id, '_wcrp_rental_products_price_additional_periods_percent', true );
						$price_additional_periods_percent = ( '' !== $price_additional_periods_percent ? $price_additional_periods_percent : $default_rental_options['_wcrp_rental_products_price_additional_periods_percent'] );

						$price_additional_period_percent = get_post_meta( $product_id, '_wcrp_rental_products_price_additional_period_percent', true );
						$price_additional_period_percent = ( '' !== $price_additional_period_percent ? $price_additional_period_percent : $default_rental_options['_wcrp_rental_products_price_additional_period_percent'] );

						if ( 'period_selection' == $pricing_type ) {

							// Total overrides are not used for the period selection pricing type, this condition exists because the meta may still exist containing total overrides when product was previously a different pricing type

							$total_overrides = '';
							$total_overrides_json = '[]';

						} else {

							$total_overrides = get_post_meta( $product_id, '_wcrp_rental_products_total_overrides', true );
							$total_overrides = ( '' !== $total_overrides ? $total_overrides : $default_rental_options['_wcrp_rental_products_total_overrides'] );
							$total_overrides_json = WCRP_Rental_Products_Misc::value_colon_price_pipe_explode( $total_overrides, true );

						}

						$advanced_pricing = get_post_meta( $product_id, '_wcrp_rental_products_advanced_pricing', true );
						$advanced_pricing = ( ( '' !== $advanced_pricing && 'default' !== $advanced_pricing ) ? $advanced_pricing : $default_rental_options['_wcrp_rental_products_advanced_pricing'] );

						$in_person_pick_up_return = get_post_meta( $product_id, '_wcrp_rental_products_in_person_pick_up_return', true );
						$in_person_pick_up_return = ( '' !== $in_person_pick_up_return ? $in_person_pick_up_return : $default_rental_options['_wcrp_rental_products_in_person_pick_up_return'] );

						$in_person_pick_up_return_time_restrictions = get_post_meta( $product_id, '_wcrp_rental_products_in_person_pick_up_return_time_restrictions', true );
						$in_person_pick_up_return_time_restrictions = ( ( '' !== $in_person_pick_up_return_time_restrictions && 'default' !== $in_person_pick_up_return_time_restrictions ) ? $in_person_pick_up_return_time_restrictions : $default_rental_options['_wcrp_rental_products_in_person_pick_up_return_time_restrictions'] );

						$in_person_return_date = get_post_meta( $product_id, '_wcrp_rental_products_in_person_return_date', true );
						$in_person_return_date = ( ( '' !== $in_person_return_date && 'default' !== $in_person_return_date ) ? $in_person_return_date : $default_rental_options['_wcrp_rental_products_in_person_return_date'] );

						$in_person_pick_up_times_fees_same_day = get_post_meta( $product_id, '_wcrp_rental_products_in_person_pick_up_times_fees_same_day', true );
						$in_person_pick_up_times_fees_same_day = ( '' !== $in_person_pick_up_times_fees_same_day ? $in_person_pick_up_times_fees_same_day : $default_rental_options['_wcrp_rental_products_in_person_pick_up_times_fees_same_day'] );
						$in_person_pick_up_times_fees_same_day_array = WCRP_Rental_Products_Misc::value_colon_price_pipe_explode( $in_person_pick_up_times_fees_same_day, false );

						$in_person_pick_up_times_fees_single_day_same_day = get_post_meta( $product_id, '_wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day', true );
						$in_person_pick_up_times_fees_single_day_same_day = ( '' !== $in_person_pick_up_times_fees_single_day_same_day ? $in_person_pick_up_times_fees_single_day_same_day : $default_rental_options['_wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day'] );
						$in_person_pick_up_times_fees_single_day_same_day_array = WCRP_Rental_Products_Misc::value_colon_price_pipe_explode( $in_person_pick_up_times_fees_single_day_same_day, false );

						$in_person_pick_up_times_fees_single_day_same_day_default_selection = false;

						foreach ( $in_person_pick_up_times_fees_single_day_same_day_array as $in_person_pick_up_time => $in_person_pick_up_fee ) {

							$in_person_pick_up_times_fees_single_day_same_day_default_selection = $in_person_pick_up_time;
							break;

						}

						ksort( $in_person_pick_up_times_fees_single_day_same_day_array ); // Sorts array earliest to latest times so select field options will be in time order regardless of how data entered, this must occur after the default selection (first time entered) is set

						$in_person_return_times_fees_same_day = get_post_meta( $product_id, '_wcrp_rental_products_in_person_return_times_fees_same_day', true );
						$in_person_return_times_fees_same_day = ( '' !== $in_person_return_times_fees_same_day ? $in_person_return_times_fees_same_day : $default_rental_options['_wcrp_rental_products_in_person_return_times_fees_same_day'] );
						$in_person_return_times_fees_same_day_array = WCRP_Rental_Products_Misc::value_colon_price_pipe_explode( $in_person_return_times_fees_same_day, false );

						$in_person_return_times_fees_single_day_same_day = get_post_meta( $product_id, '_wcrp_rental_products_in_person_return_times_fees_single_day_same_day', true );
						$in_person_return_times_fees_single_day_same_day = ( '' !== $in_person_return_times_fees_single_day_same_day ? $in_person_return_times_fees_single_day_same_day : $default_rental_options['_wcrp_rental_products_in_person_return_times_fees_single_day_same_day'] );
						$in_person_return_times_fees_single_day_same_day_array = WCRP_Rental_Products_Misc::value_colon_price_pipe_explode( $in_person_return_times_fees_single_day_same_day, false );

						$in_person_return_times_fees_single_day_same_day_default_selection = false;

						foreach ( $in_person_return_times_fees_single_day_same_day_array as $in_person_return_time => $in_person_return_fee ) {

							$in_person_return_times_fees_single_day_same_day_default_selection = $in_person_return_time;
							break;

						}

						ksort( $in_person_return_times_fees_single_day_same_day_array ); // Sorts array earliest to latest times so select field options will be in time order regardless of how data entered, this must occur after the default selection (first time entered) is set

						$in_person_pick_up_times_fees_next_day = get_post_meta( $product_id, '_wcrp_rental_products_in_person_pick_up_times_fees_next_day', true );
						$in_person_pick_up_times_fees_next_day = ( '' !== $in_person_pick_up_times_fees_next_day ? $in_person_pick_up_times_fees_next_day : $default_rental_options['_wcrp_rental_products_in_person_pick_up_times_fees_next_day'] );
						$in_person_pick_up_times_fees_next_day_array = WCRP_Rental_Products_Misc::value_colon_price_pipe_explode( $in_person_pick_up_times_fees_next_day, false );

						$in_person_pick_up_times_fees_array = ( 'same_day' == $in_person_return_date ? $in_person_pick_up_times_fees_same_day_array : $in_person_pick_up_times_fees_next_day_array );

						$in_person_pick_up_times_fees_default_selection = false;

						foreach ( $in_person_pick_up_times_fees_array as $in_person_pick_up_time => $in_person_pick_up_fee ) {

							$in_person_pick_up_times_fees_default_selection = $in_person_pick_up_time;
							break;

						}

						ksort( $in_person_pick_up_times_fees_array ); // Sorts array earliest to latest times so select field options will be in time order regardless of how data entered, this must occur after the default selection (first time entered) is set

						$in_person_return_times_fees_next_day = get_post_meta( $product_id, '_wcrp_rental_products_in_person_return_times_fees_next_day', true );
						$in_person_return_times_fees_next_day = ( '' !== $in_person_return_times_fees_next_day ? $in_person_return_times_fees_next_day : $default_rental_options['_wcrp_rental_products_in_person_return_times_fees_next_day'] );
						$in_person_return_times_fees_next_day_array = WCRP_Rental_Products_Misc::value_colon_price_pipe_explode( $in_person_return_times_fees_next_day, false );

						$in_person_return_times_fees_array = ( 'same_day' == $in_person_return_date ? $in_person_return_times_fees_same_day_array : $in_person_return_times_fees_next_day_array );

						$in_person_return_times_fees_default_selection = false;

						foreach ( $in_person_return_times_fees_array as $in_person_return_time => $in_person_return_fee ) {

							$in_person_return_times_fees_default_selection = $in_person_return_time;
							break;

						}

						ksort( $in_person_return_times_fees_array ); // Sorts array earliest to latest times so select field options will be in time order regardless of how data entered, this must occur after the default selection (first time entered) is set

						$minimum_days = get_post_meta( $product_id, '_wcrp_rental_products_minimum_days', true );
						$minimum_days = ( '' !== $minimum_days ? $minimum_days : $default_rental_options['_wcrp_rental_products_minimum_days'] );

						$maximum_days = get_post_meta( $product_id, '_wcrp_rental_products_maximum_days', true );
						$maximum_days = ( '' !== $maximum_days ? $maximum_days : $default_rental_options['_wcrp_rental_products_maximum_days'] );

						if ( 'period_selection' == $pricing_type ) {

							$minimum_days = $pricing_period;
							$maximum_days = $pricing_period;

							if ( false !== $rent_period ) {

								if ( array_key_exists( $rent_period, $pricing_period_additional_selections_array ) ) {

									$minimum_days = $rent_period;
									$maximum_days = $rent_period;

								}

							}

						}

						$start_day = get_post_meta( $product_id, '_wcrp_rental_products_start_day', true );
						$start_day = ( '' !== $start_day ? $start_day : $default_rental_options['_wcrp_rental_products_start_day'] );

						$start_days_threshold = get_post_meta( $product_id, '_wcrp_rental_products_start_days_threshold', true );
						$start_days_threshold = ( '' !== $start_days_threshold ? $start_days_threshold : $default_rental_options['_wcrp_rental_products_start_days_threshold'] );

						$return_days_threshold = get_post_meta( $product_id, '_wcrp_rental_products_return_days_threshold', true );
						$return_days_threshold = ( '' !== $return_days_threshold ? $return_days_threshold : $default_rental_options['_wcrp_rental_products_return_days_threshold'] );

						$earliest_available_date = get_post_meta( $product_id, '_wcrp_rental_products_earliest_available_date', true );
						$earliest_available_date = ( '' !== $earliest_available_date ? $earliest_available_date : $default_rental_options['_wcrp_rental_products_earliest_available_date'] );

						$disable_rental_start_end_dates_global = get_option( 'wcrp_rental_products_disable_rental_start_end_dates' );
						$disable_rental_start_end_dates_product = get_post_meta( $product_id, '_wcrp_rental_products_disable_rental_start_end_dates', true );
						$disable_rental_start_end_dates_product = ( '' !== $disable_rental_start_end_dates_product ? $disable_rental_start_end_dates_product : $default_rental_options['_wcrp_rental_products_disable_rental_start_end_dates'] );
						$disable_rental_start_end_dates_combined_string = '';

						if ( !empty( $disable_rental_start_end_dates_global ) || !empty( $disable_rental_start_end_dates_product ) ) {

							$disable_rental_start_end_dates_global_array = explode( ',', $disable_rental_start_end_dates_global );
							$disable_rental_start_end_dates_product_array = explode( ',', $disable_rental_start_end_dates_product );
							$disable_rental_start_end_dates_combined_array = array_merge( $disable_rental_start_end_dates_global_array, $disable_rental_start_end_dates_product_array );

							if ( !empty( $disable_rental_start_end_dates_combined_array ) ) {

								foreach ( $disable_rental_start_end_dates_combined_array as $disable_rental_start_end_dates_combined_array_date ) {

									$disable_rental_start_end_dates_combined_string .= "'" . $disable_rental_start_end_dates_combined_array_date . "',";

								}

								$disable_rental_start_end_dates_combined_string = rtrim( $disable_rental_start_end_dates_combined_string, ',' );

							}

						}

						$disable_rental_start_end_days = get_post_meta( $product_id, '_wcrp_rental_products_disable_rental_start_end_days', true );
						$disable_rental_start_end_days = ( '' !== $disable_rental_start_end_days ? $disable_rental_start_end_days : $default_rental_options['_wcrp_rental_products_disable_rental_start_end_days'] );

						$disable_rental_start_end_days_type = get_post_meta( $product_id, '_wcrp_rental_products_disable_rental_start_end_days_type', true );
						$disable_rental_start_end_days_type = ( '' !== $disable_rental_start_end_days_type ? $disable_rental_start_end_days_type : $default_rental_options['_wcrp_rental_products_disable_rental_start_end_days_type'] );

						$months = get_post_meta( $product_id, '_wcrp_rental_products_months', true );
						$months = ( '' !== $months ? $months : $default_rental_options['_wcrp_rental_products_months'] );

						$columns = get_post_meta( $product_id, '_wcrp_rental_products_columns', true );
						$columns = ( '' !== $columns ? $columns : $default_rental_options['_wcrp_rental_products_columns'] );

						$inline = get_post_meta( $product_id, '_wcrp_rental_products_inline', true );
						$inline = ( '' !== $inline ? $inline : $default_rental_options['_wcrp_rental_products_inline'] );
						$inline = ( 'yes' == $inline ? 'true' : 'false' ); // Note the true and false are strings as they end up in the inline js

						$multiply_addons_total_by_number_of_days_selected = get_post_meta( $product_id, '_wcrp_rental_products_multiply_addons_total_by_number_of_days_selected', true );
						$multiply_addons_total_by_number_of_days_selected = ( '' !== $multiply_addons_total_by_number_of_days_selected ? $multiply_addons_total_by_number_of_days_selected : $default_rental_options['_wcrp_rental_products_multiply_addons_total_by_number_of_days_selected'] );

						if ( '' !== $earliest_available_date && ( strtotime( $earliest_available_date ) > time() ) ) { // Earliest available date set and not past

							$minimum_date = wp_date( 'Y-m-d', strtotime( $earliest_available_date ) );

						} else {

							$minimum_date = wp_date( 'Y-m-d', strtotime( '+ ' . $start_days_threshold . ' days' ) );

						}

						if ( '' !== $start_day ) {

							if ( '0' == $start_day ) {

								$start_day_name = 'sunday';

							} elseif ( '1' == $start_day ) {

								$start_day_name = 'monday';

							} elseif ( '2' == $start_day ) {

								$start_day_name = 'tuesday';

							} elseif ( '3' == $start_day ) {

								$start_day_name = 'wednesday';

							} elseif ( '4' == $start_day ) {

								$start_day_name = 'thursday';

							} elseif ( '5' == $start_day ) {

								$start_day_name = 'friday';

							} elseif ( '6' == $start_day ) {

								$start_day_name = 'saturday';

							}

							$minimum_date = wp_date( 'Y-m-d', strtotime( 'next ' . $start_day_name, strtotime( $minimum_date ) ) );

						}

						if ( 'yes' == $in_person_pick_up_return ) {

							$in_person_pick_up_return_allowed = true;
							$in_person_invalid_times_fees_checks = false;
							$in_person_invalid_times_fees_single_day_same_day_checks = false;

							if ( 'same_day' == $in_person_return_date ) {

								if ( 'period_selection' == $pricing_type ) {

									if ( '1' == $pricing_period ) {

										$in_person_invalid_times_fees_checks = true; // For pricing period additional selections which will be higher than 1
										$in_person_invalid_times_fees_single_day_same_day_checks = true; // For the pricing period of 1

									} else {

										$in_person_invalid_times_fees_checks = true;

									}

								} else {

									if ( '1' == $minimum_days ) {

										$in_person_invalid_times_fees_single_day_same_day_checks = true;

									}

									if ( '1' !== $maximum_days ) {

										$in_person_invalid_times_fees_checks = true;

									}

								}

							} else {

								$in_person_invalid_times_fees_checks = true;

							}

							if ( true == $in_person_invalid_times_fees_checks ) {

								if ( !empty( $in_person_pick_up_times_fees_array ) && !empty( $in_person_return_times_fees_array ) ) {

									$in_person_invalid_times_fees_count = 0;

									foreach ( $in_person_return_times_fees_array as $in_person_return_time => $in_person_return_fee ) {

										foreach ( $in_person_pick_up_times_fees_array as $in_person_pick_up_time => $in_person_pick_up_fee ) {

											if ( (int) $in_person_return_time > (int) $in_person_pick_up_time ) {

												++$in_person_invalid_times_fees_count;

											}

										}

									}

									if ( $in_person_invalid_times_fees_count > 0 ) {

										if ( 'restricted' == $in_person_pick_up_return_time_restrictions ) {

											$in_person_pick_up_return_allowed = false;

										}

									}

								} else {

									$in_person_pick_up_return_allowed = false; // Still false even if unrestricted as not populated

								}

							}

							if ( true == $in_person_invalid_times_fees_single_day_same_day_checks ) { // Specifically not an elseif as scenarios where both $in_person_invalid_times_fees_checks and $in_person_invalid_times_fees_single_day_same_day_checks occur

								if ( !empty( $in_person_pick_up_times_fees_single_day_same_day_array ) && !empty( $in_person_return_times_fees_single_day_same_day_array ) ) {

									$in_person_invalid_times_fees_count = 0;

									foreach ( $in_person_return_times_fees_single_day_same_day_array as $in_person_return_time => $in_person_return_fee ) {

										foreach ( $in_person_pick_up_times_fees_single_day_same_day_array as $in_person_pick_up_time => $in_person_pick_up_fee ) {

											if ( (int) $in_person_return_time < (int) $in_person_pick_up_time ) {

												++$in_person_invalid_times_fees_count;

											}

										}

									}

									if ( $in_person_invalid_times_fees_count > 0 ) {

										if ( 'restricted' == $in_person_pick_up_return_time_restrictions ) {

											$in_person_pick_up_return_allowed = false;

										}

									}

								} else {

									$in_person_pick_up_return_allowed = false; // Still false even if unrestricted as not populated

								}

							}

						} else {

							$in_person_pick_up_return_allowed = false; // Not in person pick up/return so false

						}

						// Output the rental form markup

						?>

						<div id="wcrp-rental-products-rental-form-<?php echo esc_html( $this->rental_form_id ); ?>" class="wcrp-rental-products-rental-form<?php echo ( 'yes' == $display_after_quantity && false == $product_sold_individually ? ' wcrp-rental-products-rental-form-after-quantity' : '' ); // Class not added if sold individually as no quantity field shown, CSS of the class would cause a gap without this condition ?>">
							<?php if ( 'period_selection' == $pricing_type ) { ?>
								<div id="wcrp-rental-products-rental-period-<?php echo esc_html( $this->rental_form_id ); ?>" class="wcrp-rental-products-rental-period">
									<label for="wcrp-rental-products-rental-period-select-<?php echo esc_html( $this->rental_form_id ); ?>">
										<?php echo esc_html( apply_filters( 'wcrp_rental_products_text_rental_period', get_option( 'wcrp_rental_products_text_rental_period' ) ) ); ?>
									</label>
									<select id="wcrp-rental-products-rental-period-select-<?php echo esc_html( $this->rental_form_id ); ?>" class="wcrp-rental-products-rental-period-select">
										<option value="" data-period="default">
											<?php
											$rental_period_selection_option_name_default_price = wc_price( ( wcrp_rental_products_is_rental_purchase( $product_id ) && true == $rent ? wc_get_price_to_display( $product, array( 'price' => str_replace( $price_decimal_separator, '.', get_post_meta( $product_id, '_wcrp_rental_products_rental_purchase_price', true ) ) ) ) : $product_price ) );
											$rental_period_selection_option_name_default = $pricing_period . ' ' . ( $pricing_period > 1 ? __( 'days', 'wcrp-rental-products' ) : __( 'day', 'wcrp-rental-products' ) ) . ( 'simple' == $product_type && 'off' == $advanced_pricing ? ' ' . __( '(', 'wcrp-rental-products' ) . $rental_period_selection_option_name_default_price . __( ')', 'wcrp-rental-products' ) : '' );
											if ( 'weeks' == $period_selection_option_labels ) {
												$period_to_week = $pricing_period / 7;
												if ( floor( $period_to_week ) == $period_to_week ) {
													$rental_period_selection_option_name_default = $period_to_week . ' ' . ( $period_to_week > 1 ? __( 'weeks', 'wcrp-rental-products' ) : __( 'week', 'wcrp-rental-products' ) ) . ( 'simple' == $product_type && 'off' == $advanced_pricing ? ' ' . __( '(', 'wcrp-rental-products' ) . $rental_period_selection_option_name_default_price . __( ')', 'wcrp-rental-products' ) : '' );
												}
											}
											echo wp_kses_post( $rental_period_selection_option_name_default );
											?>
										</option>
										<?php
										if ( !empty( $pricing_period_additional_selections_array ) ) {
											foreach ( $pricing_period_additional_selections_array as $rental_period_days => $rental_period_price ) {
												$rental_period_price = str_replace( $price_decimal_separator, '.', $rental_period_price );
												if ( 'simple' == $product_type && 'yes' == $taxes_enabled ) {
													if ( 'taxable' == $product_tax_status ) {
														if ( 'no' == $prices_include_tax && 'incl' == $tax_display_shop ) {
															$rental_period_price = $rental_period_price * ( 1 + ( $product_tax_rate / 100 ) );
														} elseif ( 'yes' == $prices_include_tax && 'excl' == $tax_display_shop ) {
															$rental_period_price = $rental_period_price / ( 1 + ( $product_tax_rate / 100 ) );
														}
													}
												}
												// Note that pricing isn't shown in the option label if a variable product or if advanced pricing enabled, as the prices for those get calculated later via JS, they don't come from pricing_period_additional_selections_array()
												?>
												<option value="" data-period="<?php echo esc_html( $rental_period_days ); ?>"<?php echo esc_html( ( false !== $rent_period ? ( $rent_period == $rental_period_days ? ' selected' : '' ) : '' ) ); ?>>
													<?php
													$rental_period_selection_option_name = $rental_period_days . ' ' . ( $rental_period_days > 1 ? __( 'days', 'wcrp-rental-products' ) : __( 'day', 'wcrp-rental-products' ) ) . ( 'simple' == $product_type && 'off' == $advanced_pricing ? ' ' . __( '(', 'wcrp-rental-products' ) . wc_price( $rental_period_price ) . __( ')', 'wcrp-rental-products' ) : '' );
													if ( 'weeks' == $period_selection_option_labels ) {
														$period_to_week = $rental_period_days / 7;
														if ( floor( $period_to_week ) == $period_to_week ) {
															$rental_period_selection_option_name = $period_to_week . ' ' . ( $period_to_week > 1 ? __( 'weeks', 'wcrp-rental-products' ) : __( 'week', 'wcrp-rental-products' ) ) . ( 'simple' == $product_type && 'off' == $advanced_pricing ? ' ' . __( '(', 'wcrp-rental-products' ) . wc_price( $rental_period_price ) . __( ')', 'wcrp-rental-products' ) : '' );
														}
													}
													echo wp_kses_post( $rental_period_selection_option_name );
													?>
												</option>
												<?php
											}
										}
										?>
									</select>
								</div>
							<?php } ?>
							<div id="wcrp-rental-products-rental-dates-wrap-<?php echo esc_html( $this->rental_form_id ); ?>" class="wcrp-rental-products-rental-dates-wrap">
								<label for="wcrp-rental-products-rental-dates-<?php echo esc_html( $this->rental_form_id ); ?>">
									<?php echo esc_html( apply_filters( 'wcrp_rental_products_text_rental_dates', get_option( 'wcrp_rental_products_text_rental_dates' ) ) ); ?>
								</label>
								<div id="wcrp-rental-products-rental-dates-parent-<?php echo esc_html( $this->rental_form_id ); ?>" class="wcrp-rental-products-rental-dates-parent">
									<input type="text" id="wcrp-rental-products-rental-dates-<?php echo esc_html( $this->rental_form_id ); ?>" class="wcrp-rental-products-rental-dates" name="wcrp_rental_products_rental_dates" placeholder="<?php echo esc_html( apply_filters( 'wcrp_rental_products_text_select_dates', get_option( 'wcrp_rental_products_text_select_dates' ) ) ); ?>" readonly="readonly" required>
								</div>
							</div>
							<?php
							if ( true == $in_person_pick_up_return_allowed ) {
								if ( 'next_day' == $in_person_return_date ) {
									// This notice is outside the wrap directly below intentionally as should be displayed before dates selected
									?>
										<div id="wcrp-rental-products-in-person-pick-up-return-select-dates-info-<?php echo esc_html( $this->rental_form_id ); ?>" class="wcrp-rental-products-disable-rental-start-end wcrp-rental-products-notice<?php echo esc_attr( ( 'yes' == $notices_use_woocommerce_classes ? ' woocommerce-info' : '' ) ); ?>">
											<?php esc_html_e( 'When selecting dates above, exclude the return date. The return date will be automatically calculated and shown below.', 'wcrp-rental-products' ); ?>
										</div>
									<?php
								}
								?>
								<div id="wcrp-rental-products-in-person-pick-up-return-wrap-<?php echo esc_html( $this->rental_form_id ); ?>" class="wcrp-rental-products-in-person-pick-up-return-wrap">
									<div id="wcrp-rental-products-in-person-pick-up-time-fee-<?php echo esc_html( $this->rental_form_id ); ?>" class="wcrp-rental-products-in-person-pick-up-time-fee">
										<label for="wcrp-rental-products-in-person-pick-up-time-fee-select-<?php echo esc_html( $this->rental_form_id ); ?>">
											<?php echo esc_html( apply_filters( 'wcrp_rental_products_text_pick_up_time', get_option( 'wcrp_rental_products_text_pick_up_time' ) ) ) . ' ' . esc_html__( 'on', 'wcrp-rental-products' ); ?> <span id="wcrp-rental-products-in-person-pick-up-time-fee-date-<?php echo esc_html( $this->rental_form_id ); ?>" class="wcrp-rental-products-in-person-pick-up-time-fee-date"></span>
										</label>
										<select id="wcrp-rental-products-in-person-pick-up-time-fee-select-<?php echo esc_html( $this->rental_form_id ); ?>" class="wcrp-rental-products-in-person-pick-up-time-fee-select">
											<?php
											if ( !empty( $in_person_pick_up_times_fees_array ) ) {
												// Similar to code in next select
												foreach ( $in_person_pick_up_times_fees_array as $in_person_pick_up_time => $in_person_pick_up_fee ) {
													$in_person_pick_up_fee = str_replace( $price_decimal_separator, '.', $in_person_pick_up_fee );
													if ( 'yes' == $taxes_enabled ) {
														if ( 'taxable' == $product_tax_status ) {
															if ( 'no' == $prices_include_tax && 'incl' == $tax_display_shop ) {
																$in_person_pick_up_fee = $in_person_pick_up_fee * ( 1 + ( $product_tax_rate / 100 ) );
															} elseif ( 'yes' == $prices_include_tax && 'excl' == $tax_display_shop ) {
																$in_person_pick_up_fee = $in_person_pick_up_fee / ( 1 + ( $product_tax_rate / 100 ) );
															}
														}
													}
													?>
													<option value="<?php echo esc_html( $in_person_pick_up_time ); ?>" data-fee="<?php echo esc_html( $in_person_pick_up_fee ); ?>"<?php echo ( false !== $in_person_pick_up_times_fees_default_selection && $in_person_pick_up_time == $in_person_pick_up_times_fees_default_selection ? ' selected' : '' ); ?>>
														<?php echo esc_html( WCRP_Rental_Products_Misc::four_digit_time_formatted( $in_person_pick_up_time ) ) . ( (float) $in_person_pick_up_fee > 0 ? ' ' . esc_html__( '(', 'wcrp-rental-products' ) . esc_html__( '+', 'wcrp-rental-products' ) . wp_kses_post( wc_price( $in_person_pick_up_fee ) ) . esc_html__( ')', 'wcrp-rental-products' ) : '' ); ?>
													</option>
													<?php
												}
											}
											?>
										</select>
										<?php
										if ( 'same_day' == $in_person_return_date && !empty( $in_person_pick_up_times_fees_single_day_same_day_array ) ) {
											// The select below is just here for JS to get the options and insert into the select above if only a single day selected when in person return date is same day
											?>
											<select id="wcrp-rental-products-in-person-pick-up-time-fee-single-day-select-<?php echo esc_html( $this->rental_form_id ); ?>" class="wcrp-rental-products-in-person-pick-up-time-fee-single-day-select">
												<?php
												// Similar to code in previous select
												foreach ( $in_person_pick_up_times_fees_single_day_same_day_array as $in_person_pick_up_time => $in_person_pick_up_fee ) {
													$in_person_pick_up_fee = str_replace( $price_decimal_separator, '.', $in_person_pick_up_fee );
													if ( 'yes' == $taxes_enabled ) {
														if ( 'taxable' == $product_tax_status ) {
															if ( 'no' == $prices_include_tax && 'incl' == $tax_display_shop ) {
																$in_person_pick_up_fee = $in_person_pick_up_fee * ( 1 + ( $product_tax_rate / 100 ) );
															} elseif ( 'yes' == $prices_include_tax && 'excl' == $tax_display_shop ) {
																$in_person_pick_up_fee = $in_person_pick_up_fee / ( 1 + ( $product_tax_rate / 100 ) );
															}
														}
													}
													?>
													<option value="<?php echo esc_html( $in_person_pick_up_time ); ?>" data-fee="<?php echo esc_html( $in_person_pick_up_fee ); ?>"<?php echo ( false !== $in_person_pick_up_times_fees_single_day_same_day_default_selection && $in_person_pick_up_time == $in_person_pick_up_times_fees_single_day_same_day_default_selection ? ' selected' : '' ); ?>>
														<?php echo esc_html( WCRP_Rental_Products_Misc::four_digit_time_formatted( $in_person_pick_up_time ) ) . ( (float) $in_person_pick_up_fee > 0 ? ' ' . esc_html__( '(', 'wcrp-rental-products' ) . esc_html__( '+', 'wcrp-rental-products' ) . wp_kses_post( wc_price( $in_person_pick_up_fee ) ) . esc_html__( ')', 'wcrp-rental-products' ) : '' ); ?>
													</option>
													<?php
												}
												?>
											</select>
											<?php
										}
										?>
									</div>
									<div id="wcrp-rental-products-in-person-return-time-fee-<?php echo esc_html( $this->rental_form_id ); ?>" class="wcrp-rental-products-in-person-return-time-fee">
										<label for="wcrp-rental-products-in-person-return-time-fee-select-<?php echo esc_html( $this->rental_form_id ); ?>">
											<?php echo esc_html( apply_filters( 'wcrp_rental_products_text_return_time', get_option( 'wcrp_rental_products_text_return_time' ) ) ) . ' ' . esc_html__( 'on', 'wcrp-rental-products' ); ?> <span id="wcrp-rental-products-in-person-return-time-fee-date-<?php echo esc_html( $this->rental_form_id ); ?>" class="wcrp-rental-products-in-person-return-time-fee-date"></span>
										</label>
										<select id="wcrp-rental-products-in-person-return-time-fee-select-<?php echo esc_html( $this->rental_form_id ); ?>" class="wcrp-rental-products-in-person-return-time-fee-select">
											<?php
											if ( !empty( $in_person_return_times_fees_array ) ) {
												// Similar to code in next select
												foreach ( $in_person_return_times_fees_array as $in_person_return_time => $in_person_return_fee ) {
													$in_person_return_fee = str_replace( $price_decimal_separator, '.', $in_person_return_fee );
													if ( 'yes' == $taxes_enabled ) {
														if ( 'taxable' == $product_tax_status ) {
															if ( 'no' == $prices_include_tax && 'incl' == $tax_display_shop ) {
																$in_person_return_fee = $in_person_return_fee * ( 1 + ( $product_tax_rate / 100 ) );
															} elseif ( 'yes' == $prices_include_tax && 'excl' == $tax_display_shop ) {
																$in_person_return_fee = $in_person_return_fee / ( 1 + ( $product_tax_rate / 100 ) );
															}
														}
													}
													?>
													<option value="<?php echo esc_html( $in_person_return_time ); ?>" data-fee="<?php echo esc_html( $in_person_return_fee ); ?>"<?php echo ( false !== $in_person_return_times_fees_default_selection && $in_person_return_time == $in_person_return_times_fees_default_selection ? ' selected' : '' ); ?>>
														<?php echo esc_html( WCRP_Rental_Products_Misc::four_digit_time_formatted( $in_person_return_time ) ) . ( (float) $in_person_return_fee > 0 ? ' ' . esc_html__( '(', 'wcrp-rental-products' ) . esc_html__( '+', 'wcrp-rental-products' ) . wp_kses_post( wc_price( $in_person_return_fee ) ) . esc_html__( ')', 'wcrp-rental-products' ) : '' ); ?>
													</option>
													<?php
												}
											}
											?>
										</select>
										<?php
										if ( 'same_day' == $in_person_return_date && !empty( $in_person_return_times_fees_single_day_same_day_array ) ) {
											// The select below is just here for JS to get the options and insert into the select above if only a single day selected when in person return date is same day
											?>
											<select id="wcrp-rental-products-in-person-return-time-fee-single-day-select-<?php echo esc_html( $this->rental_form_id ); ?>" class="wcrp-rental-products-in-person-return-time-fee-single-day-select">
												<?php
												// Similar to code in previous select
												foreach ( $in_person_return_times_fees_single_day_same_day_array as $in_person_return_time => $in_person_return_fee ) {
													$in_person_return_fee = str_replace( $price_decimal_separator, '.', $in_person_return_fee );
													if ( 'yes' == $taxes_enabled ) {
														if ( 'taxable' == $product_tax_status ) {
															if ( 'no' == $prices_include_tax && 'incl' == $tax_display_shop ) {
																$in_person_return_fee = $in_person_return_fee * ( 1 + ( $product_tax_rate / 100 ) );
															} elseif ( 'yes' == $prices_include_tax && 'excl' == $tax_display_shop ) {
																$in_person_return_fee = $in_person_return_fee / ( 1 + ( $product_tax_rate / 100 ) );
															}
														}
													}
													?>
													<option value="<?php echo esc_html( $in_person_return_time ); ?>" data-fee="<?php echo esc_html( $in_person_return_fee ); ?>"<?php echo ( false !== $in_person_return_times_fees_single_day_same_day_default_selection && $in_person_return_time == $in_person_return_times_fees_single_day_same_day_default_selection ? ' selected' : '' ); ?>>
														<?php echo esc_html( WCRP_Rental_Products_Misc::four_digit_time_formatted( $in_person_return_time ) ) . ( (float) $in_person_return_fee > 0 ? ' ' . esc_html__( '(', 'wcrp-rental-products' ) . esc_html__( '+', 'wcrp-rental-products' ) . wp_kses_post( wc_price( $in_person_return_fee ) ) . esc_html__( ')', 'wcrp-rental-products' ) : '' ); ?>
													</option>
													<?php
												}
												?>
											</select>
											<?php
										}
										?>
									</div>
								</div>
								<?php
							}
							// Available rental stock totals, notices, and spinner, ensures everything bunched together nicely, e.g. notices all together (excluding the in person pick up/return next day notice further above, which has to be after the rental dates field as directly associated with it)
							if ( 'yes' == $available_rental_stock_totals ) {
								?>
								<div id="wcrp-rental-products-available-rental-stock-totals-<?php echo esc_html( $this->rental_form_id ); ?>" class="wcrp-rental-products-available-rental-stock-totals stock"></div>
								<?php
							}
							if ( false == $in_person_pick_up_return_allowed && 'yes' == $in_person_pick_up_return ) {
								// Displays an unavailable message if the product is an in person pick up/return but is not allowed e.g. if no return dates lower than pick up dates
								?>
								<div id="wcrp-rental-products-in-person-pick-up-return-unavailable-<?php echo esc_html( $this->rental_form_id ); ?>" class="wcrp-rental-products-in-person-pick-up-return-unavailable wcrp-rental-products-notice<?php echo esc_attr( ( 'yes' == $notices_use_woocommerce_classes ? ' woocommerce-info' : '' ) ); ?>">
									<?php echo esc_html( apply_filters( 'wcrp_rental_products_text_in_person_pick_up_return', get_option( 'wcrp_rental_products_text_in_person_pick_up_return' ) ) ) . ' ' . esc_html__( 'is unavailable', 'wcrp-rental-products' ) . esc_html__( '.', 'wcrp-rental-products' ); ?>
								</div>
								<?php
							}
							if ( 'yes' == $start_end_notices ) {
								$deprecated = apply_filters_deprecated( 'wcrp_rental_products_text_disable_rental_start_end_dates_notice', array( get_option( 'wcrp_rental_products_text_disable_rental_start_end_dates_notice_text' ) ), '5.0.0', 'wcrp_rental_products_text_disable_rental_start_end_notice' ); // Note the get_option name looks wrong here (_text suffix) but it was this previously by accident, see upgrade conditions for 5.0.0
								$deprecated = apply_filters_deprecated( 'wcrp_rental_products_text_disable_rental_start_end_days_notice', array( get_option( 'wcrp_rental_products_text_disable_rental_start_end_days_notice_text' ) ), '5.0.0', 'wcrp_rental_products_text_disable_rental_start_end_notice' ); // Note the get_option name looks wrong here (_text suffix) but it was this previously by accident, see upgrade conditions for 5.0.0
								$start_end_notices_to_show = false;
								$start_end_notices_dates = false;
								$start_end_notices_days = false;
								if ( !empty( $disable_rental_start_end_dates_combined_string ) ) {
									$start_end_notices_to_show = true; // Notices to show as using disable rental start/end dates
									$start_end_notices_dates = true;
								}
								if ( !empty( $disable_rental_start_end_days ) || '0' == $disable_rental_start_end_days ) {
									$start_end_notices_to_show = true; // Notices to show as using disable rental start/end days
									$start_end_notices_days = true;
								}
								if ( true == $start_end_notices_to_show ) {
									?>
									<div id="wcrp-rental-products-disable-rental-start-end-<?php echo esc_html( $this->rental_form_id ); ?>" class="wcrp-rental-products-disable-rental-start-end wcrp-rental-products-notice<?php echo esc_attr( ( 'yes' == $notices_use_woocommerce_classes ? ' woocommerce-info' : '' ) ); ?>">
										<?php
										if ( true == $start_end_notices_dates ) {
											echo esc_html( apply_filters( 'wcrp_rental_products_text_disable_rental_start_end_notice', get_option( 'wcrp_rental_products_text_disable_rental_start_end_notice' ) ) );
										}
										if ( true == $start_end_notices_dates && true == $start_end_notices_days ) {
											echo ' '; // This is not a <br> as line break looks odd on small width devices e.g. mobile
										}
										if ( true == $start_end_notices_days ) {
											$disable_rental_start_end_days_notice = '';
											if ( 'start' == $disable_rental_start_end_days_type ) { // If start type
												$disable_rental_start_end_days_notice = esc_html( apply_filters( 'wcrp_rental_products_text_disable_rental_start_notice', get_option( 'wcrp_rental_products_text_disable_rental_start_notice' ) ) );
											} elseif ( 'end' == $disable_rental_start_end_days_type ) { // If end type
												$disable_rental_start_end_days_notice = esc_html( apply_filters( 'wcrp_rental_products_text_disable_rental_end_notice', get_option( 'wcrp_rental_products_text_disable_rental_end_notice' ) ) );
											} else { // If start_end type
												if ( false == $start_end_notices_dates ) { // Below line not shown if this is true as already shown by first condition above
													$disable_rental_start_end_days_notice = esc_html( apply_filters( 'wcrp_rental_products_text_disable_rental_start_end_notice', get_option( 'wcrp_rental_products_text_disable_rental_start_end_notice' ) ) );
												}
											}
											echo esc_html( $disable_rental_start_end_days_notice );
										}
										?>
									</div>
									<?php
								}
							}
							?>
							<div id="wcrp-rental-products-rental-totals-<?php echo esc_html( $this->rental_form_id ); ?>" class="wcrp-rental-products-rental-totals wcrp-rental-products-notice<?php echo esc_attr( ( 'yes' == $notices_use_woocommerce_classes ? ' woocommerce-info' : '' ) ); ?>">
								<?php
								// Below is coded to all be output on one line otherwise browsers can add a visual space between currency symbols that should not have spaces even though there isn't a space in the code
								echo '<strong>';
								echo wp_kses_post( apply_filters( 'wcrp_rental_products_rental_form_total_prefix', esc_html__( 'Total:', 'wcrp-rental-products' ) . ' ' ) ) . ( in_array( $currency_position, array( 'left', 'left_space' ) ) ? wp_kses_post( ( 'left' == $currency_position ? $currency_symbol : $currency_symbol . ' ' ) ) : '' ) . '<span id="wcrp-rental-products-total-price-' . esc_html( $this->rental_form_id ) . '" class="wcrp-rental-products-total-price"></span>' . ( in_array( $currency_position, array( 'right', 'right_space' ) ) ? wp_kses_post( ( 'right' == $currency_position ? $currency_symbol : ' ' . $currency_symbol ) ) : '' );
								if ( 'period_selection' !== $pricing_type ) {
									// If period selection the days isn't shown as they have already been selected them and visible on the product page + if was used would require further conditions/calculations to show as weeks if the rental form period selection option labels setting is weeks, etc, etc
									if ( true == apply_filters( 'wcrp_rental_products_rental_form_total_days_parenthesis', true ) ) {
										echo ' ';
										esc_html_e( '(', 'wcrp-rental-products' );
									}
									echo '<span id="wcrp-rental-products-total-days-' . esc_html( $this->rental_form_id ) . '" class="wcrp-rental-products-total-days"></span>';
									if ( true == apply_filters( 'wcrp_rental_products_rental_form_total_days_parenthesis', true ) ) {
										esc_html_e( ')', 'wcrp-rental-products' );
									}
								}
								echo '</strong>';
								require_once ABSPATH . 'wp-admin/includes/plugin.php';
								if ( is_plugin_active( 'woocommerce-product-addons/woocommerce-product-addons.php' ) ) {
									echo '<div id="wcrp-rental-products-excludes-addons-' . esc_html( $this->rental_form_id ) . '" class="wcrp-rental-products-excludes-addons">' . esc_html__( 'Excluding any selected add-ons', 'wcrp-rental-products' ) . esc_html( ( 'yes' == $multiply_addons_total_by_number_of_days_selected ? ' ' . __( '(', 'wcrp-rental-products' ) . __( 'priced per day', 'wcrp-rental-products' ) . __( ')', 'wcrp-rental-products' ) : '' ) ) . '</div>';
								}
								if ( true == $in_person_pick_up_return_allowed ) {
									echo '<div id="wcrp-rental-products-excludes-in-person-pick-up-return-fees-' . esc_html( $this->rental_form_id ) . '" class="wcrp-rental-products-excludes-in-person-pick-up-return-fees">' . esc_html__( 'Excluding any selected pick up/return fees', 'wcrp-rental-products' ) . '</div>';
								}
								if ( 'yes' == $return_days_display ) {
									echo '<div id="wcrp-rental-products-rental-return-within-' . esc_html( $this->rental_form_id ) . '" class="wcrp-rental-products-rental-return-within">' . esc_html( apply_filters( 'wcrp_rental_products_text_rental_return_within', get_option( 'wcrp_rental_products_text_rental_return_within' ) ) ) . ' <span id="wcrp-rental-products-rental-return-within-days-' . esc_html( $this->rental_form_id ) . '" class="wcrp-rental-products-rental-return-within-days"></span> ' . esc_html__( 'days', 'wcrp-rental-products' ) . '</div>';
								}
								?>
							</div>
							<div id="wcrp-rental-products-spinner-<?php echo esc_html( $this->rental_form_id ); ?>" class="wcrp-rental-products-spinner"></div>
						</div>
						<input type="hidden" id="wcrp-rental-products-cart-item-validation-<?php echo esc_html( $this->rental_form_id ); ?>" name="wcrp_rental_products_cart_item_validation">
						<?php
						// For cart item timestamp it does not matter this might get cached as a cached timestamp will be before the real timestamp, if the modified date of the product is higher than the timestamp the cart will show a notice and stop checkout. If caching is used and a product is modified (which should trigger a product cache purge) the cached timestamp would be updated to the time of the first page load after the cache is purged (and therefore that timestamp is not before the modified date when added to cart so wouldn't show a notice, but if users already have the product in cart it would which is correct). Separately, current_time() is used instead of time() to ensure the timestamp takes into account the timezone settings set in WordPress, otherwise this could be lower than the timestamp got later in the cart check condition for the last updated date of the product, false parameter is used to get the timestamp based off the timezone settings
						?>
						<input type="hidden" id="wcrp-rental-products-cart-item-timestamp-<?php echo esc_html( $this->rental_form_id ); ?>" name="wcrp_rental_products_cart_item_timestamp" value="<?php echo esc_html( current_time( 'timestamp', false ) ); ?>">
						<input type="hidden" id="wcrp-rental-products-cart-item-price-<?php echo esc_html( $this->rental_form_id ); ?>" name="wcrp_rental_products_cart_item_price"><?php // When value populated via JS this can be any number of decimal places, cart deals with any rounding required ?>
						<input type="hidden" id="wcrp-rental-products-rent-from-<?php echo esc_html( $this->rental_form_id ); ?>" name="wcrp_rental_products_rent_from">
						<input type="hidden" id="wcrp-rental-products-rent-to-<?php echo esc_html( $this->rental_form_id ); ?>" name="wcrp_rental_products_rent_to">
						<input type="hidden" id="wcrp-rental-products-start-days-threshold-<?php echo esc_html( $this->rental_form_id ); ?>" name="wcrp_rental_products_start_days_threshold" value="<?php echo esc_html( $start_days_threshold ); ?>">
						<input type="hidden" id="wcrp-rental-products-return-days-threshold-<?php echo esc_html( $this->rental_form_id ); ?>" name="wcrp_rental_products_return_days_threshold" value="<?php echo esc_html( $return_days_threshold ); ?>">
						<input type="hidden" id="wcrp-rental-products-advanced-pricing-<?php echo esc_html( $this->rental_form_id ); ?>" name="wcrp_rental_products_advanced_pricing" value="<?php echo esc_html( $advanced_pricing ); ?>">
						<?php
						if ( true == $in_person_pick_up_return_allowed ) {
							?>
							<input type="hidden" id="wcrp-rental-products-in-person-pick-up-return-<?php echo esc_html( $this->rental_form_id ); ?>" name="wcrp_rental_products_in_person_pick_up_return" value="yes">
							<input type="hidden" id="wcrp-rental-products-in-person-pick-up-date-<?php echo esc_html( $this->rental_form_id ); ?>" name="wcrp_rental_products_in_person_pick_up_date">
							<input type="hidden" id="wcrp-rental-products-in-person-pick-up-time-<?php echo esc_html( $this->rental_form_id ); ?>" name="wcrp_rental_products_in_person_pick_up_time">
							<input type="hidden" id="wcrp-rental-products-in-person-pick-up-fee-<?php echo esc_html( $this->rental_form_id ); ?>" name="wcrp_rental_products_in_person_pick_up_fee"><?php // When value populated via JS this can be any number of decimal places, cart deals with any rounding required ?>
							<input type="hidden" id="wcrp-rental-products-in-person-return-date-<?php echo esc_html( $this->rental_form_id ); ?>" name="wcrp_rental_products_in_person_return_date">
							<input type="hidden" id="wcrp-rental-products-in-person-return-date-type-<?php echo esc_html( $this->rental_form_id ); ?>" name="wcrp_rental_products_in_person_return_date_type" value="<?php echo esc_html( $in_person_return_date ); ?>">
							<input type="hidden" id="wcrp-rental-products-in-person-return-time-<?php echo esc_html( $this->rental_form_id ); ?>" name="wcrp_rental_products_in_person_return_time">
							<input type="hidden" id="wcrp-rental-products-in-person-return-fee-<?php echo esc_html( $this->rental_form_id ); ?>" name="wcrp_rental_products_in_person_return_fee"><?php // When value populated via JS this can be any number of decimal places, cart deals with any rounding required ?>
							<?php
						}
						wp_nonce_field( 'wcrp_rental_products_rental_form', 'wcrp_rental_products_rental_form_nonce' );

						// Output the rental form script

						?>

						<script>
							jQuery( document ).ready( function( $ ) {

								<?php // Initial variables ?>

								let addRentalProductsPopup = false;
								let rentalFormUpdateAjaxRequestTimeout;
								let rentalFormUpdateAjaxRequestDelay = 1000;

								<?php

								if ( current_user_can( 'manage_woocommerce' ) ) {

									?>

									if ( window.name.startsWith( 'wcrp-rental-products-add-rental-products-popup-' ) ) {

										addRentalProductsPopup = true;
										addRentalProductsPopupOrderId = window.name.slice( window.name.lastIndexOf( '-' ) + 1 );

									}

									<?php

								}

								?>

								<?php // Functions ?>

								function rentalFormAddDayToIsoDate( isoDate ) {

									var isoDate = new Date( isoDate );
									isoDate = new Date( isoDate.setDate( isoDate.getDate() + 1 ) );
									isoDate = new Date( isoDate.getTime() - ( isoDate.getTimezoneOffset() * 60 * 1000 ) );
									return isoDate.toISOString().split('T')[0];

								}

								function rentalFormAddToStatus( status ) {

									<?php // If adding to cart ?>

									if ( addRentalProductsPopup == false ) {

										<?php // Opacity gets added/removed below as when choosing different variations standard WooCommerce functionality adds an opacity 0.2 with a disabled class but that style is not present for add to cart buttons on simple products so we just set here ?>

										if ( 'enable' == status ) {

											<?php if ( in_array( 'rental_form_add_to_status_enable_delay', $advanced_configuration ) ) { ?>

												setTimeout( () => {
													$( '#wcrp-rental-products-rental-form-wrap-<?php echo esc_html( $this->rental_form_id ); ?>' ).find( '.cart button[type="submit"]' ).css( 'opacity', '1' ).prop( 'disabled', false );
												}, '1000' );

											<?php } else { ?>

												$( '#wcrp-rental-products-rental-form-wrap-<?php echo esc_html( $this->rental_form_id ); ?>' ).find( '.cart button[type="submit"]' ).css( 'opacity', '1' ).prop( 'disabled', false );

											<?php } ?>

										} else {

											if ( 'disable' == status ) {

												$( '#wcrp-rental-products-rental-form-wrap-<?php echo esc_html( $this->rental_form_id ); ?>' ).find( '.cart button[type="submit"]' ).css( 'opacity', '0.2' ).prop( 'disabled', true );

											}

										}

									} else {

										<?php

										// If adding to order

										if ( current_user_can( 'manage_woocommerce' ) ) {

											?>

											$( '#wcrp-rental-products-rental-form-wrap-<?php echo esc_html( $this->rental_form_id ); ?>' ).find( '.cart button[type="submit"]' ).hide();
											$( '#wcrp-rental-products-add-to-order-<?php echo esc_html( $this->rental_form_id ); ?>' ).remove();

											if ( 'enable' == status ) {

												$( '<a href="#" id="wcrp-rental-products-add-to-order-<?php echo esc_html( $this->rental_form_id ); ?>" class="wcrp-rental-products-add-to-order single_add_to_cart_button button <?php echo esc_attr(  wc_wp_theme_get_element_class_name( 'button' ) ); ?>"><?php esc_html_e( 'Add to order', 'wcrp-rental-products' ); ?> <?php esc_html_e( '#', 'wcrp-rental-products' ); ?>' + addRentalProductsPopupOrderId + '</a>' ).insertBefore( $( '#wcrp-rental-products-rental-form-wrap-<?php echo esc_html( $this->rental_form_id ); ?>' ).find( '.cart button[type="submit"]' ) );

											}

											<?php

										}

										?>

									}

								}

								function rentalFormAdvancedPricingAjaxRequest( advancedPricingTotal, advancedPricingCartItemPrice ) {

									var rentalFormAdvancedPricingAjaxRequestData = {
										'action': 'wcrp_rental_products_rental_form_advanced_pricing',
										'nonce': '<?php echo esc_html( wp_create_nonce( 'wcrp_rental_products_rental_form_advanced_pricing' ) ); ?>',
										'data' : {
											'quantity': qty,
											<?php if ( 'variable' == $product_type ) { ?>
												'product_id': $( '#wcrp-rental-products-rental-form-wrap-<?php echo esc_html( $this->rental_form_id ); ?>' ).find( '.cart input[name="product_id"]' ).val(),
												'variation_id': $( '#wcrp-rental-products-rental-form-wrap-<?php echo esc_html( $this->rental_form_id ); ?>' ).find( '.cart input[name="variation_id"]' ).val(),
											<?php } else { ?>
												'product_id': $( '#wcrp-rental-products-rental-form-wrap-<?php echo esc_html( $this->rental_form_id ); ?>' ).find( '.cart button[name="add-to-cart"]' ).val(),
											<?php } ?>
											'rent_from': $( '#wcrp-rental-products-rent-from-<?php echo esc_html( $this->rental_form_id ); ?>' ).val(),
											'rent_to': $( '#wcrp-rental-products-rent-to-<?php echo esc_html( $this->rental_form_id ); ?>' ).val(),
											'return_days_threshold': $( '#wcrp-rental-products-return-days-threshold-<?php echo esc_html( $this->rental_form_id ); ?>' ).val(),
											'total': advancedPricingTotal,
											'cart_item_price': advancedPricingCartItemPrice,
											<?php
											if ( !empty( $_GET ) ) {
												foreach ( $_GET as $get_key => $get_value ) {
													?>
													'_GET_<?php echo esc_html( $get_key ); ?>': '<?php echo esc_html( $get_value ); ?>', <?php // Adds _GET_ data, this is each $_GET, useful for advanced pricing calculations based off ?rent_period=x, etc ?>
													<?php
												}
											}
											?>
										},
									};

									var rentalFormAdvancedPricingAjaxRequest = jQuery.ajax({
										'url':		'<?php echo esc_html( admin_url( 'admin-ajax.php' ) ); ?>',
										'method':	'POST',
										'data':		rentalFormAdvancedPricingAjaxRequestData,
									});

									rentalFormAdvancedPricingAjaxRequest.done( function( rentalFormAdvancedPricingAjaxRequestResponse ) {

										if ( rentalFormAdvancedPricingAjaxRequestResponse !== 'error' ) {

											var rentalFormAdvancedPricingAjaxRequestResponse = JSON.parse( rentalFormAdvancedPricingAjaxRequestResponse );

											<?php // Update total price and cart item price hidden fields ?>

											$( '#wcrp-rental-products-total-price-<?php echo esc_html( $this->rental_form_id ); ?>' ).text( parseFloat( rentalFormAdvancedPricingAjaxRequestResponse['total'] ).toFixed( <?php echo esc_html( $price_decimals ); ?> ).replace( '.', '<?php echo wp_kses_post( $price_decimal_separator ); ?>' ) );

											$( '#wcrp-rental-products-cart-item-price-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( rentalFormAdvancedPricingAjaxRequestResponse['cart_item_price'] );

											<?php // Update cart item validation, needs to be done as above hidden fields changed so needs new validation, the rentalFormUpdateCartItemValidation() further below does not apply as the advanced pricing is a later AJAX request ?>

											rentalFormUpdateCartItemValidation();

											<?php // Show rental totals ?>

											$( '#wcrp-rental-products-rental-totals-<?php echo esc_html( $this->rental_form_id ); ?>' ).attr( 'style', 'display: block !important;' ); <?php // Not .show() as !important required (see related public CSS for info) ?>

											<?php // Enable add to cart ?>

											rentalFormAddToStatus( 'enable' );

										} else {

											<?php // Error handling ?>

											rentalFormReset();
											alert( '<?php esc_html_e( 'Sorry, there was an error in price calculation, please try refreshing the page and try again.', 'wcrp-rental-products' ); ?>' );

										}

									});

								}

								function rentalFormConvertPriceToIncExcTax( price ) {

									<?php

									// Convert price to inc/exc tax as per tax settings, this function is generally used for prices which haven't gone through wc_get_price_to_display(), e.g. where originating from WCRP_Rental_Products_Misc::value_colon_price_pipe_explode() which doesn't converted it, see comments in that fuction why not

									if ( 'yes' == $taxes_enabled ) {

										if ( 'taxable' == $product_tax_status ) {

											if ( 'no' == $prices_include_tax && 'incl' == $tax_display_shop ) {

												?>

												var price = parseFloat( price ) * ( 1 + ( <?php echo esc_html( $product_tax_rate ); ?> / 100 ) );

												<?php

											} elseif ( 'yes' == $prices_include_tax && 'excl' == $tax_display_shop ) {

												?>

												var price = parseFloat( price ) / ( 1 + ( <?php echo esc_html( $product_tax_rate ); ?> / 100 ) );

												<?php

											}

										}

									}

									?>

									return parseFloat( price );

								}

								function rentalFormReset() {

									<?php // Disable add to cart ?>

									rentalFormAddToStatus( 'disable' );

									<?php // Message/notices to hide on reset ?>

									$( '#wcrp-rental-products-availability-checker-auto-population-information-<?php echo esc_html( $this->rental_form_id ); ?>' ).hide();
									$( '#wcrp-rental-products-available-rental-stock-totals-<?php echo esc_html( $this->rental_form_id ); ?>' ).attr( 'style', 'display: none !important;' ); <?php // Not .hide() as !important required (see related public CSS for info) ?>
									$( '#wcrp-rental-products-rental-totals-<?php echo esc_html( $this->rental_form_id ); ?>' ).attr( 'style', 'display: none !important;' ); <?php // Not .hide() as !important required (see related public CSS for info) ?>

									<?php // Hidden fields, just the dynamic ones e.g. hidden fields in above HTML markup with static values aren't needed to be reset as always the same value ?>

									$( '#wcrp-rental-products-cart-item-validation-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( '' );
									$( '#wcrp-rental-products-cart-item-price-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( '' );
									$( '#wcrp-rental-products-rent-from-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( '' );
									$( '#wcrp-rental-products-rent-to-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( '' );
									<?php if ( true == $in_person_pick_up_return_allowed ) { ?>
										$( '#wcrp-rental-products-in-person-pick-up-date-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( '' );
										$( '#wcrp-rental-products-in-person-pick-up-time-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( '' );
										$( '#wcrp-rental-products-in-person-pick-up-fee-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( '' );
										$( '#wcrp-rental-products-in-person-return-date-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( '' );
										$( '#wcrp-rental-products-in-person-return-time-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( '' );
										$( '#wcrp-rental-products-in-person-return-fee-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( '' );
									<?php } ?>

									<?php if ( 'period_selection' == $pricing_type ) { ?>

										rentalFormUpdateRentalPeriodOptions();

									<?php } ?>

								}

								<?php if ( 'period_selection' == $pricing_type ) { ?>

									function rentalFormUpdateRentalPeriodOptions() {

										<?php // This runs on page load as rentalFormUpdate() is called which in turn calls rentalFormReset() which calls this, this is also called upon variation selection ?>

										$( '#wcrp-rental-products-rental-period-select-<?php echo esc_html( $this->rental_form_id ); ?> option' ).each( function() {

											<?php

											if ( false == is_preview() ) {

												?>

												var rentalPeriodOptionUrl = '<?php echo esc_url( $product_url ) . ( WCRP_Rental_Products_Misc::string_contains( $product_url, '?' ) ? '&' : '?' ); ?>';

												<?php

											} else {

												?>

												var rentalPeriodOptionUrl = '<?php echo esc_url( $product_url_preview ); ?>&';

												<?php

											}

											?>

											if ( 'default' == $( this ).attr( 'data-period' ) ) {

												rentalPeriodOptionUrl += '<?php echo ( true == $rent ? 'rent=1&' : '' ); ?>rent_period_qty=' + $( '#wcrp-rental-products-rental-form-wrap-<?php echo esc_html( $this->rental_form_id ); ?>' ).find( '.cart input[name="quantity"]' ).val();

											} else {

												rentalPeriodOptionUrl += '<?php echo ( true == $rent ? 'rent=1&' : '' ); ?>rent_period=' + $( this ).attr( 'data-period' );
												rentalPeriodOptionUrl += '&rent_period_qty=' + $( '#wcrp-rental-products-rental-form-wrap-<?php echo esc_html( $this->rental_form_id ); ?>' ).find( '.cart input[name="quantity"]' ).val();

											}

											<?php if ( 'variable' == $product_type ) { ?>

												$( '#wcrp-rental-products-rental-form-wrap-<?php echo esc_html( $this->rental_form_id ); ?> .variations .value select' ).each( function() {

													if ( $( this ).val() !== '' ) {

														rentalPeriodOptionUrl += '&' + $( this ).attr( 'data-attribute_name' ) + '=' + $( this ).val();

													}

												});

											<?php } ?>

											$( this ).val( rentalPeriodOptionUrl );

										});

									}

									$( '.variations_form' ).on( 'woocommerce_variation_select_change', function() { <?php // woocommerce_variation_select_change not show_variation as ensures it can be reset if not a bonafide variation e.g. if variation option not selected ?>

										rentalFormUpdateRentalPeriodOptions();

									});

									<?php if ( false !== $rent_period_qty ) { ?>

										$( '#wcrp-rental-products-rental-form-wrap-<?php echo esc_html( $this->rental_form_id ); ?>' ).find( '.cart input[name="quantity"]' ).val( '<?php echo esc_html( $rent_period_qty ); ?>' );

									<?php

									}

								}

								?>

								function rentalFormUpdate() {

									<?php // This function runs on page load and when other actions are triggered such as quantity field change, variation selection, etc ?>

									<?php // Show spinner ?>

									$( '#wcrp-rental-products-spinner-<?php echo esc_html( $this->rental_form_id ); ?>' ).fadeIn();

									<?php // Reset rental form ?>

									rentalFormReset();

									<?php

									// Set/remove qty field max attribute conditionally

									if ( 'simple' == $product_type ) {

										// If a simple product, note there is no check if product is sold individually as in this scenario the qty field is hidden and while the max will have been updated by the below it cannot be changed from 1 anyway

										$qty_max_set_rental_stock_simple_product = get_post_meta( $product_id, '_wcrp_rental_products_rental_stock', true );
										$qty_max_set_rental_stock_simple_product = ( '' !== $qty_max_set_rental_stock_simple_product ? $qty_max_set_rental_stock_simple_product : PHP_INT_MAX );

										if ( PHP_INT_MAX == $qty_max_set_rental_stock_simple_product ) {

											?>

											$( '#wcrp-rental-products-rental-form-wrap-<?php echo esc_html( $this->rental_form_id ); ?>' ).find( '.cart input[name="quantity"]' ).removeAttr( 'max' );

											<?php

										} else {

											?>

											$( '#wcrp-rental-products-rental-form-wrap-<?php echo esc_html( $this->rental_form_id ); ?>' ).find( '.cart input[name="quantity"]' ).attr( 'max', '<?php echo esc_html( $qty_max_set_rental_stock_simple_product ); ?>' );

											<?php

										}

									} else {

										// If not a simple product then max qty is removed, this is because on page load WooCommerce sets it to the purchasable stock level, for variables the rental stock level isn't available to set it as this is determined based on the variation rental stock meta which is got after the options, dates, etc selected via the rental form update AJAX

										?>

										$( '#wcrp-rental-products-rental-form-wrap-<?php echo esc_html( $this->rental_form_id ); ?>' ).find( '.cart input[name="quantity"]' ).removeAttr( 'max' );

										<?php

									}

									// On initial page load the rentalFormCalendar won't be instantiated yet so we don't attempt to use it or will cause JS errors, for availability checker auto population on page load these end up populated as the dates get set on rentalFormCalendar render and then the rentalFormCalendar select function triggers this rentalFormUpdate() and at that point rentalFormCalendar is instantiated and these get set

									?>

									if ( undefined !== rentalFormCalendar ) {

										<?php

										// Format dates in hidden fields

										if ( '1' == $minimum_days && '1' == $maximum_days ) {

											// Note that rentalFormCalendar.getEndDate() note available in this scenario as singular date, hence rentalFormCalendar.getStartDate() used for both

											?>

											if ( null !== rentalFormCalendar.getStartDate() ) {

												$( '#wcrp-rental-products-rent-from-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( rentalFormCalendar.getStartDate().format( 'YYYY-MM-DD' ) );
												$( '#wcrp-rental-products-rent-to-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( rentalFormCalendar.getStartDate().format( 'YYYY-MM-DD' ) );

												<?php

												if ( true == $in_person_pick_up_return_allowed ) {

													?>

													$( '#wcrp-rental-products-in-person-pick-up-date-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( rentalFormCalendar.getStartDate().format( 'YYYY-MM-DD' ) );

													<?php

													if ( 'same_day' == $in_person_return_date ) {

														?>

														$( '#wcrp-rental-products-in-person-return-date-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( rentalFormCalendar.getStartDate().format( 'YYYY-MM-DD' ) );

														<?php

													} else {

														?>

														$( '#wcrp-rental-products-in-person-return-date-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( rentalFormAddDayToIsoDate( rentalFormCalendar.getStartDate().format( 'YYYY-MM-DD' ) ) );

														<?php

													}

												}

												?>

											}

											<?php

										} else {

											// The 2 null !== conditions below were previously 1 line as if ( null !== rentalFormCalendar.getStartDate() && null !== rentalFormCalendar.getEndDate() ) { however on some server setups the markup would change && to &#038;&#038; so we've split out the conditions

											?>

											if ( null !== rentalFormCalendar.getStartDate() ) {

												if ( null !== rentalFormCalendar.getEndDate() ) {

													$( '#wcrp-rental-products-rent-from-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( rentalFormCalendar.getStartDate().format( 'YYYY-MM-DD' ) );
													$( '#wcrp-rental-products-rent-to-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( rentalFormCalendar.getEndDate().format( 'YYYY-MM-DD' ) );

													<?php

													if ( true == $in_person_pick_up_return_allowed ) {

														?>

														$( '#wcrp-rental-products-in-person-pick-up-date-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( rentalFormCalendar.getStartDate().format( 'YYYY-MM-DD' ) );

														<?php

														if ( 'same_day' == $in_person_return_date ) {

															?>

															$( '#wcrp-rental-products-in-person-return-date-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( rentalFormCalendar.getEndDate().format( 'YYYY-MM-DD' ) );

															<?php

														} else {

															?>

															$( '#wcrp-rental-products-in-person-return-date-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( rentalFormAddDayToIsoDate( rentalFormCalendar.getEndDate().format( 'YYYY-MM-DD' ) ) );

															<?php

														}

													}

													?>

												}

											}

											<?php

										}

										// If in person pick up/return allowed then set the hidden fields to be as per the default selections at page load

										if ( true == $in_person_pick_up_return_allowed ) {

											?>

											if ( null !== rentalFormCalendar.getStartDate() ) {

												$( '#wcrp-rental-products-in-person-pick-up-time-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( $( '#wcrp-rental-products-in-person-pick-up-time-fee-select-<?php echo esc_html( $this->rental_form_id ); ?> option:selected' ).val() );
												$( '#wcrp-rental-products-in-person-pick-up-fee-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( $( '#wcrp-rental-products-in-person-pick-up-time-fee-select-<?php echo esc_html( $this->rental_form_id ); ?> option:selected' ).attr( 'data-fee' ) );
												$( '#wcrp-rental-products-in-person-return-time-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( $( '#wcrp-rental-products-in-person-return-time-fee-select-<?php echo esc_html( $this->rental_form_id ); ?> option:selected' ).val() );
												$( '#wcrp-rental-products-in-person-return-fee-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( $( '#wcrp-rental-products-in-person-return-time-fee-select-<?php echo esc_html( $this->rental_form_id ); ?> option:selected' ).attr( 'data-fee' ) );

											}

											<?php

										}

										?>

									}

									function rentalFormUpdateAjaxRequest() {

										var rentalFormUpdateAjaxRequestData = {
											'action': 'wcrp_rental_products_rental_form_update',
											'nonce': '<?php echo esc_html( wp_create_nonce( 'wcrp_rental_products_rental_form_update' ) ); ?>',
											'qty': $( '#wcrp-rental-products-rental-form-wrap-<?php echo esc_html( $this->rental_form_id ); ?>' ).find( '.cart input[name="quantity"]' ).val(),
											<?php if ( 'variable' == $product_type ) { ?>
												'product_id': $( '#wcrp-rental-products-rental-form-wrap-<?php echo esc_html( $this->rental_form_id ); ?>' ).find( '.cart input[name="product_id"]' ).val(),
												'variation_id': $( '#wcrp-rental-products-rental-form-wrap-<?php echo esc_html( $this->rental_form_id ); ?>' ).find( '.cart input[name="variation_id"]' ).val(),
											<?php } else { ?>
												'product_id': $( '#wcrp-rental-products-rental-form-wrap-<?php echo esc_html( $this->rental_form_id ); ?>' ).find( '.cart button[name="add-to-cart"]' ).val(),
											<?php } ?>
											'rent_from': $( '#wcrp-rental-products-rent-from-<?php echo esc_html( $this->rental_form_id ); ?>' ).val(),
											'rent_to': $( '#wcrp-rental-products-rent-to-<?php echo esc_html( $this->rental_form_id ); ?>' ).val(),
											'return_days_threshold': $( '#wcrp-rental-products-return-days-threshold-<?php echo esc_html( $this->rental_form_id ); ?>' ).val(),
										};

										var rentalFormUpdateAjaxRequest = jQuery.ajax({
											'url':		'<?php echo esc_html( admin_url( 'admin-ajax.php' ) ); ?>',
											'method':	'POST',
											'data':		rentalFormUpdateAjaxRequestData,
										});

										rentalFormUpdateAjaxRequest.done( function( rentalFormUpdateAjaxRequestResponse ) {

											$( '#wcrp-rental-products-spinner-<?php echo esc_html( $this->rental_form_id ); ?>' ).hide(); <?php // Hide immediately no fade out so not jumpy ?>

											if ( rentalFormUpdateAjaxRequestResponse !== 'error' ) {

												if ( 'no_product_options_selected' !== rentalFormUpdateAjaxRequestResponse ) {

													if ( rentalFormUpdateAjaxRequestResponse.startsWith( 'unavailable_stock_max_' ) ) {

														var maxQtyAvailable = rentalFormUpdateAjaxRequestResponse.replace( 'unavailable_stock_max_', '' );

														if ( '0' == maxQtyAvailable ) {

															var maxQtyAlert = "<?php esc_html_e( 'Sorry, this product is out of stock for rental.', 'wcrp-rental-products' ); ?>";

														} else {

															var maxQtyAlert = "<?php esc_html_e( 'Sorry, the maximum quantity available for rental is', 'wcrp-rental-products' ); ?> " + maxQtyAvailable + "<?php esc_html_e( '.', 'wcrp-rental-products' ); ?> <?php esc_html_e( 'Quantity previously entered has been reduced to', 'wcrp-rental-products' ); ?> " + maxQtyAvailable + "<?php esc_html_e( '.', 'wcrp-rental-products' ); ?>";

															$( '#wcrp-rental-products-rental-form-wrap-<?php echo esc_html( $this->rental_form_id ); ?>' ).find( '.cart input[name="quantity"]' ).val( maxQtyAvailable ).trigger( 'change' );

														}

														alert( maxQtyAlert );

													} else {

														if ( isNaN( rentalPrice ) ) { <?php // If no price, this isn't a '' !== condition because rentalPrice already parsed as a float, if empty is NaN, this also ensures that if a price is entered as 0.00 it is still let through to not trigger this no price notice ?>

															alert( '<?php esc_html_e( 'Sorry, this product is unavailable for rental due to a pricing issue.', 'wcrp-rental-products' ); ?>' ); <?php // Specifically states a pricing issue and not no price set, this is because if pricing period selection and rental or purchase, if additional pricing periods price is set but the overall rental price field used for the default selection is empty then the price in the rental period default selection option name falls back to display the purchasable price assumedly due to how it runs through wc_get_price_to_display (it doesn't let you purchase it due to this alert but if this alert said no price it might be confusing to the customer as it is showing a price, but it is incorrect) ?>

														} else {

															var rentalFormUpdateAjaxRequestResponseJSON = JSON.parse( rentalFormUpdateAjaxRequestResponse );

															<?php

															if ( in_array( 'rental_form_update_ajax_request_response_json_console_log', $advanced_configuration ) ) {

																?>

																console.log( rentalFormUpdateAjaxRequestResponseJSON );

																<?php

															}

															?>

															rentalFormCalendar.setOptions({
																lockDays: rentalFormUpdateAjaxRequestResponseJSON.disabled_dates,
															});

															var rentFromDate = $( '#wcrp-rental-products-rent-from-<?php echo esc_html( $this->rental_form_id ); ?>' ).val();
															var rentToDate = $( '#wcrp-rental-products-rent-to-<?php echo esc_html( $this->rental_form_id ); ?>' ).val();

															<?php // Lock days check, this is needed to check that the dates currently selected do not include any lockDays (unavailable days), this is because a user could select a date range that is available then change the quantity or an option, etc, the dates remain selected but they could now include lockDays and therefore we need to stop the product being allowed to be added to cart and display an alert ?>

															let lockDaysCheckPassed = true;

															if ( rentFromDate !== '' && rentToDate !== '' ) {

																const lockDaysCheckRentFromDate = rentFromDate;
																const lockDaysCheckRentToDate = rentToDate;
																const lockDaysCheckRentDateMove = new Date( lockDaysCheckRentFromDate );
																let lockDaysCheckRentDate = lockDaysCheckRentFromDate;

																if ( lockDaysCheckRentDate == lockDaysCheckRentToDate ) {

																	<?php // If a single day rental ?>

																	lockDaysCheckRentDate = lockDaysCheckRentDateMove.toISOString().slice( 0, 10 );

																	$.map( rentalFormUpdateAjaxRequestResponseJSON.disabled_dates, function( value, index ) {

																		if ( lockDaysCheckRentDate == rentalFormUpdateAjaxRequestResponseJSON.disabled_dates[index] ) {

																			lockDaysCheckPassed = false;

																		}

																	});

																} else {

																	<?php // If a multiple day rental loop all dates to check each date, note the single day rental condition above cannot be removed and the below condition replaced with a <=, as with a <= condition this would include an extra date that gets checked when shouldn't on lockDaysCheckRentDate ?>

																	while ( lockDaysCheckRentDate < lockDaysCheckRentToDate ) {

																		lockDaysCheckRentDate = lockDaysCheckRentDateMove.toISOString().slice( 0, 10 );

																		$.map( rentalFormUpdateAjaxRequestResponseJSON.disabled_dates, function( value, index ) {

																			if ( lockDaysCheckRentDate == rentalFormUpdateAjaxRequestResponseJSON.disabled_dates[index] ) {

																				lockDaysCheckPassed = false;

																			}

																		});

																		if ( lockDaysCheckPassed == false ) {

																			break;

																		}

																		lockDaysCheckRentDateMove.setDate( lockDaysCheckRentDateMove.getDate() + 1 );

																	}

																}

																if ( false == lockDaysCheckPassed ) {

																	alert( '<?php esc_html_e( 'Sorry, these dates cannot be selected as they contain days where unavailable.', 'wcrp-rental-products' ); ?>' );

																}

															}

															<?php // If lock days check passed ?>

															if ( true == lockDaysCheckPassed ) {

																<?php // If a rental price, rent from and rent to date set ?>

																if ( !isNaN( rentalPrice ) && '' !== rentFromDate && '' !== rentToDate ) {

																	qty = parseInt( $( '#wcrp-rental-products-rental-form-wrap-<?php echo esc_html( $this->rental_form_id ); ?>' ).find( '.cart input[name="quantity"]' ).val() ); <?php // This field is always in the markup, even if the product is set to sold individually - the field is just hidden and it's value is set to 1 by WooCommerce, so a check to see if it exists and set it to 1 is not required here ?>

																	<?php // Date related variables below use timestamps based off 12 noon to ensure calculated correctly when daylight savings time changes during dates ?>

																	var rentFromDateSplit = rentFromDate.split( '-' );
																	var rentFromDateTimestamp = new Date( rentFromDateSplit[ 0 ], rentFromDateSplit[1] - 1, rentFromDateSplit[ 2 ] , 12, 0, 0, 0 ).getTime(); <?php // -1 as months argument start from 0 ?>

																	var rentToDateSplit = rentToDate.split( '-' );
																	var rentToDateTimestamp = new Date( rentToDateSplit[ 0 ], rentToDateSplit[1] - 1, rentToDateSplit[ 2 ] , 12, 0, 0, 0 ).getTime(); <?php // -1 as months argument start from 0 ?>

																	rentedDays = Math.round( Math.abs( ( rentFromDateTimestamp - rentToDateTimestamp ) / ( 24 * 60 * 60 * 1000 ) ) ) + 1;
																	pricingPeriod = <?php echo esc_html( $pricing_period ); ?>;
																	pricingTiersData = '<?php echo wp_json_encode( $pricing_tiers_data ); ?>';
																	pricingTiersData = JSON.parse( pricingTiersData );
																	pricingTierPercent = 0; <?php // If there aren't any matching days greater than the rental days then % is 0 (no change) ?>
																	pricingTierHighest = 0;
																	priceAdditionalPeriodPercent = <?php echo ( !empty( $price_additional_period_percent ) ? esc_html( $price_additional_period_percent ) : 0 ); ?>;

																	<?php if ( !empty( $pricing_tiers_data ) ) { // Stops JS .length undefined if pricing tiers data is empty for any reason, it shouldn't be due to upgrade function but some installs may not ?>

																		for ( var i = 0; i < pricingTiersData.days.length; i++ ) {

																			<?php // Highest used as days maybe unordered e.g. 1 is 10%, 5 is 20%, 3 is 15% so we want to use the highest ?>

																			if ( parseInt( pricingTiersData.days[i] ) > pricingTierHighest ) { <?php // parseInt as days ?>

																				if ( rentedDays > parseInt( pricingTiersData.days[i] ) ) {

																					pricingTierHighest = parseInt( pricingTiersData.days[i] ); <?php // parseInt as days ?>
																					pricingTierPercent = parseFloat( pricingTiersData.percent[i] ); <?php // parseFloat as can be multiple decimal places ?>

																				}

																			}

																		}

																	<?php } ?>

																	if ( Math.sign( pricingTierPercent ) == 1 ) { <?php // If positive ?>

																		pricingTiersPercentMultiplier = 1 + ( pricingTierPercent / 100 );

																	} else { // If negative

																		pricingTiersPercentMultiplier = 1 - ( Math.abs( pricingTierPercent ) / 100 );

																	}

																	<?php // Set total price and cart item price ?>

																	if ( totalOverrides.hasOwnProperty( rentedDays ) ) { <?php // Note that this will be empty if period_selection, see $total_overrides_json conditions ?>

																		totalOverridesPrice = rentalFormConvertPriceToIncExcTax( totalOverrides[rentedDays] );

																		<?php

																		if ( 'on' == $advanced_pricing ) {

																			?>

																			rentalFormAdvancedPricingAjaxRequest(
																				<?php
																				// First parameter is same as the off condition except no decimal separator replace, as further calculations and decimal replace occur in rentalFormAdvancedPricingAjaxRequest()
																				// Second parameter for cart item price is same as the off condition
																				?>
																				parseFloat( totalOverridesPrice * qty ).toFixed( <?php echo esc_html( $price_decimals ); ?> ),
																				totalOverridesPrice,
																			);

																			<?php

																		} else {

																			?>

																			$( '#wcrp-rental-products-total-price-<?php echo esc_html( $this->rental_form_id ); ?>' ).text( parseFloat( totalOverridesPrice * qty ).toFixed( <?php echo esc_html( $price_decimals ); ?> ).replace( '.', '<?php echo wp_kses_post( $price_decimal_separator ); ?>' ) );
																			$( '#wcrp-rental-products-cart-item-price-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( totalOverridesPrice );

																			<?php

																		}

																		?>

																	} else {

																		<?php

																		if ( 'fixed' == $pricing_type ) {

																			if ( 'yes' == $pricing_tiers && !empty( $pricing_tiers_data ) ) {

																				if ( 'on' == $advanced_pricing ) {

																					?>

																					rentalFormAdvancedPricingAjaxRequest(
																						<?php
																						// First parameter is same as the off condition except no decimal separator replace, as further calculations and decimal replace occur in rentalFormAdvancedPricingAjaxRequest()
																						// Second parameter for cart item price is same as the off condition
																						?>
																						parseFloat( ( ( rentalPrice ) * qty ) * pricingTiersPercentMultiplier ).toFixed( <?php echo esc_html( $price_decimals ); ?> ),
																						rentalPrice * pricingTiersPercentMultiplier,
																					);

																					<?php

																				} else {

																					?>

																					$( '#wcrp-rental-products-total-price-<?php echo esc_html( $this->rental_form_id ); ?>' ).text( parseFloat( ( ( rentalPrice ) * qty ) * pricingTiersPercentMultiplier ).toFixed( <?php echo esc_html( $price_decimals ); ?> ).replace( '.', '<?php echo wp_kses_post( $price_decimal_separator ); ?>' ) );
																					$( '#wcrp-rental-products-cart-item-price-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( rentalPrice * pricingTiersPercentMultiplier );

																					<?php

																				}

																			} else {

																				if ( 'on' == $advanced_pricing ) {

																					?>

																					rentalFormAdvancedPricingAjaxRequest(
																						<?php
																						// First parameter is same as the off condition except no decimal separator replace, as further calculations and decimal replace occur in rentalFormAdvancedPricingAjaxRequest()
																						// Second parameter for cart item price is same as the off condition
																						?>
																						parseFloat( rentalPrice * qty ).toFixed( <?php echo esc_html( $price_decimals ); ?> ),
																						rentalPrice,
																					);

																					<?php

																				} else {

																					?>

																					$( '#wcrp-rental-products-total-price-<?php echo esc_html( $this->rental_form_id ); ?>' ).text( parseFloat( rentalPrice * qty ).toFixed( <?php echo esc_html( $price_decimals ); ?> ).replace( '.', '<?php echo wp_kses_post( $price_decimal_separator ); ?>' ) );
																					$( '#wcrp-rental-products-cart-item-price-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( rentalPrice );

																					<?php

																				}

																			}

																		} elseif ( 'period' == $pricing_type ) {

																			if ( '1' !== $pricing_period ) {

																				if ( 'yes' == $pricing_period_multiples ) {

																					if ( 'yes' == $pricing_tiers && !empty( $pricing_tiers_data ) ) {

																						if ( 'yes' == $price_additional_periods_percent && (float) $price_additional_period_percent > 0 ) {

																							if ( 'on' == $advanced_pricing ) {

																								?>

																								rentalFormAdvancedPricingAjaxRequest(
																									<?php
																									// First parameter is same as the off condition except no decimal separator replace, as further calculations and decimal replace occur in rentalFormAdvancedPricingAjaxRequest()
																									// Second parameter for cart item price is same as the off condition
																									?>
																									parseFloat( ( rentalPrice + ( ( ( rentalPrice * priceAdditionalPeriodPercent ) / 100 ) * ( ( rentedDays / pricingPeriod ) - 1 ) ) ) * qty * pricingTiersPercentMultiplier ).toFixed( <?php echo esc_html( $price_decimals ); ?> ),
																									( rentalPrice + ( ( ( rentalPrice * priceAdditionalPeriodPercent ) / 100 ) * ( ( rentedDays / pricingPeriod ) - 1 ) ) ) * pricingTiersPercentMultiplier,
																								);

																								<?php

																							} else {

																								?>

																								$( '#wcrp-rental-products-total-price-<?php echo esc_html( $this->rental_form_id ); ?>' ).text( parseFloat( ( rentalPrice + ( ( ( rentalPrice * priceAdditionalPeriodPercent ) / 100 ) * ( ( rentedDays / pricingPeriod ) - 1 ) ) ) * qty * pricingTiersPercentMultiplier ).toFixed( <?php echo esc_html( $price_decimals ); ?> ).replace( '.', '<?php echo wp_kses_post( $price_decimal_separator ); ?>' ) );
																								$( '#wcrp-rental-products-cart-item-price-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( ( rentalPrice + ( ( ( rentalPrice * priceAdditionalPeriodPercent ) / 100 ) * ( ( rentedDays / pricingPeriod ) - 1 ) ) ) * pricingTiersPercentMultiplier );

																								<?php

																							}

																						} else {

																							if ( 'on' == $advanced_pricing ) {

																								?>

																								rentalFormAdvancedPricingAjaxRequest(
																									<?php
																									// First parameter is same as the off condition except no decimal separator replace, as further calculations and decimal replace occur in rentalFormAdvancedPricingAjaxRequest()
																									// Second parameter for cart item price is same as the off condition
																									?>
																									parseFloat( rentalPrice * ( rentedDays / pricingPeriod ) * qty * pricingTiersPercentMultiplier ).toFixed( <?php echo esc_html( $price_decimals ); ?> ),
																									rentalPrice * ( rentedDays / pricingPeriod ) * pricingTiersPercentMultiplier,
																								);

																								<?php

																							} else {

																								?>

																								$( '#wcrp-rental-products-total-price-<?php echo esc_html( $this->rental_form_id ); ?>' ).text( parseFloat( rentalPrice * ( rentedDays / pricingPeriod ) * qty * pricingTiersPercentMultiplier ).toFixed( <?php echo esc_html( $price_decimals ); ?> ).replace( '.', '<?php echo wp_kses_post( $price_decimal_separator ); ?>' ) );
																								$( '#wcrp-rental-products-cart-item-price-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( rentalPrice * ( rentedDays / pricingPeriod ) * pricingTiersPercentMultiplier );

																								<?php

																							}

																						}

																					} else {

																						if ( 'yes' == $price_additional_periods_percent && (float) $price_additional_period_percent > 0 ) {

																							if ( 'on' == $advanced_pricing ) {

																								?>

																								rentalFormAdvancedPricingAjaxRequest(
																									<?php
																									// First parameter is same as the off condition except no decimal separator replace, as further calculations and decimal replace occur in rentalFormAdvancedPricingAjaxRequest()
																									// Second parameter for cart item price is same as the off condition
																									?>
																									parseFloat( ( rentalPrice + ( ( ( rentalPrice * priceAdditionalPeriodPercent ) / 100 ) * ( ( rentedDays / pricingPeriod ) - 1 ) ) ) * qty ).toFixed( <?php echo esc_html( $price_decimals ); ?> ),
																									rentalPrice + ( ( ( rentalPrice * priceAdditionalPeriodPercent ) / 100 ) * ( ( rentedDays / pricingPeriod ) - 1 ) ),
																								);

																								<?php

																							} else {

																								?>

																								$( '#wcrp-rental-products-total-price-<?php echo esc_html( $this->rental_form_id ); ?>' ).text( parseFloat( ( rentalPrice + ( ( ( rentalPrice * priceAdditionalPeriodPercent ) / 100 ) * ( ( rentedDays / pricingPeriod ) - 1 ) ) ) * qty ).toFixed( <?php echo esc_html( $price_decimals ); ?> ).replace( '.', '<?php echo wp_kses_post( $price_decimal_separator ); ?>' ) );
																								$( '#wcrp-rental-products-cart-item-price-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( rentalPrice + ( ( ( rentalPrice * priceAdditionalPeriodPercent ) / 100 ) * ( ( rentedDays / pricingPeriod ) - 1 ) ) );

																								<?php

																							}

																						} else {

																							if ( 'on' == $advanced_pricing ) {

																								?>

																								rentalFormAdvancedPricingAjaxRequest(
																									<?php
																									// First parameter is same as the off condition except no decimal separator replace, as further calculations and decimal replace occur in rentalFormAdvancedPricingAjaxRequest()
																									// Second parameter for cart item price is same as the off condition
																									?>
																									parseFloat( rentalPrice * ( rentedDays / pricingPeriod ) * qty ).toFixed( <?php echo esc_html( $price_decimals ); ?> ),
																									rentalPrice * ( rentedDays / pricingPeriod ),
																								);

																								<?php

																							} else {

																								?>

																								$( '#wcrp-rental-products-total-price-<?php echo esc_html( $this->rental_form_id ); ?>' ).text( parseFloat( rentalPrice * ( rentedDays / pricingPeriod ) * qty ).toFixed( <?php echo esc_html( $price_decimals ); ?> ).replace( '.', '<?php echo wp_kses_post( $price_decimal_separator ); ?>' ) );
																								$( '#wcrp-rental-products-cart-item-price-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( rentalPrice * ( rentedDays / pricingPeriod ) );

																								<?php

																							}

																						}

																					}

																				} else {

																					if ( 'on' == $advanced_pricing ) {

																						?>

																						rentalFormAdvancedPricingAjaxRequest(
																							<?php
																							// First parameter is same as the off condition except no decimal separator replace, as further calculations and decimal replace occur in rentalFormAdvancedPricingAjaxRequest()
																							// Second parameter for cart item price is same as the off condition
																							?>
																							parseFloat( rentalPrice * qty ).toFixed( <?php echo esc_html( $price_decimals ); ?> ),
																							rentalPrice,
																						);

																						<?php

																					} else {

																						?>

																						$( '#wcrp-rental-products-total-price-<?php echo esc_html( $this->rental_form_id ); ?>' ).text( parseFloat( rentalPrice * qty ).toFixed( <?php echo esc_html( $price_decimals ); ?> ).replace( '.', '<?php echo wp_kses_post( $price_decimal_separator ); ?>' ) );
																						$( '#wcrp-rental-products-cart-item-price-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( rentalPrice );

																						<?php

																					}

																				}

																			} else {

																				if ( 'yes' == $pricing_tiers && !empty( $pricing_tiers_data ) ) {

																					if ( 'yes' == $price_additional_periods_percent && (float) $price_additional_period_percent > 0 ) {

																						if ( 'on' == $advanced_pricing ) {

																							?>

																							rentalFormAdvancedPricingAjaxRequest(
																								<?php
																								// First parameter is same as the off condition except no decimal separator replace, as further calculations and decimal replace occur in rentalFormAdvancedPricingAjaxRequest()
																								// Second parameter for cart item price is same as the off condition
																								?>
																								parseFloat( ( rentalPrice + ( ( ( rentalPrice * priceAdditionalPeriodPercent ) / 100 ) * ( rentedDays - 1 ) ) ) * qty * pricingTiersPercentMultiplier ).toFixed( <?php echo esc_html( $price_decimals ); ?> ),
																								( rentalPrice + ( ( ( rentalPrice * priceAdditionalPeriodPercent ) / 100 ) * ( rentedDays - 1 ) ) ) * pricingTiersPercentMultiplier,
																							);

																							<?php

																						} else {

																							?>

																							$( '#wcrp-rental-products-total-price-<?php echo esc_html( $this->rental_form_id ); ?>' ).text( parseFloat( ( rentalPrice + ( ( ( rentalPrice * priceAdditionalPeriodPercent ) / 100 ) * ( rentedDays - 1 ) ) ) * qty * pricingTiersPercentMultiplier ).toFixed( <?php echo esc_html( $price_decimals ); ?> ).replace( '.', '<?php echo wp_kses_post( $price_decimal_separator ); ?>' ) );
																							$( '#wcrp-rental-products-cart-item-price-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( ( rentalPrice + ( ( ( rentalPrice * priceAdditionalPeriodPercent ) / 100 ) * ( rentedDays - 1 ) ) ) * pricingTiersPercentMultiplier );

																							<?php

																						}

																					} else {

																						if ( 'on' == $advanced_pricing ) {

																							?>

																							rentalFormAdvancedPricingAjaxRequest(
																								<?php
																								// First parameter is same as the off condition except no decimal separator replace, as further calculations and decimal replace occur in rentalFormAdvancedPricingAjaxRequest()
																								// Second parameter for cart item price is same as the off condition
																								?>
																								parseFloat( rentalPrice * rentedDays * qty * pricingTiersPercentMultiplier ).toFixed( <?php echo esc_html( $price_decimals ); ?> ),
																								rentalPrice * rentedDays * pricingTiersPercentMultiplier,
																							);

																							<?php

																						} else {

																							?>

																							$( '#wcrp-rental-products-total-price-<?php echo esc_html( $this->rental_form_id ); ?>' ).text( parseFloat( rentalPrice * rentedDays * qty * pricingTiersPercentMultiplier ).toFixed( <?php echo esc_html( $price_decimals ); ?> ).replace( '.', '<?php echo wp_kses_post( $price_decimal_separator ); ?>' ) );
																							$( '#wcrp-rental-products-cart-item-price-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( rentalPrice * rentedDays * pricingTiersPercentMultiplier );

																							<?php

																						}

																					}

																				} else {

																					if ( 'yes' == $price_additional_periods_percent && (float) $price_additional_period_percent > 0 ) {

																						if ( 'on' == $advanced_pricing ) {

																							?>

																							rentalFormAdvancedPricingAjaxRequest(
																								<?php
																								// First parameter is same as the off condition except no decimal separator replace, as further calculations and decimal replace occur in rentalFormAdvancedPricingAjaxRequest()
																								// Second parameter for cart item price is same as the off condition
																								?>
																								parseFloat( ( rentalPrice + ( ( ( rentalPrice * priceAdditionalPeriodPercent ) / 100 ) * ( rentedDays - 1 ) ) ) * qty ).toFixed( <?php echo esc_html( $price_decimals ); ?> ),
																								rentalPrice + ( ( ( rentalPrice * priceAdditionalPeriodPercent ) / 100 ) * ( rentedDays - 1 ) ),
																							);

																							<?php

																						} else {

																							?>

																							$( '#wcrp-rental-products-total-price-<?php echo esc_html( $this->rental_form_id ); ?>' ).text( parseFloat( ( rentalPrice + ( ( ( rentalPrice * priceAdditionalPeriodPercent ) / 100 ) * ( rentedDays - 1 ) ) ) * qty ).toFixed( <?php echo esc_html( $price_decimals ); ?> ).replace( '.', '<?php echo wp_kses_post( $price_decimal_separator ); ?>' ) );
																							$( '#wcrp-rental-products-cart-item-price-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( rentalPrice + ( ( ( rentalPrice * priceAdditionalPeriodPercent ) / 100 ) * ( rentedDays - 1 ) ) );

																							<?php

																						}

																					} else {

																						if ( 'on' == $advanced_pricing ) {

																							?>

																							rentalFormAdvancedPricingAjaxRequest(
																								<?php
																								// First parameter is same as the off condition except no decimal separator replace, as further calculations and decimal replace occur in rentalFormAdvancedPricingAjaxRequest()
																								// Second parameter for cart item price is same as the off condition
																								?>
																								parseFloat( rentalPrice * rentedDays * qty ).toFixed( <?php echo esc_html( $price_decimals ); ?> ),
																								rentalPrice * rentedDays,
																							);

																							<?php

																						} else {

																							?>

																							$( '#wcrp-rental-products-total-price-<?php echo esc_html( $this->rental_form_id ); ?>' ).text( parseFloat( rentalPrice * rentedDays * qty ).toFixed( <?php echo esc_html( $price_decimals ); ?> ).replace( '.', '<?php echo wp_kses_post( $price_decimal_separator ); ?>' ) );
																							$( '#wcrp-rental-products-cart-item-price-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( rentalPrice * rentedDays );

																							<?php

																						}

																					}

																				}

																			}

																		} elseif ( 'period_selection' == $pricing_type ) {

																			?>

																			periodSelectionPrice = rentalPrice;

																			<?php

																			// Convert periodSelectionPrice to inc/exc tax as per tax settings but only if a variable product, as if a variable product then periodSelectionPrice (rentalPrice) has come from WCRP_Rental_Products_Product_Fields::variation_data() which is via WCRP_Rental_Products_Misc::value_colon_price_pipe_explode() which hasn't converted it, see comments in that fuction why, non-variable products has got the price via wc_get_price_to_display() so no need to convert

																			if ( 'variable' == $product_type && false !== $rent_period ) { // Don't need to do it if is period selection and rent period not set as rentalPrice will be correct as via wc_get_price_to_display() from maybe_use_pricing_period_additional_selections_price

																				?>

																				periodSelectionPrice = rentalFormConvertPriceToIncExcTax( periodSelectionPrice );

																				<?php

																			}

																			if ( 'on' == $advanced_pricing ) {

																				?>

																				rentalFormAdvancedPricingAjaxRequest(
																					<?php
																					// First parameter is same as the off condition except no decimal separator replace, as further calculations and decimal replace occur in rentalFormAdvancedPricingAjaxRequest()
																					// Second parameter for cart item price is same as the off condition
																					?>
																					parseFloat( periodSelectionPrice * qty ).toFixed( <?php echo esc_html( $price_decimals ); ?> ),
																					periodSelectionPrice,
																				);

																				<?php

																			} else {

																				?>

																				$( '#wcrp-rental-products-total-price-<?php echo esc_html( $this->rental_form_id ); ?>' ).text( parseFloat( periodSelectionPrice * qty ).toFixed( <?php echo esc_html( $price_decimals ); ?> ).replace( '.', '<?php echo wp_kses_post( $price_decimal_separator ); ?>' ) );
																				$( '#wcrp-rental-products-cart-item-price-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( periodSelectionPrice );

																				<?php

																			}

																		}

																		?>

																	}

																	<?php

																	// Display available rental stock totals conditionally

																	if ( 'yes' == $available_rental_stock_totals ) {

																		?>

																		if ( rentalFormUpdateAjaxRequestResponseJSON.rent_from !== '' && rentalFormUpdateAjaxRequestResponseJSON.rent_to !== '' ) { <?php // Stops available on dates message appearing before dates set ?>

																			$( '#wcrp-rental-products-available-rental-stock-totals-<?php echo esc_html( $this->rental_form_id ); ?>' ).removeClass( 'in-stock out-of-stock' );

																			var rentalFormAvailableRentalStockTotalsShow = true;
																			var rentalFormAvailableRentalStockTotalsText = '<?php echo esc_html( apply_filters( 'wcrp_rental_products_text_available_rental_stock_totals', get_option( 'wcrp_rental_products_text_available_rental_stock_totals' ) ) ); ?>';

																			if ( rentalFormUpdateAjaxRequestResponseJSON.rental_stock_available_total == 'unlimited' ) {

																				$( '#wcrp-rental-products-available-rental-stock-totals-<?php echo esc_html( $this->rental_form_id ); ?>' ).html( rentalFormAvailableRentalStockTotalsText );
																				$( '#wcrp-rental-products-available-rental-stock-totals-<?php echo esc_html( $this->rental_form_id ); ?>' ).addClass( 'in-stock' );

																			} else {

																				$( '#wcrp-rental-products-available-rental-stock-totals-<?php echo esc_html( $this->rental_form_id ); ?>' ).html( rentalFormUpdateAjaxRequestResponseJSON.rental_stock_available_total + ' ' + rentalFormAvailableRentalStockTotalsText.toLowerCase() );

																				if ( parseInt( rentalFormUpdateAjaxRequestResponseJSON.rental_stock_available_total ) < 0 ) {

																					<?php // If available rental stock total is negative then don't show the totals, this might be if rental stock was already used on the dates and the rental stock was recently reduced to less than what was already used, however in this scenario the dates in the calendar should be disabled from selection and even if switching from dates where available an alert would appear first and it wouldn't get to this point, but just adding this condition for completeness ?>

																					rentalFormAvailableRentalStockTotalsShow = false;

																				} else {

																					if ( parseInt( rentalFormUpdateAjaxRequestResponseJSON.rental_stock_available_total ) == 0 ) {

																						<?php // This scenario should not really occur, because if unavailable the dates would be disabled in the calendar and the checks for this before this point would result in an alert and the available rental stock totals wouldn't be displayed, but including for completeness ?>

																						$( '#wcrp-rental-products-available-rental-stock-totals-<?php echo esc_html( $this->rental_form_id ); ?>' ).addClass( 'out-of-stock' );

																					} else {

																						$( '#wcrp-rental-products-available-rental-stock-totals-<?php echo esc_html( $this->rental_form_id ); ?>' ).addClass( 'in-stock' );

																					}

																				}

																			}

																			if ( rentalFormAvailableRentalStockTotalsShow == true ) {

																				$( '#wcrp-rental-products-available-rental-stock-totals-<?php echo esc_html( $this->rental_form_id ); ?>' ).attr( 'style', 'display: block !important;' ); <?php // Not .show() as !important required (see related public CSS for info), this is not hidden at the start of this code block as it gets hidden upon rental form reset/update then this runs ?>

																			}

																		}

																		<?php

																	}

																	// Rental totals

																	if ( 'period_selection' !== $pricing_type ) {

																		// Total days element isn't there for period selection, so not needed if that pricing type

																		?>

																		$( '#wcrp-rental-products-total-days-<?php echo esc_html( $this->rental_form_id ); ?>' ).text( rentedDays + ' ' + ( rentedDays > 1 ? "<?php esc_html_e( 'days', 'wcrp-rental-products' ); ?>" : "<?php esc_html_e( 'day', 'wcrp-rental-products' ); ?>" ) );

																		<?php

																	}

																	if ( $return_days_threshold > 0 ) {

																		?>

																		$( '#wcrp-rental-products-rental-return-within-<?php echo esc_html( $this->rental_form_id ); ?>' ).show();

																		<?php

																	} else {

																		?>

																		$( '#wcrp-rental-products-rental-return-within-<?php echo esc_html( $this->rental_form_id ); ?>' ).hide();

																		<?php

																	}

																	?>

																	$( '#wcrp-rental-products-rental-return-within-days-<?php echo esc_html( $this->rental_form_id ); ?>' ).text( <?php echo esc_html( $return_days_threshold ); ?> );

																	if ( $( '#product-addons-total' ).length !== 0 ) { <?php // If WooCommerce Product Add-Ons totals element is on page then we know add-ons could be used so we show the excludes info ?>

																		$( '#wcrp-rental-products-excludes-addons-<?php echo esc_html( $this->rental_form_id ); ?>' ).show();

																	}

																	<?php
																	if ( 'on' !== $advanced_pricing ) {
																		// If advanced pricing is not on we show the rental totals at this point, if it is on we the rental totals are not shown yet as the total price will change after the advanced pricing AJAX request, the show for this scenario is done in rentalFormAdvancedPricingAjaxRequest() in the same way as the line below
																		?>
																		$( '#wcrp-rental-products-rental-totals-<?php echo esc_html( $this->rental_form_id ); ?>' ).attr( 'style', 'display: block !important;' ); <?php // Not .show() as !important required (see related public CSS for info) ?>
																		<?php
																	}

																	// If in person pick up/return allowed and times chosen which incur a fee we show the excludes info

																	if ( true == $in_person_pick_up_return_allowed ) {

																		?>

																		if ( $( '#wcrp-rental-products-in-person-pick-up-time-fee-select-<?php echo esc_html( $this->rental_form_id ); ?> option:selected' ).attr( 'data-fee' ) > 0 || $( '#wcrp-rental-products-in-person-return-time-fee-select-<?php echo esc_html( $this->rental_form_id ); ?> option:selected' ).attr( 'data-fee' ) > 0 ) {

																			$( '#wcrp-rental-products-excludes-in-person-pick-up-return-fees-<?php echo esc_html( $this->rental_form_id ); ?>' ).show();

																		} else {

																			$( '#wcrp-rental-products-excludes-in-person-pick-up-return-fees-<?php echo esc_html( $this->rental_form_id ); ?>' ).hide();

																		}

																		<?php

																	}

																	?>

																	<?php

																	// Enable add to cart if advanced pricing off, if it's on it gets enabled during rentalFormAdvancedPricingAjaxRequest(), otherwise there is a brief period after rental form update but before rentalFormAdvancedPricingAjaxRequest() where the add to cart can be clicked before the advanced pricing totals updated

																	if ( 'on' !== $advanced_pricing ) {

																		?>

																		rentalFormAddToStatus( 'enable' );

																		<?php

																	}

																	?>

																}

															}

														}

													}

												} else {

													if ( $( '#wcrp-rental-products-rental-dates-<?php echo esc_html( $this->rental_form_id ); ?>' ).val() !== '' ) {

														alert( '<?php esc_html_e( 'Please select all required product option(s).', 'wcrp-rental-products' ); ?>' );

													}

												}

											} else {

												rentalFormReset();
												<?php // translators: %s: error code ?>
												alert( '<?php echo esc_html( sprintf( __( 'Sorry, there was an error, please try refreshing the page. Error code: %s.', 'wcrp-rental-products' ), 'PRODRFU0' ) ); ?>' );

											}

											<?php // Update cart item validation ?>

											rentalFormUpdateCartItemValidation();

										});

									}

									<?php // Ensure rentalFormUpdateAjaxRequest only gets run after the set timeouts, this effectively makes multiple rentalFormUpdate() calls run one by one without stopping the user interacting with the elements (e.g. fields) within the page (the alternative of setting the ajax requests to async: false would cause this). Without the timeout if changes occur to quantity/options, etc quickly the rentalFormUpdateAjaxRequest would fire immediately and the slower requests (e.g. ones that conditionally check lockDays) would end up loading and setting notices/add to cart button status last, so if for example, a user has the quantity set to 1 and could see a total price/add to cart button but then clicked the up arrow on the quantity field multiple times to get to a quantity of 10 and the product was unavailable at that stock level, the previous 9 requests would show the rental totals/add to cart button enabled for a brief period until the rentalFormUpdateAjaxRequest with the 10 qty (which would return unavailable) catches up and therefore in that period of time it would possible for a user to add 10 to cart (this would be caught on the cart/checkout pages by the cart checks, but it's best to catch this within the product page with the below) ?>

									if ( rentalFormUpdateAjaxRequestTimeout ) {

										clearTimeout( rentalFormUpdateAjaxRequestTimeout );

									}

									rentalFormUpdateAjaxRequestTimeout = setTimeout( rentalFormUpdateAjaxRequest, rentalFormUpdateAjaxRequestDelay );

								}

								function rentalFormUpdateCartItemValidation() {

									<?php // Validation to reduce risk of hidden field modification, see related functionality in WCRP_Rental_Products_Cart_Checks::add_to_cart_validation() ?>

									cartItemValidationString = $( '#wcrp-rental-products-cart-item-timestamp-<?php echo esc_html( $this->rental_form_id ); ?>' ).val();
									cartItemValidationString += $( '#wcrp-rental-products-cart-item-price-<?php echo esc_html( $this->rental_form_id ); ?>' ).val();
									cartItemValidationString += $( '#wcrp-rental-products-rent-from-<?php echo esc_html( $this->rental_form_id ); ?>' ).val();
									cartItemValidationString += $( '#wcrp-rental-products-rent-to-<?php echo esc_html( $this->rental_form_id ); ?>' ).val();
									cartItemValidationString += $( '#wcrp-rental-products-start-days-threshold-<?php echo esc_html( $this->rental_form_id ); ?>' ).val();
									cartItemValidationString += $( '#wcrp-rental-products-return-days-threshold-<?php echo esc_html( $this->rental_form_id ); ?>' ).val();
									cartItemValidationString += $( '#wcrp-rental-products-advanced-pricing-<?php echo esc_html( $this->rental_form_id ); ?>' ).val();

									<?php

									if ( true == $in_person_pick_up_return_allowed ) {

										?>

										cartItemValidationString += $( '#wcrp-rental-products-in-person-pick-up-return-<?php echo esc_html( $this->rental_form_id ); ?>' ).val();
										cartItemValidationString += $( '#wcrp-rental-products-in-person-pick-up-date-<?php echo esc_html( $this->rental_form_id ); ?>' ).val();
										cartItemValidationString += $( '#wcrp-rental-products-in-person-pick-up-time-<?php echo esc_html( $this->rental_form_id ); ?>' ).val();
										cartItemValidationString += $( '#wcrp-rental-products-in-person-pick-up-fee-<?php echo esc_html( $this->rental_form_id ); ?>' ).val();
										cartItemValidationString += $( '#wcrp-rental-products-in-person-return-date-<?php echo esc_html( $this->rental_form_id ); ?>' ).val();
										cartItemValidationString += $( '#wcrp-rental-products-in-person-return-date-type-<?php echo esc_html( $this->rental_form_id ); ?>' ).val();
										cartItemValidationString += $( '#wcrp-rental-products-in-person-return-time-<?php echo esc_html( $this->rental_form_id ); ?>' ).val();
										cartItemValidationString += $( '#wcrp-rental-products-in-person-return-fee-<?php echo esc_html( $this->rental_form_id ); ?>' ).val();

										<?php

									}

									?>

									cartItemValidationString = btoa( cartItemValidationString );

									$( '#wcrp-rental-products-cart-item-validation-<?php echo esc_html( $this->rental_form_id ); ?>' ).val( cartItemValidationString );

								}

								<?php

								// Add to order

								if ( current_user_can( 'manage_woocommerce' ) ) {

									?>

									$( document ).on( 'click', '#wcrp-rental-products-add-to-order-<?php echo esc_html( $this->rental_form_id ); ?>', function( e ) {

										e.preventDefault();

										if ( addRentalProductsPopup == true ) {

											var rentalFormAddToOrderAjaxRequestData = {
												'action': 'wcrp_rental_products_rental_form_add_to_order',
												'nonce': '<?php echo esc_html( wp_create_nonce( 'wcrp_rental_products_rental_form_add_to_order' ) ); ?>',
												'qty': $( '#wcrp-rental-products-rental-form-wrap-<?php echo esc_html( $this->rental_form_id ); ?>' ).find( '.cart input[name="quantity"]' ).val(),
												'order_id': addRentalProductsPopupOrderId,
												<?php if ( 'variable' == $product_type ) { ?>
													'product_id': $( '#wcrp-rental-products-rental-form-wrap-<?php echo esc_html( $this->rental_form_id ); ?>' ).find( '.cart input[name="product_id"]' ).val(),
													'variation_id': $( '#wcrp-rental-products-rental-form-wrap-<?php echo esc_html( $this->rental_form_id ); ?>' ).find( '.cart input[name="variation_id"]' ).val(),
												<?php } else { ?>
													'product_id': $( '#wcrp-rental-products-rental-form-wrap-<?php echo esc_html( $this->rental_form_id ); ?>' ).find( '.cart button[name="add-to-cart"]' ).val(),
												<?php } ?>
												'cart_item_price': $( '#wcrp-rental-products-cart-item-price-<?php echo esc_html( $this->rental_form_id ); ?>' ).val(),
												'rent_from': $( '#wcrp-rental-products-rent-from-<?php echo esc_html( $this->rental_form_id ); ?>' ).val(),
												'rent_to': $( '#wcrp-rental-products-rent-to-<?php echo esc_html( $this->rental_form_id ); ?>' ).val(),
												'return_days_threshold': $( '#wcrp-rental-products-return-days-threshold-<?php echo esc_html( $this->rental_form_id ); ?>' ).val(),
												<?php if ( true == $in_person_pick_up_return_allowed ) { ?>
													'in_person_pick_up_return': $( '#wcrp-rental-products-in-person-pick-up-return-<?php echo esc_html( $this->rental_form_id ); ?>' ).val(),
													'in_person_pick_up_date': $( '#wcrp-rental-products-in-person-pick-up-date-<?php echo esc_html( $this->rental_form_id ); ?>' ).val(),
													'in_person_pick_up_time': $( '#wcrp-rental-products-in-person-pick-up-time-<?php echo esc_html( $this->rental_form_id ); ?>' ).val(),
													'in_person_pick_up_fee': $( '#wcrp-rental-products-in-person-pick-up-fee-<?php echo esc_html( $this->rental_form_id ); ?>' ).val(),
													'in_person_return_date': $( '#wcrp-rental-products-in-person-return-date-<?php echo esc_html( $this->rental_form_id ); ?>' ).val(),
													'in_person_return_date_type': $( '#wcrp-rental-products-in-person-return-date-type-<?php echo esc_html( $this->rental_form_id ); ?>' ).val(),
													'in_person_return_time': $( '#wcrp-rental-products-in-person-return-time-<?php echo esc_html( $this->rental_form_id ); ?>' ).val(),
													'in_person_return_fee': $( '#wcrp-rental-products-in-person-return-fee-<?php echo esc_html( $this->rental_form_id ); ?>' ).val(),
												<?php } ?>
											};

											var rentalFormAddToOrderAjaxRequest = jQuery.ajax({
												'url':		'<?php echo esc_html( admin_url( 'admin-ajax.php' ) ); ?>',
												'method':	'POST',
												'data':		rentalFormAddToOrderAjaxRequestData,
											});

											rentalFormAddToOrderAjaxRequest.done( function( rentalFormAddToOrderAjaxRequestResponse ) {

												alert( rentalFormAddToOrderAjaxRequestResponse );

											});

										}

									});

									<?php

								}

								// Set rentalPrice

								$maybe_use_pricing_period_additional_selections_price = false;

								if ( wcrp_rental_products_is_rental_purchase( $product_id ) && true == $rent ) {

									// Note that for the condition below there isn't a need to cover a scenario it was a simple product and had the _wcrp_rental_products_rental_purchase_price then changed to a variable and the meta still exists on the parent as that meta gets removed in this scenario, see comments in WCRP_Rental_Products_Product_Save::product_data_save()

									if ( '' !== get_post_meta( $product_id, '_wcrp_rental_products_rental_purchase_price', true ) ) {

										// wc_get_price_to_display with passed rental purchase price means its converted to inc/exc tax as per settings, str_replace happens incase the store uses a different decimal separator, sets it to . character, otherwise the calculations for the rental total would be NaN, also this occurs in JS upon variation change, see later in JS

										?>

										let rentalPrice = parseFloat( "<?php echo esc_html( wc_get_price_to_display( $product, array( 'price' => str_replace( $price_decimal_separator, '.', get_post_meta( $product_id, '_wcrp_rental_products_rental_purchase_price', true ) ) ) ) ); ?>" );

										<?php

										if ( 'period_selection' == $pricing_type ) {

											$maybe_use_pricing_period_additional_selections_price = true;

										}

									} else {

										?>

										let rentalPrice = NaN; <?php // No price set, will show as unavailable, see other NaN conditions why, this may be initially set as NaN for variable products as the parent product doesn't have the _wcrp_rental_products_rental_purchase_price meta, but rentalPrice gets updated upon variation selection to the variation price if populated (or returns NaN) ?>

									<?php } ?>

								<?php } else { ?>

									let rentalPrice = parseFloat( "<?php echo esc_html( $product_price ); ?>" ); <?php // This is already via wc_get_price_to_display() so inc/exc tax, doesn't matter that this is converted to a float, as if price is empty the rental form (inc this code) doesn't get used (no price set) ?>

									<?php

									if ( 'period_selection' == $pricing_type ) {

										$maybe_use_pricing_period_additional_selections_price = true;

									}

								}

								// Maybe set rentalPrice from the pricing period additional selections array, note that variations get this done/updated via rentalFormUpdateVariablesToVariationData()

								if ( true == $maybe_use_pricing_period_additional_selections_price ) {

									if ( false !== $rent_period ) {

										if ( array_key_exists( $rent_period, $pricing_period_additional_selections_array ) ) {

											?>

											rentalPrice = parseFloat( "<?php echo esc_html( wc_get_price_to_display( $product, array( 'price' => str_replace( $price_decimal_separator, '.', $pricing_period_additional_selections_array[$rent_period] ) ) ) ); ?>" );

											<?php

										}

									}

								}

								// Set totalOverrides

								?>

								let totalOverrides = JSON.parse( '<?php echo wp_kses_post( $total_overrides_json ); ?>' );

								<?php // On quantity change update rental form ?>

								$( '#wcrp-rental-products-rental-form-wrap-<?php echo esc_html( $this->rental_form_id ); ?>' ).find( '.cart input[name="quantity"]' ).on( 'change', function() {

									rentalFormUpdate();

								});

								<?php

								// Update rental form on in person pick up time/fee select

								if ( true == $in_person_pick_up_return_allowed ) {

									?>

									$( document ).on( 'change', '#wcrp-rental-products-in-person-pick-up-time-fee-select-<?php echo esc_html( $this->rental_form_id ); ?>, #wcrp-rental-products-in-person-return-time-fee-select-<?php echo esc_html( $this->rental_form_id ); ?>', function( e ) {

										rentalFormUpdate();

									});

									<?php

								}

								// Redirect upon rental period select

								?>

								$( document ).on( 'change', '#wcrp-rental-products-rental-period-select-<?php echo esc_html( $this->rental_form_id ); ?>', function( e ) {

									window.location = $( this ).val();

								});

								<?php

								// Update variables to variation data when variation in context

								if ( 'variable' == $product_type ) {

									?>

									let rentalProductVariations = JSON.parse( $( '.variations_form' ).attr( 'data-product_variations' ) );

									<?php // On page load (covers default varitation options) ?>

									rentalFormUpdateVariablesToVariationData();

									$( '.variations_form' ).on( 'show_variation', function() { <?php // show_variation as when a combination of options selected which triggers the variation id to be populated ?>

										rentalFormUpdateVariablesToVariationData();

									});

									$( '.variations .reset_variations' ).on( 'click', function() {

										rentalFormReset();

									});

									function rentalFormUpdateVariablesToVariationData() {

										Object.keys( rentalProductVariations ).forEach( function( k ) {

											if ( $( '.variations_form input[name="variation_id"]' ).val() == rentalProductVariations[k]['variation_id'] ) {

												<?php

												/*
												None of these are in store decimal format, so no need to convert to . as already in that format, gets change to use the store decimal separator later upon display, after further calculations

												First 2 rentalPrice variables are already inc/exc tax as per tax settings before they get here

												Last rentalPrice variable isn't yet converted to inc/exc tax as per tax settings, but gets changed via rentalFormUpdateAjaxRequest(), see comments in WCRP_Rental_Products_Product_Fields::variation_data() why
												*/

												if ( wcrp_rental_products_is_rental_purchase( $product_id ) && true == $rent ) {

													?>

													if ( rentalProductVariations[k]['wcrp_rental_products_rental_purchase_price'] !== 'error' ) {

														rentalPrice = parseFloat( rentalProductVariations[k]['wcrp_rental_products_rental_purchase_price'] );

													} else {

														rentalPrice = NaN; <?php // See other NaN conditions why ?>

													}

												<?php } else { ?>

													rentalPrice = parseFloat( rentalProductVariations[k]['display_price'] );

													<?php

												}

												// Pricing period additional selection

												if ( 'period_selection' == $pricing_type ) {

													if ( false !== $rent_period ) {

														?>

														if ( rentalProductVariations[k]['wcrp_rental_products_pricing_period_additional_selections'] !== '[]' ) {

															rentalProductVariationsPricingPeriodAdditionalSelections = JSON.parse( rentalProductVariations[k]['wcrp_rental_products_pricing_period_additional_selections'] );

															if ( rentalProductVariationsPricingPeriodAdditionalSelections.hasOwnProperty( '<?php echo esc_html( $rent_period ); ?>' ) ) {

																rentalPrice = parseFloat( rentalProductVariationsPricingPeriodAdditionalSelections[<?php echo esc_html( $rent_period ); ?>] );

															}

														}

														<?php

													}

												}

												// Total overrides

												if ( 'period_selection' !== $pricing_type ) {

													?>

													if ( rentalProductVariations[k]['wcrp_rental_products_total_overrides'] !== '[]' ) {

														totalOverrides = JSON.parse( rentalProductVariations[k]['wcrp_rental_products_total_overrides'] );

													}

													<?php

												}

												?>

											}

										});

										rentalFormUpdate();

									}

									<?php

								}

								// Calendar

								?>

								var rentalFormAvailabilityCheckerPopulationInitial = true;

								<?php

								if ( true == $in_person_pick_up_return_allowed ) {
									// Set variables containing the initial pick up/return time/fee select field options, these are then used to revert the options back to initials later if the options have been changed since page load by selecting single day and need reverting back if then multiple days selected
									?>
									var inPersonPickUpTimeFeeSelectOptionsInitial = $( '#wcrp-rental-products-in-person-pick-up-time-fee-select-<?php echo esc_html( $this->rental_form_id ); ?>' ).html();
									var inPersonReturnTimeFeeSelectOptionsInitial = $( '#wcrp-rental-products-in-person-return-time-fee-select-<?php echo esc_html( $this->rental_form_id ); ?>' ).html();
									<?php

								}

								?>

								if ( addRentalProductsPopup == false ) {

									var minDate = '<?php echo esc_html( $minimum_date ); ?>';

								} else {

									<?php

									if ( current_user_can( 'manage_woocommerce' ) ) {

										if ( '' == $earliest_available_date ) {

											// minDate disabled if it is the add rental products popup and when earliest available date not set so user can create orders with past rental dates

											?>

											var minDate = null;

											<?php

										} else {

											?>

											var minDate = '<?php echo esc_html( $minimum_date ); ?>';

											<?php
										}

									}

									?>

								}

								var rentalFormCalendar = new Litepicker({

									element: document.getElementById( 'wcrp-rental-products-rental-dates-<?php echo esc_html( $this->rental_form_id ); ?>' ),
									parentEl: '#wcrp-rental-products-rental-dates-parent-<?php echo esc_html( $this->rental_form_id ); ?>', <?php // Fixes issue with total days tooltip not positioned correctly with inlineMode enabled ?>
									autoApply: <?php echo ( 'yes' == $auto_apply ? 'true' : 'false' ); ?>,
									autoRefresh: true,
									disallowLockDaysInRange: true,
									firstDay: <?php echo esc_html( wcrp_rental_products_rental_form_first_day() ); ?>,
									format: '<?php echo esc_html( wcrp_rental_products_rental_form_date_format() ); ?>',
									inlineMode: <?php echo esc_html( $inline ); ?>,
									lang: '<?php echo esc_html( apply_filters( 'wcrp_rental_products_litepicker_language', esc_html( str_replace( '_', '-', get_locale() ) ) ) ); ?>',
									minDate: minDate,
									maxDate: '<?php echo esc_html( wcrp_rental_products_rental_form_maximum_date( 'date' ) ); ?>',
									<?php
									if ( 'yes' !== $pricing_period_multiples ) {
										?>
										minDays: <?php echo esc_html( $minimum_days ); ?>,
										maxDays: <?php echo esc_html( $maximum_days ); ?>,
										<?php
									} else {
										if ( (int) $pricing_period_multiples_maximum > 0 ) {
											?>
											maxDays: <?php echo (int) $pricing_period * (int) $pricing_period_multiples_maximum; ?>,
											<?php
										}
									}
									?>
									numberOfColumns: <?php echo esc_html( $columns ); ?>,
									numberOfMonths: <?php echo esc_html( $months ); ?>,
									plugins: [ 'mobilefriendly' ],
									resetButton: <?php echo ( 'yes' == $reset_button ? 'true' : 'false' ); ?>,
									singleMode: <?php echo ( '1' == $minimum_days && '1' == $maximum_days ? 'true' : 'false' ); ?>,
									selectForward: <?php echo ( '' !== $start_day ? 'true' : 'false' ); ?>,
									tooltipText: {
										one: '<?php esc_html_e( 'day', 'wcrp-rental-products' ); ?>',
										other: '<?php esc_html_e( 'days', 'wcrp-rental-products' ); ?>',
									},

									setup: ( rentalFormCalendar ) => {

										<?php

										// If start day set

										if ( '' !== $start_day ) {

											// Populate rental form start day lock styles

											?>

											var rentalFormStartDayLockStyles = '<style>#wcrp-rental-products-rental-form-<?php echo esc_html( $this->rental_form_id ); ?> .day-item:not( .day-item-weekday-number-<?php echo esc_html( $start_day ); ?> ) { color: var( --litepicker-is-locked-color ) !important; pointer-events: none !important; }</style>';

											$( '#wcrp-rental-products-rental-form-wrap-<?php echo esc_html( $this->rental_form_id ); ?>' ).append( '<div id="wcrp-rental-products-rental-form-start-day-lock-styles-<?php echo esc_html( $this->rental_form_id ); ?>">' + rentalFormStartDayLockStyles + '</div>' );

											<?php

										}

										// On render ui

										?>

										rentalFormCalendar.on( 'render', ( ui ) => {

											<?php // Cancel and apply buttons need to be changed so they are translation based strings, otherwise cannot not be translated (these are not changed as per Litepicker's lang setting) ?>

											$( '#wcrp-rental-products-rental-form-<?php echo esc_html( $this->rental_form_id ); ?> .litepicker .button-apply' ).text( '<?php esc_html_e( 'Apply', 'wcrp-rental-products' ); ?>' );
											$( '#wcrp-rental-products-rental-form-<?php echo esc_html( $this->rental_form_id ); ?> .litepicker .button-cancel' ).text( '<?php esc_html_e( 'Cancel', 'wcrp-rental-products' ); ?>' );

											<?php

											// Populate dates using availability checker dates if simple product type (cannot work on other types)

											if ( 'simple' == $product_type ) {

												?>

												if ( rentalFormAvailabilityCheckerPopulationInitial == true ) { <?php // Ensures only runs once not on every render ?>

													rentalFormAvailabilityCheckerPopulationInitial = false;

													var rentalFormAvailabilityCheckerPopulationAjaxRequestData = {
														'action':										'wcrp_rental_products_rental_form_availability_checker_population',
														'nonce':										'<?php echo esc_html( wp_create_nonce( 'wcrp_rental_products_rental_form_availability_checker_population' ) ); ?>',
														'product_id':									'<?php echo esc_html( $product_id ); ?>',
														'product_sold_individually':					'<?php echo esc_html( ( true == $product_sold_individually ? 'yes' : 'no' ) ); ?>',
														'pricing_period':								'<?php echo esc_html( $pricing_period ); ?>',
														'pricing_period_selection_rent_period':			'<?php echo esc_html( ( false !== $rent_period ? sanitize_text_field( $rent_period ) : '' ) ); ?>',
														'pricing_period_additional_selections_array':	'<?php echo wp_json_encode( $pricing_period_additional_selections_array ); ?>',
														'pricing_type':									'<?php echo esc_html( $pricing_type ); ?>',
														'minimum_days':									'<?php echo esc_html( $minimum_days ); ?>',
														'maximum_days':									'<?php echo esc_html( $maximum_days ); ?>',
													};

													var rentalFormAvailabilityCheckerPopulationAjaxRequest = jQuery.ajax({
														'url':		'<?php echo esc_html( admin_url( 'admin-ajax.php' ) ); ?>',
														'method':	'POST',
														'data':		rentalFormAvailabilityCheckerPopulationAjaxRequestData,
													});

													rentalFormAvailabilityCheckerPopulationAjaxRequest.done( function( rentalFormAvailabilityCheckerPopulationAjaxRequestResponse ) {

														if ( rentalFormAvailabilityCheckerPopulationAjaxRequestResponse !== '' ) {

															rentalFormAvailabilityCheckerPopulationAjaxRequestResponseJSON = JSON.parse( rentalFormAvailabilityCheckerPopulationAjaxRequestResponse );

															if ( rentalFormAvailabilityCheckerPopulationAjaxRequestResponseJSON.type == 'populate_period_selection' ) {

																<?php // If the available rental is a valid period selection but isn't the currently selected option then we append which is available to the rental period select option text, note that this cannot be appended if it is the currently selected option because the dates get populated in that scenario, the return type is not populate_period_selection so would not meet this condition ?>

																var rentalFormAvailabilityCheckerPopulationPeriodSelectionOptionAvailable = $( '#wcrp-rental-products-rental-period-select-<?php echo esc_html( $this->rental_form_id ); ?> option[data-period="' + rentalFormAvailabilityCheckerPopulationAjaxRequestResponseJSON.days_total + '"]' );

																<?php // If the period selection option doesn't exist for rentalFormAvailabilityCheckerPopulationAjaxRequestResponseJSON.days_total then it will be because it is the default option which has data-period="default", so we set use that element instead ?>

																if ( rentalFormAvailabilityCheckerPopulationPeriodSelectionOptionAvailable.length == 0 ) {

																	var rentalFormAvailabilityCheckerPopulationPeriodSelectionOptionAvailable = $( '#wcrp-rental-products-rental-period-select-<?php echo esc_html( $this->rental_form_id ); ?> option[data-period="default"]' );

																}

																rentalFormAvailabilityCheckerPopulationPeriodSelectionOptionAvailable.append( "<?php echo ' ' . esc_html__( '-', 'wcrp-rental-products' ) . ' ' . esc_html( apply_filters( 'wcrp_rental_products_text_rental_available', get_option( 'wcrp_rental_products_text_rental_available' ) ) ) . ' ' . esc_html__( 'on previously selected dates', 'wcrp-rental-products' ); ?>" );

															} else if ( rentalFormAvailabilityCheckerPopulationAjaxRequestResponseJSON.type == 'populate_dates_1' || rentalFormAvailabilityCheckerPopulationAjaxRequestResponseJSON.type == 'populate_dates_2' ) {

																<?php // Populate the quantity using availability checker quantity, this is done even if quantity disabled as will be 1 (quantity field on availability checker field not there but as empty gets set to 1), note that if the setting was changed to not include quantity but users already used the availability checker with a > 1 quantity this will set their quantity to the quantity they entered at the time, but if they amend the availability checker again (new dates) it will get set to 1, for this scenario it is fine the old quantity is used as the availability status returned is still valid ?>

																$( '#wcrp-rental-products-rental-form-wrap-<?php echo esc_html( $this->rental_form_id ); ?>' ).find( '.cart input[name="quantity"]' ).val( rentalFormAvailabilityCheckerPopulationAjaxRequestResponseJSON.quantity );

																<?php // Populate the dates using availability checker rent from/to in format Litepicker can use ?>

																rentalFormCalendar.setDateRange( rentalFormCalendar.DateTime( new Date( rentalFormAvailabilityCheckerPopulationAjaxRequestResponseJSON.rent_from ).toISOString() ).format( '<?php echo esc_html( wcrp_rental_products_rental_form_date_format() ); ?>' ), rentalFormCalendar.DateTime( new Date( rentalFormAvailabilityCheckerPopulationAjaxRequestResponseJSON.rent_to ).toISOString() ).format( '<?php echo esc_html( wcrp_rental_products_rental_form_date_format() ); ?>' ) );

																<?php // No need for rentalFormUpdate() here as setDateRange triggers a selection of dates which in turn triggers rentalFormUpdate() ?>

																<?php // Show auto population information, this must come after the dates are set above, as that triggers a selection of dates which in turn triggers rentalFormUpdate() which triggers rentalFormReset(), where this message gets removed, by this coming after it ensures it remains on page load, then if the rental form is updated, e.g. user changes quantity, options, dates, etc it disappears as no longer relevant ?>

																if ( rentalFormAvailabilityCheckerPopulationAjaxRequestResponseJSON.type == 'populate_dates_1' ) {

																	var rentalFormAvailabilityCheckerPopulationInformation = '<?php esc_html_e( 'These dates have been automatically populated as this product is available for your previously selected dates.', 'wcrp-rental-products' ); ?>';

																} else if ( rentalFormAvailabilityCheckerPopulationAjaxRequestResponseJSON.type == 'populate_dates_2' ) {

																	var rentalFormAvailabilityCheckerPopulationInformation = '<?php esc_html_e( 'These dates and quantity have been automatically populated as this product is available for your previously selected dates and quantity.', 'wcrp-rental-products' ); ?>';

																}

																$( '<div id="wcrp-rental-products-availability-checker-auto-population-information-<?php echo esc_html( $this->rental_form_id ); ?>" class="wcrp-rental-products-availability-checker-auto-population-information wcrp-rental-products-information">' + rentalFormAvailabilityCheckerPopulationInformation + ' <a href="<?php echo esc_url( add_query_arg( 'wcrp_rental_products_availability_checker_reset', '1' ) ); ?>"><?php esc_html_e( 'Reset?', 'wcrp-rental-products' ); ?></a></div>' ).insertAfter( $( '#wcrp-rental-products-rental-dates-wrap-<?php echo esc_html( $this->rental_form_id ); ?>' ) );

															}

														}

													});

												}

												<?php

											}

											?>

										});

										<?php // On render day ?>

										rentalFormCalendar.on( 'render:day', ( day, date ) => {

											<?php // Add weekday number class ?>

											$( day ).addClass( 'day-item-weekday-number-' + date.getDay() );

											<?php

											// Highlight disable start/end dates

											if ( !empty( $disable_rental_start_end_dates_combined_string ) ) {

												?>

												disableRentalStartEndDates = [<?php echo wp_kses_post( $disable_rental_start_end_dates_combined_string ); ?>];

												dateFormatted = new Date( date.dateInstance );
												dateFormatted = new Date( dateFormatted.getTime() - ( dateFormatted.getTimezoneOffset() * 60 * 1000 ) );
												dateFormatted = dateFormatted.toISOString().split( 'T' )[0];

												if ( disableRentalStartEndDates.includes( dateFormatted ) ) {

													$( day ).addClass( 'is-highlighted' );
													$( day ).css( 'pointer-events', 'none' );

												}

												<?php

											}

											// Highlight disable start/end days

											if ( !empty( $disable_rental_start_end_days ) || '0' == $disable_rental_start_end_days ) {

												?>

												disableRentalStartEndDays = [<?php echo esc_html( $disable_rental_start_end_days ); ?>];

												if ( disableRentalStartEndDays.includes( date.getDay() ) ) {

													<?php

													if ( 'start_end' == $disable_rental_start_end_days_type ) { // If start/end then we do pointer-events none as should not be clickable

														?>

														$( day ).addClass( 'is-highlighted' );
														$( day ).css( 'pointer-events', 'none' );

														<?php

													} else {

														?>

														$( day ).addClass( 'is-dotted-border' ); <?php // If start or end (not start/end) then dotted border as per notice, pointer-events not set to none as needs to be clickable for the start or end date depending on which is allowed ?>

														<?php

													}

													?>

												}

												<?php

											}

											?>

										});

										<?php // On day click, this is not on before:click as additional classes e.g. is-end-date not yet available when before:click triggered, note that the selector of .day-item is intentional, the rental form parent element IDs are not used with this as they are not yet available for the on click method when this is called, therefore if making any changes to the calendar within this function those changes should target the specific rental form incase there are more than rental forms on the page e.g. plugins that show a product page within a product page such as quick view ?>

										$( document ).on( 'click', '.day-item', function() {

											<?php

											// If a set start day and the clicked day is not this then clear selection and reset the start day lock styles

											if ( '' !== $start_day ) {

												?>

												$( '#wcrp-rental-products-rental-form-start-day-lock-styles-<?php echo esc_html( $this->rental_form_id ); ?>' ).html( '' );

												clickedDayItemDate = parseInt( $( this ).attr( 'data-time' ) );
												clickedDayItemDate = new Date( clickedDayItemDate );

												if ( clickedDayItemDate.getDay() !== <?php echo esc_html( $start_day ); ?> ) {

													if ( !$( this ).hasClass( 'is-end-date' ) ) {

														rentalFormCalendar.clearSelection();
														$( '#wcrp-rental-products-rental-form-start-day-lock-styles-<?php echo esc_html( $this->rental_form_id ); ?>' ).html( rentalFormStartDayLockStyles );

													}

												}

												<?php

											}

											?>

										});

										<?php // On date selection pre submit ?>

										rentalFormCalendar.on( 'preselect', ( date1, date2 ) => {

											<?php

											// Auto select end date

											if ( 'yes' == $auto_select_end_date && $minimum_days == $maximum_days ) {

												if ( 'period' == $pricing_type && 'yes' == $pricing_period_multiples ) {

													?>

													var doAutoSelectEndDate = false;

													<?php

												} else {

													?>

													var doAutoSelectEndDate = true;

													<?php

												}

											} else {

												?>

												var doAutoSelectEndDate = false;

												<?php

											}

											?>

											if ( doAutoSelectEndDate == true ) {

												<?php // New variables required as date1.add() effects date1, cannot be set to another variable before the days added and then used as date1 still ends up with the added days ?>

												var newDate1 = new Date( date1.getTime() );
												var newDate2 = date1.add( <?php echo esc_html( $minimum_days ); ?> - 1, 'days' ); <?php // -1 ensures end date correct ?>

												rentalFormCalendar.clearSelection();
												rentalFormCalendar.setDateRange( newDate1, newDate2 );
												rentalFormCalendar.hide();

											}

											<?php

											// Allow apply button (only available if auto apply disabled) to be clicked if only rent from selected, also requires a button:apply condition, see later in this code

											if ( 'no' == $auto_apply ) {

												?>

												allowApplyIfOnlyRentFromSelected = null;

												if ( date1 && !date2 ) {

													allowApplyIfOnlyRentFromSelected = date1;

												}

												rentalFormCalendar.ui.querySelector( '.button-apply' ).disabled = false;

												<?php

											}

											?>

										});

										<?php // On date selection submit ?>

										rentalFormCalendar.on( 'selected', ( date1, date2 ) => {

											<?php // If singleMode is enabled then only one date is passed and date2 will be undefined so its set to date1 here otherwise the .getDay() calls later will cause an error ?>

											if ( rentalFormCalendar.options.singleMode == true ) {

												date2 = date1;

											}

											<?php

											// If in person pick up/return, a single date is selected and if the single day pick up/return times/fees options available

											if ( true == $in_person_pick_up_return_allowed && !empty( $in_person_pick_up_times_fees_single_day_same_day_array ) && !empty( $in_person_return_times_fees_single_day_same_day_array ) ) {

												// If a single day selected, note this won't work if condition below was date1 == date2 as these are objects which cannot be determined to be the same

												?>

												if ( date1.getTime() == date2.getTime() ) { <?php // Not .getDate() as this is just the day number, so if used that would cause 13th June to 13th July to be matched as one day ?>

													<?php // Change in person pick up time/fee and return time/fee options to single day options ?>

													$( '#wcrp-rental-products-in-person-pick-up-time-fee-select-<?php echo esc_html( $this->rental_form_id ); ?>' ).html( $( '#wcrp-rental-products-in-person-pick-up-time-fee-single-day-select-<?php echo esc_html( $this->rental_form_id ); ?>' ).html() );

													$( '#wcrp-rental-products-in-person-return-time-fee-select-<?php echo esc_html( $this->rental_form_id ); ?>' ).html( $( '#wcrp-rental-products-in-person-return-time-fee-single-day-select-<?php echo esc_html( $this->rental_form_id ); ?>' ).html() );

												} else {

													<?php // Change in person pick up time/fee and return time/fee to non-single day options ?>

													$( '#wcrp-rental-products-in-person-pick-up-time-fee-select-<?php echo esc_html( $this->rental_form_id ); ?>' ).html( inPersonPickUpTimeFeeSelectOptionsInitial );
													$( '#wcrp-rental-products-in-person-return-time-fee-select-<?php echo esc_html( $this->rental_form_id ); ?>' ).html( inPersonReturnTimeFeeSelectOptionsInitial );

												}

												<?php

											}

											// Show in person pick up/return fields if allowed including setting the pick up/return time/fee field label dates based on rental dates selected

											if ( true == $in_person_pick_up_return_allowed ) {

												?>

												$( '#wcrp-rental-products-in-person-pick-up-time-fee-date-<?php echo esc_html( $this->rental_form_id ); ?>' ).text( date1.format( rentalFormCalendar.options.format ) );

												<?php

												if ( 'same_day' == $in_person_return_date ) {

													?>

													$( '#wcrp-rental-products-in-person-return-time-fee-date-<?php echo esc_html( $this->rental_form_id ); ?>' ).text( date2.format( rentalFormCalendar.options.format ) );

													<?php

												} else {

													?>

													$( '#wcrp-rental-products-in-person-return-time-fee-date-<?php echo esc_html( $this->rental_form_id ); ?>' ).text( rentalFormCalendar.DateTime( rentalFormAddDayToIsoDate( date2.format( 'YYYY-MM-DD' ) ) ).format( rentalFormCalendar.options.format ) );

													<?php

												}

												?>

												$( '#wcrp-rental-products-in-person-pick-up-return-wrap-<?php echo esc_html( $this->rental_form_id ); ?>' ).fadeIn();

												<?php

											}

											// Start day catch if browser does not support pointer-events: none is not included here as the form is cleared before selection on day click if a user manages to get through the pointer-events: none

											// Disable rental start/end dates catch if browser does not support pointer-events: none

											if ( !empty( $disable_rental_start_end_dates_combined_string ) ) {

												?>

												disableRentalStartEndDates = [<?php echo wp_kses_post( $disable_rental_start_end_dates_combined_string ); ?>];

												if ( disableRentalStartEndDates.includes( date1.format( 'YYYY-MM-DD' ) ) || disableRentalStartEndDates.includes( date2.format( 'YYYY-MM-DD' ) ) ) {

													alert( '<?php esc_html_e( 'Sorry, these dates cannot be selected as they start or end on the highlighted days.', 'wcrp-rental-products' ); ?>' );
													rentalFormCalendar.clearSelection();

												}

												<?php

											}

											// Disable rental start/end days catch if browser does not support pointer-events: none, or if the disable rental start/end days type is only start or end, not both (as in this scenario pointer-events is not used on the days)

											if ( !empty( $disable_rental_start_end_days ) || '0' == $disable_rental_start_end_days ) {

												?>

												disableRentalStartEndDays = [<?php echo esc_html( $disable_rental_start_end_days ); ?>];

												<?php

												if ( 'start_end' == $disable_rental_start_end_days_type ) {

													?>

													if ( disableRentalStartEndDays.includes( date1.getDay() ) || disableRentalStartEndDays.includes( date2.getDay() ) ) {

														alert( '<?php esc_html_e( 'Sorry, these dates cannot be selected as they start/end on the highlighted days.', 'wcrp-rental-products' ); ?>' );
														rentalFormCalendar.clearSelection();

													}

													<?php

												} elseif ( 'start' == $disable_rental_start_end_days_type ) {

													?>

													if ( disableRentalStartEndDays.includes( date1.getDay() ) ) {

														alert( '<?php esc_html_e( 'Sorry, these dates cannot be selected as they start on the highlighted days.', 'wcrp-rental-products' ); ?>' ); <?php // The highlighting is a border, but we don't say this here, as potentially it might have been changed with CSS, in that scenario it would be expected the related text setting would also be changed so no longer specifies the border, however because there isn't a text setting for this particular alert we just leave it as highlighted ?>
														rentalFormCalendar.clearSelection();

													}

													<?php

												} elseif ( 'end' == $disable_rental_start_end_days_type ) {

													?>

													if ( disableRentalStartEndDays.includes( date2.getDay() ) ) {

														alert( '<?php esc_html_e( 'Sorry, these dates cannot be selected as they end on the highlighted days.', 'wcrp-rental-products' ); ?>' ); <?php // The highlighting is a border, but we don't say this here, as potentially it might have been changed with CSS, in that scenario it would be expected the related text setting would also be changed so no longer specifies the border, however because there isn't a text setting for this particular alert we just leave it as highlighted ?>
														rentalFormCalendar.clearSelection();

													}

													<?php

												}

											}

											?>

											rentalFormUpdate();

										});

										<?php

										// On apply button click (only available if auto apply disabled)

										if ( 'no' == $auto_apply ) {

											?>

											rentalFormCalendar.on( 'button:apply', ( date1, date2 ) => {

												<?php // Allow apply button to be clicked if only rent from selected, also requires a preselect condition, see earlier in this code ?>

												if ( typeof allowApplyIfOnlyRentFromSelected !== 'undefined' && allowApplyIfOnlyRentFromSelected !== null ) {

													rentalFormCalendar.setDateRange( allowApplyIfOnlyRentFromSelected, allowApplyIfOnlyRentFromSelected );

												}

											});

											<?php

										}

										// On clear date selection e.g. when reset button used if enabled or where rentalFormCalendar.clearSelection() called

										?>

										rentalFormCalendar.on( 'clear:selection', () => {

											rentalFormReset(); <?php // Form is reset as add to cart button may have been enabled, without this resetting it's possible to add to cart using the previously selected dates even though the rental dates field is empty ?>

											<?php

											// Hide in person pick up/return fields if allowed

											if ( true == $in_person_pick_up_return_allowed ) {

												?>

												$( '#wcrp-rental-products-in-person-pick-up-return-wrap-<?php echo esc_html( $this->rental_form_id ); ?>' ).fadeOut();

												<?php

											}

											?>

										});

										<?php // On range error ?>

										rentalFormCalendar.on( 'error:range', () => {

											<?php // Show alert if dates selected include unavailable days, when this occurs the dates revert to the previously selected available dates or if no dates were selected previously has no date selected yet ?>

											alert( '<?php esc_html_e( 'Sorry, these dates cannot be selected as they include unavailable days.', 'wcrp-rental-products' ); ?>' );

											<?php // No need to clear selection as it is an error so no selection was made ?>

											<?php

											// If start day set and dates selected result in a range error reset the start day lock styles

											if ( '' !== $start_day ) {

												?>

												$( '#wcrp-rental-products-rental-form-start-day-lock-styles-<?php echo esc_html( $this->rental_form_id ); ?>' ).html( rentalFormStartDayLockStyles );

												<?php

											}

											?>

										});

									},

									lockDaysFilter: ( pickedDateFrom, pickedDateTo, pickedDates ) => {

										<?php

										// Set lock days based on pricing period multiples

										if ( 'yes' == $pricing_period_multiples ) {

											?>

											let pricingPeriod = <?php echo esc_html( $pricing_period ); ?>;

											if ( pickedDates.length === 1 ) {

												const pickedDatesClone = pickedDates[0].clone();
												let diff = Math.abs( pickedDatesClone.diff( pickedDateFrom, 'day' ) ) + 1;
												return diff % pricingPeriod !== 0;

											}

											<?php

										}

										?>

										return false;

									},

								});

								<?php // Initial rental form update to ensure specific disabled dates, availability checker population, etc is taken into account ?>

								rentalFormUpdate();

							});

						</script>

						<?php

					}

				}

			}

		}

		public function rental_form_update() {

			global $wpdb;

			$return = 'error';

			if ( isset( $_POST['nonce'] ) ) {

				if ( wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'wcrp_rental_products_rental_form_update' ) ) {

					if ( isset( $_POST['qty'] ) && isset( $_POST['product_id'] ) && isset( $_POST['rent_from'] ) && isset( $_POST['rent_to'] ) && isset( $_POST['return_days_threshold'] ) ) {

						// Set initial variables

						$quantity_needed = (int) sanitize_text_field( $_POST['qty'] );

						$product_id = sanitize_text_field( $_POST['product_id'] );

						if ( isset( $_POST['variation_id'] ) ) {

							if ( !empty( $_POST['variation_id'] ) ) {

								$product_id = sanitize_text_field( $_POST['variation_id'] );

							} else {

								// If a variable and options not yet selected

								echo 'no_product_options_selected';
								exit;

							}

						}

						$product = wc_get_product( $product_id );
						$rent_from = sanitize_text_field( $_POST['rent_from'] );
						$rent_to = sanitize_text_field( $_POST['rent_to'] );
						$return_days_threshold = sanitize_text_field( $_POST['return_days_threshold'] );

						// Check availability

						if ( isset( $quantity_needed ) ) {

							// Get total stock available for product

							$rental_stock = get_post_meta( $product_id, '_wcrp_rental_products_rental_stock', true );

							if ( '' == $rental_stock ) {

								// No need to find out if variation and get parent rental stock as parent stock field not used for the parent variable product (it gets hidden on inventory tab if variable like core WooCommerce)

								$stock_available = PHP_INT_MAX; // Unlimited rental stock

							} else {

								$stock_available = (int) $rental_stock;

							}

							// Setup the return array, if conditions are met where an array based return is required then this array is used as a starting point

							$return_array_initial = array();
							$return_array_initial['rent_from'] = $rent_from;
							$return_array_initial['rent_to'] = $rent_to;
							$return_array_initial['return_days_threshold'] = $return_days_threshold;
							$return_array_initial['disabled_dates'] = array();
							$return_array_initial['rental_stock_available_on_date'] = array();
							$return_array_initial['rental_stock_available_total'] = '';

							// If not unlimited rental stock

							if ( PHP_INT_MAX !== $stock_available ) {

								if ( $quantity_needed > $stock_available ) {

									$return = 'unavailable_stock_max_' . $stock_available; // $stock_available is the maximum available, see rentalFormUpdateAjaxRequestResponse for how this gets used to show alerts

								} else {

									// Disable dates where not enough stock on dates, we don't run every possible future date selectable in the rental form calendar through wcrp_rental_products_check_availability() as this could potentially be a huge amount of dates depending on the rental form maximum date setting, instead we get the reserved dates from the rentals database table matching the product

									// The query below returns every rental row in the rentals table that has not been removed (e.g. cancelled, refunded, etc) and gets the date and total quantity rented for that date, it includes rentals that have been marked as returned, we deal with if it has been returned and if so whether those dates should remain disabled later depending on immediate rental stock replenishment setting

									// Archive database table data not included as this has all been returned and therefore available

									$maybe_disabled_dates = $wpdb->get_results(
										$wpdb->prepare(
											"SELECT reserved_date, SUM( quantity ) AS quantity FROM `{$wpdb->prefix}wcrp_rental_products_rentals` WHERE `product_id` = %d GROUP BY reserved_date;",
											$product_id
										)
									); // This is named "maybe" because we are getting the total rented for the product on each date, then depending on the calculations below the date gets disabled

									// Return will be array based

									$return = $return_array_initial;

									// If there are disabled dates

									if ( !empty( $maybe_disabled_dates ) ) {

										foreach ( $maybe_disabled_dates as $maybe_disabled_date ) {

											// Subtract from disabled dates total if certain conditions are met as $maybe_disabled_dates does not account for immediate rental stock replenishment, in person return conditions, etc

											$maybe_disabled_date->quantity = WCRP_Rental_Products_Stock_Helpers::maybe_subtract_from_date_rented_total(
												array(
													'date'			=> $maybe_disabled_date->reserved_date,
													'product_id'	=> $product_id,
													'total'			=> $maybe_disabled_date->quantity,
												)
											);

											// If quantity needed greater than stock available

											if ( $quantity_needed > ( $stock_available - (int) $maybe_disabled_date->quantity ) ) {

												// Add date to disabled array

												$return['disabled_dates'][] = $maybe_disabled_date->reserved_date;

												// Ensure dates are disabled prior to the disabled date based off the return days threshold so someone can't rent a product which can't be returned in time, this is all the dates between the disabled date and the same disabled date minus the return days threshold

												$current = strtotime( '-' . $return_days_threshold . ' days', strtotime( $maybe_disabled_date->reserved_date ) );
												$last = strtotime( $maybe_disabled_date->reserved_date );

												while ( $current <= $last ) {

													// Add disabled date

													$return['disabled_dates'][] = gmdate( 'Y-m-d', $current );

													// Up current to next day

													$current = strtotime( '+1 day', $current );

												}

											}

										}

									}

									// Add the manually disabled rental dates and days

									$return = $this->rental_form_update_add_manually_disabled_dates_days( $return, $product );

									// Set the rental stock available on date total

									if ( '' !== $rent_from && '' !== $rent_to ) { // This condition ensures the rental_stock_available_on_date does not get populated if rent from/to not set yet (e.g. initial page load), if not rental_stock_available_on_date would be populated with 1969/1970 dates due to the strotime + days below on an empty date, which would be based on start of epoch

										// We iterate the dates starting with rent from to the rent to plus the return days threshold, this ensures that we get the rental stock available for all the dates to be used including return days

										$current = strtotime( $rent_from ); // We don't do minus return days threshold here because the earlier foreach condition for disabling dates will have already disabled them from selection as a rent from date if not enough stock
										$last = strtotime( '+' . $return_days_threshold . ' days', strtotime( $rent_to ) );

										while ( $current <= $last ) {

											// Set initial variables

											$rental_stock_available_on_date_already_set = false;

											// If the current date iteration is a disabled date

											if ( in_array( gmdate( 'Y-m-d', $current ), $return['disabled_dates'] ) ) {

												// If the disabled date is between rent from and to, this condition ensures that if it is a disabled date and it is outside rent from/to (e.g. the return days threshold after) they don't get set to 0, as returns can still happen on disabled dates and those need to be a part of $return['rental_stock_available_on_date'] so considered for $return['rental_stock_available_total']

												if ( ( gmdate( 'Y-m-d', $current ) >= $rent_from ) && ( gmdate( 'Y-m-d', $current ) <= $rent_to ) ) {

													// Rental stock available on date set to 0 as unavailable

													$return['rental_stock_available_on_date'][gmdate( 'Y-m-d', $current )] = 0;

													// Set this variable to true as already set above

													$rental_stock_available_on_date_already_set = true;

												}

											}

											if ( false == $rental_stock_available_on_date_already_set ) {

												// Get total rented on date

												$total_rented_on_date = WCRP_Rental_Products_Stock_Helpers::total_rented_on_date(
													array(
														'date'			=> gmdate( 'Y-m-d', $current ),
														'product_id'	=> $product_id,
													)
												);

												// Add date with rental stock available

												$return['rental_stock_available_on_date'][gmdate( 'Y-m-d', $current )] = $stock_available - (int) $total_rented_on_date;

											}

											// Up current to next day

											$current = strtotime( '+1 day', $current );

										}

									}

									// Set the rental stock available total

									if ( !empty( $return['rental_stock_available_on_date'] ) ) { // Stops uncaught ValueError: min() must contain at least one element if $return['rental_stock_available_on_date'] = array()

										$return['rental_stock_available_total'] = min( $return['rental_stock_available_on_date'] ); // The lowest rental stock from all the dates is the total rental stock available across all the dates selected (including return days threshold), if rental_stock_available_on_date is empty this returns '', the available rental stock display does not display in this scenario

									} else {

										$return['rental_stock_available_total'] = 0;

									}

								}

							} else { // If is unlimited rental stock

								// Set the rental stock return data in the return array

								$return = $return_array_initial;
								$return = $this->rental_form_update_add_manually_disabled_dates_days( $return, $product ); // Add the manually disabled rental dates and days
								$return['rental_stock_available_total'] = 'unlimited'; // Set the rental stock available total, rental stock available on date total remains empty (as in initial array) as unlimited

							}

						}

					}

				}

			}

			if ( is_array( $return ) ) {

				$return['disabled_dates'] = array_values( array_unique( $return['disabled_dates'] ) ); // Removes duplicate disabled dates via array_unique, as that returns key => values we then use array_values to remove the keys (duplicates disabled dates may exist e.g. by an already disabled date through availability also being manually disabled)
				sort( $return['disabled_dates'] ); // Sorts the disabled dates as may not be in order due to being added to the array at different points in the above
				$return = map_deep( $return, 'wp_kses_post' );

			}

			echo ( is_array( $return ) ? wp_json_encode( $return ) : esc_html( $return ) ); // This is done like this as if changed in the is_array and in a !is_array condition above it would need an escape which can't be done as 2 different types, so would be codesniff fail

			exit;

		}

		public function rental_form_advanced_pricing() {

			$return = 'error';

			if ( isset( $_POST['nonce'] ) ) {

				if ( wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'wcrp_rental_products_rental_form_advanced_pricing' ) ) {

					if ( isset( $_POST['data'] ) ) {

						$data = array_map( 'sanitize_text_field', $_POST['data'] );
						$quantity = (int) $data['quantity'];
						$total = (float) $data['total'];
						$total_filtered = (float) apply_filters( 'wcrp_rental_products_advanced_pricing', $total, $data );

						// If total or cart_item_price is a long decimal point number like 18.3299999999999982946974341757595539093017578125, when the JSON data is used it will get rounded automatically by JS

						$return = wp_json_encode(
							array(
								'total'				=> $total_filtered,
								'cart_item_price'	=> $total_filtered / $quantity,
								'changed'			=> ( $total == $total_filtered ? 'no' : 'yes' ), // Flags whether changes have occurred
							)
						);

					}

				}

			}

			echo wp_kses_post( $return ); // wp_kses_post() used over esc_html() as the latter converts JSON quotes into &quot;

			exit;

		}

		public function rental_form_availability_checker_population() {

			$return = '';

			if ( isset( $_POST['nonce'] ) ) {

				if ( wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'wcrp_rental_products_rental_form_availability_checker_population' ) ) {

					$availability_checker_data = wcrp_rental_products_availability_checker_data();

					if ( !empty( $availability_checker_data ) ) {

						if ( isset( $_POST['product_id'] ) && isset( $_POST['product_sold_individually'] ) && isset( $_POST['pricing_period'] ) && isset( $_POST['pricing_period_selection_rent_period'] ) && isset( $_POST['pricing_period_additional_selections_array'] ) && isset( $_POST['pricing_type'] ) && isset( $_POST['minimum_days'] ) && isset( $_POST['maximum_days'] ) ) {

							$product_id = sanitize_text_field( $_POST['product_id'] );
							$product_sold_individually = sanitize_text_field( $_POST['product_sold_individually'] );
							$pricing_period = sanitize_text_field( $_POST['pricing_period'] );
							$pricing_period_selection_rent_period = sanitize_text_field( $_POST['pricing_period_selection_rent_period'] );
							$pricing_period_additional_selections_array = json_decode( sanitize_text_field( $_POST['pricing_period_additional_selections_array'] ), true );
							$pricing_type = sanitize_text_field( $_POST['pricing_type'] );
							$minimum_days = sanitize_text_field( $_POST['minimum_days'] );
							$maximum_days = sanitize_text_field( $_POST['maximum_days'] );

							$availability_checker_population_dates = true;
							$availability_checker_days_total = (string) WCRP_Rental_Products_Misc::days_total_from_dates( $availability_checker_data['rent_from'], $availability_checker_data['rent_to'] );

							if ( !WCRP_Rental_Products_Misc::string_starts_with( wcrp_rental_products_check_availability( $product_id, $availability_checker_data['rent_from'], $availability_checker_data['rent_to'], $availability_checker_data['quantity'], array() ), 'unavailable' ) ) {

								if ( 'period_selection' == $pricing_type ) {

									if ( isset( $pricing_period_additional_selections_array[$availability_checker_days_total] ) || $availability_checker_days_total == $pricing_period ) { // The or part of this condition is done because the default pricing period isn't in the additional selections array

										if ( $pricing_period_selection_rent_period !== $availability_checker_days_total && $minimum_days !== $availability_checker_days_total && $maximum_days !== $availability_checker_days_total ) { // The latter 2 conditions are done because $pricing_period_selection_rent_period is '' if the default option, so we need an extra check that results in the append for if the default option is currently selected

											$return = array(
												'type' => 'populate_period_selection', // See the conditions related to this type in the rental form for what it and the conditions above do
											);

											$availability_checker_population_dates = false;

										}

									}

								}

								if ( true == $availability_checker_population_dates ) {

									if ( 'no' == get_option( 'wcrp_rental_products_availability_checker_quantity' ) || 'yes' == $product_sold_individually ) {

										$return = array(
											'type' => 'populate_dates_1', // See the conditions related to this type in the rental form for what it and the conditions above do
										);

									} else {

										$return = array(
											'type' => 'populate_dates_2', // See the conditions related to this type in the rental form for what it and the conditions above do
										);

									}

								}

								$return['rent_from'] = $availability_checker_data['rent_from'];
								$return['rent_to'] = $availability_checker_data['rent_to'];
								$return['days_total'] = $availability_checker_days_total;
								$return['quantity'] = $availability_checker_data['quantity'];

							}

						}

					}

				}

			}

			echo ( is_array( $return ) ? wp_json_encode( $return ) : esc_html( $return ) );

			exit;

		}

		public function rental_form_disable_addons( $addons, $product_id ) {

			/*
			This function disables add-ons conditionally for either or both parts of rental or purchase products if the disable addons product options are enabled, there is no need to check if WooCommerce Product Add-ons active as this filter hook is only run if that extension is active

			Important to note that there are some limitations to disabling add-ons (information on this is shown when these product options are enabled) e.g.

			If global product add-ons are used and have required fields then:

			If disable add-ons for rental part of rental or purchase products is enabled, then the add-ons won't show on the rental part, but if you try to add to cart it will state that the global add-on is required, this is also the same for the other disable add-ons option

			This does not effect product level add-ons

			The reason why this occurs is because WC_Product_Addons_Helper::get_product_addons() uses the get_product_addons_fields filter hook this function is hooked on, however for global product add-ons it doesn't pass the product id, it passes the global product add-on post id, for the product page we can get the product id from global $post if the ID passed is the global_product_addon post type (as shown below), however it's not possible to do this when on cart/checkout/during add to cart from the product page, as it returns a null global $post, this means we can't accurately get the product id in all places needed to then get the product meta to check if a rental or purchase and get the disable add-ons meta from that product to succesfully remove the required data conditionally to allow it to be added to cart, this is done succesfully below for product level add-ons as $product_id is set accurately at all times

			Separately, note that there is also a get_parent_product_addons_fields filter hook (as opposed to the get_product_addons_fields filter hook this function is hooked on), we don't filter add-ons through get_parent_product_addons_fields, it just appears to be for returning back the add-ons for each product in a grouped product, however add-ons aren't shown in that context, and users just click through to rentals before selecting any add-ons, so don't need to filter it
			*/

			global $post;

			if ( 'global_product_addon' == get_post_type( $product_id ) ) {

				if ( !empty( $post ) ) { // Catches null global $post, if null causes PHP warning, can be null in cart related scenarios, see comment above

					$product_id = $post->ID; // This ensures global product add-ons are conditionally disabled later in this code, with the limitation that if required global product add-ons are used the entire product won't be able to be added to cart due to the required add-ons existing even though not shown, see comment above why

				}

			}

			if ( wcrp_rental_products_is_rental_purchase( $product_id ) ) {

				$rent = false;

				if ( isset( $_GET['rent'] ) ) {

					if ( '1' == $_GET['rent'] ) {

						$rent = true;

					}

				}

				$default_rental_options = wcrp_rental_products_default_rental_options();

				$disable_addons_rental_purchase_rental = get_post_meta( $product_id, '_wcrp_rental_products_disable_addons_rental_purchase_rental', true );
				$disable_addons_rental_purchase_rental = ( '' !== $disable_addons_rental_purchase_rental ? $disable_addons_rental_purchase_rental : $default_rental_options['_wcrp_rental_products_disable_addons_rental_purchase_rental'] );

				$disable_addons_rental_purchase_purchase = get_post_meta( $product_id, '_wcrp_rental_products_disable_addons_rental_purchase_purchase', true );
				$disable_addons_rental_purchase_purchase = ( '' !== $disable_addons_rental_purchase_purchase ? $disable_addons_rental_purchase_purchase : $default_rental_options['_wcrp_rental_products_disable_addons_rental_purchase_purchase'] );

				if ( is_product() ) {

					// If it is a product page we return the add ons empty if disabled depending on the part of the product so they do not get used

					if ( 'yes' == $disable_addons_rental_purchase_rental ) {

						if ( true == $rent ) {

							$addons = array();

						}

					}

					if ( 'yes' == $disable_addons_rental_purchase_purchase ) {

						if ( false == $rent ) {

							$addons = array();

						}

					}

				} else {

					// If it is not a product page, e.g. when product add-ons got during the cart and run through validation checks, we need to ensure any required fields are manipulated to not be required, this is because the validation causes an error if there are required add-ons which aren't on the cart item, but in some scenarios they shouldn't be there, e.g. for rental or purchase products where the product has required add-ons but the add-ons are being disabled for the purchase part of the product and the purchase part of the product is in cart (without the below the validation check thinks there is a product in cart that should have required add-ons but doesn't)

					if ( !empty( $addons ) ) {

						if ( 'yes' == $disable_addons_rental_purchase_rental || 'yes' == $disable_addons_rental_purchase_purchase ) {

							foreach ( $addons as $addon_key => $addon ) {

								if ( isset( $addon['required'] ) ) {

									$addons[$addon_key]['required'] = false; // See comment above why, note this isn't possible for global product add-ons, see comment at start of this function why

								}

							}

						}

					}

				}

			}

			return $addons;

		}

		public function rental_form_add_to_order() {

			$return = __( 'Sorry, product could not be added to order due to an error.', 'wcrp-rental-products' );

			if ( isset( $_POST['nonce'] ) ) {

				if ( wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'wcrp_rental_products_rental_form_add_to_order' ) ) {

					if ( isset( $_POST['qty'] ) && isset( $_POST['order_id'] ) && isset( $_POST['product_id'] ) && isset( $_POST['cart_item_price'] ) && isset( $_POST['rent_from'] ) && isset( $_POST['rent_to'] ) && isset( $_POST['return_days_threshold'] ) ) {

						if ( !current_user_can( 'manage_woocommerce' ) ) {

							// This condition was added to ensure the user has the correct permissions to be able to add a product to an order (e.g. stopping non-logged in users from manipulating the AJAX code or logged in users logging out via the popup and logging in as a different user), if the user logs out of the dashboard within the popup they get the general error message above (unsure exactly why they don't get this far but it's okay), if the user is logged in but doesn't have the correct permissions it shows this message (e.g. if they try to log out via the popup and log back in as a user without manage_woocommerce capabilities), guests in any scenario cannot add products to the order

							$return = __( 'Sorry, product could not be added to order due to insufficient permissions.', 'wcrp-rental-products' );

						} else {

							// Initial data

							$qty = sanitize_text_field( $_POST['qty'] );
							$order_id = sanitize_text_field( $_POST['order_id'] );
							$product_id = sanitize_text_field( $_POST['product_id'] );
							$cart_item_price = sanitize_text_field( $_POST['cart_item_price'] );
							$rent_from = sanitize_text_field( $_POST['rent_from'] );
							$rent_to = sanitize_text_field( $_POST['rent_to'] );
							$return_days_threshold = sanitize_text_field( $_POST['return_days_threshold'] );
							$in_person_pick_up_return = ( isset( $_POST['in_person_pick_up_return'] ) ? sanitize_text_field( $_POST['in_person_pick_up_return'] ) : 'no' );
							$in_person_pick_up_date = ( 'yes' == $in_person_pick_up_return && isset( $_POST['in_person_pick_up_date'] ) ? sanitize_text_field( $_POST['in_person_pick_up_date'] ) : false );
							$in_person_pick_up_time = ( 'yes' == $in_person_pick_up_return && isset( $_POST['in_person_pick_up_time'] ) ? sanitize_text_field( $_POST['in_person_pick_up_time'] ) : false );
							$in_person_pick_up_fee = ( 'yes' == $in_person_pick_up_return && isset( $_POST['in_person_pick_up_fee'] ) ? sanitize_text_field( $_POST['in_person_pick_up_fee'] ) : false );
							$in_person_return_date = ( 'yes' == $in_person_pick_up_return && isset( $_POST['in_person_return_date'] ) ? sanitize_text_field( $_POST['in_person_return_date'] ) : false );
							$in_person_return_date_type = ( isset( $_POST['in_person_return_date_type'] ) ? sanitize_text_field( $_POST['in_person_return_date_type'] ) : false );
							$in_person_return_time = ( 'yes' == $in_person_pick_up_return && isset( $_POST['in_person_return_time'] ) ? sanitize_text_field( $_POST['in_person_return_time'] ) : false );
							$in_person_return_fee = ( 'yes' == $in_person_pick_up_return && isset( $_POST['in_person_return_fee'] ) ? sanitize_text_field( $_POST['in_person_return_fee'] ) : false );
							$taxes_enabled = get_option( 'woocommerce_calc_taxes' );

							$order = wc_get_order( $order_id );

							if ( isset( $_POST['variation_id'] ) ) {

								if ( !empty( sanitize_text_field( $_POST['variation_id'] ) ) ) {

									$product_id = sanitize_text_field( $_POST['variation_id'] ); // Product ID gets set to the variation ID if a variation

								}

							}

							$product = wc_get_product( $product_id );
							$product_name = $product->get_name();
							$product_sku = $product->get_sku();

							// Check availability

							if ( 'available' == wcrp_rental_products_check_availability( $product_id, $rent_from, $rent_to, $qty, array( 'rental_form_add_to_order_checks' => array( 'rent_from_past_date' ) ) ) ) {

								// If in person pick up/return add fees

								if ( 'yes' == $in_person_pick_up_return ) {

									$cart_item_price = $cart_item_price + (float) $in_person_pick_up_fee + (float) $in_person_return_fee;

								}

								// Manipulate cart item price depending on tax settings

								if ( 'yes' == $taxes_enabled ) {

									$tax_status = $product->get_tax_status();

									if ( 'taxable' == $tax_status ) {

										$tax_class = $product->get_tax_class();
										$tax_display_shop = get_option( 'woocommerce_tax_display_shop' );

										$taxes = WC_Tax::get_rates( $tax_class );

										if ( !empty( $taxes ) ) { // This ensures array_shift does not cause fatal error if empty, WooCommerce Tax extension can return this empty when the automated taxes option is enabled

											$tax_rates = array_shift( $taxes );
											$tax_rate = array_shift( $tax_rates );

											if ( 'incl' == $tax_display_shop ) {

												$cart_item_price = (float) $cart_item_price / ( 1 + ( $tax_rate / 100 ) ); // Remove the tax as the price used is before tax and tax calculated in tax column separately

											}

										}

									}

								}

								// Add product to order

								$total = (float) $cart_item_price * (int) $qty;
								$order_item_id = $order->add_product( $product, $qty, array( 'subtotal' => $total, 'total' => $total ) ); // Sub total and total same

								$order->add_order_note(
									esc_html__( 'Line item added:', 'wcrp-rental-products' ) . ' ' . $product_name . ( !empty( $product_sku ) ? ' ' . esc_html__( '(', 'wcrp-rental-products' ) . $product_sku . esc_html__( ')', 'wcrp-rental-products' ) : '' ) . esc_html__( '.', 'wcrp-rental-products' ), // Line item added is consistent messaging to WooCommerce add purchasable product messaging
									false,
									true
								);

								if ( !empty( $order_item_id ) ) {

									// Add order item meta

									wc_add_order_item_meta( $order_item_id, 'wcrp_rental_products_rent_from', $rent_from, true );
									wc_add_order_item_meta( $order_item_id, 'wcrp_rental_products_rent_to', $rent_to, true );
									wc_add_order_item_meta( $order_item_id, 'wcrp_rental_products_return_days_threshold', $return_days_threshold, true );

									// Add in person pick up/return order item meta

									if ( 'yes' == $in_person_pick_up_return ) {

										wc_add_order_item_meta( $order_item_id, 'wcrp_rental_products_in_person_pick_up_return', $in_person_pick_up_return, true );
										wc_add_order_item_meta( $order_item_id, 'wcrp_rental_products_in_person_pick_up_date', $in_person_pick_up_date, true );
										wc_add_order_item_meta( $order_item_id, 'wcrp_rental_products_in_person_pick_up_time', $in_person_pick_up_time, true );
										wc_add_order_item_meta( $order_item_id, 'wcrp_rental_products_in_person_pick_up_fee', $in_person_pick_up_fee, true );
										wc_add_order_item_meta( $order_item_id, 'wcrp_rental_products_in_person_return_date', $in_person_return_date, true );
										wc_add_order_item_meta( $order_item_id, 'wcrp_rental_products_in_person_return_date_type', $in_person_return_date_type, true );
										wc_add_order_item_meta( $order_item_id, 'wcrp_rental_products_in_person_return_time', $in_person_return_time, true );
										wc_add_order_item_meta( $order_item_id, 'wcrp_rental_products_in_person_return_fee', $in_person_return_fee, true );

									}

									// Update rentals table

									WCRP_Rental_Products_Order_Save::rentals_add_update( $order_id, $order );

									// Add security deposits

									$security_deposit = WCRP_Rental_Products_Cart_Fees::prepare_security_deposits_array( array(), $qty, sanitize_text_field( $_POST['product_id'] ), ( isset( $_POST['variation_id'] ) ? sanitize_text_field( $_POST['variation_id'] ) : false ) ); // IMPORTANT - this specifically does not use the $product_id variable as this can be either the product or variation id, so the original $_POST data for both IDs is used here instead, variation might not be passed as part of the AJAX data if not variation hence the ternary condition here

									if ( !empty( $security_deposit ) ) {

										// The array will always contain only 1 value in the add product to order context

										$fee = new WC_Order_Item_Fee();
										$fee->set_name( $security_deposit[0]['name'] );
										$fee->set_amount( $security_deposit[0]['amount'] );
										$fee->set_tax_class( $security_deposit[0]['tax_class'] );
										$fee->set_tax_status( $security_deposit[0]['tax_status'] );
										$fee->set_total( $security_deposit[0]['amount'] );

										$order->add_item( $fee );
										$order->save();

									}

									$return = __( 'Product added to order, continue adding more rental products or close this popup to refresh the items within the order. Once closed, it is recommended the recalculate button is used to recalculate all totals after making changes to the order contents.', 'wcrp-rental-products' );

								}

							} else {

								$return = __( 'Sorry, product could not be added to order due to availability, it may have become unavailable recently prior to the add to order request.', 'wcrp-rental-products' );

							}

						}

					}

				}

			}

			echo esc_html( $return );

			exit;

		}

		public function ajax_variation_threshold( $qty, $product ) {

			$product_id = $product->get_id();

			if ( wcrp_rental_products_is_rental_only( $product_id ) || wcrp_rental_products_is_rental_purchase( $product_id ) ) {

				$return = PHP_INT_MAX; // If a variable product has more than a specific number of variations ($qty) then it uses ajax to load the variation data instead of outputting them in the .variations_form element's data-product_variations attribute, this must not occur for rentals as the data-product_variations data is used (if this limit is not lifted data-product_variations is set to false)

			} else {

				$return = $qty;

			}

			return $return;

		}

		public static function rental_form_calendar_styling_defaults() {

			// These are copied as is from Litepickers root variables in inspector (note that --litepicker-mobilefriendly-backdrop-color-bg included at the end is in a separate root code block as from a plugin), this is solely used to reset the styling to defaults in settings and for initial population during upgrade, there is no need to include !important on these as they get included after Litepicker

			return trim( preg_replace( '/^\h+|\h+$/m', '', '
			--litepicker-container-months-color-bg: #fff;
			--litepicker-container-months-box-shadow-color: #ddd;
			--litepicker-footer-color-bg: #fafafa;
			--litepicker-footer-box-shadow-color: #ddd;
			--litepicker-tooltip-color-bg: #fff;
			--litepicker-month-header-color: #333;
			--litepicker-button-prev-month-color: #9e9e9e;
			--litepicker-button-next-month-color: #9e9e9e;
			--litepicker-button-prev-month-color-hover: #2196f3;
			--litepicker-button-next-month-color-hover: #2196f3;
			--litepicker-month-width: calc(var(--litepicker-day-width) * 7);
			--litepicker-month-weekday-color: #9e9e9e;
			--litepicker-month-week-number-color: #9e9e9e;
			--litepicker-day-width: 38px;
			--litepicker-day-color: #333;
			--litepicker-day-color-hover: #2196f3;
			--litepicker-is-today-color: #f44336;
			--litepicker-is-in-range-color: #bbdefb;
			--litepicker-is-locked-color: #9e9e9e;
			--litepicker-is-start-color: #fff;
			--litepicker-is-start-color-bg: #2196f3;
			--litepicker-is-end-color: #fff;
			--litepicker-is-end-color-bg: #2196f3;
			--litepicker-button-cancel-color: #fff;
			--litepicker-button-cancel-color-bg: #9e9e9e;
			--litepicker-button-apply-color: #fff;
			--litepicker-button-apply-color-bg: #2196f3;
			--litepicker-button-reset-color: #909090;
			--litepicker-button-reset-color-hover: #2196f3;
			--litepicker-highlighted-day-color: #333;
			--litepicker-highlighted-day-color-bg: #ffeb3b;
			' ) );

		}

		public function rental_form_update_add_manually_disabled_dates_days( $return, $product ) {

			// This adds the manually disabled dates and days (not rental start/end dates/days as these do not end up as lockDays), parts of this functionality below is similar to code in wcrp_rental_products_check_availability(), updates here may need to be reflected there

			$product_type = $product->get_type();
			$default_rental_options = wcrp_rental_products_default_rental_options();

			// Manually disabled dates

			$manually_disabled_dates_global = get_option( 'wcrp_rental_products_disable_rental_dates' );

			if ( 'variation' == $product_type ) {

				$manually_disabled_dates_product = get_post_meta( $product->get_parent_id(), '_wcrp_rental_products_disable_rental_dates', true );
				$manually_disabled_dates_product = ( '' !== $manually_disabled_dates_product ? $manually_disabled_dates_product : $default_rental_options['_wcrp_rental_products_disable_rental_dates'] );

			} else {

				$manually_disabled_dates_product = get_post_meta( $product->get_id(), '_wcrp_rental_products_disable_rental_dates', true );
				$manually_disabled_dates_product = ( '' !== $manually_disabled_dates_product ? $manually_disabled_dates_product : $default_rental_options['_wcrp_rental_products_disable_rental_dates'] );

			}

			if ( !empty( $manually_disabled_dates_global ) ) {

				$manually_disabled_dates_global = explode( ',', $manually_disabled_dates_global );

				if ( !empty( $manually_disabled_dates_global ) ) {

					foreach ( $manually_disabled_dates_global as $manually_disabled_date_global ) {

						$return['disabled_dates'][] = $manually_disabled_date_global;

					}

				}

			}

			if ( !empty( $manually_disabled_dates_product ) ) {

				$manually_disabled_dates_product = explode( ',', $manually_disabled_dates_product );

				if ( !empty( $manually_disabled_dates_product ) ) {

					foreach ( $manually_disabled_dates_product as $manually_disabled_date_product ) {

						$return['disabled_dates'][] = $manually_disabled_date_product;

					}

				}

			}

			// Manually disabled days

			if ( 'variation' == $product_type ) {

				$manually_disabled_days = get_post_meta( $product->get_parent_id(), '_wcrp_rental_products_disable_rental_days', true );
				$manually_disabled_days = ( '' !== $manually_disabled_days ? $manually_disabled_days : $default_rental_options['_wcrp_rental_products_disable_rental_days'] );

			} else {

				$manually_disabled_days = get_post_meta( $product->get_id(), '_wcrp_rental_products_disable_rental_days', true );
				$manually_disabled_days = ( '' !== $manually_disabled_days ? $manually_disabled_days : $default_rental_options['_wcrp_rental_products_disable_rental_days'] );

			}

			$manually_disabled_days = explode( ',', $manually_disabled_days );

			if ( !empty( $manually_disabled_days ) ) {

				foreach ( $manually_disabled_days as $manually_disabled_day ) {

					$now = strtotime( 'now' );

					while ( gmdate( 'Y-m-d', $now ) !== wcrp_rental_products_rental_form_maximum_date( 'date' ) ) {

						$day_index = gmdate( 'w', $now );

						if ( $day_index == $manually_disabled_day ) {

							$return['disabled_dates'][] = gmdate( 'Y-m-d', $now );

						}

						$now = strtotime( gmdate( 'Y-m-d', $now ) . '+1 day' );

					}

				}

			}

			// Return array

			return $return;

		}

	}

}
