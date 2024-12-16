<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Order_Filters' ) ) {

	class WCRP_Rental_Products_Order_Filters {

		public function __construct() {

			add_action( 'restrict_manage_posts', array( $this, 'rentals_filter_field' ) ); // If HPOS order tables disabled
			add_action( 'woocommerce_order_list_table_restrict_manage_orders', array( $this, 'rentals_filter_field' ) ); // If HPOS order tables enabled
			add_action( 'posts_where', array( $this, 'rentals_filter_clause' ) ); // If HPOS order tables disabled
			add_filter( 'woocommerce_orders_table_query_clauses', array( $this, 'rentals_filter_clause' ) ); // If HPOS order tables enabled

		}

		public function rentals_filter_field() {

			global $pagenow;

			$maybe_add_filter = false;

			if ( wcrp_rental_products_hpos_enabled() ) {

				if ( isset( $_GET['page'] ) ) {

					if ( 'admin.php' == $pagenow && 'wc-orders' == $_GET['page'] ) {

						$maybe_add_filter = true;

					}

				}

			} else {

				if ( isset( $_GET['post_type'] ) ) {

					if ( 'edit.php' == $pagenow && 'shop_order' == $_GET['post_type'] ) {

						$maybe_add_filter = true;

					}

				}

			}

			if ( true == $maybe_add_filter ) {

				$filter = '';

				if ( isset( $_GET[ 'wcrp_rental_products_rentals_filter' ] ) && !empty( $_GET[ 'wcrp_rental_products_rentals_filter' ] ) ) {

					$filter = sanitize_text_field( $_GET[ 'wcrp_rental_products_rentals_filter' ] );

				}

				?>

				<select name="wcrp_rental_products_rentals_filter">
					<option value=""><?php esc_html_e( 'Rentals filter', 'wcrp-rental-products' ); ?></option>
					<option value="inc"<?php echo ( 'inc' == $filter ? ' selected' : '' ); ?>><?php esc_html_e( 'Orders including rentals', 'wcrp-rental-products' ); ?></option>
					<option value="exc"<?php echo ( 'exc' == $filter ? ' selected' : '' ); ?>><?php esc_html_e( 'Orders excluding rentals', 'wcrp-rental-products' ); ?></option>
				</select>

				<?php

			}

		}

		public function rentals_filter_clause( $where ) {

			global $typenow;
			global $wpdb;

			if ( isset( $_GET['wcrp_rental_products_rentals_filter'] ) && !empty( $_GET['wcrp_rental_products_rentals_filter'] ) ) {

				$filter = trim( sanitize_text_field( $_GET['wcrp_rental_products_rentals_filter'] ) );
				$filter = $wpdb->_escape( $filter );
				$in_not_in = ( 'inc' == $filter ? 'IN' : 'NOT IN' );

				// Note that wcrp_rental_products_rent_from is used in the queries below as every rental will have this, except for cancelled which removes rent from/to meta, hence wcrp_rental_products_cancelled also included in the query

				if ( wcrp_rental_products_hpos_enabled() ) {

					if ( isset( $where['where'] ) ) {

						$where['where'] = $where['where'] . " AND {$wpdb->prefix}wc_orders.id " . $in_not_in . " ( SELECT order_id FROM `{$wpdb->prefix}woocommerce_order_items` AS oi INNER JOIN `{$wpdb->prefix}woocommerce_order_itemmeta` AS oim ON oi.order_item_id = oim.order_item_id WHERE meta_key IN ( 'wcrp_rental_products_rent_from', 'wcrp_rental_products_cancelled' ) )";

					}

				} else {

					if ( is_search() && 'shop_order' == $typenow ) {

						$where .= " AND $wpdb->posts.ID " . $in_not_in . " ( SELECT order_id FROM `{$wpdb->prefix}woocommerce_order_items` AS oi INNER JOIN `{$wpdb->prefix}woocommerce_order_itemmeta` AS oim ON oi.order_item_id = oim.order_item_id WHERE meta_key IN ( 'wcrp_rental_products_rent_from', 'wcrp_rental_products_cancelled' ) )";

					}

				}

			}

			return $where;

		}

	}

}
