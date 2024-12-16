<?php
/**
 * Staff profile data panel.
 *
 * @package Multi-Vendor for WooCommerce\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div id="profile_staff_data" class="panel woocommerce_options_panel">
	<div class="options_group mvr-staff-profile">
		<h4><?php esc_html_e( 'Profile', 'multi-vendor-marketplace' ); ?></h4>
		<p class="form-field">
			<label for="_profile"><?php esc_html_e( 'Profile Picture:', 'multi-vendor-marketplace' ); ?></label>
			<img alt="<?php esc_html_e( 'Profile Picture', 'multi-vendor-marketplace' ); ?>" src="<?php echo esc_url( get_avatar_url( $staff_obj->get_user_id() ) ); ?>" class="avatar avatar-96 photo" height="96" width="96" loading="lazy" decoding="async">
		</p>
		<p class="form-field">
			<label for="_user_id"><?php esc_html_e( 'User ID:', 'multi-vendor-marketplace' ); ?></label>
			<a href="<?php echo esc_url( get_edit_user_link( $staff_obj->get_user_id() ) ); ?>"><?php echo '#' . esc_attr( $staff_obj->get_user_id() ); ?></a>
		</p>

		<p class="form-field">
			<label for="_name"><?php esc_html_e( 'Name:', 'multi-vendor-marketplace' ); ?></label>
			<?php echo esc_attr( $staff_obj->get_name() ); ?>
		</p>

		<p class="form-field">
			<label for="_email"><?php esc_html_e( 'Email:', 'multi-vendor-marketplace' ); ?></label>
			<?php echo esc_attr( $staff_obj->get_email() ); ?>
		</p>
		<p class="form-field">
			<label for="_vendor_id"><?php esc_html_e( 'Vendor:', 'multi-vendor-marketplace' ); ?></label>
			<?php
				mvr_select2_html(
					array(
						'name'        => '_vendor_id',
						'class'       => 'wc-product-search',
						'placeholder' => esc_html__( 'Search for a Vendor', 'multi-vendor-marketplace' ),
						'options'     => $staff_obj->get_vendor_id(),
						'type'        => 'vendor',
						'action'      => 'mvr_json_search_vendors',
						'css'         => 'width:80%',
						'multiple'    => false,
					)
				);
				?>
		</p>
		<?php

		/**
		 * Staff Profile Options.
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_staff_options_profile' );
		?>
	</div>

	<?php
	/**
	 * Staff Profile data.
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_vendor_options_profile_vendor_data' );
	?>
</div>
