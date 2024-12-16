<?php
/**
 * Notification List Table.
 *
 * @package Multi-Vendor/List Table
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'MVR_Admin_List_Table_Notification' ) ) {

	/**
	 * MVR_Admin_List_Table_Notification Class.
	 * */
	class MVR_Admin_List_Table_Notification extends WP_List_Table {

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
		 * Notification IDs.
		 *
		 * @var Array
		 * */
		private $notification_ids;

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
			$this->base_url = mvr_get_notification_page_url();

			$this->prepare_notification_ids();
			$this->prepare_current_url();
			$this->process_bulk_action();
			$this->get_current_pagenum();
			$this->get_current_enquiries();
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
			$keys         = array( 'ID', 'type', 'message', 'date' );
			$labels       = mvr_get_notification_table_labels();
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

			if ( ! wp_verify_nonce( $nonce, 'mvr-search_notification' ) ) {
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
						$notification_obj = mvr_get_notification( $id );

						if ( mvr_is_notification( $notification_obj ) ) {
							$notification_obj->delete();
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
			$status_array = array( 'all' => esc_html__( 'All', 'multi-vendor-marketplace' ) ) + mvr_get_notification_statuses();

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
		 * @param MVR_Notification $notification_obj Notification object.
		 * @return HTML
		 * */
		protected function column_cb( $notification_obj ) {
			return sprintf( '<input class="mvr-notification-cb" type="checkbox" name="id[]" value="%s" />', $notification_obj->get_id() );
		}

		/**
		 * Prepare the each column data.
		 *
		 * @since 1.0.0
		 * @param MVR_Notification $notification_obj Notification object.
		 * @param String           $column_name Name of the column.
		 * @return mixed
		 * */
		protected function column_default( $notification_obj, $column_name ) {
			switch ( $column_name ) {
				case 'ID':
					echo '#' . esc_attr( $notification_obj->get_id() );
					break;
				case 'type':
					echo esc_attr( mvr_notification_type_name( $notification_obj->get_source_from() ) );
					break;
				case 'message':
					echo wp_kses_post( $notification_obj->get_message() );
					break;
				case 'date':
					echo esc_html( $notification_obj->get_date_created()->date_i18n( wc_date_format() . ' ' . wc_time_format() ) );
					break;
			}
		}

		/**
		 * Get the current page items.
		 *
		 * @since 1.0.0
		 * */
		private function get_current_enquiries() {
			$status      = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
			$vendor_id   = isset( $_REQUEST['_mvr_vendor'] ) ? absint( wp_unslash( $_REQUEST['_mvr_vendor'] ) ) : '';
			$search_term = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';
			$args        = array(
				'status'  => ( $status && 'all' !== $status ) ? $status : array_keys( mvr_get_notification_statuses() ),
				'fields'  => 'objects',
				's'       => $search_term,
				'orderby' => isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : $this->orderby,
				'order'   => isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : $this->order,
				'limit'   => $this->limit,
				'page'    => $this->get_pagenum(),
				'to'      => 'admin',
			);

			if ( $vendor_id ) {
				$args['vendor_id'] = $vendor_id;
			}

			$notifications_obj = mvr_get_notifications( $args );
			$this->items       = $notifications_obj->notifications;
			$this->total_items = $notifications_obj->total_notifications;
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
				$status = array_keys( mvr_get_notification_statuses() );
			}

			$args = array(
				'status' => $status,
				'fields' => 'ids',
				'to'     => 'admin',
			);

			$search_term = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';

			if ( $search_term ) {
				$args['s'] = $search_term;
			}

			$notifications_obj = mvr_get_notifications( $args );

			return $notifications_obj->total_notifications;
		}

		/**
		 * Prepare the Commission IDs.
		 *
		 * @since 1.0.0
		 * */
		private function prepare_notification_ids() {
			$all_notification_ids = mvr_get_notifications(
				array(
					'fields' => 'ids',
					'to'     => 'admin',
				)
			);

			/**
			 * Notification ids in list table.
			 *
			 * @since 1.0.0
			 */
			$this->notification_ids = apply_filters( 'mvr_admin_list_table_notification_ids', $all_notification_ids );
		}
	}
}
