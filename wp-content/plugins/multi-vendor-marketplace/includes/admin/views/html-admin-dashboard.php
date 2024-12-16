<?php
/**
 * Vendor Admin Dashboard HTML
 *
 * @package  Multi-Vendor\Admin Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="mvr-admin-dashboard-stats">
	<div class="mvr-admin-dashboard-stats-container mvr-cross-sale-container">
		<div class="mvr-admin-dashboard-stats-container-data mvr-cross-sale-container-data">
			<strong> <?php echo wp_kses_post( wc_price( $data['total'] ) ); ?> </strong>
			<label> <?php esc_html_e( 'Gross Sales in the Last 7 Days', 'multi-vendor-marketplace' ); ?></label>
		</div>
				<div class="mvr-admin-dashboard-stats-container-icon mvr-cross-sale-container-icon">
					<span class="dashicons dashicons-money-alt"></span>
				</div>
	</div>

	<div class="mvr-admin-dashboard-stats-container mvr-admin-commission-container">
		<div class="mvr-admin-dashboard-stats-container-data mvr-admin-commission-container-data">
			<strong> <?php echo wp_kses_post( wc_price( $data['admin_commission'] ) ); ?></strong>
			<label> <?php esc_html_e( 'Admin Commission in the Last 7 Days', 'multi-vendor-marketplace' ); ?></label>
		</div>
			<div class="mvr-admin-dashboard-stats-container-icon mvr-admin-commission-container-icon">
				<span class="dashicons dashicons-money"></span>
			</div>
	</div>

	<div class="mvr-admin-dashboard-stats-container mvr-sold-items-container">
		<div class="mvr-admin-dashboard-stats-container-data mvr-sold-items-container-data">
			<strong> 
			<?php
				/* translators: %s: Item Count */
				printf( esc_html__( '%s items', 'multi-vendor-marketplace' ), esc_attr( $data['item_count'] ) );
			?>
			</strong>
			<label> <?php esc_html_e( 'Sold in the Last 7 Days', 'multi-vendor-marketplace' ); ?></label>
		</div>
			<div class="mvr-admin-dashboard-stats-container-icon mvr-sold-items-container-icon">
				<span class="dashicons dashicons-products"></span>
			</div>
	</div>

	<div class="mvr-admin-dashboard-stats-container mvr-order-container">
		<div class="mvr-admin-dashboard-stats-container-data mvr-order-container-data">
			<strong>
			<?php
				/* translators: %s: Order Count */
				printf( esc_html__( '%s orders', 'multi-vendor-marketplace' ), esc_attr( $data['order_count'] ) );
			?>
			</strong>
			<label> <?php esc_html_e( 'Received in the Last 7 Days', 'multi-vendor-marketplace' ); ?></label>
		</div>
			<div class="mvr-admin-dashboard-stats-container-icon mvr-order-container-icon">
				<span class="dashicons dashicons-cart"></span>
			</div>
	</div>

	<div class="mvr-admin-dashboard-stats-container mvr-vendor-earning-container">
		<div class="mvr-admin-dashboard-stats-container-data mvr-vendor-earning-container-data">
			<strong> <?php echo wp_kses_post( wc_price( $data['vendor_earnings'] ) ); ?> </strong>
			<label> <?php esc_html_e( 'Vendor Earnings in the Last 7 Days', 'multi-vendor-marketplace' ); ?></label>
		</div>
			<div class="mvr-admin-dashboard-stats-container-icon mvr-vendor-earnings-container-icon">
				<span class="dashicons dashicons-money-alt"></span>
			</div>
	</div>
</div>
