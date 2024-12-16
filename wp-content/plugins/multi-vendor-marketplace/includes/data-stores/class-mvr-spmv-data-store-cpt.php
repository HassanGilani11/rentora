<?php
/**
 * Single Product Multi Vendor Data Store
 *
 * @package Multi Vendor Marketplace
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_SPMV_Data_Store_CPT' ) ) {

	/**
	 * Single Product Multi Vendor Data Store CPT
	 *
	 * @class MVR_SPMV_Data_Store_CPT
	 * @package Class
	 */
	class MVR_SPMV_Data_Store_CPT extends WC_Data_Store_WP implements WC_Object_Data_Store_Interface {

		/**
		 * Data stored props.
		 *
		 * @var Array
		 */
		protected $internal_props = array(
			'map_id',
			'product_id',
			'vendor_id',
			'date_created',
			'date_created_gmt',
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
		 * @param MVR_SPMV $spmv Single Product Multi Vendor object.
		 */
		public function create( &$spmv ) {
			global $wpdb;

			$wpdb_ref = &$wpdb;

			$spmv->set_version( MVR_VERSION );
			$spmv->set_date_created( time() );

			$table  = "{$wpdb->prefix}mvr_product_map";
			$data   = array(
				'map_id'           => $spmv->get_map_id(),
				'product_id'       => $spmv->get_product_id(),
				'vendor_id'        => $spmv->get_vendor_id(),
				'date_created'     => current_time( 'mysql' ),
				'date_created_gmt' => current_time( 'mysql', 1 ),
				'parent_id'        => $spmv->get_parent_id(),
				'version'          => $spmv->get_version(),
			);
			$format = array(
				'map_id'           => '%d',
				'product_id'       => '%d',
				'vendor_id'        => '%d',
				'date_created'     => '%s',
				'date_created_gmt' => '%s',
				'parent_id'        => '%d',
				'version'          => '%s',
			);

			$id = mvr_insert_row_query( $table, $data, $format );

			if ( $id && ! is_wp_error( $id ) ) {
				$spmv->set_id( $id );
				$spmv->apply_changes();

				/**
				 * New Single Product Multi Vendor Hook
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_new_spmv', $spmv->get_id(), $spmv );
			}
		}

		/**
		 * Method to read data from the database.
		 *
		 * @since 1.0.0
		 * @param MVR_SPMV $spmv Single Product Multi Vendor object.
		 * @throws Exception Invalid Post.
		 */
		public function read( &$spmv ) {
			$spmv->set_defaults();

			if ( ! $spmv->get_id() ) {
				throw new Exception( esc_html__( 'Invalid Single Product Multi Vendor.', 'multi-vendor-marketplace' ) );
			}

			global $wpdb;

			$wpdb_ref = &$wpdb;
			$data     = $wpdb_ref->get_row(
				$wpdb_ref->prepare( "SELECT * from {$wpdb->prefix}mvr_product_map WHERE ID=%d", $spmv->get_id() )
			);

			foreach ( $this->internal_props as $prop ) {
				$setter = "set_$prop";

				if ( is_callable( array( $spmv, $setter ) ) && is_object( $data ) && property_exists( $data, $prop ) ) {
					$spmv->{$setter}( $data->$prop );
				}
			}

			$spmv->set_object_read( true );
		}

		/**
		 * Method to update changes in the database.
		 *
		 * @since 1.0.0
		 * @param MVR_SPMV $spmv Single Product Multi Vendor object.
		 */
		public function update( &$spmv ) {
			global $wpdb;

			$wpdb_ref = &$wpdb;

			$spmv->set_version( MVR_VERSION );

			if ( ! $spmv->get_date_created( 'edit' ) ) {
				$spmv->set_date_created( time() );
			}

			$format = array(
				'map_id'     => '%d',
				'product_id' => '%d',
				'vendor_id'  => '%d',
				'parent_id'  => '%d',
				'version'    => '%s',
			);
			$data   = array(
				'map_id'     => $spmv->get_map_id(),
				'product_id' => $spmv->get_product_id(),
				'vendor_id'  => $spmv->get_vendor_id(),
				'parent_id'  => $spmv->get_parent_id(),
				'version'    => $spmv->get_version(),
			);
			$table  = "{$wpdb->prefix}mvr_product_map";
			$where  = '`ID` = ' . $spmv->get_id();
			$id     = mvr_update_row_query( $table, $format, $data, $where );

			if ( $id && ! is_wp_error( $id ) ) {
				$spmv->apply_changes();

				/**
				 * Update Single Product Multi Vendor Hook
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_update_spmv', $spmv->get_id(), $spmv );
			}
		}

		/**
		 * Delete an object, set the ID to 0.
		 *
		 * @since 1.0.0
		 * @param MVR_Enquiry $spmv Single Product Multi Vendor object.
		 * @param Array       $args Array of args to pass to the delete method.
		 * @return Boolean
		 */
		public function delete( &$spmv, $args = array() ) {
			$id = $spmv->get_id();

			if ( ! $id ) {
				return;
			}

			global $wpdb;

			$wpdb_ref = &$wpdb;
			$result   = $wpdb_ref->delete( "{$wpdb->prefix}mvr_product_map", array( 'ID' => $spmv->get_id() ) );

			if ( ! $result ) {
				return false;
			}

			$spmv->set_id( 0 );

			/**
			 * Delete Single Product Multi Vendor.
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_delete_spmv', $id );

			return true;
		}
	}
}
