<?php
/**
 * Enquiry Data.
 *
 * @package Multi-Vendor for WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Enquiry' ) ) {
	/**
	 * Enquiry
	 *
	 * @class MVR_Enquiry
	 * @package Class
	 */
	class MVR_Enquiry extends WC_Data {

		/**
		 * Enquiry Data array.
		 *
		 * @var Array
		 */
		protected $data = array(
			'vendor_id'      => '',
			'date_created'   => '',
			'author_id'      => '',
			'customer_id'    => '',
			'customer_name'  => '',
			'customer_email' => '',
			'message'        => '',
			'reply'          => '',
			'source_id'      => '',
			'source_from'    => '',
			'status'         => '',
			'date_modified'  => '',
			'version'        => '',
		);

		/**
		 * Which data store to load.
		 *
		 * @var String
		 */
		protected $data_store_name = 'mvr_enquiry';

		/**
		 * Get the Enquiry if ID is passed, otherwise the enquiry is new and empty.
		 *
		 * @since 1.0.0
		 * @param  int|object|MVR_Enquiry $enquiry enquiry to read.
		 */
		public function __construct( $enquiry = 0 ) {
			parent::__construct( $enquiry );

			if ( is_numeric( $enquiry ) && $enquiry > 0 ) {
				$this->set_id( $enquiry );
			} elseif ( $enquiry instanceof self ) {
				$this->set_id( $enquiry->get_id() );
			} elseif ( ! empty( $enquiry->ID ) ) {
				$this->set_id( $enquiry->ID );
			} else {
				$this->set_object_read( true );
			}

			$this->data_store = WC_Data_Store::load( $this->data_store_name );

			if ( $this->get_id() > 0 ) {
				$this->data_store->read( $this );
			}
		}

		/**
		 * Get's the URL to edit the vendor in the backend.
		 *
		 * @since 1.0.0
		 * @return String
		 */
		public function get_admin_edit_url() {
			/**
			 * Edit Vendor URL
			 *
			 * @since 1.0.0
			 */
			return apply_filters( 'mvr_get_admin_edit_enquiry_url', admin_url( 'admin.php?page=mvr_enquiry' ), $this );
		}

		/**
		 * Log an error about this enquiry is exception is encountered.
		 *
		 * @since 1.0.0
		 * @param Exception $e Exception object.
		 * @param String    $enquiry Enquiry regarding exception thrown.
		 */
		protected function handle_exception( $e, $enquiry = 'Error' ) {
			wc_get_logger()->error(
				$enquiry,
				array(
					'enquiry' => $this,
					'error'   => $e,
				)
			);
		}

		/**
		 * Get all valid statuses for this enquiry
		 *
		 * @since 1.0.0
		 * @return Array Internal status keys e.g. 'mvr-active'
		 */
		public function get_valid_statuses() {
			return array_keys( mvr_get_enquiry_statuses() );
		}

		/**
		 * Updates status of enquiry immediately.
		 *
		 * @since 1.0.0
		 * @uses MVR_Enquiry::set_status()
		 * @param String $new_status    Status to change the enquiry to. No internal mvr- prefix is required.
		 * @return Boolean
		 */
		public function update_status( $new_status ) {
			if ( ! $this->get_id() ) { // Enquiry must exist.
				return false;
			}

			try {
				$this->set_status( $new_status );
				$this->save();
			} catch ( Exception $e ) {
				$logger = wc_get_logger();
				$logger->error(
					sprintf(
						'Error updating status for enquiry #%d',
						$this->get_id()
					),
					array(
						'enquiry' => $this,
						'error'   => $e,
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
		 * @return String|NULL String if the vendor id is set or null if there is no vendor id.
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
		 * Get Author id.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the author id is set or null if there is no author id.
		 */
		public function get_author_id( $context = 'view' ) {
			return $this->get_prop( 'author_id', $context );
		}

		/**
		 * Get Customer id.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the Customer id is set or null if there is no Customer id.
		 */
		public function get_customer_id( $context = 'view' ) {
			return $this->get_prop( 'customer_id', $context );
		}

		/**
		 * Get Customer name.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the Customer name is set or null if there is no Customer name.
		 */
		public function get_customer_name( $context = 'view' ) {
			return $this->get_prop( 'customer_name', $context );
		}

		/**
		 * Get Customer email.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the Customer email is set or null if there is no Customer email.
		 */
		public function get_customer_email( $context = 'view' ) {
			return $this->get_prop( 'customer_email', $context );
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
		 * Get reply.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the reply is set or null if there is no reply.
		 */
		public function get_reply( $context = 'view' ) {
			return $this->get_prop( 'reply', $context );
		}

		/**
		 * Get status.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the Status is set or null if there is no Status.
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
		 * Set author id.
		 *
		 * @since 1.0.0
		 * @param String $value Author ID.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_author_id( $value = null ) {
			$this->set_prop( 'author_id', $value );
		}

		/**
		 * Set customer id.
		 *
		 * @since 1.0.0
		 * @param String $value Customer ID.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_customer_id( $value = null ) {
			$this->set_prop( 'customer_id', $value );
		}

		/**
		 * Set customer name.
		 *
		 * @since 1.0.0
		 * @param String $value Customer Name.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_customer_name( $value = null ) {
			$this->set_prop( 'customer_name', $value );
		}

		/**
		 * Set customer email.
		 *
		 * @since 1.0.0
		 * @param String $value Customer email.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_customer_email( $value = null ) {
			$this->set_prop( 'customer_email', $value );
		}

		/**
		 * Set Message.
		 *
		 * @since 1.0.0
		 * @param String $value Message.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_message( $value = null ) {
			$this->set_prop( 'message', $value );
		}

		/**
		 * Set reply.
		 *
		 * @since 1.0.0
		 * @param String $value reply.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_reply( $value = null ) {
			$this->set_prop( 'reply', $value );
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
