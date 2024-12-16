<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Account' ) ) {

	class WCRP_Rental_Products_Account {

		public function __construct() {

			add_action( 'woocommerce_order_details_after_order_table', array( $this, 'order_again_disable' ), 0 ); // 0 priority so hooked before the potential removal occurs
			add_filter( 'woocommerce_my_account_my_orders_actions', array( $this, 'failed_order_pay_disable' ), PHP_INT_MAX, 2 );

		}

		public function order_again_disable( $order ) {

			if ( true == wcrp_rental_products_order_has_rentals( $order->get_id() ) ) {

				remove_action( 'woocommerce_order_details_after_order_table', 'woocommerce_order_again_button' ); // Order again button is not available for orders containing rental products as if used the cart item meta would not be added and the price is got from the product object (which is only the base price), the meta could be added by us instead of disabling this functionality but even if it was the price calculation would not be correct and the dates would be the same as previously rented which would probably have passed so there isn't any point allowing this, at time of writing rental product date selection and price calculations only occur on the product page, there isn't any functionality to allow the dates/prices to be calculated at the point of order again functionality

			}

		}

		public function failed_order_pay_disable( $actions, $order ) {

			// If cancel rentals in failed orders is enabled then payment is disabled, this is because the rentals within the order have been cancelled and therefore the order shouldn't be paid as the rentals are no longer valid/totals wrong

			if ( true == wcrp_rental_products_order_has_rentals( $order->get_id() ) && 'failed' == $order->get_status() && 'yes' == get_option( 'wcrp_rental_products_cancel_rentals_in_failed_orders' ) ) {

				unset( $actions['pay'] );

			}

			return $actions;

		}

	}

}
