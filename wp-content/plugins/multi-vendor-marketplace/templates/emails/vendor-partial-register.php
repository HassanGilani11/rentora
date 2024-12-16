<?php
/**
 * Vendor Partial Register
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/emails/vendor-partial-register.php.
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
/* translators: %1$s: Site Title */
printf( wp_kses_post( __( 'The Vendor Application which you have submitted on the site %1$s is partially Complete. You need to fill in additional details in order for the application to be fully complete.', 'multi-vendor-marketplace' ) ), esc_html( get_bloginfo( 'name', 'display' ) ) );
?>
</p>

<p>
<?php
/* translators: %1$s: Order number, %2$s: Customer full name.  */
printf( wp_kses_post( __( 'You can fill in the additional details to your application by clicking the link below. %1$s <a href="%2$s" target="_blank">click here</a>', 'multi-vendor-marketplace' ) ), '<br/>', esc_url( mvr_get_page_permalink( 'dashboard' ) ) );
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
