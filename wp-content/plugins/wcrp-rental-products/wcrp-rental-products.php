<?php

/**
 * Plugin name: Rental Products
 * Plugin URI: https://woocommerce.com/products/rental-products/
 * Description: WooCommerce extension by 99w.
 * Author: 99w
 * Author URI: https://99w.co.uk
 * Developer: 99w
 * Developer URI: https://99w.co.uk
 * Version: 5.1.0
 * Requires at least: 6.3.0
 * Requires PHP: 7.4.0
 * Requires plugins: woocommerce
 * WC requires at least: 8.5.0
 * WC tested up to: 9.3.1
 * Woo: 5860277:2a71a9c5d8ea27ef91a69904294b660c
 * Domain path: /languages
 * Text domain: wcrp-rental-products
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products' ) ) {

	define( 'WCRP_RENTAL_PRODUCTS_VERSION', '5.1.0' );
	define( 'WCRP_RENTAL_PRODUCTS_UPLOADS_PATH', WP_CONTENT_DIR . '/uploads/wcrp-rental-products/' );
	define( 'WCRP_RENTAL_PRODUCTS_TEMPLATES_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/templates/' );

	class WCRP_Rental_Products {

		public function __construct() {

			require_once __DIR__ . '/includes/class-wcrp-rental-products-activation.php';
			require_once __DIR__ . '/includes/class-wcrp-rental-products-deactivation.php';
			require_once __DIR__ . '/includes/class-wcrp-rental-products-translation.php';

			new WCRP_Rental_Products_Activation();
			new WCRP_Rental_Products_Deactivation();
			new WCRP_Rental_Products_Translation();

			require_once ABSPATH . 'wp-admin/includes/plugin.php';

			if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

				add_action( 'before_woocommerce_init', function() {

					if ( class_exists( 'Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {

						Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );

					}

				});

				add_action( 'before_woocommerce_init', function() {

					if ( class_exists( 'Automattic\WooCommerce\Utilities\FeaturesUtil' ) && version_compare( WC_VERSION, '8.0.0', '>=' ) ) {

						Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );

					}

				});

				function wcrp_rental_products_hpos_enabled() {

					// This function is not recommended for use in custom development

					return class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) && Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();

				}

				require_once __DIR__ . '/includes/functions.php';

				require_once __DIR__ . '/includes/class-wcrp-rental-products-account.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-archive.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-availability-checker.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-blocks.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-cart-checks.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-cart-fees.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-cart-items.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-cart-redirects.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-emails.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-enqueues-admin.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-enqueues-assets.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-enqueues-public.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-feeds.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-misc.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-notices-admin.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-order-drafts.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-order-filters.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-order-info.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-order-line-items.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-order-save.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-product-bulk-edits.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-product-data-returns.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-product-display.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-product-fields.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-product-filters.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-product-rental-form.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-product-save.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-product-search.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-rentals-calendar.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-rentals-dashboard.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-rentals-inventory.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-rentals-summary.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-rentals-tools.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-settings.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-shortcodes.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-stat-helpers.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-stock-helpers.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-stock-manipulation.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-upgrade.php';
				require_once __DIR__ . '/includes/class-wcrp-rental-products-widgets.php';

				new WCRP_Rental_Products_Account();
				new WCRP_Rental_Products_Archive();
				new WCRP_Rental_Products_Availability_Checker();
				new WCRP_Rental_Products_Blocks();
				new WCRP_Rental_Products_Cart_Checks();
				new WCRP_Rental_Products_Cart_Fees();
				new WCRP_Rental_Products_Cart_Items();
				new WCRP_Rental_Products_Cart_Redirects();
				new WCRP_Rental_Products_Emails();
				new WCRP_Rental_Products_Enqueues_Admin();
				new WCRP_Rental_Products_Enqueues_Assets();
				new WCRP_Rental_Products_Enqueues_Public();
				new WCRP_Rental_Products_Feeds();
				new WCRP_Rental_Products_Misc();
				new WCRP_Rental_Products_Notices_Admin();
				new WCRP_Rental_Products_Order_Drafts();
				new WCRP_Rental_Products_Order_Filters();
				new WCRP_Rental_Products_Order_Info();
				new WCRP_Rental_Products_Order_Line_items();
				new WCRP_Rental_Products_Order_Save();
				new WCRP_Rental_Products_Product_Bulk_Edits();
				new WCRP_Rental_Products_Product_Data_Returns();
				new WCRP_Rental_Products_Product_Display();
				new WCRP_Rental_Products_Product_Fields();
				new WCRP_Rental_Products_Product_Filters();
				new WCRP_Rental_Products_Product_Rental_Form();
				new WCRP_Rental_Products_Product_Save();
				new WCRP_Rental_Products_Product_Search();
				new WCRP_Rental_Products_Rentals_Calendar();
				new WCRP_Rental_Products_Rentals_Dashboard();
				new WCRP_Rental_Products_Rentals_Inventory();
				new WCRP_Rental_Products_Rentals_Summary();
				new WCRP_Rental_Products_Rentals_Tools();
				new WCRP_Rental_Products_Settings();
				new WCRP_Rental_Products_Shortcodes();
				new WCRP_Rental_Products_Stat_Helpers();
				new WCRP_Rental_Products_Stock_Helpers();
				new WCRP_Rental_Products_Stock_Manipulation();
				new WCRP_Rental_Products_Upgrade();
				new WCRP_Rental_Products_Widgets();

			} else {

				add_action( 'admin_notices', function() {

					if ( current_user_can( 'edit_plugins' ) ) {

						?>

						<div class="notice notice-error">
							<p><strong><?php esc_html_e( 'Rental Products requires WooCommerce to be installed and activated.', 'wcrp-rental-products' ); ?></strong></p>
						</div>

						<?php

					}

				});

			}

		}

	}

	new WCRP_Rental_Products();

}
