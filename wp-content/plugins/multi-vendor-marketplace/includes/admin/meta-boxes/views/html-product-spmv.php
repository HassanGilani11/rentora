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
<div class="mvr-product-spmv">
	<ul class="mvr-product-spmv-list">
		<?php
		if ( ! empty( $product_map_id ) && $product_map->has_spmv ) :
			foreach ( $product_map->spmv_args as $spmv_args ) :
				$spmv_vendor_obj = mvr_get_vendor( $spmv_args->get_vendor_id() );

				if ( ! mvr_is_vendor( $spmv_vendor_obj ) ) {
					continue;
				}

				$spmv_product_obj = wc_get_product( $spmv_args->get_product_id() );

				if ( ! is_a( $spmv_product_obj, 'WC_Product' ) ) {
					continue;
				}

				if ( $vendor_id === $spmv_vendor_obj->get_id() ) {
					continue;
				}

				$exclude_vendors[] = $spmv_vendor_obj->get_id();
				?>
				<li>
					<a href="<?php echo esc_url( get_edit_post_link( $spmv_vendor_obj->get_id() ) ); ?>"><?php echo esc_attr( $spmv_vendor_obj->get_shop_name() ) . ' - ' . esc_attr( $spmv_vendor_obj->get_name() ); ?></a>
					<span class="mvr-delete-product">
						<span class="mvr-remove-spmv" data-spmv-id="<?php echo esc_attr( $spmv_args->get_id() ); ?>" data-product-id="<?php echo esc_attr( $spmv_product_obj->get_id() ); ?>">x</span>
					</span>
				</li>
				<?php
			endforeach;
		endif;
		?>
	</ul>
	<?php
	mvr_select2_html(
		array(
			'name'        => '_mvr_product_spmv',
			'class'       => 'wc-product-search mvr-spmv-search-field',
			'placeholder' => esc_html__( 'Search for a Vendor', 'multi-vendor-marketplace' ),
			'options'     => array(),
			'type'        => 'vendor',
			'action'      => 'mvr_json_search_vendors',
			'css'         => 'width:80%',
			'exclude'     => $exclude_vendors,
			'multiple'    => false,
		)
	);
	?>
	<button type="button" class="button mvr-product-spmv-btn" disabled><?php esc_html_e( 'Assign Products', 'multi-vendor-marketplace' ); ?></button>
	<input type="hidden" class="mvr-source-vendor-id" value="<?php echo esc_attr( $product->get_meta( '_mvr_vendor', true ) ); ?>"/>
	<input type="hidden" class="mvr-product-id" value="<?php echo esc_attr( $product->get_id() ); ?>"/>
</div>
<?php
