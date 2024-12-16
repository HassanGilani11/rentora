<?php
/**
 * Payout Batch Log.
 *
 * @package Multi-vendor/Admin
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$search_term = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : ''
?>
<div class = "wrap mvr-wrapper-cover woocommerce">
	<form method = "post" id="mvr_admin_payout_batch_form" enctype = "multipart/form-data">
		<div class="mvr-table-wrap">
			<div class="mvr-payout-batch-head-button">
				<h1 class="wp-heading-inline"><?php echo esc_html__( 'Payout Batch Log', 'multi-vendor-marketplace' ); ?></h1>
			</div>

			<?php
			if ( strlen( $search_term ) ) {
				/* translators: %s: search keywords */
				echo wp_kses_post( sprintf( '<span class="subtitle">' . esc_html__( 'Search results for &#8220;%s&#8221;', 'multi-vendor-marketplace' ) . '</span>', esc_html( $search_term ) ) );
			}

			$post_table->views();
			$post_table->search_box( esc_html__( 'Search Payout Batch', 'multi-vendor-marketplace' ), 'mvr_search' );
			wp_nonce_field( 'mvr-search_payout_batch', '_mvr_nonce' );
			$post_table->display();
			?>
		</div>
	</form>
</div>
<?php
