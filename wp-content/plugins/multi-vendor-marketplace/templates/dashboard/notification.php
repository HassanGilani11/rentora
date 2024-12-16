<?php
/**
 * Notification
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/dashboard/notification.php.
 *
 * @package Multi Vendor\Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Notification Table before
 *
 * @since 1.0.0
 */
do_action( 'mvr_before_dashboard_notification' );
?>
<div class="mvr-wrap">
	<form id="mvr-dashboard-notification" method="get">
		<!-- Search Result -->
		<?php if ( $term ) : ?>
			<span class="subtitle">
				<?php
				/* translators: Term */
				printf( esc_html__( 'Search results for: %s', 'multi-vendor-marketplace' ), wp_kses_post( '<strong>' . $term . '</strong>' ) );
				?>
			</span>
			<?php
		endif;
		if ( $has_notifications ) :
			?>
		<ul class="subsubsub">
			<?php foreach ( mvr_dashboard_notification_table_views() as $key => $value ) : ?>
				<li class="<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( $value ); ?></li>
			<?php endforeach; ?>
		</ul>

		<p class="mvr-search-box">
			<input type="hidden" name="action" value="mvr_search_notification" />
			<input type="search" id="mvr-notification-search-input" name="mvr_search" value="<?php echo esc_attr( $term ); ?>">
			<?php wp_nonce_field( 'mvr-search-notification', '_mvr_nonce' ); ?>
			<button type="submit"><?php esc_html_e( 'Search Notification', 'multi-vendor-marketplace' ); ?></button>
		</p>

			<?php
			/**
			 * Notification Table before
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_before_dashboard_notification_table', $has_notifications );
			?>
		<table class="mvr-notification-table mvr-dashboard-notification-table mvr-frontend-table shop_table shop_table_responsive my_account_orders account-orders-table">
			<thead>
				<tr>
					<?php foreach ( mvr_get_dashboard_notification_columns() as $column_id => $column_name ) : ?>
						<th class="mvr-notification-table__header mvr-notification-table__header-<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo wp_kses_post( $column_name ); ?></span></th>
					<?php endforeach; ?>
				</tr>
			</thead>

			<tbody>
				<?php
				foreach ( $vendor_notifications->notifications as $notification_obj ) :
					$notification_obj = mvr_get_notification( $notification_obj );

					if ( ! mvr_is_notification( $notification_obj ) ) :
						continue;
					endif;

					if ( 'unread' === $notification_obj->get_status() ) :
						$notification_obj->update_status( 'read' );
					endif;
					?>
					<tr class="mvr-notification-table__row mvr-notification-table__row--status-<?php echo esc_attr( $notification_obj->get_status() ); ?> notification">
						<?php foreach ( mvr_get_dashboard_notification_columns() as $column_id => $column_name ) : ?>
							<td class="mvr-notification-table__cell mvr-notification-table__cell-<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
								<?php
								if ( has_action( 'mvr_dashboard_notification_column_' . $column_id ) ) :
									/**
									 * Custom Dashboard Notification Column
									 *
									 * @since 1.0.0
									 */
									do_action( 'mvr_dashboard_notification_column_' . $column_id, $notification_obj );
								elseif ( 'notification-type' === $column_id ) :
									echo esc_html( mvr_notification_type_name( $notification_obj->get_source_from() ) );
								elseif ( 'notification-message' === $column_id ) :
									echo wp_kses_post( $notification_obj->get_message() );
								elseif ( 'notification-status' === $column_id ) :
									echo esc_html( $notification_obj->get_status() );
								elseif ( 'notification-date' === $column_id ) :
									?>
									<time datetime="<?php echo esc_attr( $notification_obj->get_date_created()->date( 'c' ) ); ?>"><?php echo esc_html( wc_format_datetime( $notification_obj->get_date_created() ) ); ?></time>
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
			 * Notification Table Pagination
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_before_dashboard_notification_pagination' );

			if ( 1 < $vendor_notifications->max_num_pages ) :
				mvr_get_template(
					'dashboard/pagination.php',
					array(
						'current_page'    => $current_page,
						'wp_button_class' => $wp_button_class,
						'prev_url'        => mvr_get_dashboard_endpoint_url( 'mvr-notification', $current_page - 1 ),
						'next_url'        => mvr_get_dashboard_endpoint_url( 'mvr-notification', $current_page + 1 ),
					)
				);
			endif;
	else :
		wc_print_notice( esc_html__( 'No notification found.', 'multi-vendor-marketplace' ), 'notice' );
	endif;

	/**
	 * Notification Table after
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_after_dashboard_notification', $has_notifications );
	?>
	</form>
</div>
