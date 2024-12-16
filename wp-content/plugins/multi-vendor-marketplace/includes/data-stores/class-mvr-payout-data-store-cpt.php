<?php
/**
 * Payout Data Store
 *
 * @package Multi Vendor Marketplace
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Payout_Data_Store_CPT' ) ) {

	/**
	 * Payout Data Store CPT
	 *
	 * @class MVR_Payout_Data_Store_CPT
	 * @package Class
	 */
	class MVR_Payout_Data_Store_CPT extends WC_Data_Store_WP implements WC_Object_Data_Store_Interface {

		/**
		 * Data stored props.
		 *
		 * @var Array
		 */
		protected $internal_props = array(
			'vendor_id',
			'user_id',
			'email',
			'amount',
			'payment_method',
			'currency',
			'date_created',
			'date_created_gmt',
			'status',
			'created_via',
			'source_id',
			'source_from',
			'batch_id',
			'schedule',
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
		 * @param MVR_Payout $payout Payout object.
		 */
		public function create( &$payout ) {
			global $wpdb;

			$wpdb_ref = &$wpdb;

			$payout->set_version( MVR_VERSION );
			$payout->set_date_created( time() );

			$table  = "{$wpdb->prefix}mvr_payout";
			$format = array(
				'vendor_id'         => '%d',
				'user_id'           => '%d',
				'email'             => '%s',
				'amount'            => '%s',
				'currency'          => '%s',
				'payment_method'    => '%s',
				'status'            => '%s',
				'created_via'       => '%s',
				'source_id'         => '%s',
				'source_from'       => '%s',
				'batch_id'          => '%s',
				'schedule'          => '%s',
				'date_created'      => '%s',
				'date_created_gmt'  => '%s',
				'date_modified'     => '%s',
				'date_modified_gmt' => '%s',
				'version'           => '%s',
			);
			$data   = array(
				'vendor_id'         => $payout->get_vendor_id(),
				'user_id'           => $payout->get_user_id(),
				'email'             => $payout->get_email(),
				'amount'            => $payout->get_amount(),
				'currency'          => $payout->get_currency(),
				'payment_method'    => $payout->get_payment_method(),
				'status'            => $this->get_payout_status( $payout ),
				'created_via'       => $payout->get_created_via(),
				'source_id'         => $payout->get_source_id(),
				'source_from'       => $payout->get_source_from(),
				'batch_id'          => $payout->get_batch_id(),
				'schedule'          => $payout->get_schedule(),
				'date_created'      => current_time( 'mysql' ),
				'date_created_gmt'  => current_time( 'mysql', 1 ),
				'date_modified'     => current_time( 'mysql' ),
				'date_modified_gmt' => current_time( 'mysql', 1 ),
				'version'           => $payout->get_version(),
			);

			$id = mvr_insert_row_query( $table, $data, $format );

			if ( $id && ! is_wp_error( $id ) ) {
				$payout->set_id( $id );
				$payout->apply_changes();

				/**
				 * New Payout Hook
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_new_payout', $payout->get_id(), $payout );
			}
		}

		/**
		 * Method to read data from the database.
		 *
		 * @since 1.0.0
		 * @param MVR_Payout $payout Payout object.
		 * @throws Exception Invalid Post.
		 */
		public function read( &$payout ) {
			$payout->set_defaults();

			if ( ! $payout->get_id() ) {
				throw new Exception( esc_html__( 'Invalid Payout.', 'multi-vendor-marketplace' ) );
			}

			global $wpdb;

			$wpdb_ref = &$wpdb;
			$data     = $wpdb_ref->get_row(
				$wpdb_ref->prepare( "SELECT * from {$wpdb->prefix}mvr_payout WHERE ID=%d", $payout->get_id() )
			);

			foreach ( $this->internal_props as $prop ) {
				$setter = "set_$prop";

				if ( is_callable( array( $payout, $setter ) ) && is_object( $data ) && property_exists( $data, $prop ) ) {
					$payout->{$setter}( $data->$prop );
				}
			}

			$payout->set_object_read( true );
		}

		/**
		 * Method to update changes in the database.
		 *
		 * @since 1.0.0
		 * @param MVR_Payout $payout Payout object.
		 */
		public function update( &$payout ) {
			global $wpdb;

			$wpdb_ref = &$wpdb;
			$payout->set_version( MVR_VERSION );

			if ( ! $payout->get_date_created( 'edit' ) ) {
				$payout->set_date_created( time() );
			}

			$format = array(
				'vendor_id'         => '%d',
				'user_id'           => '%d',
				'email'             => '%s',
				'amount'            => '%s',
				'currency'          => '%s',
				'payment_method'    => '%s',
				'status'            => '%s',
				'created_via'       => '%s',
				'source_id'         => '%s',
				'source_from'       => '%s',
				'batch_id'          => '%s',
				'schedule'          => '%s',
				'date_modified'     => '%s',
				'date_modified_gmt' => '%s',
				'version'           => '%s',
			);
			$data   = array(
				'vendor_id'         => $payout->get_vendor_id(),
				'user_id'           => $payout->get_user_id(),
				'email'             => $payout->get_email(),
				'amount'            => $payout->get_amount(),
				'currency'          => $payout->get_currency(),
				'payment_method'    => $payout->get_payment_method(),
				'status'            => $this->get_payout_status( $payout ),
				'created_via'       => $payout->get_created_via(),
				'source_id'         => $payout->get_source_id(),
				'source_from'       => $payout->get_source_from(),
				'batch_id'          => $payout->get_batch_id(),
				'schedule'          => $payout->get_schedule(),
				'date_modified'     => current_time( 'mysql' ),
				'date_modified_gmt' => current_time( 'mysql', 1 ),
				'version'           => $payout->get_version(),
			);
			$table  = "{$wpdb_ref->prefix}mvr_payout";
			$where  = '`ID` = ' . $payout->get_id();
			$id     = mvr_update_row_query( $table, $format, $data, $where );

			if ( $id && ! is_wp_error( $id ) ) {
				$payout->apply_changes();

				/**
				 * Update Payout Hook
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_update_payout', $payout->get_id(), $payout );
			}
		}

		/**
		 * Delete an object, set the ID to 0.
		 *
		 * @since 1.0.0
		 * @param MVR_Payout $payout Payout object.
		 * @param Array      $args Array of args to pass to the delete method.
		 * @return Boolean
		 */
		public function delete( &$payout, $args = array() ) {
			$id = $payout->get_id();

			if ( ! $id ) {
				return;
			}

			global $wpdb;

			$wpdb_ref = &$wpdb;
			$result   = $wpdb_ref->delete( "{$wpdb->prefix}mvr_payout", array( 'ID' => $payout->get_id() ) );

			if ( ! $result ) {
				return false;
			}

			$payout->set_id( 0 );

			/**
			 * Delete Payout.
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_delete_payout', $id );

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
		 * @param MVR_Payout $payout Payout object.
		 * @return String
		 */
		protected function get_payout_status( $payout ) {
			$payout_status = $payout->get_status( 'edit' );

			if ( ! $payout_status ) {
				/**
				 * Default Payout Amount.
				 *
				 * @since 1.0.0
				 */
				$payout_status = apply_filters( 'mvr_default_paid_status', 'mvr-unpaid' );
			}

			if ( in_array( 'mvr-' . $payout_status, $payout->get_valid_statuses(), true ) ) {
				$payout_status = 'mvr-' . $payout_status;
			}

			return $payout_status;
		}
	}
}
