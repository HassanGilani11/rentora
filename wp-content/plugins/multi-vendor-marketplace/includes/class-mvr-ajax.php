<?php
/**
 * Ajax Function
 *
 * @package Multi-Vendor for WooCommerce/Ajax
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Ajax' ) ) {
	/**
	 * Class MVR_Ajax.
	 * */
	class MVR_Ajax {

		/**
		 * Class initialization.
		 * */
		public static function init() {
			$ajax_events = array(
				'json_search_users'              => false,
				'json_search_vendors'            => false,
				'json_search_staffs'             => false,
				'get_vendor_details'             => false,
				'get_commission_details'         => false,
				'duplicate_product'              => false,
				'remove_spmv_product'            => false,
				'validate_vendor_shop'           => true,
				'validate_vendor_slug'           => true,
				'add_vendor_note'                => false,
				'delete_vendor_note'             => false,
				'add_payout_batch_note'          => false,
				'delete_payout_batch_note'       => false,
				'send_enquiry_reply'             => false,
				'add_vendor'                     => false,
				'pay_vendor_amount'              => false,
				'add_staff'                      => false,
				'add_commission'                 => false,
				'check_order_commission'         => false,
				'add_withdraw'                   => false,
				'get_vendor_withdraw_amount'     => false,
				'add_vendor_staff'               => false,
				'remove_vendor_staff'            => false,
				'update_notification_read_count' => false,
				'update_enquiry_read_count'      => false,
			);

			foreach ( $ajax_events as $ajax_event => $nopriv ) {
				// For user support.
				add_action( "wp_ajax_mvr_{$ajax_event}", array( __CLASS__, $ajax_event ) );

				if ( $nopriv ) {
					// For guest support.
					add_action( "wp_ajax_nopriv_mvr_{$ajax_event}", array( __CLASS__, $ajax_event ) );
				}
			}
		}

		/**
		 * Search Users.
		 *
		 * @since 1.0.0
		 * @throws exception Invalid Request.
		 * */
		public static function json_search_users() {
			check_ajax_referer( 'mvr-search-nonce', 'security' );

			try {
				$request = $_GET;
				$term    = isset( $request['term'] ) ? sanitize_text_field( wp_unslash( $request['term'] ) ) : '';

				if ( empty( $term ) ) {
					throw new exception( esc_html__( 'No user found', 'multi-vendor-marketplace' ) );
				}

				$found_users = array();
				$users_obj   = mvr_get_users(
					array(
						'include_ids' => mvr_get_allowed_user_ids(),
						's'           => $term,
					)
				);

				if ( $users_obj->has_user ) {
					foreach ( $users_obj->users as $user ) {
						$found_users[ $user->ID ] = $user->display_name . ' (#' . $user->ID . ' &ndash; ' . sanitize_email( $user->user_email ) . ')';
					}
				}

				wp_send_json( $found_users );
			} catch ( Exception $ex ) {
				wp_die();
			}
		}

		/**
		 * Search Staff
		 *
		 * @since 1.0.0
		 * */
		public static function json_search_staffs() {
			check_ajax_referer( 'search-products', 'security' );

			$requested = $_GET;
			$args      = array();

			if ( isset( $requested['exclude'] ) && ! empty( $requested['exclude'] ) ) {
				if ( is_array( $requested['exclude'] ) ) {
					$args['exclude_ids'] = array_map( 'absint', $requested['exclude'] );
				} else {
					$args['exclude_ids'] = array_map( 'absint', explode( ',', $requested['exclude'] ) );
				}
			}

			if ( isset( $requested['include'] ) && ! empty( $requested['include'] ) ) {
				if ( is_array( $requested['include'] ) ) {
					$args['include_ids'] = array_map( 'absint', $requested['include'] );
				} else {
					$args['include_ids'] = array_map( 'absint', explode( ',', $requested['include'] ) );
				}
			}

			if ( isset( $requested['term'] ) ) {
				$args['s'] = (string) wc_clean( stripslashes( $requested['term'] ) );
			}

			if ( isset( $requested['limit'] ) && is_numeric( $requested['limit'] ) ) {
				$args['limit'] = $requested['limit'];
			}

			$staffs_object = mvr_get_staffs( $args );
			$found_staffs  = array();

			if ( $staffs_object->has_staff ) {
				foreach ( $staffs_object->staffs as $staff ) {
					if ( empty( $staff->get_vendor_id() ) ) {
						$found_staffs[ $staff->get_id() ] = wp_kses_post( '(#' . absint( $staff->get_id() ) . ') &ndash; ' . $staff->get_name() );
					}
				}
			}

			wp_send_json( $found_staffs );
		}

		/**
		 * Search Vendors
		 *
		 * @since 1.0.0
		 * */
		public static function json_search_vendors() {
			check_ajax_referer( 'search-products', 'security' );

			$requested = $_GET;
			$args      = array();

			if ( isset( $requested['exclude'] ) && ! empty( $requested['exclude'] ) ) {
				if ( is_array( $requested['exclude'] ) ) {
					$args['exclude_ids'] = array_map( 'absint', $requested['exclude'] );
				} else {
					$args['exclude_ids'] = array_map( 'absint', explode( ',', $requested['exclude'] ) );
				}
			}

			if ( isset( $requested['include'] ) && ! empty( $requested['include'] ) ) {
				if ( is_array( $requested['include'] ) ) {
					$args['include_ids'] = array_map( 'absint', $requested['include'] );
				} else {
					$args['include_ids'] = array_map( 'absint', explode( ',', $requested['include'] ) );
				}
			}

			if ( isset( $requested['term'] ) ) {
				$args['s'] = (string) wc_clean( stripslashes( $requested['term'] ) );
			}

			if ( isset( $requested['limit'] ) && is_numeric( $requested['limit'] ) ) {
				$args['limit'] = $requested['limit'];
			}

			$vendors_object = mvr_get_vendors( $args );
			$found_vendors  = array();

			if ( $vendors_object->has_vendor ) {
				foreach ( $vendors_object->vendors as $vendor ) {
					$found_vendors[ $vendor->get_id() ] = wp_kses_post( '(#' . absint( $vendor->get_id() ) . ') &ndash; ' . $vendor->get_shop_name() . ' - ' . $vendor->get_name() );
				}
			}

			wp_send_json( $found_vendors );
		}

		/**
		 * Get vendor details.
		 *
		 * @since 1.0.0
		 */
		public static function get_vendor_details() {
			check_admin_referer( 'mvr-pay-vendor', 'security' );

			if ( ! isset( $_GET['vendor_id'] ) ) {
				wp_die( -1 );
			}

			$vendor_obj = mvr_get_vendor( absint( $_GET['vendor_id'] ) );

			if ( ! mvr_is_vendor( $vendor_obj ) ) {
				wp_die( -1 );
			}

			/**
			 * Vendor details
			 *
			 * @since 1.0.0
			 */
			$vendor_data = apply_filters(
				'mvr_admin_pay_vendor_get_vendor_details',
				array(
					'vendor_id'          => $vendor_obj->get_id(),
					'vendor_name'        => $vendor_obj->get_name(),
					'date'               => $vendor_obj->get_date_created()->date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ),
					'amount'             => $vendor_obj->get_amount(),
					'locked_amount'      => $vendor_obj->get_locked_amount(),
					'total_amount'       => $vendor_obj->get_total_amount(),
					'amount_disp'        => wc_price( $vendor_obj->get_amount() ),
					'locked_amount_disp' => wc_price( $vendor_obj->get_locked_amount() ),
					'total_amount_disp'  => wc_price( $vendor_obj->get_total_amount() ),
				),
				$vendor_obj
			);

			wp_send_json_success( $vendor_data );
		}

		/**
		 * Get commission details.
		 *
		 * @since 1.0.0
		 */
		public static function get_commission_details() {
			check_admin_referer( 'mvr-preview-commission', 'security' );

			if ( ! isset( $_GET['commission_id'] ) ) {
				wp_die( -1 );
			}

			$commission_obj = mvr_get_commission( absint( $_GET['commission_id'] ) );

			if ( mvr_is_commission( $commission_obj ) ) {
				include_once __DIR__ . '/admin/list-tables/class-mvr-admin-list-table-commission.php';

				wp_send_json_success( MVR_Admin_List_Table_Commission::commission_preview_get_commission_details( $commission_obj ) );
			}

			wp_die();
		}

		/**
		 * Duplicate Product.
		 *
		 * @since 1.0.0
		 * @throws Exception Invalid Request.
		 */
		public static function duplicate_product() {
			check_admin_referer( 'mvr-duplicate-product', 'security' );

			try {
				if ( ! isset( $_POST['product_id'] ) || ! isset( $_POST['vendor_id'] ) || ! isset( $_POST['source_vendor_id'] ) ) {
					throw new Exception( __( 'Invalid response', 'multi-vendor-marketplace' ) );
				}

				$product_id = absint( wp_unslash( $_POST['product_id'] ) );

				if ( empty( $product_id ) ) {
					throw new Exception( __( 'Invalid Product', 'multi-vendor-marketplace' ) );
				}

				$vendor_id  = absint( wp_unslash( $_POST['vendor_id'] ) );
				$vendor_obj = mvr_get_vendor( $vendor_id );

				if ( empty( $vendor_id ) || ! mvr_is_vendor( $vendor_obj ) ) {
					throw new Exception( __( 'Invalid Vendor', 'multi-vendor-marketplace' ) );
				}

				$wc_adp            = new WC_Admin_Duplicate_Product();
				$duplicate_product = $wc_adp->product_duplicate( wc_get_product( $product_id ) );

				if ( ! $duplicate_product ) {
					throw new Exception( __( 'Invalid Duplicate Product', 'multi-vendor-marketplace' ) );
				}

				$duplicate_product->add_meta_data( '_mvr_vendor', $vendor_id, true );
				$duplicate_product->save();

				$source_vendor_id = absint( wp_unslash( $_POST['source_vendor_id'] ) );
				$product_map      = mvr_get_all_spmv(
					array(
						'product_id' => $product_id,
						'vendor_id'  => $source_vendor_id,
					)
				);

				if ( ! $product_map->has_spmv ) {
					$spmv_obj = new MVR_SPMV();
					$spmv_obj->set_props(
						array(
							'map_id'     => mvr_get_map_id(),
							'product_id' => $product_id,
							'vendor_id'  => $source_vendor_id,
						)
					);
					$spmv_obj->set_map_id( mvr_get_map_id() );
					$spmv_obj->save();
				} else {
					$spmv_obj = current( $product_map->spmv_args );
				}

				if ( ! mvr_is_spmv( $spmv_obj ) ) {
					throw new Exception( __( 'Something went wrong', 'multi-vendor-marketplace' ) );
				}

				$vendor_product_map_ids = mvr_get_spmv_product_map_ids( array( 'vendor_id' => $vendor_id ) );

				if ( mvr_check_is_array( $vendor_product_map_ids ) ) {
					if ( in_array( $spmv_obj->get_map_id(), $vendor_product_map_ids, true ) ) {
						throw new Exception( __( 'Product Already Linked to Vendor', 'multi-vendor-marketplace' ) );
					}
				}

				$new_spmv_obj = new MVR_SPMV();
				$new_spmv_obj->set_props(
					array(
						'product_id' => $duplicate_product->get_id(),
						'vendor_id'  => $vendor_obj->get_id(),
						'map_id'     => $spmv_obj->get_map_id(),
						'version'    => MVR_VERSION,
					)
				);
				$new_spmv_obj->save();

				wp_send_json_success(
					array(
						'vendor_url'  => esc_url( get_edit_post_link( $vendor_obj->get_id() ) ),
						'shop_name'   => $vendor_obj->get_shop_name(),
						'spmv_id'     => $new_spmv_obj->get_id(),
						'vendor_name' => $vendor_obj->get_name(),
						'product_id'  => $duplicate_product->get_id(),
						'redirect'    => esc_url( mvr_get_dashboard_endpoint_url( 'mvr-edit-product', $duplicate_product->get_id() ) ),
					)
				);
			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => esc_html( $e->getMessage() ) ) );
			}
		}

		/**
		 * Remove Single Product Multi vendor Product
		 *
		 * @since 1.0.0
		 * @throws Exception Invalid Request.
		 */
		public static function remove_spmv_product() {
			check_admin_referer( 'mvr-remove-spmv-product', 'security' );

			try {
				if ( ! isset( $_POST['product_id'] ) || ! isset( $_POST['spmv_id'] ) ) {
					throw new Exception( __( 'Invalid response', 'multi-vendor-marketplace' ) );
				}

				$spmv_id     = absint( wp_unslash( $_POST['spmv_id'] ) );
				$product_id  = absint( wp_unslash( $_POST['product_id'] ) );
				$product_obj = wc_get_product( $product_id );

				if ( empty( $product_id ) || ! is_a( $product_obj, 'WC_Product' ) ) {
					throw new Exception( __( 'Invalid Product', 'multi-vendor-marketplace' ) );
				}

				$spmv_obj = mvr_get_spmv( $spmv_id );

				if ( ! mvr_is_spmv( $spmv_obj ) ) {
					throw new Exception( __( 'Invalid Object', 'multi-vendor-marketplace' ) );
				}

				$vendor_id = $spmv_obj->get_vendor_id();

				// Delete Product.
				$product_obj->delete( true );
				$spmv_obj->delete( true );

				wp_send_json_success( array( 'vendor_id' => $vendor_id ) );
			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => esc_html( $e->getMessage() ) ) );
			}
		}

		/**
		 * Validate Vendor Slug.
		 *
		 * @since 1.0.0
		 * @throws Exception Invalid Request.
		 */
		public static function validate_vendor_shop() {
			check_admin_referer( 'mvr-vendor-shop', 'security' );

			try {
				if ( ! isset( $_POST['shop_name'] ) ) {
					throw new Exception( __( 'Invalid response', 'multi-vendor-marketplace' ) );
				}

				$shop_name = sanitize_text_field( wp_unslash( $_POST['shop_name'] ) );
				$vendor_id = isset( $_POST['vendor_id'] ) ? absint( sanitize_text_field( wp_unslash( $_POST['vendor_id'] ) ) ) : '';

				if ( empty( $shop_name ) || strlen( $shop_name ) < 3 ) {
					throw new Exception( __( 'Please Enter the shop more than 3 or more characters', 'multi-vendor-marketplace' ) );
				}

				$status = mvr_check_shop_name_exists( $shop_name, $vendor_id ) ? 'unavailable' : 'available';

				wp_send_json_success(
					array(
						'status'    => $status,
						'shop_name' => $shop_name,
					)
				);
			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => esc_html( $e->getMessage() ) ) );
			}
		}

		/**
		 * Validate Vendor Slug.
		 *
		 * @since 1.0.0
		 * @throws Exception Invalid Request.
		 */
		public static function validate_vendor_slug() {
			check_admin_referer( 'mvr-vendor-slug', 'security' );

			try {
				if ( ! isset( $_POST['slug'] ) ) {
					throw new Exception( __( 'Invalid response', 'multi-vendor-marketplace' ) );
				}

				$slug      = sanitize_title( wp_unslash( $_POST['slug'] ) );
				$vendor_id = isset( $_POST['vendor_id'] ) ? absint( sanitize_text_field( wp_unslash( $_POST['vendor_id'] ) ) ) : '';

				if ( empty( $slug ) || strlen( $slug ) < 3 ) {
					throw new Exception( __( 'Please Enter the slug more than 3 or more characters', 'multi-vendor-marketplace' ) );
				}

				$status = mvr_check_shop_slug_exists( $slug, $vendor_id ) ? 'unavailable' : 'available';

				wp_send_json_success(
					array(
						'status' => $status,
						'url'    => mvr_get_store_url( $slug ),
					)
				);
			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => esc_html( $e->getMessage() ) ) );
			}
		}

		/**
		 * Add vendor note via ajax.
		 *
		 * @since 1.0.0
		 * @throws Exception Invalid Arguments.
		 */
		public static function add_vendor_note() {
			check_ajax_referer( 'mvr-add-vendor-note', 'security' );

			try {
				$posted = $_POST;

				if ( ! current_user_can( 'edit_posts' ) || ! isset( $posted['post_id'], $posted['note'] ) ) {
					throw new Exception( __( 'Invalid response', 'multi-vendor-marketplace' ) );
				}

				$post_id = absint( $posted['post_id'] );
				$note    = wp_kses_post( trim( wp_unslash( $posted['note'] ) ) );

				if ( empty( $post_id ) ) {
					throw new Exception( __( 'Invalid Post', 'multi-vendor-marketplace' ) );
				}

				$vendor_obj = mvr_get_vendor( $post_id );

				if ( ! mvr_is_vendor( $vendor_obj ) ) {
					throw new Exception( __( 'Invalid Vendor', 'multi-vendor-marketplace' ) );
				}

				ob_start();

				$comment_id = $vendor_obj->add_note( $note, false, false );
				$note       = mvr_get_vendor_note( $comment_id );
				/**
				 * Get the vendor note CSS class.
				 *
				 * @since 1.0.0
				 */
				$css_class = apply_filters( 'mvr_vendor_note_class', array( 'note' ), $note );

				include 'admin/meta-boxes/views/html-vendor-note.php';

				$html = ob_get_contents();

				ob_end_clean();

				wp_send_json_success( array( 'html' => $html ) );
			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => esc_html( $e->getMessage() ) ) );
			}
		}

		/**
		 * Delete vendor note via ajax.
		 *
		 * @since 1.0.0
		 * @throws Exception When invalid response.
		 */
		public static function delete_vendor_note() {
			check_ajax_referer( 'mvr-delete-vendor-note', 'security' );

			try {
				if ( ! current_user_can( 'edit_posts' ) || ! isset( $_POST['note_id'] ) ) {
					throw new Exception( __( 'Invalid response', 'multi-vendor-marketplace' ) );
				}

				$note_id = absint( $_POST['note_id'] );

				if ( $note_id > 0 ) {
					mvr_delete_vendor_note( $note_id );
				}

				wp_send_json_success( true );
			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => esc_html( $e->getMessage() ) ) );
			}
		}

		/**
		 * Add Payout Batch note via ajax.
		 *
		 * @since 1.0.0
		 * @throws Exception Invalid Arguments.
		 */
		public static function add_payout_batch_note() {
			check_ajax_referer( 'mvr-add-vendor-note', 'security' );

			try {
				$posted = $_POST;

				if ( ! current_user_can( 'edit_posts' ) || ! isset( $posted['post_id'], $posted['note'] ) ) {
					throw new Exception( __( 'Invalid response', 'multi-vendor-marketplace' ) );
				}

				$post_id = absint( $posted['post_id'] );

				if ( empty( $post_id ) ) {
					throw new Exception( __( 'Invalid Post', 'multi-vendor-marketplace' ) );
				}

				$payout_batch_obj = mvr_get_payout_batch( $post_id );

				if ( ! mvr_is_payout_batch( $payout_batch_obj ) ) {
					throw new Exception( __( 'Invalid Payout Batch', 'multi-vendor-marketplace' ) );
				}

				$note       = wp_kses_post( trim( wp_unslash( $posted['note'] ) ) );
				$comment_id = $payout_batch_obj->add_note( $note );

				if ( ! $comment_id ) {
					throw new Exception( __( 'Invalid Payout Batch Note', 'multi-vendor-marketplace' ) );
				}

				$note = mvr_get_payout_batch_note( $comment_id );

				/**
				 * Get the Payout batch note CSS class.
				 *
				 * @since 1.0.0
				 */
				$css_class = apply_filters( 'mvr_payout_batch_note_class', array( 'note' ), $note );

				ob_start();

				include 'admin/meta-boxes/views/html-payout-batch-note.php';

				$html = ob_get_contents();

				ob_end_clean();

				wp_send_json_success( array( 'html' => $html ) );
			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => esc_html( $e->getMessage() ) ) );
			}
		}

		/**
		 * Delete payout batch note via ajax.
		 *
		 * @since 1.0.0
		 * @throws Exception When invalid response.
		 */
		public static function delete_payout_batch_note() {
			check_ajax_referer( 'mvr-delete-vendor-note', 'security' );

			try {
				if ( ! current_user_can( 'edit_posts' ) || ! isset( $_POST['note_id'] ) ) {
					throw new Exception( __( 'Invalid response', 'multi-vendor-marketplace' ) );
				}

				$note_id = absint( $_POST['note_id'] );

				if ( $note_id > 0 ) {
					mvr_delete_payout_batch_note( $note_id );
				}

				wp_send_json_success( true );
			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => esc_html( $e->getMessage() ) ) );
			}
		}

		/**
		 * Send Enquiry reply message.
		 *
		 * @since 1.0.0
		 * @throws Exception Invalid Request Data.
		 */
		public static function send_enquiry_reply() {
			check_ajax_referer( 'mvr-enquiry', 'security' );

			try {
				if ( ! isset( $_POST['enquiry_id'] ) || ! isset( $_POST['customer_email'] ) || ! isset( $_POST['message'] ) ) {
					throw new Exception( __( 'Invalid response', 'multi-vendor-marketplace' ) );
				}

				$posted         = $_POST;
				$customer_email = sanitize_email( wp_unslash( $posted['customer_email'] ) );
				$enquiry_id     = sanitize_text_field( wp_unslash( $posted['enquiry_id'] ) );
				$enquiry_obj    = mvr_get_enquiry( $enquiry_id );

				if ( ! mvr_is_enquiry( $enquiry_obj ) ) {
					throw new Exception( __( 'Invalid Enquiry', 'multi-vendor-marketplace' ) );
				}
				/* translators: %s:Enquiry ID */
				$subject = sprintf( esc_html__( 'Enquiry %s', 'multi-vendor-marketplace' ), '#' . esc_attr( $enquiry_obj->get_id() ) );
				$message = wp_unslash( $posted['message'] );

				if ( empty( $message ) ) {
					throw new Exception( __( 'Please Enter a Message', 'multi-vendor-marketplace' ) );
				}

				if ( ! MVR_Emails::send( $customer_email, $subject, $message ) ) {
					throw new Exception( __( 'Something went wrong. Email not sent.', 'multi-vendor-marketplace' ) );
				}

				$replies = maybe_unserialize( $enquiry_obj->get_reply() );
				$args    = array(
					'date'    => current_time( 'mysql', 1 ),
					'subject' => $subject,
					'message' => $message,
				);

				if ( mvr_check_is_array( $replies ) ) {
					$replies[] = $args;
				} else {
					$replies = array( $args );
				}

				$enquiry_obj->set_reply( $replies );
				$enquiry_obj->save();

				wp_send_json_success(
					array(
						'message' => esc_html__( 'Email Sent Successfully', 'multi-vendor-marketplace' ),
						'url'     => esc_url( mvr_get_dashboard_endpoint_url( 'mvr-enquiry', $enquiry_obj->get_id() ) ),
					)
				);
			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => esc_html( $e->getMessage() ) ) );
			}
		}

		/**
		 * Add Vendor.
		 *
		 * @since 1.0.0
		 * @throws Exception Invalid Exception.
		 */
		public static function add_vendor() {
			check_ajax_referer( 'mvr-add-vendor', 'security' );

			try {
				$request     = $_POST;
				$vendor_from = isset( $request['vendor_from'] ) ? wp_unslash( $request['vendor_from'] ) : '1';

				if ( '2' === $vendor_from ) {
					$user_name = isset( $request['user_name'] ) ? sanitize_title( wp_unslash( $request['user_name'] ) ) : '';

					if ( empty( $user_name ) ) {
						throw new Exception( esc_html__( 'Username Required', 'multi-vendor-marketplace' ) );
					}

					$email = isset( $request['user_email'] ) ? sanitize_email( wp_unslash( $request['user_email'] ) ) : '';

					if ( email_exists( $email ) ) {
						throw new Exception( __( 'An account is already registered with your email address.', 'multi-vendor-marketplace' ) );
					}

					$password = isset( $request['password'] ) ? sanitize_text_field( wp_unslash( $request['password'] ) ) : '';

					if ( empty( $password ) ) {
						throw new Exception( esc_html__( 'Password Required', 'multi-vendor-marketplace' ) );
					}

					$confirm_password = isset( $request['confirm_password'] ) ? sanitize_text_field( wp_unslash( $request['confirm_password'] ) ) : '';

					if ( $confirm_password !== $password ) {
						throw new Exception( esc_html__( 'Password Not Matching', 'multi-vendor-marketplace' ) );
					}

					$user_id = wc_create_new_customer( sanitize_email( $email ), $user_name, $password, array( 'is_mvr_vendor' => true ) );
				} else {
					$user_id = isset( $request['user_id'] ) ? absint( wp_unslash( $request['user_id'] ) ) : 0;

					if ( empty( $user_id ) ) {
						throw new Exception( __( 'Invalid User', 'multi-vendor-marketplace' ) );
					}

					if ( mvr_user_is_vendor( $user_id ) ) {
						throw new Exception( __( 'This user is already a vendor.', 'multi-vendor-marketplace' ) );
					}
				}
				$user_obj = get_user_by( 'ID', $user_id );

				if ( ! $user_obj ) {
					throw new Exception( __( 'Invalid User', 'multi-vendor-marketplace' ) );
				}

				$user_obj->remove_role( 'customer' );
				$user_obj->set_role( 'mvr-vendor' );

				$vendor_obj = new MVR_Vendor();
				$vendor_obj->set_props(
					array(
						'user_id'                        => $user_obj->ID,
						'name'                           => $user_obj->user_login,
						'email'                          => $user_obj->user_email,
						'enable_product_management'      => get_option( 'mvr_settings_enable_product_management', 'no' ),
						'product_creation'               => get_option( 'mvr_settings_enable_product_creation', 'no' ),
						'product_modification'           => get_option( 'mvr_settings_enable_product_modification', 'no' ),
						'published_product_modification' => get_option( 'mvr_settings_enable_published_product_modification', 'no' ),
						'manage_inventory'               => get_option( 'mvr_settings_enable_manage_inventory', 'no' ),
						'product_deletion'               => get_option( 'mvr_settings_enable_product_deletion', 'no' ),
						'enable_order_management'        => get_option( 'mvr_settings_enable_order_management', 'no' ),
						'order_status_modification'      => get_option( 'mvr_settings_enable_order_status_management', 'no' ),
						'commission_info_display'        => get_option( 'mvr_settings_commission_info_management', 'no' ),
						'enable_coupon_management'       => get_option( 'mvr_settings_enable_coupon_management', 'no' ),
						'coupon_creation'                => get_option( 'mvr_settings_enable_coupon_creation_management', 'no' ),
						'published_coupon_modification'  => get_option( 'mvr_settings_enable_published_coupon_modification', 'no' ),
						'coupon_modification'            => get_option( 'mvr_settings_enable_coupon_modification_management', 'no' ),
						'coupon_deletion'                => get_option( 'mvr_settings_enable_coupon_deletion_management', 'no' ),
					)
				);
				$vendor_obj->save();

				/**
				 * Admin After Create Vendor
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_admin_after_create_vendor', $vendor_obj );

				wp_send_json_success( array( 'redirect' => $vendor_obj->get_admin_edit_url() ) );
			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => wp_kses_post( $e->getMessage() ) ) );
			}
		}

		/**
		 * Pay Vendor Amount.
		 *
		 * @since 1.0.0
		 * @throws Exception Invalid Exception.
		 */
		public static function pay_vendor_amount() {
			check_ajax_referer( 'mvr-pay-vendor', 'security' );

			try {
				$request   = $_POST;
				$vendor_id = isset( $request['vendor_id'] ) ? absint( wp_unslash( $request['vendor_id'] ) ) : '';

				if ( empty( $vendor_id ) ) {
					throw new Exception( esc_html__( 'Invalid Vendor', 'multi-vendor-marketplace' ) );
				}

				$vendor_obj = mvr_get_vendor( $vendor_id );

				if ( ! mvr_is_vendor( $vendor_obj ) ) {
					throw new Exception( esc_html__( 'Invalid Vendor', 'multi-vendor-marketplace' ) );
				}

				$withdraw_amount = isset( $_POST['amount'] ) ? (float) sanitize_text_field( wp_unslash( $_POST['amount'] ) ) : 0;

				if ( empty( $withdraw_amount ) ) {
					throw new Exception( esc_html__( 'Please Enter Valid Amount', 'multi-vendor-marketplace' ) );
				}

				$minimum_withdraw = (float) get_option( 'mvr_settings_min_withdraw_threshold', 0 );

				if ( $withdraw_amount < $minimum_withdraw ) {
					throw new Exception( esc_html__( 'Amount should not be less than the minimum amount', 'multi-vendor-marketplace' ) );
				}

				if ( $withdraw_amount > (float) $vendor_obj->get_amount() ) {
					throw new Exception( esc_html__( 'Amount should not be more than the maximum amount', 'multi-vendor-marketplace' ) );
				}

				$withdraw_charge = $vendor_obj->calculate_withdraw_charge( $withdraw_amount );
				$withdraw_amount = $withdraw_amount - $withdraw_charge;

				if ( $withdraw_amount <= 0 ) {
					throw new Exception( esc_html__( 'Invalid Amount', 'multi-vendor-marketplace' ) );
				}

				// Create Withdraw Object.
				$withdraw_obj = new MVR_Withdraw();
				$withdraw_obj->set_props(
					array(
						'amount'         => $withdraw_amount,
						'vendor_id'      => $vendor_obj->get_id(),
						'payment_method' => $vendor_obj->get_payment_method(),
						'charge_amount'  => $withdraw_charge,
						'created_via'    => 'admin',
						'source_id'      => 0,
						'source_from'    => 'manual',
						'currency'       => get_woocommerce_currency(),
					)
				);
				$withdraw_obj->save();
				$withdraw_obj->update_status( 'pending' );

				// Update Vendor Amount.
				$vendor_amount = $vendor_obj->get_amount() - ( $withdraw_amount + $withdraw_charge );

				$vendor_obj->set_amount( $vendor_amount );
				$vendor_obj->save();

				if ( '2' === $vendor_obj->get_payment_method() && 'yes' === get_option( 'mvr_settings_enable_paypal_payouts', 'no' ) ) {
					$receiver_email = sanitize_email( $vendor_obj->get_paypal_email() );

					if ( empty( $receiver_email ) ) {
						throw new Exception( esc_html__( 'Invalid PayPal Email.', 'multi-vendor-marketplace' ) );
					}

					$items = array(
						array(
							'recipient_type' => 'EMAIL',
							'receiver'       => $receiver_email,
							'note'           => __( 'Payout received.', 'multi-vendor-marketplace' ),
							'sender_item_id' => $vendor_obj->get_id(),
							'amount'         => array(
								'value'    => wc_format_decimal( $withdraw_amount, wc_get_price_decimals() ),
								'currency' => get_woocommerce_currency(),
							),
						),
					);
					$args  = array(
						$vendor_obj->get_id() => array(
							'vendor_id'   => $vendor_obj->get_id(),
							'source_id'   => $withdraw_obj->get_id(),
							'source_from' => 'withdraw',
							'amount'      => (float) $withdraw_amount,
							'charge'      => $withdraw_charge,
							'schedule'    => '0',
							'receiver'    => $receiver_email,
						),
					);

					$payout_batch_obj = new MVR_Payout_Batch();
					$payout_batch_obj->set_props(
						array(
							'items'           => $items,
							'email_subject'   => __( 'Payout Received Successful', 'multi-vendor-marketplace' ),
							'email_message'   => __( 'You have received a payout! Thanks!!', 'multi-vendor-marketplace' ),
							'additional_data' => $args,
						)
					);
					$payout_batch_obj->save();
					$payout_batch_obj->add_note( __( 'New Payout entry created.', 'multi-vendor-marketplace' ) );

					if ( mvr_is_payout_batch( $payout_batch_obj ) ) {
						$withdraw_obj->update_status( 'progress' );

						$response = MVR_PayPal_Payouts_Helper::create_batch_payout(
							array(
								'sender_batch_header' => array(
									'sender_batch_id' => $payout_batch_obj->get_id(),
									'email_subject'   => $payout_batch_obj->get_email_subject(),
									'email_message'   => $payout_batch_obj->get_email_message(),
								),
								'items'               => $payout_batch_obj->get_items(),
							)
						);

						if ( is_wp_error( $response ) ) {
							$payout_batch_obj->add_note( $response->get_error_message() );
						} elseif ( is_object( $response ) ) {
							$batch_id = $response->batch_header->payout_batch_id;

							if ( ! empty( $batch_id ) ) {
								$param = array(
									'batch_id'         => $batch_id,
									'payout_batch_obj' => $payout_batch_obj,
									'response'         => $response,
									'payouts_data'     => $args,
								);

								/**
								 * PayPal Batch Payout Completed.
								 *
								 * @since 1.0.0
								 * */
								do_action( 'mvr_after_paypal_payout_batch_completed', $param );
							}
						}
					}
				}

				wp_send_json_success( array( 'redirect' => mvr_get_vendor_page_url() ) );
			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => wp_kses_post( $e->getMessage() ) ) );
			}
		}

		/**
		 * Add Staff.
		 *
		 * @since 1.0.0
		 * @throws Exception Invalid Exception.
		 */
		public static function add_staff() {
			check_ajax_referer( 'mvr-add-staff', 'security' );

			try {
				$request    = $_POST;
				$staff_from = isset( $request['staff_from'] ) ? wp_unslash( $request['staff_from'] ) : '1';

				if ( '2' === $staff_from ) {
					$user_name = isset( $request['user_name'] ) ? sanitize_title( wp_unslash( $request['user_name'] ) ) : '';

					if ( empty( $user_name ) ) {
						throw new Exception( esc_html__( 'Username Required', 'multi-vendor-marketplace' ) );
					}

					$email = isset( $request['user_email'] ) ? sanitize_email( wp_unslash( $request['user_email'] ) ) : '';

					if ( empty( $email ) ) {
						throw new Exception( esc_html__( 'User Email Required', 'multi-vendor-marketplace' ) );
					}

					if ( email_exists( $email ) ) {
						throw new Exception( __( 'An account is already registered with your email address.', 'multi-vendor-marketplace' ) );
					}

					$password = isset( $request['password'] ) ? sanitize_text_field( wp_unslash( $request['password'] ) ) : '';

					if ( empty( $password ) ) {
						throw new Exception( esc_html__( 'Password Required', 'multi-vendor-marketplace' ) );
					}

					$confirm_password = isset( $request['confirm_password'] ) ? sanitize_text_field( wp_unslash( $request['confirm_password'] ) ) : '';

					if ( $confirm_password !== $password ) {
						throw new Exception( esc_html__( 'Password Not Matching', 'multi-vendor-marketplace' ) );
					}

					$user_id = wc_create_new_customer( sanitize_email( $email ), $user_name, $password, array( 'is_mvr_staff' => true ) );

					if ( is_wp_error( $user_id ) ) {
						throw new Exception( $user_id->get_error_message() );
					}
				} else {
					$user_id = isset( $request['user_id'] ) ? absint( wp_unslash( $request['user_id'] ) ) : 0;

					if ( empty( $user_id ) ) {
						throw new Exception( __( 'Invalid User', 'multi-vendor-marketplace' ) );
					}

					if ( mvr_user_is_staff( $user_id ) ) {
						throw new Exception( __( 'This user is already a Staff.', 'multi-vendor-marketplace' ) );
					}
				}

				$user_obj = get_user_by( 'ID', $user_id );

				if ( ! $user_obj ) {
					throw new Exception( __( 'Invalid User', 'multi-vendor-marketplace' ) );
				}

				$user_obj->remove_role( 'customer' );
				$user_obj->set_role( 'mvr-staff' );

				$staff_obj = new MVR_Staff();
				$staff_obj->set_props(
					array(
						'user_id' => $user_obj->ID,
						'name'    => $user_obj->user_login,
						'email'   => $user_obj->user_email,
					)
				);
				$staff_obj->save();

				/**
				 * Admin After Create Vendor
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_admin_after_create_staff', $staff_obj );

				wp_send_json_success( array( 'redirect' => $staff_obj->get_admin_edit_url() ) );
			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => wp_kses_post( $e->getMessage() ) ) );
			}
		}

		/**
		 * Check Order Has Vendor or commissions.
		 *
		 * @since 1.0.0
		 * @throws Exception Invalid Exception.
		 */
		public static function check_order_commission() {
			check_ajax_referer( 'mvr-add-commission', 'security' );

			try {
				$request = $_POST;

				$order_id = isset( $request['order_id'] ) ? absint( wp_unslash( $request['order_id'] ) ) : 0;

				if ( empty( $order_id ) ) {
					throw new Exception( __( 'Invalid Order.', 'multi-vendor-marketplace' ) );
				}

				$order_obj = wc_get_order( $order_id );

				if ( ! is_a( $order_obj, 'WC_Order' ) ) {
					throw new Exception( __( 'Invalid Order.', 'multi-vendor-marketplace' ) );
				}

				$vendor_ids     = array();
				$commission_ids = array();
				$order_vendors  = $order_obj->get_meta( 'mvr_vendor_id', false );

				if ( mvr_check_is_array( $order_vendors ) ) {
					$vendor_ids = array_values( wp_list_pluck( $order_vendors, 'value' ) );
				}

				$commissions_obj = mvr_get_commissions(
					array(
						'source_id'   => $order_id,
						'source_from' => 'order',
						'fields'      => 'ids',
					)
				);

				if ( $commissions_obj->has_commission ) {
					$commission_ids = $commissions_obj->commissions;
				}

				wp_send_json_success(
					array(
						'vendor_ids'     => $vendor_ids,
						'commission_ids' => $commission_ids,
					)
				);
			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => wp_kses_post( $e->getMessage() ) ) );
			}
		}

		/**
		 * Add Commission.
		 *
		 * @since 1.0.0
		 * @throws Exception Invalid Exception.
		 */
		public static function add_commission() {
			check_ajax_referer( 'mvr-add-commission', 'security' );

			try {
				$request         = $_POST;
				$commission_from = isset( $request['commission_from'] ) ? wp_unslash( $request['commission_from'] ) : '1';

				if ( '2' === $commission_from ) {
					$vendor_id = isset( $request['vendor_id'] ) ? absint( wp_unslash( $request['vendor_id'] ) ) : '';

					if ( empty( $vendor_id ) ) {
						throw new Exception( esc_html__( 'Vendor Required', 'multi-vendor-marketplace' ) );
					}

					$vendor_obj = mvr_get_vendor( $vendor_id );

					if ( ! mvr_is_vendor( $vendor_obj ) ) {
						throw new Exception( esc_html__( 'Invalid Vendor', 'multi-vendor-marketplace' ) );
					}

					$amount = isset( $request['amount'] ) ? sanitize_text_field( wp_unslash( $request['amount'] ) ) : '';

					if ( empty( $amount ) ) {
						throw new Exception( esc_html__( 'Commission Amount Required', 'multi-vendor-marketplace' ) );
					}

					$status      = isset( $request['status'] ) ? sanitize_text_field( wp_unslash( $request['status'] ) ) : '';
					$source_id   = isset( $request['source_id'] ) ? absint( wp_unslash( $request['source_id'] ) ) : '';
					$source_from = isset( $request['source_from'] ) ? sanitize_text_field( wp_unslash( $request['source_from'] ) ) : '';
					$settings    = ( 'order' === $source_from ) ? $vendor_obj->get_commission_settings() : $vendor_obj->get_withdraw_settings();

					// Commission Object Creation.
					$commission_obj = new MVR_Commission();

					$commission_obj->set_props(
						array(
							'amount'      => $amount,
							'vendor_id'   => $vendor_id,
							'source_id'   => $source_id,
							'source_from' => $source_from,
							'settings'    => $settings,
							'created_via' => 'manual',
							'currency'    => get_woocommerce_currency(),
							'status'      => $status,
						)
					);
					$commission_obj->save();
				} else {
					$order_id = isset( $request['order_id'] ) ? absint( wp_unslash( $request['order_id'] ) ) : 0;

					if ( empty( $order_id ) ) {
						throw new Exception( __( 'Invalid Order.', 'multi-vendor-marketplace' ) );
					}

					$order_obj = wc_get_order( $order_id );

					if ( ! is_a( $order_obj, 'WC_Order' ) ) {
						throw new Exception( __( 'Invalid Order.', 'multi-vendor-marketplace' ) );
					}

					if ( in_array( $order_obj->get_status(), mvr_get_success_order_statuses(), true ) ) {
						MVR_Order_Manager::update_commission_success( $order_id, $order_obj );
					} elseif ( in_array( $order_obj->get_status(), mvr_get_failed_order_statuses(), true ) ) {
						MVR_Order_Manager::update_commission_failed( $order_id, $order_obj );
					} else {
						MVR_Order_Manager::update_commission_processing( $order_id, $order_obj );
					}
				}

				wp_send_json_success( array( 'redirect' => mvr_get_commission_page_url() ) );
			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => wp_kses_post( $e->getMessage() ) ) );
			}
		}

		/**
		 * Add withdraw.
		 *
		 * @since 1.0.0
		 * @throws Exception Invalid Exception.
		 */
		public static function add_withdraw() {
			check_ajax_referer( 'mvr-add-withdraw', 'security' );

			try {
				$vendor_id = isset( $_POST['vendor_id'] ) ? absint( wp_unslash( $_POST['vendor_id'] ) ) : '';

				if ( empty( $vendor_id ) ) {
					throw new Exception( esc_html__( 'Invalid Vendor', 'multi-vendor-marketplace' ) );
				}

				$vendor_obj = mvr_get_vendor( $vendor_id );

				if ( ! mvr_is_vendor( $vendor_obj ) ) {
					throw new Exception( esc_html__( 'Invalid Vendor', 'multi-vendor-marketplace' ) );
				}

				$withdraw_amount = isset( $_POST['amount'] ) ? (float) sanitize_text_field( wp_unslash( $_POST['amount'] ) ) : 0;

				if ( empty( $withdraw_amount ) ) {
					throw new Exception( esc_html__( 'Please Enter Valid Amount', 'multi-vendor-marketplace' ) );
				}

				$minimum_withdraw = (float) get_option( 'mvr_settings_min_withdraw_threshold', 0 );

				if ( $withdraw_amount < $minimum_withdraw ) {
					throw new Exception( esc_html__( 'Amount should not be less than the minimum amount', 'multi-vendor-marketplace' ) );
				}

				if ( $withdraw_amount > (float) $vendor_obj->get_amount() ) {
					throw new Exception( esc_html__( 'Amount should not be more than the maximum amount', 'multi-vendor-marketplace' ) );
				}

				// Charge amount Calculation.
				$charge_amount = $vendor_obj->calculate_withdraw_charge( $withdraw_amount );

				if ( $charge_amount > 0 ) {
					$withdraw_amount = $withdraw_amount - $charge_amount;
					$withdraw_amount = ( $withdraw_amount < 0 ) ? 0 : $withdraw_amount;
				}

				$status = isset( $_POST['status'] ) ? (float) sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'pending';

				// Create Withdraw Object.
				$withdraw_obj = new MVR_Withdraw();
				$withdraw_obj->set_props(
					array(
						'amount'         => $withdraw_amount,
						'vendor_id'      => $vendor_obj->get_id(),
						'payment_method' => $vendor_obj->get_payment_method(),
						'charge_amount'  => $charge_amount,
						'created_via'    => 'manual_request',
						'source_id'      => 0,
						'source_from'    => 'admin',
						'currency'       => get_woocommerce_currency(),
					)
				);
				$withdraw_obj->save();
				$withdraw_obj->update_status( $status );

				// Update Vendor Amount.
				$vendor_amount = $vendor_obj->get_amount() - $withdraw_amount;

				$vendor_obj->set_amount( $vendor_amount );
				$vendor_obj->save();

				/**
				 * Admin After Create Withdraw Request
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_admin_after_create_withdraw', $withdraw_obj, $vendor_obj );

				wp_send_json_success( array( 'redirect' => mvr_get_withdraw_page_url() ) );
			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => wp_kses_post( $e->getMessage() ) ) );
			}
		}

		/**
		 * Add withdraw.
		 *
		 * @since 1.0.0
		 * @throws Exception Invalid Exception.
		 */
		public static function get_vendor_withdraw_amount() {
			check_ajax_referer( 'mvr-add-withdraw', 'security' );

			try {
				$vendor_id = isset( $_REQUEST['vendor_id'] ) ? absint( wp_unslash( $_REQUEST['vendor_id'] ) ) : '';

				if ( empty( $vendor_id ) ) {
					throw new Exception( __( 'Please Select a Vendor', 'multi-vendor-marketplace' ) );
				}

				$vendor_obj = mvr_get_vendor( $vendor_id );

				if ( ! mvr_is_vendor( $vendor_obj ) ) {
					throw new Exception( __( 'Invalid Vendor', 'multi-vendor-marketplace' ) );
				}

				/* translators: %s:Vendor Amount */
				$html = sprintf( __( 'Available Amount: %s', 'multi-vendor-marketplace' ), wc_price( $vendor_obj->get_amount() ) );

				wp_send_json_success(
					array(
						'amount' => $vendor_obj->get_amount(),
						'html'   => $html,
					)
				);
			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => wp_kses_post( $e->getMessage() ) ) );
			}
		}


		/**
		 * Add vendor Staff.
		 *
		 * @since 1.0.0
		 * @throws Exception Invalid Exception.
		 */
		public static function add_vendor_staff() {
			check_ajax_referer( 'mvr-add-vendor-staff', 'security' );

			try {
				$request  = $_POST;
				$staff_id = isset( $request['staff_id'] ) ? absint( wp_unslash( $request['staff_id'] ) ) : '';

				if ( empty( $staff_id ) ) {
					throw new Exception( esc_html__( 'Staff ID Required', 'multi-vendor-marketplace' ) );
				}

				$staff_obj = mvr_get_staff( $staff_id );

				if ( ! mvr_is_staff( $staff_obj ) ) {
					throw new Exception( esc_html__( 'Invalid Staff', 'multi-vendor-marketplace' ) );
				}

				$vendor_id = isset( $request['vendor_id'] ) ? absint( wp_unslash( $request['vendor_id'] ) ) : '';

				if ( empty( $vendor_id ) ) {
					throw new Exception( esc_html__( 'Invalid Vendor', 'multi-vendor-marketplace' ) );
				}

				$vendor_obj = mvr_get_vendor( $vendor_id );

				if ( ! mvr_is_vendor( $vendor_obj ) ) {
					throw new Exception( esc_html__( 'Invalid Vendor', 'multi-vendor-marketplace' ) );
				}

				$staff_obj->set_vendor_id( $vendor_id );
				$staff_obj->save();

				/**
				 * After Staff Assign.
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_after_assign_staff', $staff_obj );

				ob_start();

				include 'admin/meta-boxes/views/vendor-tab/html-staff-data.php';

				$html = ob_get_contents();

				ob_end_clean();

				wp_send_json_success( array( 'html' => $html ) );
			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => wp_kses_post( $e->getMessage() ) ) );
			}
		}

		/**
		 * Remove vendor Staff.
		 *
		 * @since 1.0.0
		 * @throws Exception Invalid Exception.
		 */
		public static function remove_vendor_staff() {
			check_ajax_referer( 'mvr-remove-vendor-staff', 'security' );

			try {
				$request  = $_POST;
				$staff_id = isset( $request['staff_id'] ) ? absint( wp_unslash( $request['staff_id'] ) ) : '';

				if ( empty( $staff_id ) ) {
					throw new Exception( esc_html__( 'Staff ID Required', 'multi-vendor-marketplace' ) );
				}

				$staff_obj = mvr_get_staff( $staff_id );

				if ( ! mvr_is_staff( $staff_obj ) ) {
					throw new Exception( esc_html__( 'Invalid Staff', 'multi-vendor-marketplace' ) );
				}

				$vendor_id = isset( $request['vendor_id'] ) ? absint( wp_unslash( $request['vendor_id'] ) ) : '';

				if ( empty( $vendor_id ) ) {
					throw new Exception( esc_html__( 'Invalid Vendor', 'multi-vendor-marketplace' ) );
				}

				$vendor_obj = mvr_get_vendor( $vendor_id );

				if ( ! mvr_is_vendor( $vendor_obj ) ) {
					throw new Exception( esc_html__( 'Invalid Vendor', 'multi-vendor-marketplace' ) );
				}

				/**
				 * After Staff remove.
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_before_remove_staff', $staff_obj );

				$staff_obj->set_vendor_id( 0 );
				$staff_obj->save();

				if ( ! $vendor_obj->get_staffs( array( 'status' => 'active' ) )->has_staff ) {
					$html = '<div class="mvr-no-staff-data">' . esc_html__( 'No Staff Found', 'multi-vendor-marketplace' ) . '</div>';
				} else {
					$html = '';
				}

				wp_send_json_success( array( 'html' => $html ) );
			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => wp_kses_post( $e->getMessage() ) ) );
			}
		}

		/**
		 * Update Notification Read Count.
		 *
		 * @since 1.0.0
		 * @throws Exception Invalid Exception.
		 */
		public static function update_notification_read_count() {
			check_ajax_referer( 'mvr-notification', 'security' );

			try {
				$request   = $_POST;
				$vendor_id = isset( $request['vendor_id'] ) ? absint( wp_unslash( $request['vendor_id'] ) ) : '';

				if ( empty( $vendor_id ) ) {
					throw new Exception( esc_html__( 'Invalid Vendor', 'multi-vendor-marketplace' ) );
				}

				$vendor_obj = mvr_get_vendor( $vendor_id );

				if ( ! mvr_is_vendor( $vendor_obj ) ) {
					throw new Exception( esc_html__( 'Invalid Vendor', 'multi-vendor-marketplace' ) );
				}

				wp_send_json_success( array( 'count' => (int) $vendor_obj->get_unread_notification_count() ) );
			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => wp_kses_post( $e->getMessage() ) ) );
			}
		}

		/**
		 * Update Enquiry Read Count.
		 *
		 * @since 1.0.0
		 * @throws Exception Invalid Exception.
		 */
		public static function update_enquiry_read_count() {
			check_ajax_referer( 'mvr-enquiry', 'security' );

			try {
				$request   = $_POST;
				$vendor_id = isset( $request['vendor_id'] ) ? absint( wp_unslash( $request['vendor_id'] ) ) : '';

				if ( empty( $vendor_id ) ) {
					throw new Exception( esc_html__( 'Invalid Vendor', 'multi-vendor-marketplace' ) );
				}

				$vendor_obj = mvr_get_vendor( $vendor_id );

				if ( ! mvr_is_vendor( $vendor_obj ) ) {
					throw new Exception( esc_html__( 'Invalid Vendor', 'multi-vendor-marketplace' ) );
				}

				wp_send_json_success( array( 'count' => (int) $vendor_obj->get_unread_enquiry_count() ) );
			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => wp_kses_post( $e->getMessage() ) ) );
			}
		}
	}

	MVR_Ajax::init();
}
