<?php
/**
 * Transaction List Table.
 *
 * @package Multi-Vendor/List Table
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'MVR_Admin_List_Table_Transaction' ) ) {

	/**
	 * MVR_Admin_List_Table_Transaction Class.
	 * */
	class MVR_Admin_List_Table_Transaction extends WP_List_Table {

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
		private $list_slug = 'mvr';

		/**
		 * Transaction IDs.
		 *
		 * @var array
		 * */
		private $transaction_ids;

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
			$this->base_url = mvr_get_transaction_page_url();

			$this->prepare_transaction_ids();
			$this->prepare_current_url();
			$this->process_bulk_action();
			$this->get_current_pagenum();
			$this->get_current_transactions();
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
			$columns      = array(
				'cb' => '<input type="checkbox" />',
			);
			$keys         = array( 'vendor', 'amount', 'type', 'source', 'status', 'date' );
			$labels       = mvr_get_transaction_table_labels();
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
		 * @return array
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
		 * Extra controls to be displayed between bulk actions and pagination.
		 *
		 * @since 1.0.0
		 * @param String $which Which Position.
		 */
		protected function extra_tablenav( $which ) {
			if ( 'top' === $which ) {
				echo '<div class="alignleft actions">';
					$this->vendor_dropdown();
					$this->source_dropdown();
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
		 * Source Dropdown
		 *
		 * @since 1.0.0
		 */
		public function source_dropdown() {
			$source_from = ( isset( $_REQUEST['_mvr_t_source'] ) && ! empty( $_REQUEST['_mvr_t_source'] ) ) ? wc_clean( wp_unslash( $_REQUEST['_mvr_t_source'] ) ) : '';
			?>
			<select name="_mvr_t_source" multiple class="mvr-select2">
				<?php
				$sources = array(
					'order'    => esc_html__( 'Order', 'multi-vendor-marketplace' ),
					'withdraw' => esc_html__( 'Withdraw', 'multi-vendor-marketplace' ),
				);

				foreach ( $sources as $key => $value ) {
					echo '<option value="' . esc_attr( $key ) . '"' . esc_html( wc_selected( $key, $source_from ) ) . '>' . esc_html( $value ) . '</option>';
				}
				?>
			</select>
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

			if ( ! wp_verify_nonce( $nonce, 'mvr-search_transaction' ) ) {
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
						$transaction_obj = mvr_get_transaction( $id );

						if ( mvr_is_transaction( $transaction_obj ) ) {
							$transaction_obj->delete();
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
		 * @return array
		 * */
		protected function get_views() {
			$args         = array();
			$views        = array();
			$status       = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
			$status_array = mvr_get_transaction_statuses();
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
		 * @param MVR_Transaction $transaction_obj Transaction object.
		 * @return HTML
		 * */
		protected function column_cb( $transaction_obj ) {
			return sprintf( '<input class="mvr-transaction-cb" type="checkbox" name="id[]" value="%s" />', $transaction_obj->get_id() );
		}

		/**
		 * Prepare the each column data.
		 *
		 * @since 1.0.0
		 * @param MVR_Transaction $transaction_obj Transaction object.
		 * @param String          $column_name Name of the column.
		 * @return mixed
		 * */
		protected function column_default( $transaction_obj, $column_name ) {
			switch ( $column_name ) {
				case 'vendor':
					$vendor_obj = $transaction_obj->get_vendor();
					$actions    = array();

					/* translators: %s: Transaction ID */
					$actions ['id'] = sprintf( esc_html__( 'ID: %s', 'multi-vendor-marketplace' ), $transaction_obj->get_id() );

					if ( mvr_is_vendor( $vendor_obj ) ) {
						$views = '<a class="mvr-transaction row-title" href="' . esc_url( get_edit_post_link( $vendor_obj->get_id() ) ) . '">' . esc_html( $vendor_obj->get_name() ) . '</a> <br/> <div class="row-actions">';
					} else {
						$views = esc_html__( 'Not found', 'multi-vendor-marketplace' ) . ' <br/> <div class="row-actions">';
					}

					$actions ['delete'] = '<a class="mvr-delete" href="' . esc_url(
						add_query_arg(
							array(
								'action' => 'delete',
								'id'     => $transaction_obj->get_id(),
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
					echo wp_kses_post( wc_price( $transaction_obj->get_amount() ) );
					break;
				case 'date':
					echo esc_html( $transaction_obj->get_date_created()->date_i18n( wc_date_format() . ' ' . wc_time_format() ) );
					break;
				case 'type':
					$type = ( 'credit' === $transaction_obj->get_type() ) ? esc_html__( 'Credit', 'multi-vendor-marketplace' ) : esc_html__( 'Debit', 'multi-vendor-marketplace' );
					echo esc_html( $type );
					break;
				case 'source':
					/* translators: %1$s: Source From, %2$s: Source ID */
					printf( esc_html__( 'From: %1$s ID: %2$s', 'multi-vendor-marketplace' ), esc_html( ucfirst( $transaction_obj->get_source_from() ) ) . '<br/>', esc_attr( $transaction_obj->get_source_id() ) );
					break;
				case 'status':
					printf( '<mark class="mvr-post-status %s"><span>%s</span></mark>', esc_attr( sanitize_html_class( 'status-' . $transaction_obj->get_status() ) ), esc_html( mvr_get_transaction_status_name( $transaction_obj->get_status() ) ) );
					break;
			}
		}

		/**
		 * Get the current page items.
		 *
		 * @since 1.0.0
		 * */
		private function get_current_transactions() {
			$request     = $_REQUEST;
			$vendor_id   = isset( $request['_mvr_vendor'] ) ? absint( wp_unslash( $request['_mvr_vendor'] ) ) : '';
			$source_from = isset( $request['_mvr_t_source'] ) ? wc_clean( wp_unslash( $request['_mvr_t_source'] ) ) : '';
			$status      = isset( $request['status'] ) ? sanitize_text_field( wp_unslash( $request['status'] ) ) : '';
			$search_term = isset( $request['s'] ) ? sanitize_text_field( wp_unslash( $request['s'] ) ) : '';
			$args        = array(
				'status'  => ( $status && 'all' !== $status ) ? $status : array_keys( mvr_get_transaction_statuses() ),
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

			if ( $source_from ) {
				$args['source_from'] = $source_from;
			}

			$transactions_obj = mvr_get_transactions( $args );

			$this->items       = $transactions_obj->transactions;
			$this->total_items = $transactions_obj->total_transactions;
		}

		/**
		 * Get the item count for the status.
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
				$status = array_keys( mvr_get_transaction_statuses() );
			}

			$args = array(
				'status' => $status,
				'fields' => 'ids',
			);

			$search_term = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';

			if ( $search_term ) {
				$args['s'] = $search_term;
			}

			$transactions_obj = mvr_get_transactions( $args );

			return $transactions_obj->total_transactions;
		}

		/**
		 * Prepare the Transaction IDs.
		 *
		 * @since 1.0.0
		 * */
		private function prepare_transaction_ids() {
			$all_transaction_ids = mvr_get_transactions(
				array(
					'fields' => 'ids',
				)
			);

			/**
			 * Transaction ids in list table.
			 *
			 * @since 1.0.0
			 */
			$this->transaction_ids = apply_filters( 'mvr_admin_list_table_transaction_ids', $all_transaction_ids );
		}
	}

}
