<?php
/**
 * Payout Batch Data Store
 *
 * @package Multi Vendor Marketplace
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Payout_Batch_Data_Store_CPT' ) ) {

	/**
	 * Vendor Data Store CPT
	 *
	 * @class MVR_Payout_Batch_Data_Store_CPT
	 * @package Class
	 */
	class MVR_Payout_Batch_Data_Store_CPT extends WC_Data_Store_WP implements WC_Object_Data_Store_Interface {

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
			'_batch_id'        => 'batch_id',
			'_batch_amount'    => 'batch_amount',
			'_batch_fee'       => 'batch_fee',
			'_batch_status'    => 'batch_status',
			'_name'            => 'name',
			'_version'         => 'version',
			'_time_created'    => 'time_created',
			'_time_completed'  => 'time_completed',
			'_items'           => 'items',
			'_additional_data' => 'additional_data',
			'_email_subject'   => 'email_subject',
			'_email_message'   => 'email_message',
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
		 * @param MVR_Payout_Batch $payout_batch Payout Batch object.
		 */
		public function create( &$payout_batch ) {
			$payout_batch->set_version( MVR_VERSION );
			$payout_batch->set_date_created( time() );

			$id = wp_insert_post(
				array(
					'post_date'     => gmdate( 'Y-m-d H:i:s', $payout_batch->get_date_created( 'edit' )->getOffsetTimestamp() ),
					'post_date_gmt' => gmdate( 'Y-m-d H:i:s', $payout_batch->get_date_created( 'edit' )->getTimestamp() ),
					'post_type'     => $payout_batch->get_type(),
					'post_status'   => $this->get_post_status( $payout_batch ),
					'ping_status'   => 'closed',
					'post_author'   => 1,
					'post_parent'   => 1,
					'post_title'    => $payout_batch->get_name(),
				),
				true
			);

			if ( $id && ! is_wp_error( $id ) ) {
				$payout_batch->set_id( $id );
				$this->update_post_meta( $payout_batch );
				$payout_batch->save_meta_data();
				$payout_batch->apply_changes();
				$this->clear_caches( $payout_batch );

				/**
				 * Payout Batch Object Creation
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_new_payout_batch', $payout_batch->get_id(), $payout_batch );
			}
		}

		/**
		 * Method to read data from the database.
		 *
		 * @since 1.0.0
		 * @param MVR_Payout_Batch $payout_batch Payout Batch object.
		 * @throws Exception Invalid Post.
		 */
		public function read( &$payout_batch ) {
			$payout_batch->set_defaults();
			$post = get_post( $payout_batch->get_id() );

			if ( ! $payout_batch->get_id() || ! $post || $post->post_type !== $payout_batch->get_type() ) {
				throw new Exception( esc_html__( 'Invalid Payout Batch', 'multi-vendor-marketplace' ) );
			}

			$payout_batch->set_props(
				array(
					'date_created'  => $this->string_to_timestamp( $post->post_date_gmt ),
					'date_modified' => $this->string_to_timestamp( $post->post_modified_gmt ),
					'status'        => $post->post_status,
				)
			);

			$this->read_vendor_data( $payout_batch, $post );

			$payout_batch->read_meta_data();
			$payout_batch->set_object_read( true );
		}

		/**
		 * Method to update changes in the database.
		 *
		 * @since 1.0.0
		 * @param MVR_Payout_Batch $payout_batch Payout Batch object.
		 */
		public function update( &$payout_batch ) {
			$payout_batch->save_meta_data();
			$payout_batch->set_version( MVR_VERSION );

			if ( ! $payout_batch->get_date_created( 'edit' ) ) {
				$payout_batch->set_date_created( time() );
			}

			$changes = $payout_batch->get_changes();

			// Only update the post when the post data changes.
			if ( array_intersect( array( 'name', 'batch_id', 'batch_amount', 'batch_fee', 'batch_status', 'time_created', 'time_completed', 'items', 'additional_data', 'date_created', 'date_modified', 'status', 'email_subject', 'email_message' ), array_keys( $changes ) ) ) {
				$post_data = array(
					'post_date'         => gmdate( 'Y-m-d H:i:s', $payout_batch->get_date_created( 'edit' )->getOffsetTimestamp() ),
					'post_date_gmt'     => gmdate( 'Y-m-d H:i:s', $payout_batch->get_date_created( 'edit' )->getTimestamp() ),
					'post_status'       => $this->get_post_status( $payout_batch ),
					'post_modified'     => isset( $changes['date_modified'] ) ? gmdate( 'Y-m-d H:i:s', $payout_batch->get_date_modified( 'edit' )->getOffsetTimestamp() ) : current_time( 'mysql' ),
					'post_modified_gmt' => isset( $changes['date_modified'] ) ? gmdate( 'Y-m-d H:i:s', $payout_batch->get_date_modified( 'edit' )->getTimestamp() ) : current_time( 'mysql', 1 ),
					'post_title'        => $payout_batch->get_name(),
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
					$GLOBALS['wpdb']->update( $GLOBALS['wpdb']->posts, $post_data, array( 'ID' => $payout_batch->get_id() ) );
					clean_post_cache( $payout_batch->get_id() );
				} else {
					wp_update_post( array_merge( array( 'ID' => $payout_batch->get_id() ), $post_data ) );
				}

				$payout_batch->read_meta_data( true ); // Refresh internal meta data, in case things were hooked into `save_post` or another WP hook.
			}

			$this->update_post_meta( $payout_batch );
			$payout_batch->apply_changes();
			$this->clear_caches( $payout_batch );
		}

		/**
		 * Delete an object, set the ID to 0.
		 *
		 * @since 1.0.0
		 * @param MVR_Payout_batch $payout_batch Payout Batch object.
		 * @param Array            $args Array of args to pass to the delete method.
		 * @return Boolean
		 */
		public function delete( &$payout_batch, $args = array() ) {
			$id   = $payout_batch->get_id();
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
				do_action( 'mvr_before_delete_payout_batch', $id );

				wp_delete_post( $id, true );

				$payout_batch->set_id( 0 );

				/**
				 * Delete Payout Batch
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_delete_payout_batch', $id );
			} else {
				wp_trash_post( $id );

				$payout_batch->set_status( 'trash' );

				/**
				 * Trash Payout Batch
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_trash_payout_batch', $id );
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
		 * @param MVR_Payout_Batch $payout_batch Vendor object.
		 * @return String
		 */
		protected function get_post_status( $payout_batch ) {
			$post_status = $payout_batch->get_status( 'edit' );

			if ( ! $post_status ) {
				/**
				 * Default Vendor Status.
				 *
				 * @since 1.0.0
				 */
				$post_status = apply_filters( 'mvr_default_payout_batch_status', 'pending' );
			}

			if ( in_array( 'mvr-' . $post_status, $payout_batch->get_valid_statuses(), true ) ) {
				$post_status = 'mvr-' . $post_status;
			}

			return $post_status;
		}

		/**
		 * Read vendor data from the database.
		 *
		 * @since 1.0.0
		 * @param MVR_Payout_Batch $payout_batch Vendor object.
		 * @param Object           $post_object Post object.
		 */
		protected function read_vendor_data( &$payout_batch, $post_object ) {
			foreach ( $this->internal_meta_key_to_props as $meta_key => $prop ) {
				$setter = "set_$prop";

				if ( is_callable( array( $payout_batch, $setter ) ) && metadata_exists( 'post', $payout_batch->get_id(), "$meta_key" ) ) {
					$payout_batch->{$setter}( get_post_meta( $payout_batch->get_id(), "$meta_key", true ) );
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
		 * Helper method that updates all the post meta for an Payout Batch.
		 *
		 * @since 1.0.0
		 * @param MVR_Payout_Batch $payout_batch Payout Batch object.
		 */
		protected function update_post_meta( &$payout_batch ) {
			$updated_props   = array();
			$props_to_update = $this->get_props_to_update( $payout_batch, $this->internal_meta_key_to_props );

			foreach ( $props_to_update as $meta_key => $prop ) {
				$getter  = "get_$prop";
				$value   = is_callable( array( $payout_batch, $getter ) ) ? $payout_batch->{$getter}( 'edit' ) : '';
				$value   = is_string( $value ) ? wp_slash( $value ) : $value;
				$updated = $this->update_or_delete_post_meta( $payout_batch, $meta_key, $value );

				if ( $updated ) {
					$updated_props[] = $prop;
				}
			}

			/**
			 * Payout Batch Object Update Properties
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_payout_batch_object_updated_props', $payout_batch, $updated_props );
		}

		/**
		 * Clear any caches.
		 *
		 * @since 1.0.0
		 * @param MVR_Payout_Batch $payout_batch Vendor object.
		 */
		protected function clear_caches( &$payout_batch ) {
			clean_post_cache( $payout_batch->get_id() );
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
