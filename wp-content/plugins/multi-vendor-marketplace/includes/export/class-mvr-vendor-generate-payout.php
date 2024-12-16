<?php
/**
 * Handles Vendor Generate Payout.
 *
 * @package Multi-Vendor\Generate Payout
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'MVR_Vendor_Generate_Payout' ) ) {
	/**
	 * MVR_Vendor_Generate_Payout Class.
	 */
	class MVR_Vendor_Generate_Payout {

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
		protected $payout_data = array();

		/**
		 * Total Payouts.
		 *
		 * @var integer
		 */
		protected $total_payouts = 0;

		/**
		 * Which payment types are being exported.
		 *
		 * @var Array
		 */
		protected $payment_types_to_payout = array();

		/**
		 * Vendors to payout.
		 *
		 * @var Array
		 */
		protected $vendors_to_payout = array();

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->set_payment_types_to_payout( array_keys( MVR_Admin_Exporters::get_payment_methods() ) );
		}

		/**
		 * Payment types to payout.
		 *
		 * @since 1.0.0
		 * @param Array $payment_types_to_payout List of payment types.
		 */
		public function set_payment_types_to_payout( $payment_types_to_payout ) {
			$this->payment_types_to_payout = array_map( 'wc_clean', $payment_types_to_payout );
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
			return apply_filters( 'mvr_vendor_generate_payout_limit', $this->limit, $this );
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
		 * Prepare data for payout.
		 *
		 * @since 1.0.0
		 */
		public function prepare_data_to_payout() {
			$args = array(
				'payment_method' => $this->payment_types_to_payout,
				'include_ids'    => $this->vendors_to_payout,
			);

			/**
			 * Vendor Generate Payout Query Parameters
			 *
			 * @since 1.0.0
			 * */
			$vendors = mvr_get_vendors( apply_filters( 'mvr_vendor_generate_payout_query_args', $args ) );

			$this->total_payouts = $vendors->total_vendors;
			$this->payout_data   = $vendors->vendors;
		}

		/**
		 * Generate Payout
		 *
		 * @since 1.0.0
		 */
		public function generate_payout() {
			$this->prepare_data_to_payout();
			$this->process_vendor_payout();
		}

		/**
		 * Generate Payout
		 *
		 * @since 1.0.0
		 */
		public function process_vendor_payout() {
			if ( mvr_check_is_array( $this->payout_data ) ) {
				$args                = array();
				$items               = array();
				$payout_withdraw_ids = array();

				foreach ( $this->payout_data as $vendor_obj ) {
					++$this->payed_count;

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
							'source_id'      => 0,
							'source_from'    => 'admin',
							'currency'       => get_woocommerce_currency(),
						)
					);
					$withdraw_obj->save();
					$withdraw_obj->update_status( 'pending' );

					$vendor_obj->set_amount( 0 );
					$vendor_obj->save();

					if ( '1' === $vendor_obj->get_payment_method() ) {
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
						'sender_item_id' => $vendor_obj->get_id(),
						'amount'         => array(
							'value'    => wc_format_decimal( (float) array_sum( $withdraw_amount ), wc_get_price_decimals() ),
							'currency' => get_woocommerce_currency(),
						),
					);

					$args[ $vendor_obj->get_id() ] = array(
						'vendor_id'   => $vendor_obj->get_id(),
						'source_id'   => $withdraw_obj->get_id(),
						'source_from' => 'withdraw',
						'created_via' => 'admin',
						'amount'      => (float) $withdraw_amount,
						'charge'      => 0,
						'schedule'    => '0',
						'receiver'    => $receiver_email,
					);

					$payout_withdraw_ids[] = $withdraw_obj->get_id();
				}

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
			return $this->total_payouts ? (int) floor( ( $this->get_total_payed() / $this->total_payouts ) * 100 ) : 100;
		}
	}
}
