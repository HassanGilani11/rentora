<?php
/**
 * Customers
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/dashboard/customers.php.
 *
 * @package Multi Vendor Marketplace\Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="mvr-wrap mvr-customers-wrapper">
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
			<input type="search" id="mvr-customer-search-input" name="mvr_search" value="<?php echo esc_attr( $term ); ?>">
			<input type="hidden" id="_mvr_nonce" name="_mvr_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mvr-dashboard-customer-nonce' ) ); ?>">
			<button type="submit"><?php echo esc_attr( get_option( 'mvr_dashboard_customer_search_btn_label', 'Search' ) ); ?></button>
		</p>
	<?php if ( $vendor_customers->has_customer ) : ?>
		<table class="mvr-customers-table mvr-dashboard-customers mvr-frontend-table shop_table shop_table_responsive my_account_orders account-orders-table">
			<thead>
				<tr>
					<?php foreach ( mvr_get_dashboard_customers_columns() as $column_id => $column_name ) : ?>
						<th class="mvr-customers-table__header mvr-customers-table__header-<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
					<?php endforeach; ?>
				</tr>
			</thead>

			<tbody>
				<?php
				foreach ( $vendor_customers->customers as $customer_obj ) :
					// User object is required.
					if ( ! mvr_is_customer( $customer_obj ) ) {
						continue;
					}
					?>
					<tr class="mvr-customers-table__row order">
						<?php
						foreach ( mvr_get_dashboard_customers_columns() as $column_id => $column_name ) :
							?>
							<td class="mvr-customers-table__cell mvr-customers-table__cell-<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
								<?php

								if ( has_action( 'mvr_dashboard_customers_column_' . $column_id ) ) :
									/**
									 * Dashboard Customers Column
									 *
									 * @since 1.0.0
									 */
									do_action( 'mvr_dashboard_customers_column_' . $column_id, $customer_obj );
								elseif ( 'customer-email' === $column_id ) :
									echo esc_html( $customer_obj->get_email() );
								elseif ( 'customer-last_active' === $column_id ) :
									echo esc_html( MVR_Date_Time::get_wp_format_datetime( $customer_obj->get_date_modified() ) );
								elseif ( 'customer-date_register' === $column_id ) :
									echo esc_html( MVR_Date_Time::get_wp_format_datetime( $customer_obj->get_date_created() ) );
								elseif ( 'customer-orders' === $column_id ) :
									echo esc_attr( $customer_obj->get_orders()->total_orders );
								elseif ( 'customer-total_spend' === $column_id ) :
									echo wp_kses_post( wc_price( $customer_obj->get_total_spend() ) );
								elseif ( 'customer-address' === $column_id ) :
									echo wp_kses_post(
										WC()->countries->get_formatted_address(
											array(
												'first_name' => $customer_obj->get_first_name(),
												'last_name' => $customer_obj->get_last_name(),
												'city'     => $customer_obj->get_city(),
												'state'    => $customer_obj->get_state(),
												'postcode' => $customer_obj->get_postcode(),
												'country'  => $customer_obj->get_country(),
											)
										)
									);
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
		 * Customer Pagination.
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_before_dashboard_customers_pagination' );

		if ( 1 < $vendor_customers->max_num_pages ) :
			mvr_get_template(
				'dashboard/pagination.php',
				array(
					'current_page'    => $current_page,
					'wp_button_class' => $wp_button_class,
					'prev_url'        => mvr_get_dashboard_endpoint_url( 'mvr-customers', $current_page - 1 ),
					'next_url'        => mvr_get_dashboard_endpoint_url( 'mvr-customers', $current_page + 1 ),
				)
			);
		endif;

		else :
			wc_print_notice( esc_html__( 'No customers found', 'multi-vendor-marketplace' ), 'notice' );
		endif;

		/**
		 * After Dashboard Customers
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_after_dashboard_customers' );
		?>
	</form>
</div>
