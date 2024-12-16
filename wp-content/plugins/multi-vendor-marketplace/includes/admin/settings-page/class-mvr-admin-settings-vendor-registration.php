<?php
/**
 * Vendor Registration Section
 *
 * @package Multi Vendor Marketplace/Setting Tab/Vendor Registration Section
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Admin_Settings_Vendor_Registration' ) ) {
	/**
	 * Vendor Registration Tab.
	 *
	 * @class MVR_Admin_Settings_Vendor_Registration
	 * @package Class
	 */
	class MVR_Admin_Settings_Vendor_Registration extends MVR_Abstract_Settings {
		/**
		 * MVR_Admin_Settings_Vendor_Registration constructor.
		 */
		public function __construct() {
			$this->id            = 'vendor_registration';
			$this->label         = __( 'Vendor Registration', 'multi-vendor-marketplace' );
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
			 * Vendor Registration Settings
			 *
			 * @since 1.0.0
			 */
			return apply_filters(
				'mvr_get_' . $this->id . '_settings',
				array(
					array(
						'type'  => 'title',
						'title' => esc_html__( 'Vendor Registration Settings', 'multi-vendor-marketplace' ),
						'id'    => 'mvr_vendor_registration_options',
					),
					array(
						'title'   => esc_html__( 'Allow Logged-In Users to Register as Vendor', 'multi-vendor-marketplace' ),
						'class'   => 'mvr-settings-allow-user-register',
						'type'    => 'checkbox',
						'default' => 'yes',
						'id'      => $this->get_option_key( 'allow_user_vendor_reg' ),
					),
					array(
						'title'   => esc_html__( 'Allowed Logged-In User role to become a vendor', 'multi-vendor-marketplace' ),
						'type'    => 'multiselect',
						'class'   => 'mvr-select2 mvr-settings-become-vendor-role',
						'options' => mvr_get_site_user_roles(),
						'id'      => $this->get_option_key( 'become_a_vendor_roles' ),
						'default' => array( 'customer' ),
					),
					array(
						'title'   => esc_html__( 'Allow Guest to Register as Vendor', 'multi-vendor-marketplace' ),
						'class'   => 'mvr-settings-allow-guest-register',
						'type'    => 'checkbox',
						'default' => 'no',
						'id'      => $this->get_option_key( 'allow_guest_vendor_reg' ),
					),
					array(
						'title'   => esc_html__( 'Vendor Application Should be Approved by Site Admin', 'multi-vendor-marketplace' ),
						'type'    => 'checkbox',
						'default' => 'yes',
						'id'      => $this->get_option_key( 'vendor_admin_approval_req' ),
					),
					array(
						'title'   => esc_html__( 'Remind Vendors to Accept the Terms & Conditions and Privacy Policy when Updated', 'multi-vendor-marketplace' ),
						'type'    => 'checkbox',
						'default' => 'no',
						'id'      => $this->get_option_key( 'vendor_mandatory_terms_condition' ),
					),
					array(
						'type' => 'sectionend',
						'id'   => 'mvr_vendor_registration_options',
					),
					array(
						'type'  => 'title',
						'title' => esc_html__( 'Registration Fields', 'multi-vendor-marketplace' ),
						'id'    => 'mvr_vendor_registration_options',
					),
					array(
						'title'   => esc_html__( 'Email Address Field', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'default' => 'Email Address',
						'id'      => $this->get_option_key( 'vendor_email_field' ),
					),
					array(
						'title'   => esc_html__( 'Create Password Field', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'default' => 'Create Password',
						'id'      => $this->get_option_key( 'vendor_password_field' ),
					),
					array(
						'title'   => esc_html__( 'Confirm Password Field', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'default' => 'Confirm Password',
						'id'      => $this->get_option_key( 'vendor_confirm_password_field' ),
					),
					array(
						'title'   => esc_html__( 'Vendor Name Field', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'default' => 'Vendor Name',
						'id'      => $this->get_option_key( 'vendor_vendor_name_field' ),
					),
					array(
						'title'   => esc_html__( 'Store Name Field', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'default' => 'Store Name',
						'id'      => $this->get_option_key( 'vendor_store_name_field' ),
					),
					array(
						'title'   => esc_html__( 'Store Slug Field', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'default' => 'Store Slug',
						'id'      => $this->get_option_key( 'vendor_store_slug_field' ),
					),
					array(
						'type' => 'sectionend',
						'id'   => 'mvr_vendor_registration_field_options',
					),
				)
			);
		}
	}

	return new MVR_Admin_Settings_Vendor_Registration();
}
