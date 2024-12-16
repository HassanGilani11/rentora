<?php
/**
 * Commission Data.
 *
 * @package Multi-Vendor for WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Commission' ) ) {
	/**
	 * Commission
	 *
	 * @class MVR_Commission
	 * @package Class
	 */
	class MVR_Commission extends WC_Data {

		/**
		 * Stores data about status changes so relevant hooks can be fired.
		 *
		 * @var bool|array
		 */
		protected $status_transition = false;

		/**
		 * Commission Data array.
		 *
		 * @var Array
		 */
		protected $data = array(
			'vendor_id'     => '',
			'date_created'  => null,
			'amount'        => '',
			'vendor_amount' => '',
			'currency'      => '',
			'status'        => '',
			'created_via'   => '',
			'source_id'     => '',
			'source_from'   => '',
			'date_modified' => null,
			'settings'      => '',
			'products'      => '',
			'parent_id'     => '',
			'version'       => '',
		);

		/**
		 * Which data store to load.
		 *
		 * @var String
		 */
		protected $data_store_name = 'mvr_commission';

		/**
		 * Get the Commission if ID is passed, otherwise the commission is new and empty.
		 *
		 * @since 1.0.0
		 * @param  int|object|MVR_Commission $commission Commission to read.
		 */
		public function __construct( $commission = 0 ) {
			parent::__construct( $commission );

			if ( is_numeric( $commission ) && $commission > 0 ) {
				$this->set_id( $commission );
			} elseif ( $commission instanceof self ) {
				$this->set_id( $commission->get_id() );
			} elseif ( ! empty( $commission->ID ) ) {
				$this->set_id( $commission->ID );
			} else {
				$this->set_object_read( true );
			}

			$this->data_store = WC_Data_Store::load( $this->data_store_name );

			if ( $this->get_id() > 0 ) {
				$this->data_store->read( $this );
			}
		}

		/**
		 * Get all valid statuses for this commission
		 *
		 * @since 1.0.0
		 * @return Array Internal status keys e.g. 'mvr-paid'
		 */
		public function get_valid_statuses() {
			return array_keys( mvr_get_commission_statuses() );
		}

		/**
		 * Updates status of commission immediately.
		 *
		 * @since 1.0.0
		 * @uses MVR_Commission::set_status()
		 * @param String $new_status    Status to change the commission to. No internal mvr- prefix is required.
		 * @return Boolean
		 */
		public function update_status( $new_status ) {
			if ( ! $this->get_id() ) { // Commission must exist.
				return false;
			}

			try {
				$this->set_status( $new_status );
				$this->save();
			} catch ( Exception $e ) {
				$logger = wc_get_logger();
				$logger->error(
					sprintf(
						'Error updating status for commission #%d',
						$this->get_id()
					),
					array(
						'commission' => $this,
						'error'      => $e,
					)
				);

				return false;
			}

			return true;
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
					 * Commission status updated to.
					 *
					 * @since 1.0.0
					 */
					do_action( 'mvr_commission_status_' . $status_transition['to'], $this );

					if ( ! empty( $status_transition['from'] ) && $status_transition['from'] !== $status_transition['to'] ) {
						/* translators: 1: old status 2: new status */
						$transition_note = sprintf( __( 'Status changed from <b>%1$s</b> to <b>%2$s</b>.', 'multi-vendor-marketplace' ), mvr_get_commission_status_name( $status_transition['from'] ), mvr_get_commission_status_name( $status_transition['to'] ) );

						/**
						 * Commission status updated from and to.
						 *
						 * @since 1.0.0
						 */
						do_action( 'mvr_commission_status_' . $status_transition['from'] . '_to_' . $status_transition['to'], $this );

						/**
						 * Commission status changed.
						 *
						 * @since 1.0.0
						 */
						do_action( 'mvr_commission_status_changed', $status_transition['from'], $status_transition['to'], $this );

						/**
						 * Commission status updated.
						 *
						 * @since 1.0.0
						 */
						do_action( 'mvr_commission_status_updated', $status_transition['to'], $status_transition['manual'], $this );
					}
				} catch ( Exception $e ) {
					$this->handle_exception( $e, sprintf( 'Status transition of commission #%d errored!', $this->get_id() ) );
				}
			}
		}

		/**
		 * Log an error about this commission is exception is encountered.
		 *
		 * @since 1.0.0
		 * @param Exception $e Exception object.
		 * @param String    $message Message regarding exception thrown.
		 */
		protected function handle_exception( $e, $message = 'Error' ) {
			wc_get_logger()->error(
				$message,
				array(
					'commission' => $this,
					'error'      => $e,
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
		 * Checks the commission status against a passed in status.
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
			return apply_filters( 'mvr_commission_has_status', ( is_array( $status ) && in_array( $this->get_status(), $status, true ) ) || $this->get_status() === $status, $this, $status );
		}

		/*
		|--------------------------------------------------------------------------
		| URLs and Endpoints
		|--------------------------------------------------------------------------
		 */

		/**
		 * Get's the URL to edit the commission in the backend.
		 *
		 * @since 1.0.0
		 * @return String
		 */
		public function get_admin_edit_url() {
			/**
			 * Edit Commission URL.
			 *
			 * @since 1.0.0
			 */
			return apply_filters(
				'mvr_get_admin_edit_commission_url',
				wp_nonce_url(
					mvr_get_commission_page_url(
						array(
							'action' => 'view',
							'id'     => $this->get_id(),
						)
					),
					'mvr-edit-commission',
					'_mvr_nonce'
				),
				$this
			);
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
				$order_obj = wc_get_order( $this->get_source_id() );

				if ( ! is_a( $order_obj, 'WC_Order' ) ) {
					return false;
				}

				return $order_obj;
			}

			return false;
		}

		/**
		 * Get's withdraw Obj.
		 *
		 * @since 1.0.0
		 * @return MVR_Withdraw
		 */
		public function get_withdraw() {
			if ( 'withdraw' === $this->get_source_from() ) {
				$withdraw_obj = mvr_get_withdraw( $this->get_source_id() );

				if ( ! mvr_is_withdraw( $withdraw_obj ) ) {
					return false;
				}

				return $withdraw_obj;
			}

			return false;
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
		 * @return String|NULL String if the source id is set or null if there is no source id.
		 */
		public function get_source_id( $context = 'view' ) {
			return $this->get_prop( 'source_id', $context );
		}

		/**
		 * Get Source from.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the source from is set or null if there is no source from.
		 */
		public function get_source_from( $context = 'view' ) {
			return $this->get_prop( 'source_from', $context );
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
				$status = apply_filters( 'mvr_default_commission_status', 'pending' );
			}

			return $status;
		}

		/**
		 * Get commission amount.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the commission amount is set or null if there is no commission amount.
		 */
		public function get_amount( $context = 'view' ) {
			return $this->get_prop( 'amount', $context );
		}

		/**
		 * Get vendor earning amount.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the vendor amount is set or null if there is no vendor amount.
		 */
		public function get_vendor_amount( $context = 'view' ) {
			return $this->get_prop( 'vendor_amount', $context );
		}

		/**
		 * Get commission settings.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the commission settings is set or null if there is no commission settings.
		 */
		public function get_settings( $context = 'view' ) {
			return $this->get_prop( 'settings', $context );
		}

		/**
		 * Get commission products.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the commission products is set or null if there is no commission products.
		 */
		public function get_products( $context = 'view' ) {
			return $this->get_prop( 'products', $context );
		}

		/**
		 * Get parent id.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the commission parent id is set or null if there is no commission parent id.
		 */
		public function get_parent_id( $context = 'view' ) {
			return $this->get_prop( 'parent_id', $context );
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
						 * When commission status has been manually edited.
						 *
						 * @since 1.0
						 */
						do_action( 'mvr_commission_edit_status', $this->get_id(), $new_status );
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
		 * Set source id.
		 *
		 * @since 1.0.0
		 * @param String $value Source id.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_source_id( $value = null ) {
			$this->set_prop( 'source_id', (int) $value );
		}

		/**
		 * Set source from.
		 *
		 * @since 1.0.0
		 * @param String $value Source from.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_source_from( $value = null ) {
			$this->set_prop( 'source_from', $value );
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
		 * Set vendor amount.
		 *
		 * @since 1.0.0
		 * @param String $value vendor amount.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_vendor_amount( $value = null ) {
			$this->set_prop( 'vendor_amount', (float) wc_format_decimal( $value, false, true ) );
		}

		/**
		 * Set settings.
		 *
		 * @since 1.0.0
		 * @param String $value Commission settings.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_settings( $value = null ) {
			$this->set_prop( 'settings', $value );
		}

		/**
		 * Set products.
		 *
		 * @since 1.0.0
		 * @param String $value Commission products.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_products( $value = null ) {
			$this->set_prop( 'products', $value );
		}

		/**
		 * Set Parent ID.
		 *
		 * @since 1.0.0
		 * @param String $value Commission parent id.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_parent_id( $value = null ) {
			$this->set_prop( 'parent_id', (int) $value );
		}
	}
}
