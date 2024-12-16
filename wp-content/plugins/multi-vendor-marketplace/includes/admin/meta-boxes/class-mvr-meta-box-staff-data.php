<?php
/**
 * Staff Data
 *
 * Displays the Staff data box, tabbed, with several panels covering capabilities, profile etc.
 *
 * @package  Multi-Vendor\Admin\Meta Boxes
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'MVR_Meta_Box_Staff_Data' ) ) {
	/**
	 * MVR_Meta_Box_Staff_Data Class.
	 */
	class MVR_Meta_Box_Staff_Data {

		/**
		 * Output the metabox.
		 *
		 * @since 1.0.0
		 * @param WP_Post $post Post object.
		 */
		public static function output( $post ) {
			global $post, $staff_obj;

			if ( ! is_object( $staff_obj ) ) {
				$staff_obj = new MVR_Staff( $post->ID );
			}

			$vendor_obj = $staff_obj->get_vendor();

			wp_nonce_field( 'mvr_save_data', 'mvr_save_meta_nonce' );

			include __DIR__ . '/views/html-staff-data-panel.php';
		}

		/**
		 * Show tab content/settings.
		 *
		 * @since 1.0.0
		 */
		public static function output_tabs() {
			global $post, $staff_id, $staff_obj;

			$vendor_obj = $staff_obj->get_vendor();

			include __DIR__ . '/views/staff-tab/html-staff-data-profile.php';
			include __DIR__ . '/views/staff-tab/html-staff-data-capability.php';
		}

		/**
		 * Output the metabox.
		 *
		 * @since 1.0.0
		 * @param WP_Post $post Post object.
		 */
		public static function output_action( $post ) {
			global $post, $staff_obj;

			if ( ! is_object( $staff_obj ) ) {
				$staff_obj = new MVR_Staff( $post->ID );
			}

			include 'views/html-staff-action.php';
		}

		/**
		 * Return array of tabs to show.
		 *
		 * @since 1.0.0
		 * @return Array
		 */
		public static function get_staff_data_tabs() {
			global $post, $staff_obj;

			$args = array(
				'profile' => array(
					'label'    => __( 'Profile', 'multi-vendor-marketplace' ),
					'target'   => 'profile_staff_data',
					'class'    => array(),
					'priority' => 10,
				),
			);

			if ( $staff_obj->allow_capability() ) {
				$args['capabilities'] = array(
					'label'    => __( 'Capabilities', 'multi-vendor-marketplace' ),
					'target'   => 'capabilities_staff_data',
					'class'    => array(),
					'priority' => 20,
				);
			}

			/**
			 * Staff Data Tab.
			 *
			 * @since 1.0.0
			 */
			$tabs = apply_filters( 'mvr_staff_data_tabs', $args );

			// Sort tabs based on priority.
			uasort( $tabs, array( __CLASS__, 'staff_data_tabs_sort' ) );

			return $tabs;
		}

		/**
		 * Callback to sort product data tabs on priority.
		 *
		 * @since 1.0.0
		 * @param Integer $a First item.
		 * @param Integer $b Second item.
		 * @return Boolean
		 */
		private static function staff_data_tabs_sort( $a, $b ) {
			if ( ! isset( $a['priority'], $b['priority'] ) ) {
				return -1;
			}

			if ( $a['priority'] === $b['priority'] ) {
				return 0;
			}

			return $a['priority'] < $b['priority'] ? -1 : 1;
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
			$staff_args = array(
				'vendor_id'                      => isset( $posted['_vendor_id'] ) ? absint( wp_unslash( $posted['_vendor_id'] ) ) : '',
				'enable_product_management'      => isset( $posted['_enable_product_management'] ) ? wp_unslash( $posted['_enable_product_management'] ) : '',
				'product_creation'               => isset( $posted['_product_creation'] ) ? wp_unslash( $posted['_product_creation'] ) : '',
				'product_modification'           => isset( $posted['_product_modification'] ) ? wp_unslash( $posted['_product_modification'] ) : '',
				'published_product_modification' => isset( $posted['_published_product_modification'] ) ? wp_unslash( $posted['_published_product_modification'] ) : '',
				'manage_inventory'               => isset( $posted['_manage_inventory'] ) ? wp_unslash( $posted['_manage_inventory'] ) : '',
				'product_deletion'               => isset( $posted['_product_deletion'] ) ? wp_unslash( $posted['_product_deletion'] ) : '',
				'enable_order_management'        => isset( $posted['_enable_order_management'] ) ? wp_unslash( $posted['_enable_order_management'] ) : '',
				'order_status_modification'      => isset( $posted['_order_status_modification'] ) ? wp_unslash( $posted['_order_status_modification'] ) : '',
				'commission_info_display'        => isset( $posted['_commission_info_display'] ) ? wp_unslash( $posted['_commission_info_display'] ) : '',
				'enable_coupon_management'       => isset( $posted['_enable_coupon_management'] ) ? wp_unslash( $posted['_enable_coupon_management'] ) : '',
				'coupon_creation'                => isset( $posted['_coupon_creation'] ) ? wp_unslash( $posted['_coupon_creation'] ) : '',
				'coupon_modification'            => isset( $posted['_coupon_modification'] ) ? wp_unslash( $posted['_coupon_modification'] ) : '',
				'published_coupon_modification'  => isset( $posted['_published_coupon_modification'] ) ? wp_unslash( $posted['_published_coupon_modification'] ) : '',
				'coupon_deletion'                => isset( $posted['_coupon_deletion'] ) ? wp_unslash( $posted['_coupon_deletion'] ) : '',
				'enable_commission_withdraw'     => isset( $posted['_enable_commission_withdraw'] ) ? wp_unslash( $posted['_enable_commission_withdraw'] ) : '',
				'commission_transaction'         => isset( $posted['_commission_transaction'] ) ? wp_unslash( $posted['_commission_transaction'] ) : '',
				'commission_transaction_info'    => isset( $posted['_commission_transaction_info'] ) ? wp_unslash( $posted['_commission_transaction_info'] ) : '',
			);
			$staff_obj  = new MVR_Staff( $post_id );
			$errors     = $staff_obj->set_props( $staff_args );

			if ( is_wp_error( $errors ) ) {
				MVR_Admin::add_error( $errors->get_error_message() );
			}

			/**
			 * Staff Before Save.
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_admin_staff_before_save', $staff_obj, $staff_args );

			$staff_obj->update_status( wc_clean( wp_unslash( $posted['_status'] ) ) );
			$staff_obj->save();
		}
	}
}
