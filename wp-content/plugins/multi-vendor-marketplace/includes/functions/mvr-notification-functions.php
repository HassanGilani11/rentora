<?php
/**
 * Notification Functions.
 *
 * @package Multi-Vendor Functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'mvr_is_notification' ) ) {
	/**
	 * Check whether the given the value is notification.
	 *
	 * @since 1.0.0
	 * @param  Mixed $notification Post object or post ID of the Notification.
	 * @return Boolean True on success.
	 */
	function mvr_is_notification( $notification ) {
		return $notification && is_a( $notification, 'MVR_Notification' );
	}
}

if ( ! function_exists( 'mvr_notification_types' ) ) {
	/**
	 * Notification Type.
	 *
	 * @since 1.0.0
	 * @return Boolean True on success.
	 */
	function mvr_notification_types() {
		return array(
			'new_order'     => __( 'New Order', 'multi-vendor-marketplace' ),
			'new_product'   => __( 'New Product', 'multi-vendor-marketplace' ),
			'new_coupon'    => __( 'New Coupon', 'multi-vendor-marketplace' ),
			'store_enquiry' => __( 'New Enquiry', 'multi-vendor-marketplace' ),
		);
	}
}

if ( ! function_exists( 'mvr_notification_type_name' ) ) {
	/**
	 * Notification Type Name.
	 *
	 * @since 1.0.0
	 * @param String $type Notification Type.
	 * @return Boolean True on success.
	 */
	function mvr_notification_type_name( $type ) {
		$types = mvr_notification_types();

		return isset( $types[ "{$type}" ] ) ? $types[ "{$type}" ] : ucfirst( str_replace( '_', ' ', $type ) );
	}
}



if ( ! function_exists( 'mvr_get_notification' ) ) {
	/**
	 * Get Notification.
	 *
	 * @since 1.0.0
	 * @param MVR_Notification $notification Notification.
	 * @param Boolean          $wp_error WordPress error.
	 * @return bool|\MVR_Notification
	 */
	function mvr_get_notification( $notification, $wp_error = false ) {
		if ( ! $notification ) {
			return false;
		}

		try {
			$notification = new MVR_Notification( $notification );
		} catch ( Exception $e ) {
			return $wp_error ? new WP_Error( 'error', $e->getMessage() ) : false;
		}

		return $notification;
	}
}

if ( ! function_exists( 'mvr_get_notification_statuses' ) ) {
	/**
	 * Get notification statuses.
	 *
	 * @since 1.0.0
	 * @return Array
	 */
	function mvr_get_notification_statuses() {
		return array(
			'read'   => __( 'Read', 'multi-vendor-marketplace' ),
			'unread' => __( 'Unread', 'multi-vendor-marketplace' ),
		);
	}
}

if ( ! function_exists( 'mvr_get_notification_status_name' ) ) {
	/**
	 * Get the notification status name.
	 *
	 * @since 1.0.0
	 * @param String $status Status name.
	 * @return String
	 */
	function mvr_get_notification_status_name( $status ) {
		$statuses = mvr_get_notification_statuses();
		$status   = mvr_trim_post_status( $status );

		return isset( $statuses[ "{$status}" ] ) ? $statuses[ "{$status}" ] : $status;
	}
}

if ( ! function_exists( 'mvr_is_notification_status' ) ) {
	/**
	 * See if a string is an Notification status.
	 *
	 * @since 1.0.0
	 * @param  String $maybe_status Status, including any mvr- prefix.
	 * @return Boolean
	 */
	function mvr_is_notification_status( $maybe_status ) {
		$statuses = mvr_get_notification_statuses();

		return isset( $statuses[ $maybe_status ] );
	}
}

if ( ! function_exists( 'mvr_get_notifications' ) ) {
	/**
	 * Return the array of notifications based upon the args requested.
	 *
	 * @since 1.0.0
	 * @param Array $args Arguments.
	 * @return Object
	 */
	function mvr_get_notifications( $args = array() ) {
		global $wpdb;
		$wpdb_ref = &$wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'status'      => array_keys( mvr_get_notification_statuses() ),
				'include_ids' => array(),
				'exclude_ids' => array(),
				's'           => '',
				'to'          => '',
				'source_id'   => '',
				'source_from' => '',
				'vendor_id'   => '',
				'page'        => 1,
				'limit'       => -1,
				'fields'      => 'objects',
				'orderby'     => 'ID',
				'order'       => 'DESC',
			)
		);

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
				(status LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR 
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
			$exclude_ids = " AND `ID` NOT IN ('" . implode( "','", $args['exclude_ids'] ) . "') ";
		} else {
			$exclude_ids = '';
		}

		// To.
		if ( ! empty( $args['to'] ) ) {
			$to = " AND `to` = '" . esc_sql( $args['to'] ) . "' ";
		} else {
			$to = '';
		}

		// Source ID.
		if ( ! empty( $args['vendor_id'] ) ) {
			if ( is_array( $args['vendor_id'] ) ) {
				$allowed_vendor_ids = " AND `vendor_id` IN ('" . implode( "','", $args['vendor_id'] ) . "') ";
			} else {
				$allowed_vendor_ids = " AND `vendor_id` = '" . esc_sql( $args['vendor_id'] ) . "' ";
			}
		} else {
			$allowed_vendor_ids = '';
		}

		// Source ID.
		if ( ! empty( $args['source_id'] ) ) {
			if ( is_array( $args['source_id'] ) ) {
				$allowed_source_ids = " AND `source_id` IN ('" . implode( "','", $args['source_id'] ) . "') ";
			} else {
				$allowed_source_ids = " AND `source_id` = '" . esc_sql( $args['source_id'] ) . "' ";
			}
		} else {
			$allowed_source_ids = '';
		}

		// Source From.
		if ( ! empty( $args['source_from'] ) ) {
			if ( is_array( $args['source_from'] ) ) {
				$allowed_source_from = " AND `source_from` IN ('" . implode( "','", $args['source_from'] ) . "') ";
			} else {
				$allowed_source_from = " AND `source_from` = '" . esc_sql( $args['source_from'] ) . "' ";
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

		$all_notification_ids = $wpdb_ref->get_var(
			"SELECT COUNT(DISTINCT ID) FROM {$wpdb->prefix}mvr_notification AS n
			WHERE 1=1 {$allowed_statuses} {$search_where} {$include_ids} {$exclude_ids} {$to} {$allowed_vendor_ids} {$allowed_source_ids} {$allowed_source_from}"
		);

		$notification_ids = $wpdb_ref->get_col(
			"SELECT DISTINCT ID FROM {$wpdb->prefix}mvr_notification AS n
			WHERE 1=1 {$allowed_statuses} {$search_where} {$include_ids} {$exclude_ids} {$to} {$allowed_vendor_ids} {$allowed_source_ids} {$allowed_source_from}
			{$orderby} {$order} {$limits}"
		);

		if ( 'objects' === $args['fields'] ) {
			$notifications = array_filter( array_combine( $notification_ids, array_map( 'mvr_get_notification', $notification_ids ) ) );
		} else {
			$notifications = $notification_ids;
		}

		$notifications_count = count( $notifications );
		$query_object        = (object) array(
			'notifications'       => $notifications,
			'total_notifications' => $all_notification_ids,
			'has_notification'    => $notifications_count > 0,
			'max_num_pages'       => $args['limit'] > 0 ? ceil( $all_notification_ids / $args['limit'] ) : 1,
		);

		return $query_object;
	}
}
