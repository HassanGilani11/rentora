<?php
/**
 * Product details table shown in emails.
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/emails/email-product-details.php.
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
 * Before Email product Table.
 *
 * @since 1.0.0
 */
do_action( 'mvr_email_before_product_table', $product_obj, $sent_to_admin, $plain_text, $email ); ?>

<h2>
	<?php
	if ( $sent_to_admin ) {
		$before = '<a class="link" href="' . esc_url( get_admin_url( null, 'post.php?post=' . $product_obj->get_id() . '&action=edit' ) ) . '">';
		$after  = '</a>';
	} else {
		$before = '';
		$after  = '';
	}

	if ( $product_obj->get_date_created() ) :
		/* translators: %s: Product ID. */
		echo wp_kses_post( $before . sprintf( __( '[Product #%s]', 'multi-vendor-marketplace' ) . $after . ' (<time datetime="%s">%s</time>)', $product_obj->get_id(), $product_obj->get_date_created()->format( 'c' ), wc_format_datetime( $product_obj->get_date_created() ) ) );
	else :
		/* translators: %s: Product ID. */
		echo wp_kses_post( $before . sprintf( __( '[Product #%s]', 'multi-vendor-marketplace' ) . $after, $product_obj->get_id() ) );
	endif;
	?>
</h2>

<div style="margin-bottom: 40px;">
	<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
		<thead>
			<tr>
				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Product Name', 'multi-vendor-marketplace' ); ?></th>
				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Price', 'multi-vendor-marketplace' ); ?></th>
				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Category', 'multi-vendor-marketplace' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;"><?php echo esc_html( $product_obj->get_name() ); ?></td>
			<td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;"><?php echo wp_kses_post( $product_obj->get_price_html() ); ?></td>
			<td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
			<?php
				$terms = get_the_terms( $product_obj->get_id(), 'product_cat' );

			if ( ! $terms ) :
				echo '<span class="na">&ndash;</span>';
				else :
					$term_list = array();

					foreach ( $terms as $_term ) :
						$term_list[] = '<a href="' . esc_url( get_term_link( $_term->term_id, 'product_cat' ) ) . ' ">' . esc_html( $_term->name ) . '</a>';
					endforeach;

					/**
					 * Product Term List
					 *
					 * @since 1.0.0
					 */
					echo wp_kses_post( apply_filters( 'woocommerce_admin_product_term_list', implode( ', ', $term_list ), 'product_cat', $product_obj->get_id(), $term_list, $terms ) );
				endif;
				?>
			</td>
		</tbody>
	</table>
</div>

<?php
/**
 * After Email Product Table.
 *
 * @since 1.0.0
 */
do_action( 'mvr_email_after_product_table', $product_obj, $sent_to_admin, $plain_text, $email );
?>
