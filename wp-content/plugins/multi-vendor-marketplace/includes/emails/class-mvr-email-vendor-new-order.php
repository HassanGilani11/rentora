<?php
/**
 * Vendor  - New Order for Vendor.
 *
 * @package Multi-Vendor for WooCommerce\emails\
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MVR_Email_Vendor_New_Order', false ) ) {

	/**
	 * New Order.
	 *
	 * @class MVR_Email_Vendor_New_Order
	 * @package class
	 * @extends MVR_Abstract_Email
	 */
	class MVR_Email_Vendor_New_Order extends MVR_Abstract_Email {

		/**
		 * Gets the Multi Vendor Order object.
		 *
		 * @var MVR_Order
		 */
		public $mvr_order_obj;

		/**
		 * Gets the order object.
		 *
		 * @var WC_Order
		 */
		public $order_obj;

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
			$this->id             = 'mvr_email_vendor_new_order';
			$this->title          = __( 'Vendor - New Order Received', 'multi-vendor-marketplace' );
			$this->description    = __( 'New Order emails are sent to vendor when a vendor received order submitted by a customer.', 'multi-vendor-marketplace' );
			$this->template_html  = 'emails/vendor-new-order.php';
			$this->template_plain = 'emails/plain/vendor-new-order.php';
			$this->subject        = __( '{site_title}: New Order Received', 'multi-vendor-marketplace' );
			$this->heading        = __( 'A New Order has been received', 'multi-vendor-marketplace' );

			// Triggers for this email.
			add_action( 'mvr_create_new_order_notification', array( $this, 'trigger' ), 10, 2 );

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
			$content_args['mvr_order_obj'] = $this->mvr_order_obj;
			$content_args['order_obj']     = $this->order_obj;
			$content_args['vendor_obj']    = $this->vendor_obj;

			return $content_args;
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @since 1.0.0
		 * @param MVR_Order  $mvr_order_id Multi Vendor Order ID.
		 * @param MVR_Vendor $vendor_obj Vendor Object.
		 */
		public function trigger( $mvr_order_id, $vendor_obj ) {
			$vendor_obj = mvr_get_vendor( $vendor_obj );

			if ( mvr_is_vendor( $vendor_obj ) ) {
				$mvr_order_obj = mvr_get_order( $mvr_order_id );

				if ( mvr_is_order( $mvr_order_obj ) ) {
					$order_obj = wc_get_order( $mvr_order_obj->get_order_id() );

					if ( is_a( $order_obj, 'WC_Order' ) ) {
						$this->mvr_order_obj = $mvr_order_obj;
						$this->order_obj     = $order_obj;
						$this->vendor_obj    = $vendor_obj;
						$this->recipient     = $vendor_obj->get_email();

						$this->maybe_trigger();
					}
				}
			}
		}
	}

}

return new MVR_Email_Vendor_New_Order();
