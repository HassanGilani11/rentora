<?php
/**
 * Staff Data Store
 *
 * @package Multi Vendor Marketplace
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Staff_Data_Store_CPT' ) ) {

	/**
	 * Staff Data Store CPT
	 *
	 * @class MVR_Staff_Data_Store_CPT
	 * @package Class
	 */
	class MVR_Staff_Data_Store_CPT extends WC_Data_Store_WP implements WC_Object_Data_Store_Interface {

		/**
		 * Internal meta type used to store staff data.
		 *
		 * @var String
		 */
		protected $meta_type = 'post';

		/**
		 * Data stored in meta keys, but not considered "meta" for an staff.
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
			'_version'                        => 'version',
			'_name'                           => 'name',
			'_description'                    => 'description',
			'_email'                          => 'email',
			'_vendor_id'                      => 'vendor_id',
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
		 * @param MVR_Staff $staff Staff object.
		 */
		public function create( &$staff ) {
			$staff->set_version( MVR_VERSION );
			$staff->set_date_created( time() );

			$id = wp_insert_post(
				array(
					'post_date'     => gmdate( 'Y-m-d H:i:s', $staff->get_date_created( 'edit' )->getOffsetTimestamp() ),
					'post_date_gmt' => gmdate( 'Y-m-d H:i:s', $staff->get_date_created( 'edit' )->getTimestamp() ),
					'post_type'     => $staff->get_type(),
					'post_status'   => $this->get_post_status( $staff ),
					'ping_status'   => 'closed',
					'post_parent'   => $staff->get_user_id(),
					'post_name'     => $staff->get_name(),
					'post_title'    => $staff->get_name(),
					'post_content'  => $staff->get_description(),
				),
				true
			);

			if ( $id && ! is_wp_error( $id ) ) {
				$staff->set_id( $id );
				$this->update_post_meta( $staff );
				$staff->save_meta_data();
				$staff->apply_changes();
				$this->clear_caches( $staff );

				/**
				 * Staff Object Creation
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_new_staff', $staff->get_id(), $staff );
			}
		}

		/**
		 * Method to read data from the database.
		 *
		 * @since 1.0.0
		 * @param MVR_Staff $staff Staff object.
		 * @throws Exception Invalid Post.
		 */
		public function read( &$staff ) {
			$staff->set_defaults();
			$post = get_post( $staff->get_id() );

			if ( ! $staff->get_id() || ! $post || $post->post_type !== $staff->get_type() ) {
				throw new Exception( esc_html__( 'Invalid Staff', 'multi-vendor-marketplace' ) );
			}

			$staff->set_props(
				array(
					'user_name'     => $post->post_title,
					'date_created'  => $this->string_to_timestamp( $post->post_date_gmt ),
					'date_modified' => $this->string_to_timestamp( $post->post_modified_gmt ),
					'status'        => $post->post_status,
					'parent_id'     => $post->post_parent,
				)
			);

			$this->read_staff_data( $staff, $post );
			$staff->read_meta_data();
			$staff->set_object_read( true );
		}

		/**
		 * Method to update changes in the database.
		 *
		 * @since 1.0.0
		 * @param MVR_Staff $staff Staff object.
		 */
		public function update( &$staff ) {
			$staff->save_meta_data();
			$staff->set_version( MVR_VERSION );

			if ( ! $staff->get_date_created( 'edit' ) ) {
				$staff->set_date_created( time() );
			}

			$changes = $staff->get_changes();

			// Only update the post when the post data changes.
			if ( array_intersect( array( 'name', 'description', 'date_created', 'date_modified', 'status', 'user_id', 'parent_id' ), array_keys( $changes ) ) ) {
				$post_data = array(
					'post_date'         => gmdate( 'Y-m-d H:i:s', $staff->get_date_created( 'edit' )->getOffsetTimestamp() ),
					'post_date_gmt'     => gmdate( 'Y-m-d H:i:s', $staff->get_date_created( 'edit' )->getTimestamp() ),
					'post_status'       => $this->get_post_status( $staff ),
					'post_parent'       => $staff->get_user_id(),
					'post_modified'     => isset( $changes['date_modified'] ) ? gmdate( 'Y-m-d H:i:s', $staff->get_date_modified( 'edit' )->getOffsetTimestamp() ) : current_time( 'mysql' ),
					'post_modified_gmt' => isset( $changes['date_modified'] ) ? gmdate( 'Y-m-d H:i:s', $staff->get_date_modified( 'edit' )->getTimestamp() ) : current_time( 'mysql', 1 ),
					'post_name'         => $staff->get_name(),
					'post_title'        => $staff->get_name(),
					'post_content'      => $staff->get_description(),
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
					$GLOBALS['wpdb']->update( $GLOBALS['wpdb']->posts, $post_data, array( 'ID' => $staff->get_id() ) );
					clean_post_cache( $staff->get_id() );
				} else {
					wp_update_post( array_merge( array( 'ID' => $staff->get_id() ), $post_data ) );
				}

				$staff->read_meta_data( true ); // Refresh internal meta data, in case things were hooked into `save_post` or another WP hook.
			}

			$this->update_post_meta( $staff );
			$staff->apply_changes();
			$this->clear_caches( $staff );
		}

		/**
		 * Delete an object, set the ID to 0.
		 *
		 * @since 1.0.0
		 * @param MVR_Staff $staff Staff object.
		 * @param Array     $args Array of args to pass to the delete method.
		 * @return Boolean
		 */
		public function delete( &$staff, $args = array() ) {
			$id   = $staff->get_id();
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
				 * Delete Staff
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_before_delete_staff', $id );

				wp_delete_post( $id, true );

				$staff->set_id( 0 );

				/**
				 * Delete Staff
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_delete_staff', $id );
			} else {
				wp_trash_post( $id );

				$staff->set_status( 'trash' );

				/**
				 * Trash Staff
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_trash_staff', $id );
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
		 * @param MVR_Staff $staff Staff object.
		 * @return String
		 */
		protected function get_post_status( $staff ) {
			$post_status = $staff->get_status( 'edit' );

			if ( ! $post_status ) {
				/**
				 * Default Staff Status.
				 *
				 * @since 1.0.0
				 */
				$post_status = apply_filters( 'mvr_default_staff_status', 'pending' );
			}

			if ( in_array( 'mvr-' . $post_status, $staff->get_valid_statuses(), true ) ) {
				$post_status = 'mvr-' . $post_status;
			}

			return $post_status;
		}

		/**
		 * Read staff data from the database.
		 *
		 * @since 1.0.0
		 * @param MVR_Staff $staff Staff object.
		 * @param Object    $post_object Post object.
		 */
		protected function read_staff_data( &$staff, $post_object ) {
			foreach ( $this->internal_meta_key_to_props as $meta_key => $prop ) {
				$setter = "set_$prop";

				if ( is_callable( array( $staff, $setter ) ) && metadata_exists( 'post', $staff->get_id(), "$meta_key" ) ) {
					$staff->{$setter}( get_post_meta( $staff->get_id(), "$meta_key", true ) );
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
		 * Helper method that updates all the post meta for an staff.
		 *
		 * @since 1.0.0
		 * @param MVR_Staff $staff Staff object.
		 */
		protected function update_post_meta( &$staff ) {
			$updated_props   = array();
			$props_to_update = $this->get_props_to_update( $staff, $this->internal_meta_key_to_props );

			foreach ( $props_to_update as $meta_key => $prop ) {
				$getter  = "get_$prop";
				$value   = is_callable( array( $staff, $getter ) ) ? $staff->{$getter}( 'edit' ) : '';
				$value   = is_string( $value ) ? wp_slash( $value ) : $value;
				$updated = $this->update_or_delete_post_meta( $staff, $meta_key, $value );

				if ( $updated ) {
					$updated_props[] = $prop;
				}
			}

			/**
			 * Staff Object Update Properties
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_staff_object_updated_props', $staff, $updated_props );
		}

		/**
		 * Clear any caches.
		 *
		 * @since 1.0.0
		 * @param MVR_Staff $staff Staff object.
		 */
		protected function clear_caches( &$staff ) {
			clean_post_cache( $staff->get_id() );
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
