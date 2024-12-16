<?php
/**
 * Stores product orderby.
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/stores/orderby.php.
 *
 * @package Multi Vendor Marketplace\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<form class="woocommerce-ordering" method="get">
	<select name="orderby" class="orderby" aria-label="<?php esc_attr_e( 'Shop order', 'multi-vendor-marketplace' ); ?>">
		<?php foreach ( $catalog_orderby_options as $key => $value ) : ?>
			<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $orderby, $key ); ?>><?php echo esc_html( $value ); ?></option>
		<?php endforeach; ?>
	</select>
	<input type="hidden" name="paged" value="1" />
	<?php wc_query_string_form_fields( null, array( 'orderby', 'submit', 'paged', 'product-page' ) ); ?>
</form>
