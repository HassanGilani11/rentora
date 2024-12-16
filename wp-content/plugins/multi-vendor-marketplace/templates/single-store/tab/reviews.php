<?php
/**
 * Reviews.
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/single-store/tab/reviews.php.
 *
 * @package Multi Vendor Marketplace\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit accessed directly.
}

?>
<div class="mvr-single-store-review-wrapper">
	<form id="mvr-review-form" method="post">
		<div class="mvr-comments">
			<h2 class="mvr-reviews-title">
				<?php
				$count = $vendor_obj->get_review_count();

				if ( $count && wc_review_ratings_enabled() ) :
					/* translators: 1: reviews count 2: store name */
					$reviews_title = sprintf( esc_html( _n( '%1$s review for %2$s', '%1$s reviews for %2$s', $count, 'multi-vendor-marketplace' ) ), esc_html( $count ), '<span>' . $vendor_obj->get_shop_name() . '</span>' );

					/**
					 * Review Title
					 *
					 * @since 1.0.0
					 */
					$reviews_title = apply_filters( 'mvr_store_reviews_title', $reviews_title, $count, $vendor_obj );

					echo wp_kses_post( $reviews_title );
				else :
					esc_html_e( 'Reviews', 'multi-vendor-marketplace' );
				endif;
				?>
			</h2>

			<?php
			if ( $reviews_obj->has_review ) :
				?>
				<ol class="commentlist">
					<?php
					wp_list_comments(
						/**
						 * Review List Arguments
						 *
						 * @since 1.0.0
						 */
						apply_filters(
							'mvr_store_review_list_args',
							array(
								'callback' => 'woocommerce_comments',
								'echo'     => true,
							),
							$reviews_obj
						),
						$reviews_obj->reviews
					);
					?>
				</ol>

				<?php
				if ( 1 < $reviews_obj->max_num_pages ) :
					$preview_url = add_query_arg(
						array(
							'tab' => 'reviews',
							'_p'  => $current_page - 1,
						),
						$vendor_obj->get_shop_url()
					);
					$next_url    = add_query_arg(
						array(
							'tab' => 'reviews',
							'_p'  => $current_page + 1,
						),
						$vendor_obj->get_shop_url()
					);
					mvr_get_template(
						'dashboard/pagination.php',
						array(
							'current_page'    => $current_page,
							'wp_button_class' => $wp_button_class,
							'prev_url'        => $preview_url,
							'next_url'        => $next_url,
						)
					);
				endif;
				?>
			<?php else : ?>
				<p class="woocommerce-noreviews"><?php esc_html_e( 'There are no reviews yet.', 'multi-vendor-marketplace' ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( $user_id && (int) $vendor_obj->get_user_id() !== $user_id && $is_vendor_customer && ! $reviewed ) : ?>
			<div class="mvr-rating">
				<label for="_rating"><?php esc_html_e( 'Your rating', 'multi-vendor-marketplace' ); ?></label>
				<p class="mvr-stars">						
					<span>							
						<a class="star-1 mvr-star" href="#">1</a>							
						<a class="star-2 mvr-star" href="#">2</a>							
						<a class="star-3 mvr-star" href="#">3</a>							
						<a class="star-4 mvr-star" href="#">4</a>							
						<a class="star-5 mvr-star" href="#">5</a>						
					</span>
					<input type="hidden" class="mvr-store-rating" name="_rating" value="" />					
				</p>
			</div>
			<div class="mvr-review">
				<label for="_review"><?php esc_html_e( 'Your Comment', 'multi-vendor-marketplace' ); ?></label>
				<textarea id="_review" name="_review"></textarea>
			</div>
			<div class="mvr-submit-wrapper">
				<input type="hidden" name="_vendor_id" value="<?php echo esc_attr( $vendor_obj->get_id() ); ?>" />
				<input type="hidden" name="_user_id" value="<?php echo esc_attr( $user_id ); ?>" />
				<input type="hidden" name="action" value="mvr_vendor_review" />
				<?php wp_nonce_field( 'mvr_vendor_review', '_mvr_nonce' ); ?>
				<button type="submit" class="woocommerce-Button button mvr-vendor-review-submit" name="mvr_vendor_review" disabled=true ><?php esc_html_e( 'Submit', 'multi-vendor-marketplace' ); ?></button>
			</div>
		<?php endif; ?>
		</form>
</div>
