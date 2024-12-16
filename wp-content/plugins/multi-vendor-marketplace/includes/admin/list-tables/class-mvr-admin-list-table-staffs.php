<?php
/**
 * Staff List Table.
 *
 * @package Multi-Vendor for WooCommerce/List Table
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


if ( ! class_exists( 'WC_Admin_List_Table', false ) ) {
	include_once WC_ABSPATH . '/includes/admin/list-tables/abstract-class-wc-admin-list-table.php';
}

if ( ! class_exists( 'MVR_Admin_List_Table_Staffs' ) ) {


	/**
	 * Vendors List Table.
	 *
	 * @class MVR_Admin_List_Table_Staffs
	 * @package Class
	 */
	class MVR_Admin_List_Table_Staffs extends WC_Admin_List_Table {

		/**
		 * Post type.
		 *
		 * @var String
		 */
		protected $list_table_type = MVR_Post_Types::MVR_STAFF;

		/**
		 * Product Objects
		 *
		 * @var Array
		 */
		protected $product_objs = array();

		/**
		 * Product Objects
		 *
		 * @var Array
		 */
		protected $order_objs = array();

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			parent::__construct();
			add_action( 'admin_notices', array( $this, 'bulk_admin_notices' ) );
			add_filter( 'get_search_query', array( $this, 'search_label' ) );
			add_filter( 'query_vars', array( $this, 'add_custom_query_var' ) );
			add_filter( "views_edit-{$this->list_table_type}", array( $this, 'remove_unwanted_post_status' ) );
		}

		/**
		 * Show blank slate.
		 *
		 * @since 1.0.0
		 * @param Array $views Post Status Views.
		 */
		public function remove_unwanted_post_status( $views ) {
			unset( $views['mine'] );

			return $views;
		}

		/**
		 * Show blank slate.
		 *
		 * @since 1.0.0
		 * @param String $which String which tablenav is being shown.
		 */
		public function maybe_render_blank_state( $which ) {
		}

		/**
		 * Handle any custom filters.
		 *
		 * @param array $query_vars Query vars.
		 * @return array
		 */
		protected function query_filters( $query_vars ) {
			if ( empty( $query_vars['orderby'] ) ) {
				$query_vars['orderby'] = 'ID';
			}

			if ( empty( $query_vars['order'] ) ) {
				$query_vars['order'] = 'DESC';
			}

			if ( ! empty( $_GET['_mvr_vendor'] ) ) {
				$query_vars['vendor_id'] = (int) $_GET['_mvr_vendor'];
			}

			return $query_vars;
		}

		/**
		 * See if we should render search filters or not.
		 */
		public function restrict_manage_posts() {
			global $typenow;

			if ( $typenow === $this->list_table_type ) {
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
		}

		/**
		 * Define primary column.
		 *
		 * @since 1.0.0
		 * @return String
		 */
		protected function get_primary_column() {
			return 'name';
		}

		/**
		 * Get row actions to show in the list table.
		 *
		 * @since 1.0.0
		 * @param Array   $actions Array of actions.
		 * @param WP_Post $post Current post object.
		 * @return Array
		 */
		protected function get_row_actions( $actions, $post ) {
			$this->prepare_row_data( $post->ID );

			/* translators: %s:ID */
			$actions           = array( 'id' => sprintf( esc_html__( 'ID: %s', 'multi-vendor-marketplace' ), $post->ID ) ) + $actions;
			$actions['delete'] = sprintf( '<a href="%s" class="mvr-delete-staff submitdelete" aria-label="Delete">%s</a>', get_delete_post_link( $post->ID, '', true ), __( 'Delete' ) );

			unset( $actions['trash'], $actions['inline hide-if-no-js'] );

			return $actions;
		}

		/**
		 * Define which columns are sortable.
		 *
		 * @since 1.0.0
		 * @param Array $columns Existing columns.
		 * @return Array
		 */
		public function define_sortable_columns( $columns ) {
			$custom = array(
				'ID'            => 'ID',
				'name'          => 'title',
				'last_modified' => 'date',
			);

			return wp_parse_args( $custom, $columns );
		}

		/**
		 * Define which columns to show on this screen.
		 *
		 * @since 1.0.0
		 * @param Array $columns Existing columns.
		 * @return Array
		 */
		public function define_columns( $columns ) {
			$keys         = array( 'name', 'vendor', 'status', 'created_date' );
			$labels       = mvr_get_staff_table_labels();
			$show_columns = array(
				'cb' => $columns['cb'],
			);

			foreach ( $keys as $key ) {
				$show_columns[ $key ] = ( isset( $labels[ $key ] ) ) ? $labels[ $key ] : '';
			}

			return $show_columns;
		}

		/**
		 * Define bulk actions.
		 *
		 * @since 1.0.0
		 * @param Array $actions Existing actions.
		 * @return Array
		 */
		public function define_bulk_actions( $actions ) {
			if ( isset( $actions['edit'] ) ) {
				unset( $actions['edit'], $actions['trash'] );

				$actions['activate']   = __( 'Activate', 'multi-vendor-marketplace' );
				$actions['deactivate'] = __( 'Deactivate', 'multi-vendor-marketplace' );
				$actions['_delete']    = __( 'Delete' );
			}

			return $actions;
		}

		/**
		 * Pre-fetch any data for the row each column has access to it.
		 *
		 * @since 1.0.0
		 * @param Integer $post_id Post ID being shown.
		 */
		protected function prepare_row_data( $post_id ) {
			if ( empty( $this->object ) || $this->object->get_id() !== $post_id ) {
				$this->object = mvr_get_staff( $post_id );
			}
		}

		/**
		 * Render column: name.
		 *
		 * @since 1.0.0
		 */
		protected function render_name_column() {
			/* translators: %1$s: Strong Start %2$s: Vendor Name */
			printf( esc_html__( '%1$s Name: %2$s', 'multi-vendor-marketplace' ), '<strong>', '<a href="' . esc_url( $this->object->get_admin_edit_url() ) . '">' . esc_attr( $this->object->get_name() ) . '</a></strong><br/>' );
			/* translators: %1$s: Strong Start %2$s: Email */
			printf( esc_html__( '%1$s Email: %2$s', 'multi-vendor-marketplace' ), '<strong>', '</strong>' . esc_attr( $this->object->get_email() ) );
		}

		/**
		 * Render column: vendor.
		 *
		 * @since 1.0.0
		 */
		protected function render_vendor_column() {
			$vendor_obj = $this->object->get_vendor();

			if ( mvr_is_vendor( $vendor_obj ) ) {
				/* translators: %1$s: Strong Start %2$s: Vendor Name */
				echo '<strong>', '<a href="' . esc_url( $vendor_obj->get_admin_edit_url() ) . '">' . esc_attr( $vendor_obj->get_name() ) . '</a></strong>';
			} else {
				esc_html_e( 'No Vendor Found', 'multi-vendor-marketplace' );
			}
		}

		/**
		 * Render column: Staff status.
		 *
		 * @since 1.0.0
		 */
		protected function render_status_column() {
			printf( '<mark class="mvr-post-status %s"><span>%s</span></mark>', esc_attr( sanitize_html_class( 'status-' . $this->object->get_status() ) ), esc_html( mvr_get_staff_status_name( $this->object->get_status() ) ) );
		}

		/**
		 * Render column: created_date.
		 *
		 * @since 1.0.0
		 */
		public function render_created_date_column() {
			$created_timestamp = $this->object->get_date_created() ? $this->object->get_date_created()->getTimestamp() : '';

			if ( ! $created_timestamp ) {
				echo '&ndash;';
				return;
			}

			// Check if the plan was created within the last 24 hours, and not in the future.
			if ( $created_timestamp > strtotime( '-1 day', time() ) && $created_timestamp <= time() ) {
				$show_date = sprintf(
					/* translators: %s: human-readable time difference */
					_x( '%s ago', '%s = human-readable time difference', 'multi-vendor-marketplace' ),
					human_time_diff( $this->object->get_date_created()->getTimestamp(), time() )
				);
			} else {
				$show_date = $this->object->get_date_created()->date_i18n( __( 'M j, Y', 'multi-vendor-marketplace' ) );
			}

			printf(
				'<time datetime="%1$s" title="%2$s">%3$s</time>',
				esc_attr( $this->object->get_date_created()->date( 'c' ) ),
				esc_html( $this->object->get_date_created()->date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ),
				esc_html( $show_date )
			);
		}

		/**
		 * Change the label when searching staff.
		 *
		 * @since 1.0.0
		 * @param Mixed $query Current search query.
		 * @return String
		 */
		public function search_label( $query ) {
			global $pagenow, $typenow;

			$request = $_GET;

			if ( 'edit.php' !== $pagenow || $this->list_table_type !== $typenow || ! get_query_var( "{$this->list_table_type}_search" ) || ! isset( $request['s'] ) ) {
				return $query;
			}

			return sanitize_text_field( wp_unslash( $request['s'] ) );
		}

		/**
		 * Query vars for custom searches.
		 *
		 * @since 1.0.0
		 * @param Mixed $public_query_vars Array of query vars.
		 * @return Array
		 */
		public function add_custom_query_var( $public_query_vars ) {
			$public_query_vars[] = "{$this->list_table_type}_search";

			return $public_query_vars;
		}

		/**
		 * Handle bulk actions.
		 *
		 * @since 1.0.0
		 * @param  String $redirect_to URL to redirect to.
		 * @param  String $action      Action name.
		 * @param  Array  $ids         List of ids.
		 * @return String
		 */
		public function handle_bulk_actions( $redirect_to, $action, $ids ) {
			$changed = 0;

			if ( 'activate' === $action ) {
				$report_action = 'staff_activated';

				foreach ( $ids as $id ) {
					$staff = mvr_get_staff( $id );

					if ( $staff ) {
						$staff->update_status( 'active' );
						$changed++;
					}
				}
			} elseif ( 'deactivate' === $action ) {
				$report_action = 'staff_deactivated';

				foreach ( $ids as $id ) {
					$staff = mvr_get_staff( $id );

					if ( $staff ) {
						$staff->update_status( 'inactive' );
						$changed++;
					}
				}
			} elseif ( '_delete' === $action ) {
				$report_action = 'staff_deleted';

				foreach ( $ids as $id ) {
					$staff = mvr_get_staff( $id );

					if ( $staff ) {
						$staff->delete( true );
						$changed++;
					}
				}
			}

			if ( $changed ) {
				$redirect_to = add_query_arg(
					array(
						'post_type'   => $this->list_table_type,
						'bulk_action' => $report_action,
						'changed'     => $changed,
						'ids'         => join( ',', $ids ),
					),
					$redirect_to
				);
			}

			return esc_url_raw( $redirect_to );
		}

		/**
		 * Show confirmation message that plan status changed for number of plans.
		 *
		 * @since 1.0.0
		 */
		public function bulk_admin_notices() {
			global $post_type, $pagenow;

			$request = $_REQUEST;

			// Bail out if not on plan list page.
			if ( 'edit.php' !== $pagenow || $this->list_table_type !== $post_type || ! isset( $request['bulk_action'] ) ) {
				return;
			}

			$number = isset( $request['changed'] ) ? absint( $request['changed'] ) : 0;

			if ( 'staff_deleted' === wc_clean( wp_unslash( $request['bulk_action'] ) ) ) {
				/* translators: %d: plans count */
				$message = sprintf( _n( '%d staff deleted.', '%d staff permanently deleted.', $number, 'multi-vendor-marketplace' ), number_format_i18n( $number ) );
			} else {
				/* translators: %d: plans count */
				$message = sprintf( _n( '%d staff status changed.', '%d staff statuses changed.', $number, 'multi-vendor-marketplace' ), number_format_i18n( $number ) );
			}

			echo '<div class="updated"><p>' . esc_html( $message ) . '</p></div>';
		}

		/**
		 * Staff Template
		 *
		 * @since 1.0.0
		 */
		public function add_staff_template() {
			?>
			<script type="text/template" id="tmpl-mvr-modal-add-staff">
				<?php include_once MVR_ABSPATH . 'includes/admin/views/html-admin-add-staff.php'; ?>
			</script>
			<?php
		}
	}
}
