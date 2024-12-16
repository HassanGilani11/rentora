<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Cart_Checks' ) ) {

	class WCRP_Rental_Products_Cart_Checks {

		public function __construct() {

			add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'add_to_cart_validation' ), PHP_INT_MAX, 3 );
			add_action( 'woocommerce_check_cart_items', array( $this, 'check_rental_cart_items' ), 0 );

		}

		public function add_to_cart_validation( $validation, $product_id, $quantity ) {

			// Initial variables

			$advanced_configuration = wcrp_rental_products_advanced_configuration();
			$referer = wp_get_referer(); // MUST use this, this is used for checking if contains rent=1, using $_GET['rent'] isn't reliable enough here when tested, note that this is empty if the referer is the same page as currently on

			$check_0_failed = false;
			$check_1_failed = false;
			$check_2_failed = false;
			$check_3_failed = false;

			$debug_log = get_option( 'wcrp_rental_products_debug_log' );
			$debug_log_request = array(
				'timestamp'	=> time(),
				'url'		=> ( isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( $_SERVER['REQUEST_URI'] ) : false ), // $referer not use as can be empty, see comment above
			);

			if ( empty( $debug_log ) || !is_array( $debug_log ) ) {

				$debug_log = array();

			}

			// Check 0 - nonce check

			if ( !in_array( 'cart_checks_disable_check_0', $advanced_configuration ) ) {

				if ( wcrp_rental_products_is_rental_only( $product_id ) || ( wcrp_rental_products_is_rental_purchase( $product_id ) && WCRP_Rental_Products_Misc::string_contains( $referer, 'rent=1' ) ) ) {

					// nosemgrep: scanner.php.wp.security.csrf.nonce-check-not-dying
					if ( !isset( $_POST['wcrp_rental_products_rental_form_nonce'] ) || !wp_verify_nonce( sanitize_key( $_POST['wcrp_rental_products_rental_form_nonce'] ), 'wcrp_rental_products_rental_form' ) ) {

						$check_0_failed = true;

						// No need to add to debug log as if shows error code 0 it is a nonce error, there aren't any other possible outcomes

					}

					if ( true == $check_0_failed ) {

						// translators: %1$s: product title, %2$s: product page link, %3$s: error code number
						wc_add_notice( wp_kses_post( sprintf( __( 'Sorry, "%1$s" could not be added to cart. Please try adding again from the %2$s. Error code: %3$s.', 'wcrp-rental-products' ), get_the_title( $product_id ), '<a href="' . get_permalink( $product_id ) . '">' . __( 'product page', 'wcrp-rental-products' ) . '</a>', 'CARTADD0' ) ), 'error' );

						return false; // Return immediately not $validation = false as if more checks fail it results in multiple notices

					}

				}

			}

			// Check 1 - rental product with missing hidden fields, stops scenarios where a theme might have ?add-to-cart=xxx links which would let a rental through without all the data required, or if hidden fields removed in dev tools, etc, note that this check does not include the in person pickup/returns hidden fields as they are only used if an in person pick up/return, whereas the ones conditioned below are required

			if ( !in_array( 'cart_checks_disable_check_1', $advanced_configuration ) ) {

				if ( wcrp_rental_products_is_rental_only( $product_id ) || ( wcrp_rental_products_is_rental_purchase( $product_id ) && WCRP_Rental_Products_Misc::string_contains( $referer, 'rent=1' ) ) ) {

					if ( isset( $_POST['wcrp_rental_products_rental_form_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['wcrp_rental_products_rental_form_nonce'] ), 'wcrp_rental_products_rental_form' ) ) {

						if ( !isset( $_POST['wcrp_rental_products_cart_item_validation'] ) || !isset( $_POST['wcrp_rental_products_cart_item_timestamp'] ) || !isset( $_POST['wcrp_rental_products_cart_item_price'] ) || !isset( $_POST['wcrp_rental_products_rent_from'] ) || !isset( $_POST['wcrp_rental_products_rent_to'] ) || !isset( $_POST['wcrp_rental_products_start_days_threshold'] ) || !isset( $_POST['wcrp_rental_products_return_days_threshold'] ) || !isset( $_POST['wcrp_rental_products_advanced_pricing'] ) ) {

							$check_1_failed = true;

							$debug_log['WCRP_Rental_Products_Cart_Checks']['add_to_cart_validation'][1] = array(
								'request'										=> $debug_log_request,
								'wcrp_rental_products_cart_item_validation'		=> isset( $_POST['wcrp_rental_products_cart_item_validation'] ),
								'wcrp_rental_products_cart_item_timestamp'		=> isset( $_POST['wcrp_rental_products_cart_item_timestamp'] ),
								'wcrp_rental_products_cart_item_price'			=> isset( $_POST['wcrp_rental_products_cart_item_price'] ),
								'wcrp_rental_products_rent_from'				=> isset( $_POST['wcrp_rental_products_rent_from'] ),
								'wcrp_rental_products_rent_to'					=> isset( $_POST['wcrp_rental_products_rent_to'] ),
								'wcrp_rental_products_start_days_threshold'		=> isset( $_POST['wcrp_rental_products_start_days_threshold'] ),
								'wcrp_rental_products_return_days_threshold'	=> isset( $_POST['wcrp_rental_products_return_days_threshold'] ),
								'wcrp_rental_products_advanced_pricing'			=> isset( $_POST['wcrp_rental_products_advanced_pricing'] ),
							);

						}

					}

					if ( true == $check_1_failed ) {

						update_option( 'wcrp_rental_products_debug_log', $debug_log );

						// translators: %1$s: product title, %2$s: product page link, %3$s: error code number
						wc_add_notice( wp_kses_post( sprintf( __( 'Sorry, "%1$s" could not be added to cart. Please try adding again from the %2$s. Error code: %3$s.', 'wcrp-rental-products' ), get_the_title( $product_id ), '<a href="' . get_permalink( $product_id ) . '">' . __( 'product page', 'wcrp-rental-products' ) . '</a>', 'CARTADD1' ) ), 'error' );

						return false; // Return immediately not $validation = false as if more checks fail it results in multiple notices

					}

				}

			}

			// Check 2 - validation to reduce risk of hidden field manupilation, see related functionality in WCRP_Rental_Products_Product_Rental_Form::rental_form()

			if ( !in_array( 'cart_checks_disable_check_2', $advanced_configuration ) ) {

				if ( wcrp_rental_products_is_rental_only( $product_id ) || ( wcrp_rental_products_is_rental_purchase( $product_id ) && WCRP_Rental_Products_Misc::string_contains( $referer, 'rent=1' ) ) ) {

					if ( isset( $_POST['wcrp_rental_products_cart_item_timestamp'] ) || isset( $_POST['wcrp_rental_products_cart_item_price'] ) || isset( $_POST['wcrp_rental_products_rent_from'] ) || isset( $_POST['wcrp_rental_products_rent_to'] ) || isset( $_POST['wcrp_rental_products_start_days_threshold'] ) || isset( $_POST['wcrp_rental_products_return_days_threshold'] ) || isset( $_POST['wcrp_rental_products_advanced_pricing'] ) ) {

						// Always set for rentals

						$validation_string = sanitize_text_field( $_POST['wcrp_rental_products_cart_item_timestamp'] );
						$validation_string .= sanitize_text_field( $_POST['wcrp_rental_products_cart_item_price'] );
						$validation_string .= sanitize_text_field( $_POST['wcrp_rental_products_rent_from'] );
						$validation_string .= sanitize_text_field( $_POST['wcrp_rental_products_rent_to'] );
						$validation_string .= sanitize_text_field( $_POST['wcrp_rental_products_start_days_threshold'] );
						$validation_string .= sanitize_text_field( $_POST['wcrp_rental_products_return_days_threshold'] );
						$validation_string .= sanitize_text_field( $_POST['wcrp_rental_products_advanced_pricing'] );

						// Only set for rentals if it is an in person pickup/return

						if ( isset( $_POST['wcrp_rental_products_in_person_pick_up_return'] ) && isset( $_POST['wcrp_rental_products_in_person_pick_up_date'] ) && isset( $_POST['wcrp_rental_products_in_person_pick_up_time'] ) && isset( $_POST['wcrp_rental_products_in_person_pick_up_fee'] ) && isset( $_POST['wcrp_rental_products_in_person_return_date'] ) && isset( $_POST['wcrp_rental_products_in_person_return_date_type'] ) && isset( $_POST['wcrp_rental_products_in_person_return_time'] ) && isset( $_POST['wcrp_rental_products_in_person_return_fee'] ) ) {

							$validation_string .= sanitize_text_field( $_POST['wcrp_rental_products_in_person_pick_up_return'] );
							$validation_string .= sanitize_text_field( $_POST['wcrp_rental_products_in_person_pick_up_date'] );
							$validation_string .= sanitize_text_field( $_POST['wcrp_rental_products_in_person_pick_up_time'] );
							$validation_string .= sanitize_text_field( $_POST['wcrp_rental_products_in_person_pick_up_fee'] );
							$validation_string .= sanitize_text_field( $_POST['wcrp_rental_products_in_person_return_date'] );
							$validation_string .= sanitize_text_field( $_POST['wcrp_rental_products_in_person_return_date_type'] );
							$validation_string .= sanitize_text_field( $_POST['wcrp_rental_products_in_person_return_time'] );
							$validation_string .= sanitize_text_field( $_POST['wcrp_rental_products_in_person_return_fee'] );

						}

						$validation_string_decoded = $validation_string;
						$validation_string_encoded = base64_encode( $validation_string_decoded );

						if ( $_POST['wcrp_rental_products_cart_item_validation'] !== $validation_string_encoded ) {

							$check_2_failed = true;

							$debug_log['WCRP_Rental_Products_Cart_Checks']['add_to_cart_validation'][2] = array(
								'request'					=> $debug_log_request,
								'validation_string_decoded'	=> $validation_string_decoded,
								'validation_string_encoded'	=> $validation_string_encoded,
							);

						}

					} // No need for an else statement here as check 1 already covers that scenario

					if ( true == $check_2_failed ) {

						update_option( 'wcrp_rental_products_debug_log', $debug_log );

						// translators: %1$s: product title, %2$s: product page link, %3$s: error code number
						wc_add_notice( wp_kses_post( sprintf( __( 'Sorry, "%1$s" could not be added to cart due to a validation error. Please try refreshing the %2$s and try again. Error code: %3$s.', 'wcrp-rental-products' ), get_the_title( $product_id ), '<a href="' . get_permalink( $product_id ) . '">' . __( 'product page', 'wcrp-rental-products' ) . '</a>', 'CARTADD2' ) ), 'error' );

						return false; // Return immediately not $validation = false as if more checks fail it results in multiple notices

					}

				}

			}

			/*
			Check 3 - restrict add to cart of purchases and rentals of the same product

			For rental or purchase products only a rental OR a purchase of the product can be added to cart. This is due to WooCommerce core using WC_Cart::check_cart_item_stock() and subsequent functions to check if the product has stock before allowing progress through the cart/checkout (these functions cannot be filtered to allow us to check if cart item meta exists and effect the return, we have requested this but it isn't something that is going to be included in core)

			We get around this by filtering stock related returns of the WC_Product object conditionally if a rental only or rental or purchase product, see: WCRP_Rental_Products_Product_Data_Returns::wc_product_and_variants. However in WCRP_Rental_Products_Product_Data_Returns::wc_product_and_variants we cannot determine that a rental or purchase product during cart/checkout is the rental part (as we can't rely on ?rent=1 or $_POST data like we do upon adding to cart as they do not exist at cart/checkout), therefore to check if a rental or purchase product in cart is the rental part we can only get all cart items, loop through them and if the product has rental cart item meta deem that product as a rental (which would deem ALL cart items of that product as rentals), hence why only rental OR purchases of a rental or purchase product are allowed to be added to cart (if this didn't occur and you had 2 x rental or purchase products in cart, one rental and one purchase then both would be deemed a rental and bypass stock checks)

			So we had a choice between disabling rentals AND purchases of the same rental or purchase product being added to cart or making rental or purchase products require no stock management (effectively no stock management on the purchasable part). The latter was in earlier versions of this extension and we had many requests regarding allowing stock management for the purchasable part of rental or purchase products, and therefore we now use the method of disabling rentals AND purchases of the same product from being added to cart. Obviously there will be scenarios where a store owner wants to do this - but these scenarios are far less than a store owner wanting to use stock management on the purchasable part of rental or purchase products

			Unfortunately due to the lack of extensibility in the core functions this is the only option available, we monitor these core functions regularly and if they are amended to include extensibility in future we can review and potentially remove this functionality
			*/

			if ( !in_array( 'cart_checks_disable_check_3', $advanced_configuration ) ) {

				if ( wcrp_rental_products_is_rental_purchase( $product_id ) ) {

					global $woocommerce;
					$cart_items = $woocommerce->cart->get_cart();

					if ( !empty( $cart_items ) ) {

						foreach ( $cart_items as $cart_item ) {

							if ( $product_id == $cart_item['product_id'] ) {

								// Flag this product has a rental, this is used later to stop both purchase and rentals of the same product being added to cart

								if ( isset( $cart_item['wcrp_rental_products_rent_from'] ) ) {

									$product_has_cart_rental = true;

								} else {

									$product_has_cart_rental = false;

								}

							}

						}

						if ( isset( $product_has_cart_rental ) ) { // If not set then do nothing (as a rental of this product id is not already in cart, without this the false == $product_has_cart_rental condition below would trigger)

							if ( true == $product_has_cart_rental ) { // If the rental or purchase product has a rental already in cart

								if ( !WCRP_Rental_Products_Misc::string_contains( $referer, 'rent=1' ) ) { // If attempting to add a purchase of this rental or purchase product to cart when a rental already in cart - we look for rent=1 and not ?rent=1 as it could be ? or & depending on add_query_arg()

									$check_3_failed = true;

									// No need to add to debug log as nothing to fix

								}

							} elseif ( false == $product_has_cart_rental ) { // If the rental or purchase product has a purchase already in cart

								if ( WCRP_Rental_Products_Misc::string_contains( $referer, 'rent=1' ) ) { // If attempting to add a rental of this rental or purchase product to cart when a purchase already in cart - we look for rent=1 and not ?rent=1 as it could be ? or & depending on add_query_arg()

									$check_3_failed = true;

									// No need to add to debug log as nothing to fix

								}

							}

						}

					}

					if ( true == $check_3_failed ) {

						wc_add_notice( __( 'Sorry, you cannot add a purchase and rental of the same product to cart. If you require a purchase and rental of the same product then these must be ordered separately.', 'wcrp-rental-products' ), 'error' );

						return false; // Return immediately not $validation = false as if more checks fail it results in multiple notices

					}

				}

			}

			return $validation;

		}

		public function check_rental_cart_items() {

			// If this function throws an error notice it also stops checkout if accessing checkout directly bypassing the cart and shows a "go back to cart" notice (see class-wc-shortcode-checkout.php)

			global $woocommerce;

			$cart_items = $woocommerce->cart->get_cart();
			$multiple_same_rental_products = array();
			$multiple_rental_product_availability_issues = array();
			$product_updated_restrictions = get_option( 'wcrp_rental_products_product_updated_restrictions' );
			$same_rental_dates_required = get_option( 'wcrp_rental_products_same_rental_dates_required' );
			$same_rental_dates_required_rent_from = false;
			$same_rental_dates_required_rent_to = false;

			if ( !empty( $cart_items ) ) {

				foreach ( $cart_items as $cart_item ) {

					// Product data

					if ( (int) $cart_item['variation_id'] > 0 ) {

						$product_id = $cart_item['variation_id'];

					} else {

						$product_id = $cart_item['product_id'];

					}

					// If same rental dates required

					if ( 'yes' == $same_rental_dates_required ) {

						if ( isset( $cart_item['wcrp_rental_products_rent_from'] ) && isset( $cart_item['wcrp_rental_products_rent_to'] ) ) {

							if ( false == $same_rental_dates_required_rent_from && false == $same_rental_dates_required_rent_to ) {

								// Store the rent from/to dates if first rental cart item so we can then check they are the same on further iterations of rental cart items

								$same_rental_dates_required_rent_from = $cart_item['wcrp_rental_products_rent_from'];
								$same_rental_dates_required_rent_to = $cart_item['wcrp_rental_products_rent_to'];

							} else {

								// If rental dates of this cart item do not match previous item then show notice, this does not occur within add_to_cart_validation() as cart item meta does not exist at that point

								if ( $same_rental_dates_required_rent_from !== $cart_item['wcrp_rental_products_rent_from'] || $same_rental_dates_required_rent_to !== $cart_item['wcrp_rental_products_rent_to'] ) {

									wc_add_notice( esc_html__( 'Sorry, when ordering multiple rental products they must all have the same rental dates.', 'wcrp-rental-products' ), 'error' );

									remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 ); // This does not work for cart/checkout blocks, however the wc_add_notice above is enough, the proceed to checkout button is still there and lets you through to checkout but clicking place order just scrolls you up to the notice rather than placing the order, priority is 20 because it must match the priority where added in WooCommerce core

									return false; // Means one notice will be displayed at time to deal with the cart rather than multiples (could be multiple products with different dates and would add the same message)

								}

							}

						}

					}

					// If is a rental (without this it's not a rental or is the purchasable part of a rental or purchase based product)

					if ( isset( $cart_item['wcrp_rental_products_rent_from'] ) ) {

						// Show notice if the product has been updated since the item was added to cart and product updated restrictions is enabled, as rental pricing/availability may have changed and therefore a customer could checkout when an item is unavailable or a different price

						if ( 'yes' == $product_updated_restrictions ) {

							if ( get_the_modified_date( 'U', $cart_item['product_id'] ) > $cart_item['wcrp_rental_products_cart_item_timestamp'] ) { // Uses $cart_item['product_id'] as it's always the parent regardless of variation

								// translators: %s: product title
								wc_add_notice( wp_kses_post( sprintf( __( 'Sorry, "%s" has recently been updated and pricing/availability may have changed. Please remove the product from your cart and add again.', 'wcrp-rental-products' ), get_the_title( $product_id ) ) ), 'error' );

								remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 ); // This does not work for cart/checkout blocks, however the wc_add_notice above is enough, the proceed to checkout button is still there and lets you through to checkout but clicking place order just scrolls you up to the notice rather than placing the order, priority is 20 because it must match the priority where added in WooCommerce core

								return false; // Means one notice will be displayed at time to deal with the cart rather than multiples (could be the same product with different dates and would add the same message)

							}

						}

						// Check availability

						$availability = wcrp_rental_products_check_availability( $product_id, $cart_item['wcrp_rental_products_rent_from'], $cart_item['wcrp_rental_products_rent_to'], $cart_item['quantity'], array() );

						if ( WCRP_Rental_Products_Misc::string_starts_with( $availability, 'unavailable' ) ) {

							if ( WCRP_Rental_Products_Misc::string_starts_with( $availability, 'unavailable_stock_' ) ) {

								if ( WCRP_Rental_Products_Misc::string_starts_with( $availability, 'unavailable_stock_max_' ) ) {

									// translators: %1$s: product title, %2$s: maximum quantity available
									wc_add_notice( wp_kses_post( sprintf( __( 'Sorry, "%1$s" has a maximum quantity limit of %2$s. Please reduce the quantity to %2$s or less.', 'wcrp-rental-products' ), get_the_title( $product_id ), str_replace( 'unavailable_stock_max_', '', $availability ) ) ), 'error' );

								} else {

									// translators: %1$s: product title, %2$s: maximum quantity available
									wc_add_notice( wp_kses_post( sprintf( __( 'Sorry, "%1$s" is unavailable on these dates for the quantity selected. Please reduce the quantity to %2$s or less, or remove it from your cart and try alternative dates.', 'wcrp-rental-products' ), get_the_title( $product_id ), str_replace( 'unavailable_stock_', '', $availability ) ) ), 'error' );

								}

							} elseif ( WCRP_Rental_Products_Misc::string_starts_with( $availability, 'unavailable_dates' ) ) {

								// translators: %s: product title
								wc_add_notice( wp_kses_post( sprintf( __( 'Sorry, "%s" is unavailable on the rental dates selected. Please remove it from your cart and try alternative dates.', 'wcrp-rental-products' ), get_the_title( $product_id ) ) ), 'error' );

							} else {

								// translators: %s: product title
								wc_add_notice( wp_kses_post( sprintf( __( 'Sorry, "%s" is unavailable. Please remove it from your cart.', 'wcrp-rental-products' ), get_the_title( $product_id ) ) ), 'error' );

							}

							remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 ); // This does not work for cart/checkout blocks, however the wc_add_notice above is enough, the proceed to checkout button is still there and lets you through to checkout but clicking place order just scrolls you up to the notice rather than placing the order, priority is 20 because it must match the priority where added in WooCommerce core

							return false; // Means one notice will be displayed at time to deal with the cart rather than multiples (could be the same product with different dates and would add the same message)

						} else {

							// Store multiple same products so we can iterate over these later and check combined quantities required on the dates

							$multiple_same_rental_products[$product_id][] = array(
								'product_id'						=> $product_id,
								'quantity'							=> $cart_item['quantity'],
								'rent_from'							=> $cart_item['wcrp_rental_products_rent_from'],
								'rent_to'							=> $cart_item['wcrp_rental_products_rent_to'],
								'return_days_threshold'				=> $cart_item['wcrp_rental_products_return_days_threshold'],
							);

						}

					}

				}

			}

			// If multiple same rental products, will generally always be !empty due to how array populated above, but only taken into account later if there are more than 1 array items for the $multiple_same_rental_product in the foreach

			if ( !empty( $multiple_same_rental_products ) ) {

				// Check for multiple same products to find any date clashes and ensure if they are unavailable checkout is disabled notice shown

				foreach ( $multiple_same_rental_products as $multiple_same_rental_product ) {

					// Start an array which will be populated with all the dates in use by this product across all cart items

					$multiple_same_rental_product_combined_dates = array();

					// If more than 1 of same product

					if ( count( $multiple_same_rental_product ) > 1 ) {

						foreach ( $multiple_same_rental_product as $multiple_same_rental_product_values ) {

							// Get all dates in use, note that the combined dates functionality below is similar to in wcrp_rental_products_check_availability(), if making changes to this consider amends to the similar functionality there

							$current = strtotime( $multiple_same_rental_product_values['rent_from'] );
							$last = strtotime( '+' . $multiple_same_rental_product_values['return_days_threshold'] . ' days', strtotime( $multiple_same_rental_product_values['rent_to'] ) ); // Date to plus the return days to ensure how long it takes for this item to be returned is taken into account as well as the selected end date e.g. for a product with 1 stock it would stop someone renting for 1st to 3rd when someone has already rented 4th-5th as 1st to 3rd is actually 1st to 6th (if 3 return days)

							while ( $current <= $last ) {

								$multiple_same_rental_product_combined_dates[ gmdate( 'Y-m-d', $current ) ] = array(
									'product_id' => $multiple_same_rental_product_values['product_id'], // It doesn't matter this gets overwritten as it goes through the foreach as it's the same value for this product
									'quantity' => ( isset( $multiple_same_rental_product_combined_dates[ gmdate( 'Y-m-d', $current ) ]['quantity'] ) ? $multiple_same_rental_product_combined_dates[ gmdate( 'Y-m-d', $current ) ]['quantity'] + $multiple_same_rental_product_values['quantity'] : $multiple_same_rental_product_values['quantity'] ),
								);

								$current = strtotime( '+1 day', $current );

							}

						}

					}

					// If multiple same rental product combined dates

					if ( !empty( $multiple_same_rental_product_combined_dates ) ) {

						// Loop through the multiple same rental product combined dates

						foreach ( $multiple_same_rental_product_combined_dates as $multiple_same_rental_product_combined_date => $multiple_same_rental_product_combined_date_values ) {

							// Check if availability for the current iterative combined date is unavailable, one date passed as the rent from/to along with the combined date quantity to find if unavailable

							if ( WCRP_Rental_Products_Misc::string_starts_with( wcrp_rental_products_check_availability( $multiple_same_rental_product_combined_date_values['product_id'], $multiple_same_rental_product_combined_date, $multiple_same_rental_product_combined_date, $multiple_same_rental_product_combined_date_values['quantity'], array( 'cart_checks' => array( 'multiple_same_rental_product_combined_dates' ) ) ), 'unavailable' ) ) {

								$multiple_rental_product_availability_issues[$multiple_same_rental_product_combined_date_values['product_id']] = get_the_title( $multiple_same_rental_product_combined_date_values['product_id'] );

							}

						}

					}

				}

			}

			// If multiple same rental product combined dates unavailable

			if ( !empty( $multiple_rental_product_availability_issues ) ) {

				// translators: %s: products with issues
				wc_add_notice( wp_kses_post( sprintf( __( 'Sorry, you have added more than 1 of the same products "%s" to your cart and we do not have availability to fulfil the combined rental dates selected. Please remove one or more of these products from your cart to continue checkout.', 'wcrp-rental-products' ), implode( ', ', $multiple_rental_product_availability_issues ) ) ), 'error' );

				remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 ); // This does not work for cart/checkout blocks, however the wc_add_notice above is enough, the proceed to checkout button is still there and lets you through to checkout but clicking place order just scrolls you up to the notice rather than placing the order, priority is 20 because it must match the priority where added in WooCommerce core

				return false; // Means one notice will be displayed at time to deal with the cart rather than multiples (could be the same product with different dates and would add the same message)

			}

		}

	}

}
