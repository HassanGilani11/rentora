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
<div id="payout_vendor_data" class="panel woocommerce_options_panel">
	<div class="options_group mvr-vendor-payout">
		<h4><?php esc_html_e( 'Payout', 'multi-vendor-marketplace' ); ?></h4>
		<?php
		woocommerce_wp_select(
			array(
				'id'      => '_payout_type',
				'label'   => __( 'Payout Type', 'multi-vendor-marketplace' ),
				'value'   => $vendor_obj->get_payout_type(),
				'options' => mvr_payout_type_options(),
				'class'   => 'mvr-payout-type',
			)
		);

		if ( mvr_check_is_array( mvr_prepare_payout_schedule_options() ) ) {
			woocommerce_wp_select(
				array(
					'id'      => '_payout_schedule',
					'label'   => __( 'Payout Schedule', 'multi-vendor-marketplace' ),
					'value'   => $vendor_obj->get_payout_schedule(),
					'options' => mvr_prepare_payout_schedule_options(),
					'class'   => 'mvr-payout-schedule mvr-auto-payout-field',
				)
			);
		}

		/**
		 * Vendor Payment Type Option.
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_vendor_options_payment_type' );
		?>
	</div>

	<?php
	/**
	 * Vendor Options payout data.
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_vendor_options_payout_data' );
	?>
</div>
