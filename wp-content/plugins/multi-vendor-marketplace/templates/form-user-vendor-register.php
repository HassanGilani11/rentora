<?php
/**
 * Form User vendor register.
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/form-user-vendor-register.php.
 *
 * @package Multi Vendor Marketplace\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="mvr-become-vendor-wrapper">
	<div class="mvr-become-vendor-button-wrapper">
		<button class="mvr-become-vendor-btn"><?php esc_html_e( 'Become a Vendor', 'multi-vendor-marketplace' ); ?></button>
	</div>

	<div class="mvr-become-vendor-form-wrapper">
		<?php
		/**
		 * Before User Register Form
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_before_user_vendor_register_form' );
		?>
		<form id="mvr_become_vendor" method="post">
			<p class="woocommerce-form-row">
				<label for="_name"><?php echo esc_attr( get_option( 'mvr_settings_vendor_vendor_name_field', 'Vendor Name' ) ); ?>&nbsp;<span class="required">*</span></label>
				<input type="text" id="_name" name="_name" class="woocommerce-Input woocommerce-Input--text input-text mvr-required-field" value="<?php echo esc_attr( $form_fields['_name'] ); ?>">
			</p>
			<p class="woocommerce-form-row">
				<label for="_shop_name"><?php echo esc_attr( get_option( 'mvr_settings_vendor_store_name_field', 'Store Name' ) ); ?>&nbsp;<span class="required">*</span></label>
				<input type="text" name="_shop_name" id="_shop_name" class="mvr-shop woocommerce-Input woocommerce-Input--text input-text mvr-required-field" value="<?php echo esc_attr( $form_fields['_shop_name'] ); ?>">
				<span class="mvr-description"></span>
			</p>
			<p class="woocommerce-form-row">
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
			<p>
				<?php wp_nonce_field( 'mvr_become_vendor', '_mvr_nonce' ); ?>
				<button type="submit" class="mvr-disabled woocommerce-Button button mvr-become-vendor-submit mvr-vendor-register-submit" name="mvr_become_vendor"><?php esc_html_e( 'Register', 'multi-vendor-marketplace' ); ?></button>
				<input type="hidden" name="action" value="mvr_become_vendor" />
			</p>
		</form>
		<?php
		/**
		 * After User Register Form
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_after_user_vendor_register_form' );
		?>
	</div>
</div>
