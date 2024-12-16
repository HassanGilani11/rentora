<?php
/**
 * Staff data meta box.
 *
 * @package Multi-Vendor for WooCommerce\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // exit if accessed directly.
}
?>

<div class="panel-wrap product_data">
	<ul class="mvr_staff_data_tabs wc-tabs">
		<?php foreach ( MVR_Meta_Box_Staff_Data::get_staff_data_tabs() as $key => $tab_args ) : ?>
			<li class="<?php echo esc_attr( $key ); ?>_options <?php echo esc_attr( $key ); ?>_tab <?php echo esc_attr( isset( $tab_args['class'] ) ? implode( ' ', (array) $tab_args['class'] ) : '' ); ?>">
				<a href="#<?php echo esc_attr( $tab_args['target'] ); ?>"><span><?php echo esc_html( $tab_args['label'] ); ?></span></a>
			</li>
		<?php endforeach; ?>
		<?php
		/**
		 * Staff Data Tab.
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_staff_write_panel_tabs' );
		?>
	</ul>

	<?php
		MVR_Meta_Box_Staff_Data::output_tabs();
		/**
		 * Staff Data Tab Content.
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_staff_data_panels' );
	?>
	<div class="clear"></div>
</div>
