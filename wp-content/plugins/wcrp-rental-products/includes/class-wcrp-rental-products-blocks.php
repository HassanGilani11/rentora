<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Blocks' ) ) {

	class WCRP_Rental_Products_Blocks {

		public function __construct() {

			add_filter( 'block_categories_all', array( $this, 'category' ) );
			add_action( 'init', array( $this, 'availability_checker' ), 0 );

		}

		public function category( $categories ) {

			$category_slugs = wp_list_pluck( $categories, 'slug' );

			return in_array( 'wcrp-rental-products', $category_slugs, true ) ? $categories : array_merge(
				$categories,
				array(
					array(
						'slug'  => 'wcrp-rental-products',
						'title' => __( 'Rental Products', 'wcrp-rental-products' ),
					),
				)
			);

		}

		public function availability_checker() {

			$asset_file = include plugin_dir_path( __FILE__ ) . 'blocks/availability-checker/build/index.asset.php';

			wp_register_script(
				'wcrp-rental-products-availability-checker-block',
				plugins_url( 'blocks/availability-checker/build/index.js', __FILE__ ),
				$asset_file['dependencies'],
				$asset_file['version'],
				true
			);

			register_block_type( 'wcrp-rental-products/availability-checker',
				array(
					'api_version'		=> 2,
					'editor_script'		=> 'wcrp-rental-products-availability-checker-block',
					'render_callback'	=> array( $this, 'availability_checker_render' ),
				)
			);

		}

		public function availability_checker_render() {

			$availability_checker_mode = get_option( 'wcrp_rental_products_availability_checker_mode' );
			$availability_checker = ( 'ajax' == $availability_checker_mode ? WCRP_Rental_Products_Availability_Checker::display_ajax_placeholder() : WCRP_Rental_Products_Availability_Checker::display() );

			return $availability_checker;

		}

	}

}
