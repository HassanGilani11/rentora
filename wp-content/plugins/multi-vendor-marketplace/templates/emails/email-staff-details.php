<?php
/**
 * Staff details table shown in emails.
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/emails/email-staff-details.php.
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
 * Before Email Staff Table.
 *
 * @since 1.0.0
 */
do_action( 'mvr_email_before_staff_table', $staff_obj, $sent_to_admin, $plain_text, $email ); ?>

<h2>
	<?php
	if ( $sent_to_admin ) {
		$before = '<a class="link" href="' . esc_url( get_admin_url( null, 'post.php?post=' . $staff_obj->get_id() . '&action=edit' ) ) . '">';
		$after  = '</a>';
	} else {
		$before = '';
		$after  = '';
	}
	/* translators: %s: Order ID. */
	echo wp_kses_post( $before . sprintf( __( '[Staff #%s]', 'multi-vendor-marketplace' ) . $after . ' (<time datetime="%s">%s</time>)', $staff_obj->get_id(), $staff_obj->get_date_created()->format( 'c' ), wc_format_datetime( $staff_obj->get_date_created() ) ) );
	?>
</h2>

<div style="margin-bottom: 40px;">
	<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
		<thead>
			<tr>
				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Staff Name', 'multi-vendor-marketplace' ); ?></th>
				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Vendor Name', 'multi-vendor-marketplace' ); ?></th>
				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Email', 'multi-vendor-marketplace' ); ?></th>
				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Registered date', 'multi-vendor-marketplace' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;"><?php echo esc_html( $staff_obj->get_name() ); ?></td>
			<td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;"><?php echo esc_html( $staff_obj->get_vendor()->get_name() ); ?></td>
			<td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;"><?php echo esc_html( $staff_obj->get_email() ); ?></td>
			<td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;"><?php echo esc_html( $staff_obj->get_date_created()->format( 'Y-m-d H:i:s' ) ); ?></td>
		</tbody>
	</table>
</div>

<?php
/**
 * After Email Staff Table.
 *
 * @since 1.0.0
 */
do_action( 'mvr_email_after_staff_table', $staff_obj, $sent_to_admin, $plain_text, $email );
?>
