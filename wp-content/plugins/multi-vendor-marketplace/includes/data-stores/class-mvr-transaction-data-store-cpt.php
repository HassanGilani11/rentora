<?php
/**
 * Transaction Data Store
 *
 * @package Multi Vendor Marketplace
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Transaction_Data_Store_CPT' ) ) {

	/**
	 * Transaction Data Store CPT
	 *
	 * @class MVR_Transaction_Data_Store_CPT
	 * @package Class
	 */
	class MVR_Transaction_Data_Store_CPT extends WC_Data_Store_WP implements WC_Object_Data_Store_Interface {

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
			'currency',
			'type',
			'status',
			'created_via',
			'source_id',
			'source_from',
			'withdraw_date',
			'date_modified',
			'date_modified_gmt',
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
		 * @param MVR_Transaction $transaction Transaction object.
		 */
		public function create( &$transaction ) {
			global $wpdb;

			$wpdb_ref = &$wpdb;

			$transaction->set_version( MVR_VERSION );
			$transaction->set_date_created( time() );

			$table  = "{$wpdb->prefix}mvr_transaction";
			$format = array(
				'vendor_id'         => '%d',
				'date_created'      => '%s',
				'date_created_gmt'  => '%s',
				'amount'            => '%s',
				'currency'          => '%s',
				'type'              => '%s',
				'status'            => '%s',
				'created_via'       => '%s',
				'source_id'         => '%s',
				'source_from'       => '%s',
				'withdraw_date'     => '%s',
				'date_modified'     => '%s',
				'date_modified_gmt' => '%s',
				'parent_id'         => '%d',
				'version'           => '%s',
			);
			$data   = array(
				'vendor_id'         => $transaction->get_vendor_id(),
				'date_created'      => current_time( 'mysql' ),
				'date_created_gmt'  => current_time( 'mysql', 1 ),
				'amount'            => $transaction->get_amount(),
				'currency'          => $transaction->get_currency(),
				'type'              => $transaction->get_type(),
				'status'            => $this->get_transaction_status( $transaction ),
				'created_via'       => $transaction->get_created_via(),
				'source_id'         => $transaction->get_source_id(),
				'source_from'       => $transaction->get_source_from(),
				'withdraw_date'     => $transaction->get_withdraw_date(),
				'date_modified'     => current_time( 'mysql' ),
				'date_modified_gmt' => current_time( 'mysql', 1 ),
				'parent_id'         => $transaction->get_parent_id(),
				'version'           => $transaction->get_version(),
			);

			$id = mvr_insert_row_query( $table, $data, $format );

			if ( $id && ! is_wp_error( $id ) ) {
				$transaction->set_id( $id );
				$transaction->apply_changes();

				/**
				 * New Transaction Hook
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_new_transaction', $transaction->get_id(), $transaction );
			}
		}

		/**
		 * Method to read data from the database.
		 *
		 * @since 1.0.0
		 * @param MVR_Transaction $transaction Transaction object.
		 * @throws Exception Invalid Post.
		 */
		public function read( &$transaction ) {
			$transaction->set_defaults();

			if ( ! $transaction->get_id() ) {
				throw new Exception( esc_html__( 'Invalid Transaction', 'multi-vendor-marketplace' ) );
			}

			global $wpdb;

			$wpdb_ref = &$wpdb;
			$data     = $wpdb_ref->get_row(
				$wpdb_ref->prepare( "SELECT * from {$wpdb->prefix}mvr_transaction WHERE ID=%d", $transaction->get_id() )
			);

			foreach ( $this->internal_props as $prop ) {
				$setter = "set_$prop";

				if ( is_callable( array( $transaction, $setter ) ) && is_object( $data ) && property_exists( $data, $prop ) ) {
					$transaction->{$setter}( $data->$prop );
				}
			}

			$transaction->set_object_read( true );
		}

		/**
		 * Method to update changes in the database.
		 *
		 * @since 1.0.0
		 * @param MVR_Transaction $transaction Transaction object.
		 */
		public function update( &$transaction ) {
			global $wpdb;

			$wpdb_ref = &$wpdb;
			$transaction->set_version( MVR_VERSION );

			if ( ! $transaction->get_date_created( 'edit' ) ) {
				$transaction->set_date_created( time() );
			}

			$format = array(
				'vendor_id'         => '%d',
				'amount'            => '%s',
				'currency'          => '%s',
				'type'              => '%s',
				'status'            => '%s',
				'created_via'       => '%s',
				'source_id'         => '%s',
				'source_from'       => '%s',
				'withdraw_date'     => '%s',
				'date_modified'     => '%s',
				'date_modified_gmt' => '%s',
				'parent_id'         => '%d',
				'version'           => '%s',
			);
			$data   = array(
				'vendor_id'         => $transaction->get_vendor_id(),
				'amount'            => $transaction->get_amount(),
				'currency'          => $transaction->get_currency(),
				'type'              => $transaction->get_type(),
				'status'            => $this->get_transaction_status( $transaction ),
				'created_via'       => $transaction->get_created_via(),
				'source_id'         => $transaction->get_source_id(),
				'source_from'       => $transaction->get_source_from(),
				'withdraw_date'     => $transaction->get_withdraw_date(),
				'date_modified'     => current_time( 'mysql' ),
				'date_modified_gmt' => current_time( 'mysql', 1 ),
				'parent_id'         => $transaction->get_parent_id(),
				'version'           => $transaction->get_version(),
			);
			$table  = "{$wpdb->prefix}mvr_transaction";
			$where  = '`ID` = ' . $transaction->get_id();
			$id     = mvr_update_row_query( $table, $format, $data, $where );

			if ( $id && ! is_wp_error( $id ) ) {
				$transaction->apply_changes();

				/**
				 * Update Transaction Hook
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_update_transaction', $transaction->get_id(), $transaction );
			}
		}

		/**
		 * Delete an object, set the ID to 0.
		 *
		 * @since 1.0.0
		 * @param MVR_Transaction $transaction Transaction object.
		 * @param Array           $args Array of args to pass to the delete method.
		 * @return Boolean
		 */
		public function delete( &$transaction, $args = array() ) {
			$id = $transaction->get_id();

			if ( ! $id ) {
				return false;
			}

			global $wpdb;

			$wpdb_ref = &$wpdb;
			$result   = $wpdb_ref->delete( "{$wpdb->prefix}mvr_transaction", array( 'ID' => $transaction->get_id() ) );

			if ( ! $result ) {
				return false;
			}

			$transaction->set_id( 0 );

			/**
			 * Delete Transaction.
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_delete_transaction', $id );

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
		 * @param MVR_Transaction $transaction Transaction object.
		 * @return String
		 */
		protected function get_transaction_status( $transaction ) {
			$transaction_status = $transaction->get_status( 'edit' );

			if ( ! $transaction_status ) {
				/**
				 * Default Transaction Amount.
				 *
				 * @since 1.0.0
				 */
				$transaction_status = apply_filters( 'mvr_default_transaction_status', 'mvr-processing' );
			}

			if ( in_array( 'mvr-' . $transaction_status, $transaction->get_valid_statuses(), true ) ) {
				$transaction_status = 'mvr-' . $transaction_status;
			}

			return $transaction_status;
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
