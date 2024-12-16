<?php
/**
 * Admin new withdraw request
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/emails/admin-new-withdraw-request.php.
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
	/* translators: %1$s: Vendor Name, %2$s: Site Name.  */
	printf( wp_kses_post( __( 'A new Withdrawal Request has been submitted by %1$s on your site %2$s. The submitted withdrawal details are as follows,', 'multi-vendor-marketplace' ) ), esc_html( $vendor_obj->get_name() ), esc_html( get_bloginfo( 'name', 'display' ) ) );
	?>
</p>

<?php
/**
 * Withdraw details
 *
 * @since 1.0.0
 * @hooked MVR_Emails::withdraw_details() Shows withdraw details.
 */
do_action( 'mvr_email_withdraw_details', $withdraw_obj, $sent_to_admin, $plain_text, $email );
?>

<p>
	<?php
	/* translators: %1$s: Order number, %2$s: Customer full name.  */
	printf( wp_kses_post( __( 'You can review and respond to the withdrawal request by clicking the link below. %1$s <a href="%2$s" target="_blank">click here</a>', 'multi-vendor-marketplace' ) ), '<br/>', esc_url( $withdraw_obj->get_admin_edit_url() ) );
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
