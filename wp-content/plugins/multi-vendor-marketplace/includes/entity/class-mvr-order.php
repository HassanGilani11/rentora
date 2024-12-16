<?php
/**
 * Order Data.
 *
 * @package Multi-Vendor for WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Order' ) ) {
	/**
	 * Order
	 *
	 * @class MVR_Order
	 * @package Class
	 */
	class MVR_Order extends WC_Data {

		/**
		 * Order Data array.
		 *
		 * @var Array
		 */
		protected $data = array(
			'vendor_id'     => '',
			'date_created'  => null,
			'order_id'      => '',
			'commission_id' => '',
			'user_id'       => '',
			'email'         => '',
			'mvr_user_id'   => '',
			'status'        => '',
			'created_via'   => '',
			'date_modified' => null,
			'currency'      => '',
			'version'       => '',
		);

		/**
		 * Which data store to load.
		 *
		 * @var String
		 */
		protected $data_store_name = 'mvr_order';

		/**
		 * Get the order if ID is passed, otherwise the order is new and empty.
		 *
		 * @since 1.0.0
		 * @param  int|object|MVR_Order $order Order to read.
		 */
		public function __construct( $order = 0 ) {
			parent::__construct( $order );

			if ( is_numeric( $order ) && $order > 0 ) {
				$this->set_id( $order );
			} elseif ( $order instanceof self ) {
				$this->set_id( $order->get_id() );
			} elseif ( ! empty( $order->ID ) ) {
				$this->set_id( $order->ID );
			} else {
				$this->set_object_read( true );
			}

			$this->data_store = WC_Data_Store::load( $this->data_store_name );

			if ( $this->get_id() > 0 ) {
				$this->data_store->read( $this );
			}
		}

		/**
		 * Get all valid statuses for this order
		 *
		 * @since 1.0.0
		 * @return Array Internal status keys e.g. 'mvr-paid'
		 */
		public function get_valid_statuses() {
			return array_keys( wc_get_order_statuses() );
		}

		/**
		 * Updates status of order immediately.
		 *
		 * @since 1.0.0
		 * @uses MVR_Order::set_status()
		 * @param String $new_status    Status to change the order to. No internal mvr- prefix is required.
		 * @return Boolean
		 */
		public function update_status( $new_status ) {
			if ( ! $this->get_id() ) { // Order must exist.
				return false;
			}

			try {
				$this->set_status( $new_status );
				$this->save();
			} catch ( Exception $e ) {
				$logger = wc_get_logger();
				$logger->error(
					sprintf(
						'Error updating status for order #%d',
						$this->get_id()
					),
					array(
						'order' => $this,
						'error' => $e,
					)
				);

				return false;
			}

			return true;
		}

		/**
		 * Log an error about this order is exception is encountered.
		 *
		 * @since 1.0.0
		 * @param Exception $e Exception object.
		 * @param String    $message Message regarding exception thrown.
		 */
		protected function handle_exception( $e, $message = 'Error' ) {
			wc_get_logger()->error(
				$message,
				array(
					'order' => $this,
					'error' => $e,
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
		 * Checks the order status against a passed in status.
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
			return apply_filters( 'mvr_order_has_status', ( is_array( $status ) && in_array( $this->get_status(), $status, true ) ) || $this->get_status() === $status, $this, $status );
		}

		/*
		|--------------------------------------------------------------------------
		| URLs and Endpoints
		|--------------------------------------------------------------------------
		 */

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
			$order_obj = wc_get_order( $this->get_order_id() );

			if ( ! is_a( $order_obj, 'WC_Order' ) ) {
				return false;
			}

			return $order_obj;
		}

		/**
		 * Get's Commission Obj.
		 *
		 * @since 1.0.0
		 * @return MVR_Commission
		 */
		public function get_commission() {
			$commission_obj = mvr_get_commission( $this->get_commission_id() );

			if ( ! mvr_is_commission( $commission_obj ) ) {
				return false;
			}

			return $commission_obj;
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
		 * Get user ID.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the email id is set or null if there is no email id.
		 */
		public function get_user_id( $context = 'view' ) {
			return (int) $this->get_prop( 'user_id', $context );
		}

		/**
		 * Get Multi Vendor user ID.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the multi vendor user id is set or null if there is no multi vendor user id.
		 */
		public function get_mvr_user_id( $context = 'view' ) {
			return (int) $this->get_prop( 'mvr_user_id', $context );
		}

		/**
		 * Get Email.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the Email is set or null if there is no Email.
		 */
		public function get_email( $context = 'view' ) {
			return $this->get_prop( 'email', $context );
		}

		/**
		 * Get commission ID.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the commission id is set or null if there is no commission id.
		 */
		public function get_commission_id( $context = 'view' ) {
			return (int) $this->get_prop( 'commission_id', $context );
		}

		/**
		 * Get Order ID.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the order id is set or null if there is no order id.
		 */
		public function get_order_id( $context = 'view' ) {
			return (int) $this->get_prop( 'order_id', $context );
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
				 * Default Status
				 *
				 * @since 1.0.0
				 */
				$status = apply_filters( 'mvr_default_order_status', 'wc-pending' );
			}

			return $status;
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
		 */
		public function set_status( $new_status ) {
			$old_status = $this->get_status();
			$new_status = 'wc-' === substr( $new_status, 0, 3 ) ? substr( $new_status, 3 ) : $new_status;

			$status_exceptions = array( 'auto-draft', 'trash' );

			// If setting the status, ensure it's set to a valid status.
			if ( true === $this->object_read ) {
				// Only allow valid new status.
				if ( ! in_array( 'wc-' . $new_status, $this->get_valid_statuses(), true ) && ! in_array( $new_status, $status_exceptions, true ) ) {
					$new_status = 'pending';
				}

				// If the old status is set but unknown (e.g. draft) assume its pending for action usage.
				if ( $old_status && ! in_array( 'wc-' . $old_status, $this->get_valid_statuses(), true ) && ! in_array( $old_status, $status_exceptions, true ) ) {
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
		 * Set order id.
		 *
		 * @since 1.0.0
		 * @param String $value Order id.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_order_id( $value = null ) {
			$this->set_prop( 'order_id', (int) $value );
		}

		/**
		 * Set user id.
		 *
		 * @since 1.0.0
		 * @param String $value user id.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_user_id( $value = null ) {
			$this->set_prop( 'user_id', (int) $value );
		}

		/**
		 * Set mvr_user_id.
		 *
		 * @since 1.0.0
		 * @param String $value mvr_user_id.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_mvr_user_id( $value = null ) {
			$this->set_prop( 'mvr_user_id', (int) $value );
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
		 * Set commission id.
		 *
		 * @since 1.0.0
		 * @param String $value commission id.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_commission_id( $value = null ) {
			$this->set_prop( 'commission_id', (int) $value );
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
	}
}
