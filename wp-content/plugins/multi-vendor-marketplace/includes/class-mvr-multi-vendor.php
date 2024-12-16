<?php
/**
 * Multi Vendor Marketplace Main Class
 *
 * @package Multi Vendor Marketplace
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Multi_Vendor' ) ) {

	/**
	 * Main Class.
	 * */
	final class MVR_Multi_Vendor {

		/**
		 * Version.
		 *
		 * @var String
		 * */
		private $version = '1.0.1';

		/**
		 * WordPress Requires
		 *
		 * @var String
		 * */
		public $wp_requires = '4.6';

		/**
		 * WooCommerce Requires
		 *
		 * @var String
		 * */
		public $wc_requires = '4.0';

		/**
		 * Plugin prefix.
		 *
		 * @var String
		 */
		public $prefix = 'mvr';

		/**
		 * Widgets.
		 *
		 * @var Array
		 * */
		protected $compatibilities;

		/**
		 * Entity.
		 *
		 * @var Array
		 * */
		public $entity;

		/**
		 * Entity.
		 *
		 * @var Array
		 * */
		public $data_stores;

		/**
		 * Notifications.
		 *
		 * @var Array
		 * */
		protected $notifications;

		/**
		 * The single instance of the class.
		 *
		 * @var Object
		 * */
		protected static $instance = null;

		/**
		 * Get Query instance.
		 *
		 * @var MVR_Query
		 */
		public $query;

		/**
		 * Load Vendor Class in Single Instance.
		 *
		 * @since 1.0.0
		 * */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Cloning has been forbidden
		 *
		 * @since 1.0.0
		 * */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, 'You are not allowed to perform this action!!!', '1.0' );
		}

		/**
		 * UnSerialize the class data has been forbidden.
		 *
		 * @since 1.0.0
		 * */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, 'You are not allowed to perform this action!!!', '1.0' );
		}

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 * */
		public function __construct() {
			if ( ! function_exists( 'wp_get_current_user' ) ) {
				include ABSPATH . 'wp-includes/pluggable.php';
			}

			$this->define_constants();
			$this->include_files();
			$this->init_hooks();
			$this->add_entity();
		}

		/**
		 * Load plugin the translate files.
		 *
		 * @since 1.0.0
		 * */
		private function load_plugin_textdomain() {
			if ( function_exists( 'determine_locale' ) ) {
				$locale = determine_locale();
			} else {
				$locale = is_admin() ? get_user_locale() : get_locale();
			}

			/**
			 * Plugin locale.
			 *
			 * @since 1.0.0
			 */
			$locale = apply_filters( 'plugin_locale', $locale, 'multi-vendor-marketplace' );

			load_textdomain( 'multi-vendor-marketplace', WP_LANG_DIR . '/multi-vendor-marketplace/multi-vendor-marketplace-' . $locale . '.mo' ); // Load the text domain from WordPress languages folder.
			load_plugin_textdomain( 'multi-vendor-marketplace', false, dirname( plugin_basename( MVR_PLUGIN_FILE ) ) . '/languages' ); // Load the text domain from plugin.
		}

		/**
		 * Prepare the constants value array.
		 *
		 * @since 1.0.0
		 * */
		private function define_constants() {
			$protocol = 'http://';

			if ( ( isset( $_SERVER['HTTPS'] ) && ( ( 'on' === $_SERVER['HTTPS'] ) || ( 1 === $_SERVER['HTTPS'] ) ) )
			|| ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'] ) ) {
				$protocol = 'https://';
			}

			$constant_array = array(
				'MVR_VERSION'        => $this->version,
				'MVR_FOLDER_NAME'    => 'multi-vendor-marketplace',
				'MVR_ABSPATH'        => dirname( MVR_PLUGIN_FILE ) . '/',
				'MVR_ADMIN_URL'      => admin_url( 'admin.php' ),
				'MVR_ADMIN_AJAX_URL' => admin_url( 'admin-ajax.php' ),
				'MVR_PLUGIN_SLUG'    => plugin_basename( MVR_PLUGIN_FILE ),
				'MVR_PLUGIN_PATH'    => untrailingslashit( plugin_dir_path( MVR_PLUGIN_FILE ) ),
				'MVR_PLUGIN_URL'     => untrailingslashit( plugins_url( '/', MVR_PLUGIN_FILE ) ),
				'MVR_PROTOCOL'       => $protocol,
				'MVR_PREFIX'         => $this->prefix,
			);

			/**
			 * Define Constants.
			 *
			 * @since 1.0.0
			 */
			$constant_array = apply_filters( 'mvr_define_constants', $constant_array );

			if ( is_array( $constant_array ) && ! empty( $constant_array ) ) {
				foreach ( $constant_array as $name => $value ) {
					$this->define_constant( $name, $value );
				}
			}
		}

		/**
		 * Define the Constants value.
		 *
		 * @since 1.0.0
		 * @param String $name Constant Name.
		 * @param String $value Constant Value.
		 * */
		private function define_constant( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Include required files.
		 *
		 * @since 1.0.0
		 * */
		private function include_files() {
			// Functions.
			include_once MVR_ABSPATH . 'includes/functions/mvr-core-functions.php';

			// Autoload.
			include_once MVR_ABSPATH . 'includes/class-mvr-autoload.php';

			// Abstract classes.
			include_once MVR_ABSPATH . 'includes/abstracts/class-mvr-abstract-settings.php';

			// Install.
			include_once MVR_ABSPATH . 'includes/class-mvr-install.php';

			include_once MVR_ABSPATH . 'includes/class-mvr-emails.php';
			include_once MVR_ABSPATH . 'includes/class-mvr-order-manager.php';
			include_once MVR_ABSPATH . 'includes/class-mvr-commission-manager.php';
			include_once MVR_ABSPATH . 'includes/class-mvr-transaction-manager.php';
			include_once MVR_ABSPATH . 'includes/class-mvr-date-time.php';
			include_once MVR_ABSPATH . 'includes/class-mvr-payout-manager.php';
			include_once MVR_ABSPATH . 'includes/class-mvr-notification-manager.php';
			include_once MVR_ABSPATH . 'includes/class-mvr-register.php';
			include_once MVR_ABSPATH . 'includes/class-mvr-comments.php';
			include_once MVR_ABSPATH . 'includes/class-mvr-action-scheduler.php';

			// AJAX.
			include_once MVR_ABSPATH . 'includes/class-mvr-ajax.php';

			// Post Type and Status.
			include_once MVR_ABSPATH . 'includes/class-mvr-post-types.php';

			// GDPR Privacy Policy.
			include_once MVR_ABSPATH . 'includes/privacy/class-mvr-privacy.php';

			if ( $this->is_request( 'admin' ) ) {
				include_once MVR_ABSPATH . 'includes/admin/class-mvr-admin.php';
			}

			if ( $this->is_request( 'frontend' ) ) {
				include_once MVR_ABSPATH . 'includes/frontend/class-mvr-frontend.php';
			}

			if ( class_exists( 'MVR_Query' ) ) {
				$this->query = new MVR_Query();
			}
		}

		/**
		 * To check and include the file
		 *
		 * @since 1.0.0
		 * @param String $type Include Type.
		 * @return Boolean
		 */
		private function is_request( $type ) {
			switch ( $type ) {
				case 'admin':
					return is_admin();
				case 'ajax':
					return defined( 'DOING_AJAX' );
				case 'cron':
					return defined( 'DOING_CRON' );
				case 'frontend':
					return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
			}

			return false;
		}

		/**
		 * Define the hooks.
		 *
		 * @since 1.0.0
		 * */
		private function init_hooks() {
			register_activation_hook( MVR_PLUGIN_FILE, array( 'MVR_Install', 'install' ) ); // Register the plugin.
			register_deactivation_hook( MVR_PLUGIN_FILE, array( $this, 'upon_deactivation' ) );
			add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), 20 ); // Init the plugin.
			add_filter( 'woocommerce_data_stores', array( $this, 'add_data_stores' ) ); // Data stores.
			add_action( 'init', array( 'MVR_Shortcodes', 'init' ) ); // Shortcode Initialization.
			add_action( 'init', array( 'MVR_Action_Scheduler', 'init' ) ); // Action Scheduler.
			add_action( 'init', array( $this, 'restrict_vendor_backend_access' ) ); // Vendor Backend Access restriction.
		}

		/**
		 * Plugins Loaded.
		 *
		 * @since 1.0.0
		 * */
		public function plugins_loaded() {
			/**
			 * Before Plugin Loaded.
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_before_plugin_loaded' );

			include_once MVR_ABSPATH . 'includes/payouts/class-mvr-paypal-payouts-helper.php';

			$this->load_plugin_textdomain();

			/**
			 * After Plugin Loaded.
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_after_plugin_loaded' );
		}

		/**
		 * Fire upon deactivating Multi Vendor Marketplace
		 *
		 * @since 1.0.0
		 */
		public function upon_deactivation() {
			delete_option( 'mvr_flush_rewrite_rules' );
		}

		/**
		 * Add our data stores to WC.
		 *
		 * @since 1.0.0
		 */
		public function add_entity() {
			$this->entity = array(
				'mvr_vendor'       => 'MVR_Vendor',
				'mvr_staff'        => 'MVR_Staff',
				'mvr_commission'   => 'MVR_Commission',
				'mvr_withdraw'     => 'MVR_Withdraw',
				'mvr_transaction'  => 'MVR_Transaction',
				'mvr_payout'       => 'MVR_Payout',
				'mvr_payout_batch' => 'MVR_Payout_Batch',
				'mvr_notification' => 'MVR_Notification',
				'mvr_enquiry'      => 'MVR_Enquiry',
				'mvr_order'        => 'MVR_Order',
				'mvr_customer'     => 'MVR_Customer',
				'mvr_spmv'         => 'MVR_SPMV',
			);
		}

		/**
		 * Add our data stores to WC.
		 *
		 * @since 1.0.0
		 * @param Array $data_stores Data Stores.
		 * @return Array
		 */
		public function add_data_stores( $data_stores ) {
			$this->data_stores = array(
				'mvr_vendor'       => 'MVR_Vendor_Data_Store_CPT',
				'mvr_staff'        => 'MVR_Staff_Data_Store_CPT',
				'mvr_commission'   => 'MVR_Commission_Data_Store_CPT',
				'mvr_withdraw'     => 'MVR_Withdraw_Data_Store_CPT',
				'mvr_transaction'  => 'MVR_Transaction_Data_Store_CPT',
				'mvr_payout'       => 'MVR_Payout_Data_Store_CPT',
				'mvr_payout_batch' => 'MVR_Payout_Batch_Data_Store_CPT',
				'mvr_notification' => 'MVR_Notification_Data_Store_CPT',
				'mvr_enquiry'      => 'MVR_Enquiry_Data_Store_CPT',
				'mvr_order'        => 'MVR_Order_Data_Store_CPT',
				'mvr_customer'     => 'MVR_Customer_Data_Store_CPT',
				'mvr_spmv'         => 'MVR_SPMV_Data_Store_CPT',
			);

			return $data_stores + $this->data_stores;
		}

		/**
		 * Templates.
		 *
		 * @since 1.0.0
		 * */
		public function restrict_vendor_backend_access() {
			global $pagenow;

			if ( ! is_admin() || defined( 'DOING_AJAX' ) ) {
				return false;
			}

			if ( ! mvr_check_user_as_vendor_or_staff() ) {
				return false;
			}

			$post_type = isset( $_REQUEST['post_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['post_type'] ) ) : '';

			if ( 'post-new.php' === $pagenow && in_array( $post_type, array( 'product', 'shop_coupon' ), true ) ) {
				return false;
			}

			$post_type = isset( $_GET['post'] ) ? get_post_type( absint( wp_unslash( $_GET['post'] ) ) ) : '';

			if ( 'post.php' === $pagenow && in_array( $post_type, array( 'product', 'shop_coupon' ), true ) ) {
				return false;
			}

			$is_iframe = isset( $_SERVER['HTTP_SEC_FETCH_DEST'] ) && 'iframe' === $_SERVER['HTTP_SEC_FETCH_DEST'];

			if ( ! $is_iframe ) {
				wp_safe_redirect( mvr_get_page_permalink( 'dashboard' ) );
				exit();
			}

			$mvr_access = isset( $_REQUEST['_mvr_access'] ) ? sanitize_key( wp_unslash( $_REQUEST['_mvr_access'] ) ) : '';

			if ( ! wp_verify_nonce( $mvr_access, 'mvr-vendor-access' ) ) {
				wp_die( esc_html__( 'You are not allowed to access this page.', 'multi-vendor-marketplace' ) );
				return false;
			}
		}

		/**
		 * Get the plugin url.
		 *
		 * @since 1.0.0
		 * @return string
		 */
		public function plugin_url() {
			return untrailingslashit( plugins_url( '/', MVR_PLUGIN_FILE ) );
		}


		/**
		 * Get the plugin path.
		 *
		 * @since 1.0.0
		 * @return String
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( MVR_PLUGIN_FILE ) );
		}

		/**
		 * Templates.
		 *
		 * @since 1.0.0
		 * @return HTML
		 * */
		public function templates() {
			return MVR_PLUGIN_PATH . '/templates/';
		}

		/**
		 * Get the template path.
		 *
		 * @since 1.0.0
		 * @return String
		 */
		public function template_path() {
			return trailingslashit( dirname( MVR_PLUGIN_SLUG ) );
		}

		/**
		 * Notifications instances.
		 *
		 * @since 1.0.0
		 * */
		public function notifications() {
			return $this->notifications;
		}
	}
}
