<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

$latest_rental_orders = WCRP_Rental_Products_Rentals_Summary::latest_rental_orders();
$flagged_rental_orders = WCRP_Rental_Products_Rentals_Summary::flagged_rental_orders();

?>

<div id="wcrp-rental-products-rentals-summary-actions" class="wcrp-rental-products-rentals-actions">
	<div>
		<?php
		$bullet = ' &bull; ';
		echo '<strong>' . esc_html( wp_date( $rental_date_format ) ) . ' ' . esc_html__( 'at', 'wcrp-rental-products' ) . ' ' . esc_html( wp_date( $rental_time_format ) ) . '</strong>';
		echo esc_html( $bullet );
		echo esc_html__( 'Current rentals:', 'wcrp-rental-products' ) . ' <strong>' . esc_html( number_format_i18n( WCRP_Rental_Products_Stat_Helpers::total_current_rentals() ) ) . '<span class="dashicons dashicons-editor-help" title="' . esc_attr( WCRP_Rental_Products_Stat_Helpers::total_current_rentals_description() ) . '"></span></strong>';
		echo esc_html( $bullet );
		echo esc_html__( 'Rental orders today:', 'wcrp-rental-products' ) . ' <strong>' . esc_html( number_format_i18n( WCRP_Rental_Products_Stat_Helpers::total_rental_orders( 1 ) ) ) . '<span class="dashicons dashicons-editor-help" title="' . esc_attr( WCRP_Rental_Products_Stat_Helpers::total_rental_orders_description( 1 ) ) . '"></span></strong>';
		echo esc_html( $bullet );
		echo esc_html__( 'Rental orders last 7 days:', 'wcrp-rental-products' ) . ' <strong>' . esc_html( number_format_i18n( WCRP_Rental_Products_Stat_Helpers::total_rental_orders( 7 ) ) ) . '<span class="dashicons dashicons-editor-help" title="' . esc_attr( WCRP_Rental_Products_Stat_Helpers::total_rental_orders_description( 7 ) ) . '"></span></strong>';
		echo esc_html( $bullet );
		echo esc_html__( 'Rental orders last 30 days:', 'wcrp-rental-products' ) . ' <strong>' . esc_html( number_format_i18n( WCRP_Rental_Products_Stat_Helpers::total_rental_orders( 30 ) ) ) . '<span class="dashicons dashicons-editor-help" title="' . esc_attr( WCRP_Rental_Products_Stat_Helpers::total_rental_orders_description( 30 ) ) . '"></span></strong>';
		echo esc_html( $bullet );
		echo esc_html__( 'Rental products:', 'wcrp-rental-products' ) . ' <strong>' . esc_html( number_format_i18n( WCRP_Rental_Products_Stat_Helpers::total_rental_products() ) ) . '<span class="dashicons dashicons-editor-help" title="' . esc_attr( WCRP_Rental_Products_Stat_Helpers::total_rental_products_description() ) . '"></span></strong>';
		?>
	</div>
	<div>
		<a href="javascript:location.reload();" class="button"><span class="dashicons dashicons-update"></span><?php esc_html_e( 'Refresh summary', 'wcrp-rental-products' ); ?></a>
	</div>
</div>
<div class="wcrp-rental-products-rentals-summary-section-wrap">
	<div id="wcrp-rental-products-rentals-summary-latest-rental-orders" class="wcrp-rental-products-rentals-summary-section">
		<h2><?php esc_html_e( 'Latest Rental Orders', 'wcrp-rental-products' ); ?></h2>
		<p>
			<?php
			echo wp_kses_post(
				sprintf(
					// translators: %1$s: latest orders number, %2$s: all rental orders link, %3$s: all orders link
					__( 'Latest %1$s rental orders (excluding orders where all rentals cancelled), you can also view %2$s, or %3$s.', 'wcrp-rental-products' ),
					WCRP_Rental_Products_Rentals_Summary::orders_limit(),
					'<a href="' . esc_url( get_admin_url() . ( wcrp_rental_products_hpos_enabled() ? 'admin.php?page=wc-orders&action=-1&wcrp_rental_products_rentals_filter=inc' : 'edit.php?s&post_type=shop_order&action=-1&wcrp_rental_products_rentals_filter=inc' ) ) . '" target="_blank">' . __( 'all rental orders', 'wcrp-rental-products' ) . '</a>',
					'<a href="' . esc_url( get_admin_url() . ( wcrp_rental_products_hpos_enabled() ? 'admin.php?page=wc-orders' : 'edit.php?s&post_type=shop_order' ) ) . '" target="_blank">' . __( 'all orders', 'wcrp-rental-products' ) . '</a>'
				)
			);
			?>
		</p>
		<?php
		if ( !empty( $latest_rental_orders ) ) {
			?>
			<div class="wcrp-rental-products-summary-orders-table">
				<table id="wcrp-rental-products-rentals-summary-orders-table-latest" class="wcrp-rental-products-datatable widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Order', 'wcrp-rental-products' ); ?></th>
							<th><?php esc_html_e( 'Customer', 'wcrp-rental-products' ); ?></th>
							<th><?php esc_html_e( 'Date', 'wcrp-rental-products' ); ?></th>
							<th><?php esc_html_e( 'Time', 'wcrp-rental-products' ); ?></th>
							<th><?php esc_html_e( 'Status', 'wcrp-rental-products' ); ?></th>
							<th><?php esc_html_e( 'Total', 'wcrp-rental-products' ); ?></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ( $latest_rental_orders as $latest_rental_order ) {
							$latest_rental_order_id = $latest_rental_order->order_id;
							$latest_rental_order = wc_get_order( $latest_rental_order_id );
							if ( !empty( $latest_rental_order ) ) {
								$order_customer_name = WCRP_Rental_Products_Rentals_Dashboard::order_customer_name( $latest_rental_order );
								$order_link = $latest_rental_order->get_edit_order_url();
								$order_status = $latest_rental_order->get_status();
								?>
								<tr>
									<td><a href="<?php echo esc_url( $order_link ); ?>" target="_blank"><?php esc_html_e( '#', 'wcrp-rental-products' ); ?><?php echo esc_html( $latest_rental_order_id ); ?></a></td>
									<td><?php echo esc_html( $order_customer_name ); ?></td>
									<td><?php echo esc_html( wp_date( $rental_date_format, strtotime( $latest_rental_order->get_date_created() ) ) ); // Uses wp_date() not gmdate() as date got is not local, we want it converted to WordPress local time so matches order date on edit order ?></td>
									<td><?php echo esc_html( wp_date( $rental_time_format, strtotime( $latest_rental_order->get_date_created() ) ) ); // Same applies as above comment ?></td>
									<td class="wcrp-rental-products-rentals-summary-orders-table-order-status wcrp-rental-products-rentals-summary-orders-table-order-status-<?php echo esc_attr( $order_status ); ?>"><span><?php echo esc_html( ucfirst( wc_get_order_status_name( $order_status ) ) ); // ucfirst as can be a trash status and as not a WooCommerce order status is all lowercase ?></span></td>
									<td><strong><?php echo wp_kses_post( $latest_rental_order->get_formatted_order_total() ); ?></strong></td>
									<td><a href="<?php echo esc_url( $order_link ); ?>" class="button button-small" target="_blank"><?php esc_html_e( 'View order', 'wcrp-rental-products' ); ?></a></td>
								</tr>
								<?php
							}
						}
						?>
					</tbody>
				</table>
			</div>
			<?php
		} else {
			?>
			<div class="notice notice-info inline">
				<p><?php esc_html_e( 'No latest rental orders.', 'wcrp-rental-products' ); ?></p>
			</div>
			<?php
		}
		?>
	</div>
	<div id="wcrp-rental-products-rentals-summary-flagged-rental-orders" class="wcrp-rental-products-rentals-summary-section">
		<h2><?php esc_html_e( 'Flagged Rental Orders', 'wcrp-rental-products' ); ?></h2>
		<p><?php esc_html_e( 'Rental orders which may require action, such as rentals not returned by due date.', 'wcrp-rental-products' ); ?></p>
		<?php
		if ( !empty( $flagged_rental_orders ) ) {
			?>
			<div class="wcrp-rental-products-summary-orders-table">
				<table id="wcrp-rental-products-rentals-summary-orders-table-flagged" class="wcrp-rental-products-datatable widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Order', 'wcrp-rental-products' ); ?></th>
							<th><?php esc_html_e( 'Customer', 'wcrp-rental-products' ); ?></th>
							<th><?php esc_html_e( 'Flagged', 'wcrp-rental-products' ); ?></th>
							<th><?php esc_html_e( 'Due', 'wcrp-rental-products' ); ?></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ( $flagged_rental_orders as $flagged_rental_order_id => $flagged_rental_order_items ) {
							$flagged_rental_order = wc_get_order( $flagged_rental_order_id );
							if ( !empty( $flagged_rental_order ) && !empty( $flagged_rental_order_items ) ) {
								$flagged = array();
								$due = array();
								$order_customer_name = WCRP_Rental_Products_Rentals_Dashboard::order_customer_name( $flagged_rental_order );
								$order_link = $flagged_rental_order->get_edit_order_url();
								foreach ( $flagged_rental_order_items as $flagged_rental_order_item_id => $flagged_rental_order_item_reserved_date ) {
									try {
										// Try to get order item, if order item is invalid (e.g. order item id passed is invalid for some reason such as old invalid/manipulated data) then skip as causes fatal error uncaught exception: invalid order item
										$flagged_rental_order_item = new WC_Order_Item_Product( $flagged_rental_order_item_id ); // As these order item ids are from the rentals database tables we already know they are WC_Order_Item_Product
									} catch ( Exception $e ) {
										continue;
									}
									if ( !empty( $flagged_rental_order_item ) ) {
										$flagged[] = $flagged_rental_order_item->get_quantity() . ' ' . esc_html__( 'x', 'wcrp-rental-products' ) . ' ' . $flagged_rental_order_item->get_name();
										$due[] = $flagged_rental_order_item_reserved_date;
									}
								}
								?>
								<tr>
									<td><a href="<?php echo esc_url( $order_link ); ?>" target="_blank"><?php esc_html_e( '#', 'wcrp-rental-products' ); ?><?php echo esc_html( $flagged_rental_order_id ); ?></a></td>
									<td><?php echo esc_html( $order_customer_name ); ?></td>
									<td>
										<?php
										foreach ( $flagged as $flagged_row ) {
											echo esc_html( $flagged_row ) . ' <br>'; // Space before <br> intentional so split in export
										}
										?>
									</td>
									<td style="color: <?php echo esc_html( $colors['red'] ); ?>;">
										<?php
										foreach ( $due as $due_row ) {
											echo '<strong>' . esc_html( gmdate( $rental_date_format, strtotime( $due_row ) ) ) . '</strong> <br>'; // Space before <br> intentional so split in export
										}
										?>
									</td>
									<td><a href="<?php echo esc_url( $order_link ); ?>" class="button button-small" target="_blank"><?php esc_html_e( 'View order', 'wcrp-rental-products' ); ?></a></td>
								</tr>
								<?php
							}
						}
						?>
					</tbody>
				</table>
			</div>
			<?php
		} else {
			?>
			<div class="notice notice-success inline">
				<p><?php esc_html_e( 'No flagged rental orders.', 'wcrp-rental-products' ); ?></p>
			</div>
			<?php
		}
		?>
	</div>
</div>
