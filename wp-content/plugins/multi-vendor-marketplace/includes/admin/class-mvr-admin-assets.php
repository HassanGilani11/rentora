<?php
/**
 * Admin Assets.
 *
 * @package Multi-Vendor for WooCommerce/Admin Assets
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Admin_Assets' ) ) {

	/**
	 * Main Class.
	 * */
	class MVR_Admin_Assets {

		/**
		 * Suffix.
		 *
		 * @var string.
		 * */
		private static $suffix;

		/**
		 * Class Initialization.
		 *
		 * @since 1.0.0
		 * */
		public static function init() {
			self::$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'external_js_css_files' ) );
		}

		/**
		 * Enqueue external JS CSS files.
		 *
		 * @since 1.0.0
		 * */
		public static function external_js_css_files() {
			self::external_css_files();
			self::external_js_files();
			self::add_inline_style();
		}

		/**
		 * Enqueue external CSS files.
		 *
		 * @since 1.0.0
		 * */
		public static function external_css_files() {
			$screen_ids         = mvr_page_screen_ids();
			$current_screen_obj = get_current_screen();

			if ( ! is_object( $current_screen_obj ) ) {
				return;
			}

			$screen_id = str_replace( 'edit-', '', $current_screen_obj->id );

			if ( ! in_array( $screen_id, $screen_ids, true ) ) {
				return;
			}

			wp_enqueue_style( 'mvr-admin', MVR_PLUGIN_URL . '/assets/css/admin/admin.css', array( 'woocommerce_admin_styles', 'jquery-ui-style' ), MVR_VERSION );
		}

		/**
		 * Add Inline style
		 *
		 * @since 1.0.0
		 */
		public static function add_inline_style() {
			$contents = '';

			if ( mvr_check_user_as_vendor_or_staff() ) {
				$contents .= 'body #wpwrap #adminmenumain #adminmenuwrap,
				body #wpwrap #adminmenumain #adminmenuback,
				body #wpwrap #wpcontent #wpadminbar
				body #wpwrap #wpcontent #woocommerce-embedded-root
				body #wpwrap #wpcontent #woocommerce-embedded-root .woocommerce-layout
				{
                    visibility:hidden;
					pointer-events:none;
                }
				#adminmenuwrap{
					display: none;
				}
				#wpadminbar {
					display: none;
				}
				#adminmenuback {
					display: none;
				}
				
				#adminmenuwrap {
					display: none;
				}
				
				#wpadminbar {
					display: none;
				}
				
				#wpcontent {
					margin-left: 10px !important;
				}
				
				.woocommerce-layout__header {
					display: none;
				}
				
				#wpbody-content #screen-meta-links{
					display: none;
				}';
			}

			if ( ! $contents ) {
				return;
			}

			// Add custom css as inline style.
			wp_add_inline_style( 'mvr-admin', $contents );
		}

		/**
		 * Enqueue external JS files.
		 *
		 * @since 1.0.0
		 * */
		public static function external_js_files() {
			$screen_ids         = mvr_page_screen_ids();
			$current_screen_obj = get_current_screen();

			if ( ! is_object( $current_screen_obj ) ) {
				return;
			}

			$screen_id = str_replace( 'edit-', '', $current_screen_obj->id );

			$enqueue_array = array(
				'mvr-admin'                => array(
					'callable' => array( __CLASS__, 'admin' ),
					'restrict' => in_array( $screen_id, $screen_ids, true ),
				),
				'mvr-enhanced-assests'     => array(
					'callable' => array( __CLASS__, 'enhanced_assests' ),
					'restrict' => in_array( $screen_id, $screen_ids, true ),
				),
				'mvr-admin-metabox-vendor' => array(
					'callable' => array( __CLASS__, 'vendor_metabox' ),
					'restrict' => in_array( $screen_id, array( 'mvr_vendor' ), true ),
				),
				'mvr-commission'           => array(
					'callable' => array( __CLASS__, 'commission' ),
					'restrict' => in_array( $screen_id, array( 'woocommerce_page_mvr_commission' ), true ),
				),
			);

			/**
			 * Admin_assets.
			 *
			 * @since 1.0.0
			 */
			$enqueue_array = apply_filters( 'mvr_admin_assets', $enqueue_array );

			if ( ! mvr_check_is_array( $enqueue_array ) ) {
				return;
			}

			foreach ( $enqueue_array as $key => $enqueue ) {
				if ( ! mvr_check_is_array( $enqueue ) ) {
					continue;
				}

				if ( $enqueue['restrict'] ) {
					call_user_func_array( $enqueue['callable'], array() );
				}
			}
		}

		/**
		 * Enqueue Admin end requires JS files.
		 *
		 * @since 1.0.0
		 * */
		public static function admin() {
			global $post;

			$current_screen_obj = get_current_screen();

			if ( ! is_object( $current_screen_obj ) ) {
				return;
			}

			$screen_id = str_replace( 'edit-', '', $current_screen_obj->id );

			if ( ( mvr_check_user_as_vendor_or_staff() && in_array( $screen_id, array( 'product', 'shop_coupon' ), true ) ) || in_array( $screen_id, array( 'mvr_staff', 'mvr_vendor' ), true ) ) {
				wp_dequeue_script( 'autosave' ); // Disable WP Auto Save on Edit Page.
			}

			$wc_screen_id = sanitize_title( __( 'WooCommerce', 'woocommerce' ) );

			// WordPress Media.
			wp_enqueue_script( 'selectWoo' );
			wp_enqueue_script( 'wc-country-select' );
			wp_enqueue_script( 'wc-address-i18n' );

			// Admin.
			wp_enqueue_script( 'mvr-admin', MVR_PLUGIN_URL . '/assets/js/admin/admin.js', array( 'jquery', 'jquery-blockui', 'wc-admin-meta-boxes', 'wc-backbone-modal' ), MVR_VERSION, false );
			wp_localize_script(
				'mvr-admin',
				'mvr_admin_params',
				array(
					'ajaxurl'                      => MVR_ADMIN_AJAX_URL,
					'invalid_email_msg'            => esc_html__( 'Please enter valid email address', 'multivendor-for-woocommerce' ),
					'user_email_nonce'             => wp_create_nonce( 'mvr-user-email-nonce' ),
					'post_id'                      => isset( $post->ID ) ? $post->ID : '',
					'delete_note_msg'              => esc_html__( 'Are you sure you want to delete this note?', 'multi-vendor-marketplace' ),
					'vendor_delete_msg'            => esc_html__( 'Are you sure you want to delete this Vendor?', 'multi-vendor-marketplace' ),
					'commission_delete_msg'        => esc_html__( 'Are you sure you want to delete this Commission?', 'multi-vendor-marketplace' ),
					'withdraw_delete_msg'          => esc_html__( 'Are you sure you want to delete this Withdrawal Request?', 'multi-vendor-marketplace' ),
					'transaction_delete_msg'       => esc_html__( 'Are you sure you want to delete this Transaction?', 'multi-vendor-marketplace' ),
					'payout_delete_msg'            => esc_html__( 'Are you sure you want to delete this Payout?', 'multi-vendor-marketplace' ),
					'enquiry_delete_msg'           => esc_html__( 'Are you sure you want to delete this Enquiry?', 'multi-vendor-marketplace' ),
					'staff_delete_msg'             => esc_html__( 'Are you sure you want to delete this Staff?', 'multi-vendor-marketplace' ),
					'withdraw_make_payment_msg'    => esc_html__( 'Are you sure you want to continue to proceed payment?', 'multi-vendor-marketplace' ),
					'withdraw_reject_payment_msg'  => esc_html__( 'Are you sure you want to continue to reject the payment?', 'multi-vendor-marketplace' ),
					'add_vendor_note_nonce'        => wp_create_nonce( 'mvr-add-vendor-note' ),
					'delete_vendor_note_nonce'     => wp_create_nonce( 'mvr-delete-vendor-note' ),
					'add_spmv_nonce'               => wp_create_nonce( 'mvr-duplicate-product' ),
					'remove_spmv_nonce'            => wp_create_nonce( 'mvr-remove-spmv-product' ),
					'add_vendor_nonce'             => wp_create_nonce( 'mvr-add-vendor' ),
					'add_staff_nonce'              => wp_create_nonce( 'mvr-add-staff' ),
					'add_commission_nonce'         => wp_create_nonce( 'mvr-add-commission' ),
					'add_withdraw_nonce'           => wp_create_nonce( 'mvr-add-withdraw' ),
					'pay_vendor_nonce'             => wp_create_nonce( 'mvr-pay-vendor' ),
					'pay_vendor_msg'               => esc_html__( 'Vendor Payment Paid Successfully', 'multi-vendor-marketplace' ),
					'minimum_withdraw'             => (float) get_option( 'mvr_settings_min_withdraw_threshold', 0 ),
					'min_withdraw_msg'             => esc_html__( 'Withdraw amount should not be less than the minimum amount', 'multi-vendor-marketplace' ),
					'max_withdraw_msg'             => esc_html__( 'Withdraw amount should not more than the available amount', 'multi-vendor-marketplace' ),
					'remove_spmv_msg'              => esc_html__( 'Are you sure you want to delete this product?', 'multi-vendor-marketplace' ),
					'enable_withdraw_charge'       => get_option( 'mvr_settings_enable_withdraw_charge_req', 'no' ),
					'withdraw_charge_type'         => get_option( 'mvr_settings_withdraw_charge_type', '1' ),
					'withdraw_charge'              => get_option( 'mvr_settings_withdraw_charge_val', '0' ),
					'withdraw_amount_label'        => esc_html__( 'Withdrawal Amount :', 'multi-vendor-marketplace' ),
					'withdraw_charge_label'        => esc_html__( 'Withdrawal Charge Amount :', 'multi-vendor-marketplace' ),
					'wc_screen_id'                 => $wc_screen_id,
					'commission_already_has_msg'   => esc_html__( 'This Order already has a commission entry. Are you sure you want to recalculate this order?', 'multi-vendor-marketplace' ),
					'no_vendor_order_msg'          => esc_html__( 'No Vendor(s) were associated with the Order', 'multi-vendor-marketplace' ),
					'decimal_point'                => wc_get_price_decimal_separator(),
					'currency_format_num_decimals' => wc_get_price_decimals(),
					'wc_currency_symbol'           => get_woocommerce_currency_symbol(),
					'currency_format_thousand_sep' => esc_attr( wc_get_price_thousand_separator() ),
					'currency_position'            => esc_attr( stripslashes( get_option( 'woocommerce_currency_pos' ) ) ),
					/**
					 * Price Trim Zero
					 *
					 * @since 1.0.0
					 * @return Boolean
					 * */
					'currency_format_trim_zeros'   => ( false === apply_filters( 'woocommerce_price_trim_zeros', false ) ) ? 'no' : 'yes',
					/* translators: %s: price decimal separator */
					'decimal_error'                => sprintf( __( 'Please enter a value with one monetary decimal point (%s) without thousand separators and currency symbols.', 'multi-vendor-marketplace' ), wc_get_price_decimal_separator() ),
					'commission_created_msg'       => esc_html__( 'Commission created successfully', 'multi-vendor-marketplace' ),
					'available_amount_msg'         => esc_html__( 'Please enter the amount not more than the available amount', 'multi-vendor-marketplace' ),
					'valid_amount_msg'             => esc_html__( 'Please enter the amount more than 0', 'multi-vendor-marketplace' ),
					'urls'                         => array(
						'export_withdraws'         => current_user_can( 'export' ) ? esc_url_raw( admin_url( 'edit.php?post_type=mvr_vendor&page=mvr_withdraw_exporter' ) ) : null,
						'generate_payout'          => esc_url_raw( admin_url( 'edit.php?post_type=mvr_vendor&page=mvr_generate_payout' ) ),
						'generate_withdraw_payout' => esc_url_raw( admin_url( 'edit.php?post_type=mvr_vendor&page=mvr_generate_withdraw_payout' ) ),
					),
					'strings'                      => array(
						'export_withdraws'         => __( 'Export', 'multi-vendor-marketplace' ),
						'generate_payout'          => __( 'Generate Payout', 'multi-vendor-marketplace' ),
						'generate_withdraw_payout' => __( 'Generate Payout', 'multi-vendor-marketplace' ),
					),
				)
			);
		}

		/**
		 * Commission Post.
		 *
		 * @since 1.0.0
		 * */
		public static function commission() {
			wp_enqueue_script( 'mvr-commission', MVR_PLUGIN_URL . '/assets/js/admin/commission.js', array( 'jquery', 'jquery-blockui', 'wc-admin-meta-boxes', 'wc-backbone-modal' ), MVR_VERSION, false );
			wp_localize_script(
				'mvr-commission',
				'mvr_commission_params',
				array(
					'commission_preview_nonce' => wp_create_nonce( 'mvr-preview-commission' ),
				)
			);
		}

		/**
		 * Enqueue scripts and CSS.
		 *
		 * @since 1.0.0
		 * */
		public static function enhanced_assests() {
			wp_enqueue_style( 'select2' );
			wp_enqueue_script( 'mvr-enhanced', MVR_PLUGIN_URL . '/assets/js/mvr-enhanced.js', array( 'jquery', 'select2', 'jquery-ui-datepicker' ), MVR_VERSION, false );
			wp_localize_script(
				'mvr-enhanced',
				'mvr_enhanced_params',
				array(
					'i18n_no_matches'           => esc_html_x( 'No matches found', 'enhanced select', 'multi-vendor-marketplace' ),
					'i18n_input_too_short_1'    => esc_html_x( 'Please enter 1 or more characters', 'enhanced select', 'multi-vendor-marketplace' ),
					'i18n_input_too_short_n'    => esc_html_x( 'Please enter %qty% or more characters', 'enhanced select', 'multi-vendor-marketplace' ),
					'i18n_input_too_long_1'     => esc_html_x( 'Please delete 1 character', 'enhanced select', 'multi-vendor-marketplace' ),
					'i18n_input_too_long_n'     => esc_html_x( 'Please delete %qty% characters', 'enhanced select', 'multi-vendor-marketplace' ),
					'i18n_selection_too_long_1' => esc_html_x( 'You can only select 1 item', 'enhanced select', 'multi-vendor-marketplace' ),
					'i18n_selection_too_long_n' => esc_html_x( 'You can only select %qty% items', 'enhanced select', 'multi-vendor-marketplace' ),
					'i18n_load_more'            => esc_html_x( 'Loading more results&hellip;', 'enhanced select', 'multi-vendor-marketplace' ),
					'i18n_searching'            => esc_html_x( 'Searching&hellip;', 'enhanced select', 'multi-vendor-marketplace' ),
					'search_nonce'              => wp_create_nonce( 'mvr-search-nonce' ),
					'calendar_image'            => WC()->plugin_url() . '/assets/images/calendar.png',
					'ajaxurl'                   => MVR_ADMIN_AJAX_URL,
					'wc_version'                => WC()->version,
				)
			);
		}

		/**
		 * Vendor Meta box.
		 *
		 * @since 1.0.0
		 */
		public static function vendor_metabox() {
			$default_location = wc_get_customer_default_location();

			wp_enqueue_script( 'mvr-admin-metabox-vendor', MVR_PLUGIN_URL . '/assets/js/admin/meta-boxes-vendor.js', array( 'jquery', 'select2', 'jquery-ui-datepicker' ), MVR_VERSION, false );
			wp_localize_script(
				'mvr-admin-metabox-vendor',
				'mvr_admin_meta_boxes_vendor',
				array(
					'countries'                 => wp_json_encode( array_merge( WC()->countries->get_allowed_country_states(), WC()->countries->get_shipping_country_states() ) ),
					'i18n_select_state_text'    => esc_attr__( 'Select an option&hellip;', 'woocommerce' ),
					'default_country'           => isset( $default_location['country'] ) ? $default_location['country'] : '',
					'default_state'             => isset( $default_location['state'] ) ? $default_location['state'] : '',
					'choose_image_title'        => esc_html__( 'Choose an Image', 'multi-vendor-marketplace' ),
					'add_image_title'           => esc_html__( 'Add Image', 'multi-vendor-marketplace' ),
					'vendor_description_title'  => esc_html__( 'Vendor Description', 'multi-vendor-marketplace' ),
					'add_vendor_staff_nonce'    => wp_create_nonce( 'mvr-add-vendor-staff' ),
					'remove_vendor_staff_nonce' => wp_create_nonce( 'mvr-remove-vendor-staff' ),
					'vendor_shop_nonce'         => wp_create_nonce( 'mvr-vendor-shop' ),
					'vendor_slug_nonce'         => wp_create_nonce( 'mvr-vendor-slug' ),
					'shop_available_txt'        => esc_html__( 'Shop Name available', 'multi-vendor-marketplace' ),
					'shop_unavailable_txt'      => esc_html__( 'Shop Name already exist', 'multi-vendor-marketplace' ),
					'available_txt'             => esc_html__( 'Available', 'multi-vendor-marketplace' ),
					'unavailable_txt'           => esc_html__( 'Unavailable', 'multi-vendor-marketplace' ),
					'min_char_shop_txt'         => esc_html__( 'Please enter the Shop Name in 3 or more characters', 'multi-vendor-marketplace' ),
					'min_char_slug_txt'         => esc_html__( 'Please enter 3 or more characters for the shop slug', 'multi-vendor-marketplace' ),
					'default_store_url'         => mvr_get_store_url( '{slug}' ),
				)
			);
		}
	}

	MVR_Admin_Assets::init();
}
