<?php
/**
 * Withdraw
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/dashboard/withdraw.php.
 *
 * @package Multi Vendor Marketplace\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Before Withdraw Form.
 *
 * @since 1.0.0
 */
do_action( 'mvr_before_withdraw_form' );
?>
<div class="mvr-withdraw-form-wrapper">
	<form class="mvr-withdraw-form edit-form" method="post">

		<?php
		/**
		 * Withdraw Form Start
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_withdraw_form_start' );
		?>

		<p class="woocommerce-form-row">
			<label for="_withdraw_amount"><?php echo esc_attr( get_option( 'mvr_dashboard_withdraw_amount_field_label', 'Enter the amount you wish to Withdraw' ) ); ?>&nbsp;<span class="required">*</span></label>
			<input type="text" id="_withdraw_amount" name="_withdraw_amount" class="mvr-withdraw-amount mvr-input-price">
			<span class="mvr-amount-desc"></span>
		</p>

		<div class="clear"></div>

		<?php
		/**
		 * Withdraw Form
		 *
		 * Hook: mvr_withdraw_form
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_withdraw_form' );
		?>

		<p>
			<?php wp_nonce_field( 'mvr_withdraw_request', '_mvr_nonce' ); ?>
			<button type="submit" class="mvr-withdraw-req-submit woocommerce-Button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="save_mvr_payment_details" value="<?php echo esc_attr( get_option( 'mvr_dashboard_withdraw_submit_btn_label', 'Submit' ) ); ?>" <?php echo ( ! $vendor_obj->cleared_payment_tab() ) ? 'disabled' : ''; ?>><?php esc_html_e( 'Submit', 'multi-vendor-marketplace' ); ?></button>
			<input type="hidden" name="action" value="mvr_withdraw_request" />
			<input type="hidden" name="_vendor_id" value="<?php echo esc_attr( $vendor_obj->get_id() ); ?>" />
		</p>

		<?php
		/**
		 * Payment Form End
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_withdraw_form_end' );
		?>
	</form>
</div>
<?php
/**
 * After Withdraw Form.
 *
 * @since 1.0.0
 */
do_action( 'mvr_after_withdraw_form' );

