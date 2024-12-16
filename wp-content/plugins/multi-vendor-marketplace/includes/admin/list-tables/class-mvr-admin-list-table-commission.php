<?php
/**
 * Commission List Table.
 *
 * @package Multi-Vendor/List Table
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'MVR_Admin_List_Table_Commission' ) ) {

	/**
	 * MVR_Admin_List_Table_Commission Class.
	 * */
	class MVR_Admin_List_Table_Commission extends WP_List_Table {

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
		 * Commission IDs.
		 *
		 * @var array
		 * */
		private $commission_ids;

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
		 *
		 * @since 1.0.0
		 * */
		public function prepare_items() {
			global $wpdb;
			$this->database = $wpdb;
			$this->base_url = mvr_get_commission_page_url();

			$this->prepare_current_url();
			$this->get_current_pagenum();
			$this->get_current_commissions();
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
			$keys         = array( 'name', 'from', 'status', 'amount', 'vendor_amount', 'date' );
			$labels       = mvr_get_commission_table_labels();
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

			if ( ! wp_verify_nonce( $nonce, 'mvr-search_commission' ) ) {
				return;
			}

			$action = $this->current_action();
			$ids    = isset( $_REQUEST['id'] ) ? wc_clean( wp_unslash( $_REQUEST['id'] ) ) : array();
			$ids    = ! is_array( $ids ) ? explode( ',', $ids ) : $ids;

			if ( ! mvr_check_is_array( $ids ) ) {
				return;
			}

			foreach ( $ids as $id ) {
				switch ( $action ) {
					case 'delete':
						$commission_obj = mvr_get_commission( $id );

						if ( mvr_is_commission( $commission_obj ) ) {
							$commission_obj->delete();
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
			$status_array = mvr_get_commission_statuses();
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
		 * @param MVR_Commission $commission_obj Commission object.
		 * @return HTML
		 * */
		protected function column_cb( $commission_obj ) {
			return sprintf( '<input class="mvr-commission-cb" type="checkbox" name="id[]" value="%s" />', $commission_obj->get_id() );
		}

		/**
		 * Prepare the each column data.
		 *
		 * @since 1.0.0
		 * @param MVR_Commission $commission_obj Commission object.
		 * @param String         $column_name Name of the column.
		 * @return mixed
		 * */
		protected function column_default( $commission_obj, $column_name ) {
			switch ( $column_name ) {
				case 'name':
					$vendor_obj = $commission_obj->get_vendor();
					$vendor     = mvr_is_vendor( $vendor_obj ) ? $vendor_obj->get_name() : '';
					$actions    = array();

					/* translators: %s: Commission ID */
					$actions ['id'] = sprintf( esc_html__( 'ID: %s', 'multi-vendor-marketplace' ), $commission_obj->get_id() );

					if ( mvr_is_vendor( $vendor_obj ) ) {
						echo '<a href="#" class="mvr-commission-preview" data-commission_id="' . absint( $commission_obj->get_id() ) . '" title="' . esc_html__( 'Preview', 'multi-vendor-marketplace' ) . '">' . esc_html__( 'Preview', 'multi-vendor-marketplace' ) . '</a>';
						$views = '<a class="row-title" href="' . esc_url( $commission_obj->get_admin_edit_url() ) . '" class="commission-view"><strong>' . esc_html( $vendor ) . '</strong></a> <br/> <div class="row-actions">';
					} else {
						$views = esc_html__( 'Not found', 'multi-vendor-marketplace' ) . ' <br/> <div class="row-actions">';
					}

					$actions ['delete'] = '<a class="mvr-delete-commission mvr-delete" href="' . esc_url(
						add_query_arg(
							array(
								'action' => 'delete',
								'id'     => $commission_obj->get_id(),
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
				case 'from':
					$source_id = $commission_obj->get_source_id();

					if ( 'order' === $commission_obj->get_source_from() ) {
						$order_obj = $commission_obj->get_order();

						if ( ! is_a( $order_obj, 'WC_Order' ) ) {
							/* translators: %1$s: Strong Start %2$s: Order ID */
							printf( esc_html__( '%1$s Order ID: %2$s', 'multi-vendor-marketplace' ), '<strong>', '#' . esc_attr( $commission_obj->get_source_id() ) . '</strong><br/>' );
						} else {
							/* translators: %1$s: Strong Start %2$s: Order ID */
							printf( esc_html__( '%1$s Order ID: %2$s', 'multi-vendor-marketplace' ), '<strong>', '<a href="' . esc_url( $commission_obj->get_order()->get_edit_order_url() ) . '">#' . esc_attr( $commission_obj->get_source_id() ) . '</a></strong><br/>' );
						}
					} elseif ( 'withdraw' === $commission_obj->get_source_from() ) {
						$withdraw_obj = $commission_obj->get_withdraw();

						if ( ! mvr_is_withdraw( $withdraw_obj ) ) {
							/* translators: %1$s: Strong Start %2$s: Withdraw ID */
							printf( esc_html__( '%1$s Withdrawal ID: %2$s', 'multi-vendor-marketplace' ), '<strong>', '#' . esc_attr( $commission_obj->get_source_id() ) . '</strong><br/>' );
						} else {
							/* translators: %1$s: Strong Start %2$s: Withdraw ID */
							printf( esc_html__( '%1$s Withdrawal ID: %2$s', 'multi-vendor-marketplace' ), '<strong>', '<a href="' . esc_url( $withdraw_obj->get_admin_view_url() ) . '">#' . esc_attr( $commission_obj->get_source_id() ) . '</a></strong><br/>' );
						}
					}

					break;
				case 'status':
					printf( '<mark class="mvr-post-status %s"><span>%s</span></mark>', esc_attr( sanitize_html_class( 'status-' . $commission_obj->get_status() ) ), esc_html( mvr_get_commission_status_name( $commission_obj->get_status() ) ) );
					break;
				case 'amount':
					echo wp_kses_post( wc_price( $commission_obj->get_amount(), true ) );
					break;
				case 'vendor_amount':
					echo wp_kses_post( wc_price( $commission_obj->get_vendor_amount(), true ) );
					break;
				case 'date':
					echo esc_html( $commission_obj->get_date_created()->date_i18n( wc_date_format() . ' ' . wc_time_format() ) );
					break;
			}
		}

		/**
		 * Get the current page items.
		 *
		 * @since 1.0.0
		 * */
		private function get_current_commissions() {
			$request     = $_REQUEST;
			$vendor_id   = isset( $request['_mvr_vendor'] ) ? absint( wp_unslash( $request['_mvr_vendor'] ) ) : '';
			$status      = isset( $request['status'] ) ? sanitize_text_field( wp_unslash( $request['status'] ) ) : '';
			$search_term = isset( $request['s'] ) ? sanitize_text_field( wp_unslash( $request['s'] ) ) : '';
			$args        = array(
				'status'  => ( $status && 'all' !== $status ) ? $status : array_keys( mvr_get_commission_statuses() ),
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

			$commissions_obj = mvr_get_commissions( $args );

			$this->items       = $commissions_obj->commissions;
			$this->total_items = $commissions_obj->total_commissions;
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
				$status = array_keys( mvr_get_commission_statuses() );
			}

			$args = array(
				'status' => $status,
				'fields' => 'ids',
			);

			$search_term = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';

			if ( $search_term ) {
				$args['s'] = $search_term;
			}

			$commissions_obj = mvr_get_commissions( $args );

			return $commissions_obj->total_commissions;
		}

		/**
		 * Prepare the Commission IDs.
		 *
		 * @since 1.0.0
		 * */
		private function prepare_commission_ids() {
			$all_commission_ids = mvr_get_commissions(
				array(
					'fields' => 'ids',
				)
			);

			/**
			 * Commission ids in list table.
			 *
			 * @since 1.0.0
			 */
			$this->commission_ids = apply_filters( 'mvr_admin_list_table_commission_ids', $all_commission_ids );
		}

		/**
		 * Get commission details to send to the ajax endpoint for previews.
		 *
		 * @since 1.0.0
		 * @param MVR_Commission $commission_obj Commission object.
		 * @return Array
		 */
		public static function commission_preview_get_commission_details( $commission_obj ) {
			if ( ! mvr_is_commission( $commission_obj ) ) {
				return array();
			}

			/**
			 * Commission Preview details
			 *
			 * @since 1.0.0
			 */
			return apply_filters(
				'mvr_admin_commission_preview_get_commission_details',
				array(
					'commission_id' => $commission_obj->get_id(),
					'vendor_name'   => $commission_obj->get_vendor()->get_name(),
					'order_id'      => $commission_obj->get_source_id(),
					'date'          => $commission_obj->get_date_created()->date_i18n( wc_date_format() . ' ' . wc_time_format() ),
					'tax'           => mvr_tax_type_options( $commission_obj->get_vendor()->get_tax_to() ),
					'item_html'     => self::get_commission_preview_item_html( $commission_obj ),
					'actions_html'  => self::get_commission_preview_actions_html( $commission_obj ),
					'type'          => $commission_obj->get_source_from(),
					'status'        => $commission_obj->get_status(),
					'status_name'   => mvr_get_commission_status_name( $commission_obj->get_status() ),
				),
				$commission_obj
			);
		}

		/**
		 * Get items to display in the preview as HTML.
		 *
		 * @since 1.0.0
		 * @param MVR_Commission $commission_obj Commission object.
		 * @return String
		 */
		public static function get_commission_preview_item_html( $commission_obj ) {
			$html = '<div class="wc-order-preview-table-wrapper">
						<table cellspacing="0" class="wc-order-preview-table">';

			if ( 'withdraw' === $commission_obj->get_source_from() ) {
				/**
				 * Admin Commission Preview Line Item Columns
				 *
				 * @since 1.0.0
				 */
				$columns = apply_filters(
					'mvr_admin_commission_preview_line_item_columns',
					array(
						'amount'         => esc_html__( 'Withdraw Amount', 'multi-vendor-marketplace' ),
						'charge'         => esc_html__( 'Withdraw Charge', 'multi-vendor-marketplace' ),
						'status'         => esc_html__( 'Status', 'multi-vendor-marketplace' ),
						'payment_method' => esc_html__( 'Payment Method', 'multi-vendor-marketplace' ),
					),
					$commission_obj
				);

				$html .= '<thead>
				<tr>';

				foreach ( $columns as $column => $label ) {
					$html .= '<th class="wc-order-preview-table__column--' . esc_attr( $column ) . '">' . esc_html( $label ) . '</th>';
				}

				$html .= '</tr>
					</thead>
				<tbody>';

				$withdraw_id  = $commission_obj->get_source_id();
				$withdraw_obj = mvr_get_withdraw( $withdraw_id );

				if ( ! mvr_is_withdraw( $withdraw_obj ) ) {
					esc_html_e( 'Not Found', 'multi-vendor-marketplace' );
				} else {
					$html .= '<tr class="mvr-commission-preview--' . esc_attr( $withdraw_id ) . '">';

					foreach ( $columns as $column => $label ) {
						$html .= '<td class="wc-order-preview-table__column--' . esc_attr( $column ) . '">';

						switch ( $column ) {
							case 'amount':
								$html .= wc_price( $withdraw_obj->get_amount() );
								break;
							case 'charge':
								$html .= wc_price( $withdraw_obj->get_charge_amount(), array( 'currency' => $withdraw_obj->get_currency() ) );
								break;
							case 'status':
								$html .= '<mark class="mvr-post-status ' . sanitize_html_class( 'status-' . $withdraw_obj->get_status() ) . '"><span>' . mvr_get_withdraw_status_name( $withdraw_obj->get_status() ) . '</span></mark>';
								break;
							case 'payment_method':
								$html .= mvr_payment_method_options( $withdraw_obj->get_payment_method() );
								break;
							default:
								/**
								 * Commission Default Column
								 *
								 * @since 1.0.0
								 */
								$html .= apply_filters( 'mvr_admin_commission_preview_line_item_column_' . sanitize_key( $column ), '', $commission_obj );
								break;
						}
						$html .= '</td>';
					}

					$html .= '</tr>';
				}

				$html .= '</tbody>';
			} else {
				/**
				 * Commission Preview Item HTML
				 *
				 * @since 1.0.0
				 */
				$hidden_order_itemmeta = apply_filters(
					'woocommerce_hidden_order_itemmeta',
					array(
						'_qty',
						'_tax_class',
						'_product_id',
						'_variation_id',
						'_line_subtotal',
						'_line_subtotal_tax',
						'_line_total',
						'_line_tax',
						'method_id',
						'cost',
						'_reduced_stock',
						'_restock_refunded_items',
					)
				);

				/**
				 * Admin Commission Preview Line Item
				 *
				 * @since 1.0.0
				 */
				$line_items = apply_filters( 'mvr_admin_commission_preview_line_items', $commission_obj->get_products(), $commission_obj );

				/**
				 * Admin Commission Preview Line Item Columns
				 *
				 * @since 1.0.0
				 */
				$columns = apply_filters(
					'mvr_admin_commission_preview_line_item_columns',
					array(
						'product'    => esc_html__( 'Product', 'multi-vendor-marketplace' ),
						'quantity'   => esc_html__( 'Quantity', 'multi-vendor-marketplace' ),
						'tax'        => esc_html__( 'Tax', 'multi-vendor-marketplace' ),
						'total'      => esc_html__( 'Total', 'multi-vendor-marketplace' ),
						'commission' => esc_html__( 'Commission', 'multi-vendor-marketplace' ),
					),
					$commission_obj
				);

				if ( ! wc_tax_enabled() ) {
					unset( $columns['tax'] );
				}

				$html .= '<thead>
					<tr>';

				foreach ( $columns as $column => $label ) {
					$html .= '<th class="wc-order-preview-table__column--' . esc_attr( $column ) . '">' . esc_html( $label ) . '</th>';
				}

				$html .= '</tr>
					</thead>
				<tbody>';

				foreach ( $line_items as $item_id => $item_args ) {
					$item        = isset( $item_args['item'] ) ? $item_args['item'] : array();
					$product_obj = wc_get_product( $item->get_product_id() );

					/**
					 * Commission preview Row Class
					 *
					 * @since 1.0.0
					 */
					$row_class = apply_filters( 'mvr_admin_html_commission_preview_item_class', '', $item, $commission_obj );
					$html     .= '<tr class="wc-order-preview-table__item wc-order-preview-table__item--' . esc_attr( $item_id ) . ( $row_class ? ' ' . esc_attr( $row_class ) : '' ) . '">';

					foreach ( $columns as $column => $label ) {
						$html .= '<td class="wc-order-preview-table__column--' . esc_attr( $column ) . '">';

						switch ( $column ) {
							case 'product':
								$html .= wp_kses_post( $item->get_name() );

								if ( $product_obj ) {
									$html .= '<div class="wc-order-item-sku">' . esc_html( $product_obj->get_sku() ) . '</div>';
								}

								$meta_data = $item->get_all_formatted_meta_data( '' );

								if ( $meta_data ) {
									$html .= '<table cellspacing="0" class="wc-order-item-meta">';

									foreach ( $meta_data as $meta_id => $meta ) {
										if ( in_array( $meta->key, $hidden_order_itemmeta, true ) ) {
											continue;
										}

										$html .= '<tr><th>' . wp_kses_post( $meta->display_key ) . ':</th><td>' . wp_kses_post( force_balance_tags( $meta->display_value ) ) . '</td></tr>';
									}

									$html .= '</table>';
								}
								break;
							case 'quantity':
								$html .= esc_html( $item->get_quantity() );
								break;
							case 'tax':
								$html .= wc_price( $item->get_total_tax(), array( 'currency' => $commission_obj->get_currency() ) );
								break;
							case 'total':
								$html .= wc_price( $item->get_total(), array( 'currency' => $commission_obj->get_currency() ) );
								break;
							case 'commission':
								$html .= wc_price( $item_args['commission'], array( 'currency' => $commission_obj->get_currency() ) );
								break;
							default:
								/**
								 * Commission Default Column
								 *
								 * @since 1.0.0
								 */
								$html .= apply_filters( 'mvr_admin_commission_preview_line_item_column_' . sanitize_key( $column ), '', $commission_obj );
								break;
						}
						$html .= '</td>';
					}

					$html .= '</tr>';
				}

				$html .= '</tbody>';
			}

			$html .= '</table>
				</div>';

			return $html;
		}

		/**
		 * Get items to display in the preview as HTML.
		 *
		 * @since 1.0.0
		 * @param MVR_Commission $commission_obj Commission object.
		 * @return String
		 */
		public static function get_commission_preview_actions_html( $commission_obj ) {
			$actions = array();

			/**
			 * Commission Actions
			 *
			 * @since 1.0.0
			 */
			$actions = apply_filters( 'mvr_admin_commission_preview_actions', $actions, $commission_obj );

			return wc_render_action_buttons( $actions );
		}
	}

}
