<?php
/**
 * Payment Section
 *
 * @package Multi Vendor Marketplace/Setting Tab/Payment Section
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Admin_Settings_Payment' ) ) {
	/**
	 * Payment Tab.
	 *
	 * @class MVR_Admin_Settings_Payment
	 * @package Class
	 */
	class MVR_Admin_Settings_Payment extends MVR_Abstract_Settings {
		/**
		 * MVR_Admin_Settings_Payment constructor.
		 */
		public function __construct() {
			$this->id            = 'payment';
			$this->label         = __( 'Payment', 'multi-vendor-marketplace' );
			$this->custom_fields = array();
			$this->settings      = $this->get_settings();
			$this->init();

			add_action( 'mvr_settings_after_setting_buttons_payment', array( $this, 'get_payout_batch_table' ) );
		}

		/**
		 * Get settings array.
		 *
		 * @since 1.0.0
		 * @return Array
		 */
		public function get_settings() {
			/**
			 * Payment Settings Fields.
			 *
			 * @since 1.0.0
			 */
			return apply_filters(
				'mvr_get_' . $this->id . '_settings',
				array(
					array(
						'type'  => 'title',
						'title' => esc_html__( 'Payment Settings', 'multi-vendor-marketplace' ),
						'id'    => 'mvr_payment_options',
					),
					array(
						'title'   => esc_html__( 'Enable PayPal Payout', 'multi-vendor-marketplace' ),
						'type'    => 'checkbox',
						'id'      => $this->get_option_key( 'enable_paypal_payouts' ),
						'class'   => 'mvr-enable-paypal-payout',
						'default' => 'no',
					),
					array(
						'title'   => esc_html__( 'Sandbox Mode', 'multi-vendor-marketplace' ),
						'type'    => 'checkbox',
						'default' => 'no',
						'id'      => $this->get_option_key( 'paypal_payout_sandbox_mode' ),
						'class'   => 'mvr-paypal-payout-mode mvr-paypal-payout-field',
					),
					array(
						'title'   => esc_html__( 'Client ID', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'id'      => $this->get_option_key( 'paypal_payouts_live_client_id' ),
						'class'   => 'mvr-paypal-payout-live-field mvr-paypal-payout-field',
						'default' => '',
					),
					array(
						'title'   => esc_html__( 'Secret Key', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'id'      => $this->get_option_key( 'paypal_payouts_live_client_secret_key' ),
						'class'   => 'mvr-paypal-payout-live-field mvr-paypal-payout-field',
						'default' => '',
					),
					array(
						'title'   => esc_html__( 'Sandbox Client ID', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'id'      => $this->get_option_key( 'paypal_payouts_sandbox_client_id' ),
						'class'   => 'mvr-paypal-payout-sand-field mvr-paypal-payout-field',
						'default' => '',
					),
					array(
						'title'   => esc_html__( 'Sandbox Secret Key', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'id'      => $this->get_option_key( 'paypal_payouts_sandbox_client_secret_key' ),
						'class'   => 'mvr-paypal-payout-sand-field mvr-paypal-payout-field',
						'default' => '',
					),
					array(
						'type' => 'sectionend',
						'id'   => 'mvr_payment_options',
					),
				)
			);
		}

		/**
		 * Batch Payout Table
		 *
		 * @since 1.0.0
		 */
		public function get_payout_batch_table() {
			MVR_Admin_Payout_Batch::output();
		}
	}

	return new MVR_Admin_Settings_Payment();
}
