<?php
/**
 * Vendor  - New Register.
 *
 * @package Multi-Vendor for WooCommerce\emails\
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MVR_Email_Vendor_New_Register', false ) ) {

	/**
	 * New Vendor Register.
	 *
	 * @class MVR_Email_Vendor_New_Register
	 * @package class
	 * @extends MVR_Abstract_Email
	 */
	class MVR_Email_Vendor_New_Register extends MVR_Abstract_Email {

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
			$this->id             = 'mvr_email_vendor_new_register';
			$this->title          = __( 'Vendor Application Submitted', 'multi-vendor-marketplace' );
			$this->description    = __( 'Vendor Application Submitted emails are sent to vendor when a vendor register as a vendor.', 'multi-vendor-marketplace' );
			$this->template_html  = 'emails/vendor-new-register.php';
			$this->template_plain = 'emails/plain/vendor-new-register.php';
			$this->subject        = __( '{site_title}: Vendor Application Submission Success', 'multi-vendor-marketplace' );
			$this->heading        = __( 'Your Vendor Application has been successfully completed', 'multi-vendor-marketplace' );

			// Triggers for this email.
			add_action( 'mvr_after_register_vendor_notification', array( $this, 'trigger' ) );
			add_action( 'mvr_after_become_vendor_notification', array( $this, 'trigger' ) );
			add_action( 'mvr_admin_after_create_vendor_notification', array( $this, 'trigger' ) );

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
			$content_args               = parent::get_content_args();
			$content_args['vendor_obj'] = $this->vendor_obj;

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
				$this->recipient  = $vendor_obj->get_email();

				$this->maybe_trigger();
			}
		}
	}

}

return new MVR_Email_Vendor_New_Register();