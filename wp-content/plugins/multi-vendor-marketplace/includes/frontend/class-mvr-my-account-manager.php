<?php
/**
 * My Account Manager.
 *
 * @package Multi-Vendor Functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_My_Account_Manager' ) ) {


	/**
	 * Manage my account.
	 *
	 * @class MVR_My_Account_Manager
	 * @package Class
	 */
	class MVR_My_Account_Manager {

		/**
		 * Init MVR_My_Account_Manager.
		 *
		 * @since 1.0.0
		 */
		public static function init() {
			add_action( 'woocommerce_after_my_account', __CLASS__ . '::become_vendor_link' );
		}

		/**
		 * Create Vendor Link.
		 *
		 * @since 1.0.0
		 */
		public static function become_vendor_link() {
			$user_id = get_current_user_id();

			if ( mvr_check_user_as_vendor_or_staff( $user_id ) ) {
				mvr_get_template( 'dashboard-link.php' );
			} else {
				if ( ! mvr_user_eligible_for_register( $user_id ) ) {
					return;
				}

				$store_url   = mvr_get_store_url( '{slug}' );
				$nonce_value = isset( $_POST['_mvr_nonce'] ) ? sanitize_key( wp_unslash( $_POST['_mvr_nonce'] ) ) : '';
				$form_fields = array(
					'_name'      => '',
					'_shop_name' => '',
					'_slug'      => '',
				);

				if ( wp_verify_nonce( $nonce_value, 'mvr_become_vendor' ) ) {
					$form_fields['_name']      = isset( $_POST['_name'] ) ? sanitize_text_field( wp_unslash( $_POST['_name'] ) ) : '';
					$form_fields['_shop_name'] = isset( $_POST['_shop_name'] ) ? sanitize_text_field( wp_unslash( $_POST['_shop_name'] ) ) : '';
					$form_fields['_slug']      = isset( $_POST['_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['_slug'] ) ) : '';
				}

				mvr_get_template(
					'form-user-vendor-register.php',
					array(
						'store_url'   => $store_url,
						/**
						* Register Form Field Value.
						*
						* @since 1.0.0
						* */
						'form_fields' => apply_filters( 'mvr_user_vendor_register_form_fields_value', $form_fields ),
					)
				);
			}
		}
	}

	MVR_My_Account_Manager::init();
}
