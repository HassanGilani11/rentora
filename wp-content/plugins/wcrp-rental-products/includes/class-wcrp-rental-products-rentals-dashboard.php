<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Rentals_Dashboard' ) ) {

	class WCRP_Rental_Products_Rentals_Dashboard {

		public function __construct() {

			add_action( 'admin_menu', array( $this, 'menu_page' ) );

		}

		public function menu_page() {

			$total_current_rentals = WCRP_Rental_Products_Stat_Helpers::total_current_rentals();
			$total_current_rentals = ( $total_current_rentals > 0 ? ' <span class="awaiting-mod">' . number_format_i18n( $total_current_rentals ) . '</span>' : '' ); // Consistent with WooCommerce orders menu

			add_submenu_page(
				'woocommerce',
				__( 'Rentals Dashboard', 'wcrp-rental-products' ),
				__( 'Rentals', 'wcrp-rental-products' ) . $total_current_rentals,
				'manage_woocommerce',
				'wcrp-rental-products-rentals',
				array( $this, 'render' ),
				2 // Under orders menu item
			);

		}

		public function render() {

			// Globals

			global $wpdb; // This looks like it isn't used in this function, but it can get called via the partial includes

			// Tab

			if ( isset( $_GET['tab'] ) ) {

				$tab = sanitize_text_field( $_GET['tab'] );

			} else {

				$tab = get_option( 'wcrp_rental_products_rentals_dashboard_default_tab' );

			}

			// Colors

			$colors = self::colors();

			// Date/time formats (WordPress)

			$rental_date_format = wcrp_rental_products_rental_date_format(); // This looks like it isn't used in this function, but it can get called via the partial includes
			$rental_time_format = wcrp_rental_products_rental_time_format(); // This looks like it isn't used in this function, but it can get called via the partial includes

			// Query args to remove when switching tabs

			$tab_switch_remove_query_args = array(
				'calendar_archive',
				'inventory_date',
				'sub_tab'
			);

			?>

			<div id="wcrp-rental-products-rentals" class="wrap">
				<h1 class="wp-heading-inline"><?php esc_html_e( 'Rentals Dashboard', 'wcrp-rental-products' ); ?></h1>
				<hr class="wp-header-end">
				<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
					<a href="<?php echo esc_url( remove_query_arg( $tab_switch_remove_query_args, add_query_arg( 'tab', 'summary' ) ) ); ?>" class="nav-tab<?php echo ( 'summary' == $tab ? ' nav-tab-active' : '' ); ?>" title="<?php esc_html_e( 'Summary', 'wcrp-rental-products' ); ?>"><span class="dashicons dashicons-editor-ul"></span><?php esc_html_e( 'Summary', 'wcrp-rental-products' ); ?></a>
					<a href="<?php echo esc_url( remove_query_arg( $tab_switch_remove_query_args, add_query_arg( 'tab', 'calendar' ) ) ); ?>" class="nav-tab<?php echo ( 'calendar' == $tab ? ' nav-tab-active' : '' ); ?>" title="<?php esc_html_e( 'Calendar', 'wcrp-rental-products' ); ?>"><span class="dashicons dashicons-calendar-alt"></span><?php esc_html_e( 'Calendar', 'wcrp-rental-products' ); ?></a>
					<a href="<?php echo esc_url( remove_query_arg( $tab_switch_remove_query_args, add_query_arg( 'tab', 'inventory' ) ) ); ?>" class="nav-tab<?php echo ( 'inventory' == $tab ? ' nav-tab-active' : '' ); ?>" title="<?php esc_html_e( 'Inventory', 'wcrp-rental-products' ); ?>"><span class="dashicons dashicons-screenoptions"></span><?php esc_html_e( 'Inventory', 'wcrp-rental-products' ); ?></a>
					<a href="<?php echo esc_url( remove_query_arg( $tab_switch_remove_query_args, add_query_arg( 'tab', 'tools' ) ) ); ?>" class="nav-tab<?php echo ( 'tools' == $tab ? ' nav-tab-active' : '' ); ?>" title="<?php esc_html_e( 'Tools', 'wcrp-rental-products' ); ?>"><span class="dashicons dashicons-admin-tools"></span><?php esc_html_e( 'Tools', 'wcrp-rental-products' ); ?></a>
					<a href="<?php echo esc_url( get_admin_url() . ( wcrp_rental_products_hpos_enabled() ? 'admin.php?page=wc-orders&action=-1&wcrp_rental_products_rentals_filter=inc' : 'edit.php?s&post_type=shop_order&action=-1&wcrp_rental_products_rentals_filter=inc' ) ); ?>" class="nav-tab" target="_blank" title="<?php esc_html_e( 'Orders (opens in a new tab)', 'wcrp-rental-products' ); ?>"><span class="dashicons dashicons-cart"></span><?php esc_html_e( 'Orders*', 'wcrp-rental-products' ); ?></a>
					<a href="<?php echo esc_url( get_admin_url() . 'edit.php?s&post_status=all&post_type=product&action=-1&wcrp_rental_products_rentals_filter=all' ); ?>" class="nav-tab" target="_blank" title="<?php esc_html_e( 'Products (opens in a new tab)', 'wcrp-rental-products' ); ?>"><span class="dashicons dashicons-archive"></span><?php esc_html_e( 'Products*', 'wcrp-rental-products' ); ?></a>
					<a href="<?php echo esc_url( get_admin_url() . 'admin.php?page=wc-settings&tab=products&section=wcrp-rental-products' ); ?>" class="nav-tab" target="_blank" title="<?php esc_html_e( 'Settings (opens in a new tab)', 'wcrp-rental-products' ); ?>"><span class="dashicons dashicons-admin-settings"></span><?php esc_html_e( 'Settings*', 'wcrp-rental-products' ); ?></a>
					<?php if ( 'summary' == $tab ) { ?>
						<div class="wcrp-rental-products-rentals-tab-info">
							<?php esc_html_e( 'Summary of latest and flagged rental orders.', 'wcrp-rental-products' ); ?>
						</div>
					<?php } elseif ( 'calendar' == $tab ) { ?>
						<div id="wcrp-rental-products-rentals-color-key" class="wcrp-rental-products-rentals-tab-info">
							<span><?php esc_html_e( 'Calendar of rentals. Color key (hover for info):', 'wcrp-rental-products' ); ?></span>
							<div style="background-color: <?php echo esc_html( $colors['blue_dark'] ); ?>" title="<?php esc_html_e( 'Rentals that span the current date/time except where already returned or due to be returned.', 'wcrp-rental-products' ); ?>"><?php esc_html_e( 'Current', 'wcrp-rental-products' ); ?></div>
							<div style="background-color: <?php echo esc_html( $colors['blue'] ); ?>" title="<?php esc_html_e( 'Rentals that occur on future dates/times.', 'wcrp-rental-products' ); ?>"><?php esc_html_e( 'Future', 'wcrp-rental-products' ); ?></div>
							<div style="background-color: <?php echo esc_html( $colors['green'] ); ?>" title="<?php esc_html_e( 'Rentals that have been returned.', 'wcrp-rental-products' ); ?>"><?php esc_html_e( 'Returned', 'wcrp-rental-products' ); ?></div>
							<div style="background-color: <?php echo esc_html( $colors['red'] ); ?>" title="<?php esc_html_e( 'Rentals that have not been returned and are due.', 'wcrp-rental-products' ); ?>"><?php esc_html_e( 'Not returned', 'wcrp-rental-products' ); ?></div>
						</div>
					<?php } elseif ( 'inventory' == $tab ) { ?>
						<div id="wcrp-rental-products-rentals-inventory-info">
							<p><strong><?php esc_html_e( 'The inventory shows the rental stock totals of all published rental products.', 'wcrp-rental-products' ); ?></strong></p>
							<p><?php esc_html_e( 'There are 2 views available, date view and live view.', 'wcrp-rental-products' ); ?></p>
							<p><strong><?php esc_html_e( 'Date view:', 'wcrp-rental-products' ); ?></strong></p>
							<p><?php esc_html_e( 'Shows estimated rental stock totals for the date set. Rentals are considered in when the rental dates (including any return days thresholds) have past, regardless of if marked as returned or not.', 'wcrp-rental-products' ); ?></p>
							<p><?php esc_html_e( 'As the inventory can be used to view rental stock totals for dates in the future the rental stock totals assume that rentals will have have been returned by the expected return dates (including any return days thresholds).', 'wcrp-rental-products' ); ?></p>
							<p><strong><?php esc_html_e( 'Live view:', 'wcrp-rental-products' ); ?></strong></p>
							<p><?php esc_html_e( 'Shows rental stock totals for the current date and time. Rentals are considered out unless marked as returned or cancelled.', 'wcrp-rental-products' ); ?></p>
							<p><strong><?php esc_html_e( 'Why do I have negative rental stock totals?', 'wcrp-rental-products' ); ?></strong></p>
							<p><?php esc_html_e( 'This can occur temporarily when the product has had rental stock reduced and more than the new level was already reserved. Once those reserved rentals have been returned the negative rental stocks levels will rectify.', 'wcrp-rental-products' ); ?></p>
							<p><strong><?php esc_html_e( 'Why do customers sometimes see different availability to the inventory?', 'wcrp-rental-products' ); ?></strong></p>
							<p><?php esc_html_e( 'There are a variety of factors which determine the availability of a rental product, this can mean that the totals you see in the inventory do not match the availability a customer sees when choosing a rental on specific dates/quantities, such as accounting for the return days thresholds of rentals prior to the dates they have selected.', 'wcrp-rental-products' ); ?></p>
							<p>
								<?php
								// translators: %s: settings link
								echo wp_kses_post( sprintf( __( 'In addition to the above, availability may also differ from the inventory due to immediate rental stock replenishment in %s.', 'wcrp-rental-products' ), '<a href="' . esc_url( get_admin_url() . 'admin.php?page=wc-settings&tab=products&section=wcrp-rental-products' ) . '" target="_blank">' . esc_html__( 'settings', 'wcrp-rental-products' ) . '</a>' ) );
								?>
							</p>
						</div>
						<div class="wcrp-rental-products-rentals-tab-info">
							<?php
							// translators: %s: inventory info link
							echo wp_kses_post( sprintf( __( 'Inventory of all published rental products. For more details, %s.', 'wcrp-rental-products' ), '<a href="#TB_inline?&width=600&height=550&inlineId=wcrp-rental-products-rentals-inventory-info" class="thickbox" title="' . esc_html__( 'Inventory', 'wcrp-rental-products' ) . '">' . esc_html__( 'click here', 'wcrp-rental-products' ) . '</a>' ) );
							?>
						</div>
					<?php } elseif ( 'tools' == $tab ) { ?>
						<div class="wcrp-rental-products-rentals-tab-info">
							<?php esc_html_e( 'Various tools which can be used for rental product management purposes.', 'wcrp-rental-products' ); ?>
						</div>
					<?php } ?>
				</nav>
				<?php
				if ( 'summary' == $tab ) {
					require_once __DIR__ . '/partials/rentals-dashboard/summary.php';
				} elseif ( 'calendar' == $tab ) {
					require_once __DIR__ . '/partials/rentals-dashboard/calendar.php';
				} elseif ( 'inventory' == $tab ) {
					require_once __DIR__ . '/partials/rentals-dashboard/inventory.php';
				} elseif ( 'tools' == $tab ) {
					require_once __DIR__ . '/partials/rentals-dashboard/tools.php';
				}
				?>
				<div class="wcrp-rental-products-rentals-footer">
					<div><a href="#" class="button button-small"><?php esc_html_e( 'Back to top', 'wcrp-rental-products' ); ?></a></div>
					<div>
						<?php
						// translators: %s version number
						echo sprintf ( esc_html__( 'Rental Products v%s', 'wcrp-rental-products' ), esc_html( WCRP_RENTAL_PRODUCTS_VERSION ) );
						?>
					</div>
					<div><a href="#" class="button button-small"><?php esc_html_e( 'Back to top', 'wcrp-rental-products' ); ?></a></div>
				</div>
				<?php
				if ( in_array( 'debug_log_show', wcrp_rental_products_advanced_configuration() ) ) {
					echo '<p><strong>' . esc_html__( 'Debug log (being displayed due to an advanced configuration setting):', 'wcrp-rental-products' ) . '</strong></p>';
					$debug_log = get_option( 'wcrp_rental_products_debug_log' );
					if ( !empty( $debug_log ) ) {
						echo '<small><pre>';
						echo esc_html( htmlentities( print_r( $debug_log, true ), ENT_COMPAT ) );
						echo '</pre></small>';
					} else {
						echo '<p><small>' . esc_html__( 'No debug logs yet.', 'wcrp-rental-products' ) . '</small></p>';
					}
				}
				?>
			</div>

			<?php

		}

		public static function colors() {

			return array(
				'blue'		=> '#2271b1',
				'blue_dark'	=> '#103656',
				'green'		=> '#00a32a',
				'red'		=> '#d63638',
			);

		}

		public static function order_customer_name( $order ) {

			$customer_name = '';

			if ( !empty( $order ) ) {

				$customer_first_name = $order->get_billing_first_name();
				$customer_last_name = $order->get_billing_last_name();
				$customer_company = $order->get_billing_company();

				if ( !empty( $customer_first_name ) || !empty( $customer_last_name ) ) {

					if ( !empty( $customer_first_name ) ) {

						$customer_name .= $customer_first_name;

					}

					if ( !empty( $customer_last_name ) ) {

						$customer_name .= ' ' . $customer_last_name;

					}

				} elseif ( !empty( $customer_company ) ) {

					$customer_name .= $customer_company;

				}

			}

			return $customer_name;

		}

	}

}
