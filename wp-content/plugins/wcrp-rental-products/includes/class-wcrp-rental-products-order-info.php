<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Order_Info' ) ) {

	class WCRP_Rental_Products_Order_Info {

		public function __construct() {

			add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'after_order_details' ) );
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
			add_filter( 'woocommerce_admin_order_buyer_name', array( $this, 'order_list_includes_rentals_text' ), 10, 2 );

		}

		public function after_order_details( $order ) {

			// If type is shop_order, this ensures it only appears for bonafide orders, without this condition it would appear when should not for shop_subscription (if WooCommerce Subscriptions active), awcdp_payment (if Deposits & Partial Payments for WooCommerce active) and potentially other third party types which use the woocommerce_admin_order_data_after_order_details action hook

			if ( 'shop_order' == $order->get_type() ) {

				// There is no check if the order has rentals here and is displayed always, this is because it may be a new order and we want to ensure the user has this rental information incase the new order contains rentals or if it's an existing order that previously didn't have rentals but might be getting rentals added

				$cancel_rentals_in_failed_orders = get_option( 'wcrp_rental_products_cancel_rentals_in_failed_orders' );
				$return_rentals_in_completed_orders = get_option( 'wcrp_rental_products_return_rentals_in_completed_orders' );

				if ( 'yes' == $cancel_rentals_in_failed_orders ) {

					if ( 'yes' == $return_rentals_in_completed_orders ) {

						// translators: %1$s: completed order status name, %2$s: cancelled order status name, %3$s: refunded order status name, %4$s: failed order status name
						echo '<div id="wcrp-rental-products-order-details-notice" class="form-field form-field-wide notice notice-info inline"><p>' . sprintf( esc_html__( 'Setting order status to %1$s will mark all rentals within the order as returned and setting order status to %2$s, %3$s or %4$s will cancel all rentals within the order.', 'wcrp-rental-products' ), esc_html( strtolower( wc_get_order_status_name( 'wc-completed' ) ) ), esc_html( strtolower( wc_get_order_status_name( 'wc-cancelled' ) ) ), esc_html( strtolower( wc_get_order_status_name( 'wc-refunded' ) ) ), esc_html( strtolower( wc_get_order_status_name( 'wc-failed' ) ) ) ) . '</p></div>';

					} else {

						// translators: %1$s: cancelled order status name, %2$s: refunded order status name, %3$s: failed order status name
						echo '<div id="wcrp-rental-products-order-details-notice" class="form-field form-field-wide notice notice-info inline"><p>' . sprintf( esc_html__( 'Setting order status to %1$s, %2$s or %3$s will cancel all rentals within the order.', 'wcrp-rental-products' ), esc_html( strtolower( wc_get_order_status_name( 'wc-cancelled' ) ) ), esc_html( strtolower( wc_get_order_status_name( 'wc-refunded' ) ) ), esc_html( strtolower( wc_get_order_status_name( 'wc-failed' ) ) ) ) . '</p></div>';

					}

				} else {

					if ( 'yes' == $return_rentals_in_completed_orders ) {

						// translators: %1$s: completed order status name, %2$s: cancelled order status name, %3$s: refunded order status name
						echo '<div id="wcrp-rental-products-order-details-notice" class="form-field form-field-wide notice notice-info inline"><p>' . sprintf( esc_html__( 'Setting order status to %1$s will mark all rentals within the order as returned and setting order status to %2$s or %3$s will cancel all rentals within the order.', 'wcrp-rental-products' ), esc_html( strtolower( wc_get_order_status_name( 'wc-completed' ) ) ), esc_html( strtolower( wc_get_order_status_name( 'wc-cancelled' ) ) ), esc_html( strtolower( wc_get_order_status_name( 'wc-refunded' ) ) ) ) . '</p></div>';

					} else {

						// translators: %1$s: cancelled order status name, %2$s: refunded order status name
						echo '<div id="wcrp-rental-products-order-details-notice" class="form-field form-field-wide notice notice-info inline"><p>' . sprintf( esc_html__( 'Setting order status to %1$s or %2$s will cancel all rentals within the order.', 'wcrp-rental-products' ), esc_html( strtolower( wc_get_order_status_name( 'wc-cancelled' ) ) ), esc_html( strtolower( wc_get_order_status_name( 'wc-refunded' ) ) ) ) . '</p></div>';

					}

				}

			}

		}

		public function add_meta_boxes() {

			$managing_rental_orders_information = get_option( 'wcrp_rental_products_managing_rental_orders_information' );

			// There is no check if the order has rentals here and is displayed always, this is because it may be a new order and we want to ensure the user has this rental information incase the new order contains rentals or if it's an existing order that previously didn't have rentals but might be getting rentals added

			if ( 'yes' == $managing_rental_orders_information ) {

				add_meta_box(
					'wcrp-rental-products-managing-rental-orders',
					__( 'Managing rental orders', 'wcrp-rental-products' ),
					array( $this, 'meta_box_managing_rental_orders' ),
					( wcrp_rental_products_hpos_enabled() ? wc_get_page_screen_id( 'shop-order' ) : 'shop_order' ),
					'normal',
					'core'
				);

			}

		}

		public function meta_box_managing_rental_orders() {

			$cancel_rentals_in_failed_orders = get_option( 'wcrp_rental_products_cancel_rentals_in_failed_orders' );
			$return_rentals_in_completed_orders = get_option( 'wcrp_rental_products_return_rentals_in_completed_orders' );

			// translators: %s: rental settings link
			echo '<p>' . wp_kses_post( sprintf( __( 'Information on managing rental orders based on the %s currently configured.', 'wcrp-rental-products' ), '<a href="' . esc_url( get_admin_url() . 'admin.php?page=wc-settings&tab=products&section=wcrp-rental-products' ) . '" target="_blank">' . esc_html__( 'rental settings', 'wcrp-rental-products' ) . '</a>' ) ) . '</p>';

			echo '<p><strong>' . esc_html__( 'Order statuses', 'wcrp-rental-products' ) . '</strong></p>';
			echo '<ul>';

			if ( 'yes' == $return_rentals_in_completed_orders ) {

				// translators: %s: completed order status name
				echo '<li>' . sprintf( esc_html__( 'The %s order status should only be used when all rental items within the order are returned', 'wcrp-rental-products' ), esc_html( strtolower( wc_get_order_status_name( 'completed' ) ) ) ) . '</li>';

			} else {

				echo '<li>' . esc_html__( 'When rentals are returned mark each rental item within the order as returned', 'wcrp-rental-products' ) . '</li>';

			}

			if ( 'yes' == $cancel_rentals_in_failed_orders ) {

				require_once ABSPATH . 'wp-admin/includes/plugin.php';

				if ( is_plugin_active( 'deposits-partial-payments-for-woocommerce/start.php' ) || is_plugin_active( 'deposits-partial-payments-for-woocommerce-pro/start.php' ) ) {

					// translators: %1$s: pending order status name, %2$s: on-hold order status name, %3$s: processing order status name, %4$s: partially paid order status name, %5$s: Deposits & Partial Payments for WooCommerce name
					echo '<li>' . sprintf( esc_html__( 'If this order has a status of %1$s, %2$s, %3$s or %4$s* then the rental items within this order have rental stock reserved (* = order status of %5$s)', 'wcrp-rental-products' ), esc_html( strtolower( wc_get_order_status_name( 'pending' ) ) ), esc_html( strtolower( wc_get_order_status_name( 'on-hold' ) ) ), esc_html( strtolower( wc_get_order_status_name( 'processing' ) ) ), esc_html( strtolower( wc_get_order_status_name( 'partially-paid' ) ) ), 'Deposits & Partial Payments for WooCommerce' ) . '</li>';

				} else {

					// translators: %1$s: pending order status name, %2$s: on-hold order status name, %3$s: processing order status name
					echo '<li>' . sprintf( esc_html__( 'If this order has a status of %1$s, %2$s or %3$s then the rental items within this order have rental stock reserved', 'wcrp-rental-products' ), esc_html( strtolower( wc_get_order_status_name( 'pending' ) ) ), esc_html( strtolower( wc_get_order_status_name( 'on-hold' ) ) ), esc_html( strtolower( wc_get_order_status_name( 'processing' ) ) ) ) . '</li>';

				}

				// translators: %1$s: cancelled order status name, %2$s: refunded order status name, %3$s: failed order status name
				echo '<li>' . sprintf( esc_html__( 'If the order is deleted (after trashing) or if the order status is %1$s, %2$s or %3$s then all rental stock reserved for the order will be made available again, in addition if the order status is %2$s all rental items will be marked as cancelled', 'wcrp-rental-products' ), esc_html( strtolower( wc_get_order_status_name( 'cancelled' ) ) ), esc_html( strtolower( wc_get_order_status_name( 'refunded' ) ) ), esc_html( strtolower( wc_get_order_status_name( 'failed' ) ) ) ) . '</li>';

			} else {

				require_once ABSPATH . 'wp-admin/includes/plugin.php';

				if ( is_plugin_active( 'deposits-partial-payments-for-woocommerce/start.php' ) || is_plugin_active( 'deposits-partial-payments-for-woocommerce-pro/start.php' ) ) {

					// translators: %1$s: pending order status name, %2$s: on-hold order status name, %3$s: processing order status name, %4$s: failed order status name, %5$s: partially paid order status name, %6$s: Deposits & Partial Payments for WooCommerce name
					echo '<li>' . sprintf( esc_html__( 'If this order has a status of %1$s, %2$s, %3$s, %4$s or %5$s* then the rental items within this order have rental stock reserved (* = order status of %6$s)', 'wcrp-rental-products' ), esc_html( strtolower( wc_get_order_status_name( 'pending' ) ) ), esc_html( strtolower( wc_get_order_status_name( 'on-hold' ) ) ), esc_html( strtolower( wc_get_order_status_name( 'processing' ) ) ), esc_html( strtolower( wc_get_order_status_name( 'failed' ) ) ), esc_html( strtolower( wc_get_order_status_name( 'partially-paid' ) ) ), 'Deposits & Partial Payments for WooCommerce' ) . '</li>';

				} else {

					// translators: %1$s: pending order status name, %2$s: on-hold order status name, %3$s: processing order status name, %4$s: failed order status name
					echo '<li>' . sprintf( esc_html__( 'If this order has a status of %1$s, %2$s, %3$s, or %4$s then the rental items within this order have rental stock reserved', 'wcrp-rental-products' ), esc_html( strtolower( wc_get_order_status_name( 'pending' ) ) ), esc_html( strtolower( wc_get_order_status_name( 'on-hold' ) ) ), esc_html( strtolower( wc_get_order_status_name( 'processing' ) ) ), esc_html( strtolower( wc_get_order_status_name( 'failed' ) ) ), esc_html( strtolower( wc_get_order_status_name( 'partially-paid' ) ) ) ) . '</li>';

				}

				// translators: %1$s: cancelled order status name, %2$s: refunded order status name
				echo '<li>' . sprintf( esc_html__( 'If the order is deleted (after trashing) or if the order status is %1$s or %2$s then all rental stock reserved for the order will be made available again, in addition if the order status is %2$s all rental items will be marked as cancelled', 'wcrp-rental-products' ), esc_html( strtolower( wc_get_order_status_name( 'cancelled' ) ) ), esc_html( strtolower( wc_get_order_status_name( 'refunded' ) ) ) ) . '</li>';

			}

			echo '<li>' . esc_html__( 'If setting custom order statuses on the order then it should still be transitioned through any relevant order statuses above to ensure rental stock is reserved/made available again (e.g. if you use custom order statuses to trigger shipping notification emails)', 'wcrp-rental-products' ) . '</li>';

			echo '</ul>';

			echo '<p><strong>' . esc_html__( 'Cancelling, removing or refunding rental items', 'wcrp-rental-products' ) . '</strong></p>';
			echo '<ul>';
			echo '<li>' . esc_html__( 'If rental item is cancelled or removed then the rental stock reserved will be made available again', 'wcrp-rental-products' ) . '</li>';
			echo '<li>' . esc_html__( 'If rental item is cancelled it does not adjust the totals or perform a refund, if either is required this should be done manually', 'wcrp-rental-products' ) . '</li>';
			echo '<li>' . esc_html__( 'If rental item is refunded then the cancel rental or mark as returned button should be used after (depending on partial or full refund of the rental item) for the rental stock reserved to be made available again', 'wcrp-rental-products' ) . '</li>';
			echo '<li>' . esc_html__( 'Removing rental items from the order using the delete item icon is not recommended if the customer has already made payment as the rental item would then no longer exist to register a refund against it in future', 'wcrp-rental-products' ) . '</li>';
			echo '</ul>';

			echo '<p><strong>' . esc_html__( 'Refunding rental security deposits', 'wcrp-rental-products' ) . '</strong></p>';
			echo '<ul>';
			echo '<li>' . esc_html__( 'If the order includes rental security deposits (as a product in the order has the security deposits product option enabled) then you may wish to refund the security deposit upon satisfactory return of the product', 'wcrp-rental-products' ) . '</li>';
			echo '<li>' . esc_html__( 'Rental security deposits need to be manually refunded, it does not occur automatically upon marking a rental item as returned, etc', 'wcrp-rental-products' ) . '</li>';
			echo '</ul>';

			echo '<p><strong>' . esc_html__( 'Adding or changing rental items', 'wcrp-rental-products' ) . '</strong></p>';
			echo '<ul>';
			echo '<li>' . esc_html__( 'Ensure the order is set to an order status which allows order editing', 'wcrp-rental-products' ) . '</li>';
			echo '<li>' . esc_html__( 'To add a rental item click the add item(s) button and select add rental product(s) - note that if you are attempting to add a rental or purchase type product and require the purchasable part of the product use the add product(s) button instead of add rental product(s)', 'wcrp-rental-products' ) . '</li>';
			echo '<li>' . esc_html__( 'To change a rental item click the change rental button for instructions', 'wcrp-rental-products' ) . '</li>';
			echo '</ul>';

			echo '<p><strong>' . esc_html__( 'Recalculating totals', 'wcrp-rental-products' ) . '</strong></p>';
			echo '<ul>';
			echo '<li>' . esc_html__( 'It is recommended the recalculate button is used to recalculate all totals after making changes to the order contents', 'wcrp-rental-products' ) . '</li>';
			echo '</ul>';

			echo '<p><strong>' . esc_html__( 'General order management information', 'wcrp-rental-products' ) . '</strong></p>';
			echo '<ul>';
			echo '<li><a href="https://woocommerce.com/document/managing-orders/" target="_blank">' . esc_html__( 'Order management basics', 'wcrp-rental-products' ) . '</a></li>';
			echo '<li><a href="https://woocommerce.com/document/woocommerce-refunds/" target="_blank">' . esc_html__( 'Processing refunds', 'wcrp-rental-products' ) . '</a> <small>' . esc_html__( '-', 'wcrp-rental-products' ) . ' ' . esc_html__( 'This documentation refers to automatic and manual refunds, for automatic refunds this is in the context of performing a refund via the WooCommerce order screen without the need to manually login to your payment provider to process it, it should not be confused with a means of automatically processing refunds upon marking a rental item as returned, etc', 'wcrp-rental-products' ) . '</small></li>';
			echo '</ul>';

		}

		public function order_list_includes_rentals_text( $buyer, $order ) {

			if ( !empty( $order ) ) {

				if ( wcrp_rental_products_order_has_rentals( $order->get_id() ) ) {

					$buyer .= ' ' . esc_html__( '-', 'wcrp-rental-products' ) . ' ' . esc_html__( 'Includes rentals', 'wcrp-rental-products' );

				}

			}

			return $buyer;

		}

	}

}
