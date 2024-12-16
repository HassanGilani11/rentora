<?php
/**
 * Register Form
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/form-vendor-register.php.
 *
 * @package Multi Vendor Marketplace\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Before Register Form
 *
 * @since 1.0.0
 */
do_action( 'mvr_before_vendor_register_form' ); ?>

<div class="mvr-vendor-register-form-wrapper">

	<h2><?php esc_html_e( 'Registration Form', 'multi-vendor-marketplace' ); ?></h2>

	<form method="post" class="woocommerce-form mvr-vendor-form-register register" 
	<?php
	/**
	 * Register Form Tags
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_vendor_register_form_tag' );
	?>
	>
		<?php
		/**
		 * Register Form Start
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_vendor_register_form_start' );

		if ( empty( $user_id ) ) :
			?>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="_email"><?php echo esc_attr( get_option( 'mvr_settings_vendor_email_field', 'Email Address' ) ); ?>&nbsp;<span class="required">*</span></label>
				<input type="email" class="woocommerce-Input woocommerce-Input--text input-text mvr-required-field" name="_email" id="_email" autocomplete="email" value="<?php echo esc_attr( $form_fields['_email'] ); ?>" />
			</p>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="_password"><?php echo esc_attr( get_option( 'mvr_settings_vendor_password_field', 'Create Password' ) ); ?>&nbsp;<span class="required">*</span></label>
				<input type="password" class="woocommerce-Input woocommerce-Input--text input-text mvr-required-field" name="_password" id="_password"/>
			</p>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="_confirm_password"><?php echo esc_attr( get_option( 'mvr_settings_vendor_confirm_password_field', 'Confirm Password' ) ); ?>&nbsp;<span class="required">*</span></label>
				<input type="password" class="woocommerce-Input woocommerce-Input--text input-text mvr-required-field" name="_confirm_password" id="_confirm_password"/>
			</p>

		<?php endif; ?>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="_name"><?php echo esc_attr( get_option( 'mvr_settings_vendor_vendor_name_field', 'Vendor Name' ) ); ?>&nbsp;<span class="required">*</span></label>
			<input type="text" name="_name" id="_name" class="woocommerce-Input woocommerce-Input--text input-text mvr-required-field" value="<?php echo esc_attr( $form_fields['_name'] ); ?>">
		</p>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="_shop_name"><?php echo esc_attr( get_option( 'mvr_settings_vendor_store_name_field', 'Store Name' ) ); ?>&nbsp;<span class="required">*</span></label>
			<input type="text" name="_shop_name" id="_shop_name" class="mvr-shop woocommerce-Input woocommerce-Input--text input-text mvr-required-field" value="<?php echo esc_attr( $form_fields['_shop_name'] ); ?>">
			<span class="mvr-description"></span>
		</p>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="_slug"><?php echo esc_attr( get_option( 'mvr_settings_vendor_store_slug_field', 'Store Slug' ) ); ?>&nbsp;<span class="required">*</span></label>
			<input type="text" name="_slug" id="_slug" class="mvr-slug woocommerce-Input woocommerce-Input--text input-text mvr-required-field" value="<?php echo esc_attr( $form_fields['_slug'] ); ?>">
			<span class="mvr-description">
				<span class="mvr-store-url"> 
					<?php echo wp_kses_post( $store_url ); ?>
				</span>
			</span>
		</p>

		<p class="form-row">
			<label for="_terms_and_conditions" class="woocommerce-form__label woocommerce-form__label-for-checkbox">
			<input type="checkbox" id="_terms_and_conditions" name="_terms_and_conditions" class="mvr-tac-cb woocommerce-form__input woocommerce-form__input-checkbox mvr-required-field">
			<span>
			<?php
			$tac_page_id = get_option( 'mvr_settings_vendor_tac_page' );
			$url_label   = esc_html__( 'Terms & Conditions', 'multi-vendor-marketplace' );
			$url         = esc_url( get_permalink( $tac_page_id ) );

			if ( ! empty( $tac_page_id ) ) {
				$tac_url = '<a href="' . $url . '">' . $url_label . '</a>';
			} else {
				$tac_url = $url_label;
			}

			/* translators: %s: Terms & Conditions URL */
			printf( esc_html__( 'I have read and accept the %s to become a Vendor on this site', 'multi-vendor-marketplace' ), wp_kses_post( $tac_url ) );
			?>
			</span>
			</label>
		</p>

		<p class="form-row">
			<label for="_privacy_policy" class="woocommerce-form__label woocommerce-form__label-for-checkbox">
			<input type="checkbox" id="_privacy_policy" name="_privacy_policy" class="mvr-privacy-cb woocommerce-form__input woocommerce-form__input-checkbox mvr-required-field">
			<span>
			<?php
			$pp_page_id = get_option( 'mvr_settings_vendor_privacy_policy_page' );
			$url_label  = esc_html__( 'Privacy Policy', 'multi-vendor-marketplace' );
			$url        = esc_url( get_permalink( $pp_page_id ) );

			if ( ! empty( $pp_page_id ) ) {
				$pp_url = '<a href="' . $url . '">' . $url_label . '</a>';
			} else {
				$pp_url = $url_label;
			}

			/* translators: %s: Privacy policy URL */
			printf( esc_html__( 'I have read and accept the %s to become a Vendor on this site', 'multi-vendor-marketplace' ), wp_kses_post( $pp_url ) );
			?>
			</span>
			</label>
		</p>

		<?php
		/**
		 * Register Form
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_vendor_register_form' );
		?>

		<p class="woocommerce-form-row form-row mvr-form-submit">
			<?php wp_nonce_field( 'mvr_vendor_register', '_mvr_nonce' ); ?>
			<button type="submit" class="mvr-disabled woocommerce-Button woocommerce-button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?> mvr-vendor-register-submit mvr-form-vendor-register__submit" name="register" value="<?php esc_attr_e( 'Register', 'multi-vendor-marketplace' ); ?>"><?php esc_html_e( 'Register', 'multi-vendor-marketplace' ); ?></button>
			<input type="hidden" name="action" value="mvr_vendor_register" />
			<input type="hidden" name="_user_id" value="<?php echo esc_attr( $user_id ); ?>" />
		</p>

		<?php
		/**
		 * Register Form End
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_vendor_register_form_end' );
		?>

	</form>

</div>

<?php
/**
 * After Register Form
 *
 * @since 1.0.0
 */
do_action( 'mvr_after_vendor_register_form' );
?>
