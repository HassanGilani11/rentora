<?php
/**
 * Vendor - Staff Deleted
 *
 * @package Multi-Vendor for WooCommerce\emails\
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MVR_Email_Vendor_Staff_Deleted', false ) ) {

	/**
	 * Vendor Staff Deleted
	 *
	 * @class MVR_Email_Vendor_Staff_Deleted
	 * @package class
	 * @extends MVR_Abstract_Email
	 */
	class MVR_Email_Vendor_Staff_Deleted extends MVR_Abstract_Email {

		/**
		 * Staff Object.
		 *
		 * @var MVR_Staff
		 */
		public $staff_obj;

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
			$this->id             = 'mvr_email_vendor_staff_deleted';
			$this->title          = __( 'Vendor - Staff Deleted', 'multi-vendor-marketplace' );
			$this->description    = __( 'Staff Deleted emails are sent to vendor when an admin delete the Staff.', 'multi-vendor-marketplace' );
			$this->template_html  = 'emails/vendor-staff-deleted.php';
			$this->template_plain = 'emails/plain/vendor-staff-deleted.php';
			$this->subject        = __( '{site_title}: Staff Deleted', 'multi-vendor-marketplace' );
			$this->heading        = __( 'Your Staff has been Deleted', 'multi-vendor-marketplace' );

			// Triggers for this email.
			add_action( 'mvr_before_delete_staff_notification', array( $this, 'trigger' ) );
			add_action( 'mvr_before_remove_staff_notification', array( $this, 'trigger' ) );

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
			$content_args['staff_obj']     = $this->staff_obj;
			$content_args['vendor_obj']    = $this->vendor_obj;
			$content_args['sent_to_admin'] = true;

			return $content_args;
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @since 1.0.0
		 * @param MVR_Staff $staff_id Staff ID.
		 */
		public function trigger( $staff_id ) {
			$staff_obj = mvr_get_staff( $staff_id );

			if ( mvr_is_staff( $staff_obj ) ) {
				$vendor_obj = $staff_obj->get_vendor();

				if ( mvr_is_vendor( $vendor_obj ) ) {
					$this->vendor_obj = $vendor_obj;
					$this->staff_obj  = $staff_obj;
					$this->recipient  = $vendor_obj->get_email();

					$this->maybe_trigger();
				}
			}
		}
	}
}

return new MVR_Email_Vendor_Staff_Deleted();
