<?php
/**
 * Vendor profile data panel.
 *
 * @package Multi-Vendor for WooCommerce\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="social_link_vendor_data" class="panel woocommerce_options_panel">
	<div class="options_group mvr-vendor-social-links">
		<h4><?php esc_html_e( 'Social Links', 'multi-vendor-marketplace' ); ?></h4>
		<?php
		woocommerce_wp_text_input(
			array(
				'id'          => '_facebook',
				'value'       => $vendor_obj->get_facebook(),
				'label'       => __( 'Facebook', 'multi-vendor-marketplace' ),
				'description' => __( 'Facebook', 'multi-vendor-marketplace' ),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'          => '_twitter',
				'value'       => $vendor_obj->get_twitter(),
				'label'       => __( 'X', 'multi-vendor-marketplace' ),
				'description' => __( 'X', 'multi-vendor-marketplace' ),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'          => '_youtube',
				'value'       => $vendor_obj->get_instagram(),
				'label'       => __( 'Youtube', 'multi-vendor-marketplace' ),
				'description' => __( 'Youtube', 'multi-vendor-marketplace' ),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'          => '_instagram',
				'value'       => $vendor_obj->get_instagram(),
				'label'       => __( 'Instagram', 'multi-vendor-marketplace' ),
				'description' => __( 'Instagram', 'multi-vendor-marketplace' ),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'          => '_linkedin',
				'value'       => $vendor_obj->get_linkedin(),
				'label'       => __( 'Linkedin', 'multi-vendor-marketplace' ),
				'description' => __( 'Linkedin', 'multi-vendor-marketplace' ),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'          => '_pinterest',
				'value'       => $vendor_obj->get_pinterest(),
				'label'       => __( 'Pinterest', 'multi-vendor-marketplace' ),
				'description' => __( 'Pinterest', 'multi-vendor-marketplace' ),
			)
		);

		/**
		 * Vendor Options Social Links.
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_vendor_options_social_links' );
		?>
	</div>

	<?php
	/**
	 * Vendor Options Social Links Data.
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_vendor_options_social_links_data' );
	?>
</div>
