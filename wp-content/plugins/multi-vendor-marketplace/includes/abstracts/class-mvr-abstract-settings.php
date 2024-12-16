<?php
/**
 * General Section
 *
 * @package Multi Vendor Marketplace/Setting Tab/General Section
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Abstract_Settings' ) ) {
	/**
	 * Abstract Multi Vendor Marketplace Settings Page
	 *
	 * @abstract MVR_Abstract_Settings
	 */
	abstract class MVR_Abstract_Settings {

		/**
		 * Page id.
		 *
		 * @var String
		 */
		protected $id = '';

		/**
		 * Setting page label.
		 *
		 * @var String
		 */
		protected $label = '';

		/**
		 * Get settings.
		 *
		 * @var Array
		 */
		public $settings = array();

		/**
		 * Get custom field type[].
		 *
		 * @var Array
		 */
		protected $custom_fields = array();

		/**
		 * Get custom field option[].
		 *
		 * @var Array
		 */
		protected $custom_fields_option = array();

		/**
		 * Get custom settings field prefix.
		 *
		 * @var String
		 */
		protected $custom_field_prefix = 'woocommerce_admin_field_';

		/**
		 * Plugin slug.
		 *
		 * @var String
		 * */
		protected $plugin_slug = 'mvr';

		/**
		 * Init setting page.
		 *
		 * @since 1.0.0
		 */
		protected function init() {
			global $current_tab;

			add_filter( 'mvr_settings_tabs_array', array( $this, 'add_settings_page' ) );
			add_action( 'mvr_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'mvr_add_options_' . $this->id, array( $this, 'add_options' ) );
			add_action( 'mvr_update_options_' . $this->id, array( $this, 'save' ) );
			add_action( 'mvr_reset_options_' . $this->id, array( $this, 'reset' ) );

			foreach ( $this->custom_fields as $type ) {
				if ( $current_tab === $this->id ) {
					add_action( $this->get_custom_field_hook( $type ), array( $this, $type ) );
				}
			}
		}

		/**
		 * Get settings page ID.
		 *
		 * @since 1.0.0
		 * @return String
		 */
		public function get_id() {
			return $this->id;
		}

		/**
		 * Get settings page label.
		 *
		 * @since 1.0.0
		 * @return String
		 */
		public function get_label() {
			return $this->label;
		}

		/**
		 * Get settings Array.
		 *
		 * @since 1.0.0
		 * @return Array
		 */
		public function get_settings() {
			return $this->settings;
		}

		/**
		 * Get custom field type hook
		 *
		 * @since 1.0.0
		 * @param String $type Field type.
		 * @return String
		 */
		public function get_custom_field_hook( $type ) {
			return $this->custom_field_prefix . $this->get_custom_field_type( $type );
		}

		/**
		 * Get custom field type
		 *
		 * @since 1.0.0
		 * @param string $type Field type.
		 * @return string
		 */
		public function get_custom_field_type( $type ) {
			return "mvr_{$type}";
		}

		/**
		 * Add this page to settings.
		 *
		 * @since 1.0.0
		 * @param Array $pages Pages.
		 * @return Array
		 */
		public function add_settings_page( $pages ) {
			$pages[ $this->id ] = $this->label;

			return $pages;
		}

		/**
		 * Output the settings.
		 *
		 * @since 1.0.0
		 */
		public function output() {
			woocommerce_admin_fields( $this->settings );
		}

		/**
		 * Looping through each settings fields and save the option once.
		 *
		 * @since 1.0.0
		 */
		public function add_options() {
			if ( is_callable( array( $this, 'custom_types_add_options' ) ) ) {
				$this->custom_types_add_options();
			}

			foreach ( $this->settings as $setting ) {
				if ( isset( $setting['id'], $setting['default'] ) ) {
					add_option( $setting['id'], $setting['default'] );
				}
			}
		}

		/**
		 * Save settings.
		 *
		 * @since 1.0.0
		 * @param Array $posted Posted Data.
		 */
		public function save( $posted ) {
			woocommerce_update_options( $this->settings );

			if ( is_callable( array( $this, 'custom_types_delete_options' ) ) ) {
				$this->custom_types_delete_options( $posted );
			}

			if ( is_callable( array( $this, 'custom_types_save' ) ) ) {
				$this->custom_types_save( $posted );
			}

			delete_option( 'mvr_flush_rewrite_rules' );
		}

		/**
		 * Reset settings.
		 *
		 * @since 1.0.0
		 * @param Array $posted Posted Data.
		 */
		public function reset( $posted ) {
			if ( is_callable( array( $this, 'custom_types_delete_options' ) ) ) {
				$this->custom_types_delete_options( $posted );
			}

			if ( is_callable( array( $this, 'custom_types_add_options' ) ) ) {
				$this->custom_types_add_options( $posted );
			}

			foreach ( $this->settings as $setting ) {
				if ( isset( $setting['id'], $setting['default'] ) ) {
					delete_option( $setting['id'] );
					add_option( $setting['id'], $setting['default'] );
				}
			}

			delete_option( 'mvr_flush_rewrite_rules' );
		}

		/**
		 * Get option key
		 *
		 * @since 1.0
		 * @param String $key Option Key.
		 * @return String
		 **/
		public function get_option_key( $key ) {
			return sanitize_key( $this->plugin_slug . '_settings_' . $key );
		}
	}
}
