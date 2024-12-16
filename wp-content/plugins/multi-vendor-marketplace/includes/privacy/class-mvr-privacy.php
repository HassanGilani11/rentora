<?php
/**
 * GDPR Compliance.
 *
 * @package Multi-Vendor/GDPR
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Privacy' ) ) {

	/**
	 * Main class.
	 * */
	class MVR_Privacy {

		/**
		 * Constructor.
		 * */
		public function __construct() {
			$this->init_hooks();
		}

		/**
		 * Register plugin.
		 *
		 * @since 1.0.0
		 * */
		public function init_hooks() {
			// This hook registers Booking System privacy content.
			add_action( 'admin_init', array( __CLASS__, 'register_privacy_content' ), 20 );
		}

		/**
		 * Register Privacy Content.
		 *
		 * @since 1.0.0
		 * */
		public static function register_privacy_content() {
			if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
				return;
			}

			$content = self::get_privacy_message();

			if ( $content ) {
				wp_add_privacy_policy_content( esc_html__( 'Multi Vendor Marketplace', 'multi-vendor-marketplace' ), $content );
			}
		}

		/**
		 * Prepare Privacy Content.
		 *
		 * @since 1.0.0
		 * */
		public static function get_privacy_message() {
			return self::get_privacy_message_html();
		}

		/**
		 * Get Privacy Content.
		 *
		 * @since 1.0.0
		 * */
		public static function get_privacy_message_html() {
			ob_start();
			?>
			<p><?php esc_html_e( 'This includes the basics of what personal data your store may be collecting, storing and sharing. Depending on what settings are enabled and which additional plugins are used, the specific information shared by your store will vary.', 'multi-vendor-marketplace' ); ?></p>
			<h2><?php esc_html_e( 'WHAT DOES THE PLUGIN DO?', 'multi-vendor-marketplace' ); ?></h2>
			<p><?php esc_html_e( 'Using Multi Vendor Marketplace for WooCommerce you can convert your existing Woocommerce shop into a Multi Vendor Market', 'multi-vendor-marketplace' ); ?> </p>
			<h2><?php esc_html_e( 'WHAT WE COLLECT AND STORE?', 'multi-vendor-marketplace' ); ?></h2>
			<h3><?php esc_html_e( 'Username', 'multi-vendor-marketplace' ); ?></h3>
			<p><?php esc_html_e( 'To identify the Vendors within the Plugin.', 'multi-vendor-marketplace' ); ?> </p> 
			<h3><?php esc_html_e( 'Vendor Related Info', 'multi-vendor-marketplace' ); ?></h3>
			<p><?php echo wp_kses_post( __( 'We record the following details provided by the Vendor. This information is presented to the user on the Vendor Profile. First Name, Last Name, Door Number, Street, City, Country, State, Zip Code, Phone, Facebook Profile URL, X Profile URL, Youtube Profile URL, Instagram Profile URL, Linkedin Profile URL, Pinterest Profile URL', 'multi-vendor-marketplace' ) ); ?></p>
			<?php
			$contents = ob_get_contents();
			ob_end_clean();

			return $contents;
		}
	}

	new MVR_Privacy();
}
