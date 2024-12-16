<?php
/**
 * Vendor Login Shortcode
 *
 * Used on the Login page, the dashboard shortcode displays the vendor Login content.
 *
 * @package Multi Vendor Marketplace\Shortcodes\login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if directly accessed.
}


if ( ! class_exists( 'MVR_Shortcode_Vendor_Login' ) ) {
	/**
	 * Shortcode -> Vendor Login.
	 *
	 * @class MVR_Shortcode_Vendor_Login
	 * @package Class
	 */
	class MVR_Shortcode_Vendor_Login {

		/**
		 * Get the shortcode content.
		 *
		 * @param array $atts Shortcode attributes.
		 *
		 * @return string
		 */
		public static function get( $atts ) {
			return MVR_Shortcodes::shortcode_wrapper( array( __CLASS__, 'output' ), $atts );
		}

		/**
		 * Output the shortcode.
		 *
		 * @since 1.0.0
		 * @param Array $atts Shortcode attributes.
		 */
		public static function output( $atts ) {
			// Output the new account page.
			self::login( $atts );
		}

		/**
		 * Login.
		 *
		 * @since 1.0.0
		 * @param Array $atts Shortcode attributes.
		 */
		public static function login( $atts ) {
			$form_fields = array(
				'username' => '',
				'password' => '',
			);
			$nonce_value = isset( $_POST['woocommerce-login-nonce'] ) ? sanitize_key( wp_unslash( $_POST['woocommerce-login-nonce'] ) ) : '';

			if ( wp_verify_nonce( $nonce_value, 'woocommerce-login' ) ) {
				$posted      = $_POST;
				$form_fields = array(
					'username' => isset( $posted['username'] ) ? wp_unslash( $posted['username'] ) : '',
					'password' => isset( $posted['password'] ) ? wp_unslash( $posted['password'] ) : '',
				);
			}

			$args = array(
				'redirect'    => mvr_get_page_permalink( 'dashboard' ),
				'form_fields' => $form_fields,
			);

			mvr_get_template( 'form-vendor-login.php', $args );
		}
	}
}
