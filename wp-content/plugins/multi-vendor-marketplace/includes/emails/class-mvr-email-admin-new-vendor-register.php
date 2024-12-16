<?php
/**
 * Admin  - New Vendor Register.
 *
 * @package Multi-Vendor for WooCommerce\emails\
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MVR_Email_Admin_New_Vendor_Register', false ) ) {

	/**
	 * New Vendor Register.
	 *
	 * New Vendor Register emails are sent to admin when a vendor register submitted by a vendor.
	 *
	 * @class MVR_Email_Admin_New_Vendor_Register
	 * @package class
	 * @extends MVR_Abstract_Email
	 */
	class MVR_Email_Admin_New_Vendor_Register extends MVR_Abstract_Email {

		/**
		 * Gets the coupon object.
		 *
		 * @var WC_Coupon
		 */
		public $coupon_obj;

		/**
		 * Gets the vendor object.
		 *
		 * @var MVR_Vendor
		 */
		public $vendor_obj;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id             = 'mvr_email_admin_new_vendor_register';
			$this->title          = __( 'New Vendor Register', 'multi-vendor-marketplace' );
			$this->description    = __( 'New Vendor Register emails are sent to Admin when a vendor registered by a customer.', 'multi-vendor-marketplace' );
			$this->template_html  = 'emails/admin-new-vendor-register.php';
			$this->template_plain = 'emails/plain/admin-new-vendor-register.php';
			$this->subject        = __( '{site_title}: New Vendor Registered', 'multi-vendor-marketplace' );
			$this->heading        = __( 'New Vendor Registered on your Site', 'multi-vendor-marketplace' );

			// Triggers for this email.
			add_action( 'mvr_after_register_vendor_notification', array( $this, 'trigger' ) );
			add_action( 'mvr_after_become_vendor_notification', array( $this, 'trigger' ) );

			// Other settings.
			$this->supports = array( 'recipient' );

			// Call parent constructor.
			parent::__construct();
		}

		/**
		 * Get content args.
		 *
		 * @since 1.0.0
		 * @return Array
		 */
		public function get_content_args() {
			$content_args                  = parent::get_content_args();
			$content_args['vendor_obj']    = $this->vendor_obj;
			$content_args['sent_to_admin'] = true;

			return $content_args;
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @since 1.0.0
		 * @param MVR_Vendor $vendor_obj Vendor Object.
		 */
		public function trigger( $vendor_obj ) {
			$vendor_obj = mvr_get_vendor( $vendor_obj );

			if ( mvr_is_vendor( $vendor_obj ) && 'active' === $vendor_obj->get_status() ) {
				$this->vendor_obj = $vendor_obj;
				$this->maybe_trigger();
			}
		}
	}

}

return new MVR_Email_Admin_New_Vendor_Register();
