<?php
/**
 * Admin Transaction.
 *
 * @package Multi Vendor/Admin
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Admin_Transaction' ) ) {

	/**
	 * MVR_Admin_Transaction Class.
	 * */
	class MVR_Admin_Transaction {

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
					self::render_transaction();
					break;
			}
		}

		/**
		 * Output the Fund WP List Table.
		 *
		 * @since 1.0.0
		 * */
		public static function render_transaction() {
			if ( ! class_exists( 'MVR_Admin_List_Table_Transaction' ) ) {
				require_once MVR_PLUGIN_PATH . '/includes/admin/list-tables/class-mvr-admin-list-table-transaction.php';
			}

			$post_table = new MVR_Admin_List_Table_Transaction();
			$post_table->prepare_items();

			include_once MVR_PLUGIN_PATH . '/includes/admin/views/html-transaction.php';
		}
	}
}
