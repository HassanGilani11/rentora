<?php
/**
 * Admin Enquiry.
 *
 * @package Multi Vendor/Admin
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Admin_Enquiry' ) ) {

	/**
	 * MVR_Admin_Enquiry Class.
	 * */
	class MVR_Admin_Enquiry {

		/**
		 * Class initialization.
		 *
		 * @since 1.0.0
		 * */
		public static function init() {
		}

		/**
		 * Output Fund Page.
		 *
		 * @since 1.0.0
		 * */
		public static function output() {
			global $current_action;

			switch ( $current_action ) {
				default:
					self::render_enquiry();
					break;
			}
		}

		/**
		 * Output the enquiry WP List Table.
		 *
		 * @since 1.0.0
		 * */
		public static function render_enquiry() {
			if ( ! class_exists( 'MVR_Admin_List_Table_Enquiry' ) ) {
				require_once MVR_PLUGIN_PATH . '/includes/admin/list-tables/class-mvr-admin-list-table-enquiry.php';
			}

			$post_table = new MVR_Admin_List_Table_Enquiry();
			$post_table->prepare_items();

			include_once MVR_PLUGIN_PATH . '/includes/admin/views/html-enquiry.php';
		}
	}
}
