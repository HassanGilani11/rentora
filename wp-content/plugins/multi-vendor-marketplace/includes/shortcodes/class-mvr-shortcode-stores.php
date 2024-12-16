<?php
/**
 * Stores Shortcode
 *
 * Used on the Dashboard page, the dashboard shortcode displays the vendor dashboard content.
 *
 * @package Multi Vendor Marketplace\Shortcodes\stores
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if directly accessed.
}


if ( ! class_exists( 'MVR_Shortcode_Stores' ) ) {
	/**
	 * Shortcode -> Stores.
	 *
	 * @class MVR_Shortcode_Stores
	 * @package Class
	 */
	class MVR_Shortcode_Stores {

		/**
		 * Get the shortcode content.
		 *
		 * @param array $atts Shortcode attributes.
		 *
		 * @return string
		 */
		public static function get( $atts ) {
			return MVR_Shortcodes::shortcode_wrapper( array( __CLASS__, 'output' ), $atts );
		}

		/**
		 * Output the shortcode.
		 *
		 * @since 1.0.0
		 * @param Array $atts Shortcode attributes.
		 */
		public static function output( $atts ) {
			// Output the new account page.
			self::stores( $atts );
		}

		/**
		 * Stores.
		 *
		 * @since 1.0.0
		 * @param Array $atts Shortcode attributes.
		 */
		public static function stores( $atts ) {
			global $mvr_stores;

			$atts = shortcode_atts(
				/**
				 * Stores Default Attributes
				 *
				 * @since 1.0.0
				 */
				apply_filters(
					'mvr_stores_default_attributes',
					array(
						'limit'      => '-1',
						'orderby'    => 'name',
						'order'      => 'ASC',
						'columns'    => '4',
						'hide_empty' => 1,
						'parent'     => '',
						'ids'        => '',
						'search'     => '',
					)
				),
				$atts
			);
			$vendors_objs  = mvr_get_vendors(
				array(
					'status' => 'active',
				)
			);
			$mvr_stores    = $vendors_objs;
			$limit         = '';
			$offset        = '';
			$paged         = '';
			$vendor_search = '';

			/**
			 * Stores Attributes
			 *
			 * @since 1.0.0
			 */
			$args = apply_filters(
				'mvr_stores_args',
				array(
					'vendors_objs'  => $vendors_objs,
					'limit'         => $limit,
					'offset'        => $offset,
					'paged'         => $paged,
					'search'        => $atts['search'],
					'columns'       => $atts['columns'],
					'vendor_search' => $vendor_search,
				)
			);

			mvr_get_template( 'stores.php', $args );
		}
	}
}
