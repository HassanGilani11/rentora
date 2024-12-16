<?php
/**
 * Customer Data Store
 *
 * @package Multi Vendor Marketplace
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Customer_Data_Store_CPT' ) ) {

	/**
	 * Customer Data Store CPT
	 *
	 * @class MVR_Customer_Data_Store_CPT
	 * @package Class
	 */
	class MVR_Customer_Data_Store_CPT extends WC_Data_Store_WP implements WC_Object_Data_Store_Interface {

		/**
		 * Data stored props.
		 *
		 * @var Array
		 */
		protected $internal_props = array(
			'vendor_id',
			'user_id',
			'first_name',
			'last_name',
			'company',
			'address_1',
			'address_2',
			'city',
			'state',
			'country',
			'postcode',
			'phone',
			'email',
			'date_created',
			'date_modified',
			'source_id',
			'source_from',
			'created_via',
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
		 * @param MVR_Customer $customer Customer object.
		 */
		public function create( &$customer ) {
			global $wpdb;

			$wpdb_ref = &$wpdb;

			$customer->set_version( MVR_VERSION );
			$customer->set_date_created( time() );

			$table  = "{$wpdb->prefix}mvr_customer";
			$format = array(
				'vendor_id'         => '%d',
				'user_id'           => '%d',
				'first_name'        => '%s',
				'last_name'         => '%s',
				'company'           => '%s',
				'address_1'         => '%s',
				'address_2'         => '%s',
				'city'              => '%s',
				'state'             => '%s',
				'country'           => '%s',
				'postcode'          => '%s',
				'phone'             => '%s',
				'email'             => '%s',
				'date_created'      => '%s',
				'date_created_gmt'  => '%s',
				'date_modified'     => '%s',
				'date_modified_gmt' => '%s',
				'source_id'         => '%d',
				'source_from'       => '%s',
				'created_via'       => '%s',
				'version'           => '%s',
			);
			$data   = array(
				'vendor_id'         => $customer->get_vendor_id(),
				'user_id'           => $customer->get_user_id(),
				'first_name'        => $customer->get_first_name(),
				'last_name'         => $customer->get_last_name(),
				'company'           => $customer->get_company(),
				'address_1'         => $customer->get_address_1(),
				'address_2'         => $customer->get_address_2(),
				'city'              => $customer->get_city(),
				'state'             => $customer->get_state(),
				'country'           => $customer->get_country(),
				'postcode'          => $customer->get_postcode(),
				'phone'             => $customer->get_phone(),
				'email'             => $customer->get_email(),
				'date_created'      => current_time( 'mysql' ),
				'date_created_gmt'  => current_time( 'mysql', 1 ),
				'date_modified'     => current_time( 'mysql' ),
				'date_modified_gmt' => current_time( 'mysql', 1 ),
				'source_id'         => $customer->get_source_id(),
				'source_from'       => $customer->get_source_from(),
				'created_via'       => $customer->get_created_via(),
				'version'           => $customer->get_version(),
			);

			$id = mvr_insert_row_query( $table, $data, $format );

			if ( $id && ! is_wp_error( $id ) ) {
				$customer->set_id( $id );
				$customer->apply_changes();

				/**
				 * New Customer Hook
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_new_customer', $customer->get_id(), $customer );
			}
		}

		/**
		 * Method to read data from the database.
		 *
		 * @since 1.0.0
		 * @param MVR_Customer $customer Customer object.
		 * @throws Exception Invalid Post.
		 */
		public function read( &$customer ) {
			$customer->set_defaults();

			if ( ! $customer->get_id() ) {
				throw new Exception( esc_html__( 'Invalid Customer.', 'multi-vendor-marketplace' ) );
			}

			global $wpdb;
			$wpdb_ref = &$wpdb;
			$data     = $wpdb_ref->get_row(
				$wpdb_ref->prepare( "SELECT * from {$wpdb->prefix}mvr_customer WHERE ID=%d", $customer->get_id() )
			);

			foreach ( $this->internal_props as $prop ) {
				$setter = "set_$prop";

				if ( is_callable( array( $customer, $setter ) ) && is_object( $data ) && property_exists( $data, $prop ) ) {
					$customer->{$setter}( $data->$prop );
				}
			}

			$customer->set_object_read( true );
		}

		/**
		 * Method to update changes in the database.
		 *
		 * @since 1.0.0
		 * @param MVR_Customer $customer Customer object.
		 */
		public function update( &$customer ) {
			global $wpdb;
			$wpdb_ref = &$wpdb;
			$customer->set_version( MVR_VERSION );

			if ( ! $customer->get_date_created( 'edit' ) ) {
				$customer->set_date_created( time() );
			}

			$format = array(
				'vendor_id'         => '%d',
				'user_id'           => '%d',
				'first_name'        => '%s',
				'last_name'         => '%s',
				'company'           => '%s',
				'address_1'         => '%s',
				'address_2'         => '%s',
				'city'              => '%s',
				'state'             => '%s',
				'country'           => '%s',
				'postcode'          => '%s',
				'phone'             => '%s',
				'email'             => '%s',
				'date_modified'     => '%s',
				'date_modified_gmt' => '%s',
				'source_id'         => '%d',
				'source_from'       => '%s',
				'created_via'       => '%s',
				'version'           => '%s',
			);
			$data   = array(
				'vendor_id'         => $customer->get_vendor_id(),
				'user_id'           => $customer->get_user_id(),
				'first_name'        => $customer->get_first_name(),
				'last_name'         => $customer->get_last_name(),
				'company'           => $customer->get_company(),
				'address_1'         => $customer->get_address_1(),
				'address_2'         => $customer->get_address_2(),
				'city'              => $customer->get_city(),
				'state'             => $customer->get_state(),
				'country'           => $customer->get_country(),
				'postcode'          => $customer->get_postcode(),
				'phone'             => $customer->get_phone(),
				'email'             => $customer->get_email(),
				'date_modified'     => current_time( 'mysql' ),
				'date_modified_gmt' => current_time( 'mysql', 1 ),
				'created_via'       => $customer->get_created_via(),
				'version'           => $customer->get_version(),
			);
			$table  = "{$wpdb_ref->prefix}mvr_customer";
			$where  = '`ID` = ' . $customer->get_id();
			$id     = mvr_update_row_query( $table, $format, $data, $where );

			if ( $id && ! is_wp_error( $id ) ) {
				$customer->apply_changes();

				/**
				 * Update Customer Hook
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_update_customer', $customer->get_id(), $customer );
			}
		}

		/**
		 * Delete an object, set the ID to 0.
		 *
		 * @since 1.0.0
		 * @param MVR_Customer $customer Customer object.
		 * @param Array        $args Array of args to pass to the delete method.
		 * @return Boolean
		 */
		public function delete( &$customer, $args = array() ) {
			$id = $customer->get_id();

			if ( ! $id ) {
				return false;
			}

			global $wpdb;

			$wpdb_ref = &$wpdb;
			$result   = $wpdb_ref->delete( "{$wpdb->prefix}mvr_customer", array( 'ID' => $customer->get_id() ) );

			if ( ! $result ) {
				return false;
			}

			$customer->set_id( 0 );

			/**
			 * Delete Customer.
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_delete_customer', $id );

			return true;
		}
	}
}
