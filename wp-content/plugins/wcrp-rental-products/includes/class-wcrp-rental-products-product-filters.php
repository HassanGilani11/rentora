<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Product_Filters' ) ) {

	class WCRP_Rental_Products_Product_Filters {

		public function __construct() {

			add_action( 'restrict_manage_posts', array( $this, 'rentals_filter' ), PHP_INT_MAX );
			add_action( 'posts_where', array( $this, 'rentals_filter_clause' ) );

		}

		public function rentals_filter() {

			global $typenow;

			if ( 'product' !== $typenow ) {

				return;

			}

			$filter = '';

			if ( isset( $_GET[ 'wcrp_rental_products_rentals_filter' ] ) && !empty( $_GET[ 'wcrp_rental_products_rentals_filter' ] ) ) {

				$filter = sanitize_text_field( $_GET[ 'wcrp_rental_products_rentals_filter' ] );

			}

			?>

			<select name="wcrp_rental_products_rentals_filter">
				<option value=""><?php esc_html_e( 'Filter by rentals', 'wcrp-rental-products' ); ?></option>
				<option value="rental_only"<?php echo ( 'rental_only' == $filter ? ' selected' : '' ); ?>><?php esc_html_e( 'Rental only', 'wcrp-rental-products' ); ?></option>
				<option value="rental_purchase"<?php echo ( 'rental_purchase' == $filter ? ' selected' : '' ); ?>><?php esc_html_e( 'Rental or purchase', 'wcrp-rental-products' ); ?></option>
				<option value="all"<?php echo ( 'all' == $filter ? ' selected' : '' ); ?>><?php esc_html_e( 'All rentals', 'wcrp-rental-products' ); ?></option>
			</select>

			<?php

		}

		public function rentals_filter_clause( $where ) {

			global $typenow;
			global $wpdb;

			if ( is_admin() && 'product' == $typenow ) {

				if ( isset( $_GET[ 'wcrp_rental_products_rentals_filter' ] ) && !empty( $_GET[ 'wcrp_rental_products_rentals_filter' ] ) ) {

					$filter = trim( sanitize_text_field( $_GET[ 'wcrp_rental_products_rentals_filter' ] ) );
					$filter = $wpdb->_escape( $filter );

					if ( 'rental_only' == $filter ) {

						$where .= " AND ($wpdb->posts.ID IN(
						SELECT post_id FROM $wpdb->postmeta
						WHERE $wpdb->postmeta.meta_key = '_wcrp_rental_products_rental'
						AND $wpdb->postmeta.meta_value = 'yes' ) )";

					} elseif ( 'rental_purchase' == $filter ) {

						$where .= " AND ($wpdb->posts.ID IN(
						SELECT post_id FROM $wpdb->postmeta
						WHERE $wpdb->postmeta.meta_key = '_wcrp_rental_products_rental'
						AND $wpdb->postmeta.meta_value = 'yes_purchase' ) )";

					} elseif ( 'all' == $filter ) {

						$where .= " AND ($wpdb->posts.ID IN(
						SELECT post_id FROM $wpdb->postmeta
						WHERE $wpdb->postmeta.meta_key = '_wcrp_rental_products_rental'
						AND $wpdb->postmeta.meta_value IN( 'yes', 'yes_purchase' ) ) )";

					}

				}

			}

			return $where;

		}

	}

}
