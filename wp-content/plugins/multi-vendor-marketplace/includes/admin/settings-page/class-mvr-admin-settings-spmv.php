<?php
/**
 * Single Product Multiple Vendor Section
 *
 * @package Multi Vendor Marketplace/Setting Tab/SPMV Section
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Admin_Settings_SPMV' ) ) {
	/**
	 * SPMV Tab.
	 *
	 * @class MVR_Admin_Settings_SPMV
	 * @package Class
	 */
	class MVR_Admin_Settings_SPMV extends MVR_Abstract_Settings {
		/**
		 * MVR_Admin_Settings_SPMV constructor.
		 */
		public function __construct() {
			$this->id            = 'spmv';
			$this->label         = __( 'Single Product Multiple Vendors', 'multi-vendor-marketplace' );
			$this->custom_fields = array();
			$this->settings      = $this->get_settings();
			$this->init();
		}

		/**
		 * Get settings array.
		 *
		 * @since 1.0.0
		 * @return Array
		 */
		public function get_settings() {
			/**
			 * General Settings Fields.
			 *
			 * @since 1.0.0
			 */
			return apply_filters(
				'mvr_get_' . $this->id . '_settings',
				array(
					array(
						'type'  => 'title',
						'title' => esc_html__( 'Single Product Multi Vendor Settings', 'multi-vendor-marketplace' ),
						'id'    => 'mvr_spmv_options',
					),
					array(
						'title'   => esc_html__( 'Enable Single Product Multiple Vendors Settings', 'multi-vendor-marketplace' ),
						'type'    => 'checkbox',
						'default' => 'no',
						'id'      => $this->get_option_key( 'allow_spmv' ),
					),
					array(
						'type' => 'sectionend',
						'id'   => 'mvr_spmv_options',
					),
				)
			);
		}
	}

	return new MVR_Admin_Settings_SPMV();
}
