<?php
/**
 * Vendor  - New Enquiry.
 *
 * @package Multi-Vendor for WooCommerce\emails\
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MVR_Email_Vendor_New_Enquiry', false ) ) {

	/**
	 * New Vendor Enquiry.
	 *
	 * @class MVR_Email_Vendor_New_Enquiry
	 * @package class
	 * @extends MVR_Abstract_Email
	 */
	class MVR_Email_Vendor_New_Enquiry extends MVR_Abstract_Email {

		/**
		 * Gets the Enquiry object.
		 *
		 * @var MVR_Enquiry
		 */
		public $enquiry_obj;

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
			$this->id             = 'mvr_email_vendor_new_enquiry';
			$this->title          = __( 'New Enquiry Received', 'multi-vendor-marketplace' );
			$this->description    = __( 'New Vendor Enquiry emails are sent to vendor when a customer submit enquiry.', 'multi-vendor-marketplace' );
			$this->template_html  = 'emails/vendor-new-enquiry.php';
			$this->template_plain = 'emails/plain/vendor-new-enquiry.php';
			$this->subject        = __( '{site_title}: New Enquiry Received', 'multi-vendor-marketplace' );
			$this->heading        = __( 'A New Enquiry has been Received', 'multi-vendor-marketplace' );

			// Triggers for this email.
			add_action( 'mvr_after_store_enquiry_submitted_notification', array( $this, 'trigger' ), 10, 2 );

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
			$content_args                = parent::get_content_args();
			$content_args['vendor_obj']  = $this->vendor_obj;
			$content_args['enquiry_obj'] = $this->enquiry_obj;

			return $content_args;
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @since 1.0.0
		 * @param MVR_Enquiry $enquiry_obj Enquiry object.
		 * @param MVR_Vendor  $vendor_obj Vendor Object.
		 */
		public function trigger( $enquiry_obj, $vendor_obj ) {
			$vendor_obj = mvr_get_vendor( $vendor_obj );

			if ( mvr_is_vendor( $vendor_obj ) ) {
				$this->enquiry_obj = $enquiry_obj;
				$this->vendor_obj  = $vendor_obj;
				$this->recipient   = $vendor_obj->get_email();

				$this->maybe_trigger();
			}
		}
	}

}

return new MVR_Email_Vendor_New_Enquiry();
