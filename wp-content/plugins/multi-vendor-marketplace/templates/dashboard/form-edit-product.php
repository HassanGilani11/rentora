<?php
/**
 * Edit Product form
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/dashboard/form-edit-product.php.
 *
 * @package Multi-Vendor\Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! empty( $product_id ) ) {
	$product_edit_url = admin_url( 'post.php?post=' . absint( $product_id ) ) . '&action=edit';
} else {
	$product_edit_url = admin_url( 'post-new.php?post_type=product' );
}

$url = add_query_arg( array( '_mvr_access' => wp_create_nonce( 'mvr-vendor-access' ) ), $product_edit_url );

?>
<div class="mvr-edit-product-frame">
	<iframe id='mvr_edit_product_frame' class='mvr-edit-post' src="<?php echo esc_url( $url ); ?>" frameborder="0" height='800' width ='100%'></iframe>
</div>
<?php
