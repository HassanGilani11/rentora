<?php
/**
 * Vendor Admin Handler
 *
 * @package  Multi-Vendor\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Admin' ) ) {

	/**
	 * Multi Vendor Admin
	 *
	 * @class MVR_Admin
	 * @package Class
	 */
	class MVR_Admin {

		/**
		 * Success messages.
		 *
		 * @var array
		 */
		protected static $success_messages = array();

		/**
		 * Error messages.
		 *
		 * @var array
		 */
		protected static $error_messages = array();

		/**
		 * Init MVR_Admin.
		 *
		 * @since 1.0.0
		 */
		public static function init() {
			add_action( 'init', __CLASS__ . '::includes', 10 );
			add_action( 'admin_init', __CLASS__ . '::admin_action' );
			add_action( 'admin_head', __CLASS__ . '::hide_menus' );
			add_action( 'admin_menu', __CLASS__ . '::admin_menus', 10 );
			add_filter( 'parent_file', __CLASS__ . '::assign_default_menu', 99 );
			add_filter( 'submenu_file', __CLASS__ . '::assign_default_submenu', 99 );
			add_action( 'all_admin_notices', __CLASS__ . '::render_tabs', 5 );

			// Success/Error handling.
			add_action( 'admin_notices', __CLASS__ . '::output_messages' );
			add_action( 'admin_notices', 'mvr_display_admin_notices' );
			add_action( 'shutdown', __CLASS__ . '::save_messages' );

			add_filter( 'woocommerce_new_customer_data', __CLASS__ . '::set_vendor_role' );
			add_filter( 'product_type_selector', __CLASS__ . '::set_vendor_product_type_selector', 999 );
			add_filter( 'user_has_cap', __CLASS__ . '::check_user_capability', 10, 4 );
			add_filter( 'woocommerce_product_data_tabs', __CLASS__ . '::check_inventory_capability' );
		}

		/**
		 * Hide Admin Menus for vendor and staff.
		 *
		 * @since 1.0.0
		 */
		public static function hide_menus() {
			global $menu, $submenu;

			$allow_menu_keys     = array( 'edit_products', 'edit_shop_coupons' );
			$allow_sub_menu_keys = array( 'edit.php?post_type=product' );

			if ( mvr_check_user_as_vendor_or_staff() ) {
				foreach ( $menu as $key => $value ) {
					if ( ! isset( $value[1] ) ) {
						continue;
					}

					if ( ! in_array( $value[1], $allow_menu_keys, true ) ) {
						unset( $menu[ $key ] );
					}
				}

				foreach ( $submenu as $key => $value ) {
					if ( ! in_array( $key, $allow_sub_menu_keys, true ) ) {
						unset( $submenu[ $key ] );
					}
				}
			}
		}

		/**
		 * Include any classes we need within admin.
		 *
		 * @since 1.0.0
		 */
		public static function includes() {
			include_once 'mvr-admin-functions.php';
			include_once 'class-mvr-admin-post-types.php';
			include_once 'class-mvr-admin-meta-boxes.php';
			include_once 'class-mvr-admin-dashboard.php';
			include_once 'class-mvr-admin-commission.php';
			include_once 'class-mvr-admin-withdraw.php';
			include_once 'class-mvr-admin-transaction.php';
			include_once 'class-mvr-admin-payout.php';
			include_once 'class-mvr-admin-payout-batch.php';
			include_once 'class-mvr-admin-notification.php';
			include_once 'class-mvr-admin-review.php';
			include_once 'class-mvr-admin-enquiry.php';
			include_once 'class-mvr-admin-settings.php';
			include_once 'class-mvr-admin-assets.php';
			include_once 'class-mvr-admin-exporters.php';
		}

		/**
		 * Check User Capability
		 *
		 * @since 1.0.0
		 * @param Array   $all_caps All capabilities.
		 * @param Array   $caps    Capabilities.
		 * @param Array   $args    Arguments.
		 * @param WP_User $user_obj User Object.
		 * @return Array
		 */
		public static function check_user_capability( $all_caps, $caps, $args, $user_obj ) {
			if ( ! mvr_check_user_as_staff() ) {
				return $all_caps;
			}

			if ( isset( $caps[0] ) ) {
				switch ( $caps[0] ) {
					case 'edit_others_products':
					case 'edit_others_shop_coupons':
						$user_id = $user_obj->ID;
						$post_id = isset( $_REQUEST['post'] ) ? absint( wp_unslash( $_REQUEST['post'] ) ) : '';

						if ( empty( $post_id ) ) {
							return $all_caps;
						}

						$vendor_id = get_post_meta( $post_id, '_mvr_vendor', true );

						if ( empty( $vendor_id ) ) {
							return $all_caps;
						}

						$vendor_obj = mvr_get_vendor( $vendor_id );

						if ( ! $vendor_obj ) {
							return $all_caps;
						}

						$staff_obj = mvr_get_current_staff_object( $user_id );

						if ( ! $staff_obj ) {
							return $all_caps;
						}

						if ( $staff_obj->get_vendor_id() === $vendor_obj->get_id() ) {
							foreach ( $caps as $cap ) {
								$all_caps[ $cap ] = true;
							}
						}
						break;
				}
			}

			return $all_caps;
		}

		/**
		 * Admin Action
		 *
		 * @since 1.0.0
		 */
		public static function admin_action() {
			if ( ! isset( $_REQUEST['action'] ) || ! isset( $_GET['_mvr_nonce'] ) || ! isset( $_GET['page'] ) || ! isset( $_GET['tab'] ) ) {
				return;
			}

			$action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) );
			$nonce  = sanitize_key( wp_unslash( $_GET['_mvr_nonce'] ) );
			$page   = sanitize_key( wp_unslash( $_GET['page'] ) );
			$tab    = sanitize_key( wp_unslash( $_GET['tab'] ) );

			if ( ! wp_verify_nonce( $nonce, "mvr-{$action}" ) ) {
				return;
			}

			switch ( $action ) {
				case 'install_pages':
					$redirect = mvr_get_settings_page_url( array( 'tab' => 'advanced' ) );
					MVR_Install::create_pages();

					self::add_success( esc_html__( 'Default Multi Vendor Pages Successfully Installed', 'multi-vendor-marketplace' ) );

					wp_safe_redirect( $redirect );
					exit;
				case 'verify_db_tables':
					$redirect = mvr_get_settings_page_url( array( 'tab' => 'advanced' ) );

					if ( ! mvr_check_is_array( MVR_Install::verify_base_tables( true ) ) ) {
						self::add_success( esc_html__( 'Database verified successfully', 'multi-vendor-marketplace' ) );
					}

					wp_safe_redirect( $redirect );
					exit;
			}
		}

		/**
		 * Add admin menu pages.
		 *
		 * @since 1.0.0
		 */
		public static function admin_menus() {
			add_submenu_page( 'woocommerce', __( 'Dashboard', 'multi-vendor-marketplace' ), __( 'Multi Vendor', 'multi-vendor-marketplace' ), 'manage_woocommerce', 'mvr_dashboard', 'MVR_Admin_Dashboard::output' );
			add_submenu_page( 'woocommerce', __( 'Commission', 'multi-vendor-marketplace' ), __( 'Multi Vendor', 'multi-vendor-marketplace' ), 'manage_woocommerce', 'mvr_commission', 'MVR_Admin_Commission::output' );
			add_submenu_page( 'woocommerce', __( 'Withdraw', 'multi-vendor-marketplace' ), __( 'Multi Vendor', 'multi-vendor-marketplace' ), 'manage_woocommerce', 'mvr_withdraw', 'MVR_Admin_Withdraw::output' );
			add_submenu_page( 'woocommerce', __( 'Transaction', 'multi-vendor-marketplace' ), __( 'Multi Vendor', 'multi-vendor-marketplace' ), 'manage_woocommerce', 'mvr_transaction', 'MVR_Admin_Transaction::output' );
			add_submenu_page( 'woocommerce', __( 'Payout', 'multi-vendor-marketplace' ), __( 'Multi Vendor', 'multi-vendor-marketplace' ), 'manage_woocommerce', 'mvr_payout', 'MVR_Admin_Payout::output' );
			add_submenu_page( 'woocommerce', __( 'Notification', 'multi-vendor-marketplace' ), __( 'Multi Vendor', 'multi-vendor-marketplace' ), 'manage_woocommerce', 'mvr_notification', 'MVR_Admin_Notification::output' );
			add_submenu_page( 'woocommerce', __( 'Enquiry', 'multi-vendor-marketplace' ), __( 'Multi Vendor', 'multi-vendor-marketplace' ), 'manage_woocommerce', 'mvr_enquiry', 'MVR_Admin_Enquiry::output' );
			add_submenu_page( 'woocommerce', __( 'Store Review', 'multi-vendor-marketplace' ), __( 'Multi Vendor', 'multi-vendor-marketplace' ), 'manage_woocommerce', 'mvr_store_review', 'MVR_Admin_Review::output' );
			add_submenu_page( 'woocommerce', __( 'Settings', 'multi-vendor-marketplace' ), __( 'Multi Vendor', 'multi-vendor-marketplace' ), 'manage_woocommerce', 'mvr_settings', 'MVR_Admin_Settings::output' );
		}

		/**
		 * Assign default Menu.
		 *
		 * @since 1.0.0
		 * @param String $parent_file Parent File.
		 * @return String
		 */
		public static function assign_default_menu( $parent_file ) {
			global $submenu;

			$current_screen = get_current_screen();
			$screen_id      = str_replace( 'edit-', '', $current_screen->id );
			$comment_id     = isset( $_GET['c'] ) ? absint( wp_unslash( $_GET['c'] ) ) : '';

			if ( ! empty( $submenu['woocommerce'] ) ) {
				foreach ( $submenu['woocommerce'] as $key => $item ) {
					if ( isset( $item[2] ) && 'mvr_dashboard' !== $item[2] && mvr_check_is_screen( $item[2] ) ) {
						unset( $submenu['woocommerce'][ $key ] );
					}
				}
			}

			if ( $comment_id ) {
				if ( isset( $screen_id, $comment_id ) && 'comment' === $screen_id ) {
					$comment = get_comment( $comment_id );

					if ( isset( $comment->comment_parent ) && $comment->comment_parent > 0 ) {
						$comment = get_comment( $comment->comment_parent );
					}

					if ( isset( $comment->comment_post_ID ) && get_post_type( $comment->comment_post_ID ) === 'mvr_vendor' ) {
						$parent_file = 'woocommerce';

						return $parent_file;
					}
				}
			}

			if ( $current_screen && mvr_check_is_screen( $screen_id ) && 'comment' !== $screen_id ) {
				$parent_file = 'woocommerce';
			}

			return $parent_file;
		}

		/**
		 * Assign default submenu.
		 *
		 * @since 1.0.0
		 * @param String $submenu_file Submenu.
		 * @return string
		 */
		public static function assign_default_submenu( $submenu_file ) {
			$current_screen = get_current_screen();
			$screen_id      = str_replace( 'edit-', '', $current_screen->id );
			$comment_id     = isset( $_GET['c'] ) ? absint( wp_unslash( $_GET['c'] ) ) : '';
			$is_mvr_comment = false;

			if ( isset( $current_screen->id, $comment_id ) && 'comment' === $current_screen->id ) {
				$comment = get_comment( $comment_id );

				if ( isset( $comment->comment_parent ) && $comment->comment_parent > 0 ) {
					$comment = get_comment( $comment->comment_parent );
				}

				if ( isset( $comment->comment_post_ID ) && get_post_type( $comment->comment_post_ID ) === 'mvr_vendor' ) {
					$submenu_file = 'mvr_dashboard';

					return $submenu_file;
				}
			}

			if ( $current_screen && mvr_check_is_screen( $screen_id ) && 'comment' !== $screen_id ) {
				$submenu_file = 'mvr_dashboard';
			}

			return $submenu_file;
		}

		/**
		 * Render Admin Tabs.
		 *
		 * @since 1.0.0
		 */
		public static function render_tabs() {
			$current_screen = get_current_screen();

			if ( ! is_object( $current_screen ) ) {
				return;
			}

			$screen_id = str_replace( 'edit-', '', $current_screen->id );

			if ( ! $current_screen || ! mvr_check_is_screen( $screen_id ) ) {
				return;
			}

			if ( 'comment' === $screen_id ) {
				$comment_id = isset( $_GET['c'] ) ? absint( wp_unslash( $_GET['c'] ) ) : '';
				$comment    = get_comment( $comment_id );

				if ( isset( $comment->comment_parent ) && $comment->comment_parent > 0 ) {
					$comment = get_comment( $comment->comment_parent );
				}

				if ( ! isset( $comment->comment_post_ID ) || get_post_type( $comment->comment_post_ID ) !== 'mvr_vendor' ) {
					return;
				}
			}

			$tabs        = self::admin_tabs();
			$current_tab = mvr_get_current_menu_tab( $screen_id );

			/* Include admin html settings. */
			include_once 'views/html-settings-tabs.php';
		}

		/**
		 * Settings page tabs.
		 *
		 * @since 1.0.0
		 * */
		public static function admin_tabs() {
			/**
			 * Filter for Settings Tab.
			 *
			 * @since 1.0.0
			 * */
			return apply_filters(
				'mvr_admin_tabs',
				array(
					'dashboard'    => array(
						'title' => esc_html__( 'Dashboard', 'multi-vendor-marketplace' ),
						'url'   => admin_url( 'admin.php?page=mvr_dashboard' ),
					),
					'vendor'       => array(
						'title' => esc_html__( 'Vendors', 'multi-vendor-marketplace' ),
						'url'   => admin_url( 'edit.php?post_type=mvr_vendor' ),
					),
					'commission'   => array(
						'title' => esc_html__( 'Earnings', 'multi-vendor-marketplace' ),
						'url'   => admin_url( 'admin.php?page=mvr_commission' ),
					),
					'withdraw'     => array(
						'title' => esc_html__( 'Withdrawal Requests', 'multi-vendor-marketplace' ),
						'url'   => admin_url( 'admin.php?page=mvr_withdraw' ),
					),
					'transaction'  => array(
						'title' => esc_html__( 'Transactions', 'multi-vendor-marketplace' ),
						'url'   => admin_url( 'admin.php?page=mvr_transaction' ),
					),
					'payout'       => array(
						'title' => esc_html__( 'Payout', 'multi-vendor-marketplace' ),
						'url'   => admin_url( 'admin.php?page=mvr_payout' ),
					),
					'notification' => array(
						'title' => esc_html__( 'Notification', 'multi-vendor-marketplace' ),
						'url'   => admin_url( 'admin.php?page=mvr_notification' ),
					),
					'enquiry'      => array(
						'title' => esc_html__( 'Enquiry', 'multi-vendor-marketplace' ),
						'url'   => admin_url( 'admin.php?page=mvr_enquiry' ),
					),
					'staff'        => array(
						'title' => esc_html__( 'Staff', 'multi-vendor-marketplace' ),
						'url'   => admin_url( 'edit.php?post_type=mvr_staff' ),
					),
					'review'       => array(
						'title' => esc_html__( 'Store Review', 'multi-vendor-marketplace' ),
						'url'   => admin_url( 'admin.php?page=mvr_store_review' ),
					),
					'settings'     => array(
						'title' => esc_html__( 'Settings', 'multi-vendor-marketplace' ),
						'url'   => admin_url( 'admin.php?page=mvr_settings' ),
					),
				)
			);
		}

		/**
		 * Add an success message.
		 *
		 * @since 1.0.0
		 * @param String $text Success to add.
		 */
		public static function add_success( $text ) {
			self::$success_messages[] = $text;
		}

		/**
		 * Add an error message.
		 *
		 * @since 1.0.0
		 * @param String $text Error to add.
		 */
		public static function add_error( $text ) {
			self::$error_messages[] = $text;
		}

		/**
		 * Display a notice message.
		 *
		 * @since 1.0.0
		 * @param String $text Notice to display.
		 */
		public static function print_notice( $text ) {
			if ( ! empty( $text ) ) {
				echo '<div class="mvr-notice"><p>' . wp_kses_post( $text ) . '</p></div>';
			}
		}

		/**
		 * Save success/errors to an option.
		 *
		 * @since 1.0.0
		 */
		public static function save_messages() {
			update_option( 'mvr_admin_success_messages', self::$success_messages );
			update_option( 'mvr_admin_error_messages', self::$error_messages );
		}

		/**
		 * Show any stored success/errors messages.
		 *
		 * @since 1.0.0
		 */
		public static function output_messages() {
			$success_messages = array_filter( (array) get_option( 'mvr_admin_success_messages' ) );
			$error_messages   = array_filter( (array) get_option( 'mvr_admin_error_messages' ) );

			if ( ! empty( $success_messages ) ) {
				echo '<div id="message" class="updated notice is-dismissible">';

				foreach ( $success_messages as $success ) {
					echo '<p>' . wp_kses_post( $success ) . '</p>';
				}

				echo '</div>';

				// Clear.
				delete_option( 'mvr_admin_success_messages' );
			}

			if ( ! empty( $error_messages ) ) {
				echo '<div id="message" class="error notice is-dismissible">';

				foreach ( $error_messages as $error ) {
					echo '<p>' . wp_kses_post( $error ) . '</p>';
				}

				echo '</div>';

				// Clear.
				delete_option( 'mvr_admin_error_messages' );
			}
		}

		/**
		 * Set Vendor Role
		 *
		 * @since 1.0.0
		 * @param Array $data Data.
		 * @return Array
		 */
		public static function set_vendor_role( $data ) {
			if ( ! mvr_check_is_array( $data ) || ! isset( $data['is_mvr_vendor'] ) || false !== $data['is_mvr_vendor'] ) {
				return $data;
			}

			$data['role'] = 'mvr_vendor';

			return $data;
		}

		/**
		 * Get product type Selector for vendor
		 *
		 * @since 1.0.0
		 * @param Array $product_types Product Types.
		 * @return Array
		 */
		public static function set_vendor_product_type_selector( $product_types ) {
			$vendor_obj = mvr_get_current_vendor_object();

			if ( ! mvr_is_vendor( $vendor_obj ) ) {
				return $product_types;
			}

			$allowed_product_types = get_option( 'mvr_settings_allowed_product_type', array( 'simple', 'variable' ) );

			if ( ! mvr_check_is_array( $allowed_product_types ) ) {
				return $product_types;
			}

			foreach ( $product_types as $key => $product_type ) {
				if ( ! in_array( $key, $allowed_product_types, true ) ) {
					unset( $product_types[ $key ] );
				}
			}

			return $product_types;
		}

		/**
		 * Check Inventory Capability for staff and vendor.
		 *
		 * @since 1.0.0
		 * @param Array $product_tabs Product Tabs.
		 * @return Array
		 */
		public static function check_inventory_capability( $product_tabs ) {
			if ( ! mvr_check_user_as_vendor_or_staff() ) {
				return $product_tabs;
			}

			if ( ! mvr_check_is_array( $product_tabs ) || ! isset( $product_tabs['inventory'] ) ) {
				return $product_tabs;
			}

			$vendor_obj = mvr_get_current_vendor_object();

			if ( 'yes' !== $vendor_obj->get_manage_inventory() ) {
				unset( $product_tabs['inventory'] );
				return $product_tabs;
			}

			if ( mvr_check_user_as_staff() ) {
				$staff_obj = mvr_get_current_staff_object();

				if ( 'yes' !== $staff_obj->get_manage_inventory() ) {
					unset( $product_tabs['inventory'] );
					return $product_tabs;
				}
			}

			return $product_tabs;
		}
	}

	MVR_Admin::init();
}
