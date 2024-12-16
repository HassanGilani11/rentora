<?php
/**
 * Review List Table.
 *
 * @package Multi-Vendor/List Table
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'MVR_Admin_List_Table_Review' ) ) {

	/**
	 * MVR_Admin_List_Table_Review Class.
	 * */
	class MVR_Admin_List_Table_Review extends WP_List_Table {

		/**
		 * Memoization flag to determine if the current user can edit the current review.
		 *
		 * @var bool
		 */
		private $current_user_can_edit_review = false;

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
		 * Commission IDs.
		 *
		 * @var array
		 * */
		private $review_ids;

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

			$this->set_review_status();

			$this->database = $wpdb;
			$this->base_url = mvr_get_review_page_url();

			$this->prepare_review_ids();
			$this->prepare_current_url();
			$this->process_bulk_action();
			$this->get_current_pagenum();
			$this->get_current_reviews();
			$this->prepare_pagination_args();
			$this->prepare_column_headers();
		}

		/**
		 * Sets the `$comment_status` global based on the current request.
		 *
		 * @since 1.0.0
		 * @global String $_comment_status Comment status.
		 */
		protected function set_review_status() {
			global $_comment_status;

			$_comment_status = isset( $_REQUEST['status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['status'] ) ) : 'all';

			if ( ! in_array( $_comment_status, array( 'all', 'moderated', 'approved', 'spam', 'trash' ), true ) ) {
				$_comment_status = 'all';
			}
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
		 * Generate and display row actions links.
		 *
		 * @since 1.0.0
		 * @param WP_Comment|Mixed $review_obj The Store review or reply in context.
		 * @param String|Mixed     $column_name Current column name.
		 * @param String|Mixed     $primary     Primary column name.
		 * @return String
		 */
		protected function handle_row_actions( $review_obj, $column_name, $primary ) {
			global $_comment_status;

			if ( $primary !== $column_name || ! current_user_can( 'edit_comment', $review_obj->comment_ID ) ) {
				return '';
			}

			$review_status = wp_get_comment_status( $review_obj );

			$url = add_query_arg(
				array(
					'c' => rawurlencode( $review_obj->comment_ID ),
				),
				admin_url( 'comment.php' )
			);

			$approve_url   = wp_nonce_url( add_query_arg( 'action', 'approvecomment', $url ), "approve-comment_$review_obj->comment_ID" );
			$unapprove_url = wp_nonce_url( add_query_arg( 'action', 'unapprovecomment', $url ), "approve-comment_$review_obj->comment_ID" );
			$spam_url      = wp_nonce_url( add_query_arg( 'action', 'spamcomment', $url ), "delete-comment_$review_obj->comment_ID" );
			$unspam_url    = wp_nonce_url( add_query_arg( 'action', 'unspamcomment', $url ), "delete-comment_$review_obj->comment_ID" );
			$trash_url     = wp_nonce_url( add_query_arg( 'action', 'trashcomment', $url ), "delete-comment_$review_obj->comment_ID" );
			$untrash_url   = wp_nonce_url( add_query_arg( 'action', 'untrashcomment', $url ), "delete-comment_$review_obj->comment_ID" );
			$delete_url    = wp_nonce_url( add_query_arg( 'action', 'deletecomment', $url ), "delete-comment_$review_obj->comment_ID" );

			$actions = array(
				'approve'   => '',
				'unapprove' => '',
				'reply'     => '',
				'quickedit' => '',
				'edit'      => '',
				'spam'      => '',
				'unspam'    => '',
				'trash'     => '',
				'untrash'   => '',
				'delete'    => '',
			);

			if ( 'approved' === $review_status ) {
				$actions['unapprove'] = sprintf(
					'<a href="%s" data-wp-lists="%s" class="vim-u vim-destructive aria-button-if-js" aria-label="%s">%s</a>',
					esc_url( $unapprove_url ),
					esc_attr( "delete:the-comment-list:comment-{$review_obj->comment_ID}:e7e7d3:action=dim-comment&amp;new=unapproved" ),
					esc_attr__( 'Unapprove this review', 'multi-vendor-marketplace' ),
					esc_html__( 'Unapprove', 'multi-vendor-marketplace' )
				);
			} elseif ( 'unapproved' === $review_status ) {
				$actions['approve'] = sprintf(
					'<a href="%s" data-wp-lists="%s" class="vim-a vim-destructive aria-button-if-js" aria-label="%s">%s</a>',
					esc_url( $approve_url ),
					esc_attr( "delete:the-comment-list:comment-{$review_obj->comment_ID}:e7e7d3:action=dim-comment&amp;new=approved" ),
					esc_attr__( 'Approve this review', 'multi-vendor-marketplace' ),
					esc_html__( 'Approve', 'multi-vendor-marketplace' )
				);
			}

			if ( 'spam' !== $review_status ) {
				$actions['spam'] = sprintf(
					'<a href="%s" data-wp-lists="%s" class="vim-s vim-destructive aria-button-if-js" aria-label="%s">%s</a>',
					esc_url( $spam_url ),
					esc_attr( "delete:the-comment-list:comment-{$review_obj->comment_ID}::spam=1" ),
					esc_attr__( 'Mark this review as spam', 'multi-vendor-marketplace' ),
					/* translators: "Mark as spam" link. */
					esc_html_x( 'Spam', 'verb', 'multi-vendor-marketplace' )
				);
			} else {
				$actions['unspam'] = sprintf(
					'<a href="%s" data-wp-lists="%s" class="vim-z vim-destructive aria-button-if-js" aria-label="%s">%s</a>',
					esc_url( $unspam_url ),
					esc_attr( "delete:the-comment-list:comment-{$review_obj->comment_ID}:66cc66:unspam=1" ),
					esc_attr__( 'Restore this review from the spam', 'multi-vendor-marketplace' ),
					esc_html_x( 'Not Spam', 'review', 'multi-vendor-marketplace' )
				);
			}

			if ( 'trash' === $review_status ) {
				$actions['untrash'] = sprintf(
					'<a href="%s" data-wp-lists="%s" class="vim-z vim-destructive aria-button-if-js" aria-label="%s">%s</a>',
					esc_url( $untrash_url ),
					esc_attr( "delete:the-comment-list:comment-{$review_obj->comment_ID}:66cc66:untrash=1" ),
					esc_attr__( 'Restore this review from the Trash', 'multi-vendor-marketplace' ),
					esc_html__( 'Restore', 'multi-vendor-marketplace' )
				);
			}

			if ( 'spam' === $review_status || 'trash' === $review_status || ! EMPTY_TRASH_DAYS ) {
				$actions['delete'] = sprintf(
					'<a href="%s" data-wp-lists="%s" class="delete vim-d vim-destructive aria-button-if-js" aria-label="%s">%s</a>',
					esc_url( $delete_url ),
					esc_attr( "delete:the-comment-list:comment-{$review_obj->comment_ID}::delete=1" ),
					esc_attr__( 'Delete this review permanently', 'multi-vendor-marketplace' ),
					esc_html__( 'Delete Permanently', 'multi-vendor-marketplace' )
				);
			} else {
				$actions['trash'] = sprintf(
					'<a href="%s" data-wp-lists="%s" class="delete vim-d vim-destructive aria-button-if-js" aria-label="%s">%s</a>',
					esc_url( $trash_url ),
					esc_attr( "delete:the-comment-list:comment-{$review_obj->comment_ID}::trash=1" ),
					esc_attr__( 'Move this review to the Trash', 'multi-vendor-marketplace' ),
					esc_html_x( 'Trash', 'verb', 'multi-vendor-marketplace' )
				);
			}

			if ( 'spam' !== $review_status && 'trash' !== $review_status ) {
				$actions['edit'] = sprintf(
					'<a href="%s" aria-label="%s">%s</a>',
					esc_url(
						add_query_arg(
							array(
								'action' => 'editcomment',
								'c'      => rawurlencode( $review_obj->comment_ID ),
							),
							admin_url( 'comment.php' )
						)
					),
					esc_attr__( 'Edit this review', 'multi-vendor-marketplace' ),
					esc_html__( 'Edit', 'multi-vendor-marketplace' )
				);

				$format = '<button type="button" data-comment-id="%d" data-post-id="%d" data-action="%s" class="%s button-link" aria-expanded="false" aria-label="%s">%s</button>';
			}

			$always_visible = 'excerpt' === get_user_setting( 'posts_list_mode', 'list' );
			$output         = '<div class="' . ( $always_visible ? 'row-actions visible' : 'row-actions' ) . '">';
			$i              = 0;

			foreach ( array_filter( $actions ) as $action => $link ) {
				++$i;

				if ( ( ( 'approve' === $action || 'unapprove' === $action ) && 2 === $i ) || 1 === $i ) {
					$sep = '';
				} else {
					$sep = ' | ';
				}

				if ( ( 'reply' === $action || 'quickedit' === $action ) && ! wp_doing_ajax() ) {
					$action .= ' hide-if-no-js';
				} elseif ( ( 'untrash' === $action && 'trash' === $review_status ) || ( 'unspam' === $action && 'spam' === $review_status ) ) {
					if ( '1' === get_comment_meta( $review_obj->comment_ID, '_wp_trash_meta_status', true ) ) {
						$action .= ' approve';
					} else {
						$action .= ' unapprove';
					}
				}

				$output .= "<span class='$action'>$sep$link</span>";
			}

			$output .= '</div>';
			$output .= '<button type="button" class="toggle-row"><span class="screen-reader-text">' . esc_html__( 'Show more details', 'multi-vendor-marketplace' ) . '</span></button>';

			return $output;
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
			$keys         = array( 'type', 'author', 'rating', 'comment', 'response', 'date' );
			$labels       = mvr_get_review_table_labels();
			$show_columns = array(
				'cb' => $columns['cb'],
			);

			foreach ( $keys as $key ) {
				$show_columns[ $key ] = ( isset( $labels[ $key ] ) ) ? $labels[ $key ] : '';
			}

			return $show_columns;
		}


		/**
		 * Gets the name of the default primary column.
		 *
		 * @since 1.0.0
		 * @return String Name of the primary colum.
		 */
		protected function get_primary_column_name() {
			return 'comment';
		}

		/**
		 * Get a list of hidden columns.
		 *
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
			return array(
				'author'   => 'comment_author',
				'response' => 'comment_post_ID',
				'date'     => 'comment_date_gmt',
				'type'     => 'comment_type',
				'rating'   => 'rating',
			);
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
		 * Get a list of bulk actions.
		 *
		 * @since 1.0.0
		 * @return Array
		 * */
		protected function get_bulk_actions() {
			global $_comment_status;

			$actions = array();

			if ( in_array( $_comment_status, array( 'all', 'approved' ), true ) ) {
				$actions['unapprove'] = __( 'Unapprove', 'woocommerce' );
			}

			if ( in_array( $_comment_status, array( 'all', 'moderated' ), true ) ) {
				$actions['approve'] = __( 'Approve', 'woocommerce' );
			}

			if ( in_array( $_comment_status, array( 'all', 'moderated', 'approved', 'trash' ), true ) ) {
				$actions['spam'] = _x( 'Mark as spam', 'review', 'woocommerce' );
			}

			if ( 'trash' === $_comment_status ) {
				$actions['untrash'] = __( 'Restore', 'woocommerce' );
			} elseif ( 'spam' === $_comment_status ) {
				$actions['unspam'] = _x( 'Not spam', 'review', 'woocommerce' );
			}

			if ( in_array( $_comment_status, array( 'trash', 'spam' ), true ) || ! EMPTY_TRASH_DAYS ) {
				$actions['delete'] = __( 'Delete permanently', 'woocommerce' );
			} else {
				$actions['trash'] = __( 'Move to Trash', 'woocommerce' );
			}

			/**
			 * Store Reviews Bulk Action.
			 *
			 * @since 1.0.0
			 */
			$actions = apply_filters( 'mvr_store_reviews_bulk_actions', $actions );

			return $actions;
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

			if ( ! wp_verify_nonce( $nonce, 'mvr-search_review' ) ) {
				return;
			}

			$action = $this->current_action();
			$ids    = isset( $_REQUEST['id'] ) ? wc_clean( wp_unslash( $_REQUEST['id'] ) ) : array();
			$ids    = ! is_array( $ids ) ? explode( ',', $ids ) : $ids;

			if ( ! mvr_check_is_array( $ids ) ) {
				return;
			}

			foreach ( $ids as $id ) {
				$old_status = wp_get_comment_status( $id );

				if ( $action === $old_status ) {
					return false;
				}

				switch ( $action ) {
					case 'unapprove':
						wp_set_comment_status( $id, 'hold' );
						break;
					case 'approve':
						wp_set_comment_status( $id, 'approve' );
						break;
					case 'span':
						wp_spam_comment( $id );
						break;
					case 'trash':
						wp_trash_comment( $id );
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
			$request      = $_REQUEST;
			$args         = array();
			$views        = array();
			$status       = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
			$status_array = mvr_get_review_statuses();
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

				$search_term = isset( $request['s'] ) ? sanitize_text_field( wp_unslash( $request['s'] ) ) : '';

				if ( $search_term ) {
					$args['search'] = $search_term;
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
		 * Gets the in-reply-to-review text.
		 *
		 * @since 1.0.0
		 * @param WP_Comment|Mixed $reply Reply to review.
		 * @return String
		 */
		private function get_in_reply_to_review_text( $reply ) {
			$review = $reply->comment_parent ? get_comment( $reply->comment_parent ) : null;

			if ( ! $review ) {
				return '';
			}

			$parent_review_link = get_comment_link( $review );
			$review_author_name = get_comment_author( $review );

			/* translators: %s: Parent review link with review author name. */
			return sprintf( ent2ncr( __( 'In reply to %s.', 'multi-vendor-marketplace' ) ), '<a href="' . esc_url( $parent_review_link ) . '">' . esc_html( $review_author_name ) . '</a>' );
		}

		/**
		 * Displays a review count bubble.
		 *
		 * Based on {@see WP_List_Table::comments_bubble()}, but overridden, so we can customize the URL and text output.
		 *
		 * @since 1.0.0
		 * @param Integer $post_id The product ID.
		 * @param Integer $pending_comments Number of pending reviews.
		 */
		protected function comments_bubble( $post_id, $pending_comments ) {
			$approved_count          = get_comments_number();
			$approved_reviews_number = number_format_i18n( $approved_count );
			$pending_reviews_number  = number_format_i18n( $pending_comments );

			/* translators: %s: Number of reviews. */
			$approved_only_phrase = sprintf( _n( '%s review', '%s reviews', esc_attr( $approved_count ), 'multi-vendor-marketplace' ), $approved_reviews_number );

			/* translators: %s: Number of reviews. */
			$approved_phrase = sprintf( _n( '%s approved review', '%s approved reviews', esc_attr( $approved_count ), 'multi-vendor-marketplace' ), $approved_reviews_number );

			/* translators: %s: Number of reviews. */
			$pending_phrase = sprintf( _n( '%s pending review', '%s pending reviews', esc_attr( $pending_comments ), 'multi-vendor-marketplace' ), $pending_reviews_number );

			if ( ! $approved_count && ! $pending_comments ) {
				// No reviews at all.
				printf( '<span aria-hidden="true">&#8212;</span><span class="screen-reader-text">%s</span>', esc_html__( 'No reviews', 'multi-vendor-marketplace' ) );
			} elseif ( $approved_count && 'trash' === get_post_status( $post_id ) ) {
				// Don't link the comment bubble for a trashed product.
				printf(
					'<span class="post-com-count post-com-count-approved"><span class="comment-count-approved" aria-hidden="true">%s</span><span class="screen-reader-text">%s</span></span>',
					esc_html( $approved_reviews_number ),
					$pending_comments ? esc_html( $approved_phrase ) : esc_html( $approved_only_phrase )
				);
			} elseif ( $approved_count ) {
				// Link the comment bubble to approved reviews.
				printf(
					'<a href="%s" class="post-com-count post-com-count-approved"><span class="comment-count-approved" aria-hidden="true">%s</span><span class="screen-reader-text">%s</span></a>',
					esc_url(
						mvr_get_review_page_url(
							array(
								'vendor_id'      => rawurlencode( $post_id ),
								'comment_status' => 'approved',
							)
						)
					),
					esc_html( $approved_reviews_number ),
					$pending_comments ? esc_html( $approved_phrase ) : esc_html( $approved_only_phrase )
				);
			} else {
				// Don't link the comment bubble when there are no approved reviews.
				printf(
					'<span class="post-com-count post-com-count-no-comments"><span class="comment-count comment-count-no-comments" aria-hidden="true">%s</span><span class="screen-reader-text">%s</span></span>',
					esc_html( $approved_reviews_number ),
					$pending_comments ? esc_html__( 'No approved reviews', 'multi-vendor-marketplace' ) : esc_html__( 'No reviews', 'multi-vendor-marketplace' )
				);
			}

			if ( $pending_comments ) {
				printf(
					'<a href="%s" class="post-com-count post-com-count-pending"><span class="comment-count-pending" aria-hidden="true">%s</span><span class="screen-reader-text">%s</span></a>',
					esc_url(
						mvr_get_review_page_url(
							array(
								'vendor_id'      => rawurlencode( $post_id ),
								'comment_status' => 'moderated',
							)
						)
					),
					esc_html( $pending_reviews_number ),
					esc_html( $pending_phrase )
				);
			} else {
				printf(
					'<span class="post-com-count post-com-count-pending post-com-count-no-pending"><span class="comment-count comment-count-no-pending" aria-hidden="true">%s</span><span class="screen-reader-text">%s</span></span>',
					esc_html( $pending_reviews_number ),
					$approved_count ? esc_html__( 'No pending reviews', 'multi-vendor-marketplace' ) : esc_html__( 'No reviews', 'multi-vendor-marketplace' )
				);
			}
		}

		/**
		 * Prepare the CB column data.
		 *
		 * @since 1.0.0
		 * @param Object $review_obj Commission object.
		 * @return HTML
		 * */
		protected function column_cb( $review_obj ) {
			return sprintf( '<input class="mvr-review-cb" type="checkbox" name="id[]" value="%s" />', $review_obj->comment_ID );
		}

		/**
		 * Prepare the each column data.
		 *
		 * @since 1.0.0
		 * @param Object $review_obj Review object.
		 * @param String $column_name Name of the column.
		 * @return mixed
		 * */
		protected function column_default( $review_obj, $column_name ) {
			switch ( $column_name ) {
				case 'type':
					$type = ( 'mvr_store_review' === $review_obj->comment_type ) ? '&#9734;&nbsp;' . __( 'Review', 'multi-vendor-marketplace' ) : __( 'Reply', 'multi-vendor-marketplace' );
					echo wp_kses_post( $type );
					break;
				case 'author':
					if ( get_option( 'show_avatars' ) ) {
						echo wp_kses_post( get_avatar( $review_obj, 32, 'mystery' ) );
					}

					echo '<strong>' . esc_attr( $review_obj->comment_author ) . '</strong>';

					if ( $review_obj->author_url ) {
						?>
						<a title="<?php echo esc_attr( $review_obj->author_url ); ?>" href="<?php echo esc_url( $review_obj->author_url ); ?>" rel="noopener noreferrer"><?php echo esc_html( $review_obj->author_url ); ?></a>
						<br>
						<?php
					}
					break;
				case 'rating':
					$rating = get_comment_meta( $review_obj->comment_ID, 'rating', true );

					if ( ! empty( $rating ) && is_numeric( $rating ) ) {
						$rating = (int) $rating;

						/* translators: 1: number representing a rating */
						$accessibility_label = sprintf( esc_html__( '%1$d out of 5', 'multi-vendor-marketplace' ), $rating );

						$stars  = str_repeat( '&#9733;', $rating );
						$stars .= str_repeat( '&#9734;', 5 - $rating );

						?>
						<span aria-label="<?php echo esc_attr( $accessibility_label ); ?>"><?php echo esc_html( $stars ); ?></span>
						<?php
					}
					break;
				case 'comment':
					$in_reply_to = $this->get_in_reply_to_review_text( $review_obj );

					if ( $in_reply_to ) {
						echo wp_kses_post( $in_reply_to ) . '<br><br>';
					}

					printf( '%1$s%2$s%3$s', '<div class="comment-text">', wp_kses_post( get_comment_text( $review_obj->comment_ID ) ), '</div>' );
					break;
				case 'response':
					$vendor_id = $review_obj->comment_post_ID;

					if ( empty( $vendor_id ) ) {
						return;
					}

					$vendor_obj = mvr_get_vendor( $vendor_id );

					if ( ! $vendor_obj ) {
						return;
					}

					if ( $vendor_obj->get_admin_edit_url() ) {
						echo '<a href="' . esc_url( $vendor_obj->get_admin_edit_url() ) . '">' . esc_attr( $vendor_obj->get_name() ) . '</a><br/>';
					}
					?>
						<span class="post-com-count-wrapper post-com-count-<?php echo esc_attr( $vendor_obj->get_id() ); ?>">
							<?php $this->comments_bubble( $vendor_obj->get_id(), get_pending_comments_num( $vendor_obj->get_id() ) ); ?>
						</span>
					<?php
					break;
				case 'date':
					$created_timestamp = $review_obj->comment_date ? strtotime( $review_obj->comment_date ) : '';

					if ( ! $created_timestamp ) {
						echo '&ndash;';
						return;
					}

					// Check if the plan was created within the last 24 hours, and not in the future.
					if ( $created_timestamp > strtotime( '-1 day', time() ) && $created_timestamp <= time() ) {
						$show_date = sprintf(
							/* translators: %s: human-readable time difference */
							_x( '%s ago', '%s = human-readable time difference', 'multi-vendor-marketplace' ),
							human_time_diff( strtotime( $review_obj->comment_date ), time() )
						);
					} else {
						$show_date = gmdate( 'M j, Y', strtotime( $review_obj->comment_date ) );
					}

					printf(
						'<time datetime="%1$s" title="%2$s">%3$s</time>',
						esc_attr( gmdate( 'c', strtotime( $review_obj->comment_date ) ) ),
						esc_html( gmdate( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $review_obj->comment_date ) ) ),
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
		private function get_current_reviews() {
			$request     = $_REQUEST;
			$status      = isset( $request['status'] ) ? sanitize_text_field( wp_unslash( $request['status'] ) ) : '';
			$vendor_id   = isset( $request['_mvr_vendor'] ) ? absint( wp_unslash( $request['_mvr_vendor'] ) ) : '';
			$search_term = isset( $request['s'] ) ? sanitize_text_field( wp_unslash( $request['s'] ) ) : '';
			$orderby     = isset( $request['orderby'] ) ? sanitize_text_field( wp_unslash( $request['orderby'] ) ) : $this->orderby;
			$order       = isset( $request['order'] ) ? sanitize_text_field( wp_unslash( $request['order'] ) ) : $this->order;
			$args        = array(
				'status'  => mvr_convert_review_status_to_query_val( $status ),
				'search'  => $search_term,
				'limit'   => $this->limit,
				'offset'  => $this->offset,
				'orderby' => $orderby,
				'order'   => strtolower( $order ),
			);

			if ( $vendor_id ) {
				$args['vendor_id'] = $vendor_id;
			}

			$reviews_obj = mvr_get_reviews( $args );

			$this->items       = $reviews_obj->reviews;
			$this->total_items = $reviews_obj->total_review;
		}

		/**
		 * Get the commission count for the status.
		 *
		 * @since 1.0.0
		 * @param String $status Status.
		 * @return Integer
		 * */
		private function get_item_count_for_status( $status = '' ) {
			$request = $_REQUEST;

			if ( empty( $status ) ) {
				$status = isset( $request['status'] ) ? sanitize_text_field( wp_unslash( $request['status'] ) ) : '';
			}

			if ( 'all' === $status || '' === $status ) {
				$status = 'all';
			}

			$args = array(
				'status' => mvr_convert_review_status_to_query_val( $status ),
			);

			$search_term = isset( $request['s'] ) ? sanitize_text_field( wp_unslash( $request['s'] ) ) : '';

			if ( $search_term ) {
				$args['search'] = $search_term;
			}

			$reviews = mvr_get_reviews( $args );

			return (int) $reviews->total_review;
		}

		/**
		 * Prepare the Commission IDs.
		 *
		 * @since 1.0.0
		 * */
		private function prepare_review_ids() {
			$all_review_ids = get_comments(
				array(
					'type'    => 'mvr_store_review',
					'approve' => 'approve',
				)
			);

			/**
			 * Review ids in list table.
			 *
			 * @since 1.0.0
			 */
			$this->review_ids = apply_filters( 'mvr_admin_list_table_review_ids', $all_review_ids );
		}

		/**
		 * Displays the product reviews HTML table.
		 *
		 * Reimplements {@see WP_Comment_::display()} but we change the ID to match the one output by {@see WP_Comments_List_Table::display()}.
		 * This will automatically handle additional CSS for consistency with the comments page.
		 *
		 * @return void
		 */
		public function display() {
			$this->display_tablenav( 'top' );
			$this->screen->render_screen_reader_content( 'heading_list' );
			?>

			<table class="wp-list-table <?php echo esc_attr( implode( ' ', $this->get_table_classes() ) ); ?>">
				<thead>
					<tr>
						<?php $this->print_column_headers(); ?>
					</tr>
				</thead>
				<tbody id="the-comment-list" data-wp-lists="list:comment">
					<?php $this->display_rows_or_placeholder(); ?>
				</tbody>
				<tfoot>
					<tr>
						<?php $this->print_column_headers( false ); ?>
					</tr>
				</tfoot>
			</table>
			<?php

			$this->display_tablenav( 'bottom' );
		}

		/**
		 * Render a single row HTML.
		 *
		 * @param WP_Comment|mixed $item Review or reply being rendered.
		 * @return void
		 */
		public function single_row( $item ) {
			$the_comment_class = (string) wp_get_comment_status( $item->comment_ID );
			$the_comment_class = implode( ' ', get_comment_class( $the_comment_class, $item->comment_ID, $item->comment_post_ID ) );
			$post              = get_post( $item->comment_post_ID );

			$this->current_user_can_edit_review = current_user_can( 'edit_comment', $item->comment_ID );

			?>
			<tr id="comment-<?php echo esc_attr( $item->comment_ID ); ?>" class="comment <?php echo esc_attr( $the_comment_class ); ?>">
				<?php $this->single_row_columns( $item ); ?>
			</tr>
			<?php
		}
	}

}
