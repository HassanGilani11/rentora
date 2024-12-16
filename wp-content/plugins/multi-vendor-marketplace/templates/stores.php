<?php
/**
 * Stores Page.
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/stores.php.
 *
 * @package Multi Vendor Marketplace\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit accessed directly.
}
?>
<div class="mvr-stores-wrapper">
	<?php
	/**
	 * Hook: mvr_stores_content.
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_stores_content', $vendors_objs );
	?>
</div>
<?php
