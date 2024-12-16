<?php
/**
 * Vendor - Coupon Approved.
 *
 * @package Multi-Vendor for WooCommerce\emails\
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MVR_Email_Vendor_Coupon_Approved', false ) ) {

	/**
	 * Vendor Coupon Approved.
	 *
	 * @class MVR_Email_Vendor_Coupon_Approved
	 * @package class
	 * @extends MVR_Abstract_Email
	 */
	class MVR_Email_Vendor_Coupon_Approved extends MVR_Abstract_Email {

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
			$this->id             = 'mvr_email_vendor_coupon_approved';
			$this->title          = __( 'Vendor - Coupon Approved', 'multi-vendor-marketplace' );
			$this->description    = __( 'Coupon Approved emails are sent to vendor when an Admin approve the coupon.', 'multi-vendor-marketplace' );
			$this->template_html  = 'emails/vendor-coupon-approved.php';
			$this->template_plain = 'emails/plain/vendor-coupon-approved.php';
			$this->subject        = __( '{site_title}: New Coupon Submission Approved', 'multi-vendor-marketplace' );
			$this->heading        = __( 'Your New Coupon Submission has been Approved', 'multi-vendor-marketplace' );

			// Triggers for this email.
			add_action( 'mvr_vendor_new_coupon_submitted_notification', array( $this, 'trigger' ), 10, 2 );
			add_action( 'mvr_admin_coupon_save_notification', array( $this, 'trigger' ), 10, 2 );

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

			if ( mvr_is_vendor( $vendor_obj ) && 'publish' === $coupon_obj->get_status() && ! $coupon_obj->get_meta( 'mvr_approved_email_sent' ) ) {
				$this->vendor_obj = $vendor_obj;
				$this->coupon_obj = $coupon_obj;
				$this->recipient  = $vendor_obj->get_email();

				$coupon_obj->add_meta_data( 'mvr_approved_email_sent', true );
				$coupon_obj->save();

				$this->maybe_trigger();
			}
		}
	}

}

return new MVR_Email_Vendor_Coupon_Approved();
