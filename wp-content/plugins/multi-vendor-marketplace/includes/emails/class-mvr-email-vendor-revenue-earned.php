<?php
/**
 * Vendor - Revenue Earned
 *
 * @package Multi-Vendor for WooCommerce\emails\
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MVR_Email_Vendor_Revenue_Earned', false ) ) {

	/**
	 * Vendor Revenue Earned.
	 *
	 * @class MVR_Email_Vendor_Revenue_Earned
	 * @package class
	 * @extends MVR_Abstract_Email
	 */
	class MVR_Email_Vendor_Revenue_Earned extends MVR_Abstract_Email {
		/**
		 * Gets the order object.
		 *
		 * @var WC_Order
		 */
		public $order_obj;

		/**
		 * Gets the transaction object.
		 *
		 * @var MVR_Transaction
		 */
		public $transaction_obj;

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
			$this->id             = 'mvr_email_vendor_revenue_earned';
			$this->title          = __( 'Vendor - Revenue Earned', 'multi-vendor-marketplace' );
			$this->description    = __( 'Vendor Revenue Earned emails are sent to vendor when an order placed by a customer.', 'multi-vendor-marketplace' );
			$this->template_html  = 'emails/vendor-revenue-earned.php';
			$this->template_plain = 'emails/plain/vendor-revenue-earned.php';
			$this->subject        = __( '{site_title}: Revenue Earned', 'multi-vendor-marketplace' );
			$this->heading        = __( 'New Revenue Earned', 'multi-vendor-marketplace' );

			// Triggers for this email.
			add_action( 'mvr_new_transaction_notification', array( $this, 'trigger' ), 10, 2 );

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
			$content_args                    = parent::get_content_args();
			$content_args['vendor_obj']      = $this->vendor_obj;
			$content_args['transaction_obj'] = $this->transaction_obj;
			$content_args['order_obj']       = $this->order_obj;

			return $content_args;
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @since 1.0.0
		 * @param Integer         $transaction_id Transaction ID.
		 * @param MVR_Transaction $transaction_obj Transaction Object.
		 */
		public function trigger( $transaction_id, $transaction_obj ) {
			$transaction_obj = mvr_get_transaction( $transaction_id );

			if ( mvr_is_transaction( $transaction_obj ) ) {
				if ( 'order' === $transaction_obj->get_source_from() ) {
					$order_id  = $transaction_obj->get_source_id();
					$order_obj = wc_get_order( $order_id );

					if ( is_a( $order_obj, 'WC_Order' ) ) {
						$vendor_id  = $transaction_obj->get_vendor_id();
						$vendor_obj = mvr_get_vendor( $vendor_id );

						if ( mvr_is_vendor( $vendor_obj ) ) {
							$this->vendor_obj      = $vendor_obj;
							$this->transaction_obj = $transaction_obj;
							$this->order_obj       = $order_obj;
							$this->recipient       = $vendor_obj->get_email();

							$this->maybe_trigger();
						}
					}
				}
			}
		}
	}

}

return new MVR_Email_Vendor_Revenue_Earned();
