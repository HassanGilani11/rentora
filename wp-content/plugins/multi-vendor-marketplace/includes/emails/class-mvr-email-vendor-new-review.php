<?php
/**
 * Vendor  - New Review.
 *
 * @package Multi-Vendor for WooCommerce\emails\
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MVR_Email_Vendor_New_Review', false ) ) {

	/**
	 * New Review.
	 *
	 * @class MVR_Email_Vendor_New_Review
	 * @package class
	 * @extends MVR_Abstract_Email
	 */
	class MVR_Email_Vendor_New_Review extends MVR_Abstract_Email {

		/**
		 * Gets the review object.
		 *
		 * @var MVR_Review
		 */
		public $review_obj;

		/**
		 * Gets the Comment ID.
		 *
		 * @var Integer
		 */
		public $comment_id;

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
			$this->id             = 'mvr_email_vendor_new_review';
			$this->title          = __( 'New Review', 'multi-vendor-marketplace' );
			$this->description    = __( 'New Review emails are sent to vendor when a review submitted by a customer.', 'multi-vendor-marketplace' );
			$this->template_html  = 'emails/vendor-new-review.php';
			$this->template_plain = 'emails/plain/vendor-new-review.php';
			$this->subject        = __( '{site_title}: New Review Received', 'multi-vendor-marketplace' );
			$this->heading        = __( 'A New Review has been Received', 'multi-vendor-marketplace' );

			// Triggers for this email.
			add_action( 'mvr_after_store_review_submitted_notification', array( $this, 'trigger' ), 10, 2 );

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
			$content_args['comment_id'] = $this->comment_id;
			$content_args['vendor_obj'] = $this->vendor_obj;

			return $content_args;
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @since 1.0.0
		 * @param MVR_Review $comment_id Comment ID.
		 * @param MVR_Vendor $vendor_obj Vendor Object.
		 */
		public function trigger( $comment_id, $vendor_obj ) {
			$vendor_obj = mvr_get_vendor( $vendor_obj );

			if ( mvr_is_vendor( $vendor_obj ) ) {
				$this->comment_id = $comment_id;
				$this->vendor_obj = $vendor_obj;
				$this->recipient  = $vendor_obj->get_email();

				$this->maybe_trigger();
			}
		}
	}

}

return new MVR_Email_Vendor_New_Review();