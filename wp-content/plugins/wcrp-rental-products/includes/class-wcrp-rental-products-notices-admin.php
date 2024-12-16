<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Notices_Admin' ) ) {

	class WCRP_Rental_Products_Notices_Admin {

		// This class is for general notices, there may be more functionality specific notices in other classes

		public function __construct() {

			add_action( 'admin_notices', array( $this, 'activation_notice' ) );
			add_action( 'admin_head', array( $this, 'activation_notice_dismiss' ) );
			add_action( 'admin_notices', array( $this, 'deposits_partial_payments_for_woocommerce_settings_notice' ) );
			add_action( 'admin_notices', array( $this, 'woocommerce_import_export_notice' ) );

		}

		public function activation_notice() {

			if ( !empty( get_transient( 'wcrp_rental_products_activation_notice' ) ) ) {

				if ( current_user_can( 'edit_plugins' ) ) {

					echo '<div class="notice notice-success"><p><strong>' . esc_html__( 'Rental Products has been activated.', 'wcrp-rental-products' ) . '</strong></p><p><a href="' . esc_url( 'https://woocommerce.com/document/rental-products/' ) . '" target="_blank">' . esc_html__( 'Read documentation', 'wcrp-rental-products' ) . '</a><br><a href="' . esc_url( get_admin_url() . 'admin.php?page=wc-settings&tab=products&section=wcrp-rental-products' ) . '">' . esc_html__( 'Configure settings', 'wcrp-rental-products' ) . '</a><br><a href="' . esc_url( get_admin_url() . 'admin.php?page=wcrp-rental-products-rentals' ) . '">' . esc_html__( 'Rentals dashboard', 'wcrp-rental-products' ) . '</a><br><a href="' . esc_url( add_query_arg( 'wcrp_rental_products_activation_notice_dismiss', '1' ) ) . '">' . esc_html__( 'Dismiss notice', 'wcrp-rental-products' ) . '</a></p></div>';

				}

			}

		}

		public function activation_notice_dismiss() {

			if ( isset( $_GET['wcrp_rental_products_activation_notice_dismiss'] ) ) {

				if ( '1' == $_GET['wcrp_rental_products_activation_notice_dismiss'] ) {

					delete_transient( 'wcrp_rental_products_activation_notice' );

				}

			}

		}

		public function deposits_partial_payments_for_woocommerce_settings_notice() {

			if ( current_user_can( 'edit_plugins' ) ) {

				if ( 'yes' == get_option( 'wcrp_rental_products_return_rentals_in_completed_orders' ) ) {

					require_once ABSPATH . 'wp-admin/includes/plugin.php';

					if ( is_plugin_active( 'deposits-partial-payments-for-woocommerce/start.php' ) || is_plugin_active( 'deposits-partial-payments-for-woocommerce-pro/start.php' ) ) {

						$deposits_partial_payments_settings = get_option( 'awcdp_general_settings' );

						if ( isset( $deposits_partial_payments_settings['fully_paid_status'] ) ) {

							if ( 'completed' == $deposits_partial_payments_settings['fully_paid_status'] ) {

								// translators: %1$s: Deposits & Partial Payments for WooCommerce name, %2$s: completed order status name, %3$s: Rental Products name
								echo '<div class="notice notice-error"><p>' . sprintf( esc_html__( '%1$s has order fully paid status setting set to %2$s and %3$s has return rentals in completed orders setting set to enabled.', 'wcrp-rental-products' ), 'Deposits & Partial Payments for WooCommerce', esc_html( strtolower( wc_get_order_status_name( 'completed' ) ) ), 'Rental Products' ) . '</p><p>' . esc_html__( 'The combination of these settings this will cause rentals to be automatically marked as returned in deposit/partial payment based orders upon becoming fully paid. One of these settings should be changed immediately.', 'wcrp-rental-products' ) . '</p></div>';

							}

						}

					}

				}

			}

		}

		public function woocommerce_import_export_notice() {

			global $pagenow;

			if ( !empty( $pagenow ) ) {

				if ( isset( $_GET['page'] ) && isset( $_GET['post_type'] ) ) {

					if ( 'edit.php' == $pagenow && 'product' == $_GET['post_type'] && in_array( $_GET['page'], array( 'product_importer', 'product_exporter' ) ) ) {

						// translators: %s: rentals dashboard tools link
						echo '<div class="notice notice-warning is-dismissible"><p>' . wp_kses_post( sprintf( __( 'If you are importing/exporting rental products, please ensure you read the %s before continuing.', 'wcrp-rental-products' ), '<a href="' . esc_url( get_admin_url() . 'admin.php?page=wcrp-rental-products-rentals&tab=tools' ) . '" target="_blank">' . __( 'rental product import and export information', 'wcrp-rental-products' ) . '</a>' ) ) . '</p></div>';

					}

				}

			}

		}

	}

}
