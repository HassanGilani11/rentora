<?php
/**
 * Vendor data meta box.
 *
 * @package Multi-Vendor for WooCommerce\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // exit if accessed directly.
}
?>

<div class="panel-wrap product_data">
	<ul class="mvr_vendor_data_tabs wc-tabs">
		<?php
		foreach ( MVR_Meta_Box_Vendor_Data::get_vendor_data_tabs() as $key => $tab_args ) :

			if ( 'profile' === $key && ! $vendor_obj->cleared_profile_tab( 'admin' ) ) {
				$tab_args['class'][] = 'mvr-required-tab';
			}

			if ( 'address' === $key && ! $vendor_obj->cleared_address_tab() ) {
				$tab_args['class'][] = 'mvr-required-tab';
			}

			if ( 'payment' === $key && ! $vendor_obj->cleared_payment_tab() ) {
				$tab_args['class'][] = 'mvr-required-tab';
			}
			?>
			<li class="<?php echo esc_attr( $key ); ?>_options <?php echo esc_attr( $key ); ?>_tab <?php echo esc_attr( isset( $tab_args['class'] ) ? implode( ' ', (array) $tab_args['class'] ) : '' ); ?>">
				<a href="#<?php echo esc_attr( $tab_args['target'] ); ?>"><span><?php echo esc_html( $tab_args['label'] ); ?></span></a>
			</li>
		<?php endforeach; ?>
		<?php
		/**
		 * Vendor Data Tab.
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_vendor_write_panel_tabs' );
		?>
	</ul>

	<?php
		MVR_Meta_Box_Vendor_Data::output_tabs();
		/**
		 * Vendor Data Tab Content.
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_vendor_data_panels' );
	?>
	<div class="clear"></div>
</div>
