<?php
/**
 * Payout Manager.
 *
 * @package Multi-Vendor-Marketplace.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Payout_Manager' ) ) {

	/**
	 * Manage payout activities.
	 *
	 * @class MVR_Payout_Manager
	 * @package Class
	 */
	class MVR_Payout_Manager {

		/**
		 * Init MVR_Order_Manager.
		 *
		 * @since 1.0.0
		 */
		public static function init() {
			add_action( 'mvr_after_paypal_payout_batch_completed', __CLASS__ . '::create_paypal_payout' );
			add_action( 'mvr_after_bank_transfer_completed', __CLASS__ . '::create_bank_transfer_payout', 10, 3 );
		}

		/**
		 * Create PayPal Payout.
		 *
		 * @since 1.0.0
		 * @param Array $params Vendor Data.
		 */
		public static function create_paypal_payout( $params ) {
			if ( ! mvr_check_is_array( $params ) ) {
				return;
			}

			$batch_id         = isset( $params['batch_id'] ) ? $params['batch_id'] : '';
			$payout_batch_obj = isset( $params['payout_batch_obj'] ) ? $params['payout_batch_obj'] : '';
			$response         = isset( $params['response'] ) ? $params['response'] : '';
			$payouts_data     = isset( $params['payouts_data'] ) ? $params['payouts_data'] : '';

			if ( ! is_object( $response ) || ! mvr_check_is_array( $payouts_data ) ) {
				return;
			}

			if ( empty( $batch_id ) ) {
				$batch_id = $response->batch_header->payout_batch_id;

				if ( empty( $batch_id ) ) {
					return;
				}
			}

			$payout_items = array();
			$details      = MVR_PayPal_Payouts_Helper::get_payout_batch_details( $batch_id );

			if ( ! is_object( $details ) ) {
				return;
			}

			foreach ( $details->items as $key => $item_obj ) {
				if ( ! is_object( $item_obj ) ) {
					continue;
				}

				$vendor_id  = $item_obj->payout_item->sender_item_id;
				$vendor_obj = mvr_get_vendor( $vendor_id );

				if ( ! mvr_is_vendor( $vendor_obj ) ) {
					continue;
				}

				$source_id  = $payouts_data[ $vendor_id ]['source_id'];
				$payout_obj = new MVR_Payout();

				if ( ! empty( $source_id ) ) {
					$payouts_obj = mvr_get_payouts(
						array(
							'source_id'   => $source_id,
							'source_from' => 'withdraw',
							'limit'       => '1',
						)
					);

					if ( $payouts_obj->has_payout ) {
						$payout_obj = current( $payouts_obj->payouts );
					}
				}

				$amount = $item_obj->payout_item->amount;
				$payout_obj->set_props(
					array(
						'batch_id'       => $batch_id,
						'batch_log_id'   => $payout_batch_obj->get_id(),
						'vendor_id'      => $vendor_id,
						'user_id'        => $vendor_obj->get_user_id(),
						'email'          => $vendor_obj->get_paypal_email(),
						'amount'         => $amount->value,
						'currency'       => $amount->currency,
						'payment_method' => '2',
						'created_via'    => 'auto',
						'source_id'      => $payouts_data[ $vendor_id ]['source_id'],
						'source_from'    => 'withdraw',
						'schedule'       => $payouts_data[ $vendor_id ]['schedule'],
					)
				);
				$payout_obj->save();
				$payout_obj->update_status( 'paid' );

				if ( $payouts_data[ $vendor_id ]['source_id'] && 'withdraw' === $payouts_data[ $vendor_id ]['source_from'] ) {
					$withdraw_obj = mvr_get_withdraw( $payouts_data[ $vendor_id ]['source_id'] );

					if ( mvr_is_withdraw( $withdraw_obj ) ) {
						$withdraw_obj->update_status( 'success' );
					}
				}

				$payout_items[ $item_obj->payout_item->receiver ] = array(
					'payout_item_id'     => $item_obj->payout_item_id,
					'sender_item_id'     => $item_obj->payout_item->sender_item_id,
					'transaction_status' => $item_obj->transaction_status,
					'receiver'           => $item_obj->payout_item->receiver,
					'fee'                => array(
						'currency' => $item_obj->payout_item_fee->currency,
						'value'    => $item_obj->payout_item_fee->value,
					),
					'amount'             => array(
						'currency' => $item_obj->payout_item->amount->currency,
						'value'    => $item_obj->payout_item->amount->value,
					),
					'note'               => $item_obj->payout_item->note,
				);
			}

			if ( $details->batch_header->payout_batch_id ) {
				$payout_batch_obj->set_props(
					array(
						'batch_id'       => $details->batch_header->payout_batch_id,
						'batch_status'   => $details->batch_header->batch_status,
						'time_created'   => $details->batch_header->time_created,
						'time_completed' => isset( $details->batch_header->time_completed ) ? $details->batch_header->time_completed : '',
						'batch_amount'   => array(
							'currency' => $details->batch_header->amount->currency,
							'value'    => $details->batch_header->amount->value,
						),
						'batch_fee'      => array(
							'currency' => $details->batch_header->fees->currency,
							'value'    => $details->batch_header->fees->value,
						),
						'email_subject'  => $details->batch_header->sender_batch_header->email_subject,
						'items'          => $payout_items,
					)
				);

				$payout_batch_obj->update_status( 'paid' );
				$payout_batch_obj->add_note( __( 'Successfully retrieved Payout data from PayPal.', 'multi-vendor-marketplace' ) );
			}
		}

		/**
		 * Create Bank Transfer Payout.
		 *
		 * @since 1.0.0
		 * @param MVR_Withdraw $withdraw_obj Withdraw Object.
		 * @param MVR_Vendor   $vendor_obj Vendor Object.
		 * @param Integer      $schedule Schedule.
		 */
		public static function create_bank_transfer_payout( $withdraw_obj, $vendor_obj, $schedule ) {
			// Payout.
			$payout_obj = new MVR_Payout();
			$payout_obj->set_props(
				array(
					'vendor_id'      => $vendor_obj->get_id(),
					'user_id'        => $vendor_obj->get_user_id(),
					'email'          => $vendor_obj->get_email(),
					'amount'         => $withdraw_obj->get_amount(),
					'currency'       => $withdraw_obj->get_currency(),
					'payment_method' => '1',
					'created_via'    => 'auto',
					'source_id'      => $withdraw_obj->get_id(),
					'source_from'    => 'withdraw',
					'schedule'       => $schedule,
				)
			);
			$payout_obj->save();
			$payout_obj->update_status( 'paid' );
		}
	}

	MVR_Payout_Manager::init();
}
