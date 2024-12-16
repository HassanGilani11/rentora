<?php
/**
 * Social Links Dashboard
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/dashboard/form-edit-social-links.php.
 *
 * @package Multi Vendor\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Before Social Links Form
 *
 * Hook: mvr_before_social_links_form
 *
 * @since 1.0.0
 */
do_action( 'mvr_before_social_links_form' ); ?>

<div class="mvr-social-links-form-wrapper">
	<form class="mvr-social-links-form edit-form" action="" method="post" 
	<?php
	/**
	 * Social Links Form Start
	 *
	 * Hook: mvr_social_links_form_tag
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_social_links_form_tag' );
	?>
	>

		<?php
		/**
		 * Social Links Form Start
		 *
		 * Hook: mvr_social_links_form_start
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_social_links_form_start' );
		?>

		<p class="woocommerce-form-row">
			<label for="_facebook"><?php echo esc_attr( get_option( 'mvr_dashboard_vendor_facebook_field_label', 'Facebook' ) ); ?></label>
			<input type="text" class="mvr-facebook" style="" name="_facebook" id="_facebook" value="<?php echo esc_html( $vendor_obj->get_facebook() ); ?>">
		</p>

		<p class="woocommerce-form-row">
			<label for="_twitter"><?php echo esc_attr( get_option( 'mvr_dashboard_vendor_twitter_field_label', 'X' ) ); ?></label>
			<input type="text" class="mvr-twitter" style="" name="_twitter" id="_twitter" value="<?php echo esc_html( $vendor_obj->get_twitter() ); ?>">
		</p>

		<p class="woocommerce-form-row">
			<label for="_youtube"><?php echo esc_attr( get_option( 'mvr_dashboard_vendor_youtube_field_label', 'Youtube' ) ); ?></label>
			<input type="text" class="mvr-youtube" style="" name="_youtube" id="_youtube" value="<?php echo esc_html( $vendor_obj->get_youtube() ); ?>">
		</p>

		<p class="woocommerce-form-row">
			<label for="_instagram"><?php echo esc_attr( get_option( 'mvr_dashboard_vendor_instagram_field_label', 'Instagram' ) ); ?></label>
			<input type="text" class="mvr-instagram" style="" name="_instagram" id="_instagram" value="<?php echo esc_html( $vendor_obj->get_instagram() ); ?>">
		</p>

		<p class="woocommerce-form-row">
			<label for="_linkedin"><?php echo esc_attr( get_option( 'mvr_dashboard_vendor_linkedin_field_label', 'Linkedin' ) ); ?></label>
			<input type="text" class="mvr-linkedin" style="" name="_linkedin" id="_linkedin" value="<?php echo esc_html( $vendor_obj->get_linkedin() ); ?>">
		</p>

		<p class="woocommerce-form-row">
			<label for="_pinterest"><?php echo esc_attr( get_option( 'mvr_dashboard_vendor_pinterest_field_label', 'Pinterest' ) ); ?></label>
			<input type="text" class="mvr-pinterest" style="" name="_pinterest" id="_pinterest" value="<?php echo esc_html( $vendor_obj->get_pinterest() ); ?>">
		</p>

		<div class="clear"></div>

		<?php
		/**
		 * Social Media Form
		 *
		 * Hook: mvr_social_media_form
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_social_media_form' );
		?>

		<p>
			<?php wp_nonce_field( 'save_mvr_social_media', '_mvr_nonce' ); ?>
			<button type="submit" class="woocommerce-Button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="save_mvr_payment_details" value="<?php esc_attr_e( 'Save changes', 'multi-vendor-marketplace' ); ?>"><?php esc_html_e( 'Save changes', 'multi-vendor-marketplace' ); ?></button>
			<input type="hidden" name="action" value="save_mvr_social_media" />
			<input type="hidden" name="_vendor_id" value="<?php echo esc_attr( $vendor_obj->get_id() ); ?>" />
		</p>

		<?php
		/**
		 * Social Media Form End
		 *
		 * Hook: mvr_social_media_form_end
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_soial_media_form_end' );
		?>
	</form>
</div>
<?php
/**
 * After Social Media Form
 *
 * Hook: mvr_after_social_media_form
 *
 * @since 1.0.0
 */
do_action( 'mvr_after_social_media_form' );
?>
