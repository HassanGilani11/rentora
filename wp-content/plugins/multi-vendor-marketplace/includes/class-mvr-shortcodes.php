<?php
/**
 * Shortcodes
 *
 * @package Multi-Vendor for WooCommerce\Classes
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Shortcodes' ) ) {

	/**
	 * Multi Vendor Shortcodes class.
	 */
	class MVR_Shortcodes {

		/**
		 * Get the available shortcodes.
		 *
		 * @var Array
		 */
		protected static $shortcodes = array(
			'mvr_dashboard'       => __CLASS__ . '::vendor_dashboard',
			'mvr_vendor_register' => __CLASS__ . '::vendor_register',
			'mvr_vendor_login'    => __CLASS__ . '::vendor_login',
			'mvr_stores'          => __CLASS__ . '::stores',
		);

		/**
		 * Init shortcodes.
		 *
		 * @since 1.0.0
		 */
		public static function init() {
			foreach ( self::$shortcodes as $shortcode => $function ) {

				/**
				 * Shortcode Tag.
				 *
				 * @since 1.0.0
				 */
				add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );
			}
		}

		/**
		 * Return the array of available shortcodes.
		 *
		 * @since 1.0.0
		 * @return Array
		 */
		public static function get_shortcodes() {
			return self::$shortcodes;
		}

		/**
		 * Shortcode Wrapper.
		 *
		 * @since 1.0.0
		 * @param String $function Callback function.
		 * @param Array  $atts     Attributes. Default to empty array.
		 * @param Array  $wrapper  Customer wrapper data.
		 * @return String
		 */
		public static function shortcode_wrapper( $function, $atts = array(), $wrapper = array(
			'class'  => 'multi-vendor-marketplace woocommerce',
			'before' => null,
			'after'  => null,
		) ) {
			ob_start();

			echo empty( $wrapper['before'] ) ? '<div class="' . esc_attr( $wrapper['class'] ) . '">' : wp_kses_post( $wrapper['before'] );
			call_user_func( $function, $atts );
			echo empty( $wrapper['after'] ) ? '</div>' : wp_kses_post( $wrapper['after'] );

			return ob_get_clean();
		}

		/**
		 * Vendor Dashboard page shortcode.
		 *
		 * @since 1.0.0
		 * @param Array $atts Attributes.
		 * @return String
		 */
		public static function vendor_dashboard( $atts ) {
			return self::shortcode_wrapper( array( 'MVR_Shortcode_Vendor_Dashboard', 'output' ), $atts );
		}

		/**
		 * Vendor Register page shortcode.
		 *
		 * @since 1.0.0
		 * @param Array $atts Attributes.
		 * @return String
		 */
		public static function vendor_register( $atts ) {
			return self::shortcode_wrapper( array( 'MVR_Shortcode_Vendor_Register', 'output' ), $atts );
		}

		/**
		 * Vendor Login page shortcode.
		 *
		 * @since 1.0.0
		 * @param Array $atts Attributes.
		 * @return String
		 */
		public static function vendor_login( $atts ) {
			return self::shortcode_wrapper( array( 'MVR_Shortcode_Vendor_Login', 'output' ), $atts );
		}

		/**
		 * Stores page shortcode.
		 *
		 * @since 1.0.0
		 * @param Array $atts Attributes.
		 * @return String
		 */
		public static function stores( $atts ) {
			ob_start();
				echo '<div class="multi-vendor-marketplace">';
					MVR_Shortcode_Stores::output( $atts );
				echo '</div>';
			return ob_get_clean();
		}
	}
}
