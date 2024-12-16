<?php
/**
 * Admin Review.
 *
 * @package Multi Vendor/Admin
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Admin_Review' ) ) {

	/**
	 * MVR_Admin_Review Class.
	 * */
	class MVR_Admin_Review {

		/**
		 * Class initialization.
		 *
		 * @since 1.0.0
		 * */
		public static function init() {
			add_filter( 'get_comment_link', __CLASS__ . '::get_comment_link', 10, 4 );
		}

		/**
		 * Comment URL
		 *
		 * @since 1.0.0
		 * @param URL     $comment_link Comment URL.
		 * @param Object  $comment Comment object.
		 * @param Array   $args Array of arguments.
		 * @param Integer $c_page        The calculated 'c_page' value.
		 * */
		public static function get_comment_link( $comment_link, $comment, $args, $c_page ) {
			if ( ! $comment || 'mvr_vendor' !== get_post_type( $comment->comment_post_ID ) ) {
				return $comment_link;
			}

			$vendor_id = $comment->comment_post_ID;

			if ( ! $vendor_id ) {
				return $comment_link;
			}

			$vendor_obj = mvr_get_vendor( $vendor_id );

			if ( ! mvr_is_vendor( $vendor_obj ) ) {
				return $comment_link;
			}

			return $vendor_obj->get_shop_url();
		}

		/**
		 * Output Fund Page.
		 *
		 * @since 1.0.0
		 * */
		public static function output() {
			global $current_action;

			switch ( $current_action ) {
				default:
					self::render_review();
					break;
			}
		}

		/**
		 * Output the review WP List Table.
		 *
		 * @since 1.0.0
		 * */
		public static function render_review() {
			if ( ! class_exists( 'MVR_Admin_List_Table_Review' ) ) {
				require_once MVR_PLUGIN_PATH . '/includes/admin/list-tables/class-mvr-admin-list-table-review.php';
			}

			$post_table = new MVR_Admin_List_Table_Review();
			$post_table->prepare_items();

			include_once MVR_PLUGIN_PATH . '/includes/admin/views/html-review.php';
		}
	}

	MVR_Admin_Review::init();
}
