<?php
/**
 * Payout Batch Data.
 *
 * @package Multi-Vendor for WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Payout_Batch' ) ) {
	/**
	 * Payout Batch
	 *
	 * @class MVR_Payout_Batch
	 * @package Class
	 */
	class MVR_Payout_Batch extends WC_Data {

		/**
		 * Stores data about status changes so relevant hooks can be fired.
		 *
		 * @var bool|array
		 */
		protected $status_transition = false;

		/**
		 * Vendor Data array.
		 *
		 * @var Array
		 */
		protected $data = array(
			'batch_id'        => '',
			'batch_amount'    => array(),
			'batch_fee'       => array(),
			'batch_status'    => '',
			'name'            => '',
			'status'          => '',
			'time_created'    => '',
			'time_completed'  => '',
			'items'           => array(),
			'additional_data' => array(),
			'email_subject'   => '',
			'email_message'   => '',
			'version'         => '',
			'date_created'    => null,
			'date_modified'   => null,
		);

		/**
		 * Stores meta in cache for future reads.
		 *
		 * A group must be set to to enable caching.
		 *
		 * @var String
		 */
		protected $cache_group = 'mvr_payout_batches';

		/**
		 * Which data store to load.
		 *
		 * @var String
		 */
		protected $data_store_name = 'mvr_payout_batch';

		/**
		 * This is the name of this object type.
		 *
		 * @var String
		 */
		protected $object_type = 'mvr_payout_batch';

		/**
		 * Get the Payout Batch if ID is passed, otherwise the Payout Batch is new and empty.
		 *
		 * @since 1.0.0
		 * @param  int|object|MVR_Payout_Batch $payout_batch Payout Batch to read.
		 */
		public function __construct( $payout_batch = 0 ) {
			parent::__construct( $payout_batch );

			if ( is_numeric( $payout_batch ) && $payout_batch > 0 ) {
				$this->set_id( $payout_batch );
			} elseif ( $payout_batch instanceof self ) {
				$this->set_id( $payout_batch->get_id() );
			} elseif ( ! empty( $payout_batch->ID ) ) {
				$this->set_id( $payout_batch->ID );
			} else {
				$this->set_object_read( true );
			}

			$this->data_store = WC_Data_Store::load( $this->data_store_name );

			if ( $this->get_id() > 0 ) {
				$this->data_store->read( $this );
			}
		}

		/**
		 * Get internal type.
		 *
		 * @since 1.0.0
		 * @return String
		 */
		public function get_type() {
			return $this->object_type;
		}

		/**
		 * Get all valid statuses for this vendor
		 *
		 * @since 1.0.0
		 * @return Array Internal status keys e.g. 'mvr-active'
		 */
		public function get_valid_statuses() {
			return array_keys( mvr_get_payout_batch_statuses() );
		}

		/**
		 * Updates status of payout batch immediately.
		 *
		 * @since 1.0.0
		 * @uses MVR_Payout_Batch::set_status()
		 * @param String $new_status    Status to change the Payout Batch Status to. No internal mvr- prefix is required.
		 * @return Boolean
		 */
		public function update_status( $new_status ) {
			if ( ! $this->get_id() ) { // Vendor must exist.
				return false;
			}

			try {
				$this->set_status( $new_status );
				$this->save();
			} catch ( Exception $e ) {
				$logger = wc_get_logger();
				$logger->error(
					sprintf(
						'Error updating status for payout batch #%d',
						$this->get_id()
					),
					array(
						'payout_batch' => $this,
						'error'        => $e,
					)
				);

				return false;
			}

			return true;
		}

		/**
		 * Handle the status transition.
		 *
		 * @since 1.0.0
		 */
		protected function status_transition() {
			$status_transition = $this->status_transition;

			// Reset status transition variable.
			$this->status_transition = false;

			if ( $status_transition ) {
				try {
					/**
					 * Vendor status updated to.
					 *
					 * @since 1.0.0
					 */
					do_action( 'mvr_payout_batch_status_' . $status_transition['to'], $this );

					if ( ! empty( $status_transition['from'] ) && $status_transition['from'] !== $status_transition['to'] ) {
						/* translators: 1: old status 2: new status */
						$transition_note = sprintf( __( 'Status changed from <b>%1$s</b> to <b>%2$s</b>.', 'multi-vendor-marketplace' ), mvr_get_vendor_status_name( $status_transition['from'] ), mvr_get_vendor_status_name( $status_transition['to'] ) );

						// Note the transition occurred.
						$this->add_note( trim( $status_transition['note'] . ' ' . $transition_note ), 0, $status_transition['manual'] );

						/**
						 * Payout Batch status updated from and to.
						 *
						 * @since 1.0.0
						 */
						do_action( 'mvr_payout_batch_status_' . $status_transition['from'] . '_to_' . $status_transition['to'], $this );

						/**
						 * Payout Batch status changed.
						 *
						 * @since 1.0.0
						 */
						do_action( 'mvr_payout_batch_status_changed', $status_transition['from'], $status_transition['to'], $this );

						/**
						 * Payout Batch status updated.
						 *
						 * @since 1.0.0
						 */
						do_action( 'mvr_payout_batch_status_updated', $status_transition['to'], $status_transition['manual'], $this );
					} else {
						/* translators: %s: new status */
						$transition_note = sprintf( __( 'Payout Batch status set to <b>%s</b>.', 'multi-vendor-marketplace' ), mvr_get_vendor_status_name( $status_transition['to'] ) );

						// Note the transition occurred.
						$this->add_note( trim( $status_transition['note'] . ' ' . $transition_note ), 0, $status_transition['manual'] );
					}
				} catch ( Exception $e ) {
					$this->handle_exception( $e, sprintf( 'Status transition of payout batch #%d errored!', $this->get_id() ) );
				}
			}
		}

		/**
		 * Save data to the database.
		 *
		 * @since 1.0.0
		 * @return Integer Vendor ID.
		 */
		public function save() {
			parent::save();
			$this->status_transition();

			return $this->get_id();
		}

		/**
		 * Adds a note (comment) to the payout batch. Payout batch must exist.
		 *
		 * @param  String  $note Note to add.
		 * @param  Integer $is_customer_note Is this a note for the customer?.
		 * @param  Boolean $added_by_user Was the note added by a user?.
		 * @return Integer Comment ID.
		 */
		public function add_note( $note, $is_customer_note = 0, $added_by_user = false ) {
			if ( ! $this->get_id() ) {
				return 0;
			}

			$comment_author       = '';
			$comment_author_email = '';

			if ( is_user_logged_in() && current_user_can( 'edit_post', $this->get_id() ) && $added_by_user ) {
				$user = get_user_by( 'id', get_current_user_id() );

				if ( $user instanceof WP_User ) {
					$comment_author       = $user->display_name;
					$comment_author_email = $user->user_email;
				}
			} else {
				$comment_author        = __( 'Multi Vendor Marketplace', 'multi-vendor-marketplace' );
				$comment_author_email  = strtolower( __( 'Multi Vendor Marketplace', 'multi-vendor-marketplace' ) ) . '@';
				$comment_author_email .= isset( $_SERVER['HTTP_HOST'] ) ? str_replace( 'www.', '', sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) ) : 'noreply.com';
				$comment_author_email  = sanitize_email( $comment_author_email );
			}

			/**
			 * Get payout batch note args.
			 *
			 * @since 1.0.0
			 */
			$comment_data = apply_filters(
				'mvr_new_payout_batch_note_data',
				array(
					'comment_post_ID'      => $this->get_id(),
					'comment_author'       => $comment_author,
					'comment_author_email' => $comment_author_email,
					'comment_author_url'   => '',
					'comment_content'      => $note,
					'comment_agent'        => 'Multi Vendor Marketplace',
					'comment_type'         => 'mvr_pay_batch_note',
					'comment_parent'       => 0,
					'comment_approved'     => 1,
				),
				array(
					'payout_batch_id'  => $this->get_id(),
					'is_customer_note' => $is_customer_note,
				)
			);

			$comment_id = wp_insert_comment( $comment_data );

			if ( $is_customer_note ) {
				add_comment_meta( $comment_id, 'is_customer_note', 1 );

				/**
				 * New vendor customer note created.
				 *
				 * @since 1.0.0
				 */
				do_action(
					'mvr_vendor_new_customer_note',
					array(
						'vendor_id'     => $this->get_id(),
						'customer_note' => $comment_data['comment_content'],
					)
				);
			}

			/**
			 * Action hook fired after an vendor note is added.
			 *
			 * @param Integer $comment_id Comment ID.
			 * @param MVR_Vendor $this Vendor Object.
			 * @since 1.0.0
			 */
			do_action( 'mvr_payout_batch_note_added', $comment_id, $this );

			return $comment_id;
		}

		/**
		 * Log an error about this Payout Batch is exception is encountered.
		 *
		 * @since 1.0.0
		 * @param Exception $e Exception object.
		 * @param String    $message Message regarding exception thrown.
		 */
		protected function handle_exception( $e, $message = 'Error' ) {
			wc_get_logger()->error(
				$message,
				array(
					'vendor' => $this,
					'error'  => $e,
				)
			);

			$this->add_note( $message . ' ' . $e->getMessage() );
		}

		/*
		|--------------------------------------------------------------------------
		| Conditionals
		|--------------------------------------------------------------------------
		|
		| Checks if a condition is true or false.
		|
		 */

		/**
		 * Checks the payout batch status against a passed in status.
		 *
		 * @since 1.0.0
		 * @param Array|String $status Status to check.
		 * @return Boolean
		 */
		public function has_status( $status ) {
			/**
			 * Has Status.
			 *
			 * @since 1.0.0
			 */
			return apply_filters( 'mvr_payout_batch_has_status', ( is_array( $status ) && in_array( $this->get_status(), $status, true ) ) || $this->get_status() === $status, $this, $status );
		}

		/*
		|--------------------------------------------------------------------------
		| URLs and Endpoints
		|--------------------------------------------------------------------------
		 */

		/**
		 * Get's the URL to edit the payout batch in the backend.
		 *
		 * @since 1.0.0
		 * @return String
		 */
		public function get_admin_edit_url() {
			/**
			 * Edit payout batch URL
			 *
			 * @since 1.0.0
			 */
			return apply_filters( 'mvr_get_admin_edit_payout_batch_url', get_admin_url( null, 'post.php?post=' . $this->get_id() . '&action=edit' ), $this );
		}

		/**
		 * Get Payouts.
		 *
		 * @since 1.0.0
		 * @param Array $args Arguments.
		 */
		public function get_payouts( $args ) {
			$args = wp_parse_args(
				$args,
				array(
					'batch_log_id' => $this->get_id(),
				)
			);

			/**
			 * Vendor Products Query.
			 *
			 * @since 1.0.0
			 */
			$payouts = mvr_get_payouts( apply_filters( 'mvr_vendor_payouts_query', $args ) );

			return $payouts;
		}

		/*
		|--------------------------------------------------------------------------
		| Getters
		|--------------------------------------------------------------------------
		 */

		/**
		 * Get batch id
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return Integer
		 */
		public function get_batch_id( $context = 'view' ) {
			return $this->get_prop( 'batch_id', $context );
		}

		/**
		 * Get batch amount
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return Integer
		 */
		public function get_batch_amount( $context = 'view' ) {
			return $this->get_prop( 'batch_amount', $context );
		}

		/**
		 * Get batch fee
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return Integer
		 */
		public function get_batch_fee( $context = 'view' ) {
			return $this->get_prop( 'batch_fee', $context );
		}

		/**
		 * Get batch Status
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return Integer
		 */
		public function get_batch_status( $context = 'view' ) {
			return $this->get_prop( 'batch_status', $context );
		}

		/**
		 * Get name
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return Integer
		 */
		public function get_name( $context = 'view' ) {
			return $this->get_prop( 'name', $context );
		}

		/**
		 * Get version.
		 *
		 * @since 1.0.0
		 * @param  String $context View or edit context.
		 * @return String
		 */
		public function get_version( $context = 'view' ) {
			return $this->get_prop( 'version', $context );
		}

		/**
		 * Get time created.
		 *
		 * @since 1.0.0
		 * @param  String $context View or edit context.
		 * @return WC_DateTime|NULL object if the date is set or null if there is no date.
		 */
		public function get_time_created( $context = 'view' ) {
			return $this->get_prop( 'time_created', $context );
		}

		/**
		 * Get time Completed.
		 *
		 * @since 1.0.0
		 * @param  String $context View or edit context.
		 * @return WC_DateTime|NULL object if the date is set or null if there is no date.
		 */
		public function get_time_completed( $context = 'view' ) {
			return $this->get_prop( 'time_completed', $context );
		}

		/**
		 * Get Items
		 *
		 * @since 1.0.0
		 * @param  String $context View or edit context.
		 * @return Array object if the date is set or null if there is no date.
		 */
		public function get_items( $context = 'view' ) {
			return $this->get_prop( 'items', $context );
		}

		/**
		 * Get Additional Data
		 *
		 * @since 1.0.0
		 * @param  String $context View or edit context.
		 * @return Array object if the date is set or null if there is no date.
		 */
		public function get_additional_data( $context = 'view' ) {
			return $this->get_prop( 'additional_data', $context );
		}

		/**
		 * Get email subject
		 *
		 * @since 1.0.0
		 * @param  String $context View or edit context.
		 * @return Array object if the date is set or null if there is no date.
		 */
		public function get_email_subject( $context = 'view' ) {
			return $this->get_prop( 'email_subject', $context );
		}

		/**
		 * Get email message
		 *
		 * @since 1.0.0
		 * @param  String $context View or edit context.
		 * @return Array object if the date is set or null if there is no date.
		 */
		public function get_email_message( $context = 'view' ) {
			return $this->get_prop( 'email_message', $context );
		}

		/**
		 * Get date created.
		 *
		 * @since 1.0.0
		 * @param  String $context View or edit context.
		 * @return WC_DateTime|NULL object if the date is set or null if there is no date.
		 */
		public function get_date_created( $context = 'view' ) {
			return $this->get_prop( 'date_created', $context );
		}

		/**
		 * Get date modified.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return WC_DateTime|NULL object if the date is set or null if there is no date.
		 */
		public function get_date_modified( $context = 'view' ) {
			return $this->get_prop( 'date_modified', $context );
		}

		/**
		 * Return the payout batch statuses without mvr- internal prefix.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String
		 */
		public function get_status( $context = 'view' ) {
			$status = $this->get_prop( 'status', $context );

			if ( empty( $status ) && 'view' === $context ) {
				/**
				 * Default Status.
				 *
				 * @since 1.0.0
				 */
				$status = apply_filters( 'mvr_default_payout_patch_status', 'pending' );
			}

			return $status;
		}

		/*
		|--------------------------------------------------------------------------
		| Setters
		|--------------------------------------------------------------------------
		|
		| Functions for setting payout batch data. These should not update anything in the
		| database itself and should only change what is stored in the class
		| object.
		 */

		/**
		 * Set status.
		 *
		 * @since 1.0.0
		 * @param String  $new_status Status to change the payout batch to. No internal mvr- prefix is required.
		 * @param String  $note          Optional note to add.
		 * @param Boolean $manual_update Is this a manual user payment status change?.
		 * @return Array details of change
		 */
		public function set_status( $new_status, $note = '', $manual_update = false ) {
			$old_status        = $this->get_status();
			$new_status        = mvr_trim_post_status( $new_status );
			$status_exceptions = array( 'auto-draft', 'trash' );

			// If setting the status, ensure it's set to a valid status.
			if ( true === $this->object_read ) {
				// Only allow valid new status.
				if ( ! in_array( 'mvr-' . $new_status, $this->get_valid_statuses(), true ) && ! in_array( $new_status, $status_exceptions, true ) ) {
					$new_status = 'pending';
				}

				// If the old status is set but unknown (e.g. draft) assume its active for action usage.
				if ( $old_status && ! in_array( 'mvr-' . $old_status, $this->get_valid_statuses(), true ) && ! in_array( $old_status, $status_exceptions, true ) ) {
					$old_status = 'pending';
				}

				if ( ! empty( $old_status ) && $old_status !== $new_status ) {
					$this->status_transition = array(
						'from'   => ! empty( $this->status_transition['from'] ) ? $this->status_transition['from'] : $old_status,
						'to'     => $new_status,
						'note'   => $note,
						'manual' => (bool) $manual_update,
					);

					if ( $manual_update ) {
						/**
						 * When payout batch status has been manually edited.
						 *
						 * @since 1.0
						 */
						do_action( 'mvr_payout_batch_edit_status', $this->get_id(), $new_status );
					}
				}
			}
			$this->set_prop( 'status', $new_status );

			return array(
				'from' => $old_status,
				'to'   => $new_status,
			);
		}

		/**
		 * Set batch ID.
		 *
		 * @since 1.0.0
		 * @param Integer $value Value to set.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_batch_id( $value ) {
			$this->set_prop( 'batch_id', $value );
		}

		/**
		 * Set batch amount.
		 *
		 * @since 1.0.0
		 * @param Integer $value Value to set.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_batch_amount( $value ) {
			$this->set_prop( 'batch_amount', $value );
		}

		/**
		 * Set batch fee.
		 *
		 * @since 1.0.0
		 * @param Integer $value Value to set.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_batch_fee( $value ) {
			$this->set_prop( 'batch_fee', $value );
		}

		/**
		 * Set batch Status.
		 *
		 * @since 1.0.0
		 * @param Integer $value Value to set.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_batch_status( $value ) {
			$this->set_prop( 'batch_status', $value );
		}

		/**
		 * Set name.
		 *
		 * @since 1.0.0
		 * @param Integer $value Value to set.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_name( $value ) {
			$this->set_prop( 'name', $value );
		}

		/**
		 * Set version.
		 *
		 * @since 1.0.0
		 * @param String $value Value to set.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_version( $value ) {
			$this->set_prop( 'version', $value );
		}

		/**
		 * Set time created.
		 *
		 * @since 1.0.0
		 * @param String|Integer|Null $date UTC timestamp, or ISO 8601 DateTime. If the DateTime string has no timezone or offset, WordPress site timezone will be assumed. Null if there is no date.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_time_created( $date = null ) {
			$this->set_prop( 'time_created', $date );
		}

		/**
		 * Set time created.
		 *
		 * @since 1.0.0
		 * @param String|Integer|Null $date UTC timestamp, or ISO 8601 DateTime. If the DateTime string has no timezone or offset, WordPress site timezone will be assumed. Null if there is no date.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_time_completed( $date = null ) {
			$this->set_prop( 'time_completed', $date );
		}

		/**
		 * Set items.
		 *
		 * @since 1.0.0
		 * @param Array $value Value to set.
		 * @throws Exception Exception may be thrown if value is invalid.
		 */
		public function set_items( $value ) {
			$this->set_prop( 'items', $value );
		}

		/**
		 * Set additional data.
		 *
		 * @since 1.0.0
		 * @param Array $value Value to set.
		 * @throws Exception Exception may be thrown if value is invalid.
		 */
		public function set_additional_data( $value ) {
			$this->set_prop( 'additional_data', $value );
		}

		/**
		 * Set email subject.
		 *
		 * @since 1.0.0
		 * @param Array $value Value to set.
		 * @throws Exception Exception may be thrown if value is invalid.
		 */
		public function set_email_subject( $value ) {
			$this->set_prop( 'email_subject', $value );
		}

		/**
		 * Set email message.
		 *
		 * @since 1.0.0
		 * @param Array $value Value to set.
		 * @throws Exception Exception may be thrown if value is invalid.
		 */
		public function set_email_message( $value ) {
			$this->set_prop( 'email_message', $value );
		}

		/**
		 * Set date created.
		 *
		 * @since 1.0.0
		 * @param String|Integer|Null $date UTC timestamp, or ISO 8601 DateTime. If the DateTime string has no timezone or offset, WordPress site timezone will be assumed. Null if there is no date.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_date_created( $date = null ) {
			$this->set_date_prop( 'date_created', $date );
		}

		/**
		 * Set date modified.
		 *
		 * @since 1.0.0
		 * @param String|Integer|Null $date UTC timestamp, or ISO 8601 DateTime. If the DateTime string has no timezone or offset, WordPress site timezone will be assumed. Null if there is no date.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_date_modified( $date = null ) {
			$this->set_date_prop( 'date_modified', $date );
		}
	}
}
