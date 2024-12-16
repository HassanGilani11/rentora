<?php
/**
 * Admin Payout.
 *
 * @package Multi Vendor/Admin
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Admin_Payout' ) ) {

	/**
	 * MVR_Admin_Payout Class.
	 * */
	class MVR_Admin_Payout {

		/**
		 * Class initialization.
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
					self::render_payout();
					break;
			}
		}

		/**
		 * Output the Fund WP List Table.
		 *
		 * @since 1.0.0
		 * */
		public static function render_payout() {
			if ( ! class_exists( 'MVR_Admin_List_Table_Payout' ) ) {
				require_once MVR_PLUGIN_PATH . '/includes/admin/list-tables/class-mvr-admin-list-table-payout.php';
			}

			$post_table = new MVR_Admin_List_Table_Payout();
			$post_table->prepare_items();

			include_once MVR_PLUGIN_PATH . '/includes/admin/views/html-payout.php';
		}
	}
}
