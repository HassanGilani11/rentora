<?php
/**
 * Vendor Coupon Data
 *
 * Displays the Coupon Vendor etc.
 *
 * @package  Multi-Vendor\Admin\Meta Boxes
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Meta_Box_Coupon_Data' ) ) {

	/**
	 * Coupon Vendor.
	 *
	 * @class MVR_Meta_Box_Coupon_Data
	 */
	class MVR_Meta_Box_Coupon_Data {

		/**
		 * Output the metabox.
		 *
		 * @since 1.0.0
		 * @param WP_Post $post Post object.
		 */
		public static function output( $post ) {
			global $post, $coupon_obj, $pagenow;

			if ( ! is_object( $coupon_obj ) ) {
				$coupon_obj = new WC_Coupon( $post->ID );
			}

			wp_nonce_field( 'mvr_save_data', 'mvr_save_meta_nonce' );

			include 'views/html-coupon-vendor.php';
		}

		/**
		 * Output the metabox.
		 *
		 * @since 1.0.0
		 * @param WP_Post $post Post object.
		 */
		public static function output_vendor_action( $post ) {
			global $post, $coupon_obj, $pagenow;

			if ( ! is_object( $coupon_obj ) ) {
				$coupon_obj = new WC_Coupon( $post->ID );
			}

			wp_nonce_field( 'mvr_save_data', 'mvr_save_meta_nonce' );

			include 'views/html-coupon-save.php';
		}

		/**
		 * Save meta box data.
		 *
		 * @since 1.0.0
		 * @param Integer $post_id Post ID.
		 * @param WP_Post $post Post object.
		 * @param Array   $posted Posted Data.
		 * @throws WC_Data_Exception When invalid data is returned.
		 */
		public static function save( $post_id, $post, $posted ) {
			$coupon_obj = new WC_Coupon( $post_id );
			$vendor_id  = isset( $posted['_mvr_coupon_vendor'] ) ? $posted['_mvr_coupon_vendor'] : '';

			$coupon_obj->add_meta_data( '_mvr_vendor', $vendor_id, true );
			$coupon_obj->save();

			$vendor_obj = mvr_get_vendor( $vendor_id );

			/**
			 * Vendor Coupon Save
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_admin_coupon_save', $coupon_obj, $vendor_obj );
		}

		/**
		 * Save meta box data from vendor.
		 *
		 * @since 1.0.0
		 * @param Integer $post_id Post ID.
		 * @param WP_Post $post Post object.
		 * @param Array   $posted Posted Data.
		 * @throws WC_Data_Exception When invalid data is returned.
		 */
		public static function vendor_save( $post_id, $post, $posted ) {
			$coupon_obj       = new WC_Coupon( $post_id );
			$vendor_id        = isset( $posted['_mvr_coupon_vendor'] ) ? $posted['_mvr_coupon_vendor'] : '';
			$status           = isset( $posted['_mvr_coupon_status'] ) ? $posted['_mvr_coupon_status'] : '';
			$coupon_post_type = isset( $posted['_mvr_coupon_post_type'] ) ? $posted['_mvr_coupon_post_type'] : '';

			if ( $status ) {
				$coupon_obj->set_props( array( 'status' => $status ) );
			}

			$coupon_obj->update_meta_data( '_mvr_vendor', $vendor_id, true );
			$coupon_obj->save();

			if ( ! empty( $vendor_id ) ) {
				$vendor_obj = mvr_get_vendor( $vendor_id );

				if ( ! mvr_is_vendor( $vendor_obj ) ) {
					return;
				}

				$arg = array(
					'ID'          => $coupon_obj->get_id(),
					'post_author' => $vendor_obj->get_user_id(),
					'post_status' => $status,
				);

				wp_update_post( $arg );

				if ( 'new' === $coupon_post_type ) {
					/**
					 * Add New Coupon from Vendor.
					 *
					 * @since 1.0.0
					 */
					do_action( 'mvr_vendor_new_coupon_submitted', $coupon_obj, $vendor_obj );
				}

				/**
				 * Vendor Coupon Save
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_vendor_coupon_save', $coupon_obj, $vendor_obj );

				$coupon_link = $coupon_obj ? admin_url( 'post.php?post=' . $coupon_obj->get_id() . '&action=edit' ) : '';

				if ( $coupon_link ) {
					mvr_add_admin_notice( esc_html__( 'Coupon saved', 'multi-vendor-marketplace' ) );
					wp_safe_redirect( add_query_arg( array( '_mvr_access' => wp_create_nonce( 'mvr-vendor-access' ) ), $coupon_link ) );
					exit();
				}
			}
		}
	}
}
