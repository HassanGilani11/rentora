<?php
/**
 * Order Data Store
 *
 * @package Multi Vendor Marketplace
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Order_Data_Store_CPT' ) ) {

	/**
	 * Order Data Store CPT
	 *
	 * @class MVR_Order_Data_Store_CPT
	 * @package Class
	 */
	class MVR_Order_Data_Store_CPT extends WC_Data_Store_WP implements WC_Object_Data_Store_Interface {

		/**
		 * Data stored props.
		 *
		 * @var Array
		 */
		protected $internal_props = array(
			'vendor_id',
			'date_created',
			'date_created_gmt',
			'order_id',
			'commission_id',
			'user_id',
			'mvr_user_id',
			'status',
			'created_via',
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
		 * @param MVR_Order $order Order object.
		 */
		public function create( &$order ) {
			global $wpdb;

			$wpdb_ref = &$wpdb;

			$order->set_version( MVR_VERSION );
			$order->set_date_created( time() );

			$table  = "{$wpdb->prefix}mvr_order";
			$format = array(
				'vendor_id'         => '%d',
				'date_created'      => '%s',
				'date_created_gmt'  => '%s',
				'order_id'          => '%d',
				'commission_id'     => '%d',
				'user_id'           => '%d',
				'mvr_user_id'       => '%d',
				'status'            => '%s',
				'created_via'       => '%s',
				'date_modified'     => '%s',
				'date_modified_gmt' => '%s',
				'version'           => '%s',
			);
			$data   = array(
				'vendor_id'         => $order->get_vendor_id(),
				'date_created'      => current_time( 'mysql' ),
				'date_created_gmt'  => current_time( 'mysql', 1 ),
				'order_id'          => $order->get_order_id(),
				'commission_id'     => $order->get_commission_id(),
				'user_id'           => $order->get_user_id(),
				'mvr_user_id'       => $order->get_mvr_user_id(),
				'status'            => $this->get_order_status( $order ),
				'created_via'       => $order->get_created_via(),
				'date_modified'     => current_time( 'mysql' ),
				'date_modified_gmt' => current_time( 'mysql', 1 ),
				'version'           => $order->get_version(),
			);

			$id = mvr_insert_row_query( $table, $data, $format );

			if ( $id && ! is_wp_error( $id ) ) {
				$order->set_id( $id );
				$order->apply_changes();

				/**
				 * New Order Hook
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_new_order', $order->get_id(), $order );
			}
		}

		/**
		 * Method to read data from the database.
		 *
		 * @since 1.0.0
		 * @param MVR_Order $order Order object.
		 * @throws Exception Invalid Post.
		 */
		public function read( &$order ) {
			$order->set_defaults();

			if ( ! $order->get_id() ) {
				throw new Exception( esc_html__( 'Invalid Order.', 'multi-vendor-marketplace' ) );
			}

			global $wpdb;
			$wpdb_ref = &$wpdb;
			$data     = $wpdb_ref->get_row(
				$wpdb_ref->prepare( "SELECT * from {$wpdb->prefix}mvr_order WHERE ID=%d", $order->get_id() )
			);

			foreach ( $this->internal_props as $prop ) {
				$setter = "set_$prop";

				if ( is_callable( array( $order, $setter ) ) && is_object( $data ) && property_exists( $data, $prop ) ) {
					$order->{$setter}( $data->$prop );
				}
			}

			$order->set_object_read( true );
		}

		/**
		 * Method to update changes in the database.
		 *
		 * @since 1.0.0
		 * @param MVR_Order $order Order object.
		 */
		public function update( &$order ) {
			global $wpdb;
			$wpdb_ref = &$wpdb;
			$order->set_version( MVR_VERSION );

			if ( ! $order->get_date_created( 'edit' ) ) {
				$order->set_date_created( time() );
			}

			$format = array(
				'vendor_id'         => '%d',
				'order_id'          => '%d',
				'commission_id'     => '%d',
				'user_id'           => '%d',
				'mvr_user_id'       => '%d',
				'status'            => '%s',
				'created_via'       => '%s',
				'date_modified'     => '%s',
				'date_modified_gmt' => '%s',
				'version'           => '%s',
			);
			$data   = array(
				'vendor_id'         => $order->get_vendor_id(),
				'order_id'          => $order->get_order_id(),
				'commission_id'     => $order->get_commission_id(),
				'user_id'           => $order->get_user_id(),
				'mvr_user_id'       => $order->get_mvr_user_id(),
				'status'            => $this->get_order_status( $order ),
				'created_via'       => $order->get_created_via(),
				'date_modified'     => current_time( 'mysql' ),
				'date_modified_gmt' => current_time( 'mysql', 1 ),
				'version'           => $order->get_version(),
			);
			$table  = "{$wpdb_ref->prefix}mvr_order";
			$where  = '`ID` = ' . $order->get_id();
			$id     = mvr_update_row_query( $table, $format, $data, $where );

			if ( $id && ! is_wp_error( $id ) ) {
				$order->apply_changes();

				/**
				 * Update Order Hook
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_update_order', $order->get_id(), $order );
			}
		}

		/**
		 * Delete an object, set the ID to 0.
		 *
		 * @since 1.0.0
		 * @param MVR_Order $order Order object.
		 * @param Array     $args Array of args to pass to the delete method.
		 * @return Boolean
		 */
		public function delete( &$order, $args = array() ) {
			$id = $order->get_id();

			if ( ! $id ) {
				return false;
			}

			global $wpdb;

			$wpdb_ref = &$wpdb;
			$result   = $wpdb_ref->delete( "{$wpdb->prefix}mvr_order", array( 'ID' => $order->get_id() ) );

			if ( ! $result ) {
				return false;
			}

			$order->set_id( 0 );

			/**
			 * Delete Order.
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_delete_order', $id );

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
		 * @param MVR_Order $order Order object.
		 * @return String
		 */
		protected function get_order_status( $order ) {
			$order_status = $order->get_status( 'edit' );

			if ( ! $order_status ) {
				/**
				 * Default Commission Status.
				 *
				 * @since 1.0.0
				 */
				$order_status = apply_filters( 'mvr_default_order_status', 'wc-pending' );
			}

			if ( in_array( 'wc-' . $order_status, $order->get_valid_statuses(), true ) ) {
				$order_status = 'wc-' . $order_status;
			}

			return $order_status;
		}
	}
}
