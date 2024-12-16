<?php
/**
 * Staff
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/dashboard/staffs.php.
 *
 * @package Multi Vendor\Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Staff Table before
 *
 * @since 1.0.0
 */
do_action( 'mvr_before_dashboard_staff' );
?>
<div class="mvr-wrap">
	<form id="mvr-dashboard-staff" method="get">
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
			<input type="hidden" name="action" value="mvr_search_staff" />
			<input type="search" id="mvr-staff-search-input" name="mvr_search" value="<?php echo esc_attr( $term ); ?>">
			<?php wp_nonce_field( 'mvr-search-staff', '_mvr_nonce' ); ?>
			<button type="submit"><?php echo esc_attr( get_option( 'mvr_dashboard_search_staff_btn_label', 'Search staff' ) ); ?></button>
		</p>

		<?php if ( $has_staff ) : ?>
			<ul class="subsubsub">
				<?php foreach ( mvr_dashboard_staff_table_views() as $key => $value ) : ?>
					<li class="<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( $value ); ?></li>
				<?php endforeach; ?>
			</ul>

			<?php
			/**
			 * Staff Table before
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_before_dashboard_staff_table', $has_staff );
			?>
		<table class="mvr-staff-table mvr-dashboard-staff-table mvr-frontend-table shop_table shop_table_responsive my_account_orders account-orders-table">
			<thead>
				<tr>
					<?php foreach ( mvr_get_dashboard_staff_columns() as $column_id => $column_name ) : ?>
						<th class="mvr-staff-table__header mvr-staff-table__header-<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo wp_kses_post( $column_name ); ?></span></th>
					<?php endforeach; ?>
				</tr>
			</thead>

			<tbody>
				<?php
				foreach ( $vendor_staffs->staffs as $vendor_staff ) :
					$staff_obj = mvr_get_staff( $vendor_staff );
					?>
					<tr class="mvr-staff-table__row mvr-staff-table__row--status-<?php echo esc_attr( $staff_obj->get_status() ); ?> staff">
						<?php foreach ( mvr_get_dashboard_staff_columns() as $column_id => $column_name ) : ?>
							<td class="mvr-staff-table__cell mvr-staff-table__cell-<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
								<?php
								if ( has_action( 'mvr_dashboard_staff_column_' . $column_id ) ) :
									/**
									 * Staff Table Column
									 *
									 * @since 1.0.0
									 */
									do_action( 'mvr_dashboard_staff_column_' . $column_id, $staff_obj );
								elseif ( 'staff-image' === $column_id ) :
									?>
									<img alt="<?php esc_html_e( 'Profile Picture', 'multi-vendor-marketplace' ); ?>" src="<?php echo esc_url( get_avatar_url( $staff_obj->get_user_id() ) ); ?>" class="avatar avatar-96 photo" height="96" width="96" loading="lazy" decoding="async">
									<?php
								elseif ( 'staff-details' === $column_id ) :
									/* translators: %1$s: Strong Start %2$s: Vendor Name */
									printf( esc_html__( '%1$s Name: %2$s', 'multi-vendor-marketplace' ), '<strong>', '<a href="' . esc_url( mvr_get_dashboard_endpoint_url( 'mvr-edit-staff', $staff_obj->get_id() ) ) . '">' . esc_attr( $staff_obj->get_name() ) . '</a></strong><br/>' );
									/* translators: %1$s: Strong Start %2$s: Email */
									printf( esc_html__( '%1$s Email: %2$s', 'multi-vendor-marketplace' ), '<strong>', '</strong>' . esc_attr( $staff_obj->get_email() ) );

								elseif ( 'staff-date' === $column_id ) :
									?>
									<time datetime="<?php echo esc_attr( $staff_obj->get_date_created()->date( 'c' ) ); ?>"><?php echo esc_html( wc_format_datetime( $staff_obj->get_date_created() ) ); ?></time>
									<?php
								elseif ( 'staff-actions' === $column_id ) :
									$actions = mvr_get_dashboard_staff_actions( $staff_obj );

									if ( ! empty( $actions ) ) :
										foreach ( $actions as $key => $_action ) :
											echo '<a href="' . esc_url( $_action['url'] ) . '" class="woocommerce-button' . esc_attr( $wp_button_class ) . ' button mvr-' . sanitize_html_class( $key ) . ' ' . sanitize_html_class( $key ) . '">' . esc_html( $_action['name'] ) . '</a>';
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
			 * Staff Table before Pagination
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_before_dashboard_staff_pagination' );

			if ( 1 < $vendor_staffs->max_num_pages ) :
				mvr_get_template(
					'dashboard/pagination.php',
					array(
						'current_page'    => $current_page,
						'wp_button_class' => $wp_button_class,
						'prev_url'        => mvr_get_dashboard_endpoint_url( 'mvr-staff', $current_page - 1 ),
						'next_url'        => mvr_get_dashboard_endpoint_url( 'mvr-staff', $current_page + 1 ),
					)
				);
			endif;
	else :
		wc_print_notice( esc_html__( 'No staff found', 'multi-vendor-marketplace' ), 'notice' );
	endif;

	/**
	 * Staff Table after
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_after_dashboard_staff', $has_staff );
	?>
	</form>
</div>
