<?php
/**
 * Order Customer Details
 *
 * @since 1.0.0
 * @package Multi Vendor Marketplace\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$show_shipping = ! wc_ship_to_billing_address_only() && $order_obj->needs_shipping_address();
?>
<section class="mvr-customer-details woocommerce-customer-details">
	<?php
	/**
	 * Before Order Customer Details
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_order_details_before_customer_details', $order_id );

	if ( $show_shipping ) :
		?>
		<section class="mvr-address-columns woocommerce-columns woocommerce-columns--2 woocommerce-columns--addresses col2-set addresses">
			<div class="mvr-billing-address woocommerce-column woocommerce-column--1 woocommerce-column--billing-address col-1">
	<?php endif; ?>

	<h2 class="mvr-address-title woocommerce-column__title"><?php esc_html_e( 'Billing address', 'multi-vendor-marketplace' ); ?></h2>

	<address>
		<?php echo wp_kses_post( $order_obj->get_formatted_billing_address( esc_html__( 'N/A', 'multi-vendor-marketplace' ) ) ); ?>

		<?php if ( $order_obj->get_billing_phone() ) : ?>
			<p class="mvr-customer-phone woocommerce-customer-details--phone"><?php echo esc_html( $order_obj->get_billing_phone() ); ?></p>
		<?php endif; ?>

		<?php if ( $order_obj->get_billing_email() ) : ?>
			<p class="mvr-customer-email woocommerce-customer-details--email"><?php echo esc_html( $order_obj->get_billing_email() ); ?></p>
		<?php endif; ?>
	</address>

	<?php if ( $show_shipping ) : ?>
		</div><!-- /.col-1 -->

		<div class="mvr-address-columns woocommerce-column woocommerce-column--2 woocommerce-column--shipping-address col-2">
			<h2 class="mvr-shipping-address woocommerce-column__title"><?php esc_html_e( 'Shipping address', 'multi-vendor-marketplace' ); ?></h2>
			<address>
				<?php echo wp_kses_post( $order_obj->get_formatted_shipping_address( esc_html__( 'N/A', 'multi-vendor-marketplace' ) ) ); ?>

				<?php if ( $order_obj->get_shipping_phone() ) : ?>
					<p class="mvr-customer-phone woocommerce-customer-details--phone"><?php echo esc_html( $order_obj->get_shipping_phone() ); ?></p>
				<?php endif; ?>
			</address>
		</div><!-- /.col-2 -->
	</section><!-- /.col2-set -->

		<?php
	endif;

	/**
	 * After Order Customer Details
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_order_details_after_customer_details', $order_id );
	?>
</section>
