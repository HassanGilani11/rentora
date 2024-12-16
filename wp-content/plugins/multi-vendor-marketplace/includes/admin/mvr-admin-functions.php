<?php
/**
 * Admin functions
 *
 * @package Multi Vendor Marketplace/Admin Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'mvr_get_allowed_setting_tabs' ) ) {

	/**
	 * Get the setting tabs.
	 *
	 * @return Array
	 * */
	function mvr_get_allowed_setting_tabs() {
		/**
		 * Settings tabs array.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( 'mvr_settings_tabs_array', array() );
	}
}

if ( ! function_exists( 'mvr_screen_ids' ) ) {

	/**
	 * Multi Vendor screen IDs.
	 *
	 * @since 1.0.0
	 * @return Array
	 * */
	function mvr_screen_ids() {
		$wc_screen_id = sanitize_title( __( 'WooCommerce', 'woocommerce' ) );

		/**
		 * Multi Vendor Marketplace screen ids.
		 *
		 * @since 1.0.0
		 * @return Array
		 */
		return apply_filters(
			'mvr_screen_ids',
			array(
				'mvr_dashboard',
				'mvr_settings',
				'mvr_vendor',
				'mvr_payout_batch',
				'mvr_staff',
				'mvr_commission',
				'mvr_withdraw',
				'mvr_transaction',
				'mvr_payout',
				'mvr_store_review',
				'mvr_notification',
				'mvr_enquiry',
				$wc_screen_id . '_page_mvr_dashboard',
				$wc_screen_id . '_page_mvr_settings',
				$wc_screen_id . '_page_mvr_commission',
				$wc_screen_id . '_page_mvr_withdraw',
				$wc_screen_id . '_page_mvr_transaction',
				$wc_screen_id . '_page_mvr_payout',
				$wc_screen_id . '_page_mvr_store_review',
				$wc_screen_id . '_page_mvr_notification',
				$wc_screen_id . '_page_mvr_enquiry',
				'admin_page_mvr_withdraw_exporter',
				'admin_page_mvr_generate_payout',
				'admin_page_mvr_generate_withdraw_payout',
				'comment',
			)
		);
	}
}

if ( ! function_exists( 'mvr_page_screen_ids' ) ) {

	/**
	 * Page screen IDs.
	 *
	 * @since 1.0.0
	 * @return Array
	 * */
	function mvr_page_screen_ids() {
		$mvr_screen_ids  = mvr_screen_ids();
		$page_screen_ids = array( 'product', 'shop_coupon' );

		if ( mvr_check_is_array( $mvr_screen_ids ) ) {
			$page_screen_ids = array_merge( $mvr_screen_ids, $page_screen_ids );
		}

		/**
		 * Page screen ids.
		 *
		 * @since 1.0.0
		 * @return Array
		 */
		return apply_filters( 'mvr_page_screen_ids', $page_screen_ids );
	}
}

if ( ! function_exists( 'mvr_get_current_menu_tab' ) ) {

	/**
	 * Get Current Menu Tab.
	 *
	 * @since 1.0.0
	 * @param String $screen_id Screen_id.
	 * @return String
	 * */
	function mvr_get_current_menu_tab( $screen_id ) {
		$wc_screen_id = sanitize_title( __( 'WooCommerce', 'woocommerce' ) );

		if ( mvr_check_is_screen( $screen_id, array( "{$wc_screen_id}_page_mvr_settings", 'mvr_payout_batch' ) ) ) {
			return 'settings';
		} elseif ( mvr_check_is_screen( $screen_id, array( 'mvr_vendor', 'admin_page_mvr_generate_payout' ) ) ) {
			return 'vendor';
		} elseif ( mvr_check_is_screen( $screen_id, 'mvr_staff' ) ) {
			return 'staff';
		} elseif ( mvr_check_is_screen( $screen_id, 'mvr_commission' ) ) {
			return 'commission';
		} elseif ( mvr_check_is_screen( $screen_id, 'mvr_transaction' ) ) {
			return 'transaction';
		} elseif ( mvr_check_is_screen( $screen_id, 'mvr_notification' ) ) {
			return 'notification';
		} elseif ( mvr_check_is_screen( $screen_id, 'mvr_enquiry' ) ) {
			return 'enquiry';
		} elseif ( mvr_check_is_screen( $screen_id, 'mvr_payout' ) ) {
			return 'payout';
		} elseif ( mvr_check_is_screen( $screen_id, array( "{$wc_screen_id}_page_mvr_store_review", 'comment' ) ) ) {
			return 'review';
		} elseif ( mvr_check_is_screen( $screen_id, 'mvr_report' ) ) {
			return 'report';
		} elseif ( mvr_check_is_screen( $screen_id, array( "{$wc_screen_id}_page_mvr_withdraw", 'admin_page_mvr_withdraw_exporter', 'admin_page_mvr_generate_withdraw_payout' ) ) ) {
			return 'withdraw';
		} else {
			return 'dashboard';
		}
	}
}

if ( ! function_exists( 'mvr_get_settings_page_url' ) ) {

	/**
	 * Get the settings page URL.
	 *
	 * @since 1.0.0
	 * @param Array $args Arguments of URL.
	 * @return URL
	 * */
	function mvr_get_settings_page_url( $args = array() ) {
		$url = admin_url( 'admin.php?page=mvr_settings' );

		if ( mvr_check_is_array( $args ) ) {
			$url = add_query_arg( $args, $url );
		}

		return $url;
	}
}

if ( ! function_exists( 'mvr_get_vendor_page_url' ) ) {

	/**
	 * Get the Vendor page URL.
	 *
	 * @since 1.0.0
	 * @param Array $args Arguments of URL.
	 * @return URL
	 * */
	function mvr_get_vendor_page_url( $args = array() ) {
		$url = admin_url( 'edit.php?post_type=mvr_vendor' );

		if ( mvr_check_is_array( $args ) ) {
			$url = add_query_arg( $args, $url );
		}

		return $url;
	}
}

if ( ! function_exists( 'mvr_get_dashboard_page_url' ) ) {
	/**
	 * Get the Dashboard page URL.
	 *
	 * @since 1.0
	 * @param Array $args Arguments of URL.
	 * @return URL
	 * */
	function mvr_get_dashboard_page_url( $args = array() ) {
		$url = admin_url( 'admin.php?page=mvr_dashboard' );

		if ( mvr_check_is_array( $args ) ) {
			$url = add_query_arg( $args, $url );
		}

		return $url;
	}
}

if ( ! function_exists( 'mvr_get_commission_page_url' ) ) {
	/**
	 * Get the Commission page URL.
	 *
	 * @since 1.0
	 * @param Array $args Arguments of URL.
	 * @return URL
	 * */
	function mvr_get_commission_page_url( $args = array() ) {
		$url = admin_url( 'admin.php?page=mvr_commission' );

		if ( mvr_check_is_array( $args ) ) {
			$url = add_query_arg( $args, $url );
		}

		return $url;
	}
}

if ( ! function_exists( 'mvr_get_withdraw_page_url' ) ) {
	/**
	 * Get the Withdraw page URL.
	 *
	 * @since 1.0
	 * @param Array $args Arguments of URL.
	 * @return URL
	 * */
	function mvr_get_withdraw_page_url( $args = array() ) {
		$url = admin_url( 'admin.php?page=mvr_withdraw' );

		if ( mvr_check_is_array( $args ) ) {
			$url = add_query_arg( $args, $url );
		}

		return $url;
	}
}

if ( ! function_exists( 'mvr_get_transaction_page_url' ) ) {
	/**
	 * Get the Transaction page URL.
	 *
	 * @since 1.0
	 * @param Array $args Arguments of URL.
	 * @return URL
	 * */
	function mvr_get_transaction_page_url( $args = array() ) {
		$url = admin_url( 'admin.php?page=mvr_transaction' );

		if ( mvr_check_is_array( $args ) ) {
			$url = add_query_arg( $args, $url );
		}

		return $url;
	}
}

if ( ! function_exists( 'mvr_get_payout_page_url' ) ) {
	/**
	 * Get the Payout page URL.
	 *
	 * @since 1.0
	 * @param Array $args Arguments of URL.
	 * @return URL
	 * */
	function mvr_get_payout_page_url( $args = array() ) {
		$url = admin_url( 'admin.php?page=mvr_payout' );

		if ( mvr_check_is_array( $args ) ) {
			$url = add_query_arg( $args, $url );
		}

		return $url;
	}
}

if ( ! function_exists( 'mvr_get_payout_batch_page_url' ) ) {
	/**
	 * Get the Payout Batch page URL.
	 *
	 * @since 1.0
	 * @param Array $args Arguments of URL.
	 * @return URL
	 * */
	function mvr_get_payout_batch_page_url( $args = array() ) {
		$url = admin_url( 'admin.php?page=mvr_settings&tab=payment' );

		if ( mvr_check_is_array( $args ) ) {
			$url = add_query_arg( $args, $url );
		}

		return $url;
	}
}

if ( ! function_exists( 'mvr_get_enquiry_page_url' ) ) {
	/**
	 * Get the enquiry page URL.
	 *
	 * @since 1.0
	 * @param Array $args Arguments of URL.
	 * @return URL
	 * */
	function mvr_get_enquiry_page_url( $args = array() ) {
		$url = admin_url( 'admin.php?page=mvr_enquiry' );

		if ( mvr_check_is_array( $args ) ) {
			$url = add_query_arg( $args, $url );
		}

		return $url;
	}
}

if ( ! function_exists( 'mvr_get_notification_page_url' ) ) {
	/**
	 * Get the notification page URL.
	 *
	 * @since 1.0
	 * @param Array $args Arguments of URL.
	 * @return URL
	 * */
	function mvr_get_notification_page_url( $args = array() ) {
		$url = admin_url( 'admin.php?page=mvr_notification' );

		if ( mvr_check_is_array( $args ) ) {
			$url = add_query_arg( $args, $url );
		}

		return $url;
	}
}

if ( ! function_exists( 'mvr_get_review_page_url' ) ) {
	/**
	 * Get the review page URL.
	 *
	 * @since 1.0
	 * @param Array $args Arguments of URL.
	 * @return URL
	 * */
	function mvr_get_review_page_url( $args = array() ) {
		$url = admin_url( 'admin.php?page=mvr_store_review' );

		if ( mvr_check_is_array( $args ) ) {
			$url = add_query_arg( $args, $url );
		}

		return $url;
	}
}

if ( ! function_exists( 'mvr_check_is_screen' ) ) {
	/**
	 * Check the screen against the which context.
	 *
	 * @param String $screen_id Screen ID.
	 * @param String $which Condition to check.
	 * @return Boolean
	 */
	function mvr_check_is_screen( $screen_id, $which = 'any' ) {
		if ( ! in_array( $screen_id, mvr_screen_ids(), true ) ) {
			return false;
		}

		if ( is_array( $which ) ) {
			return in_array( $screen_id, $which, true );
		} elseif ( 'any' !== $which ) {
			$wc_screen_id = sanitize_title( __( 'WooCommerce', 'woocommerce' ) );

			return ( $screen_id === $which || "{$wc_screen_id}_page_{$which}" === $screen_id );
		}

		return true;
	}
}

if ( ! function_exists( 'mvr_get_pages_options' ) ) {

	/**
	 * Function to Commission Tax Calculation Type Options
	 *
	 * @since 1.0.0
	 * @param String $key Key.
	 * @param Array  $exclude Exclude Keys.
	 * @return Array
	 * */
	function mvr_get_pages_options( $key = '', $exclude = array() ) {
		$args = array(
			'0' => esc_html__( 'Select page', 'multi-vendor-marketplace' ),
		);

		$pages = get_posts(
			array(
				'post_type'   => 'page',
				'numberposts' => - 1,
			)
		);

		if ( mvr_check_is_array( $pages ) ) {
			foreach ( $pages as $page ) {
				if ( ! is_object( $page ) ) {
					continue;
				}

				$args[ $page->ID ] = $page->post_title;
			}
		}

		if ( mvr_check_is_array( $exclude ) ) {
			$args = array_diff_key( $args, array_flip( $exclude ) );
		}

		if ( ! empty( $key ) && isset( $args[ $key ] ) ) {
			return $args[ $key ];
		}

		return $args;
	}
}

if ( ! function_exists( 'mvr_get_withdraw_payment_type_options' ) ) {

	/**
	 * Withdraw Payment Type Options
	 *
	 * @since 1.0.0
	 * @param String $key Key.
	 * @param Array  $exclude Exclude Keys.
	 * @return Array
	 * */
	function mvr_get_withdraw_payment_type_options( $key = '', $exclude = array() ) {
		/**
		 * Withdraw Payment Type Option.
		 *
		 * @since 1.0.0
		 */
		$args = apply_filters(
			'mvr_get_withdraw_payment_type_options',
			array(
				'1' => esc_html__( 'Bank Transfer', 'multi-vendor-marketplace' ),
				'2' => esc_html__( 'PayPal', 'multi-vendor-marketplace' ),
			)
		);

		if ( mvr_check_is_array( $exclude ) ) {
			$args = array_diff_key( $args, array_flip( $exclude ) );
		}

		if ( ! empty( $key ) && isset( $args[ $key ] ) ) {
			return $args[ $key ];
		}

		return $args;
	}
}

if ( ! function_exists( 'mvr_allowed_product_type_options' ) ) {

	/**
	 * Allowed Product Type Options
	 *
	 * @since 1.0.0
	 * @param String $key Key.
	 * @param Array  $exclude Exclude Keys.
	 * @return Array
	 * */
	function mvr_allowed_product_type_options( $key = '', $exclude = array() ) {
		/**
		 * Allowed Product Type Option.
		 *
		 * @since 1.0.0
		 */
		$args = apply_filters(
			'mvr_allowed_product_type_options',
			array(
				'1' => esc_html__( 'Simple Products', 'multi-vendor-marketplace' ),
				'2' => esc_html__( 'Variable Products', 'multi-vendor-marketplace' ),
			)
		);

		if ( mvr_check_is_array( $exclude ) ) {
			$args = array_diff_key( $args, array_flip( $exclude ) );
		}

		if ( ! empty( $key ) && isset( $args[ $key ] ) ) {
			return $args[ $key ];
		}

		return $args;
	}
}


if ( ! function_exists( 'mvr_get_vendor_table_labels' ) ) {
	/**
	 * Vendor table labels
	 *
	 * @since 1.0.0
	 * @return Array
	 * */
	function mvr_get_vendor_table_labels() {
		return array(
			'name'         => esc_html__( 'Vendor', 'multi-vendor-marketplace' ),
			'amount'       => esc_html__( 'Payable Amount', 'multi-vendor-marketplace' ),
			'status'       => esc_html__( 'Status', 'multi-vendor-marketplace' ),
			'subscription' => esc_html__( 'Subscription', 'multi-vendor-marketplace' ),
			'created_date' => esc_html__( 'Registered Date & Time', 'multi-vendor-marketplace' ),
			'tax_shipping' => esc_html__( 'Tax Cost', 'multi-vendor-marketplace' ),
			'commission'   => esc_html__( 'Commission Value ', 'multi-vendor-marketplace' ),
			'property'     => esc_html__( 'Products & Orders', 'multi-vendor-marketplace' ),
		);
	}
}

if ( ! function_exists( 'mvr_get_staff_table_labels' ) ) {
	/**
	 * Vendor Staff table labels
	 *
	 * @since 1.0.0
	 * @return Array
	 * */
	function mvr_get_staff_table_labels() {
		return array(
			'name'         => esc_html__( 'Staff Name', 'multi-vendor-marketplace' ),
			'vendor'       => esc_html__( 'Vendor', 'multi-vendor-marketplace' ),
			'status'       => esc_html__( 'Status', 'multi-vendor-marketplace' ),
			'created_date' => esc_html__( 'Created Date', 'multi-vendor-marketplace' ),
		);
	}
}

if ( ! function_exists( 'mvr_get_commission_table_labels' ) ) {
	/**
	 * Commission table labels
	 *
	 * @since 1.0.0
	 * @return Array
	 * */
	function mvr_get_commission_table_labels() {
		return array(
			'name'          => esc_html__( 'Vendor', 'multi-vendor-marketplace' ),
			'from'          => esc_html__( 'Activity', 'multi-vendor-marketplace' ),
			'status'        => esc_html__( 'Status', 'multi-vendor-marketplace' ),
			'amount'        => esc_html__( 'Admin Earnings', 'multi-vendor-marketplace' ),
			'vendor_amount' => esc_html__( 'Vendor Earnings', 'multi-vendor-marketplace' ),
			'date'          => esc_html__( 'Date & Time', 'multi-vendor-marketplace' ),
		);
	}
}

if ( ! function_exists( 'mvr_get_withdraw_table_labels' ) ) {

	/**
	 * Withdraw table labels
	 *
	 * @since 1.0.0
	 * @return Array
	 * */
	function mvr_get_withdraw_table_labels() {
		return array(
			'name'    => esc_html__( 'Vendor Name', 'multi-vendor-marketplace' ),
			'amount'  => esc_html__( 'Requested Amount', 'multi-vendor-marketplace' ),
			'charge'  => esc_html__( 'Withdrawal Charges', 'multi-vendor-marketplace' ),
			'status'  => esc_html__( 'Status', 'multi-vendor-marketplace' ),
			'payment' => esc_html__( 'Payment Method', 'multi-vendor-marketplace' ),
			'date'    => esc_html__( 'Last Modified on', 'multi-vendor-marketplace' ),
		);
	}
}

if ( ! function_exists( 'mvr_get_enquiry_table_labels' ) ) {

	/**
	 * Enquiry table labels
	 *
	 * @since 1.0.0
	 * @return Array
	 * */
	function mvr_get_enquiry_table_labels() {
		return array(
			'customer' => esc_html__( 'Customer', 'multi-vendor-marketplace' ),
			'vendor'   => esc_html__( 'Vendor', 'multi-vendor-marketplace' ),
			'message'  => esc_html__( 'Message', 'multi-vendor-marketplace' ),
			'status'   => esc_html__( 'Status', 'multi-vendor-marketplace' ),
			'date'     => esc_html__( 'Date and Time', 'multi-vendor-marketplace' ),
		);
	}
}

if ( ! function_exists( 'mvr_get_notification_table_labels' ) ) {

	/**
	 * Enquiry table labels
	 *
	 * @since 1.0.0
	 * @return Array
	 * */
	function mvr_get_notification_table_labels() {
		return array(
			'ID'      => esc_html__( 'ID', 'multi-vendor-marketplace' ),
			'type'    => esc_html__( 'Type', 'multi-vendor-marketplace' ),
			'message' => esc_html__( 'Message', 'multi-vendor-marketplace' ),
			'date'    => esc_html__( 'Date and Time', 'multi-vendor-marketplace' ),
		);
	}
}

if ( ! function_exists( 'mvr_get_review_table_labels' ) ) {
	/**
	 * Commission table labels
	 *
	 * @since 1.0.0
	 * @return Array
	 * */
	function mvr_get_review_table_labels() {
		return array(
			'type'     => esc_html__( 'Type', 'multi-vendor-marketplace' ),
			'author'   => esc_html__( 'Author', 'multi-vendor-marketplace' ),
			'rating'   => esc_html__( 'Rating', 'multi-vendor-marketplace' ),
			'comment'  => esc_html__( 'Review', 'multi-vendor-marketplace' ),
			'response' => esc_html__( 'Store', 'multi-vendor-marketplace' ),
			'date'     => esc_html__( 'Submitted on', 'multi-vendor-marketplace' ),
		);
	}
}

if ( ! function_exists( 'mvr_get_transaction_table_labels' ) ) {

	/**
	 * Payout table labels
	 *
	 * @since 1.0.0
	 * @return Array
	 * */
	function mvr_get_transaction_table_labels() {
		return array(
			'vendor' => esc_html__( 'Vendor', 'multi-vendor-marketplace' ),
			'amount' => esc_html__( 'Amount', 'multi-vendor-marketplace' ),
			'type'   => esc_html__( 'Type', 'multi-vendor-marketplace' ),
			'source' => esc_html__( 'Source', 'multi-vendor-marketplace' ),
			'status' => esc_html__( 'Status', 'multi-vendor-marketplace' ),
			'date'   => esc_html__( 'Last Modified on', 'multi-vendor-marketplace' ),
		);
	}
}

if ( ! function_exists( 'mvr_get_payout_table_labels' ) ) {

	/**
	 * Payout table labels
	 *
	 * @since 1.0.0
	 * @return Array
	 * */
	function mvr_get_payout_table_labels() {
		return array(
			'vendor'   => esc_html__( 'Vendor', 'multi-vendor-marketplace' ),
			'amount'   => esc_html__( 'Amount', 'multi-vendor-marketplace' ),
			'email'    => esc_html__( 'Email', 'multi-vendor-marketplace' ),
			'batch_id' => esc_html__( 'Batch ID', 'multi-vendor-marketplace' ),
			'status'   => esc_html__( 'Status', 'multi-vendor-marketplace' ),
			'date'     => esc_html__( 'Last Modified on', 'multi-vendor-marketplace' ),
		);
	}
}

if ( ! function_exists( 'mvr_get_payout_batch_table_labels' ) ) {

	/**
	 * Payout table labels
	 *
	 * @since 1.0.0
	 * @return Array
	 * */
	function mvr_get_payout_batch_table_labels() {
		return array(
			'id'       => esc_html__( 'ID', 'multi-vendor-marketplace' ),
			'batch_id' => esc_html__( 'Batch ID', 'multi-vendor-marketplace' ),
			'status'   => esc_html__( 'Status', 'multi-vendor-marketplace' ),
			'date'     => esc_html__( 'Date and Time', 'multi-vendor-marketplace' ),
		);
	}
}

if ( ! function_exists( 'mvr_get_payout_batch_item_table_labels' ) ) {

	/**
	 * Payout table labels
	 *
	 * @since 1.0.0
	 * @return Array
	 * */
	function mvr_get_payout_batch_item_table_labels() {
		return array(
			'no'     => esc_html__( 'S.no', 'multi-vendor-marketplace' ),
			'vendor' => esc_html__( 'Vendor Name', 'multi-vendor-marketplace' ),
			'email'  => esc_html__( 'Papal Email', 'multi-vendor-marketplace' ),
			'amount' => esc_html__( 'Amount', 'multi-vendor-marketplace' ),
			'fee'    => esc_html__( 'Fee', 'multi-vendor-marketplace' ),
			'status' => esc_html__( 'Status', 'multi-vendor-marketplace' ),
		);
	}
}

if ( ! function_exists( 'mvr_get_vendor_payout_type_options' ) ) {
	/**
	 * Vendor Payout Options
	 *
	 * @since 1.0.0
	 * @param String $key Key.
	 * @param Array  $exclude Exclude Keys.
	 * @return Array
	 * */
	function mvr_get_vendor_payout_type_options( $key = '', $exclude = array() ) {
		/**
		 * Vendor Payout Type Option.
		 *
		 * @since 1.0.0
		 */
		$args = apply_filters(
			'mvr_get_vendor_payout_type_options',
			array(
				'1' => esc_html__( 'PayPal', 'multi-vendor-marketplace' ),
				'2' => esc_html__( 'Bank', 'multi-vendor-marketplace' ),
			)
		);

		if ( mvr_check_is_array( $exclude ) ) {
			$args = array_diff_key( $args, array_flip( $exclude ) );
		}

		if ( ! empty( $key ) && isset( $args[ $key ] ) ) {
			return $args[ $key ];
		}

		return $args;
	}
}

if ( ! function_exists( 'mvr_add_admin_notice' ) ) {
	/**
	 * Store a message to display via @see mvr_display_admin_notices().
	 *
	 * @param String $message The message to display.
	 * @param String $notice_type Notice Type.
	 * @since 1.0.0
	 */
	function mvr_add_admin_notice( $message, $notice_type = 'success' ) {
		$notices = get_transient( '_mvr_admin_notices' );

		if ( false === $notices ) {
			$notices = array();
		}

		$notices[ $notice_type ][] = $message;

		set_transient( '_mvr_admin_notices', $notices, 60 * 60 );
	}
}

if ( ! function_exists( 'mvr_display_admin_notices' ) ) {
	/**
	 * Display any notices added with @see mvr_add_admin_notice()
	 * This method is also hooked to 'admin_notices' to display notices there.
	 *
	 * @param Boolean $clear Whether to clear all notices.
	 * @since 1.0.0
	 */
	function mvr_display_admin_notices( $clear = true ) {
		$notices = get_transient( '_mvr_admin_notices' );

		if ( false !== $notices && ! empty( $notices ) ) {
			if ( ! empty( $notices['success'] ) ) {
				array_walk( $notices['success'], 'esc_html' );
				echo '<div id="moderated" class="updated"><p>' . wp_kses_post( implode( "</p>\n<p>", $notices['success'] ) ) . '</p></div>';
			}

			if ( ! empty( $notices['error'] ) ) {
				array_walk( $notices['error'], 'esc_html' );
				echo '<div id="moderated" class="error"><p>' . wp_kses_post( implode( "</p>\n<p>", $notices['error'] ) ) . '</p></div>';
			}
		}

		if ( false !== $clear ) {
			mvr_clear_admin_notices();
		}
	}
}

if ( ! function_exists( 'mvr_clear_admin_notices' ) ) {
	/**
	 * Delete any admin notices we stored for display later.
	 *
	 * @since 1.0.0
	 */
	function mvr_clear_admin_notices() {
		delete_transient( '_mvr_admin_notices' );
	}
}

if ( ! function_exists( 'mvr_create_page' ) ) {
	/**
	 * Create a page and store the ID in an option.
	 *
	 * @since 1.0.0
	 * @param Mixed   $slug Slug for the new page.
	 * @param String  $option Option name to store the page's ID.
	 * @param String  $page_title (default: '') Title for the new page.
	 * @param String  $page_content (default: '') Content for the new page.
	 * @param Integer $post_parent (default: 0) Parent for the new page.
	 * @return Integer page ID.
	 */
	function mvr_create_page( $slug, $option = '', $page_title = '', $page_content = '', $post_parent = 0 ) {
		$option_value = '' !== $option ? get_option( $option ) : 0;

		if ( $option_value > 0 ) {
			$page_object = get_post( $option_value );

			if ( $page_object && 'page' === $page_object->post_type && ! in_array( $page_object->post_status, array( 'pending', 'trash', 'future', 'auto-draft' ), true ) ) {
				// Valid page is already in place.
				return $page_object->ID;
			}
		}

		$page_data = array(
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'post_author'    => 1,
			'post_name'      => $slug,
			'post_title'     => $page_title,
			'post_content'   => $page_content,
			'post_parent'    => $post_parent,
			'comment_status' => 'closed',
		);

		$page_id = wp_insert_post( $page_data );

		if ( $option ) {
			update_option( $option, $page_id );
		}

		return $page_id;
	}
}

if ( ! function_exists( 'mvr_vendor_banner_html' ) ) {
	/**
	 * Returns HTML for the vendor banner meta box.
	 *
	 * @since 1.0.0
	 * @param Integer|Null         $thumbnail_id Optional. Thumbnail attachment ID. Default null.
	 * @param Integer|WP_Post|Null $post Optional. The post ID or object associated with the thumbnail. Defaults to global $post.
	 * @return string The post thumbnail HTML.
	 */
	function mvr_vendor_banner_html( $thumbnail_id = null, $post = null ) {
		$_wp_additional_image_sizes = wp_get_additional_image_sizes();
		$post                       = get_post( $post );
		$post_type_object           = get_post_type_object( $post->post_type );
		$set_thumbnail_link         = '<p class="hide-if-no-js"><a href="%s" id="set-post-thumbnail"%s class="thickbox">%s</a></p>';
		$upload_iframe_src          = get_upload_iframe_src( 'image', $post->ID );

		$content = sprintf(
			$set_thumbnail_link,
			esc_url( $upload_iframe_src ),
			'', // Empty when there's no featured image set, `aria-describedby` attribute otherwise.
			esc_html( $post_type_object->labels->set_featured_image )
		);

		if ( $thumbnail_id && get_post( $thumbnail_id ) ) {
			$size = isset( $_wp_additional_image_sizes['post-thumbnail'] ) ? 'post-thumbnail' : array( 266, 266 );

			/**
			 * Filters the size used to display the post thumbnail image in the 'Featured image' meta box.
			 *
			 * Note: When a theme adds 'post-thumbnail' support, a special 'post-thumbnail'
			 * image size is registered, which differs from the 'thumbnail' image size
			 * managed via the Settings > Media screen.
			 *
			 * @since 4.4.0
			 *
			 * @param string|int[] $size         Requested image size. Can be any registered image size name, or
			 *                                   an array of width and height values in pixels (in that order).
			 * @param int          $thumbnail_id Post thumbnail attachment ID.
			 * @param WP_Post      $post         The post object associated with the thumbnail.
			 */
			$size           = apply_filters( 'admin_post_thumbnail_size', $size, $thumbnail_id, $post );
			$thumbnail_html = wp_get_attachment_image( $thumbnail_id, $size );

			if ( ! empty( $thumbnail_html ) ) {
				$content  = sprintf(
					$set_thumbnail_link,
					esc_url( $upload_iframe_src ),
					' aria-describedby="set-post-thumbnail-desc"',
					$thumbnail_html
				);
				$content .= '<p class="hide-if-no-js howto" id="set-post-thumbnail-desc">' . __( 'Click the image to edit or update' ) . '</p>';
				$content .= '<p class="hide-if-no-js"><a href="#" id="remove-post-thumbnail">' . esc_html( $post_type_object->labels->remove_featured_image ) . '</a></p>';
			}
		}

		$content .= '<input type="hidden" id="_thumbnail_id" name="_thumbnail_id" value="' . esc_attr( $thumbnail_id ? $thumbnail_id : '' ) . '" />';

		/**
		 * Filters the admin post thumbnail HTML markup to return.
		 *
		 * @since 2.9.0
		 * @since 3.5.0 Added the `$post_id` parameter.
		 * @since 4.6.0 Added the `$thumbnail_id` parameter.
		 *
		 * @param string   $content      Admin post thumbnail HTML markup.
		 * @param int      $post_id      Post ID.
		 * @param int|null $thumbnail_id Thumbnail attachment ID, or null if there isn't one.
		 */
		return apply_filters( 'admin_post_thumbnail_html', $content, $post->ID, $thumbnail_id );
	}
}

if ( ! function_exists( 'mvr_get_admin_dashboard_data' ) ) {
	/**
	 * Get Admin Dashboard Data.
	 *
	 * @since 1.0.0
	 */
	function mvr_get_admin_dashboard_data() {
		$order_args = array(
			'admin_commission' => 0,
			'item_count'       => 0,
			'order_count'      => 0,
			'total'            => 0,
			'vendor_earnings'  => 0,
		);

		$mvr_orders_obj = mvr_get_orders(
			array(
				'date_after' => strtotime( '-7 DAY' ),
				'status'     => array( 'processing', 'completed' ),
			)
		);

		if ( $mvr_orders_obj->has_order ) {
			foreach ( $mvr_orders_obj->orders as $mvr_order_obj ) {
				if ( ! mvr_is_order( $mvr_order_obj ) ) {
					continue;
				}

				$order_obj = wc_get_order( $mvr_order_obj->get_order_id() );

				if ( ! is_a( $order_obj, 'WC_Order' ) ) {
					continue;
				}

				$order_vendors = $order_obj->get_meta( 'mvr_vendor_id', false );

				if ( mvr_check_is_array( $order_vendors ) ) {
					$vendor_ids = wp_list_pluck( $order_vendors, 'value' );

					foreach ( $vendor_ids as $vendor_id ) {
						if ( empty( $vendor_id ) ) {
							continue;
						}

						$vendor_obj = mvr_get_vendor( $vendor_id );

						if ( ! mvr_is_vendor( $vendor_obj ) ) {
							continue;
						}

						$order_details = mvr_get_vendor_order_details( $vendor_obj, $order_obj );

						if ( ! mvr_check_is_array( $order_details ) ) {
							continue;
						}

						$order_args['item_count'] += $order_details['item_count'];
					}
				}

				$order_args['total']       += $order_obj->get_total();
				$order_args['order_count'] += 1;
			}
		}

		$commission_objs = mvr_get_commissions(
			array(
				'status'     => 'paid',
				'date_after' => strtotime( '-7 DAY' ),
			)
		);

		if ( $commission_objs->has_commission ) {
			foreach ( $commission_objs->commissions as $commission_obj ) {
				$order_args['admin_commission'] += $commission_obj->get_amount();
				$order_args['vendor_earnings']  += $commission_obj->get_vendor_amount();
			}
		}

		return $order_args;
	}
}

if ( ! function_exists( 'mvr_get_commission_overview_details' ) ) {

	/**
	 * Get Commission Overview Details
	 *
	 * @since 1.0.0
	 * @param Integer $commission_id Commission ID.
	 * @return Array
	 * */
	function mvr_get_commission_overview_details( $commission_id ) {
		if ( empty( $commission_id ) ) {
			return;
		}

		$commission_obj = mvr_get_commission( $commission_id );

		if ( ! mvr_is_commission( $commission_obj ) ) {
			return;
		}

		$vendor_obj = $commission_obj->get_vendor();

		$overview_data = array(
			'_id'     => array(
				'label' => esc_html__( 'ID', 'multi-vendor-marketplace' ),
				'value' => '<a class="row-title" href="' . esc_url( $vendor_obj->get_admin_edit_url() ) . '" class="vendor-view"><strong>#' . $commission_obj->get_id() . '</strong></a>',
			),
			'_vendor' => array(
				'label' => esc_html__( 'Vendor', 'multi-vendor-marketplace' ),
				'value' => ( mvr_is_vendor( $vendor_obj ) ) ? esc_html( $vendor_obj->get_name() ) : esc_html_e( 'Not found', 'multi-vendor-marketplace' ),
			),
			'_shop'   => array(
				'label' => esc_html__( 'Store Name', 'multi-vendor-marketplace' ),
				'value' => ( mvr_is_vendor( $vendor_obj ) ) ? esc_html( $vendor_obj->get_shop_name() ) : esc_html_e( 'Not found', 'multi-vendor-marketplace' ),
			),
		);

		if ( 'withdraw' === $commission_obj->get_source_from() ) {
			$from         = esc_html__( 'Withdraw', 'multi-vendor-marketplace' );
			$withdraw_obj = $commission_obj->get_withdraw();

			if ( ! mvr_is_withdraw( $withdraw_obj ) ) {
				$from_id = '#' . esc_attr( $commission_obj->get_source_id() );
			} else {
				$from_id = '<a href="' . esc_url( $withdraw_obj->get_admin_view_url() ) . '">#' . esc_attr( $withdraw_obj->get_id() ) . '</a>';
			}
		} else {
			$from      = esc_html__( 'Order', 'multi-vendor-marketplace' );
			$order_obj = $commission_obj->get_order();

			if ( ! is_a( $order_obj, 'WC_Order' ) ) {
				$from_id = '#' . esc_attr( $commission_obj->get_source_id() );
			} else {
				$from_id = '<a href="' . esc_url( $order_obj->get_edit_order_url() ) . '">#' . esc_attr( $order_obj->get_id() ) . '</a>';
			}
		}

		$overview_data['_from'] = array(
			'label' => esc_html__( 'From', 'multi-vendor-marketplace' ),
			'value' => $from,
		);

		$overview_data['_from_id'] = array(
			'label' => esc_html__( 'From ID', 'multi-vendor-marketplace' ),
			'value' => $from_id,
		);

		$overview_data['_status'] = array(
			'label' => esc_html__( 'Status', 'multi-vendor-marketplace' ),
			'value' => mvr_get_commission_status_name( $commission_obj->get_status() ),
		);

		$overview_data['_amount'] = array(
			'label' => esc_html__( 'Amount', 'multi-vendor-marketplace' ),
			'value' => wc_price( $commission_obj->get_amount() ),
		);

		$overview_data['_vendor_amount'] = array(
			'label' => esc_html__( 'Vendor Earning', 'multi-vendor-marketplace' ),
			'value' => wc_price( $commission_obj->get_vendor_amount() ),
		);

		$overview_data['_date'] = array(
			'label' => esc_html__( 'Date and Time', 'multi-vendor-marketplace' ),
			'value' => $commission_obj->get_date_created()->date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ),
		);

		return $overview_data;
	}
}

if ( ! function_exists( 'mvr_get_commission_settings_details' ) ) {

	/**
	 * Get Commission Settings Details
	 *
	 * @since 1.0.0
	 * @param Integer $commission_id Commission ID.
	 * @return Array
	 * */
	function mvr_get_commission_settings_details( $commission_id ) {
		if ( empty( $commission_id ) ) {
			return;
		}

		$commission_obj = mvr_get_commission( $commission_id );

		if ( ! mvr_is_commission( $commission_obj ) ) {
			return;
		}

		$settings = $commission_obj->get_settings();

		if ( 'withdraw' === $commission_obj->get_source_from() ) {
			$settings_data = array(
				'_from'  => array(
					'label' => esc_html__( 'Withdraw Settings From', 'multi-vendor-marketplace' ),
					'value' => mvr_commission_from_options( $settings['from'] ),
				),
				'_type'  => array(
					'label' => esc_html__( 'Withdraw Charge Type', 'multi-vendor-marketplace' ),
					'value' => mvr_withdraw_charge_type_options( $settings['charge_type'] ),
				),
				'_value' => array(
					'label' => esc_html__( 'Withdraw Charge', 'multi-vendor-marketplace' ),
					'value' => ( '1' === $settings['charge_type'] ) ? wc_price( $settings['charge_value'] ) : $settings['charge_value'] . '%',
				),
			);
		} else {
			$settings_data = array(
				'_criteria' => array(
					'label' => esc_html__( 'Criteria', 'multi-vendor-marketplace' ),
					'value' => mvr_commission_criteria_options( $settings['criteria'] ),
				),
			);

			if ( '1' !== $settings['criteria'] ) {
				$settings_data['_criteria_value'] = array(
					'label' => esc_html__( 'Criteria Value', 'multi-vendor-marketplace' ),
					'value' => ( '4' === $settings['criteria'] ) ? wc_price( $settings['criteria_value'] ) : $settings['criteria_value'],
				);
			}

			$settings_data['_type']          = array(
				'label' => esc_html__( 'Commission Type', 'multi-vendor-marketplace' ),
				'value' => mvr_commission_type_options( $settings['type'] ),
			);
			$settings_data['_value']         = array(
				'label' => esc_html__( 'Commission Value', 'multi-vendor-marketplace' ),
				'value' => ( '1' === $settings['type'] ) ? wc_price( $settings['value'] ) : $settings['value'] . '%',
			);
			$settings_data['_coupon']        = array(
				'label' => esc_html__( 'Calculate Commission after Applying Admin Created Coupons', 'multi-vendor-marketplace' ),
				'value' => ( $settings['after_coupon'] ) ? esc_html__( 'Yes', 'multi-vendor-marketplace' ) : esc_html__( 'No', 'multi-vendor-marketplace' ),
			);
			$settings_data['_vendor_coupon'] = array(
				'label' => esc_html__( 'Calculate Commission after Applying Vendor Created Coupons', 'multi-vendor-marketplace' ),
				'value' => ( $settings['after_vendor_coupon'] ) ? esc_html__( 'Yes', 'multi-vendor-marketplace' ) : esc_html__( 'No', 'multi-vendor-marketplace' ),
			);
			$settings_data['_tax']           = array(
				'label' => esc_html__( 'Tax Calculation', 'multi-vendor-marketplace' ),
				'value' => mvr_tax_type_options( $settings['tax_to'] ),
			);
		}

		return $settings_data;
	}
}
