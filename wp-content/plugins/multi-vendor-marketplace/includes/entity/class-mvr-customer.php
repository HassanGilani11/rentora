<?php
/**
 * Customer Data.
 *
 * @package Multi-Vendor for WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Customer' ) ) {
	/**
	 * Order
	 *
	 * @class MVR_Customer
	 * @package Class
	 */
	class MVR_Customer extends WC_Data {

		/**
		 * Order Data array.
		 *
		 * @var Array
		 */
		protected $data = array(
			'vendor_id'     => '',
			'user_id'       => '',
			'first_name'    => '',
			'last_name'     => '',
			'company'       => '',
			'address_1'     => '',
			'address_2'     => '',
			'city'          => '',
			'state'         => '',
			'country'       => '',
			'postcode'      => '',
			'phone'         => '',
			'email'         => '',
			'date_created'  => null,
			'date_modified' => null,
			'source_id'     => '',
			'source_from'   => '',
			'created_via'   => '',
			'version'       => '',
		);

		/**
		 * Which data store to load.
		 *
		 * @var String
		 */
		protected $data_store_name = 'mvr_customer';

		/**
		 * Get the customer if ID is passed, otherwise the customer is new and empty.
		 *
		 * @since 1.0.0
		 * @param  int|object|MVR_customer $customer Customer to read.
		 */
		public function __construct( $customer = 0 ) {
			parent::__construct( $customer );

			if ( is_numeric( $customer ) && $customer > 0 ) {
				$this->set_id( $customer );
			} elseif ( $customer instanceof self ) {
				$this->set_id( $customer->get_id() );
			} elseif ( ! empty( $customer->ID ) ) {
				$this->set_id( $customer->ID );
			} else {
				$this->set_object_read( true );
			}

			$this->data_store = WC_Data_Store::load( $this->data_store_name );

			if ( $this->get_id() > 0 ) {
				$this->data_store->read( $this );
			}
		}

		/**
		 * Updates status of customer immediately.
		 *
		 * @since 1.0.0
		 * @uses MVR_Customer::set_status()
		 * @param String $new_status    Status to change the customer to. No internal mvr- prefix is required.
		 * @return Boolean
		 */
		public function update_status( $new_status ) {
			if ( ! $this->get_id() ) { // Customer must exist.
				return false;
			}

			try {
				$this->set_status( $new_status );
				$this->save();
			} catch ( Exception $e ) {
				$logger = wc_get_logger();
				$logger->error(
					sprintf(
						'Error updating status for customer #%d',
						$this->get_id()
					),
					array(
						'customer' => $this,
						'error'    => $e,
					)
				);

				return false;
			}

			return true;
		}

		/**
		 * Log an error about this customer is exception is encountered.
		 *
		 * @since 1.0.0
		 * @param Exception $e Exception object.
		 * @param String    $message Message regarding exception thrown.
		 */
		protected function handle_exception( $e, $message = 'Error' ) {
			wc_get_logger()->error(
				$message,
				array(
					'customer' => $this,
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
		 * Get's User Obj.
		 *
		 * @since 1.0.0
		 * @return WC_Order
		 */
		public function get_user() {
			$user_obj = get_user_by( 'id', $this->get_user_id() );

			if ( ! $user_obj instanceof WP_User ) {
				return false;
			}

			return $user_obj;
		}

		/**
		 * Get's Customer Orders Obj.
		 *
		 * @since 1.0.0
		 * @param Array $args Arguments.
		 * @return MVR_Order
		 */
		public function get_orders( $args = array() ) {
			$args = wp_parse_args(
				$args,
				array(
					'status'      => array_keys( wc_get_order_statuses() ),
					'vendor_id'   => $this->get_vendor()->get_id(),
					'mvr_user_id' => $this->get_id(),
				)
			);

			return mvr_get_orders( $args );
		}

		/**
		 * Get's Customer Orders Obj.
		 *
		 * @since 1.0.0
		 * @param Array $args Arguments.
		 * @return MVR_Order
		 */
		public function get_total_spend( $args = array() ) {
			$total      = 0;
			$total_tax  = 0;
			$orders_obj = $this->get_orders( $args );

			foreach ( $orders_obj->orders as $mvr_order_obj ) {
				if ( ! mvr_is_order( $mvr_order_obj ) ) {
					continue;
				}

				$order_obj = wc_get_order( $mvr_order_obj->get_order_id() );

				if ( ! is_a( $order_obj, 'WC_Order' ) ) {
					continue;
				}

				foreach ( $order_obj->get_items( 'line_item' ) as $item_id => $item ) {
					$product_obj = $item->get_product();

					if ( ! $product_obj || (int) $this->get_vendor_id() !== (int) $product_obj->get_meta( '_mvr_vendor', true ) ) {
						continue;
					}

					$taxes = $item->get_taxes();

					foreach ( $taxes['total'] as $tax_rate_id => $tax ) {
						$total_tax += (float) $tax;
					}

					$total += $order_obj->get_line_total( $item );
				}
			}

			return $total + $total_tax;
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
		 * @return String|NULL String if the user id is set or null if there is no user id.
		 */
		public function get_user_id( $context = 'view' ) {
			return (int) $this->get_prop( 'user_id', $context );
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
		 * Get first_name.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the first_name is set or null if there is no first_name.
		 */
		public function get_first_name( $context = 'view' ) {
			return $this->get_prop( 'first_name', $context );
		}

		/**
		 * Get last_name.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the last_name is set or null if there is no last_name.
		 */
		public function get_last_name( $context = 'view' ) {
			return $this->get_prop( 'last_name', $context );
		}

		/**
		 * Get company.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the company is set or null if there is no company.
		 */
		public function get_company( $context = 'view' ) {
			return $this->get_prop( 'company', $context );
		}

		/**
		 * Get address_1.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the address_1 is set or null if there is no address_1.
		 */
		public function get_address_1( $context = 'view' ) {
			return $this->get_prop( 'address_1', $context );
		}

		/**
		 * Get address_2.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the address_2 is set or null if there is no address_2.
		 */
		public function get_address_2( $context = 'view' ) {
			return $this->get_prop( 'address_2', $context );
		}

		/**
		 * Get city.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the city is set or null if there is no city.
		 */
		public function get_city( $context = 'view' ) {
			return $this->get_prop( 'city', $context );
		}

		/**
		 * Get state.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the state is set or null if there is no state.
		 */
		public function get_state( $context = 'view' ) {
			return $this->get_prop( 'state', $context );
		}

		/**
		 * Get country.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the country is set or null if there is no country.
		 */
		public function get_country( $context = 'view' ) {
			return $this->get_prop( 'country', $context );
		}

		/**
		 * Get postcode.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the postcode is set or null if there is no postcode.
		 */
		public function get_postcode( $context = 'view' ) {
			return $this->get_prop( 'postcode', $context );
		}

		/**
		 * Get phone.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the phone is set or null if there is no phone.
		 */
		public function get_phone( $context = 'view' ) {
			return $this->get_prop( 'phone', $context );
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
		 * Get source_id.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the source_id is set or null if there is no source_id.
		 */
		public function get_source_id( $context = 'view' ) {
			return $this->get_prop( 'source_id', $context );
		}

		/**
		 * Get source_from.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the source_from is set or null if there is no source_from.
		 */
		public function get_source_from( $context = 'view' ) {
			return $this->get_prop( 'source_from', $context );
		}

		/**
		 * Get created_via.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the created_via is set or null if there is no created_via.
		 */
		public function get_created_via( $context = 'view' ) {
			return $this->get_prop( 'created_via', $context );
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
		 * @param String $value Status to change the vendor to. No internal mvr- prefix is required.
		 */
		public function set_status( $value ) {
			$this->set_prop( 'status', $value );
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
		 * Set first_name.
		 *
		 * @since 1.0.0
		 * @param String $value first_name.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_first_name( $value = null ) {
			$this->set_prop( 'first_name', $value );
		}

		/**
		 * Set last_name.
		 *
		 * @since 1.0.0
		 * @param String $value last_name.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_last_name( $value = null ) {
			$this->set_prop( 'last_name', $value );
		}

		/**
		 * Set company.
		 *
		 * @since 1.0.0
		 * @param String $value company.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_company( $value = null ) {
			$this->set_prop( 'company', $value );
		}

		/**
		 * Set address_1.
		 *
		 * @since 1.0.0
		 * @param String $value address_1.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_address_1( $value = null ) {
			$this->set_prop( 'address_1', $value );
		}

		/**
		 * Set address_2.
		 *
		 * @since 1.0.0
		 * @param String $value address_2.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_address_2( $value = null ) {
			$this->set_prop( 'address_2', $value );
		}

		/**
		 * Set city.
		 *
		 * @since 1.0.0
		 * @param String $value city.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_city( $value = null ) {
			$this->set_prop( 'city', $value );
		}

		/**
		 * Set state.
		 *
		 * @since 1.0.0
		 * @param String $value state.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_state( $value = null ) {
			$this->set_prop( 'state', $value );
		}

		/**
		 * Set country.
		 *
		 * @since 1.0.0
		 * @param String $value country.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_country( $value = null ) {
			$this->set_prop( 'country', $value );
		}

		/**
		 * Set postcode.
		 *
		 * @since 1.0.0
		 * @param String $value postcode.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_postcode( $value = null ) {
			$this->set_prop( 'postcode', $value );
		}

		/**
		 * Set phone.
		 *
		 * @since 1.0.0
		 * @param String $value phone.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_phone( $value = null ) {
			$this->set_prop( 'phone', $value );
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
		 * Set created_via.
		 *
		 * @since 1.0.0
		 * @param String $value created_via.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_created_via( $value = null ) {
			$this->set_prop( 'created_via', $value );
		}
	}
}
