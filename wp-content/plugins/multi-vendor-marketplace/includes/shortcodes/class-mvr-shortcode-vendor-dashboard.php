<?php
/**
 * Vendor Dashboard Shortcode
 *
 * Used on the Dashboard page, the dashboard shortcode displays the vendor dashboard content.
 *
 * @package Multi Vendor Marketplace\Shortcodes\Dashboard
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if directly accessed.
}


if ( ! class_exists( 'MVR_Shortcode_Vendor_Dashboard' ) ) {
	/**
	 * Shortcode -> Vendor Dashboard.
	 *
	 * @class MVR_Shortcode_Vendor_Dashboard
	 * @package Class
	 */
	class MVR_Shortcode_Vendor_Dashboard {

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
			self::dashboard( $atts );
		}

		/**
		 * Dashboard.
		 *
		 * @since 1.0.0
		 * @param Array $atts Shortcode attributes.
		 */
		public static function dashboard( $atts ) {
			$user_id = get_current_user_id();

			if ( empty( $user_id ) ) {
				$message = sprintf( '<a href="%s" tabindex="1" class="button wc-forward">%s</a> %s', esc_url( mvr_get_page_permalink( 'vendor_register' ) ), esc_html__( 'Register', 'multi-vendor-marketplace' ), esc_html__( 'Vendor Dashboard as a Guest', 'multi-vendor-marketplace' ) );
				wc_print_notice( $message, 'notice' );
			} else {
				$vendor_id = mvr_get_current_vendor_id();

				if ( empty( $vendor_id ) ) {
					$message = get_option( 'mvr_vendor_only_vendor_message', 'This dashboard is only for Vendors' );

					if ( mvr_user_eligible_for_register() ) {
						$wp_button_class = wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '';
						$message         = sprintf( '<a href="%s" tabindex="1" class="button wc-forward%s">%s</a> %s', esc_url( mvr_get_page_permalink( 'vendor_register' ) ), esc_attr( $wp_button_class ), esc_html__( 'Register as Vendor', 'multi-vendor-marketplace' ), esc_html( $message ) );
					}

					wc_print_notice( $message, 'notice' );
				} else {
					mvr_get_template(
						'dashboard.php',
						array(
							'current_user' => get_user_by( 'id', get_current_user_id() ),
						)
					);
				}
			}
		}
	}
}
