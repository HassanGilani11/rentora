<?php
/**
 * Products
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/dashboard/products.php.
 *
 * @package Multi Vendor\Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
	<?php if ( 1 !== $current_page ) : ?>
		<a class="mvr-button mvr-button-prev woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button<?php echo esc_attr( $wp_button_class ); ?>" href="<?php echo esc_url( $prev_url ); ?>"><?php esc_html_e( 'Previous', 'multi-vendor-marketplace' ); ?></a>
	<?php endif; ?>

	<?php if ( intval( 2 ) !== $current_page ) : ?>
		<a class="mvr-button mvr-button-next woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button<?php echo esc_attr( $wp_button_class ); ?>" href="<?php echo esc_url( $next_url ); ?>"><?php esc_html_e( 'Next', 'multi-vendor-marketplace' ); ?></a>
	<?php endif; ?>
</div>
