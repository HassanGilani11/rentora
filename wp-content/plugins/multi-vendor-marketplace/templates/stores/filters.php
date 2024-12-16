<?php
/**
 * Stores Filters.
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/stores/filters.php.
 *
 * @package Multi Vendor Marketplace\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit accessed directly.
}
?>
<div class="mvr-store-filter-wrapper">
	<div class="mvr-store-count">
		<p class="item store-count">
			<?php
			/* translators: 1) number of stores */
			printf( esc_html__( 'Total Stores: %d', 'multi-vendor-marketplace' ), esc_attr( $vendors_objs->total_vendors ) );
			?>
		</p>
	</div>
	<div class="mvr-stores-filter-area">
		<form method="get">
			<?php if ( 'top' === $position ) : ?>
				<input type="text" name="mvr_store_name" class="store-name-search mvr-stores-filter-search" placeholder="<?php esc_html_e( 'Enter Store Name', 'multi-vendor-marketplace' ); ?>">
				<input type="submit" name="search_store_products" class="search-store-products" value="Search">
			<?php endif; ?>
		</form>
	</div>
</div>
