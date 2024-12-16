<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Availability_Checker' ) ) {

	class WCRP_Rental_Products_Availability_Checker {

		public function __construct() {

			add_action( 'wp_loaded', array( $this, 'cookies' ) );
			add_action( 'wp_ajax_wcrp_rental_products_availability_checker_display', array( $this, 'display_ajax' ) );
			add_action( 'wp_ajax_nopriv_wcrp_rental_products_availability_checker_display', array( $this, 'display_ajax' ) );
			add_action( 'wp_ajax_wcrp_rental_products_availability_checker_statuses', array( $this, 'statuses_ajax' ) );
			add_action( 'wp_ajax_nopriv_wcrp_rental_products_availability_checker_statuses', array( $this, 'statuses_ajax' ) );

		}

		public function cookies() {

			$set_cookies = false;
			$unset_cookies = false;
			$unset_rental_form_auto_population_reset_query_var = false;

			if ( isset( $_POST['wcrp_rental_products_availability_checker_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['wcrp_rental_products_availability_checker_nonce'] ), 'wcrp_rental_products_availability_checker' ) ) {

				if ( isset( $_POST['wcrp_rental_products_availability_checker_rent_from'] ) && isset( $_POST['wcrp_rental_products_availability_checker_rent_to'] ) && isset( $_POST['wcrp_rental_products_availability_checker_quantity'] ) ) {

					// If setting from availability checker unapplied form

					$set_cookies = true;

				} elseif ( isset( $_POST['wcrp_rental_products_availability_checker_reset'] ) ) {

					// If resetting from availability checker applied form

					$unset_cookies = true;

				}

			} else {

				// If resetting from rental form auto population reset link

				if ( isset( $_GET['wcrp_rental_products_availability_checker_reset'] ) ) {

					$unset_cookies = true;
					$unset_rental_form_auto_population_reset_query_var = true;

				}

			}

			// If rent from is less than the current date unset cookies, as previously set dates are no longer available as in past

			if ( isset( $_COOKIE['wcrp_rental_products_availability_checker_rent_from'] ) ) {

				if ( strtotime( sanitize_text_field( $_COOKIE['wcrp_rental_products_availability_checker_rent_from'] ) ) < strtotime( 'today midnight' ) ) {

					$unset_cookies = true;

				}

			}

			if ( true == $set_cookies ) {

				// Set cookies for 30 days

				setcookie( 'wcrp_rental_products_availability_checker_rent_from', sanitize_text_field( $_POST['wcrp_rental_products_availability_checker_rent_from'] ), time() + 60 * 60 * 24 * 30, '/' );
				setcookie( 'wcrp_rental_products_availability_checker_rent_to', sanitize_text_field( $_POST['wcrp_rental_products_availability_checker_rent_to'] ), time() + 60 * 60 * 24 * 30, '/' );
				setcookie( 'wcrp_rental_products_availability_checker_quantity', sanitize_text_field( $_POST['wcrp_rental_products_availability_checker_quantity'] ), time() + 60 * 60 * 24 * 30, '/' );

				// Maybe redirect

				$apply_dates_redirect = get_option( 'wcrp_rental_products_availability_checker_apply_dates_redirect' );

				if ( '' !== $apply_dates_redirect ) {

					wp_safe_redirect( esc_url_raw( $apply_dates_redirect ), 302 ); // esc_url_raw over esc_url as the former replaces entities like & when in a redirect, https://developer.wordpress.org/reference/functions/esc_url_raw/ - states to use esc_url_raw for redirects, 302 temporary redirect used, shouldn't matter whether 301 or 302 as only applies if cookies set which search engines won't have, however using 302 just to ensure URL of page before direct can't be remembered as a permanent redirect for any reason
					exit;

				}

			} elseif ( true == $unset_cookies ) {

				// Unset cookies

				unset( $_COOKIE['wcrp_rental_products_availability_checker_rent_from'] ); // PHP cookie
				setcookie( 'wcrp_rental_products_availability_checker_rent_from', '', time() - 3600, '/' ); // Browser cookie

				unset( $_COOKIE['wcrp_rental_products_availability_checker_rent_to'] ); // PHP cookie
				setcookie( 'wcrp_rental_products_availability_checker_rent_to', '', time() - 3600, '/' ); // Browser cookie

				unset( $_COOKIE['wcrp_rental_products_availability_checker_quantity'] ); // PHP cookie
				setcookie( 'wcrp_rental_products_availability_checker_quantity', '', time() - 3600, '/' ); // Browser cookie

				// Unset rental form auto population reset query var

				if ( true == $unset_rental_form_auto_population_reset_query_var ) {

					// If the reset link is used from the rental form auto population, we remove the wcrp_rental_products_availability_checker_reset query var, just incase the availability checker is somewhere on the page, e.g. in the header, if they used it to apply dates again because the wcrp_rental_products_availability_checker_reset query var is still there it would be resetting it again immediately

					wp_safe_redirect( esc_url_raw( remove_query_arg( 'wcrp_rental_products_availability_checker_reset' ) ), 302 ); // esc_url_raw and 302 done for the same reasons as other redirects in this function
					exit;

				}

			}

		}

		public static function display( $atts = array() ) {

			if ( is_cart() || is_checkout() ) { // Availability checker is not shown on products in the cart (e.g. if the website has a you may also like section on the cart) as products have already been selected at this point and it also uses the WooCommerce notice CSS classes and WooCommerce moves notices with these to the top of the page on cart when emptied, we have also included the checkout condition here for completeness just incase any themes/plugins/custom dev work displays products there

				return '';

			}

			ob_start();

			$availability_checker_id = uniqid(); // Ensures if multiple availability checkers on page the elements within are targeted instead of the other instances - form names do not matter as submitted
			$availability_checker_data = wcrp_rental_products_availability_checker_data();
			$availability_checker_auto_apply = get_option( 'wcrp_rental_products_rental_form_auto_apply' );
			$availability_checker_minimum_days = get_option( 'wcrp_rental_products_availability_checker_minimum_days' );
			$availability_checker_maximum_days = get_option( 'wcrp_rental_products_availability_checker_maximum_days' );
			$availability_checker_period_multiples = get_option( 'wcrp_rental_products_availability_checker_period_multiples' );
			$availability_checker_quantity = get_option( 'wcrp_rental_products_availability_checker_quantity' );
			$availability_checker_reset_button = get_option( 'wcrp_rental_products_rental_form_reset_button' );

			// Availability checker start

			echo '<div id="wcrp-rental-products-availability-checker-' . esc_html( $availability_checker_id ) . '" class="wcrp-rental-products-availability-checker">';

			if ( !empty( $availability_checker_data ) ) {

				// Availability checker applied start

				echo '<div class="wcrp-rental-products-availability-checker-applied">';

				// Notice

				echo '<div class="wcrp-rental-products-availability-checker-applied-notice woocommerce-message">' . esc_html( apply_filters( 'wcrp_rental_products_text_availability_checker_applied', get_option( 'wcrp_rental_products_text_availability_checker_applied' ) ) ) . '</div>';

				// Availability checker applied info start

				echo '<div class="wcrp-rental-products-availability-checker-applied-info">';

				// Availability checker applied info row rent from

				echo '<div class="wcrp-rental-products-availability-checker-applied-info-row wcrp-rental-products-availability-checker-status-applied-info-row-rent-from">';
				echo '<span class="wcrp-rental-products-availability-checker-applied-info-row-heading wcrp-rental-products-availability-checker-applied-info-row-heading-rent-from">';
				echo esc_html( apply_filters( 'wcrp_rental_products_text_rent_from', get_option( 'wcrp_rental_products_text_rent_from' ) ) );
				echo '</span>';
				echo '<span class="wcrp-rental-products-availability-checker-applied-info-row-value wcrp-rental-products-availability-checker-applied-info-row-value-rent-from">';
				echo esc_html( date_i18n( wcrp_rental_products_rental_date_format(), strtotime( $availability_checker_data['rent_from'] ) ) );
				echo '</span>';
				echo '</div>';

				// Availability checker applied info row rent to

				echo '<div class="wcrp-rental-products-availability-checker-applied-info-row wcrp-rental-products-availability-checker-status-applied-info-row-rent-to">';
				echo '<span class="wcrp-rental-products-availability-checker-applied-info-row-heading wcrp-rental-products-availability-checker-applied-info-row-heading-rent-to">';
				echo esc_html( apply_filters( 'wcrp_rental_products_text_rent_to', get_option( 'wcrp_rental_products_text_rent_to' ) ) );
				echo '</span>';
				echo '<span class="wcrp-rental-products-availability-checker-applied-info-row-value wcrp-rental-products-availability-checker-applied-info-row-value-rent-to">';
				echo esc_html( date_i18n( wcrp_rental_products_rental_date_format(), strtotime( $availability_checker_data['rent_to'] ) ) );
				echo '</span>';
				echo '</div>';

				// Availability checker applied info row quantity

				if ( 'yes' == $availability_checker_quantity ) {

					echo '<div class="wcrp-rental-products-availability-checker-applied-info-row wcrp-rental-products-availability-checker-status-applied-info-row-quantity">';
					echo '<span class="wcrp-rental-products-availability-checker-applied-info-row-heading wcrp-rental-products-availability-checker-applied-info-row-heading-quantity">';
					echo esc_html__( 'Quantity', 'wcrp-rental-products' );
					echo '</span>';
					echo '<span class="wcrp-rental-products-availability-checker-applied-info-row-value wcrp-rental-products-availability-checker-applied-info-row-value-quantity">';
					echo esc_html( $availability_checker_data['quantity'] );
					echo '</span>';
					echo '</div>';

				}

				// Availability checker applied info end

				echo '</div>';

				// Availability checker applied form start

				echo '<form id="wcrp-rental-products-availability-checker-applied-form-' . esc_html( $availability_checker_id ) . '" class="wcrp-rental-products-availability-checker-applied-form" method="post">';

				// Availability checker applied form hidden fields

				wp_nonce_field( 'wcrp_rental_products_availability_checker', 'wcrp_rental_products_availability_checker_nonce' );

				// Availability checker applied form submit

				echo '<button type="submit" id="wcrp-rental-products-availability-checker-reset-' . esc_html( $availability_checker_id ) . '" class="wcrp-rental-products-availability-checker-button button ' . esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ) . '" name="wcrp_rental_products_availability_checker_reset" value="1">' . esc_html( apply_filters( 'wcrp_rental_products_text_reset_dates', get_option( 'wcrp_rental_products_text_reset_dates' ) ) ) . '</button>';

				// Availability checker applied form end

				echo '</form>';

				// Availability checker applied end

				echo '</div>';

			} else {

				// Availability checker unapplied start

				echo '<div class="wcrp-rental-products-availability-checker-unapplied">';

				// Availability checker unapplied form start

				echo '<form id="wcrp-rental-products-availability-checker-unapplied-form-' . esc_html( $availability_checker_id ) . '" class="wcrp-rental-products-availability-checker-unapplied-form" method="post">';

				// Availability checker unapplied form hidden fields

				wp_nonce_field( 'wcrp_rental_products_availability_checker', 'wcrp_rental_products_availability_checker_nonce' );
				echo '<input type="hidden" id="wcrp-rental-products-availability-checker-rent-from-' . esc_html( $availability_checker_id ) . '" class="wcrp-rental-products-availability-checker-rent-from" name="wcrp_rental_products_availability_checker_rent_from" readonly="readonly">';
				echo '<input type="hidden" id="wcrp-rental-products-availability-checker-rent-to-' . esc_html( $availability_checker_id ) . '" class="wcrp-rental-products-availability-checker-rent-to" name="wcrp_rental_products_availability_checker_rent_to" readonly="readonly">';

				// Availability checker unapplied form dates

				echo '<div class="wcrp-rental-products-availability-checker-unapplied-form-field wcrp-rental-products-availability-checker-unapplied-form-field-dates">';
				echo '<label class="wcrp-rental-products-availability-checker-unapplied-form-field-label wcrp-rental-products-availability-checker-unapplied-form-field-label-dates" for="wcrp-rental-products-availability-checker-dates-' . esc_html( $availability_checker_id ) . '">' . esc_html( apply_filters( 'wcrp_rental_products_text_rental_dates', get_option( 'wcrp_rental_products_text_rental_dates' ) ) ) . '</label>';
				echo '<div id="wcrp-rental-products-availability-checker-parent-' . esc_html( $availability_checker_id ) . '" class="wcrp-rental-products-availability-checker-parent">';
				echo '<input type="text" id="wcrp-rental-products-availability-checker-dates-' . esc_html( $availability_checker_id ) . '" class="wcrp-rental-products-availability-checker-dates" name="wcrp_rental_products_availability_checker_dates" placeholder="' . esc_html( apply_filters( 'wcrp_rental_products_text_select_dates', get_option( 'wcrp_rental_products_text_select_dates' ) ) ) . '" readonly="readonly" required>';
				echo '</div>';
				echo '</div>';

				// Availability checker unapplied form quantity, this is hidden not removed if availability checker quantity is disabled as the field is still required to be submitted to allow the availability checker to work, quantity must be set, for disabled quantity this defaults to 1

				echo '<div class="wcrp-rental-products-availability-checker-unapplied-form-field wcrp-rental-products-availability-checker-unapplied-form-field-quantity' . ( 'no' == $availability_checker_quantity ? ' wcrp-rental-products-availability-checker-unapplied-form-field-hidden' : '' ) . '">';
				echo '<label class="wcrp-rental-products-availability-checker-unapplied-form-field-label wcrp-rental-products-availability-checker-unapplied-form-field-label-quantity" for="wcrp-rental-products-availability-checker-quantity-' . esc_html( $availability_checker_id ) . '">' . esc_html__( 'Quantity', 'wcrp-rental-products' ) . '</label>';
				echo '<input type="number" id="wcrp-rental-products-availability-checker-quantity-' . esc_html( $availability_checker_id ) . '" class="wcrp-rental-products-availability-checker-quantity" name="wcrp_rental_products_availability_checker_quantity" min="1" value="1">';
				echo '</div>';

				// Availability checker unapplied form submit

				echo '<button type="submit" id="wcrp-rental-products-availability-checker-submit-' . esc_html( $availability_checker_id ) . '" class="wcrp-rental-products-availability-checker-button button ' . esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ) . '" name="wcrp_rental_products_availability_checker_submit">' . esc_html( apply_filters( 'wcrp_rental_products_text_check_availability', get_option( 'wcrp_rental_products_text_check_availability' ) ) ) . '</button>';

				// Availability checker unapplied form end

				echo '</form>';

				// Availability checker unapplied scripts ?>

				<script>

					jQuery( document ).ready( function( $ ) {

						function availabilityCheckerCalendarFormatSelectedDate( date ) {

							var d = new Date( date.dateInstance ),
							month = '' + ( d.getMonth() + 1 ),
							day = '' + d.getDate(),
							year = d.getFullYear();

							if ( month.length < 2 ) {

								month = '0' + month;

							}

							if ( day.length < 2 ) {

								day = '0' + day;

							}

							return [ year, month, day ].join( '-' );

						}

						var availabilityCheckerCalendar = new Litepicker({
							element: document.getElementById( 'wcrp-rental-products-availability-checker-dates-<?php echo esc_html( $availability_checker_id ); ?>' ),
							parentEl: '#wcrp-rental-products-availability-checker-parent-<?php echo esc_html( $availability_checker_id ); ?>', <?php // Fixes issue with total days tooltip not positioned correctly with inlineMode enabled ?>
							autoApply: <?php echo ( 'yes' == $availability_checker_auto_apply ? 'true' : 'false' ); ?>,
							autoRefresh: true,
							firstDay: <?php echo esc_html( wcrp_rental_products_rental_form_first_day() ); ?>,
							format: '<?php echo esc_html( wcrp_rental_products_rental_form_date_format() ); ?>',
							lang: '<?php echo esc_html( apply_filters( 'wcrp_rental_products_litepicker_language', esc_html( str_replace( '_', '-', get_locale() ) ) ) ); ?>',
							<?php if ( 'no' == $availability_checker_period_multiples || ( 'yes' == $availability_checker_period_multiples && ( $availability_checker_minimum_days !== $availability_checker_maximum_days ) ) ) { ?>
								minDays: <?php echo ( (int) $availability_checker_minimum_days > 0 ? esc_html( $availability_checker_minimum_days ) : 'null' ); ?>,
								maxDays: <?php echo ( (int) $availability_checker_maximum_days > 0 ? esc_html( $availability_checker_maximum_days ) : 'null' ); ?>,
							<?php } ?>
							minDate: '<?php echo esc_html( wp_date( 'Y-m-d' ) ); ?>',
							maxDate: '<?php echo esc_html( wcrp_rental_products_rental_form_maximum_date( 'date' ) ); ?>',
							plugins: ['mobilefriendly'],
							singleMode: <?php echo ( '1' == $availability_checker_minimum_days && '1' == $availability_checker_maximum_days ? 'true' : 'false' ); ?>,
							resetButton: <?php echo ( 'yes' == $availability_checker_reset_button ? 'true' : 'false' ); ?>,
							tooltipText: {
								one: '<?php esc_html_e( 'day', 'wcrp-rental-products' ); ?>',
								other: '<?php esc_html_e( 'days', 'wcrp-rental-products' ); ?>',
							},
							setup: ( availabilityCheckerCalendar ) => {

								<?php // On render ui ?>

								availabilityCheckerCalendar.on( 'render', ( ui ) => {

									<?php // Cancel and apply buttons need to be changed so they are translation based strings, otherwise cannot not be translated (these are not changed as per Litepicker's lang setting) ?>

									$( '#wcrp-rental-products-availability-checker-unapplied-form-<?php echo esc_html( $availability_checker_id ); ?> .litepicker .button-apply' ).text( '<?php esc_html_e( 'Apply', 'wcrp-rental-products' ); ?>' );
									$( '#wcrp-rental-products-availability-checker-unapplied-form-<?php echo esc_html( $availability_checker_id ); ?> .litepicker .button-cancel' ).text( '<?php esc_html_e( 'Cancel', 'wcrp-rental-products' ); ?>' );

								});

								<?php // On date selection pre submit ?>

								availabilityCheckerCalendar.on( 'preselect', ( date1, date2 ) => {

									<?php
									// Allow apply button (only available if auto apply disabled) to be clicked if only rent from selected, also requires a button:apply condition, see later in this code
									if ( 'no' == $availability_checker_auto_apply ) {
										?>
										allowApplyIfOnlyRentFromSelected = null;
										if ( date1 && !date2 ) {
											allowApplyIfOnlyRentFromSelected = date1;
										}
										availabilityCheckerCalendar.ui.querySelector( '.button-apply' ).disabled = false;
										<?php
									}
									?>

								});

								<?php // On date selection submit ?>

								availabilityCheckerCalendar.on( 'selected', ( date1, date2 ) => {

									<?php // If singleMode is enabled then only one date is passed and date2 will be undefined so its set to date1 here otherwise availabilityCheckerCalendarFormatSelectedDate() calls will cause an error ?>
									if ( availabilityCheckerCalendar.options.singleMode == true ) {
										date2 = date1;
									}
									$( '#wcrp-rental-products-availability-checker-rent-from-<?php echo esc_html( $availability_checker_id ); ?>' ).val( availabilityCheckerCalendarFormatSelectedDate( date1 ) ); <?php // As date2 wouldn't exist with singleMode true ?>
									$( '#wcrp-rental-products-availability-checker-rent-to-<?php echo esc_html( $availability_checker_id ); ?>' ).val( availabilityCheckerCalendarFormatSelectedDate( date2 ) );

								});

								<?php

								// On apply button click (only available if auto apply disabled)

								if ( 'no' == $availability_checker_auto_apply ) {

									?>

									availabilityCheckerCalendar.on( 'button:apply', ( date1, date2 ) => {

										<?php // Allow apply button to be clicked if only rent from selected, also requires a preselect condition, see earlier in this code ?>

										if ( typeof allowApplyIfOnlyRentFromSelected !== 'undefined' && allowApplyIfOnlyRentFromSelected !== null ) {

											availabilityCheckerCalendar.setDateRange( allowApplyIfOnlyRentFromSelected, allowApplyIfOnlyRentFromSelected );

										}

									});

									<?php

								}

								?>

							},
							lockDaysFilter: ( pickedDateFrom, pickedDateTo, pickedDates ) => {

								<?php

								// Set lock days based on pricing period multiples

								if ( 'yes' == $availability_checker_period_multiples && (int) $availability_checker_minimum_days > 0 && (int) $availability_checker_maximum_days > 0 && ( $availability_checker_minimum_days == $availability_checker_maximum_days ) ) {
									?>

									let pricingPeriod = <?php echo esc_html( $availability_checker_minimum_days ); ?>; <?php // Minimum used, but min/max are the same, it's just setting the period ?>

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

						$( '#wcrp-rental-products-availability-checker-unapplied-form-<?php echo esc_html( $availability_checker_id ); ?>' ).submit( function( e ) {

							if ( '' == $( '#wcrp-rental-products-availability-checker-rent-from-<?php echo esc_html( $availability_checker_id ); ?>' ).val() || '' == $( '#wcrp-rental-products-availability-checker-rent-to-<?php echo esc_html( $availability_checker_id ); ?>' ).val() || '' == $( '#wcrp-rental-products-availability-checker-quantity-<?php echo esc_html( $availability_checker_id ); ?>' ).val() ) {

								e.preventDefault();

								if ( 'yes' == '<?php echo esc_html( $availability_checker_quantity ); ?>' ) {

									<?php // translators: %s: select dates text ?>
									alert( '<?php echo sprintf( esc_html__( 'Please %s and quantity to check availability.', 'wcrp-rental-products' ), esc_html( strtolower( apply_filters( 'wcrp_rental_products_text_select_dates', get_option( 'wcrp_rental_products_text_select_dates' ) ) ) ) ); ?>' );

								} else {

									<?php // translators: %s: select dates text ?>
									alert( '<?php echo sprintf( esc_html__( 'Please %s to check availability.', 'wcrp-rental-products' ), esc_html( strtolower( apply_filters( 'wcrp_rental_products_text_select_dates', get_option( 'wcrp_rental_products_text_select_dates' ) ) ) ) ); ?>' );

								}

							}

						});

					});

				</script>

				<?php

				// Availability checker unapplied end

				echo '</div>';

			}

			// Availability checker end

			echo '</div>';

			return ob_get_clean();

		}

		public function display_ajax() {

			echo wp_json_encode( $this->display() ); // wp_json_encode used because if attempting to use wp_kses for codesniff pass (with allowed tags for form, inputs, button, etc) still converts entities plus some conditions in the JS become malformed, wp_json_encode gets passed and then parsed in the JS to return the full HTML with working JS
			exit;

		}

		public static function display_ajax_placeholder() {

			// This is a placeholder element, the availability checker gets inserted after it and then it gets removed in public-availability-checker-ajax.js, note this is a class not an id as there potentially maybe more than one on a page, although edge case

			return '<div class="wcrp-rental-products-availability-checker-ajax-placeholder"></div>';

		}

		public static function status( $product ) {

			if ( is_cart() || is_checkout() ) { // Availability checker status is not shown on products in the cart as products have already been selected at this point and it also uses the WooCommerce notice CSS classes and in some scenarios (e.g. classic cart experience) we have found WooCommerce can move notices with these to the top of the page on cart when emptied, so without this the availability checker status on products in the cross sells section (or if the theme/custom dev work has added them elsewhere) would all be at the top of the page. We have also included the checkout condition here incase any themes/custom dev work include products with availability checker statuses there

				return '';

			}

			$status = '';

			$availability_checker_data = wcrp_rental_products_availability_checker_data();
			$availability_checker_quantity = get_option( 'wcrp_rental_products_availability_checker_quantity' );
			$availability_checker_status_display = get_option( 'wcrp_rental_products_availability_checker_status_display' );

			if ( !empty( $availability_checker_data ) ) {

				$product_id = $product->get_id();
				$product_type = $product->get_type();

				if ( 'grouped' == $product_type ) {

					if ( true == WCRP_Rental_Products_Product_Display::grouped_product_check_all_products_rental_only( $product ) ) { // Show select options if grouped product only contains rental only products (if rental or purchase/other then no status as select options text may not be relevant)

						$status .= self::status_select_options();

					}

				} else {

					if ( wcrp_rental_products_is_rental_only( $product_id ) || wcrp_rental_products_is_rental_purchase( $product_id ) ) {

						if ( 'simple' == $product_type ) {

							$availability = wcrp_rental_products_check_availability( $product_id, $availability_checker_data['rent_from'], $availability_checker_data['rent_to'], $availability_checker_data['quantity'], array() );

							if ( 'unavailable_non_rental' !== $availability ) {

								// Availability checker status start

								$status .= '<div class="wcrp-rental-products-availability-checker-status wcrp-rental-products-availability-checker-status-' . esc_attr( $availability_checker_status_display ) . ( WCRP_Rental_Products_Misc::string_starts_with( $availability, 'unavailable' ) ? ' wcrp-rental-products-availability-checker-status-unavailable woocommerce-error' : ' wcrp-rental-products-availability-checker-status-available woocommerce-message' ) . '">';

								// Availability checker status title

								$status .= '<div class="wcrp-rental-products-availability-checker-status-title">';

								if ( WCRP_Rental_Products_Misc::string_starts_with( $availability, 'unavailable' ) ) {

									$status .= esc_html( apply_filters( 'wcrp_rental_products_text_rental_unavailable', get_option( 'wcrp_rental_products_text_rental_unavailable' ) ) );

								} else {

									$status .= esc_html( apply_filters( 'wcrp_rental_products_text_rental_available', get_option( 'wcrp_rental_products_text_rental_available' ) ) );

								}

								$status .= '</div>';

								// If availability checker status display is standard

								if ( 'standard' == $availability_checker_status_display ) {

									// Availability checker status info start

									$status .= '<div class="wcrp-rental-products-availability-checker-status-info">';

									// Availability checker status info row rent from

									$status .= '<div class="wcrp-rental-products-availability-checker-status-info-row wcrp-rental-products-availability-checker-status-info-row-rent-from">';
									$status .= '<span class="wcrp-rental-products-availability-checker-status-info-row-heading wcrp-rental-products-availability-checker-status-info-row-heading-rent-from">';
									$status .= apply_filters( 'wcrp_rental_products_text_rent_from', get_option( 'wcrp_rental_products_text_rent_from' ) ) . __( ':', 'wcrp-rental-products' );
									$status .= '</span>';
									$status .= '<span class="wcrp-rental-products-availability-checker-status-info-row-value wcrp-rental-products-availability-checker-status-info-row-value-rent-from">';
									$status .= date_i18n( wcrp_rental_products_rental_date_format(), strtotime( $availability_checker_data['rent_from'] ) );
									$status .= '</span>';
									$status .= '</div>';

									// Availability checker status info row rent to

									$status .= '<div class="wcrp-rental-products-availability-checker-status-info-row wcrp-rental-products-availability-checker-status-info-row-rent-to">';
									$status .= '<span class="wcrp-rental-products-availability-checker-status-info-row-heading wcrp-rental-products-availability-checker-status-info-row-heading-rent-to">';
									$status .= apply_filters( 'wcrp_rental_products_text_rent_to', get_option( 'wcrp_rental_products_text_rent_to' ) ) . __( ':', 'wcrp-rental-products' );
									$status .= '</span>';
									$status .= '<span class="wcrp-rental-products-availability-checker-status-info-row-value wcrp-rental-products-availability-checker-status-info-row-value-rent-to">';
									$status .= date_i18n( wcrp_rental_products_rental_date_format(), strtotime( $availability_checker_data['rent_to'] ) );
									$status .= '</span>';
									$status .= '</div>';

									// Availability checker status info row quantity

									if ( 'yes' == $availability_checker_quantity ) {

										$status .= '<div class="wcrp-rental-products-availability-checker-status-info-row wcrp-rental-products-availability-checker-status-info-row-quantity">';
										$status .= '<span class="wcrp-rental-products-availability-checker-status-info-row-heading wcrp-rental-products-availability-checker-status-info-row-heading-quantity">';
										$status .= esc_html__( 'Quantity:', 'wcrp-rental-products' );
										$status .= '</span>';
										$status .= '<span class="wcrp-rental-products-availability-checker-status-info-row-value wcrp-rental-products-availability-checker-status-info-row-value-quantity">';
										$status .= $availability_checker_data['quantity'];
										$status .= '</span>';
										$status .= '</div>';

									}

									// Availability checker status info end

									$status .= '</div>';

								}

								// Availability checker status end

								$status .= '</div>';

							}

						} else {

							$status .= self::status_select_options();

						}

					}

				}

			}

			return $status;

		}

		public static function status_ajax_placeholder( $product ) {

			// This is a placeholder element, the availability checker status gets inserted after it and then it gets removed in public-availability-checker-ajax.js

			return '<div class="wcrp-rental-products-availability-checker-status-ajax-placeholder" data-product-id="' . esc_attr( $product->get_id() ) . '"></div>';

		}

		public static function status_select_options() {

			if ( is_cart() || is_checkout() ) { // Availability checker status select options is not shown on products in the cart as products have already been selected at this point and it also uses the WooCommerce notice CSS classes and WooCommerce moves notices with these to the top of the page on cart when emptied, so without this the availability checker status select options on products in the cross sells section (or if the theme/custom dev work has added them elsewhere) would all be at the top of the page. We have also included the checkout condition here incase any themes/custom dev work include products with availability checker status select options there

				return '';

			}

			$status = '<div class="wcrp-rental-products-availability-checker-status-select-options woocommerce-info"><strong>' . __( 'Select rental dates/options', 'wcrp-rental-products' ) . ' ' . __( 'to', 'wcrp-rental-products' ) . ' ' . strtolower( esc_html( apply_filters( 'wcrp_rental_products_text_check_availability', get_option( 'wcrp_rental_products_text_check_availability' ) ) ) ) . '</strong></div>';

			return $status;

		}

		public function statuses_ajax() {

			$statuses = array();

			if ( isset( $_POST['nonce'] ) ) {

				if ( wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'wcrp_rental_products_availability_checker_ajax' ) ) {

					if ( isset( $_POST['product_ids'] ) ) {

						if ( is_array( $_POST['product_ids'] ) ) {

							$product_ids = array_map( 'sanitize_text_field', $_POST['product_ids'] );

							foreach ( $product_ids as $product_id ) {

								$product = wc_get_product( $product_id );
								$statuses[$product->get_id()] = self::status( $product );

							}

						}

					}

				}

			}

			echo wp_json_encode( $statuses );

			exit;

		}

	}

}
