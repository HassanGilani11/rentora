<?php
/**
 * Vendor - Revenue Credited
 *
 * @package Multi-Vendor for WooCommerce\emails\
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MVR_Email_Vendor_Revenue_Credited', false ) ) {

	/**
	 * New Vendor Register.
	 *
	 * New Vendor Register emails are sent to admin when a vendor register submitted by a vendor.
	 *
	 * @class MVR_Email_Vendor_Revenue_Credited
	 * @package class
	 * @extends MVR_Abstract_Email
	 */
	class MVR_Email_Vendor_Revenue_Credited extends MVR_Abstract_Email {

		/**
		 * Gets the vendor object.
		 *
		 * @var MVR_Vendor
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
			$this->id             = 'mvr_email_vendor_revenue_credited';
			$this->title          = __( 'Vendor - Revenue Credited', 'multi-vendor-marketplace' );
			$this->description    = __( 'New Revenue Credited.', 'multi-vendor-marketplace' );
			$this->template_html  = 'emails/vendor-revenue-credited.php';
			$this->template_plain = 'emails/plain/vendor-revenue-credited.php';
			$this->subject        = __( '{site_title}: Revenue Credited', 'multi-vendor-marketplace' );
			$this->heading        = __( 'New Revenue Credited', 'multi-vendor-marketplace' );

			// Triggers for this email.
			add_action( 'mvr_after_transaction_complete_notification', array( $this, 'trigger' ), 10, 2 );

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
			$content_args                    = parent::get_content_args();
			$content_args['transaction_obj'] = $this->transaction_obj;
			$content_args['vendor_obj']      = $this->vendor_obj;

			return $content_args;
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @since 1.0.0
		 * @param MVR_Transaction $transaction_obj Transaction object.
		 * @param MVR_Vendor      $vendor_obj Vendor Object.
		 */
		public function trigger( $transaction_obj, $vendor_obj ) {
			$vendor_obj      = mvr_get_vendor( $vendor_obj );
			$transaction_obj = mvr_get_transaction( $transaction_obj );

			if ( mvr_is_vendor( $vendor_obj ) && mvr_is_transaction( $transaction_obj ) ) {
				$this->transaction_obj = $transaction_obj;
				$this->vendor_obj      = $vendor_obj;
				$this->recipient       = $vendor_obj->get_email();

				$this->maybe_trigger();
			}
		}
	}

}

return new MVR_Email_Vendor_Revenue_Credited();
