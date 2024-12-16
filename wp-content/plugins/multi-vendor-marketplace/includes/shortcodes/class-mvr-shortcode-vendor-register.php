<?php
/**
 * Vendor Dashboard Shortcode
 *
 * Used on the Dashboard page, the dashboard shortcode displays the vendor dashboard content.
 *
 * @package Multi Vendor Marketplace\Shortcodes\register
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if directly accessed.
}


if ( ! class_exists( 'MVR_Shortcode_Vendor_Register' ) ) {
	/**
	 * Shortcode -> Vendor Register.
	 *
	 * @class MVR_Shortcode_Vendor_Register
	 * @package Class
	 */
	class MVR_Shortcode_Vendor_Register {

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
			self::register( $atts );
		}

		/**
		 * Register.
		 *
		 * @since 1.0.0
		 * @param Array $atts Shortcode attributes.
		 */
		public static function register( $atts ) {
			if ( 'yes' !== get_option( 'mvr_settings_allow_guest_vendor_reg', 'no' ) ) {
				wc_print_notice( get_option( 'mvr_vendor_guest_not_eligible_message', 'Your are not allowed to access this page.' ), 'notice' );
				return false;
			}

			$user_id = get_current_user_id();

			if ( ! empty( $user_id ) && ! mvr_user_is_vendor( $user_id ) ) {
				if ( ! mvr_user_eligible_for_register( $user_id ) ) {
					wc_print_notice( get_option( 'mvr_vendor_user_not_eligible_message', 'Your are not eligible to register as a vendor', 'multi-vendor-marketplace' ), 'notice' );
					return false;
				}
			}

			$store_url   = mvr_get_store_url( '{slug}' );
			$nonce_value = isset( $_POST['_mvr_nonce'] ) ? sanitize_key( wp_unslash( $_POST['_mvr_nonce'] ) ) : '';
			$form_fields = array(
				'_email'     => '',
				'_name'      => '',
				'_shop_name' => '',
				'_slug'      => '',
			);

			if ( wp_verify_nonce( $nonce_value, 'mvr_vendor_register' ) ) {
				$form_fields['_email']     = isset( $_POST['_email'] ) ? sanitize_text_field( wp_unslash( $_POST['_email'] ) ) : '';
				$form_fields['_name']      = isset( $_POST['_name'] ) ? sanitize_text_field( wp_unslash( $_POST['_name'] ) ) : '';
				$form_fields['_shop_name'] = isset( $_POST['_shop_name'] ) ? sanitize_text_field( wp_unslash( $_POST['_shop_name'] ) ) : '';
				$form_fields['_slug']      = isset( $_POST['_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['_slug'] ) ) : '';
			}

			mvr_get_template(
				'form-vendor-register.php',
				array(
					'user_id'     => $user_id,
					'store_url'   => $store_url,
					/**
					 * Register Form Field Value.
					 *
					 * @since 1.0.0
					 * */
					'form_fields' => apply_filters( 'mvr_vendor_register_form_fields_value', $form_fields ),
				)
			);
		}
	}
}
