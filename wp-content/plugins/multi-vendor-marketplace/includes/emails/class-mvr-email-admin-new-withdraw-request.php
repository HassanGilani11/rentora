<?php
/**
 * Admin  - New Withdraw Request.
 *
 * @package Multi-Vendor for WooCommerce\emails\
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MVR_Email_Admin_New_Withdraw_Request', false ) ) {

	/**
	 * New Withdraw Request.
	 *
	 * New Withdraw request emails are sent to admin when a Withdraw request submitted by a vendor.
	 *
	 * @class MVR_Email_Admin_New_Withdraw_Request
	 * @package class
	 * @extends MVR_Abstract_Email
	 */
	class MVR_Email_Admin_New_Withdraw_Request extends MVR_Abstract_Email {

		/**
		 * Gets the withdraw object.
		 *
		 * @var MVR_Withdraw
		 */
		public $withdraw_obj;

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
			$this->id             = 'mvr_email_admin_new_withdraw_request';
			$this->title          = __( 'New Withdraw Request', 'multi-vendor-marketplace' );
			$this->description    = __( 'New Withdraw Request emails are sent to Admin when a Withdraw request submitted by a vendor.', 'multi-vendor-marketplace' );
			$this->template_html  = 'emails/admin-new-withdraw-request.php';
			$this->template_plain = 'emails/plain/admin-new-withdraw-request.php';
			$this->subject        = __( '{site_title}: New Withdrawal Request', 'multi-vendor-marketplace' );
			$this->heading        = __( 'A New Withdrawal Request Submitted', 'multi-vendor-marketplace' );

			// Triggers for this email.
			add_action( 'mvr_vendor_new_withdraw_request_notification', array( $this, 'trigger' ), 10, 2 );

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
			$content_args['withdraw_obj']  = $this->withdraw_obj;
			$content_args['vendor_obj']    = $this->vendor_obj;
			$content_args['sent_to_admin'] = true;

			return $content_args;
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @since 1.0.0
		 * @param MVR_Withdraw $withdraw_obj Withdraw Object.
		 * @param MVR_Vendor   $vendor_obj Vendor Object.
		 */
		public function trigger( $withdraw_obj, $vendor_obj ) {
			$vendor_obj = mvr_get_vendor( $vendor_obj );

			if ( mvr_is_vendor( $vendor_obj ) ) {
				$this->withdraw_obj = $withdraw_obj;
				$this->vendor_obj   = $vendor_obj;

				$this->maybe_trigger();
			}
		}
	}

}

return new MVR_Email_Admin_New_Withdraw_Request();
