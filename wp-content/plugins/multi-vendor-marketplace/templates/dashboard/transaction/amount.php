<?php
/**
 * Transaction Amount
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/dashboard/transaction/amount.php.
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
do_action( 'mvr_before_transaction_amount' );
?>
<div class="mvr-withdraw-amount-wrapper">
	<div class="mvr-transaction-total-amount">
		<label><?php echo esc_attr( get_option( 'mvr_dashboard_transaction_amount_label', 'Amount' ) ); ?></label>
		<?php echo wp_kses_post( wc_price( $vendor_obj->get_total_amount() ) ); ?>
	</div>
	<div class="mvr-transaction-completed-amount">
		<label><?php echo esc_attr( get_option( 'mvr_dashboard_transaction_completed_amount_label', 'Completed Amount' ) ); ?></label>
		<?php echo wp_kses_post( wc_price( $vendor_obj->get_amount() ) ); ?>
	</div>
	<div class="mvr-transaction-processing-amount">
		<label><?php echo esc_attr( get_option( 'mvr_dashboard_transaction_processing_amount_label', 'Processing Amount' ) ); ?></label>
		<?php echo wp_kses_post( wc_price( $vendor_obj->get_locked_amount() ) ); ?>
	</div>
	<div class="mvr-admin-commission">
		<label><?php echo esc_attr( get_option( 'mvr_dashboard_transaction_admin_commission_label', 'Admin Commission' ) ); ?></label>
		<?php echo wp_kses_post( $vendor_obj->display_admin_commission() ); ?>
	</div>
</div>
<?php
/**
 * After Withdraw amount.
 *
 * @since 1.0.0
 */
do_action( 'mvr_before_transaction_amount' );

