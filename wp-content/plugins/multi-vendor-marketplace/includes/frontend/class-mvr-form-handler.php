<?php
/**
 * Handle frontend forms.
 *
 * @package Multi-Vendor for WooCommerce\Classes\
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if directly accessed.
}

if ( ! class_exists( 'MVR_Form_Handler' ) ) {
	/**
	 * MVR_Form_Handler class.
	 */
	class MVR_Form_Handler {

		/**
		 * Hook in methods.
		 *
		 * @since 1.0.0
		 */
		public static function init() {
			add_action( 'wp_loaded', array( __CLASS__, 'registration_process' ) );
			add_action( 'wp_loaded', array( __CLASS__, 'become_as_vendor' ) );
			add_action( 'wp_loaded', array( __CLASS__, 'create_withdraw_request' ) );
			add_action( 'wp_loaded', array( __CLASS__, 'create_staff' ) );
			add_action( 'wp_loaded', array( __CLASS__, 'delete_product' ) );
			add_action( 'wp_loaded', array( __CLASS__, 'delete_coupon' ) );
			add_action( 'wp_loaded', array( __CLASS__, 'delete_staff' ) );
			add_action( 'wp_loaded', array( __CLASS__, 'enquiry_process' ) );
			add_action( 'wp_loaded', array( __CLASS__, 'review_process' ) );

			add_action( 'template_redirect', array( __CLASS__, 'vendor_template_redirect' ) );
			add_action( 'template_redirect', array( __CLASS__, 'save_profile_details' ) );
			add_action( 'template_redirect', array( __CLASS__, 'save_payment_details' ) );
			add_action( 'template_redirect', array( __CLASS__, 'save_payout_details' ) );
			add_action( 'template_redirect', array( __CLASS__, 'save_vendor_address' ) );
			add_action( 'template_redirect', array( __CLASS__, 'save_social_links' ) );
			add_action( 'template_redirect', array( __CLASS__, 'update_order_status' ) );
		}

		/**
		 * Become As vendor.
		 *
		 * @since 1.0.0
		 * @throws Exception Invalid Arguments.
		 */
		public static function become_as_vendor() {
			$nonce_value = isset( $_POST['_mvr_nonce'] ) ? sanitize_key( wp_unslash( $_POST['_mvr_nonce'] ) ) : '';

			if ( ! wp_verify_nonce( $nonce_value, 'mvr_become_vendor' ) ) {
				return;
			}

			if ( empty( $_POST['action'] ) || 'mvr_become_vendor' !== $_POST['action'] ) {
				return;
			}

			try {
				if ( ! isset( $_POST['_terms_and_conditions'] ) ) {
					throw new Exception( esc_html__( 'Please accept the Terms and Conditions', 'multi-vendor-marketplace' ) );
				}

				if ( ! isset( $_POST['_privacy_policy'] ) ) {
					throw new Exception( esc_html__( 'Please accept the Privacy Policy', 'multi-vendor-marketplace' ) );
				}

				$name = isset( $_POST['_name'] ) ? sanitize_text_field( wp_unslash( $_POST['_name'] ) ) : '';

				if ( empty( $name ) ) {
					throw new Exception( esc_html__( 'Please enter a valid Vendor Name', 'multi-vendor-marketplace' ) );
				}

				$shop_name = isset( $_POST['_shop_name'] ) ? sanitize_text_field( wp_unslash( $_POST['_shop_name'] ) ) : '';

				if ( empty( $shop_name ) || strlen( $shop_name ) < 3 ) {
					throw new Exception( esc_html__( 'Please Enter the Shop name 3 or more characters', 'multi-vendor-marketplace' ) );
				}

				if ( mvr_check_shop_name_exists( $shop_name ) ) {
					throw new Exception( esc_html__( 'Shop Name already exists. Please try another', 'multi-vendor-marketplace' ) );
				}

				$slug = isset( $_POST['_slug'] ) ? sanitize_title( wp_unslash( $_POST['_slug'] ) ) : '';

				if ( empty( $slug ) || strlen( $slug ) < 3 ) {
					throw new Exception( esc_html__( 'Please enter 3 or more characters for the shop slug', 'multi-vendor-marketplace' ) );
				}

				if ( mvr_check_shop_slug_exists( $slug ) ) {
					throw new Exception( esc_html__( 'Shop slug already exists. Please try another', 'multi-vendor-marketplace' ) );
				}

				$user_id  = get_current_user_id();
				$user_obj = get_userdata( $user_id );

				if ( empty( $user_id ) || ! is_a( $user_obj, 'WP_User' ) ) {
					throw new Exception( esc_html__( 'Invalid User', 'multi-vendor-marketplace' ) );
				}

				$user_obj->remove_role( 'customer' );
				$user_obj->set_role( 'mvr-vendor' );

				$vendor_obj = new MVR_Vendor();
				$vendor_obj->set_props(
					array(
						'user_id'                        => $user_id,
						'first_name'                     => $user_obj->first_name,
						'last_name'                      => $user_obj->last_name,
						'email'                          => $user_obj->user_email,
						'name'                           => $name,
						'shop_name'                      => $shop_name,
						'slug'                           => $slug,
						'enable_product_management'      => get_option( 'mvr_settings_enable_product_management', 'no' ),
						'product_creation'               => get_option( 'mvr_settings_enable_product_creation', 'no' ),
						'product_modification'           => get_option( 'mvr_settings_enable_product_modification', 'no' ),
						'published_product_modification' => get_option( 'mvr_settings_enable_published_product_modification', 'no' ),
						'manage_inventory'               => get_option( 'mvr_settings_enable_manage_inventory', 'no' ),
						'product_deletion'               => get_option( 'mvr_settings_enable_product_deletion', 'no' ),
						'enable_order_management'        => get_option( 'mvr_settings_enable_order_management', 'no' ),
						'order_status_modification'      => get_option( 'mvr_settings_enable_order_status_management', 'no' ),
						'commission_info_display'        => get_option( 'mvr_settings_commission_info_management', 'no' ),
						'enable_coupon_management'       => get_option( 'mvr_settings_enable_coupon_management', 'no' ),
						'coupon_creation'                => get_option( 'mvr_settings_enable_coupon_creation_management', 'no' ),
						'published_coupon_modification'  => get_option( 'mvr_settings_enable_published_coupon_modification', 'no' ),
						'coupon_modification'            => get_option( 'mvr_settings_enable_coupon_modification_management', 'no' ),
						'coupon_deletion'                => get_option( 'mvr_settings_enable_coupon_deletion_management', 'no' ),
					)
				);
				$vendor_obj->save();

				/**
				 * After Become a Vendor Register
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_after_become_vendor', $vendor_obj );

				wc_add_notice( esc_html__( 'Vendor Application Submitted.', 'multi-vendor-marketplace' ), 'success' );

				// redirect to account page.
				wp_safe_redirect( wc_get_account_endpoint_url( 'dashboard' ) );
				exit;
			} catch ( Exception $e ) {
				wc_add_notice( $e->getMessage(), 'error' );
				// redirect to account page.
				wp_safe_redirect( wc_get_account_endpoint_url( 'dashboard' ) );
				exit;
			}
		}

		/**
		 * Vendor Registration.
		 *
		 * @since 1.0.0
		 * @throws Exception Invalid Arguments.
		 */
		public static function registration_process() {
			$nonce_value = isset( $_POST['_mvr_nonce'] ) ? sanitize_key( wp_unslash( $_POST['_mvr_nonce'] ) ) : '';

			if ( wp_verify_nonce( $nonce_value, 'mvr_vendor_register' ) ) {
				if ( empty( $_POST['action'] ) || 'mvr_vendor_register' !== $_POST['action'] ) {
					return;
				}

				try {
					if ( ! isset( $_POST['_terms_and_conditions'] ) ) {
						throw new Exception( esc_html__( 'Please accept the Terms and Conditions', 'multi-vendor-marketplace' ) );
					}

					if ( ! isset( $_POST['_privacy_policy'] ) ) {
						throw new Exception( esc_html__( 'Please accept the Privacy Policy', 'multi-vendor-marketplace' ) );
					}

					$name = isset( $_POST['_name'] ) ? sanitize_text_field( wp_unslash( $_POST['_name'] ) ) : '';

					if ( empty( $name ) ) {
						throw new Exception( esc_html__( 'Please enter a valid Vendor Name', 'multi-vendor-marketplace' ) );
					}

					$shop_name = isset( $_POST['_shop_name'] ) ? sanitize_text_field( wp_unslash( $_POST['_shop_name'] ) ) : '';

					if ( empty( $shop_name ) || strlen( $shop_name ) < 3 ) {
						throw new Exception( esc_html__( 'Please enter  a valid Store Name', 'multi-vendor-marketplace' ) );
					}

					if ( mvr_check_shop_name_exists( $shop_name ) ) {
						throw new Exception( esc_html__( 'Shop Name already exists. Please try another', 'multi-vendor-marketplace' ) );
					}

					$slug = isset( $_POST['_slug'] ) ? sanitize_title( wp_unslash( $_POST['_slug'] ) ) : '';

					if ( empty( $slug ) || strlen( $slug ) < 3 ) {
						throw new Exception( esc_html__( 'Please enter 3 or more characters for the shop slug', 'multi-vendor-marketplace' ) );
					}

					if ( mvr_check_shop_slug_exists( $slug ) ) {
						throw new Exception( esc_html__( 'Shop slug already exists. Please try another', 'multi-vendor-marketplace' ) );
					}

					$user_id = isset( $_POST['_user_id'] ) ? absint( wp_unslash( $_POST['_user_id'] ) ) : '';

					if ( empty( $user_id ) ) {
						$email = isset( $_POST['_email'] ) ? sanitize_email( wp_unslash( $_POST['_email'] ) ) : '';

						if ( email_exists( $email ) ) {
							/* translators: %s Vendor Login Page URL */
							throw new Exception( sprintf( __( 'An account is already registered with your email address. <a href="%s" class="showlogin">Please log in.</a>', 'multi-vendor-marketplace' ), mvr_get_page_permalink( 'vendor_login' ) ) );
						}

						$password = isset( $_POST['_password'] ) ? sanitize_text_field( wp_unslash( $_POST['_password'] ) ) : '';

						if ( empty( $password ) ) {
							throw new Exception( esc_html__( 'Password Required', 'multi-vendor-marketplace' ) );
						}

						$confirm_password = isset( $_POST['_confirm_password'] ) ? sanitize_text_field( wp_unslash( $_POST['_confirm_password'] ) ) : '';

						if ( $confirm_password !== $password ) {
							throw new Exception( esc_html__( 'Password Not Matching', 'multi-vendor-marketplace' ) );
						}

						$username = wc_create_new_customer_username( $email );
						$user_id  = wc_create_new_customer( sanitize_email( $email ), $username, $password, array( 'is_mvr_vendor' => true ) );

						if ( is_wp_error( $user_id ) ) {
							throw new Exception( $user_id->get_error_message() );
						}

						wc_set_customer_auth_cookie( $user_id );
					}

					$user_obj = get_user_by( 'ID', $user_id );

					if ( ! $user_obj ) {
						throw new Exception( __( 'Invalid user.', 'multi-vendor-marketplace' ) );
					}

					$user_obj->remove_role( 'customer' );
					$user_obj->set_role( 'mvr-vendor' );

					$vendor_obj = new MVR_Vendor();
					$vendor_obj->set_props(
						array(
							'user_id'                   => $user_id,
							'name'                      => $name,
							'shop_name'                 => $shop_name,
							'slug'                      => $slug,
							'email'                     => $user_obj->user_email,
							'enable_product_management' => get_option( 'mvr_settings_enable_product_management', 'no' ),
							'product_creation'          => get_option( 'mvr_settings_enable_product_creation', 'no' ),
							'product_modification'      => get_option( 'mvr_settings_enable_product_modification', 'no' ),
							'published_product_modification' => get_option( 'mvr_settings_enable_published_product_modification', 'no' ),
							'manage_inventory'          => get_option( 'mvr_settings_enable_manage_inventory', 'no' ),
							'product_deletion'          => get_option( 'mvr_settings_enable_product_deletion', 'no' ),
							'enable_order_management'   => get_option( 'mvr_settings_enable_order_management', 'no' ),
							'order_status_modification' => get_option( 'mvr_settings_enable_order_status_management', 'no' ),
							'commission_info_display'   => get_option( 'mvr_settings_commission_info_management', 'no' ),
							'enable_coupon_management'  => get_option( 'mvr_settings_enable_coupon_management', 'no' ),
							'coupon_creation'           => get_option( 'mvr_settings_enable_coupon_creation_management', 'no' ),
							'published_coupon_modification' => get_option( 'mvr_settings_enable_published_coupon_modification', 'no' ),
							'coupon_modification'       => get_option( 'mvr_settings_enable_coupon_modification_management', 'no' ),
							'coupon_deletion'           => get_option( 'mvr_settings_enable_coupon_deletion_management', 'no' ),
						)
					);
					$vendor_obj->save();

					/**
					 * After Vendor Register
					 *
					 * @since 1.0.0
					 */
					do_action( 'mvr_after_register_vendor', $vendor_obj );

					wc_add_notice( esc_html__( 'Vendor Application Submitted.', 'multi-vendor-marketplace' ), 'success' );
				} catch ( Exception $e ) {
					wc_add_notice( $e->getMessage(), 'error' );
				}
			}
		}

		/**
		 * Delete Product.
		 *
		 * @since 1.0.0
		 * @throws Exception Invalid Arguments.
		 */
		public static function delete_product() {
			$nonce_value = isset( $_REQUEST['_mvr_nonce'] ) ? sanitize_key( wp_unslash( $_REQUEST['_mvr_nonce'] ) ) : '';

			if ( wp_verify_nonce( $nonce_value, 'mvr-delete-product-nonce' ) ) {
				if ( empty( $_REQUEST['action'] ) || 'mvr_delete_product' !== $_REQUEST['action'] ) {
					return;
				}

				try {
					if ( ! isset( $_REQUEST['product_id'] ) ) {
						return;
					}

					$product_id = absint( wp_unslash( $_REQUEST['product_id'] ) );

					if ( empty( $product_id ) ) {
						throw new Exception( __( 'Invalid Product', 'multi-vendor-marketplace' ) );
					}

					$product_obj = wc_get_product( $product_id );

					if ( ! is_a( $product_obj, 'WC_Product' ) ) {
						$product_title = 'Product';
						wp_delete_post( $product_id, true );
					} else {
						$product_title = $product_obj->get_title();
						$product_obj->delete( true );
					}

					/* translators: %s:Product title */
					wc_add_notice( sprintf( esc_html__( '%s has been deleted', 'multi-vendor-marketplace' ), esc_html( $product_title ) ), 'success' );
					wp_safe_redirect( mvr_get_dashboard_endpoint_url( 'mvr-products' ) );
					exit;
				} catch ( Exception $e ) {
					wc_add_notice( $e->getMessage(), 'error' );
				}
			}
		}

		/**
		 * Delete Coupon.
		 *
		 * @since 1.0.0
		 * @throws Exception Invalid Arguments.
		 */
		public static function delete_coupon() {
			$nonce_value = isset( $_REQUEST['_mvr_nonce'] ) ? sanitize_key( wp_unslash( $_REQUEST['_mvr_nonce'] ) ) : '';

			if ( wp_verify_nonce( $nonce_value, 'mvr-delete-coupon-nonce' ) ) {
				if ( empty( $_REQUEST['action'] ) || 'mvr_delete_coupon' !== $_REQUEST['action'] ) {
					return;
				}

				try {
					if ( ! isset( $_REQUEST['coupon_id'] ) ) {
						return;
					}

					$coupon_id = absint( wp_unslash( $_REQUEST['coupon_id'] ) );

					if ( empty( $coupon_id ) ) {
						throw new Exception( __( 'Invalid Coupon', 'multi-vendor-marketplace' ) );
					}

					$coupon_obj = new WC_Coupon( $coupon_id );

					if ( ! is_a( $coupon_obj, 'WC_Coupon' ) ) {
						$coupon_title = 'Coupon';
						wp_delete_post( $coupon_id, true );
					} else {
						$coupon_title = $coupon_obj->get_title();
						$coupon_obj->delete( true );
					}

					/* translators: %s:Product title */
					wc_add_notice( sprintf( esc_html__( '%s has been deleted', 'multi-vendor-marketplace' ), esc_html( $coupon_title ) ), 'success' );
					wp_safe_redirect( mvr_get_dashboard_endpoint_url( 'mvr-coupons' ) );
					exit;
				} catch ( Exception $e ) {
					wc_add_notice( $e->getMessage(), 'error' );
				}
			}
		}

		/**
		 * Delete Staff.
		 *
		 * @since 1.0.0
		 * @throws Exception Invalid Arguments.
		 */
		public static function delete_staff() {
			$nonce_value = isset( $_REQUEST['_mvr_nonce'] ) ? sanitize_key( wp_unslash( $_REQUEST['_mvr_nonce'] ) ) : '';

			if ( wp_verify_nonce( $nonce_value, 'mvr-dashboard-staff-nonce' ) ) {
				if ( empty( $_REQUEST['action'] ) || 'mvr_delete_staff' !== $_REQUEST['action'] ) {
					return;
				}

				try {
					if ( ! isset( $_REQUEST['staff_id'] ) ) {
						return;
					}

					$staff_id = absint( wp_unslash( $_REQUEST['staff_id'] ) );

					if ( empty( $staff_id ) ) {
						throw new Exception( __( 'Invalid Staff ID', 'multi-vendor-marketplace' ) );
					}

					$staff_obj = mvr_get_staff( $staff_id );

					if ( ! mvr_is_staff( $staff_obj ) ) {
						$staff_title = 'Staff';
						wp_delete_post( $staff_id, true );
					} else {
						$staff_title = $staff_obj->get_title();
						$staff_obj->delete( true );
					}

					/* translators: %s:Product title */
					wc_add_notice( sprintf( esc_html__( '%s has been deleted', 'multi-vendor-marketplace' ), esc_html( $staff_title ) ), 'success' );
					wp_safe_redirect( mvr_get_dashboard_endpoint_url( 'mvr-staff' ) );
					exit;
				} catch ( Exception $e ) {
					wc_add_notice( $e->getMessage(), 'error' );
				}
			}
		}

		/**
		 * Enquiry Process
		 *
		 * @since 1.0.0
		 * @throws Exception Invalid Arguments.
		 */
		public static function enquiry_process() {
			$nonce_value = isset( $_POST['_mvr_nonce'] ) ? sanitize_key( wp_unslash( $_POST['_mvr_nonce'] ) ) : '';

			if ( wp_verify_nonce( $nonce_value, 'mvr_vendor_enquiry' ) ) {
				if ( empty( $_POST['action'] ) || 'mvr_vendor_enquiry' !== $_POST['action'] ) {
					return;
				}

				try {
					$email = isset( $_POST['_email'] ) ? sanitize_email( wp_unslash( $_POST['_email'] ) ) : '';

					if ( empty( $email ) || ! is_email( $email ) ) {
						throw new Exception( esc_html__( 'Please enter valid email address', 'multi-vendor-marketplace' ) );
					}

					$name    = isset( $_POST['_name'] ) ? sanitize_text_field( wp_unslash( $_POST['_name'] ) ) : '';
					$message = isset( $_POST['_message'] ) ? sanitize_text_field( wp_unslash( $_POST['_message'] ) ) : '';

					if ( empty( $message ) ) {
						throw new Exception( esc_html__( 'Please enter your enquiry message', 'multi-vendor-marketplace' ) );
					}

					$vendor_id  = isset( $_POST['_vendor_id'] ) ? absint( wp_unslash( $_POST['_vendor_id'] ) ) : '';
					$vendor_obj = mvr_get_vendor( $vendor_id );

					if ( ! $vendor_obj ) {
						throw new Exception( esc_html__( 'Invalid Vendor', 'multi-vendor-marketplace' ) );
					}

					$user_id   = isset( $_POST['_user_id'] ) ? absint( wp_unslash( $_POST['_user_id'] ) ) : '';
					$source_id = isset( $_POST['_source_id'] ) ? absint( wp_unslash( $_POST['_source_id'] ) ) : '';
					$form_type = isset( $_POST['_form_type'] ) ? sanitize_text_field( wp_unslash( $_POST['_form_type'] ) ) : '';

					$enquiry_obj = new MVR_Enquiry();
					$enquiry_obj->set_props(
						array(
							'vendor_id'      => $vendor_id,
							'author_id'      => $user_id,
							'customer_id'    => get_current_user_id(),
							'customer_name'  => $name,
							'customer_email' => $email,
							'message'        => $message,
							'source_id'      => $source_id,
							'source_from'    => $form_type,
						)
					);
					$enquiry_obj->save();
					$enquiry_obj->update_status( 'unread' );

					/**
					 * After Store Enquiry Submitted
					 *
					 * @since 1.0.0
					 */
					do_action( 'mvr_after_store_enquiry_submitted', $enquiry_obj, $vendor_obj );

					wc_add_notice( esc_html__( 'Your enquiry has been submitted successfully.', 'multi-vendor-marketplace' ), 'success' );
				} catch ( Exception $e ) {
					wc_add_notice( $e->getMessage(), 'error' );
				}
			}
		}

		/**
		 * Review Process
		 *
		 * @since 1.0.0
		 * @throws Exception Invalid Arguments.
		 */
		public static function review_process() {
			$nonce_value = isset( $_POST['_mvr_nonce'] ) ? sanitize_key( wp_unslash( $_POST['_mvr_nonce'] ) ) : '';

			if ( wp_verify_nonce( $nonce_value, 'mvr_vendor_review' ) ) {
				if ( empty( $_POST['action'] ) || 'mvr_vendor_review' !== $_POST['action'] ) {
					return;
				}

				try {
					$rating = isset( $_POST['_rating'] ) ? absint( wp_unslash( $_POST['_rating'] ) ) : '';
					$review = isset( $_POST['_review'] ) ? sanitize_text_field( wp_unslash( $_POST['_review'] ) ) : '';

					if ( empty( $rating ) ) {
						throw new Exception( esc_html__( 'Rating Should not be empty', 'multi-vendor-marketplace' ) );
					}

					$vendor_id  = isset( $_POST['_vendor_id'] ) ? absint( wp_unslash( $_POST['_vendor_id'] ) ) : '';
					$vendor_obj = mvr_get_vendor( $vendor_id );

					if ( ! $vendor_obj ) {
						throw new Exception( esc_html__( 'Invalid Vendor', 'multi-vendor-marketplace' ) );
					}

					$user_id     = isset( $_POST['_user_id'] ) ? absint( wp_unslash( $_POST['_user_id'] ) ) : '';
					$reviews_obj = mvr_get_reviews(
						array(
							'vendor_id' => $vendor_obj->get_id(),
							'user_id'   => $user_id,
						)
					);

					if ( $reviews_obj->has_review ) {
						throw new Exception( esc_html__( 'Review Already Submitted', 'multi-vendor-marketplace' ) );
					}

					$comment_id = $vendor_obj->add_store_review( $rating, $review, $user_id );

					/**
					 * After Store review Submitted
					 *
					 * @since 1.0.0
					 */
					do_action( 'mvr_after_store_review_submitted', $comment_id, $vendor_obj );

					wc_add_notice( esc_html__( 'Thank you for sharing your experience with us!', 'multi-vendor-marketplace' ), 'success' );
				} catch ( Exception $e ) {
					wc_add_notice( $e->getMessage(), 'error' );
				}
			}
		}

		/**
		 * Create Staff.
		 *
		 * @since 1.0.0
		 * @throws Exception Invalid Arguments.
		 */
		public static function create_staff() {
			$nonce_value = isset( $_POST['_mvr_nonce'] ) ? sanitize_key( wp_unslash( $_POST['_mvr_nonce'] ) ) : '';

			if ( wp_verify_nonce( $nonce_value, 'save_mvr_vendor_staff' ) ) {
				$posted = $_POST;

				if ( empty( $posted['action'] ) || 'save_mvr_vendor_staff' !== $posted['action'] ) {
					return;
				}

				try {
					$staff_id = isset( $posted['_staff_id'] ) ? absint( wp_unslash( $posted['_staff_id'] ) ) : '';

					if ( empty( $staff_id ) ) {
						$vendor_id = isset( $posted['_vendor_id'] ) ? absint( wp_unslash( $posted['_vendor_id'] ) ) : '';

						if ( empty( $vendor_id ) ) {
							throw new Exception( esc_html__( 'Invalid Vendor', 'multi-vendor-marketplace' ) );
						}

						$vendor_obj = mvr_get_vendor( $vendor_id );

						if ( ! mvr_is_vendor( $vendor_obj ) ) {
							throw new Exception( esc_html__( 'Invalid Vendor', 'multi-vendor-marketplace' ) );
						}

						$user_name = isset( $posted['_user_name'] ) ? sanitize_title( wp_unslash( $posted['_user_name'] ) ) : '';

						if ( empty( $user_name ) ) {
							throw new Exception( esc_html__( 'Username Required', 'multi-vendor-marketplace' ) );
						}

						$email = isset( $posted['_email'] ) ? sanitize_email( wp_unslash( $posted['_email'] ) ) : '';

						if ( empty( $email ) ) {
							throw new Exception( esc_html__( 'User Email Required', 'multi-vendor-marketplace' ) );
						}

						if ( email_exists( $email ) ) {
							throw new Exception( __( 'An account is already registered with your email address.', 'multi-vendor-marketplace' ) );
						}

						$password = isset( $posted['_password'] ) ? sanitize_text_field( wp_unslash( $posted['_password'] ) ) : '';

						if ( empty( $password ) ) {
							throw new Exception( esc_html__( 'Password Required', 'multi-vendor-marketplace' ) );
						}

						$confirm_password = isset( $posted['_confirm_password'] ) ? sanitize_text_field( wp_unslash( $posted['_confirm_password'] ) ) : '';

						if ( $confirm_password !== $password ) {
							throw new Exception( esc_html__( 'Password Not Matching', 'multi-vendor-marketplace' ) );
						}

						$user_id = wc_create_new_customer( sanitize_email( $email ), $user_name, $password, array( 'is_mvr_staff' => true ) );

						if ( is_wp_error( $user_id ) ) {
							throw new Exception( $user_id->get_error_message() );
						}

						$user_obj = get_user_by( 'ID', $user_id );

						if ( ! $user_obj ) {
							throw new Exception( __( 'Invalid user.', 'multi-vendor-marketplace' ) );
						}

						$user_obj->set_role( 'mvr-staff' );

						$staff_obj = new MVR_Staff();
						$staff_obj->set_props(
							array(
								'user_id'   => $user_obj->ID,
								'name'      => $user_obj->user_login,
								'email'     => $user_obj->user_email,
								'vendor_id' => $vendor_id,
							)
						);
						$staff_obj->save();
						$staff_obj->update_status( 'active' );

						/**
						 * After Become a Vendor Register
						 *
						 * @since 1.0.0
						 */
						do_action( 'mvr_after_create_staff', $staff_obj );
					} else {
						$staff_obj = mvr_get_staff( $staff_id );

						if ( ! mvr_is_staff( $staff_obj ) ) {
							throw new Exception( esc_html__( 'Invalid Staff', 'multi-vendor-marketplace' ) );
						}
					}

					$staff_obj->set_props(
						array(
							'enable_product_management'   => isset( $posted['_enable_product_management'] ) ? 'yes' : 'no',
							'product_creation'            => isset( $posted['_product_creation'] ) ? 'yes' : 'no',
							'product_modification'        => isset( $posted['_product_modification'] ) ? 'yes' : 'no',
							'published_product_modification' => isset( $posted['_published_product_modification'] ) ? 'yes' : 'no',
							'manage_inventory'            => isset( $posted['_manage_inventory'] ) ? 'yes' : 'no',
							'product_deletion'            => isset( $posted['_product_deletion'] ) ? 'yes' : 'no',
							'enable_order_management'     => isset( $posted['_enable_order_management'] ) ? 'yes' : 'no',
							'order_status_modification'   => isset( $posted['_order_status_modification'] ) ? 'yes' : 'no',
							'commission_info_display'     => isset( $posted['_commission_info_display'] ) ? 'yes' : 'no',
							'enable_coupon_management'    => isset( $posted['_enable_coupon_management'] ) ? 'yes' : 'no',
							'coupon_creation'             => isset( $posted['_coupon_creation'] ) ? 'yes' : 'no',
							'coupon_modification'         => isset( $posted['_coupon_modification'] ) ? 'yes' : 'no',
							'published_coupon_modification' => isset( $posted['_published_coupon_modification'] ) ? 'yes' : 'no',
							'coupon_deletion'             => isset( $posted['_coupon_deletion'] ) ? 'yes' : 'no',
							'enable_commission_withdraw'  => isset( $posted['_enable_commission_withdraw'] ) ? 'yes' : 'no',
							'commission_transaction'      => isset( $posted['_commission_transaction'] ) ? 'yes' : 'no',
							'commission_transaction_info' => isset( $posted['_commission_transaction_info'] ) ? 'yes' : 'no',
						)
					);
					$staff_obj->save();

					wc_add_notice( esc_html__( 'Staff Created Successfully', 'multi-vendor-marketplace' ), 'success' );
					wp_safe_redirect( wc_get_endpoint_url( 'mvr-edit-staff', $staff_obj->get_id(), mvr_get_page_permalink( 'dashboard' ) ) );
					exit();
				} catch ( Exception $e ) {
					wc_add_notice( $e->getMessage(), 'error' );
				}
			}
		}

		/**
		 * Create Withdraw Request.
		 *
		 * @since 1.0.0
		 * @throws Exception Invalid Arguments.
		 */
		public static function create_withdraw_request() {
			$nonce_value = isset( $_POST['_mvr_nonce'] ) ? sanitize_key( wp_unslash( $_POST['_mvr_nonce'] ) ) : '';

			if ( wp_verify_nonce( $nonce_value, 'mvr_withdraw_request' ) ) {

				if ( empty( $_POST['action'] ) || 'mvr_withdraw_request' !== $_POST['action'] ) {
					return;
				}

				try {
					$vendor_id = isset( $_POST['_vendor_id'] ) ? sanitize_key( wp_unslash( $_POST['_vendor_id'] ) ) : '';

					if ( empty( $vendor_id ) ) {
						throw new Exception( esc_html__( 'Invalid Vendor', 'multi-vendor-marketplace' ) );
					}

					$vendor_obj = mvr_get_vendor( $vendor_id );

					if ( ! mvr_is_vendor( $vendor_obj ) ) {
						throw new Exception( esc_html__( 'Invalid Vendor', 'multi-vendor-marketplace' ) );
					}

					$withdraw_amount = isset( $_POST['_withdraw_amount'] ) ? (float) sanitize_text_field( wp_unslash( $_POST['_withdraw_amount'] ) ) : 0;

					if ( empty( $withdraw_amount ) ) {
						throw new Exception( esc_html__( 'Please Enter Valid Amount', 'multi-vendor-marketplace' ) );
					}

					$minimum_withdraw = (float) get_option( 'mvr_settings_min_withdraw_threshold', 0 );

					if ( $withdraw_amount < $minimum_withdraw ) {
						throw new Exception( esc_html__( 'Amount should not be less than the minimum amount', 'multi-vendor-marketplace' ) );
					}

					if ( $withdraw_amount > (float) $vendor_obj->get_amount() ) {
						throw new Exception( esc_html__( 'Amount should not be more than the maximum amount', 'multi-vendor-marketplace' ) );
					}

					// Charge amount Calculation.
					$charge_amount = $vendor_obj->calculate_withdraw_charge( $withdraw_amount );

					if ( $charge_amount > 0 ) {
						$withdraw_amount = $withdraw_amount - $charge_amount;
						$withdraw_amount = ( $withdraw_amount < 0 ) ? 0 : $withdraw_amount;
					}

					// Create Withdraw Object.
					$withdraw_obj = new MVR_Withdraw();
					$withdraw_obj->set_props(
						array(
							'amount'         => $withdraw_amount,
							'vendor_id'      => $vendor_obj->get_id(),
							'payment_method' => $vendor_obj->get_payment_method(),
							'charge_amount'  => $charge_amount,
							'created_via'    => 'manual_request',
							'source_id'      => 0,
							'source_from'    => 'vendor',
							'currency'       => get_woocommerce_currency(),
						)
					);
					$withdraw_obj->save();
					$withdraw_obj->update_status( 'pending' );

					// Update Vendor Amount.
					$vendor_amount = $vendor_obj->get_amount() - ( $withdraw_amount + $charge_amount );

					$vendor_obj->set_amount( $vendor_amount );
					$vendor_obj->save();

					/**
					 * New Withdraw Request.
					 *
					 * @since 1.0.0
					 */
					do_action( 'mvr_vendor_new_withdraw_request', $withdraw_obj, $vendor_obj );

					wc_add_notice( esc_html__( 'Request Submitted Successfully', 'multi-vendor-marketplace' ), 'success' );
					wp_safe_redirect( wc_get_endpoint_url( 'mvr-withdraw', '', mvr_get_page_permalink( 'dashboard' ) ) );
					exit();
				} catch ( Exception $e ) {
					wc_add_notice( $e->getMessage(), 'error' );
				}
			}
		}

		/**
		 * Vendor Template Redirection.
		 *
		 * @since 1.0.0
		 * @throws Exception Invalid Arguments.
		 */
		public static function vendor_template_redirect() {
			global $wp;
			$user_id = get_current_user_id();

			if ( is_user_logged_in() ) {
				if ( mvr_user_is_vendor( $user_id ) ) {
					if ( mvr_is_vendor_login_page() || mvr_is_vendor_register_page() ) {
						wp_safe_redirect( mvr_get_page_permalink( 'dashboard' ) );
						exit;
					}
				}

				if ( isset( $wp->query_vars['mvr-logout'] ) && ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'mvr-logout' ) ) {
					wp_safe_redirect( str_replace( '&amp;', '&', wp_logout_url( mvr_get_page_permalink( 'vendor_login' ) ) ) );
					exit;
				}
			} elseif ( mvr_is_dashboard_page() ) {
					wp_safe_redirect( mvr_get_page_permalink( 'vendor_login' ) );
					exit;
			}
		}

		/**
		 * Save Profile Details.
		 *
		 * @since 1.0.0
		 */
		public static function save_profile_details() {
			$nonce_value = isset( $_POST['_mvr_nonce'] ) ? sanitize_key( wp_unslash( $_POST['_mvr_nonce'] ) ) : '';

			if ( wp_verify_nonce( $nonce_value, 'save_mvr_profile_details' ) ) {

				if ( empty( $_POST['action'] ) || 'save_mvr_profile_details' !== $_POST['action'] ) {
					return;
				}

				$posted    = $_POST;
				$vendor_id = isset( $posted['_vendor_id'] ) ? wp_unslash( $posted['_vendor_id'] ) : '';

				if ( empty( $vendor_id ) ) {
					wc_add_notice( esc_html__( 'Invalid Vendor', 'multi-vendor-marketplace' ), 'error' );
				}

				$vendor_obj = mvr_get_vendor( $vendor_id );

				if ( ! mvr_is_vendor( $vendor_obj ) ) {
					wc_add_notice( esc_html__( 'Invalid Vendor', 'multi-vendor-marketplace' ), 'error' );
				}

				$logo_id   = isset( $posted['_logo_id'] ) ? wp_unslash( $posted['_logo_id'] ) : '';
				$banner_id = isset( $posted['_banner_id'] ) ? wp_unslash( $posted['_banner_id'] ) : '';
				$name      = isset( $posted['_name'] ) ? wp_unslash( $posted['_name'] ) : '';
				$shop_name = isset( $posted['_shop_name'] ) ? wp_unslash( $posted['_shop_name'] ) : '';

				if ( empty( $shop_name ) || strlen( $shop_name ) < 3 ) {
					wc_add_notice( esc_html__( 'Please enter  a valid Store Name', 'multi-vendor-marketplace' ), 'error' );
				} elseif ( mvr_check_shop_name_exists( $shop_name, $vendor_id ) ) {
					wc_add_notice( esc_html__( 'Shop Name already exists. Please try another', 'multi-vendor-marketplace' ), 'error' );
				}

				$slug = isset( $posted['_slug'] ) ? sanitize_title( $posted['_slug'] ) : '';

				if ( empty( $slug ) || strlen( $slug ) < 3 ) {
					wc_add_notice( esc_html__( 'Please enter 3 or more characters for the shop slug', 'multi-vendor-marketplace' ), 'error' );
				} elseif ( mvr_check_shop_slug_exists( $slug, $vendor_id ) ) {
					wc_add_notice( esc_html__( 'Shop slug already exists. Please try another', 'multi-vendor-marketplace' ), 'error' );
				}

				$description = isset( $posted['_description'] ) ? wp_unslash( $posted['_description'] ) : '';
				$tac         = isset( $posted['_tac'] ) ? wp_unslash( $posted['_tac'] ) : '';

				/**
				 * Profile Details Required Field.
				 *
				 * @since 1.0.0
				 */
				$required_fields = apply_filters(
					'mvr_save_profile_details_required_fields',
					array(
						'_name'        => get_option( 'mvr_dashboard_vendor_name_field_label', 'Vendor Name' ),
						'_shop_name'   => get_option( 'mvr_dashboard_vendor_store_name_field_label', 'Store Name' ),
						'_slug'        => get_option( 'mvr_dashboard_vendor_slug_field_label', 'Vendor Slug' ),
						'_description' => get_option( 'mvr_dashboard_vendor_description_field_label', 'Description' ),
						'_tac'         => get_option( 'mvr_dashboard_vendor_toc_field_label', 'Terms & Conditions' ),
					)
				);

				foreach ( $required_fields as $field_key => $field_name ) {
					if ( empty( $posted[ $field_key ] ) ) {
						/* translators: %s: Field name. */
						wc_add_notice( sprintf( __( '%s is a required field.', 'multi-vendor-marketplace' ), '<strong>' . esc_html( $field_name ) . '</strong>' ), 'error', array( 'id' => $field_key ) );
					}
				}

				$errors = $vendor_obj->set_props(
					array(
						'logo_id'     => $logo_id,
						'banner_id'   => $banner_id,
						'name'        => $name,
						'shop_name'   => $shop_name,
						'slug'        => $slug,
						'description' => $description,
						'tac'         => $tac,
					)
				);

				if ( is_wp_error( $errors ) ) {
					if ( $errors->get_error_messages() ) {
						foreach ( $errors->get_error_messages() as $error ) {
							wc_add_notice( $error, 'error' );
						}
					}
				}

				$errors = new WP_Error();

				/**
				 * Profile Details save errors.
				 *
				 * @since 1.0.0
				 */
				do_action_ref_array( 'mvr_save_vendor_profile_details_errors', array( &$errors, &$vendor_obj ) );

				if ( $errors->get_error_messages() ) {
					foreach ( $errors->get_error_messages() as $error ) {
						wc_add_notice( $error, 'error' );
					}
				}

				if ( wc_notice_count( 'error' ) === 0 ) {
					$vendor_obj->save();

					self::auto_vendor_activate( $vendor_obj );

					wc_add_notice( esc_html__( 'Profile Details Saved Successfully', 'multi-vendor-marketplace' ), 'success' );

					/**
					 * Before Save Profile Details.
					 *
					 * @since 1.0.0
					 */
					do_action( 'mvr_vendor_save_profile_details', $vendor_obj );
				}
			}
		}

		/**
		 * Save Vendor Details.
		 *
		 * @since 1.0.0
		 */
		public static function save_payout_details() {
			$nonce_value = isset( $_POST['_mvr_nonce'] ) ? sanitize_key( wp_unslash( $_POST['_mvr_nonce'] ) ) : '';

			if ( wp_verify_nonce( $nonce_value, 'save_mvr_payout_details' ) ) {

				if ( empty( $_POST['action'] ) || 'save_mvr_payout_details' !== $_POST['action'] ) {
					return;
				}

				$posted    = $_POST;
				$vendor_id = isset( $posted['_vendor_id'] ) ? wp_unslash( $posted['_vendor_id'] ) : '';

				if ( empty( $vendor_id ) ) {
					wc_add_notice( esc_html__( 'Invalid Vendor', 'multi-vendor-marketplace' ), 'error' );
				}

				$vendor_obj = mvr_get_vendor( $vendor_id );

				if ( ! mvr_is_vendor( $vendor_obj ) ) {
					wc_add_notice( esc_html__( 'Invalid Vendor', 'multi-vendor-marketplace' ), 'error' );
				}

				$payout_type     = isset( $posted['_payout_type'] ) ? wp_unslash( $posted['_payout_type'] ) : '';
				$payout_schedule = isset( $posted['_payout_schedule'] ) ? wp_unslash( $posted['_payout_schedule'] ) : '';

				$errors = $vendor_obj->set_props(
					array(
						'payout_type'     => $payout_type,
						'payout_schedule' => $payout_schedule,
					)
				);

				if ( is_wp_error( $errors ) ) {
					if ( $errors->get_error_messages() ) {
						foreach ( $errors->get_error_messages() as $error ) {
							wc_add_notice( $error, 'error' );
						}
					}
				}

				$errors = new WP_Error();

				/**
				 * Payout Details save errors.
				 *
				 * @since 1.0.0
				 */
				do_action_ref_array( 'mvr_save_vendor_payout_details_errors', array( &$errors, &$vendor_obj ) );

				if ( $errors->get_error_messages() ) {
					foreach ( $errors->get_error_messages() as $error ) {
						wc_add_notice( $error, 'error' );
					}
				}

				if ( wc_notice_count( 'error' ) === 0 ) {
					$vendor_obj->save();

					self::auto_vendor_activate( $vendor_obj );

					wc_add_notice( esc_html__( 'Payout Details Saved Successfully', 'multi-vendor-marketplace' ), 'success' );

					/**
					 * Before Save Payout Details.
					 *
					 * @since 1.0.0
					 */
					do_action( 'mvr_vendor_save_payout_details', $vendor_obj );
				}
			}
		}

		/**
		 * Save Vendor Details.
		 *
		 * @since 1.0.0
		 */
		public static function save_vendor_address() {
			$nonce_value = isset( $_POST['_mvr_nonce'] ) ? sanitize_key( wp_unslash( $_POST['_mvr_nonce'] ) ) : '';

			if ( wp_verify_nonce( $nonce_value, 'save_mvr_vendor_address' ) ) {
				if ( empty( $_POST['action'] ) || 'save_mvr_vendor_address' !== $_POST['action'] ) {
					return;
				}

				$posted    = $_POST;
				$vendor_id = isset( $posted['_vendor_id'] ) ? wp_unslash( $posted['_vendor_id'] ) : '';

				if ( empty( $vendor_id ) ) {
					wc_add_notice( esc_html__( 'Invalid Vendor', 'multi-vendor-marketplace' ), 'error' );
				}

				$vendor_obj = mvr_get_vendor( $vendor_id );

				if ( ! mvr_is_vendor( $vendor_obj ) ) {
					wc_add_notice( esc_html__( 'Invalid Vendor', 'multi-vendor-marketplace' ), 'error' );
				}

				$first_name = isset( $posted['_first_name'] ) ? wp_unslash( $posted['_first_name'] ) : '';
				$last_name  = isset( $posted['_last_name'] ) ? wp_unslash( $posted['_last_name'] ) : '';
				$address1   = isset( $posted['_address1'] ) ? wp_unslash( $posted['_address1'] ) : '';
				$address2   = isset( $posted['_address2'] ) ? wp_unslash( $posted['_address2'] ) : '';
				$city       = isset( $posted['_city'] ) ? wp_unslash( $posted['_city'] ) : '';
				$country    = isset( $posted['_country'] ) ? wp_unslash( $posted['_country'] ) : '';
				$state      = isset( $posted['_state'] ) ? wp_unslash( $posted['_state'] ) : '';
				$zip_code   = isset( $posted['_zip_code'] ) ? wp_unslash( $posted['_zip_code'] ) : '';
				$phone      = isset( $posted['_phone'] ) ? wp_unslash( $posted['_phone'] ) : '';

				/**
				 * Address Required Field.
				 *
				 * @since 1.0.0
				 */
				$required_fields = apply_filters(
					'mvr_save_address_required_fields',
					array(
						'_first_name' => get_option( 'mvr_dashboard_vendor_fname_field_label', 'First name' ),
						'_last_name'  => get_option( 'mvr_dashboard_vendor_lname_field_label', 'Last name' ),
						'_address1'   => get_option( 'mvr_dashboard_vendor_addr1_field_label', 'Address 1' ),
						'_city'       => get_option( 'mvr_dashboard_vendor_city_field_label', 'City' ),
						'_country'    => get_option( 'mvr_dashboard_vendor_country_field_label', 'Country' ),
						'_state'      => get_option( 'mvr_dashboard_vendor_state_field_label', 'State' ),
						'_zip_code'   => get_option( 'mvr_dashboard_vendor_zip_code_field_label', 'Zip Code' ),
						'_phone'      => get_option( 'mvr_dashboard_vendor_phone_field_label', 'Phone' ),
					)
				);

				foreach ( $required_fields as $field_key => $field_name ) {
					if ( empty( $posted[ $field_key ] ) ) {
						/* translators: %s: Field name. */
						wc_add_notice( sprintf( __( '%s is a required field.', 'multi-vendor-marketplace' ), '<strong>' . esc_html( $field_name ) . '</strong>' ), 'error', array( 'id' => $field_key ) );
					}
				}

				$errors = $vendor_obj->set_props(
					array(
						'first_name' => $first_name,
						'last_name'  => $last_name,
						'address1'   => $address1,
						'address2'   => $address2,
						'city'       => $city,
						'country'    => $country,
						'state'      => $state,
						'zip_code'   => $zip_code,
						'phone'      => $phone,
					)
				);

				$vendor_obj->save();

				if ( is_wp_error( $errors ) ) {
					if ( $errors->get_error_messages() ) {
						foreach ( $errors->get_error_messages() as $error ) {
							wc_add_notice( $error, 'error' );
						}
					}
				}

				$errors = new WP_Error();

				/**
				 * Address save errors.
				 *
				 * @since 1.0.0
				 */
				do_action_ref_array( 'mvr_save_vendor_address_errors', array( &$errors, &$vendor_obj ) );

				if ( $errors->get_error_messages() ) {
					foreach ( $errors->get_error_messages() as $error ) {
						wc_add_notice( $error, 'error' );
					}
				}

				if ( wc_notice_count( 'error' ) === 0 ) {
					$vendor_obj->save();

					self::auto_vendor_activate( $vendor_obj );

					wc_add_notice( esc_html__( 'Address Saved Successfully', 'multi-vendor-marketplace' ), 'success' );

					/**
					 * Before Save Address.
					 *
					 * @since 1.0.0
					 */
					do_action( 'mvr_vendor_save_address', $vendor_obj );
				}
			}
		}

		/**
		 * Save Vendor Details.
		 *
		 * @since 1.0.0
		 */
		public static function save_social_links() {
			$nonce_value = isset( $_POST['_mvr_nonce'] ) ? sanitize_key( wp_unslash( $_POST['_mvr_nonce'] ) ) : '';

			if ( wp_verify_nonce( $nonce_value, 'save_mvr_social_media' ) ) {

				if ( empty( $_POST['action'] ) || 'save_mvr_social_media' !== $_POST['action'] ) {
					return;
				}

				$posted    = $_POST;
				$vendor_id = isset( $posted['_vendor_id'] ) ? wp_unslash( $posted['_vendor_id'] ) : '';

				if ( empty( $vendor_id ) ) {
					wc_add_notice( esc_html__( 'Invalid Vendor', 'multi-vendor-marketplace' ), 'error' );
				}

				$vendor_obj = mvr_get_vendor( $vendor_id );

				if ( ! mvr_is_vendor( $vendor_obj ) ) {
					wc_add_notice( esc_html__( 'Invalid Vendor', 'multi-vendor-marketplace' ), 'error' );
				}

				$facebook  = isset( $posted['_facebook'] ) ? wp_unslash( $posted['_facebook'] ) : '';
				$twitter   = isset( $posted['_twitter'] ) ? wp_unslash( $posted['_twitter'] ) : '';
				$youtube   = isset( $posted['_youtube'] ) ? wp_unslash( $posted['_youtube'] ) : '';
				$instagram = isset( $posted['_instagram'] ) ? wp_unslash( $posted['_instagram'] ) : '';
				$linkedin  = isset( $posted['_linkedin'] ) ? wp_unslash( $posted['_linkedin'] ) : '';
				$pinterest = isset( $posted['_pinterest'] ) ? wp_unslash( $posted['_pinterest'] ) : '';

				$errors = $vendor_obj->set_props(
					array(
						'facebook'  => $facebook,
						'twitter'   => $twitter,
						'youtube'   => $youtube,
						'instagram' => $instagram,
						'linkedin'  => $linkedin,
						'pinterest' => $pinterest,
					)
				);

				if ( is_wp_error( $errors ) ) {
					if ( $errors->get_error_messages() ) {
						foreach ( $errors->get_error_messages() as $error ) {
							wc_add_notice( $error, 'error' );
						}
					}
				}

				$errors = new WP_Error();

				/**
				 * Social Media save errors.
				 *
				 * @since 1.0.0
				 */
				do_action_ref_array( 'mvr_save_vendor_social_media_errors', array( &$errors, &$vendor_obj ) );

				if ( $errors->get_error_messages() ) {
					foreach ( $errors->get_error_messages() as $error ) {
						wc_add_notice( $error, 'error' );
					}
				}

				if ( wc_notice_count( 'error' ) === 0 ) {
					$vendor_obj->save();

					self::auto_vendor_activate( $vendor_obj );

					wc_add_notice( esc_html__( 'Social Media Saved Successfully', 'multi-vendor-marketplace' ), 'success' );

					/**
					 * Before Save Social Media
					 *
					 * @since 1.0.0
					 */
					do_action( 'mvr_vendor_save_social_media', $vendor_obj );
				}
			}
		}

		/**
		 * Save Payment Details.
		 *
		 * @since 1.0.0
		 */
		public static function save_payment_details() {
			$vendor_obj = mvr_get_current_vendor_object();

			if ( ! $vendor_obj ) {
				return;
			}

			$nonce_value = isset( $_POST['_mvr_nonce'] ) ? sanitize_key( wp_unslash( $_POST['_mvr_nonce'] ) ) : '';

			if ( ! wp_verify_nonce( $nonce_value, 'save-mvr-payment-details-nonce' ) ) {
				return;
			}

			if ( empty( $_POST['action'] ) || 'save_mvr_payment_details' !== $_POST['action'] ) {
				return;
			}

			$posted         = $_POST;
			$payment_method = isset( $posted['_payment_method'] ) ? wp_unslash( $posted['_payment_method'] ) : '1';

			if ( '2' === $payment_method ) {
				$paypal_email = isset( $posted['_paypal_email'] ) ? wp_unslash( $posted['_paypal_email'] ) : '';

				if ( empty( $paypal_email ) ) {
					wc_add_notice( esc_html__( 'PayPal Email is required', 'multi-vendor-marketplace' ), 'error' );
				}

				$errors = $vendor_obj->set_props(
					array(
						'payment_method' => $payment_method,
						'paypal_email'   => $paypal_email,
					)
				);

				if ( is_wp_error( $errors ) ) {
					if ( $errors->get_error_messages() ) {
						foreach ( $errors->get_error_messages() as $error ) {
							wc_add_notice( $error, 'error' );
						}
					}
				}
			} else {
				$required_fields = array(
					'_bank_account_name'   => esc_html__( 'Account Name', 'multi-vendor-marketplace' ),
					'_bank_account_number' => esc_html__( 'Account Number', 'multi-vendor-marketplace' ),
					'_bank_name'           => esc_html__( 'Bank Name', 'multi-vendor-marketplace' ),
				);

				foreach ( $required_fields as $field_key => $field_name ) {
					if ( empty( $posted[ $field_key ] ) ) {
						/* translators: %s: Field name. */
						wc_add_notice( sprintf( __( '%s is a required field.', 'multi-vendor-marketplace' ), '<strong>' . esc_html( $field_name ) . '</strong>' ), 'error', array( 'id' => $field_key ) );
					}
				}

				$bank_account_name   = isset( $posted['_bank_account_name'] ) ? wp_unslash( $posted['_bank_account_name'] ) : '';
				$bank_account_number = isset( $posted['_bank_account_number'] ) ? wp_unslash( $posted['_bank_account_number'] ) : '';
				$bank_account_type   = isset( $posted['_bank_account_type'] ) ? wp_unslash( $posted['_bank_account_type'] ) : '1';
				$bank_name           = isset( $posted['_bank_name'] ) ? wp_unslash( $posted['_bank_name'] ) : '';
				$iban                = isset( $posted['_iban'] ) ? wp_unslash( $posted['_iban'] ) : '';
				$swift               = isset( $posted['_swift'] ) ? wp_unslash( $posted['_swift'] ) : '';
				$errors              = $vendor_obj->set_props(
					array(
						'payment_method'      => $payment_method,
						'bank_account_name'   => $bank_account_name,
						'bank_account_number' => $bank_account_number,
						'bank_account_type'   => $bank_account_type,
						'bank_name'           => $bank_name,
						'iban'                => $iban,
						'swift'               => $swift,
					)
				);

				if ( is_wp_error( $errors ) ) {
					if ( $errors->get_error_messages() ) {
						foreach ( $errors->get_error_messages() as $error ) {
							wc_add_notice( $error, 'error' );
						}
					}
				}
			}

			$errors = new WP_Error();

			/**
			 * Payment Details save errors.
			 *
			 * @since 1.0.0
			 */
			do_action_ref_array( 'mvr_save_vendor_payment_details_errors', array( &$errors, &$vendor_obj ) );

			if ( $errors->get_error_messages() ) {
				foreach ( $errors->get_error_messages() as $error ) {
					wc_add_notice( $error, 'error' );
				}
			}

			if ( wc_notice_count( 'error' ) === 0 ) {
				$vendor_obj->save();

				self::auto_vendor_activate( $vendor_obj );

				wc_add_notice( esc_html__( 'Payment Details Saved Successfully', 'multi-vendor-marketplace' ), 'success' );

				/**
				 * Before Save Payment Details.
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_vendor_save_payment_details', $vendor_obj );
			}
		}

		/**
		 * Automatic Vendor Activate.
		 *
		 * @since 1.0.0
		 * @param MVR_Vendor $vendor_obj Vendor Object.
		 */
		public static function auto_vendor_activate( $vendor_obj ) {
			if ( 'mvr-pending' !== $vendor_obj->get_status() ) {
				return;
			}

			if ( ! $vendor_obj->cleared_form_filling() ) {
				return;
			}

			if ( 'yes' === get_option( 'mvr_settings_vendor_admin_approval_req', 'yes' ) ) {
				return;
			}

			if ( 'yes' === get_option( 'mvr_settings_enable_vendor_subscription', 'no' ) && ( ! $vendor_obj->has_subscribed() || 'active' !== $vendor_obj->get_subscription_status() ) ) {
				return;
			}

			$vendor_obj->update_status( 'active' );
		}

		/**
		 * Update Order Status
		 *
		 * @since 1.0.0
		 */
		public static function update_order_status() {
			$vendor_obj = mvr_get_current_vendor_object();

			if ( ! $vendor_obj ) {
				return;
			}

			$nonce_value = isset( $_POST['_mvr_nonce'] ) ? sanitize_key( wp_unslash( $_POST['_mvr_nonce'] ) ) : '';

			if ( ! wp_verify_nonce( $nonce_value, 'mvr-update-order-status' ) ) {
				return;
			}

			if ( empty( $_POST['action'] ) || 'mvr_update_order_status' !== $_POST['action'] ) {
				return;
			}

			$posted    = $_POST;
			$order_id  = isset( $posted['_order_id'] ) ? wp_unslash( $posted['_order_id'] ) : '';
			$order_obj = new WC_Order( $order_id );

			if ( empty( $order_id ) || ! is_a( $order_obj, 'WC_Order' ) ) {
				wc_add_notice( esc_html__( 'Invalid Order', 'multi-vendor-marketplace' ), 'error' );
				return false;
			}

			$order_status = isset( $posted['_order_status'] ) ? wp_unslash( $posted['_order_status'] ) : '';

			if ( $order_obj->get_status() === $order_status ) {
				return;
			}

			$order_obj->update_status( $order_status, esc_html__( 'Updated by Vendor', 'multi-vendor-marketplace' ), true );

			wc_add_notice( esc_html__( 'Order Updated', 'multi-vendor-marketplace' ), 'success' );

			/**
			 * Order Status Updated.
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_vendor_order_status_updated', $order_obj, $vendor_obj );
		}
	}

	MVR_Form_Handler::init();
}
