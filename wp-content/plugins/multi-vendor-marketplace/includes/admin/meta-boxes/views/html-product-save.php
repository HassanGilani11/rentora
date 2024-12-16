<?php
/**
 * Product Vendor.
 *
 * @package Multi-Vendor for WooCommerce\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="mvr-product-save">
	<input type="hidden" name="_mvr_product_post_type" value="<?php echo ( 'post-new.php' === $pagenow ) ? 'new' : 'edit'; ?>">
	<input type="hidden" name="_mvr_product_vendor" value="<?php echo esc_attr( mvr_get_current_vendor_id() ); ?>">
	<input type="hidden" name="post" value="<?php echo esc_attr( $post->ID ); ?>">
	<input type="hidden" name="_mvr_product_status" value="pending">
	<?php wp_nonce_field( 'mvr-vendor-access', '_mvr_access' ); ?>
	<input type="submit" name="publish" id="publish" class="mvr-admin-save-btn mvr-product-save-btn button button-primary button-large" value="<?php esc_html_e( 'Save', 'multi-vendor-marketplace' ); ?>">
</div>
<?php
