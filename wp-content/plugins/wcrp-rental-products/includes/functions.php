<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

function wcrp_rental_products_advanced_configuration() {

	$advanced_configuration = array();
	$advanced_configuration_option = get_option( 'wcrp_rental_products_advanced_configuration' );

	if ( !empty( $advanced_configuration_option ) ) {

		$advanced_configuration = explode( ',', $advanced_configuration_option );

	}

	return $advanced_configuration;

}

function wcrp_rental_products_availability_checker_data() {

	if ( isset( $_POST[ 'wcrp_rental_products_availability_checker_rent_from' ] ) && isset( $_POST['wcrp_rental_products_availability_checker_nonce'] ) ) {

		if ( wp_verify_nonce( sanitize_key( $_POST['wcrp_rental_products_availability_checker_nonce'] ), 'wcrp_rental_products_availability_checker' ) ) {

			$data['rent_from'] = sanitize_text_field( $_POST[ 'wcrp_rental_products_availability_checker_rent_from' ] );

		}

	} elseif ( isset( $_COOKIE[ 'wcrp_rental_products_availability_checker_rent_from' ] ) ) {

		$data['rent_from'] = sanitize_text_field( $_COOKIE[ 'wcrp_rental_products_availability_checker_rent_from' ] );

	}

	if ( isset( $_POST[ 'wcrp_rental_products_availability_checker_rent_to' ] ) && isset( $_POST['wcrp_rental_products_availability_checker_nonce'] ) ) {

		if ( wp_verify_nonce( sanitize_key( $_POST['wcrp_rental_products_availability_checker_nonce'] ), 'wcrp_rental_products_availability_checker' ) ) {

			$data['rent_to'] = sanitize_text_field( $_POST[ 'wcrp_rental_products_availability_checker_rent_to' ] );

		}

	} elseif ( isset( $_COOKIE[ 'wcrp_rental_products_availability_checker_rent_to' ] ) ) {

		$data['rent_to'] = sanitize_text_field( $_COOKIE[ 'wcrp_rental_products_availability_checker_rent_to' ] );

	}

	if ( isset( $_POST[ 'wcrp_rental_products_availability_checker_quantity' ] ) && isset( $_POST['wcrp_rental_products_availability_checker_nonce'] ) ) {

		if ( wp_verify_nonce( sanitize_key( $_POST['wcrp_rental_products_availability_checker_nonce'] ), 'wcrp_rental_products_availability_checker' ) ) {

			$data['quantity'] = sanitize_text_field( $_POST[ 'wcrp_rental_products_availability_checker_quantity' ] );

		}

	} elseif ( isset( $_COOKIE[ 'wcrp_rental_products_availability_checker_quantity' ] ) ) {

		$data['quantity'] = sanitize_text_field( $_COOKIE[ 'wcrp_rental_products_availability_checker_quantity' ] );

	}

	if ( isset( $data['rent_from'] ) && isset( $data['rent_to'] ) && isset( $data['quantity'] ) ) {

		return $data;

	} else {

		return array();

	}

}

function wcrp_rental_products_check_availability( $product_id, $rent_from, $rent_to, $quantity, $args = array() ) {

	if ( !empty( $product_id ) && !empty( $rent_from ) && !empty( $rent_to ) && !empty( $quantity ) ) {

		$product = wc_get_product( $product_id );
		$product_parent_id = $product->get_parent_id();
		$product_sold_individually = $product->get_sold_individually();
		$product_type = $product->get_type();
		$quantity = (int) $quantity;

		$cart_checks = ( isset( $args['cart_checks'] ) ? $args['cart_checks'] : array() ); // Array will be populated if it's a specific cart check to condition off e.g. multiple_same_rental_product_combined_dates as certain availability checks need to be excluded if it's this cart check as it checks availability for the combined dates of multiple same rental products in cart by iterating through singular dates within the combined dates and therefore some of the checks such as disable rental start/end dates/days and minimum/maximum day checks shouldn't get checked in this scenario as would return unavailable when the full rent from/to and return days period is actually available
		$rental_form_add_to_order_checks = ( isset( $args['rental_form_add_to_order_checks'] ) ? $args['rental_form_add_to_order_checks'] : array() ); // Array will be populated if checking availability via rental form add to order

		$default_rental_options = wcrp_rental_products_default_rental_options();

		$disable_rental_dates_global = get_option( 'wcrp_rental_products_disable_rental_dates' );
		$disable_rental_dates_global_array = explode( ',', $disable_rental_dates_global );

		$disable_rental_start_end_dates_global = get_option( 'wcrp_rental_products_disable_rental_start_end_dates' );
		$disable_rental_start_end_dates_global_array = explode( ',', $disable_rental_start_end_dates_global );

		if ( 'variable' == $product_type ) {

			// Catch if not a rental product

			if ( false == wcrp_rental_products_is_rental_only( $product_id ) && false == wcrp_rental_products_is_rental_purchase( $product_id ) ) {

				return 'unavailable_non_rental';

			}

			// Variable products don't get rented, the variations within them do

			return 'unavailable_variable';

		} elseif ( 'variation' == $product_type ) {

			// Catch if not a rental product

			if ( false == wcrp_rental_products_is_rental_only( $product_parent_id ) && false == wcrp_rental_products_is_rental_purchase( $product_parent_id ) ) {

				return 'unavailable_non_rental';

			}

			// Get product data if available on variation, if not use parent

			$rental_stock = get_post_meta( $product_id, '_wcrp_rental_products_rental_stock', true ); // Try to get stock from variation (but maybe empty as set on parent product)

			if ( '' == $rental_stock ) { // Rental stock on variation empty so use the stock from inventory tab

				$rental_stock = get_post_meta( $product_parent_id, '_wcrp_rental_products_rental_stock', true );

			}

			$pricing_period_additional_selections = get_post_meta( $product_id, '_wcrp_rental_products_pricing_period_additional_selections', true );

			if ( '' == $pricing_period_additional_selections ) {

				$pricing_period_additional_selections = get_post_meta( $product_parent_id, '_wcrp_rental_products_pricing_period_additional_selections', true );

			}

			$pricing_period_additional_selections_array = WCRP_Rental_Products_Misc::value_colon_price_pipe_explode( $pricing_period_additional_selections, false );

			// Get product data from parent

			$pricing_type = get_post_meta( $product_parent_id, '_wcrp_rental_products_pricing_type', true );
			$pricing_type = ( '' !== $pricing_type ? $pricing_type : $default_rental_options['_wcrp_rental_products_pricing_type'] );

			$pricing_period = get_post_meta( $product_parent_id, '_wcrp_rental_products_pricing_period', true );
			$pricing_period = ( '' !== $pricing_period ? $pricing_period : $default_rental_options['_wcrp_rental_products_pricing_period'] );

			$pricing_period_multiples = get_post_meta( $product_parent_id, '_wcrp_rental_products_pricing_period_multiples', true );
			$pricing_period_multiples = ( '' !== $pricing_period_multiples ? $pricing_period_multiples : $default_rental_options['_wcrp_rental_products_pricing_period_multiples'] );

			$pricing_period_multiples_maximum = get_post_meta( $product_parent_id, '_wcrp_rental_products_pricing_period_multiples_maximum', true );
			$pricing_period_multiples_maximum = ( '' !== $pricing_period_multiples_maximum ? $pricing_period_multiples_maximum : $default_rental_options['_wcrp_rental_products_pricing_period_multiples_maximum'] );

			$minimum_days = get_post_meta( $product_parent_id, '_wcrp_rental_products_minimum_days', true );
			$minimum_days = ( '' !== $minimum_days ? $minimum_days : $default_rental_options['_wcrp_rental_products_minimum_days'] );

			$maximum_days = get_post_meta( $product_parent_id, '_wcrp_rental_products_maximum_days', true );
			$maximum_days = ( '' !== $maximum_days ? $maximum_days : $default_rental_options['_wcrp_rental_products_maximum_days'] );

			$start_day = get_post_meta( $product_parent_id, '_wcrp_rental_products_start_day', true );
			$start_day = ( '' !== $start_day ? $start_day : $default_rental_options['_wcrp_rental_products_start_day'] );

			$start_days_threshold = get_post_meta( $product_parent_id, '_wcrp_rental_products_start_days_threshold', true );
			$start_days_threshold = ( '' !== $start_days_threshold ? $start_days_threshold : $default_rental_options['_wcrp_rental_products_start_days_threshold'] );

			$return_days_threshold = get_post_meta( $product_parent_id, '_wcrp_rental_products_return_days_threshold', true );
			$return_days_threshold = ( '' !== $return_days_threshold ? $return_days_threshold : $default_rental_options['_wcrp_rental_products_return_days_threshold'] );

			$earliest_available_date = get_post_meta( $product_parent_id, '_wcrp_rental_products_earliest_available_date', true );
			$earliest_available_date = ( '' !== $earliest_available_date ? $earliest_available_date : $default_rental_options['_wcrp_rental_products_earliest_available_date'] );

			$disable_rental_dates_product = get_post_meta( $product_parent_id, '_wcrp_rental_products_disable_rental_dates', true );
			$disable_rental_dates_product = ( '' !== $disable_rental_dates_product ? $disable_rental_dates_product : $default_rental_options['_wcrp_rental_products_disable_rental_dates'] );
			$disable_rental_dates_product_array = explode( ',', $disable_rental_dates_product );

			$disable_rental_days = get_post_meta( $product_parent_id, '_wcrp_rental_products_disable_rental_days', true );
			$disable_rental_days = ( '' !== $disable_rental_days ? $disable_rental_days : $default_rental_options['_wcrp_rental_products_disable_rental_days'] );
			$disable_rental_days = explode( ',', $disable_rental_days ); // Day numbers not full date

			$disable_rental_start_end_dates_product = get_post_meta( $product_parent_id, '_wcrp_rental_products_disable_rental_start_end_dates', true );
			$disable_rental_start_end_dates_product = ( '' !== $disable_rental_start_end_dates_product ? $disable_rental_start_end_dates_product : $default_rental_options['_wcrp_rental_products_disable_rental_start_end_dates'] );
			$disable_rental_start_end_dates_product_array = explode( ',', $disable_rental_start_end_dates_product );

			$disable_rental_start_end_days = get_post_meta( $product_parent_id, '_wcrp_rental_products_disable_rental_start_end_days', true );
			$disable_rental_start_end_days = ( '' !== $disable_rental_start_end_days ? $disable_rental_start_end_days : $default_rental_options['_wcrp_rental_products_disable_rental_start_end_days'] );
			$disable_rental_start_end_days = explode( ',', $disable_rental_start_end_days ); // Day numbers not full date

			$disable_rental_start_end_days_type = get_post_meta( $product_parent_id, '_wcrp_rental_products_disable_rental_start_end_days_type', true );
			$disable_rental_start_end_days_type = ( '' !== $disable_rental_start_end_days_type ? $disable_rental_start_end_days_type : $default_rental_options['_wcrp_rental_products_disable_rental_start_end_days_type'] );

		} else {

			// Catch if not a rental product

			if ( false == wcrp_rental_products_is_rental_only( $product_id ) && false == wcrp_rental_products_is_rental_purchase( $product_id ) ) {

				return 'unavailable_non_rental';

			}

			// Get product data

			$rental_stock = get_post_meta( $product_id, '_wcrp_rental_products_rental_stock', true );

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

			$minimum_days = get_post_meta( $product_id, '_wcrp_rental_products_minimum_days', true );
			$minimum_days = ( '' !== $minimum_days ? $minimum_days : $default_rental_options['_wcrp_rental_products_minimum_days'] );

			$maximum_days = get_post_meta( $product_id, '_wcrp_rental_products_maximum_days', true );
			$maximum_days = ( '' !== $maximum_days ? $maximum_days : $default_rental_options['_wcrp_rental_products_maximum_days'] );

			$start_day = get_post_meta( $product_id, '_wcrp_rental_products_start_day', true );
			$start_day = ( '' !== $start_day ? $start_day : $default_rental_options['_wcrp_rental_products_start_day'] );

			$start_days_threshold = get_post_meta( $product_id, '_wcrp_rental_products_start_days_threshold', true );
			$start_days_threshold = ( '' !== $start_days_threshold ? $start_days_threshold : $default_rental_options['_wcrp_rental_products_start_days_threshold'] );

			$return_days_threshold = get_post_meta( $product_id, '_wcrp_rental_products_return_days_threshold', true );
			$return_days_threshold = ( '' !== $return_days_threshold ? $return_days_threshold : $default_rental_options['_wcrp_rental_products_return_days_threshold'] );

			$earliest_available_date = get_post_meta( $product_id, '_wcrp_rental_products_earliest_available_date', true );
			$earliest_available_date = ( '' !== $earliest_available_date ? $earliest_available_date : $default_rental_options['_wcrp_rental_products_earliest_available_date'] );

			$disable_rental_dates_product = get_post_meta( $product_id, '_wcrp_rental_products_disable_rental_dates', true );
			$disable_rental_dates_product = ( '' !== $disable_rental_dates_product ? $disable_rental_dates_product : $default_rental_options['_wcrp_rental_products_disable_rental_dates'] );
			$disable_rental_dates_product_array = explode( ',', $disable_rental_dates_product );

			$disable_rental_days = get_post_meta( $product_id, '_wcrp_rental_products_disable_rental_days', true );
			$disable_rental_days = ( '' !== $disable_rental_days ? $disable_rental_days : $default_rental_options['_wcrp_rental_products_disable_rental_days'] );
			$disable_rental_days = explode( ',', $disable_rental_days ); // Day numbers not full date

			$disable_rental_start_end_dates_product = get_post_meta( $product_id, '_wcrp_rental_products_disable_rental_start_end_dates', true );
			$disable_rental_start_end_dates_product = ( '' !== $disable_rental_start_end_dates_product ? $disable_rental_start_end_dates_product : $default_rental_options['_wcrp_rental_products_disable_rental_start_end_dates'] );
			$disable_rental_start_end_dates_product_array = explode( ',', $disable_rental_start_end_dates_product );

			$disable_rental_start_end_days = get_post_meta( $product_id, '_wcrp_rental_products_disable_rental_start_end_days', true );
			$disable_rental_start_end_days = ( '' !== $disable_rental_start_end_days ? $disable_rental_start_end_days : $default_rental_options['_wcrp_rental_products_disable_rental_start_end_days'] );
			$disable_rental_start_end_days = explode( ',', $disable_rental_start_end_days ); // Day numbers not full date

			$disable_rental_start_end_days_type = get_post_meta( $product_id, '_wcrp_rental_products_disable_rental_start_end_days_type', true );
			$disable_rental_start_end_days_type = ( '' !== $disable_rental_start_end_days_type ? $disable_rental_start_end_days_type : $default_rental_options['_wcrp_rental_products_disable_rental_start_end_days_type'] );

		}

		// Catch if quantity greater than 1 and product is only sold individually

		if ( $quantity > 1 && true == $product_sold_individually ) {

			return 'unavailable_sold_individually';

		}

		// Combined global and product arrays

		$disable_rental_dates_combined = array_merge( $disable_rental_dates_global_array, $disable_rental_dates_product_array );
		$disable_rental_start_end_dates_combined = array_merge( $disable_rental_start_end_dates_global_array, $disable_rental_start_end_dates_product_array );

		// If rental stock is empty set stock available to unlimited rental stock, otherwise the rental stock level

		if ( '' == $rental_stock ) {

			$stock_available = PHP_INT_MAX; // Unlimited rental stock

		} else {

			$stock_available = (int) $rental_stock; // Use rental stock level

		}

		// Get combined dates excluding return days

		$combined_dates_exc_return_days = array();
		$combined_dates_exc_return_days_current = strtotime( $rent_from );
		$combined_dates_exc_return_days_last = strtotime( $rent_to );

		while ( $combined_dates_exc_return_days_current <= $combined_dates_exc_return_days_last ) {

			$combined_dates_exc_return_days[] = gmdate( 'Y-m-d', $combined_dates_exc_return_days_current );
			$combined_dates_exc_return_days_current = strtotime( '+1 day', $combined_dates_exc_return_days_current );

		}

		$combined_dates_exc_return_days_count = count( $combined_dates_exc_return_days );

		// Get combined dates including return days

		$combined_dates_inc_return_days = array();
		$combined_dates_inc_return_days_current = strtotime( $rent_from );
		$combined_dates_inc_return_days_last = strtotime( '+' . $return_days_threshold . ' days', strtotime( $rent_to ) ); // Date to plus the return days to ensure we take into account how long it takes for this item to be returned as well as the selected end date e.g. for a product with 1 stock it would stop someone renting for 1st to 3rd when someone has already rented 4th-5th as 1st to 3rd is actually 1st to 6th (if 3 return days)

		while ( $combined_dates_inc_return_days_current <= $combined_dates_inc_return_days_last ) {

			$combined_dates_inc_return_days[] = gmdate( 'Y-m-d', $combined_dates_inc_return_days_current );
			$combined_dates_inc_return_days_current = strtotime( '+1 day', $combined_dates_inc_return_days_current );

		}

		$combined_dates_inc_return_days_count = count( $combined_dates_inc_return_days );

		// Standard availability checks which aren't done if multiple_same_rental_product_combined_dates excluded from $cart_checks as these are iterative individual date based checks, generally from WCRP_Rental_Products_Cart_Checks::check_rental_cart_items(), these standard checks will already be picked up and be flagged as unavailable before the multiple_same_rental_product_combined_dates anyway due to the overall date range check that calls this function in WCRP_Rental_Products_Cart_Checks::check_rental_cart_items()

		if ( !in_array( 'multiple_same_rental_product_combined_dates', $cart_checks ) ) {

			// These are in order of the product option fields

			// Check rent to date does not exceed maximum date allowed

			if ( $rent_to > wcrp_rental_products_rental_form_maximum_date( 'date' ) ) {

				return 'unavailable_dates';

			}

			// Check pricing period multiples

			if ( 'yes' == $pricing_period_multiples ) {

				// Check when pricing period multiples enabled that total rental days ($combined_dates_exc_return_days_count) must be equal to or a multiple of the pricing period set

				if ( (int) $pricing_period !== $combined_dates_exc_return_days_count ) { // If pricing period is the same as rental days total then check passes

					$is_multiple_check = ( 0 === $combined_dates_exc_return_days_count % (int) $pricing_period );

					if ( false == $is_multiple_check ) { // If rental days total is not a multiple of pricing period then unavailable (e.g. if pricing period is 2 and pricing multiples enabled it must be a multiple of 2, if total rental days is 5 it's unavailable)

						return 'unavailable_dates';

					}

				}

				// Check when pricing period multiples enabled that multiples does not exceed the maxmimum allowed multiples

				if ( '0' !== $pricing_period_multiples_maximum ) { // Not unlimited

					if ( $combined_dates_exc_return_days_count > ( $pricing_period * (int) $pricing_period_multiples_maximum ) ) { // If the total rental days exceeds the days available based on the maximum amount of multiples then unavailable (e.g. if pricing period is 7, multiples maximum is 4 then the total rental days cannot exceed 28)

						return 'unavailable_dates';

					}

				}

			}

			// Check pricing period additional selections (if is period selection but not an additional selection then these get checked via the minimum/maximum days checks)

			if ( 'period_selection' == $pricing_type ) {

				if ( (int) $pricing_period !== $combined_dates_exc_return_days_count ) {

					// Check date period is a valid pricing period additional selection

					if ( !isset( $pricing_period_additional_selections_array[$combined_dates_exc_return_days_count] ) ) {

						return 'unavailable_dates';

					}

				}

			}

			// Check minimum/maximum days

			$minimum_maximum_days_check = true;

			if ( 'period' == $pricing_type && (int) $pricing_period > 1 && 'yes' == $pricing_period_multiples ) {

				// Check does not occur if pricing type is period, pricing period is > 1 and pricing period multiples enabled, this is because if multiples are used then min/max days are still set to the initial period and would get an unavailable return with this check, however this scenario is checked via the pricing period multiples checks elsewhere in this function

				$minimum_maximum_days_check = false;

			} elseif ( 'period_selection' == $pricing_type && ( (int) $pricing_period !== $combined_dates_exc_return_days_count ) ) {

				// Check does not occur if pricing type is period selection and pricing period isn't the total days rented, this is because the period will be an additional selections and min/max days are still set to the initial period and would get an unavailable return with this check, however this scenario is checked via the pricing period selections checks elsewhere in this function, this minimum/maximum days check does still occur if it is a period selection but pricing period is equal to the total days rented (the default selection) as the minimum/maximum days in this scenario should match the pricing period

				$minimum_maximum_days_check = false;

			}

			if ( true == $minimum_maximum_days_check ) {

				if ( (int) $minimum_days > 0 ) {

					if ( $combined_dates_exc_return_days_count < (int) $minimum_days ) {

						return 'unavailable_dates';

					}

				}

				if ( (int) $maximum_days > 0 ) {

					if ( $combined_dates_exc_return_days_count > (int) $maximum_days ) {

						return 'unavailable_dates';

					}

				}

			}

			// Check start day

			if ( '' !== $start_day ) { // If not any

				if ( gmdate( 'w', strtotime( $rent_from ) ) !== $start_day ) { // Date format w as $start_day is 0-6

					return 'unavailable_dates';

				}

			}

			// Check rental start date is valid

			if ( '' !== $earliest_available_date ) {

				// If earliest available date enabled

				if ( $rent_from < $earliest_available_date ) { // Check if rent from less than the earliest available date

					return 'unavailable_dates';

				}

			} else {

				// If earliest available date disabled

				if ( !in_array( 'rent_from_past_date', $rental_form_add_to_order_checks ) ) { // Check not done when adding a product to an order via dashboard, as user may wish to use past dates (this condition isn't included in the earliest available date condition above as in that scenario it shouldn't be possible to choose past dates)

					if ( $rent_from < gmdate( 'Y-m-d', strtotime( '+' . $start_days_threshold . ' days', time() ) ) ) { // Check if rent from less than todays date + the start days threshold

						return 'unavailable_dates';

					}

				}

			}

			// Check disable rental dates

			foreach ( $combined_dates_exc_return_days as $combined_dates_exc_return_days_date ) {

				// Loop through $combined_dates_exc_return_days and check these dates are not a disabled rental date, $combined_dates_inc_return_days is not used because they may include disabled dates in the return days threshold which should not be deemed unavailable as rental products can be returned during disabled days (just not rented on)

				if ( in_array( $combined_dates_exc_return_days_date, $disable_rental_dates_combined ) ) {

					return 'unavailable_dates';

				}

			}

			// Check disable rental days

			$disable_rental_days_dates = array();

			foreach ( $disable_rental_days as $disable_rental_days_day ) {

				$now = strtotime( 'now' );
				$end_date = strtotime( '+' . wcrp_rental_products_rental_form_maximum_date( 'days' ) . ' days' );

				while ( gmdate( 'Y-m-d', $now ) !== gmdate( 'Y-m-d', $end_date ) ) {

					$day_index = gmdate( 'w', $now );

					if ( $day_index == $disable_rental_days_day ) {

						$disable_rental_days_dates[] = gmdate( 'Y-m-d', $now );

					}

					$now = strtotime( gmdate( 'Y-m-d', $now ) . '+1 day' );

				}

			}

			foreach ( $combined_dates_exc_return_days as $combined_dates_exc_return_days_date ) {

				// Loop through $combined_dates_exc_return_days and check these dates are not a disabled rental day, $combined_dates_inc_return_days is not used because they may include disabled days in the return days threshold which should not be deemed unavailable as rental products can be returned during disabled days (just not rented on)

				if ( in_array( $combined_dates_exc_return_days_date, $disable_rental_days_dates ) ) {

					return 'unavailable_dates';

				}

			}

			// Check if start or end day is not one of disable rental start/end dates

			if ( in_array( $rent_from, $disable_rental_start_end_dates_combined ) || in_array( $rent_to, $disable_rental_start_end_dates_combined ) ) {

				return 'unavailable_dates';

			}

			// Check if start and/or end day is not one of disable rental start/end days depending on the type used

			if ( 'start_end' == $disable_rental_start_end_days_type ) {

				if ( in_array( gmdate( 'w', strtotime( $rent_from ) ), $disable_rental_start_end_days ) || in_array( gmdate( 'w', strtotime( $rent_to ) ), $disable_rental_start_end_days ) ) { // Date format w as $disable_rental_start_end_days is 0-6

					return 'unavailable_dates';

				}

			} elseif ( 'start' == $disable_rental_start_end_days_type ) {

				if ( in_array( gmdate( 'w', strtotime( $rent_from ) ), $disable_rental_start_end_days ) ) { // Date format w as $disable_rental_start_end_days is 0-6

					return 'unavailable_dates';

				}

			} elseif ( 'end' == $disable_rental_start_end_days_type ) {

				if ( in_array( gmdate( 'w', strtotime( $rent_to ) ), $disable_rental_start_end_days ) ) { // Date format w as $disable_rental_start_end_days is 0-6

					return 'unavailable_dates';

				}

			}

		}

		// Check if available based on stock/dates already booked

		if ( $quantity > $stock_available ) {

			return 'unavailable_stock_max_' . $stock_available; // Quantity needed is higher than the max rental stock, so no need to check any further

		} else {

			// Loop through combined dates inc return days (as we need all the dates including returns as this is when product is not available)

			foreach ( $combined_dates_inc_return_days as $combined_dates_inc_return_days_date ) {

				// Get total rented on date of all rentals which match this date and product id iteration

				$total_rented_on_date = WCRP_Rental_Products_Stock_Helpers::total_rented_on_date(
					array(
						'date'			=> $combined_dates_inc_return_days_date,
						'product_id'	=> $product_id,
					)
				);

				// If stock available is less than available

				if ( $stock_available < ( $quantity + $total_rented_on_date ) ) {

					return 'unavailable_stock_' . ( $stock_available - $total_rented_on_date );

				}

			}

		}

		// Return available

		return 'available';

	}

}

function wcrp_rental_products_default_rental_options() {

	// This function defines the default rental options, note that these are NOT USED to pre-populate rental product option fields when creating new products, they are used to define the default value for some rental product options in a number of scenarios e.g. product options which return a default value when empty, product options which can be set to use default from settings and/or as fallbacks if product option meta is missing

	$options = array();

	$options['_wcrp_rental_products_rental'] = '';
	$options['_wcrp_rental_products_rental_purchase_price'] = '';
	$options['_wcrp_rental_products_rental_stock'] = '';
	$options['_wcrp_rental_products_pricing_type'] = 'period';
	$options['_wcrp_rental_products_pricing_period'] = '1';
	$options['_wcrp_rental_products_pricing_period_multiples'] = 'no';
	$options['_wcrp_rental_products_pricing_period_multiples_maximum'] = '0';
	$options['_wcrp_rental_products_pricing_period_additional_selections'] = '';
	$options['_wcrp_rental_products_pricing_tiers'] = 'no';
	$options['_wcrp_rental_products_pricing_tiers_data'] = array(
		'days' => array(
			'0' => '1'
		),
		'percent' => array(
			'0' => '0'
		),
	);
	$options['_wcrp_rental_products_price_additional_periods_percent'] = 'no';
	$options['_wcrp_rental_products_price_additional_period_percent'] = '';
	$options['_wcrp_rental_products_price_display_override'] = '';
	$options['_wcrp_rental_products_total_overrides'] = '';
	$options['_wcrp_rental_products_advanced_pricing'] = get_option( 'wcrp_rental_products_advanced_pricing' ); // Default from settings
	$options['_wcrp_rental_products_in_person_pick_up_return'] = 'no';
	$options['_wcrp_rental_products_in_person_pick_up_return_time_restrictions'] = get_option( 'wcrp_rental_products_in_person_pick_up_return_time_restrictions' ); // Default from settings
	$options['_wcrp_rental_products_in_person_return_date'] = get_option( 'wcrp_rental_products_in_person_return_date' ); // Default from settings
	$options['_wcrp_rental_products_in_person_pick_up_times_fees_same_day'] = get_option( 'wcrp_rental_products_in_person_pick_up_times_fees_same_day' ); // Default from settings
	$options['_wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day'] = get_option( 'wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day' ); // Default from settings
	$options['_wcrp_rental_products_in_person_return_times_fees_same_day'] = get_option( 'wcrp_rental_products_in_person_return_times_fees_same_day' ); // Default from settings
	$options['_wcrp_rental_products_in_person_return_times_fees_single_day_same_day'] = get_option( 'wcrp_rental_products_in_person_return_times_fees_single_day_same_day' ); // Default from settings
	$options['_wcrp_rental_products_in_person_pick_up_times_fees_next_day'] = get_option( 'wcrp_rental_products_in_person_pick_up_times_fees_next_day' ); // Default from settings
	$options['_wcrp_rental_products_in_person_return_times_fees_next_day'] = get_option( 'wcrp_rental_products_in_person_return_times_fees_next_day' ); // Default from settings
	$options['_wcrp_rental_products_minimum_days'] = '1';
	$options['_wcrp_rental_products_maximum_days'] = '0';
	$options['_wcrp_rental_products_start_day'] = '';
	$options['_wcrp_rental_products_start_days_threshold'] = '3';
	$options['_wcrp_rental_products_return_days_threshold'] = '3';
	$options['_wcrp_rental_products_earliest_available_date'] = '';
	$options['_wcrp_rental_products_disable_rental_dates'] = '';
	$options['_wcrp_rental_products_disable_rental_days'] = '';
	$options['_wcrp_rental_products_disable_rental_start_end_dates'] = '';
	$options['_wcrp_rental_products_disable_rental_start_end_days'] = '';
	$options['_wcrp_rental_products_disable_rental_start_end_days_type'] = 'start_end';
	$options['_wcrp_rental_products_security_deposit_amount'] = '';
	$options['_wcrp_rental_products_security_deposit_calculation'] = 'quantity';
	$options['_wcrp_rental_products_security_deposit_tax_status'] = 'taxable';
	$options['_wcrp_rental_products_security_deposit_tax_class'] = ''; // Standard tax class
	$options['_wcrp_rental_products_security_deposit_non_refundable'] = 'no';
	$options['_wcrp_rental_products_multiply_addons_total_by_number_of_days_selected'] = 'no';
	$options['_wcrp_rental_products_disable_addons_rental_purchase_rental'] = 'no';
	$options['_wcrp_rental_products_disable_addons_rental_purchase_purchase'] = 'no';
	$options['_wcrp_rental_products_months'] = '1';
	$options['_wcrp_rental_products_columns'] = '1';
	$options['_wcrp_rental_products_inline'] = 'no';
	$options['_wcrp_rental_products_rental_information'] = '';
	$options['_wcrp_rental_products_rental_purchase_rental_tax_override'] = 'no';
	$options['_wcrp_rental_products_rental_purchase_rental_tax_override_status'] = 'taxable';
	$options['_wcrp_rental_products_rental_purchase_rental_tax_override_class'] = ''; // Standard tax class
	$options['_wcrp_rental_products_rental_purchase_rental_shipping_override'] = 'no';
	$options['_wcrp_rental_products_rental_purchase_rental_shipping_override_class'] = ''; // Standard tax class

	$options = apply_filters( 'wcrp_rental_products_default_rental_options', $options ); // This is legacy filter hook, it is likely defaults will be managed via settings in future at which point this hook will be removed, if the defaults are filtered you may experience product data issues in some scenarios, filtering via this hook is not recommended

	return $options;

}

function wcrp_rental_products_is_rental_only( $product_id ) {

	// Returns true if the product is rental only, if a rental product variation id is passed it will not return true, this function is intended to be used purely for determining if the entire product (main product id) is rental only

	$is_rental = false;

	if ( !empty( $product_id ) ) {

		if ( 'yes' == get_post_meta( $product_id, '_wcrp_rental_products_rental', true ) ) {

			$is_rental = true;

		}

	}

	return $is_rental;

}

function wcrp_rental_products_is_rental_purchase( $product_id ) {

	/*
	Returns true if the product is rental or purchase, if a rental product variation id is passed it will not return true, this function is intended to be used purely for determining if the entire product (main product id) is rental or purchase

	We do not include a wcrp_rental_products_is_rental_purchase_rental function that would return true if it is the rental part of a rental or purchase product as this requires getting of $_GET[rent] = 1 and therefore the function would only be usable on the product page itself not everywhere and could potentially cause confusion
	*/

	$is_rental_purchase = false;

	if ( !empty( $product_id ) ) {

		if ( 'yes_purchase' == get_post_meta( $product_id, '_wcrp_rental_products_rental', true ) ) {

			$is_rental_purchase = true;

		}

	}

	return $is_rental_purchase;

}

function wcrp_rental_products_order_has_rentals( $order_id ) {

	// Returns true if given order has rentals, including rentals that have been cancelled

	$order_has_rentals = false;

	if ( !empty( $order_id ) ) {

		global $wpdb;

		// Note that wcrp_rental_products_rent_from is used in the query as every rental will have this, except for cancelled which removes rent from/to meta, hence wcrp_rental_products_cancelled also included in the query

		$rentals = $wpdb->get_results(
			$wpdb->prepare( "
				SELECT COUNT(*) AS count
				FROM `{$wpdb->prefix}woocommerce_order_items` as oi
				INNER JOIN `{$wpdb->prefix}woocommerce_order_itemmeta` as oim
				ON oi.order_item_id = oim.order_item_id
				WHERE meta_key IN ( 'wcrp_rental_products_rent_from', 'wcrp_rental_products_cancelled' )
				AND oi.order_id = %d
			", $order_id )
		);

		if ( !empty( $rentals ) ) {

			if ( $rentals[0]->count > 0 ) {

				$order_has_rentals = true;

			}

		}

	}

	return $order_has_rentals;

}

function wcrp_rental_products_rental_date_format() {

	$rental_date_format = get_option( 'wcrp_rental_products_rental_date_format' );

	if ( !empty( $rental_date_format ) ) {

		$return = $rental_date_format;

	} else {

		$return = 'Y-m-d'; // Default - this is referenced on the settings page and an update here should be updated there

	}

	return $return;

}

function wcrp_rental_products_rental_form_date_format() {

	$rental_form_date_format = get_option( 'wcrp_rental_products_rental_form_date_format' );

	if ( !empty( $rental_form_date_format ) ) {

		$return = $rental_form_date_format;

	} else {

		$return = 'D MMM YYYY'; // Default - this is referenced on the settings page and an update here should be updated there

	}

	return $return;

}

function wcrp_rental_products_rental_form_first_day() {

	$rental_form_first_day = get_option( 'wcrp_rental_products_rental_form_first_day' );

	if ( false === $rental_form_first_day ) { // If option does not exist return default, === required (this is used rather than !empty as 0 is a valid value which would trigger empty being true)

		$return = '1'; // Default - this is referenced on the settings page and an update here should be updated there (Monday being first in the setting select field)

	} else { // If does exist return the value

		$return = $rental_form_first_day;

	}

	return $return;

}

function wcrp_rental_products_rental_form_maximum_date( $return_type ) {

	// Returns the maximum date as string if $return_type == 'date', returns the number of days from current date until maximum date as int if $return_type = 'days'

	$rental_form_maximum_date_days = get_option( 'wcrp_rental_products_rental_form_maximum_date_days' );
	$rental_form_maximum_date_specific = get_option( 'wcrp_rental_products_rental_form_maximum_date_specific' );

	if ( empty( $rental_form_maximum_date_days ) ) {

		$rental_form_maximum_date_days = '730'; // Default - this is referenced on the settings page and an update here should be updated there

	}

	if ( !empty( $rental_form_maximum_date_specific ) ) {

		$date = $rental_form_maximum_date_specific;
		$days = WCRP_Rental_Products_Misc::days_total_from_dates( wp_date( 'Y-m-d' ), $rental_form_maximum_date_specific );

	} else {

		$date = wp_date( 'Y-m-d', strtotime( '+ ' . $rental_form_maximum_date_days . ' days' ) );
		$days = $rental_form_maximum_date_days;

	}

	if ( 'date' == $return_type ) {

		return (string) $date;

	} elseif ( 'days' == $return_type ) {

		return (int) $days;

	} else {

		return (int) $days;

	}

}

function wcrp_rental_products_rental_time_format() {

	$rental_time_format = get_option( 'wcrp_rental_products_rental_time_format' );

	if ( !empty( $rental_time_format ) ) {

		$return = $rental_time_format;

	} else {

		$return = 'H:i'; // Default - this is referenced on the settings page and an update here should be updated there

	}

	return $return;

}
