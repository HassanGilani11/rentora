<?php
/**
 * Handles Withdraw Generate Payout.
 *
 * @package Multi-Vendor\Generate Payout
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'MVR_Withdraw_Generate_Payout' ) ) {
	/**
	 * MVR_Withdraw_Generate_Payout Class.
	 */
	class MVR_Withdraw_Generate_Payout {

		/**
		 * Page being payout
		 *
		 * @var integer
		 */
		protected $page = 1;

		/**
		 * Batch limit.
		 *
		 * @var integer
		 */
		protected $limit = 50;

		/**
		 * Number exported.
		 *
		 * @var integer
		 */
		protected $payed_count = 0;

		/**
		 * Raw data to export.
		 *
		 * @var array
		 */
		protected $withdraw_payout_data = array();

		/**
		 * Total Payouts.
		 *
		 * @var integer
		 */
		protected $total_withdraw_payouts = 0;

		/**
		 * Which payment types are being exported.
		 *
		 * @var Array
		 */
		protected $payment_type_to_payout = array();

		/**
		 * Withdraws to payout.
		 *
		 * @var Array
		 */
		protected $vendors_to_payout = array();

		/**
		 * Exclude Vendors to payout.
		 *
		 * @var Array
		 */
		protected $exclude_vendors_to_payout = array();

		/**
		 * Withdraws to payout.
		 *
		 * @var Array
		 */
		protected $from_date_to_payout = '';

		/**
		 * Exclude Vendors to payout.
		 *
		 * @var Array
		 */
		protected $to_date_to_payout = '';

		/**
		 * Status to payout.
		 *
		 * @var Array
		 */
		protected $status_to_payout = '';

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->set_status_to_payout( 'mvr-pending' );
			$this->set_payment_type_to_payout( array_key_first( MVR_Admin_Exporters::get_payment_methods() ) );
		}

		/**
		 * Payment types to payout.
		 *
		 * @since 1.0.0
		 * @param Array $payment_type_to_payout Payment type to payout.
		 */
		public function set_payment_type_to_payout( $payment_type_to_payout ) {
			$this->payment_type_to_payout = wc_clean( $payment_type_to_payout );
		}

		/**
		 * Set Vendors to payout.
		 *
		 * @since 1.0.0
		 * @param Array $vendors_to_payout List of vendors.
		 */
		public function set_vendors_to_payout( $vendors_to_payout ) {
			$this->vendors_to_payout = array_map( 'wc_clean', $vendors_to_payout );
		}

		/**
		 * Set Exclude Vendors to payout.
		 *
		 * @since 1.0.0
		 * @param Array $exclude_vendors_to_payout List of exclude vendors.
		 */
		public function set_exclude_vendors_to_payout( $exclude_vendors_to_payout ) {
			$this->exclude_vendors_to_payout = array_map( 'wc_clean', $exclude_vendors_to_payout );
		}

		/**
		 * Set From Date to payout.
		 *
		 * @since 1.0.0
		 * @param String $from_date_to_payout From Date.
		 */
		public function set_from_date_to_payout( $from_date_to_payout ) {
			$this->from_date_to_payout = wc_clean( $from_date_to_payout );
		}

		/**
		 * Set to Date to payout.
		 *
		 * @since 1.0.0
		 * @param String $to_date_to_payout From Date.
		 */
		public function set_to_date_to_payout( $to_date_to_payout ) {
			$this->to_date_to_payout = wc_clean( $to_date_to_payout );
		}

		/**
		 * Set to Date to payout.
		 *
		 * @since 1.0.0
		 * @param String $status_to_payout From Date.
		 */
		public function set_status_to_payout( $status_to_payout ) {
			$this->status_to_payout = wc_clean( $status_to_payout );
		}

		/**
		 * Get page.
		 *
		 * @since 3.1.0
		 * @return int
		 */
		public function get_page() {
			return $this->page;
		}

		/**
		 * Set page.
		 *
		 * @since 1.0.0
		 * @param Integer $page Page Nr.
		 */
		public function set_page( $page ) {
			$this->page = absint( $page );
		}

		/**
		 * Get batch limit.
		 *
		 * @since 1.0.0
		 * @return Integer
		 */
		public function get_limit() {
			/**
			 * Vendor Generate Payout limit
			 *
			 * @since 1.0.0
			 * */
			return apply_filters( 'mvr_withdraw_generate_payout_limit', $this->limit, $this );
		}

		/**
		 * Set batch limit.
		 *
		 * @since 1.0.0
		 * @param Integer $limit Limit to export.
		 */
		public function set_limit( $limit ) {
			$this->limit = absint( $limit );
		}

		/**
		 * Generate Payout
		 *
		 * @since 1.0.0
		 */
		public function generate_withdraw_payout() {
			$this->prepare_withdraw_data_to_payout();
			$this->process_withdraw_payout();
		}

		/**
		 * Prepare data for payout.
		 *
		 * @since 1.0.0
		 */
		public function prepare_withdraw_data_to_payout() {
			$args = array(
				'payment_method'     => $this->payment_type_to_payout,
				'include_vendor_ids' => $this->vendors_to_payout,
				'exclude_vendor_ids' => $this->exclude_vendors_to_payout,
				'status'             => $this->status_to_payout,
				'date_before'        => $this->to_date_to_payout,
				'date_after'         => $this->from_date_to_payout,
			);

			/**
			 * Withdraw Generate Payout Query Parameters
			 *
			 * @since 1.0.0
			 * */
			$withdraws_obj = mvr_get_withdraws( apply_filters( 'mvr_withdraw_generate_payout_query_args', $args ) );

			$this->total_withdraw_payouts = $withdraws_obj->total_withdraws;
			$this->withdraw_payout_data   = $withdraws_obj->withdraws;
		}

		/**
		 * Generate Payout
		 *
		 * @since 1.0.0
		 */
		public function process_withdraw_payout() {
			if ( mvr_check_is_array( $this->withdraw_payout_data ) ) {
				$vendors      = array();
				$items        = array();
				$args         = array();
				$withdraw_ids = array();

				foreach ( $this->withdraw_payout_data as $withdraw_obj ) {
					++$this->payed_count;

					if ( ! mvr_is_withdraw( $withdraw_obj ) || ! mvr_is_vendor( $withdraw_obj->get_vendor() ) ) {
						continue;
					}

					if ( '1' === $withdraw_obj->get_payment_method() ) {
						if ( 'pending' === $withdraw_obj->get_status() ) {
							$withdraw_obj->update_status( 'progress' );
						} elseif ( 'progress' === $withdraw_obj->get_status() ) {
							$withdraw_obj->update_status( 'success' );
						}

						continue;
					}

					$receiver_email = sanitize_email( $withdraw_obj->get_vendor()->get_paypal_email() );

					if ( empty( $receiver_email ) ) {
						continue;
					}

					$withdraw_ids[ $withdraw_obj->get_vendor_id() ][] = $withdraw_obj->get_id();
					$vendors[ $withdraw_obj->get_vendor_id() ][]      = $withdraw_obj->get_amount();
				}

				foreach ( $vendors as $vendor_id => $amounts ) {
					$vendor_obj = mvr_get_vendor( $vendor_id );

					if ( ! mvr_is_vendor( $vendor_obj ) ) {
						continue;
					}

					$receiver_email = sanitize_email( $vendor_obj->get_paypal_email() );

					if ( empty( $receiver_email ) || 'yes' !== get_option( 'mvr_settings_enable_paypal_payouts', 'no' ) ) {
						continue;
					}

					$items[] = array(
						'recipient_type' => 'EMAIL',
						'receiver'       => $receiver_email,
						'note'           => __( 'Payout received.', 'multi-vendor-marketplace' ),
						'sender_item_id' => $vendor_id,
						'amount'         => array(
							'value'    => wc_format_decimal( (float) array_sum( $amounts ), wc_get_price_decimals() ),
							'currency' => get_woocommerce_currency(),
						),
					);

					$args[ $vendor_id ] = array(
						'vendor_id'   => $vendor_id,
						'amount'      => (float) array_sum( $amounts ),
						'charge'      => 0,
						'schedule'    => '0',
						'source_id'   => '',
						'source_from' => '',
						'created_via' => 'admin',
						'receiver'    => $receiver_email,
					);
				}

				if ( mvr_check_is_array( $items ) ) {
					$payout_batch_obj = new MVR_Payout_Batch();
					$payout_batch_obj->set_props(
						array(
							'items'           => $items,
							'email_subject'   => __( 'Payout Received Successful', 'multi-vendor-marketplace' ),
							'email_message'   => __( 'You have received a payout! Thanks!!', 'multi-vendor-marketplace' ),
							'additional_data' => $args,
						)
					);
					$payout_batch_obj->save();
					$payout_batch_obj->add_note( __( 'New Payout entry created.', 'multi-vendor-marketplace' ) );

					if ( mvr_is_payout_batch( $payout_batch_obj ) ) {
						foreach ( $withdraw_ids as $vendor_id => $_withdraw_ids ) {
							foreach ( $_withdraw_ids as $withdraw_id ) {
								$withdraw_obj = mvr_get_withdraw( $withdraw_id );

								if ( mvr_is_withdraw( $withdraw_obj ) ) {
									$withdraw_obj->update_status( 'progress' );
								}
							}
						}

						$response = MVR_PayPal_Payouts_Helper::create_batch_payout(
							array(
								'sender_batch_header' => array(
									'sender_batch_id' => uniqid(),
									'email_subject'   => __( 'Payout Received Successful', 'multi-vendor-marketplace' ),
									'email_message'   => __( 'You have received a payout! Thanks!!', 'multi-vendor-marketplace' ),
								),
								'items'               => $items,
							)
						);

						if ( is_wp_error( $response ) ) {
							$payout_batch_obj->add_note( $response->get_error_message() );
						} elseif ( is_object( $response ) ) {
							$batch_id = $response->batch_header->payout_batch_id;

							if ( ! empty( $batch_id ) ) {
								foreach ( $withdraw_ids as $vendor_id => $_withdraw_ids ) {
									foreach ( $_withdraw_ids as $withdraw_id ) {
										$withdraw_obj = mvr_get_withdraw( $withdraw_id );

										if ( mvr_is_withdraw( $withdraw_obj ) ) {
											$withdraw_obj->update_status( 'success' );
										}
									}
								}

								$param = array(
									'batch_id'         => $batch_id,
									'payout_batch_obj' => $payout_batch_obj,
									'response'         => $response,
									'payouts_data'     => $args,
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
			}
		}

		/**
		 * Get count of records payed.
		 *
		 * @since 1.0.0
		 * @return Integer
		 */
		public function get_total_payed() {
			return ( ( $this->get_page() - 1 ) * $this->get_limit() ) + $this->payed_count;
		}

		/**
		 * Get total % complete.
		 *
		 * @since 1.0.0
		 * @return Integer
		 */
		public function get_percent_complete() {
			return $this->total_withdraw_payouts ? (int) floor( ( $this->get_total_payed() / $this->total_withdraw_payouts ) * 100 ) : 100;
		}
	}
}
