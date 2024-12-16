<?php
/**
 * Vendor profile data panel.
 *
 * @package Multi-Vendor for WooCommerce\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div id="commission_vendor_data" class="panel woocommerce_options_panel">
	<div class="options_group mvr-vendor-commission">
		<h4><?php esc_html_e( 'Commission', 'multi-vendor-marketplace' ); ?></h4>
		<?php
		woocommerce_wp_select(
			array(
				'id'      => '_commission_from',
				'label'   => __( 'Commission From', 'multi-vendor-marketplace' ),
				'value'   => $vendor_obj->get_commission_from(),
				'options' => mvr_commission_from_options(),
				'class'   => 'mvr-vendor-commission-from',
			)
		);

		woocommerce_wp_select(
			array(
				'id'      => '_commission_criteria',
				'label'   => __( 'Admin Commission is Calculated', 'multi-vendor-marketplace' ),
				'value'   => $vendor_obj->get_commission_criteria(),
				'options' => mvr_commission_criteria_options(),
				'class'   => 'mvr-commission-field mvr-commission-criteria',
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'    => '_commission_criteria_value',
				'label' => __( 'Value', 'multi-vendor-marketplace' ),
				'value' => $vendor_obj->get_commission_criteria_value(),
				'class' => 'mvr-commission-field mvr-commission-criteria-field',
			)
		);

		woocommerce_wp_select(
			array(
				'id'      => '_commission_type',
				'label'   => __( 'Commission Type', 'multi-vendor-marketplace' ),
				'value'   => $vendor_obj->get_commission_type(),
				'options' => mvr_commission_type_options(),
				'class'   => 'mvr-commission-field mvr_vendor_commission_type',
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'          => '_commission_value',
				'value'       => $vendor_obj->get_commission_value(),
				'label'       => __( 'Value', 'multi-vendor-marketplace' ),
				'description' => __( 'Value.', 'multi-vendor-marketplace' ),
				'class'       => 'mvr-commission-field',
			)
		);

		woocommerce_wp_select(
			array(
				'id'      => '_tax_to',
				'label'   => __( 'Tax Calculation', 'multi-vendor-marketplace' ),
				'value'   => $vendor_obj->get_tax_to(),
				'options' => mvr_tax_type_options(),
				'class'   => 'mvr-commission-field',
			)
		);

		woocommerce_wp_checkbox(
			array(
				'id'    => '_commission_after_coupon',
				'label' => esc_html__( 'Calculate Commission after Applying Admin Created Coupons', 'multi-vendor-marketplace' ),
				'value' => $vendor_obj->get_commission_after_coupon(),
				'class' => 'mvr-commission-field',
			)
		);

		woocommerce_wp_checkbox(
			array(
				'id'    => '_commission_after_vendor_coupon',
				'label' => esc_html__( 'Calculate Commission after Applying Vendor Created Coupons', 'multi-vendor-marketplace' ),
				'value' => $vendor_obj->get_commission_after_vendor_coupon(),
				'class' => 'mvr-commission-field',
			)
		);

		/**
		 * Vendor Capability Commission Data.
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_vendor_options_commission' );
		?>
	</div>

	<?php
	/**
	 * Vendor Capability Commission Data.
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_vendor_options_commission_data' );
	?>
</div>
