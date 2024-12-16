<?php
/**
 * Notification Data Store
 *
 * @package Multi Vendor Marketplace
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Notification_Data_Store_CPT' ) ) {

	/**
	 * Notification Data Store CPT
	 *
	 * @class MVR_Notification_Data_Store_CPT
	 * @package Class
	 */
	class MVR_Notification_Data_Store_CPT extends WC_Data_Store_WP implements WC_Object_Data_Store_Interface {

		/**
		 * Data stored props.
		 *
		 * @var Array
		 */
		protected $internal_props = array(
			'vendor_id',
			'date_created',
			'date_created_gmt',
			'message',
			'source_id',
			'source_from',
			'to',
			'status',
			'date_modified',
			'date_modified_gmt',
			'version',
		);

		/*
		|--------------------------------------------------------------------------
		| CRUD Methods
		|--------------------------------------------------------------------------
		 */

		/**
		 * Method to create a new ID in the database from the new changes.
		 *
		 * @since 1.0.0
		 * @param MVR_Notification $notification Notification object.
		 */
		public function create( &$notification ) {
			global $wpdb;

			$wpdb_ref = &$wpdb;

			$notification->set_version( MVR_VERSION );
			$notification->set_date_created( time() );

			$table  = "{$wpdb->prefix}mvr_notification";
			$data   = array(
				'vendor_id'         => $notification->get_vendor_id(),
				'date_created'      => current_time( 'mysql' ),
				'date_created_gmt'  => current_time( 'mysql', 1 ),
				'date_modified'     => current_time( 'mysql' ),
				'date_modified_gmt' => current_time( 'mysql', 1 ),
				'message'           => $notification->get_message(),
				'source_id'         => $notification->get_source_id(),
				'source_from'       => $notification->get_source_from(),
				'to'                => $notification->get_to(),
				'status'            => $this->get_notification_status( $notification ),
				'version'           => $notification->get_version(),
			);
			$format = array(
				'vendor_id'         => '%d',
				'date_created'      => '%s',
				'date_created_gmt'  => '%s',
				'message'           => '%s',
				'source_id'         => '%s',
				'source_from'       => '%s',
				'to'                => '%s',
				'status'            => '%s',
				'date_modified'     => '%s',
				'date_modified_gmt' => '%s',
				'version'           => '%s',
			);

			$id = mvr_insert_row_query( $table, $data, $format );

			if ( $id && ! is_wp_error( $id ) ) {
				$notification->set_id( $id );
				$notification->apply_changes();

				/**
				 * New Notification Hook
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_new_notification', $notification->get_id(), $notification );
			}
		}

		/**
		 * Method to read data from the database.
		 *
		 * @since 1.0.0
		 * @param MVR_Notification $notification Notification object.
		 * @throws Exception Invalid Post.
		 */
		public function read( &$notification ) {
			$notification->set_defaults();

			if ( ! $notification->get_id() ) {
				throw new Exception( esc_html__( 'Invalid Notification.', 'multi-vendor-marketplace' ) );
			}

			global $wpdb;

			$wpdb_ref = &$wpdb;
			$data     = $wpdb_ref->get_row(
				$wpdb_ref->prepare( "SELECT * from {$wpdb->prefix}mvr_notification WHERE ID=%d", $notification->get_id() )
			);

			foreach ( $this->internal_props as $prop ) {
				$setter = "set_$prop";

				if ( is_callable( array( $notification, $setter ) ) && is_object( $data ) && property_exists( $data, $prop ) ) {
					$notification->{$setter}( $data->$prop );
				}
			}

			$notification->set_object_read( true );
		}

		/**
		 * Method to update changes in the database.
		 *
		 * @since 1.0.0
		 * @param MVR_Notification $notification Notification object.
		 */
		public function update( &$notification ) {
			global $wpdb;

			$wpdb_ref = &$wpdb;

			$notification->set_version( MVR_VERSION );

			if ( ! $notification->get_date_created( 'edit' ) ) {
				$notification->set_date_created( time() );
			}

			$format = array(
				'vendor_id'         => '%d',
				'status'            => '%s',
				'date_modified'     => '%s',
				'date_modified_gmt' => '%s',
				'message'           => '%s',
				'source_id'         => '%s',
				'source_from'       => '%s',
				'to'                => '%s',
				'version'           => '%s',
			);
			$data   = array(
				'vendor_id'         => maybe_serialize( $notification->get_vendor_id() ),
				'status'            => $this->get_notification_status( $notification ),
				'date_modified'     => current_time( 'mysql' ),
				'date_modified_gmt' => current_time( 'mysql', 1 ),
				'message'           => $notification->get_message(),
				'source_id'         => $notification->get_source_id(),
				'source_from'       => $notification->get_source_from(),
				'to'                => $notification->get_to(),
				'version'           => $notification->get_version(),
			);
			$table  = "{$wpdb->prefix}mvr_notification";
			$where  = '`ID` = ' . $notification->get_id();
			$id     = mvr_update_row_query( $table, $format, $data, $where );

			if ( $id && ! is_wp_error( $id ) ) {
				$notification->apply_changes();

				/**
				 * Update Notification Hook
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_update_notification', $notification->get_id(), $notification );
			}
		}

		/**
		 * Delete an object, set the ID to 0.
		 *
		 * @since 1.0.0
		 * @param MVR_Notification $notification Notification object.
		 * @param Array            $args Array of args to pass to the delete method.
		 * @return Boolean
		 */
		public function delete( &$notification, $args = array() ) {
			$id = $notification->get_id();

			if ( ! $id ) {
				return false;
			}

			global $wpdb;

			$wpdb_ref = &$wpdb;
			$result   = $wpdb_ref->delete( "{$wpdb->prefix}mvr_notification", array( 'ID' => $notification->get_id() ) );

			if ( ! $result ) {
				return false;
			}

			$notification->set_id( 0 );

			/**
			 * Delete Notification.
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_delete_notification', $id );

			return true;
		}

		/*
		|------------------------|
		|   Additional Methods   |
		|------------------------|
		 */

		/**
		 * Get the status to object.
		 *
		 * @since 1.0.0
		 * @param MVR_Notification $notification Notification object.
		 * @return String
		 */
		protected function get_notification_status( $notification ) {
			$notification_status = $notification->get_status( 'edit' );

			if ( ! $notification_status ) {
				/**
				 * Default Notification Amount.
				 *
				 * @since 1.0.0
				 */
				$notification_status = apply_filters( 'mvr_default_notification_status', 'unread' );
			}

			if ( in_array( 'mvr-' . $notification_status, $notification->get_valid_statuses(), true ) ) {
				$notification_status = 'mvr-' . $notification_status;
			}

			return $notification_status;
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
