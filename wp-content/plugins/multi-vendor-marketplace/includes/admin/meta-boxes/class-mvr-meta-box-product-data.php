<?php
/**
 * Vendor Product data
 *
 * Displays the Product Vendor etc.
 *
 * @package  Multi-Vendor\Admin\Meta Boxes
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Meta_Box_Product_Data' ) ) {

	/**
	 * Product Vendor.
	 *
	 * @class MVR_Meta_Box_Product_Data
	 */
	class MVR_Meta_Box_Product_Data {

		/**
		 * Output the metabox.
		 *
		 * @since 1.0.0
		 * @param WP_Post $post Post object.
		 */
		public static function output( $post ) {
			global $post, $product, $pagenow;

			if ( ! is_object( $product ) ) {
				$product = wc_get_product( $post->ID );
			}

			wp_nonce_field( 'mvr_save_data', 'mvr_save_meta_nonce' );

			include 'views/html-product-vendor.php';
		}

		/**
		 * Output the metabox.
		 *
		 * @since 1.0.0
		 * @param WP_Post $post Post object.
		 */
		public static function output_spmv( $post ) {
			global $post, $product;

			if ( ! is_object( $product ) ) {
				$product = wc_get_product( $post->ID );
			}

			$vendor_id = absint( $product->get_meta( '_mvr_vendor', true ) );

			if ( empty( $vendor_id ) ) {
				return;
			}

			$vendor_obj = mvr_get_vendor( $vendor_id );

			if ( ! mvr_is_vendor( $vendor_obj ) ) {
				return;
			}

			$product_map_id = current(
				mvr_get_spmv_product_map_ids(
					array(
						'vendor_id'  => $vendor_id,
						'product_id' => $product->get_id(),
					)
				)
			);

			if ( empty( $product_map_id ) ) {
				$product_map = array();
			} else {
				$product_map = mvr_get_all_spmv(
					array(
						'map_id' => $product_map_id,
					)
				);
			}

			$exclude_vendors = array( $vendor_obj->get_id() );

			wp_nonce_field( 'mvr_save_data', 'mvr_save_meta_nonce' );

			include 'views/html-product-spmv.php';
		}

		/**
		 * Output the metabox.
		 *
		 * @since 1.0.0
		 * @param WP_Post $post Post object.
		 */
		public static function output_vendor_action( $post ) {
			global $post, $product, $pagenow;

			if ( ! is_object( $product ) ) {
				$product = wc_get_product( $post->ID );
			}

			wp_nonce_field( 'mvr_save_data', 'mvr_save_meta_nonce' );

			include 'views/html-product-save.php';
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
			$product       = wc_get_product( $post_id );
			$old_vendor_id = $product->get_meta( '_mvr_vendor', true );

			$vendor_id = isset( $posted['_mvr_product_vendor'] ) ? $posted['_mvr_product_vendor'] : '';
			$product->add_meta_data( '_mvr_vendor', $vendor_id, true );
			$product->save();

			if ( ! empty( $vendor_id ) ) {
				$vendor_obj = mvr_get_vendor( $vendor_id );

				if ( ! mvr_is_vendor( $vendor_obj ) ) {
					return;
				}

				$arg = array(
					'ID'          => $product->get_id(),
					'post_author' => $vendor_obj->get_user_id(),
				);

				wp_update_post( $arg );

				/**
				 * Vendor Coupon Save
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_admin_product_save', $product, $vendor_obj );
			}

			if ( $old_vendor_id && (int) $old_vendor_id !== (int) $vendor_id ) {
				// Single Product Multi Vendor Update.
				$product_map = mvr_get_all_spmv(
					array(
						'product_id' => $product->get_id(),
						'vendor_id'  => $old_vendor_id,
					)
				);
			}
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
			$product           = wc_get_product( $post_id );
			$vendor_id         = isset( $posted['_mvr_product_vendor'] ) ? $posted['_mvr_product_vendor'] : '';
			$status            = isset( $posted['_mvr_product_status'] ) ? $posted['_mvr_product_status'] : '';
			$product_post_type = isset( $posted['_mvr_product_post_type'] ) ? $posted['_mvr_product_post_type'] : '';

			if ( $status ) {
				$product->set_status( $status );
			}

			$product->add_meta_data( '_mvr_vendor', $vendor_id, true );
			$product->save();

			if ( ! empty( $vendor_id ) ) {
				$vendor_obj = mvr_get_vendor( $vendor_id );

				if ( ! mvr_is_vendor( $vendor_obj ) ) {
					return;
				}

				$arg = array(
					'ID'          => $product->get_id(),
					'post_author' => $vendor_obj->get_user_id(),
				);

				wp_update_post( $arg );

				if ( 'new' === $product_post_type ) {
					/**
					 * Add New Product from Vendor.
					 *
					 * @since 1.0.0
					 */
					do_action( 'mvr_vendor_new_product_submitted', $product, $vendor_obj );
				}

				/**
				 * Vendor Product Save
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_vendor_product_save', $product, $vendor_obj );

				$product_link = $product ? admin_url( 'post.php?post=' . $product->get_id() . '&action=edit' ) : '';

				if ( $product_link ) {
					mvr_add_admin_notice( esc_html__( 'Product saved', 'multi-vendor-marketplace' ) );
					wp_safe_redirect( add_query_arg( array( '_mvr_access' => wp_create_nonce( 'mvr-vendor-access' ) ), $product_link ) );
					exit();
				}
			}
		}
	}
}
