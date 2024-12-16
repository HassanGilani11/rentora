<?php
/**
 * Plugin Name: Multi Vendor Marketplace
 * Plugin URI:
 * Description: Convert your existing WooCommerce shop into a Multi Vendor Marketplace.
 * Version: 1.0.1
 * Author: Flintop
 * Author URI: https://woocommerce.com/vendor/flintop/
 * Developer: Flintop
 * Developer URI: https://woocommerce.com/vendor/flintop/
 * Text Domain: multi-vendor-marketplace
 * Domain Path: /languages
 * WC requires at least: 6.0.0
 * WC tested up to: 9.2.3
 * Tested up to: 6.6.1
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Requires Plugins: woocommerce
 *
 * @package Multi Vendor Marketplace
 * Woo: 18734004162571:7b5f9d1fed683976bf1592ec6813538c

 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Include once will help to avoid fatal error by load the files when you call init hook.
 * */
require_once ABSPATH . 'wp-admin/includes/plugin.php';

/**
 * Include main class file.
 * */
if ( ! class_exists( 'MVR_Multi_Vendor' ) ) {
	include_once 'includes/class-mvr-multi-vendor.php';
}

/**
 * Define constant.
 * */
if ( ! defined( 'MVR_PLUGIN_FILE' ) ) {
	define( 'MVR_PLUGIN_FILE', __FILE__ );
}

if ( ! function_exists( 'mvr' ) ) {

	/**
	 * Multi Vendor class object.
	 *
	 * @since 1.0.0
	 * @return Object
	 * */
	function mvr() {
		return MVR_Multi_Vendor::instance();
	}
}

if ( ! function_exists( 'mvr_is_valid_wp' ) ) {
	/**
	 * Is valid WordPress version?
	 *
	 * @since 1.0.0
	 * @return Boolean
	 */
	function mvr_is_valid_wp() {
		return ( version_compare( get_bloginfo( 'version' ), mvr()->wp_requires, '<' ) ) ? false : true;
	}
}

if ( ! function_exists( 'mvr_is_valid_wc' ) ) {
	/**
	 * Is valid WooCommerce version?
	 *
	 * @since 1.0.0
	 * @return Boolean
	 */
	function mvr_is_valid_wc() {
		return ( version_compare( get_option( 'woocommerce_version' ), mvr()->wc_requires, '<' ) ) ? false : true;
	}
}

if ( ! function_exists( 'mvr_is_wc_active' ) ) {
	/**
	 * Function to check whether WooCommerce is active or not.
	 *
	 * @since 1.0.0
	 * @return Boolean
	 */
	function mvr_is_wc_active() {
		// This condition is for multi site installation.
		if ( is_multisite() && ! is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) && ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return false;
		} elseif ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) { // This condition is for single site installation.
			return false;
		}

		return true;
	}
}

if ( ! function_exists( 'mvr_is_plugin_active' ) ) {
	/**
	 * Is plugin active?
	 *
	 * @return bool
	 */
	function mvr_is_plugin_active() {
		if ( mvr_is_valid_wp() && mvr_is_wc_active() && mvr_is_valid_wc() ) {
			return true;
		}

		add_action(
			'admin_notices',
			function () {
				if ( ! mvr_is_valid_wp() ) {
					$notice = sprintf( 'This version of Multi Vendor Marketplace requires WordPress %1s or newer.', mvr()->wp_requires );
				} elseif ( ! mvr_is_wc_active() ) {
					$notice = 'Multi Vendor Marketplace Plugin will not work until WooCommerce Plugin is Activated. Please Activate the WooCommerce Plugin.';
				} elseif ( ! mvr_is_valid_wc() ) {
					$notice = sprintf( 'This version of Multi Vendor Marketplace requires WooCommerce %1s or newer.', mvr()->wc_requires );
				} else {
					$notice = '';
				}

				if ( $notice ) {
					echo '<div class="error">';
					echo '<p>' . wp_kses_post( $notice ) . '</p>';
					echo '</div>';
				}
			}
		);

		return false;
	}
}

// Return if the plugin is not active.
if ( ! mvr_is_plugin_active() ) {
	return;
}

/**
 * Add HPOS support.
 */
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

/**
 * Initialize the plugin.
 * */
mvr();
