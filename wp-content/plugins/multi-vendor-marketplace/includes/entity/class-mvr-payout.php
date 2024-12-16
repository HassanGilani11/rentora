<?php
/**
 * Payout Data.
 *
 * @package Multi-Vendor for WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Payout' ) ) {
	/**
	 * Payout
	 *
	 * @class MVR_Payout
	 * @package Class
	 */
	class MVR_Payout extends WC_Data {

		/**
		 * Payout Data array.
		 *
		 * @var Array
		 */
		protected $data = array(
			'vendor_id'      => '',
			'user_id'        => '',
			'email'          => '',
			'amount'         => '',
			'currency'       => '',
			'payment_method' => '',
			'status'         => '',
			'created_via'    => '',
			'source_id'      => '',
			'source_from'    => '',
			'batch_id'       => '',
			'schedule'       => '',
			'date_created'   => '',
			'date_modified'  => '',
			'version'        => '',
		);

		/**
		 * Which data store to load.
		 *
		 * @var String
		 */
		protected $data_store_name = 'mvr_payout';

		/**
		 * Get the Payout if ID is passed, otherwise the payout is new and empty.
		 *
		 * @since 1.0.0
		 * @param  int|object|MVR_Payout $payout Payout to read.
		 */
		public function __construct( $payout = 0 ) {
			parent::__construct( $payout );

			if ( is_numeric( $payout ) && $payout > 0 ) {
				$this->set_id( $payout );
			} elseif ( $payout instanceof self ) {
				$this->set_id( $payout->get_id() );
			} elseif ( ! empty( $payout->ID ) ) {
				$this->set_id( $payout->ID );
			} else {
				$this->set_object_read( true );
			}

			$this->data_store = WC_Data_Store::load( $this->data_store_name );

			if ( $this->get_id() > 0 ) {
				$this->data_store->read( $this );
			}
		}

		/**
		 * Get all valid statuses for this payout
		 *
		 * @since 1.0.0
		 * @return Array Internal status keys e.g. 'mvr-paid'
		 */
		public function get_valid_statuses() {
			return array_keys( mvr_get_payout_statuses() );
		}

		/**
		 * Updates status of Payout immediately.
		 *
		 * @since 1.0.0
		 * @uses MVR_Payout::set_status()
		 * @param String $new_status    Status to change the payout to. No internal mvr- prefix is required.
		 * @return Boolean
		 */
		public function update_status( $new_status ) {
			if ( ! $this->get_id() ) { // Payout must exist.
				return false;
			}

			try {
				$this->set_status( $new_status );
				$this->save();
			} catch ( Exception $e ) {
				$logger = wc_get_logger();
				$logger->error(
					sprintf(
						'Error updating status for payout #%d',
						$this->get_id()
					),
					array(
						'payout' => $this,
						'error'  => $e,
					)
				);

				return false;
			}

			return true;
		}

		/**
		 * Log an error about this payout is exception is encountered.
		 *
		 * @since 1.0.0
		 * @param Exception $e Exception object.
		 * @param String    $message Message regarding exception thrown.
		 */
		protected function handle_exception( $e, $message = 'Error' ) {
			wc_get_logger()->error(
				$message,
				array(
					'payout' => $this,
					'error'  => $e,
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
		 * Checks the payout status against a passed in status.
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
			return apply_filters( 'mvr_payout_has_status', ( is_array( $status ) && in_array( $this->get_status(), $status, true ) ) || $this->get_status() === $status, $this, $status );
		}

		/*
		|--------------------------------------------------------------------------
		| URLs and Endpoints
		|--------------------------------------------------------------------------
		 */

		/**
		 * Get's the URL to edit the payout in the backend.
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
			return apply_filters( 'mvr_get_admin_edit_payout_url', get_admin_url( null, 'post.php?post=' . $this->get_id() . '&action=edit' ), $this );
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
		 * Get Source ID.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the Source id is set or null if there is no source id.
		 */
		public function get_source_id( $context = 'view' ) {
			return $this->get_prop( 'source_id', $context );
		}

		/**
		 * Get Batch ID.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the batch id is set or null if there is no batch id.
		 */
		public function get_batch_id( $context = 'view' ) {
			return $this->get_prop( 'batch_id', $context );
		}

		/**
		 * Get schedule.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the schedule is set or null if there is no schedule.
		 */
		public function get_schedule( $context = 'view' ) {
			return $this->get_prop( 'schedule', $context );
		}

		/**
		 * Get Vendor ID.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the vendor id is set or null if there is no vendor id.
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
		 * @return String|NULL String if the source from is set or null if there is no source from.
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
		 * Get user id.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the user id is set or null if there is no user id.
		 */
		public function get_user_id( $context = 'view' ) {
			return $this->get_prop( 'user_id', $context );
		}

		/**
		 * Get email.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the email is set or null if there is no email.
		 */
		public function get_email( $context = 'view' ) {
			return $this->get_prop( 'email', $context );
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
				$status = apply_filters( 'mvr_default_payout_status', 'unpaid' );
			}

			return $status;
		}

		/**
		 * Get payout amount.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the payout amount is set or null if there is no payout amount.
		 */
		public function get_amount( $context = 'view' ) {
			return (float) $this->get_prop( 'amount', $context );
		}

		/**
		 * Get payout payment method.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the payout payment method is set or null if there is no payout payment method.
		 */
		public function get_payment_method( $context = 'view' ) {
			return $this->get_prop( 'payment_method', $context );
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
		 * Set Batch id.
		 *
		 * @since 1.0.0
		 * @param String $value Batch ID.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_batch_id( $value = null ) {
			$this->set_prop( 'batch_id', $value );
		}

		/**
		 * Set Schedule.
		 *
		 * @since 1.0.0
		 * @param String $value Schedule.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_schedule( $value = null ) {
			$this->set_prop( 'schedule', $value );
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
		 * Set user ID.
		 *
		 * @since 1.0.0
		 * @param String $value user id.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_user_id( $value = null ) {
			$this->set_prop( 'user_id', (int) $value );
		}

		/**
		 * Set email.
		 *
		 * @since 1.0.0
		 * @param String $value email.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_email( $value = null ) {
			$this->set_prop( 'email', $value );
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
		 * Set Payout payment method.
		 *
		 * @since 1.0.0
		 * @param String $value payout payment method.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_payment_method( $value = null ) {
			$this->set_prop( 'payment_method', $value );
		}
	}
}
