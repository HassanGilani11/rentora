<?php
/**
 * Staff  - Deleted
 *
 * @package Multi-Vendor for WooCommerce\emails\
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MVR_Email_Staff_Deleted', false ) ) {

	/**
	 * Staff Deleted
	 *
	 * @class MVR_Email_Staff_Deleted
	 * @package class
	 * @extends MVR_Abstract_Email
	 */
	class MVR_Email_Staff_Deleted extends MVR_Abstract_Email {

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
			$this->id             = 'mvr_email_staff_deleted';
			$this->title          = __( 'Staff Deleted', 'multi-vendor-marketplace' );
			$this->description    = __( 'Staff Deleted emails are sent to Staff when an admin deleted Staff.', 'multi-vendor-marketplace' );
			$this->template_html  = 'emails/staff-deleted.php';
			$this->template_plain = 'emails/plain/staff-deleted.php';
			$this->subject        = __( '{site_title}: Staff Deleted', 'multi-vendor-marketplace' );
			$this->heading        = __( 'A Staff has been Deleted', 'multi-vendor-marketplace' );

			// Triggers for this email.
			add_action( 'mvr_before_delete_staff_notification', array( $this, 'trigger' ) );

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
		 * @param Integer $staff_id Staff ID.
		 */
		public function trigger( $staff_id ) {
			$staff_obj = mvr_get_staff( $staff_id );

			if ( mvr_is_staff( $staff_obj ) ) {
				$vendor_obj = $staff_obj->get_vendor();

				if ( mvr_is_vendor( $vendor_obj ) ) {
					$this->vendor_obj = $vendor_obj;
					$this->recipient  = $staff_obj->get_email();

					$this->maybe_trigger();
				}
			}
		}
	}

}

return new MVR_Email_Staff_Deleted();
