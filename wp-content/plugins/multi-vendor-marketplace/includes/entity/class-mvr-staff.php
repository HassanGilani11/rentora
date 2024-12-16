<?php
/**
 * Staff Data.
 *
 * @package Multi-Vendor for WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Staff' ) ) {
	/**
	 * Staff
	 *
	 * @class MVR_Staff
	 * @package Class
	 */
	class MVR_Staff extends WC_Data {

		/**
		 * Staff Data array.
		 *
		 * @var Array
		 */
		protected $data = array(
			'user_id'                        => '',
			'status'                         => '',
			'version'                        => '',
			'date_created'                   => null,
			'date_modified'                  => null,
			'name'                           => '',
			'description'                    => '',
			'email'                          => '',
			'vendor_id'                      => '',
			'enable_product_management'      => '',
			'product_creation'               => '',
			'product_modification'           => '',
			'published_product_modification' => '',
			'manage_inventory'               => '',
			'product_deletion'               => '',
			'enable_order_management'        => '',
			'order_status_modification'      => '',
			'commission_info_display'        => '',
			'enable_coupon_management'       => '',
			'coupon_creation'                => '',
			'coupon_modification'            => '',
			'published_coupon_modification'  => '',
			'coupon_deletion'                => '',
			'enable_commission_withdraw'     => '',
			'commission_transaction'         => '',
			'commission_transaction_info'    => '',
			'average_rating'                 => 0,
			'review_count'                   => 0,
		);

		/**
		 * Stores meta in cache for future reads.
		 *
		 * A group must be set to to enable caching.
		 *
		 * @var String
		 */
		protected $cache_group = 'mvr_staffs';

		/**
		 * Which data store to load.
		 *
		 * @var String
		 */
		protected $data_store_name = 'mvr_staff';

		/**
		 * This is the name of this object type.
		 *
		 * @var String
		 */
		protected $object_type = 'mvr_staff';

		/**
		 * Get the Staff if ID is passed, otherwise the Staff is new and empty.
		 *
		 * @since 1.0.0
		 * @param  int|object|MVR_Staff $staff Staff to read.
		 */
		public function __construct( $staff = 0 ) {
			parent::__construct( $staff );

			if ( is_numeric( $staff ) && $staff > 0 ) {
				$this->set_id( $staff );
			} elseif ( $staff instanceof self ) {
				$this->set_id( $staff->get_id() );
			} elseif ( ! empty( $staff->ID ) ) {
				$this->set_id( $staff->ID );
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
		 * Allow capability.
		 *
		 * @since 1.0.0
		 * @param MVR_Vendor $vendor_obj Vendor_obj.
		 * @return Boolean
		 */
		public function allow_capability( $vendor_obj = '' ) {
			if ( ! $vendor_obj ) {
				$vendor_obj = $this->get_vendor();
			}

			if ( $this->allow_product_management( $vendor_obj ) || $this->allow_order_management( $vendor_obj ) || $this->allow_coupon_management( $vendor_obj ) ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Allow Product Management.
		 *
		 * @since 1.0.0
		 * @param MVR_Vendor $vendor_obj Vendor_obj.
		 * @return Boolean
		 */
		public function allow_product_management( $vendor_obj = '' ) {
			if ( ! $vendor_obj ) {
				$vendor_obj = $this->get_vendor();
			}

			if ( ! mvr_is_vendor( $vendor_obj ) ) {
				return false;
			}

			if ( 'yes' === $vendor_obj->get_enable_product_management() ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Allow Order Management.
		 *
		 * @since 1.0.0
		 * @param MVR_Vendor $vendor_obj Vendor_obj.
		 * @return Boolean
		 */
		public function allow_order_management( $vendor_obj = '' ) {
			if ( ! $vendor_obj ) {
				$vendor_obj = $this->get_vendor();
			}

			if ( ! mvr_is_vendor( $vendor_obj ) ) {
				return false;
			}

			if ( 'yes' === $vendor_obj->get_enable_order_management() ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Allow Coupon Management.
		 *
		 * @since 1.0.0
		 * @param MVR_Vendor $vendor_obj Vendor_obj.
		 * @return Boolean
		 */
		public function allow_coupon_management( $vendor_obj = '' ) {
			if ( ! $vendor_obj ) {
				$vendor_obj = $this->get_vendor();
			}

			if ( ! mvr_is_vendor( $vendor_obj ) ) {
				return false;
			}

			if ( 'yes' === $vendor_obj->get_enable_coupon_management() ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Get all valid statuses for this Staff
		 *
		 * @since 1.0.0
		 * @return Array Internal status keys e.g. 'mvr-active'
		 */
		public function get_valid_statuses() {
			return array_keys( mvr_get_staff_statuses() );
		}

		/**
		 * Updates status of Staff immediately.
		 *
		 * @since 1.0.0
		 * @uses MVR_Staff::set_status()
		 * @param String $new_status    Status to change the Staff to. No internal mvr- prefix is required.
		 * @return Boolean
		 */
		public function update_status( $new_status ) {
			if ( ! $this->get_id() ) {
				return false;
			}

			try {
				$this->set_status( $new_status );
				$this->save();
			} catch ( Exception $e ) {
				$logger = wc_get_logger();
				$logger->error(
					sprintf(
						'Error updating status for Staff #%d',
						$this->get_id()
					),
					array(
						'staff' => $this,
						'error' => $e,
					)
				);

				return false;
			}

			return true;
		}

		/**
		 * Log an error about this Staff is exception is encountered.
		 *
		 * @since 1.0.0
		 * @param Exception $e Exception object.
		 * @param String    $message Message regarding exception thrown.
		 */
		protected function handle_exception( $e, $message = 'Error' ) {
			wc_get_logger()->error(
				$message,
				array(
					'staff' => $this,
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
		 * Checks the Staff status against a passed in status.
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
			return apply_filters( 'mvr_staff_has_status', ( is_array( $status ) && in_array( $this->get_status(), $status, true ) ) || $this->get_status() === $status, $this, $status );
		}

		/*
		|--------------------------------------------------------------------------
		| URLs and Endpoints
		|--------------------------------------------------------------------------
		 */

		/**
		 * Get's the URL to edit the Staff in the backend.
		 *
		 * @since 1.0.0
		 * @return String
		 */
		public function get_admin_edit_url() {
			/**
			 * Edit Staff URL
			 *
			 * @since 1.0.0
			 */
			return apply_filters( 'mvr_get_admin_edit_staff_url', get_admin_url( null, 'post.php?post=' . $this->get_id() . '&action=edit' ), $this );
		}

		/*
		|--------------------------------------------------------------------------
		| Getters
		|--------------------------------------------------------------------------
		 */

		/**
		 * Get Staff user id
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return Integer
		 */
		public function get_user_id( $context = 'view' ) {
			return $this->get_prop( 'user_id', $context );
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
		 * Get Description.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the description is set or null if there is no description.
		 */
		public function get_description( $context = 'view' ) {
			return $this->get_prop( 'description', $context );
		}

		/**
		 * Get name.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the name is set or null if there is no name.
		 */
		public function get_name( $context = 'view' ) {
			return $this->get_prop( 'name', $context );
		}

		/**
		 * Get Email.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the email is set or null if there is no email.
		 */
		public function get_email( $context = 'view' ) {
			return $this->get_prop( 'email', $context );
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
		 * Get Enable Product Management.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the enable_product_management is set or null if there is no enable_product_management.
		 */
		public function get_enable_product_management( $context = 'view' ) {
			return $this->get_prop( 'enable_product_management', $context );
		}

		/**
		 * Get Product Creation.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the product_creation is set or null if there is no product_creation.
		 */
		public function get_product_creation( $context = 'view' ) {
			return $this->get_prop( 'product_creation', $context );
		}

		/**
		 * Get Product Modification.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the product_modification is set or null if there is no product_modification.
		 */
		public function get_product_modification( $context = 'view' ) {
			return $this->get_prop( 'product_modification', $context );
		}

		/**
		 * Get Published Product Modification.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the published_product_modification is set or null if there is no published_product_modification.
		 */
		public function get_published_product_modification( $context = 'view' ) {
			return $this->get_prop( 'published_product_modification', $context );
		}

		/**
		 * Get Manage Inventory.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the manage_inventory is set or null if there is no manage_inventory.
		 */
		public function get_manage_inventory( $context = 'view' ) {
			return $this->get_prop( 'manage_inventory', $context );
		}

		/**
		 * Get Product Deletion.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the product_deletion is set or null if there is no product_deletion.
		 */
		public function get_product_deletion( $context = 'view' ) {
			return $this->get_prop( 'product_deletion', $context );
		}

		/**
		 * Get enable_order_management.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the enable_order_management is set or null if there is no enable_order_management.
		 */
		public function get_enable_order_management( $context = 'view' ) {
			return $this->get_prop( 'enable_order_management', $context );
		}

		/**
		 * Get Instagram.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the order_status_modification is set or null if there is no order_status_modification.
		 */
		public function get_order_status_modification( $context = 'view' ) {
			return $this->get_prop( 'order_status_modification', $context );
		}

		/**
		 * Get Commission Info Display.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the commission_info_display is set or null if there is no commission_info_display.
		 */
		public function get_commission_info_display( $context = 'view' ) {
			return $this->get_prop( 'commission_info_display', $context );
		}

		/**
		 * Get Enable Coupon Management.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the enable_coupon_management is set or null if there is no enable_coupon_management.
		 */
		public function get_enable_coupon_management( $context = 'view' ) {
			return $this->get_prop( 'enable_coupon_management', $context );
		}

		/**
		 * Get Coupon Creation.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the coupon_creation is set or null if there is no coupon_creation.
		 */
		public function get_coupon_creation( $context = 'view' ) {
			return $this->get_prop( 'coupon_creation', $context );
		}

		/**
		 * Get published Coupon modification.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the published coupon modification is set or null if there is no published coupon modification.
		 */
		public function get_published_coupon_modification( $context = 'view' ) {
			return $this->get_prop( 'published_coupon_modification', $context );
		}

		/**
		 * Get Coupon Modification.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the coupon_modification is set or null if there is no coupon_modification.
		 */
		public function get_coupon_modification( $context = 'view' ) {
			return $this->get_prop( 'coupon_modification', $context );
		}

		/**
		 * Get Coupon Deletion.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the coupon deletion is set or null if there is no coupon deletion.
		 */
		public function get_coupon_deletion( $context = 'view' ) {
			return $this->get_prop( 'coupon_deletion', $context );
		}

		/**
		 * Return the Staff statuses without mvr- internal prefix.
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
				$status = apply_filters( 'mvr_default_staff_status', 'active' );
			}

			return $status;
		}

		/*
		|--------------------------------------------------------------------------
		| Setters
		|--------------------------------------------------------------------------
		|
		| Functions for setting Staff data. These should not update anything in the
		| database itself and should only change what is stored in the class
		| object.
		 */

		/**
		 * Set status.
		 *
		 * @since 1.0.0
		 * @param String $new_status Status to change the Staff to. No internal mvr- prefix is required.
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
					$new_status = 'active';
				}

				// If the old status is set but unknown (e.g. draft) assume its active for action usage.
				if ( $old_status && ! in_array( 'mvr-' . $old_status, $this->get_valid_statuses(), true ) && ! in_array( $old_status, $status_exceptions, true ) ) {
					$old_status = 'active';
				}
			}

			$this->set_prop( 'status', $new_status );

			return array(
				'from' => $old_status,
				'to'   => $new_status,
			);
		}

		/**
		 * Set User ID.
		 *
		 * @since 1.0.0
		 * @param Integer $value Value to set.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_user_id( $value ) {
			$this->set_prop( 'user_id', $value );
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
		 * Set description.
		 *
		 * @since 1.0.0
		 * @param String $value description.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_description( $value = null ) {
			$this->set_prop( 'description', $value );
		}

		/**
		 * Set Staff name.
		 *
		 * @since 1.0.0
		 * @param String $value name.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_name( $value = null ) {
			$this->set_prop( 'name', $value );
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
		 * Set vendor id.
		 *
		 * @since 1.0.0
		 * @param String $value email.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_vendor_id( $value = null ) {
			$this->set_prop( 'vendor_id', $value );
		}

		/**
		 * Set enable product management.
		 *
		 * @since 1.0.0
		 * @param String $value enable product management.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_enable_product_management( $value = null ) {
			$this->set_prop( 'enable_product_management', $value );
		}

		/**
		 * Set product creation.
		 *
		 * @since 1.0.0
		 * @param String $value instant product submission.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_product_creation( $value = null ) {
			$this->set_prop( 'product_creation', $value );
		}

		/**
		 * Set product modification.
		 *
		 * @since 1.0.0
		 * @param String $value product modification.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_product_modification( $value = null ) {
			$this->set_prop( 'product_modification', $value );
		}

		/**
		 * Set published product modification.
		 *
		 * @since 1.0.0
		 * @param String $value published product modification.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_published_product_modification( $value = null ) {
			$this->set_prop( 'published_product_modification', $value );
		}

		/**
		 * Set manage inventory.
		 *
		 * @since 1.0.0
		 * @param String $value manage inventory.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_manage_inventory( $value = null ) {
			$this->set_prop( 'manage_inventory', $value );
		}

		/**
		 * Set product deletion.
		 *
		 * @since 1.0.0
		 * @param String $value instant product submission.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_product_deletion( $value = null ) {
			$this->set_prop( 'product_deletion', $value );
		}

		/**
		 * Set enable order management.
		 *
		 * @since 1.0.0
		 * @param String $value enable order management.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_enable_order_management( $value = null ) {
			$this->set_prop( 'enable_order_management', $value );
		}

		/**
		 * Set Order Status Modification.
		 *
		 * @since 1.0.0
		 * @param String $value Order Status Modification.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_order_status_modification( $value = null ) {
			$this->set_prop( 'order_status_modification', $value );
		}

		/**
		 * Set Commission Info Display.
		 *
		 * @since 1.0.0
		 * @param String $value Shop Name.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_commission_info_display( $value = null ) {
			$this->set_prop( 'commission_info_display', $value );
		}

		/**
		 * Set enable coupon management.
		 *
		 * @since 1.0.0
		 * @param String $value enable coupon management.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_enable_coupon_management( $value = null ) {
			$this->set_prop( 'enable_coupon_management', $value );
		}

		/**
		 * Set coupon creation.
		 *
		 * @since 1.0.0
		 * @param String $value coupon creation.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_coupon_creation( $value = null ) {
			$this->set_prop( 'coupon_creation', $value );
		}

		/**
		 * Set published Coupon Modification.
		 *
		 * @since 1.0.0
		 * @param String $value published coupon Modification.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_published_coupon_modification( $value = null ) {
			$this->set_prop( 'published_coupon_modification', $value );
		}

		/**
		 * Set Coupon Modification.
		 *
		 * @since 1.0.0
		 * @param String $value coupon modification.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_coupon_modification( $value = null ) {
			$this->set_prop( 'coupon_modification', $value );
		}

		/**
		 * Set Coupon Deletion.
		 *
		 * @since 1.0.0
		 * @param String $value coupon deletion.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_coupon_deletion( $value = null ) {
			$this->set_prop( 'coupon_deletion', $value );
		}
	}
}
