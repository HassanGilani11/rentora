<?php
/**
 * Vendor - Registration Rejected.
 *
 * @package Multi-Vendor for WooCommerce\emails\
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MVR_Email_Vendor_Rejected', false ) ) {

	/**
	 * Vendor Registration Rejected.
	 *
	 * @class MVR_Email_Vendor_Rejected
	 * @package class
	 * @extends MVR_Abstract_Email
	 */
	class MVR_Email_Vendor_Rejected extends MVR_Abstract_Email {

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
			$this->id             = 'mvr_email_vendor_rejected';
			$this->title          = __( 'Vendor Application Rejected', 'multi-vendor-marketplace' );
			$this->description    = __( 'Vendor Application Rejected emails are sent to vendor when an admin reject vendor request.', 'multi-vendor-marketplace' );
			$this->template_html  = 'emails/vendor-rejected.php';
			$this->template_plain = 'emails/plain/vendor-rejected.php';
			$this->subject        = __( '{site_title}: Vendor Application Rejected', 'multi-vendor-marketplace' );
			$this->heading        = __( 'Your Vendor Application has been Rejected', 'multi-vendor-marketplace' );

			// Triggers for this email.
			add_action( 'mvr_vendor_status_reject_notification', array( $this, 'trigger' ) );

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
			$vendor_obj = mvr_get_vendor( $vendor_obj->get_id() );

			if ( mvr_is_vendor( $vendor_obj ) ) {
				$this->vendor_obj = $vendor_obj;
				$this->recipient  = $vendor_obj->get_email();

				$this->maybe_trigger();
			}
		}
	}

}

return new MVR_Email_Vendor_Rejected();
