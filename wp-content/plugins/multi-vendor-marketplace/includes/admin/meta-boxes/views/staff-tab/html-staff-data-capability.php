<?php
/**
 * Staff capability data panel.
 *
 * @package Multi-Vendor for WooCommerce\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div id="capabilities_staff_data" class="panel woocommerce_options_panel">
	<?php if ( ! mvr_is_vendor( $vendor_obj ) ) : ?>
		<div class="options_group mvr-staff-capability">
			<?php esc_html_e( 'Vendor needs to be selected first', 'multi-vendor-marketplace' ); ?>
		</div>
		<?php
	else :
		if ( $staff_obj->allow_product_management() ) :
			?>
			<div class="options_group mvr-staff-capability">
				<h4><?php esc_html_e( 'Product Management', 'multi-vendor-marketplace' ); ?></h4>
					<?php
					if ( 'yes' === $vendor_obj->get_enable_product_management() ) :
						woocommerce_wp_checkbox(
							array(
								'id'          => '_enable_product_management',
								'value'       => $staff_obj->get_enable_product_management(),
								'label'       => __( 'Product Management', 'multi-vendor-marketplace' ),
								'description' => __( 'Product Management', 'multi-vendor-marketplace' ),
							)
						);
					endif;

					if ( 'yes' === $vendor_obj->get_product_creation() ) :
						woocommerce_wp_checkbox(
							array(
								'id'          => '_product_creation',
								'value'       => $staff_obj->get_product_creation(),
								'label'       => __( 'Product Creation', 'multi-vendor-marketplace' ),
								'description' => __( 'Product Creation', 'multi-vendor-marketplace' ),
							)
						);
					endif;

					if ( 'yes' === $vendor_obj->get_product_modification() ) :
						woocommerce_wp_checkbox(
							array(
								'id'          => '_product_modification',
								'value'       => $staff_obj->get_product_modification(),
								'label'       => __( 'Product Modification', 'multi-vendor-marketplace' ),
								'description' => __( 'Product Modification', 'multi-vendor-marketplace' ),
							)
						);
					endif;

					if ( 'yes' === $vendor_obj->get_published_product_modification() ) :
						woocommerce_wp_checkbox(
							array(
								'id'          => '_published_product_modification',
								'value'       => $staff_obj->get_published_product_modification(),
								'label'       => __( 'Published Product Modification', 'multi-vendor-marketplace' ),
								'description' => __( 'Published Product Modification', 'multi-vendor-marketplace' ),
							)
						);
					endif;

					if ( 'yes' === $vendor_obj->get_manage_inventory() ) :
						woocommerce_wp_checkbox(
							array(
								'id'          => '_manage_inventory',
								'value'       => $staff_obj->get_manage_inventory(),
								'label'       => __( 'Manage Inventory', 'multi-vendor-marketplace' ),
								'description' => __( 'Manage Inventory', 'multi-vendor-marketplace' ),
							)
						);
					endif;

					if ( 'yes' === $vendor_obj->get_product_deletion() ) :
						woocommerce_wp_checkbox(
							array(
								'id'          => '_product_deletion',
								'value'       => $staff_obj->get_product_deletion(),
								'label'       => __( 'Product Deletion', 'multi-vendor-marketplace' ),
								'description' => __( 'Product Deletion', 'multi-vendor-marketplace' ),
							)
						);
					endif;

					/**
					 * Vendor Capability Product Management Data.
					 *
					 * @since 1.0.0
					 */
					do_action( 'mvr_vendor_options_product_management_capability' );
					?>
			</div>
			<?php
		endif;

		if ( $staff_obj->allow_order_management() ) :
			?>
			<div class="options_group mvr-staff-order-management">
				<h4><?php esc_html_e( 'Order Management', 'multi-vendor-marketplace' ); ?></h4>
			<?php
			if ( 'yes' === $vendor_obj->get_enable_order_management() ) :
				woocommerce_wp_checkbox(
					array(
						'id'          => '_enable_order_management',
						'value'       => $staff_obj->get_enable_order_management(),
						'label'       => __( 'Order Management', 'multi-vendor-marketplace' ),
						'description' => __( 'Order Management', 'multi-vendor-marketplace' ),
					)
				);
			endif;

			if ( 'yes' === $vendor_obj->get_order_status_modification() ) :
				woocommerce_wp_checkbox(
					array(
						'id'          => '_order_status_modification',
						'value'       => $staff_obj->get_order_status_modification(),
						'label'       => __( 'Order Status Modification', 'multi-vendor-marketplace' ),
						'description' => __( 'Order Status Modification', 'multi-vendor-marketplace' ),
					)
				);
			endif;

			if ( 'yes' === $vendor_obj->get_commission_info_display() ) :
				woocommerce_wp_checkbox(
					array(
						'id'          => '_commission_info_display',
						'value'       => $staff_obj->get_commission_info_display(),
						'label'       => __( 'Commission Info Display', 'multi-vendor-marketplace' ),
						'description' => __( 'Commission Info Display', 'multi-vendor-marketplace' ),
					)
				);
			endif;

			/**
			 * Vendor Capability Order Management Data.
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_vendor_options_order_management_capability' );
			?>
			</div>
			<?php
		endif;

		if ( $staff_obj->allow_coupon_management() ) :
			?>
			<div class="options_group mvr-staff-coupon-management">
				<h4><?php esc_html_e( 'Coupon Management', 'multi-vendor-marketplace' ); ?></h4>
			<?php
			if ( 'yes' === $vendor_obj->get_enable_coupon_management() ) :
				woocommerce_wp_checkbox(
					array(
						'id'          => '_enable_coupon_management',
						'value'       => $staff_obj->get_enable_coupon_management(),
						'class'       => 'mvr-enable-coupon-management',
						'label'       => __( 'Coupon Management', 'multi-vendor-marketplace' ),
						'description' => __( 'Coupon Management', 'multi-vendor-marketplace' ),
					)
				);
				endif;

			if ( 'yes' === $vendor_obj->get_coupon_creation() ) :
				woocommerce_wp_checkbox(
					array(
						'id'          => '_coupon_creation',
						'value'       => $staff_obj->get_coupon_creation(),
						'class'       => 'mvr-coupon-management-field',
						'label'       => __( 'Coupon Creation', 'multi-vendor-marketplace' ),
						'description' => __( 'Coupon Creation', 'multi-vendor-marketplace' ),
					)
				);
				endif;

			if ( 'yes' === $vendor_obj->get_coupon_modification() ) :
				woocommerce_wp_checkbox(
					array(
						'id'          => '_coupon_modification',
						'value'       => $staff_obj->get_coupon_modification(),
						'class'       => 'mvr-coupon-management-field',
						'label'       => __( 'Coupon Modification', 'multi-vendor-marketplace' ),
						'description' => __( 'Coupon Modification', 'multi-vendor-marketplace' ),
					)
				);
			endif;

			if ( 'yes' === $vendor_obj->get_published_coupon_modification() ) :
				woocommerce_wp_checkbox(
					array(
						'id'          => '_published_coupon_modification',
						'value'       => $staff_obj->get_published_coupon_modification(),
						'class'       => 'mvr-coupon-management-field',
						'label'       => __( 'Published Coupon Modification', 'multi-vendor-marketplace' ),
						'description' => __( 'Published Coupon Modification', 'multi-vendor-marketplace' ),
					)
				);
			endif;

			if ( 'yes' === $vendor_obj->get_coupon_deletion() ) :
				woocommerce_wp_checkbox(
					array(
						'id'          => '_coupon_deletion',
						'value'       => $staff_obj->get_coupon_deletion(),
						'class'       => 'mvr-coupon-management-field',
						'label'       => __( 'Coupon Deletion', 'multi-vendor-marketplace' ),
						'description' => __( 'Coupon Deletion', 'multi-vendor-marketplace' ),
					)
				);
			endif;

			/**
			 * Vendor Capability Coupon Data.
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_vendor_options_coupon_management_capability' );
			?>
			</div>
			<?php
		endif;
	endif;
	/**
	 * Vendor Capability Data.
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_vendor_options_capability_data' );
	?>
</div>
