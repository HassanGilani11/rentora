<?php
/**
 * Coupon Vendor.
 *
 * @package Multi-Vendor for WooCommerce\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="mvr-vendor-banner-container">
	<img src="<?php echo esc_url( $banner ); ?>" width="800" height ="200"/>
	<input type="hidden" class="mvr-vendor-banner-id" name="_banner_id" value="<?php echo esc_attr( $vendor_obj->get_banner_id() ); ?>">
</div>

<p>
	<a class="mvr-remove-store-banner<?php echo ! empty( $vendor_obj->get_banner_id() ) ? '' : ' mvr-hide'; ?>" href="#"><?php esc_html_e( 'Remove store banner', 'multi-vendor-marketplace' ); ?></a> 
	<a class="mvr-add-store-banner<?php echo ! empty( $vendor_obj->get_banner_id() ) ? ' mvr-hide' : ''; ?>" href="#"><?php esc_html_e( 'Set store banner', 'multi-vendor-marketplace' ); ?></a> 
</p>
<?php
