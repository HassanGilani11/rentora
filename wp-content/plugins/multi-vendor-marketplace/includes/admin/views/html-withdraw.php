<?php
/**
 * Withdraw.
 *
 * @package Multi-vendor/Admin
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$add_new_withdraw_url = mvr_get_withdraw_page_url( array( 'action' => 'add_new_withdraw' ) );
$search_term          = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : ''
?>
<div class = "wrap mvr-wrapper-cover woocommerce">
	<form method = "post" id="mvr_admin_withdraw_form" enctype = "multipart/form-data">
		<div class="mvr-table-wrap">
			<div class="mvr-withdraw-head-button">
				<h1 class="wp-heading-inline"><?php echo esc_html__( 'Withdrawal Requests', 'multi-vendor-marketplace' ); ?></h1>
				<a class="page-title-action mvr-admin-add-withdraw" href=" <?php echo esc_url( $add_new_withdraw_url ); ?> "> <?php esc_html_e( 'Create New Withdrawal Request', 'multi-vendor-marketplace' ); ?></a>
				<hr class="wp-header-end">
			</div>

			<?php
			if ( strlen( $search_term ) ) {
				/* translators: %s: search keywords */
				echo wp_kses_post( sprintf( '<span class="subtitle">' . esc_html__( 'Search results for &#8220;%s&#8221;', 'multi-vendor-marketplace' ) . '</span>', esc_html( $search_term ) ) );
			}

			$post_table->views();
			$post_table->search_box( esc_html__( 'Search Withdrawal Request', 'multi-vendor-marketplace' ), 'mvr_search' );
			wp_nonce_field( 'mvr-search_withdraw', '_mvr_nonce' );
			$post_table->display();
			?>
		</div>
	</form>
</div>
<?php
