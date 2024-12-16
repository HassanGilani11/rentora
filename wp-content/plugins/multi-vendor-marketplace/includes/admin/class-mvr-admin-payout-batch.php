<?php
/**
 * Admin Payout.
 *
 * @package Multi Vendor/Admin
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Admin_Payout_Batch' ) ) {

	/**
	 * MVR_Admin_Payout_Batch Class.
	 * */
	class MVR_Admin_Payout_Batch {

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
					self::render_payout_batch();
					break;
			}
		}

		/**
		 * Output the Fund WP List Table.
		 *
		 * @since 1.0.0
		 * */
		public static function render_payout_batch() {
			if ( ! class_exists( 'MVR_Admin_List_Table_Payout_Batch' ) ) {
				require_once MVR_PLUGIN_PATH . '/includes/admin/list-tables/class-mvr-admin-list-table-payout-batch.php';
			}

			$post_table = new MVR_Admin_List_Table_Payout_Batch();
			$post_table->prepare_items();

			include_once MVR_PLUGIN_PATH . '/includes/admin/views/html-payout-batch.php';
		}
	}
}
