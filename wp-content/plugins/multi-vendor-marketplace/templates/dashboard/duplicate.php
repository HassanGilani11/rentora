<?php
/**
 * Products
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/dashboard/duplicate.php.
 *
 * @package Multi Vendor\Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="mvr-wrap">
	<form id="mvr-product-filter" method="get">
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
			<input type="search" id="mvr-product-search-input" name="mvr_search" value="<?php echo esc_attr( $term ); ?>">
			<input type="hidden" id="_mvr_nonce" name="_mvr_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mvr-dashboard-products-nonce' ) ); ?>">
			<button type="submit"><?php echo esc_attr( get_option( 'mvr_dashboard_duplicate_product_search_label', 'Search products' ) ); ?></button>
		</p>
	<?php if ( $has_products ) : ?>
		<?php
		/**
		 * Products Table before
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_before_dashboard_duplicate_products', $has_products );
		?>
		<table class="mvr-duplicate-products-table mvr-dashboard-duplicate-products-table mvr-frontend-table shop_table shop_table_responsive my_account_orders account-orders-table">
			<thead>
				<tr>
					<?php foreach ( mvr_get_dashboard_duplicate_products_columns() as $column_id => $column_name ) : ?>
						<th class="mvr-products-table__header mvr-products-table__header-<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo wp_kses_post( $column_name ); ?></span></th>
					<?php endforeach; ?>
				</tr>
			</thead>

			<tbody>
				<?php
				foreach ( $vendor_products->products as $vendor_product ) :
					$product_obj = wc_get_product( $vendor_product );
					?>
					<tr class="mvr-products-table__row mvr-products-table__row--status-<?php echo esc_attr( $product_obj->get_status() ); ?> product">
						<?php foreach ( mvr_get_dashboard_duplicate_products_columns() as $column_id => $column_name ) : ?>
							<td class="mvr-products-table__cell mvr-products-table__cell-<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
								<?php
								if ( has_action( 'mvr_dashboard_duplicate_products_column_' . $column_id ) ) :
									/**
									 * Duplicate Product Table Columns
									 *
									 * @since 1.0.0
									 */
									do_action( 'mvr_dashboard_duplicate_products_column_' . $column_id, $product_obj );
								elseif ( 'product-thumb' === $column_id ) :
									?>
									<a href="<?php echo esc_url( mvr_get_dashboard_endpoint_url( 'edit-product', $product_obj->get_id() ) ); ?>">
										<?php echo wp_kses_post( $product_obj->get_image( 'thumbnail' ) ); ?>
									</a>
									<?php
								elseif ( 'product-details' === $column_id ) :
									/* translators: %1$s: Strong Start %2$s: Product Name */
									printf( esc_html__( '%1$s Name: %2$s', 'multi-vendor-marketplace' ), '<strong>', '</strong>' . esc_attr( $product_obj->get_name() ) . '<br/>' );
									/* translators: %1$s: Strong Start %2$s: Product Status */
									printf( esc_html__( '%1$s Status: %2$s', 'multi-vendor-marketplace' ), '<strong>', '</strong>' . esc_html( mvr_get_product_status_name( $product_obj->get_status() ) ) . '<br/>' );

									if ( 'yes' === get_option( 'woocommerce_manage_stock' ) ) :
										if ( $product_obj->is_on_backorder() ) :
											$stock_html = '<mark class="onbackorder">' . __( 'On backorder', 'multi-vendor-marketplace' ) . '</mark>';
										elseif ( $product_obj->is_in_stock() ) :
											$stock_html = '<mark class="instock">' . __( 'In stock', 'multi-vendor-marketplace' ) . '</mark>';
										else :
											$stock_html = '<mark class="outofstock">' . __( 'Out of stock', 'multi-vendor-marketplace' ) . '</mark>';
										endif;

										if ( $product_obj->managing_stock() ) :
											$stock_html .= ' (' . wc_stock_amount( $product_obj->get_stock_quantity() ) . ')';
										endif;

										/**
										 * Product Stock
										 *
										 * @since 1.0.0
										 */
										$stock_html = apply_filters( 'woocommerce_admin_stock_html', $stock_html, $product_obj );

										/* translators: %1$s: Strong Start %2$s: Product Stock */
										printf( esc_html__( '%1$s Stock: %2$s', 'multi-vendor-marketplace' ), '<strong>', '</strong>' . wp_kses_post( $stock_html ) . '<br/>' );
									endif;

									if ( wc_product_sku_enabled() ) :
										$sku = $product_obj->get_sku() ? esc_html( $product_obj->get_sku() ) : '<span class="na">&ndash;</span>';
										/* translators: %1$s: Strong Start %2$s: SKU */
										printf( esc_html__( '%1$s SKU: %2$s', 'multi-vendor-marketplace' ), '<strong>', '</strong>' . wp_kses_post( $sku ) . '<br/>' );
									endif;

									/* translators: %1$s: Strong Start %2$s: Date */
									printf( esc_html__( '%1$s Date: %2$s', 'multi-vendor-marketplace' ), '<strong>', '</strong><time datetime="' . esc_attr( $product_obj->get_date_created()->date( 'c' ) ) . '">' . esc_html( wc_format_datetime( $product_obj->get_date_created() ) ) . '</time><br/>' );
								elseif ( 'product-price' === $column_id ) :
									echo $product_obj->get_price_html() ? wp_kses_post( $product_obj->get_price_html() ) : '<span class="na">&ndash;</span>';
								elseif ( 'product-tag' === $column_id ) :
									$terms = get_the_terms( $product_obj->get_id(), 'product_tag' );

									if ( ! $terms ) :
										echo '<span class="na">&ndash;</span>';
									else :
										$term_list = array();

										foreach ( $terms as $_term ) :
											$term_list[] = '<a href="' . esc_url( get_tag_link( $_term->term_id ) ) . ' ">' . esc_html( $_term->name ) . '</a>';
										endforeach;

										/**
										 * Admin Product Terms List
										 *
										 * @since 1.0.0
										 */
										echo wp_kses_post( apply_filters( 'woocommerce_admin_product_term_list', implode( ', ', $term_list ), 'product_tag', $product_obj->get_id(), $term_list, $terms ) );
									endif;
								elseif ( 'product-category' === $column_id ) :
									$terms = get_the_terms( $product_obj->get_id(), 'product_cat' );

									if ( ! $terms ) :
										echo '<span class="na">&ndash;</span>';
									else :
										$term_list = array();

										foreach ( $terms as $_term ) :
											$term_list[] = '<a href="' . esc_url( get_term_link( $_term->term_id, 'product_cat' ) ) . ' ">' . esc_html( $_term->name ) . '</a>';
										endforeach;

										/**
										 * Admin Product Terms List
										 *
										 * @since 1.0.0
										 */
										echo wp_kses_post( apply_filters( 'woocommerce_admin_product_term_list', implode( ', ', $term_list ), 'product_cat', $product_obj->get_id(), $term_list, $terms ) );
									endif;
								elseif ( 'product-date' === $column_id ) :
									?>
									<time datetime="<?php echo esc_attr( $product_obj->get_date_created()->date( 'c' ) ); ?>"><?php echo esc_html( wc_format_datetime( $product_obj->get_date_created() ) ); ?></time>
									<?php
								elseif ( 'product-actions' === $column_id ) :
									?>
									<?php
									$actions = mvr_get_dashboard_duplicate_products_actions( $product_obj );

									if ( ! empty( $actions ) ) :
										foreach ( $actions as $key => $_action ) :
											switch ( $key ) :
												case 'mvr-duplicate':
													echo '<a href="' . esc_url( $_action['url'] ) . '" 
															data-product_id = "' . esc_attr( $product_obj->get_id() ) . '" 
															data-vendor_id = "' . esc_attr( $vendor_id ) . '" 
															data-source_vendor_id = "' . esc_attr( $product_obj->get_meta( '_mvr_vendor', true ) ) . '" 
															class="woocommerce-button' . esc_attr( $wp_button_class ) . ' button ' . sanitize_html_class( $key ) . '">' .
															esc_html( $_action['name'] ) .
														'</a>';
													break;
												default:
													echo '<a href="' . esc_url( $_action['url'] ) . '" class="woocommerce-button' . esc_attr( $wp_button_class ) . ' button ' . sanitize_html_class( $key ) . '">' . esc_html( $_action['name'] ) . '</a>';
													break;
											endswitch;
										endforeach;
									endif;
								endif;
								?>
							</td>
						<?php endforeach; ?>
					</tr>
					<?php
				endforeach;
				?>
			</tbody>
		</table>

		<?php
		/**
		 * Before Duplicate Product Pagination.
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_before_dashboard_duplicate_products_pagination' );

		if ( 1 < $vendor_products->max_num_pages ) :
			mvr_get_template(
				'dashboard/pagination.php',
				array(
					'current_page'    => $current_page,
					'wp_button_class' => $wp_button_class,
					'prev_url'        => mvr_get_dashboard_endpoint_url( 'mvr-duplicate', $current_page - 1 ),
					'next_url'        => mvr_get_dashboard_endpoint_url( 'mvr-duplicate', $current_page + 1 ),
				)
			);
		endif;
	else :
		wc_print_notice( esc_html__( 'No products found', 'multi-vendor-marketplace' ), 'notice' );
	endif;

	/**
	 * Products Table after
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_after_dashboard_products', $has_products );
	?>
	</form>
</div>