<?php
/**
 * Admin Meta boxes
 *
 * @package Multi-Vendor for WooCommerce
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Admin_Meta_Boxes' ) ) {
	/**
	 * Handle Admin meta-boxes.
	 *
	 * @since 1.0.0
	 */
	class MVR_Admin_Meta_Boxes {

		/**
		 * Is meta boxes saved once?
		 *
		 * @var Boolean
		 */
		private static $saved_meta_boxes = false;

		/**
		 * MVR_Admin_Meta_Boxes constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			add_action( 'add_meta_boxes', array( $this, 'remove_meta_boxes' ), 50 );
			add_action( 'add_meta_boxes', array( $this, 'rename_meta_boxes' ), 60 );
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 50 );
			add_action( 'save_post', array( $this, 'save_meta_boxes' ), 5, 2 );

			add_action( 'mvr_process_vendor_meta', 'MVR_Meta_Box_Vendor_Data::save', 10, 3 );
			add_action( 'mvr_process_staff_meta', 'MVR_Meta_Box_Staff_Data::save', 10, 3 );

			if ( ! mvr_check_user_as_vendor_or_staff() ) {
				add_action( 'mvr_process_product_vendor_meta', 'MVR_Meta_Box_Product_Data::save', 10, 3 );
				add_action( 'mvr_process_coupon_vendor_meta', 'MVR_Meta_Box_Coupon_Data::save', 10, 3 );
			} else {
				add_action( 'mvr_process_product_vendor_meta', 'MVR_Meta_Box_Product_Data::vendor_save', 10, 3 );
				add_action( 'mvr_process_coupon_vendor_meta', 'MVR_Meta_Box_Coupon_Data::vendor_save', 10, 3 );
			}
		}

		/**
		 * Add MBP Metaboxes.
		 *
		 * @since 1.0.0
		 */
		public function add_meta_boxes() {
			global $post;
			// Vendor.
			add_meta_box( 'mvr_store_banner', __( 'Store Banner', 'multi-vendor-marketplace' ), 'MVR_Meta_Box_Vendor_Data::output_banner', MVR_Post_Types::MVR_VENDOR, 'normal', 'high' );
			add_meta_box( 'mvr_vendor_data', ' ', 'MVR_Meta_Box_Vendor_Data::output', MVR_Post_Types::MVR_VENDOR, 'normal', 'high' );
			add_meta_box( 'postexcerpt', __( 'Terms & Conditions', 'multi-vendor-marketplace' ), 'MVR_Meta_Box_Vendor_Data::output_tac', MVR_Post_Types::MVR_VENDOR, 'normal', 'low' );
			add_meta_box( 'mvr_vendor_action', __( 'Vendor Action', 'multi-vendor-marketplace' ), 'MVR_Meta_Box_Vendor_Data::output_action', MVR_Post_Types::MVR_VENDOR, 'side', 'high' );
			add_meta_box( 'mvr_vendor_amount', __( 'Vendor Amount', 'multi-vendor-marketplace' ), 'MVR_Meta_Box_Vendor_Data::output_vendor_amount', MVR_Post_Types::MVR_VENDOR, 'side', 'low' );
			add_meta_box( 'mvr_vendor_notes', __( 'Vendor Notes', 'multi-vendor-marketplace' ), 'MVR_Meta_Box_Vendor_Data::output_note', MVR_Post_Types::MVR_VENDOR, 'side', 'low' );

			// Staff.
			add_meta_box( 'mvr_staff_data', ' ', 'MVR_Meta_Box_Staff_Data::output', MVR_Post_Types::MVR_STAFF, 'normal', 'high' );
			add_meta_box( 'mvr_staff_action', __( 'Staff Action', 'multi-vendor-marketplace' ), 'MVR_Meta_Box_Staff_Data::output_action', MVR_Post_Types::MVR_STAFF, 'side', 'high' );

			// Payout Batch.
			add_meta_box( 'mvr_payout_batch_receivers', __( 'Payout Batch Receivers', 'multi-vendor-marketplace' ), 'MVR_Meta_Box_Payout_Batch_Data::output', MVR_Post_Types::MVR_PAYOUT_BATCH, 'normal', 'high' );
			add_meta_box( 'mvr_payout_batch_notes', __( 'Transaction Logs', 'multi-vendor-marketplace' ), 'MVR_Meta_Box_Payout_Batch_Data::output_note', MVR_Post_Types::MVR_PAYOUT_BATCH, 'side', 'low' );

			if ( ! mvr_check_user_as_vendor_or_staff() ) {
				add_meta_box( 'mvr_product_vendor', __( 'Vendor', 'multi-vendor-marketplace' ), 'MVR_Meta_Box_Product_Data::output', 'product', 'side', 'low' );

				if ( is_a( $post, 'WP_Post' ) && 'product' === get_post_type() ) {
					$product_obj = wc_get_product( $post->ID );
					$vendor_id   = $product_obj->get_meta( '_mvr_vendor', true );
					$vendor_obj  = mvr_get_vendor( $vendor_id );

					if ( ! empty( $vendor_id ) && mvr_is_vendor( $vendor_obj ) ) {
						add_meta_box( 'mvr_product_spmv', __( 'Single Product Multiple Vendors', 'multi-vendor-marketplace' ), 'MVR_Meta_Box_Product_Data::output_spmv', 'product', 'side', 'low' );
					}
				}

				add_meta_box( 'mvr_coupon_vendor', __( 'Vendor', 'multi-vendor-marketplace' ), 'MVR_Meta_Box_Coupon_Data::output', 'shop_coupon', 'side', 'low' );
			} else {
				add_meta_box( 'mvr_product_save', __( 'Save', 'multi-vendor-marketplace' ), 'MVR_Meta_Box_Product_Data::output_vendor_action', 'product', 'normal', 'low' );
				add_meta_box( 'mvr_coupon_save', __( 'Save', 'multi-vendor-marketplace' ), 'MVR_Meta_Box_Coupon_Data::output_vendor_action', 'shop_coupon', 'normal', 'low' );
			}
		}

		/**
		 * Remove Metaboxes.
		 *
		 * @since 1.0.0
		 */
		public function remove_meta_boxes() {
			// Vendor.
			remove_meta_box( 'commentsdiv', MVR_Post_Types::MVR_VENDOR, 'normal' );
			remove_meta_box( 'submitdiv', MVR_Post_Types::MVR_VENDOR, 'side' );
			remove_meta_box( 'commentstatusdiv', MVR_Post_Types::MVR_VENDOR, 'side' );
			remove_meta_box( 'commentstatusdiv', MVR_Post_Types::MVR_VENDOR, 'normal' );

			// Staff.
			remove_meta_box( 'submitdiv', MVR_Post_Types::MVR_STAFF, 'side' );

			// Payout Batch.
			remove_meta_box( 'commentsdiv', MVR_Post_Types::MVR_PAYOUT_BATCH, 'normal' );
			remove_meta_box( 'submitdiv', MVR_Post_Types::MVR_PAYOUT_BATCH, 'side' );
			remove_meta_box( 'commentstatusdiv', MVR_Post_Types::MVR_PAYOUT_BATCH, 'side' );
			remove_meta_box( 'commentstatusdiv', MVR_Post_Types::MVR_PAYOUT_BATCH, 'normal' );

			if ( mvr_check_user_as_vendor_or_staff() ) {
				remove_meta_box( 'tagsdiv-product_tag', 'product', 'side' );
				remove_meta_box( 'commentsdiv', 'product', 'normal' );
				remove_meta_box( 'submitdiv', 'product', 'side' );
				remove_meta_box( 'submitdiv', 'shop_coupon', 'side' );
			}
		}

		/**
		 * Rename core meta boxes.
		 */
		public function rename_meta_boxes() {
			global $post;

			// Comments/Reviews.
			if ( isset( $post ) && ( 'mvr-active' === $post->post_status ) && post_type_supports( MVR_Post_Types::MVR_VENDOR, 'comments' ) ) {
				remove_meta_box( 'commentsdiv', MVR_Post_Types::MVR_VENDOR, 'normal' );
				add_meta_box( 'commentsdiv', __( 'Reviews', 'multi-vendor-marketplace' ), 'post_comment_meta_box', MVR_Post_Types::MVR_VENDOR, 'normal' );
			}
		}

		/**
		 * Check if we're saving, the trigger an action based on the post type.
		 *
		 * @since 1.0.0
		 * @param  Integer $post_id Post ID.
		 * @param  Object  $post Post object.
		 */
		public function save_meta_boxes( $post_id, $post ) {
			$post_id = absint( $post_id );

			// $post_id and $post are required
			if ( empty( $post_id ) || empty( $post ) || self::$saved_meta_boxes ) {
				return;
			}

			// Don't save meta boxes for revisions or autosaves.
			if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
				return;
			}

			// Check the nonce.
			if ( empty( $_POST['mvr_save_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['mvr_save_meta_nonce'] ) ), 'mvr_save_data' ) ) {
				return;
			}

			// Check the post being saved == the $post_id to prevent triggering this call for other save_post events.
			$posted = $_POST;

			if ( empty( $posted['post_ID'] ) || absint( $posted['post_ID'] ) !== $post_id ) {
				return;
			}

			// Check user has permission to edit.
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}

			self::$saved_meta_boxes = true;

			if ( MVR_Post_Types::MVR_VENDOR === $post->post_type ) {
				/**
				 * Vendor Data Save.
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_process_vendor_meta', $post_id, $post, $posted );
			} elseif ( MVR_Post_Types::MVR_STAFF === $post->post_type ) {
				/**
				 * Staff Data Save.
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_process_staff_meta', $post_id, $post, $posted );
			} elseif ( 'product' === $post->post_type ) {
				/**
				 * Product Data Save.
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_process_product_vendor_meta', $post_id, $post, $posted );
			} elseif ( 'shop_coupon' === $post->post_type ) {
				/**
				 * Coupon Data Save.
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_process_coupon_vendor_meta', $post_id, $post, $posted );
			}
		}
	}

	new MVR_Admin_Meta_Boxes();
}
