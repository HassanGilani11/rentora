<?php
/**
 * Action Scheduler for Multi Vendor
 *
 * @package Action Scheduler
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MVR_Action_Scheduler' ) ) {
	/**
	 * Scheduler for user payment events that uses the Action Scheduler
	 *
	 * @class MVR_Action_Scheduler
	 * @package Class
	 */
	class MVR_Action_Scheduler {

		/**
		 * An internal cache of action hooks and corresponding date types.
		 *
		 * @var array An array of $action_hook => $date_type values
		 */
		protected static $action_hooks = array();

		/**
		 * Init Function
		 *
		 * @since 1.0.0
		 */
		public static function init() {
			add_action( 'init', __CLASS__ . '::prepare_queue_events' );

			// Auto Payout.
			add_action( 'mvr_automatic_withdrawal_daily', __CLASS__ . '::automatic_withdrawal_daily' );
			add_action( 'mvr_automatic_withdrawal_weekly', __CLASS__ . '::automatic_withdrawal_weekly' );
			add_action( 'mvr_automatic_withdrawal_biweekly', __CLASS__ . '::automatic_withdrawal_biweekly' );
			add_action( 'mvr_automatic_withdrawal_monthly', __CLASS__ . '::automatic_withdrawal_monthly' );
			add_action( 'mvr_automatic_withdrawal_quarterly', __CLASS__ . '::automatic_withdrawal_quarterly' );

			// Deep Clean.
			add_action( 'mvr_deep_clean_scheduler', __CLASS__ . '::deep_clean_scheduler' );

			// Vendor Update.
			add_action( 'mvr_vendor_update_scheduler', __CLASS__ . '::vendor_update_scheduler' );
		}

		/**
		 * Queue Setup
		 *
		 * @since 1.0.0
		 * */
		public static function prepare_queue_events() {
			if ( ! mvr_is_plugin_active() ) {
				return;
			}

			self::automatic_withdraw_queue();
			self::deep_clean_queue();
			self::update_vendor_amounts();
		}

		/**
		 * Automatic withdraw Queue.
		 *
		 * @since 1.0.0
		 * */
		public static function automatic_withdraw_queue() {
			$auto_withdrawal = get_option( 'mvr_settings_enable_automatic_withdraw', 'no' );

			$withdrawal_hooks = array(
				'daily'     => 'mvr_automatic_withdrawal_daily',
				'weekly'    => 'mvr_automatic_withdrawal_weekly',
				'biweekly'  => 'mvr_automatic_withdrawal_biweekly',
				'monthly'   => 'mvr_automatic_withdrawal_monthly',
				'quarterly' => 'mvr_automatic_withdrawal_quarterly',
			);

			if ( 'yes' !== $auto_withdrawal ) {
				foreach ( $withdrawal_hooks as $key => $hook ) {
					$next = WC()->queue()->get_next( $hook );

					if ( $next ) {
						WC()->queue()->cancel_all( $hook );
					}
				}
			} else {
				foreach ( $withdrawal_hooks as $key => $hook ) {
					$enable = get_option( 'mvr_settings_enable_auto_withdraw_' . $key, 'no' );

					if ( 'yes' !== $enable ) {
						$next = WC()->queue()->get_next( $hook );

						if ( $next ) {
							WC()->queue()->cancel_all( $hook );
						}
					} else {
						switch ( $key ) {
							case 'weekly':
								$interval   = 7;
								$start_from = mvr_get_weekly_schedule();
								break;
							case 'biweekly':
								$interval   = 14;
								$start_from = mvr_get_biweekly_schedule();
								break;
							case 'monthly':
								$interval   = 30;
								$start_from = mvr_get_month_schedule();
								break;
							case 'quarterly':
								$interval   = 90;
								$start_from = mvr_get_quarter_month_schedule();
								break;
							default:
								$interval   = 1;
								$start_from = time();
								break;
						}

						$next = WC()->queue()->get_next( $hook );

						if ( ! $next ) {
							WC()->queue()->cancel_all( $hook );
							WC()->queue()->schedule_recurring( $start_from, ( $interval * DAY_IN_SECONDS ), $hook, array(), 'MVR' );
						}
					}
				}
			}
		}

		/**
		 * Automatic withdraw Queue.
		 *
		 * @since 1.0.0
		 * */
		public static function deep_clean_queue() {
			$enable_deep_clean = get_option( 'mvr_settings_allow_multi_vendor_product', 'no' );

			if ( 'yes' !== $enable_deep_clean ) {
				$next = WC()->queue()->get_next( 'mvr_deep_clean_scheduler' );

				if ( $next ) {
					WC()->queue()->cancel_all( 'mvr_deep_clean_scheduler' );
				}
			} else {
				$next = WC()->queue()->get_next( 'mvr_deep_clean_scheduler' );

				if ( ! $next ) {
					WC()->queue()->cancel_all( 'mvr_deep_clean_scheduler' );
					WC()->queue()->schedule_recurring( time(), DAY_IN_SECONDS, 'mvr_deep_clean_scheduler', array(), 'MVR' );
				}
			}
		}

		/**
		 * Update Vendor Amount
		 *
		 * @since 1.0.0
		 * */
		public static function update_vendor_amounts() {
			$next = WC()->queue()->get_next( 'mvr_vendor_update_scheduler' );

			if ( ! $next ) {
				WC()->queue()->cancel_all( 'mvr_vendor_update_scheduler' );
				WC()->queue()->schedule_recurring( time(), HOUR_IN_SECONDS, 'mvr_vendor_update_scheduler', array(), 'MVR' );
			}
		}

		/**
		 * Automatic withdrawal Daily.
		 *
		 * @since 1.0.0
		 * */
		public static function automatic_withdrawal_daily() {
			foreach ( mvr_payment_method_options() as $key => $value ) {
				$vendors_obj = mvr_get_vendors(
					array(
						'status'          => 'mvr-active',
						'amount'          => (float) get_option( 'mvr_settings_min_withdraw_threshold', 0 ),
						'payout_type'     => '2',
						'payout_schedule' => '1',
						'payment_method'  => $key,
					)
				);

				self::process_vendors_payment( $vendors_obj, $key, '5' );
			}
		}

		/**
		 * Automatic withdrawal Weekly.
		 *
		 * @since 1.0.0
		 * */
		public static function automatic_withdrawal_weekly() {
			foreach ( mvr_payment_method_options() as $key => $value ) {
				$vendors_obj = mvr_get_vendors(
					array(
						'status'          => 'mvr-active',
						'amount'          => (float) get_option( 'mvr_settings_min_withdraw_threshold', 0 ),
						'payout_type'     => '2',
						'payout_schedule' => '2',
						'payment_method'  => $key,
					)
				);

				self::process_vendors_payment( $vendors_obj, $key, '5' );
			}
		}

		/**
		 * Automatic withdrawal Biweekly.
		 *
		 * @since 1.0.0
		 * */
		public static function automatic_withdrawal_biweekly() {
			foreach ( mvr_payment_method_options() as $key => $value ) {
				$vendors_obj = mvr_get_vendors(
					array(
						'status'          => 'mvr-active',
						'amount'          => (float) get_option( 'mvr_settings_min_withdraw_threshold', 0 ),
						'payout_type'     => '2',
						'payout_schedule' => '3',
						'payment_method'  => $key,
					)
				);

				self::process_vendors_payment( $vendors_obj, $key, '5' );
			}
		}

		/**
		 * Automatic withdrawal Monthly.
		 *
		 * @since 1.0.0
		 * */
		public static function automatic_withdrawal_monthly() {
			foreach ( mvr_payment_method_options() as $key => $value ) {
				$vendors_obj = mvr_get_vendors(
					array(
						'status'          => 'mvr-active',
						'amount'          => (float) get_option( 'mvr_settings_min_withdraw_threshold', 0 ),
						'payout_type'     => '2',
						'payout_schedule' => '4',
						'payment_method'  => $key,
					)
				);

				self::process_vendors_payment( $vendors_obj, $key, '5' );
			}
		}

		/**
		 * Automatic withdrawal Quarterly.
		 *
		 * @since 1.0.0
		 * */
		public static function automatic_withdrawal_quarterly() {
			foreach ( mvr_payment_method_options() as $key => $value ) {
				$vendors_obj = mvr_get_vendors(
					array(
						'status'          => 'mvr-active',
						'amount'          => (float) get_option( 'mvr_settings_min_withdraw_threshold', 0 ),
						'payout_type'     => '2',
						'payout_schedule' => '5',
						'payment_method'  => $key,
					)
				);

				self::process_vendors_payment( $vendors_obj, $key, '5' );
			}
		}

		/**
		 * Vendors PayPal Payment.
		 *
		 * @since 1.0.0
		 * @param Object $vendors_obj Object.
		 * @param String $payment_method payment method.
		 * @param String $schedule_type Schedule type.
		 * */
		public static function process_vendors_payment( $vendors_obj, $payment_method, $schedule_type ) {
			if ( ! is_object( $vendors_obj ) || ! $vendors_obj->has_vendor ) {
				return array();
			}

			$items               = array();
			$vendors_arg         = array();
			$payout_withdraw_ids = array();

			foreach ( $vendors_obj->vendors as $vendor_obj ) {
				if ( ! mvr_is_vendor( $vendor_obj ) ) {
					continue;
				}

				$minimum_withdraw = (float) get_option( 'mvr_settings_min_withdraw_threshold', 0 );

				if ( $vendor_obj->get_amount() <= 0 || $vendor_obj->get_amount() < $minimum_withdraw ) {
					continue;
				}

				$withdraw_charge = $vendor_obj->calculate_withdraw_charge( $vendor_obj->get_amount() );
				$withdraw_amount = $vendor_obj->get_amount() - $withdraw_charge;

				if ( $withdraw_amount <= 0 ) {
					continue;
				}

				// Create Withdraw Object.
				$withdraw_obj = new MVR_Withdraw();
				$withdraw_obj->set_props(
					array(
						'amount'         => $withdraw_amount,
						'vendor_id'      => $vendor_obj->get_id(),
						'payment_method' => $vendor_obj->get_payment_method(),
						'charge_amount'  => $withdraw_charge,
						'created_via'    => 'admin',
						'source_id'      => $schedule_type,
						'source_from'    => 'auto_withdraw',
						'currency'       => get_woocommerce_currency(),
					)
				);
				$withdraw_obj->save();
				$withdraw_obj->update_status( 'pending' );

				$vendor_obj->set_amount( 0 );
				$vendor_obj->save();

				if ( '1' == $payment_method ) {
					continue;
				}

				$receiver_email = sanitize_email( $vendor_obj->get_paypal_email() );

				if ( empty( $receiver_email ) || 'yes' !== get_option( 'mvr_settings_enable_paypal_payouts', 'no' ) ) {
					continue;
				}

				$charge_amount                        = $vendor_obj->calculate_withdraw_charge( $vendor_obj->get_amount() );
				$items[]                              = array(
					'recipient_type' => 'EMAIL',
					'receiver'       => $receiver_email,
					'note'           => __( 'Payout received.', 'multi-vendor-marketplace' ),
					'sender_item_id' => $vendor_obj->get_id(),
					'amount'         => array(
						'value'    => wc_format_decimal( $withdraw_amount, wc_get_price_decimals() ),
						'currency' => get_woocommerce_currency(),
					),
				);
				$vendors_arg[ $vendor_obj->get_id() ] = array(
					'vendor_id'   => $vendor_obj->get_id(),
					'amount'      => $withdraw_amount,
					'charge'      => $charge_amount,
					'schedule'    => $schedule_type,
					'source_id'   => $withdraw_obj->get_id(),
					'source_from' => 'withdraw',
					'receiver'    => $receiver_email,
				);
				$payout_withdraw_ids[]                = $withdraw_obj->get_id();
			}

			if ( ! mvr_check_is_array( $items ) ) {
				return false;
			}

			$payout_batch_obj = new MVR_Payout_Batch();
			$payout_batch_obj->set_props(
				array(
					'items'           => $items,
					'email_subject'   => __( 'Payout Received Successful', 'multi-vendor-marketplace' ),
					'email_message'   => __( 'You have received a payout! Thanks!!', 'multi-vendor-marketplace' ),
					'additional_data' => $vendors_arg,
				)
			);
			$payout_batch_obj->save();
			$payout_batch_obj->add_note( __( 'New Payout entry created.', 'multi-vendor-marketplace' ) );

			if ( mvr_is_payout_batch( $payout_batch_obj ) ) {
				foreach ( $payout_withdraw_ids as $withdraw_id ) {
					$withdraw_obj = mvr_get_withdraw( $withdraw_id );

					if ( mvr_is_withdraw( $withdraw_obj ) ) {
						$withdraw_obj->update_status( 'progress' );
					}
				}

				$response = MVR_PayPal_Payouts_Helper::create_batch_payout(
					array(
						'sender_batch_header' => array(
							'sender_batch_id' => $payout_batch_obj->get_id(),
							'email_subject'   => $payout_batch_obj->get_email_subject(),
							'email_message'   => $payout_batch_obj->get_email_message(),
						),
						'items'               => $payout_batch_obj->get_items(),
					)
				);

				if ( is_wp_error( $response ) ) {
					$payout_batch_obj->add_note( $response->get_error_message() );
				} elseif ( is_object( $response ) ) {
					$batch_id = $response->batch_header->payout_batch_id;

					if ( ! empty( $batch_id ) ) {
						$param = array(
							'batch_id'         => $batch_id,
							'payout_batch_obj' => $payout_batch_obj,
							'response'         => $response,
							'payouts_data'     => $vendors_arg,
						);

						/**
						 * PayPal Batch Payout Completed.
						 *
						 * @since 1.0.0
						 * */
						do_action( 'mvr_after_paypal_payout_batch_completed', $param );
					}
				}
			}
		}

		/**
		 * Deep Clean Scheduler
		 *
		 * @since 1.0.0
		 * */
		public static function deep_clean_scheduler() {
			self::clear_invalid_transactions();
			self::clear_invalid_withdraws();
		}

		/**
		 * Vendor Update
		 *
		 * @since 1.0.0
		 * */
		public static function vendor_update_scheduler() {
			$vendors_obj = mvr_get_vendors( array( 'status' => 'active' ) );

			if ( ! $vendors_obj->has_vendor ) {
				return;
			}

			foreach ( $vendors_obj->vendors as $vendor_obj ) {
				if ( mvr_is_vendor( $vendor_obj ) ) {
					$vendor_obj->update_order_amount();
				}
			}
		}

		/**
		 * Clear Invalid Transactions
		 *
		 * @since 1.0.0
		 * */
		public static function clear_invalid_transactions() {
			$vendor_ids = mvr_get_vendors( array( 'fields' => 'id' ) );

			if ( ! $vendor_ids->has_vendor ) {
				return;
			}

			global $wpdb;
			$wpdb_ref = &$wpdb;

			$transaction_ids = $wpdb_ref->get_col(
				$wpdb_ref->prepare(
					"
						SELECT DISTINCT ID FROM {$wpdb_ref->posts} AS p
						INNER JOIN {$wpdb_ref->postmeta} AS pm ON (p.ID = pm.post_id)
						WHERE 1=1 AND post_type = '%s' 
						AND post_parent NOT IN ('" . implode( "','", $vendor_ids->vendors ) . "') 
					",
					'mvr_transaction'
				)
			);

			if ( ! $transaction_ids->has_transaction ) {
				return;
			}

			foreach ( $transaction_ids as $transaction_id ) {
				$transaction_obj = mvr_get_transaction( $transaction_id );

				if ( mvr_is_transaction( $transaction_obj ) ) {
					$transaction_obj->delete( array( 'force_delete' => true ) );
				} else {
					wp_delete_post( $transaction_id, true );
				}
			}
		}

		/**
		 * Clear Invalid Withdraws
		 *
		 * @since 1.0.0
		 * */
		public static function clear_invalid_withdraws() {
			$vendor_ids = mvr_get_vendors( array( 'fields' => 'id' ) );

			if ( ! $vendor_ids->has_vendor ) {
				return;
			}

			global $wpdb;
			$wpdb_ref = &$wpdb;

			$withdraw_ids = $wpdb_ref->get_col(
				$wpdb_ref->prepare(
					"
						SELECT DISTINCT ID FROM {$wpdb_ref->posts} AS p
						INNER JOIN {$wpdb_ref->postmeta} AS pm ON (p.ID = pm.post_id)
						WHERE 1=1 AND post_type = '%s' 
						AND post_parent NOT IN ('" . implode( "','", $vendor_ids->vendors ) . "') 
					",
					'mvr_withdraw'
				)
			);

			if ( ! $withdraw_ids->has_withdraw ) {
				return;
			}

			foreach ( $withdraw_ids as $withdraw_id ) {
				$withdraw_obj = mvr_get_withdraw( $withdraw_id );

				if ( mvr_is_withdraw( $withdraw_obj ) ) {
					$withdraw_obj->delete( array( 'force_delete' => true ) );
				} else {
					wp_delete_post( $withdraw_id, true );
				}
			}
		}
	}

	MVR_Action_Scheduler::init();
}
