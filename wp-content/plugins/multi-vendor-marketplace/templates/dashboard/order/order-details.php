<?php
/**
 * Order Details
 *
 * @package Multi Vendor Marketplace\Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Action hook fired before the order details.
 *
 * @since 1.0.0
 */
do_action( 'mvr_before_order_details', $order_id );
?>
<section class="mvr-order-details woocommerce-order-details">
	<?php
	/**
	 * Before Order Details Table
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_before_order_details_table', $order_obj );
	?>

	<h2 class="mvr-order-details-title woocommerce-order-details__title"><?php esc_html_e( 'Order details', 'multi-vendor-marketplace' ); ?></h2>
	<?php if ( mvr_allow_endpoint( 'mvr-update-order-status' ) ) : ?>
	<form method="post" id="mainform" action="" enctype="multipart/form-data">
		<label for="_order_status"><?php esc_html_e( 'Order Status:', 'multi-vendor-marketplace' ); ?></label>
		<select name="_order_status" class="mvr-order-status" data-status="wc-<?php echo esc_attr( $order_obj->get_status() ); ?>">
			<?php
			foreach ( wc_get_order_statuses() as $key => $value ) :
				?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( 'wc-' . $order_obj->get_status(), $key, true ); ?>><?php echo esc_html( $value ); ?></option>
				<?php
			endforeach;
			?>
		</select>

		<?php wp_nonce_field( 'mvr-update-order-status', '_mvr_nonce' ); ?>
		<button type="submit" class="woocommerce-Button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="save_mvr_address" value="<?php esc_attr_e( 'Update', 'multi-vendor-marketplace' ); ?>"><?php esc_html_e( 'Update', 'multi-vendor-marketplace' ); ?></button>
		<input type="hidden" name="action" value="mvr_update_order_status" />
		<input type="hidden" name="_order_id" value="<?php echo esc_attr( $order_obj->get_id() ); ?>" />
	</form>
	<?php endif; ?>

	<table class="mvr-order-details-table woocommerce-table woocommerce-table--order-details shop_table order_details">
		<thead>
			<tr>
				<th class="mvr-product-name woocommerce-table__product-name product-name"><?php esc_html_e( 'Product', 'multi-vendor-marketplace' ); ?></th>
				<th class="mvr-product-total woocommerce-table__product-table product-total"><?php esc_html_e( 'Total', 'multi-vendor-marketplace' ); ?></th>
			</tr>
		</thead>

		<tbody>
			<?php
			/**
			 * Before Order Table items
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_before_order_items_table', $order_obj );

			foreach ( $order_obj->get_items( 'line_item' ) as $item_id => $item ) {
				$product_obj = wc_get_product( $item->get_product_id() );

				if ( ! $product_obj || $vendor_id !== (int) $product_obj->get_meta( '_mvr_vendor', true ) ) {
					continue;
				}

				$shipping_cost     = 0;
				$shipping_class_id = $product_obj->get_shipping_class_id();

				if ( ! empty( $shipping_class_id ) ) {
					$shipping_class_term = get_term( $shipping_class_id, 'product_shipping_class' );
					$shipping_cost       = $shipping_class_term->name;
				}

				mvr_get_template(
					'dashboard/order/order-details-item.php',
					array(
						'order_obj'          => $order_obj,
						'item_id'            => $item_id,
						'item'               => $item,
						'show_purchase_note' => $order_obj->has_status( array( 'completed', 'processing' ) ),
						'purchase_note'      => $product_obj ? $product_obj->get_purchase_note() : '',
						'product_obj'        => $product_obj,
						'shipping_cost'      => $shipping_cost,
					)
				);
			}

			/**
			 * After Order Table items
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_after_order_items_table', $order_obj );
			?>
		</tbody>

		<tfoot>
			<?php
			$order_details = mvr_get_vendor_order_details( $vendor_obj, $order_obj );
			?>
			<tr>
				<th scope="row"><?php echo esc_html_e( 'Subtotal:', 'multi-vendor-marketplace' ); ?></th>
				<td>
					<?php
					$tax_display = get_option( 'woocommerce_tax_display_cart' );

					if ( 'excl' === $tax_display ) :
						$ex_tax_label = $order_obj->get_prices_include_tax() ? 1 : 0;
						$subtotal     = wc_price(
							$order_details['subtotal'],
							array(
								'ex_tax_label' => $ex_tax_label,
								'currency'     => $order_obj->get_currency(),
							)
						);
					else :
						$subtotal = wc_price( $order_details['subtotal'], array( 'currency' => $order_obj->get_currency() ) );
					endif;

					echo wp_kses_post( $subtotal );
					?>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php echo esc_html_e( 'Tax:', 'multi-vendor-marketplace' ); ?></th>
				<td>
					<?php
					$total_tax = wc_price( $order_details['total_tax'], array( 'currency' => $order_obj->get_currency() ) );
					echo wp_kses_post( $total_tax );
					?>
				</td>
			</tr>

			<?php if ( $order_details['discount'] ) : ?>
				<tr>
					<th scope="row"><?php echo esc_html_e( 'Discount:', 'multi-vendor-marketplace' ); ?></th>
					<td>
						<?php
						$discount = wc_price( $order_details['discount'], array( 'currency' => $order_obj->get_currency() ) );
						echo '-' . wp_kses_post( $discount );
						?>
					</td>
				</tr>
				<?php
			endif;

			if ( $order_obj->get_total() > 0 && $order_obj->get_payment_method_title() && 'other' !== $order_obj->get_payment_method() ) :
				?>
				<tr>
					<th scope="row"><?php echo esc_html_e( 'Payment method:', 'multi-vendor-marketplace' ); ?></th>
					<td>
						<?php echo wp_kses_post( $order_obj->get_payment_method_title() ); ?>
					</td>
				</tr>
			<?php endif; ?>

			<tr>
				<th scope="row"><?php echo esc_html_e( 'Total:', 'multi-vendor-marketplace' ); ?></th>
				<td>
					<?php
					$total = wc_price( $order_details['total'], array( 'currency' => $order_obj->get_currency() ) );
					echo wp_kses_post( $total );
					?>
				</td>
			</tr>

			<?php
			if ( mvr_allow_endpoint( 'mvr-order-commission-info' ) ) :
				if ( $order_details['commission'] ) :
					?>
					<tr>
						<th scope="row"><?php echo esc_html_e( 'Admin Commission:', 'multi-vendor-marketplace' ); ?></th>
						<td>
							<?php
							$commission = wc_price( $order_details['commission'], array( 'currency' => $order_obj->get_currency() ) );
							echo wp_kses_post( $commission );
							?>
						</td>
					</tr>

					<?php
					if ( $order_details['vendor_amount'] ) :
						?>
						<tr>
							<th scope="row"><?php echo esc_html_e( 'Vendor(s) Earning:', 'multi-vendor-marketplace' ); ?></th>
							<td>
							<?php
							$earning = wc_price( $order_details['vendor_amount'], array( 'currency' => $order_obj->get_currency() ) );
							echo wp_kses_post( $earning );
							?>
							</td>
						</tr>
						<?php
					endif;
				endif;
			endif;
			?>

			<?php if ( $order_obj->get_customer_note() ) : ?>
				<tr>
					<th><?php esc_html_e( 'Note:', 'multi-vendor-marketplace' ); ?></th>
					<td><?php echo wp_kses_post( nl2br( wptexturize( $order_obj->get_customer_note() ) ) ); ?></td>
				</tr>
			<?php endif; ?>
		</tfoot>
	</table>

	<?php
	/**
	 * After Order Details Table
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_after_order_details_table', $order_obj );
	?>
</section>
<?php
/**
 * Action hook fired after the order details.
 *
 * @since 1.0.0
 */
do_action( 'mvr_after_order_details', $order_id );

mvr_get_template(
	'dashboard/order/order-details-customer.php',
	array(
		'order_obj' => $order_obj,
		'order_id'  => $order_id,
	)
);


