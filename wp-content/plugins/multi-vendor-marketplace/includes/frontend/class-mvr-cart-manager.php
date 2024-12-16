<?php
/**
 * Cart Manager.
 *
 * @package Multi-Vendor Functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Cart_Manager' ) ) {


	/**
	 * Manage Cart.
	 *
	 * @class MVR_Cart_Manager
	 * @package Class
	 */
	class MVR_Cart_Manager {

		/**
		 * Cached to check whether calculating multi vendor cart coupon in progress ?
		 *
		 * @var Boolean
		 */
		protected static $calculating_coupons;

		/**
		 * Init MVR_Cart_Manager.
		 *
		 * @since 1.0.0
		 */
		public static function init() {
			add_action( 'woocommerce_add_to_cart_validation', array( __CLASS__, 'validate_add_cart' ), 10, 5 );
			add_action( 'woocommerce_check_cart_items', array( __CLASS__, 'check_cart_items' ), 1 );
			add_action( 'woocommerce_calculate_totals', __CLASS__ . '::prepare_applied_item_coupons', 999 );
			add_action( 'woocommerce_cart_loaded_from_session', __CLASS__ . '::prepare_applied_item_coupons', 999 );
		}

		/**
		 * Validate Add to Cart
		 *
		 * @since 1.0.0
		 * @param Boolean $bool Validation.
		 * @param Integer $product_id Product ID.
		 * @param Integer $qty Quantity.
		 * @param Integer $variation_id Variation ID.
		 * @param Array   $cart_item_data Cart Item Data.
		 * @return Boolean
		 */
		public static function validate_add_cart( $bool, $product_id, $qty, $variation_id = 0, $cart_item_data = array() ) {
			if ( WC()->cart->is_empty() ) {
				return $bool;
			}

			$product_obj = wc_get_product( $product_id );

			if ( ! is_a( $product_obj, 'WC_Product' ) ) {
				return $bool;
			}

			$vendor_id = $product_obj->get_meta( '_mvr_vendor', true );

			if ( mvr_check_cart_contain_other_vendors( $vendor_id ) ) {
				wc_add_notice( esc_html__( 'You cannot add this product to the cart because your cart already contains product(s) from another Vendor.', 'multi-vendor-marketplace' ), 'error' );
				return false;
			}

			return $bool;
		}

		/**
		 * Validate Add to Cart
		 *
		 * @since 1.0.0
		 * @return Boolean
		 */
		public static function check_cart_items() {
			if ( WC()->cart->is_empty() ) {
				return false;
			}

			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$vendor_id = $cart_item['data']->get_meta( '_mvr_vendor', true );

				if ( $vendor_id ) {
					if ( mvr_check_cart_contain_other_vendors( $vendor_id ) ) {
						wc_add_notice( esc_html__( 'You can\'t complete the order because your cart contains different vendor products.', 'multi-vendor-marketplace' ), 'error' );
						return false;
					} else {
						return true;
					}
				}
			}

			return true;
		}

		/**
		 * Calculate Totals.
		 *
		 * @since 1.0.0
		 */
		public static function prepare_applied_item_coupons() {
			$mvr_carts = array();

			if ( ! mvr_cart_contains_vendor_product() ) {
				return;
			}

			if ( ! mvr_check_is_array( WC()->cart->get_coupons() ) ) {
				return;
			}

			if ( self::$calculating_coupons ) { // We're in the middle of a recalculation, let it run.
				return;
			}

			self::$calculating_coupons = true;

			foreach ( WC()->cart->get_cart() as $item_key => $item ) {
				$product_id = isset( $item['product_id'] ) ? $item['product_id'] : '';

				if ( empty( $product_id ) ) {
					continue;
				}

				$product_obj = wc_get_product( $product_id );

				if ( ! $product_obj ) {
					continue;
				}

				$product_vendor = $product_obj->get_meta( '_mvr_vendor', true );

				if ( empty( $product_vendor ) ) {
					continue;
				}

				$cloned_cart = clone WC()->cart;

				foreach ( $cloned_cart->get_cart() as $clone_item_key => $clone_cart_item ) {
					// Remove Fixed Cart Coupons.
					foreach ( $cloned_cart->get_coupons() as $code => $coupon_obj ) {
						if ( $coupon_obj->is_type( 'fixed_cart' ) ) {
							$cloned_cart->remove_coupon( $code );
						}
					}

					// Remove Other Products.
					if ( $clone_item_key !== $item_key ) {
						unset( $cloned_cart->cart_contents[ $clone_item_key ] );
						continue;
					}
				}

				add_filter( 'woocommerce_cart_needs_shipping', '__return_false', 999 );
				$cloned_cart->calculate_totals();
				remove_filter( 'woocommerce_cart_needs_shipping', '__return_false', 999 );

				$applied_coupons = $cloned_cart->get_coupon_discount_totals();

				if ( ! mvr_check_is_array( $applied_coupons ) ) {
					continue;
				}

				$mvr_cart = array(
					'admin_discount'    => 0,
					'vendor_discount'   => 0,
					'per_qty_discounts' => array(
						'admin_discount'  => 0,
						'vendor_discount' => 0,
					),
					'applied_coupons'   => array(
						'admin_coupons'  => array(),
						'vendor_coupons' => array(),
					),
				);

				foreach ( $applied_coupons as $coupon_code => $coupon_value ) {
					$coupon_obj    = new WC_Coupon( $coupon_code );
					$coupon_vendor = $coupon_obj->get_meta( '_mvr_vendor', true );

					if ( empty( $coupon_vendor ) ) {
						$mvr_cart['admin_discount']                  += $coupon_value;
						$mvr_cart['applied_coupons']['admin_coupons'] = array( $coupon_code => $coupon_value );
					} else {
						$mvr_cart['vendor_discount']                  += $coupon_value;
						$mvr_cart['applied_coupons']['vendor_coupons'] = array( $coupon_code => $coupon_value );
					}
				}

				$mvr_cart['per_qty_discounts']['admin_discount']  = $mvr_cart['admin_discount'] / $item['quantity'];
				$mvr_cart['per_qty_discounts']['vendor_discount'] = $mvr_cart['vendor_discount'] / $item['quantity'];
				$mvr_carts[ $item_key ]                           = $mvr_cart;
			}

			WC()->cart->mvr_carts = $mvr_carts;
		}
	}

	MVR_Cart_Manager::init();
}
