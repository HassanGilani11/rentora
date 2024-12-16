<?php
/**
 * Enquiry
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/dashboard/enquiry.php.
 *
 * @package Multi Vendor\Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Enquiry Table before
 *
 * @since 1.0.0
 */
do_action( 'mvr_before_dashboard_enquiry' );
?>
<div class="mvr-wrap">
	<form id="mvr-dashboard-enquiry" method="get">
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
			<input type="hidden" name="action" value="mvr_search_enquiry" />
			<input type="search" id="mvr-enquiry-search-input" name="mvr_search" value="<?php echo esc_attr( $term ); ?>">
			<?php wp_nonce_field( 'mvr-search-enquiry', '_mvr_nonce' ); ?>
			<button type="submit"><?php esc_html_e( 'Search enquiry', 'multi-vendor-marketplace' ); ?></button>
		</p>

		<?php if ( $has_enquiry ) : ?>
		<ul class="subsubsub">
			<?php foreach ( mvr_dashboard_enquiry_table_views() as $key => $value ) : ?>
				<li class="<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( $value ); ?></li>
			<?php endforeach; ?>
		</ul>
			<?php
			/**
			 * Enquiry Table before
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_before_dashboard_enquiry_table', $has_enquiry );
			?>
		<table class="mvr-enquiry-table mvr-dashboard-enquiry-table mvr-frontend-table shop_table shop_table_responsive my_account_orders account-orders-table">
			<thead>
				<tr>
					<?php foreach ( mvr_get_dashboard_enquiry_columns() as $column_id => $column_name ) : ?>
						<th class="mvr-enquiry-table__header mvr-enquiry-table__header-<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo wp_kses_post( $column_name ); ?></span></th>
					<?php endforeach; ?>
				</tr>
			</thead>

			<tbody>
				<?php
				foreach ( $vendor_enquiries->enquiries as $enquiry_obj ) :
					$enquiry_obj = mvr_get_enquiry( $enquiry_obj );

					if ( ! mvr_is_enquiry( $enquiry_obj ) ) :
						continue;
					endif;

					if ( 'unread' === $enquiry_obj->get_status() ) :
						$enquiry_obj->update_status( 'read' );
					endif;
					?>
					<tr class="mvr-enquiry-table__row mvr-enquiry-table__row--status-<?php echo esc_attr( $enquiry_obj->get_status() ); ?> enquiry">
						<?php foreach ( mvr_get_dashboard_enquiry_columns() as $column_id => $column_name ) : ?>
							<td class="mvr-enquiry-table__cell mvr-enquiry-table__cell-<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
								<?php
								if ( has_action( 'mvr_dashboard_enquiry_column_' . $column_id ) ) :
									/**
									 * Custom Dashboard Enquiry Column
									 *
									 * @since 1.0.0
									 */
									do_action( 'mvr_dashboard_enquiry_column_' . $column_id, $enquiry_obj );
								elseif ( 'enquiry-customer' === $column_id ) :
									echo esc_attr( $enquiry_obj->get_customer_name() ) . '(' . esc_attr( $enquiry_obj->get_customer_email() ) . ')';
								elseif ( 'enquiry-message' === $column_id ) :
									echo wp_kses_post( $enquiry_obj->get_message() );
								elseif ( 'enquiry-date' === $column_id ) :
									?>
									<time datetime="<?php echo esc_attr( $enquiry_obj->get_date_created()->date( 'c' ) ); ?>"><?php echo esc_html( wc_format_datetime( $enquiry_obj->get_date_created() ) ); ?></time>
									<?php
								elseif ( 'enquiry-action' === $column_id ) :
									$actions = mvr_get_dashboard_enquiry_actions( $enquiry_obj );

									if ( ! empty( $actions ) ) :
										foreach ( $actions as $key => $_action ) :
											echo '<a href="' . esc_url( $_action['url'] ) . '" class="woocommerce-button' . esc_attr( $wp_button_class ) . ' button mvr-' . sanitize_html_class( $key ) . ' ' . sanitize_html_class( $key ) . '">' . esc_html( $_action['name'] ) . '</a><br/>';

											if ( 'reply' === $key && mvr_check_is_array( maybe_unserialize( $enquiry_obj->get_reply() ) ) ) {
												esc_html_e( 'Replied', 'multi-vendor-marketplace' );
											}
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
			 * Enquiry Table Pagination
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_before_dashboard_enquiry_pagination' );

			if ( 1 < $vendor_enquiries->max_num_pages ) :
				mvr_get_template(
					'dashboard/pagination.php',
					array(
						'current_page'    => $current_page,
						'wp_button_class' => $wp_button_class,
						'prev_url'        => mvr_get_dashboard_endpoint_url( 'mvr-enquiry', $current_page - 1 ),
						'next_url'        => mvr_get_dashboard_endpoint_url( 'mvr-enquiry', $current_page + 1 ),
					)
				);
			endif;
	else :
		wc_print_notice( esc_html__( 'No enquiry found', 'multi-vendor-marketplace' ), 'notice' );
	endif;

	/**
	 * Enquiry Table after
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_after_dashboard_enquiry', $has_enquiry );
	?>
	</form>
</div>
