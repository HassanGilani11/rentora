<?php
/**
 * Transaction Manager.
 *
 * @package Multi-Vendor-Marketplace.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Transaction_Manager' ) ) {

	/**
	 * Manage Transaction activities.
	 *
	 * @class MVR_Transaction_Manager
	 * @package Class
	 */
	class MVR_Transaction_Manager {

		/**
		 * Init MVR_Transaction_Manager.
		 *
		 * @since 1.0.0
		 */
		public static function init() {
			add_action( 'mvr_new_withdraw', __CLASS__ . '::create_withdraw_transaction', 10, 2 );
			add_action( 'mvr_withdraw_status_success', __CLASS__ . '::update_withdraw_transaction' );
			add_action( 'mvr_withdraw_status_failed', __CLASS__ . '::update_withdraw_transaction' );
			add_action( 'mvr_withdraw_status_pending', __CLASS__ . '::update_withdraw_transaction' );
		}

		/**
		 * Create Withdraw Transaction.
		 *
		 * @since 1.0.0
		 * @param Integer      $withdraw_id Withdraw ID.
		 * @param MVR_Withdraw $withdraw_obj Withdraw Object.
		 */
		public static function create_withdraw_transaction( $withdraw_id, $withdraw_obj ) {
			$withdraw_obj = mvr_get_withdraw( $withdraw_id );

			if ( ! mvr_is_withdraw( $withdraw_obj ) || ! mvr_is_vendor( $withdraw_obj->get_vendor() ) ) {
				return;
			}

			// Create Transaction Object.
			$transaction_obj = new MVR_Transaction();
			$transaction_obj->set_props(
				array(
					'amount'      => $withdraw_obj->get_amount(),
					'vendor_id'   => $withdraw_obj->get_vendor_id(),
					'source_id'   => $withdraw_obj->get_id(),
					'source_from' => 'withdraw',
					'created_via' => 'admin',
					'currency'    => get_woocommerce_currency(),
					'type'        => 'debit',
				)
			);
			$transaction_obj->save();
			$transaction_obj->update_status( 'processing' );
		}

		/**
		 * Update Withdraw Transaction Status.
		 *
		 * @since 1.0.0
		 * @param MVR_Withdraw $withdraw_obj Withdraw Object.
		 */
		public static function update_withdraw_transaction( $withdraw_obj ) {
			if ( $withdraw_obj->has_transaction() ) {
				switch ( $withdraw_obj->get_status() ) {
					case 'success':
						$withdraw_obj->get_transaction()->update_status( 'completed' );
						break;
					case 'failed':
						$withdraw_obj->get_transaction()->update_status( 'failed' );
						break;
					default:
						$withdraw_obj->get_transaction()->update_status( 'processing' );
						break;
				}
			}
		}
	}

	MVR_Transaction_Manager::init();
}
