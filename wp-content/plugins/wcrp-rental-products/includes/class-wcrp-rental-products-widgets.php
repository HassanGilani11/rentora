<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Widgets' ) ) {

	class WCRP_Rental_Products_Widgets {

		public function __construct() {

			add_action( 'wp_dashboard_setup', array( $this, 'dashboard' ) );

		}

		public function dashboard() {

			wp_add_dashboard_widget(
				'wcrp-rental-products-dashboard-widget-rentals-summary',
				__( 'Rentals Summary', 'wcrp-rental-products' ),
				array( $this, 'dashboard_widget_rentals_summary' )
			);

		}

		public function dashboard_widget_rentals_summary() {

			?>

			<table class="widefat striped">
				<tbody>
					<tr>
						<td>
							<?php esc_html_e( 'Rentals current', 'wcrp-rental-products' ); ?>
							<span class="dashicons dashicons-editor-help" title="<?php echo esc_attr( WCRP_Rental_Products_Stat_Helpers::total_current_rentals_description() ); ?>"></span>
						</td>
						<td>
							<strong><?php echo esc_html( number_format_i18n( WCRP_Rental_Products_Stat_Helpers::total_current_rentals() ) ); ?></strong>
						</td>
					</tr>
					<tr>
						<td>
							<?php esc_html_e( 'Rental orders today', 'wcrp-rental-products' ); ?>
							<span class="dashicons dashicons-editor-help" title="<?php echo esc_attr( WCRP_Rental_Products_Stat_Helpers::total_rental_orders_description( 1 ) ); ?>"></span>
						</td>
						<td>
							<strong><?php echo esc_html( number_format_i18n( WCRP_Rental_Products_Stat_Helpers::total_rental_orders( 1 ) ) ); ?></strong>
						</td>
					</tr>
					<tr>
						<td>
							<?php esc_html_e( 'Rental orders last 7 days', 'wcrp-rental-products' ); ?>
							<span class="dashicons dashicons-editor-help" title="<?php echo esc_attr( WCRP_Rental_Products_Stat_Helpers::total_rental_orders_description( 7 ) ); ?>"></span>
						</td>
						<td>
							<strong><?php echo esc_html( number_format_i18n( WCRP_Rental_Products_Stat_Helpers::total_rental_orders( 7 ) ) ); ?></strong>
						</td>
					</tr>
					<tr>
						<td>
							<?php esc_html_e( 'Rental orders last 30 days', 'wcrp-rental-products' ); ?>
							<span class="dashicons dashicons-editor-help" title="<?php echo esc_attr( WCRP_Rental_Products_Stat_Helpers::total_rental_orders_description( 30 ) ); ?>"></span>
						</td>
						<td>
							<strong><?php echo esc_html( number_format_i18n( WCRP_Rental_Products_Stat_Helpers::total_rental_orders( 30 ) ) ); ?></strong>
						</td>
					</tr>
					<tr>
						<td>
							<?php esc_html_e( 'Rental products', 'wcrp-rental-products' ); ?>
							<span class="dashicons dashicons-editor-help" title="<?php echo esc_attr( WCRP_Rental_Products_Stat_Helpers::total_rental_products_description() ); ?>"></span>
						</td>
						<td>
							<strong><?php echo esc_html( number_format_i18n( WCRP_Rental_Products_Stat_Helpers::total_rental_products() ) ); ?></strong>
						</td>
					</tr>
				</tbody>
			</table>

			<ul class="subsubsub">
				<li><a href="<?php echo esc_url( get_admin_url() . 'admin.php?page=wcrp-rental-products-rentals' ); ?>"><?php esc_html_e( 'Dashboard', 'wcrp-rental-products' ); ?></a> <?php esc_html_e( '|', 'wcrp-rental-products' ); ?></li>
				<li><a href="<?php echo esc_url( get_admin_url() . 'admin.php?page=wcrp-rental-products-rentals&tab=summary' ); ?>"><?php esc_html_e( 'Summary', 'wcrp-rental-products' ); ?></a> <?php esc_html_e( '|', 'wcrp-rental-products' ); ?></li>
				<li><a href="<?php echo esc_url( get_admin_url() . 'admin.php?page=wcrp-rental-products-rentals&tab=calendar' ); ?>"><?php esc_html_e( 'Calendar', 'wcrp-rental-products' ); ?></a> <?php esc_html_e( '|', 'wcrp-rental-products' ); ?></li>
				<li><a href="<?php echo esc_url( get_admin_url() . 'admin.php?page=wcrp-rental-products-rentals&tab=inventory' ); ?>"><?php esc_html_e( 'Inventory', 'wcrp-rental-products' ); ?></a> <?php esc_html_e( '|', 'wcrp-rental-products' ); ?></li>
				<li><a href="<?php echo esc_url( get_admin_url() . 'admin.php?page=wcrp-rental-products-rentals&tab=tools' ); ?>"><?php esc_html_e( 'Tools', 'wcrp-rental-products' ); ?></a> <?php esc_html_e( '|', 'wcrp-rental-products' ); ?></li>
				<li><a href="<?php echo esc_url( get_admin_url() . 'admin.php?page=wc-settings&tab=products&section=wcrp-rental-products' ); ?>"><?php esc_html_e( 'Settings', 'wcrp-rental-products' ); ?></a></li>
			</ul>

			<?php

		}

	}

}
