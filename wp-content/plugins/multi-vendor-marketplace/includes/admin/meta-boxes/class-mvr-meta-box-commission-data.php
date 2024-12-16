<?php
/**
 * Commission Data
 *
 * Displays the Commission data box, tabbed, with several panels covering commission, payment etc.
 *
 * @package  Multi-Vendor\Admin\Meta Boxes
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'MVR_Meta_Box_Commission_Data' ) ) {
	/**
	 * MVR_Meta_Box_Commission_Data Class.
	 */
	class MVR_Meta_Box_Commission_Data {

		/**
		 * Output the meta-box.
		 *
		 * @since 1.0.0
		 * @param WP_Post $post Post object.
		 */
		public static function output( $post ) {
			global $post, $commission_obj;

			if ( ! is_object( $commission_obj ) ) {
				$commission_obj = new MVR_Commission( $post->ID );
			}

			wp_nonce_field( 'mvr_save_data', 'mvr_save_meta_nonce' );

			include __DIR__ . '/views/html-commission-data-panel.php';
		}
	}
}
