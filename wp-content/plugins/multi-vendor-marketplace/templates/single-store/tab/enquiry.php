<?php
/**
 * Stores enquiry Content.
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/single-store/enquiry.php.
 *
 * @package Multi Vendor Marketplace\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit accessed directly.
}

?>
<div class="mvr-single-store-enquiry-wrapper">
	<h2><?php esc_html_e( 'Enquiry', 'multi-vendor-marketplace' ); ?></h2>
	<form id="mvr-enquiry-form" method="post">
		<div class="mvr-email-field">
			<?php if ( empty( $user_email ) ) : ?>
				<label for="_email"><?php esc_html_e( 'Email Address', 'multi-vendor-marketplace' ); ?></label>
				<input id="_email" type="email" name="_email"/>
			<?php else : ?>
				<input id="_email" type="hidden" name="_email" value="<?php echo esc_attr( $user_email ); ?>"/>
			<?php endif; ?>
		</div>
		<div class="mvr-name-field">
			<?php if ( empty( $user_name ) ) : ?>
				<label for="_name"><?php esc_html_e( 'Name', 'multi-vendor-marketplace' ); ?></label>
				<input id="_name" type="text" name="_name"/>
			<?php else : ?>
				<input id="_name" type="hidden" name="_name" value="<?php echo esc_attr( $user_name ); ?>"/>
			<?php endif; ?>
		</div>
		<div class="mvr-message-field">
			<label for="_message"><?php esc_html_e( 'Your Enquiry', 'multi-vendor-marketplace' ); ?></label>
			<textarea id="_message" name="_message"></textarea>
		</div>
		<div>
			<input type="hidden" name="_vendor_id" value="<?php echo esc_attr( $vendor_obj->get_id() ); ?>" />
			<input type="hidden" name="_source_id" value="<?php echo esc_attr( $source_id ); ?>" />
			<input type="hidden" name="_form_type" value="<?php echo esc_attr( $form_type ); ?>" />
			<input type="hidden" name="_user_id" value="<?php echo esc_attr( $user_id ); ?>" />
			<input type="hidden" name="action" value="mvr_vendor_enquiry" />
			<?php wp_nonce_field( 'mvr_vendor_enquiry', '_mvr_nonce' ); ?>
			<button type="submit" class="woocommerce-Button button mvr-vendor-enquiry-submit" name="mvr_vendor_enquiry"><?php esc_html_e( 'Submit', 'multi-vendor-marketplace' ); ?></button>
		</div>
	</form>
</div>
