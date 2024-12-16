<?php
/**
 * Admin View:
 *
 * @package Multi-Vendor for WooCommerce\Admin\Payout
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_script( 'mvr-generate-payout' );

?>
<div class="mvr-vendor-payout-wrap mvr-wrap wrap woocommerce">
	<h1><?php esc_html_e( 'Generate Payout', 'multi-vendor-marketplace' ); ?></h1>

	<div class="mvr-payout-wrapper woocommerce-exporter-wrapper">
		<form class="mvr-payout woocommerce-exporter">
			<header>
				<span class="spinner is-active"></span>
				<h2><?php esc_html_e( 'Generate Payout for each Vendor', 'multi-vendor-marketplace' ); ?></h2>
				<p><?php esc_html_e( 'This tool allows you to generate a payout for the unpaid Vendor earnings.', 'multi-vendor-marketplace' ); ?></p>
			</header>
			<section>
				<table class="mvr-payout-options form-table woocommerce-exporter-options">
					<tbody>
						<tr>
							<th scope="row">
								<label for="mvr_payout_payment_types"><?php esc_html_e( 'Payment Types for Payout', 'multi-vendor-marketplace' ); ?></label>
							</th>
							<td>
								<select id="mvr_payout_payment_types" class="mvr-payout-payment-types wc-enhanced-select" style="width:100%;" multiple data-placeholder="<?php esc_attr_e( 'Payout all payment methods', 'multi-vendor-marketplace' ); ?>">
									<?php
									foreach ( MVR_Admin_Exporters::get_payment_methods() as $value => $label ) {
										echo '<option value="' . esc_attr( $value ) . '">' . esc_html( $label ) . '</option>';
									}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="mvr_payout_vendors"><?php esc_html_e( 'Vendor(s) Selection', 'multi-vendor-marketplace' ); ?></label>
							</th>
							<td>
								<?php
								mvr_select2_html(
									array(
										'id'          => 'mvr_payout_vendors',
										'class'       => 'mvr-payout-vendors wc-product-search',
										'placeholder' => esc_html__( 'All Vendor(s)', 'multi-vendor-marketplace' ),
										'options'     => array(),
										'type'        => 'vendor',
										'action'      => 'mvr_json_search_vendors',
										'multiple'    => true,
									)
								);
								?>
							</td>
						</tr>
						<?php
						/**
						 * Generate Payout Row
						 *
						 * @since 1.0.0
						 * */
						do_action( 'mvr_vendor_generate_payout_row' );
						?>
					</tbody>
				</table>
				<progress class="mvr-payout-progress woocommerce-exporter-progress" max="100" value="0"></progress>
			</section>
			<div class="mvr-actions wc-actions">
				<button type="submit" class="woocommerce-payout-button button button-primary" value="<?php esc_attr_e( 'Generate CSV', 'multi-vendor-marketplace' ); ?>"><?php esc_html_e( 'Generate Payout', 'multi-vendor-marketplace' ); ?></button>
			</div>
		</form>
	</div>
</div>
