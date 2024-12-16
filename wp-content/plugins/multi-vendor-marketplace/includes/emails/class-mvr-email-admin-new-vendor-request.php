<?php
/**
 * Admin  - New Vendor Request.
 *
 * @package Multi-Vendor for WooCommerce\emails\
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MVR_Email_Admin_New_Vendor_Request', false ) ) {

	/**
	 * New Vendor Request.
	 *
	 * New vendor request emails are sent to admin when a vendor request submitted by a vendor.
	 *
	 * @class MVR_Email_Admin_New_Vendor_Request
	 * @package class
	 * @extends MVR_Abstract_Email
	 */
	class MVR_Email_Admin_New_Vendor_Request extends MVR_Abstract_Email {

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
			$this->id             = 'mvr_email_admin_new_vendor_request';
			$this->title          = __( 'New Vendor Request', 'multi-vendor-marketplace' );
			$this->description    = __( 'New Vendor Request emails are sent to Admin when a vendor request submitted by a customer.', 'multi-vendor-marketplace' );
			$this->template_html  = 'emails/admin-new-vendor-request.php';
			$this->template_plain = 'emails/plain/admin-new-vendor-request.php';
			$this->subject        = __( '{site_title}: New Vendor Application', 'multi-vendor-marketplace' );
			$this->heading        = __( 'New Vendor Application Submitted on your Site', 'multi-vendor-marketplace' );

			// Triggers for this email.
			add_action( 'mvr_after_register_vendor_notification', array( $this, 'trigger' ), 10, 1 );
			add_action( 'mvr_after_become_vendor_notification', array( $this, 'trigger' ), 10, 1 );

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

			if ( mvr_is_vendor( $vendor_obj ) ) {
				$this->vendor_obj = $vendor_obj;

				$this->maybe_trigger();
			}
		}
	}

}

return new MVR_Email_Admin_New_Vendor_Request();
