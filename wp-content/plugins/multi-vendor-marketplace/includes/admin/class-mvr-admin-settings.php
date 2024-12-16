<?php
/**
 * Vendor Admin Handler
 *
 * @package  Multi-Vendor\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Admin_Settings' ) ) {
	/**
	 * Handle Admin menus, post types and settings.
	 *
	 * @class MVR_Admin_Settings
	 * @package Class
	 */
	class MVR_Admin_Settings {

		/**
		 * Section pages.
		 *
		 * @var Array
		 */
		private static $sections = array();

		/**
		 * Include the section classes.
		 *
		 * @since 1.0.0
		 */
		public static function get_sections() {
			$sections = array(
				'vendor-registration',
				'capabilities',
				'profile-management',
				'commission',
				'payment',
				'withdraw',
				'vendor-staff',
				'spmv',
			);

			if ( class_exists( 'WC_Subscriptions' ) ) {
				$sections[] = 'vendor-subscription';
			}

			$sections[] = 'advanced';
			$sections[] = 'localization';
			$sections[] = 'messages';

			if ( empty( self::$sections ) ) {
				foreach ( $sections as $section ) {
					self::$sections[] = include 'settings-page/class-mvr-admin-settings-' . $section . '.php';
				}
			}

			return self::$sections;
		}

		/**
		 * Output Settings.
		 *
		 * Handles the display of the main Multi-Vendor for WooCommerce settings page in admin.
		 *
		 * @since 1.0.0
		 */
		public static function output() {
			global $current_tab;

			/**
			 * Plugin settings start.
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_settings_start' );

			$current_tab = ( ! empty( $_GET['tab'] ) ) ? urldecode( sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) : 'vendor_registration';

			// Include sections.
			self::get_sections();

			/**
			 * Add plugin default options based on tab requested.
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_add_options_' . $current_tab );

			/**
			 * Add plugin default options.
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_add_options' );

			if ( ! empty( $_POST['save'] ) ) {
				if ( empty( $_REQUEST['mvr_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['mvr_nonce'] ), 'mvr-settings' ) ) {
					die( esc_html__( 'Action failed. Please refresh the page and retry.', 'multi-vendor-marketplace' ) );
				}

				/**
				 * Save plugin options when saved based on tab requested.
				 *
				 * @since 1.0.0
				 * @param Mixed $_POST Post data.
				 */
				do_action( 'mvr_update_options_' . $current_tab, $_POST );

				/**
				 * Save plugin options when saved.
				 *
				 * @since 1.0.0
				 * @param Mixed $_POST Post data.
				 */
				do_action( 'mvr_update_options', $_POST );

				wp_safe_redirect( esc_url_raw( add_query_arg( array( 'saved' => 'true' ) ) ) );
				exit;
			}

			if ( ! empty( $_POST['reset'] ) ) {
				if ( empty( $_REQUEST['mvr_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['mvr_nonce'] ), 'mvr-reset-settings' ) ) {
					die( esc_html__( 'Action failed. Please refresh the page and retry.', 'multi-vendor-marketplace' ) );
				}

				/**
				 * Reset plugin to default options based on tab requested.
				 *
				 * @since 1.0.0
				 * @param mixed $_POST
				 */
				do_action( 'mvr_reset_options_' . $current_tab, $_POST );

				wp_safe_redirect( esc_url_raw( add_query_arg( array( 'saved' => 'true' ) ) ) );
				exit;
			}

			// Get any returned messages.
			$error   = ( empty( $_GET['wc_error'] ) ) ? '' : urldecode( stripslashes( sanitize_title( wp_unslash( $_GET['wc_error'] ) ) ) );
			$message = ( empty( $_GET['wc_message'] ) ) ? '' : urldecode( stripslashes( sanitize_title( wp_unslash( $_GET['wc_message'] ) ) ) );

			if ( $error || $message ) {
				if ( $error ) {
					echo '<div id="message" class="error fade"><p><strong>' . esc_html( $error ) . '</strong></p></div>';
				} else {
					echo '<div id="message" class="updated fade"><p><strong>' . esc_html( $message ) . '</strong></p></div>';
				}
			} elseif ( ! empty( $_GET['saved'] ) ) {
				echo '<div id="message" class="updated fade"><p><strong>' . esc_html__( 'Your settings have been saved.', 'multi-vendor-marketplace' ) . '</strong></p></div>';
			} elseif ( ! empty( $_GET['reset'] ) ) {
				echo '<div id="message" class="updated fade"><p><strong>' . esc_html__( 'Your settings have been reset.', 'multi-vendor-marketplace' ) . '</strong></p></div>';
			}

			include 'views/html-admin-settings.php';
		}

		/**
		 * Default options.
		 *
		 * Sets up the default options used on the settings page.
		 *
		 * @since 1.0.0
		 */
		public static function save_default_options() {
			if ( empty( self::$sections ) ) {
				self::get_sections();
			}

			foreach ( self::$sections as $tab ) {
				if ( ! isset( $tab->settings ) || ! is_array( $tab->settings ) ) {
					continue;
				}

				$tab->add_options();
			}
		}
	}
}
