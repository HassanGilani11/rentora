<?php
/**
 * Transaction Data.
 *
 * @package Multi-Vendor for WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Transaction' ) ) {
	/**
	 * Transaction
	 *
	 * @class MVR_Transaction
	 * @package Class
	 */
	class MVR_Transaction extends WC_Data {

		/**
		 * Transaction Data array.
		 *
		 * @var Array
		 */
		protected $data = array(
			'vendor_id'     => '',
			'date_created'  => '',
			'amount'        => '',
			'currency'      => '',
			'type'          => '',
			'status'        => '',
			'created_via'   => '',
			'source_id'     => '',
			'source_from'   => '',
			'withdraw_date' => '',
			'date_modified' => '',
			'parent_id'     => '',
			'version'       => '',
		);

		/**
		 * Which data store to load.
		 *
		 * @var String
		 */
		protected $data_store_name = 'mvr_transaction';

		/**
		 * Get the Transaction if ID is passed, otherwise the transaction is new and empty.
		 *
		 * @since 1.0.0
		 * @param  int|object|MVR_Transaction $transaction transaction to read.
		 */
		public function __construct( $transaction = 0 ) {
			parent::__construct( $transaction );

			if ( is_numeric( $transaction ) && $transaction > 0 ) {
				$this->set_id( $transaction );
			} elseif ( $transaction instanceof self ) {
				$this->set_id( $transaction->get_id() );
			} elseif ( ! empty( $transaction->ID ) ) {
				$this->set_id( $transaction->ID );
			} else {
				$this->set_object_read( true );
			}

			$this->data_store = WC_Data_Store::load( $this->data_store_name );

			if ( $this->get_id() > 0 ) {
				$this->data_store->read( $this );
			}
		}

		/**
		 * Get all valid statuses for this transaction
		 *
		 * @since 1.0.0
		 * @return Array Internal status keys e.g. 'mvr-paid'
		 */
		public function get_valid_statuses() {
			return array_keys( mvr_get_transaction_statuses() );
		}

		/**
		 * Updates status of transaction immediately.
		 *
		 * @since 1.0.0
		 * @uses MVR_Transaction::set_status()
		 * @param String $new_status    Status to change the transaction to. No internal mvr- prefix is required.
		 * @return Boolean
		 */
		public function update_status( $new_status ) {
			if ( ! $this->get_id() ) { // Transaction must exist.
				return false;
			}

			try {
				$this->set_status( $new_status );
				$this->save();
			} catch ( Exception $e ) {
				$logger = wc_get_logger();
				$logger->error(
					sprintf(
						'Error updating status for transaction #%d',
						$this->get_id()
					),
					array(
						'transaction' => $this,
						'error'       => $e,
					)
				);

				return false;
			}

			return true;
		}

		/**
		 * Log an error about this transaction is exception is encountered.
		 *
		 * @since 1.0.0
		 * @param Exception $e Exception object.
		 * @param String    $message Message regarding exception thrown.
		 */
		protected function handle_exception( $e, $message = 'Error' ) {
			wc_get_logger()->error(
				$message,
				array(
					'transaction' => $this,
					'error'       => $e,
				)
			);
		}

		/*
		|--------------------------------------------------------------------------
		| Conditionals
		|--------------------------------------------------------------------------
		|
		| Checks if a condition is true or false.
		|
		 */

		/**
		 * Checks the transaction status against a passed in status.
		 *
		 * @since 1.0.0
		 * @param Array|String $status Status to check.
		 * @return Boolean
		 */
		public function has_status( $status ) {
			/**
			 * Has Status.
			 *
			 * @since 1.0.0
			 */
			return apply_filters( 'mvr_transaction_has_status', ( is_array( $status ) && in_array( $this->get_status(), $status, true ) ) || $this->get_status() === $status, $this, $status );
		}

		/*
		|--------------------------------------------------------------------------
		| URLs and Endpoints
		|--------------------------------------------------------------------------
		 */

		/**
		 * Get's the URL to edit the transaction in the backend.
		 *
		 * @since 1.0.0
		 * @return String
		 */
		public function get_admin_edit_url() {
			/**
			 * Edit URL.
			 *
			 * @since 1.0.0
			 */
			return apply_filters( 'mvr_get_admin_edit_transaction_url', get_admin_url( null, 'post.php?post=' . $this->get_id() . '&action=edit' ), $this );
		}

		/**
		 * Get's Vendor Obj.
		 *
		 * @since 1.0.0
		 * @return MVR_Vendor
		 */
		public function get_vendor() {
			$vendor = mvr_get_vendor( $this->get_vendor_id() );

			if ( ! mvr_is_vendor( $vendor ) ) {
				return false;
			}

			return $vendor;
		}

		/**
		 * Get's Order Obj.
		 *
		 * @since 1.0.0
		 * @return WC_Order
		 */
		public function get_order() {
			if ( 'order' === $this->get_source_from() ) {
				$order = wc_get_order( $this->get_source_id() );

				if ( ! is_a( $order, 'WC_Order' ) ) {
					return false;
				}

				return $order;
			}

			return false;
		}

		/**
		 * Get's Order Obj.
		 *
		 * @since 1.0.0
		 * @return WC_Order
		 */
		public function get_description() {
			switch ( $this->get_source_from() ) {
				case 'order':
					$order_url = mvr_get_dashboard_endpoint_url( 'mvr-view-order', $this->get_source_id() );
					$order     = '<a href="' . esc_url( $order_url ) . '"> #' . $this->get_source_id() . '</a>';

					/* translators: %s: Order ID */
					return sprintf( esc_html__( 'Order %s', 'multi-vendor-marketplace' ), wp_kses_post( $order ) );
				case 'withdraw':
					/* translators: %s: Withdraw ID */
					return sprintf( esc_html__( 'Withdraw #%s', 'multi-vendor-marketplace' ), esc_attr( $this->get_source_id() ) );
				case 'payout':
					return esc_html__( 'Payout', 'multi-vendor-marketplace' );
				default:
					return ucfirst( $this->get_source_from() );
			}
		}

		/*
		|--------------------------------------------------------------------------
		| Getters
		|--------------------------------------------------------------------------
		 */

		/**
		 * Get version.
		 *
		 * @since 1.0.0
		 * @param  String $context View or edit context.
		 * @return String
		 */
		public function get_version( $context = 'view' ) {
			return $this->get_prop( 'version', $context );
		}

		/**
		 * Get date created.
		 *
		 * @since 1.0.0
		 * @param  String $context View or edit context.
		 * @return WC_DateTime|NULL object if the date is set or null if there is no date.
		 */
		public function get_date_created( $context = 'view' ) {
			return $this->get_prop( 'date_created', $context );
		}

		/**
		 * Get date modified.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return WC_DateTime|NULL object if the date is set or null if there is no date.
		 */
		public function get_date_modified( $context = 'view' ) {
			return $this->get_prop( 'date_modified', $context );
		}

		/**
		 * Get Order ID.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the order id is set or null if there is no order id.
		 */
		public function get_source_id( $context = 'view' ) {
			return $this->get_prop( 'source_id', $context );
		}

		/**
		 * Get Vendor ID.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the vendor_id is set or null if there is no vendor_id.
		 */
		public function get_vendor_id( $context = 'view' ) {
			return (int) $this->get_prop( 'vendor_id', $context );
		}

		/**
		 * Get Created Via.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the created_via is set or null if there is no created_via.
		 */
		public function get_created_via( $context = 'view' ) {
			return $this->get_prop( 'created_via', $context );
		}

		/**
		 * Get Source Via.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the source_from is set or null if there is no source_from.
		 */
		public function get_source_from( $context = 'view' ) {
			return $this->get_prop( 'source_from', $context );
		}

		/**
		 * Get currency.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the currency is set or null if there is no currency.
		 */
		public function get_currency( $context = 'view' ) {
			return $this->get_prop( 'currency', $context );
		}

		/**
		 * Get Parent id.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the parent id is set or null if there is no parent id.
		 */
		public function get_parent_id( $context = 'view' ) {
			return $this->get_prop( 'parent_id', $context );
		}

		/**
		 * Get withdraw date.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the withdraw date is set or null if there is no withdraw date.
		 */
		public function get_withdraw_date( $context = 'view' ) {
			return $this->get_prop( 'withdraw_date', $context );
		}

		/**
		 * Return the vendor statuses without mvr- internal prefix.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String
		 */
		public function get_status( $context = 'view' ) {
			$status = $this->get_prop( 'status', $context );

			if ( empty( $status ) && 'view' === $context ) {
				/**
				 * Default Status.
				 *
				 * @since 1.0.0
				 */
				$status = apply_filters( 'mvr_default_transaction_status', 'process' );
			}

			return $status;
		}

		/**
		 * Get transaction amount.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the transaction amount is set or null if there is no transaction amount.
		 */
		public function get_amount( $context = 'view' ) {
			return (float) $this->get_prop( 'amount', $context );
		}

		/**
		 * Get transaction type.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the transaction type is set or null if there is no transaction type.
		 */
		public function get_type( $context = 'view' ) {
			return $this->get_prop( 'type', $context );
		}

		/**
		 * Get commission settings.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the commission settings is set or null if there is no commission settings.
		 */
		public function get_commission_settings( $context = 'view' ) {
			return $this->get_prop( 'commission_settings', $context );
		}

		/*
		|--------------------------------------------------------------------------
		| Setters
		|--------------------------------------------------------------------------
		|
		| Functions for setting vendor data. These should not update anything in the
		| database itself and should only change what is stored in the class
		| object.
		 */

		/**
		 * Set status.
		 *
		 * @since 1.0.0
		 * @param String $new_status Status to change the vendor to. No internal mvr- prefix is required.
		 * @return Array details of change
		 */
		public function set_status( $new_status ) {
			$old_status        = $this->get_status();
			$new_status        = mvr_trim_post_status( $new_status );
			$status_exceptions = array( 'auto-draft', 'trash' );

			// If setting the status, ensure it's set to a valid status.
			if ( true === $this->object_read ) {
				// Only allow valid new status.
				if ( ! in_array( 'mvr-' . $new_status, $this->get_valid_statuses(), true ) && ! in_array( $new_status, $status_exceptions, true ) ) {
					$new_status = 'pending';
				}

				// If the old status is set but unknown (e.g. draft) assume its pending for action usage.
				if ( $old_status && ! in_array( 'mvr-' . $old_status, $this->get_valid_statuses(), true ) && ! in_array( $old_status, $status_exceptions, true ) ) {
					$old_status = 'pending';
				}
			}

			$this->set_prop( 'status', $new_status );

			return array(
				'from' => $old_status,
				'to'   => $new_status,
			);
		}

		/**
		 * Set version.
		 *
		 * @since 1.0.0
		 * @param String $value Value to set.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_version( $value ) {
			$this->set_prop( 'version', $value );
		}

		/**
		 * Set date created.
		 *
		 * @since 1.0.0
		 * @param String|Integer|Null $date UTC timestamp, or ISO 8601 DateTime. If the DateTime string has no timezone or offset, WordPress site timezone will be assumed. Null if there is no date.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_date_created( $date = null ) {
			$this->set_date_prop( 'date_created', $date );
		}

		/**
		 * Set date modified.
		 *
		 * @since 1.0.0
		 * @param String|Integer|Null $date UTC timestamp, or ISO 8601 DateTime. If the DateTime string has no timezone or offset, WordPress site timezone will be assumed. Null if there is no date.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_date_modified( $date = null ) {
			$this->set_date_prop( 'date_modified', $date );
		}

		/**
		 * Set Source id.
		 *
		 * @since 1.0.0
		 * @param String $value Source ID.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_source_id( $value = null ) {
			$this->set_prop( 'source_id', $value );
		}

		/**
		 * Set vendor id.
		 *
		 * @since 1.0.0
		 * @param String $value Vendor id.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_vendor_id( $value = null ) {
			$this->set_prop( 'vendor_id', (int) $value );
		}

		/**
		 * Set created via.
		 *
		 * @since 1.0.0
		 * @param String $value Created Via.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_created_via( $value = null ) {
			$this->set_prop( 'created_via', $value );
		}

		/**
		 * Set source via.
		 *
		 * @since 1.0.0
		 * @param String $value Source Via.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_source_from( $value = null ) {
			$this->set_prop( 'source_from', $value );
		}

		/**
		 * Set currency.
		 *
		 * @since 1.0.0
		 * @param String $value currency.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_currency( $value = null ) {
			$this->set_prop( 'currency', $value );
		}

		/**
		 * Set Parent ID.
		 *
		 * @since 1.0.0
		 * @param String $value parent id.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_parent_id( $value = null ) {
			$this->set_prop( 'parent_id', (int) $value );
		}

		/**
		 * Set withdraw date.
		 *
		 * @since 1.0.0
		 * @param String $value withdraw date.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_withdraw_date( $value = null ) {
			$this->set_prop( 'withdraw_date', $value );
		}

		/**
		 * Set amount.
		 *
		 * @since 1.0.0
		 * @param String $value amount.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_amount( $value = null ) {
			$this->set_prop( 'amount', (float) wc_format_decimal( $value, false, true ) );
		}

		/**
		 * Set transaction type.
		 *
		 * @since 1.0.0
		 * @param String $value transaction type.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_type( $value = null ) {
			$this->set_prop( 'type', $value );
		}
	}
}
