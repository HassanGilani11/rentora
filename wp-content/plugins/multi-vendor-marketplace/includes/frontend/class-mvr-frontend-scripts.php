<?php
/**
 * Handle frontend scripts
 *
 * @package Multi Vendor\Classes
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'MVR_Frontend_Scripts' ) ) {

	/**
	 * Frontend scripts class.
	 */
	class MVR_Frontend_Scripts {

		/**
		 * Suffix.
		 *
		 * @var String.
		 * */
		private static $suffix;

		/**
		 * Contains an array of script handles registered by MVR.
		 *
		 * @var Array
		 */
		private static $scripts = array();

		/**
		 * Contains an array of script handles registered by MVR.
		 *
		 * @var Array
		 */
		private static $styles = array();

		/**
		 * Contains an array of script handles localized by MVR.
		 *
		 * @var Array
		 */
		private static $wp_localize_scripts = array();

		/**
		 * Hook in methods.
		 *
		 * @since 1.0.0
		 */
		public static function init() {
			self::$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'load_scripts' ) );
			add_action( 'wp_print_scripts', array( __CLASS__, 'localize_printed_scripts' ), 5 );
			add_action( 'wp_print_footer_scripts', array( __CLASS__, 'localize_printed_scripts' ), 5 );
		}

		/**
		 * Register all WC scripts.
		 *
		 * @since 1.0.0
		 */
		private static function register_scripts() {
			$register_scripts = array(
				'mvr-dashboard' => array(
					'src'  => self::get_asset_url( 'assets/js/frontend/dashboard.js' ),
					'deps' => array( 'jquery', 'jquery-blockui', 'jquery-ui-accordion', 'js-cookie', 'selectWoo', 'wc-country-select', 'wc-address-i18n' ),
				),
				'mvr-register'  => array(
					'src'  => self::get_asset_url( 'assets/js/frontend/vendor-register.js' ),
					'deps' => array( 'jquery', 'jquery-blockui', 'js-cookie' ),
				),
				'mvr-frontend'  => array(
					'src'  => self::get_asset_url( 'assets/js/frontend/frontend.js' ),
					'deps' => array( 'jquery', 'jquery-blockui', 'js-cookie' ),
				),
			);

			if ( file_exists( WC()->plugin_path() . '/assets/js/admin/wc-enhanced-select.js' ) ) {
				$register_scripts['wc-enhanced-select'] = array(
					'src'  => WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select.js',
					'deps' => array( 'jquery', 'selectWoo' ),
				);
			}

			foreach ( $register_scripts as $name => $props ) {
				self::register_script( $name, $props['src'], $props['deps'] );
			}
		}

		/**
		 * Register all WC styles.
		 *
		 * @since 1.0.0
		 */
		private static function register_styles() {
			$register_styles = array(
				'mvr-dashboard'     => array(
					'src'  => self::get_asset_url( 'assets/css/frontend/dashboard.css' ),
					'deps' => array( 'dashicons', 'select2' ),
				),
				'mvr-orders'        => array(
					'src'  => self::get_asset_url( 'assets/css/frontend/dashboard/orders.css' ),
					'deps' => array( 'dashicons' ),
				),
				'mvr-stores'        => array(
					'src'  => self::get_asset_url( 'assets/css/frontend/stores/stores.css' ),
					'deps' => array( 'dashicons' ),
				),
				'mvr-store'         => array(
					'src'  => self::get_asset_url( 'assets/css/frontend/stores/single-store.css' ),
					'deps' => array( 'dashicons' ),
				),
				'mvr-register'      => array(
					'src'  => self::get_asset_url( 'assets/css/frontend/register.css' ),
					'deps' => array( 'dashicons' ),
				),
				'mvr-become-vendor' => array(
					'src'  => self::get_asset_url( 'assets/css/frontend/become-vendor.css' ),
					'deps' => array( 'dashicons' ),
				),
				'mvr-frontend'      => array(
					'src'  => self::get_asset_url( 'assets/css/frontend/frontend.css' ),
					'deps' => array( 'dashicons' ),
				),
				'mvr-inline'        => array(
					'src'  => false,
					'deps' => '',
				),
			);

			foreach ( $register_styles as $name => $props ) {
				self::register_style( $name, $props['src'], $props['deps'] );
			}
		}

		/**
		 * Return data for script handles.
		 *
		 * @since 1.0.0
		 * @param  string $handle Script handle the data will be attached to.
		 * @return array|bool
		 */
		private static function get_script_data( $handle ) {
			global $wp;
			$vendor_obj          = mvr_get_current_vendor_object();
			$available_amount    = ( mvr_is_vendor( $vendor_obj ) ) ? $vendor_obj->get_amount() : 0;
			$min_withdraw_amount = get_option( 'mvr_settings_min_withdraw_threshold', '0' );
			$default_location    = wc_get_customer_default_location();

			switch ( $handle ) {
				case 'mvr-dashboard':
					$params = array(
						'ajax_url'                     => admin_url( 'admin-ajax.php' ),
						'logo'                         => MVR_PLUGIN_URL . '/assets/images/placeholder-64x64.png',
						'banner'                       => MVR_PLUGIN_URL . '/assets/images/placeholder-800x400.png',
						'choose_logo_title'            => esc_html__( 'Choose Logo', 'multi-vendor-marketplace' ),
						'add_logo_title'               => esc_html__( 'Add Logo', 'multi-vendor-marketplace' ),
						'choose_banner_title'          => esc_html__( 'Choose Banner', 'multi-vendor-marketplace' ),
						'add_banner_title'             => esc_html__( 'Add Banner', 'multi-vendor-marketplace' ),
						'delete_product_nonce'         => wp_create_nonce( 'mvr-delete-product' ),
						'duplicate_product_nonce'      => wp_create_nonce( 'mvr-duplicate-product' ),
						'available_amount'             => $available_amount,
						'min_withdraw_amount'          => $min_withdraw_amount,
						'enable_withdraw_charge'       => get_option( 'mvr_settings_enable_withdraw_charge_req', 'no' ),
						'withdraw_charge_type'         => get_option( 'mvr_settings_withdraw_charge_type', '1' ),
						'withdraw_charge'              => get_option( 'mvr_settings_withdraw_charge_val', '0' ),
						'withdraw_amount_label'        => esc_html__( 'Withdrawal Amount :', 'multi-vendor-marketplace' ),
						'withdraw_charge_label'        => esc_html__( 'Withdrawal Charge Amount :', 'multi-vendor-marketplace' ),
						'delete_product_confirm_msg'   => esc_html__( 'Are you sure you want to delete this product?', 'multi-vendor-marketplace' ),
						'decimal_separator'            => wc_get_price_decimal_separator(),
						'wc_currency_symbol'           => get_woocommerce_currency_symbol(),
						'currency_format_num_decimals' => wc_get_price_decimals(),
						'currency_format_thousand_sep' => esc_attr( wc_get_price_thousand_separator() ),
						'currency_position'            => esc_attr( stripslashes( get_option( 'woocommerce_currency_pos' ) ) ),
						/**
						 * Price Trim Zero
						 *
						 * @since 1.0.0
						 * @return Boolean
						 * */
						'currency_format_trim_zeros'   => ( false === apply_filters( 'woocommerce_price_trim_zeros', false ) ) ? 'no' : 'yes',
						'countries'                    => wp_json_encode( array_merge( WC()->countries->get_allowed_country_states(), WC()->countries->get_shipping_country_states() ) ),
						'i18n_select_state_text'       => esc_attr__( 'Select an option&hellip;', 'woocommerce' ),
						'default_country'              => isset( $default_location['country'] ) ? $default_location['country'] : '',
						'default_state'                => isset( $default_location['state'] ) ? $default_location['state'] : '',
						'enquiry_nonce'                => wp_create_nonce( 'mvr-enquiry' ),
						'notification_nonce'           => wp_create_nonce( 'mvr-notification' ),
						'current_vendor_id'            => mvr_get_current_vendor_id(),
					);
					break;
				case 'mvr-register':
					$params = array(
						'ajax_url'             => admin_url( 'admin-ajax.php' ),
						'vendor_shop_nonce'    => wp_create_nonce( 'mvr-vendor-shop' ),
						'vendor_slug_nonce'    => wp_create_nonce( 'mvr-vendor-slug' ),
						'shop_available_txt'   => esc_html__( 'Shop Name available', 'multi-vendor-marketplace' ),
						'shop_unavailable_txt' => esc_html__( 'Shop Name already exist', 'multi-vendor-marketplace' ),
						'available_txt'        => esc_html__( 'Available', 'multi-vendor-marketplace' ),
						'unavailable_txt'      => esc_html__( 'Unavailable', 'multi-vendor-marketplace' ),
						'min_char_shop_txt'    => esc_html__( 'Please enter the Shop Name in 3 or more characters', 'multi-vendor-marketplace' ),
						'min_char_slug_txt'    => esc_html__( 'Please enter 3 or more characters for the shop slug', 'multi-vendor-marketplace' ),
						'default_store_url'    => mvr_get_store_url( '{slug}' ),
					);
					break;
				case 'wc-enhanced-select':
					$params = array(
						'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'woocommerce' ),
						'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'woocommerce' ),
						'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'woocommerce' ),
						'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', 'woocommerce' ),
						'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'woocommerce' ),
						'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', 'woocommerce' ),
						'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'woocommerce' ),
						'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'woocommerce' ),
						'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'woocommerce' ),
						'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'woocommerce' ),
						'ajax_url'                  => admin_url( 'admin-ajax.php' ),
						'search_products_nonce'     => wp_create_nonce( 'search-products' ),
						'search_customers_nonce'    => wp_create_nonce( 'search-customers' ),
					);
					break;
				case 'mvr-frontend':
					$params = array(
						'decimal_point'   => wc_get_price_decimal_separator(),
						/* translators: %s: price decimal separator */
						'decimal_error'   => sprintf( __( 'Please enter a value with one monetary decimal point (%s) without thousand separators and currency symbols.', 'woocommerce' ), wc_get_price_decimal_separator() ),
						/* translators: %s: Withdraw Amount */
						'excess_withdraw' => sprintf( __( 'Amount to Withdraw should not be more then the Available amount (%s)', 'multi-vendor-marketplace' ), wc_price( $available_amount ) ),
						/* translators: %s: Minimum Withdraw Amount */
						'min_withdraw'    => sprintf( __( 'Amount to Withdraw should not  be less then the Minimum withdraw amount of (%s)', 'multi-vendor-marketplace' ), wc_price( $min_withdraw_amount ) ),
					);
					break;
				default:
					$params = false;
			}

			/**
			 * Script Data.
			 *
			 * @since 1.0.0
			 */
			return apply_filters( 'mvr_get_script_data', $params, $handle );
		}

		/**
		 * Return asset URL.
		 *
		 * @since 1.0.0
		 * @param String $path Assets path.
		 * @return String
		 */
		private static function get_asset_url( $path ) {
			/**
			 * Asset URL
			 *
			 * @since 1.0.0
			 */
			return apply_filters( 'mvr_get_asset_url', plugins_url( $path, MVR_PLUGIN_FILE ), $path );
		}

		/**
		 * Register a script for use.
		 *
		 * @since 1.0.0
		 * @uses   wp_register_script()
		 * @param  String   $handle    Name of the script. Should be unique.
		 * @param  String   $path      Full URL of the script, or path of the script relative to the WordPress root directory.
		 * @param  String[] $deps      An array of registered script handles this script depends on.
		 * @param  String   $version   String specifying script version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
		 * @param  Boolean  $in_footer Whether to enqueue the script before </body> instead of in the <head>. Default 'false'.
		 */
		private static function register_script( $handle, $path, $deps = array( 'jquery' ), $version = MVR_VERSION, $in_footer = true ) {
			self::$scripts[] = $handle;

			wp_register_script( $handle, $path, $deps, $version, $in_footer );
		}

		/**
		 * Register and enqueue a script for use.
		 *
		 * @since 1.0.0
		 * @uses   wp_register_style()
		 * @param  String   $handle    Name of the script. Should be unique.
		 * @param  String   $path      Full URL of the script, or path of the script relative to the WordPress root directory.
		 * @param  String[] $deps      An array of registered script handles this script depends on.
		 * @param  String   $version   String specifying script version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
		 * @param  Boolean  $in_footer Whether to enqueue the script before </body> instead of in the <head>. Default 'false'.
		 */
		private static function enqueue_script( $handle, $path = '', $deps = array( 'jquery' ), $version = MVR_VERSION, $in_footer = true ) {
			if ( ! in_array( $handle, self::$scripts, true ) && $path ) {
				self::register_script( $handle, $path, $deps, $version, $in_footer );
			}

			wp_enqueue_script( $handle );
		}

		/**
		 * Register a style for use.
		 *
		 * @uses   wp_register_style()
		 * @param  String   $handle  Name of the stylesheet. Should be unique.
		 * @param  String   $path    Full URL of the stylesheet, or path of the stylesheet relative to the WordPress root directory.
		 * @param  String[] $deps    An array of registered stylesheet handles this stylesheet depends on.
		 * @param  String   $version String specifying stylesheet version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
		 * @param  String   $media   The media for which this stylesheet has been defined. Accepts media types like 'all', 'print' and 'screen', or media queries like '(orientation: portrait)' and '(max-width: 640px)'.
		 * @param  Boolean  $has_rtl If has RTL version to load too.
		 */
		private static function register_style( $handle, $path, $deps = array(), $version = MVR_VERSION, $media = 'all', $has_rtl = false ) {
			self::$styles[] = $handle;

			wp_register_style( $handle, $path, $deps, $version, $media );

			if ( $has_rtl ) {
				wp_style_add_data( $handle, 'rtl', 'replace' );
			}
		}

		/**
		 * Register and enqueue a styles for use.
		 *
		 * @since 1.0.0
		 * @uses   wp_enqueue_style()
		 * @param  String   $handle  Name of the stylesheet. Should be unique.
		 * @param  String   $path    Full URL of the stylesheet, or path of the stylesheet relative to the WordPress root directory.
		 * @param  String[] $deps    An array of registered stylesheet handles this stylesheet depends on.
		 * @param  String   $version String specifying stylesheet version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
		 * @param  String   $media   The media for which this stylesheet has been defined. Accepts media types like 'all', 'print' and 'screen', or media queries like '(orientation: portrait)' and '(max-width: 640px)'.
		 * @param  Boolean  $has_rtl If has RTL version to load too.
		 */
		private static function enqueue_style( $handle, $path = '', $deps = array(), $version = MVR_VERSION, $media = 'all', $has_rtl = false ) {
			if ( ! in_array( $handle, self::$styles, true ) && $path ) {
				self::register_style( $handle, $path, $deps, $version, $media, $has_rtl );
			}

			wp_enqueue_style( $handle );
		}

		/**
		 * Register/queue frontend scripts.
		 *
		 * @since 1.0.0
		 * @uses wp_add_inline_style();
		 */
		public static function add_inline_styles() {
			$contents = get_option( 'mvr_settings_custom_css', '' );

			wp_add_inline_style( 'mvr-inline', $contents );
		}

		/**
		 * Register/queue frontend scripts.
		 *
		 * @since 1.0.0
		 */
		public static function load_scripts() {
			global $post;

			self::register_scripts();
			self::register_styles();

			self::enqueue_script( 'mvr-frontend' );
			self::enqueue_style( 'mvr-frontend' );
			self::add_inline_styles();

			if ( mvr_is_dashboard_page() ) {
				self::enqueue_script( 'mvr-dashboard' );
				self::enqueue_style( 'mvr-dashboard' );
			}

			if ( is_account_page() ) {
				self::enqueue_style( 'mvr-become-vendor' );
			}

			if ( mvr_is_dashboard_page( 'mvr-orders' ) || mvr_is_dashboard_page( 'mvr-view-order' ) ) {
				self::enqueue_script( 'mvr-orders' );
				self::enqueue_style( 'mvr-orders' );
			}

			if ( is_account_page() || mvr_is_vendor_register_page() || mvr_is_vendor_login_page() || mvr_is_dashboard_page( 'mvr-profile' ) ) {
				self::enqueue_script( 'mvr-register' );
				self::enqueue_style( 'mvr-register' );
			}

			if ( mvr_is_stores_page() || mvr_is_stores_page( 'mvr-store' ) ) {
				self::enqueue_script( 'mvr-stores' );
				self::enqueue_style( 'mvr-stores' );
			}

			if ( mvr_is_stores_page( 'mvr-store' ) ) {
				self::enqueue_script( 'mvr-store' );
				self::enqueue_style( 'mvr-store' );
			}
		}

		/**
		 * Localize scripts only when enqueued.
		 *
		 * @since 1.0.0
		 */
		public static function localize_printed_scripts() {
			foreach ( self::$scripts as $handle ) {
				self::localize_script( $handle );
			}
		}

		/**
		 * Localize a WC script once.
		 *
		 * @since 1.0.0
		 * @param String $handle Script handle the data will be attached to.
		 */
		private static function localize_script( $handle ) {
			if ( ! in_array( $handle, self::$wp_localize_scripts, true ) && wp_script_is( $handle ) ) {
				$data = self::get_script_data( $handle );

				if ( ! $data ) {
					return;
				}

				self::$wp_localize_scripts[] = $handle;
				$name                        = str_replace( '-', '_', $handle ) . '_params';

				/**
				 * Localize data
				 *
				 * @since 1.0.0
				 */
				$data = apply_filters( $name, $data );

				wp_localize_script( $handle, $name, $data );
			}
		}
	}

	MVR_Frontend_Scripts::init();
}
