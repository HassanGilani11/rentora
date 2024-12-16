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

?>
<div class="mvr-single-store-overview-wrapper">
	<div class="mvr-vendor-description">
		<h2><?php esc_html_e( 'Description', 'multi-vendor-marketplace' ); ?></h2>
		<p>
			<?php echo wp_kses_post( $vendor_obj->get_description() ); ?>
		</p>
	</div>
</div>
