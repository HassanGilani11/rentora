<?php
/**
 * Profile Management Section
 *
 * @package Multi Vendor Marketplace/Setting Tab/Profile Management Section
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Admin_Settings_Profile_Management' ) ) {
	/**
	 * Profile Management Tab.
	 *
	 * @class MVR_Admin_Settings_Profile_Management
	 * @package Class
	 */
	class MVR_Admin_Settings_Profile_Management extends MVR_Abstract_Settings {
		/**
		 * MVR_Admin_Settings_Profile_Management constructor.
		 */
		public function __construct() {
			$this->id            = 'profile_management';
			$this->label         = __( 'Profile Management', 'multi-vendor-marketplace' );
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
			 * Profile Management Setting.
			 *
			 * @since 1.0.0
			 */
			return apply_filters(
				'mvr_get_' . $this->id . '_settings',
				array(
					array(
						'type'  => 'title',
						'title' => esc_html__( 'Profile Management Settings', 'multi-vendor-marketplace' ),
						'id'    => 'mvr_vendor_profile_options',
					),
					array(
						'title'   => esc_html__( 'Display Vendor\'s Review', 'multi-vendor-marketplace' ),
						'type'    => 'checkbox',
						'default' => 'no',
						'id'      => $this->get_option_key( 'disp_vendor_review' ),
					),
					array(
						'title'   => esc_html__( 'Display Vendor\'s Address', 'multi-vendor-marketplace' ),
						'type'    => 'checkbox',
						'default' => 'yes',
						'id'      => $this->get_option_key( 'disp_vendor_address' ),
					),
					array(
						'title'   => esc_html__( 'Display Vendor\'s Email', 'multi-vendor-marketplace' ),
						'type'    => 'checkbox',
						'default' => 'no',
						'id'      => $this->get_option_key( 'disp_vendor_email' ),
					),
					array(
						'title'   => esc_html__( 'Display Vendor\'s Contact Number', 'multi-vendor-marketplace' ),
						'type'    => 'checkbox',
						'default' => 'no',
						'id'      => $this->get_option_key( 'disp_vendor_contact' ),
					),
					array(
						'title'   => esc_html__( 'Display Vendor\'s Social Links', 'multi-vendor-marketplace' ),
						'type'    => 'checkbox',
						'default' => 'yes',
						'id'      => $this->get_option_key( 'disp_vendor_social_link' ),
					),
					array(
						'title'   => esc_html__( 'Display Vendor\'s Products', 'multi-vendor-marketplace' ),
						'type'    => 'checkbox',
						'default' => 'yes',
						'id'      => $this->get_option_key( 'disp_vendor_product_list' ),
					),
					array(
						'title'   => esc_html__( 'Display Vendor\'s Policies', 'multi-vendor-marketplace' ),
						'type'    => 'checkbox',
						'default' => 'yes',
						'id'      => $this->get_option_key( 'disp_vendor_policy' ),
					),
					array(
						'title'   => esc_html__( 'Display Enquiry Form', 'multi-vendor-marketplace' ),
						'type'    => 'checkbox',
						'default' => 'yes',
						'id'      => $this->get_option_key( 'disp_vendor_enquiry_form' ),
					),
					array(
						'type' => 'sectionend',
						'id'   => 'mvr_vendor_registration_options',
					),
				)
			);
		}
	}

	return new MVR_Admin_Settings_Profile_Management();
}
