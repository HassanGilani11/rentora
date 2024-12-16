<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Rentals_Inventory' ) ) {

	class WCRP_Rental_Products_Rentals_Inventory {

		public static function rental_products() {

			global $wpdb;

			return $wpdb->get_results(
				"SELECT ID, post_title FROM {$wpdb->prefix}posts AS posts INNER JOIN {$wpdb->prefix}postmeta AS postmeta ON posts.ID = postmeta.post_id WHERE posts.post_type = 'product' AND posts.post_status = 'publish' AND postmeta.meta_key = '_wcrp_rental_products_rental' AND postmeta.meta_value IN ( 'yes', 'yes_purchase' ) ORDER BY post_title;"
			);

		}

		public static function row_data_date_view( $product_id, $inventory_date ) {

			global $wpdb;

			$rental_stock = get_post_meta( $product_id, '_wcrp_rental_products_rental_stock', true );
			$rental_stock_total = ( '' == $rental_stock ? esc_html__( 'Unlimited', 'wcrp-rental-products' ) : (int) $rental_stock );
			$rental_stock_out = 0;
			$rental_stock_returned = 0;

			// Get rentals from rentals table (doesn't include cancelled rentals, and archived doesn't matter as already returned) matching product id and inventory date, we then set the column data based on these rentals

			$rentals_on_date = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM `{$wpdb->prefix}wcrp_rental_products_rentals` WHERE `product_id` = %d AND `reserved_date` = %s;",
					$product_id,
					$inventory_date
				)
			);

			// Start rental stock out orders array

			$rental_stock_out_orders = array();

			// Loop through rentals on date

			foreach ( $rentals_on_date as $rental_on_date ) {

				// Add the quantity reserved on the date to the rental stock out total

				$rental_stock_out = (int) $rental_stock_out + (int) $rental_on_date->quantity;

				// If the rental was returned add the quantity to the rental stock returned total (to be subtracted later)

				if ( 'yes' == wc_get_order_item_meta( $rental_on_date->order_item_id, 'wcrp_rental_products_returned', true ) ) {

					$rental_stock_returned = (int) $rental_stock_returned + (int) $rental_on_date->quantity;

				}

				// If the rental is an in person pick up/return with an in person return date of same day then this should not be considered that the rental stock is out and therefore the quantity is removed from the rental stock out total, this is because in this scenario the rent to date is a reserved date for the quantity, but it's being returned on that date, without this if an in person return date same day rental has 1 stock, 1 is rented 2023-01-01 to 2023-01-03, on 2023-01-03 the inventory would show total 1, in 0 and out 1, but on 2023-01-03 it is being returned and available again to other customers after the return time

				if ( 'yes' == wc_get_order_item_meta( $rental_on_date->order_item_id, 'wcrp_rental_products_in_person_pick_up_return', true ) ) {

					$in_person_return_date = wc_get_order_item_meta( $rental_on_date->order_item_id, 'wcrp_rental_products_in_person_return_date', true );

					if ( wc_get_order_item_meta( $rental_on_date->order_item_id, 'wcrp_rental_products_rent_to', true ) == $in_person_return_date ) {

						if ( $in_person_return_date == $rental_on_date->reserved_date ) {

							$rental_stock_out = (int) $rental_stock_out - (int) $rental_on_date->quantity;

						}

					}

				}

				// If the rental was not returned add order to rental stock out orders array, order id key used to stop multiples of same order

				if ( 'yes' !== wc_get_order_item_meta( $rental_on_date->order_item_id, 'wcrp_rental_products_returned', true ) ) {

					$rental_stock_out_orders[$rental_on_date->order_id] = esc_html__( '#', 'wcrp-rental-products' ) . $rental_on_date->order_id;

				}

			}

			// Final calculations

			$rental_stock_out = (int) $rental_stock_out - (int) $rental_stock_returned;
			$rental_stock_in = ( is_int( $rental_stock_total ) ? $rental_stock_total - $rental_stock_out : $rental_stock_total ); // Is int condition is because it can either be int or unlimited string

			// Row data populated and returned

			$row_data = array(
				'product'					=> self::row_data_product( $product_id ),
				'id'						=> $product_id,
				'parent_id'					=> self::row_data_parent_id( $product_id ),
				'sku'						=> self::row_data_sku( $product_id ),
				'rental_stock_total'		=> $rental_stock_total,
				'rental_stock_in'			=> $rental_stock_in,
				'rental_stock_out'			=> $rental_stock_out,
				'rental_stock_out_orders'	=> $rental_stock_out_orders,
			);

			return $row_data;

		}

		public static function row_data_live_view( $product_id, $inventory_date ) {

			global $wpdb;

			$rental_stock = get_post_meta( $product_id, '_wcrp_rental_products_rental_stock', true );
			$rental_stock_total = ( '' == $rental_stock ? esc_html__( 'Unlimited', 'wcrp-rental-products' ) : (int) $rental_stock );
			$rental_stock_out = 0;

			// Get rental order items from rentals table (doesn't include cancelled rentals, and archived doesn't matter as already returned) matching product id and >= inventory date, distinct to order item id (as multiple rows per date on), we then determine if the order item has been returned later

			$order_items = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT DISTINCT( order_item_id ), order_id FROM `{$wpdb->prefix}wcrp_rental_products_rentals` WHERE `product_id` = %d AND `reserved_date` <= %s;",
					$product_id,
					$inventory_date
				)
			);

			// Start rental stock out orders array

			$rental_stock_out_orders = array();

			// Loop through order items

			foreach ( $order_items as $order_item ) {

				// If the order item has not been marked as returned

				if ( 'yes' !== wc_get_order_item_meta( $order_item->order_item_id, 'wcrp_rental_products_returned', true ) ) {

					// Add total quantity rented to rental stock out total, add order id to rental stock out orders array

					$rental_stock_out = (int) $rental_stock_out + (int) wc_get_order_item_meta( $order_item->order_item_id, '_qty', true );
					$rental_stock_out_orders[$order_item->order_id] = esc_html__( '#', 'wcrp-rental-products' ) . $order_item->order_id;

				}

			}

			// Final calculations

			$rental_stock_in = ( is_int( $rental_stock_total ) ? $rental_stock_total - $rental_stock_out : $rental_stock_total ); // Is int condition is because it can either be int or unlimited string

			// Row data populated and returned

			$row_data = array(
				'product'					=> self::row_data_product( $product_id ),
				'id'						=> $product_id,
				'parent_id'					=> self::row_data_parent_id( $product_id ),
				'sku'						=> self::row_data_sku( $product_id ),
				'rental_stock_total'		=> $rental_stock_total,
				'rental_stock_in'			=> $rental_stock_in,
				'rental_stock_out'			=> $rental_stock_out,
				'rental_stock_out_orders'	=> $rental_stock_out_orders,
			);

			return $row_data;

		}

		public static function row_data_product( $product_id ) {

			$product = get_the_title( $product_id );

			if ( 'product_variation' == get_post_type( $product_id ) ) {

				// If it's a variation then get_the_title above will include the main variation option in the name (but for variations with multiple options it does not include them all), so we remove this (everything after the last - character), we then add them all to the end via get_the_excerpt (WooCommerce always stores the full description in the excerpt for variations), this makes the display consistent with the rental calendar

				$product = substr( $product, 0, strrpos( $product, '-' ) );
				$product .= ' ' . esc_html__( '(', 'wcrp-rental-products' ) . get_the_excerpt( $product_id ) . esc_html__( ')', 'wcrp-rental-products' );

			}

			return $product;

		}

		public static function row_data_parent_id( $product_id ) {

			return wp_get_post_parent_id( $product_id );

		}

		public static function row_data_sku( $product_id ) {

			$sku = get_post_meta( $product_id, '_sku', true );
			$sku = ( !empty( $sku ) ? $sku : esc_html__( '—', 'wcrp-rental-products' ) );

			return $sku;

		}

	}

}
