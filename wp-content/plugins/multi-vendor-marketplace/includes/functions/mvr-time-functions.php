<?php
/**
 * Time Functions
 *
 * @package Multi Vendor Marketplace/ Time Functions
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'mvr_get_datetime' ) ) {

	/**
	 * Get the datetime objects in GMT/UTC+0.
	 *
	 * Don't pass the value directly as mentioned below -> the alternative suggestions for you,
	 * 1. strtotime(gmdate('y-m-d')) -> 'now'
	 * 2. date('y-m-d') -> 'now'
	 * 3. local date/time -> 'now'.
	 *
	 * @since 1.0.0
	 * @param Mixed $value A date/time string.
	 * @return WC_DateTime
	 */
	function mvr_get_datetime( $value ) {
		$original_timezone         = date_default_timezone_get();
		$date_default_timezone_set = 'date_default_timezone_set';
		$date_default_timezone_set( 'UTC' ); // WordPress.DateTime.RestrictedFunctions.timezone_change_date_default_timezone_set.

		if ( is_a( $value, 'WC_DateTime' ) ) {
			$datetime = $value;
		} elseif ( is_numeric( $value ) ) {
			// Timestamps are handled as UTC timestamps in all cases.
			$datetime = new WC_DateTime( "@{$value}", new DateTimeZone( 'UTC' ) );
		} else {
			// Strings are defined in local WP timezone. Convert to UTC.
			$timestamp = wc_string_to_timestamp( get_gmt_from_date( $value ) );
			$datetime  = new WC_DateTime( "@{$timestamp}", new DateTimeZone( 'UTC' ) );
		}

		// Set local timezone or offset.
		if ( get_option( 'timezone_string' ) ) {
			$datetime->setTimezone( new DateTimeZone( wc_timezone_string() ) );
		} else {
			$datetime->set_utc_offset( wc_timezone_offset() );
		}

		$date_default_timezone_set( $original_timezone ); // WordPress.DateTime.RestrictedFunctions.timezone_change_date_default_timezone_set.

		return $datetime;
	}
}



if ( ! function_exists( 'mvr_get_current_time' ) ) {
	/**
	 * Get the current time formatted in GMT/UTC 0 or +/- offset
	 *
	 * @since 1.0.0
	 * @param String  $format Accepts 'timestamp', or PHP date format string (e.g. 'Y-m-d').
	 * @param Boolean $gmt Optional. By default it will consider the WP offset.
	 * @return Mixed
	 */
	function mvr_get_current_time( $format = 'timestamp', $gmt = false ) {
		$now = mvr_get_datetime( 'now' );

		if ( 'timestamp' === $format ) {
			return $gmt ? $now->getTimestamp() : $now->getOffsetTimestamp();
		}

		return $gmt ? $now->gmdate( $format ) : $now->date( $format );
	}
}

if ( ! function_exists( 'mvr_format_datetime' ) ) {
	/**
	 * Format the date for output.
	 *
	 * @since 1.0.0
	 * @param Mixed   $date A valid date/time string or WC_DateTime object.
	 * @param String  $format Date format. Defaults to the wp_date_format function if not set.
	 * @param Boolean $human_read Human readable.
	 * @return String
	 */
	function mvr_format_datetime( $date, $format = '', $human_read = true ) {
		if ( empty( $date ) ) {
			return '-';
		}

		$datetime      = mvr_get_datetime( $date );
		$current_time  = mvr_get_current_time();
		$ago_time_diff = $current_time - $datetime->getOffsetTimestamp();
		$in_time_diff  = $datetime->getOffsetTimestamp() - $current_time;

		if ( $ago_time_diff > 0 && $ago_time_diff < DAY_IN_SECONDS ) {
			if ( $human_read ) {
				/* translators: 1: time */
				$display_time = sprintf( __( '%1$s ago', 'multi-vendor-marketplace' ), human_time_diff( $datetime->getOffsetTimestamp(), $current_time ) );
			} else {
				$display_time = human_time_diff( $datetime->getOffsetTimestamp(), $current_time );
			}
		} elseif ( $in_time_diff > 0 && $in_time_diff < DAY_IN_SECONDS ) {
			if ( $human_read ) {
				/* translators: 1: time */
				$display_time = sprintf( __( 'In %1$s', 'multi-vendor-marketplace' ), human_time_diff( $datetime->getOffsetTimestamp(), $current_time ) );
			} else {
				$display_time = human_time_diff( $datetime->getOffsetTimestamp(), $current_time );
			}
		} else {
			$display_time = false;
		}

		if ( ! $display_time ) {
			if ( empty( $format ) ) {
				$format = wc_date_format();
			}

			$display_time = $datetime->date_i18n( $format );
		}

		return $display_time;
	}
}


if ( ! function_exists( 'mvr_get_human_time_diff' ) ) {
	/**
	 * Get the human time difference for any time to output.
	 *
	 * @since 1.0.0
	 * @param Mixed $date A valid date/time string or WC_DateTime object.
	 * @return String
	 */
	function mvr_get_human_time_diff( $date ) {
		if ( empty( $date ) ) {
			return '-';
		}

		$from_date = mvr_get_datetime( 'now' );
		$to_date   = mvr_get_datetime( $date );

		if ( $to_date->getTimestamp() < $from_date->getOffsetTimestamp() ) {
			return 'now';
		}

		return $to_date->diff( $from_date )->format( '<b>%a</b> day(s), <b>%H</b> hour(s), <b>%I</b> minute(s), <b>%S</b> second(s)' );
	}
}

if ( ! function_exists( 'mvr_get_descriptive_week_of_month' ) ) {
	/**
	 * Descriptive Week of Month
	 *
	 * @since 1.0.0
	 * @param String $key Key of the Array.
	 * @return Array|String
	 */
	function mvr_get_descriptive_week_of_month( $key = '' ) {
		/**
		 * Descriptive Week of Month
		 *
		 * @since 1.0.0
		 */
		$args = apply_filters(
			'mvr_get_descriptive_week_of_month',
			array(
				'1' => 'first',
				'2' => 'second',
				'3' => 'third',
				'4' => 'fourth',
				'L' => 'last',
			)
		);

		if ( ! empty( $key ) ) {
			return isset( $args[ $key ] ) ? $args[ $key ] : '';
		}

		return $args;
	}
}

if ( ! function_exists( 'mvr_week_days' ) ) {

	/**
	 * Get Week Days
	 *
	 * @since 1.0.0
	 * @param String $key Key of the option.
	 * @return Array
	 */
	function mvr_week_days( $key = '' ) {

		/**
		 * Week days
		 *
		 * @hook: mvr_week_days
		 * @since 1.0.0
		 */
		$args = apply_filters(
			'mvr_week_days',
			array(
				'1' => 'Sunday',
				'2' => 'Monday',
				'3' => 'Tuesday',
				'4' => 'Wednesday',
				'5' => 'Thursday',
				'6' => 'Friday',
				'7' => 'Saturday',
			)
		);

		if ( ! empty( $key ) ) {
			return isset( $args[ $key ] ) ? $args[ $key ] : '';
		}

		return $args;
	}
}

if ( ! function_exists( 'mvr_months' ) ) {

	/**
	 * Get Months
	 *
	 * @since 1.0.0
	 * @param String $key Key of the option.
	 * @return Array
	 */
	function mvr_months( $key = '' ) {

		/**
		 * Week days
		 *
		 * @hook: mvr_months
		 * @since 1.0.0
		 */
		$args = apply_filters(
			'mvr_months',
			array(
				'1'  => 'January',
				'2'  => 'February',
				'3'  => 'March',
				'4'  => 'April',
				'5'  => 'May',
				'6'  => 'June',
				'7'  => 'July',
				'8'  => 'August',
				'9'  => 'September',
				'10' => 'October',
				'11' => 'November',
				'12' => 'December',
			)
		);

		if ( ! empty( $key ) ) {
			return isset( $args[ $key ] ) ? $args[ $key ] : '';
		}

		return $args;
	}
}
