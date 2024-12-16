<?php
/**
 * Order Functions.
 *
 * @package Multi-Vendor Functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'mvr_is_order' ) ) {
	/**
	 * Check whether the given the value is Order.
	 *
	 * @since 1.0.0
	 * @param  Mixed $order Post object or post ID of the Order.
	 * @return Boolean True on success.
	 */
	function mvr_is_order( $order ) {
		return $order && is_a( $order, 'MVR_Order' );
	}
}

if ( ! function_exists( 'mvr_get_order' ) ) {
	/**
	 * Get Order.
	 *
	 * @since 1.0.0
	 * @param MVR_Order $order Order.
	 * @param Boolean   $wp_error WordPress error.
	 * @return bool|\MVR_Order
	 */
	function mvr_get_order( $order, $wp_error = false ) {
		if ( ! $order ) {
			return false;
		}

		try {
			$order = new MVR_Order( $order );
		} catch ( Exception $e ) {
			return $wp_error ? new WP_Error( 'error', $e->getMessage() ) : false;
		}

		return $order;
	}
}

if ( ! function_exists( 'mvr_get_orders' ) ) {
	/**
	 * Return the array of orders based upon the args requested.
	 *
	 * @since 1.0.0
	 * @param Array $args Arguments.
	 * @return Object
	 */
	function mvr_get_orders( $args = array() ) {
		global $wpdb;
		$wpdb_ref = &$wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'status'        => array_keys( wc_get_order_statuses() ),
				'include_ids'   => array(),
				'exclude_ids'   => array(),
				's'             => '',
				'vendor_id'     => '',
				'order_id'      => '',
				'commission_id' => '',
				'user_id'       => '',
				'mvr_user_id'   => '',
				'page'          => 1,
				'limit'         => -1,
				'fields'        => 'objects',
				'orderby'       => 'ID',
				'order'         => 'DESC',
				'date_before'   => '',
				'date_after'    => '',
			)
		);

		// Add the 'mvr-' prefix to status if needed.
		if ( ! empty( $args['status'] ) ) {
			if ( is_array( $args['status'] ) ) {
				foreach ( $args['status'] as &$status ) {
					if ( wc_is_order_status( 'wc-' . $status ) ) {
						$status = 'wc-' . $status;
					}
				}
			} elseif ( wc_is_order_status( 'wc-' . $args['status'] ) ) {
					$args['status'] = 'wc-' . $args['status'];
			}
		}

		// Statuses.
		if ( is_array( $args['status'] ) ) {
			$allowed_statuses = " AND status IN ('" . implode( "','", $args['status'] ) . "') ";
		} else {
			$allowed_statuses = " AND status = '" . esc_sql( $args['status'] ) . "' ";
		}

		// Search term.
		if ( ! empty( $args['s'] ) ) {
			$term          = str_replace( '#', '', wc_clean( wp_unslash( $args['s'] ) ) );
			$search_fields = array();
			$search_where  = " AND ( 
                (ID LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR
                (vendor_id LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR
                (order_id LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR
				(status LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR 
				(created_via LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR 
				(user_id LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR 
				(mvr_user_id LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR 
				(commission_id LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%')
                ) ";
		} else {
			$search_where = '';
		}

		// Includes.
		if ( ! empty( $args['include_ids'] ) ) {
			$include_ids = " AND ID IN ('" . implode( "','", $args['include_ids'] ) . "') ";
		} else {
			$include_ids = '';
		}

		// Excludes.
		if ( ! empty( $args['exclude_ids'] ) ) {
			$exclude_ids = " AND ID NOT IN ('" . implode( "','", $args['exclude_ids'] ) . "') ";
		} else {
			$exclude_ids = '';
		}

		// Allowed Vendors.
		if ( ! empty( $args['vendor_id'] ) ) {
			if ( is_array( $args['vendor_id'] ) ) {
				$allowed_vendors = " AND vendor_id IN ('" . implode( "','", $args['vendor_id'] ) . "') ";
			} else {
				$allowed_vendors = " AND vendor_id = '" . esc_sql( $args['vendor_id'] ) . "' ";
			}
		} else {
			$allowed_vendors = '';
		}

		// Order ID.
		if ( ! empty( $args['order_id'] ) ) {
			if ( is_array( $args['order_id'] ) ) {
				$allowed_orders = " AND order_id IN ('" . implode( "','", $args['order_id'] ) . "') ";
			} else {
				$allowed_orders = " AND order_id = '" . esc_sql( $args['order_id'] ) . "' ";
			}
		} else {
			$allowed_orders = '';
		}

		// Commission ID.
		if ( ! empty( $args['commission_id'] ) ) {
			if ( is_array( $args['commission_id'] ) ) {
				$allowed_commissions = " AND commission_id IN ('" . implode( "','", $args['commission_id'] ) . "') ";
			} else {
				$allowed_commissions = " AND commission_id = '" . esc_sql( $args['commission_id'] ) . "' ";
			}
		} else {
			$allowed_commissions = '';
		}

		// User ID.
		if ( ! empty( $args['user_id'] ) ) {
			if ( is_array( $args['user_id'] ) ) {
				$allowed_users = " AND user_id IN ('" . implode( "','", $args['user_id'] ) . "') ";
			} else {
				$allowed_users = " AND user_id = '" . esc_sql( $args['user_id'] ) . "' ";
			}
		} else {
			$allowed_users = '';
		}

		// Allowed MVR User ID.
		if ( ! empty( $args['mvr_user_id'] ) ) {
			if ( is_array( $args['mvr_user_id'] ) ) {
				$allowed_mvr_users = " AND mvr_user_id IN ('" . implode( "','", $args['mvr_user_id'] ) . "') ";
			} else {
				$allowed_mvr_users = " AND mvr_user_id = '" . esc_sql( $args['mvr_user_id'] ) . "' ";
			}
		} else {
			$allowed_mvr_users = '';
		}

		// Date Before.
		if ( ! empty( $args['date_before'] ) ) {
			$date_before = 'AND date_created <="' . gmdate( 'Y-m-d H:i:s', absint( $args['date_before'] ) ) . '"';
		} else {
			$date_before = '';
		}

		// Date after.
		if ( ! empty( $args['date_after'] ) ) {
			$date_after = ' AND date_created >= "' . gmdate( 'Y-m-d H:i:s', absint( $args['date_after'] ) ) . '"';
		} else {
			$date_after = '';
		}

		// Order by.
		switch ( ! empty( $args['orderby'] ) ? $args['orderby'] : 'ID' ) {
			case 'ID':
				$orderby = ' ORDER BY ' . esc_sql( $args['orderby'] ) . ' ';
				break;
			default:
				$orderby = ' ORDER BY ID ';
				break;
		}

		// Order.
		if ( ! empty( $args['order'] ) && 'desc' === strtolower( $args['order'] ) ) {
			$order = ' DESC ';
		} else {
			$order = ' ASC ';
		}

		// Paging.
		if ( $args['limit'] >= 0 ) {
			$page   = absint( $args['page'] );
			$page   = $page ? $page : 1;
			$offset = absint( ( $page - 1 ) * $args['limit'] );
			$limits = 'LIMIT ' . $offset . ', ' . $args['limit'];
		} else {
			$limits = '';
		}

		$all_order_ids = $wpdb_ref->get_var(
			"SELECT COUNT(DISTINCT ID) FROM {$wpdb->prefix}mvr_order AS o
			WHERE 1=1 {$allowed_statuses} {$search_where} {$include_ids} {$exclude_ids} {$allowed_vendors} {$allowed_orders} {$allowed_users} {$allowed_mvr_users} {$allowed_commissions} {$date_before} {$date_after}"
		);

		$order_ids = $wpdb_ref->get_col(
			"SELECT DISTINCT ID FROM {$wpdb->prefix}mvr_order AS o
			WHERE 1=1 {$allowed_statuses} {$search_where} {$include_ids} {$exclude_ids}  {$allowed_vendors} {$allowed_orders} {$allowed_users} {$allowed_mvr_users} {$allowed_commissions} {$date_before} {$date_after}
			{$orderby} {$order} {$limits}"
		);

		if ( 'objects' === $args['fields'] ) {
			$orders = array_filter( array_combine( $order_ids, array_map( 'mvr_get_order', $order_ids ) ) );
		} else {
			$orders = $order_ids;
		}

		$orders_count = count( $orders );
		$query_object = (object) array(
			'orders'        => $orders,
			'total_orders'  => $all_order_ids,
			'has_order'     => $orders_count > 0,
			'max_num_pages' => $args['limit'] > 0 ? ceil( $all_order_ids / $args['limit'] ) : 1,
		);

		return $query_object;
	}
}
