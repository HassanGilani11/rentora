<?php
/**
 * Advanced Section
 *
 * @package Multi Vendor Marketplace/Setting Tab/Advanced Section
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Admin_Settings_Advanced' ) ) {
	/**
	 * Advanced Tab.
	 *
	 * @class MVR_Admin_Settings_Advanced
	 * @package Class
	 */
	class MVR_Admin_Settings_Advanced extends MVR_Abstract_Settings {
		/**
		 * MVR_Admin_Settings_Advanced constructor.
		 */
		public function __construct() {
			$this->id            = 'advanced';
			$this->label         = __( 'Advanced', 'multi-vendor-marketplace' );
			$this->custom_fields = array( 'verify_default_database_tables', 'verify_default_db_tables' );
			$this->settings      = $this->get_settings();
			$this->init();
		}

		/**
		 * Get settings array.
		 *
		 * @since 1.0.0
		 * @return Array
		 */
		public function get_settings() {
			/**
			 * Advanced Settings Fields.
			 *
			 * @since 1.0.0
			 */
			return apply_filters(
				'mvr_get_' . $this->id . '_settings',
				array(
					array(
						'type'  => 'title',
						'title' => esc_html__( 'Troubleshoot Settings', 'multi-vendor-marketplace' ),
						'id'    => 'mvr_setup_options',
					),
					array(
						'type' => $this->get_custom_field_type( 'verify_default_database_tables' ),
					),
					array(
						'type' => $this->get_custom_field_type( 'verify_default_db_tables' ),
					),
					array(
						'title'   => esc_html__( 'Delete All Vendor Data upon Uninstall', 'multi-vendor-marketplace' ),
						'type'    => 'checkbox',
						'default' => 'no',
						'id'      => $this->get_option_key( 'allow_multi_vendor_product' ),
						'desc'    => esc_html__( 'Removes all  the data and tables related to Multi Vendor plugin upon deleting the Vendor plugin', 'multi-vendor-marketplace' ),
					),
					array(
						'type' => 'sectionend',
						'id'   => 'mvr_setup_options',
					),
					array(
						'type'  => 'title',
						'title' => esc_html__( 'Default Pages', 'multi-vendor-marketplace' ),
						'id'    => 'mvr_page_setup_options',
					),
					array(
						'title'    => esc_html__( 'Vendor Registration Page', 'multi-vendor-marketplace' ),
						'id'       => $this->get_option_key( 'vendor_register_page_id' ),
						'type'     => 'single_select_page_with_search',
						'class'    => 'wc-page-search',
						'default'  => mvr_get_page_id( 'vendor_register' ),
						'css'      => 'min-width:300px;',
						'args'     => array(
							'exclude' =>
								array(
									wc_get_page_id( 'cart' ),
									wc_get_page_id( 'myaccount' ),
									wc_get_page_id( 'checkout' ),
								),
						),
						'desc_tip' => true,
						'desc'     => esc_html__( 'Vendor Registration', 'multi-vendor-marketplace' ),
						'autoload' => false,
					),
					array(
						'title'    => esc_html__( 'Vendor Login Page', 'multi-vendor-marketplace' ),
						'id'       => $this->get_option_key( 'vendor_login_page_id' ),
						'type'     => 'single_select_page_with_search',
						'class'    => 'wc-page-search',
						'default'  => mvr_get_page_id( 'vendor_login' ),
						'css'      => 'min-width:300px;',
						'args'     => array(
							'exclude' =>
								array(
									wc_get_page_id( 'cart' ),
									wc_get_page_id( 'myaccount' ),
									wc_get_page_id( 'checkout' ),
								),
						),
						'desc'     => esc_html__( 'Vendor Login', 'multi-vendor-marketplace' ),
						'desc_tip' => true,
						'autoload' => false,
					),
					array(
						'title'    => __( 'Stores Page', 'multi-vendor-marketplace' ),
						'desc'     => __( 'Page contents: [mvr_stores]', 'multi-vendor-marketplace' ),
						'id'       => $this->get_option_key( 'stores_page_id' ),
						'type'     => 'single_select_page_with_search',
						'class'    => 'wc-page-search',
						'default'  => mvr_get_page_id( 'stores' ),
						'css'      => 'min-width:300px;',
						'args'     => array(
							'exclude' =>
								array(
									wc_get_page_id( 'cart' ),
									wc_get_page_id( 'myaccount' ),
									wc_get_page_id( 'checkout' ),
								),
						),
						'desc_tip' => true,
						'autoload' => false,
					),
					array(
						'title'    => __( 'Vendor Dashboard Page', 'multi-vendor-marketplace' ),
						'desc'     => __( 'Page contents: [mvr_dashboard]', 'multi-vendor-marketplace' ),
						'id'       => $this->get_option_key( 'dashboard_page_id' ),
						'type'     => 'single_select_page_with_search',
						'class'    => 'wc-page-search',
						'default'  => mvr_get_page_id( 'dashboard' ),
						'css'      => 'min-width:300px;',
						'args'     => array(
							'exclude' =>
								array(
									wc_get_page_id( 'cart' ),
									wc_get_page_id( 'myaccount' ),
									wc_get_page_id( 'checkout' ),
								),
						),
						'desc_tip' => true,
						'autoload' => false,
					),
					array(
						'title'    => esc_html__( 'Terms & Conditions Page', 'multi-vendor-marketplace' ),
						'id'       => $this->get_option_key( 'vendor_tac_page' ),
						'type'     => 'single_select_page_with_search',
						'class'    => 'wc-page-search',
						'default'  => '',
						'css'      => 'min-width:300px;',
						'args'     => array(
							'exclude' =>
								array(
									wc_get_page_id( 'cart' ),
									wc_get_page_id( 'myaccount' ),
									wc_get_page_id( 'checkout' ),
								),
						),
						'desc_tip' => true,
						'autoload' => false,
					),
					array(
						'title'    => esc_html__( 'Privacy Policy Page', 'multi-vendor-marketplace' ),
						'id'       => $this->get_option_key( 'vendor_privacy_policy_page' ),
						'type'     => 'single_select_page_with_search',
						'class'    => 'wc-page-search',
						'default'  => '',
						'css'      => 'min-width:300px;',
						'args'     => array(
							'exclude' =>
								array(
									wc_get_page_id( 'cart' ),
									wc_get_page_id( 'myaccount' ),
									wc_get_page_id( 'checkout' ),
								),
						),
						'desc_tip' => true,
						'autoload' => false,
					),
					array(
						'type' => 'sectionend',
						'id'   => 'mvr_page_setup_options',
					),
					array(
						'type'  => 'title',
						'title' => esc_html__( 'Stores Endpoints', 'multi-vendor-marketplace' ),
						'id'    => 'mvr_stores_endpoints_options',
						'desc'  => esc_html__( 'Endpoints are appended to your page URLs to handle specific actions on stores page. They should be unique.', 'multi-vendor-marketplace' ),
					),
					array(
						'title'   => esc_html__( 'Single Store Slug', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'default' => 'mvr-store',
						'id'      => $this->get_option_key( 'single_store_endpoint' ),
						'desc'    => esc_html__( 'Enter the Vendor Store URL(siteurl/text-defined/vendor)', 'multi-vendor-marketplace' ),
					),
					array(
						'type' => 'sectionend',
						'id'   => 'mvr_stores_endpoints_options',
					),
					array(
						'type'  => 'title',
						'title' => esc_html__( 'Dashboard Endpoints', 'multi-vendor-marketplace' ),
						'id'    => 'mvr_dashboard_endpoints_options',
						'desc'  => esc_html__( 'Endpoints are appended to your page URLs to handle specific actions on dashboard page. They should be unique.', 'multi-vendor-marketplace' ),
					),
					array(
						'title'   => esc_html__( 'Products', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'default' => 'mvr-products',
						'id'      => $this->get_option_key( 'products_endpoint' ),
					),
					array(
						'title'   => esc_html__( 'Add new product', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'default' => 'mvr-add-product',
						'id'      => $this->get_option_key( 'add_product_endpoint' ),
					),
					array(
						'title'   => esc_html__( 'Edit product', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'default' => 'mvr-edit-product',
						'id'      => $this->get_option_key( 'edit_product_endpoint' ),
					),
					array(
						'title'   => esc_html__( 'Orders', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'default' => 'mvr-orders',
						'id'      => $this->get_option_key( 'orders_endpoint' ),
					),
					array(
						'title'   => esc_html__( 'View Order', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'default' => 'mvr-view-order',
						'id'      => $this->get_option_key( 'view_order_endpoint' ),
					),
					array(
						'title'   => esc_html__( 'Coupons', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'default' => 'mvr-coupons',
						'id'      => $this->get_option_key( 'coupons_endpoint' ),
					),
					array(
						'title'   => esc_html__( 'Add new coupon', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'default' => 'mvr-add-coupon',
						'id'      => $this->get_option_key( 'add_coupon_endpoint' ),
					),
					array(
						'title'   => esc_html__( 'Edit Coupon', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'default' => 'mvr-edit-coupon',
						'id'      => $this->get_option_key( 'edit_coupon_endpoint' ),
					),
					array(
						'title'   => esc_html__( 'Withdraw', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'default' => 'mvr-withdraw',
						'id'      => $this->get_option_key( 'withdraw_endpoint' ),
					),
					array(
						'title'   => esc_html__( 'Add Withdraw Request', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'default' => 'mvr-add-withdraw',
						'id'      => $this->get_option_key( 'add_withdraw_endpoint' ),
					),
					array(
						'title'   => esc_html__( 'Transaction', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'default' => 'mvr-transaction',
						'id'      => $this->get_option_key( 'transaction_endpoint' ),
					),
					array(
						'title'   => esc_html__( 'Customers', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'default' => 'mvr-customers',
						'id'      => $this->get_option_key( 'customers_endpoint' ),
					),
					array(
						'title'   => esc_html__( 'Duplicate', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'default' => 'mvr-duplicate',
						'id'      => $this->get_option_key( 'duplicate_endpoint' ),
					),
					array(
						'title'   => esc_html__( 'Payments', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'default' => 'mvr-payments',
						'id'      => $this->get_option_key( 'payment_endpoint' ),
					),
					array(
						'title'   => esc_html__( 'Payout', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'default' => 'mvr-payout',
						'id'      => $this->get_option_key( 'payout_endpoint' ),
					),
					array(
						'title'   => esc_html__( 'Profile', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'default' => 'mvr-profile',
						'id'      => $this->get_option_key( 'profile_endpoint' ),
					),
					array(
						'title'   => esc_html__( 'Address', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'default' => 'mvr-address',
						'id'      => $this->get_option_key( 'address_endpoint' ),
					),
					array(
						'title'   => esc_html__( 'Social Links', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'default' => 'mvr-social-links',
						'id'      => $this->get_option_key( 'social_links_endpoint' ),
					),
					array(
						'title'   => esc_html__( 'Staff', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'default' => 'mvr-staff',
						'id'      => $this->get_option_key( 'staff_endpoint' ),
					),
					array(
						'title'   => esc_html__( 'Add Staff', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'default' => 'mvr-add-staff',
						'id'      => $this->get_option_key( 'add_staff_endpoint' ),
					),
					array(
						'title'   => esc_html__( 'Edit Staff', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'default' => 'mvr-edit-staff',
						'id'      => $this->get_option_key( 'edit_staff_endpoint' ),
					),
					array(
						'title'   => esc_html__( 'Reviews', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'default' => 'mvr-reviews',
						'id'      => $this->get_option_key( 'reviews_endpoint' ),
					),
					array(
						'title'   => esc_html__( 'Store Notification', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'default' => 'mvr-notification',
						'id'      => $this->get_option_key( 'notification_endpoint' ),
					),
					array(
						'title'   => esc_html__( 'Store Enquiry', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'default' => 'mvr-enquiry',
						'id'      => $this->get_option_key( 'enquiry_endpoint' ),
					),
					array(
						'title'   => esc_html__( 'Store Enquiry Reply', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'default' => 'mvr-reply-enquiry',
						'id'      => $this->get_option_key( 'reply_enquiry_endpoint' ),
					),
					array(
						'type' => 'sectionend',
						'id'   => 'mvr_dashboard_endpoints_options',
					),
				)
			);
		}

		/**
		 * Get settings array.
		 *
		 * @since 1.0.0
		 */
		public function verify_default_database_tables() {
			?>
				<tr class="mvr-install-pages">
					<th>
						<strong class="name"><?php esc_html_e( 'Create Default Pages', 'multi-vendor-marketplace' ); ?></strong>
					</th>
					<td class="run-tool">
						<input type="submit" form="mvr_form_install_pages" class="button button-large" value="<?php esc_html_e( 'Create Pages', 'multi-vendor-marketplace' ); ?>">
						<p class="description"> <?php printf( '<strong>%1$s</strong> %2$s', esc_html__( 'Note:', 'multi-vendor-marketplace' ), esc_html__( 'Clicking this button will  install all the missing multi vendor pages. Pages already defined and set up will not be replaced.', 'multi-vendor-marketplace' ) ); ?> </p>
					</td>
				</tr>
			<?php
		}

		/**
		 * Get settings array.
		 *
		 * @since 1.0.0
		 */
		public function verify_default_db_tables() {
			?>
			<tr class="mvr-verify-db-tables">
				<th>
					<strong class="name"><?php esc_html_e( 'Verify Base Database Tables', 'multi-vendor-marketplace' ); ?></strong>
				</th>
				<td class="run-tool">
					<input type="submit" form="mvr_form_verify_db_tables" class="button button-large" value="<?php esc_html_e( 'Verify Database', 'multi-vendor-marketplace' ); ?>">
					<p class="description"> <?php esc_html_e( 'Verify if all base database tables are present.', 'multi-vendor-marketplace' ); ?> </p>
				</td>
			</tr>
			<?php
		}
	}

	return new MVR_Admin_Settings_Advanced();
}
