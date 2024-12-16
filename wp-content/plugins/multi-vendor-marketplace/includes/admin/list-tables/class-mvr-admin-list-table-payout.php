<?php
/**
 * Payout List Table.
 *
 * @package Multi-Vendor/List Table
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'MVR_Admin_List_Table_Payout' ) ) {

	/**
	 * MVR_Admin_List_Table_Payout Class.
	 * */
	class MVR_Admin_List_Table_Payout extends WP_List_Table {

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
		private $list_slug = 'mvr_payout';

		/**
		 * Payout IDs.
		 *
		 * @var array
		 * */
		private $payout_ids;

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
			$this->base_url = mvr_get_payout_page_url();

			$this->prepare_payout_ids();
			$this->prepare_current_url();
			$this->process_bulk_action();
			$this->get_current_pagenum();
			$this->get_current_payouts();
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

			$keys                = array( 'vendor', 'amount', 'email', 'status', 'date' );
			$payment_method      = isset( $_GET['payment_method'] ) ? sanitize_text_field( wp_unslash( $_GET['payment_method'] ) ) : '1';
			$bank_transfer_count = $this->get_item_count_for_payment_method( '1' );
			$paypal_count        = $this->get_item_count_for_payment_method( '2' );

			if ( '2' === $payment_method || ( 0 === $bank_transfer_count && 0 < $paypal_count ) ) {
				$keys[] = 'batch_id';
			}

			$labels       = mvr_get_payout_table_labels();
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
		 * Processes the bulk action.
		 *
		 * @since 1.0.0
		 * */
		public function process_bulk_action() {
			if ( ! isset( $_REQUEST['_mvr_nonce'] ) ) {
				return;
			}

			$nonce = sanitize_key( wp_unslash( $_REQUEST['_mvr_nonce'] ) );

			if ( ! wp_verify_nonce( $nonce, 'mvr-search_payout' ) ) {
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
						$payout_obj = mvr_get_payout( $id );

						if ( mvr_is_payout( $payout_obj ) ) {
							$payout_obj->delete();
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
		protected function get_available_payouts() {
			$payment_methods = mvr_payment_method_options();

			foreach ( $payment_methods as $key => $value ) {
				$count = $this->get_item_count_for_payment_method( $key );

				if ( ! $count ) {
					continue;
				}

				return (int) $key;
			}

			return 1;
		}

		/**
		 * Display the list of views available on this table.
		 *
		 * @return array
		 * */
		protected function get_views() {
			$args            = array();
			$views           = array();
			$payment_method  = isset( $_GET['payment_method'] ) ? absint( wp_unslash( $_GET['payment_method'] ) ) : $this->get_available_payouts();
			$payment_methods = mvr_payment_method_options();
			$last_payment    = array_key_last( $payment_methods );

			foreach ( $payment_methods as $key => $value ) {
				$count = $this->get_item_count_for_payment_method( $key );

				if ( ! $count ) {
					continue;
				}

				$args['payment_method'] = $key;
				$label                  = $value . ' (' . $count . ')';
				$class                  = array( sanitize_key( strtolower( $value ) ) );

				if ( $payment_method === $key ) {
					$class[] = 'current';
				}

				$class_key   = 'mvr-' . sanitize_key( strtolower( $value ) );
				$search_term = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';

				if ( $search_term ) {
					$args['s'] = $search_term;
				}

				$views[ $class_key ] = $this->get_edit_link( $args, $label, implode( ' ', $class ) );
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
		 * @param MVR_Payout $payout_obj Payout object.
		 * @return HTML
		 * */
		protected function column_cb( $payout_obj ) {
			return sprintf( '<input class="mvr-payout-cb" type="checkbox" name="id[]" value="%s" />', $payout_obj->get_id() );
		}

		/**
		 * Prepare the each column data.
		 *
		 * @since 1.0.0
		 * @param MVR_Payout $payout_obj Payout object.
		 * @param String     $column_name Name of the column.
		 * @return mixed
		 * */
		protected function column_default( $payout_obj, $column_name ) {
			switch ( $column_name ) {
				case 'vendor':
					$vendor_obj = $payout_obj->get_vendor();
					$actions    = array();

					/* translators: %s: payout ID */
					$actions ['id'] = sprintf( esc_html__( 'ID: %s', 'multi-vendor-marketplace' ), $payout_obj->get_id() );

					if ( mvr_is_vendor( $vendor_obj ) ) {
						$views = '<a class="row-title" href="' . esc_url( get_edit_post_link( $vendor_obj->get_id() ) ) . '">' . esc_html( $vendor_obj->get_name() ) . '</a> <br/> <div class="row-actions">';
					} else {
						$views = esc_html__( 'Not found', 'multi-vendor-marketplace' ) . ' <br/> <div class="row-actions">';
					}

					$actions ['delete'] = '<a class="mvr-delete-payout mvr-delete" href="' . esc_url(
						add_query_arg(
							array(
								'action' => 'delete',
								'id'     => $payout_obj->get_id(),
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
					echo wp_kses_post( wc_price( $payout_obj->get_amount() ) );
					break;
				case 'date':
					echo esc_html( $payout_obj->get_date_created()->date_i18n( wc_date_format() . ' ' . wc_time_format() ) );
					break;
				case 'email':
					echo esc_html( $payout_obj->get_email() );
					break;
				case 'batch_id':
					echo esc_html( $payout_obj->get_batch_id() );
					break;
				case 'status':
					printf( '<mark class="mvr-post-status %s"><span>%s</span></mark>', esc_attr( sanitize_html_class( 'status-' . $payout_obj->get_status() ) ), esc_html( mvr_get_payout_status_name( $payout_obj->get_status() ) ) );
					break;
			}
		}

		/**
		 * Get the current page items.
		 *
		 * @since 1.0.0
		 * */
		private function get_current_payouts() {
			$request        = $_REQUEST;
			$vendor_id      = isset( $request['_mvr_vendor'] ) ? absint( wp_unslash( $request['_mvr_vendor'] ) ) : '';
			$payment_method = isset( $request['payment_method'] ) ? absint( wp_unslash( $request['payment_method'] ) ) : $this->get_available_payouts();
			$search_term    = isset( $request['s'] ) ? sanitize_text_field( wp_unslash( $request['s'] ) ) : '';
			$args           = array(
				'payment_method' => $payment_method,
				'fields'         => 'objects',
				's'              => $search_term,
				'orderby'        => isset( $request['orderby'] ) ? sanitize_text_field( wp_unslash( $request['orderby'] ) ) : $this->orderby,
				'order'          => isset( $request['order'] ) ? sanitize_text_field( wp_unslash( $request['order'] ) ) : $this->order,
				'limit'          => $this->limit,
				'page'           => $this->get_pagenum(),
			);

			if ( $vendor_id ) {
				$args['vendor_id'] = $vendor_id;
			}

			$payouts_obj = mvr_get_payouts( $args );

			$this->items       = $payouts_obj->payouts;
			$this->total_items = $payouts_obj->total_payouts;
		}

		/**
		 * Get the item count for the payment method.
		 *
		 * @since 1.0.0
		 * @param String $payment_method Payment Method.
		 * @return Integer
		 * */
		private function get_item_count_for_payment_method( $payment_method = '' ) {
			if ( empty( $payment_method ) ) {
				$payment_method = isset( $_GET['payment_method'] ) ? absint( wp_unslash( $_GET['payment_method'] ) ) : 1;
			}

			$args = array(
				'payment_method' => $payment_method,
				'fields'         => 'ids',
			);

			$search_term = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';

			if ( $search_term ) {
				$args['s'] = $search_term;
			}

			$payouts_obj = mvr_get_payouts( $args );

			return $payouts_obj->total_payouts;
		}

		/**
		 * Prepare the Payout IDs.
		 *
		 * @since 1.0.0
		 * */
		private function prepare_payout_ids() {
			$all_payout_ids = mvr_get_payouts(
				array(
					'fields' => 'ids',
				)
			);

			/**
			 * Payout ids in list table.
			 *
			 * @since 1.0.0
			 */
			$this->payout_ids = apply_filters( 'mvr_admin_list_table_payout_ids', $all_payout_ids );
		}
	}

}
