<?php
/**
 * Init Multi Vendor data exporters.
 *
 * @package Multi-Vendor\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'MVR_Admin_Exporters' ) ) {
	/**
	 * MVR_Admin_Exporters Class.
	 */
	class MVR_Admin_Exporters {

		/**
		 * Array of exporter IDs.
		 *
		 * @var string[]
		 */
		protected $exporters = array();

		/**
		 * Constructor.
		 */
		public function __construct() {
			if ( ! $this->export_allowed() ) {
				return;
			}

			// Register Multi Vendor exporters.
			$this->exporters = array(
				'mvr_withdraw_exporter'        => array(
					'menu'       => 'edit.php?post_type=mvr_vendor',
					'name'       => __( 'Withdraw Export', 'multi-vendor-marketplace' ),
					'capability' => 'export',
					'callback'   => array( $this, 'withdraw_exporter' ),
				),
				'mvr_generate_payout'          => array(
					'menu'       => 'edit.php?post_type=mvr_vendor',
					'name'       => __( 'Generate Payout', 'multi-vendor-marketplace' ),
					'capability' => 'export',
					'callback'   => array( $this, 'generate_payout' ),
				),
				'mvr_generate_withdraw_payout' => array(
					'menu'       => 'edit.php?post_type=mvr_vendor',
					'name'       => __( 'Generate Payout', 'multi-vendor-marketplace' ),
					'capability' => 'export',
					'callback'   => array( $this, 'generate_withdraw_payout' ),
				),
			);

			add_action( 'admin_menu', array( $this, 'add_to_menus' ) );
			add_action( 'admin_head', array( $this, 'hide_from_menus' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
			add_action( 'admin_init', array( $this, 'download_export_file' ) );

			$ajax_events = array(
				'withdraw_export'          => false,
				'generate_vendor_payout'   => false,
				'withdraw_payout_generate' => false,
			);

			foreach ( $ajax_events as $ajax_event => $nopriv ) {
				// For user support.
				add_action( "wp_ajax_mvr_{$ajax_event}", array( $this, $ajax_event ) );

				if ( $nopriv ) {
					// For guest support.
					add_action( "wp_ajax_nopriv_mvr_{$ajax_event}", array( $this, $ajax_event ) );
				}
			}
		}

		/**
		 * Add menu items for our custom exporters.
		 *
		 * @since 1.0.0
		 */
		public function add_to_menus() {
			foreach ( $this->exporters as $id => $exporter ) {
				add_submenu_page( $exporter['menu'], $exporter['name'], $exporter['name'], $exporter['capability'], $id, $exporter['callback'] );
			}
		}

		/**
		 * Hide menu items from view so the pages exist, but the menu items do not.
		 *
		 * @since 1.0.0
		 */
		public function hide_from_menus() {
			global $submenu;

			foreach ( $this->exporters as $id => $exporter ) {
				if ( isset( $submenu[ $exporter['menu'] ] ) ) {
					foreach ( $submenu[ $exporter['menu'] ] as $key => $menu ) {
						if ( $id === $menu[2] ) {
							unset( $submenu[ $exporter['menu'] ][ $key ] );
						}
					}
				}
			}
		}

		/**
		 * Return true if WooCommerce export is allowed for current user, false otherwise.
		 *
		 * @since 1.0.0
		 * @return bool Whether current user can perform export.
		 */
		protected function export_allowed() {
			return current_user_can( 'export' );
		}

		/**
		 * Enqueue scripts.
		 *
		 * @since 1.0.0
		 */
		public function admin_scripts() {
			wp_register_script( 'mvr-withdraw-export', MVR()->plugin_url() . '/assets/js/admin/export/withdraw.js', array( 'jquery' ), MVR_VERSION, true );
			wp_localize_script(
				'mvr-withdraw-export',
				'mvr_withdraw_export_params',
				array(
					'export_nonce' => wp_create_nonce( 'mvr-withdraw-export' ),
				)
			);

			wp_register_script( 'mvr-generate-payout', MVR()->plugin_url() . '/assets/js/admin/export/generate-payout.js', array( 'jquery' ), MVR_VERSION, true );
			wp_localize_script(
				'mvr-generate-payout',
				'mvr_generate_payout_params',
				array(
					'payout_nonce' => wp_create_nonce( 'mvr-generate-payout' ),
				)
			);

			wp_register_script( 'mvr-generate-withdraw-payout', MVR()->plugin_url() . '/assets/js/admin/export/generate-withdraw-payout.js', array( 'jquery' ), MVR_VERSION, true );
			wp_localize_script(
				'mvr-generate-withdraw-payout',
				'mvr_generate_withdraw_payout_params',
				array(
					'payout_nonce'      => wp_create_nonce( 'mvr-generate-withdraw-payout' ),
					'success_message'   => esc_html__( 'Payout Generated successfully for the selected Data', 'multi-vendor-marketplace' ),
					'withdraw_page_url' => mvr_get_withdraw_page_url(),
				)
			);
		}

		/**
		 * Export page UI.
		 *
		 * @since 1.0.0
		 */
		public function withdraw_exporter() {
			include_once MVR_ABSPATH . 'includes/export/class-mvr-withdraw-csv-exporter.php';
			include_once __DIR__ . '/views/html-admin-page-withdraw-export.php';
		}

		/**
		 * Payout page UI.
		 *
		 * @since 1.0.0
		 */
		public function generate_payout() {
			include_once MVR_ABSPATH . 'includes/export/class-mvr-vendor-generate-payout.php';
			include_once __DIR__ . '/views/html-admin-page-generate-payout.php';
		}

		/**
		 * Payout page UI.
		 *
		 * @since 1.0.0
		 */
		public function generate_withdraw_payout() {
			include_once MVR_ABSPATH . 'includes/export/class-mvr-withdraw-generate-payout.php';
			include_once __DIR__ . '/views/html-admin-page-generate-withdraw-payout.php';
		}

		/**
		 * Serve the generated file.
		 *
		 * @since 1.0.0
		 */
		public function download_export_file() {
			$nonce  = isset( $_GET['nonce'] ) ? sanitize_key( wp_unslash( $_GET['nonce'] ) ) : '';
			$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';

			if ( 'download_withdraw_csv' === $action ) {
				if ( ! wp_verify_nonce( $nonce, 'mvr-withdraw-csv' ) ) {
					return;
				}

				include_once MVR_ABSPATH . 'includes/export/class-mvr-withdraw-csv-exporter.php';

				$exporter  = new MVR_Withdraw_CSV_Exporter();
				$file_name = isset( $_GET['filename'] ) ? sanitize_text_field( wp_unslash( $_GET['filename'] ) ) : '';

				if ( ! empty( $file_name ) ) {
					$exporter->set_filename( $file_name );
				}

				$exporter->export();
			} elseif ( 'generate_payout' === $action ) {
				if ( ! wp_verify_nonce( $nonce, 'mvr-generate-payout' ) ) {
					return;
				}

				include_once MVR_ABSPATH . 'includes/export/class-mvr-vendor-generate-payout.php';

				$payout_generator = new MVR_Vendor_Generate_Payout();
				$payout_generator->generate_payout();
			} elseif ( 'generate_withdraw_payout' === $action ) {
				if ( ! wp_verify_nonce( $nonce, 'mvr-generate-withdraw-payout' ) ) {
					return;
				}

				include_once MVR_ABSPATH . 'includes/export/class-mvr-withdraw-generate-payout.php';

				$payout_generator = new MVR_Withdraw_Generate_Payout();
				$payout_generator->generate_withdraw_payout();
			}
		}

		/**
		 * AJAX callback for doing the actual export to the CSV file.
		 *
		 * @since 1.0.0
		 */
		public function withdraw_export() {
			check_ajax_referer( 'mvr-withdraw-export', 'security' );

			if ( ! $this->export_allowed() ) {
				wp_send_json_error( array( 'message' => __( 'Insufficient privileges to export withdraw.', 'multi-vendor-marketplace' ) ) );
			}

			include_once MVR_ABSPATH . 'includes/export/class-mvr-withdraw-csv-exporter.php';

			$exporter = new MVR_Withdraw_CSV_Exporter();
			$post     = $_POST;
			$columns  = isset( $post['columns'] ) ? wc_clean( wp_unslash( $post['columns'] ) ) : '';

			if ( ! empty( $columns ) ) {
				$exporter->set_column_names( $columns );
			}

			$selected_columns = isset( $post['selected_columns'] ) ? wc_clean( wp_unslash( $post['selected_columns'] ) ) : '';

			if ( ! empty( $selected_columns ) ) {
				$exporter->set_columns_to_export( $selected_columns );
			}

			$payment_types = isset( $post['export_payment_types'] ) ? wc_clean( wp_unslash( $post['export_payment_types'] ) ) : '';

			if ( ! empty( $payment_types ) ) {
				$exporter->set_payment_types_to_export( $payment_types );
			}

			$statuses = isset( $post['export_statuses'] ) ? wc_clean( wp_unslash( $post['export_statuses'] ) ) : '';

			if ( ! empty( $statuses ) ) {
				$exporter->set_statuses_to_export( $statuses );
			}

			$file_name = isset( $post['file_name'] ) ? sanitize_text_field( wp_unslash( $post['file_name'] ) ) : '';

			if ( ! empty( $file_name ) ) {
				$exporter->set_filename( $file_name );
			}

			$step = isset( $post['step'] ) ? absint( $post['step'] ) : 1;

			$exporter->set_page( $step );
			$exporter->generate_file();

			/**
			 * Withdraw Export Ajax Query
			 *
			 * @since 1.0.0
			 */
			$query_args = apply_filters(
				'mvr_get_withdraw_export_ajax_query_args',
				array(
					'nonce'    => wp_create_nonce( 'mvr-withdraw-csv' ),
					'action'   => 'download_withdraw_csv',
					'filename' => $exporter->get_filename(),
				)
			);

			if ( 100 === $exporter->get_percent_complete() ) {
				wp_send_json_success(
					array(
						'step'       => 'done',
						'percentage' => 100,
						'url'        => add_query_arg( $query_args, admin_url( 'edit.php?post_type=mvr_vendor&page=mvr_withdraw_exporter' ) ),
					)
				);
			} else {
				wp_send_json_success(
					array(
						'step'       => ++$step,
						'percentage' => $exporter->get_percent_complete(),
						'columns'    => $exporter->get_column_names(),
					)
				);
			}
		}

		/**
		 * AJAX callback for doing the actual export to the CSV file.
		 *
		 * @since 1.0.0
		 */
		public function generate_vendor_payout() {
			check_ajax_referer( 'mvr-generate-payout', 'security' );

			if ( ! $this->export_allowed() ) {
				wp_send_json_error( array( 'message' => __( 'Insufficient privileges to payout.', 'multi-vendor-marketplace' ) ) );
			}

			include_once MVR_ABSPATH . 'includes/export/class-mvr-vendor-generate-payout.php';

			$payout_generator = new MVR_Vendor_Generate_Payout();
			$post             = $_POST;
			$payment_types    = isset( $post['export_payment_types'] ) ? wc_clean( wp_unslash( $post['export_payment_types'] ) ) : '';

			if ( ! empty( $payment_types ) ) {
				$payout_generator->set_payment_types_to_payout( $payment_types );
			}

			$vendors = isset( $post['payout_vendors'] ) ? wc_clean( wp_unslash( $post['payout_vendors'] ) ) : '';

			if ( ! empty( $vendors ) ) {
				$payout_generator->set_vendors_to_payout( $vendors );
			}

			$step = isset( $post['step'] ) ? absint( $post['step'] ) : 1;

			$payout_generator->set_page( $step );
			$payout_generator->generate_payout();

			/**
			 * Generate Payout Ajax Query
			 *
			 * @since 1.0.0
			 */
			$query_args = apply_filters(
				'mvr_get_generate_payout_ajax_query_args',
				array(
					'nonce'  => wp_create_nonce( 'mvr-generate-payout' ),
					'action' => 'generate_payout',
				)
			);

			if ( 100 === $payout_generator->get_percent_complete() ) {
				wp_send_json_success(
					array(
						'step'       => 'done',
						'percentage' => 100,
						'url'        => add_query_arg( $query_args, admin_url( 'edit.php?post_type=mvr_vendor&page=mvr_generate_payout' ) ),
					)
				);
			} else {
				wp_send_json_success(
					array(
						'step'       => ++$step,
						'percentage' => $payout_generator->get_percent_complete(),
					)
				);
			}
		}

		/**
		 * AJAX callback for doing the actual export to the CSV file.
		 *
		 * @since 1.0.0
		 */
		public function withdraw_payout_generate() {
			check_ajax_referer( 'mvr-generate-withdraw-payout', 'security' );

			if ( ! $this->export_allowed() ) {
				wp_send_json_error( array( 'message' => __( 'Insufficient privileges to export withdraw.', 'multi-vendor-marketplace' ) ) );
			}

			include_once MVR_ABSPATH . 'includes/export/class-mvr-withdraw-generate-payout.php';

			$payout_generator = new MVR_Withdraw_Generate_Payout();
			$post             = $_POST;
			$payment_type     = isset( $post['payment_type'] ) ? wc_clean( wp_unslash( $post['payment_type'] ) ) : '';

			if ( ! empty( $payment_type ) ) {
				$payout_generator->set_payment_type_to_payout( $payment_type );
			}

			$vendor_type = isset( $post['vendor_selection'] ) ? wc_clean( wp_unslash( $post['vendor_selection'] ) ) : '1';

			if ( '2' === $vendor_type ) {
				$selected_vendors = isset( $post['selected_vendors'] ) ? wc_clean( wp_unslash( $post['selected_vendors'] ) ) : array();

				if ( ! empty( $selected_vendors ) ) {
					$payout_generator->set_vendors_to_payout( $selected_vendors );
				}
			} elseif ( '3' === $vendor_type ) {
				$excluded_vendors = isset( $post['excluded_vendors'] ) ? wc_clean( wp_unslash( $post['excluded_vendors'] ) ) : array();

				if ( ! empty( $excluded_vendors ) ) {
					$payout_generator->set_exclude_vendors_to_payout( $excluded_vendors );
				}
			}

			$from_date = isset( $post['from_date'] ) ? wc_clean( wp_unslash( $post['from_date'] ) ) : '';

			if ( ! empty( $from_date ) ) {
				$payout_generator->set_from_date_to_payout( $from_date );
			}

			$to_date = isset( $post['to_date'] ) ? wc_clean( wp_unslash( $post['to_date'] ) ) : '';

			if ( ! empty( $to_date ) ) {
				$payout_generator->set_to_date_to_payout( $to_date );
			}

			$status = isset( $post['payout_status'] ) ? wc_clean( wp_unslash( $post['payout_status'] ) ) : 'pending';

			if ( ! empty( $status ) ) {
				$payout_generator->set_status_to_payout( $status );
			}

			$step = isset( $post['step'] ) ? absint( $post['step'] ) : 1;
			$payout_generator->set_page( $step );
			$payout_generator->generate_withdraw_payout();

			/**
			 * Generate Payout Ajax Query
			 *
			 * @since 1.0.0
			 */
			$query_args = apply_filters(
				'mvr_get_generate_withdraw_payout_ajax_query_args',
				array(
					'nonce'  => wp_create_nonce( 'mvr-generate-payout' ),
					'action' => 'generate_withdraw_payout',
				)
			);

			if ( 100 === $payout_generator->get_percent_complete() ) {
				wp_send_json_success(
					array(
						'step'       => 'done',
						'percentage' => 100,
						'url'        => add_query_arg( $query_args, admin_url( 'edit.php?post_type=mvr_vendor&page=mvr_generate_withdraw_payout' ) ),
					)
				);
			} else {
				wp_send_json_success(
					array(
						'step'       => ++$step,
						'percentage' => $payout_generator->get_percent_complete(),
					)
				);
			}
		}

		/**
		 * Gets the product types that can be exported.
		 *
		 * @since 1.0.0
		 * @return Array The product types keys and labels.
		 */
		public static function get_vendor_selection() {
			/**
			 * Allow third-parties to filter the exportable withdraw payment types.
			 *
			 * @since 1.0.0
			 */
			return apply_filters(
				'mvr_exporter_withdraw_vendor_selection',
				array(
					'1' => esc_html__( 'All Vendors', 'multi-vendor-marketplace' ),
					'2' => esc_html__( 'Include Vendors', 'multi-vendor-marketplace' ),
					'3' => esc_html__( 'Exclude Vendors', 'multi-vendor-marketplace' ),
				)
			);
		}

		/**
		 * Gets the product types that can be exported.
		 *
		 * @since 1.0.0
		 * @return Array The product types keys and labels.
		 */
		public static function get_payment_methods() {
			/**
			 * Allow third-parties to filter the exportable withdraw payment types.
			 *
			 * @since 1.0.0
			 */
			return apply_filters( 'mvr_exporter_withdraw_payment_methods', mvr_get_withdraw_payment_type_options() );
		}

		/**
		 * Gets the product types that can be exported.
		 *
		 * @since 1.0.0
		 * @return Array The product types keys and labels.
		 */
		public static function get_withdraw_statuses() {
			/**
			 * Allow third-parties to filter the exportable withdraw statuses.
			 *
			 * @since 1.0.0
			 */
			return apply_filters( 'mvr_exporter_withdraw_statuses', mvr_get_withdraw_statuses() );
		}
	}

	new MVR_Admin_Exporters();
}
