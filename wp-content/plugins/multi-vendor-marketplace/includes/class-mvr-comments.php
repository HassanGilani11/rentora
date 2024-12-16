<?php
/**
 * Comments.
 *
 * @package Multi-Vendor/GDPR
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Comments' ) ) {
	/**
	 * Handle comments (vendor notes).
	 *
	 * @class MVR_Comments
	 * @package Class
	 */
	class MVR_Comments {

		/**
		 * Hook in methods.
		 */
		public static function init() {
			// Secure vendor notes.
			add_filter( 'comments_clauses', array( __CLASS__, 'exclude_vendor_comments' ), 10, 1 );
			add_filter( 'comments_clauses', array( __CLASS__, 'exclude_payout_batch_comments' ), 10, 1 );
			add_filter( 'comment_feed_where', array( __CLASS__, 'exclude_vendor_comments_from_feed_where' ) );
			add_filter( 'comment_feed_where', array( __CLASS__, 'exclude_payout_batch_comments_from_feed_where' ) );

			// Count comments.
			add_filter( 'wp_count_comments', array( __CLASS__, 'wp_count_comments' ), 10, 2 );

			// Delete comments count cache whenever there is a new comment or a comment status changes.
			add_action( 'wp_insert_comment', array( __CLASS__, 'delete_comments_count_cache' ) );
			add_action( 'wp_set_comment_status', array( __CLASS__, 'delete_comments_count_cache' ) );
		}

		/**
		 * Exclude vendor comments from queries and RSS.
		 *
		 * This code should exclude mvr_vendor comments from queries. Some queries (like the recent comments widget on the dashboard) are hardcoded.
		 * and are not filtered, however, the code current_user_can( 'read_post', $comment->comment_post_ID ) should keep them safe since only admin and.
		 * shop managers can view vendor anyway.
		 *
		 * The frontend view vendor pages get around this filter by using remove_filter('comments_clauses', array( 'MVR_Comments' ,'exclude_vendor_comments'), 10, 1 );
		 *
		 * @since 1.0.0
		 * @param  Array $clauses A compacted array of comment query clauses.
		 * @return Array
		 */
		public static function exclude_vendor_comments( $clauses ) {
			$clauses['where'] .= ( $clauses['where'] ? ' AND ' : '' ) . " comment_type != 'mvr_vendor_note' ";

			return $clauses;
		}

		/**
		 * Exclude payout batch comments from queries and RSS.
		 *
		 * This code should exclude mvr_payout_batch comments from queries. Some queries (like the recent comments widget on the dashboard) are hardcoded.
		 * and are not filtered, however, the code current_user_can( 'read_post', $comment->comment_post_ID ) should keep them safe since only admin and.
		 * shop managers can view vendor anyway.
		 *
		 * The frontend view vendor pages get around this filter by using remove_filter('comments_clauses', array( 'MVR_Comments' ,'exclude_payout_batch_comments'), 10, 1 );
		 *
		 * @since 1.0.0
		 * @param  Array $clauses A compacted array of comment query clauses.
		 * @return Array
		 */
		public static function exclude_payout_batch_comments( $clauses ) {
			$clauses['where'] .= ( $clauses['where'] ? ' AND ' : '' ) . " comment_type != 'mvr_pay_batch_note' ";

			return $clauses;
		}

		/**
		 * Exclude vendor comments from queries and RSS.
		 *
		 * @since 1.0.0
		 * @param String $where The WHERE clause of the query.
		 * @return String
		 */
		public static function exclude_vendor_comments_from_feed_where( $where ) {
			$where .= ( $where ? ' AND ' : '' ) . " comment_type != 'mvr_vendor_note' ";

			return $where;
		}

		/**
		 * Exclude payout batch comments from queries and RSS.
		 *
		 * @since 1.0.0
		 * @param String $where The WHERE clause of the query.
		 * @return String
		 */
		public static function exclude_payout_batch_comments_from_feed_where( $where ) {
			$where .= ( $where ? ' AND ' : '' ) . " comment_type != 'mvr_pay_batch_note' ";

			return $where;
		}

		/**
		 * Delete comments count cache whenever there is
		 * new comment or the status of a comment changes. Cache
		 * will be regenerated next time MVR_Comments::wp_count_comments()
		 * is called.
		 *
		 * @since 1.0.0
		 */
		public static function delete_comments_count_cache() {
			delete_transient( 'mvr_count_comments' );
		}

		/**
		 * Remove vendor notes from wp_count_comments().
		 *
		 * @since 1.0.0
		 * @param Object  $stats Comment stats.
		 * @param Integer $post_id Post ID.
		 * @return Object
		 */
		public static function wp_count_comments( $stats, $post_id ) {
			global $wpdb;

			if ( 0 === $post_id ) {
				$stats = get_transient( 'mvr_count_comments' );

				if ( ! $stats ) {
					$stats = array(
						'total_comments' => 0,
						'all'            => 0,
					);

					$count = $wpdb->get_results(
						"
					SELECT comment_approved, COUNT(*) AS num_comments
					FROM {$wpdb->comments}
					LEFT JOIN {$wpdb->posts} ON comment_post_ID = {$wpdb->posts}.ID
					WHERE comment_type NOT IN ( 'mvr_vendor_note', 'mvr_pay_batch_note' )
					GROUP BY comment_approved
					",
						ARRAY_A
					);

					$approved = array(
						'0'            => 'moderated',
						'1'            => 'approved',
						'spam'         => 'spam',
						'trash'        => 'trash',
						'post-trashed' => 'post-trashed',
					);

					foreach ( (array) $count as $row ) {
						// Don't count post-trashed toward totals.
						if ( ! in_array( $row['comment_approved'], array( 'post-trashed', 'trash', 'spam' ), true ) ) {
							$stats['all']            += $row['num_comments'];
							$stats['total_comments'] += $row['num_comments'];
						} elseif ( ! in_array( $row['comment_approved'], array( 'post-trashed', 'trash' ), true ) ) {
							$stats['total_comments'] += $row['num_comments'];
						}
						if ( isset( $approved[ $row['comment_approved'] ] ) ) {
							$stats[ $approved[ $row['comment_approved'] ] ] = $row['num_comments'];
						}
					}

					foreach ( $approved as $key ) {
						if ( empty( $stats[ $key ] ) ) {
							$stats[ $key ] = 0;
						}
					}

					$stats = (object) $stats;

					set_transient( 'mvr_count_comments', $stats );
				}
			}

			return $stats;
		}
	}

	MVR_Comments::init();
}
