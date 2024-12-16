<?php
/**
 * Initialize the plugin.
 *
 * @package Multi Vendor Marketplace
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Install' ) ) {

	/**
	 * Main Class.
	 * */
	class MVR_Install {
		/**
		 * Data Base Table.
		 *
		 * @var Array
		 * */
		public static $db_tables = array(
			'commission',
			'customer',
			'enquiry',
			'notification',
			'order',
			'payout',
			'product_map',
			'transaction',
			'withdraw',
		);

		/**
		 * Hook in methods.
		 *
		 * @since 1.0.0
		 * */
		public static function init() {
			add_action( 'admin_init', array( __CLASS__, 'check_db_tables' ) );
			add_action( 'init', array( __CLASS__, 'check_version' ) );
			add_filter( 'plugin_action_links_' . MVR_PLUGIN_SLUG, array( __CLASS__, 'plugin_action_links' ) );
		}

		/**
		 * Check Database Tables are there.
		 *
		 * @since 1.0.0
		 * */
		public static function check_db_tables() {
			global $wpdb;
			$wpdb_ref       = &$wpdb;
			$missing_tables = array();

			foreach ( self::$db_tables as $db_table ) {
				$db_table = "{$wpdb_ref->prefix}mvr_" . $db_table;
				$result   = $wpdb_ref->get_var( $wpdb_ref->prepare( 'SHOW TABLES LIKE %s', $db_table ) );

				if ( $result !== $db_table ) {
					$missing_tables[] = $db_table;
				}
			}

			if ( mvr_check_is_array( $missing_tables ) ) {
				/* translators: %s: Table Name */
				$message = sprintf( esc_html__( 'Multi Vendor plugin will not work properly some of Tables are missing in the database %s. Please verify the database.', 'multi-vendor-marketplace' ), esc_html( implode( ', ', $missing_tables ) ) );

				MVR_Admin::add_error( $message );
			}
		}

		/**
		 * Check Version.
		 *
		 * @since 1.0.0
		 * */
		public static function check_version() {
			if ( version_compare( get_option( 'mvr_version', '1.0.0' ), MVR_VERSION, '!=' ) ) {
				self::install(); // Set default values.

				/**
				 * Plugin updated.
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_updated' );
			}
		}

		/**
		 * Install.
		 *
		 * @since 1.0.0
		 * */
		public static function install() {
			if ( ! defined( 'MVR_INSTALLING' ) ) {
				define( 'MVR_INSTALLING', true );
			}

			self::create_tables(); // Create Tables.
			self::verify_base_tables(); // Verify base tables.
			self::create_options(); // Set Default values.
			self::create_pages(); // Create Pages.
			self::create_roles(); // Create User Roles.
			self::update_version(); // Update current version.

			/**
			 * Plugin installed.
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_installed' );
		}

		/**
		 * Creating Multi Vendor Tables
		 *
		 * @since 1.0.0
		 */
		private static function create_tables() {
			global $wpdb;

			$wpdb->hide_errors();

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$db_delta_result = dbDelta( self::get_schema() );

			return $db_delta_result;
		}

		/**
		 * Table Schema
		 *
		 * @since 1.0.0
		 */
		private static function get_schema() {
			global $wpdb;

			$collate = '';

			if ( $wpdb->has_cap( 'collation' ) ) {
				$collate = $wpdb->get_charset_collate();
			}

			$tables = " CREATE TABLE `{$wpdb->prefix}mvr_withdraw` (
						`ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						`vendor_id` bigint(20) NOT NULL,
						`date_created` datetime,
						`date_created_gmt` datetime,
						`amount` decimal(19,4) NOT NULL,
						`charge_amount` decimal(19,4) NOT NULL,
						`payment_method` varchar(20) NOT NULL,
						`currency` varchar(20) NOT NULL,
						`status` varchar(20) NOT NULL,
						`created_via` varchar(20) NOT NULL,
						`date_modified` datetime NOT NULL,
						`date_modified_gmt` datetime NOT NULL,
						`parent_id` bigint(20),
						`version` varchar(20) NOT NULL,
						PRIMARY KEY  (`ID`),
						KEY `vendor_id` (`vendor_id`),
						KEY `parent_id` (`parent_id`)
						) $collate; 
						
						CREATE TABLE `{$wpdb->prefix}mvr_transaction` (
						`ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						`vendor_id` bigint(20) NOT NULL,
						`date_created` datetime NOT NULL,
						`date_created_gmt` datetime NOT NULL,
						`amount` decimal(19,4) NOT NULL,
						`currency` varchar(20) NOT NULL,
						`type` varchar(20) NOT NULL,
						`status` varchar(20) NOT NULL,
						`created_via` varchar(20) NOT NULL,
						`source_id` varchar(20) NOT NULL,
						`source_from` varchar(20) NOT NULL,
						`withdraw_date` datetime NOT NULL,
						`date_modified` datetime NOT NULL,
						`date_modified_gmt` datetime NOT NULL,
						`parent_id` bigint(20),
						`version` varchar(20) NOT NULL,
						PRIMARY KEY  (`ID`),
						KEY `vendor_id` (`vendor_id`),
						KEY `source_id` (`source_id`),
						KEY `parent_id` (`parent_id`)
						) $collate; 

						CREATE TABLE `{$wpdb->prefix}mvr_commission` (
						`ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						`vendor_id` bigint(20) NOT NULL,
						`date_created` datetime,
						`date_created_gmt` datetime,
						`amount` decimal(19,4) NOT NULL,
						`vendor_amount` decimal(19,4) NOT NULL,
						`currency` varchar(20) NOT NULL,
						`status` varchar(20) NOT NULL,
						`created_via` varchar(20) NOT NULL,
						`source_id` bigint(20),
						`source_from` varchar(20),
						`products` longtext,
						`date_modified` datetime,
						`date_modified_gmt` datetime,
						`settings` longtext,
						`parent_id` bigint(20),
						`version` varchar(20) NOT NULL,
						PRIMARY KEY  (`ID`),
						KEY `vendor_id` (`vendor_id`),
						KEY `source_id` (`source_id`),
						KEY `parent_id` (`parent_id`)
						) $collate;

						CREATE TABLE `{$wpdb->prefix}mvr_product_map` (
						`ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						`map_id` bigint(20) NOT NULL,
						`product_id` bigint(20) NOT NULL,
						`vendor_id` bigint(20),
						`date_created` datetime NOT NULL,
						`date_created_gmt` datetime NOT NULL,
						`parent_id` bigint(20),
						`version` varchar(20) NOT NULL,
						PRIMARY KEY  (`ID`),
						KEY `product_id` (`product_id`),
						KEY `vendor_id` (`vendor_id`),
						KEY `parent_id` (`parent_id`)
						) $collate;

						CREATE TABLE `{$wpdb->prefix}mvr_enquiry` (
						`ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						`date_created` datetime NOT NULL,
						`date_created_gmt` datetime NOT NULL,
						`author_id` bigint(20),
						`vendor_id` bigint(20),
						`customer_id` bigint(20),
						`customer_name` varchar(250),
						`customer_email` varchar(100) NOT NULL,
						`message` longtext,
						`reply` longtext,
						`source_id` bigint(20),
						`source_from` varchar(20),
						`status` varchar(20),
						`date_modified` datetime NOT NULL,
						`date_modified_gmt` datetime NOT NULL,
						`version` varchar(20) NOT NULL,
						PRIMARY KEY  (`ID`),
						KEY `vendor_id` (`vendor_id`),
						KEY `author_id` (`author_id`),
						KEY `customer_id` (`customer_id`),
						KEY `customer_email` (`customer_email`),
						KEY `source_id` (`source_id`)
						) $collate;

						CREATE TABLE `{$wpdb->prefix}mvr_notification` (
						`ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						`date_created` datetime NOT NULL,
						`date_created_gmt` datetime NOT NULL,
						`message` longtext,
						`vendor_id` bigint(20),
						`source_id` bigint(20),
						`source_from` varchar(20),
						`to` varchar(20),
						`status` varchar(20),
						`date_modified` datetime NOT NULL,
						`date_modified_gmt` datetime NOT NULL,
						`version` varchar(20) NOT NULL,
						PRIMARY KEY  (`ID`),
						KEY `source_id` (`source_id`),
						KEY `vendor_id` (`vendor_id`)
						) $collate;

						CREATE TABLE `{$wpdb->prefix}mvr_customer` (
						`ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						`vendor_id` bigint(20) NOT NULL,
						`user_id` bigint(20) NOT NULL,
						`first_name` varchar(255) NOT NULL,
						`last_name` varchar(255) NOT NULL,
						`company` varchar(255),
						`address_1` varchar(255),
						`address_2` varchar(255),
						`city` varchar(100) DEFAULT '' NOT NULL,
						`state` varchar(100) DEFAULT '' NOT NULL,
						`country` char(2) DEFAULT '' NOT NULL,
						`postcode` varchar(20) DEFAULT '' NOT NULL,
						`email` varchar(100) NULL default NULL,
						`phone` varchar(255),
						`date_created` datetime NOT NULL,
						`date_created_gmt` datetime NOT NULL,
						`source_id` bigint(20),
						`source_from` varchar(20),
						`created_via` varchar(20) NOT NULL,
						`date_modified` datetime NOT NULL,
						`date_modified_gmt` datetime NOT NULL,
						`version` varchar(20) NOT NULL,
						PRIMARY KEY  (`ID`),
						KEY `vendor_id` (`vendor_id`),
						KEY `user_id` (`user_id`)
						) $collate; 

						CREATE TABLE `{$wpdb->prefix}mvr_payout` (
						`ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						`vendor_id` bigint(20) NOT NULL,
						`user_id` bigint(20) NOT NULL,
						`email` varchar(100) NULL default NULL,
						`amount` decimal(19,4) NOT NULL,
						`payment_method` varchar(100) NULL default NULL,
						`status` varchar(20),
						`currency` varchar(20) NOT NULL,
						`date_created` datetime NOT NULL,
						`date_created_gmt` datetime NOT NULL,
						`source_id` bigint(20),
						`source_from` varchar(20),
						`batch_id` varchar(100),
						`batch_log_id` varchar(100),
						`schedule` varchar(20),
						`created_via` varchar(20) NOT NULL,
						`date_modified` datetime NOT NULL,
						`date_modified_gmt` datetime NOT NULL,
						`version` varchar(20) NOT NULL,
						PRIMARY KEY  (`ID`),
						KEY `vendor_id` (`vendor_id`),
						KEY `user_id` (`user_id`),
						KEY `batch_id` (`batch_id`),
						KEY `batch_log_id` (`batch_log_id`),
						KEY `source_id` (`source_id`)
						) $collate; 

						CREATE TABLE `{$wpdb->prefix}mvr_order` (
						`ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						`vendor_id` bigint(20) NOT NULL,
						`order_id` bigint(20) NOT NULL,
						`commission_id` bigint(20) NOT NULL,
						`user_id` bigint(20) NOT NULL,
						`mvr_user_id` bigint(20) NOT NULL,
						`date_created` datetime NOT NULL,
						`date_created_gmt` datetime NOT NULL,
						`status` varchar(20) NOT NULL,
						`created_via` varchar(20) NOT NULL,
						`date_modified` datetime NOT NULL,
						`date_modified_gmt` datetime NOT NULL,
						`version` varchar(20) NOT NULL,
						PRIMARY KEY  (`ID`),
						KEY `vendor_id` (`vendor_id`),
						KEY `order_id` (`order_id`),
						KEY `commission_id` (`commission_id`),
						KEY `user_id` (`user_id`),
						KEY `mvr_user_id` (`mvr_user_id`)
						) $collate;
						";

			return $tables;
		}

		/**
		 * Check if all the base tables are present.
		 *
		 * @since 1.0.0
		 * @param Boolean $execute       Whether to execute get_schema queries as well.
		 *
		 * @return Array List of queries.
		 */
		public static function verify_base_tables( $execute = false ) {
			if ( $execute ) {
				self::create_tables();
			}

			$missing_tables = self::get_missing_tables( self::get_schema() );

			if ( 0 < count( $missing_tables ) ) {
				$message  = esc_html__( 'Verifying database... One or more tables are still missing: ', 'multi-vendor-marketplace' );
				$message .= implode( ', ', $missing_tables );
				MVR_Admin::add_error( $message );
			}

			return $missing_tables;
		}

		/**
		 * Get Missing Table
		 *
		 * @since 1.0.0
		 * @param String $creation_queries The output from the execution of dbDelta.
		 * @return Array.
		 */
		public static function get_missing_tables( $creation_queries ) {
			global $wpdb;

			$suppress_errors = $wpdb->suppress_errors( true );

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$output = dbDelta( $creation_queries, false );

			$wpdb->suppress_errors( $suppress_errors );

			$parsed_output = self::parse_dbdelta_output( $output );

			return $parsed_output['missing_tables'];
		}

		/**
		 * Parses the output given by dbdelta and returns information about it.
		 *
		 * @since 1.0.0
		 * @param Array $dbdelta_output The output from the execution of dbdelta.
		 * @return Array.
		 */
		public static function parse_dbdelta_output( array $dbdelta_output ) {
			$created_tables = array();

			foreach ( $dbdelta_output as $table_name => $result ) {
				if ( "Created table $table_name" !== $result ) {
					$created_tables[] = $table_name;
				}
			}

			return array( 'missing_tables' => array_filter( $created_tables ) );
		}

		/**
		 * Update Multi Vendor Marketplace version to current.
		 *
		 * @since 1.0.0
		 */
		private static function update_version() {
			delete_option( 'mvr_version' );
			add_option( 'mvr_version', MVR_VERSION );
		}

		/**
		 * Default options.
		 *
		 * Sets up the default options used on the settings page.
		 *
		 * @since 1.0.0
		 */
		private static function create_options() {
			// Include settings so that we can run through defaults.
			include_once __DIR__ . '/admin/mvr-admin-functions.php';
			include_once __DIR__ . '/admin/class-mvr-admin-settings.php';

			MVR_Admin_Settings::save_default_options();
		}

		/**
		 * Create pages that the plugin relies on, storing page IDs in variables.
		 *
		 * @since 1.0.0
		 */
		public static function create_pages() {
			// Set the locale to the store locale to ensure pages are created in the correct language.
			wc_switch_to_site_locale();

			include_once __DIR__ . '/admin/mvr-admin-functions.php';

			/**
			 * Determines which pages are created during install.
			 *
			 * @since 1.0.0
			 */
			$pages = apply_filters(
				'mvr_create_pages',
				array(
					'dashboard'       => array(
						'name'    => _x( 'dashboard', 'Page slug', 'multi-vendor-marketplace' ),
						'title'   => _x( 'Vendor Dashboard', 'Page title', 'multi-vendor-marketplace' ),
						'content' => '<!-- wp:shortcode -->[mvr_dashboard]<!-- /wp:shortcode -->',
						'option'  => 'dashboard_page_id',
					),
					'vendor_register' => array(
						'name'    => _x( 'vendor_register', 'Page slug', 'multi-vendor-marketplace' ),
						'title'   => _x( 'Vendor Registration', 'Page title', 'multi-vendor-marketplace' ),
						'content' => '<!-- wp:shortcode -->[mvr_vendor_register]<!-- /wp:shortcode -->',
						'option'  => 'vendor_register_page_id',
					),
					'vendor_login'    => array(
						'name'    => _x( 'vendor_login', 'Page slug', 'multi-vendor-marketplace' ),
						'title'   => _x( 'Vendor Login', 'Page title', 'multi-vendor-marketplace' ),
						'content' => '<!-- wp:shortcode -->[mvr_vendor_login]<!-- /wp:shortcode -->',
						'option'  => 'vendor_login_page_id',
					),
					'stores'          => array(
						'name'    => _x( 'stores', 'Page slug', 'multi-vendor-marketplace' ),
						'title'   => _x( 'Stores', 'Page title', 'multi-vendor-marketplace' ),
						'content' => '<!-- wp:shortcode -->[mvr_stores]<!-- /wp:shortcode -->',
						'option'  => 'stores_page_id',
					),
				)
			);

			foreach ( $pages as $key => $page ) {
				$page_id = mvr_create_page( esc_sql( $page['name'] ), 'mvr_' . $key . '_page_id', $page['title'], $page['content'] );

				update_option( 'mvr_settings_' . $page['option'], $page_id );
			}

			// Restore the locale to the default locale.
			wc_restore_locale();
		}

		/**
		 * Create roles and capabilities.
		 *
		 * @since 1.0.0
		 */
		public static function create_roles() {
			global $wp_roles;

			if ( ! class_exists( 'WP_Roles' ) ) {
				return;
			}

			if ( ! $wp_roles ) {
				return;
			}

			/* translators: vendor role */
			_x( 'Vendor', 'Vendor role', 'multi-vendor-marketplace' );

			$role = get_role( 'shop_manager' );
			$cap  = is_object( $role ) ? $role->capabilities : array();

			if ( ! mvr_check_is_array( $cap ) ) {
				return;
			}

			add_role( 'mvr-vendor', 'Vendor', $cap );

			/* translators: Staff role */
			_x( 'Staff', 'Staff role', 'multi-vendor-marketplace' );

			add_role( 'mvr-staff', 'Staff', $cap );
		}

		/**
		 * Settings link.
		 *
		 * @since 1.0.0
		 * @param Array $links Setting Links.
		 * @return Array
		 * */
		public static function plugin_action_links( $links ) {
			$setting_page_link = '<a href="' . mvr_get_settings_page_url() . '">' . esc_html__( 'Settings', 'multi-vendor-marketplace' ) . '</a>';
			array_unshift( $links, $setting_page_link );

			$vendor_list_page_link = '<a href="' . mvr_get_vendor_page_url() . '">' . esc_html__( 'Vendors', 'multi-vendor-marketplace' ) . '</a>';
			array_unshift( $links, $vendor_list_page_link );

			return $links;
		}
	}

	MVR_Install::init();
}
