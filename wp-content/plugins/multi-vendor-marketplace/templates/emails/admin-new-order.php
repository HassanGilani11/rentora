<?php
/**
 * Admin new order
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/emails/admin-new-order.php.
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
/* translators: %1$s: Order number, %2$s: Site Title %3$s: Customer full name.  */
printf( wp_kses_post( __( 'The %1$s Vendor on your site %2$s has received a new order %3$s. The order details are as follows,', 'multi-vendor-marketplace' ) ), esc_html( $vendor_obj->get_name() ), esc_html( get_bloginfo( 'name', 'display' ) ), '#' . esc_html( $order_obj->get_id() ) );
?>
</p>

<?php
/**
 * Order details
 *
 * @since 1.0.0
 * @hooked MVR_Emails::order_details() Shows order details.
 */
do_action( 'mvr_email_order_details', $order_obj, $sent_to_admin, $plain_text, $email );
?>

<p>
	<?php
	/* translators: %1$s: Order number, %2$s: Customer full name.  */
	printf( wp_kses_post( __( 'You can review the order by clicking the link below. %1$s <a href="%2$s" target="_blank">click here</a>', 'multi-vendor-marketplace' ) ), '<br/>', esc_url( get_admin_url( null, 'post.php?post=' . $order_obj->get_id() . '&action=edit' ) ) );
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
