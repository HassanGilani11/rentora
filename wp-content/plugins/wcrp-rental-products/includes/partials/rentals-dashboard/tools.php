<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="notice notice-warning inline">
	<p><?php esc_html_e( 'Always take a backup and test on a staging/development environment before using these tools.', 'wcrp-rental-products' ); ?></p>
</div>
<div class="wcrp-rental-products-rentals-tools-section-wrap">
	<div id="wcrp-rental-products-rentals-tools-clone-rental-product-options" class="wcrp-rental-products-rentals-tools-section">
		<form method="post">
			<?php wp_nonce_field( 'wcrp_rental_products_rentals_tools_clone_rental_product_options', 'wcrp_rental_products_rentals_tools_clone_rental_product_options_nonce' ); ?>
			<h2><?php esc_html_e( 'Clone Rental Product Options', 'wcrp-rental-products' ); ?></h2>
			<p><?php esc_html_e( 'Clones rental product options from a specific product to another product or set of products. This does not clone rental price or rental stock fields as these are generally unique to each product, these can be bulk changed using the bulk edit tools within the dashboard or via import/export.', 'wcrp-rental-products' ); ?></p>
			<p><?php esc_html_e( 'Cloning will change any existing stock based options on the destination product(s) and its variations. This effectively replicates the stock option changes which occur automatically in the background when editing a rental product via the dashboard.', 'wcrp-rental-products' ); ?></p>
			<div>
				<label>
					<p><strong><?php esc_html_e( 'Clone rental product options from this rental product:', 'wcrp-rental-products' ); ?></strong></p>
					<select id="wcrp-rental-products-rentals-tools-clone-rental-product-options-from" class="wcrp-rental-products-select2-ajax-rentals-tools-clone-rental-product-options-from" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_from">
						<option value=""><?php esc_html_e( 'Select a rental product', 'wcrp-rental-products' ); ?></option>
					</select>
				</label>
			</div>
			<div id="wcrp-rental-products-rentals-tools-clone-rental-product-options-which">
				<div>
					<p><strong><?php esc_html_e( 'Which rental product options should be cloned:', 'wcrp-rental-products' ); ?></strong></p>
					<input type="checkbox" id="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-rental-product" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_which[]" value="rental_product">
					<label for="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-rental-product"><?php esc_html_e( 'Rental product', 'wcrp-rental-products' ); ?></label>
					<p class="description"><?php esc_html_e( 'No, yes - rental only, yes - rental or purchase', 'wcrp-rental-products' ); ?></p>
					<input type="checkbox" id="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-pricing" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_which[]" value="pricing">
					<label for="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-pricing"><?php esc_html_e( 'Pricing related', 'wcrp-rental-products' ); ?></label>
					<p class="description"><?php esc_html_e( 'Pricing type, pricing period (days), pricing period multiples, pricing period multiples maximum, pricing period additional selections, pricing tiers, price + additional periods %, price + additional period %, price display override, total overrides, advanced pricing, minimum days and maximum days', 'wcrp-rental-products' ); ?></p>
					<input type="checkbox" id="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-pick-up-return" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_which[]" value="pick_up_return">
					<label for="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-pick-up-return"><?php esc_html_e( 'Pick up/return related', 'wcrp-rental-products' ); ?></label>
					<p class="description"><?php esc_html_e( 'Return days threshold, in person pick up/return, in person pick up/return time restrictions, in person return date, in person pick up times/fees (same day), in person pick up times/fees if single day rental (same day), in person return times/fees (same day), in person return times/fees if single day rental (same day), in person pick up times/fees (next day) and in person return times/fees (next day)', 'wcrp-rental-products' ); ?></p>
					<input type="checkbox" id="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-start-day" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_which[]" value="start_day">
					<label for="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-start-day"><?php esc_html_e( 'Start day', 'wcrp-rental-products' ); ?></label><br>
					<input type="checkbox" id="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-start-days-threshold" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_which[]" value="start_days_threshold">
					<label for="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-start-days-threshold"><?php esc_html_e( 'Start days threshold', 'wcrp-rental-products' ); ?></label><br>
					<input type="checkbox" id="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-earliest-available-date" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_which[]" value="earliest_available_date">
					<label for="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-earliest-available-date"><?php esc_html_e( 'Earliest available date', 'wcrp-rental-products' ); ?></label><br>
					<input type="checkbox" id="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-disable-rental-dates" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_which[]" value="disable_rental_dates">
					<label for="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-disable-rental-dates"><?php esc_html_e( 'Disable rental dates', 'wcrp-rental-products' ); ?></label><br>
					<input type="checkbox" id="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-disable-rental-days" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_which[]" value="disable_rental_days">
					<label for="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-disable-rental-days"><?php esc_html_e( 'Disable rental days', 'wcrp-rental-products' ); ?></label><br>
					<input type="checkbox" id="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-disable-rental-start-end-dates" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_which[]" value="disable_rental_start_end_dates">
					<label for="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-disable-rental-start-end-dates"><?php esc_html_e( 'Disable rental start/end dates', 'wcrp-rental-products' ); ?></label><br>
					<input type="checkbox" id="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-disable-rental-start-end-days" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_which[]" value="disable_rental_start_end_days">
					<label for="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-disable-rental-start-end-days"><?php esc_html_e( 'Disable rental start/end days', 'wcrp-rental-products' ); ?></label><br>
					<input type="checkbox" id="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-disable-rental-start-end-days-type" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_which[]" value="disable_rental_start_end_days_type">
					<label for="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-disable-rental-start-end-days-type"><?php esc_html_e( 'Disable rental start/end days type', 'wcrp-rental-products' ); ?></label><br>
					<input type="checkbox" id="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-security-deposit-amount" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_which[]" value="security_deposit_amount">
					<label for="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-security-deposit-amount"><?php esc_html_e( 'Security deposit amount', 'wcrp-rental-products' ); ?></label><br>
					<input type="checkbox" id="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-security-deposit-calculation" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_which[]" value="security_deposit_calculation">
					<label for="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-security-deposit-calculation"><?php esc_html_e( 'Security deposit calculation', 'wcrp-rental-products' ); ?></label><br>
					<input type="checkbox" id="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-security-deposit-tax-status" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_which[]" value="security_deposit_tax_status">
					<label for="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-security-deposit-tax-status"><?php esc_html_e( 'Security deposit tax status', 'wcrp-rental-products' ); ?></label><br>
					<input type="checkbox" id="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-security-deposit-tax-class" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_which[]" value="security_deposit_tax_class">
					<label for="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-security-deposit-tax-class"><?php esc_html_e( 'Security deposit tax class', 'wcrp-rental-products' ); ?></label><br>
					<input type="checkbox" id="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-non-refundable">
					<label for="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-non-refundable"><?php esc_html_e( 'Security deposit non-refundable', 'wcrp-rental-products' ); ?></label><br>
					<input type="checkbox" id="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-multiply-addons-total-by-number-of-days-selected" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_which[]" value="multiply_addons_total_by_number_of_days_selected">
					<label for="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-multiply-addons-total-by-number-of-days-selected"><?php esc_html_e( 'Multiply add-ons total by number of days selected', 'wcrp-rental-products' ); ?></label><br>
					<input type="checkbox" id="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-disable-addons-rental-purchase-rental" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_which[]" value="disable_addons_rental_purchase_rental">
					<label for="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-disable-addons-rental-purchase-rental"><?php esc_html_e( 'Disable add-ons for rental part of rental or purchase products', 'wcrp-rental-products' ); ?></label><br>
					<input type="checkbox" id="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-disable-addons-rental-purchase-purchase" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_which[]" value="disable_addons_rental_purchase_purchase">
					<label for="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-disable-addons-rental-purchase-purchase"><?php esc_html_e( 'Disable add-ons for purchase part of rental or purchase products', 'wcrp-rental-products' ); ?></label><br>
					<input type="checkbox" id="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-months" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_which[]" value="months">
					<label for="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-months"><?php esc_html_e( 'Months', 'wcrp-rental-products' ); ?></label><br>
					<input type="checkbox" id="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-columns" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_which[]" value="columns">
					<label for="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-columns"><?php esc_html_e( 'Columns', 'wcrp-rental-products' ); ?></label><br>
					<input type="checkbox" id="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-inline" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_which[]" value="inline">
					<label for="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-inline"><?php esc_html_e( 'Inline', 'wcrp-rental-products' ); ?></label><br>
					<input type="checkbox" id="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-rental-information" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_which[]" value="rental_information">
					<label for="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-rental-information"><?php esc_html_e( 'Rental information', 'wcrp-rental-products' ); ?></label><br>
					<input type="checkbox" id="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-rental-purchase-rental-tax-override" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_which[]" value="rental_purchase_rental_tax_override">
					<label for="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-rental-purchase-rental-tax-override"><?php esc_html_e( 'Rental or purchase - rental tax override', 'wcrp-rental-products' ); ?></label><br>
					<input type="checkbox" id="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-rental-purchase-rental-tax-override-status" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_which[]" value="rental_purchase_rental_tax_override_status">
					<label for="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-rental-purchase-rental-tax-override-status"><?php esc_html_e( 'Rental or purchase - rental tax override status', 'wcrp-rental-products' ); ?></label><br>
					<input type="checkbox" id="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-rental-purchase-rental-tax-override-class" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_which[]" value="rental_purchase_rental_tax_override_class">
					<label for="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-rental-purchase-rental-tax-override-class"><?php esc_html_e( 'Rental or purchase - rental tax override class', 'wcrp-rental-products' ); ?></label><br>
					<input type="checkbox" id="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-rental-purchase-rental-shipping-override" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_which[]" value="rental_purchase_rental_shipping_override">
					<label for="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-rental-purchase-rental-shipping-override"><?php esc_html_e( 'Rental or purchase - rental shipping override', 'wcrp-rental-products' ); ?></label><br>
					<input type="checkbox" id="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-rental-purchase-rental-shipping-override-class" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_which[]" value="rental_purchase_rental_shipping_override_class">
					<label for="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-rental-purchase-rental-shipping-override-class"><?php esc_html_e( 'Rental or purchase - rental shipping override class', 'wcrp-rental-products' ); ?></label><br>
				</div>
				<p><a id="wcrp-rental-products-rentals-tools-clone-rental-product-options-which-select-deselect" class="button button-small" data-action="select"><?php esc_html_e( 'Select all', 'wcrp-rental-products' ); ?></a></p>
				<div class="notice notice-info inline">
					<p><?php esc_html_e( 'Rental product options selected above will only be cloned if the destination product(s) are simple or variable products (the supported rental product types). Grouped products do not apply as rental options are not set on grouped products, they are just a collection of other products. The clone does not apply to variations of a variable product.', 'wcrp-rental-products' ); ?></p>
				</div>
			</div>
			<div id="wcrp-rental-products-rentals-tools-clone-rental-product-options-to">
				<div>
					<p><strong><?php esc_html_e( 'Clone rental product options to:', 'wcrp-rental-products' ); ?></strong></p>
					<input type="radio" id="wcrp-rental-products-rentals-tools-clone-rental-product-options-to-all-products" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_to" value="all_products" checked>
					<label for="wcrp-rental-products-rentals-tools-clone-rental-product-options-to-all-products"><?php esc_html_e( 'All products', 'wcrp-rental-products' ); ?></label><br>
					<input type="radio" id="wcrp-rental-products-rentals-tools-clone-rental-product-options-to-all-rental-products" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_to" value="all_rental_products">
					<label for="wcrp-rental-products-rentals-tools-clone-rental-product-options-to-all-rental-products"><?php esc_html_e( 'All rental products', 'wcrp-rental-products' ); ?></label><br>
					<input type="radio" id="wcrp-rental-products-rentals-tools-clone-rental-product-options-to-all-products-in-specific-categories" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_to" value="all_products_in_specific_categories">
					<label for="wcrp-rental-products-rentals-tools-clone-rental-product-options-to-all-products-in-specific-categories"><?php esc_html_e( 'All products in specific categories', 'wcrp-rental-products' ); ?></label><br>
					<input type="radio" id="wcrp-rental-products-rentals-tools-clone-rental-product-options-to-all-rental-products-in-specific-categories" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_to" value="all_rental_products_in_specific_categories">
					<label for="wcrp-rental-products-rentals-tools-clone-rental-product-options-to-all-rental-products-in-specific-categories"><?php esc_html_e( 'All rental products in specific categories', 'wcrp-rental-products' ); ?></label><br>
					<input type="radio" id="wcrp-rental-products-rentals-tools-clone-rental-product-options-to-products" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_to" value="products">
					<label for="wcrp-rental-products-rentals-tools-clone-rental-product-options-to-products"><?php esc_html_e( 'Specific products', 'wcrp-rental-products' ); ?></label>
				</div>
				<div id="wcrp-rental-products-rentals-tools-clone-rental-product-options-to-categories-select">
					<label>
						<p><strong><?php esc_html_e( 'Select specific categories:', 'wcrp-rental-products' ); ?></strong></p>
						<select class="wcrp-rental-products-select2-ajax-rentals-tools-clone-rental-product-options-to-categories-select" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_to_categories_select[]" multiple>
							<option value=""><?php esc_html_e( 'Select categories', 'wcrp-rental-products' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Start typing a category to search.', 'wcrp-rental-products' ); ?></p>
					</label>
				</div>
				<div id="wcrp-rental-products-rentals-tools-clone-rental-product-options-to-products-select">
					<label>
						<p><strong><?php esc_html_e( 'Select specific products:', 'wcrp-rental-products' ); ?></strong></p>
						<select class="wcrp-rental-products-select2-ajax-rentals-tools-clone-rental-product-options-to-products-select" name="wcrp_rental_products_rentals_tools_clone_rental_product_options_to_products_select[]" multiple>
							<option value=""><?php esc_html_e( 'Select products', 'wcrp-rental-products' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Start typing a product to search.', 'wcrp-rental-products' ); ?></p>
					</label>
				</div>
				<div class="notice notice-warning inline">
					<p><?php esc_html_e( 'If the clone to option selected above potentially includes non-rental products that were created while the Rental Products extension was not active it is strongly recommended that all the rental product options are cloned.', 'wcrp-rental-products' ); ?></p>
				</div>
				<button id="wcrp-rental-products-rentals-tools-clone-rental-product-options-submit" class="button button-primary" type="submit" data-alert-text="<?php esc_html_e( 'Are you sure you want to clone rental product options to the selected product(s)? Always take a backup and test on a staging/development environment before using this tool.', 'wcrp-rental-products' ); ?>"><?php esc_html_e( 'Clone', 'wcrp-rental-products' ); ?></button>
			</div>
		</form>
	</div>
	<div id="wcrp-rental-products-rentals-tools-rental-product-import-and-export-information" class="wcrp-rental-products-rentals-tools-section">
		<h2><?php esc_html_e( 'Rental Product Import and Export Information', 'wcrp-rental-products' ); ?></h2>
		<p>
			<?php
			// translators: %s: export custom meta option title
			echo sprintf( esc_html__( 'To import and/or export rental product data you can use the standard WooCommerce product import and export tools (use the buttons below to access). Rental products are no different to non-rental products, they just have some additional meta attached to them. Use the "%s" option when exporting to see the additional meta.', 'wcrp-rental-products' ), esc_html__( 'Export custom meta?', 'woocommerce' ) ); // WooCommerce text domain as a WooCommerce string
			?>
		</p>
		<p><?php esc_html_e( 'Please note that if you use a different import/export method to the standard WooCommerce product import and export tools the column/values referenced below may be interpreted differently.', 'wcrp-rental-products' ); ?></p>
		<p><?php echo sprintf( esc_html__( 'When importing rental products you should ensure the data listed below is correct, if this data is not correct it is highly likely you will see product data issues:', 'wcrp-rental-products' ), '<code>_wcrp_rental_products</code>' ); ?></p>
		<div id="wcrp-rental-products-rentals-tools-rental-product-import-and-export-information-instructions">
			<p><strong><?php esc_html_e( 'Rules below apply to any rental product, which is any product with:', 'wcrp-rental-products' ); ?></strong></p>
			<ul>
				<li><?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_rental<?php esc_html_e( '"', 'wcrp-rental-products' ); ?> <?php esc_html_e( '=', 'wcrp-rental-products' ); ?> yes <?php esc_html_e( '(Rental only)', 'wcrp-rental-products' ); ?></li>
				<li><?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_rental<?php esc_html_e( '"', 'wcrp-rental-products' ); ?> <?php esc_html_e( '=', 'wcrp-rental-products' ); ?> yes_purchase <?php esc_html_e( '(Rental or purchase)', 'wcrp-rental-products' ); ?></li>
				<li><?php esc_html_e( 'Any variations where the variable (parent) product has the meta above', 'wcrp-rental-products' ); ?></li>
			</ul>
			<hr>
			<p><strong><?php esc_html_e( 'If', 'wcrp-rental-products' ); ?> <?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_rental<?php esc_html_e( '"', 'wcrp-rental-products' ); ?> <?php esc_html_e( '=', 'wcrp-rental-products' ); ?> yes<?php esc_html_e( ':', 'wcrp-rental-products' ); ?></strong></p>
			<ul>
				<li><?php esc_html_e( '"', 'wcrp-rental-products' ); ?><?php esc_html_e( 'In stock?', 'woocommerce' ); // WooCommerce text domain ?><?php esc_html_e( '"', 'wcrp-rental-products' ); ?><?php esc_html_e( ':', 'wcrp-rental-products' ); ?> 1</li>
				<li><?php esc_html_e( '"', 'wcrp-rental-products' ); ?><?php esc_html_e( 'Stock', 'woocommerce' ); // WooCommerce text domain ?><?php esc_html_e( '"', 'wcrp-rental-products' ); ?><?php esc_html_e( ':', 'wcrp-rental-products' ); ?> <?php esc_html_e( 'empty', 'wcrp-rental-products' ); ?></li>
				<li><?php esc_html_e( '"', 'wcrp-rental-products' ); ?><?php esc_html_e( 'Backorders allowed?', 'woocommerce' ); // WooCommerce text domain ?><?php esc_html_e( '"', 'wcrp-rental-products' ); ?><?php esc_html_e( ':', 'wcrp-rental-products' ); ?> 0</li>
			</ul>
			<p><?php esc_html_e( 'If a variable product all variations must also use the above.', 'wcrp-rental-products' ); ?></p>
			<hr>
			<p><strong><?php esc_html_e( 'If', 'wcrp-rental-products' ); ?> <?php esc_html_e( '"', 'wcrp-rental-products' ); ?><?php esc_html_e( 'Type', 'woocommerce' ); // WooCommerce text domain ?><?php esc_html_e( '"', 'wcrp-rental-products' ); ?> <?php esc_html_e( '=', 'wcrp-rental-products' ); ?> variation</strong></p>
			<ul>
				<li>
					<?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_<?php esc_html_e( '"', 'wcrp-rental-products' ); ?> <?php esc_html_e( 'prefixed meta should not exist/be empty with the exception of this optional meta:', 'wcrp-rental-products' ); ?>
					<ul>
						<li><?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_rental_purchase_price<?php esc_html_e( '"', 'wcrp-rental-products' ); ?></li>
						<li><?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_rental_stock<?php esc_html_e( '"', 'wcrp-rental-products' ); ?></li>
						<li><?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_pricing_period_additional_selections<?php esc_html_e( '"', 'wcrp-rental-products' ); ?></li>
						<li><?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_total_overrides<?php esc_html_e( '"', 'wcrp-rental-products' ); ?></li>
						<li><?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_security_deposit_amount<?php esc_html_e( '"', 'wcrp-rental-products' ); ?></li>
					</ul>
				</li>
			</ul>
			<hr>
			<p><strong><?php esc_html_e( 'If', 'wcrp-rental-products' ); ?> <?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_pricing_type<?php esc_html_e( '"', 'wcrp-rental-products' ); ?> <?php esc_html_e( '=', 'wcrp-rental-products' ); ?> fixed<?php esc_html_e( ':', 'wcrp-rental-products' ); ?></strong></p>
			<ul>
				<li><?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_pricing_period<?php esc_html_e( '"', 'wcrp-rental-products' ); ?><?php esc_html_e( ':', 'wcrp-rental-products' ); ?> 1</li>
				<li><?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_pricing_period_multiples<?php esc_html_e( '"', 'wcrp-rental-products' ); ?><?php esc_html_e( ':', 'wcrp-rental-products' ); ?> no</li>
				<li><?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_price_additional_periods_percent<?php esc_html_e( '"', 'wcrp-rental-products' ); ?><?php esc_html_e( ':', 'wcrp-rental-products' ); ?> no</li>
			</ul>
			<hr>
			<p><strong><?php esc_html_e( 'If', 'wcrp-rental-products' ); ?> <?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_pricing_type<?php esc_html_e( '"', 'wcrp-rental-products' ); ?> <?php esc_html_e( '=', 'wcrp-rental-products' ); ?> period <?php esc_html_e( 'and', 'wcrp-rental-products' ); ?> <?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_pricing_period<?php esc_html_e( '"', 'wcrp-rental-products' ); ?> <?php esc_html_e( '=', 'wcrp-rental-products' ); ?> 1<?php esc_html_e( ':', 'wcrp-rental-products' ); ?></strong></p>
			<ul>
				<li><?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_pricing_period_multiples<?php esc_html_e( '"', 'wcrp-rental-products' ); ?><?php esc_html_e( ':', 'wcrp-rental-products' ); ?> no</li>
			</ul>
			<hr>
			<p><strong><?php esc_html_e( 'If', 'wcrp-rental-products' ); ?> <?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_pricing_type<?php esc_html_e( '"', 'wcrp-rental-products' ); ?> <?php esc_html_e( '=', 'wcrp-rental-products' ); ?> period <?php esc_html_e( 'and', 'wcrp-rental-products' ); ?> <?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_pricing_period<?php esc_html_e( '"', 'wcrp-rental-products' ); ?> <?php esc_html_e( '>', 'wcrp-rental-products' ); ?> 1<?php esc_html_e( ':', 'wcrp-rental-products' ); ?></strong></p>
			<ul>
				<li><?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_minimum_days<?php esc_html_e( '"', 'wcrp-rental-products' ); ?><?php esc_html_e( ':', 'wcrp-rental-products' ); ?> <?php esc_html_e( 'Same value as', 'wcrp-rental-products' ); ?> <?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_pricing_period<?php esc_html_e( '"', 'wcrp-rental-products' ); ?></li>
				<li><?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_maximum_days<?php esc_html_e( '"', 'wcrp-rental-products' ); ?><?php esc_html_e( ':', 'wcrp-rental-products' ); ?> <?php esc_html_e( 'Same value as', 'wcrp-rental-products' ); ?> <?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_pricing_period<?php esc_html_e( '"', 'wcrp-rental-products' ); ?></li>
			</ul>
			<hr>
			<p><strong><?php esc_html_e( 'If', 'wcrp-rental-products' ); ?> <?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_pricing_type<?php esc_html_e( '"', 'wcrp-rental-products' ); ?> <?php esc_html_e( '=', 'wcrp-rental-products' ); ?> period <?php esc_html_e( 'and', 'wcrp-rental-products' ); ?> <?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_pricing_period<?php esc_html_e( '"', 'wcrp-rental-products' ); ?> <?php esc_html_e( '>', 'wcrp-rental-products' ); ?> 1 <?php esc_html_e( 'and', 'wcrp-rental-products' ); ?> <?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_pricing_period_multiples<?php esc_html_e( '"', 'wcrp-rental-products' ); ?> <?php esc_html_e( '=', 'wcrp-rental-products' ); ?> no<?php esc_html_e( ':', 'wcrp-rental-products' ); ?></strong></p>
			<ul>
				<li><?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_pricing_tiers<?php esc_html_e( '"', 'wcrp-rental-products' ); ?><?php esc_html_e( ':', 'wcrp-rental-products' ); ?> no</li>
				<li><?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_price_additional_periods_percent<?php esc_html_e( '"', 'wcrp-rental-products' ); ?><?php esc_html_e( ':', 'wcrp-rental-products' ); ?> no</li>
			</ul>
			<hr>
			<p><strong><?php esc_html_e( 'If', 'wcrp-rental-products' ); ?> <?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_pricing_type<?php esc_html_e( '"', 'wcrp-rental-products' ); ?> <?php esc_html_e( '=', 'wcrp-rental-products' ); ?> period_selection<?php esc_html_e( ':', 'wcrp-rental-products' ); ?></strong></p>
			<ul>
				<li><?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_pricing_period_multiples<?php esc_html_e( '"', 'wcrp-rental-products' ); ?><?php esc_html_e( ':', 'wcrp-rental-products' ); ?> no</li>
				<li><?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_pricing_tiers<?php esc_html_e( '"', 'wcrp-rental-products' ); ?><?php esc_html_e( ':', 'wcrp-rental-products' ); ?> no</li>
				<li><?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_price_additional_periods_percent<?php esc_html_e( '"', 'wcrp-rental-products' ); ?><?php esc_html_e( ':', 'wcrp-rental-products' ); ?> no</li>
				<li><?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_minimum_days<?php esc_html_e( '"', 'wcrp-rental-products' ); ?><?php esc_html_e( ':', 'wcrp-rental-products' ); ?> <?php esc_html_e( 'Same value as', 'wcrp-rental-products' ); ?> <?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_pricing_period<?php esc_html_e( '"', 'wcrp-rental-products' ); ?></li>
				<li><?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_maximum_days<?php esc_html_e( '"', 'wcrp-rental-products' ); ?><?php esc_html_e( ':', 'wcrp-rental-products' ); ?> <?php esc_html_e( 'Same value as', 'wcrp-rental-products' ); ?> <?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_pricing_period<?php esc_html_e( '"', 'wcrp-rental-products' ); ?></li>
			</ul>
			<hr>
			<p><strong><?php esc_html_e( 'If', 'wcrp-rental-products' ); ?> <?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_in_person_pick_up_return<?php esc_html_e( '"', 'wcrp-rental-products' ); ?> <?php esc_html_e( '=', 'wcrp-rental-products' ); ?> yes<?php esc_html_e( ':', 'wcrp-rental-products' ); ?></strong></p>
			<ul>
				<li><?php esc_html_e( '"', 'wcrp-rental-products' ); ?>_wcrp_rental_products_return_days_threshold<?php esc_html_e( '"', 'wcrp-rental-products' ); ?><?php esc_html_e( ':', 'wcrp-rental-products' ); ?> 0</li>
			</ul>
		</div>
		<div class="notice notice-info inline">
			<p><?php esc_html_e( 'Before importing please ensure you have read all the information above. Remember that instead of importing you can create/edit a rental product and then assign that rental product\'s options to one or more other products using the clone rental product options tool.', 'wcrp-rental-products' ); ?></p>
		</div>
		<a href="edit.php?post_type=product&page=product_importer" class="button button-primary"><?php esc_html_e( 'Import products', 'wcrp-rental-products' ); ?></a>
		<a href="edit.php?post_type=product&page=product_exporter" class="button button-primary"><?php esc_html_e( 'Export products', 'wcrp-rental-products' ); ?></a>
	</div>
	<div id="wcrp-rental-products-rentals-tools-rental-products-debug" class="wcrp-rental-products-rentals-tools-section">
		<form method="post">
			<?php wp_nonce_field( 'wcrp_rental_products_rentals_tools_rental_products_debug', 'wcrp_rental_products_rentals_tools_rental_products_debug_nonce' ); ?>
			<h2><?php esc_html_e( 'Rental Products Debug', 'wcrp-rental-products' ); ?></h2>
			<p><?php esc_html_e( 'Rental products require some core WooCommerce product meta and rental specific meta to be set correctly. When you add/edit products through the dashboard these are taken care of automatically. If you have attempted to import product data and you have some product data issues you can use this tool to find rental products with potential issues with an option to try fixing the issues automatically.', 'wcrp-rental-products' ); ?></p>
			<div class="notice notice-info inline">
				<p><?php esc_html_e( 'Note that this checks for common issues but is not a guaranteed means of finding and/or fixing issues. If you still have issues after using this tool we recommend deleting any products with issues and recreating them or alternatively review the data directly in the database for incorrect values. See rental product import and export information for correct values.', 'wcrp-rental-products' ); ?></p>
			</div>

			<?php

			$run_markup = '<button id="wcrp-rental-products-rentals-tools-rental-products-debug-run" name="wcrp_rental_products_rentals_tools_rental_products_debug_run" type="submit" class="button button-primary">' . esc_html__( 'Run debug', 'wcrp-rental-products' ) . '</button>';

			if ( isset( $_POST['wcrp_rental_products_rentals_tools_rental_products_debug_nonce'] ) ) {

				if ( wp_verify_nonce( sanitize_key( $_POST['wcrp_rental_products_rentals_tools_rental_products_debug_nonce'] ), 'wcrp_rental_products_rentals_tools_rental_products_debug' ) ) {

					if ( isset( $_POST['wcrp_rental_products_rentals_tools_rental_products_debug_run'] ) || isset( $_POST['wcrp_rental_products_rentals_tools_rental_products_debug_fix'] ) ) {

						if ( isset( $_POST['wcrp_rental_products_rentals_tools_rental_products_debug_fix'] ) ) {

							$fix = true;

						} else {

							$fix = false;

						}

						$found_issues = array();

						$products = get_posts(
							array(
								'posts_per_page'	=> -1,
								'post_type'			=> 'product',
								'post_status'		=> get_post_stati(), // Ensures auto-drafts are included ('any' wouldn't include this)
								'fields'			=> 'ids',
								'meta_query'		=> array(
									array(
										'key'		=> '_wcrp_rental_products_rental',
										'value'		=> array(
											'yes',
											'yes_purchase'
										),
										'compare'	=> 'IN',
								   )
								),
							)
						);

						if ( !empty( $products ) ) {

							$default_rental_options = wcrp_rental_products_default_rental_options();

							foreach ( $products as $product ) {

								$product_type = wp_get_post_terms( $product, 'product_type', array( 'fields' => 'slugs' ) );
								$manage_stock = get_post_meta( $product, '_manage_stock', true );
								$stock_status = get_post_meta( $product, '_stock_status', true );
								$backorders = get_post_meta( $product, '_backorders', true );

								$rental = get_post_meta( $product, '_wcrp_rental_products_rental', true );
								$rental = ( '' !== $rental ? $rental : $default_rental_options['_wcrp_rental_products_rental'] );

								$pricing_type = get_post_meta( $product, '_wcrp_rental_products_pricing_type', true );
								$pricing_type = ( '' !== $pricing_type ? $pricing_type : $default_rental_options['_wcrp_rental_products_pricing_type'] );

								$pricing_period = get_post_meta( $product, '_wcrp_rental_products_pricing_period', true );
								$pricing_period = ( '' !== $pricing_period ? $pricing_period : $default_rental_options['_wcrp_rental_products_pricing_period'] );

								$pricing_period_multiples = get_post_meta( $product, '_wcrp_rental_products_pricing_period_multiples', true );
								$pricing_period_multiples = ( '' !== $pricing_period_multiples ? $pricing_period_multiples : $default_rental_options['_wcrp_rental_products_pricing_period_multiples'] );

								$pricing_tiers = get_post_meta( $product, '_wcrp_rental_products_pricing_tiers', true );
								$pricing_tiers = ( '' !== $pricing_tiers ? $pricing_tiers : $default_rental_options['_wcrp_rental_products_pricing_tiers'] );

								$price_additional_periods_percent = get_post_meta( $product, '_wcrp_rental_products_price_additional_periods_percent', true );
								$price_additional_periods_percent = ( '' !== $price_additional_periods_percent ? $price_additional_periods_percent : $default_rental_options['_wcrp_rental_products_price_additional_periods_percent'] );

								$in_person_pick_up_return = get_post_meta( $product, '_wcrp_rental_products_in_person_pick_up_return', true );
								$in_person_pick_up_return = ( '' !== $in_person_pick_up_return ? $in_person_pick_up_return : $default_rental_options['_wcrp_rental_products_in_person_pick_up_return'] );

								$minimum_days = get_post_meta( $product, '_wcrp_rental_products_minimum_days', true );
								$minimum_days = ( '' !== $minimum_days ? $minimum_days : $default_rental_options['_wcrp_rental_products_minimum_days'] );

								$maximum_days = get_post_meta( $product, '_wcrp_rental_products_maximum_days', true );
								$maximum_days = ( '' !== $maximum_days ? $maximum_days : $default_rental_options['_wcrp_rental_products_maximum_days'] );

								$return_days_threshold = get_post_meta( $product, '_wcrp_rental_products_return_days_threshold', true );
								$return_days_threshold = ( '' !== $return_days_threshold ? $return_days_threshold : $default_rental_options['_wcrp_rental_products_return_days_threshold'] );

								// Product type check

								if ( !empty( $product_type ) ) {

									$product_type = $product_type[0];

									if ( !in_array( $product_type, array( 'simple', 'variable' ) ) ) { // Log issue if the products are not simple or variable products as these are the required product types for rentals, grouped products not included as rental options are not set on them, they are just a collection of other products. This condition is included as a product could have got the wrong product type/rental option imported, if it's not one of the supported types _wcrp_rental_products_rental should be blank

										if ( true == $fix ) {

											update_post_meta( $product, '_wcrp_rental_products_rental', '' );

										} else {

											$found_issues[$product][] = array(
												'title'		=> get_the_title( $product ),
												'edit_link'	=> get_edit_post_link( $product ),
												'issue'		=> esc_html__( 'Rental enabled on unsupported product type', 'wcrp-rental-products' ),
											);

										}

									}

								}

								// Pricing type checks

								if ( 'fixed' == $pricing_type ) {

									if ( '1' !== $pricing_period ) {

										if ( true == $fix ) {

											update_post_meta( $product, '_wcrp_rental_products_pricing_period', '1' );

										} else {

											$found_issues[$product][] = array(
												'title'		=> get_the_title( $product ),
												'edit_link'	=> get_edit_post_link( $product ),
												'issue'		=> esc_html__( 'Rental with fixed pricing type and pricing period not 1', 'wcrp-rental-products' ),
											);

										}

									}

									if ( 'yes' == $pricing_period_multiples ) {

										if ( true == $fix ) {

											update_post_meta( $product, '_wcrp_rental_products_pricing_period_multiples', 'no' );

										} else {

											$found_issues[$product][] = array(
												'title'		=> get_the_title( $product ),
												'edit_link'	=> get_edit_post_link( $product ),
												'issue'		=> esc_html__( 'Rental with fixed pricing type and pricing period multiples enabled', 'wcrp-rental-products' ),
											);

										}

									}

									if ( 'yes' == $price_additional_periods_percent ) {

										if ( true == $fix ) {

											update_post_meta( $product, '_wcrp_rental_products_price_additional_periods_percent', 'no' );

										} else {

											$found_issues[$product][] = array(
												'title'		=> get_the_title( $product ),
												'edit_link'	=> get_edit_post_link( $product ),
												'issue'		=> esc_html__( 'Rental with fixed pricing type and price + additional periods % enabled', 'wcrp-rental-products' ),
											);

										}

									}

								} elseif ( 'period' == $pricing_type ) {

									if ( (int) $pricing_period > 1 ) {

										if ( $minimum_days !== $pricing_period ) {

											if ( true == $fix ) {

												update_post_meta( $product, '_wcrp_rental_products_minimum_days', $pricing_period );

											} else {

												$found_issues[$product][] = array(
													'title'		=> get_the_title( $product ),
													'edit_link'	=> get_edit_post_link( $product ),
													'issue'		=> esc_html__( 'Rental with period pricing type, pricing period greater than 1 and minimum days incorrect', 'wcrp-rental-products' ),
												);

											}

										}

										if ( $maximum_days !== $pricing_period ) {

											if ( true == $fix ) {

												update_post_meta( $product, '_wcrp_rental_products_maximum_days', $pricing_period );

											} else {

												$found_issues[$product][] = array(
													'title'		=> get_the_title( $product ),
													'edit_link'	=> get_edit_post_link( $product ),
													'issue'		=> esc_html__( 'Rental with period pricing type, pricing period greater than 1 and maximum days incorrect', 'wcrp-rental-products' ),
												);

											}

										}

										if ( 'no' == $pricing_period_multiples ) {

											if ( 'yes' == $pricing_tiers ) {

												if ( true == $fix ) {

													update_post_meta( $product, '_wcrp_rental_products_pricing_tiers', 'no' );

												} else {

													$found_issues[$product][] = array(
														'title'		=> get_the_title( $product ),
														'edit_link'	=> get_edit_post_link( $product ),
														'issue'		=> esc_html__( 'Rental with period pricing type, pricing period greater than 1, pricing period multiples disabled and pricing tiers enabled', 'wcrp-rental-products' ),
													);

												}

											}

											if ( 'yes' == $price_additional_periods_percent ) {

												if ( true == $fix ) {

													update_post_meta( $product, '_wcrp_rental_products_price_additional_periods_percent', 'no' );

												} else {

													$found_issues[$product][] = array(
														'title'		=> get_the_title( $product ),
														'edit_link'	=> get_edit_post_link( $product ),
														'issue'		=> esc_html__( 'Rental with period pricing type, pricing period greater than 1, pricing period multiples disabled and price + additional periods % enabled', 'wcrp-rental-products' ),
													);

												}

											}

										}

									} elseif ( 1 == (int) $pricing_period ) {

										if ( 'yes' == $pricing_period_multiples ) {

											if ( true == $fix ) {

												update_post_meta( $product, '_wcrp_rental_products_pricing_period_multiples', 'no' );

											} else {

												$found_issues[$product][] = array(
													'title'		=> get_the_title( $product ),
													'edit_link'	=> get_edit_post_link( $product ),
													'issue'		=> esc_html__( 'Rental with period pricing type, pricing period 1 and pricing period multiples enabled', 'wcrp-rental-products' ),
												);

											}

										}

									}

								} elseif ( 'period_selection' == $pricing_type ) {

									if ( 'yes' == $pricing_period_multiples ) {

										if ( true == $fix ) {

											update_post_meta( $product, '_wcrp_rental_products_pricing_period_multiples', 'no' );

										} else {

											$found_issues[$product][] = array(
												'title'		=> get_the_title( $product ),
												'edit_link'	=> get_edit_post_link( $product ),
												'issue'		=> esc_html__( 'Rental with period selection pricing type and pricing period multiples enabled', 'wcrp-rental-products' ),
											);

										}

									}

									if ( 'yes' == $pricing_tiers ) {

										if ( true == $fix ) {

											update_post_meta( $product, '_wcrp_rental_products_pricing_tiers', 'no' );

										} else {

											$found_issues[$product][] = array(
												'title'		=> get_the_title( $product ),
												'edit_link'	=> get_edit_post_link( $product ),
												'issue'		=> esc_html__( 'Rental with period selection pricing type and pricing tiers enabled', 'wcrp-rental-products' ),
											);

										}

									}

									if ( 'yes' == $price_additional_periods_percent ) {

										if ( true == $fix ) {

											update_post_meta( $product, '_wcrp_rental_products_price_additional_periods_percent', 'no' );

										} else {

											$found_issues[$product][] = array(
												'title'		=> get_the_title( $product ),
												'edit_link'	=> get_edit_post_link( $product ),
												'issue'		=> esc_html__( 'Rental with period selection pricing type and price + additional periods % enabled', 'wcrp-rental-products' ),
											);

										}

									}

									if ( $minimum_days !== $pricing_period ) {

										if ( true == $fix ) {

											update_post_meta( $product, '_wcrp_rental_products_minimum_days', $pricing_period );

										} else {

											$found_issues[$product][] = array(
												'title'		=> get_the_title( $product ),
												'edit_link'	=> get_edit_post_link( $product ),
												'issue'		=> esc_html__( 'Rental with period selection pricing type and minimum days incorrect', 'wcrp-rental-products' ),
											);

										}

									}

									if ( $maximum_days !== $pricing_period ) {

										if ( true == $fix ) {

											update_post_meta( $product, '_wcrp_rental_products_maximum_days', $pricing_period );

										} else {

											$found_issues[$product][] = array(
												'title'		=> get_the_title( $product ),
												'edit_link'	=> get_edit_post_link( $product ),
												'issue'		=> esc_html__( 'Rental with period selection pricing type and maximum days incorrect', 'wcrp-rental-products' ),
											);

										}

									}

								}

								// In person pick up/return checks

								if ( 'yes' == $in_person_pick_up_return ) {

									if ( (int) $return_days_threshold > 0 ) {

										if ( true == $fix ) {

											update_post_meta( $product, '_wcrp_rental_products_return_days_threshold', '0' );

										} else {

											$found_issues[$product][] = array(
												'title'		=> get_the_title( $product ),
												'edit_link'	=> get_edit_post_link( $product ),
												'issue'		=> esc_html__( 'Rental with in person pick up/return and return days threshold incorrect', 'wcrp-rental-products' ),
											);

										}

									}

								}

								// Rental only core meta checks

								if ( 'yes' == $rental ) { // If rental only

									if ( 'no' !== $manage_stock || 'instock' !== $stock_status || 'no' !== $backorders ) {

										if ( true == $fix ) {

											update_post_meta( $product, '_backorders', 'no' );
											update_post_meta( $product, '_manage_stock', 'no' );
											update_post_meta( $product, '_stock', '' );
											update_post_meta( $product, '_stock_status', 'instock' );

										} else {

											$found_issues[$product][] = array(
												'title'		=> get_the_title( $product ),
												'edit_link'	=> get_edit_post_link( $product ),
												'issue'		=> esc_html__( 'Rental only product with incorrect stock options', 'wcrp-rental-products' ),
											);

										}

									}

									$variations = $wpdb->get_results(
										$wpdb->prepare(
											"SELECT ID FROM {$wpdb->prefix}posts WHERE post_parent = %d AND post_type = 'product_variation'",
											$product
										)
									);

									if ( !empty( $variations ) ) {

										foreach ( $variations as $variation ) {

											$manage_stock = get_post_meta( $variation->ID, '_manage_stock', true );
											$stock_status = get_post_meta( $variation->ID, '_stock_status', true );
											$backorders = get_post_meta( $variation->ID, '_backorders', true );

											if ( 'no' !== $manage_stock || 'instock' !== $stock_status || 'no' !== $backorders ) {

												if ( true == $fix ) {

													update_post_meta( $variation->ID, '_backorders', 'no' );
													update_post_meta( $variation->ID, '_manage_stock', 'no' );
													update_post_meta( $variation->ID, '_stock', '' );
													update_post_meta( $variation->ID, '_stock_status', 'instock' );

												} else {

													$found_issues[$variation->ID][] = array(
														'title'		=> get_the_title( $variation->ID ),
														'edit_link'	=> get_edit_post_link( $product ),
														'issue'		=> esc_html__( 'Rental only product with incorrect stock options', 'wcrp-rental-products' ),
													);

												}

											}

										}

									}

								}

							}

						}

						if ( true == $fix ) {

							?>

							<div class="notice notice-success inline">
								<p><?php esc_html_e( 'Fix completed.', 'wcrp-rental-products' ); ?></p>
							</div>
							<?php echo wp_kses_post( $run_markup ); ?>

						<?php } else { ?>

							<div class="notice notice-<?php echo ( count( $found_issues ) > 0 ? 'error' : 'success' ); ?> inline">
								<p>
									<?php
									// translators: %s: number of found issues
									echo ( count( $found_issues ) > 0 ? wp_kses_post( sprintf( __( '%s issues found.', 'wcrp-rental-products' ), count( $found_issues ) ) ) : esc_html_e( 'No issues found.', 'wcrp-rental-products' ) );
									?>
								</p>
							</div>

							<?php if ( !empty( $found_issues ) ) { ?>

								<div id="wcrp-rental-products-rentals-tools-rental-products-debug-issues">
									<ul>
										<?php
										foreach ( $found_issues as $found_issues_product_id => $found_issues_rows ) {
											if ( !empty( $found_issues_rows ) ) {
												foreach ( $found_issues_rows as $found_issues_row ) {
													?>
													<li><a href="<?php echo esc_url( $found_issues_row['edit_link'] ); ?>" target="_blank"><?php echo wp_kses_post( $found_issues_row['title'] ) . ' ' . esc_html__( '(', 'wcrp-rental-products' ) . esc_html__( 'ID:', 'wcrp-rental-products' ) . ' ' . esc_html( $found_issues_product_id ) . esc_html__( ')', 'wcrp-rental-products' ); ?></a> <?php echo esc_html__( '-', 'wcrp-rental-products' ) . ' ' . esc_html( $found_issues_row['issue'] ) ; ?></a></li>
													<?php
												}
											}
										}
										?>
									</ul>
								</div>
								<button id="wcrp-rental-products-rentals-tools-rental-products-debug-fix" name="wcrp_rental_products_rentals_tools_rental_products_debug_fix" type="submit" class="button button-primary" data-alert-text="<?php esc_html_e( 'Are you sure you want to fix the issues? Always take a backup and test on a staging/development environment before using this tool.', 'wcrp-rental-products' ); ?>"><?php esc_html_e( 'Fix', 'wcrp-rental-products' ); ?></button>
								<?php

							}

						}

					}

				}

			} else {

				echo wp_kses_post( $run_markup );

			}

			?>

		</form>
	</div>
</div>
