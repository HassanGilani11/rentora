<?php
/**
 * Vendor new coupon request
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/emails/vendor-new-coupon-request.php.
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
/* translators: %1$s: Coupon Name. %2$s: Site Name */
printf( wp_kses_post( __( 'Your %1$s coupon created on %2$s has been submitted for approval.  The submitted coupon\'s details are as follows,', 'multi-vendor-marketplace' ) ), esc_html( $coupon_obj->get_code() ), esc_html( get_bloginfo( 'name', 'display' ) ) );
?>
</p>

<?php
/**
 * Coupon details
 *
 * @since 1.0.0
 * @hooked MVR_Emails::coupon_details() Shows coupon details.
 */
do_action( 'mvr_email_coupon_details', $coupon_obj, $sent_to_admin, $plain_text, $email );
?>

<p>
	<?php esc_html_e( 'You will be notified once the coupon is approved.', 'multi-vendor-marketplace' ); ?>
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