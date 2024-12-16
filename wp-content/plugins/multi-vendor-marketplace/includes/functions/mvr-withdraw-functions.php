<?php
/**
 * Withdraw Functions.
 *
 * @package Multi-Vendor Functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'mvr_is_withdraw' ) ) {
	/**
	 * Check whether the given the value is withdraw.
	 *
	 * @since 1.0.0
	 * @param  Mixed $withdraw Post object or post ID of the withdraw.
	 * @return Boolean True on success.
	 */
	function mvr_is_withdraw( $withdraw ) {
		return $withdraw && is_a( $withdraw, 'MVR_Withdraw' );
	}
}

if ( ! function_exists( 'mvr_get_withdraw_statuses' ) ) {
	/**
	 * Get withdraw statuses.
	 *
	 * @since 1.0.0
	 * @return Array
	 */
	function mvr_get_withdraw_statuses() {
		return array(
			'mvr-pending'  => __( 'Pending', 'multi-vendor-marketplace' ),
			'mvr-progress' => __( 'In-Progress', 'multi-vendor-marketplace' ),
			'mvr-success'  => __( 'Success', 'multi-vendor-marketplace' ),
			'mvr-failed'   => __( 'Failed', 'multi-vendor-marketplace' ),
		);
	}
}

if ( ! function_exists( 'mvr_get_withdraw_status_name' ) ) {
	/**
	 * Get the withdraw status name.
	 *
	 * @since 1.0.0
	 * @param String $status Status name.
	 * @return String
	 */
	function mvr_get_withdraw_status_name( $status ) {
		$statuses = mvr_get_withdraw_statuses();
		$status   = mvr_trim_post_status( $status );

		return isset( $statuses[ "mvr-{$status}" ] ) ? $statuses[ "mvr-{$status}" ] : $status;
	}
}

if ( ! function_exists( 'mvr_is_withdraw_status' ) ) {
	/**
	 * See if a string is an withdraw status.
	 *
	 * @since 1.0.0
	 * @param  String $maybe_status Status, including any mvr- prefix.
	 * @return Boolean
	 */
	function mvr_is_withdraw_status( $maybe_status ) {
		$statuses = mvr_get_withdraw_statuses();

		return isset( $statuses[ $maybe_status ] );
	}
}

if ( ! function_exists( 'mvr_get_withdraw' ) ) {
	/**
	 * Get withdraw.
	 *
	 * @since 1.0.0
	 * @param MVR_Withdraw $withdraw Withdraw.
	 * @param Boolean      $wp_error WordPress error.
	 * @return bool|MVR_Withdraw
	 */
	function mvr_get_withdraw( $withdraw, $wp_error = false ) {
		if ( ! $withdraw ) {
			return false;
		}

		try {
			$withdraw = new MVR_Withdraw( $withdraw );
		} catch ( Exception $e ) {
			return $wp_error ? new WP_Error( 'error', $e->getMessage() ) : false;
		}

		return $withdraw;
	}
}

if ( ! function_exists( 'mvr_get_withdraws' ) ) {
	/**
	 * Return the array of withdraws based upon the args requested.
	 *
	 * @since 1.0.0
	 * @param Array $args Arguments.
	 * @return Object
	 */
	function mvr_get_withdraws( $args = array() ) {
		global $wpdb;
		$wpdb_ref = &$wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'status'             => array_keys( mvr_get_withdraw_statuses() ),
				'vendor_id'          => '',
				'include_ids'        => array(),
				'exclude_ids'        => array(),
				'include_vendor_ids' => array(),
				'exclude_vendor_ids' => array(),
				'payment_method'     => '',
				's'                  => '',
				'page'               => 1,
				'limit'              => -1,
				'fields'             => 'objects',
				'orderby'            => 'ID',
				'order'              => 'DESC',
				'date_before'        => '',
				'date_after'         => '',
			)
		);

		// Add the 'mvr-' prefix to status if needed.
		if ( ! empty( $args['status'] ) ) {
			if ( is_array( $args['status'] ) ) {
				foreach ( $args['status'] as &$status ) {
					if ( mvr_is_withdraw_status( 'mvr-' . $status ) ) {
						$status = 'mvr-' . $status;
					}
				}
			} elseif ( mvr_is_withdraw_status( 'mvr-' . $args['status'] ) ) {
				$args['status'] = 'mvr-' . $args['status'];
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
                (amount LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR
				(charge_amount LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR
				(status LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR 
				(created_via LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') 
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

		// Includes.
		if ( ! empty( $args['include_vendor_ids'] ) ) {
			$include_vendor_ids = " AND vendor_id IN ('" . implode( "','", $args['include_vendor_ids'] ) . "') ";
		} else {
			$include_vendor_ids = '';
		}

		// Excludes.
		if ( ! empty( $args['exclude_vendor_ids'] ) ) {
			$exclude_vendor_ids = " AND vendor_id NOT IN ('" . implode( "','", $args['exclude_vendor_ids'] ) . "') ";
		} else {
			$exclude_vendor_ids = '';
		}

		// Vendor ID.
		if ( is_array( $args['vendor_id'] ) ) {
			$allowed_vendor = " AND vendor_id IN ('" . implode( "','", $args['vendor_id'] ) . "') ";
		} elseif ( ! empty( $args['vendor_id'] ) ) {
			$allowed_vendor = " AND vendor_id = '" . esc_sql( $args['vendor_id'] ) . "' ";
		} else {
			$allowed_vendor = '';
		}

		// Allowed Payment Method.
		if ( is_array( $args['payment_method'] ) ) {
			$allowed_payment_method = " AND payment_method IN ('" . implode( "','", $args['payment_method'] ) . "') ";
		} elseif ( ! empty( $args['payment_method'] ) ) {
			$allowed_payment_method = " AND payment_method = '" . esc_sql( $args['payment_method'] ) . "' ";
		} else {
			$allowed_payment_method = '';
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
		switch ( ! empty( $args['orderby'] ) ? $args['orderby'] : 'menu_order' ) {
			case 'ID':
				$orderby = ' ORDER BY ' . esc_sql( $args['orderby'] ) . ' ';
				break;
			default:
				$orderby = ' ORDER BY menu_order ';
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

		$all_withdraw_ids = $wpdb_ref->get_var(
			"
			SELECT COUNT(DISTINCT ID) FROM {$wpdb->prefix}mvr_withdraw AS w
			WHERE 1=1 {$allowed_statuses} {$search_where} {$include_ids} {$exclude_ids} {$allowed_vendor} {$allowed_payment_method} {$include_vendor_ids} {$exclude_vendor_ids} {$date_before} {$date_after}
			"
		);

		$withdraw_ids = $wpdb_ref->get_col(
			"
			SELECT DISTINCT ID FROM {$wpdb->prefix}mvr_withdraw AS w
			WHERE 1=1 {$allowed_statuses} {$search_where} {$include_ids} {$exclude_ids} {$allowed_vendor} {$allowed_payment_method} {$include_vendor_ids} {$exclude_vendor_ids} {$date_before} {$date_after}
			{$orderby} {$order} {$limits} 
			"
		);

		if ( 'objects' === $args['fields'] ) {
			$withdraws = array_filter( array_combine( $withdraw_ids, array_map( 'mvr_get_withdraw', $withdraw_ids ) ) );
		} else {
			$withdraws = $withdraw_ids;
		}

		$withdraws_count = count( $withdraws );
		$query_object    = (object) array(
			'withdraws'       => $withdraws,
			'total_withdraws' => $all_withdraw_ids,
			'has_withdraw'    => $withdraws_count > 0,
			'max_num_pages'   => $args['limit'] > 0 ? ceil( $all_withdraw_ids / $args['limit'] ) : 1,
		);

		return $query_object;
	}
}
