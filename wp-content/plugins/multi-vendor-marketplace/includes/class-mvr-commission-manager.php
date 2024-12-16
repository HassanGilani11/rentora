<?php
/**
 * Commission Manager.
 *
 * @package Multi-Vendor-Marketplace.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Commission_Manager' ) ) {

	/**
	 * Manage commission activities.
	 *
	 * @class MVR_Commission_Manager
	 * @package Class
	 */
	class MVR_Commission_Manager {

		/**
		 * Init MVR_Commission_Manager.
		 *
		 * @since 1.0.0
		 */
		public static function init() {
			add_action( 'mvr_new_withdraw', __CLASS__ . '::create_withdraw_commission', 10, 2 );
			add_action( 'mvr_withdraw_status_success', __CLASS__ . '::update_commission_status' );
			add_action( 'mvr_withdraw_status_failed', __CLASS__ . '::update_commission_status' );
			add_action( 'mvr_withdraw_status_pending', __CLASS__ . '::update_commission_status' );
		}

		/**
		 * Create Withdraw Transaction.
		 *
		 * @since 1.0.0
		 * @param Integer      $withdraw_id Withdraw ID.
		 * @param MVR_Withdraw $withdraw_obj Withdraw Object.
		 */
		public static function create_withdraw_commission( $withdraw_id, $withdraw_obj ) {
			$withdraw_obj = mvr_get_withdraw( $withdraw_id );

			if ( ! mvr_is_withdraw( $withdraw_obj ) || ! mvr_is_vendor( $withdraw_obj->get_vendor() ) ) {
				return;
			}

			if ( $withdraw_obj->get_charge_amount() > 0 ) {
				// Create Commission Object.
				$commission_obj = new MVR_Commission();
				$commission_obj->set_props(
					array(
						'amount'      => $withdraw_obj->get_charge_amount(),
						'vendor_id'   => $withdraw_obj->get_vendor_id(),
						'source_id'   => $withdraw_obj->get_id(),
						'source_from' => 'withdraw',
						'settings'    => $withdraw_obj->get_vendor()->get_withdraw_settings(),
						'created_via' => 'admin',
						'currency'    => get_woocommerce_currency(),
						'status'      => 'pending',
					)
				);
				$commission_obj->save();
			}
		}

		/**
		 * Update Commission Status
		 *
		 * @since 1.0.0
		 * @param MVR_Withdraw $withdraw_obj Withdraw Object.
		 */
		public static function update_commission_status( $withdraw_obj ) {
			if ( ! mvr_is_withdraw( $withdraw_obj ) ) {
				return;
			}

			if ( $withdraw_obj->has_commission() ) {
				switch ( $withdraw_obj->get_status() ) {
					case 'success':
						$withdraw_obj->get_commission()->update_status( 'paid' );
						break;
					case 'failed':
						$withdraw_obj->get_commission()->update_status( 'failed' );
						break;
					default:
						$withdraw_obj->get_commission()->update_status( 'pending' );
						break;
				}
			}
		}
	}

	MVR_Commission_Manager::init();
}
