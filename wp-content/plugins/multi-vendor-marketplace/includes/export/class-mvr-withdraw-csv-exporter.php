<?php
/**
 * Handles Withdraw CSV export.
 *
 * @package Multi-Vendor\Export
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Include dependencies.
 */
if ( ! class_exists( 'WC_CSV_Batch_Exporter', false ) ) {
	include_once WC_ABSPATH . 'includes/export/abstract-wc-csv-batch-exporter.php';
}

if ( ! class_exists( 'MVR_Withdraw_CSV_Exporter' ) ) {
	/**
	 * MVR_Withdraw_CSV_Exporter Class.
	 */
	class MVR_Withdraw_CSV_Exporter extends WC_CSV_Batch_Exporter {

		/**
		 * Type of export used in filter names.
		 *
		 * @var String
		 */
		protected $export_type = 'withdraw';

		/**
		 * Which payment types are being exported.
		 *
		 * @var Array
		 */
		protected $payment_types_to_export = array();

		/**
		 * Which statuses are being exported.
		 *
		 * @var Array
		 */
		protected $statuses_to_export = array();

		/**
		 * Constructor.
		 */
		public function __construct() {
			parent::__construct();

			$this->set_payment_types_to_export( array_keys( MVR_Admin_Exporters::get_payment_methods() ) );
			$this->set_statuses_to_export( array_keys( MVR_Admin_Exporters::get_withdraw_statuses() ) );
		}

		/**
		 * Payment types to export.
		 *
		 * @since 1.0.0
		 * @param Array $payment_types_to_export List of types to export.
		 */
		public function set_payment_types_to_export( $payment_types_to_export ) {
			$this->payment_types_to_export = array_map( 'wc_clean', $payment_types_to_export );
		}

		/**
		 * Payment types to export.
		 *
		 * @since 1.0.0
		 * @param Array $statuses_to_export List of statuses to export.
		 */
		public function set_statuses_to_export( $statuses_to_export ) {
			$this->statuses_to_export = array_map( 'wc_clean', $statuses_to_export );
		}

		/**
		 * Return an array of columns to export.
		 *
		 * @since  1.0.0
		 * @return array
		 */
		public function get_default_column_names() {
			/**
			 * Withdraw Export Columns
			 *
			 * @since 1.0.0
			 * */
			return apply_filters(
				"mvr_export_{$this->export_type}_default_columns",
				array(
					'id'                  => __( 'ID', 'multi-vendor-marketplace' ),
					'vendor'              => __( 'Vendor', 'multi-vendor-marketplace' ),
					'amount'              => __( 'Amount', 'multi-vendor-marketplace' ),
					'charge'              => __( 'Charge', 'multi-vendor-marketplace' ),
					'payment_type'        => __( 'Payment Type', 'multi-vendor-marketplace' ),
					'paypal_email'        => __( 'PayPal Email Address', 'multi-vendor-marketplace' ),
					'bank_name'           => __( 'Bank Name', 'multi-vendor-marketplace' ),
					'bank_account_name'   => __( 'Bank Account Holder', 'multi-vendor-marketplace' ),
					'bank_account_number' => __( 'Bank Account Number', 'multi-vendor-marketplace' ),
					'bank_account_type'   => __( 'Bank Account Type', 'multi-vendor-marketplace' ),
					'iban'                => __( 'IBAN', 'multi-vendor-marketplace' ),
					'swift'               => __( 'SWIFT', 'multi-vendor-marketplace' ),
					'date'                => __( 'Date', 'multi-vendor-marketplace' ),
				)
			);
		}

		/**
		 * Prepare data for export.
		 *
		 * @since 3.1.0
		 */
		public function prepare_data_to_export() {
			$args = array(
				'status'         => $this->statuses_to_export,
				'payment_method' => $this->payment_types_to_export,
			);

			/**
			 * Withdraw Export Query Arguments
			 *
			 * @since 1.0.0
			 * */
			$withdraws = mvr_get_withdraws( apply_filters( "mvr_export_{$this->export_type}_query_args", $args ) );

			$this->total_rows = $withdraws->total_withdraws;
			$this->row_data   = array();

			foreach ( $withdraws->withdraws as $withdraw_obj ) {
				$this->row_data[] = $this->generate_row_data( $withdraw_obj );
			}
		}

		/**
		 * Take a withdraw and generate row data from it for export.
		 *
		 * @since 1.0.0
		 * @param MVR_Withdraw $withdraw_obj Withdraw object.
		 * @return Array
		 */
		protected function generate_row_data( $withdraw_obj ) {
			$columns = $this->get_column_names();
			$row     = array();

			foreach ( $columns as $column_id => $column_name ) {
				$column_id = strstr( $column_id, ':' ) ? current( explode( ':', $column_id ) ) : $column_id;
				$value     = '';

				if ( has_filter( "mvr_export_{$this->export_type}_column_{$column_id}" ) ) {
					/**
					 * Withdraw Export Columns Values
					 *
					 * @since 1.0.0
					 * */
					$value = apply_filters( "mvr_export_{$this->export_type}_column_{$column_id}", '', $withdraw_obj, $column_id );
				} elseif ( is_callable( array( $this, "get_column_value_{$column_id}" ) ) ) {
					// Handle special columns which don't map 1:1 to withdraw data.
					$value = $this->{"get_column_value_{$column_id}"}( $withdraw_obj );
				} elseif ( is_callable( array( $withdraw_obj, "get_{$column_id}" ) ) ) {
					// Default and custom handling.
					$value = $withdraw_obj->{"get_{$column_id}"}( 'edit' );
				}

				$row[ $column_id ] = $value;
			}

			/**
			 * Allow third-party plugins to filter the data in a single row of the exported CSV file.
			 *
			 * @since 1.0.0
			 * @param Array $row Row.
			 * @param MVR_Withdraw $withdraw_obj Withdraw Object..
			 * @param MVR_Withdraw_CSV_Exporter $exporter The instance of the CSV exporter.
			 */
			return apply_filters( 'mvr_withdraw_export_row_data', $row, $withdraw_obj, $this );
		}

		/**
		 * Get Vendor.
		 *
		 * @since 1.0.0
		 * @param MVR_Withdraw $withdraw_obj Withdraw Object.
		 * @return String
		 */
		protected function get_column_value_vendor( $withdraw_obj ) {
			$vendor_obj = $withdraw_obj->get_vendor();

			if ( mvr_is_vendor( $vendor_obj ) ) {
				return $vendor_obj->get_name();
			} else {
				return esc_html__( 'Not found', 'multi-vendor-marketplace' );
			}
		}

		/**
		 * Get formatted Withdraw Amount
		 *
		 * @since 1.0.0
		 * @param MVR_Withdraw $withdraw_obj Withdraw Object.
		 * @return String
		 */
		protected function get_column_value_amount( $withdraw_obj ) {
			return wc_format_localized_price( $withdraw_obj->get_amount() );
		}

		/**
		 * Get formatted Withdrawal Charge
		 *
		 * @since 1.0.0
		 * @param MVR_Withdraw $withdraw_obj Withdraw Object.
		 * @return String
		 */
		protected function get_column_value_charge( $withdraw_obj ) {
			return wc_format_localized_price( $withdraw_obj->get_charge_amount() );
		}

		/**
		 * Get Payment Method.
		 *
		 * @since 1.0.0
		 * @param MVR_Withdraw $withdraw_obj Withdraw Object.
		 * @return String
		 */
		protected function get_column_value_payment_type( $withdraw_obj ) {
			return mvr_payment_method_options( $withdraw_obj->get_payment_method() );
		}

		/**
		 * Get PayPal Email
		 *
		 * @since 1.0.0
		 * @param MVR_Withdraw $withdraw_obj Withdraw Object.
		 * @return String
		 */
		protected function get_column_value_paypal_email( $withdraw_obj ) {
			$vendor_obj = $withdraw_obj->get_vendor();

			if ( ! mvr_is_vendor( $vendor_obj ) ) {
				return esc_html__( 'Not found', 'multi-vendor-marketplace' );
			}

			return '2' === $withdraw_obj->get_payment_method() ? $vendor_obj->get_paypal_email() : '';
		}

		/**
		 * Get Bank Name.
		 *
		 * @since 1.0.0
		 * @param MVR_Withdraw $withdraw_obj Withdraw Object.
		 * @return String
		 */
		protected function get_column_value_bank_account_name( $withdraw_obj ) {
			$vendor_obj = $withdraw_obj->get_vendor();

			if ( ! mvr_is_vendor( $vendor_obj ) ) {
				return esc_html__( 'Not found', 'multi-vendor-marketplace' );
			}

			return '1' === $withdraw_obj->get_payment_method() ? $vendor_obj->get_bank_account_name() : '';
		}

		/**
		 * Get Bank Account Number
		 *
		 * @since 1.0.0
		 * @param MVR_Withdraw $withdraw_obj Withdraw Object.
		 * @return String
		 */
		protected function get_column_value_bank_account_number( $withdraw_obj ) {
			$vendor_obj = $withdraw_obj->get_vendor();

			if ( ! mvr_is_vendor( $vendor_obj ) ) {
				return esc_html__( 'Not found', 'multi-vendor-marketplace' );
			}

			return '1' === $withdraw_obj->get_payment_method() ? $vendor_obj->get_bank_account_number() : '';
		}

		/**
		 * Get Bank Account Types
		 *
		 * @since 1.0.0
		 * @param MVR_Withdraw $withdraw_obj Withdraw Object.
		 * @return String
		 */
		protected function get_column_value_bank_account_type( $withdraw_obj ) {
			$vendor_obj = $withdraw_obj->get_vendor();

			if ( ! mvr_is_vendor( $vendor_obj ) ) {
				return esc_html__( 'Not found', 'multi-vendor-marketplace' );
			}

			return '1' === $withdraw_obj->get_payment_method() ? mvr_bank_account_type_options( $vendor_obj->get_bank_account_type() ) : '';
		}

		/**
		 * Get Bank Name
		 *
		 * @since 1.0.0
		 * @param MVR_Withdraw $withdraw_obj Withdraw Object.
		 * @return String
		 */
		protected function get_column_value_bank_name( $withdraw_obj ) {
			$vendor_obj = $withdraw_obj->get_vendor();

			if ( ! mvr_is_vendor( $vendor_obj ) ) {
				return esc_html__( 'Not found', 'multi-vendor-marketplace' );
			}

			return '1' === $withdraw_obj->get_payment_method() ? $vendor_obj->get_bank_name() : '';
		}

		/**
		 * Get IBAN
		 *
		 * @since 1.0.0
		 * @param MVR_Withdraw $withdraw_obj Withdraw Object.
		 * @return String
		 */
		protected function get_column_value_iban( $withdraw_obj ) {
			$vendor_obj = $withdraw_obj->get_vendor();

			if ( ! mvr_is_vendor( $vendor_obj ) ) {
				return esc_html__( 'Not found', 'multi-vendor-marketplace' );
			}

			return '1' === $withdraw_obj->get_payment_method() ? $vendor_obj->get_iban() : '';
		}

		/**
		 * Get SWIFT
		 *
		 * @since 1.0.0
		 * @param MVR_Withdraw $withdraw_obj Withdraw Object.
		 * @return String
		 */
		protected function get_column_value_swift( $withdraw_obj ) {
			$vendor_obj = $withdraw_obj->get_vendor();

			if ( ! mvr_is_vendor( $vendor_obj ) ) {
				return esc_html__( 'Not found', 'multi-vendor-marketplace' );
			}

			return '1' === $withdraw_obj->get_payment_method() ? $vendor_obj->get_swift() : '';
		}

		/**
		 * Get Date
		 *
		 * @since 1.0.0
		 * @param MVR_Withdraw $withdraw_obj Withdraw Object.
		 * @return String
		 */
		protected function get_column_value_date( $withdraw_obj ) {
			return $withdraw_obj->get_date_created()->date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
		}
	}
}
