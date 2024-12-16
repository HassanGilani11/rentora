<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Order_Drafts' ) ) {

	class WCRP_Rental_Products_Order_Drafts {

		public function __construct() {

			add_action( 'admin_footer', array( $this, 'disable_checkout_draft_order_status_changes' ) );
			add_filter( 'woocommerce_register_shop_order_post_statuses', array( $this, 'disable_checkout_draft_order_statuses_in_orders_list' ), PHP_INT_MAX );

		}

		public function disable_checkout_draft_order_status_changes() {

			// Orders with checkout-draft status are not accessible via the orders list as per disable_checkout_draft_order_statuses_in_orders_list(), however this doesn't disable the ability to change order statuses to/from draft in scenarios like where draft orders are directly accessed via their URL (need to stop status being changed from draft) and upon order creation/edit (need to stop status being changed to draft), this function disables order status changes in these scenarios

			global $pagenow;

			// If checkout draft restricitons enabled

			if ( 'yes' == get_option( 'wcrp_rental_products_checkout_draft_restrictions' ) ) {

				// Initial variables

				$enqueue_scripts = false;
				$order_id = false;

				// Determine if an add/edit order page

				if ( wcrp_rental_products_hpos_enabled() ) {

					if ( isset( $_GET['page'] ) && isset( $_GET['action'] ) ) {

						if ( 'admin.php' == $pagenow && 'wc-orders' == $_GET['page'] ) {

							$enqueue_scripts = true;

							if ( 'edit' == $_GET['action'] ) {

								if ( isset( $_GET['id'] ) ) {

									$order_id = sanitize_text_field( $_GET['id'] );

								}

							}

						}

					}

				} else {

					if ( ( 'post.php' == $pagenow || 'post-new.php' == $pagenow ) && 'shop_order' == get_post_type() ) {

						$enqueue_scripts = true;

						if ( isset( $_GET['post'] ) ) {

							$order_id = sanitize_text_field( $_GET['post'] );

						}

					}

				}

				// Enqueue scripts

				if ( true == $enqueue_scripts ) {

					// Disable order statuses, note that in the below the order statuses have to be disabled and not hidden, as the order screen uses Select2 and it isn't aware of hidden options

					?>

					<script>
						jQuery( document ).ready( function( $ ) {

							<?php
							if ( false !== $order_id ) {

								// If order edit

								$order = wc_get_order( $order_id );
								$order_status = $order->get_status();

								// If order is a checkout draft

								if ( 'checkout-draft' == $order_status ) {

									?>

									$( '#order_status option:not([value="wc-checkout-draft"])' ).attr( 'disabled', true ); // Drafts can not have order status changed, see comments in disable_in_orders_list()

									<?php


								} else {

									// If order is not a checkout draft

									?>

									$( '#order_status option[value="wc-checkout-draft"]' ).attr( 'disabled', true ); // Stop the order from being changed to a draft

									<?php

								}

							} else {

								// If order creation

								?>

								$( '#order_status option[value="wc-checkout-draft"]' ).attr( 'disabled', true ); // Stop the order from being created as a draft

								<?php

							}
							?>

						});
					</script>

					<?php

				}

			}

		}

		public function disable_checkout_draft_order_statuses_in_orders_list( $order_statuses ) {

			// Orders with checkout-draft status are disabled in the orders list, this is because they may can contain rental order items which were available at the time the draft was created but have since been reserved, so disabling stops the scenario of a user going into a draft and changing the order status to one which reserves stock when it has already been reserved since then draft was created, they are disabled in this way rather than just disabling the order status options on the edit order page because a user can still change status via bulk actions on the orders list

			// If checkout draft restricitons enabled

			if ( 'yes' == get_option( 'wcrp_rental_products_checkout_draft_restrictions' ) ) {

				if ( isset( $order_statuses['wc-checkout-draft']['show_in_admin_all_list'] ) ) {

					$order_statuses['wc-checkout-draft']['show_in_admin_all_list'] = false; // Stops checkout drafts being included in the All (xxx) order count at the top of the orders list

				}

				if ( isset( $order_statuses['wc-checkout-draft']['show_in_admin_status_list'] ) ) {

					$order_statuses['wc-checkout-draft']['show_in_admin_status_list'] = false; // Stops checkout drafts being included in the order status toggle at top of orders list

				}

			}

			return $order_statuses;

		}

	}

}
