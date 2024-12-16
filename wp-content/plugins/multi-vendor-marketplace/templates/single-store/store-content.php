<?php
/**
 * Stores Product Content.
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/single-store/store-content.php.
 *
 * @package Multi Vendor Marketplace\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit accessed directly.
}
?>
<div class="mvr-single-store-content-wrapper">
	<div class="mvr-single-store-tabs">
		<ul>
			<?php
			foreach ( $tabs as $key => $value ) :
				$active_class = ( empty( $tab ) && 'overview' === $key ) ? ' active' : '';

				if ( ! empty( $tab ) && $key === $tab ) :
					$active_class = ' active';
				endif
				?>
				<li class="mvr-single-store-tab<?php echo esc_html( $active_class ); ?>">
					<a href="<?php echo esc_url( $value['url'] ); ?>"><?php echo esc_html( $value['label'] ); ?></a>
				</li>
				<?php
			endforeach;
			?>
		</ul>
	</div>
	<div class="mvr-single-store-tab-content">
		<?php
		/**
		 * Single store tab content
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_single_store_tab_content', $tab, $vendor_obj );
		?>
	</div>
</div>
