<?php
/**
 * Page functions
 *
 * @package Multi Vendor/Page Functions.
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'mvr_get_page_id' ) ) {
	/**
	 * Get Page ID.
	 *
	 * @since 1.0.0
	 * @param String $page Page Slug.
	 * @return Integer
	 * */
	function mvr_get_page_id( $page ) {
		/**
		 * Page ID
		 *
		 * @since 1.0.0
		 */
		$page = apply_filters( 'mvr_get_' . $page . '_page_id', get_option( 'mvr_settings_' . $page . '_page_id' ) );

		return $page ? absint( $page ) : -1;
	}
}

if ( ! function_exists( 'mvr_get_page_permalink' ) ) {
	/**
	 * Retrieve page permalink.
	 *
	 * @since 1.0.0
	 * @param String         $page page slug.
	 * @param String|Boolean $fallback Fallback URL if page is not set. Defaults to home URL.
	 * @return String
	 */
	function mvr_get_page_permalink( $page, $fallback = null ) {
		$page_id   = mvr_get_page_id( $page );
		$permalink = 0 < $page_id ? get_permalink( $page_id ) : '';

		if ( ! $permalink ) {
			$permalink = is_null( $fallback ) ? get_home_url() : $fallback;
		}

		/**
		 * Page Permalinks
		 *
		 * @since 1.0.0
		 */
		return apply_filters( 'mvr_get_' . $page . '_page_permalink', $permalink );
	}
}

if ( ! function_exists( 'mvr_get_store_url' ) ) {
	/**
	 * Store URL.
	 *
	 * @since 1.0.0
	 * @param String $slug Store Slug.
	 * @return URL
	 */
	function mvr_get_store_url( $slug ) {
		$query_vars      = mvr()->query->get_query_vars();
		$endpoint        = ! empty( $query_vars['mvr-store'] ) ? $query_vars['mvr-store'] : 'mvr-store';
		$stores_page_url = mvr_get_page_permalink( 'stores' );
		$store_url       = wc_get_endpoint_url( $endpoint, $slug, $stores_page_url );

		return $store_url;
	}
}
