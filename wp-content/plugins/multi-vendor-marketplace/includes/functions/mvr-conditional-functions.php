<?php
/**
 * Conditional Functions
 *
 * @package Multi Vendor/Functions
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'mvr_check_is_array' ) ) {

	/**
	 * Check if resource is array.
	 *
	 * @since 1.0.0
	 * @param Array $data Array.
	 * @return Boolean
	 * */
	function mvr_check_is_array( $data ) {
		return ( is_array( $data ) && ! empty( $data ) );
	}
}

if ( ! class_exists( 'mvr_is_dashboard_page' ) ) {
	/**
	 * Returns true when viewing an Dashboard page.
	 *
	 * @since 1.0.0
	 * @param String $end_point Endpoint.
	 * @return Boolean
	 */
	function mvr_is_dashboard_page( $end_point = '' ) {
		global $post, $wp;

		if ( ! empty( $end_point ) ) {
			$condition = isset( $wp->query_vars[ $end_point ] );
		} else {
			$condition = true;
		}

		return is_singular() && is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'mvr_dashboard' ) && $condition;
	}
}

if ( ! class_exists( 'mvr_is_stores_page' ) ) {
	/**
	 * Returns true when viewing an Stores page.
	 *
	 * @since 1.0.0
	 * @param String $end_point Endpoint.
	 * @return Boolean
	 */
	function mvr_is_stores_page( $end_point = '' ) {
		global $post, $wp;

		if ( ! empty( $end_point ) ) {
			$condition = isset( $wp->query_vars[ $end_point ] );
		} else {
			$condition = true;
		}

		return is_singular() && is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'mvr_stores' ) && $condition;
	}
}

if ( ! class_exists( 'mvr_is_vendor_register_page' ) ) {
	/**
	 * Returns true when viewing an Vendor Register page.
	 *
	 * @since 1.0.0
	 * @return Boolean
	 */
	function mvr_is_vendor_register_page() {
		global $post;

		return is_singular() && is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'mvr_vendor_register' );
	}
}

if ( ! class_exists( 'mvr_is_vendor_login_page' ) ) {
	/**
	 * Returns true when viewing an Vendor login page.
	 *
	 * @since 1.0.0
	 * @return Boolean
	 */
	function mvr_is_vendor_login_page() {
		global $post;

		return is_singular() && is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'mvr_vendor_login' );
	}
}

if ( ! function_exists( 'mvr_is_vendor' ) ) {
	/**
	 * Check whether the given the value is vendor.
	 *
	 * @since 1.0.0
	 * @param  Mixed $vendor Post object or post ID of the Vendor.
	 * @return bool True on success.
	 */
	function mvr_is_vendor( $vendor ) {
		if ( ! is_object( $vendor ) ) {
			$vendor = mvr_get_vendor( $vendor );
		}

		return $vendor && is_a( $vendor, 'MVR_Vendor' );
	}
}

if ( ! function_exists( 'mvr_is_staff' ) ) {
	/**
	 * Check whether the given the value is Staff.
	 *
	 * @since 1.0.0
	 * @param  Mixed $staff Post object or post ID of the Staff.
	 * @return bool True on success.
	 */
	function mvr_is_staff( $staff ) {
		return $staff && is_a( $staff, 'MVR_Staff' );
	}
}

if ( ! function_exists( 'mvr_user_is_vendor' ) ) {
	/**
	 * Check whether the user is already a vendor.
	 *
	 * @since 1.0.0
	 * @param Integer $user_id User Id.
	 * @return Boolean
	 */
	function mvr_user_is_vendor( $user_id ) {
		if ( empty( $user_id ) ) {
			return false;
		}

		$vendors_obj = mvr_get_vendors(
			array(
				'status'  => array_keys( mvr_get_vendor_statuses() ),
				'user_id' => $user_id,
				'limit'   => 1,
				'fields'  => 'ids',
			)
		);

		return $vendors_obj->has_vendor;
	}
}

if ( ! function_exists( 'mvr_user_is_staff' ) ) {
	/**
	 * Check whether the user is already a Staff.
	 *
	 * @since 1.0.0
	 * @param Integer $user_id User Id.
	 * @return Boolean
	 */
	function mvr_user_is_staff( $user_id ) {
		if ( empty( $user_id ) ) {
			return false;
		}

		$staffs_obj = mvr_get_staffs(
			array(
				'status'  => array_keys( mvr_get_staff_statuses() ),
				'user_id' => $user_id,
				'limit'   => 1,
				'fields'  => 'ids',
			)
		);

		return $staffs_obj->has_staff;
	}
}

if ( ! function_exists( 'mvr_is_vendor_status' ) ) {
	/**
	 * See if a string is an vendor status.
	 *
	 * @since 1.0.0
	 * @param  String $maybe_status Status, including any mvr- prefix.
	 * @return Boolean
	 */
	function mvr_is_vendor_status( $maybe_status ) {
		$statuses = mvr_get_vendor_statuses();

		return isset( $statuses[ $maybe_status ] );
	}
}


if ( ! function_exists( 'mvr_is_staff_status' ) ) {
	/**
	 * See if a string is an Staff status.
	 *
	 * @since 1.0.0
	 * @param  String $maybe_status Status, including any mvr- prefix.
	 * @return Boolean
	 */
	function mvr_is_staff_status( $maybe_status ) {
		$statuses = mvr_get_staff_statuses();

		return isset( $statuses[ $maybe_status ] );
	}
}

if ( ! function_exists( 'mvr_user_eligible_for_register' ) ) {
	/**
	 * Check User Eligible for Vendor Registration.
	 *
	 * @since 1.0.0
	 * @param Integer $user_id User ID.
	 * @return Boolean
	 */
	function mvr_user_eligible_for_register( $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		if ( empty( $user_id ) || 'yes' !== get_option( 'mvr_settings_allow_user_vendor_reg', 'no' ) ) {
			return false;
		}

		$user_obj      = get_userdata( $user_id );
		$allowed_roles = get_option( 'mvr_settings_become_a_vendor_roles', array( 'customer' ) );
		$bool          = array_intersect( $allowed_roles, $user_obj->roles );

		return mvr_check_is_array( $bool );
	}
}

if ( ! function_exists( 'mvr_check_shop_name_exists' ) ) {
	/**
	 * Check Shop Name exists or not.
	 *
	 * @since 1.0.0
	 * @param String  $shop_name Shop Name.
	 * @param Integer $exclude_id Exclude Vendor ID.
	 * @return Boolean
	 */
	function mvr_check_shop_name_exists( $shop_name = '', $exclude_id = '' ) {
		if ( empty( $shop_name ) ) {
			return false;
		}

		if ( $exclude_id ) {
			$exclude_vendor = " AND ID != '" . $exclude_id . "' ";
		} else {
			$exclude_vendor = '';
		}

		global $wpdb;
		$wpdb_ref = &$wpdb;

		$post_ids = $wpdb_ref->get_col(
			$wpdb_ref->prepare(
				"
					SELECT DISTINCT ID FROM {$wpdb_ref->posts} AS p 
					INNER JOIN {$wpdb_ref->postmeta} AS pm ON (p.ID = pm.post_id)
					WHERE 1=1 AND post_type = '%s' AND post_status IN ('" . implode( "','", array_keys( mvr_get_vendor_statuses() ) ) . "') 
					AND pm.meta_key='_shop_name' AND pm.meta_value = '%s'
					{$exclude_vendor}
                ",
				'mvr_vendor',
				$shop_name
			)
		);

		return mvr_check_is_array( $post_ids );
	}
}

if ( ! function_exists( 'mvr_check_shop_slug_exists' ) ) {
	/**
	 * Check Shop slug exists or not.
	 *
	 * @since 1.0.0
	 * @param Sting   $slug Slug.
	 * @param Integer $exclude_id Exclude Vendor ID.
	 * @return Boolean
	 */
	function mvr_check_shop_slug_exists( $slug = '', $exclude_id = '' ) {
		if ( empty( $slug ) ) {
			return false;
		}

		if ( $exclude_id ) {
			$exclude_vendor = " AND ID != '" . $exclude_id . "' ";
		} else {
			$exclude_vendor = '';
		}

		global $wpdb;
		$wpdb_ref = &$wpdb;

		$post_ids = $wpdb_ref->get_col(
			$wpdb_ref->prepare(
				"
					SELECT DISTINCT ID FROM {$wpdb_ref->posts} AS p
					WHERE 1=1 AND post_type = '%s' AND post_status IN ('" . implode( "','", array_keys( mvr_get_vendor_statuses() ) ) . "') AND post_name = '%s' $exclude_vendor
                ",
				'mvr_vendor',
				$slug
			)
		);

		return mvr_check_is_array( $post_ids );
	}
}

if ( ! function_exists( 'mvr_cart_contains_vendor_product' ) ) {
	/**
	 * Check whether cart contains vendor product ?
	 *
	 * @since 1.0.0
	 * @return Boolean
	 */
	function mvr_cart_contains_vendor_product() {
		if ( ! empty( WC()->cart->cart_contents ) ) {
			foreach ( WC()->cart->cart_contents as $item_key => $item ) {
				$product_id = isset( $item['product_id'] ) ? $item['product_id'] : '';

				if ( empty( $product_id ) ) {
					continue;
				}

				$product_obj = wc_get_product( $product_id );

				if ( ! $product_obj ) {
					continue;
				}

				$product_vendor = $product_obj->get_meta( '_mvr_vendor', true );

				if ( ! empty( $product_vendor ) ) {
					return true;
				}
			}
		}

		return false;
	}
}

if ( ! function_exists( 'mvr_check_user_as_vendor' ) ) {
	/**
	 * Check whether the given the value is vendor.
	 *
	 * @since 1.0.0
	 * @param  Integer $user_id User ID.
	 * @return Boolean.
	 */
	function mvr_check_user_as_vendor_or_staff( $user_id = '' ) {
		if ( mvr_check_user_as_vendor( $user_id ) || mvr_check_user_as_staff( $user_id ) ) {
			return true;
		}

		return false;
	}
}

if ( ! function_exists( 'mvr_check_user_as_vendor' ) ) {
	/**
	 * Check whether the given the value is vendor.
	 *
	 * @since 1.0.0
	 * @param  Integer $user_id User ID.
	 * @return Boolean.
	 */
	function mvr_check_user_as_vendor( $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();

			if ( empty( $user_id ) ) {
				return false;
			}
		}

		$user_meta  = get_userdata( $user_id );
		$user_roles = $user_meta->roles;

		return in_array( 'mvr-vendor', $user_roles, true );
	}
}

if ( ! function_exists( 'mvr_check_user_as_staff' ) ) {
	/**
	 * Check whether the given the value is Staff.
	 *
	 * @since 1.0.0
	 * @param  Integer $user_id User ID.
	 * @return Boolean.
	 */
	function mvr_check_user_as_staff( $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();

			if ( empty( $user_id ) ) {
				return false;
			}
		}

		$user_meta  = get_userdata( $user_id );
		$user_roles = $user_meta->roles;

		return in_array( 'mvr-staff', $user_roles, true );
	}
}

if ( ! function_exists( 'mvr_allow_endpoint' ) ) {
	/**
	 * Check Endpoint Capability.
	 *
	 * @since 1.0.0
	 * @param String $endpoint End Point.
	 * @return Boolean.
	 */
	function mvr_allow_endpoint( $endpoint ) {
		/**
		 * Check endpoint Capabilities.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( 'mvr_allow_endpoint', true, $endpoint );
	}
}

if ( ! function_exists( 'mvr_check_cart_contain_other_vendors' ) ) {
	/**
	 * Vendor ID.
	 *
	 * @since 1.0.0
	 * @param Integer $vendor_id Vendor ID.
	 * @return Boolean.
	 */
	function mvr_check_cart_contain_other_vendors( $vendor_id ) {
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$product_id  = isset( $cart_item['product_id'] ) ? $cart_item['product_id'] : '';
			$product_obj = wc_get_product( $product_id );

			if ( ! is_a( $product_obj, 'WC_Product' ) ) {
				continue;
			}

			if ( $product_obj->get_meta( '_mvr_vendor', true ) !== $vendor_id ) {
				return true;
			}
		}

		return false;
	}
}
