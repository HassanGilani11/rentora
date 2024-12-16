<?php
/**
 * Vendor - Staff Assigned
 *
 * @package Multi-Vendor for WooCommerce\emails\
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MVR_Email_Vendor_Staff_Assigned', false ) ) {

	/**
	 * Vendor Staff Assigned
	 *
	 * @class MVR_Email_Vendor_Staff_Assigned
	 * @package class
	 * @extends MVR_Abstract_Email
	 */
	class MVR_Email_Vendor_Staff_Assigned extends MVR_Abstract_Email {

		/**
		 * Gets the Staff object.
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
			$this->id             = 'mvr_email_vendor_staff_assigned';
			$this->title          = __( 'Vendor - New Staff Assigned for Vendor', 'multi-vendor-marketplace' );
			$this->description    = __( 'Staff Assigned emails are sent to Staff when a admin assign Staff to vendor.', 'multi-vendor-marketplace' );
			$this->template_html  = 'emails/vendor-staff-assigned.php';
			$this->template_plain = 'emails/plain/vendor-staff-assigned.php';
			$this->subject        = __( '{site_title}: New Staff Assigned for Vendor', 'multi-vendor-marketplace' );
			$this->heading        = __( 'A New Staff has been assigned for Vendor', 'multi-vendor-marketplace' );

			// Triggers for this email.
			add_action( 'mvr_admin_staff_before_save_notification', array( $this, 'trigger' ), 10, 2 );
			add_action( 'mvr_after_assign_staff_notification', array( $this, 'trigger' ) );

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
			$content_args['staff_obj']  = $this->staff_obj;
			$content_args['vendor_obj'] = $this->vendor_obj;

			return $content_args;
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @since 1.0.0
		 * @param MVR_Staff $staff_obj Staff Object.
		 * @param Array     $staff_data Staff data.
		 */
		public function trigger( $staff_obj, $staff_data = array() ) {
			if ( mvr_check_is_array( $staff_data ) ) {
				if ( ! isset( $staff_data['vendor_id'] ) || empty( $staff_data['vendor_id'] ) ) {
					return;
				}

				if ( (int) $staff_obj->get_vendor_id() === (int) $staff_data['vendor_id'] ) {
					return;
				}

				$vendor_id = $staff_data['vendor_id'];
			} else {
				$vendor_id = $staff_obj->get_vendor_id();
			}

			$staff_obj  = mvr_get_staff( $staff_obj );
			$vendor_obj = mvr_get_vendor( $vendor_id );

			if ( ! mvr_is_vendor( $vendor_obj ) ) {
				return;
			}

			$this->staff_obj  = $staff_obj;
			$this->vendor_obj = $vendor_obj;
			$this->recipient  = $vendor_obj->get_email();

			$this->maybe_trigger();
		}
	}

}

return new MVR_Email_Vendor_Staff_Assigned();
