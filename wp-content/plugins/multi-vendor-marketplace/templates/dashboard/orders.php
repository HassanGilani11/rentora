<?php
/**
 * Orders
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/dashboard/orders.php.
 *
 * @package Multi Vendor Marketplace\Templates
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Before Dashboard Orders
 *
 * @since 1.0.0
 */
do_action( 'mvr_before_dashboard_orders', $vendor_orders->has_order ); ?>

<div class="mvr-wrap">
	<form id="mvr-coupon-filter" method="get">
		<!-- Search Result -->
		<?php if ( $term ) : ?>
			<span class="subtitle">
				<?php
				/* translators: Term */
				printf( esc_html__( 'Search results for: %s', 'multi-vendor-marketplace' ), wp_kses_post( '<strong>' . $term . '</strong>' ) );
				?>
			</span>
		<?php endif; ?>

		<p class="mvr-search-box">
			<input type="search" id="mvr-orders-search-input" name="mvr_search" value="<?php echo esc_attr( $term ); ?>">
			<?php wp_nonce_field( 'mvr-dashboard-orders', '_mvr_nonce' ); ?>
			<button type="submit"><?php echo esc_attr( get_option( 'mvr_dashboard_order_search_btn_label', 'Search orders' ) ); ?></button>
		</p>

		<?php if ( $vendor_orders->has_order ) : ?>
			<ul class="subsubsub">
				<?php foreach ( mvr_dashboard_order_table_views() as $key => $value ) : ?>
					<li class="<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( $value ); ?></li>
				<?php endforeach; ?>
			</ul>

			<?php
			/**
			 * Before Orders Table
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_before_dashboard_orders_table', $vendor_orders->has_order );
			?>
		<table class="mvr-orders-table mvr-dashboard-orders mvr-frontend-table shop_table shop_table_responsive my_account_orders account-orders-table">
			<thead>
				<tr>
					<?php foreach ( mvr_get_dashboard_orders_columns() as $column_id => $column_name ) : ?>
						<th class="mvr-orders-table__header mvr-orders-table__header-<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
					<?php endforeach; ?>
				</tr>
			</thead>

			<tbody>
				<?php
				foreach ( $vendor_orders->orders as $mvr_order_obj ) :
					if ( ! mvr_is_order( $mvr_order_obj ) ) {
						continue;
					}

					$order_obj   = wc_get_order( $mvr_order_obj->get_order_id() );
					$items_count = count( mvr_get_vendor_order_items( $order_obj, $vendor_id ) );
					?>
					<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr( $order_obj->get_status() ); ?> order">
						<?php foreach ( wc_get_account_orders_columns() as $column_id => $column_name ) : ?>
							<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
								<?php
								if ( has_action( 'mvr_orders_column_' . $column_id ) ) :
									?>
									<?php
									/**
									 * Orders Column
									 *
									 * @since 1.0.0
									 */
									do_action( 'mvr_orders_column_' . $column_id, $order_obj );
								elseif ( 'order-number' === $column_id ) :
									$order_url = mvr_get_dashboard_endpoint_url( 'mvr-view-order', $order_obj->get_id() );
									?>
									<a href="<?php echo esc_url( $order_url ); ?>">
										<?php echo esc_html( _x( '#', 'hash before order number', 'multi-vendor-marketplace' ) . $order_obj->get_order_number() ); ?>
									</a>
									<?php
								elseif ( 'order-date' === $column_id ) :
									?>
									<time datetime="<?php echo esc_attr( $order_obj->get_date_created()->date( 'c' ) ); ?>"><?php echo esc_html( wc_format_datetime( $order_obj->get_date_created() ) ); ?></time>

									<?php
								elseif ( 'order-status' === $column_id ) :
									echo esc_html( wc_get_order_status_name( $order_obj->get_status() ) );

								elseif ( 'order-total' === $column_id ) :
									/* translators: 1: formatted order total 2: total order items */
									echo wp_kses_post( sprintf( _n( '%1$s for %2$s item', '%1$s for %2$s items', $items_count, 'woocommerce' ), $order_obj->get_formatted_order_total(), $items_count ) );
								elseif ( 'order-actions' === $column_id ) :
									$actions = mvr_get_dashboard_orders_actions( $order_obj );

									if ( ! empty( $actions ) ) :
										foreach ( $actions as $key => $_action ) :
											echo '<a href="' . esc_url( $_action['url'] ) . '" class="woocommerce-button' . esc_attr( $wp_button_class ) . ' button ' . sanitize_html_class( $key ) . '">' . esc_html( $_action['name'] ) . '</a>';
										endforeach;
									endif;
								endif;
								?>
							</td>
						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

			<?php
			/**
			 * Dashboard Orders pagination
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_before_dashboard_orders_pagination' );

			if ( 1 < $vendor_orders->max_num_pages ) :
				mvr_get_template(
					'dashboard/pagination.php',
					array(
						'current_page'    => $current_page,
						'wp_button_class' => $wp_button_class,
						'prev_url'        => mvr_get_dashboard_endpoint_url( 'mvr-orders', $current_page - 1 ),
						'next_url'        => mvr_get_dashboard_endpoint_url( 'mvr-orders', $current_page + 1 ),
					)
				);
			endif;
	else :
		wc_print_notice( esc_html__( 'No orders found', 'multi-vendor-marketplace' ), 'notice' );
	endif;

	/**
	 * After Dashboard Orders
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_after_dashboard_orders', $vendor_orders->has_order );
	?>
	</form>
</div>
