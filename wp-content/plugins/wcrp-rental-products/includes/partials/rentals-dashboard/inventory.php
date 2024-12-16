<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

$rental_products = WCRP_Rental_Products_Rentals_Inventory::rental_products();

if ( !empty( $rental_products ) ) {

	// Sub tab

	if ( isset( $_GET['sub_tab'] ) ) {

		$sub_tab = sanitize_text_field( $_GET['sub_tab'] );

		if ( empty( $sub_tab ) ) {

			$sub_tab = 'date_view';

		}

	} else {

		$sub_tab = 'date_view';

	}

	// Inventory date

	$inventory_date = ( isset( $_GET['inventory_date'] ) ? sanitize_text_field( $_GET['inventory_date'] ) : wp_date( 'Y-m-d' ) );

	if ( strtotime( $inventory_date ) < time() ) { // Stops past dates being manually typed, date picker restricted from selecting this but technically someone can manually amend the date and historic inventory wouldn't be possible anyway due to rental stock being a live value

		$inventory_date = wp_date( 'Y-m-d' );

	}

	?>

	<div class="notice notice-info inline">
		<p>
			<?php
			if ( 'date_view' == $sub_tab ) {
				// translators: %s: inventory info link
				echo wp_kses_post( sprintf( __( 'Date view shows estimated rental stock totals for the date set. Rentals are considered in when the rental dates (including any return days thresholds) have past, regardless of if marked as returned or not. For more details on how the inventory works, %s.', 'wcrp-rental-products' ), '<a href="#TB_inline?&width=600&height=550&inlineId=wcrp-rental-products-rentals-inventory-info" class="thickbox" title="' . esc_html__( 'Inventory', 'wcrp-rental-products' ) . '">' . esc_html__( 'click here', 'wcrp-rental-products' ) . '</a>' ) );
			} elseif ( 'live_view' == $sub_tab ) {
				// translators: %s: inventory info link
				echo wp_kses_post( sprintf( __( 'Live view shows rental stock totals for the current date and time. Rentals are considered out unless marked as returned or cancelled. For more details on how the inventory works, %s.', 'wcrp-rental-products' ), '<a href="#TB_inline?&width=600&height=550&inlineId=wcrp-rental-products-rentals-inventory-info" class="thickbox" title="' . esc_html__( 'Inventory', 'wcrp-rental-products' ) . '">' . esc_html__( 'click here', 'wcrp-rental-products' ) . '</a>' ) );
			}
			?>
		</p>
	</div>
	<div id="wcrp-rental-products-rentals-inventory-sub-tabs" class="wcrp-rental-products-rentals-sub-tabs">
		<a href="<?php echo esc_url( add_query_arg( 'sub_tab', 'date_view' ) ); ?>" class="wcrp-rental-products-rentals-sub-tab<?php echo ( 'date_view' == $sub_tab ? ' wcrp-rental-products-rentals-sub-tab-active' : '' ); ?>"><?php esc_html_e( 'Date view', 'wcrp-rental-products' ); ?></a> <?php esc_html_e( '|', 'wcrp-rental-products' ); ?>
		<a href="<?php echo esc_url( remove_query_arg( 'inventory_date', add_query_arg( 'sub_tab', 'live_view' ) ) ); ?>" class="wcrp-rental-products-rentals-sub-tab<?php echo ( 'live_view' == $sub_tab ? ' wcrp-rental-products-rentals-sub-tab-active' : '' ); ?>"><?php esc_html_e( 'Live view', 'wcrp-rental-products' ); ?></a>
	</div>
	<div id="wcrp-rental-products-rentals-inventory-actions" class="wcrp-rental-products-rentals-actions">
		<div>
			<?php
			if ( 'date_view' == $sub_tab ) {
				?>
				<form id="wcrp-rental-products-rentals-inventory-date" method="get">
					<label>
						<?php esc_html_e( 'Date', 'wcrp-rental-products' ); ?>
						<input type="hidden" name="page" value="wcrp-rental-products-rentals">
						<input type="hidden" name="tab" value="inventory">
						<input type="text" name="inventory_date" class="wcrp-rental-products-single-date-picker" value="<?php echo esc_html( $inventory_date ); ?>" data-from="today">
					</label>
					<input type="submit" class="button button-primary" value="<?php esc_html_e( 'Set date', 'wcrp-rental-products' ); ?>">
				</form>
				<?php
			} elseif ( 'live_view' == $sub_tab ) {
				echo '<strong>' . esc_html( wp_date( $rental_date_format ) ) . ' ' . esc_html__( 'at', 'wcrp-rental-products' ) . ' ' . esc_html( wp_date( $rental_time_format ) ) . '</strong>';
			}
			?>
		</div>
		<div>
			<a href="javascript:location.reload();" class="button"><span class="dashicons dashicons-update"></span><?php esc_html_e( 'Refresh inventory', 'wcrp-rental-products' ); ?></a>
		</div>
	</div>
	<table id="wcrp-rental-products-rentals-inventory" class="wcrp-rental-products-datatable widefat striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Product', 'wcrp-rental-products' ); ?></th>
				<th><?php esc_html_e( 'ID', 'wcrp-rental-products' ); ?></th>
				<th><?php esc_html_e( 'Parent ID', 'wcrp-rental-products' ); ?></th>
				<th><?php esc_html_e( 'SKU', 'wcrp-rental-products' ); ?></th>
				<th><?php esc_html_e( 'Rental stock total', 'wcrp-rental-products' ); ?></th>
				<th><?php esc_html_e( 'Rental stock in', 'wcrp-rental-products' ); ?></th>
				<th><?php esc_html_e( 'Rental stock out', 'wcrp-rental-products' ); ?></th>
				<th><?php esc_html_e( 'Rental stock out orders', 'wcrp-rental-products' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ( $rental_products as $rental_product ) {

				$rental_product_variations = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT `ID`, `post_title` FROM `{$wpdb->prefix}posts` WHERE `post_parent` = %d AND `post_type` = 'product_variation'",
						$rental_product->ID
					)
				); // This has been checked for the scenario where a product might have been a variable and then changed to a simple product, the variations are deleted so this query wouldn't return any old variations as they would have been deleted

				$inventory_row_data = array();

				if ( empty( $rental_product_variations ) ) {

					if ( 'date_view' == $sub_tab ) {

						$inventory_row_data[] = WCRP_Rental_Products_Rentals_Inventory::row_data_date_view( $rental_product->ID, $inventory_date );

					} elseif ( 'live_view' == $sub_tab ) {

						$inventory_row_data[] = WCRP_Rental_Products_Rentals_Inventory::row_data_live_view( $rental_product->ID, $inventory_date );

					}

				} else {

					foreach ( $rental_product_variations as $rental_product_variation ) {

						if ( 'date_view' == $sub_tab ) {

							$inventory_row_data[] = WCRP_Rental_Products_Rentals_Inventory::row_data_date_view( $rental_product_variation->ID, $inventory_date );

						} elseif ( 'live_view' == $sub_tab ) {

							$inventory_row_data[] = WCRP_Rental_Products_Rentals_Inventory::row_data_live_view( $rental_product_variation->ID, $inventory_date );

						}

					}

				}

				if ( !empty( $inventory_row_data ) ) {

					foreach ( $inventory_row_data as $inventory_row ) {

						?>

						<tr>
							<td><?php echo wp_kses_post( $inventory_row['product'] ); ?></td>
							<td><?php echo esc_html( $inventory_row['id'] ); ?></td>
							<td><?php echo esc_html( $inventory_row['parent_id'] ); ?></td>
							<td><?php echo wp_kses_post( $inventory_row['sku'] ); ?></td>
							<td><?php echo esc_html( $inventory_row['rental_stock_total'] ); ?></td>
							<td><?php echo esc_html( $inventory_row['rental_stock_in'] ); ?></td>
							<td><?php echo esc_html( $inventory_row['rental_stock_out'] ); ?></td>
							<td><?php echo wp_kses_post( implode( "\n ", $inventory_row['rental_stock_out_orders'] ) ); // Space after \n intentional so split in export ?></td>
						</tr>

						<?php

					}

				}

			}
			?>
		</tbody>
	</table>

	<?php

} else {

	?>

	<div class="notice notice-info inline">
		<p>
			<?php
			// translators: %s: create or edit product link
			echo wp_kses_post( sprintf( __( 'No rental products configured yet. %s and set as a rental product to see it included here.', 'wcrp-rental-products' ), '<a href="' . esc_url( get_admin_url() . 'edit.php?post_type=product' ) . '">' . __( 'Create or edit a product', 'wcrp-rental-products' ) . '</a>' ) );
			?>
		</p>
	</div>

	<?php

}
