<?php
/**
 * Capability Manager.
 *
 * @package Multi-Vendor Functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Capability_Manager' ) ) {
	/**
	 * Capability Manager.
	 *
	 * @class MVR_Capability_Manager
	 * @package Class
	 */
	class MVR_Capability_Manager {

		/**
		 * Init MVR_Capability_Manager.
		 *
		 * @since 1.0.0
		 */
		public static function init() {
			add_filter( 'mvr_allow_endpoint', __CLASS__ . '::allow_endpoint', 10, 2 );
		}

		/**
		 * Create Vendor Link.
		 *
		 * @since 1.0.0
		 * @param Boolean $bool Condition.
		 * @param String  $endpoint_id Endpoint ID.
		 * @return Boolean
		 */
		public static function allow_endpoint( $bool, $endpoint_id ) {
			if ( ! mvr_check_user_as_vendor_or_staff() ) {
				return false;
			}

			if ( mvr_check_user_as_staff() ) {
				$staff_obj = mvr_get_current_staff_object();

				if ( ! in_array( $endpoint_id, array( 'mvr-home', 'mvr-products', 'mvr-add-product', 'mvr-edit-product', 'mvr-coupons', 'mvr-add-coupon', 'mvr-edit-coupon', 'mvr-orders', 'mvr-order-commission-info', 'mvr-view-order', 'mvr-update-order-status', 'mvr-logout' ), true ) ) {
					return false;
				}
			}

			$vendor_obj = mvr_get_current_vendor_object();

			if ( ! mvr_is_vendor( $vendor_obj ) ) {
				return false;
			}

			if ( 'active' !== $vendor_obj->get_status() ) {
				if ( ! in_array( $endpoint_id, array( 'mvr-payments', 'mvr-profile', 'mvr-address', 'mvr-payout', 'mvr-logout' ), true ) ) {
					return false;
				}
			}

			if ( 'yes' === get_option( 'mvr_settings_enable_vendor_subscription', 'no' ) ) {
				if ( ! $vendor_obj->has_subscribed() || 'active' !== $vendor_obj->get_subscription_status() ) {
					if ( in_array( $endpoint_id, array( 'mvr-products', 'mvr-add-product', 'mvr-edit-product', 'mvr-coupons', 'mvr-add-coupon', 'mvr-edit-coupon', 'mvr-orders', 'mvr-view-order', 'mvr-notification', 'mvr-enquiry' ), true ) ) {
						return false;
					}
				}
			}

			switch ( $endpoint_id ) {
				case 'mvr-products':
					if ( mvr_check_user_as_staff() ) {
						if ( 'yes' !== $staff_obj->get_enable_product_management() || 'yes' !== $vendor_obj->get_enable_product_management() ) {
							return false;
						}
					} elseif ( 'yes' !== $vendor_obj->get_enable_product_management() ) {
							return false;
					}
					break;
				case 'mvr-add-product':
					if ( mvr_check_user_as_staff() ) {
						if ( 'yes' !== $staff_obj->get_enable_product_management() || 'yes' !== $staff_obj->get_product_creation() || 'yes' !== $vendor_obj->get_enable_product_management() || 'yes' !== $vendor_obj->get_product_creation() ) {
							return false;
						}
					} elseif ( 'yes' !== $vendor_obj->get_enable_product_management() || 'yes' !== $vendor_obj->get_product_creation() ) {
							return false;
					}
					break;
				case 'mvr-edit-product':
					if ( mvr_check_user_as_staff() ) {
						if ( 'yes' !== $staff_obj->get_enable_product_management() || 'yes' !== $staff_obj->get_product_modification() || 'yes' !== $vendor_obj->get_enable_product_management() || 'yes' !== $vendor_obj->get_product_modification() ) {
							return false;
						}
					} elseif ( 'yes' !== $vendor_obj->get_enable_product_management() || 'yes' !== $vendor_obj->get_product_modification() ) {
						return false;
					}
					break;
				case 'mvr-edit-product-publish':
					if ( mvr_check_user_as_staff() ) {
						if ( 'yes' !== $staff_obj->get_enable_product_management() || 'yes' !== $staff_obj->get_published_product_modification() || 'yes' !== $vendor_obj->get_enable_product_management() || 'yes' !== $vendor_obj->get_published_product_modification() ) {
							return false;
						}
					} elseif ( 'yes' !== $vendor_obj->get_enable_product_management() || 'yes' !== $vendor_obj->get_published_product_modification() ) {
							return false;
					}
					break;
				case 'mvr-delete-product':
					if ( mvr_check_user_as_staff() ) {
						if ( 'yes' !== $staff_obj->get_enable_product_management() || 'yes' !== $staff_obj->get_product_deletion() || 'yes' !== $vendor_obj->get_enable_product_management() || 'yes' !== $vendor_obj->get_product_deletion() ) {
							return false;
						}
					} elseif ( 'yes' !== $vendor_obj->get_enable_product_management() || 'yes' !== $vendor_obj->get_product_deletion() ) {
							return false;
					}
					break;
				case 'mvr-orders':
					if ( mvr_check_user_as_staff() ) {
						if ( 'yes' !== $staff_obj->get_enable_order_management() || 'yes' !== $vendor_obj->get_enable_order_management() ) {
							return false;
						}
					} elseif ( 'yes' !== $vendor_obj->get_enable_order_management() ) {
							return false;
					}
					break;
				case 'mvr-update-order-status':
					if ( mvr_check_user_as_staff() ) {
						if ( 'yes' !== $staff_obj->get_enable_order_management() || 'yes' !== $staff_obj->get_order_status_modification() || 'yes' !== $vendor_obj->get_enable_order_management() || 'yes' !== $vendor_obj->get_order_status_modification() ) {
							return false;
						}
					} elseif ( 'yes' !== $vendor_obj->get_enable_order_management() || 'yes' !== $vendor_obj->get_order_status_modification() ) {
						return false;
					}
					break;
				case 'mvr-order-commission-info':
					if ( mvr_check_user_as_staff() ) {
						if ( 'yes' !== $staff_obj->get_enable_order_management() || 'yes' !== $staff_obj->get_commission_info_display() || 'yes' !== $vendor_obj->get_enable_order_management() || 'yes' !== $vendor_obj->get_commission_info_display() ) {
							return false;
						}
					} elseif ( 'yes' !== $vendor_obj->get_enable_order_management() || 'yes' !== $vendor_obj->get_commission_info_display() ) {
						return false;
					}
					break;
				case 'mvr-coupons':
					if ( mvr_check_user_as_staff() ) {
						if ( 'yes' !== $staff_obj->get_enable_coupon_management() || 'yes' !== $vendor_obj->get_enable_coupon_management() ) {
							return false;
						}
					} elseif ( 'yes' !== $vendor_obj->get_enable_coupon_management() ) {
						return false;
					}
					break;
				case 'mvr-add-coupon':
					if ( mvr_check_user_as_staff() ) {
						if ( 'yes' !== $staff_obj->get_enable_coupon_management() || 'yes' !== $staff_obj->get_coupon_creation() || 'yes' !== $vendor_obj->get_enable_coupon_management() || 'yes' !== $vendor_obj->get_coupon_creation() ) {
							return false;
						}
					} elseif ( 'yes' !== $vendor_obj->get_enable_coupon_management() || 'yes' !== $vendor_obj->get_coupon_creation() ) {
						return false;
					}
					break;
				case 'mvr-edit-coupon':
					if ( mvr_check_user_as_staff() ) {
						if ( 'yes' !== $staff_obj->get_enable_coupon_management() || 'yes' !== $staff_obj->get_coupon_modification() || 'yes' !== $vendor_obj->get_enable_coupon_management() || 'yes' !== $vendor_obj->get_coupon_modification() ) {
							return false;
						}
					} elseif ( 'yes' !== $vendor_obj->get_enable_coupon_management() || 'yes' !== $vendor_obj->get_coupon_modification() ) {
							return false;
					}
					break;
				case 'mvr-edit-coupon-publish':
					if ( mvr_check_user_as_staff() ) {
						if ( 'yes' !== $staff_obj->get_enable_coupon_management() || 'yes' !== $staff_obj->get_published_coupon_modification() || 'yes' !== $vendor_obj->get_enable_coupon_management() || 'yes' !== $vendor_obj->get_published_coupon_modification() ) {
							return false;
						}
					} elseif ( 'yes' !== $vendor_obj->get_enable_coupon_management() || 'yes' !== $vendor_obj->get_published_coupon_modification() ) {
							return false;
					}
					break;
				case 'mvr-delete-coupon':
					if ( mvr_check_user_as_staff() ) {
						if ( 'yes' !== $staff_obj->get_enable_coupon_management() || 'yes' !== $staff_obj->get_coupon_deletion() || 'yes' !== $vendor_obj->get_enable_coupon_management() || 'yes' !== $vendor_obj->get_coupon_deletion() ) {
							return false;
						}
					} elseif ( 'yes' !== $vendor_obj->get_enable_coupon_management() || 'yes' !== $vendor_obj->get_coupon_deletion() ) {
							return false;
					}
					break;
				case 'mvr-withdraw':
					if ( mvr_check_user_as_staff() ) {
						return false;
					}

					if ( 'yes' !== get_option( 'mvr_settings_allow_vendor_withdraw_req', 'no' ) ) {
						return false;
					}
					break;
				case 'mvr-duplicate':
					if ( mvr_check_user_as_staff() ) {
						return false;
					}

					if ( 'yes' !== get_option( 'mvr_settings_allow_spmv', 'no' ) ) {
						return false;
					}
					break;
				case 'mvr-staff':
					if ( mvr_check_user_as_staff() ) {
						return false;
					}

					if ( 'yes' !== get_option( 'mvr_settings_allow_vendor_staff', 'no' ) ) {
						return false;
					}
					break;
			}

			return true;
		}
	}

	MVR_Capability_Manager::init();
}
