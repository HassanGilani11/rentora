<?php
/**
 * Commission.
 *
 * @package Multi-vendor/Admin
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$add_new_commission_url = mvr_get_commission_page_url( array( 'action' => 'add_new_commission' ) );
$search_term            = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : ''
?>
<div class = "wrap mvr-wrapper-cover woocommerce">
	<form method = "post" id="mvr_admin_commission_form" enctype = "multipart/form-data">
		<div class="mvr-table-wrap">
			<div class="mvr-commission-head-button">
				<h1 class="wp-heading-inline"><?php echo esc_html__( 'Earnings', 'multi-vendor-marketplace' ); ?></h1>
				<a class="page-title-action" href=" <?php echo esc_url( $add_new_commission_url ); ?> "> <?php esc_html_e( 'Create New Earning', 'multi-vendor-marketplace' ); ?></a>
				<hr class="wp-header-end">
			</div>

			<?php
			if ( strlen( $search_term ) ) {
				/* translators: %s: search keywords */
				echo wp_kses_post( sprintf( '<span class="subtitle">' . esc_html__( 'Search results for &#8220;%s&#8221;', 'multi-vendor-marketplace' ) . '</span>', esc_html( $search_term ) ) );
			}

			$post_table->prepare_commission_ids();
			$post_table->process_bulk_action();
			$post_table->views();
			$post_table->search_box( esc_html__( 'Search Earings', 'multi-vendor-marketplace' ), 'mvr_search' );
			wp_nonce_field( 'mvr-search_commission', '_mvr_nonce' );
			$post_table->display();
			?>
		</div>
	</form>
</div>
<?php
