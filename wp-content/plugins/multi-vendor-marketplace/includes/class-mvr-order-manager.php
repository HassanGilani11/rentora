<?php
/**
 * Order Manager.
 *
 * @package Multi-Vendor-Marketplace.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Order_Manager' ) ) {

	/**
	 * Manage order activities.
	 *
	 * @class MVR_Order_Manager
	 * @package Class
	 */
	class MVR_Order_Manager {

		/**
		 * Update Customer Data
		 *
		 * @since 1.0.0
		 * @var Array
		 */
		public static $vendor_orders = array();

		/**
		 * Init MVR_Order_Manager.
		 */
		public static function init() {
			$allowed_statuses = get_option( 'mvr_settings_commission_order_status', array( 'processing', 'completed' ) );

			if ( mvr_check_is_array( $allowed_statuses ) ) {
				foreach ( $allowed_statuses as $status ) {
					add_action( "woocommerce_order_status_{$status}", __CLASS__ . '::update_commission_success', 10, 2 );
				}

				$other_statuses = array_diff( array_keys( mvr_get_success_order_statuses() ), $allowed_statuses );

				if ( mvr_check_is_array( $other_statuses ) ) {
					foreach ( $other_statuses as $status ) {
						add_action( "woocommerce_order_status_{$status}", __CLASS__ . '::update_commission_processing', 10, 2 );
					}
				}
			}

			$failed_order_statuses = array_keys( mvr_get_failed_order_statuses() );

			if ( mvr_check_is_array( $failed_order_statuses ) ) {
				foreach ( $failed_order_statuses as $status ) {
					add_action( "woocommerce_order_status_{$status}", __CLASS__ . '::update_commission_failed', 10, 2 );
				}
			}

			// Order Handler.
			add_action( 'woocommerce_store_api_checkout_order_processed', __CLASS__ . '::create_vendor_order' );
			add_action( 'woocommerce_checkout_update_order_meta', __CLASS__ . '::create_vendor_order' );
			// Update Cart data to cart item.
			add_action( 'woocommerce_checkout_order_processed', __CLASS__ . '::process_checkout', 100, 2 );
			// Save edit order item.
			add_action( 'woocommerce_before_save_order_item', __CLASS__ . '::save_order_item' );
			add_action( 'woocommerce_checkout_create_order_line_item', __CLASS__ . '::update_order_item_meta', 10, 4 );
			add_filter( 'woocommerce_hidden_order_itemmeta', __CLASS__ . '::hide_order_item_meta' );
			add_action( 'woocommerce_order_status_changed', __CLASS__ . '::update_vendor_order_status', 10, 3 );
			add_action( 'before_delete_post', __CLASS__ . '::before_delete_order' );
		}

		/**
		 * Update Customer Data
		 *
		 * @since 1.0.0
		 * @param MVR_Vendor $vendor_obj Vendor Object.
		 * @param WC_Order   $order_obj Order Object.
		 *
		 * @return Integer
		 * */
		public static function update_customer_data( $vendor_obj, $order_obj ) {
			if ( ! is_a( $order_obj, 'WC_Order' ) || ! mvr_is_vendor( $vendor_obj ) ) {
				return;
			}

			$customer_args = array(
				'vendor_id'  => $vendor_obj->get_id(),
				'user_id'    => $order_obj->get_customer_id(),
				'first_name' => $order_obj->get_billing_first_name(),
				'last_name'  => $order_obj->get_billing_last_name(),
				'company'    => $order_obj->get_billing_company(),
				'address_1'  => $order_obj->get_billing_address_1(),
				'address_2'  => $order_obj->get_billing_address_2(),
				'city'       => $order_obj->get_billing_city(),
				'state'      => $order_obj->get_billing_state(),
				'country'    => $order_obj->get_billing_country(),
				'postcode'   => $order_obj->get_billing_postcode(),
				'phone'      => $order_obj->get_billing_phone(),
				'email'      => $order_obj->get_billing_email(),
			);

			$customers_obj = mvr_get_customers(
				array(
					'vendor_id' => $vendor_obj->get_id(),
					'user_id'   => $order_obj->get_customer_id(),
					'email'     => $order_obj->get_billing_email(),
					'limit'     => 1,
				)
			);

			if ( $customers_obj->has_customer ) {
				$customer_obj = current( $customers_obj->customers );
			} else {
				$customer_obj  = new MVR_Customer();
				$customer_args = array_merge(
					$customer_args,
					array(
						'source_id'   => $order_obj->get_id(),
						'source_from' => 'order',
						'created_via' => 'order',
					)
				);
			}

			$customer_obj->set_props( $customer_args );
			$customer_obj->save();

			return $customer_obj->get_id();
		}

		/**
		 * Update Order Data
		 *
		 * @since 1.0.0
		 * @param MVR_Vendor $vendor_obj Vendor Object.
		 * @param WC_Order   $order_obj Order Object.
		 * @param Integer    $customer_id Customer ID.
		 *
		 * @return Integer
		 * */
		public static function update_vendor_order( $vendor_obj, $order_obj, $customer_id ) {
			if ( ! is_a( $order_obj, 'WC_Order' ) || ! mvr_is_vendor( $vendor_obj ) ) {
				return;
			}

			$customer_obj = mvr_get_customer( $customer_id );

			if ( ! mvr_is_customer( $customer_obj ) ) {
				return;
			}

			// Update Vendor Order.
			$orders_obj = mvr_get_orders(
				array(
					'vendor_id' => $vendor_obj->get_id(),
					'order_id'  => $order_obj->get_id(),
					'limit'     => 1,
				)
			);

			if ( $orders_obj->has_order ) {
				$mvr_order_obj = current( $orders_obj->orders );
			} else {
				$mvr_order_obj = new MVR_Order();
			}

			$mvr_order_obj->set_props(
				array(
					'vendor_id'   => $vendor_obj->get_id(),
					'order_id'    => $order_obj->get_id(),
					'user_id'     => $order_obj->get_customer_id(),
					'email'       => $customer_obj->get_email(),
					'mvr_user_id' => $customer_obj->get_id(),
					'created_via' => 'order',
					'currency'    => $order_obj->get_currency(),
				)
			);
			$mvr_order_obj->save();
			$mvr_order_obj->update_status( $order_obj->get_status() );

			/**
			 * Create New Order Hook
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_create_new_order', $mvr_order_obj->get_id(), $vendor_obj );

			return $mvr_order_obj->get_id();
		}

		/**
		 * Create Vendor Order
		 *
		 * @since 1.0.0
		 * @param WC_Order|Integer $order_obj Order Object.
		 */
		public static function create_vendor_order( $order_obj ) {
			$order_obj = wc_get_order( $order_obj );

			if ( ! is_a( $order_obj, 'WC_Order' ) || isset( self::$vendor_orders[ $order_obj->get_id() ] ) ) {
				return;
			}

			self::$vendor_orders[ $order_obj->get_id() ] = true;

			foreach ( $order_obj->get_items() as $item_id => $item ) {
				$product_obj = wc_get_product( $item->get_product_id() );

				if ( ! is_a( $product_obj, 'WC_Product' ) ) {
					continue;
				}

				$vendor_id = $product_obj->get_meta( '_mvr_vendor', true );

				if ( empty( $vendor_id ) ) {
					continue;
				}

				$vendor_obj = mvr_get_vendor( $vendor_id );

				if ( ! mvr_is_vendor( $vendor_obj ) ) {
					continue;
				}

				$customer_id  = self::update_customer_data( $vendor_obj, $order_obj );
				$mvr_order_id = self::update_vendor_order( $vendor_obj, $order_obj, $customer_id );
			}
		}


		/**
		 * Update Order item meta.
		 *
		 * @since 1.0.0
		 * @param WC_Order_Item $item Order Item Object.
		 */
		public static function save_order_item( $item ) {
			if ( ! is_a( $item, 'WC_Order_Item' ) ) {
				return;
			}

			$applied_coupons = $item->get_meta( '_mvr_applied_coupons', true );

			if ( ! mvr_check_is_array( $applied_coupons ) ) {
				return;
			}

			if ( isset( $applied_coupons['per_qty_discounts'] ) ) {
				if ( isset( $applied_coupons['per_qty_discount']['admin_discount'] ) ) {
					$applied_coupons['admin_discount'] = $applied_coupons['per_qty_discount']['admin_discount'] * $item->get_quantity();
				}

				if ( isset( $applied_coupons['per_qty_discount']['vendor_discount'] ) ) {
					$applied_coupons['vendor_discount'] = $applied_coupons['per_qty_discount']['vendor_discount'] * $item->get_quantity();
				}
			}

			$item->update_meta_data( '_mvr_applied_coupons', $applied_coupons );
			$item->save();
		}

		/**
		 * Update Coupons to order meta.
		 *
		 * @since 1.0.0
		 * @param Integer $order_id Order ID.
		 * @param Array   $posted_data Posted Checkout Data.
		 */
		public static function process_checkout( $order_id, $posted_data ) {
			$order_obj = wc_get_order( $order_id );

			if ( ! $order_obj || ! mvr_cart_contains_vendor_product() || ! mvr_check_is_array( WC()->cart->get_coupons() ) || ! mvr_check_is_array( WC()->cart->mvr_carts ) ) {
				return;
			}

			$order_obj->update_meta_data( '_mvr_product_discounts', WC()->cart->mvr_carts );
			$order_obj->save_meta_data();
		}

		/**
		 * Create Vendor Commission.
		 *
		 * @since 1.0.0
		 * @param Integer  $order_id Order ID.
		 * @param WC_Order $order_obj Order Object.
		 */
		public static function update_commission_success( $order_id, $order_obj ) {
			if ( ! is_a( $order_obj, 'WC_Order' ) ) {
				$order_obj = wc_get_order( $order_id );

				if ( ! is_a( $order_obj, 'WC_Order' ) ) {
					return;
				}
			}

			$vendors_arg = array();
			$total_tax   = 0;

			foreach ( $order_obj->get_items() as $item_id => $item ) {
				$product = wc_get_product( $item->get_product_id() );

				if ( ! is_a( $product, 'WC_Product' ) ) {
					continue;
				}

				$vendor_id = $product->get_meta( '_mvr_vendor', true );

				if ( empty( $vendor_id ) ) {
					continue;
				}

				$vendor_obj = mvr_get_vendor( $vendor_id );

				if ( ! mvr_is_vendor( $vendor_obj ) ) {
					continue;
				}

				$taxes = $item->get_taxes();

				foreach ( $taxes['total'] as $tax_rate_id => $tax ) {
					$total_tax += (float) $tax;
				}

				$applied_coupons                                = $item->get_meta( '_mvr_applied_coupons', true );
				$commission                                     = (float) $vendor_obj->get_calculate_commission( $item );
				$vendor_amount                                  = (float) $vendor_obj->get_calculate_vendor_amount( $item );
				$vendor_discount                                = isset( $applied_coupons['vendor_discount'] ) ? (float) $applied_coupons['vendor_discount'] : 0;
				$admin_discount                                 = isset( $applied_coupons['admin_discount'] ) ? (float) $applied_coupons['admin_discount'] : 0;
				$vendors_arg[ $vendor_id ]['commission']        = isset( $vendors_arg[ $vendor_id ]['commission'] ) ? (float) $vendors_arg[ $vendor_id ]['commission'] + $commission : $commission;
				$vendors_arg[ $vendor_id ]['vendor_amount']     = isset( $vendors_arg[ $vendor_id ]['vendor_amount'] ) ? (float) $vendors_arg[ $vendor_id ]['vendor_amount'] + $vendor_amount : $vendor_amount;
				$vendors_arg[ $vendor_id ]['vendor_discount']   = isset( $vendors_arg[ $vendor_id ]['vendor_discount'] ) ? (float) $vendors_arg[ $vendor_id ]['vendor_discount'] + $vendor_discount : $vendor_discount;
				$vendors_arg[ $vendor_id ]['admin_discount']    = isset( $vendors_arg[ $vendor_id ]['admin_discount'] ) ? (float) $vendors_arg[ $vendor_id ]['admin_discount'] + $admin_discount : $admin_discount;
				$vendors_arg[ $vendor_id ]['items'][ $item_id ] = array(
					'item'            => $item,
					'commission'      => $commission,
					'vendor_amount'   => $vendor_amount,
					'vendor_discount' => $vendor_discount,
					'admin_discount'  => $admin_discount,
					'shipping'        => '',
					'tax'             => $total_tax,
					'applied_coupons' => $applied_coupons,
				);
			}

			if ( mvr_check_is_array( $vendors_arg ) ) {
				foreach ( $vendors_arg as $vendor_id => $args ) {
					$vendor_obj = mvr_get_vendor( $vendor_id );

					if ( ! mvr_is_vendor( $vendor_obj ) ) {
						continue;
					}

					$commission_id  = self::update_commission_data( $vendor_obj, $order_obj, $args );
					$transaction_id = self::update_transaction_data( $commission_id, $vendor_obj, $order_obj, $args );
				}
			}

			return true;
		}

		/**
		 * Revoke Vendor Commission.
		 *
		 * @since 1.0.0
		 * @param Integer  $order_id Order ID.
		 * @param WC_Order $order_obj Order Object.
		 */
		public static function update_commission_processing( $order_id, $order_obj ) {
			if ( ! is_a( $order_obj, 'WC_Order' ) ) {
				$order_obj = wc_get_order( $order_id );

				if ( ! is_a( $order_obj, 'WC_Order' ) ) {
					return;
				}
			}

			$commissions_obj = mvr_get_commissions(
				array(
					'source_id'   => $order_obj->get_id(),
					'source_from' => 'order',
				)
			);

			if ( $commissions_obj->has_commission ) {
				foreach ( $commissions_obj->commissions as $commission_obj ) {
					if ( ! mvr_is_commission( $commission_obj ) ) {
						continue;
					}

					$commission_obj->update_status( 'pending' );
				}
			}

			$transactions_obj = mvr_get_transactions(
				array(
					'status'      => array( 'processing', 'completed' ),
					'source_id'   => $order_obj->get_id(),
					'source_from' => 'order',
				)
			);

			if ( $transactions_obj->has_transaction ) {
				foreach ( $transactions_obj->transactions as $transaction_obj ) {
					if ( ! mvr_is_transaction( $transaction_obj ) ) {
						continue;
					}

					if ( mvr_is_vendor( $transaction_obj->get_vendor() ) ) {
						if ( 'completed' === $transaction_obj->get_status() ) {
							if ( $transaction_obj->get_vendor()->get_locked_amount() < $transaction_obj->get_amount() ) {
								$amount = $transaction_obj->get_vendor()->get_amount() - $transaction_obj->get_amount();
								$transaction_obj->get_vendor()->set_amount( $amount );
							} else {
								$amount = $transaction_obj->get_vendor()->get_locked_amount() - $transaction_obj->get_amount();
								$transaction_obj->get_vendor()->set_locked_amount( $amount );
							}
						}

						$transaction_obj->get_vendor()->save();
					}

					$transaction_obj->update_status( 'processing' );
				}
			}
		}

		/**
		 * Revoke Vendor Commission.
		 *
		 * @since 1.0.0
		 * @param Integer  $order_id Order ID.
		 * @param WC_Order $order_obj Order Object.
		 */
		public static function update_commission_failed( $order_id, $order_obj ) {
			$order_obj = wc_get_order( $order_id );

			if ( ! is_a( $order_obj, 'WC_Order' ) ) {
				return;
			}

			$commissions_obj = mvr_get_commissions(
				array(
					'source_id'   => $order_obj->get_id(),
					'source_from' => 'order',
				)
			);

			if ( $commissions_obj->commissions ) {
				foreach ( $commissions_obj->commissions as $commission_obj ) {
					if ( ! mvr_is_commission( $commission_obj ) ) {
						continue;
					}

					$commission_obj->update_status( 'failed' );
				}
			}

			$transactions_obj = mvr_get_transactions(
				array(
					'status'      => array( 'processing', 'completed' ),
					'source_id'   => $order_obj->get_id(),
					'source_from' => 'order',
				)
			);

			if ( $transactions_obj->has_transaction ) {
				foreach ( $transactions_obj->transactions as $transaction_obj ) {
					if ( ! mvr_is_transaction( $transaction_obj ) ) {
						continue;
					}

					$vendor_obj = mvr_get_vendor( $transaction_obj->get_vendor_id() );

					if ( mvr_is_vendor( $vendor_obj ) ) {
						if ( $vendor_obj->get_locked_amount() < $transaction_obj->get_amount() ) {
							$amount = $vendor_obj->get_amount() - $transaction_obj->get_amount();

							$vendor_obj->set_amount( $amount );
						} else {
							$amount = $vendor_obj->get_locked_amount() - $transaction_obj->get_amount();

							$vendor_obj->set_locked_amount( $amount );
						}

						$vendor_obj->save();
					}

					$transaction_obj->update_status( 'failed' );
				}
			}
		}

		/**
		 * Update Commission Item Meta
		 *
		 * @since 1.0.0
		 * @param MVR_Vendor $vendor_obj Vendor Object.
		 * @param WC_Order   $order_obj Order Object.
		 * @param Array      $vendor_data Vendor Data.
		 *
		 * @return Integer
		 * */
		public static function update_commission_data( $vendor_obj, $order_obj, $vendor_data ) {
			if ( ! is_a( $order_obj, 'WC_Order' ) || ! mvr_is_vendor( $vendor_obj ) ) {
				return;
			}

			$commissions_obj = mvr_get_commissions(
				array(
					'vendor_id'   => $vendor_obj->get_id(),
					'source_id'   => $order_obj->get_id(),
					'source_from' => 'order',
				)
			);

			if ( $commissions_obj->has_commission ) {
				$commission_obj = current( $commissions_obj->commissions );
			} else {
				$commission_obj = new MVR_Commission( 0 );
				$commission_obj->set_source_id( $order_obj->get_id() );
				$commission_obj->set_source_from( 'order' );
				$commission_obj->set_vendor_id( $vendor_obj->get_id() );
			}

			$commission_obj->set_props(
				array(
					'products'      => $vendor_data['items'],
					'amount'        => $vendor_data['commission'],
					'vendor_amount' => $vendor_data['vendor_amount'],
					'settings'      => $vendor_obj->get_commission_settings(),
					'created_via'   => 'order',
					'currency'      => $order_obj->get_currency(),
				)
			);
			$commission_obj->save();

			mvr_add_vendor_id_to_order( $order_obj, $vendor_obj->get_id() );

			$order_obj->update_meta_data( 'mvr_commission_updated', true );
			$order_obj->save();

			$mvr_orders_obj = mvr_get_orders(
				array(
					'vendor_id' => $vendor_obj->get_id(),
					'order_id'  => $order_obj->get_id(),
					'limit'     => 1,
				)
			);

			if ( ! $mvr_orders_obj->has_order ) {
				$customer_id   = self::update_customer_data( $vendor_obj, $order_obj );
				$mvr_order_id  = self::update_vendor_order( $order_obj, $vendor_obj, $customer_id );
				$mvr_order_obj = mvr_get_order( $mvr_order_id );
			} else {
				$mvr_order_obj = current( $mvr_orders_obj->orders );
			}

			if ( mvr_is_order( $mvr_order_obj ) ) {
				$mvr_order_obj->set_commission_id( $commission_obj->get_id() );
				$mvr_order_obj->save();
			}

			return $commission_obj->get_id();
		}

		/**
		 * Update Transaction Item Meta
		 *
		 * @since 1.0.0
		 * @param Integer    $commission_id Commmission ID.
		 * @param MVR_Vendor $vendor_obj Vendor Object.
		 * @param WC_Order   $order_obj Order Object.
		 * @param Array      $vendor_data Vendor Data.
		 * */
		public static function update_transaction_data( $commission_id, $vendor_obj, $order_obj, $vendor_data ) {
			if ( ! is_a( $order_obj, 'WC_Order' ) || ! mvr_is_vendor( $vendor_obj ) ) {
				return;
			}

			$commission_obj     = mvr_get_commission( $commission_id );
			$available_withdraw = get_option( 'mvr_settings_withdraw_available_after_days', '7' );

			if ( ! empty( $available_withdraw ) ) {
				$withdraw_date_obj = mvr_get_datetime( '+' . $available_withdraw . ' days' );
			} else {
				$withdraw_date_obj = mvr_get_datetime( 'now' );
			}

			$transactions_obj = mvr_get_transactions(
				array(
					'vendor_id'   => $vendor_obj->get_id(),
					'source_id'   => $order_obj->get_id(),
					'source_from' => 'order',
					'limit'       => 1,
				)
			);

			if ( $transactions_obj->has_transaction ) {
				$transaction_obj = current( $transactions_obj->transactions );

				if ( 'completed' === $transaction_obj->get_status() ) {
					$amount = $vendor_obj->get_amount() - $transaction_obj->get_amount();
					$vendor_obj->set_amount( $amount );
					$vendor_obj->save();
				}
			} else {
				$transaction_obj = new MVR_Transaction();
			}

			$transaction_obj->set_props(
				array(
					'amount'        => $vendor_data['vendor_amount'],
					'source_id'     => $order_obj->get_id(),
					'source_from'   => 'order',
					'vendor_id'     => $vendor_obj->get_id(),
					'created_via'   => 'order',
					'currency'      => $order_obj->get_currency(),
					'withdraw_date' => $withdraw_date_obj->date( 'Y-m-d H:i:s' ),
					'type'          => 'credit',
				)
			);
			$transaction_obj->save();

			// Update Vendor Amount.
			$withdraw_available_time = strtotime( $transaction_obj->get_withdraw_date() );
			$current_time            = mvr_get_current_time();

			if ( $withdraw_available_time <= $current_time ) {
				$amount = $vendor_obj->get_amount() + $transaction_obj->get_amount();
				$vendor_obj->set_amount( $amount );
				$transaction_obj->update_status( 'completed' );

				if ( mvr_is_commission( $commission_obj ) ) {
					$commission_obj->update_status( 'paid' );
				}
			} else {
				$amount = $vendor_obj->get_locked_amount() + $transaction_obj->get_amount();
				$vendor_obj->set_locked_amount( $amount );
				$transaction_obj->update_status( 'processing' );
			}

			$vendor_obj->save();

			return $transaction_obj->get_id();
		}

		/**
		 * Update Order Item Meta
		 *
		 * @since 1.0.0
		 * @param Object   $item Order Item Object.
		 * @param String   $cart_item_key Cart Item Key.
		 * @param Array    $values $item Values.
		 * @param WC_Order $order_obj Order Object.
		 * */
		public static function update_order_item_meta( $item, $cart_item_key, $values, $order_obj ) {
			if ( ! isset( $values['mvr_vendor_id'] ) ) {
				return;
			}

			$product_obj = isset( $values['product_id'] ) ? wc_get_product( $values['product_id'] ) : false;

			if ( ! $product_obj ) {
				return;
			}

			$vendor_id = $values['mvr_vendor_id'];

			if ( empty( $vendor_id ) ) {
				return;
			}

			$vendor_obj = mvr_get_vendor( $vendor_id );

			if ( ! $vendor_obj ) {
				return;
			}

			$product_discounts = WC()->cart->mvr_carts;
			$applied_coupons   = isset( $product_discounts[ $cart_item_key ] ) ? $product_discounts[ $cart_item_key ] : array();
			$commission        = (float) $vendor_obj->get_calculate_commission( $item );

			$item->add_meta_data( '_mvr_applied_coupons', $applied_coupons, true );
			$item->add_meta_data( '_mvr_vendor_commission', $commission, true );
			$item->add_meta_data( '_mvr_vendor_id', $item['mvr_vendor'], true );
			$item->add_meta_data( esc_html__( 'Sold by', 'multi-vendor-marketplace' ), '<a href="' . $vendor_obj->get_shop_url() . '">' . $vendor_obj->get_shop_name() . '</a>', true );
			$item->save();

			if ( ! is_a( $order_obj, 'WC_Order' ) ) {
				return;
			}

			mvr_add_vendor_id_to_order( $order_obj, $vendor_id );
		}

		/**
		 * Hidden Custom Order item meta.
		 *
		 * @since 1.0.0
		 * @param Array $hide_order_item_meta Hide Order Item meta.
		 * @return Array
		 * */
		public static function hide_order_item_meta( $hide_order_item_meta ) {
			return array_merge( $hide_order_item_meta, array( '_mvr_vendor_id', '_mvr_vendor_commission' ) );
		}

		/**
		 * Update Vendor Order Status.
		 *
		 * @since 1.0.0
		 * @param Integer $order_id Order ID.
		 * @param String  $old_status Old Status.
		 * @param String  $new_status New Status.
		 * */
		public static function update_vendor_order_status( $order_id, $old_status, $new_status ) {
			if ( ! $order_id ) {
				return;
			}

			$mvr_orders = mvr_get_orders( array( 'order_id' => $order_id ) );

			if ( ! $mvr_orders->has_order ) {
				return;
			}

			foreach ( $mvr_orders->orders as $mvr_order ) {
				if ( wc_is_order_status( 'wc-' . $new_status ) ) {
					$mvr_order->update_status( $new_status );
				}
			}
		}

		/**
		 * Before Delete Order
		 *
		 * @since 1.0.0
		 * @param Integer $order_id Order ID.
		 * */
		public static function before_delete_order( $order_id ) {
			$post_type = get_post_type( $order_id );

			if ( 'shop_order' !== $post_type ) {
				return;
			}

			$mvr_orders_obj = mvr_get_orders( array( 'order_id' => $order_id ) );

			if ( ! $mvr_orders_obj->has_order ) {
				return;
			}

			foreach ( $mvr_orders_obj->orders as $mvr_order_obj ) {
				$mvr_order_obj->delete( true );
			}
		}
	}

	MVR_Order_Manager::init();
}
