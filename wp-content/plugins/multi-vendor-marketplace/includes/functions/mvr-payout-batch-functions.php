<?php
/**
 * Payout Batch Functions.
 *
 * @package Multi-Vendor Functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'mvr_is_payout_batch' ) ) {
	/**
	 * Check whether the given the value is payout batch.
	 *
	 * @since 1.0.0
	 * @param  Mixed $payout_batch Post object or post ID of the Payout Batch.
	 * @return Boolean True on success.
	 */
	function mvr_is_payout_batch( $payout_batch ) {
		return $payout_batch && is_a( $payout_batch, 'MVR_Payout_Batch' );
	}
}

if ( ! function_exists( 'mvr_get_payout_batch_statuses' ) ) {
	/**
	 * Get payout batch statuses.
	 *
	 * @since 1.0.0
	 * @return Array
	 */
	function mvr_get_payout_batch_statuses() {
		return array(
			'mvr-pending' => __( 'Pending', 'multi-vendor-marketplace' ),
			'mvr-paid'    => __( 'Paid', 'multi-vendor-marketplace' ),
			'mvr-failed'  => __( 'Failed', 'multi-vendor-marketplace' ),
		);
	}
}

if ( ! function_exists( 'mvr_get_payout_batch_status_name' ) ) {
	/**
	 * Get the payout batch status name.
	 *
	 * @since 1.0.0
	 * @param String $status Status name.
	 * @return String
	 */
	function mvr_get_payout_batch_status_name( $status ) {
		$statuses = mvr_get_payout_batch_statuses();
		$status   = mvr_trim_post_status( $status );

		return isset( $statuses[ "mvr-{$status}" ] ) ? $statuses[ "mvr-{$status}" ] : $status;
	}
}

if ( ! function_exists( 'mvr_is_payout_batch_status' ) ) {
	/**
	 * See if a string is an payout status.
	 *
	 * @since 1.0.0
	 * @param  String $maybe_status Status, including any mvr- prefix.
	 * @return Boolean
	 */
	function mvr_is_payout_batch_status( $maybe_status ) {
		$statuses = mvr_get_payout_batch_statuses();

		return isset( $statuses[ $maybe_status ] );
	}
}

if ( ! function_exists( 'mvr_get_payout_batch' ) ) {
	/**
	 * Get Payout Batch.
	 *
	 * @since 1.0.0
	 * @param MVR_Payout $payout_batch Payout Batch.
	 * @param Boolean    $wp_error WordPress error.
	 * @return bool|MVR_Payout
	 */
	function mvr_get_payout_batch( $payout_batch, $wp_error = false ) {
		if ( ! $payout_batch ) {
			return false;
		}

		try {
			$payout_batch = new MVR_Payout_Batch( $payout_batch );
		} catch ( Exception $e ) {
			return $wp_error ? new WP_Error( 'error', $e->getMessage() ) : false;
		}

		return $payout_batch;
	}
}

if ( ! function_exists( 'mvr_get_payout_batches' ) ) {
	/**
	 * Return the array of payout batches based upon the args requested.
	 *
	 * @since 1.0.0
	 * @param Array $args Arguments.
	 * @return Object
	 */
	function mvr_get_payout_batches( $args = array() ) {
		global $wpdb;

		$wpdb_ref = &$wpdb;
		$args     = wp_parse_args(
			$args,
			array(
				'status'      => array_keys( mvr_get_payout_batch_statuses() ),
				'include_ids' => array(),
				'exclude_ids' => array(),
				'batch_id'    => '',
				's'           => '',
				'page'        => 1,
				'limit'       => -1,
				'fields'      => 'objects',
				'orderby'     => 'ID',
				'order'       => 'DESC',
			)
		);

		// Add the 'mvr-' prefix to status if needed.
		if ( ! empty( $args['status'] ) ) {
			if ( is_array( $args['status'] ) ) {
				foreach ( $args['status'] as &$status ) {
					if ( mvr_is_payout_batch_status( 'mvr-' . $status ) ) {
						$status = 'mvr-' . $status;
					}
				}
			} elseif ( mvr_is_payout_batch_status( 'mvr-' . $args['status'] ) ) {
					$args['status'] = 'mvr-' . $args['status'];
			}
		}

		// Statuses.
		if ( is_array( $args['status'] ) ) {
			$allowed_statuses = " AND post_status IN ('" . implode( "','", $args['status'] ) . "') ";
		} else {
			$allowed_statuses = " AND post_status = '" . esc_sql( $args['status'] ) . "' ";
		}

		// Search term.
		if ( ! empty( $args['s'] ) ) {
			$term          = str_replace( '#', '', wc_clean( wp_unslash( $args['s'] ) ) );
			$search_fields = array();
			$search_where  = " AND ( 
                (ID LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%') OR
				(post_status LIKE '%%" . $wpdb_ref->esc_like( $term ) . "%%')
                ) ";
		} else {
			$search_where = '';
		}

		// Includes.
		if ( ! empty( $args['include_ids'] ) ) {
			$include_ids = " AND ID IN ('" . implode( "','", $args['include_ids'] ) . "') ";
		} else {
			$include_ids = '';
		}

		// Excludes.
		if ( ! empty( $args['exclude_ids'] ) ) {
			$exclude_ids = " AND ID NOT IN ('" . implode( "','", $args['exclude_ids'] ) . "') ";
		} else {
			$exclude_ids = '';
		}

		// Batch ID.
		if ( ! empty( $args['batch_id'] ) ) {
			$inner_join1    = " INNER JOIN {$wpdb_ref->postmeta} AS pm1 ON (p.ID = pm1.post_id) ";
			$allow_batch_id = " AND pm1.meta_key='_batch_id' AND pm1.meta_value >= '{$args['batch_id']}'";
		} else {
			$inner_join1    = '';
			$allow_batch_id = '';
		}

		// Order by.
		switch ( ! empty( $args['orderby'] ) ? $args['orderby'] : 'menu_order' ) {
			case 'ID':
				$orderby = ' ORDER BY ' . esc_sql( $args['orderby'] ) . ' ';
				break;
			default:
				$orderby = ' ORDER BY ID ';
				break;
		}

		// Order.
		if ( ! empty( $args['order'] ) && 'desc' === strtolower( $args['order'] ) ) {
			$order = ' DESC ';
		} else {
			$order = ' ASC ';
		}

		// Paging.
		if ( $args['limit'] >= 0 ) {
			$page   = absint( $args['page'] );
			$page   = $page ? $page : 1;
			$offset = absint( ( $page - 1 ) * $args['limit'] );
			$limits = 'LIMIT ' . $offset . ', ' . $args['limit'];
		} else {
			$limits = '';
		}

		$all_payout_batch_ids = $wpdb_ref->get_var(
			$wpdb_ref->prepare(
				"
                    SELECT COUNT(DISTINCT ID) FROM {$wpdb_ref->posts} AS p
					INNER JOIN {$wpdb_ref->postmeta} AS pm ON (p.ID = pm.post_id)
					{$inner_join1}
					WHERE 1=1 AND post_type = '%s' 
					$allowed_statuses
					$allow_batch_id 
					$search_where 
					$include_ids 
					$exclude_ids 
				",
				'mvr_payout_batch'
			)
		);

		$payout_batch_ids = $wpdb_ref->get_col(
			$wpdb_ref->prepare(
				"
					SELECT DISTINCT ID FROM {$wpdb_ref->posts} AS p
					INNER JOIN {$wpdb_ref->postmeta} AS pm ON (p.ID = pm.post_id)
					{$inner_join1}
					WHERE 1=1 AND post_type = '%s' 
					$allowed_statuses 
					$allow_batch_id
					$search_where 
					$include_ids 
					$exclude_ids 
					$orderby 
					$order 
					$limits 
				",
				'mvr_payout_batch'
			)
		);

		if ( 'objects' === $args['fields'] ) {
			$payout_batches = array_filter( array_combine( $payout_batch_ids, array_map( 'mvr_get_payout_batch', $payout_batch_ids ) ) );
		} else {
			$payout_batches = $payout_batch_ids;
		}

		$payout_batches_count = count( $payout_batches );
		$query_object         = (object) array(
			'payout_batches'       => $payout_batches,
			'total_payout_batches' => $all_payout_batch_ids,
			'has_payout_batch'     => $payout_batches_count > 0,
			'max_num_pages'        => $args['limit'] > 0 ? ceil( $all_payout_batch_ids / $args['limit'] ) : 1,
		);

		return $query_object;
	}
}

if ( ! function_exists( 'mvr_get_payout_batch_notes' ) ) {
	/**
	 * Get payout batch notes.
	 *
	 * @since 1.0.0
	 * @param Array $args Arguments.
	 * @return Array
	 */
	function mvr_get_payout_batch_notes( $args ) {
		$key_mapping = array(
			'limit'                => 'number',
			'payout_batch_id'      => 'post_id',
			'payout_batch__in'     => 'post__in',
			'payout_batch__not_in' => 'post__not_in',
		);

		foreach ( $key_mapping as $query_key => $db_key ) {
			if ( isset( $args[ $query_key ] ) ) {
				$args[ $db_key ] = $args[ $query_key ];
				unset( $args[ $query_key ] );
			}
		}

		// Define orderby.
		$orderby_mapping = array(
			'date_created'     => 'comment_date',
			'date_created_gmt' => 'comment_date_gmt',
			'id'               => 'comment_ID',
		);

		$args['orderby'] = ! empty( $args['orderby'] ) && in_array( $args['orderby'], array( 'date_created', 'date_created_gmt', 'id' ), true ) ? $orderby_mapping[ $args['orderby'] ] : 'comment_ID';

		if ( isset( $args['type'] ) ) {
			// Set vendor type.
			if ( 'customer' === $args['type'] ) {
				$args['meta_query'] = array(
					array(
						'key'     => 'is_customer_note',
						'value'   => 1,
						'compare' => '=',
					),
				);
			} elseif ( 'internal' === $args['type'] ) {
				$args['meta_query'] = array(
					array(
						'key'     => 'is_customer_note',
						'compare' => 'NOT EXISTS',
					),
				);
			}
		}

		$args['type']   = 'mvr_pay_batch_note'; // Set correct comment type.
		$args['status'] = 'approve'; // Always approved.

		// Does not support 'count' or 'fields'.
		unset( $args['count'], $args['fields'] );

		remove_filter( 'comments_clauses', array( 'MVR_Comments', 'exclude_payout_batch_comments' ), 10, 1 );

		$notes = get_comments( $args );

		add_filter( 'comments_clauses', array( 'MVR_Comments', 'exclude_payout_batch_comments' ), 10, 1 );

		return array_filter( array_map( 'mvr_get_payout_batch_note', $notes ) );
	}
}

if ( ! function_exists( 'mvr_get_payout_batch_note' ) ) {
	/**
	 * Get Payout Batch note.
	 *
	 * @since 1.0.0
	 * @param  Integer|WP_Comment $data Note ID.
	 * @return stdClass|null  Object with payout batch note details or null when does not exists.
	 */
	function mvr_get_payout_batch_note( $data ) {
		if ( is_numeric( $data ) ) {
			$data = get_comment( $data );
		}

		if ( ! is_a( $data, 'WP_Comment' ) ) {
			return null;
		}

		/**
		 * Get object.
		 *
		 * @since 1.0.0
		 */
		return (object) apply_filters(
			'mvr_get_payout_batch_note',
			array(
				'id'            => (int) $data->comment_ID,
				'date_created'  => wc_string_to_datetime( $data->comment_date ),
				'content'       => $data->comment_content,
				'customer_note' => (bool) get_comment_meta( $data->comment_ID, 'is_customer_note', true ),
				'added_by'      => __( 'Multi Vendor Marketplace', 'multi-vendor-marketplace' ) === $data->comment_author ? 'system' : $data->comment_author,
			),
			$data
		);
	}
}

if ( ! function_exists( 'mvr_delete_payout_batch_note' ) ) {
	/**
	 * Delete the payout batch note.
	 *
	 * @since 1.0.0
	 * @param Integer $note_id payout batch note.
	 * @return Boolean True on success, false on failure.
	 */
	function mvr_delete_payout_batch_note( $note_id ) {
		return wp_delete_comment( $note_id, true );
	}
}
