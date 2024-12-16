<?php
/**
 * Frontend Manager.
 *
 * @package Multi-Vendor Functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Frontend' ) ) {


	/**
	 * Manage Frontend.
	 *
	 * @class MVR_Frontend
	 * @package Class
	 */
	class MVR_Frontend {

		/**
		 * Init MVR_Frontend.
		 *
		 * @since 1.0.0
		 */
		public static function init() {
			add_action( 'init', __CLASS__ . '::includes' );
			add_filter( 'show_admin_bar', __CLASS__ . '::show_admin_bar' );

			// Single Product.
			add_action( 'woocommerce_after_single_product_summary', __CLASS__ . '::single_product_multi_vendor' );

			// Product Handler.
			add_action( 'woocommerce_single_product_summary', __CLASS__ . '::display_sold_by', 11 );
			add_action( 'woocommerce_after_shop_loop_item_title', __CLASS__ . '::display_sold_by', 11 );

			// Cart Handler.
			add_filter( 'woocommerce_get_item_data', __CLASS__ . '::display_sold_by_cart', 99, 2 );
			add_filter( 'woocommerce_add_cart_item_data', __CLASS__ . '::update_cart_item_meta', 8, 2 );

			// Coupon Validation.
			add_filter( 'woocommerce_coupon_is_valid_for_product', __CLASS__ . '::product_coupon_validation', 10, 3 );
			add_filter( 'woocommerce_coupon_error', __CLASS__ . '::display_coupon_error_msg', 10, 3 );

			add_action( 'mvr_before_vendor_register_form', __CLASS__ . '::display_subscription_notice' );
			add_action( 'mvr_before_user_vendor_register_form', __CLASS__ . '::display_subscription_notice' );
			add_action( 'mvr_before_dashboard', __CLASS__ . '::display_subscription_notice' );
			add_filter( 'edit_post_link', __CLASS__ . '::remove_edit_post_link' );
		}

		/**
		 * Remove Edit Post Link
		 *
		 * @since 1.0.0
		 * @param URL $link URL to edit post.
		 */
		public static function remove_edit_post_link( $link ) {
			return ( mvr_check_user_as_vendor_or_staff() ) ? '' : $link;
		}

		/**
		 * Include Frontend Files
		 *
		 * @since 1.0.0
		 */
		public static function includes() {
			include_once 'mvr-template-hooks.php';
			include_once 'class-mvr-frontend-scripts.php';
			include_once 'class-mvr-form-handler.php';
			include_once 'class-mvr-my-account-manager.php';
			include_once 'class-mvr-capability-manager.php';
			include_once 'class-mvr-cart-manager.php';
		}

		/**
		 * Show Admin Bar
		 *
		 * @since 1.0.0
		 * @param Boolean $bool Condition.
		 * */
		public static function show_admin_bar( $bool ) {
			if ( mvr_check_user_as_vendor_or_staff() ) {
				return false;
			}

			return $bool;
		}

		/**
		 * Single Product Multi Vendor
		 *
		 * @since 1.0.0
		 */
		public static function single_product_multi_vendor() {
			if ( 'yes' !== get_option( 'mvr_settings_allow_spmv', 'no' ) ) {
				return;
			}

			global $product;

			if ( ! is_a( $product, 'WC_Product' ) ) {
				return;
			}

			$vendor_id   = $product->get_meta( '_mvr_vendor', true );
			$vendor_id   = ( $vendor_id ) ? $vendor_id : 0;
			$product_map = mvr_get_all_spmv(
				array(
					'product_id' => $product->get_id(),
					'vendor_id'  => $vendor_id,
				)
			);

			if ( ! is_object( $product_map ) || ! $product_map->has_spmv ) {
				return;
			}

			$spmv_obj = current( $product_map->spmv_args );

			if ( ! mvr_is_spmv( $spmv_obj ) ) {
				return;
			}

			$product_map_id = $spmv_obj->get_map_id();

			if ( empty( $product_map_id ) ) {
				return;
			}

			$remaining_product_spmv = mvr_get_all_spmv(
				array(
					'exclude_product_id' => $product->get_id(),
					'map_id'             => $product_map_id,
				)
			);

			if ( ! $remaining_product_spmv->has_spmv ) {
				return;
			}

			$product_list = array();

			foreach ( $remaining_product_spmv->spmv_args as $remaining_spmv_obj ) {
				if ( ! mvr_is_spmv( $remaining_spmv_obj ) ) {
					continue;
				}

				$product_obj = wc_get_product( $remaining_spmv_obj->get_product_id() );

				if ( ! is_a( $product_obj, 'WC_Product' ) || ! $product_obj->is_visible() ) {
					continue;
				}

				$vendor_id = $product_obj->get_meta( '_mvr_vendor', true );

				if ( empty( $vendor_id ) ) {
					continue;
				}

				$vendor_obj                             = mvr_get_vendor( $vendor_id );
				$product_list[ $product_obj->get_id() ] = array(
					'vendor_obj'  => $vendor_obj,
					'product_obj' => $product_obj,
				);
			}

			if ( ! mvr_check_is_array( $product_list ) ) {
				return false;
			}

			mvr_get_template(
				'single-product-multi-vendor.php',
				array(
					'product_list'    => $product_list,
					'wp_button_class' => wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '',
				)
			);
		}

		/**
		 * Create Vendor Link.
		 *
		 * @since 1.0.0
		 */
		public static function display_sold_by() {
			global $product;

			if ( ! is_a( $product, 'WC_Product' ) ) {
				return;
			}

			$vendor_id = $product->get_meta( '_mvr_vendor', true );

			if ( empty( $vendor_id ) ) {
				return;
			}

			$vendor_obj = mvr_get_vendor( $vendor_id );

			if ( ! mvr_is_vendor( $vendor_obj ) ) {
				return;
			}

			$args = array( 'vendor_obj' => $vendor_obj );

			mvr_get_template( 'sold-by.php', $args );
		}

		/**
		 * Create Vendor Link.
		 *
		 * @since 1.0.0
		 * @param Array $item_data Item Data.
		 * @param Array $cart_item Cart Item Data.
		 * @return Array
		 */
		public static function display_sold_by_cart( $item_data, $cart_item ) {
			$product_id = isset( $cart_item['product_id'] ) ? $cart_item['product_id'] : '';

			if ( empty( $product_id ) ) {
				return $item_data;
			}

			$product_obj = wc_get_product( $product_id );

			if ( ! $product_obj ) {
				return $item_data;
			}

			$vendor_id = $product_obj->get_meta( '_mvr_vendor', true );

			if ( empty( $vendor_id ) ) {
				return $item_data;
			}

			$vendor_obj = mvr_get_vendor( $vendor_id );

			if ( ! $vendor_obj ) {
				return $item_data;
			}

			$item_data[] = array(
				'key'   => esc_html__( 'Sold by', 'multi-vendor-marketplace' ),
				'value' => '<a href="' . $vendor_obj->get_shop_url() . '">' . $vendor_obj->get_shop_name() . '</a>',
			);

			return $item_data;
		}

		/**
		 * Update Cart Item Meta
		 *
		 * @since 1.0.0
		 * @param Array   $cart_item_data Cart Item Data.
		 * @param Integer $product_id Product ID.
		 * @return String.
		 * */
		public static function update_cart_item_meta( $cart_item_data, $product_id ) {
			$product_obj = wc_get_product( $product_id );

			if ( ! $product_obj ) {
				return $cart_item_data;
			}

			$vendor_id = $product_obj->get_meta( '_mvr_vendor', true );

			if ( empty( $vendor_id ) ) {
				return $cart_item_data;
			}

			$vendor_obj = mvr_get_vendor( $vendor_id );

			if ( ! $vendor_obj ) {
				return $cart_item_data;
			}

			$cart_item_data['mvr_vendor_id'] = $vendor_id;

			return $cart_item_data;
		}

		/**
		 * Product Coupon Validation
		 *
		 * @since 1.0.0
		 * @param Boolean    $bool Validate Coupon.
		 * @param WC_Product $product_obj Product Object.
		 * @param WC_Coupon  $coupon_obj Coupon Object.
		 * @return Boolean.
		 * */
		public static function product_coupon_validation( $bool, $product_obj, $coupon_obj ) {
			if ( ! is_a( $product_obj, 'WC_Product' ) ) {
				return $bool;
			}

			if ( 'variation' === $product_obj->get_type() ) {
				$product_obj = wc_get_product( $product_obj->get_parent_id() );

				if ( ! is_a( $product_obj, 'WC_Product' ) ) {
					return $bool;
				}
			}

			$coupon_vendor = $coupon_obj->get_meta( '_mvr_vendor', true );

			if ( ! $coupon_vendor ) {
				return $bool;
			}

			$product_vendor = $product_obj->get_meta( '_mvr_vendor', true );

			if ( $product_vendor !== $coupon_vendor ) {
				return false;
			}

			return $bool;
		}

		/**
		 * Cart Coupon Validation
		 *
		 * @since 1.0.0
		 * @param Boolean   $bool Validate Coupon.
		 * @param WC_Coupon $coupon_obj Coupon Object.
		 * @return Boolean.
		 * */
		public static function cart_coupon_validation( $bool, $coupon_obj ) {
			$coupon_vendor = $coupon_obj->get_meta( '_mvr_vendor', true );

			if ( ! $coupon_vendor ) {
				return $bool;
			}

			if ( is_null( WC()->cart ) || WC()->cart->is_empty() ) {
				return $bool;
			}

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
			}

			return $bool;
		}

		/**
		 * Display coupon error Message
		 *
		 * @since 1.0.0
		 * @param String    $msg Message.
		 * @param Integer   $msg_code Error code.
		 * @param WC_Coupon $coupon_obj Coupon Object.
		 */
		public static function display_coupon_error_msg( $msg, $msg_code, $coupon_obj ) {
			if ( 100 !== $msg_code ) {
				return $msg;
			}

			$coupon_vendor = $coupon_obj->get_meta( '_mvr_vendor', true );

			if ( ! $coupon_vendor ) {
				return $msg;
			}

			return esc_html__( 'Sorry, this vendor coupon is not applicable to selected products.', 'multi-vendor-marketplace' );
		}

		/**
		 * Display Subscription notice
		 *
		 * @since 1.0.0
		 */
		public static function display_subscription_notice() {
			// Subscription check.
			if ( 'yes' === get_option( 'mvr_settings_enable_vendor_subscription', 'no' ) ) {
				global $wp;

				if ( ! class_exists( 'WC_Subscriptions' ) ) {
					return;
				}

				$request    = explode( '/', $wp->request );
				$product_id = get_option( 'mvr_settings_subscription_product' );

				if ( empty( $product_id ) ) {
					return;
				}

				$product_obj = wc_get_product( $product_id );

				if ( ! is_a( $product_obj, 'WC_Product' ) ) {
					return;
				}

				$vendor_obj = mvr_get_current_vendor_object();

				if ( mvr_is_vendor_register_page() || ( 'my-account' === end( $request ) && is_account_page() ) || ( mvr_is_vendor( $vendor_obj ) && ! $vendor_obj->has_subscribed() ) ) {
					if ( mvr_is_vendor_register_page() || ( 'my-account' === end( $request ) && is_account_page() ) ) {
						$message = str_replace(
							array( '{subscription_price}' ),
							array( $product_obj->get_price_html() ),
							get_option( 'mvr_vendor_subscription_notice_message' )
						);
					} else {
						$msg              = str_replace(
							array( '{subscription_price}' ),
							array( $product_obj->get_price_html() ),
							get_option( 'mvr_vendor_subscription_notice_message' )
						);
						$checkout_page_id = wc_get_page_id( 'checkout' );
						$checkout_url     = $checkout_page_id ? get_permalink( $checkout_page_id ) : '';

						if ( empty( $checkout_url ) ) {
							return;
						}

						$checkout_url = add_query_arg(
							array(
								'add-to-cart' => $product_obj->get_id(),
								'quantity'    => 1,
							),
							$checkout_url
						);
						/* translators: %1$s: Checkout URL, %2$s: Subscribe Label, %3$s: Subscription Message */
						$message = sprintf( wp_kses_post( '<a href="%1$s" tabindex="1" class="button wc-forward">%2$s</a> %3$s ' ), esc_url( $checkout_url ), esc_html__( 'Subscribe', 'multi-vendor-marketplace' ), wp_kses_post( $msg ) );
					}

					wc_print_notice( $message, 'error' );
				}
			}
		}
	}

	MVR_Frontend::init();
}
