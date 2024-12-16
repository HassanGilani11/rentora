<?php
/**
 * Products
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/dashboard/withdraw.php.
 *
 * @package Multi Vendor\Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Withdraw Table before
 *
 * @since 1.0.0
 */
do_action( 'mvr_before_dashboard_withdraws' );
?>
<div class="mvr-wrap">
	<form id="mvr-withdraw-filter" method="get">
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
			<input type="search" id="mvr-withdraw-search-input" name="mvr_search" value="<?php echo esc_attr( $term ); ?>">
			<input type="hidden" id="_mvr_nonce" name="_mvr_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mvr-dashboard-withdraw-nonce' ) ); ?>">
			<button type="submit"><?php echo esc_attr( get_option( 'mvr_dashboard_withdraw_search_btn_label', 'Search' ) ); ?></button>
		</p>
	<?php if ( $has_withdraws ) : ?>
		<ul class="subsubsub">
			<?php foreach ( mvr_dashboard_withdraw_table_views() as $key => $value ) : ?>
				<li class="<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( $value ); ?></li>
			<?php endforeach; ?>
		</ul>
		<?php
		/**
		 * Withdraw Table before
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_before_dashboard_withdraws_table', $has_withdraws );
		?>
		<table class="mvr-withdraw-table mvr-dashboard-withdraws mvr-frontend-table shop_table shop_table_responsive my_account_orders account-orders-table">
			<thead>
				<tr>
					<?php foreach ( mvr_get_dashboard_withdraw_columns() as $column_id => $column_name ) : ?>
						<th class="mvr-withdraw-table__header mvr-withdraw-table__header-<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo wp_kses_post( $column_name ); ?></span></th>
					<?php endforeach; ?>
				</tr>
			</thead>

			<tbody>
				<?php
				foreach ( $vendor_withdraws->withdraws as $vendor_withdraw ) :
					$withdraw_obj = mvr_get_withdraw( $vendor_withdraw );
					?>
					<tr class="mvr-withdraw-table__row mvr-withdraw-table__row--status-<?php echo esc_attr( $withdraw_obj->get_status() ); ?> withdraw">
						<?php foreach ( mvr_get_dashboard_withdraw_columns() as $column_id => $column_name ) : ?>
							<td class="mvr-withdraw-table__cell mvr-withdraw-table__cell-<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
								<?php
								if ( has_action( 'mvr_dashboard_withdraw_column_' . $column_id ) ) :
									/**
									 * Withdraw Column
									 *
									 * @since 1.0.0
									 */
									do_action( 'mvr_dashboard_withdraw_column_' . $column_id, $product_obj );
								elseif ( 'withdraw-id' === $column_id ) :
									echo '#' . wp_kses_post( $withdraw_obj->get_id() );
								elseif ( 'withdraw-status' === $column_id ) :
									echo wp_kses_post( mvr_get_withdraw_status_name( $withdraw_obj->get_status() ) );
								elseif ( 'withdraw-amount' === $column_id ) :
									echo wp_kses_post( wc_price( $withdraw_obj->get_amount() ) );
								elseif ( 'withdraw-charge' === $column_id ) :
									echo wp_kses_post( wc_price( $withdraw_obj->get_charge_amount() ) );
								elseif ( 'withdraw-payment' === $column_id ) :
									echo wp_kses_post( mvr_payment_method_options( $withdraw_obj->get_payment_method() ) );
								elseif ( 'withdraw-date' === $column_id ) :
									?>
									<time datetime="<?php echo esc_attr( $withdraw_obj->get_date_created()->date( 'c' ) ); ?>"><?php echo esc_html( wc_format_datetime( $withdraw_obj->get_date_created() ) ); ?></time>
									<?php
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
		 * Before Withdraw Table Pagination
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_before_dashboard_withdraw_pagination' );

		if ( 1 < $vendor_withdraws->max_num_pages ) :
			mvr_get_template(
				'dashboard/pagination.php',
				array(
					'current_page'    => $current_page,
					'wp_button_class' => $wp_button_class,
					'prev_url'        => mvr_get_dashboard_endpoint_url( 'mvr-withdraw', $current_page - 1 ),
					'next_url'        => mvr_get_dashboard_endpoint_url( 'mvr-withdraw', $current_page + 1 ),
				)
			);
		endif;
	else :
		wc_print_notice( esc_html__( 'No request found', 'multi-vendor-marketplace' ), 'notice' );
	endif;

	/**
	 * Withdraw Table after
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_after_dashboard_withdraws', $has_withdraws );
	?>
	</form>
</div>
