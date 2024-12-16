<?php
/**
 * Sold by
 *
 * @package Multi Vendor Marketplace\Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="mvr-vendor-sold-by">
	<?php
	/* translators: %1$s: Label , %2$s: Store URL , %3$s : Store Name */
	printf( '%1$s <a href="%2$s">%3$s</a>', esc_html__( 'Sold by :', 'multi-vendor-marketplace' ), esc_url( $vendor_obj->get_shop_url() ), esc_html( $vendor_obj->get_shop_name() ) );
	?>
</div>

