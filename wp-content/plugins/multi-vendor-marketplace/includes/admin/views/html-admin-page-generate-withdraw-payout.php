<?php
/**
 * Admin View:
 *
 * @package Multi-Vendor for WooCommerce\Admin\Payout
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_script( 'mvr-generate-withdraw-payout' );

?>
<div class="mvr-withdraw-payout-wrap mvr-wrap wrap woocommerce">
	<h1><?php esc_html_e( 'Generate Payout', 'multi-vendor-marketplace' ); ?></h1>

	<div class="mvr-withdraw-payout-wrapper woocommerce-exporter-wrapper">
		<form class="mvr-withdraw-payout woocommerce-exporter">
			<header>
				<span class="spinner is-active"></span>
				<h2><?php esc_html_e( 'Generate Payout for the Withdrawal Requests', 'multi-vendor-marketplace' ); ?></h2>
				<p><?php esc_html_e( 'This tool allows you to generate a payout for the submitted Withdrawal requests', 'multi-vendor-marketplace' ); ?></p>
			</header>
			<section>
				<table class="mvr-withdraw-payout-options form-table woocommerce-exporter-options">
					<tbody>
						<tr>
							<th scope="row">
								<label for="mvr_payout_vendor_type"><?php esc_html_e( 'Vendor(s) Selection', 'multi-vendor-marketplace' ); ?></label>
							</th>
							<td>
								<select id="mvr_payout_vendor_type" class="mvr-payout-vendor-type" style="width:100%;">
									<?php
									foreach ( MVR_Admin_Exporters::get_vendor_selection() as $value => $label ) {
										echo '<option value="' . esc_attr( $value ) . '">' . esc_html( $label ) . '</option>';
									}
									?>
								</select>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="mvr_payout_vendor_type"><?php esc_html_e( 'Include Vendor(s) ', 'multi-vendor-marketplace' ); ?></label>
							</th>
							<td>
								<?php
								mvr_select2_html(
									array(
										'name'        => '_mvr_payout_include_vendor',
										'class'       => 'mvr-payout-vendor-selection mvr-payout-include-vendor wc-product-search',
										'placeholder' => esc_html__( 'Search for a Vendor', 'multi-vendor-marketplace' ),
										'type'        => 'vendor',
										'action'      => 'mvr_json_search_vendors',
										'css'         => 'width:80%',
									)
								);
								?>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="mvr_payout_vendor_type"><?php esc_html_e( 'Exclude Vendor(s) ', 'multi-vendor-marketplace' ); ?></label>
							</th>
							<td>
								<?php
								mvr_select2_html(
									array(
										'name'        => '_mvr_payout_exclude_vendor',
										'class'       => 'mvr-payout-vendor-selection mvr-payout-exclude-vendor wc-product-search',
										'placeholder' => esc_html__( 'Search for a Vendor', 'multi-vendor-marketplace' ),
										'type'        => 'vendor',
										'action'      => 'mvr_json_search_vendors',
										'css'         => 'width:80%',
									)
								);
								?>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="mvr_payout_payment_types"><?php esc_html_e( 'Payment Type', 'multi-vendor-marketplace' ); ?></label>
							</th>
							<td>
								<select id="mvr_payout_payment_types" class="mvr-payout-payment-type" style="width:100%;">
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
								<label for="mvr_payout_from_date"><?php esc_html_e( 'From Date', 'multi-vendor-marketplace' ); ?></label>
							</th>
							<td>
								<input type="text" id="mvr_payout_from_date" class="mvr-payout-from-date mvr_datepicker" value=''/>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="mvr_payout_to_date"><?php esc_html_e( 'To Date', 'multi-vendor-marketplace' ); ?></label>
							</th>
							<td>
								<input type="text" id="mvr_payout_to_date" class="mvr-payout-to-date mvr_datepicker" value=''/>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="mvr_payout_status"><?php esc_html_e( 'Status to be Generated', 'multi-vendor-marketplace' ); ?></label>
							</th>
							<td>
								<select id="mvr_payout_status" class="mvr-payout-status" style="width:100%;">
									<?php
									foreach ( MVR_Admin_Exporters::get_withdraw_statuses() as $value => $label ) {
										if ( 'mvr-pending' === $value || 'mvr-progress' === $value ) {
											echo '<option value="' . esc_attr( $value ) . '">' . esc_html( $label ) . '</option>';
										}
									}
									?>
								</select>
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
