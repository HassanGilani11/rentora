<?php
/**
 * Order Item Details
 *
 * @package Multi Vendor Marketplace\Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Order Item Visibility.
 *
 * @since 1.0.0
 */
if ( ! apply_filters( 'mvr_order_item_visible', true, $item ) ) {
	return;
}
?>
<tr class="
<?php
/**
 * Dashboard Order Item Class
 *
 * @since 1.0.0
 */
echo esc_attr( apply_filters( 'mvr_order_item_class', 'woocommerce-table__line-item order_item', $item, $order_obj ) );
?>
">
	<td class="mvr-product-name woocommerce-table__product-name product-name">
		<?php
		$is_visible = $product_obj && $product_obj->is_visible();

		/**
		 * Order Item Permalink
		 *
		 * @since 1.0.0
		 */
		$product_permalink = apply_filters( 'mvr_order_item_permalink', $is_visible ? $product_obj->get_permalink( $item ) : '', $item, $order_obj );

		/**
		 * Order Item Name.
		 *
		 * @since 1.0.0
		 */
		echo wp_kses_post( apply_filters( 'mvr_order_item_name', $product_permalink ? sprintf( '<a href="%s">%s</a>', $product_permalink, $item->get_name() ) : $item->get_name(), $item, $is_visible ) );

		$qty          = $item->get_quantity();
		$refunded_qty = $order_obj->get_qty_refunded_for_item( $item_id );

		if ( $refunded_qty ) {
			$qty_display = '<del>' . esc_html( $qty ) . '</del> <ins>' . esc_html( $qty - ( $refunded_qty * -1 ) ) . '</ins>';
		} else {
			$qty_display = esc_html( $qty );
		}

		/**
		 * Order Item Quantity.
		 *
		 * @since 1.0.0
		 */
		echo wp_kses_post( apply_filters( 'mvr_order_item_quantity_html', ' <strong class="product-quantity">' . sprintf( '&times;&nbsp;%s', $qty_display ) . '</strong>', $item ) );

		/**
		 * Order Item Meta Start.
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_order_item_meta_start', $item_id, $item, $order_obj, false );

		wc_display_item_meta( $item );

		/**
		 * Order Item Meta End.
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_order_item_meta_end', $item_id, $item, $order_obj, false );
		?>
	</td>

	<td class="mvr-product-total woocommerce-table__product-total product-total">
		<?php
		echo wp_kses_post( $order_obj->get_formatted_line_subtotal( $item ) );

		if ( mvr_allow_endpoint( 'mvr-order-commission-info' ) ) :
			$commission = $item->get_meta( '_mvr_vendor_commission', true );

			if ( $commission ) :
				echo wp_kses_post(
					/**
					 * Commission.
					 *
					 * @since 1.0.0
					 */
					apply_filters(
						'mvr_order_item_commission_html',
						/* translators: %1$s Commission Amount */
						'<small class="mvr-product-commission">(' . sprintf( esc_html__( 'Commission: %s', 'multi-vendor-marketplace' ), wp_kses_post( wc_price( $commission ) ) ) . ')</small>',
						$item
					)
				);
			endif;
		endif;
		?>
	</td>
</tr>

<?php if ( $show_purchase_note && $purchase_note ) : ?>
	<tr class="mvr-product-purchase-note woocommerce-table__product-purchase-note product-purchase-note">
		<td colspan="2"><?php echo wp_kses_post( wpautop( do_shortcode( wp_kses_post( $purchase_note ) ) ) ); ?></td>
	</tr>
<?php endif; ?>
