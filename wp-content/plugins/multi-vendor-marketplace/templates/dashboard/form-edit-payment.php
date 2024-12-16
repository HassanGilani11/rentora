<?php
/**
 * Payment Dashboard
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/dashboard/form-edit-payment.php.
 *
 * @package Multi Vendor\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Before payment Form
 *
 * Hook: mvr_before_payment_form
 *
 * @since 1.0.0
 */
do_action( 'mvr_before_payment_form' ); ?>

<div class="mvr-payment-form-wrapper">
	<form class="mvr-payment-form edit-form" action="" method="post" 
	<?php
	/**
	 * Payment Form Start
	 *
	 * Hook: mvr_payment_form_start
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_payment_form_tag' );
	?>
	>

		<?php
		/**
		 * Payment Form Start
		 *
		 * Hook: mvr_payment_form_start
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_payment_form_start' );
		?>

		<p class="woocommerce-form-row">
			<label for="_payment_method"><?php echo esc_attr( get_option( 'mvr_dashboard_payment_method_field_label', 'Payment Method' ) ); ?>&nbsp;<span class="required">*</span></label>
			<select id="_payment_method" name="_payment_method">
				<?php foreach ( mvr_payment_method_options() as $key => $value ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $vendor_obj->get_payment_method(), $key, true ); ?>><?php echo esc_html( $value ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p class="woocommerce-form-row">
			<label for="_bank_account_name"><?php echo esc_attr( get_option( 'mvr_dashboard_bank_account_name_field_label', 'Account Name' ) ); ?>&nbsp;<span class="required">*</span></label>
			<input type="text" class="mvr-vendor-bank-payment-field" style="" name="_bank_account_name" id="_bank_account_name" value="<?php echo esc_html( $vendor_obj->get_bank_account_name() ); ?>">
		</p>
		<p class="woocommerce-form-row">
			<label for="_bank_account_number"><?php echo esc_attr( get_option( 'mvr_dashboard_bank_account_number_field_label', 'Account Number' ) ); ?>&nbsp;<span class="required">*</span></label>
			<input type="text" class="mvr-vendor-bank-payment-field" name="_bank_account_number" id="_bank_account_number" value="<?php echo esc_html( $vendor_obj->get_bank_account_number() ); ?>">
		</p>
		<p class="woocommerce-form-row">
			<label for="_bank_account_type"><?php echo esc_attr( get_option( 'mvr_dashboard_bank_account_type_field_label', 'Account Type' ) ); ?>&nbsp;<span class="required">*</span></label>
			<select id="_bank_account_type" name="_bank_account_type" class="mvr-vendor-bank-payment-field">
				<?php foreach ( mvr_bank_account_type_options() as $key => $value ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $vendor_obj->get_bank_account_type(), $key, true ); ?>><?php echo esc_html( $value ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p class="woocommerce-form-row">
		<label for="_bank_name"><?php echo esc_attr( get_option( 'mvr_dashboard_bank_name_field_label', 'Bank Name' ) ); ?>&nbsp;<span class="required">*</span></label>
			<input type="text" class="mvr-vendor-bank-payment-field" name="_bank_name" id="_bank_name" value="<?php echo esc_attr( $vendor_obj->get_bank_name() ); ?>">
			</p>
		<p class="woocommerce-form-row">
			<label for="_iban"><?php echo esc_attr( get_option( 'mvr_dashboard_iban_field_label', 'IBAN' ) ); ?></label>
			<input type="text" class="mvr-vendor-bank-payment-field" name="_iban" id="_iban" value="<?php echo esc_attr( $vendor_obj->get_iban() ); ?>">
		</p>
		<p class="woocommerce-form-row">
			<label for="_swift"><?php echo esc_attr( get_option( 'mvr_dashboard_swift_field_label', 'SWIFT' ) ); ?></label>
			<input type="text" class="mvr-vendor-bank-payment-field" name="_swift" id="_swift" value="<?php echo esc_attr( $vendor_obj->get_swift() ); ?>">
		</p>
		<p class="woocommerce-form-row">
			<label for="_paypal_email"><?php echo esc_attr( get_option( 'mvr_dashboard_paypal_email_field_label', 'PayPal Email' ) ); ?>&nbsp;<span class="required">*</span></label>
			<input type="text" class="mvr-vendor-paypal-payment-field" name="_paypal_email" id="_paypal_email" value="<?php echo esc_attr( $vendor_obj->get_paypal_email() ); ?>"> 
		</p>

		<div class="clear"></div>

		<?php
		/**
		 * Payment Form
		 *
		 * Hook: mvr_payment_form
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_payment_form' );
		?>

		<p>
			<?php wp_nonce_field( 'save-mvr-payment-details-nonce', '_mvr_nonce' ); ?>
			<button type="submit" class="woocommerce-Button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="save_mvr_payment_details" value="<?php esc_attr_e( 'Save changes', 'multi-vendor-marketplace' ); ?>"><?php esc_html_e( 'Save changes', 'multi-vendor-marketplace' ); ?></button>
			<input type="hidden" name="action" value="save_mvr_payment_details" />
		</p>

		<?php
		/**
		 * Payment Form End
		 *
		 * Hook: mvr_payment_form_end
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_payment_form_end' );
		?>
	</form>
</div>
<?php
/**
 * After Payment Form
 *
 * Hook: mvr_after_payment_form
 *
 * @since 1.0.0
 */
do_action( 'mvr_after_payment_form' );
?>
