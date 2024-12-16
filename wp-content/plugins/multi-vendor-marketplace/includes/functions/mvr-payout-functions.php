<?php
/**
 * Payout Functions.
 *
 * @package Multi-Vendor Functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'mvr_is_payout' ) ) {
	/**
	 * Check whether the given the value is payout.
	 *
	 * @since 1.0.0
	 * @param  Mixed $payout Post object or post ID of the Payout.
	 * @return Boolean True on success.
	 */
	function mvr_is_payout( $payout ) {
		return $payout && is_a( $payout, 'MVR_Payout' );
	}
}

if ( ! function_exists( 'mvr_get_payout_statuses' ) ) {
	/**
	 * Get payout statuses.
	 *
	 * @since 1.0.0
	 * @return Array
	 */
	function mvr_get_payout_statuses() {
		return array(
			'mvr-paid'   => __( 'Paid', 'multi-vendor-marketplace' ),
			'mvr-unpaid' => __( 'Unpaid', 'multi-vendor-marketplace' ),
			'mvr-failed' => __( 'Failed', 'multi-vendor-marketplace' ),
		);
	}
}

if ( ! function_exists( 'mvr_get_payout_status_name' ) ) {
	/**
	 * Get the payout status name.
	 *
	 * @since 1.0.0
	 * @param String $status Status name.
	 * @return String
	 */
	function mvr_get_payout_status_name( $status ) {
		$statuses = mvr_get_payout_statuses();
		$status   = mvr_trim_post_status( $status );

		return isset( $statuses[ "mvr-{$status}" ] ) ? $statuses[ "mvr-{$status}" ] : $status;
	}
}

if ( ! function_exists( 'mvr_is_payout_status' ) ) {
	/**
	 * See if a string is an payout status.
	 *
	 * @since 1.0.0
	 * @param  String $maybe_status Status, including any mvr- prefix.
	 * @return Boolean
	 */
	function mvr_is_payout_status( $maybe_status ) {
		$statuses = mvr_get_payout_statuses();

		return isset( $statuses[ $maybe_status ] );
	}
}

if ( ! function_exists( 'mvr_get_payout' ) ) {
	/**
	 * Get Payout.
	 *
	 * @since 1.0.0
	 * @param MVR_Payout $payout Payout.
	 * @param Boolean    $wp_error WordPress error.
	 * @return bool|MVR_Payout
	 */
	function mvr_get_payout( $payout, $wp_error = false ) {
		if ( ! $payout ) {
			return false;
		}

		try {
			$payout = new MVR_Payout( $payout );
		} catch ( Exception $e ) {
			return $wp_error ? new WP_Error( 'error', $e->getMessage() ) : false;
		}

		return $payout;
	}
}

if ( ! function_exists( 'mvr_get_payouts' ) ) {
	/**
	 * Return the array of payouts based upon the args requested.
	 *
	 * @since 1.0.0
	 * @param Array $args Arguments.
	 * @return Object
	 */
	function mvr_get_payouts( $args = array() ) {
		global $wpdb;

		$wpdb_ref = &$wpdb;
		$args     = wp_parse_args(
			$args,
			array(
				'status'         => array_keys( mvr_get_payout_statuses() ),
				'vendor_id'      => '',
				'user_id'        => '',
				'batch_log_id'   => '',
				'source_id'      => '',
				'source_from'    => '',
				'include_ids'    => array(),
				'exclude_ids'    => array(),
				'payment_method' => '',
				's'              => '',
				'page'           => 1,
				'limit'          => -1,
				'fields'         => 'objects',
				'orderby'        => 'ID',
				'order'          => 'DESC',
			)
		);

		// Add the 'mvr-' prefix to status if needed.
		if ( ! empty( $args['status'] ) ) {
			if ( is_array( $args['status'] ) ) {
				foreach ( $args['status'] as &$status ) {
					if ( mvr_is_payout_status( 'mvr-' . $status ) ) {
						$status = 'mvr-' . $status;
					}
				}
			} elseif ( mvr_is_payout_status( 'mvr-' . $args['status'] ) ) {
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
				(user_id LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR
				(batch_log_id LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR
                (amount LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR
				(status LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR 
				(created_via LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR 
				(source_id LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR 
				(source_from LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%')
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

		// Payment Method.
		if ( is_array( $args['payment_method'] ) ) {
			$allowed_payment_method = " AND payment_method IN ('" . implode( "','", $args['payment_method'] ) . "') ";
		} elseif ( ! empty( $args['payment_method'] ) ) {
			$allowed_payment_method = " AND payment_method = '" . esc_sql( $args['payment_method'] ) . "' ";
		} else {
			$allowed_payment_method = '';
		}

		// Vendor ID.
		if ( is_array( $args['vendor_id'] ) ) {
			$allowed_vendor = " AND vendor_id IN ('" . implode( "','", $args['vendor_id'] ) . "') ";
		} elseif ( ! empty( $args['vendor_id'] ) ) {
			$allowed_vendor = " AND vendor_id = '" . esc_sql( $args['vendor_id'] ) . "' ";
		} else {
			$allowed_vendor = '';
		}

		// User ID.
		if ( is_array( $args['user_id'] ) ) {
			$allowed_user = " AND user_id IN ('" . implode( "','", $args['user_id'] ) . "') ";
		} elseif ( ! empty( $args['user_id'] ) ) {
			$allowed_user = " AND user_id = '" . esc_sql( $args['user_id'] ) . "' ";
		} else {
			$allowed_user = '';
		}

		// Payout Batch Log ID.
		if ( is_array( $args['batch_log_id'] ) ) {
			$allowed_batch_log = " AND batch_log_id IN ('" . implode( "','", $args['batch_log_id'] ) . "') ";
		} elseif ( ! empty( $args['batch_log_id'] ) ) {
			$allowed_batch_log = " AND batch_log_id = '" . esc_sql( $args['batch_log_id'] ) . "' ";
		} else {
			$allowed_batch_log = '';
		}

		// Source ID.
		if ( is_array( $args['source_id'] ) ) {
			$allowed_source_id = " AND source_id IN ('" . implode( "','", $args['source_id'] ) . "') ";
		} elseif ( ! empty( $args['source_id'] ) ) {
			$allowed_source_id = " AND source_id = '" . esc_sql( $args['source_id'] ) . "' ";
		} else {
			$allowed_source_id = '';
		}

		// Source Via.
		if ( is_array( $args['source_from'] ) ) {
			$allowed_source_from = " AND source_from IN ('" . implode( "','", $args['source_from'] ) . "') ";
		} elseif ( ! empty( $args['source_from'] ) ) {
			$allowed_source_from = " AND source_from = '" . esc_sql( $args['source_from'] ) . "' ";
		} else {
			$allowed_source_from = '';
		}

		// Order by.
		switch ( ! empty( $args['orderby'] ) ? $args['orderby'] : 'menu_order' ) {
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

		$all_payout_ids = $wpdb_ref->get_var(
			"SELECT COUNT(DISTINCT ID) FROM {$wpdb->prefix}mvr_payout AS p
			WHERE 1=1 {$allowed_statuses} {$search_where} {$include_ids} {$exclude_ids} {$allowed_vendor} {$allowed_payment_method} {$allowed_user} {$allowed_batch_log} {$allowed_source_id} {$allowed_source_from}"
		);

		$payout_ids = $wpdb_ref->get_col(
			"SELECT DISTINCT ID FROM {$wpdb->prefix}mvr_payout AS p
			WHERE 1=1 {$allowed_statuses} {$search_where} {$include_ids} {$exclude_ids} {$allowed_vendor} {$allowed_payment_method} {$allowed_user} {$allowed_batch_log} {$allowed_source_id} {$allowed_source_from} {$orderby} {$order} {$limits}"
		);

		if ( 'objects' === $args['fields'] ) {
			$payouts = array_filter( array_combine( $payout_ids, array_map( 'mvr_get_payout', $payout_ids ) ) );
		} else {
			$payouts = $payout_ids;
		}

		$payouts_count = count( $payouts );
		$query_object  = (object) array(
			'payouts'       => $payouts,
			'total_payouts' => (int) $all_payout_ids,
			'has_payout'    => $payouts_count > 0,
			'max_num_pages' => $args['limit'] > 0 ? ceil( $all_payout_ids / $args['limit'] ) : 1,
		);

		return $query_object;
	}
}
