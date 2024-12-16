<?php
/**
 * Customer Functions.
 *
 * @package Multi-Vendor Functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'mvr_is_customer' ) ) {
	/**
	 * Check whether the given the value is Customer of vendor.
	 *
	 * @since 1.0.0
	 * @param  Mixed $customer Post object or post ID of the customer.
	 * @return Boolean True on success.
	 */
	function mvr_is_customer( $customer ) {
		return $customer && is_a( $customer, 'MVR_Customer' );
	}
}

if ( ! function_exists( 'mvr_get_customer' ) ) {
	/**
	 * Get customer.
	 *
	 * @since 1.0.0
	 * @param MVR_Customer $customer Customer.
	 * @param Boolean      $wp_error WordPress error.
	 * @return bool|\MVR_Customer
	 */
	function mvr_get_customer( $customer, $wp_error = false ) {
		if ( ! $customer ) {
			return false;
		}

		try {
			$customer = new MVR_Customer( $customer );
		} catch ( Exception $e ) {
			return $wp_error ? new WP_Error( 'error', $e->getMessage() ) : false;
		}

		return $customer;
	}
}

if ( ! function_exists( 'mvr_get_customers' ) ) {
	/**
	 * Return the array of customers based upon the args requested.
	 *
	 * @since 1.0.0
	 * @param Array $args Arguments.
	 * @return Object
	 */
	function mvr_get_customers( $args = array() ) {
		global $wpdb;

		$wpdb_ref = &$wpdb;
		$args     = wp_parse_args(
			$args,
			array(
				'include_ids' => array(),
				'exclude_ids' => array(),
				's'           => '',
				'source_id'   => '',
				'source_from' => '',
				'vendor_id'   => '',
				'user_id'     => '',
				'email'       => '',
				'page'        => 1,
				'limit'       => -1,
				'fields'      => 'objects',
				'orderby'     => 'ID',
				'order'       => 'DESC',
			)
		);

		// Search term.
		if ( ! empty( $args['s'] ) ) {
			$term          = str_replace( '#', '', wc_clean( wp_unslash( $args['s'] ) ) );
			$search_fields = array();
			$search_where  = " AND ( 
                (ID LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR
                (vendor_id LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR
				(source_id LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR
				(created_via LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR 
				(user_id LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR 
				(email LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%')
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

		// User Email.
		if ( ! empty( $args['email'] ) ) {
			if ( is_array( $args['email'] ) ) {
				$allowed_emails = " AND email IN ('" . implode( "','", $args['email'] ) . "') ";
			} else {
				$allowed_emails = " AND email = '" . esc_sql( $args['email'] ) . "' ";
			}
		} else {
			$allowed_emails = '';
		}

		// Source ID.
		if ( ! empty( $args['source_id'] ) ) {
			if ( is_array( $args['source_id'] ) ) {
				$allowed_source_ids = " AND source_id IN ('" . implode( "','", $args['source_id'] ) . "') ";
			} else {
				$allowed_source_ids = " AND source_id = '" . esc_sql( $args['source_id'] ) . "' ";
			}
		} else {
			$allowed_source_ids = '';
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

		$all_customer_ids = $wpdb_ref->get_var(
			"SELECT COUNT(DISTINCT ID) FROM {$wpdb->prefix}mvr_customer AS c
			WHERE 1=1 {$search_where} {$include_ids} {$exclude_ids} {$allowed_vendors} {$allowed_users} {$allowed_emails} {$allowed_source_ids}"
		);

		$customer_ids = $wpdb_ref->get_col(
			"SELECT DISTINCT ID FROM {$wpdb->prefix}mvr_customer AS c
			WHERE 1=1 {$search_where} {$include_ids} {$exclude_ids}  {$allowed_vendors} {$allowed_users} {$allowed_emails} {$allowed_source_ids}
			{$orderby} {$order} {$limits}"
		);

		if ( 'objects' === $args['fields'] ) {
			$customers = array_filter( array_combine( $customer_ids, array_map( 'mvr_get_customer', $customer_ids ) ) );
		} else {
			$customers = $customer_ids;
		}

		$customers_count = count( $customers );
		$query_object    = (object) array(
			'customers'       => $customers,
			'total_customers' => $all_customer_ids,
			'has_customer'    => $customers_count > 0,
			'max_num_pages'   => $args['limit'] > 0 ? ceil( $all_customer_ids / $args['limit'] ) : 1,
		);

		return $query_object;
	}
}
