<?php
/**
 * Vendor Revenue Credited.
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/emails/vendor-revenue-credited.php.
 *
 * HOWEVER, on occasion Multi Vendor Marketplace will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woo.com/document/template-structure/
 * @package MVR_Multi_Vendor\Templates\Emails
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email Header
 *
 * @since 1.0.0
 * @hooked WC_Emails::email_header() Output the email header.
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p>
<?php
/* translators: %1$s: Revenue Amount, %2$s: Order ID. %3$s: Site Title */
printf( wp_kses_post( __( 'You are eligible to withdraw the Revenue of %1$s earned for the order %2$s on %3$s.', 'multi-vendor-marketplace' ) ), wp_kses_post( wc_price( $transaction_obj->get_amount() ), esc_attr( $transaction_obj->get_source_id() ) ), esc_html( get_bloginfo( 'name', 'display' ) ) );
?>
</p>

<p><?php esc_html_e( 'Thanks', 'multi-vendor-marketplace' ); ?></p>

<?php
/**
 * Email footer.
 *
 * @since 1.0.0
 */
do_action( 'woocommerce_email_footer', $email );
?>
