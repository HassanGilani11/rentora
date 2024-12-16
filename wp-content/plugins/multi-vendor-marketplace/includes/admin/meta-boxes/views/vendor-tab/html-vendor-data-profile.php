<?php
/**
 * Vendor profile data panel.
 *
 * @package Multi-Vendor for WooCommerce\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div id="profile_vendor_data" class="panel woocommerce_options_panel">
	<div class="options_group mvr-vendor-profile">
		<h4><?php esc_html_e( 'Profile', 'multi-vendor-marketplace' ); ?></h4>
		<?php
		woocommerce_wp_text_input(
			array(
				'id'                => '_shop_name',
				'value'             => $vendor_obj->get_shop_name(),
				'class'             => 'mvr-shop',
				'label'             => __( 'Store Name', 'multi-vendor-marketplace' ),
				'description'       => __( 'Enter Store Name', 'multi-vendor-marketplace' ),
				'custom_attributes' => array( 'data-vendor_id' => $vendor_obj->get_id() ),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'                => '_slug',
				'class'             => 'mvr-slug',
				'value'             => $vendor_obj->get_slug(),
				'label'             => __( 'Store Slug', 'multi-vendor-marketplace' ),
				'description'       => '<span class="mvr-store-url">' . mvr_get_store_url( '{slug}' ) . '</span><br/>',
				'custom_attributes' => array( 'data-vendor_id' => $vendor_obj->get_id() ),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'                => '_email',
				'value'             => $vendor_obj->get_email(),
				'class'             => 'mvr-readonly',
				'label'             => __( 'Admin', 'multi-vendor-marketplace' ),
				'description'       => __( 'Enter Vendor Email', 'multi-vendor-marketplace' ),
				'custom_attributes' => array( 'readonly' => true ),
			)
		);

		/**
		 * Vendor Profile options.
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_vendor_options_profile' );
		?>
	</div>

	<?php
	/**
	 * Vendor Profile data.
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_vendor_options_profile_vendor_data' );
	?>
</div>
