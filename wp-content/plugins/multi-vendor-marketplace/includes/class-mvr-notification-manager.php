<?php
/**
 * Notification Manager.
 *
 * @package Multi-Vendor Functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Notification_Manager' ) ) {

	/**
	 * Manage Notifications.
	 *
	 * @class MVR_Notification_Manager
	 * @package Class
	 */
	class MVR_Notification_Manager {

		/**
		 * Init MVR_Notification_Manager.
		 */
		public static function init() {
			add_action( 'mvr_after_register_vendor', __CLASS__ . '::vendor_register_notification' );
			add_action( 'mvr_after_become_vendor', __CLASS__ . '::become_vendor_notification' );
			add_action( 'mvr_vendor_new_withdraw_request', __CLASS__ . '::create_withdraw_request_notification', 10, 2 );
			add_action( 'mvr_after_store_enquiry_submitted', __CLASS__ . '::create_store_enquiry_notification', 10, 2 );
			add_action( 'mvr_vendor_new_product_submitted', __CLASS__ . '::vendor_new_product_submit_notification', 10, 2 );
			add_action( 'mvr_vendor_new_coupon_submitted', __CLASS__ . '::vendor_new_coupon_submit_notification', 10, 2 );
			add_action( 'mvr_create_new_order', __CLASS__ . '::new_order_notification', 10, 2 );
			add_action( 'post_updated', __CLASS__ . '::update_terms_conditions_notification' );
			add_action( 'mvr_after_store_review_submitted', __CLASS__ . '::new_store_review_notification', 10, 2 );
			add_action( 'mvr_admin_staff_before_save_notification', __CLASS__ . '::staff_assign_notification', 10, 2 );
			add_action( 'mvr_after_create_staff_notification', __CLASS__ . '::staff_assign_notification' );
			add_action( 'mvr_after_assign_staff', __CLASS__ . '::staff_assign_notification' );
			add_action( 'mvr_before_remove_staff', __CLASS__ . '::staff_remove_notification' );
			add_action( 'mvr_vendor_status_active', __CLASS__ . '::vendor_active_notification' );
			add_action( 'mvr_vendor_status_reject', __CLASS__ . '::vendor_reject_notification' );
			add_action( 'mvr_after_bank_transfer_completed', __CLASS__ . '::withdraw_approve_notification', 10, 2 );
			add_action( 'mvr_after_paypal_payout_completed', __CLASS__ . '::withdraw_approve_notification', 10, 2 );
			add_action( 'mvr_after_rejected_withdraw_request', __CLASS__ . '::withdraw_reject_notification', 10, 2 );
		}

		/**
		 * Withdraw Reject.
		 *
		 * @since 1.0.0
		 * @param MVR_Withdraw $withdraw_obj Withdraw Object.
		 * @param MVR_Vendor   $vendor_obj Vendor Object.
		 */
		public static function withdraw_reject_notification( $withdraw_obj, $vendor_obj ) {
			$vendor_obj   = mvr_get_vendor( $vendor_obj );
			$withdraw_obj = mvr_get_withdraw( $withdraw_obj );

			if ( ! mvr_is_vendor( $vendor_obj ) || ! mvr_is_withdraw( $withdraw_obj ) ) {
				return;
			}

			/* translators: %1$s:Withdraw ID */
			$message          = sprintf( esc_html__( 'A withdraw request #%1$s rejected', 'multi-vendor-marketplace' ), esc_attr( $withdraw_obj->get_id() ) );
			$notification_obj = new MVR_Notification();
			$notification_obj->set_props(
				array(
					'message'     => $message,
					'vendor_id'   => $vendor_obj->get_id(),
					'source_id'   => $withdraw_obj->get_id(),
					'source_from' => 'withdraw_reject',
					'to'          => 'vendor',
				)
			);
			$notification_obj->save();
		}

		/**
		 * Withdraw Approve.
		 *
		 * @since 1.0.0
		 * @param MVR_Withdraw $withdraw_obj Withdraw Object.
		 * @param MVR_Vendor   $vendor_obj Vendor Object.
		 */
		public static function withdraw_approve_notification( $withdraw_obj, $vendor_obj ) {
			$vendor_obj   = mvr_get_vendor( $vendor_obj );
			$withdraw_obj = mvr_get_withdraw( $withdraw_obj );

			if ( ! mvr_is_vendor( $vendor_obj ) || ! mvr_is_withdraw( $withdraw_obj ) ) {
				return;
			}

			/* translators: %s:Withdraw ID */
			$message          = sprintf( wp_kses_post( __( 'A withdraw request #%1$s approved', 'multi-vendor-marketplace' ) ), esc_attr( $withdraw_obj->get_id() ) );
			$notification_obj = new MVR_Notification();
			$notification_obj->set_props(
				array(
					'message'     => $message,
					'vendor_id'   => $vendor_obj->get_id(),
					'source_id'   => $withdraw_obj->get_id(),
					'source_from' => 'withdraw_approve',
					'to'          => 'vendor',
				)
			);
			$notification_obj->save();
		}

		/**
		 * Vendor Active Notification
		 *
		 * @since 1.0.0
		 * @param MVR_Vendor $vendor_obj Vendor Object.
		 */
		public static function vendor_reject_notification( $vendor_obj ) {
			if ( ! mvr_is_vendor( $vendor_obj ) ) {
				return;
			}

			$message          = esc_html__( 'Sorry, Your Vendor Application has been Rejected', 'multi-vendor-marketplace' );
			$notification_obj = new MVR_Notification();
			$notification_obj->set_props(
				array(
					'message'     => $message,
					'vendor_id'   => $vendor_obj->get_id(),
					'source_id'   => $vendor_obj->get_id(),
					'source_from' => 'vendor_active',
					'to'          => 'vendor',
				)
			);
			$notification_obj->save();
		}

		/**
		 * Vendor Active Notification
		 *
		 * @since 1.0.0
		 * @param MVR_Vendor $vendor_obj Vendor Object.
		 */
		public static function vendor_active_notification( $vendor_obj ) {
			if ( ! mvr_is_vendor( $vendor_obj ) ) {
				return;
			}

			$message          = esc_html__( 'Congratulations! Your Vendor Application has been Approved', 'multi-vendor-marketplace' );
			$notification_obj = new MVR_Notification();
			$notification_obj->set_props(
				array(
					'message'     => $message,
					'vendor_id'   => $vendor_obj->get_id(),
					'source_id'   => $vendor_obj->get_id(),
					'source_from' => 'vendor_active',
					'to'          => 'vendor',
				)
			);
			$notification_obj->save();
		}

		/**
		 * Staff remove Notification
		 *
		 * @since 1.0.0
		 * @param MVR_Staff $staff_obj Staff Object.
		 */
		public static function staff_remove_notification( $staff_obj ) {
			if ( ! mvr_is_staff( $staff_obj ) ) {
				return;
			}

			$vendor_id  = $staff_obj->get_vendor_id();
			$vendor_obj = mvr_get_vendor( $vendor_id );

			if ( ! mvr_is_vendor( $vendor_obj ) ) {
				return;
			}

			/* translators: %s:$staff */
			$message          = sprintf( esc_html__( 'Staff has been removed for you : %s', 'multi-vendor-marketplace' ), esc_attr( $staff_obj->get_email() ) );
			$notification_obj = new MVR_Notification();
			$notification_obj->set_props(
				array(
					'message'     => $message,
					'vendor_id'   => $vendor_id,
					'source_id'   => $staff_obj->get_id(),
					'source_from' => 'staff',
					'to'          => 'vendor',
				)
			);
			$notification_obj->save();
		}

		/**
		 * Staff Assign Notification
		 *
		 * @since 1.0.0
		 * @param MVR_Staff $staff_obj Staff Object.
		 * @param Array     $staff_data Staff data.
		 */
		public static function staff_assign_notification( $staff_obj, $staff_data = array() ) {
			if ( ! mvr_is_staff( $staff_obj ) ) {
				return;
			}

			if ( mvr_check_is_array( $staff_data ) ) {
				if ( ! isset( $staff_data['vendor_id'] ) || empty( $staff_data['vendor_id'] ) ) {
					return;
				}

				if ( (int) $staff_obj->get_vendor_id() === (int) $staff_data['vendor_id'] ) {
					return;
				}
				$vendor_id = $staff_data['vendor_id'];
			} else {
				$vendor_id = $staff_obj->get_vendor_id();
			}

			$vendor_obj = mvr_get_vendor( $vendor_id );

			if ( ! mvr_is_vendor( $vendor_obj ) ) {
				return;
			}

			/* translators: %s:$staff */
			$message          = sprintf( esc_html__( 'A New Staff has been assigned for you : %s', 'multi-vendor-marketplace' ), '<a href="' . esc_url( mvr_get_dashboard_endpoint_url( 'mvr-edit-staff', $staff_obj->get_id() ) ) . '">#' . esc_attr( $staff_obj->get_email() ) . '</a>' );
			$notification_obj = new MVR_Notification();
			$notification_obj->set_props(
				array(
					'message'     => $message,
					'source_id'   => $staff_obj->get_id(),
					'vendor_id'   => $vendor_id,
					'source_from' => 'staff',
					'to'          => 'vendor',
				)
			);
			$notification_obj->save();
		}

		/**
		 * Vendor Register Notification.
		 *
		 * @since 1.0.0
		 * @param MVR_Review $comment_id Comment ID.
		 * @param MVR_Vendor $vendor_obj Vendor Object.
		 */
		public static function new_store_review_notification( $comment_id, $vendor_obj ) {
			if ( ! mvr_is_vendor( $vendor_obj ) ) {
				return;
			}

			/* translators: %s:$comment_id */
			$message          = sprintf( esc_html__( 'A New Review has been Received : %s', 'multi-vendor-marketplace' ), esc_attr( $comment_id ) );
			$notification_obj = new MVR_Notification();
			$notification_obj->set_props(
				array(
					'message'     => $message,
					'vendor_id'   => $vendor_obj->get_id(),
					'source_id'   => $comment_id,
					'source_from' => 'store_review',
					'to'          => 'vendor',
				)
			);
			$notification_obj->save();
		}

		/**
		 * Vendor Register Notification.
		 *
		 * @since 1.0.0
		 * @param MVR_Vendor $vendor_obj Vendor Object.
		 */
		public static function vendor_register_notification( $vendor_obj ) {
			if ( ! mvr_is_vendor( $vendor_obj ) ) {
				return;
			}

			/* translators: %s:$vendor_id */
			$message          = sprintf( esc_html__( 'New Vendor Registration : %s', 'multi-vendor-marketplace' ), '<a href="' . esc_url( $vendor_obj->get_admin_edit_url() ) . '">' . esc_attr( $vendor_obj->get_name() ) . '</a>' );
			$notification_obj = new MVR_Notification();
			$notification_obj->set_props(
				array(
					'message'     => $message,
					'source_id'   => $vendor_obj->get_id(),
					'source_from' => 'register_vendor',
					'to'          => 'admin',
				)
			);
			$notification_obj->save();
		}

		/**
		 * Vendor Register Notification.
		 *
		 * @since 1.0.0
		 * @param MVR_Vendor $vendor_obj Vendor Object.
		 */
		public static function become_vendor_notification( $vendor_obj ) {
			if ( ! mvr_is_vendor( $vendor_obj ) ) {
				return;
			}

			/* translators: $vendor_id */
			$message          = sprintf( esc_html__( 'User Becomes a Vendor : %s', 'multi-vendor-marketplace' ), '<a href="' . esc_url( $vendor_obj->get_admin_edit_url() ) . '">' . esc_attr( $vendor_obj->get_name() ) . '</a>' );
			$notification_obj = new MVR_Notification();
			$notification_obj->set_props(
				array(
					'message'     => $message,
					'source_id'   => $vendor_obj->get_id(),
					'source_from' => 'become_vendor',
					'to'          => 'admin',
				)
			);
			$notification_obj->save();
		}

		/**
		 * New Withdraw Request Notification.
		 *
		 * @since 1.0.0
		 * @param MVR_Withdraw $withdraw_obj Withdraw Object.
		 * @param MVR_Vendor   $vendor_obj Vendor Object.
		 */
		public static function create_withdraw_request_notification( $withdraw_obj, $vendor_obj ) {
			if ( ! mvr_is_withdraw( $withdraw_obj ) || ! mvr_is_vendor( $vendor_obj ) ) {
				return;
			}

			/* translators: %1$s: vendor_id, %2$s: withdraw_id */
			$message          = sprintf( esc_html__( 'Vendor %1$s submitted a withdrawal request %2$s', 'multi-vendor-marketplace' ), '<a href="' . esc_url( $vendor_obj->get_admin_edit_url() ) . '">' . esc_attr( $vendor_obj->get_name() ) . '</a>', '<a href="' . esc_url( $withdraw_obj->get_admin_view_url() ) . '">#' . esc_attr( $withdraw_obj->get_id() ) . '</a>' );
			$notification_obj = new MVR_Notification();
			$notification_obj->set_props(
				array(
					'message'     => $message,
					'vendor_id'   => $vendor_obj->get_id(),
					'source_id'   => $withdraw_obj->get_id(),
					'source_from' => 'withdraw_request',
					'to'          => 'admin',
				)
			);
			$notification_obj->save();
		}

		/**
		 * Store enquiry Notification.
		 *
		 * @since 1.0.0
		 * @param MVR_Withdraw $enquiry_obj Enquiry Object.
		 * @param MVR_Vendor   $vendor_obj Vendor Object.
		 */
		public static function create_store_enquiry_notification( $enquiry_obj, $vendor_obj ) {
			if ( ! mvr_is_enquiry( $enquiry_obj ) || ! mvr_is_vendor( $vendor_obj ) ) {
				return;
			}

			/* translators: %1$s: enquiry id, %2$s: customer email */
			$message          = sprintf( esc_html__( 'New enquiry (%1$s) received form customer %2$s', 'multi-vendor-marketplace' ), '<a href="' . esc_url( mvr_get_dashboard_endpoint_url( 'mvr-enquiry', $enquiry_obj->get_id() ) ) . '">#' . esc_attr( $enquiry_obj->get_id() ) . '</a>', esc_attr( $enquiry_obj->get_customer_email() ) );
			$notification_obj = new MVR_Notification();
			$notification_obj->set_props(
				array(
					'message'     => $message,
					'vendor_id'   => $vendor_obj->get_id(),
					'source_id'   => $enquiry_obj->get_id(),
					'source_from' => 'store_enquiry',
					'to'          => 'vendor',
				)
			);
			$notification_obj->save();

			/* translators: %1$s: enquiry id, %2$s: customer email */
			$message          = sprintf( esc_html__( 'New enquiry (%1$s) received form customer %2$s', 'multi-vendor-marketplace' ), '<a href="' . esc_url( $enquiry_obj->get_admin_edit_url() ) . '">#' . esc_attr( $enquiry_obj->get_id() ) . '</a>', esc_attr( $enquiry_obj->get_customer_email() ) );
			$notification_obj = new MVR_Notification();
			$notification_obj->set_props(
				array(
					'message'     => $message,
					'vendor_id'   => $vendor_obj->get_id(),
					'source_id'   => $enquiry_obj->get_id(),
					'source_from' => 'store_enquiry',
					'to'          => 'admin',
				)
			);
			$notification_obj->save();
		}

		/**
		 * Vendor New Product Creation Notification
		 *
		 * @since 1.0.0
		 * @param WC_Product $product_obj Product Object.
		 * @param MVR_Vendor $vendor_obj Vendor Object.
		 */
		public static function vendor_new_product_submit_notification( $product_obj, $vendor_obj ) {
			if ( ! is_a( $product_obj, 'WC_Product' ) || ! mvr_is_vendor( $vendor_obj ) ) {
				return;
			}

			/* translators: %1$s: Product, %2$s: Vendor */
			$message          = sprintf( esc_html__( 'New Product(%1$s) Created from Vendor %2$s', 'multi-vendor-marketplace' ), '<a href="' . esc_url( admin_url( 'post.php?post=' . absint( $product_obj->get_id() ) . '&action=edit' ) ) . '">#' . esc_attr( $product_obj->get_id() ) . '</a>', '<a href="' . esc_url( $vendor_obj->get_admin_edit_url() ) . '">' . esc_attr( $vendor_obj->get_name() ) . '</a>' );
			$notification_obj = new MVR_Notification();
			$notification_obj->set_props(
				array(
					'message'     => $message,
					'vendor_id'   => $vendor_obj->get_id(),
					'source_id'   => $product_obj->get_id(),
					'source_from' => 'new_product',
					'to'          => 'admin',
				)
			);
			$notification_obj->save();
		}

		/**
		 * Vendor New Coupon Creation Notification
		 *
		 * @since 1.0.0
		 * @param WC_Coupon  $coupon_obj Coupon Object.
		 * @param MVR_Vendor $vendor_obj Vendor Object.
		 */
		public static function vendor_new_coupon_submit_notification( $coupon_obj, $vendor_obj ) {
			if ( ! is_a( $coupon_obj, 'WC_Coupon' ) || ! mvr_is_vendor( $vendor_obj ) ) {
				return;
			}

			/* translators: %1$s: Coupon, %2$s: Vendor */
			$message          = sprintf( esc_html__( 'New Coupon(%1$s) Created from Vendor %2$s', 'multi-vendor-marketplace' ), '<a href="' . esc_url( admin_url( 'post.php?post=' . absint( $coupon_obj->get_id() ) . '&action=edit' ) ) . '">#' . esc_attr( $coupon_obj->get_id() ) . '</a>', '<a href="' . esc_url( $vendor_obj->get_admin_edit_url() ) . '">' . esc_attr( $vendor_obj->get_name() ) . '</a>' );
			$notification_obj = new MVR_Notification();
			$notification_obj->set_props(
				array(
					'message'     => $message,
					'vendor_id'   => $vendor_obj->get_id(),
					'source_id'   => $coupon_obj->get_id(),
					'source_from' => 'new_coupon',
					'to'          => 'admin',
				)
			);
			$notification_obj->save();
		}

		/**
		 * Vendor New Coupon Creation Notification
		 *
		 * @since 1.0.0
		 * @param MVR_Order  $mvr_order_id Multi Vendor Order ID.
		 * @param MVR_Vendor $vendor_obj Vendor Object.
		 */
		public static function new_order_notification( $mvr_order_id, $vendor_obj ) {
			$mvr_order_obj = mvr_get_order( $mvr_order_id );

			if ( ! mvr_is_order( $mvr_order_obj ) ) {
				return;
			}

			$order_obj = wc_get_order( $mvr_order_obj->get_order_id() );

			if ( ! is_a( $order_obj, 'WC_Order' ) ) {
				return;
			}

			/* translators: %s: Order */
			$message          = sprintf( esc_html__( 'You have received an Order %s', 'multi-vendor-marketplace' ), '<a href="' . esc_url( admin_url( 'post.php?post=' . absint( $order_obj->get_id() ) . '&action=edit' ) ) . '">#' . esc_attr( $order_obj->get_id() ) . '</a>' );
			$notification_obj = new MVR_Notification();
			$notification_obj->set_props(
				array(
					'message'     => $message,
					'source_id'   => $order_obj->get_id(),
					'source_from' => 'new_order',
					'to'          => 'admin',
				)
			);
			$notification_obj->save();

			if ( mvr_is_vendor( $vendor_obj ) ) {
				/* translators: %s: Order */
				$message          = sprintf( esc_html__( 'You have received an Order %s', 'multi-vendor-marketplace' ), '<a href="' . esc_url( mvr_get_dashboard_endpoint_url( 'mvr-view-order', $order_obj->get_id() ) ) . '">#' . esc_attr( $order_obj->get_id() ) . '</a>' );
				$notification_obj = new MVR_Notification();
				$notification_obj->set_props(
					array(
						'message'     => $message,
						'vendor_id'   => $vendor_obj->get_id(),
						'source_id'   => $order_obj->get_id(),
						'source_from' => 'new_order',
						'to'          => 'vendor',
					)
				);

				$notification_obj->save();
			}
		}

		/**
		 * Update Terms and Conditions Notifications.
		 *
		 * @since 1.0.0
		 * @param Integer $page_id Page ID.
		 */
		public static function update_terms_conditions_notification( $page_id ) {
			if ( 'yes' !== get_option( 'mvr_settings_vendor_mandatory_terms_condition', 'no' ) ) {
				return;
			}

			$terms_page_id   = (int) get_option( 'mvr_settings_vendor_tac_page' );
			$privacy_page_id = (int) get_option( 'mvr_settings_vendor_privacy_policy_page' );

			if ( $page_id === $terms_page_id || $privacy_page_id === $page_id ) {
				$vendors = mvr_get_vendors(
					array(
						'status' => 'active',
						'fields' => 'ids',
					)
				);

				foreach ( $vendors->vendors as $vendor_id ) {
					$vendor_obj = mvr_get_vendor( $vendor_id );

					if ( ! mvr_is_vendor( $vendor_obj ) ) {
						continue;
					}

					if ( $page_id === $terms_page_id ) {
						/* translators: %s: Terms & Condition URL */
						$message = sprintf( esc_html__( '%s updated by admin please check it', 'multi-vendor-marketplace' ), '<a href="' . esc_url( get_permalink( $terms_page_id ) ) . '">' . esc_html__( 'Terms & Conditions', 'multi-vendor-marketplace' ) . '</a>' );
					} else {
						/* translators: %s: Privacy Policy */
						$message = sprintf( esc_html__( '%s updated by admin please check it.', 'multi-vendor-marketplace' ), '<a href="' . esc_url( get_permalink( $privacy_page_id ) ) . '">' . esc_html__( 'Privacy Policy', 'multi-vendor-marketplace' ) . '</a>' );
					}

					$notification_obj = new MVR_Notification();
					$notification_obj->set_props(
						array(
							'message'     => $message,
							'vendor_id'   => $vendor_obj->get_id(),
							'source_id'   => $page_id,
							'source_from' => 'admin',
							'to'          => 'vendor',
						)
					);
					$notification_obj->save();
				}
			}
		}
	}

	MVR_Notification_Manager::init();
}
