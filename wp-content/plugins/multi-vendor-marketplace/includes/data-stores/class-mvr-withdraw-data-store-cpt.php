<?php
/**
 * Withdraw Data Store
 *
 * @package Multi Vendor Marketplace
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Withdraw_Data_Store_CPT' ) ) {

	/**
	 * Withdraw Data Store CPT
	 *
	 * @class MVR_Withdraw_Data_Store_CPT
	 * @package Class
	 */
	class MVR_Withdraw_Data_Store_CPT extends WC_Data_Store_WP implements WC_Object_Data_Store_Interface {

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
			'charge_amount',
			'payment_method',
			'currency',
			'status',
			'created_via',
			'date_modified',
			'date_modified_gmt',
			'version',
			'parent_id',
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
		 * @param MVR_Withdraw $withdraw Withdraw object.
		 */
		public function create( &$withdraw ) {
			global $wpdb;

			$wpdb_ref = &$wpdb;

			$withdraw->set_version( MVR_VERSION );
			$withdraw->set_date_created( time() );

			$table  = "{$wpdb->prefix}mvr_withdraw";
			$data   = array(
				'vendor_id'         => $withdraw->get_vendor_id(),
				'date_created'      => current_time( 'mysql' ),
				'date_created_gmt'  => current_time( 'mysql', 1 ),
				'amount'            => $withdraw->get_amount(),
				'charge_amount'     => $withdraw->get_charge_amount(),
				'payment_method'    => $withdraw->get_payment_method(),
				'currency'          => $withdraw->get_currency(),
				'status'            => $this->get_withdraw_status( $withdraw ),
				'created_via'       => $withdraw->get_created_via(),
				'date_modified'     => current_time( 'mysql' ),
				'date_modified_gmt' => current_time( 'mysql', 1 ),
				'version'           => $withdraw->get_version(),
				'parent_id'         => $withdraw->get_parent_id(),
			);
			$format = array(
				'vendor_id'         => '%d',
				'date_created'      => '%s',
				'date_created_gmt'  => '%s',
				'amount'            => '%s',
				'charge_amount'     => '%s',
				'payment_method'    => '%s',
				'currency'          => '%s',
				'status'            => '%s',
				'created_via'       => '%s',
				'date_modified'     => '%s',
				'date_modified_gmt' => '%s',
				'version'           => '%s',
				'parent_id'         => '%d',
			);

			$id = mvr_insert_row_query( $table, $data, $format );

			if ( $id && ! is_wp_error( $id ) ) {
				$withdraw->set_id( $id );
				$withdraw->apply_changes();

				/**
				 * New Withdraw Hook
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_new_withdraw', $withdraw->get_id(), $withdraw );
			}
		}

		/**
		 * Method to read data from the database.
		 *
		 * @since 1.0.0
		 * @param MVR_Withdraw $withdraw Withdraw object.
		 * @throws Exception Invalid Post.
		 */
		public function read( &$withdraw ) {
			$withdraw->set_defaults();

			if ( ! $withdraw->get_id() ) {
				throw new Exception( esc_html__( 'Invalid Withdrawal', 'multi-vendor-marketplace' ) );
			}

			global $wpdb;

			$wpdb_ref = &$wpdb;
			$data     = $wpdb_ref->get_row(
				$wpdb_ref->prepare( "SELECT * from {$wpdb->prefix}mvr_withdraw WHERE ID=%d", $withdraw->get_id() )
			);

			foreach ( $this->internal_props as $prop ) {
				$setter = "set_$prop";

				if ( is_callable( array( $withdraw, $setter ) ) && is_object( $data ) && property_exists( $data, $prop ) ) {
					$withdraw->{$setter}( $data->$prop );
				}
			}

			$withdraw->set_object_read( true );
		}

		/**
		 * Method to update changes in the database.
		 *
		 * @since 1.0.0
		 * @param MVR_Withdraw $withdraw Withdraw object.
		 */
		public function update( &$withdraw ) {
			global $wpdb;

			$wpdb_ref = &$wpdb;

			$withdraw->set_version( MVR_VERSION );

			if ( ! $withdraw->get_date_created( 'edit' ) ) {
				$withdraw->set_date_created( time() );
			}

			$format = array(
				'vendor_id'         => '%d',
				'amount'            => '%s',
				'charge_amount'     => '%s',
				'payment_method'    => '%s',
				'currency'          => '%s',
				'status'            => '%s',
				'created_via'       => '%s',
				'date_modified'     => '%s',
				'date_modified_gmt' => '%s',
				'version'           => '%s',
				'parent_id'         => '%d',
			);
			$data   = array(
				'vendor_id'         => $withdraw->get_vendor_id(),
				'amount'            => $withdraw->get_amount(),
				'charge_amount'     => $withdraw->get_charge_amount(),
				'payment_method'    => $withdraw->get_payment_method(),
				'currency'          => $withdraw->get_currency(),
				'status'            => $this->get_withdraw_status( $withdraw ),
				'created_via'       => $withdraw->get_created_via(),
				'date_modified'     => current_time( 'mysql' ),
				'date_modified_gmt' => current_time( 'mysql', 1 ),
				'version'           => $withdraw->get_version(),
				'parent_id'         => $withdraw->get_parent_id(),
			);
			$table  = "{$wpdb->prefix}mvr_withdraw";
			$where  = '`ID` = ' . $withdraw->get_id();
			$id     = mvr_update_row_query( $table, $format, $data, $where );

			if ( $id && ! is_wp_error( $id ) ) {
				$withdraw->apply_changes();

				/**
				 * Update Withdraw Hook
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_update_withdraw', $withdraw->get_id(), $withdraw );
			}
		}

		/**
		 * Delete an object, set the ID to 0.
		 *
		 * @since 1.0.0
		 * @param MVR_Withdraw $withdraw Withdraw object.
		 * @param Array        $args Array of args to pass to the delete method.
		 * @return Boolean
		 */
		public function delete( &$withdraw, $args = array() ) {
			$id = $withdraw->get_id();

			if ( ! $id ) {
				return false;
			}

			global $wpdb;

			$wpdb_ref = &$wpdb;
			$result   = $wpdb_ref->delete( "{$wpdb->prefix}mvr_withdraw", array( 'ID' => $withdraw->get_id() ) );

			if ( ! $result ) {
				return false;
			}

			$withdraw->set_id( 0 );

			/**
			 * Delete Withdraw.
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_delete_withdraw', $id );

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
		 * @param MVR_Withdraw $withdraw withdraw object.
		 * @return String
		 */
		protected function get_withdraw_status( $withdraw ) {
			$withdraw_status = $withdraw->get_status( 'edit' );

			if ( ! $withdraw_status ) {
				/**
				 * Default Withdraw Amount.
				 *
				 * @since 1.0.0
				 */
				$withdraw_status = apply_filters( 'mvr_default_withdraw_status', 'mvr-pending' );
			}

			if ( in_array( 'mvr-' . $withdraw_status, $withdraw->get_valid_statuses(), true ) ) {
				$withdraw_status = 'mvr-' . $withdraw_status;
			}

			return $withdraw_status;
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
