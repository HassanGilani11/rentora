<?php
/**
 * Single Product Multi Vendor Functions.
 *
 * @package Multi-Vendor Functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'mvr_is_spmv' ) ) {
	/**
	 * Check whether the given the value is spmv.
	 *
	 * @since 1.0.0
	 * @param  Mixed $spmv Post object or post ID of the spmv.
	 * @return Boolean True on success.
	 */
	function mvr_is_spmv( $spmv ) {
		return $spmv && is_a( $spmv, 'MVR_SPMV' );
	}
}

if ( ! function_exists( 'mvr_get_spmv' ) ) {
	/**
	 * Get Singe Product Multi Vendor.
	 *
	 * @since 1.0.0
	 * @param MVR_SPMV $spmv Enquiry.
	 * @param Boolean  $wp_error WordPress error.
	 * @return bool|\MVR_SPMV
	 */
	function mvr_get_spmv( $spmv, $wp_error = false ) {
		if ( ! $spmv ) {
			return false;
		}

		try {
			$spmv = new MVR_SPMV( $spmv );
		} catch ( Exception $e ) {
			return $wp_error ? new WP_Error( 'error', $e->getMessage() ) : false;
		}

		return $spmv;
	}
}

if ( ! function_exists( 'mvr_get_all_spmv' ) ) {
	/**
	 * Return the array of Single Product Multiple Vendor data.
	 *
	 * @since 1.0.0
	 * @param Array $args Arguments.
	 * @return Object
	 */
	function mvr_get_all_spmv( $args = array() ) {
		global $wpdb;
		$wpdb_ref = &$wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'ID'                 => '',
				'include_ids'        => array(),
				'exclude_ids'        => array(),
				'product_id'         => '',
				'exclude_product_id' => '',
				'vendor_id'          => '',
				'map_id'             => '',
				'limit'              => -1,
				'orderby'            => 'ID',
				'order'              => 'DESC',
				'fields'             => 'objects',
			)
		);

		// ID.
		if ( ! empty( $args['ID'] ) ) {
			$id = " AND ID = '" . $args['ID'] . "' ";
		} else {
			$id = '';
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

		// Product ID.
		if ( ! empty( $args['product_id'] ) ) {
			if ( is_array( $args['product_id'] ) ) {
				$allowed_products = " AND product_id IN ('" . implode( "','", $args['product_id'] ) . "') ";
			} else {
				$allowed_products = " AND product_id = '" . esc_sql( $args['product_id'] ) . "' ";
			}
		} else {
			$allowed_products = '';
		}

		if ( ! empty( $args['exclude_product_id'] ) ) {
			if ( is_array( $args['exclude_product_id'] ) ) {
				$exclude_products = " AND product_id NOT IN ('" . implode( "','", $args['exclude_product_id'] ) . "') ";
			} else {
				$exclude_products = " AND product_id != '" . esc_sql( $args['exclude_product_id'] ) . "' ";
			}
		} else {
			$exclude_products = '';
		}

		if ( ! empty( $args['map_id'] ) ) {
			if ( is_array( $args['map_id'] ) ) {
				$allowed_map_ids = " AND map_id IN ('" . implode( "','", $args['map_id'] ) . "') ";
			} else {
				$allowed_map_ids = " AND map_id = '" . esc_sql( $args['map_id'] ) . "' ";
			}
		} else {
			$allowed_map_ids = '';
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

		$all_spmv_ids = $wpdb_ref->get_var(
			"SELECT COUNT(DISTINCT ID) FROM {$wpdb->prefix}mvr_product_map AS pm
			WHERE 1=1 {$id} {$include_ids} {$exclude_ids} {$allowed_vendors} {$allowed_products} {$exclude_products} {$allowed_map_ids}",
			ARRAY_A
		);

		$spmv_ids = $wpdb_ref->get_results(
			"SELECT DISTINCT ID FROM {$wpdb->prefix}mvr_product_map AS pm
			WHERE 1=1 {$id} {$include_ids} {$exclude_ids} {$allowed_vendors} {$allowed_products} {$exclude_products} {$allowed_map_ids}
			{$orderby} {$order} {$limits}",
			ARRAY_A
		);

		$spmv_ids = array_column( $spmv_ids, 'ID' );

		if ( 'objects' === $args['fields'] ) {
			$spmv_args = array_filter( array_combine( $spmv_ids, array_map( 'mvr_get_spmv', $spmv_ids ) ) );
		} else {
			$spmv_args = $spmv_ids;
		}

		$spmv_count   = count( $spmv_args );
		$query_object = (object) array(
			'spmv_args'     => $spmv_args,
			'total_spmv'    => $all_spmv_ids,
			'has_spmv'      => $spmv_count > 0,
			'max_num_pages' => $args['limit'] > 0 ? ceil( $all_spmv_ids / $args['limit'] ) : 1,
		);

		return $query_object;
	}
}

if ( ! function_exists( 'mvr_create_spmv_entry' ) ) {
	/**
	 * Create SPMV Entry.
	 *
	 * @since 1.0.0
	 * @param Integer $product_id Product ID.
	 * @param Integer $product_map_id Product Map ID.
	 */
	function mvr_create_spmv_entry( $product_id, $product_map_id = '' ) {
		if ( empty( $product_id ) ) {
			return false;
		}

		$product_obj = wc_get_product( $product_id );

		if ( ! is_a( $product_obj, 'WC_Product' ) ) {
			return false;
		}

		$vendor_id = $product_obj->get_meta( '_mvr_vendor', true );
		$vendor_id = $vendor_id ? $vendor_id : 0;

		global $wpdb;
		$wpdb_ref = &$wpdb;

		$product_map_id = ! empty( $product_map_id ) ? $product_map_id : mvr_get_map_id();

		$table  = "{$wpdb->prefix}mvr_product_map";
		$format = array(
			'map_id'           => '%d',
			'product_id'       => '%d',
			'vendor_id'        => '%d',
			'date_created'     => '%s',
			'date_created_gmt' => '%s',
			'parent_id'        => '%d',
			'version'          => '%s',
		);
		$data   = array(
			'map_id'           => $product_map_id,
			'product_id'       => $product_id,
			'vendor_id'        => $vendor_id,
			'date_created'     => current_time( 'mysql' ),
			'date_created_gmt' => current_time( 'mysql', 1 ),
			'parent_id'        => 0,
			'version'          => MVR_VERSION,
		);

		$id = mvr_insert_row_query( $table, $data, $format );

		return $id;
	}
}

if ( ! function_exists( 'mvr_get_map_id' ) ) {
	/**
	 * Get mapping ID for next execution
	 *
	 * @since 1.0.0
	 * @return Integer
	 */
	function mvr_get_map_id() {
		global $wpdb;

		$wpdb_ref   = &$wpdb;
		$current_id = $wpdb_ref->get_var( "SELECT MAX(`map_id`) as max_id FROM `{$wpdb->prefix}mvr_product_map`" );

		if ( ! $current_id ) {
			return 1;
		}

		return $current_id + 1;
	}
}


if ( ! function_exists( 'mvr_get_spmv_product_map_ids' ) ) {
	/**
	 * Return the array of Single Product Multiple Vendor data.
	 *
	 * @since 1.0.0
	 * @param Array $args Arguments.
	 * @return Array
	 */
	function mvr_get_spmv_product_map_ids( $args = array() ) {
		global $wpdb;

		$wpdb_ref = &$wpdb;
		$args     = wp_parse_args(
			$args,
			array(
				'product_id' => '',
				'vendor_id'  => '',
			)
		);

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

		// Product ID.
		if ( ! empty( $args['product_id'] ) ) {
			if ( is_array( $args['product_id'] ) ) {
				$allowed_products = " AND product_id IN ('" . implode( "','", $args['product_id'] ) . "') ";
			} else {
				$allowed_products = " AND product_id = '" . esc_sql( $args['product_id'] ) . "' ";
			}
		} else {
			$allowed_products = '';
		}

		$spmv_args = $wpdb_ref->get_col(
			"SELECT DISTINCT map_id FROM {$wpdb->prefix}mvr_product_map AS pm
			WHERE 1=1 {$allowed_vendors} {$allowed_products}"
		);

		return $spmv_args;
	}
}

if ( ! function_exists( 'mvr_get_spmv_product_ids' ) ) {
	/**
	 * Return the array of Single Product Multiple Vendor data.
	 *
	 * @since 1.0.0
	 * @param Integer $product_map_id Product Map ID.
	 * @return Array
	 */
	function mvr_get_spmv_product_ids( $product_map_id = '' ) {
		global $wpdb;
		$wpdb_ref = &$wpdb;

		if ( empty( $product_map_id ) ) {
			return false;
		}

		// Product ID.
		if ( ! empty( $product_map_id ) ) {
			if ( is_array( $product_map_id ) ) {
				$allowed_map_ids = " AND map_id IN ('" . implode( "','", $product_map_id ) . "') ";
			} else {
				$allowed_map_ids = " AND map_id = '" . esc_sql( $product_map_id ) . "' ";
			}
		} else {
			$allowed_map_ids = '';
		}

		$spmv_args = $wpdb_ref->get_col(
			"SELECT DISTINCT product_id FROM {$wpdb->prefix}mvr_product_map AS pm
			WHERE 1=1 {$allowed_map_ids}"
		);

		return $spmv_args;
	}
}

if ( ! function_exists( 'mvr_get_spmv_vendor_ids' ) ) {
	/**
	 * Return the array of Single Product Multiple Vendor data.
	 *
	 * @since 1.0.0
	 * @param Integer $product_map_id Product Map ID.
	 * @return Array
	 */
	function mvr_get_spmv_vendor_ids( $product_map_id = '' ) {
		global $wpdb;
		$wpdb_ref = &$wpdb;

		if ( empty( $product_map_id ) ) {
			return false;
		}

		// Product ID.
		if ( ! empty( $product_map_id ) ) {
			if ( is_array( $product_map_id ) ) {
				$allowed_map_ids = " AND map_id IN ('" . implode( "','", $product_map_id ) . "') ";
			} else {
				$allowed_map_ids = " AND map_id = '" . esc_sql( $product_map_id ) . "' ";
			}
		} else {
			$allowed_map_ids = '';
		}

		$spmv_args = $wpdb_ref->get_col(
			"SELECT DISTINCT vendor_id FROM {$wpdb->prefix}mvr_product_map AS pm
			WHERE 1=1 {$allowed_map_ids}"
		);

		return $spmv_args;
	}
}
