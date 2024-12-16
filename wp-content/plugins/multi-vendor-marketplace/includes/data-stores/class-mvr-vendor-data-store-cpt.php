<?php
/**
 * Vendor Data Store
 *
 * @package Multi Vendor Marketplace
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Vendor_Data_Store_CPT' ) ) {

	/**
	 * Vendor Data Store CPT
	 *
	 * @class MVR_Vendor_Data_Store_CPT
	 * @package Class
	 */
	class MVR_Vendor_Data_Store_CPT extends WC_Data_Store_WP implements WC_Object_Data_Store_Interface {

		/**
		 * Internal meta type used to store vendor data.
		 *
		 * @var String
		 */
		protected $meta_type = 'post';

		/**
		 * Data stored in meta keys, but not considered "meta" for an vendor.
		 *
		 * @var Array
		 */
		protected $internal_meta_keys = array();

		/**
		 * Data stored in meta key to props.
		 *
		 * @var Array
		 */
		protected $internal_meta_key_to_props = array(
			'_user_id'                        => 'user_id',
			'_amount'                         => 'amount',
			'_locked_amount'                  => 'locked_amount',
			'_thumbnail_id'                   => 'logo_id',
			'_banner_id'                      => 'banner_id',
			'_version'                        => 'version',
			'_name'                           => 'name',
			'_shop_name'                      => 'shop_name',
			'_slug'                           => 'slug',
			'_description'                    => 'description',
			'_tac'                            => 'tac',
			'_email'                          => 'email',
			'_first_name'                     => 'first_name',
			'_last_name'                      => 'last_name',
			'_address1'                       => 'address1',
			'_address2'                       => 'address2',
			'_city'                           => 'city',
			'_state'                          => 'state',
			'_country'                        => 'country',
			'_zip_code'                       => 'zip_code',
			'_phone'                          => 'phone',
			'_facebook'                       => 'facebook',
			'_twitter'                        => 'twitter',
			'_youtube'                        => 'youtube',
			'_instagram'                      => 'instagram',
			'_linkedin'                       => 'linkedin',
			'_pinterest'                      => 'pinterest',
			'_commission_from'                => 'commission_from',
			'_commission_type'                => 'commission_type',
			'_commission_criteria'            => 'commission_criteria',
			'_commission_criteria_value'      => 'commission_criteria_value',
			'_commission_value'               => 'commission_value',
			'_tax_to'                         => 'tax_to',
			'_commission_after_coupon'        => 'commission_after_coupon',
			'_commission_after_vendor_coupon' => 'commission_after_vendor_coupon',
			'_payment_method'                 => 'payment_method',
			'_bank_account_name'              => 'bank_account_name',
			'_bank_account_number'            => 'bank_account_number',
			'_bank_account_type'              => 'bank_account_type',
			'_bank_name'                      => 'bank_name',
			'_iban'                           => 'iban',
			'_swift'                          => 'swift',
			'_paypal_email'                   => 'paypal_email',
			'_payout_type'                    => 'payout_type',
			'_payout_schedule'                => 'payout_schedule',
			'_enable_withdraw_charge'         => 'enable_withdraw_charge',
			'_withdraw_charge_type'           => 'withdraw_charge_type',
			'_withdraw_charge_value'          => 'withdraw_charge_value',
			'_enable_product_management'      => 'enable_product_management',
			'_product_creation'               => 'product_creation',
			'_product_modification'           => 'product_modification',
			'_published_product_modification' => 'published_product_modification',
			'_manage_inventory'               => 'manage_inventory',
			'_product_deletion'               => 'product_deletion',
			'_enable_order_management'        => 'enable_order_management',
			'_order_status_modification'      => 'order_status_modification',
			'_commission_info_display'        => 'commission_info_display',
			'_enable_coupon_management'       => 'enable_coupon_management',
			'_coupon_creation'                => 'coupon_creation',
			'_coupon_modification'            => 'coupon_modification',
			'_published_coupon_modification'  => 'published_coupon_modification',
			'_coupon_deletion'                => 'coupon_deletion',
			'_enable_commission_withdraw'     => 'enable_commission_withdraw',
			'_commission_transaction'         => 'commission_transaction',
			'_commission_transaction_info'    => 'commission_transaction_info',
		);

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->internal_meta_keys = array_merge( $this->internal_meta_keys, array_keys( $this->internal_meta_key_to_props ) );
		}

		/*
		|--------------------------------------------------------------------------
		| CRUD Methods
		|--------------------------------------------------------------------------
		 */

		/**
		 * Method to create a new ID in the database from the new changes.
		 *
		 * @since 1.0.0
		 * @param MVR_Vendor $vendor Vendor object.
		 */
		public function create( &$vendor ) {
			$vendor->set_version( MVR_VERSION );
			$vendor->set_date_created( time() );

			$id = wp_insert_post(
				array(
					'post_date'     => gmdate( 'Y-m-d H:i:s', $vendor->get_date_created( 'edit' )->getOffsetTimestamp() ),
					'post_date_gmt' => gmdate( 'Y-m-d H:i:s', $vendor->get_date_created( 'edit' )->getTimestamp() ),
					'post_type'     => $vendor->get_type(),
					'post_status'   => $this->get_post_status( $vendor ),
					'ping_status'   => 'closed',
					'post_author'   => $vendor->get_user_id(),
					'post_parent'   => $vendor->get_user_id(),
					'post_name'     => $vendor->get_slug(),
					'post_title'    => $vendor->get_name(),
					'post_content'  => $vendor->get_description(),
				),
				true
			);

			if ( $id && ! is_wp_error( $id ) ) {
				$vendor->set_id( $id );
				$this->update_post_meta( $vendor );
				$vendor->save_meta_data();
				$vendor->apply_changes();
				$this->clear_caches( $vendor );

				/**
				 * Vendor Object Creation
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_new_vendor', $vendor->get_id(), $vendor );
			}
		}

		/**
		 * Method to read data from the database.
		 *
		 * @since 1.0.0
		 * @param MVR_Vendor $vendor Vendor object.
		 * @throws Exception Invalid Post.
		 */
		public function read( &$vendor ) {
			$vendor->set_defaults();
			$post = get_post( $vendor->get_id() );

			if ( ! $vendor->get_id() || ! $post || $post->post_type !== $vendor->get_type() ) {
				throw new Exception( esc_html__( 'Invalid Vendor', 'multi-vendor-marketplace' ) );
			}

			$vendor->set_props(
				array(
					'user_name'     => $post->post_title,
					'date_created'  => $this->string_to_timestamp( $post->post_date_gmt ),
					'date_modified' => $this->string_to_timestamp( $post->post_modified_gmt ),
					'status'        => $post->post_status,
					'parent_id'     => $post->post_parent,
				)
			);

			$this->read_vendor_data( $vendor, $post );
			$vendor->read_meta_data();
			$vendor->set_object_read( true );
		}

		/**
		 * Method to update changes in the database.
		 *
		 * @since 1.0.0
		 * @param MVR_Vendor $vendor Vendor object.
		 */
		public function update( &$vendor ) {
			$vendor->save_meta_data();
			$vendor->set_version( MVR_VERSION );

			if ( ! $vendor->get_date_created( 'edit' ) ) {
				$vendor->set_date_created( time() );
			}

			$changes = $vendor->get_changes();

			// Only update the post when the post data changes.
			if ( array_intersect( array( 'name', 'slug', 'description', 'date_created', 'date_modified', 'status', 'user_id', 'parent_id' ), array_keys( $changes ) ) ) {
				$post_data = array(
					'post_date'         => gmdate( 'Y-m-d H:i:s', $vendor->get_date_created( 'edit' )->getOffsetTimestamp() ),
					'post_date_gmt'     => gmdate( 'Y-m-d H:i:s', $vendor->get_date_created( 'edit' )->getTimestamp() ),
					'post_status'       => $this->get_post_status( $vendor ),
					'post_parent'       => $vendor->get_user_id(),
					'post_modified'     => isset( $changes['date_modified'] ) ? gmdate( 'Y-m-d H:i:s', $vendor->get_date_modified( 'edit' )->getOffsetTimestamp() ) : current_time( 'mysql' ),
					'post_modified_gmt' => isset( $changes['date_modified'] ) ? gmdate( 'Y-m-d H:i:s', $vendor->get_date_modified( 'edit' )->getTimestamp() ) : current_time( 'mysql', 1 ),
					'post_name'         => $vendor->get_slug(),
					'post_title'        => $vendor->get_name(),
					'post_content'      => $vendor->get_description(),
				);

				/**
				 * When updating this object, to prevent infinite loops, use $wpdb
				 * to update data, since wp_update_post spawns more calls to the
				 * save_post action.
				 *
				 * This ensures hooks are fired by either WP itself (admin screen save),
				 * or an update purely from CRUD.
				 */
				if ( doing_action( 'save_post' ) ) {
					$GLOBALS['wpdb']->update( $GLOBALS['wpdb']->posts, $post_data, array( 'ID' => $vendor->get_id() ) );
					clean_post_cache( $vendor->get_id() );
				} else {
					wp_update_post( array_merge( array( 'ID' => $vendor->get_id() ), $post_data ) );
				}

				$vendor->read_meta_data( true ); // Refresh internal meta data, in case things were hooked into `save_post` or another WP hook.
			}

			$this->update_post_meta( $vendor );
			$vendor->apply_changes();
			$this->clear_caches( $vendor );
		}

		/**
		 * Delete an object, set the ID to 0.
		 *
		 * @since 1.0.0
		 * @param MVR_Vendor $vendor Vendor object.
		 * @param Array      $args Array of args to pass to the delete method.
		 * @return Boolean
		 */
		public function delete( &$vendor, $args = array() ) {
			$id   = $vendor->get_id();
			$args = wp_parse_args(
				$args,
				array(
					'force_delete' => false,
				)
			);

			if ( ! $id ) {
				return false;
			}

			if ( $args['force_delete'] ) {
				/**
				 * Before Delete Vendor
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_before_delete_vendor', $id );

				wp_delete_post( $id, true );

				$vendor->set_id( 0 );

				/**
				 * Delete Vendor
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_delete_vendor', $id );
			} else {
				wp_trash_post( $id );

				$vendor->set_status( 'trash' );

				/**
				 * Trash Vendor
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_trash_vendor', $id );
			}

			return true;
		}

		/*
		|------------------------|
		|   Additional Methods   |
		|------------------------|
		 */

		/**
		 * Get the status to save to the post object.
		 *
		 * @since 1.0.0
		 * @param MVR_Vendor $vendor Vendor object.
		 * @return String
		 */
		protected function get_post_status( $vendor ) {
			$post_status = $vendor->get_status( 'edit' );

			if ( ! $post_status ) {
				/**
				 * Default Vendor Status.
				 *
				 * @since 1.0.0
				 */
				$post_status = apply_filters( 'mvr_default_vendor_status', 'pending' );
			}

			if ( in_array( 'mvr-' . $post_status, $vendor->get_valid_statuses(), true ) ) {
				$post_status = 'mvr-' . $post_status;
			}

			return $post_status;
		}

		/**
		 * Read vendor data from the database.
		 *
		 * @since 1.0.0
		 * @param MVR_Vendor $vendor Vendor object.
		 * @param Object     $post_object Post object.
		 */
		protected function read_vendor_data( &$vendor, $post_object ) {
			foreach ( $this->internal_meta_key_to_props as $meta_key => $prop ) {
				$setter = "set_$prop";

				if ( is_callable( array( $vendor, $setter ) ) && metadata_exists( 'post', $vendor->get_id(), "$meta_key" ) ) {
					$vendor->{$setter}( get_post_meta( $vendor->get_id(), "$meta_key", true ) );
				}
			}
		}

		/**
		 * Update meta data in, or delete it from, the database.
		 * As WC defined this method @since 3.6.0 and so we reuse this method here to make it compatible with v3.5.x too.
		 *
		 * @since 1.0.0
		 * @param WC_Data $object The WP_Data object.
		 * @param String  $meta_key Meta key to update.
		 * @param Mixed   $meta_value Value to save.
		 * @return Boolean True if updated/deleted.
		 */
		protected function update_or_delete_post_meta( $object, $meta_key, $meta_value ) {
			if ( is_callable( array( get_parent_class( $this ), 'update_or_delete_post_meta' ) ) ) {
				$updated = parent::update_or_delete_post_meta( $object, $meta_key, $meta_value );
			} elseif ( in_array( $meta_value, array( array(), '' ), true ) ) {
					$updated = delete_post_meta( $object->get_id(), $meta_key );
			} else {
				$updated = update_post_meta( $object->get_id(), $meta_key, $meta_value );
			}

			return (bool) $updated;
		}

		/**
		 * Helper method that updates all the post meta for an vendor.
		 *
		 * @since 1.0.0
		 * @param MVR_Vendor $vendor Vendor object.
		 */
		protected function update_post_meta( &$vendor ) {
			$updated_props   = array();
			$props_to_update = $this->get_props_to_update( $vendor, $this->internal_meta_key_to_props );

			foreach ( $props_to_update as $meta_key => $prop ) {
				$getter  = "get_$prop";
				$value   = is_callable( array( $vendor, $getter ) ) ? $vendor->{$getter}( 'edit' ) : '';
				$value   = is_string( $value ) ? wp_slash( $value ) : $value;
				$updated = $this->update_or_delete_post_meta( $vendor, $meta_key, $value );

				if ( $updated ) {
					$updated_props[] = $prop;
				}
			}

			/**
			 * Vendor Object Update Properties
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_vendor_object_updated_props', $vendor, $updated_props );
		}

		/**
		 * Clear any caches.
		 *
		 * @since 1.0.0
		 * @param MVR_Vendor $vendor Vendor object.
		 */
		protected function clear_caches( &$vendor ) {
			clean_post_cache( $vendor->get_id() );
		}

		/**
		 * Converts a WP post date string into a timestamp.
		 *
		 * @since 1.0.0
		 * @param  String $time_string The WP post date string.
		 * @return int|null The date string converted to a timestamp or null.
		 */
		protected function string_to_timestamp( $time_string ) {
			return '0000-00-00 00:00:00' !== $time_string ? wc_string_to_timestamp( $time_string ) : null;
		}
	}
}
