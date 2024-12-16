<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php // translators: %s: customer first name ?>
<p><?php printf( esc_html__( 'Hi %s,', 'wcrp-rental-products' ), esc_html( $order->get_billing_first_name() ) ); ?></p>

<?php // translators: %s: order number ?>
<p><?php printf( esc_html__( 'Just to let you know &mdash; your order #%s includes rentals which are due to be returned to us soon:', 'wcrp-rental-products' ), esc_html( $order->get_order_number() ) ); ?></p>

<?php

do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

if ( !empty( $additional_content ) ) {

	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );

}

do_action( 'woocommerce_email_footer', $email );
