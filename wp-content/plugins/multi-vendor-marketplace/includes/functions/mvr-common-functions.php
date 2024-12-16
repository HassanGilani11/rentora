<?php
/**
 * Common functions
 *
 * @package Name Your Own Price/Common Functions
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'mvr_get_order_statuses' ) ) {
	/**
	 * Function to order status
	 *
	 * @since 1.0.0
	 * @return Array
	 * */
	function mvr_get_order_statuses() {
		/**
		 * WooCommerce Order Status
		 *
		 * @since 1.0.0
		 */
		$wc_order_statuses = apply_filters(
			'wc_order_statuses',
			array(
				'wc-pending'    => _x( 'Pending payment', 'Order status', 'multi-vendor-marketplace' ),
				'wc-processing' => _x( 'Processing', 'Order status', 'multi-vendor-marketplace' ),
				'wc-on-hold'    => _x( 'On hold', 'Order status', 'multi-vendor-marketplace' ),
				'wc-completed'  => _x( 'Completed', 'Order status', 'multi-vendor-marketplace' ),
				'wc-cancelled'  => _x( 'Cancelled', 'Order status', 'multi-vendor-marketplace' ),
				'wc-refunded'   => _x( 'Refunded', 'Order status', 'multi-vendor-marketplace' ),
				'wc-failed'     => _x( 'Failed', 'Order status', 'multi-vendor-marketplace' ),
			)
		);

		$order_statuses = str_replace( 'wc-', '', array_keys( $wc_order_statuses ) );
		$order_slugs    = array_values( $wc_order_statuses );
		$order_statuses = array_combine( (array) $order_statuses, (array) $order_slugs );

		return $order_statuses;
	}
}

if ( ! function_exists( 'mvr_get_success_order_statuses' ) ) {
	/**
	 * Function to get success order status
	 *
	 * @since 1.0.0
	 * @return Array
	 * */
	function mvr_get_success_order_statuses() {
		$statuses       = array( 'pending', 'processing', 'on-hold', 'completed' );
		$order_statuses = mvr_get_order_statuses();

		foreach ( $order_statuses as $key => $value ) {
			if ( ! in_array( $key, $statuses, true ) ) {
				unset( $order_statuses[ $key ] );
			}
		}

		return $order_statuses;
	}
}

if ( ! function_exists( 'mvr_get_failed_order_statuses' ) ) {
	/**
	 * Function to get failed order status
	 *
	 * @since 1.0.0
	 * @return Array
	 * */
	function mvr_get_failed_order_statuses() {
		$statuses       = array( 'pending', 'processing', 'on-hold', 'completed' );
		$order_statuses = mvr_get_order_statuses();

		foreach ( $order_statuses as $key => $value ) {
			if ( in_array( $key, $statuses, true ) ) {
				unset( $order_statuses[ $key ] );
			}
		}

		return $order_statuses;
	}
}

if ( ! function_exists( 'mvr_commission_from_options' ) ) {

	/**
	 * Commission from Options.
	 *
	 * @since 1.0.0
	 * @param Integer|String $key Array key.
	 * @return Boolean
	 * */
	function mvr_commission_from_options( $key = '' ) {
		/**
		 * Commission from Options.
		 *
		 * @since 1.0.0
		 * @hook: mvr_commission_from_options
		 */
		$args = apply_filters(
			'mvr_commission_from_options',
			array(
				'1' => esc_html__( 'Global', 'multi-vendor-marketplace' ),
				'2' => esc_html__( 'Custom', 'multi-vendor-marketplace' ),
			)
		);

		if ( ! empty( $key ) ) {
			return isset( $args[ $key ] ) ? $args[ $key ] : '';
		}

		return $args;
	}
}

if ( ! function_exists( 'mvr_withdraw_from_options' ) ) {

	/**
	 * Withdraw from Options.
	 *
	 * @since 1.0.0
	 * @param Integer|String $key Array key.
	 * @return Boolean
	 * */
	function mvr_withdraw_from_options( $key = '' ) {
		/**
		 * Withdraw from Options.
		 *
		 * @since 1.0.0
		 * @hook: mvr_withdraw_from_options
		 */
		$args = apply_filters(
			'mvr_withdraw_from_options',
			array(
				'1' => esc_html__( 'Global', 'multi-vendor-marketplace' ),
				'2' => esc_html__( 'Custom', 'multi-vendor-marketplace' ),
			)
		);

		if ( ! empty( $key ) ) {
			return isset( $args[ $key ] ) ? $args[ $key ] : '';
		}

		return $args;
	}
}

if ( ! function_exists( 'mvr_commission_type_options' ) ) {

	/**
	 * Commission type Options.
	 *
	 * @since 1.0.0
	 * @param Integer|String $key Array key.
	 * @return Boolean
	 * */
	function mvr_commission_type_options( $key = '' ) {
		/**
		 * Commission Type Options.
		 *
		 * @since 1.0.0
		 * @hook: mvr_commission_type_options
		 */
		$args = apply_filters(
			'mvr_commission_type_options',
			array(
				'1' => esc_html__( 'Fixed', 'multi-vendor-marketplace' ),
				'2' => esc_html__( 'Percentage', 'multi-vendor-marketplace' ),
			)
		);

		if ( ! empty( $key ) ) {
			return isset( $args[ $key ] ) ? $args[ $key ] : '';
		}

		return $args;
	}
}

if ( ! function_exists( 'mvr_commission_criteria_options' ) ) {

	/**
	 * Commission Criteria Options.
	 *
	 * @since 1.0.0
	 * @param Integer|String $key Array key.
	 * @return Boolean
	 * */
	function mvr_commission_criteria_options( $key = '' ) {
		/**
		 * Commission Criteria Options.
		 *
		 * @since 1.0.0
		 * @hook: mvr_commission_criteria_options
		 */
		$args = apply_filters(
			'mvr_commission_criteria_options',
			array(
				'1' => esc_html__( 'From Vendor\'s First Order', 'multi-vendor-marketplace' ),
				'2' => esc_html__( 'After Vendor Reaching Specific Number of Orders', 'multi-vendor-marketplace' ),
				'3' => esc_html__( 'After Vendor has Earned more than a Specific Amount', 'multi-vendor-marketplace' ),
				'4' => esc_html__( 'If Vendor\'s Product Price is more than a Specific Amount', 'multi-vendor-marketplace' ),
			)
		);

		if ( ! empty( $key ) ) {
			return isset( $args[ $key ] ) ? $args[ $key ] : '';
		}

		return $args;
	}
}

if ( ! function_exists( 'mvr_tax_type_options' ) ) {

	/**
	 * Commission Tax Type Options.
	 *
	 * @since 1.0.0
	 * @param Integer|String $key Array key.
	 * @return Boolean
	 * */
	function mvr_tax_type_options( $key = '' ) {
		/**
		 * Commission Tax Type Options.
		 *
		 * @since 1.0.0
		 * @hook: mvr_tax_type_options
		 */
		$args = apply_filters(
			'mvr_tax_type_options',
			array(
				'2' => esc_html__( 'Only for Vendor', 'multi-vendor-marketplace' ),
				'1' => esc_html__( 'Only for Admin', 'multi-vendor-marketplace' ),
				'3' => esc_html__( 'Shared between Admin and Vendor', 'multi-vendor-marketplace' ),
			)
		);

		if ( ! empty( $key ) ) {
			return isset( $args[ $key ] ) ? $args[ $key ] : '';
		}

		return $args;
	}
}

if ( ! function_exists( 'mvr_payment_method_options' ) ) {

	/**
	 * Payment Method Options.
	 *
	 * @since 1.0.0
	 * @param Integer|String $key Array key.
	 * @return Boolean
	 * */
	function mvr_payment_method_options( $key = '' ) {
		/**
		 * Payment Method Options.
		 *
		 * @since 1.0.0
		 * @hook: mvr_payment_method_options
		 */
		$args = apply_filters(
			'mvr_payment_method_options',
			array(
				'1' => esc_html__( 'Bank Transfer', 'multi-vendor-marketplace' ),
				'2' => esc_html__( 'PayPal', 'multi-vendor-marketplace' ),
			)
		);

		if ( ! empty( $key ) ) {
			return isset( $args[ $key ] ) ? $args[ $key ] : '';
		}

		return $args;
	}
}

if ( ! function_exists( 'mvr_bank_account_type_options' ) ) {

	/**
	 * Bank Account Options.
	 *
	 * @since 1.0.0
	 * @param Integer|String $key Array key.
	 * @return Boolean
	 * */
	function mvr_bank_account_type_options( $key = '' ) {
		/**
		 * Bank Account Type Options.
		 *
		 * @since 1.0.0
		 * @hook: mvr_bank_account_type_options
		 */
		$args = apply_filters(
			'mvr_bank_account_type_options',
			array(
				'1' => esc_html__( 'Personal', 'multi-vendor-marketplace' ),
				'2' => esc_html__( 'Business', 'multi-vendor-marketplace' ),
			)
		);

		if ( ! empty( $key ) ) {
			return isset( $args[ $key ] ) ? $args[ $key ] : '';
		}

		return $args;
	}
}

if ( ! function_exists( 'mvr_payout_type_options' ) ) {

	/**
	 * Payout Type Options.
	 *
	 * @since 1.0.0
	 * @param Integer|String $key Array key.
	 * @return Boolean
	 * */
	function mvr_payout_type_options( $key = '' ) {
		/**
		 * Payout Type Options.
		 *
		 * @since 1.0.0
		 * @hook: mvr_payout_type_options
		 */
		$args = apply_filters(
			'mvr_payout_type_options',
			array(
				'1' => esc_html__( 'Manual', 'multi-vendor-marketplace' ),
				'2' => esc_html__( 'Automatic', 'multi-vendor-marketplace' ),
			)
		);

		if ( ! empty( $key ) ) {
			return isset( $args[ $key ] ) ? $args[ $key ] : '';
		}

		return $args;
	}
}

if ( ! function_exists( 'mvr_payout_schedule_options' ) ) {

	/**
	 * Payout Schedule Options.
	 *
	 * @since 1.0.0
	 * @param Integer|String $key Array key.
	 * @return Boolean
	 * */
	function mvr_payout_schedule_options( $key = '' ) {
		/**
		 * Payout Schedule Options.
		 *
		 * @since 1.0.0
		 * @hook: mvr_payout_schedule_options
		 */
		$args = apply_filters(
			'mvr_payout_schedule_options',
			array(
				'1' => esc_html__( 'Daily', 'multi-vendor-marketplace' ),
				'2' => esc_html__( 'Weekly', 'multi-vendor-marketplace' ),
				'3' => esc_html__( 'Bi-Weekly', 'multi-vendor-marketplace' ),
				'4' => esc_html__( 'Monthly', 'multi-vendor-marketplace' ),
				'5' => esc_html__( 'Quarterly', 'multi-vendor-marketplace' ),
			)
		);

		if ( ! empty( $key ) ) {
			return isset( $args[ $key ] ) ? $args[ $key ] : '';
		}

		return $args;
	}
}

if ( ! function_exists( 'mvr_prepare_payout_schedule_options' ) ) {

	/**
	 * Payout Schedule Options.
	 *
	 * @since 1.0.0
	 * @param Integer|String $key Array key.
	 * @return Boolean
	 * */
	function mvr_prepare_payout_schedule_options( $key = '' ) {
		$payout_schedules = mvr_payout_schedule_options();

		if ( 'yes' !== get_option( 'mvr_settings_enable_auto_withdraw_daily', 'no' ) && is_array( $payout_schedules ) ) {
			unset( $payout_schedules['1'] );
		}

		if ( 'yes' !== get_option( 'mvr_settings_enable_auto_withdraw_weekly', 'no' ) && is_array( $payout_schedules ) ) {
			unset( $payout_schedules['2'] );
		}

		if ( 'yes' !== get_option( 'mvr_settings_enable_auto_withdraw_biweekly', 'no' ) && is_array( $payout_schedules ) ) {
			unset( $payout_schedules['3'] );
		}

		if ( 'yes' !== get_option( 'mvr_settings_enable_auto_withdraw_monthly', 'no' ) && is_array( $payout_schedules ) ) {
			unset( $payout_schedules['4'] );
		}

		if ( 'yes' !== get_option( 'mvr_settings_enable_auto_withdraw_quarterly', 'no' ) && is_array( $payout_schedules ) ) {
			unset( $payout_schedules['5'] );
		}

		return $payout_schedules;
	}
}

if ( ! function_exists( 'mvr_get_product_statuses' ) ) {

	/**
	 * Product Statuses.
	 *
	 * @since 1.0.0
	 * @return Array
	 * */
	function mvr_get_product_statuses() {
		return array(
			'publish' => esc_html__( 'Published', 'multi-vendor-marketplace' ),
			'draft'   => esc_html__( 'Draft', 'multi-vendor-marketplace' ),
			'pending' => esc_html__( 'Pending', 'multi-vendor-woocommerce' ),
		);
	}
}

if ( ! function_exists( 'mvr_get_product_status_name' ) ) {
	/**
	 * Get Product Status Name.
	 *
	 * @since 1.0.0
	 * @param String $status Status name.
	 * @return String
	 */
	function mvr_get_product_status_name( $status ) {
		$statuses = mvr_get_product_statuses();

		return isset( $statuses[ "{$status}" ] ) ? $statuses[ "{$status}" ] : $status;
	}
}

if ( ! function_exists( 'mvr_get_coupon_statuses' ) ) {

	/**
	 * Coupon Statuses.
	 *
	 * @since 1.0.0
	 * @return Array
	 * */
	function mvr_get_coupon_statuses() {
		return array(
			'publish' => esc_html__( 'Published', 'multi-vendor-marketplace' ),
			'draft'   => esc_html__( 'Draft', 'multi-vendor-marketplace' ),
			'pending' => esc_html__( 'Pending', 'multi-vendor-woocommerce' ),
		);
	}
}

if ( ! function_exists( 'mvr_get_coupon_status_name' ) ) {
	/**
	 * Get Coupon Status Name.
	 *
	 * @since 1.0.0
	 * @param String $status Status name.
	 * @return String
	 */
	function mvr_get_coupon_status_name( $status ) {
		$statuses = mvr_get_coupon_statuses();

		return isset( $statuses[ "{$status}" ] ) ? $statuses[ "{$status}" ] : $status;
	}
}

if ( ! function_exists( 'mvr_withdraw_charge_type_options' ) ) {
	/**
	 * Withdrawal Charge Type Option.
	 *
	 * @since 1.0.0
	 * @param String $key Index.
	 * @return Array
	 * */
	function mvr_withdraw_charge_type_options( $key = '' ) {
		/**
		 * Withdrawal Charge Type.
		 *
		 * @since 1.0.0
		 * @hook: mvr_withdraw_charge_type_options
		 */
		$args = apply_filters(
			'mvr_withdraw_charge_type_options',
			array(
				'1' => esc_html__( 'Fixed', 'multi-vendor-marketplace' ),
				'2' => esc_html__( 'Percentage', 'multi-vendor-marketplace' ),
			)
		);

		if ( ! empty( $key ) ) {
			return isset( $args[ $key ] ) ? $args[ $key ] : '';
		}

		return $args;
	}
}

if ( ! function_exists( 'mvr_get_site_user_roles' ) ) {
	/**
	 * Withdrawal Charge Type Option.
	 *
	 * @since 1.0.0
	 * @return Array
	 * */
	function mvr_get_site_user_roles() {
		global $wp_roles;
		$roles = $wp_roles->get_names();

		if ( isset( $roles['mvr-vendor'] ) ) {
			unset( $roles['mvr-vendor'] );
		}

		return $roles;
	}
}

if ( ! function_exists( 'mvr_week_days_options' ) ) {

	/**
	 * Get Week Days
	 *
	 * @since 1.0.0
	 * @param String $key Key of the option.
	 * @return Array
	 */
	function mvr_week_days_options( $key = '' ) {

		/**
		 * Week days options
		 *
		 * @since 1.0.0
		 * @hook: mvr_week_days_options
		 */
		$args = apply_filters(
			'mvr_week_days_options',
			array(
				'1' => esc_html__( 'Sunday', 'multi-vendor-marketplace' ),
				'2' => esc_html__( 'Monday', 'multi-vendor-marketplace' ),
				'3' => esc_html__( 'Tuesday', 'multi-vendor-marketplace' ),
				'4' => esc_html__( 'Wednesday', 'multi-vendor-marketplace' ),
				'5' => esc_html__( 'Thursday', 'multi-vendor-marketplace' ),
				'6' => esc_html__( 'Friday', 'multi-vendor-marketplace' ),
				'7' => esc_html__( 'Saturday', 'multi-vendor-marketplace' ),
			)
		);

		if ( ! empty( $key ) ) {
			return isset( $args[ $key ] ) ? $args[ $key ] : '';
		}

		return $args;
	}
}

if ( ! function_exists( 'mvr_month_week_options' ) ) {

	/**
	 * Get Month Week Options
	 *
	 * @since 1.0.0
	 * @param String $key Key of the month option.
	 * @return Array
	 */
	function mvr_month_week_options( $key = '' ) {
		/**
		 * Month Options.
		 *
		 * @since 1.0.0
		 * @hook: mvr_month_week_options
		 */
		$args = apply_filters(
			'mvr_month_week_options',
			array(
				'1' => esc_html__( 'First Week', 'multi-vendor-marketplace' ),
				'2' => esc_html__( 'Second Week', 'multi-vendor-marketplace' ),
				'3' => esc_html__( 'Third Week', 'multi-vendor-marketplace' ),
				'4' => esc_html__( 'Fourth Week', 'multi-vendor-marketplace' ),
			)
		);

		if ( ! empty( $key ) ) {
			return isset( $args[ $key ] ) ? $args[ $key ] : '';
		}

		return $args;
	}
}

if ( ! function_exists( ' mvr_month_options' ) ) {

	/**
	 * Get Month Options
	 *
	 * @since 1.0.0
	 * @param String $key Key of the month option.
	 * @return Array
	 */
	function mvr_month_options( $key = '' ) {
		/**
		 * Month Options.
		 *
		 * @since 1.0.0
		 * @hook: mvr_month_options
		 */
		$args = apply_filters(
			'mvr_month_options',
			array(
				'1'  => esc_html__( 'January', 'multi-vendor-marketplace' ),
				'2'  => esc_html__( 'February', 'multi-vendor-marketplace' ),
				'3'  => esc_html__( 'March', 'multi-vendor-marketplace' ),
				'4'  => esc_html__( 'April', 'multi-vendor-marketplace' ),
				'5'  => esc_html__( 'May', 'multi-vendor-marketplace' ),
				'6'  => esc_html__( 'June', 'multi-vendor-marketplace' ),
				'7'  => esc_html__( 'July', 'multi-vendor-marketplace' ),
				'8'  => esc_html__( 'August', 'multi-vendor-marketplace' ),
				'9'  => esc_html__( 'September', 'multi-vendor-marketplace' ),
				'10' => esc_html__( 'October', 'multi-vendor-marketplace' ),
				'11' => esc_html__( 'November', 'multi-vendor-marketplace' ),
				'12' => esc_html__( 'December', 'multi-vendor-marketplace' ),
			)
		);

		if ( ! empty( $key ) ) {
			return isset( $args[ $key ] ) ? $args[ $key ] : '';
		}

		return $args;
	}
}


if ( ! function_exists( ' mvr_get_automatic_schedule_description' ) ) {

	/**
	 * Get Schedule Description
	 *
	 * @since 1.0.0
	 * @param String $key Key of the month option.
	 * @return String
	 */
	function mvr_get_automatic_schedule_description( $key ) {
		switch ( $key ) {
			case '2':
				$day = mvr_week_days( get_option( 'mvr_settings_withdraw_week_start_day', '1' ) );
				/* translators: %s: Day */
				$description = sprintf( esc_html__( 'Every %s 11:00 pm', 'multi-vendor-marketplace' ), esc_html( $day ) );
				break;
			case '3':
				$settings = get_option(
					'mvr_settings_biweekly_settings',
					array(
						'week' => '1',
						'day'  => '1',
					)
				);

				$day = mvr_week_days( $settings['day'] );

				if ( '2' === $settings['week'] ) {
					$week = mvr_month_week_options( '2' ) . ' ' . $day . ' 11:00 pm & ' . mvr_month_week_options( '4' ) . ' ' . $day . ' 11:00 pm';
				} else {
					$week = mvr_month_week_options( '1' ) . ' ' . $day . ' 11:00 pm & ' . mvr_month_week_options( '3' ) . ' ' . $day . ' 11:00 pm';
				}

				/* translators: %s: Week & Day */
				$description = sprintf( esc_html__( 'Every %1$s', 'multi-vendor-marketplace' ), esc_html( $week ) );
				break;
			case '4':
				$settings = get_option(
					'mvr_settings_monthly_settings',
					array(
						'week' => '1',
						'day'  => '1',
					)
				);

				$week = mvr_month_week_options( $settings['week'] );
				$day  = mvr_week_days( $settings['day'] );
				/* translators: %s: Week & Day */
				$description = sprintf( esc_html__( 'Every Month %1$s 11:00 pm', 'multi-vendor-marketplace' ), esc_html( $week ) . esc_html( $day ) );
				break;
			case '5':
				$args = get_option(
					'mvr_settings_quarterly_settings',
					array(
						'month' => '1',
						'week'  => '1',
						'day'   => '1',
					)
				);

				$month       = $args['month'];
				$description = '';
				for ( $i = 4; 0 < $i; $i-- ) {
					$description .= mvr_months( $month ) . ' ' . mvr_month_week_options( $args['week'] ) . ' ' . mvr_week_days( $args['day'] ) . ' 11:00 pm<br/>';
					$month       += 3;
				}

				break;
			default:
				$description = esc_html__( 'Every day 11:00 pm', 'multi-vendor-marketplace' );
				break;
		}

		return $description;
	}
}
