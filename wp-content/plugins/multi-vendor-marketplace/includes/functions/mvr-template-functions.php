<?php
/**
 * Template functions
 *
 * @package Multi Vendor/Template Functions.
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'mvr_dashboard_top_menu' ) ) {

	/**
	 * Dashboard Top Navigation
	 *
	 * @since 1.0.0
	 * */
	function mvr_dashboard_top_menu() {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		mvr_get_template(
			'dashboard/top-navigation.php',
			array(
				'vendor_obj'         => $vendor_obj,
				'notification_count' => $vendor_obj->get_unread_notification_count(),
				'enquiry_count'      => $vendor_obj->get_unread_enquiry_count(),
			)
		);
	}
}

if ( ! function_exists( 'mvr_dashboard_side_menu' ) ) {

	/**
	 * Dashboard Navigation
	 *
	 * @since 1.0.0
	 * */
	function mvr_dashboard_side_menu() {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		mvr_get_template( 'dashboard/navigation.php', array( 'vendor_obj' => $vendor_obj ) );
	}
}

if ( ! function_exists( 'mvr_dashboard_content' ) ) {

	/**
	 * My Account content output.
	 *
	 * @since 1.0.0
	 */
	function mvr_dashboard_content() {
		global $wp;

		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		if ( ! empty( $wp->query_vars ) ) {
			foreach ( $wp->query_vars as $key => $value ) {
				// Ignore page name param.
				if ( 'pagename' === $key ) {
					continue;
				}

				if ( ! mvr_allow_endpoint( $key ) ) {
					continue;
				}

				if ( has_action( 'mvr_dashboard_' . $key . '_endpoint' ) ) {
					/**
					 * Dashboard Endpoint
					 *
					 * @since 1.0.0
					 */
					do_action( 'mvr_dashboard_' . $key . '_endpoint', $value );
					return;
				}
			}
		}

		$user = get_user_by( 'id', get_current_user_id() );
		$args = array(
			'user_id'    => get_current_user_id(),
			'vendor_obj' => $vendor_obj,
			'overview'   => mvr_get_vendor_orders_details(
				$vendor_obj,
				array(
					'date_after' => gmdate( 'Y-m-01' ),
					'vendor_id'  => $vendor_obj->get_id(),
				)
			),
		);

		if ( $user instanceof WP_User ) {
			$args['current_user'] = $user;
		}

		// No endpoint found? Default to dashboard.
		mvr_get_template( 'dashboard/home.php', $args );
	}
}

if ( ! function_exists( 'mvr_dashboard_products' ) ) {
	/**
	 * Dashboard > Products template.
	 *
	 * @since 1.0.0
	 * @param Integer $current_page Current page number.
	 */
	function mvr_dashboard_products( $current_page ) {
		if ( ! mvr_check_user_as_vendor_or_staff() ) {
			return false;
		}

		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		$current_page = empty( $current_page ) ? 1 : absint( $current_page );
		$product_args = array(
			'mvr_include_vendor' => $vendor_obj->get_id(),
			'page'               => $current_page,
			'paginate'           => true,
			'author'             => $vendor_obj->get_user_id(),
			'limit'              => 10,
			'type'               => get_option( 'mvr_settings_allowed_product_type', array( 'simple', 'variable' ) ),
		);

		$status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';

		if ( ! empty( $status ) ) {
			$product_args['post_status'] = $status;
		}

		$term      = '';
		$nonce_val = isset( $_GET['_mvr_nonce'] ) ? sanitize_key( wp_unslash( $_GET['_mvr_nonce'] ) ) : '';

		if ( wp_verify_nonce( $nonce_val, 'mvr-search-products' ) ) {
			$term = isset( $_GET['mvr_search'] ) ? sanitize_text_field( wp_unslash( $_GET['mvr_search'] ) ) : '';

			if ( ! empty( $term ) ) {
				$product_args['s'] = $term;
			}
		}

		/**
		 * Vendor Products Query.
		 *
		 * @since 1.0.0
		 */
		$vendor_products = wc_get_products( apply_filters( 'mvr_vendor_products_query', $product_args ) );

		mvr_get_template(
			'dashboard/products.php',
			array(
				'term'            => $term,
				'current_page'    => absint( $current_page ),
				'vendor_products' => $vendor_products,
				'has_products'    => 0 < $vendor_products->total,
				'wp_button_class' => wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '',
			)
		);
	}
}

if ( ! function_exists( 'mvr_dashboard_add_product_button' ) ) {
	/**
	 * Dashboard > Add-Product Button.
	 *
	 * @since 1.0.0
	 */
	function mvr_dashboard_add_product_button() {
		if ( ! mvr_check_user_as_vendor_or_staff() ) {
			return;
		}

		if ( ! mvr_allow_endpoint( 'mvr-add-product' ) ) {
			return;
		}

		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		if ( ! $vendor_obj->has_status( 'active' ) ) {
			return;
		}

		$add_new_product_url = mvr_get_dashboard_endpoint_url( mvr()->query->query_vars['mvr-add-product'] );

		echo '<a href="' . esc_url( $add_new_product_url ) . '" class="mvr-page-title-action">' . esc_html__( 'Add New Product', 'multi-vendor-marketplace' ) . '</a>';
	}
}

if ( ! function_exists( 'mvr_dashboard_add_product' ) ) {
	/**
	 * Dashboard > Add-Product template.
	 *
	 * @since 1.0.0
	 */
	function mvr_dashboard_add_product() {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		$product_obj = new WC_Product();

		mvr_get_template(
			'dashboard/form-edit-product.php',
			array(
				'product_id'  => '',
				'product_obj' => $product_obj,
			)
		);
	}
}

if ( ! function_exists( 'mvr_dashboard_edit_product' ) ) {
	/**
	 * Dashboard > Edit-Product template.
	 *
	 * @since 1.0.0
	 * @param Integer $product_id Product ID.
	 */
	function mvr_dashboard_edit_product( $product_id ) {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		$product_obj = wc_get_product( $product_id );

		mvr_get_template(
			'dashboard/form-edit-product.php',
			array(
				'product_id'  => $product_id,
				'product_obj' => $product_obj,
			)
		);
	}
}

if ( ! function_exists( 'mvr_dashboard_orders' ) ) {
	/**
	 * Dashboard > Orders template.
	 *
	 * @since 1.0.0
	 * @param Integer $current_page Current page number.
	 */
	function mvr_dashboard_orders( $current_page ) {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		$current_page = empty( $current_page ) ? 1 : absint( $current_page );
		$order_args   = array(
			'vendor_id' => $vendor_obj->get_id(),
			'page'      => $current_page,
			'limit'     => 10,
		);

		$term      = '';
		$nonce_val = isset( $_GET['_mvr_nonce'] ) ? sanitize_key( wp_unslash( $_GET['_mvr_nonce'] ) ) : '';

		if ( wp_verify_nonce( $nonce_val, 'mvr-dashboard-orders' ) ) {
			$term = isset( $_GET['mvr_search'] ) ? sanitize_text_field( wp_unslash( $_GET['mvr_search'] ) ) : '';

			if ( ! empty( $term ) ) {
				$order_args['s'] = $term;
			}
		}

		$status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';

		if ( ! empty( $status ) ) {
			$order_args['status'] = $status;
		}

		/**
		 * Vendor Orders Query.
		 *
		 * @since 1.0.0
		 */
		$order_args    = apply_filters( 'mvr_vendor_orders_query', $order_args );
		$vendor_orders = $vendor_obj->get_orders( $order_args );

		mvr_get_template(
			'dashboard/orders.php',
			array(
				'term'            => $term,
				'vendor_id'       => $vendor_obj->get_id(),
				'current_page'    => absint( $current_page ),
				'vendor_orders'   => $vendor_orders,
				'wp_button_class' => wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '',
			)
		);
	}
}

if ( ! function_exists( 'mvr_dashboard_view_order' ) ) {

	/**
	 * Dashboard > View order template.
	 *
	 * @since 1.0.0
	 * @param Integer $order_id Order ID.
	 */
	function mvr_dashboard_view_order( $order_id ) {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		$order_obj = wc_get_order( $order_id );

		if ( ! $order_obj ) {
			wc_print_notice( esc_html__( 'Invalid order.', 'multi-vendor-woocommerce' ), 'error' );
			return;
		}

		mvr_get_template(
			'dashboard/view-order.php',
			array(
				'order_obj' => $order_obj,
				'order_id'  => $order_obj->get_id(),
			)
		);
	}
}

if ( ! function_exists( 'mvr_order_details_table' ) ) {

	/**
	 * Dashboard > Order Details template.
	 *
	 * @since 1.0.0
	 * @param Integer $order_id Order ID.
	 */
	function mvr_order_details_table( $order_id ) {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		$order_obj = wc_get_order( $order_id );

		if ( ! $order_obj ) {
			wc_print_notice( esc_html__( 'Invalid order.', 'multi-vendor-woocommerce' ), 'error' );
			return;
		}

		mvr_get_template(
			'dashboard/order/order-details.php',
			array(
				'order_id'   => $order_obj->get_id(),
				'order_obj'  => $order_obj,
				'vendor_id'  => $vendor_obj->get_id(),
				'vendor_obj' => $vendor_obj,
			)
		);
	}
}

if ( ! function_exists( 'mvr_dashboard_withdraw' ) ) {
	/**
	 * Dashboard > Withdraw template.
	 *
	 * @since 1.0.0
	 * @param Integer $current_page Current page number.
	 */
	function mvr_dashboard_withdraw( $current_page ) {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		$current_page  = empty( $current_page ) ? 1 : absint( $current_page );
		$withdraw_args = array(
			'vendor_id' => $vendor_obj->get_id(),
			'page'      => $current_page,
			'limit'     => 10,
			'paginate'  => true,
		);

		$term      = '';
		$nonce_val = isset( $_GET['_mvr_nonce'] ) ? sanitize_key( wp_unslash( $_GET['_mvr_nonce'] ) ) : '';

		if ( wp_verify_nonce( $nonce_val, 'mvr-dashboard-withdraw-nonce' ) ) {
			$term = isset( $_GET['mvr_search'] ) ? sanitize_text_field( wp_unslash( $_GET['mvr_search'] ) ) : '';

			if ( ! empty( $term ) ) {
				$withdraw_args['s'] = $term;
			}
		}

		$status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';

		if ( ! empty( $status ) ) {
			$withdraw_args['status'] = $status;
		}

		/**
		 * Vendor Withdraws Query.
		 *
		 * @since 1.0.0
		 */
		$vendor_withdraws = mvr_get_withdraws( apply_filters( 'mvr_vendor_withdraws_query', $withdraw_args ) );

		mvr_get_template(
			'dashboard/withdraw.php',
			array(
				'term'             => $term,
				'current_page'     => absint( $current_page ),
				'vendor_withdraws' => $vendor_withdraws,
				'has_withdraws'    => 0 < $vendor_withdraws->total_withdraws,
				'wp_button_class'  => wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '',
			)
		);
	}
}

if ( ! function_exists( 'mvr_dashboard_transaction' ) ) {
	/**
	 * Dashboard > Transaction template.
	 *
	 * @since 1.0.0
	 * @param Integer $current_page Current page number.
	 */
	function mvr_dashboard_transaction( $current_page ) {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		$current_page     = empty( $current_page ) ? 1 : absint( $current_page );
		$transaction_args = array(
			'vendor_id' => $vendor_obj->get_id(),
			'page'      => $current_page,
			'paginate'  => true,
			'limit'     => 10,
		);

		$term      = '';
		$nonce_val = isset( $_GET['_mvr_nonce'] ) ? sanitize_key( wp_unslash( $_GET['_mvr_nonce'] ) ) : '';

		if ( wp_verify_nonce( $nonce_val, 'mvr-dashboard-transaction-nonce' ) ) {
			$term = isset( $_GET['mvr_search'] ) ? sanitize_text_field( wp_unslash( $_GET['mvr_search'] ) ) : '';

			if ( ! empty( $term ) ) {
				$transaction_args['s'] = $term;
			}
		}

		$status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';

		if ( ! empty( $status ) && 'all' !== $status ) {
			$transaction_args['status'] = $status;
		}

		/**
		 * Vendor transactions Query.
		 *
		 * @since 1.0.0
		 */
		$vendor_transactions = mvr_get_transactions( apply_filters( 'mvr_vendor_transactions_query', $transaction_args ) );

		mvr_get_template(
			'dashboard/transaction.php',
			array(
				'term'                => $term,
				'current_page'        => absint( $current_page ),
				'vendor_transactions' => $vendor_transactions,
				'has_transactions'    => 0 < $vendor_transactions->total_transactions,
				'wp_button_class'     => wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '',
			)
		);
	}
}

if ( ! function_exists( 'mvr_dashboard_coupons' ) ) {
	/**
	 * Dashboard > Coupons template.
	 *
	 * @since 1.0.0
	 * @param Integer $current_page Current page number.
	 */
	function mvr_dashboard_coupons( $current_page ) {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		$current_page = empty( $current_page ) ? 1 : absint( $current_page );
		$coupon_args  = array(
			'vendor_id' => $vendor_obj->get_id(),
			'page'      => $current_page,
			'paginate'  => true,
			'limit'     => 10,
		);
		$term         = '';
		$nonce_val    = isset( $_GET['_mvr_nonce'] ) ? sanitize_key( wp_unslash( $_GET['_mvr_nonce'] ) ) : '';

		if ( wp_verify_nonce( $nonce_val, 'mvr-dashboard-coupons' ) ) {
			$term = isset( $_GET['mvr_search'] ) ? sanitize_text_field( wp_unslash( $_GET['mvr_search'] ) ) : '';

			if ( ! empty( $term ) ) {
				$coupon_args['s'] = $term;
			}
		}

		/**
		 * Vendor Coupons Query.
		 *
		 * @since 1.0.0
		 */
		$vendor_coupons = mvr_get_vendor_coupons( apply_filters( 'mvr_vendor_coupons_query', $coupon_args ) );

		mvr_get_template(
			'dashboard/coupons.php',
			array(
				'term'            => $term,
				'current_page'    => absint( $current_page ),
				'vendor_coupons'  => $vendor_coupons,
				'has_coupons'     => $vendor_coupons->has_coupon,
				'wp_button_class' => wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '',
			)
		);
	}
}

if ( ! function_exists( 'mvr_dashboard_add_coupon_button' ) ) {
	/**
	 * Dashboard > Add-Coupon Button.
	 *
	 * @since 1.0.0
	 */
	function mvr_dashboard_add_coupon_button() {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		if ( ! $vendor_obj->has_status( 'active' ) ) {
			return;
		}

		if ( ! mvr_allow_endpoint( 'mvr-add-coupon' ) ) {
			return;
		}

		$add_new_coupon_url = mvr_get_dashboard_endpoint_url( mvr()->query->query_vars['mvr-add-coupon'] );

		echo '<a href="' . esc_url( $add_new_coupon_url ) . '" class="mvr-page-title-action">' . esc_html__( 'Add New Coupon', 'multi-vendor-marketplace' ) . '</a>';
	}
}

if ( ! function_exists( 'mvr_dashboard_add_coupon' ) ) {
	/**
	 * Dashboard > Add Coupons template.
	 *
	 * @since 1.0.0
	 * @param Integer $current_page Current page number.
	 */
	function mvr_dashboard_add_coupon( $current_page ) {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		$coupon_id  = 0;
		$coupon_obj = new WC_Coupon();

		if ( ! $coupon_obj ) {
			wc_print_notice( esc_html__( 'Invalid Coupon.', 'multi-vendor-woocommerce' ), 'error' );
			return;
		}

		mvr_get_template(
			'dashboard/form-edit-coupon.php',
			array(
				'coupon_id'  => $coupon_id,
				'coupon_obj' => $coupon_obj,
			)
		);
	}
}

if ( ! function_exists( 'mvr_dashboard_edit_coupon' ) ) {
	/**
	 * Dashboard > Edit Coupons template.
	 *
	 * @since 1.0.0
	 * @param Integer $coupon_id Coupon ID.
	 */
	function mvr_dashboard_edit_coupon( $coupon_id ) {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		$coupon_obj = new WC_Coupon( $coupon_id );

		mvr_get_template(
			'dashboard/form-edit-coupon.php',
			array(
				'coupon_id'  => $coupon_id,
				'coupon_obj' => $coupon_obj,
			)
		);
	}
}

if ( ! function_exists( 'mvr_dashboard_customers' ) ) {
	/**
	 * Dashboard > Customers template.
	 *
	 * @since 1.0.0
	 * @param Integer $current_page Current page number.
	 */
	function mvr_dashboard_customers( $current_page ) {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		$current_page  = empty( $current_page ) ? 1 : absint( $current_page );
		$customer_args = array(
			'vendor_id' => $vendor_obj->get_id(),
			'page'      => $current_page,
			'paginate'  => true,
			'limit'     => 10,
		);

		$term      = '';
		$nonce_val = isset( $_GET['_mvr_nonce'] ) ? sanitize_key( wp_unslash( $_GET['_mvr_nonce'] ) ) : '';

		if ( wp_verify_nonce( $nonce_val, 'mvr-dashboard-customer-nonce' ) ) {
			$term = isset( $_GET['mvr_search'] ) ? sanitize_text_field( wp_unslash( $_GET['mvr_search'] ) ) : '';

			if ( ! empty( $term ) ) {
				$customer_args['s'] = $term;
			}
		}

		$customers_obj = $vendor_obj->get_customers( $customer_args );

		mvr_get_template(
			'dashboard/customers.php',
			array(
				'term'             => $term,
				'current_page'     => absint( $current_page ),
				'vendor_customers' => $customers_obj,
				'wp_button_class'  => wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '',
			)
		);
	}
}

if ( ! function_exists( 'mvr_dashboard_reviews' ) ) {
	/**
	 * Dashboard > Reviews template.
	 *
	 * @since 1.0.0
	 * @param Integer $current_page Current page number.
	 */
	function mvr_dashboard_reviews( $current_page ) {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		$current_page = empty( $current_page ) ? 1 : absint( $current_page );
		$reviews_args = array();

		$term      = '';
		$nonce_val = isset( $_GET['_mvr_nonce'] ) ? sanitize_key( wp_unslash( $_GET['_mvr_nonce'] ) ) : '';

		if ( wp_verify_nonce( $nonce_val, 'mvr-dashboard-coupons-nonce' ) ) {
			$term = isset( $_GET['mvr_search'] ) ? sanitize_text_field( wp_unslash( $_GET['mvr_search'] ) ) : '';

			if ( ! empty( $term ) ) {
				$reviews_args['s'] = $term;
			}
		}

		$reviews_obj = mvr_get_reviews( array( 'vendor_id' => $vendor_obj->get_id() ) );

		mvr_get_template(
			'dashboard/reviews.php',
			array(
				'term'            => $term,
				'current_page'    => absint( $current_page ),
				'reviews_obj'     => $reviews_obj,
				'wp_button_class' => wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '',
			)
		);
	}
}

if ( ! function_exists( 'mvr_get_vendor_order_items' ) ) {
	/**
	 * Get Vendor order items
	 *
	 * @since 1.0.0
	 * @param WC_Order $order_obj Order Object.
	 * @param Integer  $vendor_id Vendor ID.
	 */
	function mvr_get_vendor_order_items( $order_obj, $vendor_id ) {
		if ( ! is_object( $order_obj ) ) {
			return array();
		}

		$order_items = $order_obj->get_items();

		if ( ! mvr_check_is_array( $order_items ) ) {
			return $order_items;
		}

		foreach ( $order_items as $item_id => $item ) {
			$product = wc_get_product( $item->get_product_id() );

			if ( ! is_a( $product, 'WC_Product' ) ) {
				unset( $order_items[ $item_id ] );
				continue;
			}

			$product_vendor_id = $product->get_meta( '_mvr_vendor', true );

			if ( empty( $vendor_id ) || $vendor_id !== (int) $product_vendor_id ) {
				unset( $order_items[ $item_id ] );
				continue;
			}
		}

		return $order_items;
	}
}

if ( ! function_exists( 'mvr_dashboard_payments' ) ) {
	/**
	 * Dashboard > Payments template.
	 *
	 * @since 1.0.0
	 */
	function mvr_dashboard_payments() {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		mvr_get_template(
			'dashboard/form-edit-payment.php',
			array(
				'vendor_obj' => $vendor_obj,
			)
		);
	}
}

if ( ! function_exists( 'mvr_dashboard_payout' ) ) {
	/**
	 * Dashboard > Payout template.
	 *
	 * @since 1.0.0
	 */
	function mvr_dashboard_payout() {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		mvr_get_template(
			'dashboard/form-edit-payout.php',
			array(
				'vendor_obj' => $vendor_obj,
			)
		);
	}
}

if ( ! function_exists( 'mvr_dashboard_address' ) ) {
	/**
	 * Dashboard > Address template.
	 *
	 * @since 1.0.0
	 */
	function mvr_dashboard_address() {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		$form_fields = array(
			'_first_name' => $vendor_obj->get_first_name(),
			'_last_name'  => $vendor_obj->get_last_name(),
			'_address1'   => $vendor_obj->get_address1(),
			'_address2'   => $vendor_obj->get_address2(),
			'_city'       => $vendor_obj->get_city(),
			'_country'    => ! empty( $vendor_obj->get_country() ) ? $vendor_obj->get_country() : WC()->countries->get_base_country(),
			'_state'      => $vendor_obj->get_state(),
			'_zip_code'   => $vendor_obj->get_zip_code(),
			'_phone'      => $vendor_obj->get_phone(),
		);

		$nonce_value = isset( $_POST['_mvr_nonce'] ) ? sanitize_key( wp_unslash( $_POST['_mvr_nonce'] ) ) : '';

		if ( wp_verify_nonce( $nonce_value, 'save_mvr_vendor_address' ) ) {
			$posted = $_POST;

			$form_fields = array(
				'_first_name' => isset( $posted['_first_name'] ) ? wp_unslash( $posted['_first_name'] ) : '',
				'_last_name'  => isset( $posted['_last_name'] ) ? wp_unslash( $posted['_last_name'] ) : '',
				'_address1'   => isset( $posted['_address1'] ) ? wp_unslash( $posted['_address1'] ) : '',
				'_address2'   => isset( $posted['_address2'] ) ? wp_unslash( $posted['_address2'] ) : '',
				'_city'       => isset( $posted['_city'] ) ? wp_unslash( $posted['_city'] ) : '',
				'_country'    => isset( $posted['_country'] ) ? wp_unslash( $posted['_country'] ) : $form_fields['_country'],
				'_state'      => isset( $posted['_state'] ) ? wp_unslash( $posted['_state'] ) : '',
				'_zip_code'   => isset( $posted['_zip_code'] ) ? wp_unslash( $posted['_zip_code'] ) : '',
				'_phone'      => isset( $posted['_phone'] ) ? wp_unslash( $posted['_phone'] ) : '',
			);
		}

		/**
		 * Address Form Field Value.
		 *
		 * @since 1.0.0
		 * */
		$form_fields = apply_filters( 'mvr_vendor_address_form_fields_value', $form_fields );

		mvr_get_template(
			'dashboard/form-edit-address.php',
			array(
				'vendor_obj'  => $vendor_obj,
				'form_fields' => $form_fields,
			)
		);
	}
}

if ( ! function_exists( 'mvr_dashboard_social_links' ) ) {
	/**
	 * Dashboard > Social Links template.
	 *
	 * @since 1.0.0
	 */
	function mvr_dashboard_social_links() {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		mvr_get_template(
			'dashboard/form-edit-social-links.php',
			array(
				'vendor_obj' => $vendor_obj,
			)
		);
	}
}

if ( ! function_exists( 'mvr_dashboard_staff' ) ) {
	/**
	 * Dashboard > Staff template.
	 *
	 * @since 1.0.0
	 * @param Integer $current_page Current page number.
	 */
	function mvr_dashboard_staff( $current_page ) {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		$current_page = empty( $current_page ) ? 1 : absint( $current_page );
		$staff_args   = array(
			'vendor_id' => $vendor_obj->get_id(),
			'limit'     => 10,
			'page'      => $current_page,
			'fields'    => 'objects',
		);

		$status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';

		if ( ! empty( $status ) ) {
			$staff_args['status'] = $status;
		}

		$term      = '';
		$nonce_val = isset( $_GET['_mvr_nonce'] ) ? sanitize_key( wp_unslash( $_GET['_mvr_nonce'] ) ) : '';

		if ( wp_verify_nonce( $nonce_val, 'mvr-search-staff' ) ) {
			$term = isset( $_GET['mvr_search'] ) ? sanitize_text_field( wp_unslash( $_GET['mvr_search'] ) ) : '';

			if ( ! empty( $term ) ) {
				$staff_args['s'] = $term;
			}
		}

		/**
		 * Vendor Staff Query.
		 *
		 * @since 1.0.0
		 */
		$vendor_staffs = mvr_get_staffs( apply_filters( 'mvr_vendor_staffs_query', $staff_args ) );

		mvr_get_template(
			'dashboard/staff.php',
			array(
				'term'            => $term,
				'current_page'    => absint( $current_page ),
				'vendor_staffs'   => $vendor_staffs,
				'has_staff'       => $vendor_staffs->has_staff,
				'wp_button_class' => wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '',
			)
		);
	}
}

if ( ! function_exists( 'mvr_dashboard_add_staff_button' ) ) {
	/**
	 * Dashboard > Add-staff Button.
	 *
	 * @since 1.0.0
	 */
	function mvr_dashboard_add_staff_button() {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		if ( ! $vendor_obj->has_status( 'active' ) ) {
			return;
		}

		$add_new_product_url = mvr_get_dashboard_endpoint_url( mvr()->query->query_vars['mvr-add-staff'] );

		echo '<a href="' . esc_url( $add_new_product_url ) . '" class="mvr-page-title-action">' . esc_attr( get_option( 'mvr_dashboard_add_staff_btn_label', 'Add New Staff' ) ) . '</a>';
	}
}

if ( ! function_exists( 'mvr_dashboard_add_staff' ) ) {
	/**
	 * Dashboard > Add-staff template.
	 *
	 * @since 1.0.0
	 */
	function mvr_dashboard_add_staff() {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		$staff_obj = new MVR_Staff();

		mvr_get_template(
			'dashboard/form-edit-staff.php',
			array(
				'staff_id'   => '',
				'staff_obj'  => $staff_obj,
				'vendor_obj' => $vendor_obj,
			)
		);
	}
}

if ( ! function_exists( 'mvr_dashboard_edit_staff' ) ) {
	/**
	 * Dashboard > Edit-staff template.
	 *
	 * @since 1.0.0
	 * @param Integer $staff_id Staff ID.
	 */
	function mvr_dashboard_edit_staff( $staff_id ) {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		$staff_obj = mvr_get_staff( $staff_id );

		mvr_get_template(
			'dashboard/form-edit-staff.php',
			array(
				'staff_id'   => $staff_id,
				'staff_obj'  => $staff_obj,
				'vendor_obj' => $vendor_obj,
			)
		);
	}
}

if ( ! function_exists( 'mvr_dashboard_notification' ) ) {
	/**
	 * Dashboard > Notification template.
	 *
	 * @since 1.0.0
	 * @param Integer $current_page Current page number.
	 */
	function mvr_dashboard_notification( $current_page ) {
		if ( ! mvr_check_user_as_vendor_or_staff() ) {
			return;
		}

		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		$current_page      = empty( $current_page ) ? 1 : absint( $current_page );
		$notification_args = array(
			'vendor_id' => $vendor_obj->get_id(),
			'to'        => 'vendor',
			'page'      => $current_page,
		);

		if ( mvr_check_user_as_staff() ) {
			if ( ! mvr_allow_endpoint( 'mvr-orders' ) ) {
				$notification_args['source_from'][] = 'new_order';
			}

			if ( ! mvr_allow_endpoint( 'mvr-edit-product' ) ) {
				$notification_args['source_from'][] = 'new_product';
			}

			if ( ! mvr_allow_endpoint( 'mvr-edit-coupon' ) ) {
				$notification_args['source_from'][] = 'new_coupon';
			}
		}

		$status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';

		if ( ! empty( $status ) ) {
			$notification_args['status'] = $status;
		}

		$term      = '';
		$nonce_val = isset( $_GET['_mvr_nonce'] ) ? sanitize_key( wp_unslash( $_GET['_mvr_nonce'] ) ) : '';

		if ( wp_verify_nonce( $nonce_val, 'mvr-search-notification' ) ) {
			$term = isset( $_GET['mvr_search'] ) ? sanitize_text_field( wp_unslash( $_GET['mvr_search'] ) ) : '';

			if ( ! empty( $term ) ) {
				$notification_args['s'] = $term;
			}
		}

		/**
		 * Vendor Notifications Query.
		 *
		 * @since 1.0.0
		 */
		$vendor_notifications = mvr_get_notifications( apply_filters( 'mvr_vendor_notification_query', $notification_args ) );

		mvr_get_template(
			'dashboard/notification.php',
			array(
				'term'                 => $term,
				'current_page'         => absint( $current_page ),
				'vendor_notifications' => $vendor_notifications,
				'has_notifications'    => 0 < $vendor_notifications->total_notifications,
				'wp_button_class'      => wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '',
			)
		);
	}
}

if ( ! function_exists( 'mvr_dashboard_enquiry' ) ) {
	/**
	 * Dashboard > Enquiry template.
	 *
	 * @since 1.0.0
	 * @param Integer $current_page Current page number.
	 */
	function mvr_dashboard_enquiry( $current_page ) {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		$current_page = empty( $current_page ) ? 1 : absint( $current_page );
		$enquiry_args = array(
			'vendor_id' => $vendor_obj->get_id(),
			'page'      => $current_page,
		);

		$status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';

		if ( ! empty( $status ) ) {
			$enquiry_args['status'] = $status;
		}

		$term      = '';
		$nonce_val = isset( $_GET['_mvr_nonce'] ) ? sanitize_key( wp_unslash( $_GET['_mvr_nonce'] ) ) : '';

		if ( wp_verify_nonce( $nonce_val, 'mvr-search-enquiry' ) ) {
			$term = isset( $_GET['mvr_search'] ) ? sanitize_text_field( wp_unslash( $_GET['mvr_search'] ) ) : '';

			if ( ! empty( $term ) ) {
				$enquiry_args['s'] = $term;
			}
		}

		/**
		 * Vendor Enquiries Query.
		 *
		 * @since 1.0.0
		 */
		$vendor_enquiries = mvr_get_enquiries( apply_filters( 'mvr_vendor_enquiry_query', $enquiry_args ) );

		mvr_get_template(
			'dashboard/enquiry.php',
			array(
				'term'             => $term,
				'current_page'     => absint( $current_page ),
				'vendor_enquiries' => $vendor_enquiries,
				'has_enquiry'      => 0 < $vendor_enquiries->total_enquiries,
				'wp_button_class'  => wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '',
			)
		);
	}
}

if ( ! function_exists( 'mvr_dashboard_reply_enquiry' ) ) {

	/**
	 * Dashboard > Reply Enquiry template.
	 *
	 * @since 1.0.0
	 * @param Integer $enquiry_id Order ID.
	 */
	function mvr_dashboard_reply_enquiry( $enquiry_id ) {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		$enquiry_obj = mvr_get_enquiry( $enquiry_id );

		if ( ! $enquiry_obj ) {
			wc_print_notice( esc_html__( 'Invalid Enquiry', 'multi-vendor-woocommerce' ), 'error' );
			return;
		}

		mvr_get_template(
			'dashboard/reply-enquiry.php',
			array(
				'enquiry_obj' => $enquiry_obj,
				'enquiry_id'  => $enquiry_id,
			)
		);
	}
}

if ( ! function_exists( 'mvr_dashboard_profile' ) ) {
	/**
	 * Dashboard > Profile template.
	 *
	 * @since 1.0.0
	 */
	function mvr_dashboard_profile() {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		$form_fields = array(
			'_logo_id'     => $vendor_obj->get_logo_id(),
			'_banner_id'   => $vendor_obj->get_banner_id(),
			'_name'        => $vendor_obj->get_name(),
			'_shop_name'   => $vendor_obj->get_shop_name(),
			'_slug'        => $vendor_obj->get_slug(),
			'_email'       => $vendor_obj->get_email(),
			'_description' => $vendor_obj->get_description(),
			'_tac'         => $vendor_obj->get_tac(),
		);

		$nonce_value = isset( $_POST['_mvr_nonce'] ) ? sanitize_key( wp_unslash( $_POST['_mvr_nonce'] ) ) : '';

		if ( wp_verify_nonce( $nonce_value, 'save_mvr_profile_details' ) ) {
			$posted                      = $_POST;
			$form_fields['_logo_id']     = isset( $posted['_logo_id'] ) ? wp_unslash( $posted['_logo_id'] ) : $form_fields['_logo_id'];
			$form_fields['_banner_id']   = isset( $posted['_banner_id'] ) ? wp_unslash( $posted['_banner_id'] ) : $form_fields['_banner_id'];
			$form_fields['_name']        = isset( $posted['_name'] ) ? sanitize_text_field( wp_unslash( $posted['_name'] ) ) : $form_fields['_name'];
			$form_fields['_shop_name']   = isset( $posted['_shop_name'] ) ? sanitize_text_field( wp_unslash( $posted['_shop_name'] ) ) : $form_fields['_shop_name'];
			$form_fields['_slug']        = isset( $posted['_slug'] ) ? sanitize_title( wp_unslash( $posted['_slug'] ) ) : $form_fields['_slug'];
			$form_fields['_email']       = isset( $posted['_email'] ) ? sanitize_text_field( wp_unslash( $posted['_email'] ) ) : $form_fields['_email'];
			$form_fields['_description'] = isset( $posted['_description'] ) ? wp_unslash( $posted['_description'] ) : $form_fields['_description'];
			$form_fields['_tac']         = isset( $posted['_tac'] ) ? wp_unslash( $posted['_tac'] ) : $form_fields['_tac'];
		}

		/**
		 * Profile Form Field Value.
		 *
		 * @since 1.0.0
		 * */
		$form_fields = apply_filters( 'mvr_vendor_profile_form_fields_value', $form_fields );
		$logo        = ( $vendor_obj->get_logo_id() && $vendor_obj->get_logo_id() > 0 ) ? wp_get_attachment_url( $vendor_obj->get_logo_id() ) : MVR_PLUGIN_URL . '/assets/images/placeholder-64x64.png';
		$banner      = $vendor_obj->get_banner_id() ? wp_get_attachment_url( $vendor_obj->get_banner_id() ) : MVR_PLUGIN_URL . '/assets/images/placeholder-800x400.png';

		mvr_get_template(
			'dashboard/form-edit-profile.php',
			array(
				'vendor_obj'  => $vendor_obj,
				'store_url'   => $vendor_obj->get_shop_url(),
				'form_fields' => $form_fields,
				'logo'        => $logo,
				'banner'      => $banner,
			)
		);
	}
}

if ( ! function_exists( 'mvr_dashboard_table_views_display' ) ) {
	/**
	 * Display the list of views available on table.
	 *
	 * @since 1.0.0
	 * @param String $url URL.
	 * @param Array  $args Arguments.
	 * @return Array
	 * */
	function mvr_dashboard_table_views_display( $url, $args ) {
		$label = $args['status_label'] . ' <span class="count">(' . $args['status_count'] . ')</span>';
		$class = array( strtolower( $args['status_name'] ) );
		$_get  = $_GET;

		if ( isset( $_get['status'] ) && ( sanitize_text_field( $_get['status'] ) === $args['status_name'] ) ) {
			$class[] = 'current';
		}

		if ( ! isset( $_get['status'] ) && 'all' === $args['status_name'] ) {
			$class[] = 'current';
		}

		$url        = ( 'all' !== $args['status_name'] ) ? add_query_arg( array( 'status' => $args['status_name'] ), mvr_get_current_url() ) : remove_query_arg( array( 'status' ), mvr_get_current_url() );
		$class_html = '';

		if ( ! empty( $class ) ) {
			/* translators: %s: Class */
			$class_html = sprintf( 'class="%s"', esc_attr( implode( ' ', $class ) ) );
		}

		/* translators: %1$s: URL  %2$s: Class %3$s: Link Name */
		return sprintf( '<a href="%1$s" %2$s>%3$s</a>', esc_url( $url ), $class_html, $label );
	}
}

if ( ! function_exists( 'mvr_dashboard_notification_table_views' ) ) {
	/**
	 * Display the list of views available on notification table.
	 *
	 * @since 1.0.0
	 * @return Array
	 * */
	function mvr_dashboard_notification_table_views() {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		$views    = array();
		$statuses = array( 'all' => esc_html__( 'All', 'multi-vendor-marketplace' ) ) + mvr_get_notification_statuses();

		foreach ( $statuses as $status_name => $status_label ) {
			$table_args = array(
				'limit'     => -1,
				'fields'    => 'objects',
				'vendor_id' => $vendor_obj->get_id(),
				'to'        => 'vendor',
			);

			if ( 'all' !== $status_name ) {
				$table_args['status'] = $status_name;
			}

			/**
			 * Vendor notifications Status count Arguments.
			 *
			 * @since 1.0.0
			 */
			$result       = mvr_get_notifications( apply_filters( 'mvr_vendor_notifications_status_count_query', $table_args ) );
			$status_count = is_object( $result ) ? $result->total_notifications : 0;

			if ( ! $status_count ) {
				continue;
			}

			$url                   = wc_get_endpoint_url( 'mvr-notification', '', mvr_get_page_permalink( 'dashboard' ) );
			$views[ $status_name ] = mvr_dashboard_table_views_display(
				$url,
				array(
					'status_name'  => $status_name,
					'status_label' => $status_label,
					'status_count' => $status_count,
				)
			);
		}

		return $views;
	}
}

if ( ! function_exists( 'mvr_dashboard_enquiry_table_views' ) ) {
	/**
	 * Display the list of views available on enquiry table.
	 *
	 * @since 1.0.0
	 * @return Array
	 * */
	function mvr_dashboard_enquiry_table_views() {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		$views    = array();
		$statuses = array( 'all' => esc_html__( 'All', 'multi-vendor-marketplace' ) ) + mvr_get_enquiry_statuses();

		foreach ( $statuses as $status_name => $status_label ) {
			$table_args = array(
				'limit'     => -1,
				'fields'    => 'objects',
				'vendor_id' => $vendor_obj->get_id(),
			);

			if ( 'all' !== $status_name ) {
				$table_args['status'] = $status_name;
			}

			/**
			 * Vendor enquiry Status count Arguments.
			 *
			 * @since 1.0.0
			 */
			$result       = mvr_get_enquiries( apply_filters( 'mvr_vendor_enquiries_status_count_query', $table_args ) );
			$status_count = is_object( $result ) ? $result->total_enquiries : 0;

			if ( ! $status_count ) {
				continue;
			}

			$url                   = wc_get_endpoint_url( 'mvr-enquiry', '', mvr_get_page_permalink( 'dashboard' ) );
			$views[ $status_name ] = mvr_dashboard_table_views_display(
				$url,
				array(
					'status_name'  => $status_name,
					'status_label' => $status_label,
					'status_count' => $status_count,
				)
			);
		}

		return $views;
	}
}

if ( ! function_exists( 'mvr_dashboard_product_table_views' ) ) {
	/**
	 * Display the list of views available on product table.
	 *
	 * @since 1.0.0
	 * @return Array
	 * */
	function mvr_dashboard_product_table_views() {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		$views    = array();
		$statuses = array( 'all' => esc_html__( 'All', 'multi-vendor-marketplace' ) ) + mvr_get_product_statuses();

		foreach ( $statuses as $status_name => $status_label ) {
			$table_args = array(
				'status'             => array_keys( mvr_get_product_statuses() ),
				'mvr_include_vendor' => $vendor_obj->get_id(),
				'paginate'           => true,
				'author'             => $vendor_obj->get_user_id(),
				'type'               => get_option( 'mvr_settings_allowed_product_type', array( 'simple', 'variable' ) ),
				'return'             => 'objects',
			);

			if ( 'all' !== $status_name ) {
				$table_args['status'] = $status_name;
			}

			$term      = '';
			$nonce_val = isset( $_GET['_mvr_nonce'] ) ? sanitize_key( wp_unslash( $_GET['_mvr_nonce'] ) ) : '';

			if ( wp_verify_nonce( $nonce_val, 'mvr-dashboard-products-nonce' ) ) {
				$term = isset( $_GET['mvr_search'] ) ? sanitize_text_field( wp_unslash( $_GET['mvr_search'] ) ) : '';

				if ( ! empty( $term ) ) {
					$table_args['s'] = $term;
				}
			}

			/**
			 * Vendor Product Status count Arguments.
			 *
			 * @since 1.0.0
			 */
			$result       = wc_get_products( apply_filters( 'mvr_vendor_products_status_count_query', $table_args ) );
			$status_count = is_object( $result ) ? $result->total : 0;

			if ( ! $status_count ) {
				continue;
			}

			$url                   = wc_get_endpoint_url( 'mvr-products', '', mvr_get_page_permalink( 'dashboard' ) );
			$views[ $status_name ] = mvr_dashboard_table_views_display(
				$url,
				array(
					'status_name'  => $status_name,
					'status_label' => $status_label,
					'status_count' => $status_count,
				)
			);
		}

		return $views;
	}
}

if ( ! function_exists( 'mvr_dashboard_staff_table_views' ) ) {
	/**
	 * Display the list of views available on Staff table.
	 *
	 * @since 1.0.0
	 * @return Array
	 * */
	function mvr_dashboard_staff_table_views() {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		$views    = array();
		$statuses = array( 'all' => esc_html__( 'All', 'multi-vendor-marketplace' ) ) + mvr_get_staff_statuses();

		foreach ( $statuses as $status_name => $status_label ) {
			$table_args = array(
				'limit'     => -1,
				'fields'    => 'objects',
				'vendor_id' => $vendor_obj->get_id(),
			);

			if ( 'all' !== $status_name ) {
				$table_args['status'] = $status_name;
			}

			/**
			 * Vendor Staff Status count Arguments.
			 *
			 * @since 1.0.0
			 */
			$result       = mvr_get_staffs( apply_filters( 'mvr_vendor_staffs_status_count_query', $table_args ) );
			$status_count = is_object( $result ) ? $result->total_staffs : 0;

			if ( ! $status_count ) {
				continue;
			}

			$url                   = wc_get_endpoint_url( 'mvr-staff', '', mvr_get_page_permalink( 'dashboard' ) );
			$views[ $status_name ] = mvr_dashboard_table_views_display(
				$url,
				array(
					'status_name'  => $status_name,
					'status_label' => $status_label,
					'status_count' => $status_count,
				)
			);
		}

		return $views;
	}
}

if ( ! function_exists( 'mvr_dashboard_coupon_table_views' ) ) {
	/**
	 * Display the list of views available on coupon table.
	 *
	 * @since 1.0.0
	 * @return Array
	 * */
	function mvr_dashboard_coupon_table_views() {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		$views    = array();
		$statuses = array( 'all' => esc_html__( 'All', 'multi-vendor-marketplace' ) ) + mvr_get_coupon_statuses();

		foreach ( $statuses as $status_name => $status_label ) {
			$table_args = array(
				'limit'     => -1,
				'return'    => 'objects',
				'paginate'  => true,
				'vendor_id' => $vendor_obj->get_id(),
			);

			if ( 'all' !== $status_name ) {
				$table_args['status'] = $status_name;
			}

			/**
			 * Vendor Coupon Status count Arguments.
			 *
			 * @since 1.0.0
			 */
			$result       = mvr_get_vendor_coupons( apply_filters( 'mvr_vendor_coupons_status_count_query', $table_args ) );
			$status_count = is_object( $result ) ? $result->total : 0;

			if ( ! $status_count ) {
				continue;
			}

			$url                   = wc_get_endpoint_url( 'mvr-coupons', '', mvr_get_page_permalink( 'dashboard' ) );
			$views[ $status_name ] = mvr_dashboard_table_views_display(
				$url,
				array(
					'status_name'  => $status_name,
					'status_label' => $status_label,
					'status_count' => $status_count,
				)
			);
		}

		return $views;
	}
}

if ( ! function_exists( 'mvr_dashboard_withdraw_table_views' ) ) {
	/**
	 * Display the list of views available on withdraw table.
	 *
	 * @since 1.0.0
	 * @return Array
	 * */
	function mvr_dashboard_withdraw_table_views() {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		$views    = array();
		$statuses = array( 'all' => esc_html__( 'All', 'multi-vendor-marketplace' ) ) + mvr_get_withdraw_statuses();

		foreach ( $statuses as $status_name => $status_label ) {
			$table_args = array(
				'limit'     => -1,
				'return'    => 'objects',
				'paginate'  => true,
				'vendor_id' => $vendor_obj->get_id(),
			);

			if ( 'all' !== $status_name ) {
				$table_args['status'] = $status_name;
			}

			/**
			 * Vendor Coupon Status count Arguments.
			 *
			 * @since 1.0.0
			 */
			$result       = mvr_get_withdraws( apply_filters( 'mvr_vendor_withdraws_status_count_query', $table_args ) );
			$status_count = is_object( $result ) ? $result->total_withdraws : 0;

			if ( ! $status_count ) {
				continue;
			}

			$url                   = wc_get_endpoint_url( 'mvr-withdraw', '', mvr_get_page_permalink( 'dashboard' ) );
			$views[ $status_name ] = mvr_dashboard_table_views_display(
				$url,
				array(
					'status_name'  => $status_name,
					'status_label' => $status_label,
					'status_count' => $status_count,
				)
			);
		}

		return $views;
	}
}

if ( ! function_exists( 'mvr_dashboard_order_table_views' ) ) {
	/**
	 * Display the list of views available on order table.
	 *
	 * @since 1.0.0
	 * @return Array
	 * */
	function mvr_dashboard_order_table_views() {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		$views    = array();
		$statuses = array( 'all' => esc_html__( 'All', 'multi-vendor-marketplace' ) ) + wc_get_order_statuses();

		foreach ( $statuses as $status_name => $status_label ) {
			$table_args = array(
				'limit'     => -1,
				'return'    => 'objects',
				'paginate'  => true,
				'vendor_id' => $vendor_obj->get_id(),
			);

			if ( 'all' !== $status_name ) {
				$table_args['status'] = $status_name;
			}

			/**
			 * Vendor order Status count Arguments.
			 *
			 * @since 1.0.0
			 */
			$result       = $vendor_obj->get_orders( apply_filters( 'mvr_vendor_orders_status_count_query', $table_args ) );
			$status_count = $result->has_order ? $result->total_orders : 0;

			if ( ! $status_count ) {
				continue;
			}

			$url                   = wc_get_endpoint_url( 'mvr-orders', '', mvr_get_page_permalink( 'dashboard' ) );
			$views[ $status_name ] = mvr_dashboard_table_views_display(
				$url,
				array(
					'status_name'  => $status_name,
					'status_label' => $status_label,
					'status_count' => $status_count,
				)
			);
		}

		return $views;
	}
}

if ( ! function_exists( 'mvr_dashboard_transactions_table_views' ) ) {
	/**
	 * Display the list of views available on transaction table.
	 *
	 * @since 1.0.0
	 * @return Array
	 * */
	function mvr_dashboard_transactions_table_views() {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		$views    = array();
		$statuses = array( 'all' => esc_html__( 'All', 'multi-vendor-marketplace' ) ) + mvr_get_transaction_statuses();

		foreach ( $statuses as $status_name => $status_label ) {
			$table_args = array(
				'limit'     => -1,
				'return'    => 'objects',
				'paginate'  => true,
				'vendor_id' => $vendor_obj->get_id(),
			);

			if ( 'all' !== $status_name ) {
				$table_args['status'] = $status_name;
			}

			/**
			 * Vendor order Status count Arguments.
			 *
			 * @since 1.0.0
			 */
			$result       = mvr_get_transactions( apply_filters( 'mvr_vendor_transactions_status_count_query', $table_args ) );
			$status_count = is_object( $result ) ? $result->total_transactions : 0;

			if ( ! $status_count ) {
				continue;
			}

			$url                   = wc_get_endpoint_url( 'mvr-transaction', '', mvr_get_page_permalink( 'dashboard' ) );
			$views[ $status_name ] = mvr_dashboard_table_views_display(
				$url,
				array(
					'status_name'  => $status_name,
					'status_label' => $status_label,
					'status_count' => $status_count,
				)
			);
		}

		return $views;
	}
}

if ( ! function_exists( 'mvr_stores_content' ) ) {

	/**
	 * Stores output.
	 *
	 * @since 1.0.0
	 * @param Array $vendors_objs Vendors Objects.
	 */
	function mvr_stores_content( $vendors_objs ) {
		global $wp;

		if ( ! empty( $wp->query_vars ) ) {
			foreach ( $wp->query_vars as $key => $value ) {
				// Ignore pagename param.
				if ( 'pagename' === $key ) {
					continue;
				}

				if ( has_action( 'mvr_stores_' . $key . '_endpoint' ) ) {
					/**
					 * Stores Endpoint
					 *
					 * @since 1.0.0
					 */
					do_action( 'mvr_stores_' . $key . '_endpoint', $value );
					return;
				}
			}
		}

		// No endpoint found? Default to stores loop.
		mvr_get_template( 'stores/stores-loop.php', array( 'vendors_objs' => $vendors_objs ) );
	}
}

if ( ! function_exists( 'mvr_single_store_content' ) ) {

	/**
	 * Single Store output.
	 *
	 * @since 1.0.0
	 * @param Array $params Shop Parameters.
	 */
	function mvr_single_store_content( $params ) {
		$slug       = str_contains( $params, '/' ) ? strstr( $params, '/', true ) : $params;
		$args       = array(
			'post_name__in'  => array( $slug ),
			'post_status'    => 'mvr-active',
			'post_type'      => 'mvr_vendor',
			'fields'         => 'ids',
			'posts_per_page' => 1,
		);
		$vendor_ids = get_posts( $args );
		$vendor_id  = reset( $vendor_ids );
		$vendor_obj = mvr_get_vendor( $vendor_id );

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		mvr_get_template( 'single-store.php', array( 'vendor_obj' => $vendor_obj ) );
	}
}

if ( ! function_exists( 'mvr_single_store_contents' ) ) {

	/**
	 * Single Store output.
	 *
	 * @since 1.0.0
	 * @param MVR_Vendor $vendor_obj Vendor Object.
	 */
	function mvr_single_store_contents( $vendor_obj ) {
		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		$user_id = get_current_user_id();
		$tabs    = array(
			'overview' => array(
				'label' => get_option( 'mvr_dashboard_store_overview_tab_label', 'Overview' ),
				'url'   => $vendor_obj->get_shop_url(),
			),
		);

		if ( 'yes' === get_option( 'mvr_settings_disp_vendor_product_list', 'yes' ) ) {
			$tabs['products'] = array(
				'label' => get_option( 'mvr_dashboard_store_products_tab_label', 'Products' ),
				'url'   => add_query_arg( 'tab', 'products', $vendor_obj->get_shop_url() ),
			);
		}

		if ( ( 'yes' === get_option( 'mvr_settings_disp_vendor_enquiry_form', 'yes' ) ) && ( $vendor_obj->get_user_id() !== $user_id ) ) {
			$tabs['enquiry'] = array(
				'label' => get_option( 'mvr_dashboard_store_enquiry_tab_label', 'Enquiry' ),
				'url'   => add_query_arg( 'tab', 'enquiry', $vendor_obj->get_shop_url() ),
			);
		}

		if ( 'yes' === get_option( 'mvr_settings_disp_vendor_review', 'yes' ) ) {
			$tabs['reviews'] = array(
				'label' => get_option( 'mvr_dashboard_store_review_tab_label', 'Reviews' ),
				'url'   => add_query_arg( 'tab', 'reviews', $vendor_obj->get_shop_url() ),
			);
		}

		if ( 'yes' === get_option( 'mvr_settings_disp_vendor_policy', 'yes' ) ) {
			$tabs['terms'] = array(
				'label' => get_option( 'mvr_dashboard_store_toc_tab_label', 'Terms & Conditions' ),
				'url'   => add_query_arg( 'tab', 'terms', $vendor_obj->get_shop_url() ),
			);
		}

		$tab = isset( $_GET['tab'] ) ? (string) sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';

		mvr_get_template(
			'single-store/store-content.php',
			array(
				'vendor_obj' => $vendor_obj,
				'tabs'       => $tabs,
				'tab'        => $tab,
			)
		);
	}
}

if ( ! function_exists( 'mvr_single_store_tab_contents' ) ) {

	/**
	 * Single Store output.
	 *
	 * @since 1.0.0
	 * @param String     $tab Tab.
	 * @param MVR_Vendor $vendor_obj Vendor Object.
	 */
	function mvr_single_store_tab_contents( $tab, $vendor_obj ) {
		$user_id = get_current_user_id();

		switch ( $tab ) {
			case 'products':
				if ( 'yes' !== get_option( 'mvr_settings_disp_vendor_product_list', 'yes' ) ) {
					return;
				}

				$nonce_val = isset( $_REQUEST['_mvr_nonce'] ) ? sanitize_key( wp_unslash( $_REQUEST['_mvr_nonce'] ) ) : '';
				$paged     = isset( $_REQUEST['_p'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_p'] ) ) : 1;
				$term      = '';

				if ( wp_verify_nonce( $nonce_val, 'mvr-search-vendor-products' ) ) {
					$term = isset( $_REQUEST['_s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_s'] ) ) : '';
				}

				mvr_get_template(
					'single-store/tab/products.php',
					array(
						'vendor_obj' => $vendor_obj,
						'paged'      => $paged,
						'term'       => $term,
					)
				);
				break;
			case 'reviews':
				if ( 'yes' !== get_option( 'mvr_settings_disp_vendor_review', 'yes' ) ) {
					return;
				}

				$current_page = isset( $_REQUEST['_p'] ) ? (int) sanitize_text_field( wp_unslash( $_REQUEST['_p'] ) ) : 1;
				$reviews_obj  = mvr_get_reviews(
					array(
						'post_id'   => $vendor_obj->get_id(),
						'vendor_id' => $vendor_obj->get_id(),
						'status'    => mvr_convert_review_status_to_query_val( 'approved' ),
						'offset'    => $current_page,
						// 'number'    => 5,
					)
				);

				mvr_get_template(
					'single-store/tab/reviews.php',
					array(
						'vendor_obj'         => $vendor_obj,
						'user_id'            => $user_id,
						'reviews_obj'        => $reviews_obj,
						'current_page'       => $current_page,
						'reviewed'           => $vendor_obj->has_customer_review( $user_id ),
						'is_vendor_customer' => $vendor_obj->has_customer( $user_id ),
					)
				);

				break;
			case 'enquiry':
				if ( 'yes' !== get_option( 'mvr_settings_disp_vendor_enquiry_form', 'yes' ) ) {
					return;
				}

				if ( (int) $vendor_obj->get_user_id() === $user_id ) {
					return;
				}

				$user_email = '';
				$user_name  = '';
				$user       = get_user_by( 'id', $user_id );

				if ( $user instanceof WP_User ) {
					$user_email = $user->user_email;
					$user_name  = $user->display_name;
				}

				mvr_get_template(
					'single-store/tab/enquiry.php',
					array(
						'source_id'  => 0,
						'user_id'    => $user_id,
						'vendor_obj' => $vendor_obj,
						'user_email' => $user_email,
						'user_name'  => $user_name,
						'form_type'  => 'store',
					)
				);
				break;
			case 'terms':
				mvr_get_template(
					'single-store/tab/terms-and-conditions.php',
					array(
						'vendor_obj' => $vendor_obj,
						'user_id'    => $user_id,
					)
				);
				break;
			default:
				mvr_get_template(
					'single-store/tab/overview.php',
					array(
						'vendor_obj' => $vendor_obj,
						'user_id'    => $user_id,
					)
				);
				break;
		}
	}
}

if ( ! function_exists( 'mvr_stores_filters' ) ) {
	/**
	 * Stores Filters
	 *
	 * @since 1.0.0
	 * @param Array  $vendors_objs Vendors Objects.
	 * @param String $position Display Position.
	 * */
	function mvr_stores_filters( $vendors_objs, $position ) {
		mvr_get_template(
			'stores/orderby.php',
			array(
				'vendors_objs' => $vendors_objs,
				'position'     => $position,
			)
		);
	}
}

if ( ! function_exists( 'mvr_no_stores_found' ) ) {
	/**
	 * No Stores Found
	 *
	 * @since 1.0.0
	 * */
	function mvr_no_stores_found() {
		mvr_get_template( 'stores/no-stores-found.php' );
	}
}

if ( ! function_exists( 'mvr_stores_loop_content' ) ) {
	/**
	 * Stores List
	 *
	 * @since 1.0.0
	 * @param MVR_Vendor $vendor_obj Vendor object.
	 * */
	function mvr_stores_loop_content( $vendor_obj ) {
		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		mvr_get_template( 'stores/store.php', array( 'vendor_obj' => $vendor_obj ) );
	}
}

if ( ! function_exists( 'mvr_template_loop_store_link_open' ) ) {
	/**
	 * Insert the opening anchor tag for stores in the loop.
	 *
	 * @since 1.0.0
	 * @param MVR_Vendor $vendor_obj Vendor object.
	 */
	function mvr_template_loop_store_link_open( $vendor_obj ) {
		/**
		 * Single Store URL.
		 *
		 * @since 1.0.0
		 */
		$link = apply_filters( 'mvr_loop_store_link', $vendor_obj->get_shop_url(), $vendor_obj );

		echo '<a href="' . esc_url( $link ) . '" class="mvr-store-link">';
	}
}

if ( ! function_exists( 'mvr_template_loop_store_link_close' ) ) {
	/**
	 * Insert the closing anchor tag for stores in the loop.
	 *
	 * @since 1.0.0
	 */
	function mvr_template_loop_store_link_close() {
		echo '</a>';
	}
}

if ( ! function_exists( 'mvr_single_store_header' ) ) {
	/**
	 * Store Header
	 *
	 * @since 1.0.0
	 * @param MVR_Vendor $vendor_obj Vendor object.
	 */
	function mvr_single_store_header( $vendor_obj ) {
		$logo   = ( $vendor_obj->get_logo_id() && $vendor_obj->get_logo_id() > 0 ) ? wp_get_attachment_url( $vendor_obj->get_logo_id() ) : MVR_PLUGIN_URL . '/assets/images/placeholder-64x64.png';
		$banner = $vendor_obj->get_banner_id() ? wp_get_attachment_url( $vendor_obj->get_banner_id() ) : MVR_PLUGIN_URL . '/assets/images/placeholder-800x400.png';

		mvr_get_template(
			'single-store/store-header.php',
			array(
				'vendor_obj' => $vendor_obj,
				'banner'     => $banner,
				'logo'       => $logo,
			)
		);
	}
}


if ( ! function_exists( 'mvr_single_store_product_filters' ) ) {
	/**
	 * Store Filters
	 *
	 * @since 1.0.0
	 * @param MVR_Vendor $vendor_obj Vendor object.
	 * @param Integer    $count Count.
	 */
	function mvr_single_store_product_filters( $vendor_obj, $count ) {
		if ( $count > 0 ) {
			mvr_get_template(
				'single-store/product-filters.php',
				array(
					'vendor_obj' => $vendor_obj,
					'count'      => $count,
				)
			);
		}
	}
}

if ( ! function_exists( 'mvr_single_store_product_pagination' ) ) {
	/**
	 * Single Store Pagination
	 *
	 * @since 1.0.0
	 * @param MVR_Vendor $vendor_obj Vendor Object.
	 */
	function mvr_single_store_product_pagination( $vendor_obj ) {
		$nonce_val = isset( $_REQUEST['_mvr_nonce'] ) ? sanitize_key( wp_unslash( $_REQUEST['_mvr_nonce'] ) ) : '';

		if ( wp_verify_nonce( $nonce_val, 'mvr-search-vendor-products' ) ) {
			$term = isset( $_REQUEST['_s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_s'] ) ) : '';
		}

		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 12,
			'meta_key'       => '_mvr_vendor',
			'meta_value'     => $vendor_obj->get_id(),
		);

		if ( ! empty( $term ) ) {
			$args['s'] = $term;
		}

		$the_query    = new WP_Query( $args );
		$page_count   = $the_query->max_num_pages;
		$current_page = max( 1, $the_query->get( 'paged', 1 ) );
		$args         = array(
			'page_count'      => $page_count,
			'current_page'    => $current_page,
			'prev_page_count' => ( ( $current_page - 1 ) === 0 ) ? ( $current_page ) : ( $current_page - 1 ),
			'next_page_count' => ( ( $current_page + 1 ) > ( $page_count - 1 ) ) ? ( $current_page ) : ( $current_page + 1 ),
			'query_args'      => array(),
			'url'             => add_query_arg( 'tab', 'products', $vendor_obj->get_shop_url() ),
		);

		mvr_get_template( 'pagination.php', $args );
	}
}

if ( ! function_exists( 'mvr_template_loop_store_banner' ) ) {
	/**
	 * Insert the banner for stores in the loop.
	 *
	 * @since 1.0.0
	 * @param MVR_Vendor $vendor_obj Vendor object.
	 */
	function mvr_template_loop_store_banner( $vendor_obj ) {
		$banner = $vendor_obj->get_banner_id() ? wp_get_attachment_url( $vendor_obj->get_banner_id() ) : MVR_PLUGIN_URL . '/assets/images/placeholder-800x400.png';
		?>
		<img class="mvr-store-banner" src="<?php echo esc_url( $banner ); ?>" width="300" height ="100"/>
		<?php
	}
}

if ( ! function_exists( 'mvr_template_loop_store_logo' ) ) {
	/**
	 * Insert the logo for stores in the loop.
	 *
	 * @since 1.0.0
	 * @param MVR_Vendor $vendor_obj Vendor object.
	 */
	function mvr_template_loop_store_logo( $vendor_obj ) {
		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		$logo = ( $vendor_obj->get_logo_id() && $vendor_obj->get_logo_id() > 0 ) ? wp_get_attachment_url( $vendor_obj->get_logo_id() ) : MVR_PLUGIN_URL . '/assets/images/placeholder-64x64.png';
		?>
		<img class="mvr-store-logo" src="<?php echo esc_url( $logo ); ?>" width="60" height ="60"/>
		<?php
	}
}

if ( ! function_exists( 'mvr_template_loop_store_details' ) ) {
	/**
	 * Insert the Details for stores in the loop.
	 *
	 * @since 1.0.0
	 * @param MVR_Vendor $vendor_obj Vendor object.
	 */
	function mvr_template_loop_store_details( $vendor_obj ) {
		/**
		 * Stores loop Name Class.
		 *
		 * @since 1.0.0
		 */
		$class = apply_filters( 'mvr_store_loop_name_classes', 'mvr-loop-store-name' );

		echo '<h2 class="' . esc_attr( $class ) . '">' . wp_kses_post( $vendor_obj->get_shop_name() ) . '</h2>';

		if ( 'yes' === get_option( 'mvr_settings_disp_vendor_address', 'yes' ) ) {
			mvr_get_formated_vendor_address( $vendor_obj );
		}
	}
}
if ( ! function_exists( 'mvr_logout_url' ) ) {
	/**
	 * Get logout endpoint.
	 *
	 * @since 1.0.0
	 * @param String $redirect Redirect URL.
	 * @return String
	 */
	function mvr_logout_url( $redirect = '' ) {
		/**
		 * Logout Redirect URL
		 *
		 * @since 1.0.0
		 */
		$redirect = $redirect ? $redirect : apply_filters( 'mvr_logout_default_redirect_url', mvr_get_page_permalink( 'dashboard' ) );

		return wp_nonce_url( wc_get_endpoint_url( 'mvr-logout', '', $redirect ), 'mvr-logout' );
	}
}

if ( ! function_exists( 'mvr_product_class' ) ) {
	/**
	 * Display the classes for the store div.
	 *
	 * @since 1.0.0
	 * @param String|Array $class      One or more classes to add to the class list.
	 * @param Integer      $vendor_id Product ID or product object.
	 */
	function mvr_store_class( $class = '', $vendor_id = null ) {
		$classes = array(
			$class,
			wc_get_loop_class(),
		);

		echo 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';
	}
}

if ( ! function_exists( 'mvr_dashboard_duplicate_products' ) ) {
	/**
	 * Dashboard > Duplicate Products template.
	 *
	 * @since 1.0.0
	 * @param Integer $current_page Current page number.
	 */
	function mvr_dashboard_duplicate_products( $current_page ) {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		$current_page = empty( $current_page ) ? 1 : absint( $current_page );
		$product_args = array(
			'post_status'               => 'publish',
			'page'                      => $current_page,
			'paginate'                  => true,
			'mvr_meta_relation'         => 'OR',
			'mvr_vendor_meta_not_exist' => $vendor_obj->get_id(),
		);
		$term         = '';
		$nonce_val    = isset( $_GET['_mvr_nonce'] ) ? sanitize_key( wp_unslash( $_GET['_mvr_nonce'] ) ) : '';

		if ( wp_verify_nonce( $nonce_val, 'mvr-dashboard-products-nonce' ) ) {
			$term = isset( $_GET['mvr_search'] ) ? sanitize_text_field( wp_unslash( $_GET['mvr_search'] ) ) : '';

			if ( ! empty( $term ) ) {
				$product_args['s'] = $term;
			}
		}

		/**
		 * Duplicate Products Query.
		 *
		 * @since 1.0.0
		 */
		$site_products = wc_get_products( apply_filters( 'mvr_duplicate_products_query', $product_args ) );

		mvr_get_template(
			'dashboard/duplicate.php',
			array(
				'term'            => $term,
				'vendor_id'       => $vendor_obj->get_id(),
				'current_page'    => absint( $current_page ),
				'vendor_products' => $site_products,
				'has_products'    => 0 < $site_products->total,
				'wp_button_class' => wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '',
			)
		);
	}
}

if ( ! function_exists( 'mvr_dashboard_add_withdraw_button' ) ) {
	/**
	 * Dashboard > Add-withdraw request Button.
	 *
	 * @since 1.0.0
	 */
	function mvr_dashboard_add_withdraw_button() {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ||
		( 'yes' === get_option( 'mvr_settings_hide_withdraw', 'no' ) && '2' === $vendor_obj->get_payout_type() ) ||
		( 0 >= $vendor_obj->get_amount() ) ) {
			return;
		}

		$min_withdraw = (float) get_option( 'mvr_settings_min_withdraw_threshold', 0 );

		// Minimum Withdraw Threshold.
		if ( $min_withdraw > $vendor_obj->get_amount() ) {
			return;
		}

		$allow_payment_gateway = get_option( 'mvr_settings_withdraw_allow_payment', array( '1' ) );

		// Payment Gateway Check.
		if ( ! in_array( $vendor_obj->get_payment_method(), $allow_payment_gateway, true ) ) {
			return;
		}

		$add_new_withdraw_url = mvr_get_dashboard_endpoint_url( mvr()->query->query_vars['mvr-add-withdraw'] );

		echo '<a href="' . esc_url( $add_new_withdraw_url ) . '" class="mvr-page-title-action">' . esc_attr( get_option( 'mvr_dashboard_withdraw_add_new_btn_label', 'Add New Withdraw Request' ) ) . '</a>';
	}
}

if ( ! function_exists( 'mvr_validate_payment_method' ) ) {
	/**
	 * Dashboard > Validate Payment Method.
	 *
	 * @since 1.0.0
	 */
	function mvr_validate_payment_method() {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		if ( ! $vendor_obj->cleared_payment_tab() ) {
			wc_print_notice( esc_html__( 'Need to Fill Payment Method', 'multi-vendor-vor-woocommerce' ), 'error' );
		}
	}
}

if ( ! function_exists( 'mvr_withdraw_balance_display' ) ) {
	/**
	 * Dashboard > Withdraw Balance template.
	 *
	 * @since 1.0.0
	 */
	function mvr_withdraw_balance_display() {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		mvr_get_template(
			'dashboard/withdraw/amount.php',
			array(
				'vendor_obj' => $vendor_obj,
			)
		);
	}
}

if ( ! function_exists( 'mvr_dashboard_add_withdraw' ) ) {
	/**
	 * Dashboard > Withdraw Balance template.
	 *
	 * @since 1.0.0
	 */
	function mvr_dashboard_add_withdraw() {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		mvr_get_template(
			'dashboard/withdraw/form-withdraw.php',
			array(
				'vendor_obj' => $vendor_obj,
			)
		);
	}
}

if ( ! function_exists( 'mvr_transaction_balance_display' ) ) {
	/**
	 * Dashboard > Transaction Balance template.
	 *
	 * @since 1.0.0
	 */
	function mvr_transaction_balance_display() {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		mvr_get_template(
			'dashboard/transaction/amount.php',
			array(
				'vendor_obj' => $vendor_obj,
			)
		);
	}
}

if ( ! function_exists( 'mvr_display_vendor_notices' ) ) {
	/**
	 * Dashboard > Display Vendor Notices.
	 *
	 * @since 1.0.0
	 */
	function mvr_display_vendor_notices() {
		$vendor_obj = mvr_get_current_vendor_object();

		if ( ! mvr_is_vendor( $vendor_obj ) ) {
			return;
		}

		if ( 'pending' === $vendor_obj->get_status() ) {
			wc_print_notice( get_option( 'mvr_vendor_app_yet_approve_message', 'Vendor Application yet to be Approved' ), 'notice' );
		}

		if ( 'reject' === $vendor_obj->get_status() ) {
			wc_print_notice( get_option( 'mvr_vendor_app_rejected_message', 'Vendor Application was Rejected' ), 'error' );
		}

		if ( ! $vendor_obj->cleared_form_filling() ) {
			wc_print_notice( get_option( 'mvr_vendor_required_field_message', 'Please fill in the Required Fields to Complete the Vendor Application.' ), 'error' );
		}
	}
}

if ( ! class_exists( 'mvr_get_spmv_table_columns' ) ) {
	/**
	 * Single Product Multi Vendor Coupon columns.
	 *
	 * @since 1.0.0
	 * @return Array
	 */
	function mvr_get_spmv_table_columns() {
		$args = array(
			'product' => get_option( 'mvr_dashboard_spmv_product_col_label', 'Product Name' ),
			'price'   => get_option( 'mvr_dashboard_spmv_price_col_label', 'Price' ),
			'rating'  => get_option( 'mvr_dashboard_spmv_rating_col_label', 'Rating' ),
			'actions' => get_option( 'mvr_dashboard_spmv_action_col_label', 'Action' ),
		);

		/**
		 * Filters the array of Coupon columns.
		 *
		 * @since 1.0.0
		 * @param Array $args Array of column labels keyed by column IDs.
		 */
		return apply_filters( 'mvr_get_spmv_table_columns', $args );
	}
}


if ( ! class_exists( 'mvr_get_spmv_actions' ) ) {
	/**
	 * Single Product Multi Vendor actions.
	 *
	 * @since  1.0.0
	 * @param  int|WC_Product $product_obj Product instance or ID.
	 * @return array
	 */
	function mvr_get_spmv_actions( $product_obj ) {
		if ( ! is_object( $product_obj ) ) {
			$product_id  = absint( $product_obj );
			$product_obj = new WC_Product( $product_id );
		}

		$actions = array(
			'view' => array(
				'url'  => $product_obj->get_permalink(),
				'name' => __( 'View', 'multi-vendor-marketplace' ),
			),
		);

		if ( 'simple' === $product_obj->get_type() ) {
			$actions['add_to_cart'] = array(
				'url'  => $product_obj->add_to_cart_url(),
				'name' => __( 'Add to Cart', 'multi-vendor-marketplace' ),
			);
		}

		/**
		 * Single Product Multi Vendor Actions
		 *
		 * @since 1.0.0
		 */
		return apply_filters( 'mvr_spmv_actions', $actions, $product_obj );
	}
}

