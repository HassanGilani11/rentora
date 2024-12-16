<?php
/**
 * Vendor - Product Approved.
 *
 * @package Multi-Vendor for WooCommerce\emails\
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MVR_Email_Vendor_Product_Approved', false ) ) {

	/**
	 * Vendor Product Approved.
	 *
	 * @class MVR_Email_Vendor_Product_Approved
	 * @package class
	 * @extends MVR_Abstract_Email
	 */
	class MVR_Email_Vendor_Product_Approved extends MVR_Abstract_Email {

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
			$this->id             = 'mvr_email_vendor_product_approved';
			$this->title          = __( 'Vendor Product Approved', 'multi-vendor-marketplace' );
			$this->description    = __( 'Vendor Product Approved emails are sent to vendor when an admin approve vendor product request.', 'multi-vendor-marketplace' );
			$this->template_html  = 'emails/vendor-product-approved.php';
			$this->template_plain = 'emails/plain/vendor-product-approved.php';
			$this->subject        = __( '{site_title}: New Product Submission Approved', 'multi-vendor-marketplace' );
			$this->heading        = __( 'Your New Product Submission has been Approved', 'multi-vendor-marketplace' );

			// Triggers for this email.
			add_action( 'mvr_vendor_new_product_submitted_notification', array( $this, 'trigger' ), 10, 2 );
			add_action( 'mvr_admin_product_save_notification', array( $this, 'trigger' ), 10, 2 );

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
			$content_args['vendor_obj']  = $this->vendor_obj;
			$content_args['product_obj'] = $this->product_obj;

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

			if ( mvr_is_vendor( $vendor_obj ) && 'publish' === $product_obj->get_status() && ! $product_obj->get_meta( 'mvr_approved_email_sent' ) ) {
				$this->vendor_obj  = $vendor_obj;
				$this->product_obj = $product_obj;
				$this->recipient   = $vendor_obj->get_email();

				$product_obj->add_meta_data( 'mvr_approved_email_sent', true );
				$product_obj->save();

				$this->maybe_trigger();
			}
		}
	}

}

return new MVR_Email_Vendor_Product_Approved();
