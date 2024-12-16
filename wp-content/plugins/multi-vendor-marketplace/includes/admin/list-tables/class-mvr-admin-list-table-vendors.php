<?php
/**
 * Vendors List Table.
 *
 * @package Multi-Vendor for WooCommerce/List Table
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


if ( ! class_exists( 'WC_Admin_List_Table', false ) ) {
	include_once WC_ABSPATH . '/includes/admin/list-tables/abstract-class-wc-admin-list-table.php';
}

if ( ! class_exists( 'MVR_Admin_List_Table_Vendors' ) ) {


	/**
	 * Vendors List Table.
	 *
	 * @class MVR_Admin_List_Table_Vendors
	 * @package Class
	 */
	class MVR_Admin_List_Table_Vendors extends WC_Admin_List_Table {

		/**
		 * Post type.
		 *
		 * @var String
		 */
		protected $list_table_type = MVR_Post_Types::MVR_VENDOR;

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

			return $query_vars;
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
			$actions                 = array( 'id' => sprintf( esc_html__( 'ID: %s', 'multi-vendor-marketplace' ), $post->ID ) ) + $actions;
			$actions['commission']   = sprintf( '<a href="%s" class="mvr-commission" aria-label="Commission">%s</a>', mvr_get_commission_page_url( array( '_mvr_vendor' => $post->ID ) ), esc_html__( 'Commissions', 'multi-vendor-marketplace' ) );
			$actions['withdraw']     = sprintf( '<a href="%s" class="mvr-withdraw" aria-label="Withdraw">%s</a>', mvr_get_withdraw_page_url( array( '_mvr_vendor' => $post->ID ) ), esc_html__( 'Withdraw', 'multi-vendor-marketplace' ) );
			$actions['transaction']  = sprintf( '<a href="%s" class="mvr-transaction" aria-label="Transaction">%s</a>', mvr_get_transaction_page_url( array( '_mvr_vendor' => $post->ID ) ), esc_html__( 'Transaction', 'multi-vendor-marketplace' ) );
			$actions['payout']       = sprintf( '<a href="%s" class="mvr-payout" aria-label="Payout">%s</a>', mvr_get_payout_page_url( array( '_mvr_vendor' => $post->ID ) ), esc_html__( 'Payout', 'multi-vendor-marketplace' ) );
			$actions['notification'] = sprintf( '<a href="%s" class="mvr-notification" aria-label="Notification">%s</a>', mvr_get_notification_page_url( array( '_mvr_vendor' => $post->ID ) ), esc_html__( 'Notification', 'multi-vendor-marketplace' ) );
			$actions['enquiry']      = sprintf( '<a href="%s" class="mvr-enquiry" aria-label="Enquiry">%s</a>', mvr_get_enquiry_page_url( array( '_mvr_vendor' => $post->ID ) ), esc_html__( 'Enquiry', 'multi-vendor-marketplace' ) );
			$actions['staff']        = sprintf( '<a href="%s" class="mvr-staff" aria-label="Staff">%s</a>', add_query_arg( array( '_mvr_vendor' => $post->ID ), admin_url( 'edit.php?post_type=mvr_staff' ) ), esc_html__( 'Staff', 'multi-vendor-marketplace' ) );
			$actions['review']       = sprintf( '<a href="%s" class="mvr-review" aria-label="Review">%s</a>', mvr_get_review_page_url( array( '_mvr_vendor' => $post->ID ) ), esc_html__( 'Review', 'multi-vendor-marketplace' ) );
			$actions['delete']       = sprintf( '<a href="%s" class="mvr-vendor-delete submitdelete" aria-label="Delete">%s</a>', get_delete_post_link( $post->ID, '', true ), __( 'Delete' ) );

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
				'name'          => 'ID',
				'store_name'    => 'title',
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
			$keys         = array( 'name', 'amount', 'tax_shipping', 'commission', 'property', 'status', 'subscription', 'created_date' );
			$labels       = mvr_get_vendor_table_labels();
			$show_columns = array(
				'cb' => $columns['cb'],
			);

			foreach ( $keys as $key ) {
				if ( 'subscription' === $key && ( ! class_exists( 'WC_Subscriptions' ) || 'yes' !== get_option( 'mvr_settings_enable_vendor_subscription', 'no' ) || empty( get_option( 'mvr_settings_subscription_product' ) ) ) ) {
					continue;
				}

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
				$this->object       = mvr_get_vendor( $post_id );
				$this->product_objs = wc_get_products(
					array(
						'mvr_include_vendor' => $post_id,
					)
				);
				$this->order_objs   = $this->object->get_orders(
					array(
						'vendor_id' => $post_id,
					)
				);
			}
		}

		/**
		 * Render column: name.
		 *
		 * @since 1.0.0
		 */
		protected function render_name_column() {
			/* translators: %1$s: Strong Start %2$s: Vendor Name */
			printf( esc_html__( '%1$s Vendor Name: %2$s', 'multi-vendor-marketplace' ), '<strong>', '<a href="' . esc_url( $this->object->get_admin_edit_url() ) . '">' . esc_attr( $this->object->get_name() ) . '</a></strong><br/>' );
			/* translators: %1$s: Strong Start %2$s: Store Name */
			printf( esc_html__( '%1$s Store Name: %2$s', 'multi-vendor-marketplace' ), '<strong>', '</strong>' . esc_attr( $this->object->get_shop_name() ) . '<br/>' );
			/* translators: %1$s: Strong Start %2$s: Email */
			printf( esc_html__( '%1$s Email: %2$s', 'multi-vendor-marketplace' ), '<strong>', '</strong>' . esc_attr( $this->object->get_email() ) );
		}

		/**
		 * Render column: amount.
		 *
		 * @since 1.0.0
		 */
		protected function render_amount_column() {
			echo '<a href="' . esc_url( mvr_get_transaction_page_url( array( '_mvr_vendor' => $this->object->get_id() ) ) ) . '"><strong>' . wp_kses_post( wc_price( $this->object->get_total_amount() ) ) . '</strong>';

			if ( ! empty( $this->object->get_amount() ) &&
				$this->object->get_amount() > (float) get_option( 'mvr_settings_min_withdraw_threshold' ) &&
				'yes' === get_option( 'mvr_settings_allow_vendor_withdraw_req', 'no' ) &&
				in_array( $this->object->get_payment_method(), get_option( 'mvr_settings_withdraw_allow_payment', array() ), true )
				) {
				echo '<br/><a href="#" class="mvr-vendor-payment button" data-vendor_id="' . absint( $this->object->get_id() ) . '" title="' . esc_html__( 'Pay', 'multi-vendor-marketplace' ) . '">' . esc_html__( 'Pay', 'multi-vendor-marketplace' ) . '</a>';
			}
		}

		/**
		 * Render column: vendor_status.
		 *
		 * @since 1.0.0
		 */
		protected function render_status_column() {
			$status_name = mvr_get_vendor_status_name( $this->object->get_status() );

			if ( 'pending' === $this->object->get_status() ) {
				if ( ! $this->object->cleared_form_filling() ) {
					$status_name = esc_html__( 'Form Filling', 'multi-vendor-marketplace' );
				}
			}

			printf( '<mark class="mvr-post-status %s"><span>%s</span></mark>', esc_attr( sanitize_html_class( 'status-' . $this->object->get_status() ) ), esc_html( $status_name ) );
		}

		/**
		 * Render column: Subscription.
		 *
		 * @since 1.0.0
		 */
		protected function render_subscription_column() {
			if ( ! $this->object->has_subscribed() ) {
				printf( '<mark class="mvr-post-status status-unsubscribed"><span>%s</span></mark>', esc_html__( 'Unsubscribed', 'multi-vendor-marketplace' ) );
			} else {
				printf( '<mark class="mvr-post-status status-subscribed"><span>%s</span></mark>', esc_html__( 'Subscribed', 'multi-vendor-marketplace' ) );

				if ( $this->object->get_subscription() ) {
					if ( function_exists( 'wcs_get_subscription_status_name' ) ) {
						/* translators: %1$s: Strong Start %2$s: Subscription Status */
						printf( esc_html__( '%1$s Status: %2$s', 'multi-vendor-marketplace' ), '<br/><strong>', '</strong>' . esc_attr( esc_html( wcs_get_subscription_status_name( $this->object->get_subscription_status() ) ) ) );
					}

					/* translators: %1$s: Strong Start %2$s: Subscription ID */
					printf( esc_html__( '%1$s ID: %2$s', 'multi-vendor-marketplace' ), '<br/><strong>', '<a href="' . esc_url( $this->object->get_subscription()->get_edit_order_url() ) . '">' . esc_attr( $this->object->get_subscription()->get_order_number() ) . '</a></strong>' );
				}
			}
		}

		/**
		 * Render column: Tax & Shipping Column.
		 *
		 * @since 1.0.0
		 */
		protected function render_tax_shipping_column() {
			$commission_settings = $this->object->get_commission_settings();
			$tax                 = mvr_tax_type_options( $commission_settings['tax_to'] );

			/* translators: %1$s:Strong Start %2$s:Strong end Tax Value */
			printf( esc_html__( '%1$s Tax : %2$s', 'multi-vendor-marketplace' ), '<strong>', '</strong>' . esc_html( $tax ) );
		}

		/**
		 * Render column: commission.
		 *
		 * @since 1.0.0
		 */
		protected function render_commission_column() {
			$commission_settings = $this->object->get_commission_settings();
			$commission_from     = mvr_commission_from_options( $commission_settings['from'] );

			printf(
				/* translators: %1$s: Strong , %2$s Strong close and Commission From */
				esc_html__( '%1$s From: %2$s', 'multi-vendor-marketplace' ),
				'<strong>',
				'</strong>' . esc_html( $commission_from )
			);

			$commission_type = mvr_commission_type_options( $commission_settings['type'] );

			printf(
				/* translators: %1$s: Strong , %2$s Strong close and Commission Type */
				esc_html__( '%1$s Type: %2$s', 'multi-vendor-marketplace' ),
				'<br/><strong>',
				'</strong>' . esc_html( $commission_type )
			);

			$commission = ( '2' === $commission_settings['type'] ) ? $commission_settings['value'] . '%' : wc_price( $commission_settings['value'] );

			printf(
				/* translators: %1$s: Strong , %2$s Strong close and Commission */
				esc_html__( '%1$s Commission: %2$s', 'multi-vendor-marketplace' ),
				'<br/><strong>',
				'</strong>' . wp_kses_post( $commission )
			);

			if ( '1' !== $commission_settings['criteria'] ) {
				$criteria       = mvr_commission_criteria_options( $commission_settings['criteria'] );
				$criteria_value = ( '2' !== $commission_settings['criteria'] ) ? wc_price( $commission_settings['criteria_value'] ) : $commission_settings['criteria_value'];

				printf(
					/* translators: %1$s: Strong , %2$s Strong close, 3$s Criteria, %4$s Criteria Value */
					esc_html__( '%1$s Criteria: %2$s Greater then %3$s of %4$s', 'multi-vendor-marketplace' ) . '</strong>',
					'<br/><strong>',
					'</strong>',
					esc_html( $criteria ),
					wp_kses_post( $criteria_value )
				);
			}
		}

		/**
		 * Render column: property.
		 *
		 * @since 1.0.0
		 */
		protected function render_property_column() {
			/* translators: %1$s: Strong Start %2$s: Products Count */
			printf( esc_html__( '%1$s Product(s): %2$s', 'multi-vendor-marketplace' ), '<strong>', '<a href="' . esc_url( admin_url( 'edit.php?post_type=product&_mvr_vendor=' . $this->object->get_id() ) ) . '">' . esc_attr( count( $this->product_objs ) ) . '</a></strong><br/>' );
			/* translators: %1$s: Strong Start %2$s: Orders Count */
			printf( esc_html__( '%1$s Order(s): %2$s', 'multi-vendor-marketplace' ), '<strong>', '<a href="' . esc_url( admin_url( 'edit.php?post_type=shop_order&_mvr_vendor=' . $this->object->get_id() ) ) . '">' . esc_attr( $this->order_objs->total_orders ) . '</a></strong><br/>' );
		}

		/**
		 * Render column: created_date.
		 *
		 * @since 1.0.0
		 */
		public function render_created_date_column() {
			echo esc_html( $this->object->get_date_created()->date_i18n( wc_date_format() . ' ' . wc_time_format() ) );
		}

		/**
		 * Change the label when searching vendors.
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
				$report_action = 'vendor_activated';

				foreach ( $ids as $id ) {
					$vendor = mvr_get_vendor( $id );

					if ( $vendor ) {
						$vendor->update_status( 'active' );
						$changed++;
					}
				}
			} elseif ( 'deactivate' === $action ) {
				$report_action = 'vendor_deactivated';

				foreach ( $ids as $id ) {
					$vendor = mvr_get_vendor( $id );

					if ( $vendor ) {
						$vendor->update_status( 'inactive' );
						$changed++;
					}
				}
			} elseif ( '_delete' === $action ) {
				$report_action = 'vendor_deleted';

				foreach ( $ids as $id ) {
					$vendor = mvr_get_vendor( $id );

					if ( $vendor ) {
						$vendor->delete( true );
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

			if ( 'vendor_deleted' === wc_clean( wp_unslash( $request['bulk_action'] ) ) ) {
				/* translators: %d: plans count */
				$message = sprintf( _n( '%d vendor deleted.', '%d vendors permanently deleted.', $number, 'multi-vendor-marketplace' ), number_format_i18n( $number ) );
			} else {
				/* translators: %d: plans count */
				$message = sprintf( _n( '%d vendor status changed.', '%d vendor statuses changed.', $number, 'multi-vendor-marketplace' ), number_format_i18n( $number ) );
			}

			echo '<div class="updated"><p>' . esc_html( $message ) . '</p></div>';
		}
	}
}
