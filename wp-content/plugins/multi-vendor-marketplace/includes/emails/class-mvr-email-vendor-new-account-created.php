<?php
/**
 * New Vendor Created.
 *
 * @package Multi-Vendor for WooCommerce\emails\
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MVR_Email_Vendor_New_Account_Created', false ) ) {

	/**
	 * New Vendor Register.
	 *
	 * @class MVR_Email_Vendor_New_Account_Created
	 * @package class
	 * @extends MVR_Abstract_Email
	 */
	class MVR_Email_Vendor_New_Account_Created extends MVR_Abstract_Email {

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
			$this->id             = 'mvr_email_vendor_new_account_created';
			$this->title          = __( 'Vendor Account Created - Without Admin approval', 'multi-vendor-marketplace' );
			$this->description    = __( 'Vendor Account Created emails are sent to vendor when a customer register as vendor.', 'multi-vendor-marketplace' );
			$this->template_html  = 'emails/vendor-new-account-created.php';
			$this->template_plain = 'emails/plain/vendor-new-account-created.php';
			$this->subject        = __( '{site_title}: Vendor Application Approved', 'multi-vendor-marketplace' );
			$this->heading        = __( 'Your Vendor Application has been Approved', 'multi-vendor-marketplace' );

			// Triggers for this email.
			add_action( 'mvr_after_register_vendor_notification', array( $this, 'trigger' ) );
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

return new MVR_Email_Vendor_New_Account_Created();
