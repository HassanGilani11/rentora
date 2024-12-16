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
 * Before Withdraw amount.
 *
 * @since 1.0.0
 */
do_action( 'mvr_before_withdraw_amount' );
?>
<div class="mvr-withdraw-amount-wrapper">
	<div class="mvr-vendor-amount">
		<label><?php echo esc_attr( get_option( 'mvr_dashboard_withdraw_total_amount_label', 'Total Amount' ) ); ?></label>
		<?php echo wp_kses_post( wc_price( $vendor_obj->get_total_amount() ) ); ?>
	</div>
	<div class="mvr-vendor-available-withdraw-amount">
		<label><?php echo esc_attr( get_option( 'mvr_dashboard_withdraw_available_amount_label', 'Available Amount' ) ); ?></label>
		<?php echo wp_kses_post( wc_price( $vendor_obj->get_amount() ) ); ?>
	</div>
	<div class="mvr-vendor-locked-amount">
		<label><?php echo esc_attr( get_option( 'mvr_dashboard_withdraw_locked_amount_label', 'Locked Amount' ) ); ?></label>
		<?php echo wp_kses_post( wc_price( $vendor_obj->get_locked_amount() ) ); ?>
	</div>
	<?php
		$withdraw_settings = $vendor_obj->get_withdraw_settings();

	if ( 'yes' === $withdraw_settings['enable_charge'] ) :
		?>
			<div class="mvr-vendor-withdraw-charge">
				<label><?php echo esc_attr( get_option( 'mvr_dashboard_withdraw_charge_label', 'Withdrawal Charge' ) ); ?></label>
			<?php
			$withdraw_charge = ( '2' === $withdraw_settings['charge_type'] ) ? $withdraw_settings['charge_value'] . '%' : wc_price( $withdraw_settings['charge_value'] );

			echo esc_attr( $withdraw_charge );
			?>
			</div>
	<?php endif; ?>
</div>
<?php
/**
 * After Withdraw amount.
 *
 * @since 1.0.0
 */
do_action( 'mvr_after_withdraw_amount' );

