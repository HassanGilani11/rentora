<?php
/**
 * Stores Product Filters.
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/single-store/filters.php.
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
			printf( esc_html__( 'Total Product(s): %d', 'multi-vendor-marketplace' ), esc_attr( $count ) );
			?>
		</p>
	</div>
	<div class="mvr-stores-filter-area">
		<form method="get" id="mvr_product_search_form">
			<input type="text" name="_s" class="store-name-search mvr-stores-filter-search" placeholder="<?php esc_html_e( 'Enter product name', 'multi-vendor-marketplace' ); ?>">
			<?php wp_nonce_field( 'mvr-search-vendor-products', '_mvr_nonce' ); ?>
			<input type="hidden" name="tab" value="products"/>
			<input type="hidden" name="action" value="mvr_search_vendor_product"/>
			<button type="submit"><?php esc_html_e( 'Search products', 'multi-vendor-marketplace' ); ?></button>
		</form>
	</div>
</div>
