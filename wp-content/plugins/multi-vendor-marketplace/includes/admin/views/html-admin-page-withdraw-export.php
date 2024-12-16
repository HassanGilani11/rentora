<?php
/**
 * Admin View: Withdraw Export
 *
 * @package WooCommerce\Admin\Export
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_script( 'mvr-withdraw-export' );

$exporter = new MVR_Withdraw_CSV_Exporter();
?>
<div class="mvr-withdraw-export-wrap mvr-wrap wrap woocommerce">
	<h1><?php esc_html_e( 'Export Withdraw', 'multi-vendor-marketplace' ); ?></h1>

	<div class="mvr-exporter-wrapper woocommerce-exporter-wrapper">
		<form class="mvr-exporter woocommerce-exporter">
			<header>
				<span class="spinner is-active"></span>
				<h2><?php esc_html_e( 'Export withdraw to a CSV file', 'multi-vendor-marketplace' ); ?></h2>
				<p><?php esc_html_e( 'This tool allows you to generate and download a CSV file containing a list of all withdraws.', 'multi-vendor-marketplace' ); ?></p>
			</header>
			<section>
				<table class="mvr-withdraw-exporter-options form-table woocommerce-exporter-options">
					<tbody>
						<tr>
							<th scope="row">
								<label for="mvr_exporter_columns"><?php esc_html_e( 'Which columns should be exported?', 'multi-vendor-marketplace' ); ?></label>
							</th>
							<td>
								<select id="mvr_exporter_columns" class="mvr-exporter-columns woocommerce-exporter-columns wc-enhanced-select" style="width:100%;" multiple data-placeholder="<?php esc_attr_e( 'Export all columns', 'multi-vendor-marketplace' ); ?>">
									<?php
									foreach ( $exporter->get_default_column_names() as $column_id => $column_name ) {
										echo '<option value="' . esc_attr( $column_id ) . '">' . esc_html( $column_name ) . '</option>';
									}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="mvr_exporter_payment_types"><?php esc_html_e( 'Which withdraw payment types should be exported?', 'multi-vendor-marketplace' ); ?></label>
							</th>
							<td>
								<select id="mvr_exporter_payment_types" class="mvr-exporter-payment-types wc-enhanced-select" style="width:100%;" multiple data-placeholder="<?php esc_attr_e( 'Export all payment methods', 'multi-vendor-marketplace' ); ?>">
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
								<label for="mvr_exporter_statuses"><?php esc_html_e( 'Which withdraw status should be exported?', 'multi-vendor-marketplace' ); ?></label>
							</th>
							<td>
								<select id="mvr_exporter_statuses" class="mvr-exporter-statuses wc-enhanced-select" style="width:100%;" multiple data-placeholder="<?php esc_attr_e( 'Export all status', 'multi-vendor-marketplace' ); ?>">
									<?php
									foreach ( MVR_Admin_Exporters::get_withdraw_statuses() as $value => $label ) {
										echo '<option value="' . esc_attr( $value ) . '">' . esc_html( $label ) . '</option>';
									}
									?>
								</select>
							</td>
						</tr>
						<?php
						/**
						 * Withdraw Export row
						 *
						 * @since 1.0.0
						 * */
						do_action( 'mvr_withdraw_export_row' );
						?>
					</tbody>
				</table>
				<progress class="mvr-exporter-progress woocommerce-exporter-progress" max="100" value="0"></progress>
			</section>
			<div class="mvr-actions wc-actions">
				<button type="submit" class="mvr-withdraw-generate-submit woocommerce-exporter-button button button-primary" value="<?php esc_attr_e( 'Generate CSV', 'multi-vendor-marketplace' ); ?>"><?php esc_html_e( 'Generate CSV', 'multi-vendor-marketplace' ); ?></button>
			</div>
		</form>
	</div>
</div>
