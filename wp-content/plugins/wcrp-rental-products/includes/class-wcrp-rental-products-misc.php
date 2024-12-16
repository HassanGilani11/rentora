<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Misc' ) ) {

	class WCRP_Rental_Products_Misc {

		public static function days_total_from_dates( $date_from, $date_to ) {

			$days_total = abs( round( ( strtotime( $date_to ) - strtotime( $date_from ) ) / 86400 ) ) + 1;

			return (int) $days_total;

		}

		public static function four_digit_time_convert_to_iso_string( $time ) {

			if ( !empty( $time ) ) {

				if ( 4 == strlen( $time ) ) {

					$hour = substr( $time, 0, 2 );
					$min = substr( $time, - 2 );
					$time = 'T' . $hour . ':' . $min . ':00';

				}

			}

			return $time;

		}

		public static function four_digit_time_convert_to_timestamp_string( $time ) {

			if ( !empty( $time ) ) {

				if ( 4 == strlen( $time ) ) {

					$hour = substr( $time, 0, 2 );
					$min = substr( $time, - 2 );
					$time = $hour . ':' . $min . ':00';

				}

			}

			return $time;

		}

		public static function four_digit_time_formatted( $time ) {

			if ( !empty( $time ) ) {

				if ( 4 == strlen( $time ) ) {

					$hour = substr( $time, 0, 2 );
					$min = substr( $time, - 2 );
					$time = gmdate( wcrp_rental_products_rental_time_format(), mktime( $hour, $min, null, null, null, null ) );

				}

			}

			return $time;

		}

		public static function string_starts_with( $string, $starts_with ) {

			$len = strlen( $starts_with );
			return ( substr( $string, 0, $len ) === $starts_with );

		}

		public static function string_contains( $string, $contains ) {

			if ( strpos( $string, $contains ) !== false ) {

				return true;

			} else {

				return false;

			}

		}

		public static function value_colon_price_pipe_explode( $string, $json_encode = false ) {

			// Important to note that this function does not convert price to inc/exc tax, it returns prices exactly as entered, this doesn't get converted as this would add complexities like passing the $product object to be able to use wc_get_price_to_display(), and applying rental or purchase overrides on that product object, and have it returning different prices than entered which could cause confusion for the expected return data, the conversion to inc/exc tax depending on tax setting is instead done later on the returned data, e.g. in the rental form via rentalFormUpdateAjaxRequest()

			$array = array();
			$price_decimal_separator = wc_get_price_decimal_separator();

			if ( '' !== $string ) {

				$string_explode_1 = explode( '|', $string );

				if ( !empty( $string_explode_1 ) ) { // If only 1 entry e.g. 3:5.00 still works (explode returns 1 array key/value)

					foreach ( $string_explode_1 as $string_explode_1_value ) {

						$string_explode_2 = explode( ':', $string_explode_1_value );

						if ( isset( $string_explode_2[0] ) && isset( $string_explode_2[1] ) ) { // Without this condition if the data was incorrectly entered (e.g. doesn't include the colon) it can mean that $string_explode_2[1] is null, causing PHP warnings of undefined array key 1 and deprecated: str_replace(): passing null to parameter #3 ($subject) of type array|string is deprecated

							if ( '' !== $string_explode_2[0] && '' !== $string_explode_2[1] ) { // '' !== rather than !empty, as even though no price supposed to be entered as 0.00, it might be entered as 0 which would trigger an empty false

								$array[ $string_explode_2[0] ] = str_replace( $price_decimal_separator, '.', $string_explode_2[1] ); // Replaces decimal separator with . otherwise calculations will not work when used later, should get converted back to use decimal separator later on after return has had further calculations

							}

						}

					}

				}

			}

			if ( true == $json_encode ) {

				return wp_json_encode( $array );

			} else {

				return $array;

			}

		}

	}

}
