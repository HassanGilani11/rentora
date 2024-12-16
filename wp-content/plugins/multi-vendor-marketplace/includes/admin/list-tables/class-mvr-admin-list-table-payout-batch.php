<?php
/**
 * Payout Batch List Table.
 *
 * @package Multi-Vendor/List Table
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'MVR_Admin_List_Table_Payout_Batch' ) ) {

	/**
	 * MVR_Admin_List_Table_Payout_Batch Class.
	 * */
	class MVR_Admin_List_Table_Payout_Batch extends WP_List_Table {

		/**
		 * Per page count
		 *
		 * @var int
		 * */
		private $limit = 10;

		/**
		 * Offset
		 *
		 * @var int
		 * */
		private $offset;

		/**
		 * Order BY
		 *
		 * @var string
		 * */
		private $orderby = 'ID';

		/**
		 * Order.
		 *
		 * @var string
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
		private $list_slug = 'mvr_payout_batch';

		/**
		 * Payout IDs.
		 *
		 * @var array
		 * */
		private $payout_batch_ids;

		/**
		 * Base URL.
		 *
		 * @var string
		 * */
		private $base_url;

		/**
		 * Current URL.
		 *
		 * @var string
		 * */
		private $current_url;

		/**
		 * Prepares the list of items for displaying.
		 * */
		public function prepare_items() {
			global $wpdb;
			$this->database = $wpdb;
			$this->base_url = mvr_get_payout_batch_page_url();

			$this->prepare_payout_batch_ids();
			$this->prepare_current_url();
			$this->process_bulk_action();
			$this->get_current_pagenum();
			$this->get_current_payout_batches();
			$this->prepare_pagination_args();
			$this->prepare_column_headers();
		}

		/**
		 * Prepare pagination
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
		 * */
		private function get_current_pagenum() {
			$this->offset = $this->limit * ( $this->get_pagenum() - 1 );
		}

		/**
		 * Prepare header columns
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
		 * @return array
		 * */
		public function get_columns() {
			$columns = array(
				'cb' => '<input type="checkbox" />',
			);

			$keys         = array( 'id', 'batch_id', 'status', 'date' );
			$labels       = mvr_get_payout_batch_table_labels();
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
		 * @return array
		 * */
		protected function get_hidden_columns() {
			return array();
		}

		/**
		 * Get a list of sortable columns.
		 *
		 * @return array
		 * */
		protected function get_sortable_columns() {
			return array();
		}

		/**
		 * Get current url
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
				'delete' => esc_html__( 'Delete', 'multi-vendor-marketplace' ),
			);

			/**
			 * Get bulk actions.
			 *
			 * @since 1.0.0
			 */
			$action = apply_filters( $this->list_slug . '_bulk_actions', $action );

			return $action;
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

			if ( ! wp_verify_nonce( $nonce, 'mvr-search_payout_batch' ) ) {
				return;
			}

			$ids = isset( $_REQUEST['id'] ) ? wc_clean( wp_unslash( $_REQUEST['id'] ) ) : array();
			$ids = ! is_array( $ids ) ? explode( ',', $ids ) : $ids;

			if ( ! mvr_check_is_array( $ids ) ) {
				return;
			}

			$action = $this->current_action();

			foreach ( $ids as $id ) {
				switch ( $action ) {
					case 'delete':
						$payout_batch_obj = mvr_get_payout_batch( $id );

						if ( mvr_is_payout_batch( $payout_batch_obj ) ) {
							$payout_batch_obj->delete();
						}
						break;
				}
			}

			wp_safe_redirect( $this->current_url );
			exit();
		}

		/**
		 * Display the list of views available on this table.
		 *
		 * @since 1.0.0
		 * @return Array
		 * */
		protected function get_views() {
			$args         = array();
			$views        = array();
			$status       = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
			$status_array = mvr_get_payout_batch_statuses();
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
		 * @param MVR_Payout_Batch $payout_batch_obj Payout Batch object.
		 * @return HTML
		 * */
		protected function column_cb( $payout_batch_obj ) {
			return sprintf( '<input class="mvr-payout-batch-cb" type="checkbox" name="id[]" value="%s" />', $payout_batch_obj->get_id() );
		}

		/**
		 * Prepare the each column data.
		 *
		 * @since 1.0.0
		 * @param MVR_Payout_Batch $payout_batch_obj Payout batch object.
		 * @param String           $column_name Name of the column.
		 * @return mixed
		 * */
		protected function column_default( $payout_batch_obj, $column_name ) {
			switch ( $column_name ) {
				case 'id':
					$views              = '<a class="row-title" href="' . esc_url( get_edit_post_link( $payout_batch_obj->get_id() ) ) . '">#' . esc_html( $payout_batch_obj->get_id() ) . '</a> <br/> <div class="row-actions">';
					$actions ['delete'] = '<a class="mvr-delete-payout-batch mvr-delete" href="' . esc_url(
						add_query_arg(
							array(
								'action' => 'delete',
								'id'     => $payout_batch_obj->get_id(),
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
				case 'batch_id':
					if ( 'pending' === $payout_batch_obj->get_status() || empty( $payout_batch_obj->get_batch_id() ) ) {
						?>
						<a class="button-primary mvr-check-payout-status" href="
								<?php
								$url = wp_nonce_url(
									add_query_arg(
										array(
											'action' => 'check_payout_batch_status',
											'post'   => $payout_batch_obj->get_id(),
											'paged'  => $this->get_pagenum(),
										),
										$payout_batch_obj->get_admin_edit_url()
									),
									"mvr-check_payout_batch_status-post-{$payout_batch_obj->get_id()}"
								);

								echo esc_url_raw( $url );
								?>
								"><?php esc_html_e( 'Check Batch ID', 'multi-vendor-marketplace' ); ?>
						</a>
						<?php
					} else {
						echo esc_html( $payout_batch_obj->get_batch_id() );
					}
					break;

				case 'status':
					printf( '<mark class="mvr-post-status %s"><span>%s</span></mark>', esc_attr( sanitize_html_class( 'status-' . $payout_batch_obj->get_status() ) ), esc_html( mvr_get_payout_status_name( $payout_batch_obj->get_status() ) ) );
					break;
				case 'date':
					$created_timestamp = $payout_batch_obj->get_date_created() ? $payout_batch_obj->get_date_created()->getTimestamp() : '';

					if ( ! $created_timestamp ) {
						echo '&ndash;';
						return;
					}

					// Check if the plan was created within the last 24 hours, and not in the future.
					if ( $created_timestamp > strtotime( '-1 day', time() ) && $created_timestamp <= time() ) {
						$show_date = sprintf(
							/* translators: %s: human-readable time difference */
							_x( '%s ago', '%s = human-readable time difference', 'multi-vendor-marketplace' ),
							human_time_diff( $payout_batch_obj->get_date_created()->getTimestamp(), time() )
						);
					} else {
						$show_date = $payout_batch_obj->get_date_created()->date_i18n( __( 'M j, Y', 'multi-vendor-marketplace' ) );
					}

					printf(
						'<time datetime="%1$s" title="%2$s">%3$s</time>',
						esc_attr( $payout_batch_obj->get_date_created()->date( 'c' ) ),
						esc_html( $payout_batch_obj->get_date_created()->date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ),
						esc_html( $show_date )
					);
					break;
			}
		}

		/**
		 * Get the current page items.
		 *
		 * @since 1.0.0
		 * */
		private function get_current_payout_batches() {
			$request = $_REQUEST;
			$status  = isset( $request['status'] ) ? sanitize_text_field( wp_unslash( $request['status'] ) ) : '';

			if ( 'all' === $status || '' === $status ) {
				$status = array_keys( mvr_get_payout_batch_statuses() );
			}

			$search_term = isset( $request['s'] ) ? sanitize_text_field( wp_unslash( $request['s'] ) ) : '';
			$args        = array(
				'fields'  => 'objects',
				's'       => $search_term,
				'status'  => $status,
				'orderby' => isset( $request['orderby'] ) ? sanitize_text_field( wp_unslash( $request['orderby'] ) ) : $this->orderby,
				'order'   => isset( $request['order'] ) ? sanitize_text_field( wp_unslash( $request['order'] ) ) : $this->order,
				'limit'   => $this->limit,
				'page'    => $this->get_pagenum(),
			);

			$payout_batches_obj = mvr_get_payout_batches( $args );

			$this->items       = $payout_batches_obj->payout_batches;
			$this->total_items = $payout_batches_obj->total_payout_batches;
		}

		/**
		 * Get the payout batch count for the status.
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
				$status = array_keys( mvr_get_payout_batch_statuses() );
			}

			$args = array(
				'status' => $status,
				'fields' => 'ids',
			);

			$search_term = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';

			if ( $search_term ) {
				$args['s'] = $search_term;
			}

			$payout_batches_obj = mvr_get_payout_batches( $args );

			return $payout_batches_obj->total_payout_batches;
		}

		/**
		 * Prepare the Payout IDs.
		 *
		 * @since 1.0.0
		 * */
		private function prepare_payout_batch_ids() {
			$all_payout_batch_ids = mvr_get_payout_batches(
				array(
					'fields' => 'ids',
				)
			);

			/**
			 * Payout ids in list table.
			 *
			 * @since 1.0.0
			 */
			$this->payout_batch_ids = apply_filters( 'mvr_admin_list_table_payout_batch_ids', $all_payout_batch_ids );
		}
	}
}
