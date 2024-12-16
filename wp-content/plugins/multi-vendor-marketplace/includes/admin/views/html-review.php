<?php
/**
 * Review.
 *
 * @package Multi-vendor/Admin
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$search_term = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : ''
?>
<div class = "wrap mvr-wrapper-cover woocommerce">
	<form method = "post" id="mvr_admin_review_form" enctype = "multipart/form-data">
		<div class="mvr-table-wrap">
			<div class="mvr-review-head-button">
				<h1 class="wp-heading-inline"><?php echo esc_html__( 'Review', 'multi-vendor-marketplace' ); ?></h1>
				<hr class="wp-header-end">
			</div>

			<?php
			if ( strlen( $search_term ) ) {
				/* translators: %s: search keywords */
				echo wp_kses_post( sprintf( '<span class="subtitle">' . esc_html__( 'Search results for &#8220;%s&#8221;', 'multi-vendor-marketplace' ) . '</span>', esc_html( $search_term ) ) );
			}

			$post_table->views();
			$post_table->search_box( esc_html__( 'Search Review', 'multi-vendor-marketplace' ), 'mvr_search' );
			wp_nonce_field( 'mvr-search_review', '_mvr_nonce' );
			$post_table->display();
			?>
		</div>
	</form>
</div>
<?php
