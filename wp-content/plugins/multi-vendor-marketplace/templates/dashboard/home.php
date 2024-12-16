<?php
/**
 * Home Page
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/dashboard/home.php.
 *
 * @package Multi Vendor\Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Before Dashboard Home.
 *
 * @since 1.0.0
 */
do_action( 'mvr_before_dashboard_home' );
?>
<div class="mvr-dashboard-stats">
	<div class="mvr-dashboard-stats-container mvr-cross-sale-container">
		<div class="mvr-dashboard-stats-container-data mvr-cross-sale-container-data">
			<strong> <?php echo wp_kses_post( wc_price( $overview['cross_sale'] ) ); ?> </strong>

			<label> <?php esc_html_e( 'Gross sales this month', 'multi-vendor-marketplace' ); ?></label>
		</div>
		<div class="mvr-dashboard-stats-container-icon mvr-cross-sale-container-icon"> 
			<span class="dashicons dashicons-money-alt"></span>
		</div>
	</div>

	<div class="mvr-dashboard-stats-container mvr-admin-commission-container">
		<div class="mvr-dashboard-stats-container-data mvr-admin-commission-container-data">
			<strong> <?php echo wp_kses_post( wc_price( $overview['admin_commission'] ) ); ?></strong>

			<label> <?php esc_html_e( 'Admin Commission this month', 'multi-vendor-marketplace' ); ?></label>
		</div>
		<div class="mvr-dashboard-stats-container-icon mvr-admin-commission-container-icon"> 
			<span class="dashicons dashicons-money"></span>
		</div>
	</div>

	<div class="mvr-dashboard-stats-container mvr-earning-container">
		<div class="mvr-dashboard-stats-container-data mvr-earning-container-data">
			<strong> <?php echo wp_kses_post( wc_price( $overview['vendor_earning'] ) ); ?> </strong>

			<label> <?php esc_html_e( 'Earnings of this month', 'multi-vendor-marketplace' ); ?></label>
		</div>
		<div class="mvr-dashboard-stats-container-icon mvr-earning-container-icon"> 
			<span class="dashicons dashicons-money-alt"></span>
		</div>
	</div>

	<div class="mvr-dashboard-stats-container mvr-sold-items-container">
		<div class="mvr-dashboard-stats-container-data mvr-sold-items-container-data">
			<strong>
				<?php
				/* translators: %s: Item Count */
				printf( esc_html__( '%s items', 'multi-vendor-marketplace' ), esc_attr( $overview['item_count'] ) );
				?>
			</strong>

			<label> <?php esc_html_e( 'sold in this month', 'multi-vendor-marketplace' ); ?></label>
		</div>
		<div class="mvr-dashboard-stats-container-icon mvr-sold-items-container-icon"> 
			<span class="dashicons dashicons-products"></span>
		</div>
	</div>

	<div class="mvr-dashboard-stats-container mvr-orders-container">
		<div class="mvr-dashboard-stats-container-data mvr-orders-container-data">
			<strong>
				<?php
				/* translators: %s: Order Count */
				printf( esc_html__( '%s orders', 'multi-vendor-marketplace' ), esc_attr( $overview['order_count'] ) );
				?>
			</strong>

			<label> <?php esc_html_e( 'received in this month', 'multi-vendor-marketplace' ); ?></label>
		</div>
		<div class="mvr-dashboard-stats-container-icon mvr-orders-container-icon"> 
			<span class="dashicons dashicons-cart"></span>
		</div>
	</div>
</div>
<?php
/**
 * After Dashboard Home.
 *
 * @since 1.0.0
 */
do_action( 'mvr_after_dashboard_home' );

