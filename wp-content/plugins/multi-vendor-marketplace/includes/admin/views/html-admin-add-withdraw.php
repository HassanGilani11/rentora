<?php
/**
 * Add Withdraw
 *
 * @package Multi-Vendor for WooCommerce/Vendor
 * */

defined( 'ABSPATH' ) || exit;

$withdraw_amount = (float) get_option( 'mvr_settings_min_withdraw_threshold', 0 );
?>
<div class="wc-backbone-modal mvr-add-withdraw-wrapper">
	<div class="wc-backbone-modal-content">
		<section class="wc-backbone-modal-main" role="main">
			<header class="wc-backbone-modal-header">
				<h1><?php esc_html_e( 'Add Withdraw', 'multi-vendor-marketplace' ); ?></h1>
			</header>
			<article>
			<?php if ( ! empty( $withdraw_amount ) ) : ?>
				<span class="mvr-min-withdraw-amount"><?php echo wp_kses_post( wc_price( $withdraw_amount ) ); ?></span>
			<?php endif; ?>
				<p class="mvr-add-withdraw-fields mvr-manual-withdraw-field">
					<label for="_vendor_id"><?php esc_html_e( 'Select Vendor:', 'multi-vendor-marketplace' ); ?></label>
					<?php
					mvr_select2_html(
						array(
							'id'          => '_vendor_id',
							'class'       => 'mvr-select2-search wc-product-search mvr-withdraw-vendor-id',
							'placeholder' => esc_html__( 'Search vendor(s)', 'multi-vendor-marketplace' ),
							'type'        => 'vendor',
							'action'      => 'mvr_json_search_vendors',
							'multiple'    => false,
						)
					);
					?>
					<input type="hidden" class="mvr-available-withdraw-amount" value=""><br/>
					<span class="mvr-available-withdraw-amount-desc"></span>
				</p>

				<p class="mvr-add-withdraw-fields mvr-manual-withdraw-field">
					<label for="_amount"><?php esc_html_e( 'Withdraw Amount:', 'multi-vendor-marketplace' ); ?></label>
					<input type="text" class="mvr-withdraw-amount mvr-input-price input-text mvr-required-field" name="_amount" id="_amount"/>
					<br/><span class="mvr-amount-desc"></span>
				</p>

				<p class="mvr-add-withdraw-fields mvr-manual-withdraw-field">
					<label for="_status"><?php esc_html_e( 'Status:', 'multi-vendor-marketplace' ); ?></label>
					<select name="_status" id="_status" class="mvr-withdraw-status">
						<?php foreach ( mvr_get_withdraw_statuses() as $status_name => $status_label ) : ?>
							<option value="<?php echo esc_attr( $status_name ); ?>"><?php echo esc_html( $status_label ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>

				<span class="mvr-inside"></span>
				<span class="mvr-error" style="font-weight:bold;"></span>
			</article>
			<footer>                
				<div class="inner">
					<button class="mvr-add-withdraw button button-primary" style="display:none;"><?php esc_html_e( 'Add Withdraw', 'multi-vendor-marketplace' ); ?></button>
					<a href="<?php echo esc_url( mvr_get_withdraw_page_url() ); ?>" class="mvr-cancel-withdraw-adding button"><?php esc_html_e( 'Cancel', 'multi-vendor-marketplace' ); ?></a>
				</div>
			</footer>
		</section>
	</div>
</div>
<div class="wc-backbone-modal-backdrop"></div>
