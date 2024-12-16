<?php
/**
 * Stores Product Content.
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/single-store/store-content.php.
 *
 * @package Multi Vendor Marketplace\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit accessed directly.
}

$args = array(
	'post_type'      => 'product',
	'post_status'    => 'publish',
	'posts_per_page' => 12,
	'paged'          => $paged,
	'meta_key'       => '_mvr_vendor',
	'meta_value'     => $vendor_obj->get_id(),
);

if ( ! empty( $term ) ) {
	$args['s'] = $term;

	echo '<span class="subtitle">' . esc_html__( 'Search results for:', 'multi-vendor-marketplace' ) . '<strong>' . esc_attr( $term ) . '</strong></span>';
}

$the_query = new WP_Query( $args );
?>
<div class="mvr-single-store-products-wrapper">
	<div class="mvr-product-filter-wrapper">
		<?php
		/**
		 * Single Store Header.
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_single_store_product_filters', $vendor_obj, $the_query->found_posts );
		?>
	</div>
	<div class="mvr-product-loop-content-wrapper">
		<?php
		if ( $the_query->have_posts() ) :
			?>
			<div class="mvr-vendor-products-wrapper">
				<?php
				/**
				 * Single Store Header.
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_single_store_before_products_loop', $vendor_obj );

				woocommerce_product_loop_start();

				while ( $the_query->have_posts() ) :
					$the_query->the_post();

					wc_get_template_part( 'content', 'product' );
				endwhile;

				woocommerce_product_loop_end();

				wp_reset_postdata();

				/**
				 * Single Store Header.
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_single_store_after_products_loop', $vendor_obj );
				?>
			</div>
		<?php else : ?>
			<p class="mvr-vendor-no-product-info">
				<?php esc_html_e( 'No products were found of this vendor.', 'multi-vendor-marketplace' ); ?>
			</p>
			<?php
		endif;
		?>
	</div>
</div>
