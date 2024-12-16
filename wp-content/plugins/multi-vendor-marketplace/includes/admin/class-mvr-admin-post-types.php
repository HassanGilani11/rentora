<?php
/**
 * Admin Assets.
 *
 * @package Multi-Vendor for WooCommerce
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Admin_Post_Types' ) ) {

	/**
	 * Post Types Admin
	 *
	 * @package Class
	 */
	class MVR_Admin_Post_Types {

		/**
		 * Get our post types.
		 *
		 * @var Array
		 */
		protected static $custom_post_types = array(
			MVR_Post_Types::MVR_VENDOR => 'vendor',
			MVR_Post_Types::MVR_STAFF  => 'staff',
		);

		/**
		 * Constructor.
		 */
		public function __construct() {
			// Load correct list table classes for current screen.
			add_action( 'current_screen', array( $this, 'setup_screen' ) );
			add_action( 'check_ajax_referer', array( $this, 'setup_screen' ) );

			add_filter( 'wp_untrash_post_status', array( $this, 'wp_untrash_post_status' ), 10, 3 );
			add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ), 99 );
			add_filter( 'bulk_post_updated_messages', array( $this, 'bulk_post_updated_messages' ), 10, 2 );
			add_filter( 'enter_title_here', array( $this, 'enter_title_here' ), 1, 2 );

			add_action( 'admin_init', array( $this, 'admin_action' ) );
			add_action( 'restrict_manage_posts', array( $this, 'restrict_manage_posts' ) );
			// add_action( 'woocommerce_order_list_table_restrict_manage_orders', array( $this, 'restrict_manage_posts' ) );
			add_action( 'pre_get_posts', array( $this, 'query_filters' ) );
			add_filter( 'woocommerce_order_list_table_prepare_items_query_args', array( $this, 'order_query_filters' ) );

			// Add a post display state for special MVR pages.
			add_filter( 'display_post_states', array( $this, 'add_display_post_states' ), 10, 2 );

			// Quick edit and bulk edit.
			add_action( 'woocommerce_product_quick_edit_end', array( $this, 'quick_edit_vendor_selection' ) );
			add_action( 'woocommerce_product_bulk_edit_end', array( $this, 'quick_edit_vendor_selection' ) );
			add_action( 'manage_product_posts_custom_column', array( $this, 'vendor_quick_edit_data' ), 99, 2 );
			add_action( 'woocommerce_product_bulk_and_quick_edit', array( $this, 'bulk_and_quick_edit_save_post' ), 10, 2 );

			// Before Delete Post.
			add_action( 'before_delete_post', array( $this, 'before_delete_post' ) );
			add_action( 'admin_footer', array( $this, 'add_admin_templates' ) );

			add_filter( 'manage_edit-product_columns', __CLASS__ . '::product_vendor_column', 9999 );
			add_filter( 'manage_edit-shop_coupon_columns', __CLASS__ . '::coupon_vendor_column', 9999 );
			add_filter( 'manage_edit-shop_order_columns', __CLASS__ . '::order_vendor_column', 9999 );
			add_filter( 'woocommerce_shop_order_list_table_columns', __CLASS__ . '::order_vendor_column', 9999 );

			add_action( 'manage_product_posts_custom_column', __CLASS__ . '::product_vendor_column_value', 10, 2 );
			add_action( 'manage_shop_coupon_posts_custom_column', __CLASS__ . '::coupon_vendor_column_value', 10, 2 );
			add_action( 'manage_shop_order_posts_custom_column', __CLASS__ . '::order_vendor_column_value', 10, 2 );
			add_action( 'woocommerce_shop_order_list_table_custom_column', __CLASS__ . '::order_vendor_column_value', 10, 2 );
		}

		/**
		 * Admin Templates
		 *
		 * @since 1.0.0
		 */
		public function add_admin_templates() {
			$this->add_vendor_template();
			$this->pay_vendor_template();
			$this->add_staff_template();
		}

		/**
		 * Staff Template
		 *
		 * @since 1.0.0
		 */
		public function add_staff_template() {
			global $pagenow;

			$post_type = isset( $_GET['post'] ) ? get_post_type( absint( wp_unslash( $_GET['post'] ) ) ) : '';

			if ( 'post.php' === $pagenow && 'mvr_staff' !== $post_type ) {
				return;
			}

			$post_type = isset( $_GET['post_type'] ) ? wc_clean( wp_unslash( $_GET['post_type'] ) ) : '';

			if ( 'edit.php' === $pagenow && 'mvr_staff' !== $post_type ) {
				return;
			}
			?>
			<script type="text/template" id="tmpl-mvr-modal-add-staff">
				<?php include_once MVR_ABSPATH . 'includes/admin/views/html-admin-add-staff.php'; ?>
			</script>
			<?php
		}

		/**
		 * Vendor Template
		 *
		 * @since 1.0.0
		 */
		public function add_vendor_template() {
			global $pagenow;

			$post_type = isset( $_GET['post'] ) ? get_post_type( absint( wp_unslash( $_GET['post'] ) ) ) : '';

			if ( 'post.php' === $pagenow && 'mvr_vendor' !== $post_type ) {
				return;
			}

			$post_type = isset( $_GET['post_type'] ) ? wc_clean( wp_unslash( $_GET['post_type'] ) ) : '';

			if ( 'edit.php' === $pagenow && 'mvr_vendor' !== $post_type ) {
				return;
			}
			?>
			<script type="text/template" id="tmpl-mvr-modal-add-vendor">
				<?php include_once MVR_ABSPATH . 'includes/admin/views/html-admin-add-vendor.php'; ?>
			</script>
			<?php
		}

		/**
		 * Vendor Template
		 *
		 * @since 1.0.0
		 */
		public function pay_vendor_template() {
			global $pagenow;

			$post_type = isset( $_GET['post_type'] ) ? wc_clean( wp_unslash( $_GET['post_type'] ) ) : '';

			if ( 'edit.php' === $pagenow && 'mvr_vendor' !== $post_type ) {
				return;
			}
			?>
			<script type="text/template" id="tmpl-mvr-modal-pay-vendor">
				<?php include_once MVR_ABSPATH . 'includes/admin/views/html-admin-pay-vendor.php'; ?>
			</script>
			<?php
		}

		/**
		 * Looks at the current screen and loads the correct list table handler.
		 *
		 * @since 1.0.0
		 */
		public function setup_screen() {
			global $mvr_list_table;

			$request_data = $this->request_data();
			$screen_id    = false;

			if ( function_exists( 'get_current_screen' ) ) {
				$screen    = get_current_screen();
				$screen_id = isset( $screen, $screen->id ) ? $screen->id : '';
			}

			if ( ! empty( $request_data['screen'] ) ) {
				$screen_id = wc_clean( wp_unslash( $request_data['screen'] ) );
			}

			switch ( $screen_id ) {
				case 'edit-' . MVR_Post_Types::MVR_VENDOR:
					include_once __DIR__ . '/list-tables/class-mvr-admin-list-table-vendors.php';
					$mvr_list_table = new MVR_Admin_List_Table_Vendors();
					break;
				case 'edit-' . MVR_Post_Types::MVR_STAFF:
					include_once __DIR__ . '/list-tables/class-mvr-admin-list-table-staffs.php';
					$mvr_list_table = new MVR_Admin_List_Table_Staffs();
					break;
			}

			// Ensure the table handler is only loaded once. Prevents multiple loads if a plugin calls check_ajax_referer many times.
			remove_action( 'current_screen', __CLASS__ . '::setup_screen' );
			remove_action( 'check_ajax_referer', __CLASS__ . '::setup_screen' );
		}

		/**
		 * Ensure statuses are correctly reassigned when restoring our posts.
		 *
		 * @since 1.0.0
		 * @param String  $new_status      The new status of the post being restored.
		 * @param Integer $post_id         The ID of the post being restored.
		 * @param String  $previous_status The status of the post at the point where it was trashed.
		 * @return String
		 */
		public function wp_untrash_post_status( $new_status, $post_id, $previous_status ) {
			if ( in_array( get_post_type( $post_id ), array( MVR_Post_Types::MVR_VENDOR ), true ) ) {
				$new_status = $previous_status;
			}

			return $new_status;
		}

		/**
		 * Change messages when a post type is updated.
		 *
		 * @since 1.0.0
		 * @param Array $messages Array of messages.
		 * @return Array
		 */
		public function post_updated_messages( $messages ) {
			$messages[ MVR_Post_Types::MVR_VENDOR ] = array(
				0 => '', // Unused. Messages start at index 1.
				1 => __( 'Vendor Updated', 'multi-vendor-marketplace' ),
				4 => __( 'Vendor Updated', 'multi-vendor-marketplace' ),
				6 => __( 'Vendor Updated', 'multi-vendor-marketplace' ),
				7 => __( 'Vendor Saved', 'multi-vendor-marketplace' ),
				8 => __( 'Vendor Submitted', 'multi-vendor-marketplace' ),
			);

			$messages[ MVR_Post_Types::MVR_STAFF ] = array(
				0 => '', // Unused. Messages start at index 1.
				1 => __( 'Staff Updated', 'multi-vendor-marketplace' ),
				4 => __( 'Staff Updated', 'multi-vendor-marketplace' ),
				6 => __( 'Staff Updated', 'multi-vendor-marketplace' ),
				7 => __( 'Staff Saved', 'multi-vendor-marketplace' ),
				8 => __( 'Staff Submitted', 'multi-vendor-marketplace' ),
			);

			if ( mvr_check_user_as_vendor_or_staff() ) {
				if ( isset( $messages['product'] ) ) {
					$messages['product'][1]  = __( 'Product Updated', 'multi-vendor-marketplace' );
					$messages['product'][6]  = __( 'Product Published', 'multi-vendor-marketplace' );
					$messages['product'][8]  = __( 'Product Submitted', 'multi-vendor-marketplace' );
					$messages['product'][10] = __( 'Product Draft Updated', 'multi-vendor-marketplace' );
				}
			}

			return $messages;
		}

		/**
		 * Specify custom bulk actions messages for different post types.
		 *
		 * @since 1.0.0
		 * @param  Array $bulk_messages Array of messages.
		 * @param  Array $bulk_counts Array of how many objects were updated.
		 * @return Array
		 */
		public function bulk_post_updated_messages( $bulk_messages, $bulk_counts ) {
			$bulk_messages[ MVR_Post_Types::MVR_VENDOR ] = array(
				/* translators: %s: Vendor count */
				'updated'   => _n( '%s vendor updated.', '%s vendors updated.', $bulk_counts['updated'], 'multi-vendor-marketplace' ),
				/* translators: %s: vendor count */
				'locked'    => _n( '%s vendor not updated, somebody is editing it.', '%s vendors not updated, somebody is editing them.', $bulk_counts['locked'], 'multi-vendor-marketplace' ),
				/* translators: %s: vendor count */
				'deleted'   => _n( '%s vendor permanently deleted.', '%s vendors permanently deleted.', $bulk_counts['deleted'], 'multi-vendor-marketplace' ),
				/* translators: %s: vendor count */
				'trashed'   => _n( '%s vendor moved to the Trash.', '%s vendors moved to the Trash.', $bulk_counts['trashed'], 'multi-vendor-marketplace' ),
				/* translators: %s: vendor count */
				'untrashed' => _n( '%s vendor restored from the Trash.', '%s vendors restored from the Trash.', $bulk_counts['untrashed'], 'multi-vendor-marketplace' ),
			);

			$bulk_messages[ MVR_Post_Types::MVR_STAFF ] = array(
				/* translators: %s: Vendor count */
				'updated'   => _n( '%s staff updated.', '%s staff updated.', $bulk_counts['updated'], 'multi-vendor-marketplace' ),
				/* translators: %s: vendor count */
				'locked'    => _n( '%s staff not updated, somebody is editing it.', '%s staff not updated, somebody is editing them.', $bulk_counts['locked'], 'multi-vendor-marketplace' ),
				/* translators: %s: vendor count */
				'deleted'   => _n( '%s staff permanently deleted.', '%s staff permanently deleted.', $bulk_counts['deleted'], 'multi-vendor-marketplace' ),
				/* translators: %s: vendor count */
				'trashed'   => _n( '%s staff moved to the Trash.', '%s staff moved to the Trash.', $bulk_counts['trashed'], 'multi-vendor-marketplace' ),
				/* translators: %s: vendor count */
				'untrashed' => _n( '%s staff restored from the Trash.', '%s staff restored from the Trash.', $bulk_counts['untrashed'], 'multi-vendor-marketplace' ),
			);

			return $bulk_messages;
		}

		/**
		 * Change title boxes in admin.
		 *
		 * @since 1.0.0
		 * @param String  $text Text to shown.
		 * @param WP_Post $post Current post object.
		 * @return String
		 */
		public function enter_title_here( $text, $post ) {
			if ( MVR_Post_Types::MVR_VENDOR === $post->post_type ) {
				$text = esc_html__( 'Vendor Name', 'multi-vendor-marketplace' );
			}

			return $text;
		}

		/**
		 * Fire our actions perfomed in admin screen.
		 *
		 * @since 1.0.0
		 */
		public function admin_action() {
			if ( ! isset( $_GET['action'] ) || ! isset( $_GET['_wpnonce'] ) || ! isset( $_GET['post'] ) ) {
				return;
			}

			$action  = sanitize_text_field( wp_unslash( $_GET['action'] ) );
			$nonce   = sanitize_key( wp_unslash( $_GET['_wpnonce'] ) );
			$post_id = absint( wp_unslash( $_GET['post'] ) );

			if ( ! wp_verify_nonce( $nonce, "mvr-{$action}-post-{$post_id}" ) ) {
				return;
			}

			$report_action = '';

			if ( in_array( $action, array( 'make_payment', 'mark_paid', 'reject_withdraw' ) ) ) {
				$redirect     = mvr_get_withdraw_page_url();
				$withdraw_obj = mvr_get_withdraw( $post_id );

				if ( ! mvr_is_withdraw( $withdraw_obj ) ) {
					MVR_Admin::add_error( __( 'Invalid withdraw request.', 'multi-vendor-marketplace' ) );
					wp_safe_redirect( $redirect );
					exit;
				}

				$vendor_obj = $withdraw_obj->get_vendor();

				if ( ! mvr_is_vendor( $vendor_obj ) ) {
					MVR_Admin::add_error( __( 'Invalid Vendor.', 'multi-vendor-marketplace' ) );
					wp_safe_redirect( $redirect );
					exit;
				}

				$transactions_obj = mvr_get_transactions(
					array(
						'vendor_id'   => $vendor_obj->get_id(),
						'source_id'   => $withdraw_obj->get_id(),
						'source_from' => 'withdraw',
					)
				);

				$transaction_obj = current( $transactions_obj->transactions );
			}

			switch ( $action ) {
				case 'make_payment':
					if ( 'yes' !== get_option( 'mvr_settings_enable_paypal_payouts', 'no' ) ) {
						wp_safe_redirect( $redirect );
						exit;
					}

					$report_action  = 'Payout Payment';
					$receiver_email = sanitize_email( $vendor_obj->get_paypal_email() );

					if ( empty( $receiver_email ) ) {
						MVR_Admin::add_error( __( 'Invalid PayPal Email.', 'multi-vendor-marketplace' ) );
						wp_safe_redirect( $redirect );
						exit;
					}

					$items = array(
						array(
							'recipient_type' => 'EMAIL',
							'receiver'       => $receiver_email,
							'note'           => __( 'Payout received.', 'multi-vendor-marketplace' ),
							'sender_item_id' => $vendor_obj->get_id(),
							'amount'         => array(
								'value'    => wc_format_decimal( $withdraw_obj->get_amount(), wc_get_price_decimals() ),
								'currency' => get_woocommerce_currency(),
							),
						),
					);

					$args = array(
						$vendor_obj->get_id() => array(
							'vendor_id'   => $vendor_obj->get_id(),
							'source_id'   => $withdraw_obj->get_id(),
							'source_from' => 'withdraw',
							'amount'      => (float) $withdraw_obj->get_amount(),
							'charge'      => 0,
							'schedule'    => '0',
							'receiver'    => $receiver_email,
						),
					);

					$payout_batch_obj = new MVR_Payout_Batch();
					$payout_batch_obj->set_props(
						array(
							'items'           => $items,
							'email_subject'   => __( 'Payout Received Successful', 'multi-vendor-marketplace' ),
							'email_message'   => __( 'You have received a payout! Thanks!!', 'multi-vendor-marketplace' ),
							'additional_data' => $args,
						)
					);
					$payout_batch_obj->save();
					$payout_batch_obj->add_note( __( 'New Payout entry created.', 'multi-vendor-marketplace' ) );

					if ( mvr_is_payout_batch( $payout_batch_obj ) ) {
						$withdraw_obj->update_status( 'progress' );

						$response = MVR_PayPal_Payouts_Helper::create_batch_payout(
							array(
								'sender_batch_header' => array(
									'sender_batch_id' => $payout_batch_obj->get_id(),
									'email_subject'   => $payout_batch_obj->get_email_subject(),
									'email_message'   => $payout_batch_obj->get_email_message(),
								),
								'items'               => $payout_batch_obj->get_items(),
							)
						);

						if ( is_wp_error( $response ) ) {
							$payout_batch_obj->add_note( $response->get_error_message() );

							MVR_Admin::add_error( $response->get_error_message() );
							wp_safe_redirect( $redirect );
							exit;
						} elseif ( is_object( $response ) ) {
							$batch_id = $response->batch_header->payout_batch_id;

							if ( ! empty( $batch_id ) ) {
								$param = array(
									'batch_id'         => $batch_id,
									'payout_batch_obj' => $payout_batch_obj,
									'response'         => $response,
									'payouts_data'     => $args,
								);
								/**
								 * PayPal Batch Payout Completed.
								 *
								 * @since 1.0.0
								 * */
								do_action( 'mvr_after_paypal_payout_batch_completed', $param );

								/**
								 * Bank Transfer completed Hook
								 *
								 * @since 1.0.0
								 * */
								do_action( 'mvr_after_paypal_payout_completed', $withdraw_obj, $vendor_obj );
							}

							MVR_Admin::add_success( __( 'Withdrawal payout was completed successfully.', 'multi-vendor-marketplace' ) );
						}
					}

					break;
				case 'mark_paid':
					$report_action = 'Paid';

					/**
					 * Bank Transfer completed Hook
					 *
					 * @since 1.0.0
					 * */
					do_action( 'mvr_after_bank_transfer_completed', $withdraw_obj, $vendor_obj, '' );

					$withdraw_obj->update_status( 'success' );

					if ( mvr_is_transaction( $transaction_obj ) ) {
						$transaction_obj->update_status( 'completed' );
					}

					MVR_Admin::add_success( __( 'Withdraw request marked as paid.', 'multi-vendor-marketplace' ) );
					break;
				case 'reject_withdraw':
					$report_action = 'Withdraw Rejected';

					$withdraw_obj->update_status( 'failed' );
					$vendor_obj->update_amount( $withdraw_obj->get_amount() + $withdraw_obj->get_charge_amount() );

					if ( mvr_is_transaction( $transaction_obj ) ) {
						$transaction_obj->update_status( 'failed' );
					}

					MVR_Admin::add_success( __( 'Withdraw request was rejected.', 'multi-vendor-marketplace' ) );

					/**
					 * After Rejected Withdraw Request.
					 *
					 * @since 1.0.0
					 * */
					do_action( 'mvr_after_rejected_withdraw_request', $withdraw_obj, $vendor_obj );
					break;
				case 'check_payout_status':
					$payout_batch_obj = mvr_get_payout_batch( $post_id );
					$report_action    = 'Payout Action Checked';

					if ( mvr_is_payout_batch( $payout_batch_obj ) ) {
						$details      = MVR_PayPal_Payouts_Helper::get_payout_batch_details( $payout_batch_obj->get_batch_id() );
						$payout_items = $payout_batch_obj->get_items();

						foreach ( $details->items as $key => $item_obj ) {
							if ( ! is_object( $item_obj ) ) {
								continue;
							}

							$payout_items[ $item_obj->payout_item->receiver ] = array(
								'payout_item_id'     => $item_obj->payout_item_id,
								'sender_item_id'     => $item_obj->payout_item->sender_item_id,
								'transaction_status' => $item_obj->transaction_status,
								'receiver'           => $item_obj->payout_item->receiver,
								'fee'                => array(
									'currency' => $item_obj->payout_item_fee->currency,
									'value'    => $item_obj->payout_item_fee->value,
								),
								'amount'             => array(
									'currency' => $item_obj->payout_item->amount->currency,
									'value'    => $item_obj->payout_item->amount->value,
								),
								'note'               => $item_obj->payout_item->note,
							);
						}

						$payout_batch_obj->set_items( $payout_items );
						$payout_batch_obj->save();
						$payout_batch_obj->add_note( __( 'PayPal Batch Item Updated.', 'multi-vendor-marketplace' ) );
					}

					$redirect = $payout_batch_obj->get_admin_edit_url();

					/**
					 * After Rejected Withdraw Request.
					 *
					 * @since 1.0.0
					 * */
					do_action( 'mvr_after_payout_batch_request_updated', $payout_batch_obj );
					break;
				case 'check_payout_batch_status':
					$payout_batch_obj = mvr_get_payout_batch( $post_id );

					if ( 'pending' !== $payout_batch_obj->get_status() ) {
						return;
					}

					$response = MVR_PayPal_Payouts_Helper::create_batch_payout(
						array(
							'sender_batch_header' => array(
								'sender_batch_id' => $payout_batch_obj->get_id(),
								'email_subject'   => $payout_batch_obj->get_email_subject(),
								'email_message'   => $payout_batch_obj->get_email_message(),
							),
							'items'               => $payout_batch_obj->get_items(),
						)
					);

					if ( is_wp_error( $response ) ) {
						$payout_batch_obj->add_note( $response->get_error_message() );
						MVR_Admin::add_error( $response->get_error_message() );
					} elseif ( is_object( $response ) ) {
						$batch_id = $response->batch_header->payout_batch_id;

						if ( ! empty( $batch_id ) ) {
							$param = array(
								'batch_id'         => $batch_id,
								'payout_batch_obj' => $payout_batch_obj,
								'response'         => $response,
								'payouts_data'     => $payout_batch_obj->get_additional_data(),
							);

							MVR_Admin::add_success( __( 'Payout batch id was updated successfully.', 'multi-vendor-marketplace' ) );

							/**
							 * PayPal Batch Payout Completed.
							 *
							 * @since 1.0.0
							 * */
							do_action( 'mvr_after_paypal_payout_batch_completed', $param );
						}
					}

					$page     = isset( $_GET['paged'] ) ? absint( wp_unslash( $_GET['paged'] ) ) : 1;
					$redirect = mvr_get_settings_page_url(
						array(
							'tab'   => 'payment',
							'paged' => $page,
						)
					);

					break;
			}

			if ( $report_action ) {
				$redirect = add_query_arg( 'row_action', $report_action, $redirect );
			}

			wp_safe_redirect( $redirect );
			exit;
		}

		/**
		 * See if we should render search filters or not.
		 *
		 * @since 1.0.0
		 * @param String $post_type Post Type.
		 */
		public function restrict_manage_posts( $post_type ) {
			if ( 'product' === $post_type || 'shop_order' === $post_type ) {
				$vendor_id = ! empty( $_REQUEST['_mvr_vendor'] ) ? absint( wp_unslash( $_REQUEST['_mvr_vendor'] ) ) : '';
				?>
					<span class="mvr-select2-wrap">
						<?php
						mvr_select2_html(
							array(
								'id'          => '_mvr_vendor',
								'class'       => 'wc-product-search',
								'placeholder' => esc_html__( 'All Vendor(s)', 'multi-vendor-marketplace' ),
								'options'     => $vendor_id,
								'type'        => 'vendor',
								'action'      => 'mvr_json_search_vendors',
								'multiple'    => false,
							)
						);
						?>
					</span>
				<?php
			}
		}

		/**
		 * Query Filter.
		 *
		 * @since 1.0.0
		 * @param Object $query Query.
		 */
		public function query_filters( $query ) {
			global $pagenow;

			$vendor_id = isset( $_GET['_mvr_vendor'] ) ? absint( wp_unslash( $_GET['_mvr_vendor'] ) ) : '';

			if ( ! is_admin() || empty( $vendor_id ) ) {
				return;
			}

			$vendor_obj = mvr_get_vendor( $vendor_id );

			if ( ! mvr_is_vendor( $vendor_obj ) ) {
				return;
			}

			if ( 'product' === $query->query['post_type'] ) {
				$query->set(
					'meta_query',
					array(
						array(
							'key'     => '_mvr_vendor',
							'value'   => $vendor_id,
							'compare' => '==',
						),
					)
				);
			} elseif ( 'shop_order' === $query->query['post_type'] ) {
				$orders_ids = mvr_get_vendor_orders(
					array(
						'vendor_id' => $vendor_id,
						'fields'    => 'ids',
					)
				);
				$order_ids  = $orders_ids->orders;

				if ( ! mvr_check_is_array( $order_ids ) ) {
					$order_ids = array( 'no orders' );
				}

				$query->set( 'post__in', $order_ids );
			}
		}

		/**
		 * Query Filter.
		 *
		 * @since 1.0.0
		 * @param Object $query Query.
		 */
		public function order_query_filters( $query ) {
			$vendor_id = isset( $_GET['_mvr_vendor'] ) ? absint( wp_unslash( $_GET['_mvr_vendor'] ) ) : '';

			if ( ! is_admin() || empty( $vendor_id ) ) {
				return $query;
			}

			$vendor_obj = mvr_get_vendor( $vendor_id );

			if ( ! mvr_is_vendor( $vendor_obj ) ) {
				return $query;
			}

			$orders_ids = mvr_get_vendor_orders(
				array(
					'vendor_id' => $vendor_id,
					'fields'    => 'ids',
				)
			);
			$order_ids  = $orders_ids->orders;

			if ( ! mvr_check_is_array( $order_ids ) ) {
				$order_ids = array( 'no orders' );
			}

			$post_in           = isset( $query['post__in'] ) ? $query['post__in'] : array();
			$query['post__in'] = $post_in + $order_ids;

			return $query;
		}

		/**
		 * Display Page States.
		 *
		 * @since 1.0.0
		 * @param Array  $post_states Post States.
		 * @param Object $post Post Object.
		 * @return Array
		 */
		public function add_display_post_states( $post_states, $post ) {
			if ( mvr_get_page_id( 'dashboard' ) === $post->ID ) {
				$post_states['mvr_page_for_dashboard'] = __( 'Vendor Dashboard Page', 'multi-vendor-marketplace' );
			}

			if ( mvr_get_page_id( 'vendor_register' ) === $post->ID ) {
				$post_states['mvr_page_for_register'] = __( 'Vendor Registration Page', 'multi-vendor-marketplace' );
			}

			if ( mvr_get_page_id( 'vendor_login' ) === $post->ID ) {
				$post_states['mvr_page_for_login'] = __( 'Vendor Login Page', 'multi-vendor-marketplace' );
			}

			if ( mvr_get_page_id( 'stores' ) === $post->ID ) {
				$post_states['mvr_page_for_stores'] = __( 'Vendor Stores Page', 'multi-vendor-marketplace' );
			}

			return $post_states;
		}

		/**
		 * Vendor Selection Field
		 *
		 * @since 1.0.0
		 */
		public static function quick_edit_vendor_selection() {
			$vendors_objs = mvr_get_vendors( array() );

			if ( $vendors_objs->has_vendor ) {
				?>
				<div class="inline-edit-group">
					<span class="title"><?php esc_html_e( 'Vendor', 'multi-vendor-marketplace' ); ?></span>
					<span class="input-text-wrap">
						<select style="width:50%;" name="_mvr_product_vendor" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;' ); ?>">
						<?php
						foreach ( $vendors_objs->vendors as $vendor_id => $vendor_obj ) {
							$option_value = sprintf( '(#%s) %s', $vendor_obj->get_id(), wp_kses_post( $vendor_obj->get_name() ) );
							?>
								<option value="<?php echo esc_attr( $vendor_id ); ?>"><?php echo wp_kses_post( $option_value ); ?></option>
								<?php
						}
						?>
						</select> 
					</span>
				</div>
					<?php
			}
		}

		/**
		 * Vendor Selection Field
		 *
		 * @since 1.0.0
		 * @param String  $column Column Name.
		 * @param Integer $post_id Post ID.
		 */
		public static function vendor_quick_edit_data( $column, $post_id ) {
			$product_obj = wc_get_product( $post_id );

			if ( is_a( $product_obj, 'WC_Product' ) ) {
				if ( 'name' === $column ) {
					echo '
					<div class="hidden" id="mvr_vendor_inline_' . absint( $post_id ) . '">
						<input type="hidden" id="mvr_vendor_id" value="' . esc_attr( $product_obj->get_meta( '_mvr_vendor', true ) ) . '">
					</div>';
				}
			}
		}

		/**
		 * Quick and bulk edit saving.
		 *
		 * @since 1.0.0
		 * @param String $post_id Post ID being saved.
		 * @param Object $post Post object being saved.
		 * @return int
		 */
		public function bulk_and_quick_edit_save_post( $post_id, $post ) {
			$request_data = $this->request_data();

			if ( ! isset( $request_data['woocommerce_quick_edit_nonce'] ) || ! wp_verify_nonce( $request_data['woocommerce_quick_edit_nonce'], 'woocommerce_quick_edit_nonce' ) ) {
				return $post_id;
			}

			$product = wc_get_product( $post );

			$this->quick_edit_and_bulk_save( $post_id, $product );

			return $post_id;
		}

		/**
		 * Quick edit.
		 *
		 * @since 1.0.0
		 * @param Integer    $post_id Post ID being saved.
		 * @param WC_Product $product Product object.
		 */
		public function quick_edit_and_bulk_save( $post_id, $product ) {
			$request_data = $this->request_data();
			$vendor_id    = isset( $request_data['_mvr_product_vendor'] ) ? $request_data['_mvr_product_vendor'] : '';

			$product->add_meta_data( '_mvr_vendor', $vendor_id, true );
			$product->save();
		}

		/**
		 * Before Delete Post.
		 *
		 * @since 1.0.0
		 * @param Integer $post_id Post ID.
		 */
		public function before_delete_post( $post_id ) {
			if ( mvr_is_vendor( $post_id ) ) {
				mvr_process_delete_vendor( $post_id );
			}
		}


		/**
		 * Add Vendor Column in the Table.
		 *
		 * @since 1.0.0
		 * @param Array $columns Table Columns.
		 * @return Array
		 */
		public static function product_vendor_column( $columns ) {
			$columns['mvr_vendor'] = esc_html__( 'Vendor', 'multi-vendor-marketplace' );

			return $columns;
		}

		/**
		 * Add Vendor Column in the Table.
		 *
		 * @since 1.0.0
		 * @param Array $columns Table Columns.
		 * @return Array
		 */
		public static function coupon_vendor_column( $columns ) {
			$columns['mvr_vendor'] = esc_html__( 'Vendor', 'multi-vendor-marketplace' );

			return $columns;
		}

		/**
		 * Add Vendor Column in the Table.
		 *
		 * @since 1.0.0
		 * @param Array $columns Table Columns.
		 * @return Array
		 */
		public static function order_vendor_column( $columns ) {
			$columns['mvr_vendor'] = esc_html__( 'Vendor', 'multi-vendor-marketplace' );

			return $columns;
		}

		/**
		 * Add Vendor Column Value in the product table.
		 *
		 * @since 1.0.0
		 * @param String  $column Table Column name.
		 * @param Integer $post_id Post ID.
		 */
		public static function product_vendor_column_value( $column, $post_id ) {
			if ( 'mvr_vendor' === $column ) {
				$product_obj = wc_get_product( $post_id );

				if ( is_a( $product_obj, 'WC_Product' ) ) {
					$vendor_id = $product_obj->get_meta( '_mvr_vendor', true );

					if ( $vendor_id ) {
						$vendor_obj = mvr_get_vendor( $vendor_id );

						if ( mvr_is_vendor( $vendor_obj ) ) {
							echo '<a href="' . esc_url( $vendor_obj->get_admin_edit_url() ) . '">' . esc_attr( $vendor_obj->get_name() ) . '</a>';
						}
					}
				}
			}
		}

		/**
		 * Add Vendor Column Value in the coupon table.
		 *
		 * @since 1.0.0
		 * @param String  $column Table Column name.
		 * @param Integer $post_id Post ID.
		 */
		public static function coupon_vendor_column_value( $column, $post_id ) {
			if ( 'mvr_vendor' === $column ) {
				$coupon_obj = new WC_Coupon( $post_id );

				if ( is_a( $coupon_obj, 'WC_Coupon' ) ) {
					$vendor_id = $coupon_obj->get_meta( '_mvr_vendor', true );

					if ( $vendor_id ) {
						$vendor_obj = mvr_get_vendor( $vendor_id );

						if ( mvr_is_vendor( $vendor_obj ) ) {
							echo '<a href="' . esc_url( $vendor_obj->get_admin_edit_url() ) . '">' . esc_attr( $vendor_obj->get_name() ) . '</a>';
						}
					}
				}
			}
		}

		/**
		 * Add Vendor Column Value in the order table.
		 *
		 * @since 1.0.0
		 * @param String  $column Table Column name.
		 * @param Integer $post_id Post ID.
		 */
		public static function order_vendor_column_value( $column, $post_id ) {
			if ( 'mvr_vendor' === $column ) {
				$order_obj = wc_get_order( $post_id );

				if ( is_a( $order_obj, 'WC_Order' ) ) {
					$vendor_id = $order_obj->get_meta( 'mvr_vendor_id', true );

					if ( $vendor_id ) {
						$vendor_obj = mvr_get_vendor( $vendor_id );

						if ( mvr_is_vendor( $vendor_obj ) ) {
							echo '<a href="' . esc_url( $vendor_obj->get_admin_edit_url() ) . '">' . esc_attr( $vendor_obj->get_name() ) . '</a>';
						}
					}
				}
			}
		}

		/**
		 * Get the current request data ($_REQUEST superglobal).
		 * This method is added to ease unit testing.
		 *
		 * @since 1.0.0
		 * @return Array The $_REQUEST superglobal.
		 */
		protected function request_data() {
			return $_REQUEST;
		}
	}

	new MVR_Admin_Post_Types();
}
