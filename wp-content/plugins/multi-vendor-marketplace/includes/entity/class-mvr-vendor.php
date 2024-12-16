<?php
/**
 * Vendor Data.
 *
 * @package Multi-Vendor for WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Vendor' ) ) {
	/**
	 * Vendor
	 *
	 * @class MVR_Vendor
	 * @package Class
	 */
	class MVR_Vendor extends WC_Data {

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
			'user_id'                        => '',
			'amount'                         => 0,
			'locked_amount'                  => 0,
			'sold_amount'                    => 0,
			'logo_id'                        => '',
			'banner_id'                      => '',
			'status'                         => '',
			'version'                        => '',
			'date_created'                   => null,
			'date_modified'                  => null,
			'tac'                            => '',
			'name'                           => '',
			'shop_name'                      => '',
			'slug'                           => '',
			'description'                    => '',
			'email'                          => '',
			'first_name'                     => '',
			'last_name'                      => '',
			'address1'                       => '',
			'address2'                       => '',
			'city'                           => '',
			'state'                          => '',
			'country'                        => '',
			'zip_code'                       => '',
			'phone'                          => '',
			'facebook'                       => '',
			'twitter'                        => '',
			'youtube'                        => '',
			'instagram'                      => '',
			'linkedin'                       => '',
			'pinterest'                      => '',
			'commission_from'                => '1',
			'commission_criteria'            => '',
			'commission_criteria_value'      => '',
			'commission_type'                => '1',
			'commission_value'               => '',
			'tax_to'                         => '',
			'commission_after_coupon'        => '',
			'commission_after_vendor_coupon' => '',
			'payment_method'                 => '',
			'bank_account_name'              => '',
			'bank_account_number'            => '',
			'bank_account_type'              => '',
			'bank_name'                      => '',
			'iban'                           => '',
			'swift'                          => '',
			'paypal_email'                   => '',
			'payout_type'                    => '1',
			'payout_schedule'                => '1',
			'withdraw_from'                  => '1',
			'enable_withdraw_charge'         => '',
			'withdraw_charge_type'           => '',
			'withdraw_charge_value'          => '',
			'enable_product_management'      => '',
			'product_creation'               => '',
			'product_modification'           => '',
			'published_product_modification' => '',
			'manage_inventory'               => '',
			'product_deletion'               => '',
			'enable_order_management'        => '',
			'order_status_modification'      => '',
			'commission_info_display'        => '',
			'enable_coupon_management'       => '',
			'coupon_creation'                => '',
			'coupon_modification'            => '',
			'published_coupon_modification'  => '',
			'coupon_deletion'                => '',
			'enable_commission_withdraw'     => '',
			'commission_transaction'         => '',
			'commission_transaction_info'    => '',
		);

		/**
		 * Stores meta in cache for future reads.
		 *
		 * A group must be set to to enable caching.
		 *
		 * @var String
		 */
		protected $cache_group = 'mvr_vendors';

		/**
		 * Which data store to load.
		 *
		 * @var String
		 */
		protected $data_store_name = 'mvr_vendor';

		/**
		 * This is the name of this object type.
		 *
		 * @var String
		 */
		protected $object_type = 'mvr_vendor';

		/**
		 * Get the Vendor if ID is passed, otherwise the vendor is new and empty.
		 *
		 * @since 1.0.0
		 * @param  int|object|MVR_Vendor $vendor Vendor to read.
		 */
		public function __construct( $vendor = 0 ) {
			parent::__construct( $vendor );

			if ( is_numeric( $vendor ) && $vendor > 0 ) {
				$this->set_id( $vendor );
			} elseif ( $vendor instanceof self ) {
				$this->set_id( $vendor->get_id() );
			} elseif ( ! empty( $vendor->ID ) ) {
				$this->set_id( $vendor->ID );
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
		 * Subscribed Product.
		 *
		 * @since 1.0.0
		 * @return String
		 */
		public function has_subscribed() {
			if ( ! class_exists( 'WC_Subscriptions' ) ) {
				return true;
			}

			$subscription_product = get_option( 'mvr_settings_subscription_product' );

			if ( function_exists( 'wcs_user_has_subscription' ) ) {
				return wcs_user_has_subscription( $this->get_user_id(), $subscription_product );
			}

			return false;
		}

		/**
		 * Subscribed Product.
		 *
		 * @since 1.0.0
		 * @return String
		 */
		public function get_subscription() {
			if ( ! class_exists( 'WC_Subscriptions' ) || ! function_exists( 'wcs_get_users_subscriptions' ) ) {
				return false;
			}

			$subscription_product = get_option( 'mvr_settings_subscription_product' );
			$subscriptions        = wcs_get_users_subscriptions( $this->get_user_id() );

			foreach ( $subscriptions as $subscription ) {
				if ( $subscription->has_product( $subscription_product ) && $subscription->has_status( array( 'active' ) ) ) {
					return $subscription;
				}
			}

			foreach ( $subscriptions as $subscription ) {
				if ( $subscription->has_product( $subscription_product ) ) {
					return $subscription;
				}
			}

			return false;
		}

		/**
		 * Subscribed Product.
		 *
		 * @since 1.0.0
		 * @return String
		 */
		public function get_subscription_status() {
			$subscription = $this->get_subscription();

			if ( ! $subscription || ! is_a( $subscription, 'WC_Subscription' ) ) {
				return false;
			}

			return $subscription->get_status();
		}

		/**
		 * Cleared Form Filling
		 *
		 * @since 1.0.0
		 * @return String
		 */
		public function has_social_link() {
			if ( ! empty( $this->get_facebook() ) || ! empty( $this->get_twitter() ) || ! empty( $this->get_youtube() ) || ! empty( $this->get_linkedin() ) || ! empty( $this->get_pinterest() ) ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Cleared Form Filling
		 *
		 * @since 1.0.0
		 * @return String
		 */
		public function cleared_form_filling() {
			if ( ! $this->cleared_payment_tab() ||
				! $this->cleared_profile_tab() ||
				! $this->cleared_address_tab()
			) {
				return false;
			}

			return true;
		}

		/**
		 * Cleared Payment Tab
		 *
		 * @since 1.0.0
		 * @return Boolean
		 */
		public function cleared_payment_tab() {
			if ( '2' === $this->get_payment_method() ) {
				if ( empty( $this->get_paypal_email() ) ) {
					return false;
				}
			} elseif ( empty( $this->get_bank_account_name() ) ||
				empty( $this->get_bank_account_number() ) ||
				empty( $this->get_bank_account_type() ) ||
				empty( $this->get_bank_name() ) ) {
					return false;
			}

			return true;
		}

		/**
		 * Cleared Profile Tab
		 *
		 * @since 1.0.0
		 * @param String $from From.
		 * @return Boolean
		 */
		public function cleared_profile_tab( $from = '' ) {
			if ( 'admin' === $from ) {
				if ( empty( $this->get_shop_name() ) ||
				empty( $this->get_slug() ) ||
				empty( $this->get_email() ) ) {
					return false;
				}
			} elseif ( empty( $this->get_shop_name() ) ||
				empty( $this->get_slug() ) ||
				empty( $this->get_email() ) ||
				empty( $this->get_description() ) ||
				empty( $this->get_tac() ) ||
				empty( $this->get_name() ) ) {

					return false;
			}

			return true;
		}

		/**
		 * Cleared Profile Tab
		 *
		 * @since 1.0.0
		 * @return Boolean
		 */
		public function cleared_address_tab() {
			if (
				empty( $this->get_first_name() ) ||
				empty( $this->get_last_name() ) ||
				empty( $this->get_address1() ) ||
				empty( $this->get_city() ) ||
				empty( $this->get_state() ) ||
				empty( $this->get_country() ) ||
				empty( $this->get_zip_code() ) ||
				empty( $this->get_phone() )
			) {
				return false;
			}

			return true;
		}

		/**
		 * Get Reviews
		 *
		 * @since 1.0.0
		 * @param Array $args Arguments.
		 * @return String
		 */
		public function get_reviews( $args ) {
			$args = wp_parse_args(
				$args,
				array(
					'vendor_id' => $this->get_id(),
					'post_id'   => $this->get_id(),
				)
			);

			return mvr_get_reviews( $args );
		}

		/**
		 * Get Average Rating.
		 *
		 * @since 1.0.0
		 * @return Float.
		 */
		public function get_average_rating() {
			$total_rating   = 0;
			$average_rating = 0;
			$reviews_obj    = $this->get_reviews(
				array(
					'status' => mvr_convert_review_status_to_query_val( 'approved' ),
				)
			);

			if ( ! $reviews_obj->has_review ) {
				return 0;
			}

			foreach ( $reviews_obj->reviews as $review_obj ) {
				$rating = get_comment_meta( $review_obj->comment_ID, 'rating', true );

				if ( ! empty( $rating ) && is_numeric( $rating ) ) {
					$total_rating += (int) $rating;
				}
			}

			$average_rating = ( $total_rating / $reviews_obj->total_review );

			return $average_rating;
		}

		/**
		 * Get Review Count.
		 *
		 * @since 1.0.0
		 * @return Integer.
		 */
		public function get_review_count() {
			$reviews_obj = $this->get_reviews(
				array(
					'status' => mvr_convert_review_status_to_query_val( 'approved' ),
				)
			);

			return $reviews_obj->total_review;
		}

		/**
		 * Get Staff
		 *
		 * @since 1.0.0
		 * @param Array $args Arguments.
		 * @return String
		 */
		public function get_staffs( $args ) {
			$args = wp_parse_args(
				$args,
				array(
					'status'    => array_keys( mvr_get_staff_statuses() ),
					'vendor_id' => $this->get_id(),
				)
			);

			return mvr_get_staffs( $args );
		}

		/**
		 * Get Customers
		 *
		 * @since 1.0.0
		 * @param Array $args Arguments.
		 * @return String
		 */
		public function get_customers( $args ) {
			$args = wp_parse_args(
				$args,
				array(
					'vendor_id' => $this->get_id(),
				)
			);

			return mvr_get_customers( $args );
		}

		/**
		 * Get Shop URL
		 *
		 * @since 1.0.0
		 * @return String
		 */
		public function get_shop_url() {
			$query_vars      = mvr()->query->get_query_vars();
			$endpoint        = ! empty( $query_vars['mvr-store'] ) ? $query_vars['mvr-store'] : 'mvr-store';
			$stores_page_url = mvr_get_page_permalink( 'stores' );

			return wc_get_endpoint_url( $endpoint, $this->get_slug(), $stores_page_url );
		}

		/**
		 * Get all valid statuses for this vendor
		 *
		 * @since 1.0.0
		 * @return Array Internal status keys e.g. 'mvr-active'
		 */
		public function get_valid_statuses() {
			return array_keys( mvr_get_vendor_statuses() );
		}

		/**
		 * Updates status of vendor immediately.
		 *
		 * @since 1.0.0
		 * @uses MVR_Vendor::set_status()
		 * @param String $new_status    Status to change the vendor to. No internal mvr- prefix is required.
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
						'Error updating status for vendor #%d',
						$this->get_id()
					),
					array(
						'vendor' => $this,
						'error'  => $e,
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
					do_action( 'mvr_vendor_status_' . $status_transition['to'], $this );

					if ( ! empty( $status_transition['from'] ) && $status_transition['from'] !== $status_transition['to'] ) {
						/* translators: 1: old status 2: new status */
						$transition_note = sprintf( __( 'Status changed from <b>%1$s</b> to <b>%2$s</b>.', 'multi-vendor-marketplace' ), mvr_get_vendor_status_name( $status_transition['from'] ), mvr_get_vendor_status_name( $status_transition['to'] ) );

						// Note the transition occurred.
						$this->add_note( trim( $status_transition['note'] . ' ' . $transition_note ), 0, $status_transition['manual'] );

						/**
						 * Vendor status updated from and to.
						 *
						 * @since 1.0.0
						 */
						do_action( 'mvr_vendor_status_' . $status_transition['from'] . '_to_' . $status_transition['to'], $this );

						/**
						 * Vendor status changed.
						 *
						 * @since 1.0.0
						 */
						do_action( 'mvr_vendor_status_changed', $status_transition['from'], $status_transition['to'], $this );

						/**
						 * Vendor status updated.
						 *
						 * @since 1.0.0
						 */
						do_action( 'mvr_vendor_status_updated', $status_transition['to'], $status_transition['manual'], $this );
					} else {
						/* translators: %s: new status */
						$transition_note = sprintf( __( 'Vendor status set to <b>%s</b>.', 'multi-vendor-marketplace' ), mvr_get_vendor_status_name( $status_transition['to'] ) );

						// Note the transition occurred.
						$this->add_note( trim( $status_transition['note'] . ' ' . $transition_note ), 0, $status_transition['manual'] );
					}
				} catch ( Exception $e ) {
					$this->handle_exception( $e, sprintf( 'Status transition of vendor #%d errored!', $this->get_id() ) );
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
		 * Adds a note (comment) to the vendor. Vendor must exist.
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
			 * Get Vendor note args.
			 *
			 * @since 1.0.0
			 */
			$comment_data = apply_filters(
				'mvr_new_vendor_note_data',
				array(
					'comment_post_ID'      => $this->get_id(),
					'comment_author'       => $comment_author,
					'comment_author_email' => $comment_author_email,
					'comment_author_url'   => '',
					'comment_content'      => $note,
					'comment_agent'        => 'Multi Vendor Marketplace',
					'comment_type'         => 'mvr_vendor_note',
					'comment_parent'       => 0,
					'comment_approved'     => 1,
				),
				array(
					'vendor_id'        => $this->get_id(),
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
			do_action( 'mvr_vendor_note_added', $comment_id, $this );

			return $comment_id;
		}

		/**
		 * Adds Store Review. Vendor must exist.
		 *
		 * @param  Integer $rating Rating.
		 * @param  String  $comment Comment.
		 * @param Integer $user_id User ID.
		 * @return Integer Comment ID.
		 */
		public function add_store_review( $rating, $comment, $user_id = '' ) {
			if ( ! $this->get_id() ) {
				return 0;
			}

			$comment_author       = '';
			$comment_author_email = '';

			if ( ! is_user_logged_in() ) {
				return false;
			}

			$user_id = empty( $user_id ) ? get_current_user_id() : $user_id;

			if ( empty( $user_id ) ) {
				return;
			}

			$user = get_user_by( 'id', $user_id );

			if ( $user instanceof WP_User ) {
				$comment_author       = $user->display_name;
				$comment_author_email = $user->user_email;
			}

			/**
			 * Get Vendor note args.
			 *
			 * @since 1.0.0
			 */
			$comment_data = apply_filters(
				'mvr_new_vendor_store_review_data',
				array(
					'comment_post_ID'      => $this->get_id(),
					'comment_author'       => $comment_author,
					'comment_author_email' => $comment_author_email,
					'comment_author_url'   => '',
					'comment_content'      => $comment,
					'comment_agent'        => 'Multi Vendor Marketplace',
					'comment_type'         => 'mvr_store_review',
					'comment_parent'       => 0,
					'comment_approved'     => 1,
					'user_id'              => $user_id,
				),
				array(
					'vendor_id' => $this->get_id(),
					'user_id'   => $user_id,
				)
			);

			$comment_id = wp_insert_comment( $comment_data );

			if ( $comment_id ) {
				add_comment_meta( $comment_id, 'rating', $rating );

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
			 * Action hook fired after an store review is added.
			 *
			 * @param Integer $comment_id Comment ID.
			 * @param MVR_Vendor $this Vendor Object.
			 * @since 1.0.0
			 */
			do_action( 'mvr_vendor_store_review_added', $comment_id, $this );

			return $comment_id;
		}

		/**
		 * List vendor notes (public) for the customer.
		 *
		 * @return Array
		 */
		public function get_customer_notes() {
			$notes = array();
			$args  = array(
				'post_id' => $this->get_id(),
				'approve' => 'approve',
				'type'    => '',
			);

			remove_filter( 'comments_clauses', array( 'MVR_Comments', 'exclude_vendor_comments' ) );

			$comments = get_comments( $args );

			foreach ( $comments as $comment ) {
				if ( ! get_comment_meta( $comment->comment_ID, 'is_customer_note', true ) ) {
					continue;
				}

				$comment->comment_content = make_clickable( $comment->comment_content );
				$notes[]                  = $comment;
			}

			add_filter( 'comments_clauses', array( 'MVR_Comments', 'exclude_vendor_comments' ) );

			return $notes;
		}

		/**
		 * Updates Amount.
		 *
		 * @since 1.0.0
		 * @param Float $amount Amount to update.
		 */
		public function update_amount( $amount ) {
			$this->set_amount( $this->get_amount() + $amount );
			$this->save();
		}

		/**
		 * Updates Amount.
		 *
		 * @since 1.0.0
		 * @param Float $amount Amount to update.
		 */
		public function update_locked_amount( $amount ) {
			$this->set_locked_amount( $this->get_locked_amount() + $amount );
			$this->save();
		}

		/**
		 * Updates order Amount.
		 *
		 * @since 1.0.0
		 */
		public function update_order_amount() {
			$transactions_obj = $this->get_transactions( 'locked' );

			if ( ! $transactions_obj->has_transaction ) {
				return;
			}

			foreach ( $transactions_obj->transactions as $transaction_obj ) {
				$current_time            = mvr_get_current_time();
				$withdraw_available_time = strtotime( $transaction_obj->get_withdraw_date() );

				if ( $withdraw_available_time <= $current_time ) {
					$this->update_amount( $transaction_obj->get_amount() );

					// Update Commission Status.
					$commissions_obj = mvr_get_commissions(
						array(
							'source_id'   => $transaction_obj->get_source_id(),
							'source_from' => $transaction_obj->get_source_from(),
							'vendor_id'   => $this->get_id(),
							'status'      => 'pending',
						)
					);

					if ( $commissions_obj->has_commission ) {
						$commission_obj = current( $commissions_obj->commissions );
						$commission_obj->update_status( 'paid' );
					}

					$transaction_obj->update_status( 'completed' );

					/**
					 * Fires after Complete Transaction
					 *
					 * @since 1.0.0
					 */
					do_action( 'mvr_after_transaction_complete', $transaction_obj, $this );
				}
			}
		}

		/**
		 * Get vendor total amount
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return Integer
		 */
		public function get_total_amount( $context = 'view' ) {
			return $this->get_amount() + $this->get_locked_amount();
		}

		/**
		 * Log an error about this vendor is exception is encountered.
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
		 * Checks the vendor status against a passed in status.
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
			return apply_filters( 'mvr_vendor_has_status', ( is_array( $status ) && in_array( $this->get_status(), $status, true ) ) || $this->get_status() === $status, $this, $status );
		}

		/**
		 * Checks the vendor has this customer
		 *
		 * @since 1.0.0
		 * @param Integer $user_id User ID.
		 * @return Boolean
		 */
		public function has_customer( $user_id ) {
			$result = $this->get_customers(
				array(
					'vendor_id' => $this->get_id(),
					'user_id'   => $user_id,
				)
			);

			return $result->has_customer;
		}

		/**
		 * Checks the customer has reviewed the Shop.
		 *
		 * @since 1.0.0
		 * @param Integer $user_id User ID.
		 * @return Boolean
		 */
		public function has_customer_review( $user_id ) {
			$reviews = mvr_get_reviews(
				array(
					'vendor_id' => $this->get_id(),
					'user_id'   => $user_id,
				)
			);

			return $reviews->has_review;
		}

		/*
		|--------------------------------------------------------------------------
		| URLs and Endpoints
		|--------------------------------------------------------------------------
		 */

		/**
		 * Get's the URL to edit the vendor in the backend.
		 *
		 * @since 1.0.0
		 * @return String
		 */
		public function get_admin_edit_url() {
			/**
			 * Edit Vendor URL
			 *
			 * @since 1.0.0
			 */
			return apply_filters( 'mvr_get_admin_edit_vendor_url', get_admin_url( null, 'post.php?post=' . $this->get_id() . '&action=edit' ), $this );
		}

		/**
		 * Get Products.
		 *
		 * @since 1.0.0
		 * @param Array $args Arguments.
		 */
		public function get_products( $args ) {
			$args = wp_parse_args(
				$args,
				array(
					'post_status'        => array( 'publish' ),
					'type'               => 'product',
					'return'             => 'objects',
					'limit'              => -1,
					'mvr_include_vendor' => $this->get_id(),
				)
			);

			/**
			 * Vendor Products Query.
			 *
			 * @since 1.0.0
			 */
			$vendor_products = wc_get_products( apply_filters( 'mvr_vendor_products_query', $args ) );

			return $vendor_products;
		}

		/**
		 * Get Vendor Orders.
		 *
		 * @since 1.0.0
		 * @param Array $args Arguments.
		 * @return Array
		 */
		public function get_orders( $args = array() ) {
			$args = wp_parse_args(
				$args,
				array(
					'vendor_id' => $this->get_id(),
				)
			);

			/**
			 * Vendor Orders Query.
			 *
			 * @since 1.0.0
			 */
			$vendor_orders = mvr_get_orders( apply_filters( 'mvr_vendor_orders_query', $args ) );

			return $vendor_orders;
		}

		/**
		 * Get unread Notification Count.
		 *
		 * @since 1.0.0
		 * @return Integer
		 */
		public function get_unread_notification_count() {
			$notification_obj = mvr_get_notifications(
				array(
					'vendor_id' => $this->get_id(),
					'status'    => 'unread',
					'to'        => 'vendor',
				)
			);

			return $notification_obj->total_notifications;
		}

		/**
		 * Get unread enquiry Count.
		 *
		 * @since 1.0.0
		 * @return Integer
		 */
		public function get_unread_enquiry_count() {
			$notification_obj = mvr_get_enquiries(
				array(
					'vendor_id' => $this->get_id(),
					'status'    => 'unread',
				)
			);

			return $notification_obj->total_enquiries;
		}

		/**
		 * Get Vendor Transaction.
		 *
		 * @since 1.0.0
		 * @param String $type Transaction Type.
		 * @return Array
		 */
		public function get_transactions( $type = '' ) {
			if ( ! function_exists( 'mvr_get_transactions' ) ) {
				return false;
			}

			if ( 'locked' === $type ) {
				$args = array(
					'status'      => 'mvr-processing',
					'vendor_id'   => $this->get_id(),
					'source_from' => 'order',
				);
			} elseif ( 'open' === $type ) {
				$args = array(
					'status'      => 'mvr-completed',
					'vendor_id'   => $this->get_id(),
					'source_from' => 'order',
				);
			} else {
				$args = array(
					'status'    => array_keys( mvr_get_transaction_statuses() ),
					'vendor_id' => $this->get_id(),
				);
			}

			return mvr_get_transactions( $args );
		}

		/**
		 * Display Admin Commission
		 *
		 * @since 1.0.0
		 * @return String
		 */
		public function display_admin_commission() {
			$settings = $this->get_commission_settings();

			return ( '2' === $settings['type'] ) ? $settings['value'] . '%' : wc_price( $settings['value'] );
		}

		/**
		 * Commission Settings
		 *
		 * @since 1.0.0
		 * @param String $key Commission Settings Key.
		 * @return Array
		 */
		public function get_commission_settings( $key = '' ) {
			$args = array(
				'from' => $this->get_commission_from(),
			);

			if ( '2' === $this->get_commission_from() ) {
				$args['criteria']            = $this->get_commission_criteria();
				$args['criteria_value']      = $this->get_commission_criteria_value();
				$args['type']                = $this->get_commission_type();
				$args['value']               = $this->get_commission_value();
				$args['after_coupon']        = ( 'yes' === $this->get_commission_after_coupon() ) ? true : false;
				$args['after_vendor_coupon'] = ( 'yes' === $this->get_commission_after_vendor_coupon() ) ? true : false;
				$args['tax_to']              = $this->get_tax_to();
			} else {
				$args['criteria']            = get_option( 'mvr_settings_commission_criteria', '1' );
				$args['criteria_value']      = get_option( 'mvr_settings_commission_criteria_value' );
				$args['type']                = get_option( 'mvr_settings_commission_type', '1' );
				$args['value']               = get_option( 'mvr_settings_commission_value' );
				$args['after_coupon']        = ( 'yes' === get_option( 'mvr_settings_commission_after_coupon' ) ) ? true : false;
				$args['after_vendor_coupon'] = ( 'yes' === get_option( 'mvr_settings_commission_after_vendor_coupon' ) ) ? true : false;
				$args['tax_to']              = get_option( 'mvr_settings_commission_tax_to', '1' );
			}

			if ( ! empty( $args[ $key ] ) ) {
				return isset( $args[ $key ] ) ? $args[ $key ] : '';
			}

			return $args;
		}

		/**
		 * Withdraw Settings
		 *
		 * @since 1.0.0
		 * @param String $key Withdraw Settings Key.
		 * @return Array
		 */
		public function get_withdraw_settings( $key = '' ) {
			$args = array(
				'from' => $this->get_withdraw_from(),
			);

			if ( '2' === $this->get_withdraw_from() ) {
				$args['enable_charge'] = $this->get_enable_withdraw_charge();
				$args['charge_type']   = $this->get_withdraw_charge_type();
				$args['charge_value']  = $this->get_withdraw_charge_value();
			} else {
				$args['enable_charge'] = get_option( 'mvr_settings_enable_withdraw_charge_req', '1' );
				$args['charge_type']   = get_option( 'mvr_settings_withdraw_charge_type', '1' );
				$args['charge_value']  = get_option( 'mvr_settings_withdraw_charge_val', '0' );
			}

			if ( ! empty( $args[ $key ] ) ) {
				return isset( $args[ $key ] ) ? $args[ $key ] : '';
			}

			return $args;
		}

		/**
		 * Get Withdrawal Charge Amount
		 *
		 * @since 1.0.0
		 * @param Float $amount Vendor Amount.
		 * @return Array
		 */
		public function calculate_withdraw_charge( $amount ) {
			$withdraw_charge_settings = $this->get_withdraw_settings();
			$charge_amount            = 0;

			if ( 'yes' === $withdraw_charge_settings['enable_charge'] ) {
				if ( '2' === $withdraw_charge_settings['charge_type'] ) {
					$charge_amount = $amount * ( $withdraw_charge_settings['charge_value'] / 100 );
				} else {
					$charge_amount = $withdraw_charge_settings['charge_value'];
				}
			}

			return (float) $charge_amount;
		}

		/**
		 * Get Order Item Totals.
		 *
		 * @since 1.0.0
		 * @param WC_Order $order_obj Order Item Object.
		 * @return Array
		 */
		public function get_applied_coupons( $order_obj ) {
			$applied_coupons = array();

			if ( ! is_a( $order_obj, 'WC_Order' ) ) {
				return $applied_coupons;
			}

			if ( count( $order_obj->get_coupon_codes() ) > 0 ) {
				foreach ( $order_obj->get_coupon_codes() as $code ) {
					$coupon_obj = new WC_Coupon( $code );
				}
			}

			return $applied_coupons;
		}

		/**
		 * Calculate Vendor Amount.
		 *
		 * @since 1.0.0
		 * @param WC_Order_Item $item Order Item Object.
		 * @return Float
		 */
		public function get_calculate_vendor_amount( $item ) {
			if ( ! is_a( $item, 'WC_Order_Item' ) ) {
				return false;
			}

			$args            = $this->get_commission_settings();
			$applied_coupons = $item->get_meta( '_mvr_applied_coupons', true );
			$vendor_discount = isset( $applied_coupons['vendor_discount'] ) ? (float) $applied_coupons['vendor_discount'] : 0;
			$item_price      = (float) $item->get_subtotal() - $vendor_discount;
			$commission      = (float) $this->get_calculate_commission( $item );
			$vendor_amount   = $item_price - $commission;

			// Tax.
			$total_tax = 0;
			$taxes     = $item->get_taxes();

			foreach ( $taxes['total'] as $tax_rate_id => $tax ) {
				$total_tax += (float) $tax;
			}

			if ( '2' === $args['tax_to'] ) {
				$vendor_amount = $vendor_amount + $total_tax;
			} elseif ( '3' === $args['tax_to'] ) {
				$vendor_amount = $vendor_amount + ( $total_tax / 2 );
			}

			return $vendor_amount;
		}

		/**
		 * Calculate Commission Amount.
		 *
		 * @since 1.0.0
		 * @param WC_Order_Item $item Order Item Object.
		 * @return Float
		 */
		public function get_calculate_commission( $item ) {
			if ( ! is_a( $item, 'WC_Order_Item' ) ) {
				return false;
			}

			$args = $this->get_commission_settings();

			// Order Count Criteria.
			if ( '2' === $args['criteria'] && $args['criteria_value'] >= $this->get_orders()->total_orders ) {
				return 0;
			}

			// Total Sold Criteria.
			if ( '3' === $args['criteria'] && $args['criteria_value'] >= $this->get_sold_amount() ) {
				return 0;
			}

			// Product Price Criteria.
			$item_price = (float) $item->get_subtotal();

			if ( '3' === $args['criteria'] && $args['criteria_value'] >= $item_price ) {
				return 0;
			}

			$applied_coupons = $item->get_meta( '_mvr_applied_coupons', true );
			$total_discounts = (float) $item->get_subtotal() - (float) $item->get_total();
			$vendor_discount = isset( $applied_coupons['vendor_discount'] ) ? (float) $applied_coupons['vendor_discount'] : 0;
			$admin_discount  = $total_discounts - $vendor_discount;

			// Calculate Commission after Vendor Coupon.
			if ( $args['after_vendor_coupon'] && $vendor_discount > 0 ) {
				$item_price = $item_price - $vendor_discount;
			}

			// Calculate Commission after Admin Coupon.
			if ( $args['after_coupon'] && $admin_discount > 0 ) {
				$item_price = $item_price - $admin_discount;
			}

			$item_price = ( $item_price < 0 ) ? 0 : $item_price;
			$commission = ( '2' === $args['type'] ) ? (float) $item_price * ( (float) $args['value'] / 100 ) : (float) $args['value'];

			return $commission;
		}

		/*
		|--------------------------------------------------------------------------
		| Getters
		|--------------------------------------------------------------------------
		 */

		/**
		 * Get vendor user id
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return Integer
		 */
		public function get_user_id( $context = 'view' ) {
			return (int) $this->get_prop( 'user_id', $context );
		}

		/**
		 * Get vendor amount
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return Integer
		 */
		public function get_amount( $context = 'view' ) {
			return (float) $this->get_prop( 'amount', $context );
		}

		/**
		 * Get vendor locked amount
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return Integer
		 */
		public function get_locked_amount( $context = 'view' ) {
			return (float) $this->get_prop( 'locked_amount', $context );
		}

		/**
		 * Get vendor locked amount
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return Integer
		 */
		public function get_sold_amount( $context = 'view' ) {
			return (float) $this->get_prop( 'sold_amount', $context );
		}

		/**
		 * Get vendor Logo ID
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return Integer
		 */
		public function get_logo_id( $context = 'view' ) {
			return $this->get_prop( 'logo_id', $context );
		}

		/**
		 * Get vendor banner ID
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return Integer
		 */
		public function get_banner_id( $context = 'view' ) {
			return $this->get_prop( 'banner_id', $context );
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
		 * Get Vendor Terms and Conditions.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the Vendor Terms and Conditions is set or null if there is no Vendor Terms and Conditions.
		 */
		public function get_tac( $context = 'view' ) {
			return $this->get_prop( 'tac', $context );
		}

		/**
		 * Get Shop Name.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the shop name is set or null if there is no shop name.
		 */
		public function get_shop_name( $context = 'view' ) {
			return $this->get_prop( 'shop_name', $context );
		}

		/**
		 * Get Slug.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the slug is set or null if there is no slug.
		 */
		public function get_slug( $context = 'view' ) {
			return $this->get_prop( 'slug', $context );
		}

		/**
		 * Get Description.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the description is set or null if there is no description.
		 */
		public function get_description( $context = 'view' ) {
			return $this->get_prop( 'description', $context );
		}

		/**
		 * Get name.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the name is set or null if there is no name.
		 */
		public function get_name( $context = 'view' ) {
			return $this->get_prop( 'name', $context );
		}

		/**
		 * Get Email.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the email is set or null if there is no email.
		 */
		public function get_email( $context = 'view' ) {
			return $this->get_prop( 'email', $context );
		}

		/**
		 * Get Address
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL object if the address is set or null if there is no first name.
		 */
		public function get_address( $context = 'view' ) {
			return array(
				'first_name' => $this->get_first_name( $context ),
				'last_name'  => $this->get_last_name( $context ),
				'address_1'  => $this->get_address1( $context ),
				'address_2'  => $this->get_address2( $context ),
				'city'       => $this->get_city( $context ),
				'state'      => $this->get_state( $context ),
				'country'    => $this->get_country( $context ),
				'postcode'   => $this->get_zip_code( $context ),
			);
		}

		/**
		 * Get First Name.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL object if the first name is set or null if there is no first name.
		 */
		public function get_first_name( $context = 'view' ) {
			return $this->get_prop( 'first_name', $context );
		}

		/**
		 * Get Last Name.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the last name is set or null if there is no last name.
		 */
		public function get_last_name( $context = 'view' ) {
			return $this->get_prop( 'last_name', $context );
		}

		/**
		 * Get Door Number.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL object if the door number is set or null if there is no door number.
		 */
		public function get_address1( $context = 'view' ) {
			return $this->get_prop( 'address1', $context );
		}

		/**
		 * Get address2.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL object if the street is set or null if there is no street.
		 */
		public function get_address2( $context = 'view' ) {
			return $this->get_prop( 'address2', $context );
		}

		/**
		 * Get City.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL string if the city is set or null if there is no city.
		 */
		public function get_city( $context = 'view' ) {
			return $this->get_prop( 'city', $context );
		}

		/**
		 * Get State.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the state is set or null if there is no state.
		 */
		public function get_state( $context = 'view' ) {
			return $this->get_prop( 'state', $context );
		}

		/**
		 * Get Country.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the country is set or null if there is no country.
		 */
		public function get_country( $context = 'view' ) {
			return $this->get_prop( 'country', $context );
		}

		/**
		 * Get Zip Code.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the zip code is set or null if there is no zip code.
		 */
		public function get_zip_code( $context = 'view' ) {
			return $this->get_prop( 'zip_code', $context );
		}

		/**
		 * Get Phone.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the phone is set or null if there is no phone.
		 */
		public function get_phone( $context = 'view' ) {
			return $this->get_prop( 'phone', $context );
		}

		/**
		 * Get Facebook.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the facebook is set or null if there is no facebook.
		 */
		public function get_facebook( $context = 'view' ) {
			return $this->get_prop( 'facebook', $context );
		}

		/**
		 * Get Twitter.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the twitter is set or null if there is no twitter.
		 */
		public function get_twitter( $context = 'view' ) {
			return $this->get_prop( 'twitter', $context );
		}

		/**
		 * Get Youtube.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the youtube is set or null if there is no youtube.
		 */
		public function get_youtube( $context = 'view' ) {
			return $this->get_prop( 'youtube', $context );
		}

		/**
		 * Get Instagram.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the instagram is set or null if there is no instagram.
		 */
		public function get_instagram( $context = 'view' ) {
			return $this->get_prop( 'instagram', $context );
		}

		/**
		 * Get Linkedin.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the linkedin is set or null if there is no linkedin.
		 */
		public function get_linkedin( $context = 'view' ) {
			return $this->get_prop( 'linkedin', $context );
		}

		/**
		 * Get Pinterest.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the pinterest is set or null if there is no pinterest.
		 */
		public function get_pinterest( $context = 'view' ) {
			return $this->get_prop( 'pinterest', $context );
		}

		/**
		 * Get Commission From.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the commission_from is set or null if there is no commission_from.
		 */
		public function get_commission_from( $context = 'view' ) {
			return $this->get_prop( 'commission_from', $context );
		}

		/**
		 * Get Commission Type.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the commission_type is set or null if there is no commission_type.
		 */
		public function get_commission_type( $context = 'view' ) {
			return $this->get_prop( 'commission_type', $context );
		}

		/**
		 * Get Commission Criteria.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the commission_criteria is set or null if there is no commission_criteria.
		 */
		public function get_commission_criteria( $context = 'view' ) {
			return $this->get_prop( 'commission_criteria', $context );
		}

		/**
		 * Get Commission Criteria Value.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the commission_criteria_value is set or null if there is no commission_criteria_value.
		 */
		public function get_commission_criteria_value( $context = 'view' ) {
			return $this->get_prop( 'commission_criteria_value', $context );
		}

		/**
		 * Get Commission Value.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the commission_value is set or null if there is no commission_value.
		 */
		public function get_commission_value( $context = 'view' ) {
			return $this->get_prop( 'commission_value', $context );
		}

		/**
		 * Get Tax To.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the tax_to is set or null if there is no tax_to.
		 */
		public function get_tax_to( $context = 'view' ) {
			return $this->get_prop( 'tax_to', $context );
		}

		/**
		 * Get Commission After Admin Coupon.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the commission_after_coupon is set or null if there is no commission_after_coupon.
		 */
		public function get_commission_after_coupon( $context = 'view' ) {
			return $this->get_prop( 'commission_after_coupon', $context );
		}

		/**
		 * Get Commission After Vendor Coupon.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the commission_after_coupon is set or null if there is no commission_after_coupon.
		 */
		public function get_commission_after_vendor_coupon( $context = 'view' ) {
			return $this->get_prop( 'commission_after_vendor_coupon', $context );
		}

		/**
		 * Get Payment Method.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the payment_method is set or null if there is no payment_method.
		 */
		public function get_payment_method( $context = 'view' ) {
			return $this->get_prop( 'payment_method', $context );
		}

		/**
		 * Get Bank Account Name.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the bank_account_name is set or null if there is no bank_account_name.
		 */
		public function get_bank_account_name( $context = 'view' ) {
			return $this->get_prop( 'bank_account_name', $context );
		}

		/**
		 * Get Bank Account Number.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the bank_account_number is set or null if there is no bank_account_number.
		 */
		public function get_bank_account_number( $context = 'view' ) {
			return $this->get_prop( 'bank_account_number', $context );
		}

		/**
		 * Get Bank Account Type.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the bank_account_type is set or null if there is no bank_account_type.
		 */
		public function get_bank_account_type( $context = 'view' ) {
			return $this->get_prop( 'bank_account_type', $context );
		}

		/**
		 * Get Bank Name.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the bank_name is set or null if there is no bank_name.
		 */
		public function get_bank_name( $context = 'view' ) {
			return $this->get_prop( 'bank_name', $context );
		}

		/**
		 * Get iban.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the iban is set or null if there is no iban.
		 */
		public function get_iban( $context = 'view' ) {
			return $this->get_prop( 'iban', $context );
		}

		/**
		 * Get Swift.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the swift is set or null if there is no swift.
		 */
		public function get_swift( $context = 'view' ) {
			return $this->get_prop( 'swift', $context );
		}

		/**
		 * Get PayPal Email.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the paypal_email is set or null if there is no paypal_email.
		 */
		public function get_paypal_email( $context = 'view' ) {
			return $this->get_prop( 'paypal_email', $context );
		}

		/**
		 * Get Payment Type.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the payout_type is set or null if there is no payout_type.
		 */
		public function get_payout_type( $context = 'view' ) {
			return $this->get_prop( 'payout_type', $context );
		}

		/**
		 * Get Payment Scheduled.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the payout_schedule is set or null if there is no payout_schedule.
		 */
		public function get_payout_schedule( $context = 'view' ) {
			return $this->get_prop( 'payout_schedule', $context );
		}

		/**
		 * Get Withdraw from.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the withdraw_from is set or null if there is no withdraw_from.
		 */
		public function get_withdraw_from( $context = 'view' ) {
			return $this->get_prop( 'withdraw_from', $context );
		}

		/**
		 * Get Enable Withdrawal Charge.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the enable_withdraw_charge is set or null if there is no enable_withdraw_charge.
		 */
		public function get_enable_withdraw_charge( $context = 'view' ) {
			return $this->get_prop( 'enable_withdraw_charge', $context );
		}

		/**
		 * Get Withdrawal Charge Type.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the withdraw_charge_type is set or null if there is no withdraw_charge_type.
		 */
		public function get_withdraw_charge_type( $context = 'view' ) {
			return $this->get_prop( 'withdraw_charge_type', $context );
		}

		/**
		 * Get Withdrawal Charge Value.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the withdraw_charge_value is set or null if there is no withdraw_charge_value.
		 */
		public function get_withdraw_charge_value( $context = 'view' ) {
			return $this->get_prop( 'withdraw_charge_value', $context );
		}

		/**
		 * Get Enable Product Management.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the enable_product_management is set or null if there is no enable_product_management.
		 */
		public function get_enable_product_management( $context = 'view' ) {
			return $this->get_prop( 'enable_product_management', $context );
		}

		/**
		 * Get Instant Product Submission.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the product_creation is set or null if there is no product_creation.
		 */
		public function get_product_creation( $context = 'view' ) {
			return $this->get_prop( 'product_creation', $context );
		}

		/**
		 * Get Product Modification.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the product_modification is set or null if there is no product_modification.
		 */
		public function get_product_modification( $context = 'view' ) {
			return $this->get_prop( 'product_modification', $context );
		}

		/**
		 * Get Published Product Modification.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the published_product_modification is set or null if there is no published_product_modification.
		 */
		public function get_published_product_modification( $context = 'view' ) {
			return $this->get_prop( 'published_product_modification', $context );
		}

		/**
		 * Get Manage Inventory.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the manage_inventory is set or null if there is no manage_inventory.
		 */
		public function get_manage_inventory( $context = 'view' ) {
			return $this->get_prop( 'manage_inventory', $context );
		}

		/**
		 * Get Product Deletion.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the product_deletion is set or null if there is no product_deletion.
		 */
		public function get_product_deletion( $context = 'view' ) {
			return $this->get_prop( 'product_deletion', $context );
		}

		/**
		 * Get enable_order_management.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the enable_order_management is set or null if there is no enable_order_management.
		 */
		public function get_enable_order_management( $context = 'view' ) {
			return $this->get_prop( 'enable_order_management', $context );
		}

		/**
		 * Get Instagram.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the order_status_modification is set or null if there is no order_status_modification.
		 */
		public function get_order_status_modification( $context = 'view' ) {
			return $this->get_prop( 'order_status_modification', $context );
		}

		/**
		 * Get Commission Info Display.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the commission_info_display is set or null if there is no commission_info_display.
		 */
		public function get_commission_info_display( $context = 'view' ) {
			return $this->get_prop( 'commission_info_display', $context );
		}

		/**
		 * Get Enable Coupon Management.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the enable_coupon_management is set or null if there is no enable_coupon_management.
		 */
		public function get_enable_coupon_management( $context = 'view' ) {
			return $this->get_prop( 'enable_coupon_management', $context );
		}

		/**
		 * Get Coupon Creation.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the coupon_creation is set or null if there is no coupon_creation.
		 */
		public function get_coupon_creation( $context = 'view' ) {
			return $this->get_prop( 'coupon_creation', $context );
		}

		/**
		 * Get published Coupon Modification.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the published coupon modification is set or null if there is no published coupon modification.
		 */
		public function get_published_coupon_modification( $context = 'view' ) {
			return $this->get_prop( 'published_coupon_modification', $context );
		}

		/**
		 * Get Coupon Modification.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the coupon_modification is set or null if there is no coupon_modification.
		 */
		public function get_coupon_modification( $context = 'view' ) {
			return $this->get_prop( 'coupon_modification', $context );
		}

		/**
		 * Get Coupon Deletion.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the coupon deletion is set or null if there is no coupon deletion.
		 */
		public function get_coupon_deletion( $context = 'view' ) {
			return $this->get_prop( 'coupon_deletion', $context );
		}

		/**
		 * Get Enable Commission withdraw.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the enable commission withdraw is set or null if there is no enable commission withdraw.
		 */
		public function get_enable_commission_withdraw( $context = 'view' ) {
			return $this->get_prop( 'enable_commission_withdraw', $context );
		}

		/**
		 * Get Commission Transaction.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the commission transaction is set or null if there is no commission transaction.
		 */
		public function get_commission_transaction( $context = 'view' ) {
			return $this->get_prop( 'commission_transaction', $context );
		}

		/**
		 * Get Commission Transaction Info.
		 *
		 * @since 1.0.0
		 * @param String $context View or edit context.
		 * @return String|NULL String if the commission transaction info is set or null if there is no commission transaction info.
		 */
		public function get_commission_transaction_info( $context = 'view' ) {
			return $this->get_prop( 'commission_transaction_info', $context );
		}

		/**
		 * Return the vendor statuses without mvr- internal prefix.
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
				$status = apply_filters( 'mvr_default_vendor_status', 'pending' );
			}

			return $status;
		}

		/*
		|--------------------------------------------------------------------------
		| Setters
		|--------------------------------------------------------------------------
		|
		| Functions for setting vendor data. These should not update anything in the
		| database itself and should only change what is stored in the class
		| object.
		 */

		/**
		 * Set status.
		 *
		 * @since 1.0.0
		 * @param String  $new_status Status to change the vendor to. No internal mvr- prefix is required.
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
						 * When vendor status has been manually edited.
						 *
						 * @since 1.0
						 */
						do_action( 'mvr_vendor_edit_status', $this->get_id(), $new_status );
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
		 * Set User ID.
		 *
		 * @since 1.0.0
		 * @param Integer $value Value to set.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_user_id( $value ) {
			$this->set_prop( 'user_id', $value );
		}

		/**
		 * Set amount.
		 *
		 * @since 1.0.0
		 * @param Integer $value Value to set.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_amount( $value ) {
			$this->set_prop( 'amount', $value );
		}

		/**
		 * Set locked amount.
		 *
		 * @since 1.0.0
		 * @param Integer $value Value to set.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_locked_amount( $value ) {
			$this->set_prop( 'locked_amount', $value );
		}

		/**
		 * Set sold amount.
		 *
		 * @since 1.0.0
		 * @param Integer $value Value to set.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_sold_amount( $value ) {
			$this->set_prop( 'sold_amount', $value );
		}

		/**
		 * Set Vendor Logo ID.
		 *
		 * @since 1.0.0
		 * @param Integer $value Value to set.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_logo_id( $value ) {
			$this->set_prop( 'logo_id', $value );
		}

		/**
		 * Set Vendor Banner ID.
		 *
		 * @since 1.0.0
		 * @param Integer $value Value to set.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_banner_id( $value ) {
			$this->set_prop( 'banner_id', $value );
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

		/**
		 * Set Vendor Terms and Conditions.
		 *
		 * @since 1.0.0
		 * @param String $value Shop Name.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_tac( $value = null ) {
			$this->set_prop( 'tac', $value );
		}

		/**
		 * Set Shop Name.
		 *
		 * @since 1.0.0
		 * @param String $value Shop Name.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_shop_name( $value = null ) {
			$this->set_prop( 'shop_name', $value );
		}

		/**
		 * Set slug.
		 *
		 * @since 1.0.0
		 * @param String $value slug.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_slug( $value = null ) {
			$this->set_prop( 'slug', $value );
		}

		/**
		 * Set description.
		 *
		 * @since 1.0.0
		 * @param String $value description.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_description( $value = null ) {
			$this->set_prop( 'description', $value );
		}

		/**
		 * Set vendor name.
		 *
		 * @since 1.0.0
		 * @param String $value name.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_name( $value = null ) {
			$this->set_prop( 'name', $value );
		}

		/**
		 * Set email.
		 *
		 * @since 1.0.0
		 * @param String $value email.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_email( $value = null ) {
			$this->set_prop( 'email', $value );
		}

		/**
		 * Set first name.
		 *
		 * @since 1.0.0
		 * @param String $value first name.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_first_name( $value = null ) {
			$this->set_prop( 'first_name', $value );
		}

		/**
		 * Set last_name.
		 *
		 * @since 1.0.0
		 * @param String $value last name.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_last_name( $value = null ) {
			$this->set_prop( 'last_name', $value );
		}

		/**
		 * Set door number.
		 *
		 * @since 1.0.0
		 * @param String $value door number.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_address1( $value = null ) {
			$this->set_prop( 'address1', $value );
		}

		/**
		 * Set address2.
		 *
		 * @since 1.0.0
		 * @param String $value address2.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_address2( $value = null ) {
			$this->set_prop( 'address2', $value );
		}

		/**
		 * Set city.
		 *
		 * @since 1.0.0
		 * @param String $value city.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_city( $value = null ) {
			$this->set_prop( 'city', $value );
		}

		/**
		 * Set state.
		 *
		 * @since 1.0.0
		 * @param String $value state.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_state( $value = null ) {
			$this->set_prop( 'state', $value );
		}

		/**
		 * Set country.
		 *
		 * @since 1.0.0
		 * @param String $value country.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_country( $value = null ) {
			$this->set_prop( 'country', $value );
		}

		/**
		 * Set zip_code.
		 *
		 * @since 1.0.0
		 * @param String $value zip_code.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_zip_code( $value = null ) {
			$this->set_prop( 'zip_code', $value );
		}

		/**
		 * Set phone.
		 *
		 * @since 1.0.0
		 * @param String $value phone.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_phone( $value = null ) {
			$this->set_prop( 'phone', $value );
		}

		/**
		 * Set Shop Name.
		 *
		 * @since 1.0.0
		 * @param String $value facebook.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_facebook( $value = null ) {
			$this->set_prop( 'facebook', $value );
		}

		/**
		 * Set twitter.
		 *
		 * @since 1.0.0
		 * @param String $value twitter.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_twitter( $value = null ) {
			$this->set_prop( 'twitter', $value );
		}

		/**
		 * Set youtube.
		 *
		 * @since 1.0.0
		 * @param String $value youtube.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_youtube( $value = null ) {
			$this->set_prop( 'youtube', $value );
		}

		/**
		 * Set instagram.
		 *
		 * @since 1.0.0
		 * @param String $value instagram.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_instagram( $value = null ) {
			$this->set_prop( 'instagram', $value );
		}

		/**
		 * Set linkedin.
		 *
		 * @since 1.0.0
		 * @param String $value linkedin.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_linkedin( $value = null ) {
			$this->set_prop( 'linkedin', $value );
		}

		/**
		 * Set pinterest.
		 *
		 * @since 1.0.0
		 * @param String $value pinterest.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_pinterest( $value = null ) {
			$this->set_prop( 'pinterest', $value );
		}

		/**
		 * Set commission from.
		 *
		 * @since 1.0.0
		 * @param String $value commission from.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_commission_from( $value = null ) {
			$this->set_prop( 'commission_from', $value );
		}

		/**
		 * Set commission type.
		 *
		 * @since 1.0.0
		 * @param String $value commission type.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_commission_type( $value = null ) {
			$this->set_prop( 'commission_type', $value );
		}

		/**
		 * Set commission_criteria.
		 *
		 * @since 1.0.0
		 * @param String $value commission_criteria.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_commission_criteria( $value = null ) {
			$this->set_prop( 'commission_criteria', $value );
		}

		/**
		 * Set commission criteria Value.
		 *
		 * @since 1.0.0
		 * @param String $value commission_criteria_value.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_commission_criteria_value( $value = null ) {
			$this->set_prop( 'commission_criteria_value', $value );
		}

		/**
		 * Set Commission value.
		 *
		 * @since 1.0.0
		 * @param String $value commission value.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_commission_value( $value = null ) {
			$this->set_prop( 'commission_value', $value );
		}

		/**
		 * Set tax to.
		 *
		 * @since 1.0.0
		 * @param String $value tax_to.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_tax_to( $value = null ) {
			$this->set_prop( 'tax_to', $value );
		}

		/**
		 * Set commission after admin coupon.
		 *
		 * @since 1.0.0
		 * @param String $value commission after vendor coupon.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_commission_after_coupon( $value = null ) {
			$this->set_prop( 'commission_after_coupon', $value );
		}

		/**
		 * Set commission after vendor coupon.
		 *
		 * @since 1.0.0
		 * @param String $value commission after vendor coupon.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_commission_after_vendor_coupon( $value = null ) {
			$this->set_prop( 'commission_after_vendor_coupon', $value );
		}

		/**
		 * Set Payment Method.
		 *
		 * @since 1.0.0
		 * @param String $value Shop Name.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_payment_method( $value = null ) {
			$this->set_prop( 'payment_method', $value );
		}

		/**
		 * Set bank account name.
		 *
		 * @since 1.0.0
		 * @param String $value Shop Name.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_bank_account_name( $value = null ) {
			$this->set_prop( 'bank_account_name', $value );
		}

		/**
		 * Set bank account number.
		 *
		 * @since 1.0.0
		 * @param String $value bank account number.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_bank_account_number( $value = null ) {
			$this->set_prop( 'bank_account_number', $value );
		}

		/**
		 * Set bank account type.
		 *
		 * @since 1.0.0
		 * @param String $value bank_account_type.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_bank_account_type( $value = null ) {
			$this->set_prop( 'bank_account_type', $value );
		}

		/**
		 * Set bank name.
		 *
		 * @since 1.0.0
		 * @param String $value bank name.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_bank_name( $value = null ) {
			$this->set_prop( 'bank_name', $value );
		}

		/**
		 * Set iban.
		 *
		 * @since 1.0.0
		 * @param String $value iban.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_iban( $value = null ) {
			$this->set_prop( 'iban', $value );
		}

		/**
		 * Set swift.
		 *
		 * @since 1.0.0
		 * @param String $value swift.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_swift( $value = null ) {
			$this->set_prop( 'swift', $value );
		}

		/**
		 * Set paypal Email.
		 *
		 * @since 1.0.0
		 * @param String $value Shop Name.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_paypal_email( $value = null ) {
			$this->set_prop( 'paypal_email', $value );
		}

		/**
		 * Set Payout type.
		 *
		 * @since 1.0.0
		 * @param String $value payment type.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_payout_type( $value = null ) {
			$this->set_prop( 'payout_type', $value );
		}

		/**
		 * Set payment scheduled.
		 *
		 * @since 1.0.0
		 * @param String $value payment scheduled.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_payout_schedule( $value = null ) {
			$this->set_prop( 'payout_schedule', $value );
		}

		/**
		 * Set withdraw from.
		 *
		 * @since 1.0.0
		 * @param String $value enable withdraw.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_withdraw_from( $value = null ) {
			$this->set_prop( 'withdraw_from', $value );
		}

		/**
		 * Set enable withdraw.
		 *
		 * @since 1.0.0
		 * @param String $value enable withdraw.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_enable_withdraw_charge( $value = null ) {
			$this->set_prop( 'enable_withdraw_charge', $value );
		}

		/**
		 * Set withdraw type.
		 *
		 * @since 1.0.0
		 * @param String $value withdraw type.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_withdraw_charge_type( $value = null ) {
			$this->set_prop( 'withdraw_charge_type', $value );
		}

		/**
		 * Set withdraw value.
		 *
		 * @since 1.0.0
		 * @param String $value withdraw value.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_withdraw_charge_value( $value = null ) {
			$this->set_prop( 'withdraw_charge_value', $value );
		}

		/**
		 * Set enable product management.
		 *
		 * @since 1.0.0
		 * @param String $value enable product management.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_enable_product_management( $value = null ) {
			$this->set_prop( 'enable_product_management', $value );
		}

		/**
		 * Set instant product submission.
		 *
		 * @since 1.0.0
		 * @param String $value instant product submission.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_product_creation( $value = null ) {
			$this->set_prop( 'product_creation', $value );
		}

		/**
		 * Set product modification.
		 *
		 * @since 1.0.0
		 * @param String $value product modification.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_product_modification( $value = null ) {
			$this->set_prop( 'product_modification', $value );
		}

		/**
		 * Set published product modification.
		 *
		 * @since 1.0.0
		 * @param String $value published product modification.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_published_product_modification( $value = null ) {
			$this->set_prop( 'published_product_modification', $value );
		}

		/**
		 * Set manage inventory.
		 *
		 * @since 1.0.0
		 * @param String $value manage inventory.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_manage_inventory( $value = null ) {
			$this->set_prop( 'manage_inventory', $value );
		}

		/**
		 * Set product deletion.
		 *
		 * @since 1.0.0
		 * @param String $value instant product submission.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_product_deletion( $value = null ) {
			$this->set_prop( 'product_deletion', $value );
		}

		/**
		 * Set enable order management.
		 *
		 * @since 1.0.0
		 * @param String $value enable order management.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_enable_order_management( $value = null ) {
			$this->set_prop( 'enable_order_management', $value );
		}

		/**
		 * Set Order Status Modification.
		 *
		 * @since 1.0.0
		 * @param String $value Order Status Modification.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_order_status_modification( $value = null ) {
			$this->set_prop( 'order_status_modification', $value );
		}

		/**
		 * Set Commission Info Display.
		 *
		 * @since 1.0.0
		 * @param String $value Shop Name.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_commission_info_display( $value = null ) {
			$this->set_prop( 'commission_info_display', $value );
		}

		/**
		 * Set enable coupon management.
		 *
		 * @since 1.0.0
		 * @param String $value enable coupon management.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_enable_coupon_management( $value = null ) {
			$this->set_prop( 'enable_coupon_management', $value );
		}

		/**
		 * Set coupon creation.
		 *
		 * @since 1.0.0
		 * @param String $value coupon creation.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_coupon_creation( $value = null ) {
			$this->set_prop( 'coupon_creation', $value );
		}

		/**
		 * Set published Coupon modification.
		 *
		 * @since 1.0.0
		 * @param String $value published coupon modification.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_published_coupon_modification( $value = null ) {
			$this->set_prop( 'published_coupon_modification', $value );
		}

		/**
		 * Set Coupon Modification.
		 *
		 * @since 1.0.0
		 * @param String $value coupon modification.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_coupon_modification( $value = null ) {
			$this->set_prop( 'coupon_modification', $value );
		}

		/**
		 * Set Coupon Deletion.
		 *
		 * @since 1.0.0
		 * @param String $value coupon deletion.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_coupon_deletion( $value = null ) {
			$this->set_prop( 'coupon_deletion', $value );
		}

		/**
		 * Set Enable Commission Withdraw.
		 *
		 * @since 1.0.0
		 * @param String $value enable commission withdraw.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_enable_commission_withdraw( $value = null ) {
			$this->set_prop( 'enable_commission_withdraw', $value );
		}

		/**
		 * Set Commission Transaction.
		 *
		 * @since 1.0.0
		 * @param String $value Commission Transaction.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_commission_transaction( $value = null ) {
			$this->set_prop( 'commission_transaction', $value );
		}

		/**
		 * Set Commission Transaction Information.
		 *
		 * @since 1.0.0
		 * @param String $value commission transaction info.
		 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
		 */
		public function set_commission_transaction_info( $value = null ) {
			$this->set_prop( 'commission_transaction_info', $value );
		}
	}
}
