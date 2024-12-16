<?php
/**
 * Localization Section
 *
 * @package Multi Vendor Marketplace/Setting Tab/Localization Section
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Admin_Settings_Localization' ) ) {
	/**
	 * General Tab.
	 *
	 * @class MVR_Admin_Settings_Localization
	 * @package Class
	 */
	class MVR_Admin_Settings_Localization extends MVR_Abstract_Settings {
		/**
		 * MVR_Admin_Settings_Localization constructor.
		 */
		public function __construct() {
			$this->id                   = 'localization';
			$this->label                = __( 'Localization', 'multi-vendor-marketplace' );
			$this->custom_fields        = array(
				'get_localization',
			);
			$this->custom_fields_option = array(
				'mvr_dashboard_home_menu_label'            => 'Home',
				'mvr_dashboard_products_menu_label'        => 'Products',
				'mvr_dashboard_orders_menu_label'          => 'Orders',
				'mvr_dashboard_coupons_menu_label'         => 'Coupons',
				'mvr_dashboard_withdraw_menu_label'        => 'Withdraw',
				'mvr_dashboard_transactions_menu_label'    => 'Transactions',
				'mvr_dashboard_customers_menu_label'       => 'Customers',
				'mvr_dashboard_duplicate_menu_label'       => 'Duplicate',
				'mvr_dashboard_payments_menu_label'        => 'Payments',
				'mvr_dashboard_payout_menu_label'          => 'Payout',
				'mvr_dashboard_profile_menu_label'         => 'Profile',
				'mvr_dashboard_address_menu_label'         => 'Address',
				'mvr_dashboard_social_link_menu_label'     => 'Social Link',
				'mvr_dashboard_staff_menu_label'           => 'Staff',
				'mvr_dashboard_review_menu_label'          => 'Review',
				'mvr_dashboard_capabilities_menu_label'    => 'Capabilities',
				'mvr_dashboard_product_image_column_label' => 'Images',
				'mvr_dashboard_product_tags_column_label'  => 'Tags',
				'mvr_dashboard_product_price_column_label' => 'Price',
				'mvr_dashboard_product_details_column_label' => 'Product Details',
				'mvr_dashboard_product_actions_column_label' => 'Actions',
				'mvr_dashboard_product_categories_column_label' => 'Categories',
				'mvr_dashboard_product_add_new_label'      => 'Add New Product',
				'mvr_dashboard_product_search_label'       => 'Search Products',
				'mvr_dashboard_product_edit_label'         => 'Edit',
				'mvr_dashboard_product_view_label'         => 'View',
				'mvr_dashboard_product_delete_label'       => 'Delete',
				'mvr_dashboard_order_id_column_label'      => 'Order',
				'mvr_dashboard_order_date_column_label'    => 'Date',
				'mvr_dashboard_order_status_column_label'  => 'Status',
				'mvr_dashboard_order_total_column_label'   => 'Total',
				'mvr_dashboard_order_actions_column_label' => 'Actions',
				'mvr_dashboard_order_search_btn_label'     => 'Search Orders',
				'mvr_dashboard_view_order_btn_label'       => 'View',
				'mvr_dashboard_withdraw_id_column_label'   => 'ID',
				'mvr_dashboard_withdraw_status_column_label' => 'Status',
				'mvr_dashboard_withdraw_amount_column_label' => 'Amount',
				'mvr_dashboard_withdraw_charge_column_label' => 'Charge',
				'mvr_dashboard_withdraw_payment_column_label' => 'Payment',
				'mvr_dashboard_withdraw_date_column_label' => 'Date',
				'mvr_dashboard_withdraw_search_btn_label'  => 'Search',
				'mvr_dashboard_withdraw_total_amount_label' => 'Total Amount',
				'mvr_dashboard_withdraw_available_amount_label' => 'Available Amount',
				'mvr_dashboard_withdraw_locked_amount_label' => 'Locked Amount',
				'mvr_dashboard_withdraw_charge_label'      => 'Withdrawal Charge',
				'mvr_dashboard_withdraw_add_new_btn_label' => 'Add New Withdraw Request',
				'mvr_dashboard_withdraw_amount_field_label' => 'Enter the amount you wish to Withdraw',
				'mvr_dashboard_withdraw_submit_btn_label'  => 'Submit',
				'mvr_dashboard_transaction_id_column_label' => 'ID',
				'mvr_dashboard_transaction_status_column_label' => 'Status',
				'mvr_dashboard_transaction_amount_column_label' => 'Amount',
				'mvr_dashboard_transaction_type_column_label' => 'Type',
				'mvr_dashboard_transaction_desc_column_label' => 'Description',
				'mvr_dashboard_transaction_date_column_label' => 'Date',
				'mvr_dashboard_transaction_search_btn_label' => 'Search',
				'mvr_dashboard_transaction_amount_label'   => 'Amount',
				'mvr_dashboard_transaction_completed_amount_label' => 'Completed Amount',
				'mvr_dashboard_transaction_processing_amount_label' => 'Processing Amount',
				'mvr_dashboard_transaction_admin_commission_label' => 'Admin Commission',
				'mvr_dashboard_customer_email_column_label' => 'Email',
				'mvr_dashboard_customer_last_active_column_label' => 'Last Active',
				'mvr_dashboard_customer_register_date_column_label' => 'Register Date',
				'mvr_dashboard_customer_orders_column_label' => 'Orders',
				'mvr_dashboard_customer_total_spend_column_label' => 'Total Spend',
				'mvr_dashboard_customer_address_column_label' => 'Address',
				'mvr_dashboard_duplicate_product_image_column_label' => 'Images',
				'mvr_dashboard_duplicate_product_tags_column_label' => 'Tags',
				'mvr_dashboard_duplicate_product_price_column_label' => 'Price',
				'mvr_dashboard_duplicate_product_details_column_label' => 'Product Details',
				'mvr_dashboard_duplicate_product_actions_column_label' => 'Actions',
				'mvr_dashboard_duplicate_product_categories_column_label' => 'Categories',
				'mvr_dashboard_duplicate_product_add_new_label' => 'Add New Product',
				'mvr_dashboard_duplicate_product_search_label' => 'Search Products',
				'mvr_dashboard_duplicate_product_btn_label' => 'Duplicate',
				'mvr_dashboard_payment_method_field_label' => 'Payment Method',
				'mvr_dashboard_bank_account_name_field_label' => 'Account Name',
				'mvr_dashboard_bank_account_number_field_label' => 'Account Number',
				'mvr_dashboard_bank_account_type_field_label' => 'Account Type',
				'mvr_dashboard_bank_name_field_label'      => 'Bank Name',
				'mvr_dashboard_iban_field_label'           => 'IBAN',
				'mvr_dashboard_swift_field_label'          => 'SWIFT',
				'mvr_dashboard_paypal_email_field_label'   => 'PayPal Email',
				'mvr_dashboard_payout_type_field_label'    => 'Payout Type',
				'mvr_dashboard_payout_schedule_field_label' => 'Payout Schedule',
				'mvr_dashboard_store_logo_field_label'     => 'Store Logo',
				'mvr_dashboard_store_banner_field_label'   => 'Store Banner',
				'mvr_dashboard_vendor_name_field_label'    => 'Vendor Name',
				'mvr_dashboard_vendor_store_name_field_label' => 'Store Name',
				'mvr_dashboard_vendor_slug_field_label'    => 'Vendor Slug',
				'mvr_dashboard_vendor_email_field_label'   => 'Email Address',
				'mvr_dashboard_vendor_description_field_label' => 'Description',
				'mvr_dashboard_vendor_toc_field_label'     => 'Terms & Conditions',
				'mvr_dashboard_vendor_fname_field_label'   => 'First Name',
				'mvr_dashboard_vendor_lname_field_label'   => 'Last Name',
				'mvr_dashboard_vendor_addr1_field_label'   => 'Address 1',
				'mvr_dashboard_vendor_addr2_field_label'   => 'Address 2',
				'mvr_dashboard_vendor_city_field_label'    => 'City',
				'mvr_dashboard_vendor_country_field_label' => 'Country',
				'mvr_dashboard_vendor_state_field_label'   => 'State',
				'mvr_dashboard_vendor_zip_code_field_label' => 'Zip Code',
				'mvr_dashboard_vendor_phone_field_label'   => 'Phone',
				'mvr_dashboard_vendor_facebook_field_label' => 'Facebook',
				'mvr_dashboard_vendor_twitter_field_label' => 'X',
				'mvr_dashboard_vendor_youtube_field_label' => 'Youtube',
				'mvr_dashboard_vendor_instagram_field_label' => 'Instagram',
				'mvr_dashboard_vendor_linkedin_field_label' => 'Linkedin',
				'mvr_dashboard_vendor_pinterest_field_label' => 'Pinterest',
				'mvr_dashboard_staff_username_field_label' => 'Username',
				'mvr_dashboard_staff_user_email_field_label' => 'User Email',
				'mvr_dashboard_staff_password_field_label' => 'Create Password',
				'mvr_dashboard_staff_confirm_password_field_label' => 'Confirm Password',
				'mvr_dashboard_staff_image_column_label'   => 'Image',
				'mvr_dashboard_staff_name_column_label'    => 'Name',
				'mvr_dashboard_staff_date_column_label'    => 'Date',
				'mvr_dashboard_staff_actions_column_label' => 'Actions',
				'mvr_dashboard_add_staff_btn_label'        => 'Add New Staff',
				'mvr_dashboard_search_staff_btn_label'     => 'Search Staff',
				'mvr_dashboard_edit_staff_btn_label'       => 'Edit',
				'mvr_dashboard_delete_staff_btn_label'     => 'Delete',
				'mvr_dashboard_product_mng_header_label'   => 'Product Management',
				'mvr_dashboard_product_mng_field_label'    => 'Product Management',
				'mvr_dashboard_product_creation_field_label' => 'Product Creation',
				'mvr_dashboard_product_modi_field_label'   => 'Product Modification',
				'mvr_dashboard_pub_product_modi_field_label' => 'Published Product Modification',
				'mvr_dashboard_manage_inventory_field_label' => 'Manage Inventory',
				'mvr_dashboard_product_deletion_field_label' => 'Product Deletion',
				'mvr_dashboard_coupon_mng_header_label'    => 'Coupon Management',
				'mvr_dashboard_coupon_mng_field_label'     => 'Coupon Management',
				'mvr_dashboard_coupon_creation_field_label' => 'Coupon Creation',
				'mvr_dashboard_coupon_modi_field_label'    => 'Coupon Modification',
				'mvr_dashboard_pub_coupon_modi_field_label' => 'Published Coupon Modification',
				'mvr_dashboard_coupon_deletion_field_label' => 'Coupon Deletion',
				'mvr_dashboard_order_mng_header_label'     => 'Order Management',
				'mvr_dashboard_order_mng_field_label'      => 'Order Management',
				'mvr_dashboard_order_status_modi_field_label' => 'Order Status Modification',
				'mvr_dashboard_commission_info_field_label' => 'Commission Info Display',
				'mvr_dashboard_review_customer_column_label' => 'Customer',
				'mvr_dashboard_review_rating_column_label' => 'Rating',
				'mvr_dashboard_review_column_label'        => 'Review',
				'mvr_dashboard_review_date_label'          => 'Date',
				'mvr_dashboard_store_overview_tab_label'   => 'Overview',
				'mvr_dashboard_store_products_tab_label'   => 'Products',
				'mvr_dashboard_store_review_tab_label'     => 'Review',
				'mvr_dashboard_store_enquiry_tab_label'    => 'Enquiry',
				'mvr_dashboard_store_toc_tab_label'        => 'Terms & Conditions',
				'mvr_dashboard_spmv_title_label'           => 'Other Vendors to Consider',
				'mvr_dashboard_spmv_product_col_label'     => 'Product Name',
				'mvr_dashboard_spmv_price_col_label'       => 'Price',
				'mvr_dashboard_spmv_rating_col_label'      => 'Rating',
				'mvr_dashboard_spmv_action_col_label'      => 'Action',
			);
			$this->settings             = $this->get_settings();
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
			 * Get the admin settings array.
			 *
			 * @since 1.0.0
			 */
			return apply_filters(
				'mvr_get_' . $this->id . '_settings',
				array(
					array(
						'name' => __( 'Localization Settings', 'multi-vendor-marketplace' ),
						'type' => 'title',
						'id'   => 'mvr_localization_settings',
					),
					array( 'type' => $this->get_custom_field_type( 'get_localization' ) ),
					array(
						'type' => 'sectionend',
						'id'   => 'mvr_localization_settings',
					),
				)
			);
		}

		/**
		 * Custom type field.
		 */
		public function get_localization() {
			/**
			 * Get the admin settings sections.
			 *
			 * @since 1.0.0
			 */
			$localization_sections = apply_filters(
				'mvr_admin_settings_localization_sections',
				array(
					'dashboard'    => __( 'Dashboard', 'multi-vendor-marketplace' ),
					'products'     => __( 'Products', 'multi-vendor-marketplace' ),
					'orders'       => __( 'Orders', 'multi-vendor-marketplace' ),
					'withdraw'     => __( 'Withdraw', 'multi-vendor-marketplace' ),
					'transactions' => __( 'Transactions', 'multi-vendor-marketplace' ),
					'customers'    => __( 'Customers', 'multi-vendor-marketplace' ),
					'duplicate'    => __( 'Duplicate', 'multi-vendor-marketplace' ),
					'payments'     => __( 'Payments', 'multi-vendor-marketplace' ),
					'payout'       => __( 'Payout', 'multi-vendor-marketplace' ),
					'profile'      => __( 'Profile', 'multi-vendor-marketplace' ),
					'address'      => __( 'Address', 'multi-vendor-marketplace' ),
					'social_link'  => __( 'Social Link', 'multi-vendor-marketplace' ),
					'staff'        => __( 'Staff', 'multi-vendor-marketplace' ),
					'review'       => __( 'Review', 'multi-vendor-marketplace' ),
					'capabilities' => __( 'Capabilities', 'multi-vendor-marketplace' ),
					'single_store' => __( 'Single Store', 'multi-vendor-marketplace' ),
					'spmv'         => __( 'Single Product Multi Vendor', 'multi-vendor-marketplace' ),
				)
			);
			?>
		<div id="mvr_settings_localization" class="postbox">
			<div class="inside">
				<div class="panel-wrap">
					<ul class="mvr_settings_localization_tabs wc-tabs">
					<?php foreach ( $localization_sections as $section_key => $section_label ) { ?>
							<li class="<?php echo esc_attr( $section_key ); ?>_section active">
								<a href="#<?php echo esc_attr( $section_key ); ?>_section_localization">
									<span><?php echo esc_html( $section_label ); ?></span>
								</a>
							</li>
						<?php } ?>
					</ul>

				<?php foreach ( $localization_sections as $section_key => $section_label ) { ?>
						<div id="<?php echo esc_attr( $section_key ); ?>_section_localization" class="panel woocommerce_options_panel">
							<?php
							switch ( $section_key ) {
								case 'dashboard':
									?>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_home_menu_label"> <?php esc_html_e( 'Home Menu Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_home_menu_label" id="mvr_dashboard_home_menu_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_home_menu_label', $this->custom_fields_option['mvr_dashboard_home_menu_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_products_menu_label"> <?php esc_html_e( 'Products Menu Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_products_menu_label" id="mvr_dashboard_products_menu_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_products_menu_label', $this->custom_fields_option['mvr_dashboard_products_menu_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_orders_menu_label"> <?php esc_html_e( 'Orders Menu Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_orders_menu_label" id="mvr_dashboard_orders_menu_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_orders_menu_label', $this->custom_fields_option['mvr_dashboard_orders_menu_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_coupons_menu_label"> <?php esc_html_e( 'Coupons Menu Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_coupons_menu_label" id="mvr_dashboard_coupons_menu_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_coupons_menu_label', $this->custom_fields_option['mvr_dashboard_coupons_menu_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_withdraw_menu_label"> <?php esc_html_e( 'Withdraw Menu Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_withdraw_menu_label" id="mvr_dashboard_withdraw_menu_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_withdraw_menu_label', $this->custom_fields_option['mvr_dashboard_withdraw_menu_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_transactions_menu_label"> <?php esc_html_e( 'Transactions Menu Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_transactions_menu_label" id="mvr_dashboard_transactions_menu_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_transactions_menu_label', $this->custom_fields_option['mvr_dashboard_transactions_menu_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_customers_menu_label"> <?php esc_html_e( 'Customers Menu Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_customers_menu_label" id="mvr_dashboard_customers_menu_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_customers_menu_label', $this->custom_fields_option['mvr_dashboard_customers_menu_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_duplicate_menu_label"> <?php esc_html_e( 'Duplicate Menu Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_duplicate_menu_label" id="mvr_dashboard_duplicate_menu_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_duplicate_menu_label', $this->custom_fields_option['mvr_dashboard_duplicate_menu_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_payments_menu_label"> <?php esc_html_e( 'Payments Menu Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_payments_menu_label" id="mvr_dashboard_payments_menu_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_payments_menu_label', $this->custom_fields_option['mvr_dashboard_payments_menu_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_payout_menu_label"> <?php esc_html_e( 'Payout Menu Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_payout_menu_label" id="mvr_dashboard_payout_menu_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_payout_menu_label', $this->custom_fields_option['mvr_dashboard_payout_menu_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_profile_menu_label"> <?php esc_html_e( 'Profile Menu Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_profile_menu_label" id="mvr_dashboard_profile_menu_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_profile_menu_label', $this->custom_fields_option['mvr_dashboard_profile_menu_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_address_menu_label"> <?php esc_html_e( 'Address Menu Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_address_menu_label" id="mvr_dashboard_address_menu_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_address_menu_label', $this->custom_fields_option['mvr_dashboard_address_menu_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_social_link_menu_label"> <?php esc_html_e( 'Social Links Menu Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_social_link_menu_label" id="mvr_dashboard_social_link_menu_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_social_link_menu_label', $this->custom_fields_option['mvr_dashboard_social_link_menu_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_staff_menu_label"> <?php esc_html_e( 'Staff Menu Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_staff_menu_label" id="mvr_dashboard_staff_menu_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_staff_menu_label', $this->custom_fields_option['mvr_dashboard_staff_menu_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_review_menu_label"> <?php esc_html_e( 'Review Menu Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_review_menu_label" id="mvr_dashboard_review_menu_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_review_menu_label', $this->custom_fields_option['mvr_dashboard_review_menu_label'] ) ); ?>"/>
										<br>
									</p>
									<?php
									break;
								case 'products':
									?>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_product_image_column_label"> <?php esc_html_e( 'Product Image Column Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_product_image_column_label" id="mvr_dashboard_product_image_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_product_image_column_label', $this->custom_fields_option['mvr_dashboard_product_image_column_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_product_details_column_label"> <?php esc_html_e( 'Product Details Column Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_product_details_column_label" id="mvr_dashboard_product_details_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_product_details_column_label', $this->custom_fields_option['mvr_dashboard_product_details_column_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_product_price_column_label"> <?php esc_html_e( 'Product Price Column Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_product_price_column_label" id="mvr_dashboard_product_price_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_product_price_column_label', $this->custom_fields_option['mvr_dashboard_product_price_column_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_product_categories_column_label"> <?php esc_html_e( 'Product Categories Column Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_product_categories_column_label" id="mvr_dashboard_product_categories_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_product_categories_column_label', $this->custom_fields_option['mvr_dashboard_product_categories_column_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_product_tags_column_label"> <?php esc_html_e( 'Product Tags Column Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_product_tags_column_label" id="mvr_dashboard_product_tags_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_product_tags_column_label', $this->custom_fields_option['mvr_dashboard_product_tags_column_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_product_actions_column_label"> <?php esc_html_e( 'Product Actions Column Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_product_actions_column_label" id="mvr_dashboard_product_actions_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_product_actions_column_label', $this->custom_fields_option['mvr_dashboard_product_actions_column_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_product_add_new_label"> <?php esc_html_e( 'Add New Product Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_product_add_new_label" id="mvr_dashboard_product_add_new_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_product_add_new_label', $this->custom_fields_option['mvr_dashboard_product_add_new_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_product_search_label"> <?php esc_html_e( 'Search Product Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_product_search_label" id="mvr_dashboard_product_search_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_product_search_label', $this->custom_fields_option['mvr_dashboard_product_search_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_product_edit_label"> <?php esc_html_e( 'Edit Product Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_product_edit_label" id="mvr_dashboard_product_edit_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_product_edit_label', $this->custom_fields_option['mvr_dashboard_product_edit_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_product_view_label"> <?php esc_html_e( 'View Product Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_product_view_label" id="mvr_dashboard_product_view_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_product_view_label', $this->custom_fields_option['mvr_dashboard_product_view_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_product_delete_label"> <?php esc_html_e( 'Delete Product Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_product_delete_label" id="mvr_dashboard_product_delete_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_product_delete_label', $this->custom_fields_option['mvr_dashboard_product_delete_label'] ) ); ?>"/>
										<br>
									</p>
									<?php
									break;
								case 'orders':
									?>
										<p class="mvr-form-field">
											<label for="mvr_dashboard_order_id_column_label"> <?php esc_html_e( 'Order ID Column Label', 'multi-vendor-marketplace' ); ?> </label>
											<input type="text" name="mvr_dashboard_order_id_column_label" id="mvr_dashboard_order_id_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_order_id_column_label', $this->custom_fields_option['mvr_dashboard_order_id_column_label'] ) ); ?>"/>
											<br>
										</p>
										<p class="mvr-form-field">
											<label for="mvr_dashboard_order_date_column_label"> <?php esc_html_e( 'Order Date Column Label', 'multi-vendor-marketplace' ); ?> </label>
											<input type="text" name="mvr_dashboard_order_date_column_label" id="mvr_dashboard_order_date_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_order_date_column_label', $this->custom_fields_option['mvr_dashboard_order_date_column_label'] ) ); ?>"/>
											<br>
										</p>
										<p class="mvr-form-field">
											<label for="mvr_dashboard_order_status_column_label"> <?php esc_html_e( 'Order Status Column Label', 'multi-vendor-marketplace' ); ?> </label>
											<input type="text" name="mvr_dashboard_order_status_column_label" id="mvr_dashboard_order_status_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_order_status_column_label', $this->custom_fields_option['mvr_dashboard_order_status_column_label'] ) ); ?>"/>
											<br>
										</p>
										<p class="mvr-form-field">
											<label for="mvr_dashboard_order_total_column_label"> <?php esc_html_e( 'Order Total Column Label', 'multi-vendor-marketplace' ); ?> </label>
											<input type="text" name="mvr_dashboard_order_total_column_label" id="mvr_dashboard_order_total_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_order_total_column_label', $this->custom_fields_option['mvr_dashboard_order_total_column_label'] ) ); ?>"/>
											<br>
										</p>
										<p class="mvr-form-field">
											<label for="mvr_dashboard_order_actions_column_label"> <?php esc_html_e( 'Order Actions Column Label', 'multi-vendor-marketplace' ); ?> </label>
											<input type="text" name="mvr_dashboard_order_actions_column_label" id="mvr_dashboard_order_actions_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_order_actions_column_label', $this->custom_fields_option['mvr_dashboard_order_actions_column_label'] ) ); ?>"/>
											<br>
										</p>
										<p class="mvr-form-field">
											<label for="mvr_dashboard_order_search_btn_label"> <?php esc_html_e( 'Order Search Button Label', 'multi-vendor-marketplace' ); ?> </label>
											<input type="text" name="mvr_dashboard_order_search_btn_label" id="mvr_dashboard_order_search_btn_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_order_search_btn_label', $this->custom_fields_option['mvr_dashboard_order_search_btn_label'] ) ); ?>"/>
											<br>
										</p>
										<p class="mvr-form-field">
											<label for="mvr_dashboard_view_order_btn_label"> <?php esc_html_e( 'Order View Button Label', 'multi-vendor-marketplace' ); ?> </label>
											<input type="text" name="mvr_dashboard_view_order_btn_label" id="mvr_dashboard_view_order_btn_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_view_order_btn_label', $this->custom_fields_option['mvr_dashboard_view_order_btn_label'] ) ); ?>"/>
											<br>
										</p>
										<?php
									break;
								case 'withdraw':
									?>
										<p class="mvr-form-field">
											<label for="mvr_dashboard_withdraw_id_column_label"> <?php esc_html_e( 'Withdraw ID Column Label', 'multi-vendor-marketplace' ); ?> </label>
											<input type="text" name="mvr_dashboard_withdraw_id_column_label" id="mvr_dashboard_withdraw_id_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_withdraw_id_column_label', $this->custom_fields_option['mvr_dashboard_withdraw_id_column_label'] ) ); ?>"/>
											<br>
										</p>
										<p class="mvr-form-field">
											<label for="mvr_dashboard_withdraw_status_column_label"> <?php esc_html_e( 'Withdraw Status Column Label', 'multi-vendor-marketplace' ); ?> </label>
											<input type="text" name="mvr_dashboard_withdraw_status_column_label" id="mvr_dashboard_withdraw_status_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_withdraw_status_column_label', $this->custom_fields_option['mvr_dashboard_withdraw_status_column_label'] ) ); ?>"/>
											<br>
										</p>
										<p class="mvr-form-field">
											<label for="mvr_dashboard_withdraw_amount_column_label"> <?php esc_html_e( 'Withdraw Amount Column Label', 'multi-vendor-marketplace' ); ?> </label>
											<input type="text" name="mvr_dashboard_withdraw_amount_column_label" id="mvr_dashboard_withdraw_amount_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_withdraw_amount_column_label', $this->custom_fields_option['mvr_dashboard_withdraw_amount_column_label'] ) ); ?>"/>
											<br>
										</p>
										<p class="mvr-form-field">
											<label for="mvr_dashboard_withdraw_charge_column_label"> <?php esc_html_e( 'Withdraw Charge Column Label', 'multi-vendor-marketplace' ); ?> </label>
											<input type="text" name="mvr_dashboard_withdraw_charge_column_label" id="mvr_dashboard_withdraw_charge_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_withdraw_charge_column_label', $this->custom_fields_option['mvr_dashboard_withdraw_charge_column_label'] ) ); ?>"/>
											<br>
										</p>
										<p class="mvr-form-field">
											<label for="mvr_dashboard_withdraw_payment_column_label"> <?php esc_html_e( 'Withdraw Payment Column Label', 'multi-vendor-marketplace' ); ?> </label>
											<input type="text" name="mvr_dashboard_withdraw_payment_column_label" id="mvr_dashboard_withdraw_payment_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_withdraw_payment_column_label', $this->custom_fields_option['mvr_dashboard_withdraw_payment_column_label'] ) ); ?>"/>
											<br>
										</p>
										<p class="mvr-form-field">
											<label for="mvr_dashboard_withdraw_date_column_label"> <?php esc_html_e( 'Withdraw Date Column Label', 'multi-vendor-marketplace' ); ?> </label>
											<input type="text" name="mvr_dashboard_withdraw_date_column_label" id="mvr_dashboard_withdraw_date_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_withdraw_date_column_label', $this->custom_fields_option['mvr_dashboard_withdraw_date_column_label'] ) ); ?>"/>
											<br>
										</p>
										<p class="mvr-form-field">
											<label for="mvr_dashboard_withdraw_search_btn_label"> <?php esc_html_e( 'Withdraw Search Button Label', 'multi-vendor-marketplace' ); ?> </label>
											<input type="text" name="mvr_dashboard_withdraw_search_btn_label" id="mvr_dashboard_withdraw_search_btn_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_withdraw_search_btn_label', $this->custom_fields_option['mvr_dashboard_withdraw_search_btn_label'] ) ); ?>"/>
											<br>
										</p>
										<p class="mvr-form-field">
											<label for="mvr_dashboard_withdraw_total_amount_label"> <?php esc_html_e( 'Withdraw Total Amount Label', 'multi-vendor-marketplace' ); ?> </label>
											<input type="text" name="mvr_dashboard_withdraw_total_amount_label" id="mvr_dashboard_withdraw_total_amount_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_withdraw_total_amount_label', $this->custom_fields_option['mvr_dashboard_withdraw_total_amount_label'] ) ); ?>"/>
											<br>
										</p>
										<p class="mvr-form-field">
											<label for="mvr_dashboard_withdraw_available_amount_label"> <?php esc_html_e( 'Withdraw Available Amount Label', 'multi-vendor-marketplace' ); ?> </label>
											<input type="text" name="mvr_dashboard_withdraw_available_amount_label" id="mvr_dashboard_withdraw_available_amount_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_withdraw_available_amount_label', $this->custom_fields_option['mvr_dashboard_withdraw_available_amount_label'] ) ); ?>"/>
											<br>
										</p>
										<p class="mvr-form-field">
											<label for="mvr_dashboard_withdraw_locked_amount_label"> <?php esc_html_e( 'Withdraw Locked Amount Label', 'multi-vendor-marketplace' ); ?> </label>
											<input type="text" name="mvr_dashboard_withdraw_locked_amount_label" id="mvr_dashboard_withdraw_locked_amount_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_withdraw_locked_amount_label', $this->custom_fields_option['mvr_dashboard_withdraw_locked_amount_label'] ) ); ?>"/>
											<br>
										</p>
										<p class="mvr-form-field">
											<label for="mvr_dashboard_withdraw_charge_label"> <?php esc_html_e( 'Withdrawal Charge Label', 'multi-vendor-marketplace' ); ?> </label>
											<input type="text" name="mvr_dashboard_withdraw_charge_label" id="mvr_dashboard_withdraw_charge_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_withdraw_charge_label', $this->custom_fields_option['mvr_dashboard_withdraw_charge_label'] ) ); ?>"/>
											<br>
										</p>
										<p class="mvr-form-field">
											<label for="mvr_dashboard_withdraw_add_new_btn_label"> <?php esc_html_e( 'Add New Withdrawal Label', 'multi-vendor-marketplace' ); ?> </label>
											<input type="text" name="mvr_dashboard_withdraw_add_new_btn_label" id="mvr_dashboard_withdraw_add_new_btn_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_withdraw_add_new_btn_label', $this->custom_fields_option['mvr_dashboard_withdraw_add_new_btn_label'] ) ); ?>"/>
											<br>
										</p>
										<p class="mvr-form-field">
											<label for="mvr_dashboard_withdraw_amount_field_label"> <?php esc_html_e( 'Withdrawal Amount Field Label', 'multi-vendor-marketplace' ); ?> </label>
											<input type="text" name="mvr_dashboard_withdraw_amount_field_label" id="mvr_dashboard_withdraw_amount_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_withdraw_amount_field_label', $this->custom_fields_option['mvr_dashboard_withdraw_amount_field_label'] ) ); ?>"/>
											<br>
										</p>
										<p class="mvr-form-field">
											<label for="mvr_dashboard_withdraw_submit_btn_label"> <?php esc_html_e( 'Withdrawal Submit Button Label', 'multi-vendor-marketplace' ); ?> </label>
											<input type="text" name="mvr_dashboard_withdraw_submit_btn_label" id="mvr_dashboard_withdraw_submit_btn_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_withdraw_submit_btn_label', $this->custom_fields_option['mvr_dashboard_withdraw_submit_btn_label'] ) ); ?>"/>
											<br>
										</p>
										<?php
									break;
								case 'transactions':
									?>
										<p class="mvr-form-field">
											<label for="mvr_dashboard_transaction_id_column_label"> <?php esc_html_e( 'Transaction ID Column Label', 'multi-vendor-marketplace' ); ?> </label>
											<input type="text" name="mvr_dashboard_transaction_id_column_label" id="mvr_dashboard_transaction_id_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_transaction_id_column_label', $this->custom_fields_option['mvr_dashboard_transaction_id_column_label'] ) ); ?>"/>
											<br>
										</p>
										<p class="mvr-form-field">
											<label for="mvr_dashboard_transaction_status_column_label"> <?php esc_html_e( 'Transaction Status Column Label', 'multi-vendor-marketplace' ); ?> </label>
											<input type="text" name="mvr_dashboard_transaction_status_column_label" id="mvr_dashboard_transaction_status_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_transaction_status_column_label', $this->custom_fields_option['mvr_dashboard_transaction_status_column_label'] ) ); ?>"/>
											<br>
										</p>
										<p class="mvr-form-field">
											<label for="mvr_dashboard_transaction_amount_column_label"> <?php esc_html_e( 'Transaction Amount Column Label', 'multi-vendor-marketplace' ); ?> </label>
											<input type="text" name="mvr_dashboard_transaction_amount_column_label" id="mvr_dashboard_transaction_amount_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_transaction_amount_column_label', $this->custom_fields_option['mvr_dashboard_transaction_amount_column_label'] ) ); ?>"/>
											<br>
										</p>
										<p class="mvr-form-field">
											<label for="mvr_dashboard_transaction_desc_column_label"> <?php esc_html_e( 'Transaction Description Column Label', 'multi-vendor-marketplace' ); ?> </label>
											<input type="text" name="mvr_dashboard_transaction_desc_column_label" id="mvr_dashboard_transaction_desc_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_transaction_desc_column_label', $this->custom_fields_option['mvr_dashboard_transaction_desc_column_label'] ) ); ?>"/>
											<br>
										</p>
										<p class="mvr-form-field">
											<label for="mvr_dashboard_transaction_date_column_label"> <?php esc_html_e( 'Transaction Date Column Label', 'multi-vendor-marketplace' ); ?> </label>
											<input type="text" name="mvr_dashboard_transaction_date_column_label" id="mvr_dashboard_transaction_date_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_transaction_date_column_label', $this->custom_fields_option['mvr_dashboard_transaction_date_column_label'] ) ); ?>"/>
											<br>
										</p>
										<p class="mvr-form-field">
											<label for="mvr_dashboard_transaction_search_btn_label"> <?php esc_html_e( 'Transaction Search Button Label', 'multi-vendor-marketplace' ); ?> </label>
											<input type="text" name="mvr_dashboard_transaction_search_btn_label" id="mvr_dashboard_transaction_search_btn_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_transaction_search_btn_label', $this->custom_fields_option['mvr_dashboard_transaction_search_btn_label'] ) ); ?>"/>
											<br>
										</p>
										<p class="mvr-form-field">
											<label for="mvr_dashboard_transaction_amount_label"> <?php esc_html_e( 'Transaction Total Amount Label', 'multi-vendor-marketplace' ); ?> </label>
											<input type="text" name="mvr_dashboard_transaction_amount_label" id="mvr_dashboard_transaction_amount_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_transaction_amount_label', $this->custom_fields_option['mvr_dashboard_transaction_amount_label'] ) ); ?>"/>
											<br>
										</p>
										<p class="mvr-form-field">
											<label for="mvr_dashboard_transaction_completed_amount_label"> <?php esc_html_e( 'Transaction Completed Amount Label', 'multi-vendor-marketplace' ); ?> </label>
											<input type="text" name="mvr_dashboard_transaction_completed_amount_label" id="mvr_dashboard_transaction_completed_amount_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_transaction_completed_amount_label', $this->custom_fields_option['mvr_dashboard_transaction_completed_amount_label'] ) ); ?>"/>
											<br>
										</p>
										<p class="mvr-form-field">
											<label for="mvr_dashboard_transaction_processing_amount_label"> <?php esc_html_e( 'Transaction Processing Amount Label', 'multi-vendor-marketplace' ); ?> </label>
											<input type="text" name="mvr_dashboard_transaction_processing_amount_label" id="mvr_dashboard_transaction_processing_amount_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_transaction_processing_amount_label', $this->custom_fields_option['mvr_dashboard_transaction_processing_amount_label'] ) ); ?>"/>
											<br>
										</p>
										<p class="mvr-form-field">
											<label for="mvr_dashboard_transaction_admin_commission_label"> <?php esc_html_e( 'Transaction Admin Commission Label', 'multi-vendor-marketplace' ); ?> </label>
											<input type="text" name="mvr_dashboard_transaction_admin_commission_label" id="mvr_dashboard_transaction_admin_commission_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_transaction_admin_commission_label', $this->custom_fields_option['mvr_dashboard_transaction_admin_commission_label'] ) ); ?>"/>
											<br>
										</p>
										<?php
									break;
								case 'customers':
									?>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_customer_email_column_label"> <?php esc_html_e( 'Customer Email Column Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_customer_email_column_label" id="mvr_dashboard_customer_email_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_customer_email_column_label', $this->custom_fields_option['mvr_dashboard_customer_email_column_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_customer_last_active_column_label"> <?php esc_html_e( 'Customer Last Active Column Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_customer_last_active_column_label" id="mvr_dashboard_customer_last_active_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_customer_last_active_column_label', $this->custom_fields_option['mvr_dashboard_customer_last_active_column_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_customer_register_date_column_label"> <?php esc_html_e( 'Customer Register Date Column Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_customer_register_date_column_label" id="mvr_dashboard_customer_register_date_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_customer_register_date_column_label', $this->custom_fields_option['mvr_dashboard_customer_register_date_column_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_customer_orders_column_label"> <?php esc_html_e( 'Customer Orders Column Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_customer_orders_column_label" id="mvr_dashboard_customer_orders_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_customer_orders_column_label', $this->custom_fields_option['mvr_dashboard_customer_orders_column_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_customer_total_spend_column_label"> <?php esc_html_e( 'Customer Total Spend Column Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_customer_total_spend_column_label" id="mvr_dashboard_customer_total_spend_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_customer_total_spend_column_label', $this->custom_fields_option['mvr_dashboard_customer_total_spend_column_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_customer_address_column_label"> <?php esc_html_e( 'Customer Address Button Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_customer_address_column_label" id="mvr_dashboard_customer_address_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_customer_address_column_label', $this->custom_fields_option['mvr_dashboard_customer_address_column_label'] ) ); ?>"/>
										<br>
									</p>
									<?php
									break;
								case 'duplicate':
									?>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_duplicate_product_image_column_label"> <?php esc_html_e( 'Duplicate Product Image Column Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_duplicate_product_image_column_label" id="mvr_dashboard_duplicate_product_image_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_duplicate_product_image_column_label', $this->custom_fields_option['mvr_dashboard_duplicate_product_image_column_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_duplicate_product_details_column_label"> <?php esc_html_e( 'Duplicate Product Details Column Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_duplicate_product_details_column_label" id="mvr_dashboard_duplicate_product_details_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_duplicate_product_details_column_label', $this->custom_fields_option['mvr_dashboard_duplicate_product_details_column_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_duplicate_product_price_column_label"> <?php esc_html_e( 'Duplicate Product Price Column Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_duplicate_product_price_column_label" id="mvr_dashboard_duplicate_product_price_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_duplicate_product_price_column_label', $this->custom_fields_option['mvr_dashboard_duplicate_product_price_column_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_duplicate_product_categories_column_label"> <?php esc_html_e( 'Duplicate Product Categories Column Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_duplicate_product_categories_column_label" id="mvr_dashboard_duplicate_product_categories_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_duplicate_product_categories_column_label', $this->custom_fields_option['mvr_dashboard_duplicate_product_categories_column_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_duplicate_product_tags_column_label"> <?php esc_html_e( 'Duplicate Product Tags Column Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_duplicate_product_tags_column_label" id="mvr_dashboard_duplicate_product_tags_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_duplicate_product_tags_column_label', $this->custom_fields_option['mvr_dashboard_duplicate_product_tags_column_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_duplicate_product_actions_column_label"> <?php esc_html_e( 'Duplicate Product Actions Column Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_duplicate_product_actions_column_label" id="mvr_dashboard_duplicate_product_actions_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_duplicate_product_actions_column_label', $this->custom_fields_option['mvr_dashboard_duplicate_product_actions_column_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_duplicate_product_search_label"> <?php esc_html_e( 'Search Duplicate Product Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_duplicate_product_search_label" id="mvr_dashboard_duplicate_product_search_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_duplicate_product_search_label', $this->custom_fields_option['mvr_dashboard_duplicate_product_search_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_duplicate_product_btn_label"> <?php esc_html_e( 'Duplicate Product Button Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_duplicate_product_btn_label" id="mvr_dashboard_duplicate_product_btn_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_duplicate_product_btn_label', $this->custom_fields_option['mvr_dashboard_duplicate_product_btn_label'] ) ); ?>"/>
										<br>
									</p>
									<?php
									break;
								case 'payments':
									?>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_payment_method_field_label"> <?php esc_html_e( 'Payment Method Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_payment_method_field_label" id="mvr_dashboard_payment_method_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_payment_method_field_label', $this->custom_fields_option['mvr_dashboard_payment_method_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_bank_account_name_field_label"> <?php esc_html_e( 'Account Name Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_bank_account_name_field_label" id="mvr_dashboard_bank_account_name_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_bank_account_name_field_label', $this->custom_fields_option['mvr_dashboard_bank_account_name_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_bank_account_number_field_label"> <?php esc_html_e( 'Account Number Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_bank_account_number_field_label" id="mvr_dashboard_bank_account_number_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_bank_account_number_field_label', $this->custom_fields_option['mvr_dashboard_bank_account_number_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_bank_account_type_field_label"> <?php esc_html_e( 'Account Type Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_bank_account_type_field_label" id="mvr_dashboard_bank_account_type_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_bank_account_type_field_label', $this->custom_fields_option['mvr_dashboard_bank_account_type_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_bank_name_field_label"> <?php esc_html_e( 'Bank Name Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_bank_name_field_label" id="mvr_dashboard_bank_name_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_bank_name_field_label', $this->custom_fields_option['mvr_dashboard_bank_name_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_iban_field_label"> <?php esc_html_e( 'IBAN Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_iban_field_label" id="mvr_dashboard_iban_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_iban_field_label', $this->custom_fields_option['mvr_dashboard_iban_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_swift_field_label"> <?php esc_html_e( 'SWIFT Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_swift_field_label" id="mvr_dashboard_swift_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_swift_field_label', $this->custom_fields_option['mvr_dashboard_swift_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_paypal_email_field_label"> <?php esc_html_e( 'PayPal Email Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_paypal_email_field_label" id="mvr_dashboard_paypal_email_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_paypal_email_field_label', $this->custom_fields_option['mvr_dashboard_paypal_email_field_label'] ) ); ?>"/>
										<br>
									</p>
									<?php
									break;
								case 'payout':
									?>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_payout_type_field_label"> <?php esc_html_e( 'Payout Type Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_payout_type_field_label" id="mvr_dashboard_payout_type_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_payout_type_field_label', $this->custom_fields_option['mvr_dashboard_payout_type_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_payout_schedule_field_label"> <?php esc_html_e( 'Payout Schedule Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_payout_schedule_field_label" id="mvr_dashboard_payout_schedule_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_payout_schedule_field_label', $this->custom_fields_option['mvr_dashboard_payout_schedule_field_label'] ) ); ?>"/>
										<br>
									</p>
									<?php
									break;
								case 'profile':
									?>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_store_logo_field_label"> <?php esc_html_e( 'Vendor Store Logo Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_store_logo_field_label" id="mvr_dashboard_store_logo_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_store_logo_field_label', $this->custom_fields_option['mvr_dashboard_store_logo_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_store_banner_field_label"> <?php esc_html_e( 'Vendor Store Banner Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_store_banner_field_label" id="mvr_dashboard_store_banner_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_store_banner_field_label', $this->custom_fields_option['mvr_dashboard_store_banner_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_vendor_name_field_label"> <?php esc_html_e( 'Vendor Name Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_vendor_name_field_label" id="mvr_dashboard_vendor_name_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_vendor_name_field_label', $this->custom_fields_option['mvr_dashboard_vendor_name_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_vendor_store_name_field_label"> <?php esc_html_e( 'Vendor Store Name Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_vendor_store_name_field_label" id="mvr_dashboard_vendor_store_name_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_vendor_store_name_field_label', $this->custom_fields_option['mvr_dashboard_vendor_store_name_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_vendor_slug_field_label"> <?php esc_html_e( 'Vendor Slug Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_vendor_slug_field_label" id="mvr_dashboard_vendor_slug_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_vendor_slug_field_label', $this->custom_fields_option['mvr_dashboard_vendor_slug_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_vendor_email_field_label"> <?php esc_html_e( 'Vendor Email Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_vendor_email_field_label" id="mvr_dashboard_vendor_email_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_vendor_email_field_label', $this->custom_fields_option['mvr_dashboard_vendor_email_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_vendor_description_field_label"> <?php esc_html_e( 'Vendor Description Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_vendor_description_field_label" id="mvr_dashboard_vendor_description_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_vendor_description_field_label', $this->custom_fields_option['mvr_dashboard_vendor_description_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_vendor_toc_field_label"> <?php esc_html_e( 'Vendor Terms & Conditions Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_vendor_toc_field_label" id="mvr_dashboard_vendor_toc_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_vendor_toc_field_label', $this->custom_fields_option['mvr_dashboard_vendor_toc_field_label'] ) ); ?>"/>
										<br>
									</p>
									<?php
									break;
								case 'address':
									?>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_vendor_fname_field_label"> <?php esc_html_e( 'First Name Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_vendor_fname_field_label" id="mvr_dashboard_vendor_fname_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_vendor_fname_field_label', $this->custom_fields_option['mvr_dashboard_vendor_fname_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_vendor_lname_field_label"> <?php esc_html_e( 'Last Name Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_vendor_lname_field_label" id="mvr_dashboard_vendor_lname_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_vendor_lname_field_label', $this->custom_fields_option['mvr_dashboard_vendor_lname_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_vendor_addr1_field_label"> <?php esc_html_e( 'Address 1 Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_vendor_addr1_field_label" id="mvr_dashboard_vendor_addr1_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_vendor_addr1_field_label', $this->custom_fields_option['mvr_dashboard_vendor_addr1_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_vendor_addr2_field_label"> <?php esc_html_e( 'Address 2 Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_vendor_addr2_field_label" id="mvr_dashboard_vendor_addr2_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_vendor_addr2_field_label', $this->custom_fields_option['mvr_dashboard_vendor_addr2_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_vendor_city_field_label"> <?php esc_html_e( 'City Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_vendor_city_field_label" id="mvr_dashboard_vendor_city_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_vendor_city_field_label', $this->custom_fields_option['mvr_dashboard_vendor_city_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_vendor_country_field_label"> <?php esc_html_e( 'Country Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_vendor_country_field_label" id="mvr_dashboard_vendor_country_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_vendor_country_field_label', $this->custom_fields_option['mvr_dashboard_vendor_country_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_vendor_state_field_label"> <?php esc_html_e( 'State Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_vendor_state_field_label" id="mvr_dashboard_vendor_state_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_vendor_state_field_label', $this->custom_fields_option['mvr_dashboard_vendor_state_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_vendor_zip_code_field_label"> <?php esc_html_e( 'Zip Code Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_vendor_zip_code_field_label" id="mvr_dashboard_vendor_zip_code_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_vendor_zip_code_field_label', $this->custom_fields_option['mvr_dashboard_vendor_zip_code_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_vendor_phone_field_label"> <?php esc_html_e( 'Phone Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_vendor_phone_field_label" id="mvr_dashboard_vendor_phone_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_vendor_phone_field_label', $this->custom_fields_option['mvr_dashboard_vendor_phone_field_label'] ) ); ?>"/>
										<br>
									</p>
									<?php
									break;
								case 'social_link':
									?>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_vendor_facebook_field_label"> <?php esc_html_e( 'Facebook Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_vendor_facebook_field_label" id="mvr_dashboard_vendor_facebook_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_vendor_facebook_field_label', $this->custom_fields_option['mvr_dashboard_vendor_facebook_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_vendor_twitter_field_label"> <?php esc_html_e( 'X Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_vendor_twitter_field_label" id="mvr_dashboard_vendor_twitter_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_vendor_twitter_field_label', $this->custom_fields_option['mvr_dashboard_vendor_twitter_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_vendor_youtube_field_label"> <?php esc_html_e( 'Youtube Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_vendor_youtube_field_label" id="mvr_dashboard_vendor_youtube_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_vendor_youtube_field_label', $this->custom_fields_option['mvr_dashboard_vendor_youtube_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_vendor_instagram_field_label"> <?php esc_html_e( 'Instagram Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_vendor_instagram_field_label" id="mvr_dashboard_vendor_instagram_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_vendor_instagram_field_label', $this->custom_fields_option['mvr_dashboard_vendor_instagram_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_vendor_linkedin_field_label"> <?php esc_html_e( 'Linkedin Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_vendor_linkedin_field_label" id="mvr_dashboard_vendor_linkedin_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_vendor_linkedin_field_label', $this->custom_fields_option['mvr_dashboard_vendor_linkedin_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_vendor_pinterest_field_label"> <?php esc_html_e( 'Pinterest Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_vendor_pinterest_field_label" id="mvr_dashboard_vendor_pinterest_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_vendor_pinterest_field_label', $this->custom_fields_option['mvr_dashboard_vendor_pinterest_field_label'] ) ); ?>"/>
										<br>
									</p>
									<?php
									break;
								case 'staff':
									?>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_staff_image_column_label"> <?php esc_html_e( 'Staff Image Column Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_staff_image_column_label" id="mvr_dashboard_staff_image_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_staff_image_column_label', $this->custom_fields_option['mvr_dashboard_staff_image_column_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_staff_name_column_label"> <?php esc_html_e( 'Staff Name Column Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_staff_name_column_label" id="mvr_dashboard_staff_name_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_staff_name_column_label', $this->custom_fields_option['mvr_dashboard_staff_name_column_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_staff_date_column_label"> <?php esc_html_e( 'Staff Date Column Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_staff_date_column_label" id="mvr_dashboard_staff_date_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_staff_date_column_label', $this->custom_fields_option['mvr_dashboard_staff_date_column_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_staff_actions_column_label"> <?php esc_html_e( 'Staff Actions Column Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_staff_actions_column_label" id="mvr_dashboard_staff_actions_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_staff_actions_column_label', $this->custom_fields_option['mvr_dashboard_staff_actions_column_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_add_staff_btn_label"> <?php esc_html_e( 'Add New Staff Button Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_add_staff_btn_label" id="mvr_dashboard_add_staff_btn_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_add_staff_btn_label', $this->custom_fields_option['mvr_dashboard_add_staff_btn_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_search_staff_btn_label"> <?php esc_html_e( 'Search Staff Button Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_search_staff_btn_label" id="mvr_dashboard_search_staff_btn_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_search_staff_btn_label', $this->custom_fields_option['mvr_dashboard_search_staff_btn_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_edit_staff_btn_label"> <?php esc_html_e( 'Staff Edit Button Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_edit_staff_btn_label" id="mvr_dashboard_edit_staff_btn_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_edit_staff_btn_label', $this->custom_fields_option['mvr_dashboard_edit_staff_btn_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_delete_staff_btn_label"> <?php esc_html_e( 'Staff Delete Button Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_delete_staff_btn_label" id="mvr_dashboard_delete_staff_btn_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_delete_staff_btn_label', $this->custom_fields_option['mvr_dashboard_delete_staff_btn_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_staff_username_field_label"> <?php esc_html_e( 'Username Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_staff_username_field_label" id="mvr_dashboard_staff_username_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_staff_username_field_label', $this->custom_fields_option['mvr_dashboard_staff_username_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_staff_user_email_field_label"> <?php esc_html_e( 'User Email Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_staff_user_email_field_label" id="mvr_dashboard_staff_user_email_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_staff_user_email_field_label', $this->custom_fields_option['mvr_dashboard_staff_user_email_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_staff_password_field_label"> <?php esc_html_e( 'Create Password Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_staff_password_field_label" id="mvr_dashboard_staff_password_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_staff_password_field_label', $this->custom_fields_option['mvr_dashboard_staff_password_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_staff_confirm_password_field_label"> <?php esc_html_e( 'Confirm Password Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_staff_confirm_password_field_label" id="mvr_dashboard_staff_confirm_password_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_staff_confirm_password_field_label', $this->custom_fields_option['mvr_dashboard_staff_confirm_password_field_label'] ) ); ?>"/>
										<br>
									</p>
									<?php
									break;
								case 'review':
									?>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_review_customer_column_label"> <?php esc_html_e( 'Review Customer Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_review_customer_column_label" id="mvr_dashboard_review_customer_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_review_customer_column_label', $this->custom_fields_option['mvr_dashboard_review_customer_column_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_review_rating_column_label"> <?php esc_html_e( 'Rating Column Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_review_rating_column_label" id="mvr_dashboard_review_rating_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_review_rating_column_label', $this->custom_fields_option['mvr_dashboard_review_rating_column_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_review_column_label"> <?php esc_html_e( 'Review Column Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_review_column_label" id="mvr_dashboard_review_column_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_review_column_label', $this->custom_fields_option['mvr_dashboard_review_column_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_review_date_label"> <?php esc_html_e( 'Review Date Column Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_review_date_label" id="mvr_dashboard_review_date_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_review_date_label', $this->custom_fields_option['mvr_dashboard_review_date_label'] ) ); ?>"/>
										<br>
									</p>
									<?php
									break;
								case 'capabilities':
									?>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_product_mng_header_label"> <?php esc_html_e( 'Product Management Header Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_product_mng_header_label" id="mvr_dashboard_product_mng_header_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_product_mng_header_label', $this->custom_fields_option['mvr_dashboard_product_mng_header_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_product_mng_field_label"> <?php esc_html_e( 'Product Management Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_product_mng_field_label" id="mvr_dashboard_product_mng_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_product_mng_field_label', $this->custom_fields_option['mvr_dashboard_product_mng_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_product_creation_field_label"> <?php esc_html_e( 'Product Creation Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_product_creation_field_label" id="mvr_dashboard_product_creation_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_product_creation_field_label', $this->custom_fields_option['mvr_dashboard_product_creation_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_product_modi_field_label"> <?php esc_html_e( 'Product Modification Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_product_modi_field_label" id="mvr_dashboard_product_modi_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_product_modi_field_label', $this->custom_fields_option['mvr_dashboard_product_modi_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_pub_product_modi_field_label"> <?php esc_html_e( 'Published Product Modification Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_pub_product_modi_field_label" id="mvr_dashboard_pub_product_modi_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_pub_product_modi_field_label', $this->custom_fields_option['mvr_dashboard_pub_product_modi_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_manage_inventory_field_label"> <?php esc_html_e( 'Manage Inventory Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_manage_inventory_field_label" id="mvr_dashboard_manage_inventory_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_manage_inventory_field_label', $this->custom_fields_option['mvr_dashboard_manage_inventory_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_product_deletion_field_label"> <?php esc_html_e( 'Product Deletion Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_product_deletion_field_label" id="mvr_dashboard_product_deletion_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_product_deletion_field_label', $this->custom_fields_option['mvr_dashboard_product_deletion_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_coupon_mng_header_label"> <?php esc_html_e( 'Coupon Management Header Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_coupon_mng_header_label" id="mvr_dashboard_coupon_mng_header_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_coupon_mng_header_label', $this->custom_fields_option['mvr_dashboard_coupon_mng_header_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_coupon_mng_field_label"> <?php esc_html_e( 'Coupon Management Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_coupon_mng_field_label" id="mvr_dashboard_coupon_mng_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_coupon_mng_field_label', $this->custom_fields_option['mvr_dashboard_coupon_mng_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_coupon_creation_field_label"> <?php esc_html_e( 'Coupon Creation Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_coupon_creation_field_label" id="mvr_dashboard_coupon_creation_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_coupon_creation_field_label', $this->custom_fields_option['mvr_dashboard_coupon_creation_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_coupon_modi_field_label"> <?php esc_html_e( 'Coupon Modification Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_coupon_modi_field_label" id="mvr_dashboard_coupon_modi_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_coupon_modi_field_label', $this->custom_fields_option['mvr_dashboard_coupon_modi_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_pub_coupon_modi_field_label"> <?php esc_html_e( 'Published Coupon Modification Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_pub_coupon_modi_field_label" id="mvr_dashboard_pub_coupon_modi_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_pub_coupon_modi_field_label', $this->custom_fields_option['mvr_dashboard_pub_coupon_modi_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_coupon_deletion_field_label"> <?php esc_html_e( 'Coupon Deletion Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_coupon_deletion_field_label" id="mvr_dashboard_coupon_deletion_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_coupon_deletion_field_label', $this->custom_fields_option['mvr_dashboard_coupon_deletion_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_order_mng_header_label"> <?php esc_html_e( 'Order Management Header Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_order_mng_header_label" id="mvr_dashboard_order_mng_header_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_order_mng_header_label', $this->custom_fields_option['mvr_dashboard_order_mng_header_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_order_mng_field_label"> <?php esc_html_e( 'Order Management Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_order_mng_field_label" id="mvr_dashboard_order_mng_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_order_mng_field_label', $this->custom_fields_option['mvr_dashboard_order_mng_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_order_status_modi_field_label"> <?php esc_html_e( 'Order Status Modification Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_order_status_modi_field_label" id="mvr_dashboard_order_status_modi_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_order_status_modi_field_label', $this->custom_fields_option['mvr_dashboard_order_status_modi_field_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_commission_info_field_label"> <?php esc_html_e( 'Commission Info Display Field Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_commission_info_field_label" id="mvr_dashboard_commission_info_field_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_commission_info_field_label', $this->custom_fields_option['mvr_dashboard_commission_info_field_label'] ) ); ?>"/>
										<br>
									</p>
									<?php
									break;
								case 'single_store':
									?>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_store_overview_tab_label"> <?php esc_html_e( 'Store Overview Tab Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_store_overview_tab_label" id="mvr_dashboard_store_overview_tab_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_store_overview_tab_label', $this->custom_fields_option['mvr_dashboard_store_overview_tab_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_store_products_tab_label"> <?php esc_html_e( 'Store Products Tab Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_store_products_tab_label" id="mvr_dashboard_store_products_tab_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_store_products_tab_label', $this->custom_fields_option['mvr_dashboard_store_products_tab_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_store_review_tab_label"> <?php esc_html_e( 'Store Review Tab Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_store_review_tab_label" id="mvr_dashboard_store_review_tab_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_store_review_tab_label', $this->custom_fields_option['mvr_dashboard_store_review_tab_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_store_enquiry_tab_label"> <?php esc_html_e( 'Store Enquiry Tab Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_store_enquiry_tab_label" id="mvr_dashboard_store_enquiry_tab_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_store_enquiry_tab_label', $this->custom_fields_option['mvr_dashboard_store_enquiry_tab_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_store_toc_tab_label"> <?php esc_html_e( 'Store Terms & Conditions Tab Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_store_toc_tab_label" id="mvr_dashboard_store_toc_tab_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_store_toc_tab_label', $this->custom_fields_option['mvr_dashboard_store_toc_tab_label'] ) ); ?>"/>
										<br>
									</p>
									<?php
									break;
								case 'spmv':
									?>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_spmv_title_label"> <?php esc_html_e( 'Single Product Multi Vendor Header Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_spmv_title_label" id="mvr_dashboard_spmv_title_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_spmv_title_label', $this->custom_fields_option['mvr_dashboard_spmv_title_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_spmv_product_col_label"> <?php esc_html_e( 'Product Name Column Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_spmv_product_col_label" id="mvr_dashboard_spmv_product_col_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_spmv_product_col_label', $this->custom_fields_option['mvr_dashboard_spmv_product_col_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_spmv_price_col_label"> <?php esc_html_e( 'Price Column Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_spmv_price_col_label" id="mvr_dashboard_spmv_price_col_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_spmv_price_col_label', $this->custom_fields_option['mvr_dashboard_spmv_price_col_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_spmv_rating_col_label"> <?php esc_html_e( 'Rating Column Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_spmv_rating_col_label" id="mvr_dashboard_spmv_rating_col_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_spmv_rating_col_label', $this->custom_fields_option['mvr_dashboard_spmv_rating_col_label'] ) ); ?>"/>
										<br>
									</p>
									<p class="mvr-form-field">
										<label for="mvr_dashboard_spmv_action_col_label"> <?php esc_html_e( 'Action Column Label', 'multi-vendor-marketplace' ); ?> </label>
										<input type="text" name="mvr_dashboard_spmv_action_col_label" id="mvr_dashboard_spmv_action_col_label" value="<?php echo esc_attr( get_option( 'mvr_dashboard_spmv_action_col_label', $this->custom_fields_option['mvr_dashboard_spmv_action_col_label'] ) ); ?>"/>
										<br>
									</p>
									<?php
									break;
								default:
									/**
									 * Get the admin settings HTML.
									 *
									 * @since 1.0.0
									 */
									do_action( 'mvr_admin_settings_localization_' . $section_key . '_section_html' );
									break;
							}
							?>
							</div>
						<?php } ?>
					</div>
				</div>
				<div class="clear"></div>
			</div>
			<?php
		}

		/**
		 * Delete the custom options.
		 *
		 * @since 1.0.0
		 * @param Array $posted Posted Data.
		 */
		public function custom_types_delete_options( $posted = null ) {
			foreach ( $this->custom_fields_option as $key => $default_value ) {
				delete_option( $key );
			}
		}

		/**
		 * Save custom settings.
		 *
		 * @since 1.0.0
		 * @param Array $posted Posted Data.
		 */
		public function custom_types_save( $posted ) {
			foreach ( $this->custom_fields_option as $key => $default_value ) {
				if ( ! isset( $posted[ "{$key}" ] ) ) {
					continue;
				}

				update_option( "{$key}", $posted[ "{$key}" ] );
			}
		}

		/**
		 * Save the custom options once.
		 *
		 * @since 1.0.0
		 * @param Array $posted Posted Data.
		 */
		public function custom_types_add_options( $posted = null ) {
			foreach ( $this->custom_fields_option as $key => $default_value ) {
				add_option( $key, $default_value );
			}
		}
	}

	return new MVR_Admin_Settings_Localization();
}
