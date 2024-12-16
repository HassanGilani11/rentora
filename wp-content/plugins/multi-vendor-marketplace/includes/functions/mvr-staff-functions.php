<?php
/**
 * Staff Functions.
 *
 * @package Multi-Vendor Functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'mvr_get_current_staff_id' ) ) {
	/**
	 * Get current Staff ID
	 *
	 * @since 1.0.0
	 * @return Boolean.
	 */
	function mvr_get_current_staff_id() {
		$user_id = get_current_user_id();

		if ( empty( $user_id ) ) {
			return false;
		}

		$staffs_obj = mvr_get_staffs(
			array(
				'status'  => 'active',
				'user_id' => $user_id,
			)
		);

		if ( $staffs_obj->has_staff ) {
			$staff_obj = current( $staffs_obj->staffs );

			if ( mvr_is_staff( $staff_obj ) ) {
				return $staff_obj->get_id();
			}
		}

		return false;
	}
}

if ( ! function_exists( 'mvr_get_current_staff_object' ) ) {
	/**
	 * Get current Staff object
	 *
	 * @since 1.0.0
	 * @param Integer $user_id User ID.
	 * @return Boolean.
	 */
	function mvr_get_current_staff_object( $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();

			if ( empty( $user_id ) ) {
				return false;
			}
		}

		$staffs_obj = mvr_get_staffs(
			array(
				'user_id' => $user_id,
				'limit'   => 1,
				'status'  => array_keys( mvr_get_staff_statuses() ),
			)
		);

		if ( ! $staffs_obj->has_staff ) {
			return;
		}

		$staff_obj = current( $staffs_obj->staffs );

		if ( ! $staff_obj ) {
			return false;
		}

		return $staff_obj;
	}
}

if ( ! function_exists( 'mvr_get_staff_statuses' ) ) {
	/**
	 * Get Staff statuses.
	 *
	 * @since 1.0.0
	 * @return Array
	 */
	function mvr_get_staff_statuses() {
		return array(
			'mvr-active'   => __( 'Active', 'multi-vendor-marketplace' ),
			'mvr-inactive' => __( 'Inactive', 'multi-vendor-marketplace' ),
			'mvr-pending'  => __( 'Pending', 'multi-vendor-marketplace' ),
		);
	}
}

if ( ! function_exists( 'mvr_get_staff_status_name' ) ) {
	/**
	 * Get the vendor status name.
	 *
	 * @since 1.0.0
	 * @param String $status Status name.
	 * @return String
	 */
	function mvr_get_staff_status_name( $status ) {
		$statuses = mvr_get_staff_statuses();
		$status   = mvr_trim_post_status( $status );

		return isset( $statuses[ "mvr-{$status}" ] ) ? $statuses[ "mvr-{$status}" ] : $status;
	}
}

if ( ! function_exists( 'mvr_get_staff' ) ) {
	/**
	 * Get Vendor Staff.
	 *
	 * @since 1.0.0
	 * @param MVR_Staff $staff Vendor Staff.
	 * @param Boolean   $wp_error WordPress error.
	 * @return bool|\MVR_Staff
	 */
	function mvr_get_staff( $staff, $wp_error = false ) {
		if ( ! $staff ) {
			return false;
		}

		try {
			$staff = new MVR_Staff( $staff );
		} catch ( Exception $e ) {
			return $wp_error ? new WP_Error( 'error', $e->getMessage() ) : false;
		}

		return $staff;
	}
}

if ( ! function_exists( 'mvr_get_staffs' ) ) {
	/**
	 * Return the array of vendor staffs based upon the args requested.
	 *
	 * @since 1.0.0
	 * @param Array $args Arguments.
	 * @return Object
	 */
	function mvr_get_staffs( $args = array() ) {
		global $wpdb;
		$wpdb_ref = &$wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'status'      => array_keys( mvr_get_staff_statuses() ),
				'include_ids' => array(),
				'exclude_ids' => array(),
				's'           => '',
				'user_id'     => '',
				'vendor_id'   => '',
				'page'        => 1,
				'limit'       => -1,
				'fields'      => 'objects',
				'orderby'     => 'ID',
				'order'       => 'DESC',
			)
		);

		// Add the 'mvr-' prefix to status if needed.
		if ( ! empty( $args['status'] ) ) {
			if ( is_array( $args['status'] ) ) {
				foreach ( $args['status'] as &$status ) {
					if ( mvr_is_staff_status( 'mvr-' . $status ) ) {
						$status = 'mvr-' . $status;
					}
				}
			} elseif ( mvr_is_staff_status( 'mvr-' . $args['status'] ) ) {
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
			$search_fields = array( '_name' );
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

		// User ID.
		if ( is_array( $args['user_id'] ) ) {
			$allowed_users = " AND post_parent IN ('" . implode( "','", $args['user_id'] ) . "') ";
		} elseif ( ! empty( $args['user_id'] ) ) {
			$allowed_users = " AND post_parent = '" . esc_sql( $args['user_id'] ) . "' ";
		} else {
			$allowed_users = '';
		}

		// Vendor ID.
		if ( is_array( $args['vendor_id'] ) ) {
			$allowed_vendors = " AND pm.meta_key = '_vendor_id' AND pm.meta_value IN ('" . implode( "','", $args['vendor_id'] ) . "') ";
		} elseif ( ! empty( $args['vendor_id'] ) ) {
			$allowed_vendors = " AND pm.meta_key = '_vendor_id' AND pm.meta_value = '" . esc_sql( $args['vendor_id'] ) . "' ";
		} else {
			$allowed_vendors = '';
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

		$all_staff_ids = $wpdb_ref->get_var(
			$wpdb_ref->prepare(
				"
                    SELECT COUNT(DISTINCT ID) FROM {$wpdb_ref->posts} AS p
					INNER JOIN {$wpdb_ref->postmeta} AS pm ON (p.ID = pm.post_id)
					WHERE 1=1 AND post_type = '%s' 
					$allowed_statuses 
					$allowed_users 
					$allowed_vendors 
					$search_where 
					$include_ids 
					$exclude_ids 
				",
				'mvr_staff'
			)
		);

		$staff_ids = $wpdb_ref->get_col(
			$wpdb_ref->prepare(
				"
					SELECT DISTINCT ID FROM {$wpdb_ref->posts} AS p
					INNER JOIN {$wpdb_ref->postmeta} AS pm ON (p.ID = pm.post_id)
					WHERE 1=1 AND post_type = '%s' 
					$allowed_statuses 
					$allowed_users 
					$allowed_vendors 
					$search_where 
					$include_ids 
					$exclude_ids 
					$orderby 
					$order 
					$limits 
				",
				'mvr_staff'
			)
		);

		if ( 'objects' === $args['fields'] ) {
			$staffs = array_filter( array_combine( $staff_ids, array_map( 'mvr_get_staff', $staff_ids ) ) );
		} else {
			$staffs = $staff_ids;
		}

		$staffs_count = count( $staffs );
		$query_object = (object) array(
			'staffs'        => $staffs,
			'total_staffs'  => $all_staff_ids,
			'has_staff'     => $staffs_count > 0,
			'max_num_pages' => $args['limit'] > 0 ? ceil( $all_staff_ids / $args['limit'] ) : 1,
		);

		return $query_object;
	}
}

if ( ! function_exists( 'mvr_before_delete_staff' ) ) {
	/**
	 * Before Delete Staff Object.
	 *
	 * @since 1.0.0
	 * @param Integer $staff_id Staff Object.
	 */
	function mvr_before_delete_staff( $staff_id ) {
		if ( $staff_id ) {
			$staff_obj = mvr_get_staff( $staff_id );

			if ( mvr_is_staff( $staff_obj ) ) {
				$user_id = $staff_obj->get_user_id();

				if ( $user_id ) {
					$user_obj = get_user_by( 'ID', $user_id );

					if ( $user_obj instanceof WP_User ) {
						$user_obj->remove_role( 'mvr-staff' );
						$user_obj->set_role( 'customer' );
					}
				}
			}
		}
	}
}

add_action( 'mvr_before_delete_staff', 'mvr_before_delete_staff' );
add_action( 'before_delete_post', 'mvr_before_delete_staff' );

if ( ! function_exists( 'mvr_get_allowed_user_ids' ) ) {
	/**
	 * Return the array of user ids
	 *
	 * @since 1.0.0
	 * @return Array
	 */
	function mvr_get_allowed_user_ids() {
		$user_roles = get_option( 'mvr_settings_become_a_vendor_roles' );
		$user_ids   = array();

		if ( mvr_check_is_array( $user_roles ) ) {
			foreach ( $user_roles as $role ) {
				$role_user_ids = array_values( mvr_get_users_by_role( $role ) );

				if ( mvr_check_is_array( $user_roles ) ) {
					$user_ids = array_merge( $user_ids, $role_user_ids );
				}
			}
		}

		return array_filter( array_unique( $user_ids ) );
	}
}

if ( ! function_exists( 'mvr_get_users_by_role' ) ) {
	/**
	 * Return the array of users based upon the args requested.
	 *
	 * @since 1.0.0
	 * @param String $role User Role.
	 * @param String $orderby Order by.
	 * @param String $order Order.
	 * @return Array
	 */
	function mvr_get_users_by_role( $role, $orderby = 'ID', $order = 'ASC' ) {
		$args = array(
			'role'    => $role,
			'orderby' => $orderby,
			'order'   => $order,
			'fields'  => 'ID',
		);

		$users = get_users( $args );

		return $users;
	}
}

if ( ! function_exists( 'mvr_get_users' ) ) {
	/**
	 * Return the array of users based upon the args requested.
	 *
	 * @since 1.0.0
	 * @param Array $args Arguments.
	 * @return Object
	 */
	function mvr_get_users( $args = array() ) {
		global $wpdb;
		$wpdb_ref = &$wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'include_ids' => array(),
				'exclude_ids' => array(),
				's'           => '',
				'page'        => 1,
				'limit'       => -1,
				'fields'      => 'objects',
				'orderby'     => 'ID',
				'order'       => 'DESC',
				'date_before' => '',
				'date_after'  => '',
			)
		);

		// Date Before.
		if ( ! empty( $args['date_before'] ) ) {
			$date_before = 'AND user_registered <="' . gmdate( 'Y-m-d H:i:s', absint( $args['date_before'] ) ) . '"';
		} else {
			$date_before = '';
		}

		// Date after.
		if ( ! empty( $args['date_after'] ) ) {
			$date_after = ' AND user_registered >= "' . gmdate( 'Y-m-d H:i:s', absint( $args['date_after'] ) ) . '"';
		} else {
			$date_after = '';
		}

		// Search term.
		if ( ! empty( $args['s'] ) ) {
			$term          = str_replace( '#', '', wc_clean( wp_unslash( $args['s'] ) ) );
			$search_fields = array();
			$search_where  = " AND ( 
                (ID LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR
                (user_login LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR
                (user_email LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR
				(user_nicename LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR 
				(display_name LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%')
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

		$all_user_ids = $wpdb_ref->get_var(
			"SELECT COUNT(DISTINCT ID) FROM {$wpdb->prefix}users AS u
			INNER JOIN wp_usermeta as um on um.user_id = u.id 
			INNER JOIN wp_usermeta as um1 on um1.user_id = u.id 
			WHERE 1=1 {$search_where} {$include_ids} {$exclude_ids} {$date_before} {$date_after}"
		);

		$user_ids = $wpdb_ref->get_col(
			"SELECT DISTINCT ID FROM {$wpdb->prefix}users AS u
			INNER JOIN wp_usermeta as um on um.user_id = u.id 
			INNER JOIN wp_usermeta as um1 on um1.user_id = u.id
			WHERE 1=1 {$search_where} {$include_ids} {$exclude_ids} {$date_before} {$date_after}
			{$orderby} {$order} {$limits}"
		);

		if ( 'objects' === $args['fields'] ) {
			$users = array_filter( array_combine( $user_ids, array_map( 'mvr_get_user', $user_ids ) ) );
		} else {
			$users = $user_ids;
		}

		$users_count  = count( $users );
		$query_object = (object) array(
			'users'         => $users,
			'total_users'   => $all_user_ids,
			'has_user'      => $users_count > 0,
			'max_num_pages' => $args['limit'] > 0 ? ceil( $all_user_ids / $args['limit'] ) : 1,
		);

		return $query_object;
	}
}

if ( ! function_exists( 'mvr_get_user' ) ) {
	/**
	 * Return the user Obj
	 *
	 * @since 1.0.0
	 * @param Array $user_id user id.
	 * @return Object
	 */
	function mvr_get_user( $user_id ) {
		return $user_id ? get_user_by( 'id', $user_id ) : false;
	}
}
