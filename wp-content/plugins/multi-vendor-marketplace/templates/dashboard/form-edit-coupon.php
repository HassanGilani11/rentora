<?php
/**
 * Edit Coupon form
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/dashboard/form-edit-coupon.php.
 *
 * @package Multi-Vendor\Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


if ( ! empty( $coupon_id ) ) {
	$coupon_edit_url = admin_url( 'post.php?post=' . absint( $coupon_id ) ) . '&action=edit';
} else {
	$coupon_edit_url = admin_url( 'post-new.php?post_type=shop_coupon' );
}

$url = add_query_arg( array( '_mvr_access' => wp_create_nonce( 'mvr-vendor-access' ) ), $coupon_edit_url );
?>
<div class="mvr-edit-coupon-frame">
	<iframe id='mvr_edit_coupon_frame' class='mvr-edit-post' src="<?php echo esc_url( $url ); ?>" frameborder="0" height='800' width ='2600'></iframe>
</div>
<?php
