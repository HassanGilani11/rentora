<?php
/**
 * Admin Commission.
 *
 * @package Multi Vendor/Admin
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Admin_Commission' ) ) {

	/**
	 * MVR_Admin_Commission Class.
	 * */
	class MVR_Admin_Commission {

		/**
		 * Class initialization.
		 * */
		public static function init() {
			// Preview Commission.
			add_action( 'admin_footer', __CLASS__ . '::commission_preview_template' );
			// Add Commission.
			add_action( 'admin_footer', __CLASS__ . '::add_commission_template' );
		}

		/**
		 * Output Fund Page.
		 *
		 * @since 1.0.0
		 * */
		public static function output() {
			global $current_action, $commission_id;

			if ( isset( $_REQUEST['_mvr_nonce'] ) ) {
				$nonce = sanitize_key( wp_unslash( $_REQUEST['_mvr_nonce'] ) );

				if ( ! wp_verify_nonce( $nonce, 'mvr-admin-commission' ) ) {
					$current_action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
					$commission_id  = isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : '';
				}
			}

			switch ( $current_action ) {
				case 'add_new_commission':
					self::render_new_commission();
					break;
				case 'view':
					self::render_view_commission( $commission_id );
					break;
				default:
					self::render_commission();
					break;
			}
		}

		/**
		 * Output the Add New Commission.
		 *
		 * @since 1.0.0
		 * */
		public static function render_new_commission() {
			include_once MVR_ABSPATH . 'includes/admin/views/html-admin-add-commission.php';
		}

		/**
		 * Output the View Commission.
		 *
		 * @since 1.0.0
		 * @param Integer $commission_id Commission ID.
		 * */
		public static function render_view_commission( $commission_id ) {
			if ( empty( $commission_id ) ) {
				return;
			}

			$commission_obj = mvr_get_commission( $commission_id );

			if ( ! mvr_is_commission( $commission_obj ) ) {
				return;
			}

			$overview_data = mvr_get_commission_overview_details( $commission_id );
			$settings_data = mvr_get_commission_settings_details( $commission_id );

			include_once MVR_ABSPATH . 'includes/admin/views/html-admin-view-commission.php';
		}

		/**
		 * Output the Fund WP List Table.
		 *
		 * @since 1.0.0
		 * */
		public static function render_commission() {
			if ( ! class_exists( 'MVR_Admin_List_Table_Commission' ) ) {
				require_once MVR_PLUGIN_PATH . '/includes/admin/list-tables/class-mvr-admin-list-table-commission.php';
			}

			$post_table = new MVR_Admin_List_Table_Commission();
			$post_table->prepare_items();

			include_once MVR_PLUGIN_PATH . '/includes/admin/views/html-commission.php';
		}

		/**
		 * Returns the HTML for the order preview template.
		 *
		 * @since 1.0.0
		 */
		public static function commission_preview_template() {
			?> 
			<script type="text/template" id="tmpl-mvr-modal-view-commission"> 
				<?php include_once MVR_ABSPATH . 'includes/admin/views/html-admin-commission-preview.php'; ?>
			</script> 
			<?php
		}

		/**
		 * Add Commission Template
		 *
		 * @since 1.0.0
		 */
		public static function add_commission_template() {
			?>
			<script type="text/template" id="tmpl-mvr-modal-add-commission">
				<?php include_once MVR_ABSPATH . 'includes/admin/views/html-admin-add-commission.php'; ?>
			</script>
			<?php
		}
	}

	MVR_Admin_Commission::init();
}
