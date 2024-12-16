<?php
/**
 * Admin Notification.
 *
 * @package Multi Vendor/Admin
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Admin_Notification' ) ) {

	/**
	 * MVR_Admin_Notification Class.
	 * */
	class MVR_Admin_Notification {

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
					self::render_notification();
					break;
			}
		}

		/**
		 * Output the notification WP List Table.
		 *
		 * @since 1.0.0
		 * */
		public static function render_notification() {
			if ( ! class_exists( 'MVR_Admin_List_Table_Notification' ) ) {
				require_once MVR_PLUGIN_PATH . '/includes/admin/list-tables/class-mvr-admin-list-table-notification.php';
			}

			$post_table = new MVR_Admin_List_Table_Notification();
			$post_table->prepare_items();

			include_once MVR_PLUGIN_PATH . '/includes/admin/views/html-notification.php';
		}
	}
}
