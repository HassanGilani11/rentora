<?php
/**
 * Custom Post Type.
 *
 * @package Register Post Type
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Post_Types' ) ) {

	/**
	 * Main Class.
	 */
	class MVR_Post_Types {
		/**
		 * Vendor Constant
		 */
		const MVR_VENDOR = 'mvr_vendor';

		/**
		 * Staff Constant
		 */
		const MVR_STAFF = 'mvr_staff';

		/**
		 * Payout Batch Constant
		 */
		const MVR_PAYOUT_BATCH = 'mvr_payout_batch';

		/**
		 * Active Constant
		 */
		const MVR_ACTIVE = 'mvr-active';

		/**
		 * Inactive Constant
		 */
		const MVR_INACTIVE = 'mvr-inactive';

		/**
		 * Pending Constant
		 */
		const MVR_PENDING = 'mvr-pending';

		/**
		 * Progress Constant
		 */
		const MVR_PROGRESS = 'mvr-progress';

		/**
		 * Processing Constant
		 */
		const MVR_PROCESSING = 'mvr-processing';

		/**
		 * Completed Constant
		 */
		const MVR_COMPLETED = 'mvr-completed';

		/**
		 * Success Constant
		 */
		const MVR_SUCCESS = 'mvr-success';

		/**
		 * Failed Constant
		 */
		const MVR_FAILED = 'mvr-failed';

		/**
		 * Reject Constant
		 */
		const MVR_REJECT = 'mvr-reject';

		/**
		 * Paid Constant
		 */
		const MVR_PAID = 'mvr-paid';

		/**
		 * Class initialization.
		 *
		 * @since 1.0.0
		 */
		public static function init() {
			add_action( 'init', array( __CLASS__, 'register_post_types' ), 5 );
			add_action( 'init', array( __CLASS__, 'register_post_status' ), 5 );
		}

		/**
		 * Register Custom Post types.
		 *
		 * @version 1.0.0
		 */
		public static function register_post_types() {
			if ( ! is_blog_installed() ) {
				return;
			}

			$custom_post_types = array(
				self::MVR_VENDOR       => array( __CLASS__, 'vendor_post_type_args' ),
				self::MVR_STAFF        => array( __CLASS__, 'staff_post_type_args' ),
				self::MVR_PAYOUT_BATCH => array( __CLASS__, 'payout_batch_post_type_args' ),
			);

			/**
			 * Add custom post types.
			 *
			 * @since 1.0.0
			 */
			$custom_post_types = apply_filters( 'mvr_add_custom_post_types', $custom_post_types );

			// return if no post type to register.
			if ( ! mvr_check_is_array( $custom_post_types ) ) {
				return;
			}

			foreach ( $custom_post_types as $post_type => $args_function ) {
				$args = array();

				if ( $args_function ) {
					$args = call_user_func_array( $args_function, $args );
				}

				// Register custom post type.
				register_post_type( $post_type, $args );
			}
		}

		/**
		 * Prepare Vendor Post type arguments.
		 *
		 * @since 1.0.0
		 */
		public static function vendor_post_type_args() {
			$supports = array( 'title', 'editor', 'excerpt', 'thumbnail', 'comments' );

			/**
			 * Vendor post type args.
			 *
			 * @since 1.0.0
			 */
			return apply_filters(
				'mvr_vendor_post_type_args',
				array(
					'labels'              => array(
						'name'                  => esc_html__( 'Vendors', 'multi-vendor-marketplace' ),
						'singular_name'         => esc_html__( 'Vendor', 'multi-vendor-marketplace' ),
						'menu_name'             => esc_html__( 'Vendors', 'multi-vendor-marketplace' ),
						'add_new'               => esc_html__( 'Add new Vendor', 'multi-vendor-marketplace' ),
						'add_new_item'          => esc_html__( 'Add new Vendor', 'multi-vendor-marketplace' ),
						'new_item'              => esc_html__( 'New Vendor', 'multi-vendor-marketplace' ),
						'edit_item'             => esc_html__( 'Edit Vendor', 'multi-vendor-marketplace' ),
						'view_item'             => esc_html__( 'View Vendor', 'multi-vendor-marketplace' ),
						'featured_image'        => esc_html__( 'Store Logo', 'multi-vendor-marketplace' ),
						'set_featured_image'    => esc_html__( 'Set Store Logo', 'multi-vendor-marketplace' ),
						'remove_featured_image' => esc_html__( 'Remove Store Logo', 'multi-vendor-marketplace' ),
						'use_featured_image'    => esc_html__( 'Use as Store Logo', 'multi-vendor-marketplace' ),
						'all_items'             => esc_html__( 'All Vendors', 'multi-vendor-marketplace' ),
						'search_items'          => esc_html__( 'Search Vendors', 'multi-vendor-marketplace' ),
						'not_found'             => esc_html__( 'No Vendor Found.', 'multi-vendor-marketplace' ),
						'not_found_in_trash'    => esc_html__( 'No Vendor Found in Trash.', 'multi-vendor-marketplace' ),
					),
					'description'         => esc_html__( 'This is where store vendors are stored.', 'multi-vendor-marketplace' ),
					'public'              => false,
					'show_ui'             => true,
					'capability_type'     => 'post',
					'publicly_queryable'  => false,
					'exclude_from_search' => true,
					'show_in_menu'        => false,
					'hierarchical'        => false,
					'show_in_nav_menus'   => false,
					'query_var'           => false,
					'supports'            => $supports,
					'has_archive'         => false,
					'map_meta_cap'        => true,
				)
			);
		}

		/**
		 * Prepare Staff Post type arguments.
		 *
		 * @since 1.0.0
		 */
		public static function staff_post_type_args() {
			/**
			 * Staff post type args.
			 *
			 * @since 1.0.0
			 */
			return apply_filters(
				'mvr_staff_post_type_args',
				array(
					'labels'              => array(
						'name'               => esc_html__( 'Staff', 'multi-vendor-marketplace' ),
						'singular_name'      => esc_html__( 'Staff', 'multi-vendor-marketplace' ),
						'menu_name'          => esc_html__( 'Staff', 'multi-vendor-marketplace' ),
						'add_new'            => esc_html__( 'Add new Staff', 'multi-vendor-marketplace' ),
						'add_new_item'       => esc_html__( 'Add new Staff', 'multi-vendor-marketplace' ),
						'new_item'           => esc_html__( 'New Staff', 'multi-vendor-marketplace' ),
						'edit_item'          => esc_html__( 'Edit Staff', 'multi-vendor-marketplace' ),
						'view_item'          => esc_html__( 'View Staff', 'multi-vendor-marketplace' ),
						'all_items'          => esc_html__( 'All Staff', 'multi-vendor-marketplace' ),
						'search_items'       => esc_html__( 'Search Staff', 'multi-vendor-marketplace' ),
						'not_found'          => esc_html__( 'No Staff Found.', 'multi-vendor-marketplace' ),
						'not_found_in_trash' => esc_html__( 'No Staff Found in Trash.', 'multi-vendor-marketplace' ),
					),
					'public'              => false,
					'hierarchical'        => false,
					'supports'            => false,
					'capability_type'     => 'post',
					'show_in_menu'        => false,
					'show_ui'             => true,
					'rewrite'             => false,
					'exclude_from_search' => true,
				)
			);
		}

		/**
		 * Prepare Payout Batch Post type arguments.
		 *
		 * @since 1.0.0
		 */
		public static function payout_batch_post_type_args() {
			/**
			 * Staff post type args.
			 *
			 * @since 1.0.0
			 */
			return apply_filters(
				'mvr_payout_batch_post_type_args',
				array(
					'labels'              => array(
						'name'               => esc_html__( 'Payout Batches', 'multi-vendor-marketplace' ),
						'singular_name'      => esc_html__( 'Payout Batch', 'multi-vendor-marketplace' ),
						'menu_name'          => esc_html__( 'Payout Batch', 'multi-vendor-marketplace' ),
						'add_new'            => esc_html__( 'Add new payout batch', 'multi-vendor-marketplace' ),
						'add_new_item'       => esc_html__( 'Add new payout batch', 'multi-vendor-marketplace' ),
						'new_item'           => esc_html__( 'New payout batch', 'multi-vendor-marketplace' ),
						'edit_item'          => esc_html__( 'Edit payout batch', 'multi-vendor-marketplace' ),
						'view_item'          => esc_html__( 'View payout batch', 'multi-vendor-marketplace' ),
						'all_items'          => esc_html__( 'All payout batches', 'multi-vendor-marketplace' ),
						'search_items'       => esc_html__( 'Search payout batch', 'multi-vendor-marketplace' ),
						'not_found'          => esc_html__( 'No Payout Batch Found.', 'multi-vendor-marketplace' ),
						'not_found_in_trash' => esc_html__( 'No Payout Batch Found in Trash.', 'multi-vendor-marketplace' ),
					),
					'public'              => false,
					'hierarchical'        => false,
					'supports'            => false,
					'capability_type'     => 'post',
					'show_in_menu'        => false,
					'show_ui'             => true,
					'rewrite'             => false,
					'exclude_from_search' => true,
					'capabilities'        => array(
						'create_posts' => 'do_not_allow', // Removes support for the "Add New" function.
					),
					'map_meta_cap'        => true,
				)
			);
		}

		/**
		 * Register Custom Post status.
		 *
		 * @version 1.0.0
		 */
		public static function register_post_status() {
			$custom_post_statuses = array(
				self::MVR_ACTIVE     => array( __CLASS__, 'active_args' ),
				self::MVR_INACTIVE   => array( __CLASS__, 'inactive_args' ),
				self::MVR_PENDING    => array( __CLASS__, 'pending_args' ),
				self::MVR_PROGRESS   => array( __CLASS__, 'progress_args' ),
				self::MVR_PROCESSING => array( __CLASS__, 'processing_args' ),
				self::MVR_COMPLETED  => array( __CLASS__, 'completed_args' ),
				self::MVR_SUCCESS    => array( __CLASS__, 'success_args' ),
				self::MVR_FAILED     => array( __CLASS__, 'failed_args' ),
				self::MVR_REJECT     => array( __CLASS__, 'reject_args' ),
				self::MVR_PAID       => array( __CLASS__, 'paid_args' ),
			);

			/**
			 * Add custom post types.
			 *
			 * @since 1.0.0
			 * @param Array $custom_post_statuses Custom post status.
			 */
			$custom_post_statuses = apply_filters( 'mvr_add_custom_post_status', $custom_post_statuses );

			// return if no post type to register.
			if ( ! mvr_check_is_array( $custom_post_statuses ) ) {
				return;
			}

			foreach ( $custom_post_statuses as $post_status => $args_function ) {
				$args = array();

				if ( $args_function ) {
					$args = call_user_func_array( $args_function, $args );
				}

				// Register custom Status type.
				register_post_status( $post_status, $args );
			}
		}

		/**
		 * Active Status Arguments.
		 *
		 * @since 1.0.0
		 */
		public static function active_args() {
			/**
			 * Active post status args.
			 *
			 * @since 1.0.0
			 */
			return apply_filters(
				'mvr_active_post_status_args',
				array(
					'label'                     => _x( 'Active', 'Vendor status', 'multi-vendor-marketplace' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					/* translators: %s: number of orders */
					'label_count'               => _n_noop( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'multi-vendor-marketplace' ),
				)
			);
		}

		/**
		 * Inactive Status Arguments.
		 *
		 * @since 1.0.0
		 */
		public static function inactive_args() {
			/**
			 * Inactive post status args.
			 *
			 * @since 1.0.0
			 */
			return apply_filters(
				'mvr_inactive_post_status_args',
				array(
					'label'                     => _x( 'Inactive', 'Vendor status', 'multi-vendor-marketplace' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					/* translators: %s: number of orders */
					'label_count'               => _n_noop( 'Inactive <span class="count">(%s)</span>', 'Inactive <span class="count">(%s)</span>', 'multi-vendor-marketplace' ),
				)
			);
		}

		/**
		 * Pending Status Arguments.
		 *
		 * @since 1.0.0
		 */
		public static function pending_args() {
			/**
			 * Pending post status args.
			 *
			 * @since 1.0.0
			 */
			return apply_filters(
				'mvr_pending_post_status_args',
				array(
					'label'                     => _x( 'Pending', 'Vendor status', 'multi-vendor-marketplace' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					/* translators: %s: number of orders */
					'label_count'               => _n_noop( 'Pending <span class="count">(%s)</span>', 'Pending <span class="count">(%s)</span>', 'multi-vendor-marketplace' ),
				)
			);
		}

		/**
		 * Progress Status Arguments.
		 *
		 * @since 1.0.0
		 */
		public static function progress_args() {
			/**
			 * Progress post status args.
			 *
			 * @since 1.0.0
			 */
			return apply_filters(
				'mvr_progress_post_status_args',
				array(
					'label'                     => _x( 'In-Progress', 'Vendor status', 'multi-vendor-marketplace' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					/* translators: %s: number of orders */
					'label_count'               => _n_noop( 'In-Progress <span class="count">(%s)</span>', 'In-Progress <span class="count">(%s)</span>', 'multi-vendor-marketplace' ),
				)
			);
		}

		/**
		 * Processing Status Arguments.
		 *
		 * @since 1.0.0
		 */
		public static function processing_args() {
			/**
			 * Processing post status args.
			 *
			 * @since 1.0.0
			 */
			return apply_filters(
				'mvr_processing_post_status_args',
				array(
					'label'                     => _x( 'Processing', 'Status', 'multi-vendor-marketplace' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					/* translators: %s: number of orders */
					'label_count'               => _n_noop( 'Processing <span class="count">(%s)</span>', 'Processing <span class="count">(%s)</span>', 'multi-vendor-marketplace' ),
				)
			);
		}

		/**
		 * Completed Status Arguments.
		 *
		 * @since 1.0.0
		 */
		public static function completed_args() {
			/**
			 * Completed post status args.
			 *
			 * @since 1.0.0
			 */
			return apply_filters(
				'mvr_completed_post_status_args',
				array(
					'label'                     => _x( 'Completed', 'Status', 'multi-vendor-marketplace' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					/* translators: %s: number of orders */
					'label_count'               => _n_noop( 'Completed <span class="count">(%s)</span>', 'Completed <span class="count">(%s)</span>', 'multi-vendor-marketplace' ),
				)
			);
		}

		/**
		 * Success Status Arguments.
		 *
		 * @since 1.0.0
		 */
		public static function success_args() {
			/**
			 * Success post status args.
			 *
			 * @since 1.0.0
			 */
			return apply_filters(
				'mvr_success_post_status_args',
				array(
					'label'                     => _x( 'Success', 'Status', 'multi-vendor-marketplace' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					/* translators: %s: number of orders */
					'label_count'               => _n_noop( 'Success <span class="count">(%s)</span>', 'Success <span class="count">(%s)</span>', 'multi-vendor-marketplace' ),
				)
			);
		}

		/**
		 * Failed Status Arguments.
		 *
		 * @since 1.0.0
		 */
		public static function failed_args() {
			/**
			 * Failed post status args.
			 *
			 * @since 1.0.0
			 */
			return apply_filters(
				'mvr_failed_post_status_args',
				array(
					'label'                     => _x( 'Failed', 'Status', 'multi-vendor-marketplace' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					/* translators: %s: number of orders */
					'label_count'               => _n_noop( 'Failed <span class="count">(%s)</span>', 'Failed <span class="count">(%s)</span>', 'multi-vendor-marketplace' ),
				)
			);
		}

		/**
		 * Reject Status Arguments.
		 *
		 * @since 1.0.0
		 */
		public static function reject_args() {
			/**
			 * Reject post status args.
			 *
			 * @since 1.0.0
			 */
			return apply_filters(
				'mvr_reject_post_status_args',
				array(
					'label'                     => _x( 'Reject', 'Status', 'multi-vendor-marketplace' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					/* translators: %s: number of orders */
					'label_count'               => _n_noop( 'Rejected <span class="count">(%s)</span>', 'Rejected <span class="count">(%s)</span>', 'multi-vendor-marketplace' ),
				)
			);
		}

		/**
		 * Paid Status Arguments.
		 *
		 * @since 1.0.0
		 */
		public static function paid_args() {
			/**
			 * Paid post status args.
			 *
			 * @since 1.0.0
			 */
			return apply_filters(
				'mvr_paid_post_status_args',
				array(
					'label'                     => _x( 'Paid', 'Status', 'multi-vendor-marketplace' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					/* translators: %s: number of orders */
					'label_count'               => _n_noop( 'Paid <span class="count">(%s)</span>', 'Paid <span class="count">(%s)</span>', 'multi-vendor-marketplace' ),
				)
			);
		}
	}

	MVR_Post_Types::init();
}
