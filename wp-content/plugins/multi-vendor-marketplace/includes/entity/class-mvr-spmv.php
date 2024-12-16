<?php
/**
 * Single Product Multi Vendor Data.
 *
 * @package Multi-Vendor for WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_SPMV' ) ) {
	/**
	 * Single Product Multi Vendor
	 *
	 * @class MVR_SPMV
	 * @package Class
	 */
	class MVR_SPMV extends WC_Data {

		/**
		 * Singe Product Multi Vendor Data array.
		 *
		 * @var Array
		 */
		protected $data = array(
			'map_id'       => '',
			'product_id'   => '',
			'vendor_id'    => '',
			'date_created' => null,
			'parent_id'    => '',
			'version'      => '',
		);

		/**
		 * Which data store to load.
		 *
		 * @var String
		 */
		protected $data_store_name = 'mvr_spmv';

		/**
		 * Get the SPMV if ID is passed, otherwise the SPMV is new and empty.
		 *
		 * @since 1.0.0
		 * @param  int|object|MVR_SPMV $spmv Singe Product Multi Vendor to read.
		 */
		public function __construct( $spmv = 0 ) {
			parent::__construct( $spmv );

			if ( is_numeric( $spmv ) && $spmv > 0 ) {
				$this->set_id( $spmv );
			} elseif ( $spmv instanceof self ) {
				$this->set_id( $spmv->get_id() );
			} elseif ( ! empty( $spmv->ID ) ) {
				$this->set_id( $spmv->ID );
			} else {
				$this->set_object_read( true );
			}

			$this->data_store = WC_Data_Store::load( $this->data_store_name );

			if ( $this->get_id() > 0 ) {
				$this->data_store->read( $this );
			}
		}

		/**
		 * Log an error about this SPMV is exception is encountered.
		 *
		 * @since 1.0.0
		 * @param Exception $e Exception object.
		 * @param String    $spmv Single Product Multi Vendor regarding exception thrown.
		 */
		protected function handle_exception( $e, $spmv = 'Error' ) {
			wc_get_logger()->error(
				$spmv,
				array(
					'spmv'  => $this,
					'error' => $e,
				)
			);
		}

		/*
		|--------------------------------------------------------------------------
		| Getters
		|--------------------------------------------------------------------------
		 */

		/**
		 * Get Map ID.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the map id is set or null if there is no map id.
		 */
		public function get_map_id( $context = 'view' ) {
			return $this->get_prop( 'map_id', $context );
		}

		/**
		 * Get product ID.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the product id is set or null if there is no product id.
		 */
		public function get_product_id( $context = 'view' ) {
			return $this->get_prop( 'product_id', $context );
		}

		/**
		 * Get Vendor ID.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the vendor id is set or null if there is no vendor id.
		 */
		public function get_vendor_id( $context = 'view' ) {
			return $this->get_prop( 'vendor_id', $context );
		}

		/**
		 * Get parent ID.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the parent id is set or null if there is no parent id.
		 */
		public function get_parent_id( $context = 'view' ) {
			return $this->get_prop( 'parent_id', $context );
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
		 * Set Map id.
		 *
		 * @since 1.0.0
		 * @param String $value Map ID.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_map_id( $value = null ) {
			$this->set_prop( 'map_id', $value );
		}

		/**
		 * Set vendor id.
		 *
		 * @since 1.0.0
		 * @param String $value Vendor id.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_vendor_id( $value = null ) {
			$this->set_prop( 'vendor_id', $value );
		}

		/**
		 * Set Product id.
		 *
		 * @since 1.0.0
		 * @param String $value product id.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_product_id( $value = null ) {
			$this->set_prop( 'product_id', $value );
		}

		/**
		 * Set parent id.
		 *
		 * @since 1.0.0
		 * @param String $value parent ID.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_parent_id( $value = null ) {
			$this->set_prop( 'parent_id', $value );
		}
	}
}
