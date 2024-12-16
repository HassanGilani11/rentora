<?php
/**
 * Vendor capability data panel.
 *
 * @package Multi-Vendor for WooCommerce\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div id="capabilities_vendor_data" class="panel woocommerce_options_panel">
	<div class="options_group mvr-vendor-product-management">
		<h4><?php esc_html_e( 'Product Management', 'multi-vendor-marketplace' ); ?></h4>
		<?php
		woocommerce_wp_checkbox(
			array(
				'id'          => '_enable_product_management',
				'value'       => $vendor_obj->get_enable_product_management(),
				'class'       => 'mvr-enable-product-management',
				'label'       => __( 'Product Management', 'multi-vendor-marketplace' ),
				'description' => __( 'Product Management', 'multi-vendor-marketplace' ),
			)
		);

		woocommerce_wp_checkbox(
			array(
				'id'          => '_product_creation',
				'value'       => $vendor_obj->get_product_creation(),
				'class'       => 'mvr-product-management-field',
				'label'       => __( 'Product Creation', 'multi-vendor-marketplace' ),
				'description' => __( 'Product Creation', 'multi-vendor-marketplace' ),
			)
		);

		woocommerce_wp_checkbox(
			array(
				'id'          => '_product_modification',
				'value'       => $vendor_obj->get_product_modification(),
				'class'       => 'mvr-product-management-field',
				'label'       => __( 'Product Modification', 'multi-vendor-marketplace' ),
				'description' => __( 'Product Modification', 'multi-vendor-marketplace' ),
			)
		);

		woocommerce_wp_checkbox(
			array(
				'id'          => '_published_product_modification',
				'value'       => $vendor_obj->get_published_product_modification(),
				'class'       => 'mvr-product-management-field',
				'label'       => __( 'Published Product Modification', 'multi-vendor-marketplace' ),
				'description' => __( 'Published Product Modification', 'multi-vendor-marketplace' ),
			)
		);

		woocommerce_wp_checkbox(
			array(
				'id'          => '_manage_inventory',
				'value'       => $vendor_obj->get_manage_inventory(),
				'class'       => 'mvr-product-management-field',
				'label'       => __( 'Manage Inventory', 'multi-vendor-marketplace' ),
				'description' => __( 'Manage Inventory', 'multi-vendor-marketplace' ),
			)
		);

		woocommerce_wp_checkbox(
			array(
				'id'          => '_product_deletion',
				'value'       => $vendor_obj->get_product_deletion(),
				'class'       => 'mvr-product-management-field',
				'label'       => __( 'Product Deletion', 'multi-vendor-marketplace' ),
				'description' => __( 'Product Deletion', 'multi-vendor-marketplace' ),
			)
		);

		/**
		 * Vendor Capability Product Management Data.
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_vendor_options_product_management_capability' );
		?>
	</div>

	<div class="options_group mvr-vendor-order-management">
		<h4><?php esc_html_e( 'Order Management', 'multi-vendor-marketplace' ); ?></h4>
		<?php
		woocommerce_wp_checkbox(
			array(
				'id'          => '_enable_order_management',
				'value'       => $vendor_obj->get_enable_order_management(),
				'class'       => 'mvr-enable-order-management',
				'label'       => __( 'Order Management', 'multi-vendor-marketplace' ),
				'description' => __( 'Order Management', 'multi-vendor-marketplace' ),
			)
		);

		woocommerce_wp_checkbox(
			array(
				'id'          => '_order_status_modification',
				'value'       => $vendor_obj->get_order_status_modification(),
				'class'       => 'mvr-order-management-field',
				'label'       => __( 'Order Status Modification', 'multi-vendor-marketplace' ),
				'description' => __( 'Order Status Modification', 'multi-vendor-marketplace' ),
			)
		);

		woocommerce_wp_checkbox(
			array(
				'id'          => '_commission_info_display',
				'value'       => $vendor_obj->get_commission_info_display(),
				'class'       => 'mvr-order-management-field',
				'label'       => __( 'Commission Info Display', 'multi-vendor-marketplace' ),
				'description' => __( 'Commission Info Display', 'multi-vendor-marketplace' ),
			)
		);

		/**
		 * Vendor Capability Order Management Data.
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_vendor_options_order_management_capability' );
		?>
	</div>

	<div class="options_group mvr-vendor-coupon-management">
		<h4><?php esc_html_e( 'Coupon Management', 'multi-vendor-marketplace' ); ?></h4>
		<?php
		woocommerce_wp_checkbox(
			array(
				'id'          => '_enable_coupon_management',
				'value'       => $vendor_obj->get_enable_coupon_management(),
				'class'       => 'mvr-enable-coupon-management',
				'label'       => __( 'Coupon Management', 'multi-vendor-marketplace' ),
				'description' => __( 'Coupon Management', 'multi-vendor-marketplace' ),
			)
		);

		woocommerce_wp_checkbox(
			array(
				'id'          => '_coupon_creation',
				'value'       => $vendor_obj->get_coupon_creation(),
				'class'       => 'mvr-coupon-management-field',
				'label'       => __( 'Coupon Creation', 'multi-vendor-marketplace' ),
				'description' => __( 'Coupon Creation', 'multi-vendor-marketplace' ),
			)
		);

		woocommerce_wp_checkbox(
			array(
				'id'          => '_coupon_modification',
				'value'       => $vendor_obj->get_coupon_modification(),
				'class'       => 'mvr-coupon-management-field',
				'label'       => __( 'Coupon Modification', 'multi-vendor-marketplace' ),
				'description' => __( 'Coupon Modification', 'multi-vendor-marketplace' ),
			)
		);

		woocommerce_wp_checkbox(
			array(
				'id'          => '_published_coupon_modification',
				'value'       => $vendor_obj->get_published_coupon_modification(),
				'class'       => 'mvr-coupon-management-field',
				'label'       => __( 'Published Coupon Modification', 'multi-vendor-marketplace' ),
				'description' => __( 'Published Coupon Modification', 'multi-vendor-marketplace' ),
			)
		);

		woocommerce_wp_checkbox(
			array(
				'id'          => '_coupon_deletion',
				'value'       => $vendor_obj->get_coupon_deletion(),
				'class'       => 'mvr-coupon-management-field',
				'label'       => __( 'Coupon Deletion', 'multi-vendor-marketplace' ),
				'description' => __( 'Coupon Deletion', 'multi-vendor-marketplace' ),
			)
		);

		/**
		 * Vendor Capability Coupon Data.
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_vendor_options_coupon_management_capability' );
		?>
	</div>
	<?php
	/**
	 * Vendor Capability Data.
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_vendor_options_capability_data' );
	?>
</div>
