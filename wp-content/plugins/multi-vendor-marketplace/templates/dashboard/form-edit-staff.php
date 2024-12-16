<?php
/**
 * Staff Dashboard
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/dashboard/form-edit-staff.php.
 *
 * @package Multi Vendor\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Before staff Form
 *
 * Hook: mvr_before_staff_form
 *
 * @since 1.0.0
 */
do_action( 'mvr_before_staff_form' ); ?>
<div class="mvr-staff-form-wrapper">
	<form class="mvr-staff-form edit-form" action="" method="post" 
	<?php
	/**
	 * Staff Form Tag
	 *
	 * Hook: mvr_before_staff_form
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_before_staff_form' );
	?>
	>
		<?php
		/**
		 * Staff Form Start
		 *
		 * Hook: mvr_staff_form_start
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_staff_form_start' );

		if ( empty( $staff_obj->get_id() ) ) :
			?>
			<p class="form-row">
				<label for="_user_name"><?php echo esc_attr( get_option( 'mvr_dashboard_staff_username_field_label', 'Username' ) ); ?>&nbsp;<span class="required">*</span></label>
				<span class="woocommerce-input-wrapper">
					<input type="text" class="mvr-user-name input-text" name="_user_name" id="_user_name">
				</span>
			</p>

			<p class="form-row">
				<label for="_email"><?php echo esc_attr( get_option( 'mvr_dashboard_staff_user_email_field_label', 'User Email' ) ); ?>&nbsp;<span class="required">*</span></label>
				<span class="woocommerce-input-wrapper">
					<input type="text" class="mvr-last-name input-text" name="_email" id="_email">
				</span>
			</p>

			<p class="woocommerce-form-row">
				<label for="_password"><?php echo esc_attr( get_option( 'mvr_dashboard_staff_password_field_label', 'Create Password' ) ); ?>&nbsp;<span class="required">*</span></label>
				<input type="password" class="mvr-password" name="_password" id="_password">
			</p>

			<p class="woocommerce-form-row">
				<label for="_confirm_password"><?php echo esc_attr( get_option( 'mvr_dashboard_staff_confirm_password_field_label', 'Confirm Password' ) ); ?>&nbsp;<span class="required">*</span></label>
				<input type="password" class="mvr-confirm-password" name="_confirm_password" id="_confirm_password">
			</p>
		<?php else : ?>
			<div class="mvr-vendor-staff-details">
				<img alt="<?php esc_html_e( 'Profile Picture', 'multi-vendor-marketplace' ); ?>" src="<?php echo esc_url( get_avatar_url( $staff_obj->get_user_id() ) ); ?>" class="avatar avatar-96 photo" height="96" width="96" loading="lazy" decoding="async">
				<div class="mvr-vendor-staff-info">
					<?php
						/* translators: %1$s: Strong Start %2$s: Vendor Name */
						printf( esc_html__( '%1$s Name: %2$s', 'multi-vendor-marketplace' ), '<strong>', '<a href="' . esc_url( mvr_get_dashboard_endpoint_url( 'mvr-edit-staff', $staff_obj->get_id() ) ) . '">' . esc_attr( $staff_obj->get_name() ) . '</a></strong><br/>' );
						/* translators: %1$s: Strong Start %2$s: Email */
						printf( esc_html__( '%1$s Email: %2$s', 'multi-vendor-marketplace' ), '<strong>', '</strong>' . esc_attr( $staff_obj->get_email() ) );
					?>
				</div>
			</div>
			<?php
		endif;

		if ( $staff_obj->allow_capability( $vendor_obj ) ) :
			?>
			<h4><?php echo esc_attr( get_option( '', 'Capabilities' ) ); ?></h4>	
			<div id="accordion" class="mvr-staff-capabilities-wrapper">
				<?php if ( $staff_obj->allow_product_management( $vendor_obj ) ) : ?>
					<h4 class="mvr-product-cap-header mvr-active"><?php echo esc_attr( get_option( 'mvr_dashboard_product_mng_header_label', 'Product Management' ) ); ?></h4>
					<div class="options_group mvr-vendor-product-management">
						<?php if ( $vendor_obj->get_enable_product_management() ) : ?>
							<p class="form-field _enable_product_management_field ">
								<label for="_enable_product_management"><?php echo esc_attr( get_option( 'mvr_dashboard_product_mng_field_label', 'Product Management' ) ); ?></label>
								<input type="checkbox" class="checkbox mvr-enable-product-management" name="_enable_product_management" id="_enable_product_management" <?php checked( 'yes' === $staff_obj->get_enable_product_management(), true, true ); ?>> 
							</p>
							<?php
						endif;

						if ( $vendor_obj->get_product_creation() ) :
							?>
							<p class="form-field _product_creation_field ">
								<label for="_product_creation"><?php echo esc_attr( get_option( 'mvr_dashboard_product_creation_field_label', 'Product Creation' ) ); ?></label>
								<input type="checkbox" class="checkbox mvr-product-management-field" name="_product_creation" id="_product_creation" <?php checked( 'yes' === $staff_obj->get_product_creation(), true, true ); ?>> 
							</p>
							<?php
						endif;

						if ( $vendor_obj->get_product_modification() ) :
							?>
							<p class="form-field _product_modification_field">
								<label for="_product_modification"><?php echo esc_attr( get_option( 'mvr_dashboard_product_modi_field_label', 'Product Modification' ) ); ?></label>
								<input type="checkbox" class="checkbox mvr-product-management-field" name="_product_modification" id="_product_modification" <?php checked( 'yes' === $staff_obj->get_product_modification(), true, true ); ?>> 
							</p>
							<?php
						endif;

						if ( $vendor_obj->get_published_product_modification() ) :
							?>
							<p class="form-field _published_product_modification_field">
								<label for="_published_product_modification"><?php echo esc_attr( get_option( 'mvr_dashboard_pub_product_modi_field_label', 'Published Product Modification' ) ); ?></label>
								<input type="checkbox" class="checkbox mvr-product-management-field" name="_published_product_modification" id="_published_product_modification" <?php checked( 'yes' === $staff_obj->get_published_product_modification(), true, true ); ?>> 
							</p>
							<?php
						endif;

						if ( $vendor_obj->get_manage_inventory() ) :
							?>
							<p class="form-field _manage_inventory_field">
								<label for="_manage_inventory"><?php echo esc_attr( get_option( 'mvr_dashboard_manage_inventory_field_label', 'Manage Inventory' ) ); ?></label>
								<input type="checkbox" class="checkbox mvr-product-management-field" name="_manage_inventory" id="_manage_inventory" <?php checked( 'yes' === $staff_obj->get_manage_inventory(), true, true ); ?>> 
							</p>
							<?php
						endif;

						if ( $vendor_obj->get_product_deletion() ) :
							?>
							<p class="form-field _product_deletion_field">
								<label for="_product_deletion"><?php echo esc_attr( get_option( 'mvr_dashboard_product_deletion_field_label', 'Product Deletion' ) ); ?></label>
								<input type="checkbox" class="checkbox mvr-product-management-field" name="_product_deletion" id="_product_deletion" <?php checked( 'yes' === $staff_obj->get_product_deletion(), true, true ); ?>> 
							</p>
							<?php
						endif;
						?>
					</div>
					<?php
				endif;
				if ( $staff_obj->allow_order_management( $vendor_obj ) ) :
					?>
					<h4 class="mvr-order-cap-header"><?php echo esc_attr( get_option( 'mvr_dashboard_order_mng_header_label', 'Order Management' ) ); ?></h4>
					<div class="options_group mvr-vendor-order-management">
						<?php
						if ( $vendor_obj->get_enable_order_management() ) :
							?>
							<p class="form-field _enable_order_management_field">
								<label for="_enable_order_management"><?php echo esc_attr( get_option( 'mvr_dashboard_order_mng_field_label', 'Order Management' ) ); ?></label>
								<input type="checkbox" class="checkbox mvr-enable-order-management" name="_enable_order_management" id="_enable_order_management" <?php checked( 'yes' === $staff_obj->get_enable_order_management(), true, true ); ?>> 
							</p>
							<?php
						endif;

						if ( $vendor_obj->get_order_status_modification() ) :
							?>
							<p class="form-field _order_status_modification_field">
								<label for="_order_status_modification"><?php echo esc_attr( get_option( 'mvr_dashboard_order_status_modi_field_label', 'Order Status Modification' ) ); ?></label>
								<input type="checkbox" class="checkbox mvr-order-management-field" name="_order_status_modification" id="_order_status_modification" <?php checked( 'yes' === $staff_obj->get_order_status_modification(), true, true ); ?>> 
							</p>
							<?php
						endif;

						if ( $vendor_obj->get_commission_info_display() ) :
							?>
							<p class="form-field _commission_info_display_field">
								<label for="_commission_info_display"><?php echo esc_attr( get_option( 'mvr_dashboard_commission_info_field_label', 'Commission Info Display' ) ); ?></label>
								<input type="checkbox" class="checkbox mvr-order-management-field" name="_commission_info_display" id="_commission_info_display" <?php checked( 'yes' === $staff_obj->get_commission_info_display(), true, true ); ?>> 
							</p>
							<?php
						endif;
						?>
					</div>
					<?php
				endif;
				if ( $staff_obj->allow_coupon_management( $vendor_obj ) ) :
					?>
					<h4 class="mvr-coupon-cap-header"><?php echo esc_attr( get_option( 'mvr_dashboard_coupon_mng_header_label', 'Coupon Management' ) ); ?></h4>
					<div class="options_group mvr-vendor-coupon-management">
						<?php
						if ( $vendor_obj->get_enable_coupon_management() ) :
							?>
							<p class="form-field _enable_coupon_management_field ">
								<label for="_enable_coupon_management"><?php echo esc_attr( get_option( 'mvr_dashboard_coupon_mng_field_label', 'Coupon Management' ) ); ?></label>
								<input type="checkbox" class="checkbox mvr-enable-coupon-management" name="_enable_coupon_management" id="_enable_coupon_management" <?php checked( 'yes' === $staff_obj->get_enable_coupon_management(), true, true ); ?>> 
							</p>
							<?php
						endif;

						if ( $vendor_obj->get_coupon_creation() ) :
							?>
							<p class="form-field _coupon_creation_field ">
								<label for="_coupon_creation"><?php echo esc_attr( get_option( 'mvr_dashboard_coupon_creation_field_label', 'Coupon Creation' ) ); ?></label>
								<input type="checkbox" class="checkbox mvr-coupon-management-field" name="_coupon_creation" id="_coupon_creation" <?php checked( 'yes' === $staff_obj->get_coupon_creation(), true, true ); ?>> 
							</p>
							<?php
						endif;

						if ( $vendor_obj->get_coupon_modification() ) :
							?>
							<p class="form-field _coupon_modification_field ">
								<label for="_coupon_modification"><?php echo esc_attr( get_option( 'mvr_dashboard_coupon_modi_field_label', 'Coupon Modification' ) ); ?></label>
								<input type="checkbox" class="checkbox mvr-coupon-management-field" name="_coupon_modification" id="_coupon_modification" <?php checked( 'yes' === $staff_obj->get_coupon_modification(), true, true ); ?>> 
							</p>
							<?php
						endif;

						if ( $vendor_obj->get_published_coupon_modification() ) :
							?>
							<p class="form-field _published_coupon_modification_field ">
								<label for="_published_coupon_modification"><?php echo esc_attr( get_option( 'mvr_dashboard_pub_coupon_modi_field_label', 'Published Coupon Modification' ) ); ?></label>
								<input type="checkbox" class="checkbox mvr-coupon-management-field" name="_published_coupon_modification" id="_published_coupon_modification" <?php checked( 'yes' === $staff_obj->get_published_coupon_modification(), true, true ); ?>> 
							</p>
							<?php
						endif;

						if ( $vendor_obj->get_coupon_deletion() ) :
							?>
							<p class="form-field _coupon_deletion_field ">
								<label for="_coupon_deletion"><?php echo esc_attr( get_option( 'mvr_dashboard_coupon_deletion_field_label', 'Coupon Deletion' ) ); ?></label>
								<input type="checkbox" class="checkbox mvr-coupon-management-field" name="_coupon_deletion" id="_coupon_deletion" <?php checked( 'yes' === $staff_obj->get_coupon_deletion(), true, true ); ?>> 
							</p>
							<?php
						endif;
						?>
					</div>
					<?php
				endif;
				?>
			</div>
			<?php
		endif;
		?>
		<div class="clear"></div>

		<?php
		/**
		 * Staff Form
		 *
		 * Hook: mvr_staff_form
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_staff_form' );
		?>

		<p>
			<?php wp_nonce_field( 'save_mvr_vendor_staff', '_mvr_nonce' ); ?>
			<button type="submit" class="mvr-page-title-action woocommerce-Button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="save_mvr_address" value="<?php esc_attr_e( 'Save changes', 'multi-vendor-marketplace' ); ?>"><?php empty( $staff_id ) ? esc_html_e( 'Create Staff', 'multi-vendor-marketplace' ) : esc_html_e( 'Save changes', 'multi-vendor-marketplace' ); ?></button>
			<input type="hidden" name="action" value="save_mvr_vendor_staff" />
			<input type="hidden" name="_vendor_id" value="<?php echo esc_attr( $vendor_obj->get_id() ); ?>" />
			<input type="hidden" name="_staff_id" value="<?php echo esc_attr( $staff_obj->get_id() ); ?>" />
		</p>

		<?php
		/**
		 * Staff Form End
		 *
		 * Hook: mvr_staff_form_end
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_staff_form_end' );
		?>
	</form>
</div>
<?php
/**
 * After Staff Form
 *
 * Hook: mvr_after_staff_form
 *
 * @since 1.0.0
 */
do_action( 'mvr_after_staff_form' );
?>
