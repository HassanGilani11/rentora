<?php
/**
 * Vendor Registration
 *
 * @package Multi-Vendor/Registration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Register' ) ) {

	/**
	 * Vendor Registration
	 *
	 * @since 1.0.0
	 */
	class MVR_Register {
		/**
		 * Init Vendor Registration.
		 *
		 * @since 1.0.0
		 */
		public static function init() {
			// Woo Register Form.
			add_action( 'woocommerce_register_form', array( __CLASS__, 'woo_vendor_register_form' ) );

			// After registration.
			add_filter( 'woocommerce_new_customer_data', array( __CLASS__, 'set_vendor_role' ) );
			add_filter( 'woocommerce_new_customer_data', array( __CLASS__, 'set_staff_role' ) );
		}

		/**
		 * Validate vendor registration
		 *
		 * @since 1.0.0
		 */
		public static function woo_vendor_register_form() {
			if ( 'yes' === get_option( 'mvr_settings_allow_guest_vendor_reg', 'no' ) ) {
				echo '<div class="mvr-vendor-register-link"><a href="' . esc_url( mvr_get_page_permalink( 'vendor_register' ) ) . '">' . esc_html__( 'Became a Vendor', 'multi-vendor-marketplace' ) . '</a></div>';
			}
		}

		/**
		 * Set Vendor Role
		 *
		 * @since 1.0.0
		 * @param Array $data Data.
		 * @return Array
		 */
		public static function set_vendor_role( $data ) {
			if ( ! mvr_check_is_array( $data ) || ! isset( $data['is_mvr_vendor'] ) || false === $data['is_mvr_vendor'] ) {
				return $data;
			}

			$data['role'] = 'mvr-vendor';

			return $data;
		}

		/**
		 * Set staff Role
		 *
		 * @since 1.0.0
		 * @param Array $data Data.
		 * @return Array
		 */
		public static function set_staff_role( $data ) {
			if ( ! mvr_check_is_array( $data ) || ! isset( $data['is_mvr_staff'] ) || false === $data['is_mvr_staff'] ) {
				return $data;
			}

			$data['role'] = 'mvr-staff';

			return $data;
		}
	}

	MVR_Register::init();
}
