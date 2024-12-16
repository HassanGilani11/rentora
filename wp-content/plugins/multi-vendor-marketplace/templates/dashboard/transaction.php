<?php
/**
 * Transactions
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/dashboard/transaction.php.
 *
 * @package Multi Vendor\Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Transaction Table before
 *
 * @since 1.0.0
 */
do_action( 'mvr_before_dashboard_transactions' );
?>
<div class="mvr-wrap mvr-transaction-wrapper">
	<ul class="subsubsub">
		<?php foreach ( mvr_dashboard_transactions_table_views() as $key => $value ) : ?>
			<li class="<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( $value ); ?></li>
		<?php endforeach; ?>
	</ul>
	<form id="mvr-transaction-filter" method="get">
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
			<input type="search" id="mvr-transaction-search-input" name="mvr_search" value="<?php echo esc_attr( $term ); ?>">
			<input type="hidden" id="_mvr_nonce" name="_mvr_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mvr-dashboard-transaction-nonce' ) ); ?>">
			<button type="submit"><?php echo esc_attr( get_option( 'mvr_dashboard_transaction_search_btn_label', 'Search' ) ); ?></button>
		</p>
	<?php if ( $has_transactions ) : ?>
		<?php
		/**
		 * Transaction Table before
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_before_dashboard_transactions_table', $has_transactions );
		?>
		<table class="mvr-transaction-table mvr-dashboard-transactions-table mvr-frontend-table shop_table shop_table_responsive my_account_orders account-orders-table">
			<thead>
				<tr>
					<?php foreach ( mvr_get_dashboard_transaction_columns() as $column_id => $column_name ) : ?>
						<th class="mvr-transaction-table__header mvr-transaction-table__header-<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo wp_kses_post( $column_name ); ?></span></th>
					<?php endforeach; ?>
				</tr>
			</thead>

			<tbody>
				<?php
				foreach ( $vendor_transactions->transactions as $vendor_transaction ) :
					$transaction_obj = mvr_get_transaction( $vendor_transaction );
					?>
					<tr class="mvr-transaction-table__row mvr-transaction-table__row--status-<?php echo esc_attr( $transaction_obj->get_status() ); ?> transaction">
						<?php foreach ( mvr_get_dashboard_transaction_columns() as $column_id => $column_name ) : ?>
							<td class="mvr-transaction-table__cell mvr-transaction-table__cell-<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
								<?php
								if ( has_action( 'mvr_dashboard_transaction_column_' . $column_id ) ) :
									/**
									 * Transaction Column
									 *
									 * @since 1.0.0
									 */
									do_action( 'mvr_dashboard_transaction_column_' . $column_id, $product_obj );
								elseif ( 'transaction-id' === $column_id ) :
									echo wp_kses_post( '#' . $transaction_obj->get_id() );
								elseif ( 'transaction-status' === $column_id ) :
									echo wp_kses_post( mvr_get_transaction_status_name( $transaction_obj->get_status() ) );
								elseif ( 'transaction-amount' === $column_id ) :
									echo wp_kses_post( wc_price( $transaction_obj->get_amount() ) );
								elseif ( 'transaction-type' === $column_id ) :
									echo esc_html( strtoupper( $transaction_obj->get_type() ) );
								elseif ( 'transaction-description' === $column_id ) :
									echo wp_kses_post( $transaction_obj->get_description() );
								elseif ( 'transaction-date' === $column_id ) :
									?>
									<time datetime="<?php echo esc_attr( $transaction_obj->get_date_created()->date( 'c' ) ); ?>"><?php echo esc_html( wc_format_datetime( $transaction_obj->get_date_created() ) ); ?></time>
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
		 * Before transaction Table Pagination
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_before_dashboard_transaction_pagination' );

		if ( 1 < $vendor_transactions->max_num_pages ) :
			mvr_get_template(
				'dashboard/pagination.php',
				array(
					'current_page'    => $current_page,
					'wp_button_class' => $wp_button_class,
					'prev_url'        => mvr_get_dashboard_endpoint_url( 'mvr-transaction', $current_page - 1 ),
					'next_url'        => mvr_get_dashboard_endpoint_url( 'mvr-transaction', $current_page + 1 ),
				)
			);
		endif;
	else :
		wc_print_notice( esc_html__( 'No transactions found.', 'multi-vendor-marketplace' ), 'notice' );
	endif;

	/**
	 * Transaction Table after
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_after_dashboard_transactions', $has_transactions );
	?>
	</form>
</div>
