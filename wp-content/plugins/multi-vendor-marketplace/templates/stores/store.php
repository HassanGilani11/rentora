<?php
/**
 * Store.
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/stores/store.php.
 *
 * @package Multi Vendor Marketplace\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit accessed directly.
}

if ( ! mvr_is_vendor( $vendor_obj ) ) {
	return;
}

?>
<li <?php mvr_store_class( 'mvr-store-list' ); ?>>
	<?php
	/**
	 * Hook: mvr_before_store_loop_item.
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_before_store_loop_item', $vendor_obj );

	/**
	 * Hook: mvr_before_store_loop_item_name.
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_before_store_loop_item_name', $vendor_obj );

	/**
	 * Hook: mvr_store_loop_item_name.
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_store_loop_item_name', $vendor_obj );

	/**
	 * Hook: mvr_after_store_loop_item_name.
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_after_store_loop_item_name', $vendor_obj );

	/**
	 * Hook: mvr_after_store_loop_item.
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_after_store_loop_item', $vendor_obj );
	?>
</li>
