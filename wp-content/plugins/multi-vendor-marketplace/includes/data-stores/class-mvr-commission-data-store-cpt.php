<?php
/**
 * Commission Data Store
 *
 * @package Multi Vendor Marketplace
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Commission_Data_Store_CPT' ) ) {

	/**
	 * Commission Data Store CPT
	 *
	 * @class MVR_Commission_Data_Store_CPT
	 * @package Class
	 */
	class MVR_Commission_Data_Store_CPT extends WC_Data_Store_WP implements WC_Object_Data_Store_Interface {

		/**
		 * Data stored props.
		 *
		 * @var Array
		 */
		protected $internal_props = array(
			'vendor_id',
			'date_created',
			'date_created_gmt',
			'amount',
			'vendor_amount',
			'currency',
			'status',
			'created_via',
			'source_id',
			'source_from',
			'date_modified',
			'date_modified_gmt',
			'settings',
			'products',
			'parent_id',
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
		 * @param MVR_Commission $commission Commission object.
		 */
		public function create( &$commission ) {
			global $wpdb;

			$wpdb_ref = &$wpdb;

			$commission->set_version( MVR_VERSION );
			$commission->set_date_created( time() );

			$table  = "{$wpdb->prefix}mvr_commission";
			$format = array(
				'vendor_id'         => '%d',
				'date_created'      => '%s',
				'date_created_gmt'  => '%s',
				'amount'            => '%s',
				'vendor_amount'     => '%s',
				'currency'          => '%s',
				'status'            => '%s',
				'created_via'       => '%s',
				'source_id'         => '%d',
				'source_from'       => '%s',
				'date_modified'     => '%s',
				'date_modified_gmt' => '%s',
				'settings'          => '%s',
				'products'          => '%s',
				'parent_id'         => '%d',
				'version'           => '%s',
			);
			$data   = array(
				'vendor_id'         => $commission->get_vendor_id(),
				'date_created'      => current_time( 'mysql' ),
				'date_created_gmt'  => current_time( 'mysql', 1 ),
				'amount'            => $commission->get_amount(),
				'vendor_amount'     => $commission->get_vendor_amount(),
				'currency'          => $commission->get_currency(),
				'status'            => $this->get_commission_status( $commission ),
				'created_via'       => $commission->get_created_via(),
				'source_id'         => $commission->get_source_id(),
				'source_from'       => $commission->get_source_from(),
				'date_modified'     => current_time( 'mysql' ),
				'date_modified_gmt' => current_time( 'mysql', 1 ),
				'settings'          => maybe_serialize( $commission->get_settings() ),
				'products'          => maybe_serialize( $commission->get_products() ),
				'parent_id'         => $commission->get_parent_id(),
				'version'           => $commission->get_version(),
			);

			$id = mvr_insert_row_query( $table, $data, $format );

			if ( $id && ! is_wp_error( $id ) ) {
				$commission->set_id( $id );
				$commission->apply_changes();

				/**
				 * New Commission Hook
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_new_commission', $commission->get_id(), $commission );
			}
		}

		/**
		 * Method to read data from the database.
		 *
		 * @since 1.0.0
		 * @param MVR_Commission $commission Commission object.
		 * @throws Exception Invalid Post.
		 */
		public function read( &$commission ) {
			$commission->set_defaults();

			if ( ! $commission->get_id() ) {
				throw new Exception( esc_html__( 'Invalid Commission', 'multi-vendor-marketplace' ) );
			}

			global $wpdb;
			$wpdb_ref = &$wpdb;
			$data     = $wpdb_ref->get_row(
				$wpdb_ref->prepare( "SELECT * from {$wpdb->prefix}mvr_commission WHERE ID=%d", $commission->get_id() )
			);

			foreach ( $this->internal_props as $prop ) {
				$setter = "set_$prop";

				if ( is_callable( array( $commission, $setter ) ) && is_object( $data ) && property_exists( $data, $prop ) ) {
					if ( in_array( $prop, array( 'settings', 'products' ), true ) ) {
						$commission->{$setter}( maybe_unserialize( $data->$prop ) );
					} else {
						$commission->{$setter}( $data->$prop );
					}
				}
			}

			$commission->set_object_read( true );
		}

		/**
		 * Method to update changes in the database.
		 *
		 * @since 1.0.0
		 * @param MVR_Commission $commission Commission object.
		 */
		public function update( &$commission ) {
			global $wpdb;

			$wpdb_ref = &$wpdb;

			$commission->set_version( MVR_VERSION );

			if ( ! $commission->get_date_created( 'edit' ) ) {
				$commission->set_date_created( time() );
			}

			$format = array(
				'vendor_id'         => '%d',
				'amount'            => '%s',
				'vendor_amount'     => '%s',
				'currency'          => '%s',
				'status'            => '%s',
				'created_via'       => '%s',
				'source_id'         => '%d',
				'source_from'       => '%s',
				'date_modified'     => '%s',
				'date_modified_gmt' => '%s',
				'settings'          => '%s',
				'products'          => '%s',
				'parent_id'         => '%d',
				'version'           => '%s',
			);
			$data   = array(
				'vendor_id'         => $commission->get_vendor_id(),
				'amount'            => $commission->get_amount(),
				'vendor_amount'     => $commission->get_vendor_amount(),
				'currency'          => $commission->get_currency(),
				'status'            => $this->get_commission_status( $commission ),
				'created_via'       => $commission->get_created_via(),
				'source_id'         => $commission->get_source_id(),
				'source_from'       => $commission->get_source_from(),
				'date_modified'     => current_time( 'mysql' ),
				'date_modified_gmt' => current_time( 'mysql', 1 ),
				'settings'          => maybe_serialize( $commission->get_settings() ),
				'products'          => maybe_serialize( $commission->get_products() ),
				'version'           => $commission->get_version(),
				'parent_id'         => $commission->get_parent_id(),
			);
			$table  = "{$wpdb_ref->prefix}mvr_commission";
			$where  = '`ID` = ' . $commission->get_id();
			$id     = mvr_update_row_query( $table, $format, $data, $where );

			if ( $id && ! is_wp_error( $id ) ) {
				$commission->apply_changes();

				/**
				 * Update Commission Hook
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_update_commission', $commission->get_id(), $commission );
			}
		}

		/**
		 * Delete an object, set the ID to 0.
		 *
		 * @since 1.0.0
		 * @param MVR_Commission $commission Commission object.
		 * @param Array          $args Array of args to pass to the delete method.
		 * @return Boolean
		 */
		public function delete( &$commission, $args = array() ) {
			$id = $commission->get_id();

			if ( ! $id ) {
				return false;
			}

			global $wpdb;

			$wpdb_ref = &$wpdb;
			$result   = $wpdb_ref->delete( "{$wpdb->prefix}mvr_commission", array( 'ID' => $commission->get_id() ) );

			if ( ! $result ) {
				return false;
			}

			$commission->set_id( 0 );

			/**
			 * Delete Commission.
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_delete_commission', $id );

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
		 * @param MVR_Commission $commission Commission object.
		 * @return String
		 */
		protected function get_commission_status( $commission ) {
			$commission_status = $commission->get_status( 'edit' );

			if ( ! $commission_status ) {
				/**
				 * Default Commission Status.
				 *
				 * @since 1.0.0
				 */
				$commission_status = apply_filters( 'mvr_default_commission_status', 'mvr-pending' );
			}

			if ( in_array( 'mvr-' . $commission_status, $commission->get_valid_statuses(), true ) ) {
				$commission_status = 'mvr-' . $commission_status;
			}

			return $commission_status;
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
