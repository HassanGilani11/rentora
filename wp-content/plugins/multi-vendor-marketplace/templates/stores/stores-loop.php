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

if ( $vendors_objs->has_vendor ) :
	/**
	 * Before Stores Loop.
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_before_stores_loop', $vendors_objs, 'top' );
	?>
	<ul class="mvr-stores columns-<?php echo esc_attr( wc_get_default_products_per_row() ); ?>">
	<?php
	foreach ( $vendors_objs->vendors as $vendor_obj ) :
		/**
		 * Hook: mvr_stores_loop_content.
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_stores_loop_content', $vendor_obj );
	endforeach;
	?>
	</ul>
	<?php

	/**
	 * After Stores Loop.
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_after_stores_loop', $vendors_objs, 'bottom' );
else :
	/**
	 * Hook: mvr_no_stores_found.
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_no_stores_found' );
endif;
