<?php
/**
 * Multi Vendor Template Hooks
 *
 * Action/filter hooks used for Multi vendor functions/templates.
 *
 * @package Multi-Vendor for WooCommerce\Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if directly accessed.
}

// Dashboard.
add_action( 'mvr_before_dashboard', 'mvr_display_vendor_notices' );
add_action( 'mvr_dashboard_content', 'mvr_dashboard_content' );

// Navigation.
add_action( 'mvr_dashboard_navigation', 'mvr_dashboard_top_menu' );
add_action( 'mvr_dashboard_navigation', 'mvr_dashboard_side_menu' );

// Products.
add_action( 'mvr_dashboard_mvr-products_endpoint', 'mvr_dashboard_products' );
add_action( 'mvr_dashboard_mvr-add-product_endpoint', 'mvr_dashboard_add_product' );
add_action( 'mvr_dashboard_mvr-edit-product_endpoint', 'mvr_dashboard_edit_product' );
add_action( 'mvr_before_dashboard_products', 'mvr_dashboard_add_product_button' );

// Orders.
add_action( 'mvr_dashboard_mvr-orders_endpoint', 'mvr_dashboard_orders' );
add_action( 'mvr_dashboard_mvr-view-order_endpoint', 'mvr_dashboard_view_order' );
add_action( 'mvr_view_order', 'mvr_order_details_table' );

// Coupons.
add_action( 'mvr_dashboard_mvr-coupons_endpoint', 'mvr_dashboard_coupons' );
add_action( 'mvr_dashboard_mvr-add-coupon_endpoint', 'mvr_dashboard_add_coupon' );
add_action( 'mvr_dashboard_mvr-edit-coupon_endpoint', 'mvr_dashboard_edit_coupon' );
add_action( 'mvr_before_dashboard_coupons', 'mvr_dashboard_add_coupon_button' );

// Staff.
add_action( 'mvr_dashboard_mvr-staff_endpoint', 'mvr_dashboard_staff' );
add_action( 'mvr_dashboard_mvr-add-staff_endpoint', 'mvr_dashboard_add_staff' );
add_action( 'mvr_dashboard_mvr-edit-staff_endpoint', 'mvr_dashboard_edit_staff' );
add_action( 'mvr_before_dashboard_staff', 'mvr_dashboard_add_staff_button' );

// Withdraw.
add_action( 'mvr_before_dashboard_withdraws', 'mvr_dashboard_add_withdraw_button' );
add_action( 'mvr_before_dashboard_withdraws', 'mvr_withdraw_balance_display' );
add_action( 'mvr_before_withdraw_form', 'mvr_validate_payment_method' );
add_action( 'mvr_before_withdraw_form', 'mvr_withdraw_balance_display' );
add_action( 'mvr_dashboard_mvr-withdraw_endpoint', 'mvr_dashboard_withdraw' );
add_action( 'mvr_dashboard_mvr-add-withdraw_endpoint', 'mvr_dashboard_add_withdraw' );

// Transaction.
add_action( 'mvr_before_dashboard_transactions', 'mvr_transaction_balance_display' );
add_action( 'mvr_dashboard_mvr-transaction_endpoint', 'mvr_dashboard_transaction' );

// Enquiry.
add_action( 'mvr_dashboard_mvr-enquiry_endpoint', 'mvr_dashboard_enquiry' );
add_action( 'mvr_dashboard_mvr-reply-enquiry_endpoint', 'mvr_dashboard_reply_enquiry' );

add_action( 'mvr_dashboard_mvr-customers_endpoint', 'mvr_dashboard_customers' );
add_action( 'mvr_dashboard_mvr-reviews_endpoint', 'mvr_dashboard_reviews' );
add_action( 'mvr_dashboard_mvr-duplicate_endpoint', 'mvr_dashboard_duplicate_products' );
add_action( 'mvr_dashboard_mvr-payments_endpoint', 'mvr_dashboard_payments' );
add_action( 'mvr_dashboard_mvr-payout_endpoint', 'mvr_dashboard_payout' );
add_action( 'mvr_dashboard_mvr-profile_endpoint', 'mvr_dashboard_profile' );
add_action( 'mvr_dashboard_mvr-address_endpoint', 'mvr_dashboard_address' );
add_action( 'mvr_dashboard_mvr-social-links_endpoint', 'mvr_dashboard_social_links' );
add_action( 'mvr_dashboard_mvr-notification_endpoint', 'mvr_dashboard_notification' );

// Stores.
add_action( 'mvr_stores_content', 'mvr_stores_content' );
add_action( 'mvr_stores_mvr-store_endpoint', 'mvr_single_store_content' );
add_action( 'mvr_single_store_contents', 'mvr_single_store_contents' );
add_action( 'mvr_single_store_tab_content', 'mvr_single_store_tab_contents', 10, 2 );
add_action( 'mvr_no_stores_found', 'mvr_no_stores_found' );
add_action( 'mvr_stores_loop_content', 'mvr_stores_loop_content' );

// Stores loop.
add_action( 'mvr_before_store_loop_item', 'mvr_template_loop_store_link_open' );
add_action( 'mvr_before_store_loop_item_name', 'mvr_template_loop_store_banner' );
add_action( 'mvr_before_store_loop_item_name', 'mvr_template_loop_store_logo' );
add_action( 'mvr_store_loop_item_name', 'mvr_template_loop_store_details' );
add_action( 'mvr_after_store_loop_item', 'mvr_template_loop_store_link_close', 5 );

// Single Store.
add_action( 'mvr_single_store_header', 'mvr_single_store_header' );
add_action( 'mvr_single_store_product_filters', 'mvr_single_store_product_filters', 10, 2 );
add_action( 'mvr_single_store_after_products_loop', 'mvr_single_store_product_pagination' );
