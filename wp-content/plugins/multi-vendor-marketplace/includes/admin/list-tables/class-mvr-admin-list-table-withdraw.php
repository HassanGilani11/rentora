<?php
/**
 * Withdraw List Table.
 *
 * @package Multi-Vendor/List Table
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'MVR_Admin_List_Table_Withdraw' ) ) {

	/**
	 * MVR_Admin_List_Table_Withdraw Class.
	 * */
	class MVR_Admin_List_Table_Withdraw extends WP_List_Table {

		/**
		 * Per page count
		 *
		 * @var Integer
		 * */
		private $limit = 10;

		/**
		 * Offset
		 *
		 * @var Integer
		 * */
		private $offset;

		/**
		 * Order BY
		 *
		 * @var String
		 * */
		private $orderby = 'ID';

		/**
		 * Order.
		 *
		 * @var String
		 * */
		private $order = 'DESC';

		/**
		 * Offset
		 *
		 * @var String
		 * */
		private $database;

		/**
		 * Offset
		 *
		 * @var int
		 * */
		private $total_items;

		/**
		 * List Slug
		 *
		 * @var int
		 * */
		private $list_slug = 'mvr';

		/**
		 * Withdraw IDs.
		 *
		 * @var Array
		 * */
		private $withdraw_ids;

		/**
		 * Base URL.
		 *
		 * @var String
		 * */
		private $base_url;

		/**
		 * Current URL.
		 *
		 * @var String
		 * */
		private $current_url;

		/**
		 * Prepares the list of items for displaying.
		 *
		 * @since 1.0.0
		 * */
		public function prepare_items() {
			global $wpdb;
			$this->database = $wpdb;
			$this->base_url = mvr_get_withdraw_page_url();

			$this->prepare_withdraw_ids();
			$this->prepare_current_url();
			$this->process_bulk_action();
			$this->get_current_pagenum();
			$this->get_current_withdraws();
			$this->prepare_pagination_args();
			$this->prepare_column_headers();
		}

		/**
		 * Prepare pagination
		 *
		 * @since 1.0.0
		 * */
		private function prepare_pagination_args() {
			$args = array(
				'per_page' => $this->limit,
			);

			if ( $this->total_items ) {
				$args['total_items'] = $this->total_items;
			}

			$this->set_pagination_args( $args );
		}

		/**
		 * Get current page number
		 *
		 * @since 1.0.0
		 * */
		private function get_current_pagenum() {
			$this->offset = $this->limit * ( $this->get_pagenum() - 1 );
		}

		/**
		 * Prepare header columns
		 *
		 * @since 1.0.0
		 * */
		private function prepare_column_headers() {
			$columns  = $this->get_columns();
			$hidden   = $this->get_hidden_columns();
			$sortable = $this->get_sortable_columns();

			$this->_column_headers = array( $columns, $hidden, $sortable );
		}

		/**
		 * Get a list of columns.
		 *
		 * @since 1.0.0
		 * @return Array
		 * */
		public function get_columns() {
			$columns      = array(
				'cb' => '<input type="checkbox" />',
			);
			$keys         = array( 'name', 'amount', 'charge', 'status', 'payment', 'date' );
			$labels       = mvr_get_withdraw_table_labels();
			$show_columns = array(
				'cb' => $columns['cb'],
			);

			foreach ( $keys as $key ) {
				$show_columns[ $key ] = ( isset( $labels[ $key ] ) ) ? $labels[ $key ] : '';
			}

			return $show_columns;
		}

		/**
		 * Get a list of hidden columns.
		 *
		 * @since 1.0.0
		 * @return Array
		 * */
		protected function get_hidden_columns() {
			return array();
		}

		/**
		 * Get a list of sortable columns.
		 *
		 * @since 1.0.0
		 * @return Array
		 * */
		protected function get_sortable_columns() {
			return array();
		}

		/**
		 * Get current url
		 *
		 * @since 1.0.0
		 * */
		private function prepare_current_url() {
			$pagenum       = $this->get_pagenum();
			$args['paged'] = $pagenum;
			$url           = add_query_arg( $args, $this->base_url );

			$this->current_url = $url;
		}

		/**
		 * Get a list of bulk actions.
		 *
		 * @since 1.0.0
		 * @return Array
		 * */
		protected function get_bulk_actions() {
			$action = array(
				'delete'       => esc_html__( 'Delete', 'multi-vendor-marketplace' ),
				'mark_paid'    => esc_html__( 'Mark as Paid', 'multi-vendor-marketplace' ),
				'make_payment' => esc_html__( 'Make Payment', 'multi-vendor-marketplace' ),
			);

			/**
			 * Get bulk actions.
			 *
			 * @since 1.0
			 */
			$action = apply_filters( $this->list_slug . '_bulk_actions', $action );

			return $action;
		}


		/**
		 * Extra controls to be displayed between bulk actions and pagination.
		 *
		 * @since 1.0.0
		 * @param String $which Which Position.
		 */
		protected function extra_tablenav( $which ) {
			if ( 'top' === $which ) {
				echo '<div class="alignleft actions">';
					$this->vendor_dropdown();
					$this->payment_method_dropdown();
					submit_button( __( 'Filter', 'multi-vendor-marketplace' ), '', 'filter_action', false );
				echo '</div>';
			}
		}

		/**
		 * Display Vendor Selection dropdown
		 *
		 * @since 1.0.0
		 */
		public function vendor_dropdown() {
			$vendor_id = ( isset( $_REQUEST['_mvr_vendor'] ) && ! empty( $_REQUEST['_mvr_vendor'] ) ) ? absint( wp_unslash( $_REQUEST['_mvr_vendor'] ) ) : '';
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

		/**
		 * Display Payment Method Selection dropdown
		 *
		 * @since 1.0.0
		 */
		public function payment_method_dropdown() {
			$payment_methods = ( isset( $_REQUEST['_mvr_payment_method'] ) && ! empty( $_REQUEST['_mvr_payment_method'] ) ) ? wc_clean( wp_unslash( $_REQUEST['_mvr_payment_method'] ) ) : '';
			?>
			<span class="mvr-select2-wrap">
				<select name="_mvr_payment_method" multiple class="mvr-select2">
					<?php
					foreach ( mvr_get_withdraw_payment_type_options() as $key => $value ) {
						echo '<option value="' . esc_attr( $key ) . '"' . esc_html( wc_selected( $key, $payment_methods ) ) . '>' . esc_html( $value ) . '</option>';
					}
					?>
				</select>
			</span>
			<?php
		}

		/**
		 * Processes the bulk action.
		 *
		 * @since 1.0.0
		 * */
		public function process_bulk_action() {
			if ( ! isset( $_REQUEST['_mvr_nonce'] ) ) {
				return;
			}

			$nonce = sanitize_key( wp_unslash( $_REQUEST['_mvr_nonce'] ) );

			if ( ! wp_verify_nonce( $nonce, 'mvr-search_withdraw' ) ) {
				return;
			}

			$ids = isset( $_REQUEST['id'] ) ? wc_clean( wp_unslash( $_REQUEST['id'] ) ) : array();
			$ids = ! is_array( $ids ) ? explode( ',', $ids ) : $ids;

			if ( ! mvr_check_is_array( $ids ) ) {
				return;
			}

			$action = $this->current_action();

			if ( in_array( $action, array( 'mark_paid', 'make_payment' ) ) ) {
				$withdraw_ids = mvr_get_withdraws(
					array(
						'include_ids'    => $ids,
						'payment_method' => ( 'mark_paid' === $action ) ? '1' : '2',
						'fields'         => 'ids',
						'status'         => 'pending',
					)
				);

				if ( ! $withdraw_ids->has_withdraw ) {
					if ( 'mark_paid' === $action ) {
						MVR_Admin::add_error( esc_html__( 'There was no Bank Transfer method selected', 'multi-vendor-marketplace' ) );
					} else {
						MVR_Admin::add_error( esc_html__( 'There was no PayPal method selected', 'multi-vendor-marketplace' ) );
					}
				}
			}

			foreach ( $ids as $id ) {
				$withdraw_obj = mvr_get_withdraw( $id );

				if ( ! mvr_is_withdraw( $withdraw_obj ) ) {
					continue;
				}

				switch ( $action ) {
					case 'delete':
						$withdraw_obj->delete( true );
						break;
					case 'mark_paid':
						$vendor_obj = $withdraw_obj->get_vendor();

						if ( ! mvr_is_vendor( $vendor_obj ) || '1' !== $withdraw_obj->get_payment_method() || 'pending' !== $withdraw_obj->get_status() ) {
							continue 2;
						}

						/**
						 * Bank Transfer completed Hook
						 *
						 * @since 1.0.0
						 * */
						do_action( 'mvr_after_bank_transfer_completed', $withdraw_obj, $vendor_obj, '' );

						$withdraw_obj->update_status( 'success' );

						if ( $withdraw_obj->has_commission() ) {
							$withdraw_obj->get_commission()->update_status( 'paid' );
						}
						break;
					case 'make_payment':
						$vendor_obj = $withdraw_obj->get_vendor();

						if ( ! mvr_is_vendor( $vendor_obj ) || '2' !== $withdraw_obj->get_payment_method() || 'pending' !== $withdraw_obj->get_status() || 'yes' !== get_option( 'mvr_settings_enable_paypal_payouts', 'no' ) ) {
							continue 2;
						}

						$receiver_email = sanitize_email( $vendor_obj->get_paypal_email() );

						if ( empty( $receiver_email ) ) {
							continue 2;
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
									 * PayPal Payout completed Hook
									 *
									 * @since 1.0.0
									 * */
									do_action( 'mvr_after_paypal_payout_completed', $withdraw_obj, $vendor_obj );
								}
							}
						}
						break;
					case 'reject_withdraw':
						$vendor_obj = $withdraw_obj->get_vendor();

						if ( ! mvr_is_vendor( $vendor_obj ) || 'pending' !== $withdraw_obj->get_status() ) {
							continue 2;
						}

						$vendor_obj->update_amount( $withdraw_obj->get_amount() + $withdraw_obj->get_charge_amount() );

						$transactions_obj = mvr_get_transactions(
							array(
								'vendor_id'   => $vendor_obj->get_id(),
								'source_id'   => $withdraw_obj->get_id(),
								'source_from' => 'withdraw',
							)
						);

						$transaction_obj = current( $transactions_obj->transactions );

						if ( mvr_is_transaction( $transaction_obj ) ) {
							$transaction_obj->update_status( 'failed' );
						}

						$withdraw_obj->update_status( 'failed' );

						if ( $withdraw_obj->has_commission() ) {
							$withdraw_obj->get_commission()->update_status( 'failed' );
						}

						/**
						 * After Rejected Withdraw Request.
						 *
						 * @since 1.0.0
						 * */
						do_action( 'mvr_after_rejected_withdraw_request', $withdraw_obj, $vendor_obj );
						break;
				}
			}

			wp_safe_redirect( $this->current_url );
			exit();
		}

		/**
		 * Display the list of views available on this table.
		 *
		 * @return array
		 * */
		protected function get_views() {
			$args         = array();
			$views        = array();
			$status       = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
			$status_array = mvr_get_withdraw_statuses();
			$status_array = array( 'all' => esc_html__( 'All', 'multi-vendor-marketplace' ) ) + $status_array;

			foreach ( $status_array as $status_name => $status_label ) {
				$status_count = $this->get_item_count_for_status( $status_name );

				if ( ! $status_count ) {
					continue;
				}

				$args['status'] = $status_name;
				$label          = $status_label . ' (' . $status_count . ')';
				$class          = array( strtolower( $status_name ) );

				if ( $status === $status_name || ( 'all' === $status_name && empty( $status ) ) ) {
					$class[] = 'current';
				}

				$search_term = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';

				if ( $search_term ) {
					$args['s'] = $search_term;
				}

				$views[ $status_name ] = $this->get_edit_link( $args, $label, implode( ' ', $class ) );
			}

			return $views;
		}

		/**
		 * Get the edit link for status.
		 *
		 * @since 1.0.0
		 * @param Array  $args Arguments.
		 * @param String $label Label.
		 * @param String $class Class.
		 * @return String
		 * */
		private function get_edit_link( $args, $label, $class = '' ) {
			$url        = add_query_arg( $args, $this->base_url );
			$class_html = '';

			if ( ! empty( $class ) ) {
				/* translators: %s: Class */
				$class_html = sprintf( 'class="%s"', esc_attr( $class ) );
			}

			/* translators: %1$s: URL  %2$s: Class %3$s: Link Name */
			return sprintf( '<a href="%1$s" %2$s>%3$s</a>', esc_url( $url ), $class_html, $label );
		}

		/**
		 * Prepare the CB column data.
		 *
		 * @since 1.0.0
		 * @param MVR_Withdraw $withdraw_obj Withdraw object.
		 * @return HTML
		 * */
		protected function column_cb( $withdraw_obj ) {
			return sprintf( '<input class="mvr-withdraw-cb" type="checkbox" name="id[]" value="%s" />', $withdraw_obj->get_id() );
		}

		/**
		 * Prepare the each column data.
		 *
		 * @since 1.0.0
		 * @param MVR_Withdraw $withdraw_obj Withdraw object.
		 * @param String       $column_name Name of the column.
		 * @return mixed
		 * */
		protected function column_default( $withdraw_obj, $column_name ) {
			$vendor_obj = $withdraw_obj->get_vendor();
			switch ( $column_name ) {
				case 'name':
					$actions = array();

					/* translators: %s: Withdraw ID */
					$actions ['id'] = sprintf( esc_html__( 'ID: %s', 'multi-vendor-marketplace' ), $withdraw_obj->get_id() );

					if ( mvr_is_vendor( $vendor_obj ) ) {
						$views = '<a class="row-title" href="' . esc_url( $vendor_obj->get_admin_edit_url() ) . '"><strong>' . esc_html( $vendor_obj->get_name() ) . '</strong></a> <br/> <div class="row-actions">';
					} else {
						$views = esc_html__( 'Not found', 'multi-vendor-marketplace' ) . ' <br/> <div class="row-actions">';
					}

					$actions ['delete'] = '<a class="mvr-delete-withdraw mvr-delete" href="' . esc_url(
						add_query_arg(
							array(
								'action' => 'delete',
								'id'     => $withdraw_obj->get_id(),
							),
							$this->current_url
						)
					) . '">' . esc_html__( 'Delete', 'multi-vendor-marketplace' ) . '</a>';

					end( $actions );

					$last_key = key( $actions );

					foreach ( $actions as $key => $action ) {
						$views .= '<span class="' . $key . '">' . $action;

						if ( $last_key == $key ) {
							$views .= '</span>';
							break;
						}

						$views .= ' | </span>';
					}

					return $views . '</div>';
				case 'amount':
					echo '<strong>' . wp_kses_post( wc_price( $withdraw_obj->get_amount() ) ) . '</strong><br/>';

					if ( $withdraw_obj->has_status( 'pending' ) ) {
						if ( mvr_is_vendor( $vendor_obj ) ) {
							if ( '2' === $withdraw_obj->get_payment_method() ) {
								if ( ! empty( $vendor_obj->get_paypal_email() ) ) {
									$url = wp_nonce_url(
										add_query_arg(
											array(
												'action'   => 'make_payment',
												'post'     => $withdraw_obj->get_id(),
												'redirect' => mvr_get_current_url(),
											),
											$withdraw_obj->get_admin_edit_url()
										),
										"mvr-make_payment-post-{$withdraw_obj->get_id()}"
									);

									echo '<a class="mvr-make-payment button action" href="' . esc_url( $url ) . '">' . esc_html__( 'Make Payment', 'multi-vendor-marketplace' ) . '</a>';
								}
							} else {
								$url = wp_nonce_url(
									add_query_arg(
										array(
											'action' => 'mark_paid',
											'post'   => $withdraw_obj->get_id(),
										),
										$withdraw_obj->get_admin_edit_url()
									),
									"mvr-mark_paid-post-{$withdraw_obj->get_id()}"
								);

								echo '<a class="mvr-make-payment button action" href="' . esc_url( $url ) . '">' . esc_html__( 'Mark as Paid', 'multi-vendor-marketplace' ) . '</a>';
							}

							$url = wp_nonce_url(
								add_query_arg(
									array(
										'action' => 'reject_withdraw',
										'post'   => $withdraw_obj->get_id(),
									),
									$withdraw_obj->get_admin_edit_url()
								),
								"mvr-reject_withdraw-post-{$withdraw_obj->get_id()}"
							);

							echo '<a class="mvr-reject-payment button action" href="' . esc_url( $url ) . '">' . esc_html__( 'Reject', 'multi-vendor-marketplace' ) . '</a>';
						}
					}
					break;
				case 'charge':
					echo wp_kses_post( wc_price( $withdraw_obj->get_charge_amount() ) );
					break;
				case 'status':
					printf( '<mark class="mvr-post-status %s"><span>%s</span></mark>', esc_attr( sanitize_html_class( 'status-' . $withdraw_obj->get_status() ) ), esc_html( mvr_get_withdraw_status_name( $withdraw_obj->get_status() ) ) );
					break;
				case 'payment':
					echo wp_kses_post( mvr_payment_method_options( $withdraw_obj->get_payment_method() ) );
					break;
				case 'date':
					echo esc_html( $withdraw_obj->get_date_modified()->date_i18n( wc_date_format() . ' ' . wc_time_format() ) );
					break;
			}
		}

		/**
		 * Get the current page items.
		 *
		 * @since 1.0.0
		 * */
		private function get_current_withdraws() {
			$request        = $_REQUEST;
			$status         = isset( $request['status'] ) ? sanitize_text_field( wp_unslash( $request['status'] ) ) : '';
			$search_term    = isset( $request['s'] ) ? sanitize_text_field( wp_unslash( $request['s'] ) ) : '';
			$vendor_id      = isset( $request['_mvr_vendor'] ) ? absint( wp_unslash( $request['_mvr_vendor'] ) ) : '';
			$payment_method = isset( $request['_mvr_payment_method'] ) ? wc_clean( wp_unslash( $request['_mvr_payment_method'] ) ) : '';
			$args           = array(
				'status'  => ( $status && 'all' !== $status ) ? $status : array_keys( mvr_get_withdraw_statuses() ),
				'fields'  => 'objects',
				's'       => $search_term,
				'orderby' => isset( $request['orderby'] ) ? sanitize_text_field( wp_unslash( $request['orderby'] ) ) : $this->orderby,
				'order'   => isset( $request['order'] ) ? sanitize_text_field( wp_unslash( $request['order'] ) ) : $this->order,
				'limit'   => $this->limit,
				'page'    => $this->get_pagenum(),
			);

			if ( $vendor_id ) {
				$args['vendor_id'] = $vendor_id;
			}

			if ( $payment_method ) {
				$args['payment_method'] = $payment_method;
			}

			$withdraws_obj     = mvr_get_withdraws( $args );
			$this->items       = $withdraws_obj->withdraws;
			$this->total_items = $withdraws_obj->total_withdraws;
		}

		/**
		 * Get the commission count for the status.
		 *
		 * @since 1.0.0
		 * @param String $status Status.
		 * @return Integer
		 * */
		private function get_item_count_for_status( $status = '' ) {
			if ( empty( $status ) ) {
				$status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
			}

			if ( 'all' === $status || '' === $status ) {
				$status = array_keys( mvr_get_withdraw_statuses() );
			}

			$args        = array(
				'status' => $status,
				'fields' => 'ids',
			);
			$search_term = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';

			if ( $search_term ) {
				$args['s'] = $search_term;
			}

			$withdraws_obj = mvr_get_withdraws( $args );

			return $withdraws_obj->total_withdraws;
		}

		/**
		 * Prepare the Commission IDs.
		 *
		 * @since 1.0.0
		 * */
		private function prepare_withdraw_ids() {
			$all_withdraw_ids = mvr_get_withdraws(
				array(
					'fields' => 'ids',
				)
			);

			/**
			 * Withdraw ids in list table.
			 *
			 * @since 1.0.0
			 */
			$this->withdraw_ids = apply_filters( 'mvr_admin_list_table_withdraw_ids', $all_withdraw_ids );
		}
	}
}
