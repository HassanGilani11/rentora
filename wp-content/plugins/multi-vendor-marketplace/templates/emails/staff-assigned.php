<?php
/**
 * Staff assigned
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/emails/staff-assigned.php.
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
/* translators: %1$s: Shop Name. %2$s: Site Name */
printf( wp_kses_post( __( 'A New Staff has been assigned to the Shop %1$s on %2$s.', 'multi-vendor-marketplace' ) ), esc_html( $vendor_obj->get_shop_name() ), esc_html( get_bloginfo( 'name', 'display' ) ) );
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