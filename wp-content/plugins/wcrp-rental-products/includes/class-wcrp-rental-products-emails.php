<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Emails' ) ) {

	class WCRP_Rental_Products_Emails {

		public function __construct() {

			add_filter( 'woocommerce_email_classes', array( $this, 'classes' ) );
			add_action( 'init', array( $this, 'schedule_events' ) );
			add_action( 'wcrp_rental_products_emails_rental_return_reminders', array( $this, 'send_rental_return_reminders' ) );

		}

		public function classes( $emails ) {

			if ( !isset( $emails[ 'WCRP_Rental_Products_Email_Rental_Return_Reminder' ] ) ) {

				$emails[ 'WCRP_Rental_Products_Email_Rental_Return_Reminder' ] = include_once 'emails/class-wcrp-rental-products-email-rental-return-reminder.php';

			}

			return $emails;

		}

		public function schedule_events() {

			if ( false == wp_get_scheduled_event( 'wcrp_rental_products_emails_rental_return_reminders' ) ) {

				// The code here seems overkill, however this is the only method found that would allow an event to be scheduled at 00:30 tomorrow regardless of the timezone setting, it has been tested by setting the WordPress timezone to a GMT timezone city e.g. London, deleting the scheduled event and viewing the rescheduled event, then doing the same for a non-GMT timezone city such as Auckland, in both scenarios the event is scheduled at the correct date date/time, date being based on the timezone (could be one day lower/higher for non-GMT timezone)

				wp_schedule_event( wp_date( 'U', strtotime( gmdate( 'Y-m-d 00:30', strtotime( 'tomorrow' ) ) . ( get_option( 'gmt_offset' ) > 0 ? '-' : '+' ) . absint( get_option( 'gmt_offset' ) ) . ' hours' ) ), 'daily', 'wcrp_rental_products_emails_rental_return_reminders' );

			}

		}

		public function send_rental_return_reminders() {

			if ( isset( WC()->mailer()->get_emails()['WCRP_Rental_Products_Email_Rental_Return_Reminder'] ) ) {

				$email = WC()->mailer()->get_emails()['WCRP_Rental_Products_Email_Rental_Return_Reminder'];

				if ( !empty( $email ) ) {

					if ( $email->is_enabled() ) { // Only send, add meta, order note, etc if email enabled, the $email->trigger() also has this but only effects sending, this condition here ensures the meta/order notes do not get added if the email is disabled

						$send_once = $email->send_once;
						$days_before = $email->days_before;

						if ( !empty( $send_once ) && !empty( $days_before ) ) {

							$current_date = wp_date( 'Y-m-d' );

							$orders = wc_get_orders(
								array(
									'limit'		=> -1,
									'status'	=> array( 'wc-processing' ),
								)
							);

							if ( !empty( $orders ) ) {

								foreach ( $orders as $order ) {

									$order_id = $order->get_id();
									$order_items = $order->get_items();
									$order_emails_sent = $order->get_meta( '_wcrp_rental_products_emails_sent', true );
									$order_emails_sent = ( !empty( $order_emails_sent ) ? $order_emails_sent : array() );

									if ( 'yes' == $send_once && in_array( 'rental_return_reminder', $order_emails_sent ) ) {

										continue; // If email is set to send once and this order already has a sent rental return reminder then skip to the next order

									}

									if ( !empty( $order_items ) ) {

										foreach ( $order_items as $order_item ) {

											$order_item_type = $order_item->get_type();

											if ( 'line_item' == $order_item_type ) { // Not a fee, coupon, etc

												$rent_from = $order_item->get_meta( 'wcrp_rental_products_rent_from' );

												if ( !empty( $rent_from ) ) { // Initially check if line item is a rental, this also excludes cancelled rentals as no rent from for cancelled rentals

													$returned = $order_item->get_meta( 'wcrp_rental_products_returned' );

													// If not already returned

													if ( 'yes' !== $returned ) {

														$rent_to = $order_item->get_meta( 'wcrp_rental_products_rent_to' );
														$return_days_threshold = $order_item->get_meta( 'wcrp_rental_products_return_days_threshold' );
														$rent_to_inc_return_days = gmdate( 'Y-m-d', strtotime( $rent_to . ' + ' . $return_days_threshold . ' days' ) );
														$rent_to_inc_return_days_minus_days_before = gmdate( 'Y-m-d', strtotime( $rent_to_inc_return_days . ' - ' . $days_before . ' days' ) );

														if ( $current_date == $rent_to_inc_return_days_minus_days_before ) {

															// Send email

															$email->trigger( $order_id );

															// Add order note

															// translators: %s: email title
															$order->add_order_note( sprintf( esc_html__( '%s sent to customer.', 'wcrp-rental-products' ), $email->title ) ); // This note is intentionally similar to when sending an invoice email e.g. order details manually sent to customer.

															// Add order emails sent meta (this can then be conditionally used later to stop further emails being sent)

															if ( !in_array( 'rental_return_reminder', $order_emails_sent ) ) { // If rental_return_reminder not already in the array (don't want duplicates)

																$order_emails_sent[] = 'rental_return_reminder';
																$order->update_meta_data( '_wcrp_rental_products_emails_sent', $order_emails_sent );

															}

															// Save order

															$order->save();

															// Break order items loop so no more emails sent for the rest of the order items once one email has been sent

															break;

														}

													}

												}

											}

										}

									}

								}

							}

						}

					}

				}

			}

		}

	}

}
