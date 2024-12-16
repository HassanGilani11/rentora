<?php
/**
 * Product Vendor.
 *
 * @package Multi-Vendor for WooCommerce\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="mvr-product-vendor">
	<?php
	mvr_select2_html(
		array(
			'name'        => '_mvr_product_vendor',
			'class'       => 'wc-product-search',
			'placeholder' => esc_html__( 'Search for a Vendor', 'multi-vendor-marketplace' ),
			'options'     => $product->get_meta( '_mvr_vendor', true ),
			'type'        => 'vendor',
			'action'      => 'mvr_json_search_vendors',
			'css'         => 'width:80%',
			'multiple'    => false,
		)
	);
	?>
</div>
<?php
