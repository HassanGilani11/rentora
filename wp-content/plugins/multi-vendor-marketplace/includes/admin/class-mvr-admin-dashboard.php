<?php
/**
 * Vendor Admin Dashboard
 *
 * @package  Multi-Vendor\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Admin_Dashboard' ) ) {
	/**
	 * Handle Admin dashboard.
	 *
	 * @class MVR_Admin_Dashboard
	 * @package Class
	 */
	class MVR_Admin_Dashboard {
		/**
		 * Output Dashboard.
		 *
		 * Handles the display of the main Multi-Vendor for WooCommerce dashboard page in admin.
		 *
		 * @since 1.0.0
		 */
		public static function output() {
			/**
			 * Admin dashboard Start.
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_admin_dashboard_start' );

			$data = mvr_get_admin_dashboard_data();

			include 'views/html-admin-dashboard.php';

			/**
			 * Admin dashboard End.
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_admin_dashboard_end' );
		}
	}
}
