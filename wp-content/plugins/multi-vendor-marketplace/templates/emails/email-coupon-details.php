<?php
/**
 * Coupon details table shown in emails.
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/emails/email-coupon-details.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woo.com/document/template-structure/
 * @package MVR_Multi_Vendor\Templates\Emails
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

$text_align = is_rtl() ? 'right' : 'left';

/**
 * Before Email Coupon Table.
 *
 * @since 1.0.0
 */
do_action( 'mvr_email_before_coupon_table', $coupon_obj, $sent_to_admin, $plain_text, $email ); ?>

<h2>
	<?php
	$expiry_date = $coupon_obj->get_date_expires() ? $coupon_obj->get_date_expires()->date( 'Y-m-d' ) : '-';

	if ( $sent_to_admin ) {
		$before = '<a class="link" href="' . esc_url( get_admin_url( null, 'post.php?post=' . $coupon_obj->get_id() . '&action=edit' ) ) . '">';
		$after  = '</a>';
	} else {
		$before = '';
		$after  = '';
	}
	/* translators: %s: Order ID. */
	echo wp_kses_post( $before . sprintf( __( '[Coupon #%s]', 'multi-vendor-marketplace' ) . $after . ' (<time datetime="%s">%s</time>)', $coupon_obj->get_id(), $coupon_obj->get_date_created()->format( 'c' ), wc_format_datetime( $coupon_obj->get_date_created() ) ) );
	?>
</h2>

<div style="margin-bottom: 40px;">
	<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
		<thead>
			<tr>
				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Coupon Code', 'multi-vendor-marketplace' ); ?></th>
				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Coupon type', 'multi-vendor-marketplace' ); ?></th>
				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Coupon amount', 'multi-vendor-marketplace' ); ?></th>
				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Expiry date', 'multi-vendor-marketplace' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;"><?php echo esc_html( $coupon_obj->get_code() ); ?></td>
			<td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;"><?php echo wp_kses_post( wc_get_coupon_type( $coupon_obj->get_discount_type() ) ); ?></td>
			<td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;"><?php echo wp_kses_post( wc_price( $coupon_obj->get_amount() ) ); ?></td>
			<td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;"><?php echo wp_kses_post( $expiry_date ); ?></td>
		</tbody>
	</table>
</div>

<?php
/**
 * After Email Coupon Table.
 *
 * @since 1.0.0
 */
do_action( 'mvr_email_after_coupon_table', $coupon_obj, $sent_to_admin, $plain_text, $email );
?>
