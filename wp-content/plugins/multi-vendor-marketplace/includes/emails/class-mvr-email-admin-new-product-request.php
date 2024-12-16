<?php
/**
 * Admin  - New Product Request.
 *
 * @package Multi-Vendor for WooCommerce\emails\
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MVR_Email_Admin_New_Product_Request', false ) ) {

	/**
	 * New Product Request.
	 *
	 * New product request emails are sent to admin when a product request submitted by a vendor.
	 *
	 * @class MVR_Email_Admin_New_Product_Request
	 * @package class
	 * @extends MVR_Abstract_Email
	 */
	class MVR_Email_Admin_New_Product_Request extends MVR_Abstract_Email {

		/**
		 * Gets the Product object.
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
			$this->id             = 'mvr_email_admin_new_product_request';
			$this->title          = __( 'New Product Request', 'multi-vendor-marketplace' );
			$this->description    = __( 'New Product Request emails are sent to Admin when a product request submitted by a vendor.', 'multi-vendor-marketplace' );
			$this->template_html  = 'emails/admin-new-product-request.php';
			$this->template_plain = 'emails/plain/admin-new-product-request.php';
			$this->subject        = __( '{site_title}: New Product - Approval Needed', 'multi-vendor-marketplace' );
			$this->heading        = __( 'A New Product Submitted for Approval', 'multi-vendor-marketplace' );

			// Triggers for this email.
			add_action( 'mvr_vendor_new_product_submitted_notification', array( $this, 'trigger' ), 10, 2 );

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
			$content_args['product_obj']   = $this->product_obj;
			$content_args['vendor_obj']    = $this->vendor_obj;
			$content_args['sent_to_admin'] = true;

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
				$this->product_obj = $product_obj;
				$this->vendor_obj  = $vendor_obj;

				$this->maybe_trigger();
			}
		}
	}

}

return new MVR_Email_Admin_New_Product_Request();
