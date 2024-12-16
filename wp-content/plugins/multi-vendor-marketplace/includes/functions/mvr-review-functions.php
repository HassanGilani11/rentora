<?php
/**
 * Review Functions.
 *
 * @package Multi-Vendor Functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'mvr_convert_review_status_to_query_val' ) ) {
	/**
	 * Return Reviews.
	 *
	 * @since 1.0.0
	 * @param String $status Status.
	 * @return String
	 */
	function mvr_convert_review_status_to_query_val( $status ) {
		// These keys exactly match the database column.
		if ( in_array( $status, array( 'spam', 'trash' ), true ) ) {
			return $status;
		}

		switch ( $status ) {
			case 'moderated':
				return '0';
			case 'approved':
				return '1';
			default:
				return 'all';
		}
	}
}

if ( ! function_exists( 'mvr_get_reviews' ) ) {
	/**
	 * Return Reviews.
	 *
	 * @since 1.0.0
	 * @param Array $args Arguments.
	 * @return Object
	 */
	function mvr_get_reviews( $args = array() ) {
		global $wpdb;
		$wpdb_ref = &$wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'Comment_ID'      => '',
				'type__in'        => array( 'mvr_store_review', 'mvr_store_comment' ),
				'status'          => 'all',
				'post_type'       => MVR_Post_Types::MVR_VENDOR,
				'vendor_id'       => '',
				'user_id'         => '',
				'search'          => '',
				'offset'          => 0,
				'number'          => 0,
				'parent'          => 0,
				'post__in'        => '',
				'post__not_in'    => '',
				'comment__in'     => '',
				'comment__not_in' => '',
				'orderby'         => 'comment_date_gmt',
				'order'           => 'desc',
				'fields'          => 'objects',
				'meta_query'      => array(
					array(
						'key'     => 'rating',
						'type'    => 'NUMERIC',
						'compare' => '>',
						'value'   => 0,
					),
				),
			)
		);

		if ( 'rating' === $args['orderby'] ) {
			$args['meta_key'] = 'rating';
			$args['orderby']  = 'meta_value_num';
		}

		$reviews  = get_comments( $args );
		$per_page = (int) $args['number'];

		unset( $args['offset'] );
		unset( $args['number'] );

		$args['count'] = true;
		$all_reviews   = get_comments( $args );
		$review_count  = mvr_check_is_array( $reviews ) ? count( $reviews ) : 0;
		$query_object  = (object) array(
			'reviews'       => $reviews,
			'total_review'  => $all_reviews,
			'has_review'    => $review_count > 0,
			'max_num_pages' => $per_page > 0 ? ceil( $all_reviews / $per_page ) : 1,
		);

		return $query_object;
	}
}

if ( ! function_exists( 'mvr_delete_review' ) ) {
	/**
	 * Delete Single Product Multi Vendor
	 *
	 * @since 1.0.0
	 * @param Array $args Arguments.
	 */
	function mvr_delete_review( $args = array() ) {
		global $wpdb;
		$wpdb_ref = &$wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'ID'          => '',
				'include_ids' => array(),
				'exclude_ids' => array(),
				'vendor_id'   => '',
			)
		);

		// ID.
		if ( ! empty( $args['ID'] ) ) {
			$id = " AND comment_ID = '" . $args['ID'] . "' ";
		} else {
			$id = '';
		}

		// Includes.
		if ( ! empty( $args['include_ids'] ) ) {
			$include_ids = " AND comment_ID IN ('" . implode( "','", $args['include_ids'] ) . "') ";
		} else {
			$include_ids = '';
		}

		// Excludes.
		if ( ! empty( $args['exclude_ids'] ) ) {
			$exclude_ids = " AND comment_ID NOT IN ('" . implode( "','", $args['exclude_ids'] ) . "') ";
		} else {
			$exclude_ids = '';
		}

		// Allowed Vendors.
		if ( ! empty( $args['vendor_id'] ) ) {
			if ( is_array( $args['vendor_id'] ) ) {
				$allowed_vendors = " AND comment_post_ID IN ('" . implode( "','", $args['vendor_id'] ) . "') ";
			} else {
				$allowed_vendors = " AND comment_post_ID = '" . esc_sql( $args['vendor_id'] ) . "' ";
			}
		} else {
			$allowed_vendors = '';
		}

		$wpdb_ref->query(
			"DELETE FROM {$wpdb->prefix}comments AS c
			WHERE 1=1 {$id} {$include_ids} {$exclude_ids} {$allowed_vendors}"
		);
	}
}

if ( ! function_exists( 'mvr_get_review_statuses' ) ) {
	/**
	 * Get review statuses.
	 *
	 * @since 1.0.0
	 * @return Array
	 */
	function mvr_get_review_statuses() {
		return array(
			'approved'  => __( 'Approved', 'multi-vendor-marketplace' ),
			// 'pending'   => __( 'Pending', 'multi-vendor-marketplace' ),
			'moderated' => __( 'Moderated', 'multi-vendor-marketplace' ),
			'trash'     => __( 'Trash', 'multi-vendor-marketplace' ),
		);
	}
}
