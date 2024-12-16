<?php
/**
 * Admin Withdraw.
 *
 * @package Multi Vendor/Admin
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Admin_Withdraw' ) ) {

	/**
	 * MVR_Admin_Withdraw Class.
	 * */
	class MVR_Admin_Withdraw {

		/**
		 * Class initialization.
		 *
		 * @since 1.0.0
		 * */
		public static function init() {
			// Add Withdraw.
			add_action( 'admin_footer', __CLASS__ . '::add_withdraw_template' );
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
					self::render_withdraw();
					break;
			}
		}

		/**
		 * Output the Withdraw WP List Table.
		 *
		 * @since 1.0.0
		 * */
		public static function render_withdraw() {
			if ( ! class_exists( 'MVR_Admin_List_Table_Withdraw' ) ) {
				require_once MVR_PLUGIN_PATH . '/includes/admin/list-tables/class-mvr-admin-list-table-withdraw.php';
			}

			$post_table = new MVR_Admin_List_Table_Withdraw();
			$post_table->prepare_items();

			include_once MVR_PLUGIN_PATH . '/includes/admin/views/html-withdraw.php';
		}

		/**
		 * Add Withdraw Template
		 *
		 * @since 1.0.0
		 */
		public static function add_withdraw_template() {
			?>
			<script type="text/template" id="tmpl-mvr-modal-add-withdraw">
				<?php include_once MVR_ABSPATH . 'includes/admin/views/html-admin-add-withdraw.php'; ?>
			</script>
			<?php
		}
	}

	MVR_Admin_Withdraw::init();
}
