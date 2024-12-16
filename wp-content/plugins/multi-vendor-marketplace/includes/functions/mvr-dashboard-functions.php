<?php
/**
 * Multi vendor Dashboard Functions
 *
 * @package Multi Vendor\Functions
 * @version 2.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if directly accessed.
}

if ( ! class_exists( 'mvr_get_dashboard_top_menu_items' ) ) {
	/**
	 * Get Dashboard top menu items.
	 *
	 * @since 1.0.0
	 * @return Array
	 */
	function mvr_get_dashboard_top_menu_items() {
		$endpoints           = mvr()->query->get_query_vars();
		$menu_items          = array();
		$dashboard_endpoints = array(
			'mvr-notification',
			'mvr-enquiry',
		);

		if ( mvr_check_is_array( $endpoints ) ) {
			// Remove missing endpoints.
			foreach ( $endpoints as $endpoint_id => $endpoint ) {

				if ( ! mvr_allow_endpoint( $endpoint_id ) ) {
					continue;
				}

				if ( in_array( $endpoint_id, $dashboard_endpoints, true ) ) {
					$menu_items[ $endpoint_id ] = array(
						'label'    => mvr()->query->get_endpoint_title( $endpoint_id ),
						'endpoint' => $endpoint,
					);
				}
			}
		}

		/**
		 * Dashboard top menu items
		 *
		 * @since 1.0.0
		 * @param Array $menu_items Menu items.
		 * @param Array $endpoints Endpoints.
		 */
		return apply_filters( 'mvr_dashboard_top_menu_items', $menu_items, $endpoints );
	}
}

if ( ! class_exists( 'mvr_get_dashboard_menu_items' ) ) {
	/**
	 * Get Dashboard menu items.
	 *
	 * @since 1.0.0
	 * @return Array
	 */
	function mvr_get_dashboard_menu_items() {
		$endpoints           = mvr()->query->get_query_vars();
		$menu_items          = array(
			'mvr-home' => array(
				'label'    => __( 'Home', 'multi-vendor-marketplace' ),
				'endpoint' => 'mvr-home',
			),
		);
		$dashboard_endpoints = array(
			'mvr-products',
			'mvr-orders',
			'mvr-coupons',
			'mvr-withdraw',
			'mvr-transaction',
			'mvr-customers',
			'mvr-duplicate',
			'mvr-payments',
			'mvr-payout',
			'mvr-profile',
			'mvr-address',
			'mvr-social-links',
			'mvr-staff',
			'mvr-reviews',
			'mvr-logout',
		);

		if ( mvr_check_is_array( $endpoints ) ) {
			// Remove missing endpoints.
			foreach ( $endpoints as $endpoint_id => $endpoint ) {
				if ( ! mvr_allow_endpoint( $endpoint_id ) ) {
					continue;
				}

				if ( in_array( $endpoint_id, $dashboard_endpoints, true ) ) {
					$menu_items[ $endpoint_id ] = array(
						'label'    => mvr()->query->get_endpoint_title( $endpoint_id ),
						'endpoint' => $endpoint,
					);
				}
			}
		}

		/**
		 * Dashboard menu items
		 *
		 * @since 1.0.0
		 * @param Array $menu_items Menu items.
		 * @param Array $endpoints Endpoints.
		 */
		return apply_filters( 'mvr_dashboard_menu_items', $menu_items, $endpoints );
	}
}

if ( ! class_exists( 'mvr_get_dashboard_menu_item_classes' ) ) {

	/**
	 * Get account menu item classes.
	 *
	 * @since 1.0.0
	 * @param String $endpoint Endpoint.
	 * @return String
	 */
	function mvr_get_dashboard_menu_item_classes( $endpoint ) {
		global $wp;
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		$classes = array(
			'mvr-dashboard-navigation-link',
			'mvr-dashboard-navigation-link--' . $endpoint,
		);

		if ( 'mvr-profile' === $endpoint && ! $vendor_obj->cleared_profile_tab() ) {
			$classes[] = 'mvr-required-tab';
		}

		if ( 'mvr-address' === $endpoint && ! $vendor_obj->cleared_address_tab() ) {
			$classes[] = 'mvr-required-tab';
		}

		if ( 'mvr-payments' === $endpoint && ! $vendor_obj->cleared_payment_tab() ) {
			$classes[] = 'mvr-required-tab';
		}

		// Set current item class.
		$current = isset( $wp->query_vars[ $endpoint ] );

		if ( 'mvr-home' === $endpoint && ( isset( $wp->query_vars['page'] ) || empty( $wp->query_vars ) ) ) {
			$current = true; // Dashboard is not an endpoint, so needs a custom check.
		} elseif ( 'mvr-orders' === $endpoint && isset( $wp->query_vars['mvr-view-order'] ) ) {
			$current = true; // When looking at individual order, highlight Orders list item (to signify where in the menu the user currently is).
		} elseif ( 'mvr-products' === $endpoint && ( isset( $wp->query_vars['mvr-add-product'] ) || isset( $wp->query_vars['mvr-edit-product'] ) ) ) {
			$current = true;
		} elseif ( 'mvr-coupons' === $endpoint && ( isset( $wp->query_vars['mvr-add-coupon'] ) || isset( $wp->query_vars['mvr-edit-coupon'] ) ) ) {
			$current = true;
		} elseif ( 'mvr-withdraw' === $endpoint && ( isset( $wp->query_vars['mvr-add-withdraw'] ) ) ) {
			$current = true;
		}

		if ( $current ) {
			$classes[] = 'is-active';
		}

		/**
		 * Dashboard Menu Classes
		 *
		 * @since 1.0.0
		 * @param Array $classes Classes.
		 * @param String $endpoint Endpoint.
		 */
		$classes = apply_filters( 'mvr_dashboard_menu_item_classes', $classes, $endpoint );

		return implode( ' ', array_map( 'sanitize_html_class', $classes ) );
	}
}

if ( ! class_exists( 'mvr_get_dashboard_endpoint_url' ) ) {
	/**
	 * Get account endpoint URL.
	 *
	 * @since 2.6.0
	 * @param string  $endpoint Endpoint.
	 * @param Integer $id Identifier.
	 * @return string
	 */
	function mvr_get_dashboard_endpoint_url( $endpoint, $id = '' ) {
		$query_vars = mvr()->query->get_query_vars();
		$endpoint   = ! empty( $query_vars[ $endpoint ] ) ? $query_vars[ $endpoint ] : $endpoint;

		if ( 'mvr-home' === $endpoint ) {
			return mvr_get_page_permalink( 'dashboard' );
		}

		if ( 'mvr-logout' === $endpoint ) {
			return mvr_logout_url();
		}

		return wc_get_endpoint_url( $endpoint, $id, mvr_get_page_permalink( 'dashboard' ) );
	}
}

if ( ! class_exists( 'mvr_get_dashboard_enquiry_columns' ) ) {
	/**
	 * Dashboard > Enquiry columns.
	 *
	 * @since 1.0.0
	 * @return Array
	 */
	function mvr_get_dashboard_enquiry_columns() {
		$args = array(
			'enquiry-customer' => __( 'Customer', 'multi-vendor-marketplace' ),
			'enquiry-message'  => __( 'Message', 'multi-vendor-marketplace' ),
			'enquiry-date'     => __( 'Date', 'multi-vendor-marketplace' ),
			'enquiry-action'   => __( 'Action', 'multi-vendor-marketplace' ),
		);

		/**
		 * Filters the array of enquiry columns.
		 *
		 * @since 1.0.0
		 * @param Array $args Array of column labels keyed by column IDs.
		 */
		return apply_filters( 'mvr_dashboard_enquiry_columns', $args );
	}
}

if ( ! class_exists( 'mvr_get_dashboard_notification_columns' ) ) {
	/**
	 * Dashboard > Notification columns.
	 *
	 * @since 1.0.0
	 * @return Array
	 */
	function mvr_get_dashboard_notification_columns() {
		$args = array(
			'notification-type'    => __( 'Type', 'multi-vendor-marketplace' ),
			'notification-message' => __( 'Message', 'multi-vendor-marketplace' ),
			'notification-date'    => __( 'Date', 'multi-vendor-marketplace' ),
		);

		/**
		 * Filters the array of notification columns.
		 *
		 * @since 1.0.0
		 * @param Array $args Array of column labels keyed by column IDs.
		 */
		return apply_filters( 'mvr_dashboard_notification_columns', $args );
	}
}

if ( ! class_exists( 'mvr_get_dashboard_products_columns' ) ) {
	/**
	 * Dashboard > Products columns.
	 *
	 * @since 1.0.0
	 * @return Array
	 */
	function mvr_get_dashboard_products_columns() {
		$args                     = array();
		$args['product-thumb']    = get_option( 'mvr_dashboard_product_image_column_label', 'Image' );
		$args['product-details']  = get_option( 'mvr_dashboard_product_details_column_label', 'Product Details' );
		$args['product-price']    = get_option( 'mvr_dashboard_product_price_column_label', 'Price' );
		$args['product-category'] = get_option( 'mvr_dashboard_product_categories_column_label', 'Categories' );
		$args['product-tag']      = get_option( 'mvr_dashboard_product_tags_column_label', 'Tags' );
		$args['product-actions']  = get_option( 'mvr_dashboard_product_actions_column_label', 'Actions' );

		/**
		 * Filters the array of Products columns.
		 *
		 * @since 1.0.0
		 * @param Array $args Array of column labels keyed by column IDs.
		 */
		return apply_filters( 'mvr_dashboard_products_columns', $args );
	}
}

if ( ! class_exists( 'mvr_get_dashboard_duplicate_products_columns' ) ) {
	/**
	 * Dashboard > Duplicate Products columns.
	 *
	 * @since 1.0.0
	 * @return Array
	 */
	function mvr_get_dashboard_duplicate_products_columns() {
		$args                     = array();
		$args['product-thumb']    = get_option( 'mvr_dashboard_duplicate_product_image_column_label', 'Image' );
		$args['product-details']  = get_option( 'mvr_dashboard_duplicate_product_details_column_label', 'Product Details' );
		$args['product-price']    = get_option( 'mvr_dashboard_duplicate_product_price_column_label', 'Price' );
		$args['product-category'] = get_option( 'mvr_dashboard_duplicate_product_categories_column_label', 'Categories' );
		$args['product-tag']      = get_option( 'mvr_dashboard_duplicate_product_tags_column_label', 'Tags' );
		$args['product-actions']  = get_option( 'mvr_dashboard_duplicate_product_actions_column_label', 'Actions' );

		/**
		 * Filters the array of Products columns.
		 *
		 * @since 1.0.0
		 * @param Array $args Array of column labels keyed by column IDs.
		 */
		return apply_filters( 'mvr_dashboard_duplicate_products_columns', $args );
	}
}

if ( ! class_exists( 'mvr_get_dashboard_withdraw_columns' ) ) {
	/**
	 * Dashboard > Withdraw columns.
	 *
	 * @since 1.0.0
	 * @return Array
	 */
	function mvr_get_dashboard_withdraw_columns() {
		$args = array(
			'withdraw-id'      => get_option( 'mvr_dashboard_withdraw_id_column_label', 'ID' ),
			'withdraw-status'  => get_option( 'mvr_dashboard_withdraw_status_column_label', 'Status' ),
			'withdraw-amount'  => get_option( 'mvr_dashboard_withdraw_amount_column_label', 'Amount' ),
			'withdraw-charge'  => get_option( 'mvr_dashboard_withdraw_charge_column_label', 'Charge' ),
			'withdraw-payment' => get_option( 'mvr_dashboard_withdraw_payment_column_label', 'Payment' ),
			'withdraw-date'    => get_option( 'mvr_dashboard_withdraw_date_column_label', 'Date' ),
		);

		/**
		 * Filters the array of Withdraw columns.
		 *
		 * @since 1.0.0
		 * @param Array $args Array of column labels keyed by column IDs.
		 */
		return apply_filters( 'mvr_dashboard_withdraw_columns', $args );
	}
}

if ( ! class_exists( 'mvr_get_dashboard_transaction_columns' ) ) {
	/**
	 * Dashboard > Withdraw columns.
	 *
	 * @since 1.0.0
	 * @return Array
	 */
	function mvr_get_dashboard_transaction_columns() {
		$args = array(
			'transaction-id'          => get_option( 'mvr_dashboard_transaction_id_column_label', 'ID' ),
			'transaction-status'      => get_option( 'mvr_dashboard_transaction_status_column_label', 'Status' ),
			'transaction-amount'      => get_option( 'mvr_dashboard_transaction_amount_column_label', 'Amount' ),
			'transaction-type'        => get_option( 'mvr_dashboard_transaction_type_column_label', 'Type' ),
			'transaction-description' => get_option( 'mvr_dashboard_transaction_desc_column_label', 'Description' ),
			'transaction-date'        => get_option( 'mvr_dashboard_transaction_date_column_label', 'Date' ),
		);

		/**
		 * Filters the array of Transaction columns.
		 *
		 * @since 1.0.0
		 * @param Array $args Array of column labels keyed by column IDs.
		 */
		return apply_filters( 'mvr_dashboard_transaction_columns', $args );
	}
}

if ( ! class_exists( 'mvr_get_dashboard_staff_columns' ) ) {
	/**
	 * Dashboard > Staff columns.
	 *
	 * @since 1.0.0
	 * @return Array
	 */
	function mvr_get_dashboard_staff_columns() {
		$args = array(
			'staff-image'   => get_option( 'mvr_dashboard_staff_image_column_label', 'Image' ),
			'staff-details' => get_option( 'mvr_dashboard_staff_name_column_label', 'Name' ),
			'staff-date'    => get_option( 'mvr_dashboard_staff_date_column_label', 'Date' ),
			'staff-actions' => get_option( 'mvr_dashboard_staff_actions_column_label', 'Actions' ),
		);

		/**
		 * Filters the array of Staff columns.
		 *
		 * @since 1.0.0
		 * @param Array $args Array of column labels keyed by column IDs.
		 */
		return apply_filters( 'mvr_dashboard_staff_columns', $args );
	}
}

if ( ! class_exists( 'mvr_get_dashboard_products_actions' ) ) {
	/**
	 * Product actions.
	 *
	 * @since  1.0.0
	 * @param  Integer|WC_Product $product_obj Product instance or ID.
	 * @return Array
	 */
	function mvr_get_dashboard_products_actions( $product_obj ) {
		if ( ! is_object( $product_obj ) ) {
			$product_id  = absint( $product_obj );
			$product_obj = wc_get_product( $product_id );
		}

		$actions          = array();
		$product_endpoint = ( 'publish' === $product_obj->get_status() ) ? 'mvr-edit-product-publish' : 'mvr-edit-product';

		if ( mvr_allow_endpoint( $product_endpoint ) ) {
			$actions['edit'] = array(
				'url'  => mvr_get_dashboard_endpoint_url( 'mvr-edit-product', $product_obj->get_id() ),
				'name' => get_option( 'mvr_dashboard_product_edit_label', 'Edit' ),
			);
		}

		$actions['view'] = array(
			'url'  => get_permalink( $product_obj->get_id() ),
			'name' => get_option( 'mvr_dashboard_product_view_label', 'View' ),
		);

		if ( mvr_allow_endpoint( 'mvr-delete-product' ) ) {
			$actions['delete'] = array(
				'url'  => add_query_arg(
					array(
						'action'     => 'mvr_delete_product',
						'product_id' => $product_obj->get_id(),
						'_mvr_nonce' => wp_create_nonce( 'mvr-delete-product-nonce' ),
					),
					mvr_get_dashboard_endpoint_url( 'mvr-products' )
				),
				'name' => get_option( 'mvr_dashboard_product_delete_label', 'Delete' ),
			);
		}

		/**
		 * Product actions.
		 *
		 * @since 1.0.0
		 * @param Array $actions Actions.
		 * @param WC_Product $product_obj Product object.
		 */
		return apply_filters( 'mvr_dashboard_products_actions', $actions, $product_obj );
	}
}

if ( ! class_exists( 'mvr_get_dashboard_staff_actions' ) ) {
	/**
	 * Staff actions.
	 *
	 * @since  1.0.0
	 * @param  Integer|MVR_Staff $staff_obj Staff instance or ID.
	 * @return Array
	 */
	function mvr_get_dashboard_staff_actions( $staff_obj ) {
		if ( ! is_object( $staff_obj ) ) {
			$staff_id  = absint( $staff_obj );
			$staff_obj = mvr_get_staff( $staff_id );
		}

		$actions = array(
			'edit'   => array(
				'url'  => mvr_get_dashboard_endpoint_url( 'mvr-edit-staff', $staff_obj->get_id() ),
				'name' => get_option( 'mvr_dashboard_edit_staff_btn_label', 'Edit' ),
			),
			'delete' => array(
				'url'  => add_query_arg(
					array(
						'action'     => 'mvr_delete_staff',
						'staff_id'   => $staff_obj->get_id(),
						'_mvr_nonce' => wp_create_nonce( 'mvr-dashboard-staff-nonce' ),
					),
					mvr_get_dashboard_endpoint_url( 'mvr-staff' )
				),
				'name' => get_option( 'mvr_dashboard_delete_staff_btn_label', 'Delete' ),
			),
		);

		/**
		 * Staff actions.
		 *
		 * @since 1.0.0
		 * @param Array $actions Actions.
		 * @param MVR_Staff $staff_obj Staff object.
		 */
		return apply_filters( 'mvr_dashboard_staff_actions', $actions, $staff_obj );
	}
}

if ( ! class_exists( 'mvr_get_dashboard_duplicate_products_actions' ) ) {
	/**
	 * Get account orders actions.
	 *
	 * @since  3.2.0
	 * @param  int|WC_Product $product_obj Product instance or ID.
	 * @return array
	 */
	function mvr_get_dashboard_duplicate_products_actions( $product_obj ) {
		if ( ! is_object( $product_obj ) ) {
			$product_id  = absint( $product_obj );
			$product_obj = wc_get_product( $product_id );
		}

		$actions = array(
			'mvr-duplicate' => array(
				'url'  => '',
				'name' => __( 'Duplicate', 'multi-vendor-marketplace' ),
			),
		);

		/**
		 * Duplicate Product actions.
		 *
		 * @since 1.0.0
		 * @param Array $actions Actions.
		 * @param WC_Product $product_obj Product object.
		 */
		return apply_filters( 'mvr_dashboard_duplicate_products_actions', $actions, $product_obj );
	}
}

if ( ! class_exists( 'mvr_get_dashboard_orders_columns' ) ) {
	/**
	 * Dashboard > Order columns.
	 *
	 * @since 1.0.0
	 * @return Array
	 */
	function mvr_get_dashboard_orders_columns() {
		$args = array(
			'order-number'  => get_option( 'mvr_dashboard_order_id_column_label', 'Order' ),
			'order-date'    => get_option( 'mvr_dashboard_order_date_column_label', 'Date' ),
			'order-status'  => get_option( 'mvr_dashboard_order_status_column_label', 'Status' ),
			'order-total'   => get_option( 'mvr_dashboard_order_total_column_label', 'Total' ),
			'order-actions' => get_option( 'mvr_dashboard_order_actions_column_label', 'Actions' ),
		);

		/**
		 * Filters the array of Orders columns.
		 *
		 * @since 1.0.0
		 * @param Array $args Array of column labels keyed by column IDs.
		 */
		return apply_filters( 'mvr_dashboard_orders_columns', $args );
	}
}

if ( ! class_exists( 'mvr_get_dashboard_orders_actions' ) ) {
	/**
	 * Get dashboard orders actions.
	 *
	 * @since  1.0.0
	 * @param  Integer|WC_Order $order_obj Product instance or ID.
	 * @return Array
	 */
	function mvr_get_dashboard_orders_actions( $order_obj ) {
		if ( ! is_object( $order_obj ) ) {
			$order_id  = absint( $order_obj );
			$order_obj = wc_get_order( $order_id );
		}

		$actions = array(
			'view' => array(
				'url'  => mvr_get_dashboard_endpoint_url( 'mvr-view-order', $order_obj->get_id() ),
				'name' => get_option( 'mvr_dashboard_view_order_btn_label', 'View' ),
			),
		);

		/**
		 * Order actions.
		 *
		 * @since 1.0.0
		 * @param Array $actions Actions.
		 * @param WC_Order $order_obj Order object.
		 */
		return apply_filters( 'mvr_dashboard_orders_actions', $actions, $order_obj );
	}
}

if ( ! class_exists( 'mvr_get_dashboard_coupons_columns' ) ) {
	/**
	 * Dashboard > Coupon columns.
	 *
	 * @since 1.0.0
	 * @return Array
	 */
	function mvr_get_dashboard_coupons_columns() {
		$args = array(
			'coupon-details'     => __( 'Coupon', 'multi-vendor-marketplace' ),
			'coupon-description' => __( 'Description', 'multi-vendor-marketplace' ),
			'coupon-product_ids' => __( 'Product IDs', 'multi-vendor-marketplace' ),
			'coupon-usage_limit' => __( 'Usage / Limit', 'multi-vendor-marketplace' ),
			'coupon-actions'     => __( 'Coupon Action', 'multi-vendor-marketplace' ),
		);

		/**
		 * Filters the array of Coupon columns.
		 *
		 * @since 1.0.0
		 * @param Array $args Array of column labels keyed by column IDs.
		 */
		return apply_filters( 'mvr_dashboard_coupons_columns', $args );
	}
}

if ( ! class_exists( 'mvr_get_dashboard_coupons_actions' ) ) {
	/**
	 * Get dashboard coupons actions.
	 *
	 * @since  1.0.0
	 * @param  int|WC_Coupon $coupon_obj Coupon instance or ID.
	 * @return array
	 */
	function mvr_get_dashboard_coupons_actions( $coupon_obj ) {
		if ( ! is_object( $coupon_obj ) ) {
			$coupon_id  = absint( $coupon_obj );
			$coupon_obj = new WC_Coupon( $coupon_id );
		}

		$actions         = array();
		$coupon_endpoint = ( 'publish' === $coupon_obj->get_status() ) ? 'mvr-edit-coupon-publish' : 'mvr-edit-coupon';

		if ( mvr_allow_endpoint( $coupon_endpoint ) ) {
			$actions['edit'] = array(
				'url'  => mvr_get_dashboard_endpoint_url( 'mvr-edit-coupon', $coupon_obj->get_id() ),
				'name' => __( 'Edit', 'multi-vendor-marketplace' ),
			);
		}

		if ( mvr_allow_endpoint( 'mvr-delete-coupon' ) ) {
			$actions['delete'] = array(
				'url'  => add_query_arg(
					array(
						'action'     => 'mvr_delete_coupon',
						'coupon_id'  => $coupon_obj->get_id(),
						'_mvr_nonce' => wp_create_nonce( 'mvr-delete-coupon-nonce' ),
					),
					mvr_get_dashboard_endpoint_url( 'mvr-coupons' )
				),
				'name' => __( 'Delete', 'multi-vendor-marketplace' ),
			);
		}

		/**
		 * Coupon actions.
		 *
		 * @since 1.0.0
		 * @param Array $actions Actions.
		 * @param WC_Coupon $coupon_obj Coupon object.
		 */
		return apply_filters( 'mvr_dashboard_coupons_actions', $actions, $coupon_obj );
	}
}

if ( ! class_exists( 'mvr_get_dashboard_enquiry_actions' ) ) {
	/**
	 * Enquiry actions.
	 *
	 * @since  1.0.0
	 * @param  Integer|MVR_Enquiry $enquiry_obj Enquiry instance or ID.
	 * @return Array
	 */
	function mvr_get_dashboard_enquiry_actions( $enquiry_obj ) {
		if ( ! is_object( $enquiry_obj ) ) {
			$enquiry_id  = absint( $enquiry_obj );
			$enquiry_obj = mvr_get_enquiry( $enquiry_id );
		}

		$actions = array(
			'reply' => array(
				'url'  => mvr_get_dashboard_endpoint_url( 'mvr-reply-enquiry', $enquiry_obj->get_id() ),
				'name' => __( 'Reply', 'multi-vendor-marketplace' ),
			),
		);

		/**
		 * Enquiry actions.
		 *
		 * @since 1.0.0
		 * @param Array $actions Actions.
		 * @param MVR_Enquiry $enquiry_obj Enquiry object.
		 */
		return apply_filters( 'mvr_dashboard_enquiry_actions', $actions, $enquiry_obj );
	}
}

if ( ! class_exists( 'mvr_get_dashboard_customers_columns' ) ) {
	/**
	 * Dashboard > Customers columns.
	 *
	 * @since 1.0.0
	 * @return Array
	 */
	function mvr_get_dashboard_customers_columns() {
		$args = array(
			'customer-email'         => get_option( 'mvr_dashboard_customer_email_column_label', 'Email' ),
			'customer-last_active'   => get_option( 'mvr_dashboard_customer_last_active_column_label', 'Last Active' ),
			'customer-date_register' => get_option( 'mvr_dashboard_customer_register_date_column_label', 'Register Date' ),
			'customer-orders'        => get_option( 'mvr_dashboard_customer_orders_column_label', 'Orders' ),
			'customer-total_spend'   => get_option( 'mvr_dashboard_customer_total_spend_column_label', 'Total Spend' ),
			'customer-address'       => get_option( 'mvr_dashboard_customer_address_column_label', 'Address' ),
		);

		/**
		 * Filters the array of Customers columns.
		 *
		 * @since 1.0.0
		 * @param Array $args Array of column labels keyed by column IDs.
		 */
		return apply_filters( 'mvr_dashboard_customers_columns', $args );
	}
}

if ( ! class_exists( 'mvr_get_dashboard_reviews_columns' ) ) {
	/**
	 * Dashboard > Reviews columns.
	 *
	 * @since 1.0.0
	 * @return Array
	 */
	function mvr_get_dashboard_reviews_columns() {
		$args = array(
			'review-customer' => get_option( 'mvr_dashboard_review_customer_column_label', 'Customer' ),
			'review-rating'   => get_option( 'mvr_dashboard_review_rating_column_label', 'Rating' ),
			'review-comment'  => get_option( 'mvr_dashboard_review_column_label', 'Review' ),
			'review-date'     => get_option( 'mvr_dashboard_review_date_label', 'Date' ),
		);

		/**
		 * Filters the array of Reviews columns.
		 *
		 * @since 1.0.0
		 * @param Array $args Array of column labels keyed by column IDs.
		 */
		return apply_filters( 'mvr_dashboard_reviews_columns', $args );
	}
}
