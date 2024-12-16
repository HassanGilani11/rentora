<?php
/**
 * Vendor Functions.
 *
 * @package Multi-Vendor Functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'mvr_vendor_products_args' ) ) {
	/**
	 * Handle a custom query var to get products with the meta.
	 *
	 * @since 1.0.0
	 * @param Array $query - Args for WP_Query.
	 * @param Array $query_vars - Query vars from WC_Product_Query.
	 * @return Array modified $query
	 */
	function mvr_vendor_products_args( $query, $query_vars ) {
		if ( ! empty( $query_vars['mvr_meta_relation'] ) ) {
			$query['meta_query']['relation'] = $query_vars['mvr_meta_relation'];
		}

		if ( ! empty( $query_vars['mvr_vendor_meta_not_exist'] ) ) {
			$query['meta_query'][] = array(
				'key'     => '_mvr_vendor',
				'compare' => 'NOT EXISTS',
				'value'   => '',
			);

			$query['meta_query'][] = array(
				'key'     => '_mvr_vendor',
				'value'   => esc_attr( $query_vars['mvr_vendor_meta_not_exist'] ),
				'compare' => '!=',
			);
		}

		if ( ! empty( $query_vars['mvr_include_vendor'] ) ) {
			$query['meta_query'][] = array(
				'key'     => '_mvr_vendor',
				'value'   => esc_attr( $query_vars['mvr_include_vendor'] ),
				'compare' => '==',
			);
		}

		return $query;
	}
}

if ( ! function_exists( 'mvr_vendor_orders_args' ) ) {
	/**
	 * Handle a custom query var to get orders with the meta.
	 *
	 * @since 1.0.0
	 * @param Array $query - Args for WP_Query.
	 * @param Array $query_vars - Query vars from WC_Product_Query.
	 * @return Array modified $query
	 */
	function mvr_vendor_orders_args( $query, $query_vars ) {
		if ( ! empty( $query_vars['mvr_meta_relation'] ) ) {
			$query['meta_query']['relation'] = $query_vars['mvr_meta_relation'];
		}

		if ( ! empty( $query_vars['mvr_vendor_meta_not_exist'] ) ) {
			$query['meta_query'][] = array(
				'key'     => 'mvr_vendor_id',
				'compare' => 'NOT EXISTS',
				'value'   => '',
			);

			$query['meta_query'][] = array(
				'key'     => 'mvr_vendor_id',
				'value'   => esc_attr( $query_vars['mvr_vendor_meta_not_exist'] ),
				'compare' => '=',
			);
		}

		if ( ! empty( $query_vars['mvr_vendor_meta_exist'] ) ) {
			$query['meta_query'][] = array(
				'key'     => 'mvr_vendor_id',
				'compare' => 'EXISTS',
				'value'   => '',
			);
		}

		if ( ! empty( $query_vars['mvr_include_vendor'] ) ) {
			$query['meta_query'][] = array(
				'key'     => 'mvr_vendor_id',
				'value'   => esc_attr( $query_vars['mvr_include_vendor'] ),
				'compare' => '==',
			);
		}

		return $query;
	}
}

if ( ! function_exists( 'mvr_get_current_vendor_id' ) ) {
	/**
	 * Check whether the given the value is vendor.
	 *
	 * @since 1.0.0
	 * @return Boolean.
	 */
	function mvr_get_current_vendor_id() {
		$user_id = get_current_user_id();

		if ( empty( $user_id ) ) {
			return false;
		}

		if ( mvr_check_user_as_vendor( $user_id ) ) {
			$vendor_obj = mvr_get_current_vendor_object();

			if ( ! $vendor_obj ) {
				return false;
			}

			return ( $vendor_obj->get_id() ) ? $vendor_obj->get_id() : false;
		}

		if ( mvr_check_user_as_staff( $user_id ) ) {
			$staff_obj = mvr_get_current_staff_object();

			if ( ! $staff_obj ) {
				return false;
			}

			return ( $staff_obj->get_vendor_id() ) ? $staff_obj->get_vendor_id() : false;
		}

		return false;
	}
}

if ( ! function_exists( 'mvr_get_current_vendor_object' ) ) {
	/**
	 * Get current vendor object
	 *
	 * @since 1.0.0
	 * @return Boolean.
	 */
	function mvr_get_current_vendor_object() {
		$user_id = get_current_user_id();

		if ( empty( $user_id ) ) {
			return false;
		}

		$vendors_obj = mvr_get_vendors(
			array(
				'status'  => array_keys( mvr_get_vendor_statuses() ),
				'user_id' => $user_id,
				'limit'   => 1,
			)
		);

		if ( ! $vendors_obj->has_vendor ) {
			if ( mvr_check_user_as_staff( $user_id ) ) {
				$staff_obj = mvr_get_current_staff_object();

				if ( ! $staff_obj ) {
					return false;
				}

				return mvr_is_vendor( $staff_obj->get_vendor() ) ? $staff_obj->get_vendor() : false;
			}

			return false;
		}

		$vendor_obj = current( $vendors_obj->vendors );

		if ( ! $vendor_obj ) {
			return false;
		}

		return $vendor_obj;
	}
}

if ( ! function_exists( 'mvr_get_vendor_statuses' ) ) {
	/**
	 * Get Vendor statuses.
	 *
	 * @since 1.0.0
	 * @return Array
	 */
	function mvr_get_vendor_statuses() {
		return array(
			'mvr-active'   => __( 'Active', 'multi-vendor-marketplace' ),
			'mvr-inactive' => __( 'Inactive', 'multi-vendor-marketplace' ),
			'mvr-pending'  => __( 'Pending', 'multi-vendor-marketplace' ),
			'mvr-reject'   => __( 'Reject', 'multi-vendor-marketplace' ),
		);
	}
}

if ( ! function_exists( 'mvr_get_vendor_status_name' ) ) {
	/**
	 * Get the vendor status name.
	 *
	 * @since 1.0.0
	 * @param String $status Status name.
	 * @return String
	 */
	function mvr_get_vendor_status_name( $status ) {
		$statuses = mvr_get_vendor_statuses();
		$status   = mvr_trim_post_status( $status );

		return isset( $statuses[ "mvr-{$status}" ] ) ? $statuses[ "mvr-{$status}" ] : $status;
	}
}

if ( ! function_exists( 'mvr_get_vendor' ) ) {
	/**
	 * Get Vendor.
	 *
	 * @since 1.0.0
	 * @param MVR_Vendor $vendor Vendor.
	 * @param Boolean    $wp_error WordPress error.
	 * @return bool|\MVR_Vendor
	 */
	function mvr_get_vendor( $vendor, $wp_error = false ) {
		if ( ! $vendor ) {
			return false;
		}

		try {
			$vendor = new MVR_Vendor( $vendor );
		} catch ( Exception $e ) {
			return $wp_error ? new WP_Error( 'error', $e->getMessage() ) : false;
		}

		return $vendor;
	}
}

if ( ! function_exists( 'mvr_get_vendors' ) ) {
	/**
	 * Return the array of vendors based upon the args requested.
	 *
	 * @since 1.0.0
	 * @param Array $args Arguments.
	 * @return Object
	 */
	function mvr_get_vendors( $args = array() ) {
		global $wpdb;
		$wpdb_ref = &$wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'status'          => array_keys( mvr_get_vendor_statuses() ),
				'include_ids'     => array(),
				'exclude_ids'     => array(),
				's'               => '',
				'page'            => 1,
				'limit'           => -1,
				'fields'          => 'objects',
				'orderby'         => 'ID',
				'order'           => 'DESC',
				'user_id'         => '',
				'amount'          => '',
				'payout_type'     => '',
				'payout_schedule' => '',
				'payment_method'  => '',
			)
		);

		// Add the 'mvr-' prefix to status if needed.
		if ( ! empty( $args['status'] ) ) {
			if ( is_array( $args['status'] ) ) {
				foreach ( $args['status'] as &$status ) {
					if ( mvr_is_vendor_status( 'mvr-' . $status ) ) {
						$status = 'mvr-' . $status;
					}
				}
			} elseif ( mvr_is_vendor_status( 'mvr-' . $args['status'] ) ) {
					$args['status'] = 'mvr-' . $args['status'];
			}
		}

		// Statuses.
		if ( is_array( $args['status'] ) ) {
			$allowed_statuses = " AND post_status IN ('" . implode( "','", $args['status'] ) . "') ";
		} else {
			$allowed_statuses = " AND post_status = '" . esc_sql( $args['status'] ) . "' ";
		}

		// Search term.
		if ( ! empty( $args['s'] ) ) {
			$term          = str_replace( '#', '', wc_clean( wp_unslash( $args['s'] ) ) );
			$search_fields = array( '_shop_name', 'slug' );
			$search_where  = " AND ( 
                (ID LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR
                (post_title LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR
                (post_excerpt LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR
                (post_content LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR 
                (pm.meta_value LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%' AND pm.meta_key IN ('" . implode( "','", array_map( 'esc_sql', $search_fields ) ) . "'))
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

		if ( is_array( $args['user_id'] ) ) {
			$allowed_users = " AND post_parent IN ('" . implode( "','", $args['user_id'] ) . "') ";
		} elseif ( ! empty( $args['user_id'] ) ) {
			$allowed_users = " AND post_parent = '" . esc_sql( $args['user_id'] ) . "' ";
		} else {
			$allowed_users = '';
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

		// Amount.
		if ( ! empty( $args['amount'] ) ) {
			$inner_join1 = " INNER JOIN {$wpdb_ref->postmeta} AS pm1 ON (p.ID = pm1.post_id) ";
			$amount      = " AND pm1.meta_key='_amount' AND pm1.meta_value >= '{$args['amount']}'";
		} else {
			$inner_join1 = '';
			$amount      = '';
		}

		// Payout Type.
		if ( ! empty( $args['payout_type'] ) ) {
			$inner_join2 = " INNER JOIN {$wpdb_ref->postmeta} AS pm2 ON (p.ID = pm2.post_id) ";
			$payout_type = " AND pm2.meta_key='_payout_type' AND pm2.meta_value = '{$args['payout_type']}'";
		} else {
			$inner_join2 = '';
			$payout_type = '';
		}

		// Payout Schedule.
		if ( ! empty( $args['payout_schedule'] ) ) {
			$inner_join3     = " INNER JOIN {$wpdb_ref->postmeta} AS pm3 ON (p.ID = pm3.post_id) ";
			$payout_schedule = " AND pm3.meta_key='_payout_schedule' AND pm3.meta_value = '{$args['payout_schedule']}'";
		} else {
			$inner_join3     = '';
			$payout_schedule = '';
		}

		// Payment Method.
		if ( ! empty( $args['payment_method'] ) ) {
			$inner_join4 = " INNER JOIN {$wpdb_ref->postmeta} AS pm4 ON (p.ID = pm4.post_id) ";

			if ( is_array( $args['payment_method'] ) ) {
				$payment_method = " AND pm4.meta_key='_payment_method' AND pm4.meta_value IN ('" . implode( "','", $args['payment_method'] ) . "')";
			} else {
				$payment_method = " AND pm4.meta_key='_payment_method' AND pm4.meta_value = '{$args['payment_method']}'";
			}
		} else {
			$inner_join4    = '';
			$payment_method = '';
		}

		$all_vendor_ids = $wpdb_ref->get_var(
			$wpdb_ref->prepare(
				"
                    SELECT COUNT(DISTINCT ID) FROM {$wpdb_ref->posts} AS p
					INNER JOIN {$wpdb_ref->postmeta} AS pm ON (p.ID = pm.post_id)
					{$inner_join1}
					{$inner_join2}
					{$inner_join3}
					{$inner_join4}
					WHERE 1=1 AND post_type = '%s' 
					$allowed_statuses 
					$allowed_users 
					$search_where 
					$include_ids 
					$exclude_ids 
					$amount
					$payout_type
					$payout_schedule
					$payment_method
				",
				'mvr_vendor'
			)
		);

		$vendor_ids = $wpdb_ref->get_col(
			$wpdb_ref->prepare(
				"
					SELECT DISTINCT ID FROM {$wpdb_ref->posts} AS p
					INNER JOIN {$wpdb_ref->postmeta} AS pm ON (p.ID = pm.post_id)
					{$inner_join1}
					{$inner_join2}
					{$inner_join3}
					{$inner_join4}
					WHERE 1=1 AND post_type = '%s' 
					$allowed_statuses 
					$allowed_users 
					$search_where 
					$include_ids 
					$exclude_ids 
					$amount
					$payout_type
					$payout_schedule
					$payment_method
					$orderby 
					$order 
					$limits 
				",
				'mvr_vendor'
			)
		);

		if ( 'objects' === $args['fields'] ) {
			$vendors = array_filter( array_combine( $vendor_ids, array_map( 'mvr_get_vendor', $vendor_ids ) ) );
		} else {
			$vendors = $vendor_ids;
		}

		$vendors_count = count( $vendors );
		$query_object  = (object) array(
			'vendors'       => $vendors,
			'total_vendors' => $vendors_count,
			'has_vendor'    => $vendors_count > 0,
			'max_num_pages' => $args['limit'] > 0 ? ceil( $all_vendor_ids / $args['limit'] ) : 1,
		);

		return $query_object;
	}
}

if ( ! function_exists( 'mvr_get_vendor_notes' ) ) {
	/**
	 * Get vendor notes.
	 *
	 * @since 1.0.0
	 * @param Array $args Arguments.
	 * @return Array
	 */
	function mvr_get_vendor_notes( $args ) {
		$key_mapping = array(
			'limit'          => 'number',
			'vendor_id'      => 'post_id',
			'vendor__in'     => 'post__in',
			'vendor__not_in' => 'post__not_in',
		);

		foreach ( $key_mapping as $query_key => $db_key ) {
			if ( isset( $args[ $query_key ] ) ) {
				$args[ $db_key ] = $args[ $query_key ];
				unset( $args[ $query_key ] );
			}
		}

		// Define orderby.
		$orderby_mapping = array(
			'date_created'     => 'comment_date',
			'date_created_gmt' => 'comment_date_gmt',
			'id'               => 'comment_ID',
		);

		$args['orderby'] = ! empty( $args['orderby'] ) && in_array( $args['orderby'], array( 'date_created', 'date_created_gmt', 'id' ), true ) ? $orderby_mapping[ $args['orderby'] ] : 'comment_ID';

		if ( isset( $args['type'] ) ) {
			// Set vendor type.
			if ( 'customer' === $args['type'] ) {
				$args['meta_query'] = array(
					array(
						'key'     => 'is_customer_note',
						'value'   => 1,
						'compare' => '=',
					),
				);
			} elseif ( 'internal' === $args['type'] ) {
				$args['meta_query'] = array(
					array(
						'key'     => 'is_customer_note',
						'compare' => 'NOT EXISTS',
					),
				);
			}
		}

		$args['type']   = 'mvr_vendor_note'; // Set correct comment type.
		$args['status'] = 'approve'; // Always approved.

		// Does not support 'count' or 'fields'.
		unset( $args['count'], $args['fields'] );

		remove_filter( 'comments_clauses', array( 'MVR_Comments', 'exclude_vendor_comments' ), 10, 1 );

		$notes = get_comments( $args );

		add_filter( 'comments_clauses', array( 'MVR_Comments', 'exclude_vendor_comments' ), 10, 1 );

		return array_filter( array_map( 'mvr_get_vendor_note', $notes ) );
	}
}

if ( ! function_exists( 'mvr_get_vendor_note' ) ) {
	/**
	 * Get Vendor note.
	 *
	 * @param  Integer|WP_Comment $data Note ID.
	 * @return stdClass|null  Object with vendor note details or null when does not exists.
	 */
	function mvr_get_vendor_note( $data ) {
		if ( is_numeric( $data ) ) {
			$data = get_comment( $data );
		}

		if ( ! is_a( $data, 'WP_Comment' ) ) {
			return null;
		}

		/**
		 * Get object.
		 *
		 * @since 1.0.0
		 */
		return (object) apply_filters(
			'mvr_get_vendor_note',
			array(
				'id'            => (int) $data->comment_ID,
				'date_created'  => wc_string_to_datetime( $data->comment_date ),
				'content'       => $data->comment_content,
				'customer_note' => (bool) get_comment_meta( $data->comment_ID, 'is_customer_note', true ),
				'added_by'      => __( 'Multi Vendor Marketplace', 'multi-vendor-marketplace' ) === $data->comment_author ? 'system' : $data->comment_author,
			),
			$data
		);
	}
}

if ( ! function_exists( 'mvr_delete_vendor_note' ) ) {
	/**
	 * Delete an vendor note.
	 *
	 * @since 1.0.0
	 * @param Integer $note_id vendor note.
	 * @return Boolean True on success, false on failure.
	 */
	function mvr_delete_vendor_note( $note_id ) {
		return wp_delete_comment( $note_id, true );
	}
}

if ( ! function_exists( 'mvr_get_vendor_coupon' ) ) {
	/**
	 * Return the array of vendor coupons based upon the args requested.
	 *
	 * @since 1.0.0
	 * @param Integer $coupon_id Coupon ID.
	 * @return Object
	 */
	function mvr_get_vendor_coupon( $coupon_id ) {
		return new WC_Coupon( $coupon_id );
	}
}


if ( ! function_exists( 'mvr_get_vendor_coupons' ) ) {
	/**
	 * Return the array of vendor coupons based upon the args requested.
	 *
	 * @since 1.0.0
	 * @param Array $args Arguments.
	 * @return Object
	 */
	function mvr_get_vendor_coupons( $args = array() ) {
		global $wpdb;
		$wpdb_ref = &$wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'status'      => array( 'publish', 'pending', 'draft' ),
				'include_ids' => array(),
				'exclude_ids' => array(),
				's'           => '',
				'page'        => 1,
				'limit'       => -1,
				'fields'      => 'objects',
				'orderby'     => 'menu_order',
				'order'       => 'ASC',
				'vendor_id'   => '',
			)
		);

		// Statuses.
		if ( is_array( $args['status'] ) ) {
			$allowed_statuses = " AND post_status IN ('" . implode( "','", $args['status'] ) . "') ";
		} else {
			$allowed_statuses = " AND post_status = '" . esc_sql( $args['status'] ) . "' ";
		}

		if ( is_numeric( $args['vendor_id'] ) ) {
			$allowed_vendor = " AND pm.meta_key = '_mvr_vendor' AND pm.meta_value = " . absint( $args['vendor_id'] ) . ' ';
		} else {
			$allowed_vendor = '';
		}

		// Search term.
		if ( ! empty( $args['s'] ) ) {
			$term          = str_replace( '#', '', wc_clean( wp_unslash( $args['s'] ) ) );
			$search_fields = array();
			$search_where  = " AND ( 
                (ID LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR
                (post_title LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR
                (post_excerpt LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR
                (post_content LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR 
                (pm.meta_value LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%' AND pm.meta_key IN ('" . implode( "','", array_map( 'esc_sql', $search_fields ) ) . "'))
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

		$all_coupon_ids = $wpdb_ref->get_var(
			$wpdb_ref->prepare(
				"
				SELECT COUNT(DISTINCT ID) FROM {$wpdb_ref->posts} AS p
				INNER JOIN {$wpdb_ref->postmeta} AS pm ON (p.ID = pm.post_id)
				WHERE 1=1 AND post_type = '%s' $allowed_statuses $allowed_vendor $search_where $include_ids $exclude_ids
				",
				'shop_coupon'
			)
		);

		$coupon_ids = $wpdb_ref->get_col(
			$wpdb_ref->prepare(
				"
				SELECT DISTINCT ID FROM {$wpdb_ref->posts} AS p
				INNER JOIN {$wpdb_ref->postmeta} AS pm ON (p.ID = pm.post_id)
				WHERE 1=1 AND post_type = '%s' $allowed_statuses $allowed_vendor $search_where $include_ids $exclude_ids
				$orderby $order $limits 
				",
				'shop_coupon'
			)
		);

		if ( 'objects' === $args['fields'] ) {
			$coupons = array_filter( array_combine( $coupon_ids, array_map( 'mvr_get_vendor_coupon', $coupon_ids ) ) );
		} else {
			$coupons = $coupon_ids;
		}

		$coupons_count = count( $coupons );
		$query_object  = (object) array(
			'coupons'       => $coupons,
			'total'         => $coupons_count,
			'has_coupon'    => $coupons_count > 0,
			'max_num_pages' => $args['limit'] > 0 ? ceil( $all_coupon_ids / $args['limit'] ) : 1,
		);

		return $query_object;
	}
}

if ( ! function_exists( 'mvr_get_formated_vendor_address' ) ) {
	/**
	 * Return formated Vendor Address
	 *
	 * @since 1.0.0
	 * @param MVR_Vendor $vendor_obj Vendor Object.
	 */
	function mvr_get_formated_vendor_address( $vendor_obj ) {
		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		echo wp_kses_post( WC()->countries->get_formatted_address( $vendor_obj->get_address() ) );
	}
}

if ( ! function_exists( 'mvr_get_available_vendor_social_links' ) ) {
	/**
	 * Available Vendor Social Links
	 *
	 * @since 1.0.0
	 * @param MVR_Vendor $vendor_obj Vendor Object.
	 * @return Array
	 */
	function mvr_get_available_vendor_social_links( $vendor_obj ) {
		$social_links    = array( 'facebook', 'twitter', 'youtube', 'instagram', 'linkedin', 'pinterest' );
		$available_links = array();

		foreach ( $social_links as $link ) {
			$getter = "get_$link";

			if ( is_callable( array( $vendor_obj, $getter ) ) ) {
				$val = $vendor_obj->{$getter}();

				if ( ! empty( $val ) ) {
					$available_links[ $link ] = $val;
				}
			}
		}

		return $available_links;
	}
}

if ( ! function_exists( 'mvr_get_vendor_order_details' ) ) {
	/**
	 * Available Vendor Social Links
	 *
	 * @since 1.0.0
	 * @param MVR_Vendor $vendor_obj Vendor Object.
	 * @param WC_Order   $order_obj Vendor Object.
	 * @return Array
	 */
	function mvr_get_vendor_order_details( $vendor_obj, $order_obj ) {
		$args = array(
			'subtotal'      => 0,
			'subtotal_tax'  => 0,
			'discount'      => 0,
			'discount_tax'  => 0,
			'total'         => 0,
			'total_tax'     => 0,
			'item_count'    => 0,
			'commission'    => 0,
			'vendor_amount' => 0,
		);

		$total_tax    = 0;
		$subtotal_tax = 0;
		$total        = 0;

		if ( ! mvr_is_vendor( $vendor_obj ) || ! is_a( $order_obj, 'WC_Order' ) ) {
			return $args;
		}

		foreach ( $order_obj->get_items( 'line_item' ) as $item_id => $item ) {
			$product_obj = wc_get_product( $item->get_product_id() );

			if ( ! $product_obj || (int) $vendor_obj->get_id() !== (int) $product_obj->get_meta( '_mvr_vendor', true ) ) {
				continue;
			}

			$taxes = $item->get_taxes();

			foreach ( $taxes['total'] as $tax_rate_id => $tax ) {
				$total_tax += (float) $tax;
			}

			foreach ( $taxes['subtotal'] as $tax_rate_id => $tax ) {
				$subtotal_tax += (float) $tax;
			}

			$args['subtotal']   += $order_obj->get_line_subtotal( $item );
			$args['discount']   += ( $item['line_subtotal'] - $item['line_total'] );
			$total              += $order_obj->get_line_total( $item );
			$args['item_count'] += $item->get_quantity();
		}

		$args['subtotal_tax'] = $subtotal_tax;
		$args['discount_tax'] = wc_round_tax_total( $subtotal_tax - $total_tax );
		$args['total']        = $total + $total_tax;
		$args['total_tax']    = $total_tax;

		$commissions_obj = mvr_get_commissions(
			array(
				'vendor_id'   => $vendor_obj->get_id(),
				'source_id'   => $order_obj->get_id(),
				'source_from' => 'order',
			)
		);

		if ( $commissions_obj->has_commission ) {
			$commission_obj = current( $commissions_obj->commissions );

			if ( mvr_is_commission( $commission_obj ) ) {
				$args['commission']    = $commission_obj->get_amount();
				$args['vendor_amount'] = $commission_obj->get_vendor_amount();
			}
		}

		return $args;
	}
}

if ( ! function_exists( 'mvr_get_vendor_orders_details' ) ) {
	/**
	 * Available Vendor Social Links
	 *
	 * @since 1.0.0
	 * @param MVR_Vendor $vendor_obj The vendor object.
	 * @param Array      $args Arguments.
	 * @return Array
	 */
	function mvr_get_vendor_orders_details( $vendor_obj, $args ) {
		$order_args = array(
			'cross_sale'       => 0,
			'vendor_earning'   => 0,
			'admin_commission' => 0,
			'item_count'       => 0,
			'order_count'      => 0,
		);

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			$vendor_obj = mvr_get_vendor( $vendor_obj );
		}

		if ( ! $vendor_obj ) {
			return $order_args;
		}

		$orders_obj = $vendor_obj->get_orders( $args );

		if ( ! $orders_obj->has_order ) {
			return $order_args;
		}

		$order_args['order_count'] = $orders_obj->total_orders;

		foreach ( $orders_obj->orders as $mvr_order_obj ) {
			if ( ! mvr_is_order( $mvr_order_obj ) ) {
				continue;
			}

			$order_obj = wc_get_order( $mvr_order_obj->get_order_id() );

			if ( ! is_a( $order_obj, 'WC_Order' ) ) {
				continue;
			}

			$order_details = mvr_get_vendor_order_details( $vendor_obj, $order_obj );

			if ( ! mvr_check_is_array( $order_details ) ) {
				continue;
			}

			$order_args['cross_sale']       += $order_details['total'];
			$order_args['admin_commission'] += $order_details['commission'];
			$order_args['vendor_earning']   += $order_details['vendor_amount'];
			// $order_args['vendor_earning']   += ( $order_details['total'] - $order_details['commission'] );
			$order_args['item_count'] += $order_details['item_count'];
		}

		return $order_args;
	}
}

if ( ! function_exists( 'mvr_get_vendor_orders' ) ) {
	/**
	 * Return the array of vendor orders based upon the args requested.
	 *
	 * @since 1.0.0
	 * @param Array $args Arguments.
	 * @return Object
	 */
	function mvr_get_vendor_orders( $args = array() ) {
		global $wpdb;
		$wpdb_ref = &$wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'status'      => array_keys( wc_get_order_statuses() ),
				'include_ids' => array(),
				'exclude_ids' => array(),
				's'           => '',
				'page'        => 1,
				'limit'       => -1,
				'fields'      => 'objects',
				'orderby'     => 'ID',
				'order'       => 'DESC',
				'vendor_id'   => '',
				'user_id'     => '',
				'customer_id' => '',
				'date_before' => '',
				'date_after'  => '',
			)
		);

		// Add the 'wc-' prefix to status if needed.
		if ( ! empty( $args['status'] ) ) {
			if ( is_array( $args['status'] ) ) {
				foreach ( $args['status'] as &$status ) {
					if ( wc_is_order_status( 'wc-' . $status ) ) {
						$status = 'mvr-' . $status;
					}
				}
			} elseif ( wc_is_order_status( 'wc-' . $args['status'] ) ) {
					$args['status'] = 'wc-' . $args['status'];
			}
		}

		// Statuses.
		if ( is_array( $args['status'] ) ) {
			$allowed_statuses = " AND post_status IN ('" . implode( "','", $args['status'] ) . "') ";
		} else {
			$allowed_statuses = " AND post_status = '" . esc_sql( $args['status'] ) . "' ";
		}

		// Allowed Vendors.
		if ( ! empty( $args['vendor_id'] ) ) {
			$allowed_vendors = " AND pm.meta_key = 'mvr_vendor_id' AND pm.meta_value = '" . esc_attr( $args['vendor_id'] ) . "' ";
		} else {
			$allowed_vendors = '';
		}

		// Allowed Users.
		if ( ! empty( $args['user_id'] ) ) {
			$inner_join1   = " INNER JOIN {$wpdb_ref->postmeta} AS pm1 ON (p.ID = pm1.post_id) ";
			$allowed_users = " AND pm1.meta_key = '_customer_user' AND pm1.meta_value = '" . esc_sql( $args['user_id'] ) . "' ";
		} else {
			$inner_join1   = '';
			$allowed_users = '';
		}

		// Allowed Customers.
		if ( ! empty( $args['customer_id'] ) ) {
			$inner_join2   = " INNER JOIN {$wpdb_ref->postmeta}wc_order_stats AS os ON (p.ID = os.order_id) ";
			$allowed_users = " AND os.customer_id = '" . esc_sql( $args['customer_id'] ) . "' ";
		} else {
			$inner_join1   = '';
			$allowed_users = '';
		}

		if ( ! empty( $args['date_before'] ) ) {
			$datetime        = wc_string_to_datetime( $args['date_before'] );
			$date_before_val = strpos( $args['date_before'], ':' ) ? $datetime->getOffsetTimestamp() : $datetime->date( 'Y-m-d' );
			$date_before     = " AND `post_date_gmt` <= '" . esc_sql( $date_before_val ) . "' ";
		} else {
			$date_before = '';
		}

		if ( ! empty( $args['date_after'] ) ) {
			$datetime       = wc_string_to_datetime( $args['date_after'] );
			$date_after_val = strpos( $args['date_after'], ':' ) ? $datetime->getOffsetTimestamp() : $datetime->date( 'Y-m-d' );
			$date_after     = " AND `post_date_gmt` >= '" . esc_sql( $date_after_val ) . "' ";
		} else {
			$date_after = '';
		}

		// Search term.
		if ( ! empty( $args['s'] ) ) {
			$term          = str_replace( '#', '', wc_clean( wp_unslash( $args['s'] ) ) );
			$search_fields = array( '_shop_name', 'slug' );
			$search_where  = " AND ( 
                (ID LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR
                (post_title LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR
                (post_excerpt LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR
                (post_content LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR 
                (pm.meta_value LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%' AND pm.meta_key IN ('" . implode( "','", array_map( 'esc_sql', $search_fields ) ) . "'))
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
			$wpdb_ref->prepare(
				"
                    SELECT COUNT(DISTINCT ID) FROM {$wpdb_ref->posts} AS p
					INNER JOIN {$wpdb_ref->postmeta} AS pm ON (p.ID = pm.post_id)
					{$inner_join1}
					WHERE 1=1 AND post_type = '%s' 
					$allowed_statuses 
					$allowed_vendors 
					$allowed_users 
					$search_where 
					$include_ids 
					$exclude_ids 
					$date_before
					$date_after
				",
				'shop_order'
			)
		);

		$order_ids = $wpdb_ref->get_col(
			$wpdb_ref->prepare(
				"
					SELECT DISTINCT ID FROM {$wpdb_ref->posts} AS p
					INNER JOIN {$wpdb_ref->postmeta} AS pm ON (p.ID = pm.post_id)
					{$inner_join1}
					WHERE 1=1 AND post_type = '%s' 
					$allowed_statuses 
					$allowed_vendors 
					$allowed_users 
					$search_where 
					$include_ids 
					$exclude_ids 
					$date_before
					$date_after
					$orderby 
					$order 
					$limits 
				",
				'shop_order'
			)
		);

		if ( 'objects' === $args['fields'] ) {
			$orders = array_filter( array_combine( $order_ids, array_map( 'wc_get_order', $order_ids ) ) );
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

if ( ! function_exists( 'mvr_add_vendor_id_to_order' ) ) {
	/**
	 * Add Vendor ID to Order Object
	 *
	 * @since 1.0.0
	 * @param WC_Order $order_obj Order object.
	 * @param Integer  $vendor_id Vendor ID.
	 */
	function mvr_add_vendor_id_to_order( $order_obj, $vendor_id ) {
		$vendor_ids = $order_obj->get_meta( 'mvr_vendor_id', false );

		if ( mvr_check_is_array( $vendor_ids ) && in_array( (string) $vendor_id, wp_list_pluck( $vendor_ids, 'value' ), true ) ) {
			return;
		}

		$order_obj->add_meta_data( 'mvr_vendor_id', $vendor_id );
		$order_obj->save();
	}
}

if ( ! function_exists( 'mvr_remove_vendor_id_to_order' ) ) {
	/**
	 * Remove Vendor ID to Order Object
	 *
	 * @since 1.0.0
	 * @param WC_Order $order_obj Order object.
	 * @param Integer  $vendor_id Vendor ID.
	 */
	function mvr_remove_vendor_id_to_order( $order_obj, $vendor_id ) {
		$meta_key  = 'mvr_vendor_id';
		$vendor_id = (string) $vendor_id;

		foreach ( $order_obj->get_meta_data() as $meta ) {
			if ( $meta_key === $meta->key && $value_to_delete === $meta->value ) {
				$order_obj->delete_meta_data_by_mid( $meta->id );
			}
		}

		$order_obj->save();
	}
}

if ( ! function_exists( 'mvr_process_delete_vendor' ) ) {
	/**
	 * Remove Vendor ID to Order Object
	 *
	 * @since 1.0.0
	 * @param Integer $vendor_id Vendor ID.
	 */
	function mvr_process_delete_vendor( $vendor_id ) {
		if ( ! mvr_is_vendor( $vendor_id ) ) {
			return;
		}

		$vendor_obj = mvr_get_vendor( $vendor_id );

		$commissions_obj = mvr_get_commissions( array( 'vendor_id' => $vendor_id ) );

		if ( $commissions_obj->has_commission ) {
			foreach ( $commissions_obj->commissions as $commission_obj ) {
				$commission_obj->delete();
			}
		}

		$transactions_obj = mvr_get_transactions( array( 'vendor_id' => $vendor_id ) );

		if ( $transactions_obj->has_transaction ) {
			foreach ( $transactions_obj->transactions as $transaction_obj ) {
				$transaction_obj->delete();
			}
		}

		$withdraws_obj = mvr_get_withdraws( array( 'vendor_id' => $vendor_id ) );

		if ( $withdraws_obj->has_withdraw ) {
			foreach ( $withdraws_obj->withdraws as $withdraw_obj ) {
				$withdraw_obj->delete();
			}
		}

		$notifications_obj = mvr_get_notifications( array( 'vendor_id' => $vendor_id ) );

		if ( $notifications_obj->has_notification ) {
			foreach ( $notifications_obj->notifications as $notification_obj ) {
				$notification_obj->delete();
			}
		}

		$enquiries_obj = mvr_get_enquiries( array( 'vendor_id' => $vendor_id ) );

		if ( $enquiries_obj->has_enquiry ) {
			foreach ( $enquiries_obj->enquiries as $enquiry_obj ) {
				$enquiry_obj->delete();
			}
		}

		$staffs_obj = mvr_get_staffs( array( 'vendor_id' => $vendor_id ) );

		if ( $staffs_obj->has_staff ) {
			foreach ( $staffs_obj->staffs as $staff_obj ) {
				$staff_obj->delete();
			}
		}

		if ( $vendor_obj->get_user_id() ) {
			$user_obj = get_user_by( 'ID', $vendor_obj->get_user_id() );

			if ( $user_obj instanceof WP_User ) {
				$user_obj->remove_role( 'mvr-vendor' );
				$user_obj->set_role( 'customer' );
			}
		}
	}
}

if ( ! function_exists( 'mvr_before_delete_vendor' ) ) {
	/**
	 * Before Delete Vendor Object.
	 *
	 * @since 1.0.0
	 * @param Integer $vendor_id Vendor Id.
	 */
	function mvr_before_delete_vendor( $vendor_id ) {
		if ( $vendor_id ) {
			$vendor_obj = mvr_get_vendor( $vendor_id );

			if ( mvr_is_vendor( $vendor_obj ) ) {
				$user_id = $vendor_obj->get_user_id();

				if ( $user_id ) {
					$user_obj = get_user_by( 'ID', $user_id );

					if ( $user_obj instanceof WP_User ) {
						$user_obj->remove_role( 'mvr-vendor' );
						$user_obj->set_role( 'customer' );
					}
				}
			}
		}
	}
}

add_action( 'mvr_before_delete_vendor', 'mvr_before_delete_vendor' );
add_action( 'before_delete_post', 'mvr_before_delete_vendor' );
add_filter( 'woocommerce_product_data_store_cpt_get_products_query', 'mvr_vendor_products_args', 10, 2 );
add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', 'mvr_vendor_orders_args', 10, 2 );
