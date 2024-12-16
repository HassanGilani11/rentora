<?php
/**
 * Commissions Preview.
 *
 * @package Multi-Vendor for WooCommerce/Commission Preview
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$order_edit_url_placeholder = esc_url( admin_url( 'post.php?action=edit' ) ) . '&post={{ data.order_id }}';
?>
<div class="wc-backbone-modal wc-order-preview mvr-commission-preview-tmpl">
	<div class="wc-backbone-modal-content">
		<section class="wc-backbone-modal-main" role="main">
			<header class="wc-backbone-modal-header">
				<mark class="order-status status-{{ data.status }}"><span>{{ data.status_name }}</span></mark>
				<?php /* translators: %s: order ID */ ?>
				<h1><?php echo esc_html( sprintf( esc_html__( 'Commission #%s', 'multi-vendor-marketplace' ), '{{ data.commission_id }}' ) ); ?></h1>
				<button class="modal-close modal-close-link dashicons dashicons-no-alt">
					<span class="screen-reader-text"><?php esc_html_e( 'Close modal panel', 'multi-vendor-marketplace' ); ?></span>
				</button>
			</header>
			<article>
				<?php
				/**
				 * Commissions Preview Start.
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_admin_commission_preview_start' );
				?>
				<div class="wc-order-preview-addresses">
					<# if ( data.vendor_name ) { #>
						<div class="wc-order-preview-note">
							<strong><?php esc_html_e( 'Vendor', 'multi-vendor-marketplace' ); ?></strong>
							{{ data.vendor_name }}
						</div>
					<# } #>

					<# if ( data.order_id ) { #>
						<div class="wc-order-preview-note">
							<# if( "withdraw" === data.type ) { #>
								<strong><?php esc_html_e( 'Withdraw', 'multi-vendor-marketplace' ); ?></strong>
							<# } else { #>
								<strong><?php esc_html_e( 'Order', 'multi-vendor-marketplace' ); ?></strong>
							<# } #>

							#{{ data.order_id }}
						</div>
					<# } #>

					<# if ( data.date ) { #>
						<div class="wc-order-preview-note">
							<strong><?php esc_html_e( 'Date', 'multi-vendor-marketplace' ); ?></strong>
							{{ data.date }}
						</div>
					<# } #>

					<# if ( data.tax && "order" === data.type ) { #>
						<div class="wc-order-preview-note">
							<strong><?php esc_html_e( 'Tax', 'multi-vendor-marketplace' ); ?></strong>
							{{ data.tax }}
						</div>
					<# } #>

					<# if ( data.shipping ) { #>
						<div class="wc-order-preview-note">
							<strong><?php esc_html_e( 'Shipping', 'multi-vendor-marketplace' ); ?></strong>
							{{ data.shipping }}
						</div>
					<# } #>
				</div>

				{{{ data.item_html }}}

				<?php
				/**
				 * Commissions Preview End.
				 *
				 * @since 1.0.0
				 */
				do_action( 'woocommerce_admin_order_preview_end' );
				?>
			</article>
			<footer>
				<div class="inner">
					{{{ data.actions_html }}}

					<# if( "order" === data.type ) { #>
						<a class="button button-primary button-large" aria-label="<?php esc_attr_e( 'Edit this order', 'woocommerce' ); ?>" href="<?php echo wp_kses_post( $order_edit_url_placeholder ); ?>"><?php esc_html_e( 'Edit', 'multi-vendor-marketplace' ); ?></a>
					<# } #>
				</div>
			</footer>
		</section>
	</div>
</div>
<div class="wc-backbone-modal-backdrop modal-close"></div>
