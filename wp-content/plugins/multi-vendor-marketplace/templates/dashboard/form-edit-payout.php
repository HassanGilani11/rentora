<?php
/**
 * Payout Dashboard
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/dashboard/form-edit-payout.php.
 *
 * @package Multi Vendor\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Before payment Form
 *
 * @since 1.0.0
 */
do_action( 'mvr_before_payout_form' );
?>
<div class="mvr-payout-form-wrapper">
	<form class="mvr-payout-form edit-form" action="" method="post" 
	<?php
	/**
	 * Payout Form tag
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_payout_form_tag' );
	?>
	>

		<?php
		/**
		 * Payment Form Start
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_payout_form_start' );
		?>

		<p class="woocommerce-form-row">
			<label for="_payout_type"><?php echo esc_attr( get_option( 'mvr_dashboard_payout_type_field_label', 'Payout Type' ) ); ?>&nbsp;<span class="required">*</span></label>
			<select id="_payout_type" name="_payout_type" class="mvr-payout-type">
				<?php foreach ( mvr_payout_type_options() as $key => $value ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $vendor_obj->get_payout_type(), $key, true ); ?>><?php echo esc_html( $value ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
	<?php if ( mvr_check_is_array( mvr_prepare_payout_schedule_options() ) ) : ?>
		<p class="woocommerce-form-row">
			<label for="_payout_schedule"><?php echo esc_attr( get_option( 'mvr_dashboard_payout_schedule_field_label', 'Payout Schedule' ) ); ?>&nbsp;<span class="required">*</span></label>
			<select id="_payout_schedule" name="_payout_schedule" class="mvr-payout-schedule">
				<?php foreach ( mvr_prepare_payout_schedule_options() as $key => $value ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $vendor_obj->get_payout_schedule(), $key, true ); ?>><?php echo esc_html( $value ); ?></option>
				<?php endforeach; ?>
			</select>
			<?php foreach ( mvr_prepare_payout_schedule_options() as $key => $value ) : ?>
				<span class="mvr-description mvr-description-<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( mvr_get_automatic_schedule_description( $key ) ); ?></span>
			<?php endforeach; ?>
		</p>
	<?php endif; ?>

		<div class="clear"></div>

		<?php
		/**
		 * Payment Form
		 *
		 * Hook: mvr_payment_form
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_payout_form' );
		?>

		<p>
			<?php wp_nonce_field( 'save_mvr_payout_details', '_mvr_nonce' ); ?>
			<button type="submit" class="woocommerce-Button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="save_mvr_payment_details" value="<?php esc_attr_e( 'Save changes', 'multi-vendor-marketplace' ); ?>"><?php esc_html_e( 'Save changes', 'multi-vendor-marketplace' ); ?></button>
			<input type="hidden" name="action" value="save_mvr_payout_details" />
			<input type="hidden" name="_vendor_id" value="<?php echo esc_attr( $vendor_obj->get_id() ); ?>" />
		</p>

		<?php
		/**
		 * Payment Form End
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_payout_form_end' );
		?>
	</form>
</div>
<?php
/**
 * After Payment Form
 *
 * @since 1.0.0
 */
do_action( 'mvr_after_payout_form' );
?>
