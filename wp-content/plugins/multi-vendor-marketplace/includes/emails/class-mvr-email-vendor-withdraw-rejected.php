<?php
/**
 * Withdraw - Rejected
 *
 * @package Multi-Vendor for WooCommerce\emails\
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MVR_Email_Vendor_Withdraw_Rejected', false ) ) {

	/**
	 * Vendor Registration Rejected.
	 *
	 * @class MVR_Email_Vendor_Withdraw_Rejected
	 * @package class
	 * @extends MVR_Abstract_Email
	 */
	class MVR_Email_Vendor_Withdraw_Rejected extends MVR_Abstract_Email {

		/**
		 * Gets the Withdraw object.
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
			$this->id             = 'mvr_email_vendor_withdraw_rejected';
			$this->title          = __( 'Withdraw Request Rejected', 'multi-vendor-marketplace' );
			$this->description    = __( 'Withdraw Request Rejected emails are sent to vendor when an admin reject a withdraw request.', 'multi-vendor-marketplace' );
			$this->template_html  = 'emails/vendor-withdraw-rejected.php';
			$this->template_plain = 'emails/plain/vendor-withdraw-rejected.php';
			$this->subject        = __( '{site_title}: New Withdraw Request Rejected', 'multi-vendor-marketplace' );
			$this->heading        = __( 'Your Withdraw Request has been Rejected', 'multi-vendor-marketplace' );

			// Triggers for this email.
			add_action( 'mvr_after_rejected_withdraw_request_notification', array( $this, 'trigger' ), 10, 2 );

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
			$content_args                 = parent::get_content_args();
			$content_args['vendor_obj']   = $this->vendor_obj;
			$content_args['withdraw_obj'] = $this->withdraw_obj;

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
			$vendor_obj   = mvr_get_vendor( $vendor_obj );
			$withdraw_obj = mvr_get_withdraw( $withdraw_obj );

			if ( mvr_is_vendor( $vendor_obj ) && mvr_is_withdraw( $withdraw_obj ) ) {
				$this->vendor_obj   = $vendor_obj;
				$this->withdraw_obj = $withdraw_obj;
				$this->recipient    = $vendor_obj->get_email();

				$this->maybe_trigger();
			}
		}
	}

}

return new MVR_Email_Vendor_Withdraw_Rejected();