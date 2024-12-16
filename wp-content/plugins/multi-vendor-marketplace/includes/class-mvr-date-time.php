<?php
/**
 * Date Time
 *
 * @package Name Your Own Price/Date Time Class
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Date_Time' ) ) {

	/**
	 * Main Class.
	 * */
	class MVR_Date_Time {

		/**
		 * WordPress TimeZone.
		 *
		 * @var String
		 * */
		private static $wp_timezone;

		/**
		 * WordPress Date Format.
		 *
		 * @var String
		 * */
		private static $wp_date_format;

		/**
		 * WordPress Time Format.
		 *
		 * @var String
		 * */
		private static $wp_time_format;

		/**
		 * Get WordPress TimeZone.
		 *
		 * @since 1.0.0
		 * */
		public static function get_wp_timezone() {
			if ( static::$wp_timezone ) {
				return static::$wp_timezone;
			}

			static::$wp_timezone = get_option( 'timezone_string' );

			if ( ! static::$wp_timezone ) {
				static::$wp_timezone = get_option( 'gmt_offset' );
			}

			return static::$wp_timezone;
		}

		/**
		 * Get WordPress Date Format.
		 *
		 * @since 1.0.0
		 * @return String
		 * */
		public static function get_wp_date_format() {
			if ( static::$wp_date_format ) {
				return static::$wp_date_format;
			}

			static::$wp_date_format = get_option( 'date_format' );

			return static::$wp_date_format;
		}

		/**
		 * Get WordPress Time Format.
		 *
		 * @since 1.0.0
		 * @return String
		 * */
		public static function get_wp_time_format() {
			if ( static::$wp_time_format ) {
				return static::$wp_time_format;
			}

			static::$wp_time_format = get_option( 'time_format' );

			return static::$wp_time_format;
		}

		/**
		 * Get WordPress Date/Time Format.
		 *
		 * @since 1.0.0
		 * @param String $separator Separator.
		 * @return String
		 * */
		public static function get_wp_datetime_format( $separator = ' ' ) {
			return self::get_wp_date_format() . $separator . self::get_wp_time_format();
		}

		/**
		 * Create Date/Time Mysql format.
		 *
		 * @since 1.0.0
		 * @param String $date Date.
		 * @param String $create_tz Timezone.
		 * @param String $convert_tz Timezone.
		 * @return String
		 * */
		public static function get_mysql_date_time_format( $date, $create_tz = false, $convert_tz = false ) {
			$date_object = self::get_date_time_object( $date, $create_tz, $convert_tz );

			return is_object( $date_object ) ? $date_object->format( 'Y-m-d H:i:s' ) : '';
		}

		/**
		 * Create Date/Time Object.
		 *
		 * @since 1.0.0
		 * @param String $date Date.
		 * @param String $create_tz Timezone.
		 * @param String $convert_tz Timezone.
		 * @return String
		 * */
		public static function get_date_time_object( $date, $create_tz = false, $convert_tz = false ) {
			$tz_object   = self::maybe_get_tz_object( $create_tz );
			$date_object = date_create( $date, $tz_object );

			if ( $convert_tz ) {
				$convert_tz       = ( true === $convert_tz ) ? self::get_wp_timezone() : $convert_tz;
				$time_zone_offset = self::maybe_get_tz_offset( $convert_tz );

				$date_object->setTimezone( timezone_open( $time_zone_offset ) );
			}

			return $date_object;
		}

		/**
		 * Create GMT Date/Time Object.
		 *
		 * @since 1.0.0
		 * @param String $date Date.
		 * @return String
		 * */
		public static function get_gmt_date_time_object( $date ) {
			return self::get_date_time_object( $date, true );
		}

		/**
		 * Create Date/TimeZone Object.
		 *
		 * @since 1.0.0
		 * @param String $time_zone TimeZone.
		 * @return String
		 * */
		public static function maybe_get_tz_object( $time_zone ) {
			$time_zone_offset = self::get_wp_timezone();

			if ( $time_zone ) {
				$time_zone_offset = ( true === $time_zone ) ? 'UTC' : $time_zone;
			}

			$time_zone_offset = self::maybe_get_tz_offset( $time_zone_offset );

			return timezone_open( $time_zone_offset );
		}

		/**
		 * Format date time based on WordPress.
		 *
		 * @since 1.0.0
		 * @param String  $date Date.
		 * @param String  $format Format.
		 * @param String  $create_tz Timezone.
		 * @param String  $convert_tz Timezone.
		 * @param String  $separator Separator.
		 * @param Boolean $display_tz Display Timezone.
		 * @return String
		 * */
		public static function get_wp_format_datetime( $date, $format = false, $create_tz = false, $convert_tz = false, $separator = ' ', $display_tz = false ) {
			$tz_format   = '';
			$date_object = self::get_date_time_object( $date, $create_tz, $convert_tz );

			if ( $display_tz ) {
				$tz_format = ' (UTC ' . $date_object->format( 'P' ) . ')';
			}

			switch ( $format ) {
				case 'date':
					$val = $date_object->format( self::get_wp_date_format() );
					break;
				case 'time':
					$val = $date_object->format( self::get_wp_time_format() ) . $tz_format;
					break;
				default:
					$format = ( $format ) ? $format : self::get_wp_date_format() . $separator . self::get_wp_time_format();
					$val    = $date_object->format( $format ) . $tz_format;
					break;
			}

			return $val;
		}

		/**
		 * Format Date object based on WordPress.
		 *
		 * @since 1.0.0
		 * @param String  $date_object Date Object.
		 * @param String  $format Format.
		 * @param String  $separator Separator.
		 * @param Boolean $display_tz Display Timezone.
		 * @return String
		 * */
		public static function get_wp_format_dateobject( $date_object, $format = false, $separator = ' ', $display_tz = false ) {
			$tz_format = '';

			if ( $display_tz ) {
				$tz_format = ' (UTC ' . $date_object->format( 'P' ) . ')';
			}

			switch ( $format ) {
				case 'date':
					$val = $date_object->format( self::get_wp_date_format() );
					break;
				case 'time':
					$val = $date_object->format( self::get_wp_time_format() ) . $tz_format;
					break;
				default:
					$format = ( $format ) ? $format : self::get_wp_date_format() . $separator . self::get_wp_time_format();
					$val    = $date_object->format( $format ) . $tz_format;
					break;
			}

			return $val;
		}

		/**
		 * Format date time based on WordPress from GMT.
		 *
		 * @since 1.0.0
		 * @param String  $date Date.
		 * @param String  $format Format.
		 * @param String  $separator Separator.
		 * @param Boolean $display_tz Display Timezone.
		 * @return String
		 * */
		public static function get_wp_format_datetime_from_gmt( $date, $format = false, $separator = ' ', $display_tz = false ) {
			return self::get_wp_format_datetime( $date, $format, true, true, $separator, $display_tz );
		}

		/**
		 * Format date time based on GMT from WordPress.
		 *
		 * @since 1.0.0
		 * @param String  $date Date.
		 * @param String  $format Format.
		 * @param String  $separator Separator.
		 * @param Boolean $display_tz Display Timezone.
		 * @return String
		 * */
		public static function get_gmt_format_datetime_from_wp( $date, $format = false, $separator = ' ', $display_tz = false ) {
			return self::get_wp_format_datetime( $date, $format, false, 'UTC', $separator, $display_tz );
		}

		/**
		 * Get TimeZone offset.
		 *
		 * @since 1.0.0
		 * @param Boolean $timezone Timezone.
		 * @return String
		 * */
		public static function maybe_get_tz_offset( $timezone ) {
			if ( ! is_numeric( $timezone ) ) {
				return $timezone;
			}

			$offset = (float) $timezone;

			if ( ! is_numeric( $timezone ) ) {
				$offset = (float) str_replace( 'utc', '', trim( strtolower( $timezone ) ) );
			}

			$hours     = (int) $offset;
			$minutes   = ( $offset - $hours );
			$sign      = ( $offset < 0 ) ? '-' : '+';
			$abs_hour  = abs( $hours );
			$abs_mins  = abs( $minutes * 60 );
			$tz_offset = sprintf( '%s%02d:%02d', $sign, $abs_hour, $abs_mins );

			return $tz_offset;
		}
	}

}
