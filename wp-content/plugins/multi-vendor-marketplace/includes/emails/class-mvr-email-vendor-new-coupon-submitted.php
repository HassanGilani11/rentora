<?php
/**
 * Vendor - New Coupon Submitted.
 *
 * @package Multi-Vendor for WooCommerce\emails\
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MVR_Email_Vendor_New_Coupon_Submitted', false ) ) {

	/**
	 * Vendor New Coupon Submitted.
	 *
	 * @class MVR_Email_Vendor_New_Coupon_Submitted
	 * @package class
	 * @extends MVR_Abstract_Email
	 */
	class MVR_Email_Vendor_New_Coupon_Submitted extends MVR_Abstract_Email {

		/**
		 * Gets the Coupon object.
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
			$this->id             = 'mvr_email_vendor_new_coupon_submitted';
			$this->title          = __( 'Vendor - Coupon Submitted Successfully', 'multi-vendor-marketplace' );
			$this->description    = __( 'Vendor coupon Submitted for Admin Approval emails are sent to vendor when an vendor submit the coupon request.', 'multi-vendor-marketplace' );
			$this->template_html  = 'emails/vendor-new-coupon-submitted.php';
			$this->template_plain = 'emails/plain/vendor-new-coupon-submitted.php';
			$this->subject        = __( '{site_title}: New Coupon Submission Completed', 'multi-vendor-marketplace' );
			$this->heading        = __( 'Your New Coupon has been Submitted for Approval', 'multi-vendor-marketplace' );

			// Triggers for this email.
			add_action( 'mvr_vendor_new_coupon_submitted_notification', array( $this, 'trigger' ), 10, 2 );

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
			$content_args['coupon_obj'] = $this->coupon_obj;
			$content_args['vendor_obj'] = $this->vendor_obj;

			return $content_args;
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @since 1.0.0
		 * @param WC_Coupon  $coupon_obj Coupon Object.
		 * @param MVR_Vendor $vendor_obj Vendor Object.
		 */
		public function trigger( $coupon_obj, $vendor_obj ) {
			$vendor_obj = mvr_get_vendor( $vendor_obj );

			if ( mvr_is_vendor( $vendor_obj ) ) {
				$this->vendor_obj = $vendor_obj;
				$this->coupon_obj = $coupon_obj;
				$this->recipient  = $vendor_obj->get_email();

				$this->maybe_trigger();
			}
		}
	}

}

return new MVR_Email_Vendor_New_Coupon_Submitted();
