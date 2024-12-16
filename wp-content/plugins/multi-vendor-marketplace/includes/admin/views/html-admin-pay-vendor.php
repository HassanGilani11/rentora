<?php
/**
 * Pay Vendor Form
 *
 * @package Multi-Vendor for WooCommerce/Vendor
 * */

defined( 'ABSPATH' ) || exit;

$withdraw_amount = (float) get_option( 'mvr_settings_min_withdraw_threshold', 0 );
?>
<div class="wc-backbone-modal mvr-pay-vendor-wrapper">
	<div class="wc-backbone-modal-content">
		<section class="wc-backbone-modal-main" role="main">
			<header class="wc-backbone-modal-header">
				<h1><?php esc_html_e( 'Pay Vendor', 'multi-vendor-marketplace' ); ?></h1>
			</header>
			<article>
				<p class="mvr-vendor-amount">
					<label><?php esc_html_e( 'Total Amount', 'multi-vendor-marketplace' ); ?></label>
					{{{ data.total_amount_disp }}}
				</p>
				<p class="mvr-vendor-available-withdraw-amount">
					<label><?php esc_html_e( 'Available Amount', 'multi-vendor-marketplace' ); ?></label>
					{{{ data.amount_disp }}}
				</p>
				<p class="mvr-vendor-locked-amount">
					<label><?php esc_html_e( 'Locked Amount', 'multi-vendor-marketplace' ); ?></label>
					{{{ data.locked_amount_disp }}}
				</p>
			<?php if ( ! empty( $withdraw_amount ) ) : ?>
				<label><?php esc_html_e( 'Minimum Withdrawal Amount', 'multi-vendor-marketplace' ); ?></label>
				<span class="mvr-min-withdraw-amount"><?php echo wp_kses_post( wc_price( $withdraw_amount ) ); ?></span>
			<?php endif; ?>
				<p class="mvr-pay-vendor-fields">
					<label for="_amount"><?php esc_html_e( 'Amount to pay:', 'multi-vendor-marketplace' ); ?></label>
					<input type="text" class="mvr-pay-amount mvr-input-price input-text mvr-required-field" name="_amount" id="_amount" data-available_amount="{{ data.amount }}"  data-min_withdraw="<?php echo esc_attr( $withdraw_amount ); ?>"/>
					<br/><span class="mvr-amount-desc"></span>
				</p>

				<span class="mvr-inside"></span>
				<span class="mvr-error" style="font-weight:bold;"></span>
			</article>
			<footer>                
				<div class="inner">
					<button class="mvr-pay-vendor button button-primary" style="display:none;" data-vendor_id="{{data.vendor_id}}"><?php esc_html_e( 'Pay Vendor', 'multi-vendor-marketplace' ); ?></button>
					<a href="<?php echo esc_url( mvr_get_vendor_page_url() ); ?>" class="mvr-cancel-pay-vendor button"><?php esc_html_e( 'Cancel', 'multi-vendor-marketplace' ); ?></a>
				</div>
			</footer>
		</section>
	</div>
</div>
<div class="wc-backbone-modal-backdrop"></div>
