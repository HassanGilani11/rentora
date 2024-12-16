<?php
/**
 * View Order
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/dashboard/view-order.php.
 *
 * @package Multi Vendor\Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$notes = $order_obj->get_customer_order_notes();
?>
<p>
<?php
printf(
	/* translators: 1: order number 2: order date 3: order status */
	esc_html__( 'Order #%1$s was placed on %2$s and is currently %3$s.', 'multi-vendor-marketplace' ),
	'<mark class="order-number">' . esc_html( $order_obj->get_order_number() ) . '</mark>',
	'<mark class="order-date">' . esc_html( wc_format_datetime( $order_obj->get_date_created() ) ) . '</mark>',
	'<mark class="order-status">' . esc_html( wc_get_order_status_name( $order_obj->get_status() ) ) . '</mark>'
);
?>
</p>

<?php if ( $notes ) : ?>
	<h2><?php esc_html_e( 'Order updates', 'multi-vendor-marketplace' ); ?></h2>
	<ol class="woocommerce-OrderUpdates commentlist notes">
		<?php foreach ( $notes as $note ) : ?>
			<li class="woocommerce-OrderUpdate comment note">
				<div class="woocommerce-OrderUpdate-inner comment_container">
					<div class="woocommerce-OrderUpdate-text comment-text">
						<p class="woocommerce-OrderUpdate-meta meta"><?php echo wp_kses_post( date_i18n( esc_html__( 'l jS \o\f F Y, h:ia', 'multi-vendor-marketplace' ), strtotime( $note->comment_date ) ) ); ?></p>
						<div class="woocommerce-OrderUpdate-description description">
							<?php echo esc_html( wpautop( wptexturize( $note->comment_content ) ) ); ?>
						</div>
						<div class="clear"></div>
					</div>
					<div class="clear"></div>
				</div>
			</li>
		<?php endforeach; ?>
	</ol>
	<?php
endif;

/**
 * View Order
 *
 * @since 1.0.0
 */
do_action( 'mvr_view_order', $order_id )
?>
