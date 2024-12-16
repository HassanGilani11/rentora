<?php
/**
 * Coupons
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/dashboard/coupons.php.
 *
 * @package Multi Vendor Marketplace\Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Before Coupons
 *
 * @since 1.0.0
 */
do_action( 'mvr_before_dashboard_coupons' );
?>
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
			<input type="search" id="mvr-coupon-search-input" name="mvr_search" value="<?php echo esc_attr( $term ); ?>">
			<?php wp_nonce_field( 'mvr-dashboard-coupons', '_mvr_nonce' ); ?>
			<button type="submit"><?php esc_html_e( 'Search coupons', 'multi-vendor-marketplace' ); ?></button>
		</p>

	<?php if ( $has_coupons ) : ?>
		<ul class="subsubsub">
			<?php foreach ( mvr_dashboard_coupon_table_views() as $key => $value ) : ?>
				<li class="<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( $value ); ?></li>
			<?php endforeach; ?>
		</ul>

		<?php
		/**
		 * Before Coupons Table
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_before_dashboard_coupons_table', $has_coupons );
		?>

	<table class="mvr-coupons-table mvr-dashboard-coupons mvr-frontend-table shop_table shop_table_responsive my_account_orders account-orders-table">
		<thead>
			<tr>
			<?php foreach ( mvr_get_dashboard_coupons_columns() as $column_id => $column_name ) : ?>
					<th class="mvr-coupons-table__header mvr-coupons-table__header-<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
				<?php endforeach; ?>
			</tr>
		</thead>

		<tbody>
		<?php
		foreach ( $vendor_coupons->coupons as $vendor_coupon ) :
			$coupon_obj = new WC_Coupon( $vendor_coupon->get_id() );
			?>
				<tr class="mvr-coupons-table__row mvr-coupons-table__row--status-<?php echo esc_attr( $coupon_obj->get_status() ); ?> order">
				<?php
				foreach ( mvr_get_dashboard_coupons_columns() as $column_id => $column_name ) :
					?>
						<td class="mvr-coupons-table__cell mvr-coupons-table__cell-<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
						<?php
						if ( has_action( 'mvr_dashboard_coupons_column_' . $column_id ) ) :
							/**
							 * Dashboard Coupons column
							 *
							 * @since 1.0.0
							 */
							do_action( 'mvr_dashboard_coupons_column_' . $column_id, $coupon_obj );
							elseif ( 'coupon-details' === $column_id ) :
								$coupon_endpoint = ( 'publish' === $coupon_obj->get_status() ) ? 'mvr-edit-coupon-publish' : 'mvr-edit-coupon';

								if ( ! mvr_allow_endpoint( $coupon_endpoint ) ) :
									$coupon_code = '<a href="' . esc_url( mvr_get_dashboard_endpoint_url( 'mvr-edit-coupon', $coupon_obj->get_id() ) ) . '">' . esc_attr( $coupon_obj->get_code() ) . '</a>';
								else :
									$coupon_code = $coupon_obj->get_code();
								endif;

								/* translators: %1$s: Strong Start %2$s: Coupon Code */
								printf( esc_html__( '%1$s Code: %2$s', 'multi-vendor-marketplace' ), '<strong>', '</strong>' . wp_kses_post( $coupon_code ) . '<br/>' );
								/* translators: %1$s: Strong Start %2$s: Coupon Type */
								printf( esc_html__( '%1$s Coupon Type: %2$s', 'multi-vendor-marketplace' ), '<strong>', '</strong>' . wp_kses_post( wc_get_coupon_type( $coupon_obj->get_discount_type() ) ) . '<br/>' );
								/* translators: %1$s: Strong Start %2$s: Coupon Amount */
								printf( esc_html__( '%1$s Coupon Amount: %2$s', 'multi-vendor-marketplace' ), '<strong>', '</strong>' . wp_kses_post( wc_price( $coupon_obj->get_amount() ) ) . '<br/>' );

								$expiry_date = $coupon_obj->get_date_expires();

								if ( $expiry_date ) :
									$date = $expiry_date->date_i18n( 'F j, Y' );
								else :
									$date = '&ndash;';
								endif;

								/* translators: %1$s: Strong Start %2$s: Coupon Expiry Date */
								printf( esc_html__( '%1$s Expiry Date: %2$s', 'multi-vendor-marketplace' ), '<strong>', '</strong>' . wp_kses_post( $date ) . '<br/>' );
							elseif ( 'coupon-description' === $column_id ) :
								if ( empty( $coupon_obj->get_description() ) ) {
									echo '&ndash;';
								} else {
									echo wp_kses_post( $coupon_obj->get_description() );
								}
							elseif ( 'coupon-product_ids' === $column_id ) :
								$product_ids = $coupon_obj->get_product_ids();

								if ( count( $product_ids ) > 0 ) :
									echo esc_html( implode( ', ', $product_ids ) );
								else :
									echo '&ndash;';
								endif;
							elseif ( 'coupon-usage_limit' === $column_id ) :
								$usage_limit = $coupon_obj->get_usage_limit();

								if ( $usage_limit ) :
									echo esc_html( $usage_limit );
								else :
									echo '&ndash;';
								endif;
							elseif ( 'coupon-actions' === $column_id ) :
								$actions = mvr_get_dashboard_coupons_actions( $coupon_obj );

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

		<?php
		/**
		 * Dashboard Coupons Pagination
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_before_dashboard_coupons_pagination' );

		if ( 1 < $vendor_coupons->max_num_pages ) :
			mvr_get_template(
				'dashboard/pagination.php',
				array(
					'current_page'    => $current_page,
					'wp_button_class' => $wp_button_class,
					'prev_url'        => mvr_get_dashboard_endpoint_url( 'mvr-coupons', $current_page - 1 ),
					'next_url'        => mvr_get_dashboard_endpoint_url( 'mvr-coupons', $current_page + 1 ),
				)
			);
		endif;
	else :
		wc_print_notice( esc_html__( 'No coupons found', 'multi-vendor-marketplace' ), 'notice' );
	endif;

	/**
	 * After Dashboard Coupon
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_after_dashboard_coupons', $has_coupons );
	?>
	</form>
</div>
<?php
