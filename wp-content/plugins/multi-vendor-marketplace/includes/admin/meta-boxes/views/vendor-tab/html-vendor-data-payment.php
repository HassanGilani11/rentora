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
<div id="payment_vendor_data" class="panel woocommerce_options_panel">
	<div class="options_group mvr-vendor-payment">
		<h4><?php esc_html_e( 'Payment Method', 'multi-vendor-marketplace' ); ?></h4>
		<?php
		woocommerce_wp_select(
			array(
				'id'      => '_payment_method',
				'label'   => __( 'Payment Method', 'multi-vendor-marketplace' ),
				'value'   => $vendor_obj->get_payment_method(),
				'options' => mvr_payment_method_options(),
				'class'   => 'mvr-payment-method',
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'          => '_bank_account_name',
				'value'       => $vendor_obj->get_bank_account_name(),
				'label'       => __( 'Account Name', 'multi-vendor-marketplace' ),
				'description' => __( 'Account Name.', 'multi-vendor-marketplace' ),
				'class'       => 'mvr-bank-payment-field',
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'          => '_bank_account_number',
				'value'       => $vendor_obj->get_bank_account_number(),
				'label'       => __( 'Account Number', 'multi-vendor-marketplace' ),
				'description' => __( 'Account Number.', 'multi-vendor-marketplace' ),
				'class'       => 'mvr-bank-payment-field',
			)
		);

		woocommerce_wp_select(
			array(
				'id'      => '_bank_account_type',
				'label'   => __( 'Account Type', 'multi-vendor-marketplace' ),
				'value'   => $vendor_obj->get_bank_account_type(),
				'options' => mvr_bank_account_type_options(),
				'class'   => 'mvr-bank-payment-field',
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'          => '_bank_name',
				'value'       => $vendor_obj->get_bank_name(),
				'label'       => __( 'Bank Name', 'multi-vendor-marketplace' ),
				'description' => __( 'Bank Name.', 'multi-vendor-marketplace' ),
				'class'       => 'mvr-bank-payment-field',
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'          => '_iban',
				'value'       => $vendor_obj->get_iban(),
				'label'       => __( 'IBAN', 'multi-vendor-marketplace' ),
				'description' => __( 'IBAN.', 'multi-vendor-marketplace' ),
				'class'       => 'mvr-bank-payment-field',
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'          => '_swift',
				'value'       => $vendor_obj->get_swift(),
				'label'       => __( 'SWIFT', 'multi-vendor-marketplace' ),
				'description' => __( 'SWIFT.', 'multi-vendor-marketplace' ),
				'class'       => 'mvr-bank-payment-field',
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'          => '_paypal_email',
				'value'       => $vendor_obj->get_paypal_email(),
				'label'       => __( 'PayPal Email', 'multi-vendor-marketplace' ),
				'description' => __( 'PayPal Email.', 'multi-vendor-marketplace' ),
				'class'       => 'mvr-paypal-payment-field',
			)
		);

		/**
		 * Vendor Options Payment.
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_vendor_options_payment' );
		?>
	</div>

	<?php
	/**
	 * Vendor Options Payment data.
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_vendor_options_payment_data' );
	?>
</div>
