<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Order_Line_Items' ) ) {

	class WCRP_Rental_Products_Order_Line_Items {

		public function __construct() {

			add_action( 'admin_footer', array( $this, 'order_line_item_scripts' ) );
			add_filter( 'woocommerce_admin_html_order_item_class', array( $this, 'order_line_item_row_class' ), 10, 3 );
			add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'order_line_item_data_create' ), 10, 4 );
			add_filter( 'woocommerce_order_item_display_meta_key', array( $this, 'order_line_item_display_meta_key' ) );
			add_filter( 'woocommerce_order_item_display_meta_value', array( $this, 'order_line_item_display_meta_value' ), 10, 3 );
			add_filter( 'woocommerce_order_item_get_formatted_meta_data', array( $this, 'order_line_item_formatted_meta_data' ), 10, 2 );
			add_filter( 'woocommerce_order_item_product', array( $this, 'order_line_item_rental_purchase_overrides' ), PHP_INT_MAX, 2 );
			add_action( 'woocommerce_after_order_itemmeta', array( $this, 'order_line_item_actions' ), 10, 3 );
			add_action( 'wp_ajax_wcrp_rental_products_order_line_item_actions_save', array( $this, 'order_line_item_actions_save' ) );
			add_action( 'woocommerce_before_delete_order_item', array( $this, 'order_line_item_delete' ) );
			add_action( 'woocommerce_order_item_add_line_buttons', array( $this, 'order_line_item_add_line_buttons' ) );

		}

		public function order_line_item_scripts() {

			global $pagenow;

			$enqueue_scripts = false;

			if ( wcrp_rental_products_hpos_enabled() ) {

				if ( isset( $_GET['page'] ) && isset( $_GET['action'] ) ) {

					if ( 'admin.php' == $pagenow && 'wc-orders' == $_GET['page'] ) {

						$enqueue_scripts = true;

					}

				}

			} else {

				if ( ( 'post.php' == $pagenow || 'post-new.php' == $pagenow ) && 'shop_order' == get_post_type() ) {

					$enqueue_scripts = true;

				}

			}

			if ( true == $enqueue_scripts ) {

				// Note that there isn't a check for if the order contains rentals here using wcrp_rental_products_order_has_rentals() or similar, there used to be one however this stops the script below running if no rentals, and now rental products can be added to orders through the dashboard a new order doesn't initially have any rentals, so the scripts below wouldn't run - but they are needed if a rental product is added to the order, if the old condition was still here it would mean the order line item buttons wouldn't function correctly until the page is refreshed after adding a rental product (and the other things in the script below)

				?>

				<script>
					jQuery( document ).ready( function( $ ) {

						<?php // Disable restock refunded items ?>

						function disableRestockRefundedItems() {

							var disableRestockRefundedItems = false;

							$( '#wcrp-rental-products-restock-refunded-items-notice' ).remove();

							$( '#order_line_items .item' ).each( function( index ) {

								if ( $( this ).hasClass( 'wcrp-rental-products-order-item-is-rental' ) ) {

									if ( $( this ).find( '.refund_order_item_qty' ).val() > 0 ) {

										disableRestockRefundedItems = true;

									}

								}

							});

							if ( disableRestockRefundedItems == true ) {

								<?php // If rental products are included in the order then the restocking refunded items option is disabled, this is due to how wc_restock_refunded_items() works in relation to how we manipulate _reduced_stock in WCRP_Rental_Products_Stock_Manipulation, it isn't possible to allow this conditionally in some scenarios, like when attempting to allow restock of the purchase part of rental or purchase products, so we disable entirely if rental products are being refunded ?>

								$( '#restock_refunded_items' ).prop( 'checked', false ).attr( 'disabled', true );

								$( '<div id="wcrp-rental-products-restock-refunded-items-notice"><?php esc_html_e( 'Restock refunded items disabled as refund includes rental items', 'wcrp-rental-products' ); ?><br><?php esc_html_e( 'Rental stock reserved can be made available by marking as returned/cancelled after refund', 'wcrp-rental-products' ); ?><br><?php esc_html_e( 'To refund and restock non-rental items refund these separately to rental items', 'wcrp-rental-products' ); ?></div>' ).appendTo( 'label[for="restock_refunded_items"]' );

							} else {

								$( '#restock_refunded_items' ).attr( 'disabled', false );

							}

						}

						<?php // Disable rental order item meta fields ?>

						function disableRentalOrderItemMetaFields() {

							$( '.wcrp-rental-products-order-item-is-rental .name .edit input[type="text"]' ).each( function( index ) {

								if ( $( this ).val().startsWith( 'wcrp_rental_products_' ) ) {

									$( this ).attr( 'readonly', true );
									$( this ).parent().find( 'textarea' ).attr( 'readonly', true ); <?php // Disable order item meta fields, readonly instead of disabled to ensures still submitted ?>
									$( this ).closest( 'tr' ).find( '.remove_order_item_meta' ).attr( 'disabled', true ); <?php // Disable deleting order item meta ?>
									$( this ).closest( '.wcrp-rental-products-order-item-is-rental' ).find( '.quantity .edit .quantity' ).attr( 'readonly', true ); <?php // Quantity fields on edit/refund, readonly instead of disabled to ensures still submitted ?>

								}

							});

						}

						<?php // Line item action > mark as returned ?>

						$( document ).on( 'click', '.wcrp-rental-products-order-line-item-action-mark-as-returned', function( e ) {

							e.preventDefault();

							if ( confirm( "<?php esc_html_e( 'Are you sure you want to mark this rental as returned?', 'wcrp-rental-products' ); ?>" ) ) {

								$( this ).addClass( 'disabled' );
								$( this ).click( false );

								var orderLineItemActionsSaveAjaxRequest = jQuery.ajax({
									'url':		'<?php echo esc_html( admin_url( 'admin-ajax.php' ) ); ?>',
									'method':	'POST',
									'data':		{
										'action':			'wcrp_rental_products_order_line_item_actions_save',
										'nonce':			'<?php echo esc_html( wp_create_nonce( 'wcrp_rental_products_order_line_item_actions_save' ) ); ?>',
										'order_item_id':	$( this ).parent( '.wcrp-rental-products-order-line-item-actions' ).attr( 'data-order-item-id' ),
										'type':				'mark_as_returned',
									}
								});

								orderLineItemActionsSaveAjaxRequest.done( function( response ) {

									if ( 'error' !== response ) {

										$( '#woocommerce-order-items' ).trigger( 'wc_order_items_reload' );
										$( '#woocommerce-order-notes .inside .order_notes' ).remove(); <?php // If amending in future ensure manually adding a note does not get added twice, this line and the one below was written in a specific way due to how manually added notes HTML gets inserted ?>
										$( response ).prependTo( '#woocommerce-order-notes .inside' );

									} else {

										alert( "<?php esc_html_e( 'Rental could not be marked as returned due to an error.', 'wcrp-rental-products' ); ?>" );

									}

								});

							}

						});

						<?php // Line item action > change rental ?>

						$( document ).on( 'click', '.wcrp-rental-products-order-line-item-action-change-rental', function( e ) {

							e.preventDefault();

							alert( "<?php esc_html_e( 'The dates and quantities for this rental are reserved, to change this rental it will need to be cancelled via the cancel rental button to make the reserved dates and quantities available again.', 'wcrp-rental-products' ); ?>" + "\n\n" + "<?php esc_html_e( 'Once cancelled ensure the order is an editable order status, then use the add item(s) button to add the product again. Depending on the changes you may wish to modify line item totals and take an additional payment, or refund for the difference.', 'wcrp-rental-products' ); ?>" + "\n\n" + "<?php esc_html_e( 'It is recommended the recalculate button is used to recalculate all totals after making changes to the order contents.', 'wcrp-rental-products' ); ?>" );

						});

						<?php // Line item action > cancel rental ?>

						$( document ).on( 'click', '.wcrp-rental-products-order-line-item-action-cancel-rental', function( e ) {

							e.preventDefault();

							if ( confirm( "<?php esc_html_e( 'Are you sure you want to cancel this rental?', 'wcrp-rental-products' ); ?>" ) ) {

								$( this ).addClass( 'disabled' );
								$( this ).click( false );

								var orderLineItemActionsSaveAjaxRequest = jQuery.ajax({
									'url':		'<?php echo esc_html( admin_url( 'admin-ajax.php' ) ); ?>',
									'method':	'POST',
									'data':		{
										'action':			'wcrp_rental_products_order_line_item_actions_save',
										'nonce':			'<?php echo esc_html( wp_create_nonce( 'wcrp_rental_products_order_line_item_actions_save' ) ); ?>',
										'order_item_id':	$( this ).parent( '.wcrp-rental-products-order-line-item-actions' ).attr( 'data-order-item-id' ),
										'type':				'cancel_rental',
									}
								});

								orderLineItemActionsSaveAjaxRequest.done( function( response ) {

									if ( 'error' !== response ) {

										$( '#woocommerce-order-items' ).trigger( 'wc_order_items_reload' );
										$( '#woocommerce-order-notes .inside .order_notes' ).remove(); <?php // If amending in future ensure manually adding a note does not get added twice, this line and the one below was written in a specific way due to how manually added notes HTML gets inserted ?>
										$( response ).prependTo( '#woocommerce-order-notes .inside' );

									} else {

										alert( "<?php esc_html_e( 'Rental could not be cancelled due to an error.', 'wcrp-rental-products' ); ?>" );

									}

								});

							}

						});

						<?php // Initial ?>

						disableRentalOrderItemMetaFields();
						disableRestockRefundedItems();

						<?php
						// On AJAX complete (ensures if any changes made via AJAX request the fields get set to what they should such as disabling fields form edit, e.g. editing an order line item and clicking the cancel button would send an AJAX request and replace the order line items markup, after that happens this re-runs the functions ensuring the fields are as required)
						?>

						$( document ).ajaxComplete( function() {

							disableRentalOrderItemMetaFields();
							disableRestockRefundedItems();

						});

						<?php // On change of order item refund quantity ?>

						$( document ).on( 'change', '.refund_order_item_qty', function( e ) {

							disableRestockRefundedItems();

						});

					});

				</script>

				<?php

			}

		}

		public function order_line_item_row_class( $class, $item, $order ) {

			$item_id = $item->get_id();

			if ( !empty( $item_id ) ) {

				if ( !empty( wc_get_order_item_meta( $item->get_id(), 'wcrp_rental_products_rent_from', true ) ) || !empty( wc_get_order_item_meta( $item->get_id(), 'wcrp_rental_products_cancelled', true ) ) ) { // Cancelled condition means it still has a rental class even if cancelled

					$class .= ' wcrp-rental-products-order-item-is-rental '; // Spaces added around incase other extensions use this and lead on without spaces, would give it an incorrect class

				}

			}

			return $class;

		}

		public function order_line_item_data_create( $item, $cart_item_key, $values, $order ) {

			if ( isset( $values['wcrp_rental_products_rent_from'] ) && isset( $values['wcrp_rental_products_rent_to'] ) && isset( $values['wcrp_rental_products_return_days_threshold'] ) ) {

				// '' !== checks as could be 0 which would be conditioned as empty if using !empty

				if ( '' !== $values['wcrp_rental_products_rent_from'] && '' !== $values['wcrp_rental_products_rent_to'] && '' !== $values['wcrp_rental_products_return_days_threshold'] ) {

					$item->add_meta_data( 'wcrp_rental_products_rent_from', $values['wcrp_rental_products_rent_from'] );
					$item->add_meta_data( 'wcrp_rental_products_rent_to', $values['wcrp_rental_products_rent_to'] );
					$item->add_meta_data( 'wcrp_rental_products_return_days_threshold', $values['wcrp_rental_products_return_days_threshold'] ); // On the order screen the return days threshold is conditionally displayed/hidden via order_line_item_formatted_meta_data()

					// In person pick up/return meta data, these are all added so exist in the order line item data, but only some are displayed

					if ( isset( $values['wcrp_rental_products_in_person_pick_up_return'] ) ) {

						if ( 'yes' == $values['wcrp_rental_products_in_person_pick_up_return'] ) {

							$item->add_meta_data( 'wcrp_rental_products_in_person_pick_up_return', sanitize_text_field( $values['wcrp_rental_products_in_person_pick_up_return'] ) );
							$item->add_meta_data( 'wcrp_rental_products_in_person_pick_up_date', sanitize_text_field( $values['wcrp_rental_products_in_person_pick_up_date'] ) );
							$item->add_meta_data( 'wcrp_rental_products_in_person_pick_up_time', sanitize_text_field( $values['wcrp_rental_products_in_person_pick_up_time'] ) );
							$item->add_meta_data( 'wcrp_rental_products_in_person_pick_up_fee', sanitize_text_field( $values['wcrp_rental_products_in_person_pick_up_fee'] ) );
							$item->add_meta_data( 'wcrp_rental_products_in_person_return_date', sanitize_text_field( $values['wcrp_rental_products_in_person_return_date'] ) );
							$item->add_meta_data( 'wcrp_rental_products_in_person_return_date_type', sanitize_text_field( $values['wcrp_rental_products_in_person_return_date_type'] ) );
							$item->add_meta_data( 'wcrp_rental_products_in_person_return_time', sanitize_text_field( $values['wcrp_rental_products_in_person_return_time'] ) );
							$item->add_meta_data( 'wcrp_rental_products_in_person_return_fee', sanitize_text_field( $values['wcrp_rental_products_in_person_return_fee'] ) );

						}

					}

				}

			}

		}

		public function order_line_item_display_meta_key( $display_key ) {

			if ( 'wcrp_rental_products_rent_from' == $display_key  ) {

				$display_key = apply_filters( 'wcrp_rental_products_text_rent_from', get_option( 'wcrp_rental_products_text_rent_from' ) );

			} elseif ( 'wcrp_rental_products_rent_to' == $display_key ) {

				$display_key = apply_filters( 'wcrp_rental_products_text_rent_to', get_option( 'wcrp_rental_products_text_rent_to' ) );

			} elseif ( 'wcrp_rental_products_return_days_threshold' == $display_key ) {

				$display_key = apply_filters( 'wcrp_rental_products_text_rental_return_within', get_option( 'wcrp_rental_products_text_rental_return_within' ) ); // Conditionally displayed/hidden via order_line_item_formatted_meta_data()

			} elseif ( 'wcrp_rental_products_returned' == $display_key ) {

				$display_key = apply_filters( 'wcrp_rental_products_text_rental_returned', get_option( 'wcrp_rental_products_text_rental_returned' ) );

			} elseif ( 'wcrp_rental_products_cancelled' == $display_key ) {

				$display_key = apply_filters( 'wcrp_rental_products_text_rental_cancelled', get_option( 'wcrp_rental_products_text_rental_cancelled' ) );

			} elseif ( 'wcrp_rental_products_in_person_pick_up_return' == $display_key ) { // In person pick up/return display keys start here, only the keys which are displayed are included, the ones not omitted via order_line_item_formatted_meta_data()

				$display_key = apply_filters( 'wcrp_rental_products_text_in_person_pick_up_return', get_option( 'wcrp_rental_products_text_in_person_pick_up_return' ) );

			} elseif ( 'wcrp_rental_products_in_person_pick_up_time' == $display_key ) {

				$display_key = apply_filters( 'wcrp_rental_products_text_pick_up_time', get_option( 'wcrp_rental_products_text_pick_up_time' ) );

			} elseif ( 'wcrp_rental_products_in_person_return_date' == $display_key ) {

				$display_key = apply_filters( 'wcrp_rental_products_text_return_date', get_option( 'wcrp_rental_products_text_return_date' ) ); // This is only shown if return date !== rent to date via order_line_item_formatted_meta_data()

			} elseif ( 'wcrp_rental_products_in_person_return_time' == $display_key ) {

				$display_key = apply_filters( 'wcrp_rental_products_text_return_time', get_option( 'wcrp_rental_products_text_return_time' ) );

			}

			return $display_key;

		}

		public function order_line_item_display_meta_value( $display_meta_value, $meta, $order_item ) {

			if ( !empty( $meta ) ) {

				// Format meta values if needed to be displayed differently from how stored

				if ( 'wcrp_rental_products_rent_from' == $meta->key || 'wcrp_rental_products_rent_to' == $meta->key ) {

					$display_meta_value = date_i18n( wcrp_rental_products_rental_date_format(), strtotime( $display_meta_value ) ); // Displays date in format required

				} elseif ( 'wcrp_rental_products_returned' == $meta->key ) {

					$display_meta_value = ucfirst( $display_meta_value ); // Changes yes to Yes

				} elseif ( 'wcrp_rental_products_return_days_threshold' == $meta->key ) {

					$display_meta_value = $display_meta_value . ' ' . esc_html__( 'days', 'wcrp-rental-products' ) . ' ' . esc_html__( '(', 'wcrp-rental-products' ) . date_i18n( wcrp_rental_products_rental_date_format(), strtotime( gmdate( 'Y-m-d', strtotime( wc_get_order_item_meta( $order_item->get_id(), 'wcrp_rental_products_rent_to', true ) . ' + ' . wc_get_order_item_meta( $order_item->get_id(), 'wcrp_rental_products_return_days_threshold', true ) . 'days' ) ) ) ) . esc_html__( ')', 'wcrp-rental-products' ); // Adds days and date to end of day number, same as cart items

				} elseif ( 'wcrp_rental_products_cancelled' == $meta->key ) {

					$display_meta_value = ucfirst( $display_meta_value ); // Changes yes to Yes

				} elseif ( 'wcrp_rental_products_in_person_pick_up_return' == $meta->key ) { // In person pick up/return meta values start here, only the values which are displayed are included, the ones not omitted via order_line_item_formatted_meta_data()

					$display_meta_value = ucfirst( $display_meta_value ); // Changes yes to Yes

				} elseif ( 'wcrp_rental_products_in_person_pick_up_time' == $meta->key ) {

					$display_meta_value = WCRP_Rental_Products_Misc::four_digit_time_formatted( $display_meta_value ); // Displays time in format required

				} elseif ( 'wcrp_rental_products_in_person_return_date' == $meta->key ) {

					$display_meta_value = date_i18n( wcrp_rental_products_rental_date_format(), strtotime( $display_meta_value ) ); // Displays date in format required, this is only shown if return date !== rent to date via order_line_item_formatted_meta_data()

				} elseif ( 'wcrp_rental_products_in_person_return_time' == $meta->key ) {

					$display_meta_value = WCRP_Rental_Products_Misc::four_digit_time_formatted( $display_meta_value ); // Displays time in format required

				}

			}

			return $display_meta_value;

		}

		public function order_line_item_formatted_meta_data( $formatted_meta_data, $item ) {

			if ( !empty( $formatted_meta_data ) ) {

				// Conditionally hide meta where we do not want it to display

				foreach ( $formatted_meta_data as $key => $meta ) {

					if ( 'wcrp_rental_products_return_days_threshold' == $meta->key ) {

						if ( '0' == $meta->value ) { // Return days threshold only ever shown if > 0

							unset( $formatted_meta_data[$key] );

						} else {

							if ( 'no' == get_option( 'wcrp_rental_products_return_days_display' ) ) { // If return days display disabled then return days should not display to the customer

								// The anonymous function below fakes the order item meta display for return days threshold so it is still shown in the dashboard, important: we used to simply use a !is_admin() conditional for only disabling return days display on the frontend, however we found that if a store admin is editing orders and changing order statuses because this is an admin dashboard page it meant that the return days threshold was being included on customer facing emails, hence why we fake the order item meta display below, then unset the order item meta afterwards, the faked order item meta is not included in emails as based off the woocommerce_after_order_itemmeta action hook not used in emails

								add_action( 'woocommerce_after_order_itemmeta', function( $passed_item_id, $passed_item, $passed_product ) use ( $formatted_meta_data, $key, $item ) {

									// The passed_ prefixed variables are the ones available from woocommerce_after_order_itemmeta, the use based variables are passed from this function (order_line_item_formatted_meta_data) itself, we then can compare the 2 to ensure it only gets displayed once (see condition and comment below) and then use the existing $formatted_meta_data instead of recreating it

									if ( $passed_item == $item ) { // Ensures that it only gets added to the line item it should be on, without this if there are 2+ order line items it will output the first and second order line item return days threshold on the second order line item and so on

										echo '<table cellspacing="0" class="display_meta" style="margin-top: 0;"><tbody><tr><th>' . wp_kses_post( $formatted_meta_data[$key]->display_key ) . esc_html__( ':', 'wcrp-rental-products' ) . '</th><td>' . wp_kses_post( $formatted_meta_data[$key]->display_value ) . '</td></tr></tbody></table>';

									}

								}, 0, 3 ); // 0 priority so before order_line_item_actions() and any other custom stuff

								unset( $formatted_meta_data[$key] ); // Unset occurs after the anonymous function above as that requires $formatted_meta_data[$key] to still be available

							}

						}

					} elseif ( 'wcrp_rental_products_in_person_pick_up_date' == $meta->key ) {

						unset( $formatted_meta_data[$key] ); // Not shown as always same as rent from date

					} elseif ( 'wcrp_rental_products_in_person_pick_up_fee' == $meta->key ) {

						unset( $formatted_meta_data[$key] ); // Not shown as added to item total

					} elseif ( 'wcrp_rental_products_in_person_return_date' == $meta->key ) {

						if ( $meta->value == $item->get_meta( 'wcrp_rental_products_rent_to' ) ) {

							unset( $formatted_meta_data[$key] ); // Not shown if return date is same as rent to date

						}

					} elseif ( 'wcrp_rental_products_in_person_return_date_type' == $meta->key ) {

						unset( $formatted_meta_data[$key] ); // Not shown as just used for availability checks

					} elseif ( 'wcrp_rental_products_in_person_return_fee' == $meta->key ) {

						unset( $formatted_meta_data[$key] ); // Not shown as added to item total

					}

				}

			}

			return $formatted_meta_data;

		}

		public function order_line_item_rental_purchase_overrides( $product, $order_item ) {

			// This ensures that order items that use rental or purchase overrides return the correct data, this was added as we found that using the recalculate taxes button on orders calls the woocommerce_calc_line_taxes AJAX, this uses get_tax_status() which is off of WC_Order_Item_Product's $this->get_product() which just uses wc_get_product(), so was getting the tax status/class from the original product not overridden

			if ( !empty( $order_item->get_meta( 'wcrp_rental_products_rent_from' ) ) || !empty( $order_item->get_meta( 'wcrp_rental_products_cancelled' ) ) ) {

				// If a rental or cancelled rental, latter condition above is used because rent_from doesn't exist for cancelled rentals, however the totals of a cancelled rental should still be part of tax recalculation

				if ( !empty( $product ) ) { // Stops uncaught error: call to a member function get_id(), etc on bool if product has been deleted

					$product_id = $product->get_id();
					$product_type = $product->get_type();

					if ( 'variation' == $product_type ) {

						$parent_product_id = $product->get_parent_id();

					} else {

						$parent_product_id = false;

					}

					$rental_purchase_rental_tax_override = get_post_meta( ( 'variation' !== $product_type ? $product_id : $parent_product_id ), '_wcrp_rental_products_rental_purchase_rental_tax_override', true );
					$rental_purchase_rental_shipping_override = get_post_meta( ( 'variation' !== $product_type ? $product_id : $parent_product_id ), '_wcrp_rental_products_rental_purchase_rental_shipping_override', true );

					// In the scenario where an order exists with rentals, the tax status/class and shipping class id is set as per below, if the product's overrides meta is updated since the order to be different, then recalculating taxes still keeps the existing data, not the updated, which is correct as it wants to remain as set at the point of order, even though the code below looks like it would change it in this scenario, potentially because on order creation it populates the order quantity, subtotal, total, etc of each order item from the data below and once populated recalculating taxes works off the data of the order item fields not completely recalculating them

					if ( 'yes' == $rental_purchase_rental_tax_override ) {

						$tax_status_override = get_post_meta( ( 'variation' !== $product_type ? $product_id : $parent_product_id ), '_wcrp_rental_products_rental_purchase_rental_tax_override_status', true );
						$product->set_tax_status( $tax_status_override );

						$tax_class_override = get_post_meta( ( 'variation' !== $product_type ? $product_id : $parent_product_id ), '_wcrp_rental_products_rental_purchase_rental_tax_override_class', true );
						$product->set_tax_class( $tax_class_override );

					}

					if ( 'yes' == $rental_purchase_rental_shipping_override ) {

						$shipping_class_override = get_post_meta( ( 'variation' !== $product_type ? $product_id : $parent_product_id ), '_wcrp_rental_products_rental_purchase_rental_shipping_override_class', true );
						$product->set_shipping_class_id( $shipping_class_override );

					}

				}

			}

			return $product;

		}

		public function order_line_item_actions( $item_id, $item, $product ) {

			$rent_from = wc_get_order_item_meta( $item_id, 'wcrp_rental_products_rent_from', true );
			$rent_to = wc_get_order_item_meta( $item_id, 'wcrp_rental_products_rent_to', true );

			// No check for if the product is a rental because the product might have been a rental but no longer is

			if ( !empty( $rent_from ) && !empty( $rent_to ) ) { // This stops non rental products and cancelled rentals from having rental order line item actions (cancelled as no rent from/to meta would exist)

				$returned = wc_get_order_item_meta( $item_id, 'wcrp_rental_products_returned', true );

				?>

				<div class="wcrp-rental-products-order-line-item-actions" data-order-item-id="<?php echo esc_html( $item_id ); ?>">
					<?php if ( 'yes' !== $returned ) { ?>
						<a class="wcrp-rental-products-order-line-item-action-mark-as-returned button button-small button-primary"><?php esc_html_e( 'Mark as returned', 'wcrp-rental-products' ); ?></a>
						<a class="wcrp-rental-products-order-line-item-action-change-rental button button-small"><?php esc_html_e( 'Change rental', 'wcrp-rental-products' ); ?></a>
						<a class="wcrp-rental-products-order-line-item-action-cancel-rental button button-small"><?php esc_html_e( 'Cancel rental', 'wcrp-rental-products' ); ?></a>
					<?php } ?>
				</div>

				<?php

			}

		}

		public function order_line_item_actions_save() {

			$return = 'error';

			if ( isset( $_POST['nonce'] ) ) {

				if ( wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'wcrp_rental_products_order_line_item_actions_save' ) ) {

					if ( isset( $_POST['order_item_id'] ) && isset( $_POST['type'] ) ) {

						$order_item_id = sanitize_text_field( $_POST['order_item_id'] );
						$type = sanitize_text_field( $_POST['type'] );

						if ( 'mark_as_returned' == $type ) {

							$mark_as_returned = $this->order_line_item_mark_as_returned( $order_item_id );

							if ( false !== $mark_as_returned ) {

								$return = $mark_as_returned; // $mark_as_returned contains the updated order notes used to update the order notes immediately

							}

						} elseif ( 'cancel_rental' == $type ) {

							$cancel_rental = $this->order_line_item_cancel_rental( $order_item_id, false );

							if ( false !== $cancel_rental ) {

								$return = $cancel_rental; // $cancel_rental contains the updated order notes used to update the order notes immediately

							}

						}

					}

				}

			}

			echo wp_kses_post( $return );

			exit;

		}

		public function order_line_item_delete( $order_item_id ) {

			global $wpdb;

			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM `{$wpdb->prefix}wcrp_rental_products_rentals` WHERE `order_item_id` = %d;",
					$order_item_id
				)
			);

			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM `{$wpdb->prefix}wcrp_rental_products_rentals_archive` WHERE `order_item_id` = %d;",
					$order_item_id
				)
			);

		}

		public function order_line_item_add_line_buttons( $order ) {

			require_once ABSPATH . 'wp-admin/includes/plugin.php';

			if ( is_plugin_active( 'woocommerce-product-addons/woocommerce-product-addons.php' ) ) {

				$addons = 'yes';

			} else {

				$addons = 'no';

			}

			if ( is_plugin_active( 'deposits-partial-payments-for-woocommerce/start.php' ) || is_plugin_active( 'deposits-partial-payments-for-woocommerce-pro/start.php' ) ) {

				$deposits_partial_payments = 'yes';

			} else {

				$deposits_partial_payments = 'no';

			}

			$new_order = ( 'auto-draft' == $order->get_status() ? 'yes' : 'no' );

			echo '<button type="button" class="button" id="wcrp-rental-products-add-rental-products" data-addons="' . esc_attr( $addons ) . '" data-deposits-partial-payments="' . esc_attr( $deposits_partial_payments ) . '" data-new-order="' . esc_attr( $new_order ) . '" data-order-id="' . esc_attr( $order->get_id() ) . '" data-url="' . esc_url( get_site_url() ) . '">' . esc_html__( 'Add rental product(s)', 'wcrp-rental-products' ) . '</button>';

		}

		public static function order_line_item_mark_as_returned( $order_item_id ) {

			$return = false;

			$order_item = new WC_Order_Item_Product( $order_item_id );

			if ( !empty( $order_item ) ) {

				$order = $order_item->get_order();

				if ( !empty( $order ) ) {

					$order_item_product = $order_item->get_product();
					$order_item_product_name = $order_item->get_name();

					if ( !empty( $order_item_product ) ) { // This is not wrapped around all the code below as a product related to an order item might have been deleted, if it was wrapped around the entire code it would display an error if a product has been deleted

						$order_item_product_sku = $order_item_product->get_sku(); // SKU can only be got if product has not been deleted, ternary condition in order note added below checks for this before including

					}

					wc_add_order_item_meta( $order_item_id, 'wcrp_rental_products_returned', 'yes', true );

					$order->add_order_note(
						esc_html__( 'Rental marked as returned:', 'wcrp-rental-products' ) . ' ' . $order_item_product_name . ( !empty( $order_item_product_sku ) ? ' ' . esc_html__( '(', 'wcrp-rental-products' ) . $order_item_product_sku . esc_html__( ')', 'wcrp-rental-products' ) : '' ) . esc_html__( '.', 'wcrp-rental-products' ),
						false,
						true
					);

					$order->save();

					ob_start();
					$notes = wc_get_order_notes( array( 'order_id' => $order->get_id() ) );
					include WC_ABSPATH . 'includes/admin/meta-boxes/views/html-order-notes.php';
					$return = ob_get_clean(); // Updated order notes returned, JS then empties existing notes and replaces with this so the added note above is immediately shown before page refresh

				}

			}

			return $return;

		}

		public static function order_line_item_cancel_rental( $order_item_id, $skip_database_delete = false ) {

			global $wpdb;

			$return = false;

			$order_item = new WC_Order_Item_Product( $order_item_id );

			if ( !empty( $order_item ) ) {

				$order = $order_item->get_order();

				if ( !empty( $order ) ) {

					$order_item_product = $order_item->get_product();
					$order_item_product_name = $order_item->get_name();

					if ( !empty( $order_item_product ) ) { // This is not wrapped around all the code below as a product related to an order item might have been deleted, if it was wrapped around the entire code it would display an error if a product has been deleted

						$order_item_product_sku = $order_item_product->get_sku(); // SKU can only be got if product has not been deleted, ternary condition in order note added below checks for this before including

					}

					// In the wpdb query below we do not also delete from the archive table as that only contains rentals that have been marked as returned and therefore the cancel rental option is not available for individual order item actions, however it is possible for rentals in an order that have been marked as returned to be cancelled in a scenario where they've been marked as returned but then the order status is changed to one that cancels all rentals in the order, for these scenarios the removal and call of this function is done via WCRP_Order_Save::rentals_remove() which removes the entire order from both rentals tables (and therefore all order item id rows for that order), because of this in that scenario there is no need for the wpdb query below to occur, hence why it is skipped

					if ( false == $skip_database_delete ) {

						$wpdb->query(
							$wpdb->prepare(
								"DELETE FROM `{$wpdb->prefix}wcrp_rental_products_rentals` WHERE `order_item_id` = %d;",
								$order_item_id
							)
						);

					}

					wc_delete_order_item_meta( $order_item_id, 'wcrp_rental_products_rent_from' );
					wc_delete_order_item_meta( $order_item_id, 'wcrp_rental_products_rent_to' );
					wc_delete_order_item_meta( $order_item_id, 'wcrp_rental_products_return_days_threshold' );
					wc_delete_order_item_meta( $order_item_id, 'wcrp_rental_products_returned' );
					wc_delete_order_item_meta( $order_item_id, 'wcrp_rental_products_in_person_pick_up_return' );
					wc_delete_order_item_meta( $order_item_id, 'wcrp_rental_products_in_person_pick_up_date' );
					wc_delete_order_item_meta( $order_item_id, 'wcrp_rental_products_in_person_pick_up_time' );
					wc_delete_order_item_meta( $order_item_id, 'wcrp_rental_products_in_person_pick_up_fee' );
					wc_delete_order_item_meta( $order_item_id, 'wcrp_rental_products_in_person_return_date' );
					wc_delete_order_item_meta( $order_item_id, 'wcrp_rental_products_in_person_return_date_type' );
					wc_delete_order_item_meta( $order_item_id, 'wcrp_rental_products_in_person_return_time' );
					wc_delete_order_item_meta( $order_item_id, 'wcrp_rental_products_in_person_return_fee' );

					wc_add_order_item_meta( $order_item_id, 'wcrp_rental_products_cancelled', 'yes', true );

					$order->add_order_note(
						esc_html__( 'Rental cancelled:', 'wcrp-rental-products' ) . ' ' . $order_item_product_name . ( !empty( $order_item_product_sku ) ? ' ' . esc_html__( '(', 'wcrp-rental-products' ) . $order_item_product_sku . esc_html__( ')', 'wcrp-rental-products' ) : '' ) . esc_html__( '.', 'wcrp-rental-products' ),
						false,
						true
					);

					$order->save();

					ob_start();
					$notes = wc_get_order_notes( array( 'order_id' => $order->get_id() ) );
					include WC_ABSPATH . 'includes/admin/meta-boxes/views/html-order-notes.php';
					$return = ob_get_clean(); // Updated order notes returned, JS then empties existing notes and replaces with this so the added note above is immediately shown before page refresh

				}

			}

			return $return;

		}

	}

}
