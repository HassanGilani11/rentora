<?php
/**
 * Profile Dashboard
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/dashboard/form-edit-profile.php.
 *
 * @package Multi Vendor\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Before profile Form
 *
 * Hook: mvr_before_profile_form
 *
 * @since 1.0.0
 */
do_action( 'mvr_before_profile_form' ); ?>
<div class="mvr-profile-form-wrapper">
	<form class="mvr-profile-form edit-form" action="" method="post" 
	<?php
	/**
	 * Profile Form Tag
	 *
	 * Hook: mvr_before_profile_form
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_profile_form_tag' );
	?>
	>

		<?php
		/**
		 * Profile Form Start
		 *
		 * Hook: mvr_profile_form_start
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_profile_form_start' );
		?>

		<p class="woocommerce-form-row">
			<label for="_logo_id"><?php echo esc_attr( get_option( 'mvr_dashboard_store_logo_field_label', 'Store Logo' ) ); ?></label>
			<input type="button" class="mvr-logo-remove<?php echo ( $vendor_obj->get_logo_id() && $vendor_obj->get_logo_id() > 0 ) ? '' : ' mvr-hide'; ?>" value="x">
			<img class="mvr-add-store-logo mvr-store-logo" src="<?php echo esc_url( $logo ); ?>" width="64" height ="64"/>
			<input name="_logo_id" class="mvr-logo-id" type="hidden" value="<?php echo esc_attr( $form_fields['_logo_id'] ); ?>">
		</p>

		<p class="woocommerce-form-row">
			<label for="_banner_id"><?php echo esc_attr( get_option( 'mvr_dashboard_store_banner_field_label', 'Store Banner' ) ); ?></label>
			<input type="button" class="mvr-banner-remove<?php echo ( $form_fields['_banner_id'] ) ? '' : ' mvr-hide'; ?>" value="x">
			<img class="mvr-add-store-banner mvr-store-banner" src="<?php echo esc_url( $banner ); ?>" width="800" height ="200"/>
			<input name="_banner_id" class="mvr-banner-id" type="hidden" value="<?php echo esc_attr( $form_fields['_banner_id'] ); ?>">
		</p>

		<p class="woocommerce-form-row">
			<label for="_name"><?php echo esc_attr( get_option( 'mvr_dashboard_vendor_name_field_label', 'Vendor Name' ) ); ?>&nbsp;<span class="required">*</span></label>
			<input type="text" class="mvr-vendor-name" style="" name="_name" id="_name" value="<?php echo esc_attr( $form_fields['_name'] ); ?>">
		</p>

		<p class="woocommerce-form-row">
			<label for="_shop_name"><?php echo esc_attr( get_option( 'mvr_dashboard_vendor_store_name_field_label', 'Store Name' ) ); ?>&nbsp;<span class="required">*</span></label>
			<input type="text" class="mvr-shop mvr-shop-name" style="" name="_shop_name" id="_shop_name" data-vendor_id="<?php echo esc_attr( $vendor_obj->get_id() ); ?>" value="<?php echo esc_attr( $form_fields['_shop_name'] ); ?>">
			<span class="mvr-description"></span>
		</p>

		<p class="woocommerce-form-row">
			<label for="_slug"><?php echo esc_attr( get_option( 'mvr_dashboard_vendor_slug_field_label', 'Vendor Slug' ) ); ?>&nbsp;<span class="required">*</span></label>
			<input type="text" class="mvr-slug" name="_slug" id="_slug" data-vendor_id="<?php echo esc_attr( $vendor_obj->get_id() ); ?>" value="<?php echo esc_attr( $form_fields['_slug'] ); ?>">
			<span class="mvr-description">
				<span class="mvr-store-url">
					<?php echo wp_kses_post( $store_url ); ?>
				</span>
			</span>
		</p>

		<p class="woocommerce-form-row">
			<label for="_email"><?php echo esc_attr( get_option( 'mvr_dashboard_vendor_email_field_label', 'Email address' ) ); ?>&nbsp;<span class="required">*</span></label>
			<input type="email" class="mvr-email" name="_email" id="_email" readonly=true value="<?php echo esc_attr( $form_fields['_email'] ); ?>"/>
		</p>

		<div class="woocommerce-form-row">
			<label for="mvr_description"><?php echo esc_attr( get_option( 'mvr_dashboard_vendor_description_field_label', 'Description' ) ); ?>&nbsp;<span class="required">*</span></label>
			<?php
			wp_editor(
				htmlspecialchars_decode( $form_fields['_description'], ENT_QUOTES ),
				'_description',
				array(
					'textarea_name' => '_description',
				)
			);
			?>
		</div>
		<p></p>
		<div class="woocommerce-form-row">
			<label for="mvr_description"><?php echo esc_attr( get_option( 'mvr_dashboard_vendor_toc_field_label', 'Terms & Conditions' ) ); ?>&nbsp;<span class="required">*</span></label>
			<?php
			wp_editor(
				htmlspecialchars_decode( $form_fields['_tac'], ENT_QUOTES ),
				'_tac',
				array(
					'textarea_name' => '_tac',
				)
			);
			?>
		</div>

		<div class="clear"></div>

		<?php
		/**
		 * Profile Form
		 *
		 * Hook: mvr_profile_form
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_profile_form' );
		?>

		<p>
			<?php wp_nonce_field( 'save_mvr_profile_details', '_mvr_nonce' ); ?>
			<button type="submit" class="woocommerce-Button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="save_mvr_payment_details" value="<?php esc_attr_e( 'Save changes', 'multi-vendor-marketplace' ); ?>"><?php esc_html_e( 'Save changes', 'multi-vendor-marketplace' ); ?></button>
			<input type="hidden" name="action" value="save_mvr_profile_details" />
			<input type="hidden" name="_vendor_id" value="<?php echo esc_attr( $vendor_obj->get_id() ); ?>" />
		</p>

		<?php
		/**
		 * Profile Form End
		 *
		 * Hook: mvr_profile_form_end
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_profile_form_end' );
		?>
	</form>
</div>
<?php
/**
 * After Profile Form
 *
 * Hook: mvr_after_profile_form
 *
 * @since 1.0.0
 */
do_action( 'mvr_after_profile_form' );
?>
