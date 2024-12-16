<?php
/**
 * PayPal Payouts Helper.
 *
 * @package Multi-Vendor for Woocommerce
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MVR_PayPal_Payouts_API' ) ) {
	include_once MVR_ABSPATH . 'includes/payouts/class-mvr-paypal-payouts-api.php';
}

if ( ! class_exists( 'MVR_PayPal_Payouts_Helper' ) ) {
	/**
	 * PayPal Payouts Helper Methods.
	 *
	 * @class MVR_PayPal_Payouts_Helper
	 */
	class MVR_PayPal_Payouts_Helper extends MVR_PayPal_Payouts_API {

		/**
		 * Creates a batch payout.
		 *
		 * @since 1.0.0
		 * @param Array $request Request.
		 */
		public static function create_batch_payout( $request ) {
			self::prepare_environment();

			return self::request( $request, '/v1/payments/payouts?sync_mode=false' );
		}

		/**
		 * Shows the latest status of a batch payout. Includes the transaction status and other data for individual payout items.
		 *
		 * @since 1.0.0
		 * @param String $payout_batch_id Payout Batch ID.
		 */
		public static function get_payout_batch_details( $payout_batch_id ) {
			self::prepare_environment();

			return self::retrieve( "/v1/payments/payouts/{$payout_batch_id}" );
		}

		/**
		 * Shows details for a payout item, by ID.
		 *
		 * @since 1.0.0
		 * @param String $payout_item_id Payout Item ID.
		 */
		public static function get_payout_item_details( $payout_item_id ) {
			self::prepare_environment();

			return self::retrieve( "/v1/payments/payouts-item/{$payout_item_id}" );
		}
	}
}
