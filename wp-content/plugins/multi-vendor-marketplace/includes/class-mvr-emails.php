<?php
/**
 * Emails.
 *
 * @package Multi-Vendor for WooCommerce\Classes\
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MVR_Emails' ) ) {
	/**
	 * Emails class.
	 *
	 * @class MVR_Emails
	 * @package Class
	 */
	class MVR_Emails {

		/**
		 * Email notification classes
		 *
		 * @var WC_Email[]
		 */
		protected static $emails = array();

		/**
		 * Available email notification classes to load
		 *
		 * @var WC_Email::id => WC_Email class
		 */
		protected static $email_classes = array(
			'mvr_email_admin_new_coupon_request'     => 'MVR_Email_Admin_New_Coupon_Request',
			'mvr_email_admin_new_order'              => 'MVR_Email_Admin_New_Order',
			'mvr_email_admin_new_product_request'    => 'MVR_Email_Admin_New_Product_Request',
			'mvr_email_admin_new_review'             => 'MVR_Email_Admin_New_Review',
			'mvr_email_admin_new_vendor_register'    => 'MVR_Email_Admin_New_Vendor_Register',
			'mvr_email_admin_new_vendor_request'     => 'MVR_Email_Admin_New_Vendor_Request',
			'mvr_email_admin_new_withdraw_request'   => 'MVR_Email_Admin_New_withdraw_Request',
			'mvr_email_staff_assigned'               => 'MVR_Email_Staff_Assigned',
			'mvr_email_staff_deleted'                => 'MVR_Email_Staff_Deleted',
			'mvr_email_vendor_coupon_approved'       => 'MVR_Email_Vendor_Coupon_Approved',
			'mvr_email_vendor_coupon_rejected'       => 'MVR_Email_Vendor_Coupon_Rejected',
			'mvr_email_vendor_new_coupon_submitted'  => 'MVR_Email_Vendor_New_Coupon_Submitted',
			'mvr_email_vendor_new_enquiry'           => 'MVR_Email_Vendor_New_Enquiry',
			'mvr_email_vendor_new_order'             => 'MVR_Email_Vendor_New_Order',
			'mvr_email_vendor_new_product_submitted' => 'MVR_Email_Vendor_New_Product_Submitted',
			'mvr_email_vendor_new_register'          => 'MVR_Email_Vendor_New_Register',
			'mvr_email_vendor_new_account_created'   => 'MVR_Email_Vendor_New_Account_Created',
			'mvr_email_vendor_new_review'            => 'MVR_Email_Vendor_New_Review',
			'mvr_email_vendor_new_withdraw_request'  => 'MVR_Email_Vendor_New_Withdraw_Request',
			'mvr_email_vendor_partial_register'      => 'MVR_Email_Vendor_Partial_Register',
			'mvr_email_vendor_product_approved'      => 'MVR_Email_Vendor_Product_Approved',
			'mvr_email_vendor_product_rejected'      => 'MVR_Email_Vendor_Product_Rejected',
			'mvr_email_vendor_approved'              => 'MVR_Email_Vendor_Approved',
			'mvr_email_vendor_rejected'              => 'MVR_Email_Vendor_Rejected',
			'mvr_email_vendor_revenue_credited'      => 'MVR_Email_Vendor_Revenue_Credited',
			'mvr_email_vendor_revenue_earned'        => 'MVR_Email_Vendor_Revenue_Earned',
			'mvr_email_vendor_staff_assigned'        => 'MVR_Email_Vendor_Staff_Assigned',
			'mvr_email_vendor_staff_deleted'         => 'MVR_Email_Vendor_Staff_Deleted',
			'mvr_email_vendor_withdraw_approved'     => 'MVR_Email_Vendor_Withdraw_Approved',
			'mvr_email_vendor_withdraw_rejected'     => 'MVR_Email_Vendor_Withdraw_Rejected',
		);

		/**
		 * Init the email class hooks in all emails that can be sent.
		 *
		 * @since 1.0.0
		 */
		public static function init() {
			add_filter( 'woocommerce_email_classes', __CLASS__ . '::add_email_classes' );
			add_filter( 'woocommerce_email_actions', __CLASS__ . '::add_email_actions' );
			add_filter( 'woocommerce_template_directory', __CLASS__ . '::set_template_directory', 10, 2 );
			add_filter( 'woocommerce_template_path', __CLASS__ . '::set_template_path' );
			add_action( 'admin_init', __CLASS__ . '::hide_plain_text_template' );

			// Email Send Acknowledgement.
			add_action( 'mvr_email_sent', __CLASS__ . '::email_sent' );
			add_action( 'mvr_email_failed_to_sent', __CLASS__ . '::email_failed_to_sent' );

			add_action( 'mvr_email_coupon_details', __CLASS__ . '::coupon_details', 10, 4 );
			add_action( 'mvr_email_order_details', __CLASS__ . '::order_details', 10, 4 );
			add_action( 'mvr_email_vendor_details', __CLASS__ . '::vendor_details', 10, 4 );
			add_action( 'mvr_email_product_details', __CLASS__ . '::product_details', 10, 4 );
			add_action( 'mvr_email_staff_details', __CLASS__ . '::staff_details', 10, 4 );
			add_action( 'mvr_email_withdraw_details', __CLASS__ . '::withdraw_details', 10, 4 );
		}

		/**
		 * Get content type.
		 *
		 * @since 1.0.0
		 * @return String
		 * */
		public static function get_content_type() {
			return 'text/html';
		}

		/**
		 * Get email headers.
		 *
		 * @since 1.0.0
		 * @return String
		 * */
		public static function get_headers() {
			$header = 'Content-Type: ' . self::get_content_type() . "\r\n";

			return $header;
		}

		/**
		 * Get the from address.
		 *
		 * @since 1.0.0
		 * @return String
		 * */
		public static function get_from_address() {
			$from_address = get_option( 'woocommerce_email_from_address' ) !== '' ? get_option( 'woocommerce_email_from_address' ) : get_option( 'new_admin_email' );

			return sanitize_email( $from_address );
		}

		/**
		 * Get the from name.
		 * */
		public static function get_from_name() {
			$from_name = get_option( 'woocommerce_email_from_name' ) !== '' ? get_option( 'woocommerce_email_from_name' ) : get_option( 'blogname' );

			return wp_specialchars_decode( esc_html( $from_name ), ENT_QUOTES );
		}

		/**
		 * Send the email.
		 *
		 * @since 1.0.0
		 * @param Mixed  $to          Receiver.
		 * @param Mixed  $subject     Email subject.
		 * @param Mixed  $message     Message.
		 * @param String $headers     Email headers (default: "Content-Type: text/html\r\n").
		 * @param String $attachments Attachments (default: "").
		 * @return Boolean
		 */
		public static function send( $to, $subject, $message, $headers = false, $attachments = '' ) {
			if ( ! $headers ) {
				$headers = self::get_headers();
			}

			add_filter( 'wp_mail_from', array( __CLASS__, 'get_from_address' ), 12 );
			add_filter( 'wp_mail_from_name', array( __CLASS__, 'get_from_name' ), 12 );
			add_filter( 'wp_mail_content_type', array( __CLASS__, 'get_content_type' ), 12 );

			$mailer = WC()->mailer();
			$return = $mailer->send( $to, $subject, $message, $headers, $attachments );

			remove_filter( 'wp_mail_from', array( __CLASS__, 'get_from_address' ) );
			remove_filter( 'wp_mail_from_name', array( __CLASS__, 'get_from_name' ) );
			remove_filter( 'wp_mail_content_type', array( __CLASS__, 'get_content_type' ) );

			return $return;
		}

		/**
		 * Load our email classes.
		 *
		 * @since 1.0.0
		 * @param Array $emails Emails.
		 */
		public static function add_email_classes( $emails ) {
			if ( ! empty( self::$emails ) ) {
				return $emails + self::$emails;
			}

			// Include email classes.
			include_once 'abstracts/class-mvr-abstract-email.php';

			foreach ( self::$email_classes as $id => $class ) {
				$file_name = 'class-' . strtolower( str_replace( '_', '-', $class ) );
				$path      = MVR_ABSPATH . "includes/emails/{$file_name}.php";

				if ( is_readable( $path ) ) {
					self::$emails[ $class ] = include $path;
				}
			}

			return $emails + self::$emails;
		}

		/**
		 * Hook in all our emails to notify.
		 *
		 * @since 1.0.0
		 * @param Array $email_actions Email Actions.
		 * @return Array
		 */
		public static function add_email_actions( $email_actions ) {
			$email_actions[] = 'mvr_vendor_new_coupon_submitted';
			$email_actions[] = 'mvr_create_new_order';
			$email_actions[] = 'mvr_vendor_new_product_submitted';
			$email_actions[] = 'mvr_after_store_review_submitted';
			$email_actions[] = 'mvr_after_register_vendor';
			$email_actions[] = 'mvr_admin_after_create_vendor';
			$email_actions[] = 'mvr_after_become_vendor';
			$email_actions[] = 'mvr_after_create_staff';
			$email_actions[] = 'mvr_vendor_new_withdraw_request';
			$email_actions[] = 'mvr_admin_after_create_withdraw';
			$email_actions[] = 'mvr_new_transaction';
			$email_actions[] = 'mvr_after_transaction_complete';
			$email_actions[] = 'mvr_admin_staff_before_save';
			$email_actions[] = 'mvr_after_assign_staff';
			$email_actions[] = 'mvr_admin_after_create_staff';
			$email_actions[] = 'mvr_before_remove_staff';
			$email_actions[] = 'mvr_admin_product_save';
			$email_actions[] = 'mvr_before_delete_staff';
			$email_actions[] = 'mvr_vendor_status_active';
			$email_actions[] = 'mvr_vendor_status_reject';
			$email_actions[] = 'mvr_admin_coupon_save';
			$email_actions[] = 'mvr_after_bank_transfer_completed';
			$email_actions[] = 'mvr_after_paypal_payout_completed';
			$email_actions[] = 'mvr_after_rejected_withdraw_request';
			$email_actions[] = 'mvr_after_store_enquiry_submitted';

			return $email_actions;
		}

		/**
		 * Hide Template - Plain text
		 *
		 * @since 1.0.0
		 */
		public static function hide_plain_text_template() {
			if ( ! isset( $_GET['section'] ) ) {
				return;
			}

			WC()->mailer();

			if ( in_array( $_GET['section'], array_map( 'strtolower', array_keys( self::$emails ) ), true ) ) {
				echo '<style>div.template_plain{display:none;}</style>';
			}
		}

		/**
		 * Add the order approval note when the email sent successful.
		 *
		 * @since 1.0.0
		 * @param WC_Email $email Email Object.
		 */
		public static function email_sent( $email ) {
			if ( is_object( $email->object ) ) {
				/* translators: 1: email name 2: email recipients */
				$email->object->add_note( sprintf( __( '<b>%1$s</b> email has been sent to %2$s.', 'multi-vendor-marketplace' ), $email->title, $email->recipient ) );
			}
		}

		/**
		 * Add the order approval note when the email failed to sent.
		 *
		 * @since 1.0.0
		 * @param WC_Email $email Email Object.
		 */
		public static function email_failed_to_sent( $email ) {
			if ( is_object( $email->object ) ) {
				/* translators: 1: email name 2: email recipients */
				$email->object->add_note( sprintf( __( 'Failed to send <b>%1$s</b> email to %2$s.', 'multi-vendor-marketplace' ), $email->title, $email->recipient ) );
			}
		}

		/**
		 * Set our email templates directory.
		 *
		 * @since 1.0.0
		 * @param String $template_directory Template directory.
		 * @param String $template Template.
		 * @return String
		 */
		public static function set_template_directory( $template_directory, $template ) {
			$templates = array_map( array( 'MVR_Emails', 'get_template_name' ), array_keys( self::$email_classes ) );

			foreach ( $templates as $name ) {
				if ( in_array( $template, array( "emails/{$name}.php" ), true ) ) {
					return dirname( MVR_PLUGIN_SLUG );
				}
			}

			return $template_directory;
		}

		/**
		 * Set our template path.
		 *
		 * @since 1.0.0
		 * @param String $path Plugin Path.
		 * @return String
		 */
		public static function set_template_path( $path ) {
			if ( isset( $_REQUEST['_wpnonce'] ) && ( isset( $_POST['template_html_code'] ) || isset( $_POST['template_plain_code'] ) ) && wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'woocommerce-settings' ) ) {
				if ( isset( $_GET['section'] ) && in_array( wc_clean( wp_unslash( $_GET['section'] ) ), array_map( 'strtolower', array_values( self::$email_classes ) ) ) ) {
					$path = dirname( MVR_PLUGIN_SLUG );
				}
			}

			return $path;
		}

		/**
		 * Get the template name from email ID
		 *
		 * @since 1.0.0
		 * @param Integer $id Template ID.
		 * @return String
		 */
		public static function get_template_name( $id ) {
			return str_replace( '_', '-', ltrim( $id, 'mvr_' ) );
		}

		/**
		 * Are emails available ?
		 *
		 * @since 1.0.0
		 * @return WC_Email class
		 */
		public static function available() {
			WC()->mailer();

			return ! empty( self::$emails ) ? true : false;
		}

		/**
		 * Return the email class
		 *
		 * @since 1.0.0
		 * @param String $id Template ID.
		 * @return null|WC_Email class name
		 */
		public static function get_email_class( $id ) {
			$id = strtolower( $id );

			return isset( self::$email_classes[ $id ] ) ? self::$email_classes[ $id ] : null;
		}

		/**
		 * Return the emails
		 *
		 * @since 1.0.0
		 * @return WC_Email[]
		 */
		public static function get_emails() {
			WC()->mailer();

			return self::$emails;
		}

		/**
		 * Return the email
		 *
		 * @since 1.0.0
		 * @param string $id ID.
		 * @return WC_Email
		 */
		public static function get_email( $id ) {
			WC()->mailer();

			$class = self::get_email_class( $id );

			return isset( self::$emails[ $class ] ) ? self::$emails[ $class ] : null;
		}

		/**
		 * Show the coupon details table
		 *
		 * @since 1.0.0
		 * @param WC_Coupon $coupon_obj Coupon Object.
		 * @param Boolean   $sent_to_admin If should sent to admin.
		 * @param Boolean   $plain_text    If is plain text email.
		 * @param String    $email         Email address.
		 */
		public static function coupon_details( $coupon_obj, $sent_to_admin = false, $plain_text = false, $email = '' ) {
			if ( $plain_text ) {
				mvr_get_template(
					'emails/plain/email-coupon-details.php',
					array(
						'coupon_obj'    => $coupon_obj,
						'sent_to_admin' => $sent_to_admin,
						'plain_text'    => $plain_text,
						'email'         => $email,
					)
				);
			} else {
				mvr_get_template(
					'emails/email-coupon-details.php',
					array(
						'coupon_obj'    => $coupon_obj,
						'sent_to_admin' => $sent_to_admin,
						'plain_text'    => $plain_text,
						'email'         => $email,
					)
				);
			}
		}

		/**
		 * Show the order details table
		 *
		 * @since 1.0.0
		 * @param WC_Order $order_obj Order Object.
		 * @param Boolean  $sent_to_admin If should sent to admin.
		 * @param Boolean  $plain_text    If is plain text email.
		 * @param String   $email         Email address.
		 */
		public static function order_details( $order_obj, $sent_to_admin = false, $plain_text = false, $email = '' ) {
			$order_obj = wc_get_order( $order_obj );

			if ( $plain_text ) {
				mvr_get_template(
					'emails/plain/email-order-details.php',
					array(
						'order_obj'     => $order_obj,
						'sent_to_admin' => $sent_to_admin,
						'plain_text'    => $plain_text,
						'email'         => $email,
					)
				);
			} else {
				mvr_get_template(
					'emails/email-order-details.php',
					array(
						'order_obj'     => $order_obj,
						'sent_to_admin' => $sent_to_admin,
						'plain_text'    => $plain_text,
						'email'         => $email,
					)
				);
			}
		}

		/**
		 * Show the vendor details table
		 *
		 * @since 1.0.0
		 * @param MVR_Order $vendor_obj Vendor Object.
		 * @param Boolean   $sent_to_admin If should sent to admin.
		 * @param Boolean   $plain_text    If is plain text email.
		 * @param String    $email         Email address.
		 */
		public static function vendor_details( $vendor_obj, $sent_to_admin = false, $plain_text = false, $email = '' ) {
			if ( $plain_text ) {
				mvr_get_template(
					'emails/plain/email-vendor-details.php',
					array(
						'vendor_obj'    => $vendor_obj,
						'sent_to_admin' => $sent_to_admin,
						'plain_text'    => $plain_text,
						'email'         => $email,
					)
				);
			} else {
				mvr_get_template(
					'emails/email-vendor-details.php',
					array(
						'vendor_obj'    => $vendor_obj,
						'sent_to_admin' => $sent_to_admin,
						'plain_text'    => $plain_text,
						'email'         => $email,
					)
				);
			}
		}

		/**
		 * Show the Staff details table
		 *
		 * @since 1.0.0
		 * @param MVR_Staff $staff_obj Staff Object.
		 * @param Boolean   $sent_to_admin If should sent to admin.
		 * @param Boolean   $plain_text    If is plain text email.
		 * @param String    $email         Email address.
		 */
		public static function staff_details( $staff_obj, $sent_to_admin = false, $plain_text = false, $email = '' ) {
			if ( $plain_text ) {
				mvr_get_template(
					'emails/plain/email-staff-details.php',
					array(
						'staff_obj'     => $staff_obj,
						'sent_to_admin' => $sent_to_admin,
						'plain_text'    => $plain_text,
						'email'         => $email,
					)
				);
			} else {
				mvr_get_template(
					'emails/email-staff-details.php',
					array(
						'staff_obj'     => $staff_obj,
						'sent_to_admin' => $sent_to_admin,
						'plain_text'    => $plain_text,
						'email'         => $email,
					)
				);
			}
		}

		/**
		 * Show the Withdraw details table
		 *
		 * @since 1.0.0
		 * @param MVR_Withdraw $withdraw_obj Withdraw Object.
		 * @param Boolean      $sent_to_admin If should sent to admin.
		 * @param Boolean      $plain_text    If is plain text email.
		 * @param String       $email         Email address.
		 */
		public static function withdraw_details( $withdraw_obj, $sent_to_admin = false, $plain_text = false, $email = '' ) {
			if ( $plain_text ) {
				mvr_get_template(
					'emails/plain/email-withdraw-details.php',
					array(
						'withdraw_obj'  => $withdraw_obj,
						'sent_to_admin' => $sent_to_admin,
						'plain_text'    => $plain_text,
						'email'         => $email,
					)
				);
			} else {
				mvr_get_template(
					'emails/email-withdraw-details.php',
					array(
						'withdraw_obj'  => $withdraw_obj,
						'sent_to_admin' => $sent_to_admin,
						'plain_text'    => $plain_text,
						'email'         => $email,
					)
				);
			}
		}

		/**
		 * Show the product details table
		 *
		 * @since 1.0.0
		 * @param WC_Product $product_obj Product Object.
		 * @param Boolean    $sent_to_admin If should sent to admin.
		 * @param Boolean    $plain_text    If is plain text email.
		 * @param String     $email         Email address.
		 */
		public static function product_details( $product_obj, $sent_to_admin = false, $plain_text = false, $email = '' ) {
			if ( $plain_text ) {
				mvr_get_template(
					'emails/plain/email-product-details.php',
					array(
						'product_obj'   => $product_obj,
						'sent_to_admin' => $sent_to_admin,
						'plain_text'    => $plain_text,
						'email'         => $email,
					)
				);
			} else {
				mvr_get_template(
					'emails/email-product-details.php',
					array(
						'product_obj'   => $product_obj,
						'sent_to_admin' => $sent_to_admin,
						'plain_text'    => $plain_text,
						'email'         => $email,
					)
				);
			}
		}
	}

	MVR_Emails::init();
}
