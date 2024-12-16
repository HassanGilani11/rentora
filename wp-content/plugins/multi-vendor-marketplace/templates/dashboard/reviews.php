<?php
/**
 * Reviews
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/dashboard/reviews.php.
 *
 * @package Multi-Vendor for WooCommerce\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="mvr-reviews-wrapper">
<?php if ( $reviews_obj->has_review ) : ?>
	<table class="mvr-reviews-table mvr-dashboard-reviews mvr-frontend-table shop_table shop_table_responsive my_account_orders account-orders-table">
		<thead>
			<tr>
				<?php foreach ( mvr_get_dashboard_reviews_columns() as $column_id => $column_name ) : ?>
					<th class="mvr-reviews-table__header mvr-reviews-table__header-<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
				<?php endforeach; ?>
			</tr>
		</thead>

		<tbody>
			<?php
			foreach ( $reviews_obj->reviews as $review_obj ) :
				?>
				<tr class="mvr-reviews-table__row order">
					<?php
					foreach ( mvr_get_dashboard_reviews_columns() as $column_id => $column_name ) :
						?>
						<td class="mvr-reviews-table__cell mvr-reviews-table__cell-<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
							<?php

							if ( has_action( 'mvr_dashboard_reviews_column_' . $column_id ) ) :
								/**
								 * Dashboard Customers Column
								 *
								 * @since 1.0.0
								 */
								do_action( 'mvr_dashboard_reviews_column_' . $column_id, $customer_obj );
							elseif ( 'review-customer' === $column_id ) :
								$user_obj = get_user_by( 'ID', $review_obj->user_id );

								if ( ! $user_obj ) {
									esc_html_e( 'User', 'multi-vendor-marketplace' );
								} else {
									echo esc_html( $user_obj->user_login ) . ' (' . esc_html( $user_obj->user_email ) . ')';
								}
							elseif ( 'review-rating' === $column_id ) :
								$rating = get_comment_meta( $review_obj->comment_ID, 'rating', true );

								if ( ! empty( $rating ) && is_numeric( $rating ) ) :
									$rating = (int) $rating;

									/* translators: 1: number representing a rating */
									$accessibility_label = sprintf( esc_html__( '%1$d out of 5', 'multi-vendor-marketplace' ), $rating );
									$stars               = str_repeat( '&#9733;', $rating );
									$stars              .= str_repeat( '&#9734;', 5 - $rating );

									?>
									<span aria-label="<?php echo esc_attr( $accessibility_label ); ?>"><?php echo esc_html( $stars ); ?></span>
									<?php
								endif;
							elseif ( 'review-comment' === $column_id ) :
								echo wp_kses_post( get_comment_text( $review_obj->comment_ID ) );
							elseif ( 'review-date' === $column_id ) :
								echo esc_html( human_time_diff( strtotime( $review_obj->comment_date_gmt ) ) );
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
	 * Review Pagination.
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_before_dashboard_reviews_pagination' );

	if ( 1 < $reviews_obj->max_num_pages ) :
		mvr_get_template(
			'emails/plain/email-coupon-details.php',
			array(
				'table_name'      => 'reviews',
				'current_page'    => $current_page,
				'wp_button_class' => $wp_button_class,
				'prev_url'        => mvr_get_dashboard_endpoint_url( 'mvr-customers', $current_page - 1 ),
				'next_url'        => mvr_get_dashboard_endpoint_url( 'mvr-customers', $current_page + 1 ),
			)
		);
		endif;

	else :
		wc_print_notice( esc_html__( 'No reviews found', 'multi-vendor-marketplace' ), 'notice' );
	endif;

	/**
	 * After Dashboard Reviews
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_after_dashboard_reviews' );
	?>
</div>

