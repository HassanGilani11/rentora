<?php
/**
 * Notification Data.
 *
 * @package Multi-Vendor for WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Notification' ) ) {
	/**
	 * Notification
	 *
	 * @class MVR_Notification
	 * @package Class
	 */
	class MVR_Notification extends WC_Data {

		/**
		 * Notification Data array.
		 *
		 * @var Array
		 */
		protected $data = array(
			'vendor_id'     => '',
			'date_created'  => '',
			'message'       => '',
			'source_id'     => '',
			'source_from'   => '',
			'to'            => '',
			'status'        => '',
			'date_modified' => '',
			'version'       => '',
		);

		/**
		 * Which data store to load.
		 *
		 * @var String
		 */
		protected $data_store_name = 'mvr_notification';

		/**
		 * Get the Notification if ID is passed, otherwise the notification is new and empty.
		 *
		 * @since 1.0.0
		 * @param int|object|MVR_Notification $notification notification to read.
		 */
		public function __construct( $notification = 0 ) {
			parent::__construct( $notification );

			if ( is_numeric( $notification ) && $notification > 0 ) {
				$this->set_id( $notification );
			} elseif ( $notification instanceof self ) {
				$this->set_id( $notification->get_id() );
			} elseif ( ! empty( $notification->ID ) ) {
				$this->set_id( $notification->ID );
			} else {
				$this->set_object_read( true );
			}

			$this->data_store = WC_Data_Store::load( $this->data_store_name );

			if ( $this->get_id() > 0 ) {
				$this->data_store->read( $this );
			}
		}

		/**
		 * Log an error about this notification is exception is encountered.
		 *
		 * @since 1.0.0
		 * @param Exception $e Exception object.
		 * @param String    $notification Notification regarding exception thrown.
		 */
		protected function handle_exception( $e, $notification = 'Error' ) {
			wc_get_logger()->error(
				$notification,
				array(
					'notification' => $this,
					'error'        => $e,
				)
			);
		}

		/**
		 * Get all valid statuses for this notification
		 *
		 * @since 1.0.0
		 * @return Array Internal status keys e.g. 'read , unread'
		 */
		public function get_valid_statuses() {
			return array_keys( mvr_get_notification_statuses() );
		}

		/**
		 * Updates status of notification immediately.
		 *
		 * @since 1.0.0
		 * @uses MVR_Notification::set_status()
		 * @param String $new_status Status to change the notification.
		 * @return Boolean
		 */
		public function update_status( $new_status ) {
			if ( ! $this->get_id() ) { // Notification must exist.
				return false;
			}

			try {
				$this->set_status( $new_status );
				$this->save();
			} catch ( Exception $e ) {
				$logger = wc_get_logger();
				$logger->error(
					sprintf(
						'Error updating status for notification #%d',
						$this->get_id()
					),
					array(
						'notification' => $this,
						'error'        => $e,
					)
				);

				return false;
			}

			return true;
		}

		/*
		|--------------------------------------------------------------------------
		| Getters
		|--------------------------------------------------------------------------
		 */

		/**
		 * Get Vendor ID.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the vendor ids is set or null if there is no vendor ids.
		 */
		public function get_vendor_id( $context = 'view' ) {
			return (int) $this->get_prop( 'vendor_id', $context );
		}

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
		 * Get message.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the message is set or null if there is no message.
		 */
		public function get_message( $context = 'view' ) {
			return $this->get_prop( 'message', $context );
		}

		/**
		 * Get Source ID.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the order id is set or null if there is no order id.
		 */
		public function get_source_id( $context = 'view' ) {
			return $this->get_prop( 'source_id', $context );
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
		 * Get to.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the to is set or null if there is no to.
		 */
		public function get_to( $context = 'view' ) {
			return $this->get_prop( 'to', $context );
		}

		/**
		 * Get status.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the status is set or null if there is no status.
		 */
		public function get_status( $context = 'view' ) {
			return $this->get_prop( 'status', $context );
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
		 * Set message.
		 *
		 * @since 1.0.0
		 * @param String $value message.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_message( $value = null ) {
			$this->set_prop( 'message', $value );
		}

		/**
		 * Set Vendor id.
		 *
		 * @since 1.0.0
		 * @param String $value Vendor ID.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_vendor_id( $value = null ) {
			$this->set_prop( 'vendor_id', $value );
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
		 * Set to.
		 *
		 * @since 1.0.0
		 * @param String $value to.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_to( $value = null ) {
			$this->set_prop( 'to', $value );
		}

		/**
		 * Set status.
		 *
		 * @since 1.0.0
		 * @param String $value status.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_status( $value = null ) {
			$this->set_prop( 'status', $value );
		}
	}
}
