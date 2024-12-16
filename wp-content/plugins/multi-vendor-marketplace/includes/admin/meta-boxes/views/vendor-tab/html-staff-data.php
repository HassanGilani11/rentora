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

<div class="mvr-vendor-staff-data">
	<p class="form-field mvr-staff-profile">
		<img alt="<?php esc_html_e( 'Profile Picture', 'multi-vendor-marketplace' ); ?>" src="<?php echo esc_url( get_avatar_url( $staff_obj->get_user_id() ) ); ?>" class="avatar avatar-96 photo" height="96" width="96" loading="lazy" decoding="async">
	</p>

	<p class="form-field mvr-staff-user-id">
		<label for="_user_id"><?php esc_html_e( 'User ID:', 'multi-vendor-marketplace' ); ?></label>
		<a href="<?php echo esc_url( get_edit_user_link( $staff_obj->get_user_id() ) ); ?>"><?php echo '#' . esc_attr( $staff_obj->get_user_id() ); ?></a>
	</p>

	<p class="form-field mvr-staff-id">
		<label for="_user_id"><?php esc_html_e( 'Staff ID:', 'multi-vendor-marketplace' ); ?></label>
		<a href="<?php echo esc_url( $staff_obj->get_admin_edit_url() ); ?>"><?php echo '#' . esc_attr( $staff_obj->get_id() ); ?></a>
	</p>

	<p class="form-field mvr-staff-name">
		<label for="_name"><?php esc_html_e( 'Name:', 'multi-vendor-marketplace' ); ?></label>
		<?php echo esc_attr( $staff_obj->get_name() ); ?>
	</p>

	<p class="form-field mvr-staff-email">
		<label for="_email"><?php esc_html_e( 'Email:', 'multi-vendor-marketplace' ); ?></label>
		<?php echo esc_attr( $staff_obj->get_email() ); ?>
	</p>

	<p class="form-field mvr-staff-action">
		<a href="<?php echo esc_url( $staff_obj->get_admin_edit_url() ); ?>" class="button"><?php esc_html_e( 'Edit', 'multi-vendor-marketplace' ); ?></a>
		<button type="button" class="mvr-remove-staff button" data-staff_id="<?php echo esc_attr( $staff_obj->get_id() ); ?>"><?php esc_html_e( 'Remove', 'multi-vendor-marketplace' ); ?></button>
	</p>
</div>
