<?php
/**
 * Enquiry Data Store
 *
 * @package Multi Vendor Marketplace
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Enquiry_Data_Store_CPT' ) ) {

	/**
	 * Enquiry Data Store CPT
	 *
	 * @class MVR_Enquiry_Data_Store_CPT
	 * @package Class
	 */
	class MVR_Enquiry_Data_Store_CPT extends WC_Data_Store_WP implements WC_Object_Data_Store_Interface {

		/**
		 * Data stored props.
		 *
		 * @var Array
		 */
		protected $internal_props = array(
			'vendor_id',
			'date_created',
			'date_created_gmt',
			'author_id',
			'customer_id',
			'customer_name',
			'customer_email',
			'message',
			'reply',
			'source_id',
			'source_from',
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
		 * @param MVR_Enquiry $enquiry Enquiry object.
		 */
		public function create( &$enquiry ) {
			global $wpdb;

			$wpdb_ref = &$wpdb;

			$enquiry->set_version( MVR_VERSION );
			$enquiry->set_date_created( time() );

			$table  = "{$wpdb->prefix}mvr_enquiry";
			$data   = array(
				'vendor_id'         => $enquiry->get_vendor_id(),
				'date_created'      => current_time( 'mysql' ),
				'date_created_gmt'  => current_time( 'mysql', 1 ),
				'author_id'         => $enquiry->get_author_id(),
				'customer_id'       => $enquiry->get_customer_id(),
				'customer_name'     => $enquiry->get_customer_name(),
				'customer_email'    => $enquiry->get_customer_email(),
				'message'           => $enquiry->get_message(),
				'reply'             => maybe_serialize( $enquiry->get_reply() ),
				'source_id'         => $enquiry->get_source_id(),
				'source_from'       => $enquiry->get_source_from(),
				'date_modified'     => current_time( 'mysql' ),
				'date_modified_gmt' => current_time( 'mysql', 1 ),
				'status'            => $this->get_enquiry_status( $enquiry ),
				'version'           => $enquiry->get_version(),
			);
			$format = array(
				'vendor_id'         => '%d',
				'date_created'      => '%s',
				'date_created_gmt'  => '%s',
				'author_id'         => '%d',
				'customer_id'       => '%d',
				'customer_name'     => '%s',
				'customer_email'    => '%s',
				'message'           => '%s',
				'reply'             => '%s',
				'source_id'         => '%s',
				'source_from'       => '%s',
				'date_modified'     => '%s',
				'date_modified_gmt' => '%s',
				'status'            => '%s',
				'version'           => '%s',
			);

			$id = mvr_insert_row_query( $table, $data, $format );

			if ( $id && ! is_wp_error( $id ) ) {
				$enquiry->set_id( $id );
				$enquiry->apply_changes();

				/**
				 * New Enquiry Hook
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_new_enquiry', $enquiry->get_id(), $enquiry );
			}
		}

		/**
		 * Method to read data from the database.
		 *
		 * @since 1.0.0
		 * @param MVR_Enquiry $enquiry Enquiry object.
		 * @throws Exception Invalid Post.
		 */
		public function read( &$enquiry ) {
			$enquiry->set_defaults();

			if ( ! $enquiry->get_id() ) {
				throw new Exception( esc_html__( 'Invalid Enquiry', 'multi-vendor-marketplace' ) );
			}

			global $wpdb;

			$wpdb_ref = &$wpdb;
			$data     = $wpdb_ref->get_row(
				$wpdb_ref->prepare( "SELECT * from {$wpdb->prefix}mvr_enquiry WHERE ID=%d", $enquiry->get_id() )
			);

			foreach ( $this->internal_props as $prop ) {
				$setter = "set_$prop";

				if ( is_callable( array( $enquiry, $setter ) ) && is_object( $data ) && property_exists( $data, $prop ) ) {
					$enquiry->{$setter}( $data->$prop );
				}
			}

			$enquiry->set_object_read( true );
		}

		/**
		 * Method to update changes in the database.
		 *
		 * @since 1.0.0
		 * @param MVR_Enquiry $enquiry Enquiry object.
		 */
		public function update( &$enquiry ) {
			global $wpdb;

			$wpdb_ref = &$wpdb;

			$enquiry->set_version( MVR_VERSION );

			if ( ! $enquiry->get_date_created( 'edit' ) ) {
				$enquiry->set_date_created( time() );
			}

			$format = array(
				'vendor_id'         => '%d',
				'author_id'         => '%d',
				'customer_id'       => '%d',
				'customer_name'     => '%s',
				'customer_email'    => '%s',
				'message'           => '%s',
				'reply'             => '%s',
				'source_id'         => '%s',
				'source_from'       => '%s',
				'status'            => '%s',
				'date_modified'     => '%s',
				'date_modified_gmt' => '%s',
				'version'           => '%s',
			);
			$data   = array(
				'vendor_id'         => $enquiry->get_vendor_id(),
				'author_id'         => $enquiry->get_author_id(),
				'customer_id'       => $enquiry->get_customer_id(),
				'customer_name'     => $enquiry->get_customer_name(),
				'customer_email'    => $enquiry->get_customer_email(),
				'message'           => $enquiry->get_message(),
				'reply'             => maybe_serialize( $enquiry->get_reply() ),
				'source_id'         => $enquiry->get_source_id(),
				'source_from'       => $enquiry->get_source_from(),
				'status'            => $this->get_enquiry_status( $enquiry ),
				'date_modified'     => current_time( 'mysql' ),
				'date_modified_gmt' => current_time( 'mysql', 1 ),
				'version'           => $enquiry->get_version(),
			);
			$table  = "{$wpdb->prefix}mvr_enquiry";
			$where  = '`ID` = ' . $enquiry->get_id();
			$id     = mvr_update_row_query( $table, $format, $data, $where );

			if ( $id && ! is_wp_error( $id ) ) {
				$enquiry->apply_changes();

				/**
				 * Update Enquiry Hook
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_update_enquiry', $enquiry->get_id(), $enquiry );
			}
		}

		/**
		 * Delete an object, set the ID to 0.
		 *
		 * @since 1.0.0
		 * @param MVR_Enquiry $enquiry Enquiry object.
		 * @param Array       $args Array of args to pass to the delete method.
		 * @return Boolean
		 */
		public function delete( &$enquiry, $args = array() ) {
			$id = $enquiry->get_id();

			if ( ! $id ) {
				return false;
			}

			global $wpdb;

			$wpdb_ref = &$wpdb;
			$result   = $wpdb_ref->delete( "{$wpdb->prefix}mvr_enquiry", array( 'ID' => $enquiry->get_id() ) );

			if ( ! $result ) {
				return false;
			}

			$enquiry->set_id( 0 );

			/**
			 * Delete Enquiry.
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_delete_enquiry', $id );

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
		 * @param MVR_Enquiry $enquiry Enquiry object.
		 * @return String
		 */
		protected function get_enquiry_status( $enquiry ) {
			$enquiry_status = $enquiry->get_status( 'edit' );

			if ( ! $enquiry_status ) {
				/**
				 * Default Enquiry Amount.
				 *
				 * @since 1.0.0
				 */
				$enquiry_status = apply_filters( 'mvr_default_enquiry_status', 'unread' );
			}

			if ( in_array( 'mvr-' . $enquiry_status, $enquiry->get_valid_statuses(), true ) ) {
				$enquiry_status = 'mvr-' . $enquiry_status;
			}

			return $enquiry_status;
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
