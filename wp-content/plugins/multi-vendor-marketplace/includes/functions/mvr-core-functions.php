<?php
/**
 * Get our templates.
 *
 * @package Multi-Vendor for WooCommerce/ Core Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if access directly.
}

require_once 'mvr-commission-functions.php';
require_once 'mvr-common-functions.php';
require_once 'mvr-conditional-functions.php';
require_once 'mvr-customer-functions.php';
require_once 'mvr-dashboard-functions.php';
require_once 'mvr-enquiry-functions.php';
require_once 'mvr-layout-functions.php';
require_once 'mvr-notification-functions.php';
require_once 'mvr-order-functions.php';
require_once 'mvr-page-functions.php';
require_once 'mvr-payout-batch-functions.php';
require_once 'mvr-payout-functions.php';
require_once 'mvr-review-functions.php';
require_once 'mvr-spmv-functions.php';
require_once 'mvr-staff-functions.php';
require_once 'mvr-time-functions.php';
require_once 'mvr-template-functions.php';
require_once 'mvr-transaction-functions.php';
require_once 'mvr-vendor-functions.php';
require_once 'mvr-withdraw-functions.php';

if ( ! function_exists( 'mvr_get_template' ) ) {

	/**
	 * Get other templates from themes.
	 *
	 * @since 1.0.0
	 * @param String $template_name Template Name.
	 * @param Array  $args Arguments.
	 * */
	function mvr_get_template( $template_name, $args = array() ) {
		if ( ! $template_name ) {
			return;
		}

		wc_get_template( $template_name, $args, mvr()->template_path(), mvr()->templates() );
	}
}

if ( ! function_exists( 'mvr_get_template_html' ) ) {

	/**
	 * Like mvr_get_template, but returns the HTML instead of outputting.
	 *
	 * @since 1.0.0
	 * @param String $template_name Template Name.
	 * @param Array  $args Arguments.
	 * */
	function mvr_get_template_html( $template_name, $args = array() ) {
		ob_start();
		mvr_get_template( $template_name, $args );

		return ob_get_clean();
	}
}

if ( ! function_exists( 'mvr_array_insert' ) ) {
	/**
	 * Insert an array in to the given array.
	 *
	 * @since 1.0.0
	 * @param Array   $array Array.
	 * @param Array   $insert Array to insert.
	 * @param Integer $position Array position.
	 * @param Boolean $preserve_keys end key.
	 * @return Array
	 */
	function mvr_array_insert( $array, $insert, $position, $preserve_keys = true ) {
		return array_slice( $array, 0, $position, $preserve_keys ) + $insert + array_slice( $array, $position, count( $array ) - 1, $preserve_keys );
	}
}

if ( ! function_exists( 'mvr_get_current_url' ) ) {
	/**
	 * Get an actual url of the current page.
	 *
	 * @since 1.0.0
	 * @return String
	 */
	function mvr_get_current_url() {
		if ( ! isset( $_SERVER['HTTP_HOST'] ) || ! isset( $_SERVER['REQUEST_URI'] ) ) {
			return;
		}

		$actual_url = ( isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ? 'https' : 'http' ) . '://' . wp_kses_post( wp_unslash( $_SERVER['HTTP_HOST'] ) ) . wp_kses_post( wp_unslash( $_SERVER['REQUEST_URI'] ) );

		return $actual_url;
	}
}

if ( ! function_exists( 'mvr_trim_post_status' ) ) {
	/**
	 * Trim our post status without prefix.
	 *
	 * @since 1.0.0
	 * @param String $status Status.
	 * @return String
	 */
	function mvr_trim_post_status( $status ) {
		$status = ( 'mvr-' === substr( $status, 0, 4 ) ) ? substr( $status, 4 ) : $status;

		return $status;
	}
}

if ( ! function_exists( 'mvr_trim_post_status' ) ) {
	/**
	 * Maybe get the product instance.
	 *
	 * @since 1.0.0
	 * @param Mixed $product Product Object.
	 * @return WC_Product
	 */
	function mvr_maybe_get_product_instance( $product ) {
		if ( ! is_a( $product, 'WC_Product' ) ) {
			$product = wc_get_product( $product );
		}

		return $product;
	}
}

if ( ! function_exists( 'mvr_maybe_get_order_instance' ) ) {
	/**
	 * Get the order instance.
	 *
	 * @since 1.0.0
	 * @param Mixed $order Order Object.
	 * @return Object
	 */
	function mvr_maybe_get_order_instance( $order ) {
		if ( ! is_object( $order ) || ! is_a( $order, 'WC_Order' ) ) {
			$order = wc_get_order( $order );
		}

		return $order;
	}
}

if ( ! function_exists( 'mvr_maybe_get_post_object' ) ) {
	/**
	 * May be get the post object.
	 *
	 * @since 1.0.0
	 * @param Mixed $value Value.
	 * @return False|WP_Post
	 */
	function mvr_maybe_get_post_object( $value = null ) {
		global $post;

		if ( is_null( $value ) ) {
			$value = $post;
		}

		$post_object = false;

		if ( is_object( $value ) ) {
			if ( is_a( $value, 'WC_Product' ) ) {
				$post_object = get_post( $value->get_id() );
			} elseif ( is_a( $value, 'WP_Post' ) ) {
				$post_object = $value;
			}
		} elseif ( is_numeric( $value ) ) {
			$post_object = get_post( $value );
		}

		return $post_object;
	}
}

if ( ! function_exists( 'mvr_get_product_term_ids' ) ) {
	/**
	 * Get all terms for a product by ID, including hierarchy
	 *
	 * @since 1.0.0
	 * @param Integer $product_id Product ID.
	 * @param String  $taxonomy Taxonomy slug.
	 * @return Array
	 */
	function mvr_get_product_term_ids( $product_id, $taxonomy = 'product_cat' ) {
		$product_terms = wc_get_product_term_ids( $product_id, $taxonomy );

		foreach ( $product_terms as $product_term ) {
			$product_terms = array_merge( $product_terms, get_ancestors( $product_term, $taxonomy ) );
		}

		return $product_terms;
	}
}

if ( ! function_exists( 'mvr_get_term_product_ids' ) ) {
	/**
	 * Return products in a given term.
	 *
	 * @since 1.0.0
	 * @param Integer $term_id Term ID.
	 * @param String  $taxonomy Taxonomy slug.
	 * @return Array
	 */
	function mvr_get_term_product_ids( $term_id, $taxonomy = 'product_cat' ) {
		$term = get_term( $term_id, $taxonomy );

		if ( $term ) {
			$term_ids    = get_term_children( $term->term_id, $taxonomy );
			$term_ids[]  = $term->term_id;
			$product_ids = get_objects_in_term( $term_ids, $taxonomy );
			$product_ids = array_unique( $product_ids );
		} else {
			$product_ids = array();
		}

		return $product_ids;
	}
}

if ( ! function_exists( 'mvr_get_product_term_name_by_term_id' ) ) {
	/**
	 * Wrapper to get the product term name by its term ID.
	 *
	 * @since 1.0.0
	 * @param Integer $term_id Term Id.
	 * @param String  $taxonomy Taxonomy slug.
	 * @return String
	 */
	function mvr_get_product_term_name_by_term_id( $term_id, $taxonomy = 'product_cat' ) {
		$term = get_term( absint( $term_id ), $taxonomy );

		return $term ? $term->name : '';
	}
}

if ( ! function_exists( 'mvr_get_product_term_link_by_term_id' ) ) {
	/**
	 * Wrapper to get the product term link by its term ID.
	 *
	 * @since 1.0.0
	 * @param Integer $term_id Term ID.
	 * @param String  $taxonomy Taxonomy slug.
	 * @return String
	 */
	function mvr_get_product_term_link_by_term_id( $term_id, $taxonomy = 'product_cat' ) {
		$term_link = get_term_link( absint( $term_id ), $taxonomy );

		return ! is_wp_error( $term_link ) ? $term_link : '';
	}
}

if ( ! function_exists( 'mvr_get_quarter_month_schedule' ) ) {
	/**
	 * Get quarter month schedule
	 *
	 * @since 1.0.0
	 * @return Integer
	 */
	function mvr_get_quarter_month_schedule() {
		$now           = mvr_get_datetime( 'now' );
		$withdraw_time = '23:00';
		$args          = get_option(
			'mvr_settings_quarterly_settings',
			array(
				'month' => '1',
				'week'  => '1',
				'day'   => '1',
			)
		);
		$week          = mvr_get_descriptive_week_of_month( $args['week'] );
		$day           = strtolower( mvr_week_days( $args['day'] ) );
		$month         = strtolower( mvr_months( $args['month'] ) );
		$first_quarter = mvr_get_datetime( 'now' )->modify( "{$week} {$day} of {$month} this year {$withdraw_time}" );

		// 1st Quarter.
		if ( $now->getTimestamp() < $first_quarter->getTimestamp() ) {
			return $first_quarter->getTimestamp();
		}

		$month          = strtolower( mvr_months( ( (float) $args['month'] + 3 ) ) );
		$second_quarter = mvr_get_datetime( 'now' )->modify( "{$week} {$day} of {$month} this year {$withdraw_time}" );

		// 2nd Quarter.
		if ( $now->getTimestamp() < $second_quarter->getTimestamp() ) {
			return $second_quarter->getTimestamp();
		}

		// 3rd Quarter.
		$month         = strtolower( mvr_months( ( (float) $args['month'] + 6 ) ) );
		$third_quarter = mvr_get_datetime( 'now' )->modify( "{$week} {$day} of {$month} this year {$withdraw_time}" );

		if ( $now->getTimestamp() < $third_quarter->getTimestamp() ) {
			return $third_quarter->getTimestamp();
		}

		$month          = strtolower( mvr_months( ( (float) $args['month'] + 9 ) ) );
		$fourth_quarter = mvr_get_datetime( 'now' )->modify( "{$week} {$day} of {$month} this year {$withdraw_time}" );

		// 4th Quarter.
		if ( $now->getTimestamp() < $fourth_quarter->getTimestamp() ) {
			return $fourth_quarter->getTimestamp();
		}

		$next_year = $now->modify( 'next year' )->format( 'Y' );

		return $now->modify( "{$week} {$day} of {$month} {$next_year} {$withdraw_time}" )->getTimestamp();
	}
}

if ( ! function_exists( 'mvr_get_month_schedule' ) ) {
	/**
	 * Get month schedule
	 *
	 * @since 1.0.0
	 * @return Integer
	 */
	function mvr_get_month_schedule() {
		$now                = mvr_get_datetime( 'now' );
		$withdraw_time      = '23:00';
		$args               = get_option(
			'mvr_settings_monthly_settings',
			array(
				'week' => '1',
				'day'  => '1',
			)
		);
		$week               = mvr_get_descriptive_week_of_month( $args['week'] );
		$day                = strtolower( mvr_week_days( $args['day'] ) );
		$current_month_time = mvr_get_datetime( 'now' )->modify( "{$week} {$day} of this month {$withdraw_time}" );

		if ( $now->getTimestamp() < $current_month_time->getTimestamp() ) {
			return $current_month_time->getTimestamp();
		}

		return $now->modify( "{$week} {$day} of next month {$withdraw_time}" )->getTimestamp();
	}
}

if ( ! function_exists( 'mvr_get_biweekly_schedule' ) ) {
	/**
	 * Get biweekly schedule
	 *
	 * @since 1.0.0
	 * @return Integer
	 */
	function mvr_get_biweekly_schedule() {
		$now           = mvr_get_datetime( 'now' );
		$withdraw_time = '23:00';
		$args          = get_option(
			'mvr_settings_biweekly_settings',
			array(
				'week' => '1',
				'day'  => '1',
			)
		);
		$day           = strtolower( mvr_week_days( $args['day'] ) );
		$first_week    = mvr_get_datetime( 'now' )->modify( "first {$day} of this month {$withdraw_time}" );

		if ( '1' === $args['week'] ) {
			if ( $now->getTimestamp() < $first_week->getTimestamp() ) {
				return $first_week->getTimestamp();
			}

			if ( $now->getTimestamp() < $first_week->modify( '+2 weeks' )->getTimestamp() ) {
				return $first_week->modify( '+2 weeks' )->getTimestamp();
			}

			return $now->modify( "first {$day} of next month {$withdraw_time}" )->getTimestamp();
		}

		$second_week = mvr_get_datetime( 'now' )->modify( "second {$day} of this month {$withdraw_time}" );

		if ( $now->getTimestamp() < $second_week->getTimestamp() ) {
			return $second_week->getTimestamp();
		}

		if ( $now->getTimestamp() < $second_week->modify( '+2 weeks' )->getTimestamp() ) {
			return $second_week->modify( '+2 weeks' )->getTimestamp();
		}

		return $now->modify( "second {$day} of next month {$withdraw_time}" )->getTimestamp();
	}
}

if ( ! function_exists( 'mvr_get_weekly_schedule' ) ) {
	/**
	 * Get biweekly schedule
	 *
	 * @since 1.0.0
	 * @return Integer
	 */
	function mvr_get_weekly_schedule() {
		$now           = mvr_get_datetime( 'now' );
		$withdraw_time = '23:00';
		$day           = strtolower( mvr_week_days( get_option( 'mvr_settings_withdraw_week_start_day', '1' ) ) );
		$current_week  = mvr_get_datetime( 'now' )->modify( "this {$day} {$withdraw_time}" );

		if ( $now->getTimestamp() < $current_week->getTimestamp() ) {
			return $current_week->getTimestamp();
		}

		return $current_week->modify( '+ 1 week' )->getTimestamp();
	}
}

if ( ! function_exists( 'mvr_insert_row_query' ) ) {
	/**
	 * Insert Row Query
	 *
	 * @since 1.0.0
	 * @param String $table_name Table name.
	 * @param Array  $data Data array.
	 * @param Array  $format Data format.
	 * @return Integer
	 */
	function mvr_insert_row_query( $table_name, $data, $format ) {
		global $wpdb;

		$wpdb_ref = &$wpdb;

		if ( $wpdb_ref->get_var( "SHOW TABLES LIKE '{$table_name}'" ) ) {
			$wpdb_ref->insert(
				$table_name,
				$data,
				$format
			);

			return $wpdb_ref->insert_id;
		} else {
			/* translators: %s Database Table Name */
			return new WP_Error( 'invalid-table', sprintf( esc_html__( '%s not found in database', 'multi-vendor-marketplace' ), esc_html( $table_name ) ) );
		}
	}
}

if ( ! function_exists( 'mvr_update_row_query' ) ) {
	/**
	 * Update Row Query
	 *
	 * @since 1.0.0
	 * @param String $table_name Table name.
	 * @param Array  $data Data array.
	 * @param Array  $values Values array.
	 * @param Array  $where Condition.
	 * @return Integer
	 */
	function mvr_update_row_query( $table_name, $data, $values, $where ) {
		global $wpdb;

		$wpdb_ref = &$wpdb;

		if ( $wpdb_ref->get_var( "SHOW TABLES LIKE '{$table_name}'" ) ) {
			$columns = array();

			foreach ( $data as $key => $place_holder ) {
				$columns[] = '`' . $key . '` = ' . "{$place_holder}";
			}

			$column_clause = implode( ', ', $columns );
			$id            = $wpdb_ref->query(
				$wpdb_ref->prepare( "UPDATE $table_name SET $column_clause WHERE $where", array_values( $values ) )
			);

			return $id;
		} else {
			/* translators: %s Database Table Name */
			return new WP_Error( 'invalid-table', sprintf( esc_html__( '%s not found in database', 'multi-vendor-marketplace' ), esc_html( $table_name ) ) );
		}
	}
}
