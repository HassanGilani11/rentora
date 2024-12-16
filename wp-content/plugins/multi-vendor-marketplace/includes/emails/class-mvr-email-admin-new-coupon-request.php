<?php
/**
 * Admin  - New Coupon Request.
 *
 * @package Multi-Vendor for WooCommerce\emails\
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MVR_Email_Admin_New_Coupon_Request', false ) ) {

	/**
	 * New Coupon Request.
	 *
	 * New coupon request emails are sent to admin when a coupon request submitted by a vendor.
	 *
	 * @class MVR_Email_Admin_New_Coupon_Request
	 * @package class
	 * @extends MVR_Abstract_Email
	 */
	class MVR_Email_Admin_New_Coupon_Request extends MVR_Abstract_Email {

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
			$this->id             = 'mvr_email_admin_new_coupon_request';
			$this->title          = __( 'New Coupon Request', 'multi-vendor-marketplace' );
			$this->description    = __( 'New Coupon Request emails are sent to Admin when a coupon request submitted by a vendor.', 'multi-vendor-marketplace' );
			$this->template_html  = 'emails/admin-new-coupon-request.php';
			$this->template_plain = 'emails/plain/admin-new-coupon-request.php';
			$this->subject        = __( '{site_title}: New Coupon - Approval Needed', 'multi-vendor-marketplace' );
			$this->heading        = __( 'A New Coupon Submitted for Approval', 'multi-vendor-marketplace' );

			// Triggers for this email.
			add_action( 'mvr_vendor_new_coupon_submitted_notification', array( $this, 'trigger' ), 10, 2 );

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
			$content_args['coupon_obj']    = $this->coupon_obj;
			$content_args['vendor_obj']    = $this->vendor_obj;
			$content_args['sent_to_admin'] = true;

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
				$this->coupon_obj = $coupon_obj;
				$this->vendor_obj = $vendor_obj;

				$this->maybe_trigger();
			}
		}
	}

}

return new MVR_Email_Admin_New_Coupon_Request();
