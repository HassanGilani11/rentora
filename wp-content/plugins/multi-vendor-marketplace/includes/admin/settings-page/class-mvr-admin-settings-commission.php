<?php
/**
 * Commission Section
 *
 * @package Multi Vendor Marketplace/Setting Tab/Commission Section
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Admin_Settings_Commission' ) ) {
	/**
	 * Commission Tab.
	 *
	 * @class MVR_Admin_Settings_Commission
	 * @package Class
	 */
	class MVR_Admin_Settings_Commission extends MVR_Abstract_Settings {
		/**
		 * MVR_Admin_Settings_Commission constructor.
		 */
		public function __construct() {
			$this->id            = 'commission';
			$this->label         = __( 'Commission', 'multi-vendor-marketplace' );
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
			 * Commission Setting.
			 *
			 * @since 1.0.0
			 */
			return apply_filters(
				'mvr_get_' . $this->id . '_settings',
				array(
					array(
						'type'  => 'title',
						'title' => esc_html__( 'Commission', 'multi-vendor-marketplace' ),
						'id'    => 'mvr_commission_options',
					),

					array(
						'title'   => esc_html__( 'Calculate the Admin Commission when the Order Status Reaches', 'multi-vendor-marketplace' ),
						'type'    => 'multiselect',
						'class'   => 'mvr-select2',
						'options' => mvr_get_success_order_statuses(),
						'id'      => $this->get_option_key( 'commission_order_status' ),
						'default' => array( 'completed' ),
					),
					array(
						'title'   => esc_html__( 'Admin Commission is Calculated', 'multi-vendor-marketplace' ),
						'type'    => 'select',
						'options' => mvr_commission_criteria_options(),
						'id'      => $this->get_option_key( 'commission_criteria' ),
						'class'   => 'mvr-commission-criteria',
						'default' => '1',
					),
					array(
						'title'   => esc_html__( 'Commission Criteria Value', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'id'      => $this->get_option_key( 'commission_criteria_value' ),
						'class'   => 'mvr-commission-criteria-field',
						'desc'    => esc_html__( 'Threshold Value', 'multi-vendor-marketplace' ),
						'default' => '',
					),
					array(
						'title'   => esc_html__( 'Admin Commission Type', 'multi-vendor-marketplace' ),
						'type'    => 'select',
						'options' => mvr_commission_type_options(),
						'id'      => $this->get_option_key( 'commission_type' ),
						'default' => '2',
					),
					array(
						'title'   => esc_html__( 'Admin Commission Value', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'id'      => $this->get_option_key( 'commission_value' ),
						'default' => '10',
					),
					array(
						'title'   => esc_html__( 'Tax Calculation', 'multi-vendor-marketplace' ),
						'type'    => 'select',
						'id'      => $this->get_option_key( 'commission_tax_to' ),
						'options' => mvr_tax_type_options(),
						'default' => '2',
					),
					array(
						'title'   => esc_html__( 'Calculate Commission after Applying Admin Created Coupons', 'multi-vendor-marketplace' ),
						'type'    => 'checkbox',
						'default' => 'yes',
						'id'      => $this->get_option_key( 'commission_after_coupon' ),
					),
					array(
						'title'   => esc_html__( 'Calculate Commission after Applying Vendor Created Coupons', 'multi-vendor-marketplace' ),
						'type'    => 'checkbox',
						'default' => 'yes',
						'id'      => $this->get_option_key( 'commission_after_vendor_coupon' ),
					),
					array(
						'type' => 'sectionend',
						'id'   => 'mvr_commission_options',
					),
				)
			);
		}
	}

	return new MVR_Admin_Settings_Commission();
}

return $section_fields;
