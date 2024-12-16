<?php
/**
 * Withdraw Data.
 *
 * @package Multi-Vendor for WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Withdraw' ) ) {
	/**
	 * Withdraw
	 *
	 * @class MVR_Withdraw
	 * @package Class
	 */
	class MVR_Withdraw extends WC_Data {

		/**
		 * Stores data about status changes so relevant hooks can be fired.
		 *
		 * @var bool|array
		 */
		protected $status_transition = false;

		/**
		 * Withdraw Data array.
		 *
		 * @var Array
		 */
		protected $data = array(
			'status'         => '',
			'version'        => '',
			'date_created'   => null,
			'date_modified'  => null,
			'vendor_id'      => '',
			'created_via'    => '',
			'currency'       => '',
			'amount'         => '',
			'payment_method' => '1',
			'charge_amount'  => '',
			'parent_id'      => 0,
		);

		/**
		 * Stores meta in cache for future reads.
		 *
		 * A group must be set to to enable caching.
		 *
		 * @var String
		 */
		protected $cache_group = 'mvr_withdraws';

		/**
		 * Which data store to load.
		 *
		 * @var String
		 */
		protected $data_store_name = 'mvr_withdraw';

		/**
		 * This is the name of this object type.
		 *
		 * @var String
		 */
		protected $object_type = 'mvr_withdraw';

		/**
		 * Get the Withdraw if ID is passed, otherwise the withdraw is new and empty.
		 *
		 * @since 1.0.0
		 * @param  int|object|MVR_Withdraw $withdraw withdraw to read.
		 */
		public function __construct( $withdraw = 0 ) {
			parent::__construct( $withdraw );

			if ( is_numeric( $withdraw ) && $withdraw > 0 ) {
				$this->set_id( $withdraw );
			} elseif ( $withdraw instanceof self ) {
				$this->set_id( $withdraw->get_id() );
			} elseif ( ! empty( $withdraw->ID ) ) {
				$this->set_id( $withdraw->ID );
			} else {
				$this->set_object_read( true );
			}

			$this->data_store = WC_Data_Store::load( $this->data_store_name );

			if ( $this->get_id() > 0 ) {
				$this->data_store->read( $this );
			}
		}

		/**
		 * Get internal type.
		 *
		 * @since 1.0.0
		 * @return String
		 */
		public function get_type() {
			return $this->object_type;
		}

		/**
		 * Get all valid statuses for this withdraw
		 *
		 * @since 1.0.0
		 * @return Array Internal status keys e.g. 'mvr-paid'
		 */
		public function get_valid_statuses() {
			return array_keys( mvr_get_withdraw_statuses() );
		}

		/**
		 * Get View URL
		 *
		 * @since 1.0.0
		 */
		public function get_admin_view_url() {
			return add_query_arg(
				array(
					'action' => 'view',
					'id'     => $this->get_id(),
				),
				admin_url( 'admin.php?page=mvr_withdraw' )
			);
		}

		/**
		 * Updates status of withdraw immediately.
		 *
		 * @since 1.0.0
		 * @uses MVR_Withdraw::set_status()
		 * @param String $new_status    Status to change the withdraw to. No internal mvr- prefix is required.
		 * @return Boolean
		 */
		public function update_status( $new_status ) {
			if ( ! $this->get_id() ) { // Withdraw must exist.
				return false;
			}

			try {
				$this->set_status( $new_status );
				$this->save();
			} catch ( Exception $e ) {
				$logger = wc_get_logger();
				$logger->error(
					sprintf(
						'Error updating status for withdraw #%d',
						$this->get_id()
					),
					array(
						'withdraw' => $this,
						'error'    => $e,
					)
				);

				return false;
			}

			return true;
		}

		/**
		 * Save data to the database.
		 *
		 * @since 1.0.0
		 * @return Integer Vendor ID.
		 */
		public function save() {
			parent::save();
			$this->status_transition();

			return $this->get_id();
		}

		/**
		 * Handle the status transition.
		 *
		 * @since 1.0.0
		 */
		protected function status_transition() {
			$status_transition = $this->status_transition;

			// Reset status transition variable.
			$this->status_transition = false;

			if ( $status_transition ) {
				try {
					/**
					 * Withdraw status updated to.
					 *
					 * @since 1.0.0
					 */
					do_action( 'mvr_withdraw_status_' . $status_transition['to'], $this );

					if ( ! empty( $status_transition['from'] ) && $status_transition['from'] !== $status_transition['to'] ) {
						/* translators: 1: old status 2: new status */
						$transition_note = sprintf( __( 'Status changed from <b>%1$s</b> to <b>%2$s</b>.', 'multi-vendor-marketplace' ), mvr_get_withdraw_status_name( $status_transition['from'] ), mvr_get_withdraw_status_name( $status_transition['to'] ) );

						/**
						 * Withdraw status updated from and to.
						 *
						 * @since 1.0.0
						 */
						do_action( 'mvr_withdraw_status_' . $status_transition['from'] . '_to_' . $status_transition['to'], $this );

						/**
						 * Withdraw status changed.
						 *
						 * @since 1.0.0
						 */
						do_action( 'mvr_withdraw_status_changed', $status_transition['from'], $status_transition['to'], $this );

						/**
						 * Withdraw status updated.
						 *
						 * @since 1.0.0
						 */
						do_action( 'mvr_withdraw_status_updated', $status_transition['to'], $status_transition['manual'], $this );
					}
				} catch ( Exception $e ) {
					$this->handle_exception( $e, sprintf( 'Status transition of withdraw #%d errored!', $this->get_id() ) );
				}
			}
		}

		/**
		 * Log an error about this withdraw is exception is encountered.
		 *
		 * @since 1.0.0
		 * @param Exception $e Exception object.
		 * @param String    $message Message regarding exception thrown.
		 */
		protected function handle_exception( $e, $message = 'Error' ) {
			wc_get_logger()->error(
				$message,
				array(
					'withdraw' => $this,
					'error'    => $e,
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
		 * Checks the withdraw status against a passed in status.
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
			return apply_filters( 'mvr_withdraw_has_status', ( is_array( $status ) && in_array( $this->get_status(), $status, true ) ) || $this->get_status() === $status, $this, $status );
		}

		/**
		 * Prepare Commission Obj
		 *
		 * @since 1.0.0
		 * @return MVR_Vendor
		 */
		public function prepare_commission_objs() {
			return mvr_get_commissions(
				array(
					'source_id'   => $this->get_id(),
					'source_from' => 'withdraw',
					'vendor_id'   => $this->get_vendor_id(),
				)
			);
		}

		/**
		 * Has Commission Obj.
		 *
		 * @since 1.0.0
		 * @return Boolean
		 */
		public function has_commission() {
			return $this->prepare_commission_objs()->has_commission;
		}

		/**
		 * Get's Commission Obj.
		 *
		 * @since 1.0.0
		 * @return Boolean|MVR_Commission
		 */
		public function get_commission() {
			if ( ! $this->has_commission() ) {
				return false;
			}

			return current( $this->prepare_commission_objs()->commissions );
		}

		/**
		 * Prepare transaction Obj
		 *
		 * @since 1.0.0
		 * @return MVR_Vendor
		 */
		public function prepare_transaction_objs() {
			return mvr_get_transactions(
				array(
					'source_id'   => $this->get_id(),
					'source_from' => 'withdraw',
					'vendor_id'   => $this->get_vendor_id(),
				)
			);
		}

		/**
		 * Has Commission Obj.
		 *
		 * @since 1.0.0
		 * @return Boolean
		 */
		public function has_transaction() {
			return $this->prepare_transaction_objs()->has_transaction;
		}

		/**
		 * Get's Transaction Obj.
		 *
		 * @since 1.0.0
		 * @return Boolean|MVR_Transaction
		 */
		public function get_transaction() {
			if ( ! $this->has_transaction() ) {
				return false;
			}

			return current( $this->prepare_transaction_objs()->transactions );
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

		/*
		|--------------------------------------------------------------------------
		| URLs and Endpoints
		|--------------------------------------------------------------------------
		 */

		/**
		 * Get's the URL to edit the withdraw in the backend.
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
			return apply_filters( 'mvr_get_admin_edit_withdraw_url', get_admin_url( null, 'post.php?post=' . $this->get_id() . '&action=edit' ), $this );
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
				$status = apply_filters( 'mvr_default_withdraw_status', 'pending' );
			}

			return $status;
		}

		/**
		 * Get withdraw amount.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the withdraw amount is set or null if there is no withdraw amount.
		 */
		public function get_amount( $context = 'view' ) {
			return $this->get_prop( 'amount', $context );
		}

		/**
		 * Get withdraw payment method.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the withdraw payment type is set or null if there is no withdraw payment type.
		 */
		public function get_payment_method( $context = 'view' ) {
			return $this->get_prop( 'payment_method', $context );
		}

		/**
		 * Get withdraw charge amount.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the withdraw Charge amount is set or null if there is no withdraw charge amount.
		 */
		public function get_charge_amount( $context = 'view' ) {
			return $this->get_prop( 'charge_amount', $context );
		}

		/**
		 * Get parent.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the Parent is set or null if there is no Parent.
		 */
		public function get_parent_id( $context = 'view' ) {
			return $this->get_prop( 'parent_id', (int) $context );
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
		 * @param String  $new_status Status to change the vendor to. No internal mvr- prefix is required.
		 * @param Boolean $manual_update Is this a manual user payment status change?.
		 * @return Array details of change
		 */
		public function set_status( $new_status, $manual_update = false ) {
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

				if ( ! empty( $old_status ) && $old_status !== $new_status ) {
					$this->status_transition = array(
						'from'   => ! empty( $this->status_transition['from'] ) ? $this->status_transition['from'] : $old_status,
						'to'     => $new_status,
						'note'   => '',
						'manual' => (bool) $manual_update,
					);

					if ( $manual_update ) {
						/**
						 * When withdraw status has been manually edited.
						 *
						 * @since 1.0
						 */
						do_action( 'mvr_withdraw_edit_status', $this->get_id(), $new_status );
					}
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
		 * Set payment method.
		 *
		 * @since 1.0.0
		 * @param String $value payment method.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_payment_method( $value = null ) {
			$this->set_prop( 'payment_method', $value );
		}

		/**
		 * Set Charge amount.
		 *
		 * @since 1.0.0
		 * @param String $value charge amount.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_charge_amount( $value = null ) {
			$this->set_prop( 'charge_amount', (float) wc_format_decimal( $value, false, true ) );
		}

		/**
		 * Set Parent.
		 *
		 * @since 1.0.0
		 * @param String $value parent.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_parent_id( $value = null ) {
			$this->set_prop( 'parent_id', (int) $value );
		}
	}
}
