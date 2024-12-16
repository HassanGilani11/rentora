<?php
/**
 * Vendor Staff Section
 *
 * @package Multi Vendor Marketplace/Setting Tab/Vendor Staff Section
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Admin_Settings_Vendor_Staff' ) ) {
	/**
	 * Vendor Staff Tab.
	 *
	 * @class MVR_Admin_Settings_Vendor_Staff
	 * @package Class
	 */
	class MVR_Admin_Settings_Vendor_Staff extends MVR_Abstract_Settings {
		/**
		 * MVR_Admin_Settings_Vendor_Staff constructor.
		 */
		public function __construct() {
			$this->id            = 'vendor_staff';
			$this->label         = __( 'Vendor Staff', 'multi-vendor-marketplace' );
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
			return array(
				array(
					'type'  => 'title',
					'title' => esc_html__( 'Vendor Staff', 'multi-vendor-marketplace' ),
					'id'    => 'mvr_vendor_staff_options',
				),
				array(
					'title'   => esc_html__( 'Allow Vendors to Create Staff for Managing their Shop', 'multi-vendor-marketplace' ),
					'type'    => 'checkbox',
					'default' => 'no',
					'id'      => $this->get_option_key( 'allow_vendor_staff' ),
				),
				array(
					'type' => 'sectionend',
					'id'   => 'mvr_vendor_staff_options',
				),
			);
		}
	}

	return new MVR_Admin_Settings_Vendor_Staff();
}
