<?php
/**
 * Vendor - New Product Submitted.
 *
 * @package Multi-Vendor for WooCommerce\emails\
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MVR_Email_Vendor_New_Product_Submitted', false ) ) {

	/**
	 * Vendor New Product Submitted.
	 *
	 * @class MVR_Email_Vendor_New_Product_Submitted
	 * @package class
	 * @extends MVR_Abstract_Email
	 */
	class MVR_Email_Vendor_New_Product_Submitted extends MVR_Abstract_Email {

		/**
		 * Gets the product object.
		 *
		 * @var WC_Product
		 */
		public $product_obj;

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
			$this->id             = 'mvr_email_vendor_new_product_submitted';
			$this->title          = __( 'Vendor Product Submitted for Admin Approval', 'multi-vendor-marketplace' );
			$this->description    = __( 'Vendor Product Submitted for Admin Approval emails are sent to vendor when an vendor submit the product request.', 'multi-vendor-marketplace' );
			$this->template_html  = 'emails/vendor-new-product-submitted.php';
			$this->template_plain = 'emails/plain/vendor-new-product-submitted.php';
			$this->subject        = __( '{site_title}: New Product Submission Completed', 'multi-vendor-marketplace' );
			$this->heading        = __( 'Your New Product has been Submitted for Approval', 'multi-vendor-marketplace' );

			// Triggers for this email.
			add_action( 'mvr_vendor_new_product_submitted_notification', array( $this, 'trigger' ), 10, 2 );

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
			$content_args                = parent::get_content_args();
			$content_args['product_obj'] = $this->product_obj;
			$content_args['vendor_obj']  = $this->vendor_obj;

			return $content_args;
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @since 1.0.0
		 * @param WC_Product $product_obj Product Object.
		 * @param MVR_Vendor $vendor_obj Vendor Object.
		 */
		public function trigger( $product_obj, $vendor_obj ) {
			$vendor_obj = mvr_get_vendor( $vendor_obj );

			if ( mvr_is_vendor( $vendor_obj ) ) {
				$this->vendor_obj  = $vendor_obj;
				$this->product_obj = $product_obj;
				$this->recipient   = $vendor_obj->get_email();

				$this->maybe_trigger();
			}
		}
	}

}

return new MVR_Email_Vendor_New_Product_Submitted();
