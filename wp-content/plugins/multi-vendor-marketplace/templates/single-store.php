<?php
/**
 * Single Store Page.
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/single-store.php.
 *
 * @package Multi Vendor Marketplace\Templates
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="mvr-single-store-wrapper">
	<?php
	/**
	 * Single Store Header.
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_single_store_header', $vendor_obj );

	/**
	 * Single Store Header.
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_single_store_contents', $vendor_obj );
	?>
</div>
<?php
