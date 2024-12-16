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
<div id="withdraw_vendor_data" class="panel woocommerce_options_panel">
	<div class="options_group mvr-vendor-withdraw-charge">
		<h4><?php esc_html_e( 'Withdrawal Charge', 'multi-vendor-marketplace' ); ?></h4>
		<?php
		woocommerce_wp_select(
			array(
				'id'      => '_withdraw_from',
				'label'   => __( 'Withdrawal From', 'multi-vendor-marketplace' ),
				'value'   => $vendor_obj->get_withdraw_from(),
				'options' => mvr_commission_from_options(),
				'class'   => 'mvr-withdraw-from',
			)
		);

		woocommerce_wp_checkbox(
			array(
				'id'    => '_enable_withdraw_charge',
				'label' => __( 'Enable charge for Withdrawal request', 'multi-vendor-marketplace' ),
				'value' => $vendor_obj->get_enable_withdraw_charge(),
				'class' => 'mvr-enable-withdraw-charge mvr-withdraw-field',
			)
		);

		woocommerce_wp_select(
			array(
				'id'      => '_withdraw_charge_type',
				'label'   => __( 'Charging Type', 'multi-vendor-marketplace' ),
				'value'   => $vendor_obj->get_withdraw_charge_type(),
				'options' => mvr_withdraw_charge_type_options(),
				'class'   => 'mvr-withdraw-charge-field mvr-withdraw-field',
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'    => '_withdraw_charge_value',
				'label' => __( 'Value', 'multi-vendor-marketplace' ),
				'value' => $vendor_obj->get_withdraw_charge_value(),
				'class' => 'mvr-withdraw-charge-field mvr-withdraw-field wc_input_price',
			)
		);

		/**
		 * Vendor Options withdraw.
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_vendor_options_withdraw' );
		?>
	</div>

	<?php
	/**
	 * Vendor Options withdraw data.
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_vendor_options_withdraw_data' );
	?>
</div>
