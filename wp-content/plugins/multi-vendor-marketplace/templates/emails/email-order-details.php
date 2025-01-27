<?php
/**
 * Order details table shown in emails.
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/emails/email-order-details.php.
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
 * Email Before order table.
 *
 * @since 1.0.0
 */
do_action( 'woocommerce_email_before_order_table', $order_obj, $sent_to_admin, $plain_text, $email ); ?>

<h2>
	<?php
	if ( $sent_to_admin ) {
		$before = '<a class="link" href="' . esc_url( $order_obj->get_edit_order_url() ) . '">';
		$after  = '</a>';
	} else {
		$before = '';
		$after  = '';
	}
	/* translators: %s: Order ID. */
	echo wp_kses_post( $before . sprintf( __( '[Order #%s]', 'multi-vendor-marketplace' ) . $after . ' (<time datetime="%s">%s</time>)', $order_obj->get_order_number(), $order_obj->get_date_created()->format( 'c' ), wc_format_datetime( $order_obj->get_date_created() ) ) );
	?>
</h2>

<div style="margin-bottom: 40px;">
	<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
		<thead>
			<tr>
				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Product', 'multi-vendor-marketplace' ); ?></th>
				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Quantity', 'multi-vendor-marketplace' ); ?></th>
				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Price', 'multi-vendor-marketplace' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			echo wp_kses_post(
				wc_get_email_order_items(
					$order_obj,
					array(
						'show_sku'      => $sent_to_admin,
						'show_image'    => false,
						'image_size'    => array( 32, 32 ),
						'plain_text'    => $plain_text,
						'sent_to_admin' => $sent_to_admin,
					)
				)
			);
			?>
		</tbody>
		<tfoot>
			<?php
			$item_totals = $order_obj->get_order_item_totals();

			if ( $item_totals ) {
				$i = 0;
				foreach ( $item_totals as $total ) {
					$i++;
					?>
					<tr>
						<th class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>; <?php echo ( 1 === $i ) ? 'border-top-width: 4px;' : ''; ?>"><?php echo wp_kses_post( $total['label'] ); ?></th>
						<td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; <?php echo ( 1 === $i ) ? 'border-top-width: 4px;' : ''; ?>"><?php echo wp_kses_post( $total['value'] ); ?></td>
					</tr>
					<?php
				}
			}
			if ( $order_obj->get_customer_note() ) {
				?>
				<tr>
					<th class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Note:', 'multi-vendor-marketplace' ); ?></th>
					<td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php echo wp_kses_post( nl2br( wptexturize( $order_obj->get_customer_note() ) ) ); ?></td>
				</tr>
				<?php
			}
			?>
		</tfoot>
	</table>
</div>

<?php
/**
 * Email After order table.
 *
 * @since 1.0.0
 */
do_action( 'mvr_email_after_order_table', $order_obj, $sent_to_admin, $plain_text, $email );
?>
