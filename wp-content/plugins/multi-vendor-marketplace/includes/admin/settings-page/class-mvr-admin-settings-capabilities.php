<?php
/**
 * Capabilities Section
 *
 * @package Multi Vendor Marketplace/Setting Tab/Vendor Capabilities Section
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Admin_Settings_Capabilities' ) ) {
	/**
	 * Capabilities Tab.
	 *
	 * @class MVR_Admin_Settings_Capabilities
	 * @package Class
	 */
	class MVR_Admin_Settings_Capabilities extends MVR_Abstract_Settings {
		/**
		 * MVR_Admin_Settings_Capabilities constructor.
		 */
		public function __construct() {
			$this->id            = 'capabilities';
			$this->label         = __( 'Capabilities', 'multi-vendor-marketplace' );
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
			 * Capabilities Settings Fields.
			 *
			 * @since 1.0.0
			 */
			return apply_filters(
				'mvr_get_' . $this->id . '_settings',
				array(
					array(
						'type'  => 'title',
						'title' => esc_html__( 'Product Management', 'multi-vendor-marketplace' ),
						'id'    => 'mvr_product_management_options',
					),
					array(
						'title'   => esc_html__( 'Product Management', 'multi-vendor-marketplace' ),
						'class'   => 'mvr-settings-enable-product-management',
						'type'    => 'checkbox',
						'default' => 'yes',
						'id'      => $this->get_option_key( 'enable_product_management' ),
					),
					array(
						'title'   => esc_html__( 'Product Creation', 'multi-vendor-marketplace' ),
						'class'   => 'mvr-settings-product-management-field',
						'type'    => 'checkbox',
						'default' => 'yes',
						'id'      => $this->get_option_key( 'enable_product_creation' ),
					),
					array(
						'title'   => esc_html__( 'Product Modification', 'multi-vendor-marketplace' ),
						'class'   => 'mvr-settings-product-management-field',
						'type'    => 'checkbox',
						'default' => 'yes',
						'id'      => $this->get_option_key( 'enable_product_modification' ),
					),
					array(
						'title'   => esc_html__( 'Published Product Modification', 'multi-vendor-marketplace' ),
						'class'   => 'mvr-settings-product-management-field',
						'type'    => 'checkbox',
						'default' => 'yes',
						'id'      => $this->get_option_key( 'enable_published_product_modification' ),
					),
					array(
						'title'   => esc_html__( 'Manage Inventory', 'multi-vendor-marketplace' ),
						'class'   => 'mvr-settings-product-management-field',
						'type'    => 'checkbox',
						'default' => 'yes',
						'id'      => $this->get_option_key( 'enable_manage_inventory' ),
					),
					array(
						'title'   => esc_html__( 'Product Deletion', 'multi-vendor-marketplace' ),
						'class'   => 'mvr-settings-product-management-field',
						'type'    => 'checkbox',
						'default' => 'no',
						'id'      => $this->get_option_key( 'enable_product_deletion' ),
					),
					array(
						'title'   => esc_html__( 'Allowed Product Types', 'multi-vendor-marketplace' ),
						'type'    => 'multiselect',
						'class'   => 'mvr-select2 mvr-settings-product-management-field',
						'default' => array( 'simple', 'variable' ),
						'id'      => $this->get_option_key( 'allowed_product_type' ),
						'options' => wc_get_product_types(),
					),
					array(
						'type' => 'sectionend',
						'id'   => 'mvr_product_management_options',
					),
					array(
						'type'  => 'title',
						'title' => esc_html__( 'Order Management', 'multi-vendor-marketplace' ),
						'id'    => 'mvr_order_management_options',
					),
					array(
						'title'   => esc_html__( 'Order Management', 'multi-vendor-marketplace' ),
						'class'   => 'mvr-settings-enable-order-management',
						'type'    => 'checkbox',
						'default' => 'yes',
						'id'      => $this->get_option_key( 'enable_order_management' ),
					),
					array(
						'title'   => esc_html__( 'Order Status Modification', 'multi-vendor-marketplace' ),
						'class'   => 'mvr-settings-order-management-field',
						'type'    => 'checkbox',
						'default' => 'yes',
						'id'      => $this->get_option_key( 'enable_order_status_management' ),
					),
					array(
						'title'   => esc_html__( 'Commission Info Display', 'multi-vendor-marketplace' ),
						'class'   => 'mvr-settings-order-management-field',
						'type'    => 'checkbox',
						'default' => 'yes',
						'id'      => $this->get_option_key( 'enable_commission_info_management' ),
					),
					array(
						'type' => 'sectionend',
						'id'   => 'mvr_order_management_options',
					),
					array(
						'type'  => 'title',
						'title' => esc_html__( 'Coupon Management', 'multi-vendor-marketplace' ),
						'id'    => 'mvr_coupon_management_options',
					),
					array(
						'title'   => esc_html__( 'Coupon Management', 'multi-vendor-marketplace' ),
						'class'   => 'mvr-settings-enable-coupon-management',
						'type'    => 'checkbox',
						'default' => 'no',
						'id'      => $this->get_option_key( 'enable_coupon_management' ),
					),
					array(
						'title'   => esc_html__( 'Coupon Creation', 'multi-vendor-marketplace' ),
						'class'   => 'mvr-settings-coupon-management-field',
						'type'    => 'checkbox',
						'default' => 'no',
						'id'      => $this->get_option_key( 'enable_coupon_creation_management' ),
					),
					array(
						'title'   => esc_html__( 'Coupon Modification', 'multi-vendor-marketplace' ),
						'class'   => 'mvr-settings-coupon-management-field',
						'type'    => 'checkbox',
						'default' => 'no',
						'id'      => $this->get_option_key( 'enable_coupon_modification_management' ),
					),
					array(
						'title'   => esc_html__( 'Published Coupon Modification', 'multi-vendor-marketplace' ),
						'class'   => 'mvr-settings-coupon-management-field',
						'type'    => 'checkbox',
						'default' => 'no',
						'id'      => $this->get_option_key( 'enable_published_coupon_modification' ),
					),
					array(
						'title'   => esc_html__( 'Coupon Deletion', 'multi-vendor-marketplace' ),
						'class'   => 'mvr-settings-coupon-management-field',
						'type'    => 'checkbox',
						'default' => 'no',
						'id'      => $this->get_option_key( 'enable_coupon_deletion_management' ),
					),
					array(
						'type' => 'sectionend',
						'id'   => 'mvr_coupon_management_options',
					),
				)
			);
		}
	}

	return new MVR_Admin_Settings_Capabilities();
}
