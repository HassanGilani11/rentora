<?php
/**
 * Query
 *
 * @package Multi Vendor/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit accessed directly.
}

if ( ! class_exists( 'MVR_Query' ) ) {

	/**
	 * Contains the query functions for Multi Vendor Marketplace
	 *
	 * @class MVR_Query
	 * @package Class
	 */
	class MVR_Query {

		/**
		 * Query vars to add to wp
		 *
		 * @var Array
		 */
		public $query_vars = array();

		/**
		 * Constructor for the query class. Hooks in methods.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'add_endpoints' ) );

			if ( ! is_admin() ) {
				add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );
				add_action( 'parse_request', array( $this, 'parse_request' ), 0 );
				add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
				add_filter( 'the_title', array( $this, 'set_page_endpoint_title' ) );
				add_filter( 'woocommerce_get_breadcrumb', array( $this, 'get_breadcrumb' ), 10, 1 );
			}

			$this->init_query_vars();
		}

		/**
		 * Init query vars by loading options.
		 *
		 * @since 1.0.0
		 */
		public function init_query_vars() {
			/**
			 * Get query vars to add to WP.
			 *
			 * @since 1.0.0
			 */
			$this->query_vars = array(
				'mvr-products'      => get_option( 'mvr_settings_products_endpoint', 'mvr-products' ),
				'mvr-add-product'   => get_option( 'mvr_settings_add_product_endpoint', 'mvr-add-product' ),
				'mvr-edit-product'  => get_option( 'mvr_settings_edit_product_endpoint', 'mvr-edit-product' ),
				'mvr-orders'        => get_option( 'mvr_settings_orders_endpoint', 'mvr-orders' ),
				'mvr-view-order'    => get_option( 'mvr_settings_view_order_endpoint', 'mvr-view-order' ),
				'mvr-coupons'       => get_option( 'mvr_settings_coupons_endpoint', 'mvr-coupons' ),
				'mvr-add-coupon'    => get_option( 'mvr_settings_add_coupon_endpoint', 'mvr-add-coupon' ),
				'mvr-edit-coupon'   => get_option( 'mvr_settings_edit_coupon_endpoint', 'mvr-edit-coupon' ),
				'mvr-withdraw'      => get_option( 'mvr_settings_withdraw_endpoint', 'mvr-withdraw' ),
				'mvr-add-withdraw'  => get_option( 'mvr_settings_add_withdraw_endpoint', 'mvr-add-withdraw' ),
				'mvr-transaction'   => get_option( 'mvr_settings_transaction_endpoint', 'mvr-transaction' ),
				'mvr-customers'     => get_option( 'mvr_settings_customers_endpoint', 'mvr-customers' ),
				'mvr-duplicate'     => get_option( 'mvr_settings_duplicate_endpoint', 'mvr-duplicate' ),
				'mvr-payments'      => get_option( 'mvr_settings_payment_endpoint', 'mvr-payments' ),
				'mvr-payout'        => get_option( 'mvr_settings_payout_endpoint', 'mvr-payout' ),
				'mvr-profile'       => get_option( 'mvr_settings_profile_endpoint', 'mvr-profile' ),
				'mvr-address'       => get_option( 'mvr_settings_address_endpoint', 'mvr-address' ),
				'mvr-social-links'  => get_option( 'mvr_settings_social_links_endpoint', 'mvr-social-links' ),
				'mvr-staff'         => get_option( 'mvr_settings_staff_endpoint', 'mvr-staff' ),
				'mvr-add-staff'     => get_option( 'mvr_settings_add_staff_endpoint', 'mvr-add-staff' ),
				'mvr-edit-staff'    => get_option( 'mvr_settings_edit_staff_endpoint', 'mvr-edit-staff' ),
				'mvr-reviews'       => get_option( 'mvr_settings_reviews_endpoint', 'mvr-reviews' ),
				'mvr-logout'        => get_option( 'mvr_settings_logout_endpoint', 'mvr-logout' ),
				'mvr-store'         => get_option( 'mvr_settings_store_endpoint', 'mvr-store' ),
				'mvr-notification'  => get_option( 'mvr_settings_notification_endpoint', 'mvr-notification' ),
				'mvr-enquiry'       => get_option( 'mvr_settings_enquiry_endpoint', 'mvr-enquiry' ),
				'mvr-reply-enquiry' => get_option( 'mvr_settings_reply_enquiry_endpoint', 'mvr-reply-enquiry' ),
			);
		}

		/**
		 * Get page title for an endpoint.
		 *
		 * @since 1.0.0
		 * @param  String $endpoint Endpoint name.
		 * @return String
		 */
		public function get_endpoint_title( $endpoint ) {
			global $wp;

			switch ( $endpoint ) {
				case 'mvr-products':
					$title = get_option( 'mvr_dashboard_products_menu_label', 'Products' );
					break;
				case 'mvr-add-product':
					$title = __( 'Add new product', 'multi-vendor-marketplace' );
					break;
				case 'mvr-edit-product':
					$product_obj = wc_get_product( $wp->query_vars['mvr-edit-product'] );
					/* translators: %s: Product number */
					$title = ( $product_obj ) ? sprintf( __( 'Edit product #%s', 'multi-vendor-marketplace' ), $product_obj->get_id() ) : '';
					break;
				case 'mvr-orders':
					/* translators: %1$s: Page Title, %2$s: Page Number */
					$title = ( ! empty( $wp->query_vars['mvr-orders'] ) ) ? sprintf( __( '%1$s (page %2$d)', 'multi-vendor-marketplace' ), get_option( 'mvr_dashboard_orders_menu_label', 'Orders' ), intval( $wp->query_vars['mvr-orders'] ) ) : get_option( 'mvr_dashboard_orders_menu_label', 'Orders' );
					break;
				case 'mvr-view-order':
					$order_obj = wc_get_order( $wp->query_vars['mvr-view-order'] );
					/* translators: %s: order number */
					$title = ( $order_obj ) ? sprintf( __( 'Order #%s', 'multi-vendor-marketplace' ), $order_obj->get_order_number() ) : '';
					break;
				case 'mvr-coupons':
					$title = get_option( 'mvr_dashboard_coupons_menu_label', 'Coupons' );
					break;
				case 'mvr-add-coupon':
					$title = __( 'Add new coupon', 'multi-vendor-marketplace' );
					break;
				case 'mvr-edit-coupon':
					$coupon_obj = new WC_Coupon( $wp->query_vars['mvr-edit-coupon'] );
					/* translators: %s: Coupon ID */
					$title = ( $coupon_obj ) ? sprintf( __( 'Edit coupon #%s', 'multi-vendor-marketplace' ), $coupon_obj->get_id() ) : '';
					break;
				case 'mvr-withdraw':
					$title = get_option( 'mvr_dashboard_withdraw_menu_label', 'Withdraw' );
					break;
				case 'mvr-transaction':
					$title = get_option( 'mvr_dashboard_transactions_menu_label', 'Transactions' );
					break;
				case 'mvr-payments':
					$title = get_option( 'mvr_dashboard_payments_menu_label', 'Payments' );
					break;
				case 'mvr-customers':
					$title = get_option( 'mvr_dashboard_customers_menu_label', 'Customers' );
					break;
				case 'mvr-duplicate':
					$title = get_option( 'mvr_dashboard_duplicate_menu_label', 'Duplicate' );
					break;
				case 'mvr-reviews':
					$title = get_option( 'mvr_dashboard_review_menu_label', 'Reviews' );
					break;
				case 'mvr-payout':
					$title = get_option( 'mvr_dashboard_payout_menu_label', 'Payout' );
					break;
				case 'mvr-profile':
					$title = get_option( 'mvr_dashboard_profile_menu_label', 'Profile' );
					break;
				case 'mvr-address':
					$title = get_option( 'mvr_dashboard_address_menu_label', 'Address' );
					break;
				case 'mvr-social-links':
					$title = get_option( 'mvr_dashboard_social_link_menu_label', 'Social Links' );
					break;
				case 'mvr-staff':
					$title = get_option( 'mvr_dashboard_staff_menu_label', 'Staff' );
					break;
				case 'mvr-add-staff':
					$title = __( 'Add New Staff', 'multi-vendor-marketplace' );
					break;
				case 'mvr-edit-staff':
					$staff_obj = mvr_get_staff( $wp->query_vars['mvr-edit-staff'] );
					/* translators: %s: Edit staff */
					$title = ( $staff_obj ) ? sprintf( __( 'Edit Staff #%s', 'multi-vendor-marketplace' ), $staff_obj->get_id() ) : '';
					break;
				case 'mvr-store':
					$slug = $wp->query_vars[ $endpoint ];
					$slug = str_contains( $slug, '/' ) ? strstr( $slug, '/', true ) : $slug;

					if ( empty( $slug ) ) {
						return;
					}

					$args       = array(
						'post_name__in'  => array( $slug ),
						'post_status'    => 'mvr-active',
						'post_type'      => 'mvr_vendor',
						'fields'         => 'ids',
						'posts_per_page' => 1,
					);
					$vendor_ids = get_posts( $args );
					$vendor_id  = reset( $vendor_ids );

					if ( empty( $vendor_id ) ) {
						return;
					}

					$vendor_obj = mvr_get_vendor( $vendor_id );

					if ( ! mvr_is_vendor( $vendor_obj ) ) {
						return;
					}

					$title = $vendor_obj->get_shop_name();
					break;
				case 'mvr-logout':
					$title = __( 'Logout', 'multi-vendor-marketplace' );
					break;
				case 'mvr-notification':
					$title = __( 'Notification', 'multi-vendor-marketplace' );
					break;
				case 'mvr-enquiry':
					$title = __( 'Enquiry', 'multi-vendor-marketplace' );
					break;
				case 'mvr-reply-enquiry':
					$enquiry_obj = mvr_get_enquiry( $wp->query_vars['mvr-reply-enquiry'] );
					/* translators: %s: Enquiry ID */
					$title = ( $enquiry_obj ) ? sprintf( __( 'Enquiry #%s', 'multi-vendor-marketplace' ), $enquiry_obj->get_id() ) : '';
					break;
				default:
					$title = '';
					break;
			}

			/**
			 * Get the current endpoint title.
			 *
			 * @since 1.0.0
			 */
			return apply_filters( 'mvr_get_current_endpoint_title', $title, $endpoint );
		}

		/**
		 * Endpoint mask describing the places the endpoint should be added.
		 *
		 * @since 1.0.0
		 * @return Integer
		 */
		public function get_endpoints_mask() {
			if ( 'page' === get_option( 'show_on_front' ) ) {
				$page_on_front     = get_option( 'page_on_front' );
				$dashboard_page_id = get_option( 'mvr_settings_dashboard_page_id' );

				if ( in_array( $page_on_front, array( $dashboard_page_id ), true ) ) {
					return EP_ROOT | EP_PAGES;
				}
			}

			return EP_PAGES;
		}

		/**
		 * Add endpoints for query vars.
		 *
		 * @since 1.0.0
		 */
		public function add_endpoints() {
			$mask = $this->get_endpoints_mask();

			foreach ( $this->get_query_vars() as $key => $var ) {
				if ( ! empty( $var ) ) {
					add_rewrite_endpoint( $var, $mask );
				}
			}

			$do_flush = get_option( 'mvr_flush_rewrite_rules', 1 );

			if ( $do_flush && ! empty( $this->query_vars ) ) {
				update_option( 'mvr_flush_rewrite_rules', 0 );
				flush_rewrite_rules();
			}
		}

		/**
		 * Add query vars.
		 *
		 * @since 1.0.0
		 * @param Array $vars Query Variables.
		 * @return Array
		 */
		public function add_query_vars( $vars ) {
			foreach ( $this->get_query_vars() as $key => $var ) {
				$vars[] = $key;
			}

			return $vars;
		}

		/**
		 * Get query vars.
		 *
		 * @since 1.0.0
		 * @return Array
		 */
		public function get_query_vars() {
			/**
			 * Query Variables.
			 *
			 *  @since 1.0.0
			 */
			return apply_filters( 'mvr_get_query_vars', $this->query_vars );
		}

		/**
		 * Get current active query var.
		 *
		 * @since 1.0.0
		 * @return String
		 */
		public function get_current_endpoint() {
			global $wp;

			foreach ( $this->get_query_vars() as $key => $var ) {
				if ( isset( $wp->query_vars[ $key ] ) ) {
					return $key;
				}
			}

			return '';
		}

		/**
		 * Parse the request and look for query vars - endpoints may not be supported.
		 *
		 * @since 1.0.0
		 */
		public function parse_request() {
			global $wp;

			// Map query vars to their keys, or get them if endpoints are not supported.
			foreach ( $this->get_query_vars() as $key => $var ) {
				if ( isset( $_GET[ $var ] ) ) {
					$wp->query_vars[ $key ] = sanitize_text_field( wp_unslash( $_GET[ $var ] ) );
				} elseif ( isset( $wp->query_vars[ $var ] ) ) {
					$wp->query_vars[ $key ] = $wp->query_vars[ $var ];
				}
			}
		}

		/**
		 * Are we currently on the front page?
		 *
		 * @since 1.0.0
		 * @param Object $q Query object.
		 * @return Boolean
		 */
		private function is_showing_page_on_front( $q ) {
			return ( $q->is_home() && ! $q->is_posts_page ) && 'page' === get_option( 'show_on_front' );
		}

		/**
		 * Is the front page a page we define?
		 *
		 * @since 1.0.0
		 * @param int $page_id Page ID.
		 * @return Boolean
		 */
		private function page_on_front_is( $page_id ) {
			return absint( get_option( 'page_on_front' ) ) === absint( $page_id );
		}

		/**
		 * Hook into pre_get_posts to do the main product query.
		 *
		 * @since 1.0.0
		 * @param Object $q query object.
		 */
		public function pre_get_posts( $q ) {
			// We only want to affect the main query.
			if ( ! $q->is_main_query() ) {
				return;
			}

			// Fix for endpoints on the homepage.
			if ( $this->is_showing_page_on_front( $q ) ) {
				if ( ! $this->page_on_front_is( $q->get( 'page_id' ) ) ) {
					$_query = wp_parse_args( $q->query );

					if ( ! empty( $_query ) && array_intersect( array_keys( $_query ), array_keys( $this->query_vars ) ) ) {
						$q->is_page     = true;
						$q->is_home     = false;
						$q->is_singular = true;
						$q->set( 'page_id', (int) get_option( 'page_on_front' ) );
						add_filter( 'redirect_canonical', '__return_false' );
					}
				}

				if ( $this->page_on_front_is( mvr_get_page_id( 'stores' ) ) ) {
					$_query = $this->filter_out_valid_front_page_query_vars( wp_parse_args( $q->query ) );

					if ( empty( $_query ) ) {
						$q->set( 'page_id', (int) get_option( 'page_on_front' ) );
						$q->is_page = true;
						$q->is_home = false;

						// WP supporting themes show post type archive.
						if ( current_theme_supports( 'woocommerce' ) ) {
							$q->set( 'post_type', 'mvr_vendor' );

							if ( isset( $q->query['tab'] ) ) {
								$q->set( 'tab', $q->query['paged'] );
							}
						} else {
							$q->is_singular = true;
						}
					}
				}
			}
		}

		/**
		 * Get query var for the given endpoint.
		 *
		 * @since 1.0.0
		 * @param string $endpoint End Point Name.
		 * @return Array
		 */
		public function get_query_var( $endpoint ) {
			return isset( $this->query_vars[ $endpoint ] ) ? $this->query_vars[ $endpoint ] : $endpoint;
		}

		/**
		 * Returns a copy of `$query` with all query vars that are allowed on the front page stripped.
		 * Used when the shop page is also the front page.
		 *
		 * @param array $query The unfiltered array.
		 * @return array The filtered query vars.
		 */
		private function filter_out_valid_front_page_query_vars( $query ) {
			return array_filter(
				$query,
				function ( $key ) {
					return ! $this->is_query_var_valid_on_front_page( $key );
				},
				ARRAY_FILTER_USE_KEY
			);
		}

		/**
		 * Checks whether a query var is allowed on the front page or not.
		 *
		 * @param string $query_var Query var name.
		 * @return boolean TRUE when query var is allowed on the front page. FALSE otherwise.
		 */
		private function is_query_var_valid_on_front_page( $query_var ) {
			return in_array( $query_var, array( 'paged', 'tab', 'orderby', 'p' ), true );
		}

		/**
		 * Replace a page title with the endpoint title.
		 *
		 * @since 1.0.0
		 * @param  String $title Title of the endpoint page.
		 * @return String
		 */
		public function set_page_endpoint_title( $title ) {
			global $wp_query;

			$endpoint = $this->get_current_endpoint();

			if ( ! is_null( $wp_query ) && ! is_admin() && is_main_query() && in_the_loop() && is_page() && '' !== $endpoint ) {
				$endpoint_title = $this->get_endpoint_title( $endpoint );

				if ( $endpoint_title ) {
					$title = $endpoint_title;
				}

				remove_filter( 'the_title', array( $this, 'set_page_endpoint_title' ) );
			}

			return $title;
		}

		/**
		 * Get the breadcrumb.
		 *
		 * @since 1.0.0
		 * @param Array $crumbs Breadcrumbs.
		 * @return Array
		 */
		public function get_breadcrumb( $crumbs ) {
			global $wp_query;

			if ( ! is_main_query() || ! is_page() || ! mvr_is_dashboard_page() ) {
				return $crumbs;
			}

			$crumbs[1] = array(
				get_the_title( mvr_get_page_id( 'dashboard' ) ),
				get_permalink( mvr_get_page_id( 'dashboard' ) ),
			);

			foreach ( $this->get_query_vars() as $endpoint_id => $end_point ) {
				if ( isset( $wp_query->query_vars[ $this->get_query_var( $endpoint_id ) ] ) ) {
					$crumbs[2] = array(
						mvr()->query->get_endpoint_title( $endpoint_id ),
						get_permalink( mvr_get_dashboard_endpoint_url( $endpoint_id ) ),
					);
				}
			}

			return $crumbs;
		}
	}
}
