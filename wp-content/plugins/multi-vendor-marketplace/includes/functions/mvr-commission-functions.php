<?php
/**
 * Commission Functions.
 *
 * @package Multi-Vendor Functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'mvr_is_commission' ) ) {
	/**
	 * Check whether the given the value is commission.
	 *
	 * @since 1.0.0
	 * @param  Mixed $commission Post object or post ID of the Commission.
	 * @return Boolean True on success.
	 */
	function mvr_is_commission( $commission ) {
		return $commission && is_a( $commission, 'MVR_Commission' );
	}
}

if ( ! function_exists( 'mvr_get_commission' ) ) {
	/**
	 * Get Commission.
	 *
	 * @since 1.0.0
	 * @param MVR_Commission $commission Commission.
	 * @param Boolean        $wp_error WordPress error.
	 * @return bool|\MVR_Commission
	 */
	function mvr_get_commission( $commission, $wp_error = false ) {
		if ( ! $commission ) {
			return false;
		}

		try {
			$commission = new MVR_Commission( $commission );
		} catch ( Exception $e ) {
			return $wp_error ? new WP_Error( 'error', $e->getMessage() ) : false;
		}

		return $commission;
	}
}

if ( ! function_exists( 'mvr_get_commission_sources' ) ) {
	/**
	 * Get Commission sources.
	 *
	 * @since 1.0.0
	 * @return Array
	 */
	function mvr_get_commission_sources() {
		return array(
			'order'    => __( 'Order', 'multi-vendor-marketplace' ),
			'withdraw' => __( 'Withdraw', 'multi-vendor-marketplace' ),
		);
	}
}

if ( ! function_exists( 'mvr_get_commission_source_name' ) ) {
	/**
	 * Get the commission Source name.
	 *
	 * @since 1.0.0
	 * @param String $source Source name.
	 * @return String
	 */
	function mvr_get_commission_source_name( $source ) {
		$sources = mvr_get_commission_sources();

		return isset( $sources[ "{$source}" ] ) ? $sources[ "{$source}" ] : $source;
	}
}

if ( ! function_exists( 'mvr_get_commission_statuses' ) ) {
	/**
	 * Get Commission statuses.
	 *
	 * @since 1.0.0
	 * @return Array
	 */
	function mvr_get_commission_statuses() {
		return array(
			'mvr-paid'    => __( 'Paid', 'multi-vendor-marketplace' ),
			'mvr-pending' => __( 'Pending', 'multi-vendor-marketplace' ),
			'mvr-failed'  => __( 'Failed', 'multi-vendor-marketplace' ),
		);
	}
}

if ( ! function_exists( 'mvr_get_commission_status_name' ) ) {
	/**
	 * Get the commission status name.
	 *
	 * @since 1.0.0
	 * @param String $status Status name.
	 * @return String
	 */
	function mvr_get_commission_status_name( $status ) {
		$statuses = mvr_get_commission_statuses();
		$status   = mvr_trim_post_status( $status );

		return isset( $statuses[ "mvr-{$status}" ] ) ? $statuses[ "mvr-{$status}" ] : $status;
	}
}

if ( ! function_exists( 'mvr_is_commission_status' ) ) {
	/**
	 * See if a string is an commission status.
	 *
	 * @since 1.0.0
	 * @param  String $maybe_status Status, including any mvr- prefix.
	 * @return Boolean
	 */
	function mvr_is_commission_status( $maybe_status ) {
		$statuses = mvr_get_commission_statuses();

		return isset( $statuses[ $maybe_status ] );
	}
}

if ( ! function_exists( 'mvr_get_commissions' ) ) {
	/**
	 * Return the array of commissions based upon the args requested.
	 *
	 * @since 1.0.0
	 * @param Array $args Arguments.
	 * @return Object
	 */
	function mvr_get_commissions( $args = array() ) {
		global $wpdb;
		$wpdb_ref = &$wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'status'      => array_keys( mvr_get_commission_statuses() ),
				'include_ids' => array(),
				'exclude_ids' => array(),
				's'           => '',
				'source_id'   => '',
				'source_from' => '',
				'vendor_id'   => '',
				'page'        => 1,
				'limit'       => -1,
				'fields'      => 'objects',
				'orderby'     => 'ID',
				'order'       => 'DESC',
				'date_before' => '',
				'date_after'  => '',
			)
		);

		// Add the 'mvr-' prefix to status if needed.
		if ( ! empty( $args['status'] ) ) {
			if ( is_array( $args['status'] ) ) {
				foreach ( $args['status'] as &$status ) {
					if ( mvr_is_commission_status( 'mvr-' . $status ) ) {
						$status = 'mvr-' . $status;
					}
				}
			} elseif ( mvr_is_commission_status( 'mvr-' . $args['status'] ) ) {
				$args['status'] = 'mvr-' . $args['status'];
			}
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

		// Source From.
		if ( ! empty( $args['source_from'] ) ) {
			if ( is_array( $args['source_from'] ) ) {
				$allowed_source_from = " AND source_from IN ('" . implode( "','", $args['source_from'] ) . "') ";
			} else {
				$allowed_source_from = " AND source_from = '" . esc_sql( $args['source_from'] ) . "' ";
			}
		} else {
			$allowed_source_from = '';
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

		$all_commission_ids = $wpdb_ref->get_var(
			"SELECT COUNT(DISTINCT ID) FROM {$wpdb->prefix}mvr_commission AS c
			WHERE 1=1 {$allowed_statuses} {$search_where} {$include_ids} {$exclude_ids} {$allowed_vendors} {$allowed_source_ids} {$allowed_source_from} {$date_before} {$date_after}"
		);

		$commission_ids = $wpdb_ref->get_col(
			"SELECT DISTINCT ID FROM {$wpdb->prefix}mvr_commission AS c
			WHERE 1=1 {$allowed_statuses} {$search_where} {$include_ids} {$exclude_ids} {$allowed_vendors} {$allowed_source_ids} {$allowed_source_from} {$date_before} {$date_after}
			{$orderby} {$order} {$limits}"
		);

		if ( 'objects' === $args['fields'] ) {
			$commissions = array_filter( array_combine( $commission_ids, array_map( 'mvr_get_commission', $commission_ids ) ) );
		} else {
			$commissions = $commission_ids;
		}

		$commissions_count = count( $commissions );
		$query_object      = (object) array(
			'commissions'       => $commissions,
			'total_commissions' => $all_commission_ids,
			'has_commission'    => $commissions_count > 0,
			'max_num_pages'     => $args['limit'] > 0 ? ceil( $all_commission_ids / $args['limit'] ) : 1,
		);

		return $query_object;
	}
}
