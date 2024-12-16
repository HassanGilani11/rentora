<?php
/**
 * Single Product Multi Vendor.
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/single-product-multi-vendor.php.
 *
 * @package Multi Vendor Marketplace\Templates
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="mvr-single-product-multi-vendor-wrapper">
	<h3><?php echo esc_html( get_option( 'mvr_dashboard_spmv_title_label', 'Other Vendor to Consider' ) ); ?></h3>
	<table class="mvr-spmv-product-list mvr-frontend-table">
		<thead>
			<tr>
			<?php foreach ( mvr_get_spmv_table_columns() as $column_id => $column_name ) : ?>
					<th class="mvr-spmv-table__header mvr-spmv-table__header-<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tbody>
		<?php
		foreach ( $product_list as $product_id => $args ) :
			$vendor_obj = isset( $args['vendor_obj'] ) ? $args['vendor_obj'] : '';

			if ( ! mvr_is_vendor( $vendor_obj ) ) {
				continue;
			}

			$product_obj = isset( $args['product_obj'] ) ? $args['product_obj'] : '';

			if ( ! is_a( $product_obj, 'WC_Product' ) ) {
				continue;
			}
			?>
				<tr class="mvr-spmv-table__row mvr-spmv-table__row--status-<?php echo esc_attr( $product_obj->get_status() ); ?> product">
				<?php
				foreach ( mvr_get_spmv_table_columns() as $column_id => $column_name ) :
					?>
						<td class="mvr-spmv-table__cell mvr-spmv-table__cell-<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
						<?php
						if ( has_action( 'mvr_dashboard_spmv_column_' . $column_id ) ) :
							/**
							 * Single Product Multi Vendor Column.
							 *
							 * @since 1.0.0
							 */
							do_action( 'mvr_dashboard_spmv_column_' . $column_id, $product_obj );

							elseif ( 'product' === $column_id ) :
								echo '<b><a href="' . esc_url( $product_obj->get_permalink() ) . '">' . esc_html( $product_obj->get_title() ) . '</a></b>';
								mvr_get_template( 'sold-by.php', array( 'vendor_obj' => $vendor_obj ) );
							elseif ( 'price' === $column_id ) :
								echo wp_kses_post( $product_obj->get_price_html() );

							elseif ( 'rating' === $column_id ) :
								echo wp_kses_post( wc_get_rating_html( $product_obj->get_average_rating(), $product_obj->get_rating_count() ) );

								if ( comments_open() && $product_obj->get_review_count() ) :
									$review_url = $product_obj->get_permalink() . '#reviews';
									/* translators: %s:Reviews Count */
									echo wp_kses_post( sprintf( __( '<a href="%1$s" class="woocommerce-review-link" rel="nofollow">( %2$s customer review )</a>', 'multi-vendor-marketplace' ), esc_url( $review_url ), '<span class="count">' . esc_html( $product_obj->get_review_count() ) . '</span>' ) );
								else :
									esc_html_e( 'There are no reviews yet.', 'multi-vendor-marketplace' );
								endif;

							elseif ( 'actions' === $column_id ) :
								$actions = mvr_get_spmv_actions( $product_obj );

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
				<?php
			endforeach;
		?>
		</tbody>
	</table>
</div>
<?php
