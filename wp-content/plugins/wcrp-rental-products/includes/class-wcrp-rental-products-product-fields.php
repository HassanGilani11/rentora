<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Product_Fields' ) ) {

	class WCRP_Rental_Products_Product_Fields {

		public function __construct() {

			add_filter( 'woocommerce_product_data_tabs', array( $this, 'product_data_tab' ) );
			add_filter( 'woocommerce_product_data_panels', array( $this, 'product_data_panel' ) );
			add_action( 'woocommerce_product_options_general_product_data', array( $this, 'product_data_general_tab_fields' ) );
			add_action( 'woocommerce_product_options_inventory_product_data', array( $this, 'product_data_inventory_tab_fields' ) );
			add_action( 'woocommerce_variation_options_pricing', array( $this, 'product_data_variations_tab_fields' ), 10, 3 ); // Inventory based included in pricing hook as inventory hook output doesn't show if manage stock disabled
			add_filter( 'woocommerce_available_variation', array( $this, 'variation_data' ) );
			add_filter( 'woocommerce_product_allow_backorder_use_radio', '__return_false', PHP_INT_MAX ); // In WooCommerce 7.6.0 there was a change from using select fields to radio buttons on product stock fields (if less than x options), these radio buttons have been disabled as if remain we would need to include conditions in the JS which targets the fields for pre 7.6.0 and 7.6.0+ and then for the 7.6.0+ add various conditionals to determine how many options are available and then target either the radio buttons or selects
			add_filter( 'woocommerce_product_stock_status_use_radio', '__return_false', PHP_INT_MAX );  // In WooCommerce 7.6.0 there was a change from using select fields to radio buttons on product stock fields (if less than x options), these radio buttons have been disabled as if remain we would need to include conditions in the JS which targets the fields for pre 7.6.0 and 7.6.0+ and then for the 7.6.0+ add various conditionals to determine how many options are available and then target either the radio buttons or selects

		}

		public function product_data_tab( $tabs ) {

			$tabs['wcrp_rental_products'] = array(
				'label'		=> esc_html__( 'Rental', 'wcrp-rental-products' ),
				'target'	=> 'wcrp-rental-products-panel',
			);

			return $tabs;

		}

		public function product_data_panel() {

			global $post;

			$default_rental_options = wcrp_rental_products_default_rental_options();
			$price_decimal_separator = wc_get_price_decimal_separator();
			$taxes_enabled = get_option( 'woocommerce_calc_taxes' );

			$tax_statuses = array(
				'taxable'	=> __( 'Taxable', 'wcrp-rental-products' ),
				'shipping'	=> __( 'Shipping only', 'wcrp-rental-products' ),
				'none'		=> __( 'None', 'wcrp-rental-products' ),
			);

			$tax_classes = wc_get_product_tax_class_options();

			$shipping_classes = get_terms(
				array(
					'hide_empty'	=> false,
					'fields'		=> 'id=>name',
					'taxonomy'		=> 'product_shipping_class',
				)
			);

			$shipping_classes = array( '' => __( 'No shipping class', 'wcrp-rental-products' ) ) + $shipping_classes;

			?>

			<div id="wcrp-rental-products-panel" class="panel woocommerce_options_panel">

				<?php

				echo '<div class="wcrp-rental-products-panel-heading"><span class="dashicons dashicons-calendar-alt"></span>' . esc_html__( 'Rental', 'wcrp-rental-products' ) . '</div>';

				woocommerce_wp_select(
					array(
						'id'			=> '_wcrp_rental_products_rental',
						'value'			=> get_post_meta( $post->ID, '_wcrp_rental_products_rental', true ),
						'label'			=> esc_html__( 'Rental product', 'wcrp-rental-products' ),
						'desc_tip'		=> true,
						'description'	=> esc_html__( 'Set whether this product is a rental.', 'wcrp-rental-products' ),
						'options'		=> array(
							''				=> __( 'No', 'wcrp-rental-products' ),
							'yes'			=> __( 'Yes - Rental only', 'wcrp-rental-products' ),
							'yes_purchase'	=> __( 'Yes - Rental or purchase', 'wcrp-rental-products' )
						),
						'selected'		=> true,
						'custom_attributes' => array(
							'data-last-value'	=> get_post_meta( $post->ID, '_wcrp_rental_products_rental', true ),
						),
					)
				);

				?>

				<?php // translators: %s: more info link ?>
				<div id="wcrp-rental-products-panel-field-description-rental-product" class="wcrp-rental-products-panel-field-description"><?php echo wp_kses_post( sprintf( __( 'On change the selected option and product/variation stock options will be amended and saved. %s.', 'wcrp-rental-products' ), '<a href="#" id="wcrp-rental-products-panel-rental-product-info">' . esc_html__( 'More info', 'wcrp-rental-products' ) . '</a>' ) ); ?></div>

				<div id="wcrp-rental-products-panel-rental-product-info-expand" class="wcrp-rental-products-panel-info-expand">
					<p><?php esc_html_e( 'Sets whether this product is a rental. Review and understand how each option works below:', 'wcrp-rental-products' ); ?></p>
					<table class="widefat fixed striped">
						<thead>
							<tr>
								<th>
									<strong><?php esc_html_e( 'Rental product', 'wcrp-rental-products' ); ?> &rarr;</strong><br>
									<strong><?php esc_html_e( 'Options', 'wcrp-rental-products' ); ?></strong> &darr;
								</th>
								<th><strong><?php esc_html_e( 'No', 'wcrp-rental-products' ); ?></strong></th>
								<th><strong><?php esc_html_e( 'Yes - Rental only', 'wcrp-rental-products' ); ?></strong></th>
								<th><strong><?php esc_html_e( 'Yes - Rental or purchase', 'wcrp-rental-products' ); ?></strong></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><?php esc_html_e( 'Purchases', 'wcrp-rental-products' ); ?></td>
								<td><span class="dashicons dashicons-yes"></span></td>
								<td><span class="dashicons dashicons-no"></span></td>
								<td><span class="dashicons dashicons-yes"></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'Rental', 'wcrp-rental-products' ); ?></td>
								<td><span class="dashicons dashicons-no"></span></td>
								<td><span class="dashicons dashicons-yes"></span></td>
								<td><span class="dashicons dashicons-yes"></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'Purchasable stock', 'wcrp-rental-products' ); ?></td>
								<td><span class="dashicons dashicons-yes"></span></td>
								<td><span class="dashicons dashicons-no"></span></td>
								<td><span class="dashicons dashicons-yes"></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'Rental stock', 'wcrp-rental-products' ); ?></td>
								<td><span class="dashicons dashicons-no"></span></td>
								<td><span class="dashicons dashicons-yes"></span></td>
								<td><span class="dashicons dashicons-yes"></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'Purchasable price', 'wcrp-rental-products' ); ?></td>
								<td><span class="dashicons dashicons-yes"></span></td>
								<td><span class="dashicons dashicons-no"></span></td>
								<td><span class="dashicons dashicons-yes"></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'Rental price', 'wcrp-rental-products' ); ?></td>
								<td><span class="dashicons dashicons-no"></span></td>
								<td><span class="dashicons dashicons-yes"></span></td>
								<td><span class="dashicons dashicons-yes"></span></td>
							</tr>
						</tbody>
					</table>
					<div class="notice notice-warning inline">
						<p><?php esc_html_e( 'Selecting a yes option will amend any existing stock options set, including variations of variable products.', 'wcrp-rental-products' ); ?></p>
					</div>
				</div>

				<?php

				echo '<div class="wcrp-rental-products-panel-heading"><span class="dashicons dashicons-money-alt"></span>' . esc_html__( 'Pricing', 'wcrp-rental-products' ) . '</div>';
				echo '<p class="form-field"><label>' . esc_html__( 'Rental price', 'wcrp-rental-products' ) . '</label><span id="wcrp-rental-products-rental-price-shortcut"></span></p>';

				woocommerce_wp_select(
					array(
						'id'			=> '_wcrp_rental_products_pricing_type',
						'value'			=> get_post_meta( $post->ID, '_wcrp_rental_products_pricing_type', true ),
						'label'   		=> esc_html__( 'Pricing type', 'wcrp-rental-products' ),
						'desc_tip'		=> true,
						'description'	=> esc_html__( 'Set the type of pricing. The period pricing type allows you to set a number of days the price is for (e.g. 3 day rental for $10.00), the period selection pricing type allows you to set a number of periods for selection (e.g. choose between a 1 day rental for $10.00, 3 day rental for $20.00, etc) and the fixed pricing type uses the same price regardless of the number of days selected.', 'wcrp-rental-products' ),
						'options'		=> array(
							'period'			=> __( 'Period', 'wcrp-rental-products' ),
							'period_selection'	=> __( 'Period selection', 'wcrp-rental-products' ),
							'fixed'				=> __( 'Fixed', 'wcrp-rental-products' ),
						),
						'selected'		=> true,
					)
				);

				echo '<div id="wcrp-rental-products-panel-field-description-pricing-type-period-selection" class="wcrp-rental-products-panel-field-description">' . esc_html__( 'Rental price and pricing period (days) should be set to the lowest period selection. Add additional pricing period selections (days and price) using the pricing period additional selections option.', 'wcrp-rental-products' ) . '</div>'; // This is shown conditionally when the period selection pricing type is selected

				woocommerce_wp_text_input(
					array(
						'type'			=> 'number',
						'id'			=> '_wcrp_rental_products_pricing_period',
						'value'			=> get_post_meta( $post->ID, '_wcrp_rental_products_pricing_period', true ),
						'label'			=> esc_html__( 'Pricing period (days)', 'wcrp-rental-products' ),
						'desc_tip'		=> true,
						'description'	=> esc_html__( 'Set the period of the price set in days (e.g. 7 would make the price set be for a 7 day rental).', 'wcrp-rental-products' ),
						// translators: %s: default value
						'placeholder'	=> sprintf( esc_html__( 'If empty defaults to %s', 'wcrp-rental-products' ), $default_rental_options['_wcrp_rental_products_pricing_period'] ),
						'custom_attributes' => array(
							'data-default'	=> $default_rental_options['_wcrp_rental_products_pricing_period'],
							'min'			=> '1',
							'step'			=> '1',
						),
					)
				);

				woocommerce_wp_checkbox(
					array(
						'id'			=> '_wcrp_rental_products_pricing_period_multiples',
						'value'			=> get_post_meta( $post->ID, '_wcrp_rental_products_pricing_period_multiples', true ),
						'label'			=> esc_html__( 'Pricing period multiples', 'wcrp-rental-products' ),
						'desc_tip'		=> true,
						'description'	=> esc_html__( 'Allow a customer to select multiples of this pricing period (e.g. if pricing period is 7 the customer can select a 7, 14, 21, etc day period). If the customer selects multiple pricing periods the price is multiplied by the amount of pricing periods.', 'wcrp-rental-products' ),
					)
				);

				woocommerce_wp_text_input(
					array(
						'type'				=> 'number',
						'id'				=> '_wcrp_rental_products_pricing_period_multiples_maximum',
						'value'				=> get_post_meta( $post->ID, '_wcrp_rental_products_pricing_period_multiples_maximum', true ),
						'label'				=> esc_html__( 'Pricing period multiples maximum', 'wcrp-rental-products' ),
						'desc_tip'			=> true,
						'description'		=> esc_html__( 'Maximum number of multiples that can be selectable by customer (e.g. if pricing period multiples is enabled, pricing period is 7 and this option is 4 a customer can select a 7, 14, 21 or 28 day rental but no more). Set to 0 for unlimited.', 'wcrp-rental-products' ),
						// translators: %s: default value
						'placeholder'		=> sprintf( esc_html__( 'If empty defaults to %s', 'wcrp-rental-products' ), $default_rental_options['_wcrp_rental_products_maximum_days'] ),
						'custom_attributes' => array(
							'min'	=> '0',
							'step'	=> '1',
						),
					)
				);

				woocommerce_wp_textarea_input(
					array(
						'id'				=> '_wcrp_rental_products_pricing_period_additional_selections',
						'class'				=> $this->shared_field_attributes( '_wcrp_rental_products_pricing_period_additional_selections', 'class' ),
						'value'				=> get_post_meta( $post->ID, '_wcrp_rental_products_pricing_period_additional_selections', true ),
						'label'				=> $this->shared_field_attributes( '_wcrp_rental_products_pricing_period_additional_selections', 'label' ),
						'desc_tip'			=> true,
						'description'		=> $this->shared_field_attributes( '_wcrp_rental_products_pricing_period_additional_selections', 'description' ),
						'placeholder'		=> $this->shared_field_attributes( '_wcrp_rental_products_pricing_period_additional_selections', 'placeholder' ),
						'style'				=> $this->shared_field_attributes( '_wcrp_rental_products_pricing_period_additional_selections', 'style' ),
						'custom_attributes'	=> $this->shared_field_attributes( '_wcrp_rental_products_pricing_period_additional_selections', 'custom_attributes' ),
					)
				);

				woocommerce_wp_checkbox(
					array(
						'id'			=> '_wcrp_rental_products_pricing_tiers',
						'value'			=> get_post_meta( $post->ID, '_wcrp_rental_products_pricing_tiers', true ),
						'label'			=> esc_html__( 'Pricing tiers', 'wcrp-rental-products' ),
						'desc_tip'		=> true,
						'description'	=> esc_html__( 'Increase/decrease price by percentage by number of days selected. Applies to product and any variations.', 'wcrp-rental-products' ),
					)
				);

				$pricing_tiers_data = get_post_meta( $post->ID, '_wcrp_rental_products_pricing_tiers_data', true );

				?>

				<div id="wcrp-rental-products-pricing-tiers-data-expand" class="wcrp-rental-products-panel-expand-background">
					<div id="wcrp-rental-products-pricing-tiers-data">
						<?php
						if ( !empty( $pricing_tiers_data ) ) { // Although pricing tiers data should never be empty to ensure calculations are correct, if the field is not set it will still have the default array as added if not set during product save (or the data added during upgrade functions)
							foreach ( $pricing_tiers_data['days'] as $pricing_tiers_data_key => $pricing_tiers_data_day ) {
								$this->pricing_tiers_data_fields( $pricing_tiers_data_day, $pricing_tiers_data['percent'][$pricing_tiers_data_key] );
							}
						} else {
							$this->pricing_tiers_data_fields( '1', '0' );
						}
						?>
					</div>
					<button id="wcrp-rental-products-pricing-tiers-data-add-pricing-tier" class="button button-small" data-click-text="<?php esc_html_e( 'Adding...', 'wcrp-rental-products' ); ?>"><?php esc_html_e( 'Add pricing tier', 'wcrp-rental-products' ); ?></button>
					<div class="wcrp-rental-products-pricing-tiers-info">
						<?php esc_html_e( 'Days greater than should be greater than the minimum rental period as rental price display may not be accurate without override.', 'wcrp-rental-products' ); ?><br>
						<?php esc_html_e( 'Percent should use a positive or negative number (e.g. 25 for a 25% price increase or -25 for a 25% price decrease on the total).', 'wcrp-rental-products' ); ?>
					</div>
				</div>

				<?php

				woocommerce_wp_checkbox(
					array(
						'id'			=> '_wcrp_rental_products_price_additional_periods_percent',
						'value'			=> get_post_meta( $post->ID, '_wcrp_rental_products_price_additional_periods_percent', true ),
						'label'			=> esc_html__( 'Price + additional periods %', 'wcrp-rental-products' ),
						'desc_tip'		=> true,
						'description'	=> esc_html__( 'When enabled the price will be the period price + a percentage of the period price multiplied by the amount of periods selected (e.g. 7 day pricing period product is $100 with additional periods % at 10% then if a customer selects a 14 day rental, which is 2 x 7 day periods, then the price will be $100 + $10 as the second period is 10% of $100).', 'wcrp-rental-products' ),
					)
				);

				woocommerce_wp_text_input(
					array(
						'type'				=> 'number',
						'id'				=> '_wcrp_rental_products_price_additional_period_percent',
						'value'				=> get_post_meta( $post->ID, '_wcrp_rental_products_price_additional_period_percent', true ),
						'label'				=> esc_html__( 'Price + additional period %', 'wcrp-rental-products' ),
						'desc_tip'			=> true,
						'description'		=> esc_html__( 'Set additional period percentage used (e.g. 10 for a 10% additional period percentage).', 'wcrp-rental-products' ),
						'custom_attributes' => array(
							'min'	=> '0',
							'step'	=> 'any',
						),
					)
				);

				woocommerce_wp_text_input(
					array(
						'id'			=> '_wcrp_rental_products_price_display_override',
						'value'			=> get_post_meta( $post->ID, '_wcrp_rental_products_price_display_override', true ),
						'label'			=> esc_html__( 'Price display override', 'wcrp-rental-products' ),
						'desc_tip'		=> true,
						'description'	=> esc_html__( 'Override the rental price display with specific text entered. The rental price display suffix/prefix will be applied, this can be disabled in settings. This is simply the price displayed to the customer near the product title, it has no bearing on rental price calculations.', 'wcrp-rental-products' ),
					)
				);

				woocommerce_wp_textarea_input(
					array(
						'id'				=> '_wcrp_rental_products_total_overrides',
						'class'				=> $this->shared_field_attributes( '_wcrp_rental_products_total_overrides', 'class' ),
						'value'				=> get_post_meta( $post->ID, '_wcrp_rental_products_total_overrides', true ),
						'label'				=> $this->shared_field_attributes( '_wcrp_rental_products_total_overrides', 'label' ),
						'desc_tip'			=> true,
						'description'		=> $this->shared_field_attributes( '_wcrp_rental_products_total_overrides', 'description' ),
						'placeholder'		=> $this->shared_field_attributes( '_wcrp_rental_products_total_overrides', 'placeholder' ),
						'style'				=> $this->shared_field_attributes( '_wcrp_rental_products_total_overrides', 'style' ),
						'custom_attributes'	=> $this->shared_field_attributes( '_wcrp_rental_products_total_overrides', 'custom_attributes' ),
					)
				);

				woocommerce_wp_select(
					array(
						'id'			=> '_wcrp_rental_products_advanced_pricing',
						'value'			=> get_post_meta( $post->ID, '_wcrp_rental_products_advanced_pricing', true ),
						'label'			=> $this->shared_field_attributes( '_wcrp_rental_products_advanced_pricing', 'label' ),
						'desc_tip'		=> true,
						'description'	=> $this->shared_field_attributes( '_wcrp_rental_products_advanced_pricing', 'description' ),
						'options'		=> $this->shared_field_attributes( '_wcrp_rental_products_advanced_pricing', 'options' ),
						'selected'		=> true,
					)
				);

				?>

				<?php // translators: %s: more info link ?>
				<div id="wcrp-rental-products-panel-field-description-advanced-pricing" class="wcrp-rental-products-panel-field-description"><?php echo wp_kses_post( sprintf( __( 'Allows advanced pricing calculations to be configured and used. %s.', 'wcrp-rental-products' ), '<a href="#" id="wcrp-rental-products-panel-advanced-pricing-info">' . esc_html__( 'More info', 'wcrp-rental-products' ) . '</a>' ) ); ?></div>

				<div id="wcrp-rental-products-panel-advanced-pricing-info-expand" class="wcrp-rental-products-panel-info-expand">
					<p><?php esc_html_e( 'Allows an additional level of calculation on the total. Requires knowledge of PHP and WordPress filter hooks.', 'wcrp-rental-products' ); ?></p>
					<p><?php esc_html_e( 'If incorrectly configured it may cause rental products to be unavailable and/or have incorrect pricing, and in some scenarios cause fatal PHP errors.', 'wcrp-rental-products' ); ?></p>
					<p><?php esc_html_e( 'Some example use cases are seasonal or quantity specific calculations. By using the filter hook below you can filter the total conditionally based on the passed data.', 'wcrp-rental-products' ); ?></p>
					<ul>
						<li>
							<?php esc_html_e( 'Filter hook:', 'wcrp-rental-products' ); ?> <code>wcrp_rental_products_advanced_pricing</code>
						</li>
						<li>
							<?php
							// translators: %1$s: passed parameter, %2$s: passed parameter
							echo wp_kses_post( sprintf( __( 'Parameters: %1$s and %2$s.', 'wcrp-rental-products' ), '<code>$total</code>', '<code>$data</code>' ) );
							?>
						</li>
					</ul>
					<p><strong><?php esc_html_e( 'Important to note:', 'wcrp-rental-products' ); ?></strong></p>
					<ul>
						<li>
							<?php
							// translators: %s: decimal separator
							echo wp_kses_post( sprintf( __( 'Do not alter the %s decimal separator, this will get converted to the store decimal separator automatically', 'wcrp-rental-products' ), '<code>.</code>' ) );
							?>
						</li>
						<li>
							<?php esc_html_e( 'Items in cart with advanced pricing cannot have their quantity changed at cart level', 'wcrp-rental-products' ); ?>
						</li>
						<li>
							<?php esc_html_e( 'Where any third party functionality is used that programmatically changes cart quantities this is likely to cause pricing inaccuracies, so is not recommended', 'wcrp-rental-products' ); ?>
						</li>
						<li>
							<?php esc_html_e( 'You may wish to also use a price display override in some scenarios, such as where advanced pricing effects the minimum rental price displayed', 'wcrp-rental-products' ); ?>
						</li>
					</ul>
					<p><strong><?php esc_html_e( 'Example code snippet:', 'wcrp-rental-products' ); ?></strong></p>
					<p><?php esc_html_e( 'The following example will add 10.00 to the total of any rental product where the rent from date selected is the 25th December 2024.', 'wcrp-rental-products' ); ?></p>
<?php // Code example below is not indented to ensure displays correctly when rendered ?>
<pre>
function customprefix_rental_pricing_xmas_day( $total, $data ) {

	if ( isset( $data['rent_from'] ) ) {

		if ( '2024-12-25' == $data['rent_from'] ) {

			$total = $total + 10.00;

		}

	}

	return $total;

}
add_filter( 'wcrp_rental_products_advanced_pricing', 'customprefix_rental_pricing_xmas_day', 10, 2 );
</pre>
					<div class="notice notice-warning inline">
						<p><?php esc_html_e( 'It is strongly recommended you thoroughly test any advanced pricing code on a staging/development environment before use on a production website.', 'wcrp-rental-products' ); ?></p>
					</div>
				</div>

				<?php

				echo '<div class="wcrp-rental-products-panel-heading"><span class="dashicons dashicons-backup"></span>' . esc_html__( 'Availability', 'wcrp-rental-products' ) . '</div>';
				echo '<p class="form-field"><label>' . esc_html__( 'Rental stock', 'wcrp-rental-products' ) . '</label><span id="wcrp-rental-products-rental-stock-shortcut"></span></p>';

				woocommerce_wp_checkbox(
					array(
						'id'			=> '_wcrp_rental_products_in_person_pick_up_return',
						'value'			=> get_post_meta( $post->ID, '_wcrp_rental_products_in_person_pick_up_return', true ),
						'label'			=> esc_html__( 'In person pick up/return', 'wcrp-rental-products' ),
						'desc_tip'		=> true,
						'description'	=> esc_html__( 'Set if the rental can only be picked up/returned in person.', 'wcrp-rental-products' ),
					)
				);

				// translators: %s: more info link
				echo '<div id="wcrp-rental-products-panel-field-description-in-person-pick-up-return" class="wcrp-rental-products-panel-field-description">' . wp_kses_post( sprintf( __( 'Enable if the product can only be picked up/returned in person, read the following info. %s.', 'wcrp-rental-products' ), '<a href="#" id="wcrp-rental-products-panel-in-person-pick-up-return-info">' . esc_html__( 'More info', 'wcrp-rental-products' ) . '</a>' ) ) . '</div>';

				?>

				<div id="wcrp-rental-products-panel-in-person-pick-up-return-info-expand" class="wcrp-rental-products-panel-info-expand wcrp-rental-products-panel-info-expand-border-bottom">
					<p><?php esc_html_e( 'Sets a product to be available for in person pick up/return:', 'wcrp-rental-products' ); ?></p>
					<ul>
						<li><?php esc_html_e( 'Allows customers to select pick up/return times, in addition to charging fees depending on the times selected', 'wcrp-rental-products' ); ?></li>
						<li><?php esc_html_e( 'It offers a simple means of offering in person pick up/return time/fee options, however depending on your availability/pricing requirements it might not be suitable for every use case', 'wcrp-rental-products' ); ?></li>
						<li><?php esc_html_e( 'If you require functionality more bespoke than this option provides, or to offer the option of both shipped and in person pick up/returns, consider disabling this option and adding optional in person pick up/return time options in product pages and/or in the cart/checkout', 'wcrp-rental-products' ); ?></li>
					</ul>
				</div>

				<?php

				echo '<div id="wcrp-rental-products-in-person-pick-up-return-expand" class="wcrp-rental-products-panel-expand-background">';

				woocommerce_wp_select(
					array(
						'id'			=> '_wcrp_rental_products_in_person_pick_up_return_time_restrictions',
						'value'			=> get_post_meta( $post->ID, '_wcrp_rental_products_in_person_pick_up_return_time_restrictions', true ),
						'label'			=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_return_time_restrictions', 'label' ),
						'desc_tip'		=> true,
						'description'	=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_return_time_restrictions', 'description' ),
						'options'		=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_return_time_restrictions', 'options' ),
						'selected'		=> true,
					)
				);

				woocommerce_wp_select(
					array(
						'id'			=> '_wcrp_rental_products_in_person_return_date',
						'value'			=> get_post_meta( $post->ID, '_wcrp_rental_products_in_person_return_date', true ),
						'label'			=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_return_date', 'label' ),
						'desc_tip'		=> true,
						'description'	=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_return_date', 'description' ),
						'options'		=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_return_date', 'options' ),
						'selected'		=> true,
					)
				);

				echo '<div id="wcrp-rental-products-panel-field-description-in-person-return-date" class="wcrp-rental-products-panel-field-description">';

				// translators: %s: rental settings link
				echo wp_kses_post( sprintf( __( 'In person pick up and return times/fees below must be populated or set to use the defaults populated in %s.', 'wcrp-rental-products' ), '<a href="' . esc_url( get_admin_url() . 'admin.php?page=wc-settings&tab=products&section=wcrp-rental-products' ) . '" target="_blank">' . __( 'rental settings', 'wcrp-rental-products' ) . '</a>' ) ) . '<br>';

				echo esc_html__( 'In person pick up/return may be unavailable if times/fees are incorrectly set as per the tooltip information below, except where unrestricted.', 'wcrp-rental-products' ) . '<br>';

				// translators: %s: in person return date information link
				echo wp_kses_post( sprintf( __( 'If in person return date is set to same day, there are a few %s that should be considered.', 'wcrp-rental-products' ), '<a href="#" id="wcrp-rental-products-panel-in-person-pick-up-return-date-info">' . __( 'complexities', 'wcrp-rental-products' ) . '</a>' ) );

				echo '</div>';

				?>

				<div id="wcrp-rental-products-panel-in-person-pick-up-return-date-info-expand" class="wcrp-rental-products-panel-info-expand wcrp-rental-products-panel-info-expand-border-bottom">
					<p><?php esc_html_e( 'If in person return date is set to same day:', 'wcrp-rental-products' ); ?></p>
					<ul>
						<li><?php esc_html_e( 'You must enter pick up and return times/fees for both single and multiple day rentals', 'wcrp-rental-products' ); ?></li>
						<li><?php esc_html_e( 'If a customer selects a single day rental, the quantity will be reserved for the entire day, regardless of pick up/return times', 'wcrp-rental-products' ); ?></li>
						<li><?php esc_html_e( 'If a customer selects a multiple day rental, the quantity will be reserved for the date range, excluding the last day, to allow other rentals to be picked up on that date', 'wcrp-rental-products' ); ?></li>
						<li><?php esc_html_e( 'Where limited rental stock available, a customer may not be able to select a rental date range where the return date is the same as the start date of another rental e.g. if rental stock is 1, a customer places an order which reserves a rental to be picked up on the 15th and returned on the 17th, another customer will not be able to reserve a rental which has a return date of the 15th, due to the various combinations of availability scenarios which can potentially occur and how the rental form functions, if you have limited rental stock and are likely to meet this scenario, consider using the next day in person return date option instead', 'wcrp-rental-products' ); ?></li>
					</ul>
				</div>

				<?php

				woocommerce_wp_textarea_input(
					array(
						'id'				=> '_wcrp_rental_products_in_person_pick_up_times_fees_same_day',
						'class'				=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_same_day', 'class' ),
						'value'				=> get_post_meta( $post->ID, '_wcrp_rental_products_in_person_pick_up_times_fees_same_day', true ),
						'label'				=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_same_day', 'label' ),
						'desc_tip'			=> true,
						'description'		=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_same_day', 'description' ) . ' ' . esc_html__( 'Set to empty to use defaults from rental settings.', 'wcrp-rental-products' ),
						'placeholder'		=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_same_day', 'placeholder' ) . ' ' . esc_html__( 'or leave empty to use defaults from rental settings', 'wcrp-rental-products' ),
						'style'				=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_same_day', 'style' ),
						'custom_attributes' => $this->shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_same_day', 'custom_attributes' ),
					)
				);

				woocommerce_wp_textarea_input(
					array(
						'id'				=> '_wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day',
						'class'				=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day', 'class' ),
						'value'				=> get_post_meta( $post->ID, '_wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day', true ),
						'label'				=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day', 'label' ),
						'desc_tip'			=> true,
						'description'		=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day', 'description' ) . ' ' . esc_html__( 'Set to empty to use defaults from rental settings.', 'wcrp-rental-products' ),
						'placeholder'		=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day', 'placeholder' ) . ' ' . esc_html__( 'or leave empty to use defaults from rental settings', 'wcrp-rental-products' ),
						'style'				=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day', 'style' ),
						'custom_attributes' => $this->shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day', 'custom_attributes' ),
					)
				);

				woocommerce_wp_textarea_input(
					array(
						'id'				=> '_wcrp_rental_products_in_person_return_times_fees_same_day',
						'class'				=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_same_day', 'class' ),
						'value'				=> get_post_meta( $post->ID, '_wcrp_rental_products_in_person_return_times_fees_same_day', true ),
						'label'				=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_same_day', 'label' ),
						'desc_tip'			=> true,
						'description'		=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_same_day', 'description' ) . ' ' . esc_html__( 'Set to empty to use defaults from rental settings.', 'wcrp-rental-products' ),
						'placeholder'		=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_same_day', 'placeholder' ) . ' ' . esc_html__( 'or leave empty to use defaults from rental settings', 'wcrp-rental-products' ),
						'style'				=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_same_day', 'style' ),
						'custom_attributes' => $this->shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_same_day', 'custom_attributes' ),
					)
				);

				woocommerce_wp_textarea_input(
					array(
						'id'				=> '_wcrp_rental_products_in_person_return_times_fees_single_day_same_day',
						'class'				=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_single_day_same_day', 'class' ),
						'value'				=> get_post_meta( $post->ID, '_wcrp_rental_products_in_person_return_times_fees_single_day_same_day', true ),
						'label'				=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_single_day_same_day', 'label' ),
						'desc_tip'			=> true,
						'description'		=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_single_day_same_day', 'description' ) . ' ' . esc_html__( 'Set to empty to use defaults from rental settings.', 'wcrp-rental-products' ),
						'placeholder'		=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_single_day_same_day', 'placeholder' ) . ' ' . esc_html__( 'or leave empty to use defaults from rental settings', 'wcrp-rental-products' ),
						'style'				=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_single_day_same_day', 'style' ),
						'custom_attributes' => $this->shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_single_day_same_day', 'custom_attributes' ),
					)
				);

				woocommerce_wp_textarea_input(
					array(
						'id'				=> '_wcrp_rental_products_in_person_pick_up_times_fees_next_day',
						'class'				=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_next_day', 'class' ),
						'value'				=> get_post_meta( $post->ID, '_wcrp_rental_products_in_person_pick_up_times_fees_next_day', true ),
						'label'				=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_next_day', 'label' ),
						'desc_tip'			=> true,
						'description'		=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_next_day', 'description' ) . ' ' . esc_html__( 'Set to empty to use defaults from rental settings.', 'wcrp-rental-products' ),
						'placeholder'		=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_next_day', 'placeholder' ) . ' ' . esc_html__( 'or leave empty to use defaults from rental settings', 'wcrp-rental-products' ),
						'style'				=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_next_day', 'style' ),
						'custom_attributes' => $this->shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_next_day', 'custom_attributes' ),
					)
				);

				woocommerce_wp_textarea_input(
					array(
						'id'				=> '_wcrp_rental_products_in_person_return_times_fees_next_day',
						'class'				=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_next_day', 'class' ),
						'value'				=> get_post_meta( $post->ID, '_wcrp_rental_products_in_person_return_times_fees_next_day', true ),
						'label'				=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_next_day', 'label' ),
						'desc_tip'			=> true,
						'description'		=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_next_day', 'description' ) . ' ' . esc_html__( 'Set to empty to use defaults from rental settings.', 'wcrp-rental-products' ),
						'placeholder'		=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_next_day', 'placeholder' ) . ' ' . esc_html__( 'or leave empty to use defaults from rental settings', 'wcrp-rental-products' ),
						'style'				=> $this->shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_next_day', 'style' ),
						'custom_attributes' => $this->shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_next_day', 'custom_attributes' ),
					)
				);

				echo '</div>';

				woocommerce_wp_text_input(
					array(
						'type'				=> 'number',
						'id'				=> '_wcrp_rental_products_minimum_days',
						'value'				=> get_post_meta( $post->ID, '_wcrp_rental_products_minimum_days', true ),
						'label'				=> esc_html__( 'Minimum days', 'wcrp-rental-products' ),
						'desc_tip'			=> true,
						'description'		=> esc_html__( 'Minimum number of days that must be selectable by customer.', 'wcrp-rental-products' ),
						// translators: %s: default value
						'placeholder'		=> sprintf( esc_html__( 'If empty defaults to %s', 'wcrp-rental-products' ), $default_rental_options['_wcrp_rental_products_minimum_days'] ),
						'custom_attributes'	=> array(
							'min'	=> '1',
							'step'	=> '1',
						),
					)
				);

				woocommerce_wp_text_input(
					array(
						'type'				=> 'number',
						'id'				=> '_wcrp_rental_products_maximum_days',
						'value'				=> get_post_meta( $post->ID, '_wcrp_rental_products_maximum_days', true ),
						'label'				=> esc_html__( 'Maximum days', 'wcrp-rental-products' ),
						'desc_tip'			=> true,
						'description'		=> esc_html__( 'Maximum number of days that can be selectable by customer. Set to 0 for unlimited.', 'wcrp-rental-products' ),
						// translators: %s: default value
						'placeholder'		=> sprintf( esc_html__( 'If empty defaults to %s', 'wcrp-rental-products' ), $default_rental_options['_wcrp_rental_products_maximum_days'] ),
						'custom_attributes' => array(
							'min'	=> '0',
							'step'	=> '1',
						),
					)
				);

				woocommerce_wp_select(
					array(
						'id'			=> '_wcrp_rental_products_start_day',
						'value'			=> get_post_meta( $post->ID, '_wcrp_rental_products_start_day', true ),
						'label'			=> esc_html__( 'Start day', 'wcrp-rental-products' ),
						'desc_tip'		=> true,
						'description'	=> esc_html__( 'Specific day of the week rental must start on. Use with caution if setting this to a specific day while also disabling dates/days via the other availability options, consider potential clashes.', 'wcrp-rental-products' ),
						'options'		=> array(
							''	=> __( 'Any', 'wcrp-rental-products' ),
							'1'	=> __( 'Monday', 'wcrp-rental-products' ),
							'2'	=> __( 'Tuesday', 'wcrp-rental-products' ),
							'3'	=> __( 'Wednesday', 'wcrp-rental-products' ),
							'4'	=> __( 'Thursday', 'wcrp-rental-products' ),
							'5'	=> __( 'Friday', 'wcrp-rental-products' ),
							'6'	=> __( 'Saturday', 'wcrp-rental-products' ),
							'0'	=> __( 'Sunday', 'wcrp-rental-products' ),
						),
						'selected'		=> true,
					)
				);

				woocommerce_wp_text_input(
					array(
						'type'				=> 'number',
						'id'				=> '_wcrp_rental_products_start_days_threshold',
						'value'				=> get_post_meta( $post->ID, '_wcrp_rental_products_start_days_threshold', true ),
						'label'				=> esc_html__( 'Start days threshold', 'wcrp-rental-products' ),
						'desc_tip'			=> true,
						'description'		=> esc_html__( 'Number of days including the current day before rental dates selectable by customer. If a specific start day is set the next available start day is selected in conjunction with this start days threshold.', 'wcrp-rental-products' ),
						// translators: %s: default value
						'placeholder'		=> sprintf( esc_html__( 'If empty defaults to %s', 'wcrp-rental-products' ), $default_rental_options['_wcrp_rental_products_start_days_threshold'] ),
						'custom_attributes'	=> array(
							'min'	=> '0',
							'step'	=> '1',
						),
					)
				);

				woocommerce_wp_text_input(
					array(
						'type'				=> 'number',
						'id'				=> '_wcrp_rental_products_return_days_threshold',
						'value'				=> get_post_meta( $post->ID, '_wcrp_rental_products_return_days_threshold', true ),
						'label'				=> esc_html__( 'Return days threshold', 'wcrp-rental-products' ),
						'desc_tip'			=> true,
						'description'		=> esc_html__( 'Number of days after the rental dates for the customer to return rented products.', 'wcrp-rental-products' ),
						// translators: %s: default value
						'placeholder'		=> sprintf( esc_html__( 'If empty defaults to %s', 'wcrp-rental-products' ), $default_rental_options['_wcrp_rental_products_return_days_threshold'] ),
						'custom_attributes'	=> array(
							'min'	=> '0',
							'step'	=> '1',
						),
					)
				);

				woocommerce_wp_text_input(
					array(
						'id'				=> '_wcrp_rental_products_earliest_available_date',
						'class'				=> 'wcrp-rental-products-single-date-picker',
						'value'				=> get_post_meta( $post->ID, '_wcrp_rental_products_earliest_available_date', true ),
						'label'				=> esc_html__( 'Earliest available date', 'wcrp-rental-products' ),
						'desc_tip'			=> true,
						'description'		=> esc_html__( 'If an earliest available date is set then the customer cannot select dates before this date. When using this option any start days threshold set does not apply. If the start day option is set to a specific day then the earliest available date will be the start day on/after the date set.', 'wcrp-rental-products' ),
					)
				);

				woocommerce_wp_text_input(
					array(
						'id'				=> '_wcrp_rental_products_disable_rental_dates',
						'class'				=> 'wcrp-rental-products-disable-dates-picker',
						'value'				=> get_post_meta( $post->ID, '_wcrp_rental_products_disable_rental_dates', true ),
						'label'				=> esc_html__( 'Disable rental dates', 'wcrp-rental-products' ),
						'desc_tip'			=> true,
						'description'		=> esc_html__( 'Rental cannot occur if the dates selected include any of these dates. These will be used in addition to any disabled rental dates which are set in rental settings.', 'wcrp-rental-products' ),
					)
				);

				echo '<div id="wcrp-rental-products-panel-field-description-disable-rental-dates" class="wcrp-rental-products-panel-field-description">' . esc_html__( 'Upon clicking the field above previously disabled dates appear light gray in the calendar, click date again to enable.', 'wcrp-rental-products' ) . '</div>';

				$disable_rental_days = get_post_meta( $post->ID, '_wcrp_rental_products_disable_rental_days', true );
				$disable_rental_days = ( '' !== $disable_rental_days ? explode( ',', $disable_rental_days ) : array() ); // Value could just be 0 (Sunday), if 0 the explode still works and becomes an array of the 0 only, otherwise set to an empty array so the in_array conditions below work

				?>

				<p class="form-field _wcrp_rental_products_disable_rental_days">
					<label><?php esc_html_e( 'Disable rental days', 'wcrp-rental-products' ); ?></label>
					<label><input type="checkbox" name="_wcrp_rental_products_disable_rental_days[]" value="1"<?php echo ( in_array( '1', $disable_rental_days ) ? ' checked' : '' ); ?>><?php esc_html_e( 'Mon', 'wcrp-rental-products' ); ?></label>
					<label><input type="checkbox" name="_wcrp_rental_products_disable_rental_days[]" value="2"<?php echo ( in_array( '2', $disable_rental_days ) ? ' checked' : '' ); ?>><?php esc_html_e( 'Tue', 'wcrp-rental-products' ); ?></label>
					<label><input type="checkbox" name="_wcrp_rental_products_disable_rental_days[]" value="3"<?php echo ( in_array( '3', $disable_rental_days ) ? ' checked' : '' ); ?>><?php esc_html_e( 'Wed', 'wcrp-rental-products' ); ?></label>
					<label><input type="checkbox" name="_wcrp_rental_products_disable_rental_days[]" value="4"<?php echo ( in_array( '4', $disable_rental_days ) ? ' checked' : '' ); ?>><?php esc_html_e( 'Thu', 'wcrp-rental-products' ); ?></label>
					<label><input type="checkbox" name="_wcrp_rental_products_disable_rental_days[]" value="5"<?php echo ( in_array( '5', $disable_rental_days ) ? ' checked' : '' ); ?>><?php esc_html_e( 'Fri', 'wcrp-rental-products' ); ?></label>
					<label><input type="checkbox" name="_wcrp_rental_products_disable_rental_days[]" value="6"<?php echo ( in_array( '6', $disable_rental_days ) ? ' checked' : '' ); ?>><?php esc_html_e( 'Sat', 'wcrp-rental-products' ); ?></label>
					<label><input type="checkbox" name="_wcrp_rental_products_disable_rental_days[]" value="0"<?php echo ( in_array( '0', $disable_rental_days ) ? ' checked' : '' ); ?>><?php esc_html_e( 'Sun', 'wcrp-rental-products' ); ?></label>
				</p>

				<?php

				echo '<div id="wcrp-rental-products-panel-field-description-disable-rental-days" class="wcrp-rental-products-panel-field-description">' . esc_html__( 'Rental cannot occur if the dates selected include any of these days.', 'wcrp-rental-products' ) . '</div>';

				woocommerce_wp_text_input(
					array(
						'id'				=> '_wcrp_rental_products_disable_rental_start_end_dates',
						'class'				=> 'wcrp-rental-products-disable-dates-picker',
						'value'				=> get_post_meta( $post->ID, '_wcrp_rental_products_disable_rental_start_end_dates', true ),
						'label'				=> esc_html__( 'Disable rental start/end dates', 'wcrp-rental-products' ),
						'desc_tip'			=> true,
						'description'		=> esc_html__( 'Rental cannot occur if the dates selected start/end on these dates, but can occur if dates selected go through these dates. These will be used in addition to any disabled rental start/end dates which are set in rental settings.', 'wcrp-rental-products' ),
					)
				);

				echo '<div id="wcrp-rental-products-panel-field-description-disable-rental-start-end-dates" class="wcrp-rental-products-panel-field-description">' . esc_html__( 'Upon clicking the field above previously disabled dates appear light gray in the calendar, click date again to enable.', 'wcrp-rental-products' ) . '</div>';

				$disable_rental_start_end_days = get_post_meta( $post->ID, '_wcrp_rental_products_disable_rental_start_end_days', true );
				$disable_rental_start_end_days = ( '' !== $disable_rental_start_end_days ? explode( ',', $disable_rental_start_end_days ) : array() ); // Value could just be 0 (Sunday), if 0 the explode still works and becomes an array of the 0 only, otherwise set to an empty array so the in_array conditions below work

				?>

				<p class="form-field _wcrp_rental_products_disable_rental_start_end_days">
					<label><?php esc_html_e( 'Disable rental start/end days', 'wcrp-rental-products' ); ?></label>
					<label><input type="checkbox" name="_wcrp_rental_products_disable_rental_start_end_days[]" value="1"<?php echo ( in_array( '1', $disable_rental_start_end_days ) ? ' checked' : '' ); ?>><?php esc_html_e( 'Mon', 'wcrp-rental-products' ); ?></label>
					<label><input type="checkbox" name="_wcrp_rental_products_disable_rental_start_end_days[]" value="2"<?php echo ( in_array( '2', $disable_rental_start_end_days ) ? ' checked' : '' ); ?>><?php esc_html_e( 'Tue', 'wcrp-rental-products' ); ?></label>
					<label><input type="checkbox" name="_wcrp_rental_products_disable_rental_start_end_days[]" value="3"<?php echo ( in_array( '3', $disable_rental_start_end_days ) ? ' checked' : '' ); ?>><?php esc_html_e( 'Wed', 'wcrp-rental-products' ); ?></label>
					<label><input type="checkbox" name="_wcrp_rental_products_disable_rental_start_end_days[]" value="4"<?php echo ( in_array( '4', $disable_rental_start_end_days ) ? ' checked' : '' ); ?>><?php esc_html_e( 'Thu', 'wcrp-rental-products' ); ?></label>
					<label><input type="checkbox" name="_wcrp_rental_products_disable_rental_start_end_days[]" value="5"<?php echo ( in_array( '5', $disable_rental_start_end_days ) ? ' checked' : '' ); ?>><?php esc_html_e( 'Fri', 'wcrp-rental-products' ); ?></label>
					<label><input type="checkbox" name="_wcrp_rental_products_disable_rental_start_end_days[]" value="6"<?php echo ( in_array( '6', $disable_rental_start_end_days ) ? ' checked' : '' ); ?>><?php esc_html_e( 'Sat', 'wcrp-rental-products' ); ?></label>
					<label><input type="checkbox" name="_wcrp_rental_products_disable_rental_start_end_days[]" value="0"<?php echo ( in_array( '0', $disable_rental_start_end_days ) ? ' checked' : '' ); ?>><?php esc_html_e( 'Sun', 'wcrp-rental-products' ); ?></label>
				</p>

				<?php

				echo '<div id="wcrp-rental-products-panel-field-description-disable-rental-start-end-days" class="wcrp-rental-products-panel-field-description">' . esc_html__( 'Rental cannot occur if the dates selected start/end on these days, but can occur if dates selected go through these days.', 'wcrp-rental-products' ) . '</div>';

				woocommerce_wp_select(
					array(
						'id'			=> '_wcrp_rental_products_disable_rental_start_end_days_type',
						'value'			=> get_post_meta( $post->ID, '_wcrp_rental_products_disable_rental_start_end_days_type', true ),
						'label'			=> esc_html__( 'Disable rental start/end days type', 'wcrp-rental-products' ),
						'desc_tip'		=> true,
						'description'	=> esc_html__( 'Sets whether the disable rental start/end days option above should apply to start/end days, start days only, or end days only.', 'wcrp-rental-products' ),
						'options'		=> array(
							'start_end'	=> __( 'Start/end days', 'wcrp-rental-products' ),
							'start'		=> __( 'Start days only (BETA)', 'wcrp-rental-products' ),
							'end'		=> __( 'End days only (BETA)', 'wcrp-rental-products' ),
						),
						'selected'		=> true,
					)
				);

				echo '<div class="wcrp-rental-products-panel-heading"><span class="dashicons dashicons-database-import"></span>' . esc_html__( 'Deposits', 'wcrp-rental-products' ) . '</div>';

				$deposits_partial_payments_for_woocommerce_link = '<a href="' . esc_url( 'https://wordpress.org/plugins/deposits-partial-payments-for-woocommerce/' ) . '" target="_blank">Deposits & Partial Payments for WooCommerce</a>';

				require_once ABSPATH . 'wp-admin/includes/plugin.php';

				if ( is_plugin_active( 'deposits-partial-payments-for-woocommerce/start.php' ) || is_plugin_active( 'deposits-partial-payments-for-woocommerce-pro/start.php' ) ) {

					// translators: %s: Deposits & Partial Payments for WooCommerce name
					echo '<div class="notice notice-warning inline"><p>' . wp_kses_post( sprintf( __( '%s is active, it is recommended you configure deposits directly in that and not use the options below by setting the amount to empty, including on any variations if a variable product.', 'wcrp-rental-products' ), $deposits_partial_payments_for_woocommerce_link ) ) . '</p></div>';

					if ( !is_plugin_active( 'deposits-partial-payments-for-woocommerce-pro/start.php' ) && 'yes' == $taxes_enabled ) {

						// translators: %s: Deposits & Partial Payments for WooCommerce name
						echo '<div class="notice notice-warning inline"><p>' . wp_kses_post( sprintf( __( 'It is recommended to use the premium version of %s, which has tax handling capabilities, without this tax calculations may be inaccurate.', 'wcrp-rental-products' ), $deposits_partial_payments_for_woocommerce_link ) ) . '</p></div>';

					}

				} else {

					// translators: %s: Deposits & Partial Payments for WooCommerce link
					echo '<p class="wcrp-rental-products-panel-paragraph-full-width">' . wp_kses_post( sprintf( __( 'The options below add a security deposit which is paid during checkout, alternatively for a partial deposit with the remaining balance paid later use %s.', 'wcrp-rental-products' ), $deposits_partial_payments_for_woocommerce_link ) ) . '</p>';

				}

				woocommerce_wp_text_input(
					array(
						'id'				=> '_wcrp_rental_products_security_deposit_amount',
						'class'				=> $this->shared_field_attributes( '_wcrp_rental_products_security_deposit_amount', 'class' ),
						'value'				=> get_post_meta( $post->ID, '_wcrp_rental_products_security_deposit_amount', true ),
						'label'				=> $this->shared_field_attributes( '_wcrp_rental_products_security_deposit_amount', 'label' ),
						'desc_tip'			=> true,
						'description'		=> $this->shared_field_attributes( '_wcrp_rental_products_security_deposit_amount', 'description' ),
					)
				);

				echo '<div id="wcrp-rental-products-panel-field-description-security-deposit-amount" class="wcrp-rental-products-panel-field-description">' . esc_html__( 'Security deposits are included in the order total during checkout, transaction fees on the total, including security deposits, will apply if using a card payment provider.', 'wcrp-rental-products' ) . '</div>';

				woocommerce_wp_select(
					array(
						'id'			=> '_wcrp_rental_products_security_deposit_calculation',
						'value'			=> get_post_meta( $post->ID, '_wcrp_rental_products_security_deposit_calculation', true ),
						'label'			=> esc_html__( 'Security deposit calculation', 'wcrp-rental-products' ),
						'desc_tip'		=> true,
						'description'	=> esc_html__( 'Set how security deposit is calculated. Fixed is the same value regardless of quantity, the quantity option multiplies the security deposit amount by the quantity.', 'wcrp-rental-products' ),
						'options'		=> array(
							'quantity'	=> __( 'Quantity', 'wcrp-rental-products' ),
							'fixed'		=> __( 'Fixed', 'wcrp-rental-products' ),
						),
						'selected'		=> true,
					)
				);

				$tax_statuses_security_deposit = $tax_statuses;

				if ( isset( $tax_statuses_security_deposit['shipping'] ) ) {

					unset( $tax_statuses_security_deposit['shipping'] ); // Shipping only tax status doesn't apply to security deposits as deposits are not shipped

				}

				woocommerce_wp_select(
					array(
						'id'			=> '_wcrp_rental_products_security_deposit_tax_status',
						'value'			=> get_post_meta( $post->ID, '_wcrp_rental_products_security_deposit_tax_status', true ),
						'label'			=> esc_html__( 'Security deposit tax status', 'wcrp-rental-products' ),
						'desc_tip'		=> true,
						'description'	=> esc_html__( 'Set the security deposit tax status.', 'wcrp-rental-products' ),
						'options'		=> $tax_statuses_security_deposit,
						'selected'		=> true,
					)
				);

				woocommerce_wp_select(
					array(
						'id'			=> '_wcrp_rental_products_security_deposit_tax_class',
						'value'			=> get_post_meta( $post->ID, '_wcrp_rental_products_security_deposit_tax_class', true ),
						'label'			=> esc_html__( 'Security deposit tax class', 'wcrp-rental-products' ),
						'desc_tip'		=> true,
						'description'	=> esc_html__( 'Set the security deposit tax class.', 'wcrp-rental-products' ),
						'options'		=> $tax_classes,
						'selected'		=> true,
					)
				);

				woocommerce_wp_checkbox(
					array(
						'id'			=> '_wcrp_rental_products_security_deposit_non_refundable',
						'value'			=> get_post_meta( $post->ID, '_wcrp_rental_products_security_deposit_non_refundable', true ),
						'label'			=> esc_html__( 'Security deposit non-refundable', 'wcrp-rental-products' ),
						'desc_tip'		=> true,
						'description'	=> esc_html__( 'Set whether the security deposit is non-refundable.', 'wcrp-rental-products' ),
					)
				);

				echo '<div class="wcrp-rental-products-panel-heading"><span class="dashicons dashicons-plus-alt"></span>' . esc_html__( 'Add-ons', 'wcrp-rental-products' ) . '</div>';

				require_once ABSPATH . 'wp-admin/includes/plugin.php';

				if ( is_plugin_active( 'woocommerce-product-addons/woocommerce-product-addons.php' ) ) {

					echo '<p class="wcrp-rental-products-panel-paragraph-full-width"><a href="#" id="wcrp-rental-products-add-ons-shortcut" class="button button-small">' . esc_html__( 'Set in add-ons tab', 'wcrp-rental-products' ) . '</a></p>';

					woocommerce_wp_checkbox(
						array(
							'id'			=> '_wcrp_rental_products_multiply_addons_total_by_number_of_days_selected',
							'value'			=> get_post_meta( $post->ID, '_wcrp_rental_products_multiply_addons_total_by_number_of_days_selected', true ),
							'label'			=> esc_html__( 'Multiply add-ons total by number of days selected', 'wcrp-rental-products' ),
							'desc_tip'		=> true,
							// translators: %s: multiply addons total by number of days selected flat fees filter hook
							'description'		=> wp_kses_post( sprintf( __( 'Multiplies the add-ons total by the number of days which have been selected. When enabled if you want to exclude add-ons with flat fees from being multiplied use the %s filter hook and return it false.', 'wcrp-rental-products' ), '<span class="wcrp-rental-products-tooltip-word-break">wcrp_rental_products_multiply_addons_total_by_number_of_days_selected_flat_fees</span>' ) ),
							'wrapper_class'	=> 'wcrp-rental-products-panel-form-field-label-wide',
						)
					);

					woocommerce_wp_checkbox(
						array(
							'id'			=> '_wcrp_rental_products_disable_addons_rental_purchase_rental',
							'value'			=> get_post_meta( $post->ID, '_wcrp_rental_products_disable_addons_rental_purchase_rental', true ),
							'label'			=> esc_html__( 'Disable add-ons for rental part of rental or purchase products', 'wcrp-rental-products' ),
							'desc_tip'		=> true,
							'description'	=> esc_html__( 'If a rental or purchase based product, disable add-ons for the rental.', 'wcrp-rental-products' ),
							'wrapper_class'	=> 'wcrp-rental-products-panel-form-field-label-wide',
						)
					);

					woocommerce_wp_checkbox(
						array(
							'id'			=> '_wcrp_rental_products_disable_addons_rental_purchase_purchase',
							'value'			=> get_post_meta( $post->ID, '_wcrp_rental_products_disable_addons_rental_purchase_purchase', true ),
							'label'			=> esc_html__( 'Disable add-ons for purchase part of rental or purchase products', 'wcrp-rental-products' ),
							'desc_tip'		=> true,
							'description'	=> esc_html__( 'If a rental or purchase based product, disable add-ons for the purchase.', 'wcrp-rental-products' ),
							'wrapper_class'	=> 'wcrp-rental-products-panel-form-field-label-wide',
						)
					);

					echo '<div id="wcrp-rental-products-panel-field-notice-disable-addons-rental-purchase" class="notice notice-warning inline"><p>' . esc_html__( 'There is a limitation when either of the disable add-ons options above are enabled if you are using global product add-ons with required selection, even though the product add-ons are not displayed, the product won\'t allow add to cart, this is due to a limitation in the extensibility of the WooCommerce Product Add-ons extension. Product level add-ons are unaffected by this. It is therefore not recommended to combine either of the above 2 options with global product add-ons which have required selection.', 'wcrp-rental-products' ) . '</p></div>'; // This is shown conditionally when the period selection pricing type is selected

				} else {

					// translators: %s: WooCommerce Product Add-ons link

					echo '<p class="wcrp-rental-products-panel-paragraph-full-width">' . wp_kses_post( sprintf( __( 'To add fields e.g. text, select, checkboxes, etc that collect information and/or charge additional fees use %s.', 'wcrp-rental-products' ), '<a href="' . esc_url( 'https://woocommerce.com/products/product-add-ons/' ) . '" target="_blank">WooCommerce Product Add-ons</a>' ) ) . '</p>';

				}

				echo '<div class="wcrp-rental-products-panel-heading"><span class="dashicons dashicons-calendar-alt"></span>' . esc_html__( 'Calendar', 'wcrp-rental-products' ) . '</div>';

				woocommerce_wp_text_input(
					array(
						'type'				=> 'number',
						'id'				=> '_wcrp_rental_products_months',
						'value'				=> get_post_meta( $post->ID, '_wcrp_rental_products_months', true ),
						'label'				=> esc_html__( 'Months', 'wcrp-rental-products' ),
						'desc_tip'			=> true,
						'description'		=> esc_html__( 'Number of months to show within the calendar before pagination. On mobile/tablet devices this option may be ignored to ensure the calender is legible.', 'wcrp-rental-products' ),
						// translators: %s: default value
						'placeholder'		=> sprintf( esc_html__( 'If empty defaults to %s', 'wcrp-rental-products' ), $default_rental_options['_wcrp_rental_products_months'] ),
						'custom_attributes' => array(
							'min'	=> '1',
							'step'	=> '1',
						),
					)
				);

				woocommerce_wp_text_input(
					array(
						'type'				=> 'number',
						'id'				=> '_wcrp_rental_products_columns',
						'value'				=> get_post_meta( $post->ID, '_wcrp_rental_products_columns', true ),
						'label'				=> esc_html__( 'Columns', 'wcrp-rental-products' ),
						'desc_tip'			=> true,
						'description'		=> esc_html__( 'Number of columns to show within the calendar before pagination. On mobile/tablet devices this option may be ignored to ensure the calender is legible.', 'wcrp-rental-products' ),
						// translators: %s: default value
						'placeholder'		=> sprintf( esc_html__( 'If empty defaults to %s', 'wcrp-rental-products' ), $default_rental_options['_wcrp_rental_products_columns'] ),
						'custom_attributes' => array(
							'min'	=> '1',
							'max'	=> '4',
							'step'	=> '1',
						),
					)
				);

				woocommerce_wp_checkbox(
					array(
						'id'			=> '_wcrp_rental_products_inline',
						'value'			=> get_post_meta( $post->ID, '_wcrp_rental_products_inline', true ),
						'label'			=> esc_html__( 'Inline', 'wcrp-rental-products' ),
						'desc_tip'		=> true,
						'description'	=> esc_html__( 'Allows the customer to select dates immediately without first selecting the date selection field.', 'wcrp-rental-products' ),
					)
				);

				echo '<div class="wcrp-rental-products-panel-heading"><span class="dashicons dashicons-info-outline"></span>' . esc_html__( 'Information', 'wcrp-rental-products' ) . '</div>';

				woocommerce_wp_textarea_input(
					array(
						'id'			=> '_wcrp_rental_products_rental_information',
						'value'			=> get_post_meta( $post->ID, '_wcrp_rental_products_rental_information', true ),
						'label'			=> esc_html__( 'Rental information', 'wcrp-rental-products' ),
						'desc_tip'		=> true,
						'description'	=> esc_html__( 'Adds rental information to a tab on the product page. This will be used in addition to any rental information which may be set via rental settings.', 'wcrp-rental-products' ),
						'style'			=> 'height: 70px;',
					)
				);

				echo '<div class="wcrp-rental-products-panel-heading"><span class="dashicons dashicons-admin-settings"></span>' . esc_html__( 'Advanced', 'wcrp-rental-products' ) . '</div>';

				woocommerce_wp_checkbox(
					array(
						'id'			=> '_wcrp_rental_products_rental_purchase_rental_tax_override',
						'value'			=> get_post_meta( $post->ID, '_wcrp_rental_products_rental_purchase_rental_tax_override', true ),
						'label'			=> esc_html__( 'Rental or purchase - rental tax override', 'wcrp-rental-products' ),
						'desc_tip'		=> true,
						'description'	=> esc_html__( 'Use a different tax status/class than the purchasable tax status/class for the rental part of rental or purchase products.', 'wcrp-rental-products' ),
					)
				);

				woocommerce_wp_select(
					array(
						'id'			=> '_wcrp_rental_products_rental_purchase_rental_tax_override_status',
						'value'			=> get_post_meta( $post->ID, '_wcrp_rental_products_rental_purchase_rental_tax_override_status', true ),
						'label'			=> esc_html__( 'Rental or purchase - rental tax override status', 'wcrp-rental-products' ),
						'desc_tip'		=> true,
						'description'	=> esc_html__( 'Set the tax status of the rental part of a rental or purchase product. Requires rental or purchase - rental tax override option to be enabled.', 'wcrp-rental-products' ),
						'options'		=> $tax_statuses,
						'selected'		=> true,
					)
				);

				woocommerce_wp_select(
					array(
						'id'			=> '_wcrp_rental_products_rental_purchase_rental_tax_override_class',
						'value'			=> get_post_meta( $post->ID, '_wcrp_rental_products_rental_purchase_rental_tax_override_class', true ),
						'label'			=> esc_html__( 'Rental or purchase - rental tax override class', 'wcrp-rental-products' ),
						'desc_tip'		=> true,
						'description'	=> esc_html__( 'Set the tax class of the rental part of a rental or purchase product. Requires rental or purchase - rental tax override option to be enabled.', 'wcrp-rental-products' ),
						'options'		=> $tax_classes,
						'selected'		=> true,
					)
				);

				woocommerce_wp_checkbox(
					array(
						'id'			=> '_wcrp_rental_products_rental_purchase_rental_shipping_override',
						'value'			=> get_post_meta( $post->ID, '_wcrp_rental_products_rental_purchase_rental_shipping_override', true ),
						'label'			=> esc_html__( 'Rental or purchase - rental shipping override', 'wcrp-rental-products' ),
						'desc_tip'		=> true,
						'description'	=> esc_html__( 'Use a different rental shipping class than the purchasable shipping class for the rental part of rental or purchase products.', 'wcrp-rental-products' ),
					)
				);

				woocommerce_wp_select(
					array(
						'id'			=> '_wcrp_rental_products_rental_purchase_rental_shipping_override_class',
						'value'			=> get_post_meta( $post->ID, '_wcrp_rental_products_rental_purchase_rental_shipping_override_class', true ),
						'label'			=> esc_html__( 'Rental or purchase - rental shipping override class', 'wcrp-rental-products' ),
						'desc_tip'		=> true,
						'description'	=> esc_html__( 'Set the shipping class of the rental part of a rental or purchase product. Requires rental or purchase - rental shipping override option to be enabled.', 'wcrp-rental-products' ),
						'options'		=> $shipping_classes,
						'selected'		=> true,
					)
				);

				echo '<p id="wcrp-rental-products-panel-general-rental-settings"><a href="' . esc_url( get_admin_url() . 'admin.php?page=wc-settings&tab=products&section=wcrp-rental-products' ) . '" class="button button-small" target="_blank">' . esc_html__( 'General rental settings', 'wcrp-rental-products' ) . '</a></p>';

				?>
			</div>

			<script>

				jQuery( document ).ready( function( $ ) {

					<?php // Save the rental product option (this must occur immediately on change via AJAX to ensure that if variations are loaded and saved then the _wcrp_rental_products_rental meta is correct to the option selected, without this the variation saving function which amends the stock meta would be conditioning off the old rental type meta and therefore would set incorrect stock meta as it thinks its different from the selected) ?>

					function saveRentalProductOptionAjax() {

						var data = {
							'action':			'wcrp_rental_products_save_rental_product_option_ajax',
							'post_id':			$( '#post_ID' ).val(),
							'rental_product':	$( '#_wcrp_rental_products_rental' ).val(),
							'nonce':			'<?php echo esc_html( wp_create_nonce( 'wcrp_rental_products_save_rental_product_option_ajax' ) ); ?>',
						};

						ajaxErrorNotice = "<?php esc_html_e( 'There was an AJAX error when attempting to save the rental product option, do not amend any other options and save this product using the normal publish/update buttons.', 'wcrp-rental-products' ); ?>";

						jQuery.post('<?php echo esc_html( admin_url( 'admin-ajax.php' ) ); ?>', data, function( response ) {

							if ( '0' == response ) {

								alert( ajaxErrorNotice );

							} else {

								$( '#_wcrp_rental_products_rental' ).css( 'opacity', '1' );

								if ( '2' == response ) { <?php // If no longer rental only ?>

									<?php // As the stock fields could have had manage stock enabled before a change to rental and then back to a non rental, we change them to what they should be, e.g. changing from rental or purchase to rental only makes it manage stock disabled, if turned back from rental only to rental or purchase without the below the stock fields would be as they were before which is different to how the meta will have been updated to in the AJAX meta save, this sets them to match what the meta now is, important to note that if you track down these elements after changing the rental product option in the debugger and unhide them you'll notice the elements don't seem to take effect on what is being set below, however it is occuring, this can be observed by setting up a rental or purchase, enabling manage stock, then changing to rental only and then back again, you'll notice the manage stock option is disabled even though it shows as enabled in debugger when unhidden before the final change to rental or purchase, in regards to saving regardless of the options set below the correct meta will be set anyway due to WCRP_Rental_Products_Product_Save::force_stock_meta() ?>

									$( '#_backorders' ).val( 'no' );
									$( '#_manage_stock' ).prop( 'checked', false ).trigger( 'change' );
									$( '#_stock_status' ).val( 'instock' );

									$( '.variable_manage_stock' ).each( function( index ) { <?php // Variations > variation > manage stock ?>

										$( this ).prop( 'checked', false ).trigger( 'change' );
										$( this ).closest( '.data' ).find( '.variable_stock_status select' ).val( 'instock' );
										$( this ).closest( '.data' ).find( '.show_if_variation_manage_stock' ).find( 'select[id^="variable_backorders"]' ).val( 'no' );

									});

									if ( 'yes_purchase' == data.rental_product ) {

										alert( "<?php esc_html_e( 'This product is now a rental or purchase product, remember to set rental price/stock in the additional rental price/stock fields which are now available.', 'wcrp-rental-products' ); ?>" );

									}

								}

							}

						}).fail( function() {

							alert( ajaxErrorNotice );

						})

					}

					<?php // Toggle rental options depending on the product and rental type ?>

					function toggleRentalOptions() {

						<?php // Initial resets - product options ?>

						$( '.wcrp-rental-products-rental-purchase-price-variations' ).closest( '.form-field' ).hide();
						$( '.wcrp-rental-products-rental-stock-variations' ).closest( '.form-field' ).hide();
						$( '.wcrp-rental-products-pricing-period-additional-selections-variations' ).closest( '.form-field' ).hide();
						$( '.wcrp-rental-products-total-overrides-variations' ).closest( '.form-field' ).hide();
						$( '.wcrp-rental-products-security-deposit-amount-variations' ).closest( '.form-field' ).hide();

						$( '#wcrp-rental-products-product-data-general-tab-fields-styles' ).remove();
						$( '<style id="wcrp-rental-products-product-data-general-tab-fields-styles" type="text/css">.wcrp-rental-products-product-data-general-tab-fields { display: none; }</style>' ).appendTo( 'head' );

						$( '#wcrp-rental-products-product-data-inventory-tab-fields-styles' ).remove();
						$( '<style id="wcrp-rental-products-product-data-inventory-tab-fields-styles" type="text/css">.wcrp-rental-products-product-data-inventory-tab-fields { display: none; }</style>' ).appendTo( 'head' );

						<?php // Initial resets - bulk edit variation options, for details of these see WCRP_Rental_Products_Product_Bulk_Edits::bulk_edit_variations(), they are done here rather than there as this function already includes all the conditions needed to determine whether these options should be shown/hidden ?>

						$( '#wcrp-rental-products-bulk-edit-variations' ).hide();
						$( '#wcrp-rental-products-bulk-edit-variations option[value="variable_rental_price"]' ).hide();
						$( '#wcrp-rental-products-bulk-edit-variations option[value="variable_rental_stock"]' ).hide();
						$( '#wcrp-rental-products-bulk-edit-variations option[value="variable_rental_pricing_period_additional_selections"]' ).hide();
						$( '#wcrp-rental-products-bulk-edit-variations option[value="variable_rental_total_overrides"]' ).hide();
						$( '#wcrp-rental-products-bulk-edit-variations option[value="variable_rental_security_deposit_amount"]' ).hide();

						<?php // Note that below we do not need to set the values when hiding/showing options as these are changed to the required values upon save of product/variation ?>

						if ( $( '#product-type option[value="simple"]' ).is( ':selected' ) || $( '#product-type option[value="variable"]' ).is( ':selected' ) ) {

							$( '.product_data_tabs .wcrp_rental_products_tab' ).show();

							if ( $( '#_wcrp_rental_products_rental option[value="yes"]' ).is( ':selected' ) || $( '#_wcrp_rental_products_rental option[value="yes_purchase"]' ).is( ':selected' ) ) {

								<?php // If rental or purchase based product show/hide fields ?>

								if ( $( '#_wcrp_rental_products_rental option[value="yes_purchase"]' ).is( ':selected' ) ) {

									<?php // Show variation fields/bulk edit variation options for a rental or purchase based product ?>

									$( '.wcrp-rental-products-rental-purchase-price-variations' ).closest( '.form-field' ).show();
									$( '.wcrp-rental-products-rental-stock-variations' ).closest( '.form-field' ).show();
									$( '.wcrp-rental-products-security-deposit-amount-variations' ).closest( '.form-field' ).show();

									$( '#wcrp-rental-products-bulk-edit-variations' ).show();
									$( '#wcrp-rental-products-bulk-edit-variations option[value="variable_rental_price"]' ).show();
									$( '#wcrp-rental-products-bulk-edit-variations option[value="variable_rental_stock"]' ).show();
									$( '#wcrp-rental-products-bulk-edit-variations option[value="variable_rental_security_deposit_amount"]' ).show();

									if ( $( '#_wcrp_rental_products_pricing_type option[value="period_selection"]' ).is( ':selected' ) ) {

										$( '.wcrp-rental-products-pricing-period-additional-selections-variations' ).closest( '.form-field' ).show();

										$( '#wcrp-rental-products-bulk-edit-variations option[value="variable_rental_pricing_period_additional_selections"]' ).show();

									} else {

										$( '.wcrp-rental-products-total-overrides-variations' ).closest( '.form-field' ).show();

										$( '#wcrp-rental-products-bulk-edit-variations option[value="variable_rental_total_overrides"]' ).show();

									}

									<?php // Show parent fields if a simple product type ?>

									if ( $( '#product-type option[value="simple"]' ).is( ':selected' ) ) {

										$( '#wcrp-rental-products-product-data-general-tab-fields-styles' ).html( '.wcrp-rental-products-product-data-general-tab-fields { display: block; }' );
										$( '#wcrp-rental-products-product-data-inventory-tab-fields-styles' ).html( '.wcrp-rental-products-product-data-inventory-tab-fields { display: block; }' );

									}

									<?php // Show standard (non-variation) core stock fields ?>

									$( '._manage_stock_field' ).show(); <?php // Inventory > Manage stock ?>

									<?php // Note that the stock fields below get shown/hidden even though WooCommerce does this itself because when switching between rental types these fields get shown/hidden conditionally on top of WooCommerce's show/hide, so we have to ensure they get shown/hidden correctly as the previous selection might have shown/hidden some that need changing and we cannot rely on the native WooCommerce show/hide as that isn't aware of the modified show/hides done, it is also recommended if ever amending this code to look at a vanilla WooCommerce edit product screen to see the options which should be shown/hidden based on the product type ?>

									if ( $( '#_manage_stock' ).is( ':checked' ) ) {

										if ( $( '#product-type option[value="simple"]' ).is( ':selected' ) ) {

											$( '.stock_status_field' ).hide();
											$( '.stock_fields' ).show();

										} else if ( $( '#product-type option[value="variable"]' ).is( ':selected' ) ) {

											$( '.stock_status_field' ).hide();
											$( '.stock_fields' ).show();

										}

									} else {

										if ( $( '#product-type option[value="simple"]' ).is( ':selected' ) ) {

											$( '.stock_status_field' ).show();
											$( '.stock_fields' ).hide();

										} else if ( $( '#product-type option[value="variable"]' ).is( ':selected' ) ) {

											$( '.stock_status_field' ).hide();
											$( '.stock_fields' ).hide();

										}

									}

									<?php // Show variation core stock fields ?>

									$( '.variable_manage_stock' ).each( function( index ) {

										$( this ).closest( 'label' ).show();

									});

									$( '.variable_stock_status' ).each( function( index ) {

										if ( $( this ).closest( '.variable_manage_stock' ).prop( 'checked' ) == false ) {

											$( this ).show();

										}

									});

									$( '.show_if_variation_manage_stock' ).each( function( index ) {

										if ( $( this ).closest( '.variable_manage_stock' ).prop( 'checked' ) == true ) {

											$( this ).show();

										}

									});

								} else {

									<?php // If rental only based product show/hide options ?>

									if ( $( '#_wcrp_rental_products_rental option[value="yes"]' ).is( ':selected' ) ) {

										<?php // Show variation fields/bulk edit variation options for a rental only based product ?>

										$( '.wcrp-rental-products-rental-stock-variations' ).closest( '.form-field' ).show();
										$( '.wcrp-rental-products-security-deposit-amount-variations' ).closest( '.form-field' ).show();

										$( '#wcrp-rental-products-bulk-edit-variations' ).show();
										$( '#wcrp-rental-products-bulk-edit-variations option[value="variable_rental_stock"]' ).show();
										$( '#wcrp-rental-products-bulk-edit-variations option[value="variable_rental_security_deposit_amount"]' ).show();

										if ( $( '#_wcrp_rental_products_pricing_type option[value="period_selection"]' ).is( ':selected' ) ) {

											$( '.wcrp-rental-products-pricing-period-additional-selections-variations' ).closest( '.form-field' ).show();

											$( '#wcrp-rental-products-bulk-edit-variations option[value="variable_rental_pricing_period_additional_selections"]' ).show();

										} else {

											$( '.wcrp-rental-products-total-overrides-variations' ).closest( '.form-field' ).show();

											$( '#wcrp-rental-products-bulk-edit-variations option[value="variable_rental_total_overrides"]' ).show();

										}

										<?php // Show parent fields if a simple product type ?>

										if ( $( '#product-type option[value="simple"]' ).is( ':selected' ) ) {

											$( '#wcrp-rental-products-product-data-inventory-tab-fields-styles' ).html( '.wcrp-rental-products-product-data-inventory-tab-fields { display: block; }' );

										}

										<?php // Note that hides below may keep the values (e.g. if you switch from a rental or purchase to rental only the manage stock field would be hidden but remain checked, this does not matter as it's hidden and on save it makes the field value correct, in that scenario it would mean manage stock gets unchecked) ?>

										<?php // Hide standard (non-variation) stock fields ?>

										$( '._manage_stock_field' ).hide();
										$( '.stock_status_field' ).hide();
										$( '.stock_fields' ).hide();

										<?php // Hide variation stock fields ?>

										$( '.variable_manage_stock' ).each( function( index ) {

											$( this ).closest( 'label' ).hide();

										});

										$( '.variable_stock_status' ).each( function( index ) {

											$( this ).hide();

										});

										$( '.show_if_variation_manage_stock' ).each( function( index ) {

											$( this ).hide();

										});

									}

								}

							} else {

								<?php // Show the standard (non-variation) stock fields ?>

								$( '._manage_stock_field' ).show(); <?php // Inventory > Manage Stock ?>

								if ( $( '#_manage_stock' ).is( ':checked' ) ) {

									$( '.stock_fields' ).show(); <?php // Inventory > manage stock: enabled > related stock fields (stock level, backorders, low stock threshold etc) ?>

								} else {

									if ( $( '#product-type option[value="simple"]').is( ':selected' ) ) {

										$( '.stock_status_field' ).show(); <?php // Simple product > inventory > manage stock: disabled > stock status (this is not shown if a variable as set on the variation itself) ?>

									}

								}

								// Show variation stock fields

								$( '.variable_manage_stock' ).each( function( index ) { <?php // Variations > variation > manage stock ?>

									$( this ).closest( 'label' ).show();

									if ( $( this ).is(':checked') ) {

										$( this ).closest( '.data' ).find( '.show_if_variation_manage_stock' ).show(); <?php // Inventory > manage stock: enabled > related stock fields (stock level, backorders, low stock threshold etc) ?>

									} else {

										$( this ).closest( '.data' ).find( '.variable_stock_status' ).show(); <?php // Variations > variation > manage stock: disabled > stock status ?>

									}

								});

							}

						} else {

							$( '.product_data_tabs .wcrp_rental_products_tab' ).hide(); <?php // Hides the rentals product tab if not simple or variable product type as other product types are either not supported or the rental tab is not required for that type (e.g. grouped type) ?>
							$( '#_wcrp_rental_products_rental').val( '' ); <?php // Ensures that the product does not get saved as rental when a non-rental product type is selected ?>

						}

					}

					<?php // Triggers ?>

					toggleRentalOptions();

					$( document ).on( 'woocommerce_variations_loaded', function() {

						toggleRentalOptions();

					});

					$( document ).on( 'woocommerce_variations_added', function() {

						toggleRentalOptions();

					});

					$( document ).on( 'change', '#product-type', function() {

						toggleRentalOptions();

					});

					$( document ).on( 'change', '#_wcrp_rental_products_rental', function( e ) {

						if ( confirm( "<?php esc_html_e( 'Are you sure? On change the selected option and product/variation stock options will be amended and saved.', 'wcrp-rental-products' ); ?>" ) ) {

							$( this ).css( 'opacity', '0.5' );
							$( this ).attr( 'data-last-value', $( this ).val() );

							saveRentalProductOptionAjax();
							toggleRentalOptions();

						} else {

							$( this ).val( $( '#_wcrp_rental_products_rental' ).attr( 'data-last-value' ) );

						}

					});

					$( document ).on( 'change', '#_wcrp_rental_products_pricing_type', function() {

						toggleRentalOptions();

					});

				});

			</script>

			<?php

		}

		public function product_data_general_tab_fields() {

			global $post;

			?>

			<div class="options_group wcrp-rental-products-product-data-general-tab-fields">

				<?php
				woocommerce_wp_text_input(
					array(
						'id'			=> '_wcrp_rental_products_rental_purchase_price',
						'class'			=> $this->shared_field_attributes( '_wcrp_rental_products_rental_purchase_price', 'class' ),
						'value'			=> get_post_meta( $post->ID, '_wcrp_rental_products_rental_purchase_price', true ),
						'label'			=> $this->shared_field_attributes( '_wcrp_rental_products_rental_purchase_price', 'label' ),
						'desc_tip'		=> true,
						'description'	=> $this->shared_field_attributes( '_wcrp_rental_products_rental_purchase_price', 'description' ),
					)
				);
				?>

			</div>

			<?php

		}

		public function product_data_inventory_tab_fields() {

			global $post;

			?>

			<div class="options_group wcrp-rental-products-product-data-inventory-tab-fields">

				<?php
				woocommerce_wp_text_input(
					array(
						'id'				=> '_wcrp_rental_products_rental_stock',
						'value'				=> get_post_meta( $post->ID, '_wcrp_rental_products_rental_stock', true ),
						'label'				=> $this->shared_field_attributes( '_wcrp_rental_products_rental_stock', 'label' ),
						'desc_tip'			=> true,
						'description'		=> $this->shared_field_attributes( '_wcrp_rental_products_rental_stock', 'description' ),
						'type'				=> $this->shared_field_attributes( '_wcrp_rental_products_rental_stock', 'type' ),
						'placeholder'		=> $this->shared_field_attributes( '_wcrp_rental_products_rental_stock', 'placeholder' ),
						'custom_attributes' => $this->shared_field_attributes( '_wcrp_rental_products_rental_stock', 'custom_attributes' ),
					)
				);
				?>

			</div>

			<?php

		}

		public function product_data_variations_tab_fields( $loop, $variation_data, $variation ) {

			// Rental prefix is added to the label on some of these fields where it doesn't already start with rental, this is because the options are mixed in with other non-rental based fields, so adding the rental prefix to the label just ensures the user understands it's a rental based field

			woocommerce_wp_text_input(
				array(
					'id'			=> '_wcrp_rental_products_rental_purchase_price[' . $loop . ']',
					'class'			=> 'wcrp-rental-products-rental-purchase-price-variations ' . $this->shared_field_attributes( '_wcrp_rental_products_rental_purchase_price', 'class' ),
					'value'			=> get_post_meta( $variation->ID, '_wcrp_rental_products_rental_purchase_price', true ),
					'label'			=> $this->shared_field_attributes( '_wcrp_rental_products_rental_purchase_price', 'label' ),
					'desc_tip'		=> true,
					'description'	=> $this->shared_field_attributes( '_wcrp_rental_products_rental_purchase_price', 'description' ),
					'wrapper_class'	=> 'form-row', // We don't use form-row-first and form-row-last as some of these variation fields are conditionally shown/hidden depending on other options, so in some scenarios using them can cause blank sections/incorrectly floated fields
				)
			);

			woocommerce_wp_textarea_input(
				array(
					'id'				=> '_wcrp_rental_products_pricing_period_additional_selections[' . $loop . ']',
					'class'				=> 'wcrp-rental-products-pricing-period-additional-selections-variations wcrp-rental-products-value-colon-price-validation-variation ' . $this->shared_field_attributes( '_wcrp_rental_products_pricing_period_additional_selections', 'class' ),
					'value'				=> get_post_meta( $variation->ID, '_wcrp_rental_products_pricing_period_additional_selections', true ),
					'label'				=> $this->shared_field_attributes( '_wcrp_rental_products_pricing_period_additional_selections', 'label' ),
					'desc_tip'			=> false,
					'description'		=> __( 'Must match the pricing period additional selections from the rental tab (pricing can be different), should not include the period entered in the pricing period (days) field in the rental tab, price for that period is the price set on this variation.', 'wcrp-rental-products' ) . '<br>' . $this->shared_field_attributes( '_wcrp_rental_products_pricing_period_additional_selections', 'description' ),
					'wrapper_class'		=> 'form-row', // We don't use form-row-first and form-row-last as some of these variation fields are conditionally shown/hidden depending on other options, so in some scenarios using them can cause blank sections/incorrectly floated fields
					'placeholder'		=> $this->shared_field_attributes( '_wcrp_rental_products_pricing_period_additional_selections', 'placeholder' ) . ' ' . __( '-', 'wcrp-rental-products' ) . ' ' . __( 'If empty uses rental tab pricing period additional selections', 'wcrp-rental-products' ),
					'style'				=> $this->shared_field_attributes( '_wcrp_rental_products_pricing_period_additional_selections', 'style' ),
					'custom_attributes'	=> $this->shared_field_attributes( '_wcrp_rental_products_pricing_period_additional_selections', 'custom_attributes' ),
				)
			);

			woocommerce_wp_textarea_input(
				array(
					'id'				=> '_wcrp_rental_products_total_overrides[' . $loop . ']',
					'class'				=> 'wcrp-rental-products-total-overrides-variations wcrp-rental-products-value-colon-price-validation-variation ' . $this->shared_field_attributes( '_wcrp_rental_products_total_overrides', 'class' ),
					'value'				=> get_post_meta( $variation->ID, '_wcrp_rental_products_total_overrides', true ),
					'label'				=> $this->shared_field_attributes( '_wcrp_rental_products_total_overrides', 'label' ),
					'desc_tip'			=> false,
					'description'		=> $this->shared_field_attributes( '_wcrp_rental_products_total_overrides', 'description' ) . ' ' . __( 'If empty uses rental tab total overrides', 'wcrp-rental-products' ) . __( '.', 'wcrp-rental-products' ),
					'wrapper_class'		=> 'form-row', // We don't use form-row-first and form-row-last as some of these variation fields are conditionally shown/hidden depending on other options, so in some scenarios using them can cause blank sections/incorrectly floated fields
					'placeholder'		=> $this->shared_field_attributes( '_wcrp_rental_products_total_overrides', 'placeholder' ) . ' ' . __( '-', 'wcrp-rental-products' ) . ' ' . __( 'If empty uses rental tab total overrides', 'wcrp-rental-products' ),
					'style'				=> $this->shared_field_attributes( '_wcrp_rental_products_total_overrides', 'style' ),
					'custom_attributes'	=> $this->shared_field_attributes( '_wcrp_rental_products_total_overrides', 'custom_attributes' ),
				)
			);

			woocommerce_wp_text_input(
				array(
					'id'				=> '_wcrp_rental_products_security_deposit_amount[' . $loop . ']',
					'class'				=> 'wcrp-rental-products-security-deposit-amount-variations ' . $this->shared_field_attributes( '_wcrp_rental_products_security_deposit_amount', 'class' ),
					'value'				=> get_post_meta( $variation->ID, '_wcrp_rental_products_security_deposit_amount', true ),
					'label'				=> $this->shared_field_attributes( '_wcrp_rental_products_security_deposit_amount', 'label' ),
					'desc_tip'			=> true,
					'description'		=> $this->shared_field_attributes( '_wcrp_rental_products_security_deposit_amount', 'description' ) . ' ' . __( 'If empty uses rental tab security deposit amount', 'wcrp-rental-products' ) . __( '.', 'wcrp-rental-products' ),
					'wrapper_class'		=> 'form-row', // We don't use form-row-first and form-row-last as some of these variation fields are conditionally shown/hidden depending on other options, so in some scenarios using them can cause blank sections/incorrectly floated fields
					'placeholder'		=> esc_html__( 'If empty uses rental tab security deposit amount', 'wcrp-rental-products' ),
				)
			);

			// Rental stock intentionally included at bottom as inventory fields (purchasable stock) are then directly after, so the stock fields are all together, note that we don't add this off of woocommerce_variation_options_inventory for the reasons in the __construct comment for this function

			woocommerce_wp_text_input(
				array(
					'type'				=> $this->shared_field_attributes( '_wcrp_rental_products_rental_stock', 'type' ),
					'id'				=> '_wcrp_rental_products_rental_stock[' . $loop . ']',
					'class'				=> 'wcrp-rental-products-rental-stock-variations',
					'value'				=> get_post_meta( $variation->ID, '_wcrp_rental_products_rental_stock', true ),
					'label'				=> $this->shared_field_attributes( '_wcrp_rental_products_rental_stock', 'label' ),
					'desc_tip'			=> true,
					'description'		=> $this->shared_field_attributes( '_wcrp_rental_products_rental_stock', 'description' ),
					'wrapper_class'		=> 'form-row', // We don't use form-row-first and form-row-last as some of these variation fields are conditionally shown/hidden depending on other options, so in some scenarios using them can cause blank sections/incorrectly floated fields
					'placeholder'		=> $this->shared_field_attributes( '_wcrp_rental_products_rental_stock', 'placeholder' ),
					'custom_attributes' => $this->shared_field_attributes( '_wcrp_rental_products_rental_stock', 'custom_attributes' ),
				)
			);

		}

		public function variation_data( $variations ) {

			$variation = wc_get_product( $variations[ 'variation_id' ] );

			if ( !empty( $variation ) ) {

				// Rent (used later to determine if the rental part of a rental or purchase product)

				$rent = false;

				if ( isset( $_GET['rent'] ) ) {

					if ( '1' == $_GET['rent'] ) {

						$rent = true;

					}

				}

				// Override $variation tax class/status early for rental or purchase products if overrides enabled

				if ( wcrp_rental_products_is_rental_purchase( $variation->get_parent_id() ) && true == $rent ) {

					$rental_purchase_rental_tax_override = get_post_meta( $variation->get_parent_id(), '_wcrp_rental_products_rental_purchase_rental_tax_override', true );

					if ( 'yes' == $rental_purchase_rental_tax_override ) {

						// The tax status/class must be updated on the $variation early, before wc_get_price_to_display

						$tax_status_override = get_post_meta( $variation->get_parent_id(), '_wcrp_rental_products_rental_purchase_rental_tax_override_status', true );
						$variation->set_tax_status( $tax_status_override );

						$tax_class_override = get_post_meta( $variation->get_parent_id(), '_wcrp_rental_products_rental_purchase_rental_tax_override_class', true );
						$variation->set_tax_class( $tax_class_override );

					}

				}

				// Prepare variables to be used for variation field data

				$default_rental_options = wcrp_rental_products_default_rental_options();
				$pricing_period_additional_selections = get_post_meta( $variations[ 'variation_id' ], '_wcrp_rental_products_pricing_period_additional_selections', true );

				if ( '' == $pricing_period_additional_selections ) {

					$pricing_period_additional_selections = get_post_meta( wp_get_post_parent_id( $variations[ 'variation_id' ] ), '_wcrp_rental_products_pricing_period_additional_selections', true );

				}

				$pricing_period_additional_selections = ( '' !== $pricing_period_additional_selections ? $pricing_period_additional_selections : $default_rental_options['_wcrp_rental_products_pricing_period_additional_selections'] );

				$total_overrides = get_post_meta( $variations[ 'variation_id' ], '_wcrp_rental_products_total_overrides', true );

				if ( '' == $total_overrides ) {

					$total_overrides = get_post_meta( wp_get_post_parent_id( $variations[ 'variation_id' ] ), '_wcrp_rental_products_total_overrides', true );

				}

				$total_overrides = ( '' !== $total_overrides ? $total_overrides : $default_rental_options['_wcrp_rental_products_total_overrides'] );

				/*
				Variation field data added, these are used so the data gets included in the variations JSON data on variable product pages, which is then used to update existing JS variables in the rental form depending on variation selected, there is other variation data in product_data_variations_tab_fields() not included here, these aren't included as they don't get used via the JSON data on variable product pages

				wcrp_rental_products_rental_purchase_price has to use wc_get_price_to_display() so that the price is correct as per the tax settings (e.g. if prices entered inc tax but displayed exc tax it returns the price exc tax)

				wcrp_rental_products_pricing_period_additional_selections and wcrp_rental_products_total_overrides is via WCRP_Rental_Products_Misc::value_colon_price_pipe_explode() and output as JSON, unlike wcrp_rental_products_rental_purchase_price the prices for these 2 aren't converted to inc/exc tax, see comments in WCRP_Rental_Products_Misc::value_colon_price_pipe_explode() why, it does however convert decimals to . as the return sould be used in further calculations and then gets converted back to the store decimal on display
				*/

				$rental_purchase_price = get_post_meta( $variations[ 'variation_id' ], '_wcrp_rental_products_rental_purchase_price', true );

				if ( '' !== $rental_purchase_price ) { // '' !== is used to scenario where 0.00 wanted as a price

					$variations['wcrp_rental_products_rental_purchase_price'] = wc_get_price_to_display( $variation, array( 'price' => str_replace( wc_get_price_decimal_separator(), '.', $rental_purchase_price ) ) ); // Has to have decimal separator replaced as wc_get_price_to_display() expects in . format, without doing this if using a non . seperator then for prices like 99,99 wc_get_price_to_display() would calculate based off 99, not 99,99

				} else {

					// If there isn't a rental or purchase purchasable price then return error, this is then used to ensure rentalPrice becomes NaN to trigger NaN conditions in rental form

					$variations['wcrp_rental_products_rental_purchase_price'] = 'error';

				}

				$variations['wcrp_rental_products_pricing_period_additional_selections'] = WCRP_Rental_Products_Misc::value_colon_price_pipe_explode( $pricing_period_additional_selections, true );
				$variations['wcrp_rental_products_total_overrides'] = WCRP_Rental_Products_Misc::value_colon_price_pipe_explode( $total_overrides, true );

			}

			return $variations;

		}

		public static function shared_field_attributes( $id, $attribute ) {

			global $pagenow;

			$is_settings = false; // Changed to true if it's the settings page, this is used to condition and format the shared fields differently, e.g. settings doesn't render HTML markup, but add/edit product page does, some of these shared field attributes are used in both the product and setting pages, for the ones that are setings in this scenario we need to amend the attributes to not have HTML

			if ( 'admin.php' == $pagenow ) {

				if ( isset( $_GET['page'] ) ) {

					if ( 'wc-settings' == sanitize_text_field( $_GET['page'] ) ) { // WooCommerce > Settings

						$is_settings = true;

					}

				}

			}

			$currency_symbol = get_woocommerce_currency_symbol();
			$price_decimal_separator = wc_get_price_decimal_separator();

			// The array of shared field attributes below are used when attributes of the field are used across multiple fields and therefore the label, description, etc (and any other attribute thats the same across both) can be got one from this one location rather than manually typing these in the field instances to ensure consistency, e.g. where there is a parent variable field and equivalent field for a variation or if, for example, a field is in the add/edit product page and there is an equivalent field in rental settings

			$shared_field_attributes = array();

			$shared_field_attributes['_wcrp_rental_products_rental_purchase_price'] = array(
				'class'			=> 'wc_input_price',
				// translators: %s: currency symbol
				'label'			=> wp_kses_post( sprintf( __( 'Rental price (%s)', 'wcrp-rental-products' ), $currency_symbol ) ),
				'description'	=> esc_html__( 'Set the rental price.', 'wcrp-rental-products' ),
			);

			$shared_field_attributes['_wcrp_rental_products_rental_stock'] = array(
				'label'				=> esc_html__( 'Rental stock (in and out)', 'wcrp-rental-products' ),
				'description'		=> esc_html__( 'Enter total stock available for rental. This is used for availability calculations and does not fluctuate as rentals are reserved/returned. Rental stock for every possible future date is calculated from this overall total minus any rentals reserved, disabled dates, etc. Any other stock options displayed are for non-rentals. Set to empty for unlimited.', 'wcrp-rental-products' ),
				'type'				=> 'number',
				'custom_attributes' => array(
					'min'			=> '0',
					'oninput'		=> "this.value = this.value.replace( /[^0-9]/g, '' )", // Stops decimals being used
					'step'			=> '1',
				),
			);

			$shared_field_attributes['_wcrp_rental_products_pricing_period_additional_selections'] = array(
				'class'			=> 'wcrp-rental-products-value-colon-price-validation short',
				'label'			=> esc_html__( 'Pricing period additional selections', 'wcrp-rental-products' ),
				// translators: %1$s: currency symbol, %2$s: price decimal separator
				'description'	=> sprintf( esc_html__( 'Pricing period additional selections are used in addition to the lowest period set on pricing period (days). Enter in format of 3:5%2$s00|7:10%2$s00 (this example adds 2 additional pricing period selections, a 3 day rental for %1$s5%2$s00 and a 7 day rental for %1$s10%2$s00).', 'wcrp-rental-products' ), $currency_symbol, $price_decimal_separator ),
				// translators: %1$s: price decimal separator
				'placeholder'	=> sprintf( esc_html__( 'e.g. 3:5%1$s00|7:10%1$s00', 'wcrp-rental-products' ), $price_decimal_separator ),
				'style'			=> 'height: 70px;',
				'custom_attributes'	=> array(
					'data-price-decimal-separator'	=> $price_decimal_separator,
					'data-validation-type'			=> 'pricing_period_additional_selections',
				),
			);

			$shared_field_attributes['_wcrp_rental_products_total_overrides'] = array(
				'class'			=> 'wcrp-rental-products-value-colon-price-validation short',
				'label'			=> esc_html__( 'Total overrides', 'wcrp-rental-products' ),
				// translators: %1$s: currency symbol, %2$s: price decimal separator
				'description'	=> sprintf( esc_html__( 'Overrides the total based on the number of days rented. Enter in format of 1:5%2$s00|2:10%2$s00 (this example makes a 1 day rental %1$s5%2$s00 and a 2 day rental %1$s10%2$s00). When using total overrides the rental price display remains as calculated based off the non-overriden pricing, therefore it is recommended you also use a price display override.', 'wcrp-rental-products' ), $currency_symbol, $price_decimal_separator ),
				// translators: %1$s: price decimal separator
				'placeholder'	=> sprintf( esc_html__( 'e.g. 1:5%1$s00|2:10%1$s00', 'wcrp-rental-products' ), $price_decimal_separator ),
				'style'			=> 'height: 70px;',
				'custom_attributes'	=> array(
					'data-price-decimal-separator'	=> $price_decimal_separator,
					'data-validation-type'			=> 'total_overrides',
				),
			);

			$shared_field_attributes['_wcrp_rental_products_advanced_pricing'] = array(
				'label'			=> esc_html__( 'Advanced pricing', 'wcrp-rental-products' ),
				'description'	=> esc_html__( 'Set if advanced pricing calculations should be used.', 'wcrp-rental-products' ) . ( true == $is_settings ? ' ' . esc_html__( 'Before enabling this setting, see the related information under this option when adding/editing a rental product.', 'wcrp-rental-products' ) : '' ),
				'options'		=> array(
					'default'	=> __( 'Use default from rental settings', 'wcrp-rental-products' ),
					'off'		=> __( 'Off', 'wcrp-rental-products' ),
					'on'		=> __( 'On', 'wcrp-rental-products' ),
				),
			);

			$shared_field_attributes['_wcrp_rental_products_in_person_pick_up_return_time_restrictions'] = array(
				'label'			=> esc_html__( 'In person pick up/return time restrictions', 'wcrp-rental-products' ),
				'description'	=> esc_html__( 'When restricted the in person pick up times set below must be lower/higher than return times (see tooltip information of each option). If not set correctly the in person pick up/return will be unavailable. When unrestricted they can be set without any restrictions. Restricted is recommended to reduce risk of overlaps in availability. In scenarios where there is unlimited rental stock or there are rental stock reserves to account for overlaps in availability, you may wish to set this to unrestricted.', 'wcrp-rental-products' ),
				'options'		=> array(
					'default'		=> __( 'Use default from rental settings', 'wcrp-rental-products' ),
					'restricted'	=> __( 'Restricted', 'wcrp-rental-products' ),
					'unrestricted'	=> __( 'Unrestricted', 'wcrp-rental-products' ),
				),
			);

			$shared_field_attributes['_wcrp_rental_products_in_person_return_date'] = array(
				'label'			=> esc_html__( 'In person return date', 'wcrp-rental-products' ),
				'description'	=> esc_html__( 'Set when an in person return should occur.', 'wcrp-rental-products' ) . ( true == $is_settings ? ' ' . esc_html__( 'The in person pick up and return times/fees below are based on the in person return date selected above. You may wish to populate the settings for both options to cover all product data scenarios.', 'wcrp-rental-products' ) : '' ),
				'options'		=> array(
					'default'	=> __( 'Use default from rental settings', 'wcrp-rental-products' ),
					'same_day'	=> __( 'Same day (rent to date)', 'wcrp-rental-products' ),
					'next_day'	=> __( 'Next day (rent to date + 1 day)', 'wcrp-rental-products' ),
				),
			);

			$shared_field_attributes['_wcrp_rental_products_in_person_pick_up_times_fees_same_day'] = array(
				'class'				=> 'wcrp-rental-products-value-colon-price-validation short',
				'label'				=> esc_html__( 'In person pick up times/fees (multiple day rentals)', 'wcrp-rental-products' ) . ( false == $is_settings ? '<span class="wcrp-rental-products-panel-field-label-description">' . esc_html__( 'Used only when in person return date is SAME DAY', 'wcrp-rental-products' ) . '</span>' : '' ),
				// translators: %1$s: currency symbol, %2$s: price decimal separator
				'description'		=> sprintf( esc_html__( 'Set times/fees to pick up the rental. Times should be higher than "in person return times/fees (multiple day rentals)". First time/fee entered is the default selection. Enter in format of 1300:5%2$s00|1500:0%2$s00 (this example would allow selection of 2 pick up times, one at 13:00 with a fee of %1$s5%2$s00 and one at 15:00 with a fee of %1$s0%2$s00).', 'wcrp-rental-products' ), $currency_symbol, $price_decimal_separator ),
				// translators: %1$s: price decimal separator
				'placeholder'		=> sprintf( esc_html__( 'e.g. 1300:5%1$s00|1500:0%1$s00', 'wcrp-rental-products' ), $price_decimal_separator ),
				'style'				=> 'height: 70px;',
				'custom_attributes'	=> array(
					'data-price-decimal-separator'	=> $price_decimal_separator,
					'data-validation-type'			=> 'times_fees',
				),
			);

			$shared_field_attributes['_wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day'] = array(
				'class'				=> 'wcrp-rental-products-value-colon-price-validation short',
				'label'				=> esc_html__( 'In person pick up times/fees (single day rentals)', 'wcrp-rental-products' ) . ( false == $is_settings ? '<span class="wcrp-rental-products-panel-field-label-description">' . esc_html__( 'Used only when in person return date is SAME DAY', 'wcrp-rental-products' ) . '</span>' : '' ),
				// translators: %1$s: currency symbol, %2$s: price decimal separator
				'description'		=> sprintf( esc_html__( 'Set times/fees to pick up the rental. Times should be lower than "in person return times/fees (single day rentals)". First time/fee entered is the default selection. Enter in format of 1300:5%2$s00|1500:0%2$s00 (this example would allow selection of 2 pick up times, one at 13:00 with a fee of %1$s5%2$s00 and one at 15:00 with a fee of %1$s0%2$s00).', 'wcrp-rental-products' ), $currency_symbol, $price_decimal_separator ),
				// translators: %1$s: price decimal separator
				'placeholder'		=> sprintf( esc_html__( 'e.g. 1300:5%1$s00|1500:0%1$s00', 'wcrp-rental-products' ), $price_decimal_separator ),
				'style'				=> 'height: 70px;',
				'custom_attributes'	=> array(
					'data-price-decimal-separator'	=> $price_decimal_separator,
					'data-validation-type'			=> 'times_fees',
				),
			);

			$shared_field_attributes['_wcrp_rental_products_in_person_return_times_fees_same_day'] = array(
				'class'				=> 'wcrp-rental-products-value-colon-price-validation short',
				'label'				=> esc_html__( 'In person return times/fees (multiple day rentals)', 'wcrp-rental-products' ) . ( false == $is_settings ? '<span class="wcrp-rental-products-panel-field-label-description">' . esc_html__( 'Used only when in person return date is SAME DAY', 'wcrp-rental-products' ) . '</span>' : '' ),
				// translators: %1$s: currency symbol, %2$s: price decimal separator
				'description'		=> sprintf( esc_html__( 'Set times/fees to return the rental. Times should be lower than "in person pick up times/fees (multiple day rentals)". First time/fee entered is the default selection. Enter in format of 1030:0%2$s00|1230:5%2$s00 (this example would allow selection of 2 return times, one at 10:30 with a fee of %1$s0%2$s00 and one at 12:30 with a fee of %1$s5%2$s00).', 'wcrp-rental-products' ), $currency_symbol, $price_decimal_separator ),
				// translators: %1$s: price decimal separator
				'placeholder'		=> sprintf( esc_html__( 'e.g. 1030:0%1$s00|1230:5%1$s00', 'wcrp-rental-products' ), $price_decimal_separator ),
				'style'				=> 'height: 70px;',
				'custom_attributes' => array(
					'data-price-decimal-separator'	=> $price_decimal_separator,
					'data-validation-type'			=> 'times_fees',
				),
			);

			$shared_field_attributes['_wcrp_rental_products_in_person_return_times_fees_single_day_same_day'] = array(
				'class'				=> 'wcrp-rental-products-value-colon-price-validation short',
				'label'				=> esc_html__( 'In person return times/fees (single day rentals)', 'wcrp-rental-products' ) . ( false == $is_settings ? '<span class="wcrp-rental-products-panel-field-label-description">' . esc_html__( 'Used only when in person return date is SAME DAY', 'wcrp-rental-products' ) . '</span>' : '' ),
				// translators: %1$s: currency symbol, %2$s: price decimal separator
				'description'		=> sprintf( esc_html__( 'Set times/fees to return the rental. Times should be higher than "in person pick up times/fees (single day rentals)". First time/fee entered is the default selection. Enter in format of 1700:0%2$s00|1900:5%2$s00 (this example would allow selection of 2 return times, one at 17:00 with a fee of %1$s0%2$s00 and one at 19:00 with a fee of %1$s5%2$s00).', 'wcrp-rental-products' ), $currency_symbol, $price_decimal_separator ),
				// translators: %1$s: price decimal separator
				'placeholder'		=> sprintf( esc_html__( 'e.g. 1700:0%1$s00|1900:5%1$s00', 'wcrp-rental-products' ), $price_decimal_separator ),
				'style'				=> 'height: 70px;',
				'custom_attributes' => array(
					'data-price-decimal-separator'	=> $price_decimal_separator,
					'data-validation-type'			=> 'times_fees',
				),
			);

			$shared_field_attributes['_wcrp_rental_products_in_person_pick_up_times_fees_next_day'] = array(
				'class'				=> 'wcrp-rental-products-value-colon-price-validation short',
				'label'				=> esc_html__( 'In person pick up times/fees', 'wcrp-rental-products' ) . ( false == $is_settings ? '<span class="wcrp-rental-products-panel-field-label-description">' . esc_html__( 'Used only when in person return date is NEXT DAY', 'wcrp-rental-products' ) . '</span>' : '' ),
				// translators: %1$s: currency symbol, %2$s: price decimal separator
				'description'		=> sprintf( esc_html__( 'Set times/fees to pick up the rental. Times should be higher than "in person return times/fees". First time/fee entered is the default selection. Enter in format of 1300:5%2$s00|1500:0%2$s00 (this example would allow selection of 2 pick up times, one at 13:00 with a fee of %1$s5%2$s00 and one at 15:00 with a fee of %1$s0%2$s00).', 'wcrp-rental-products' ), $currency_symbol, $price_decimal_separator ),
				// translators: %1$s: price decimal separator
				'placeholder'		=> sprintf( esc_html__( 'e.g. 1300:5%1$s00|1500:0%1$s00', 'wcrp-rental-products' ), $price_decimal_separator ),
				'style'				=> 'height: 70px;',
				'custom_attributes' => array(
					'data-price-decimal-separator'	=> $price_decimal_separator,
					'data-validation-type'			=> 'times_fees',
				),
			);

			$shared_field_attributes['_wcrp_rental_products_in_person_return_times_fees_next_day'] = array(
				'class'				=> 'wcrp-rental-products-value-colon-price-validation short',
				'label'				=> esc_html__( 'In person return times/fees', 'wcrp-rental-products' ) . ( false == $is_settings ? '<span class="wcrp-rental-products-panel-field-label-description">' . esc_html__( 'Used only when in person return date is NEXT DAY', 'wcrp-rental-products' ) . '</span>' : '' ),
				// translators: %1$s: currency symbol, %2$s: price decimal separator
				'description'		=> sprintf( esc_html__( 'Set times/fees to return the rental. Times should be lower than "in person pick up times/fees". First time/fee entered is the default selection. Enter in format of 1030:0%2$s00|1230:5%2$s00 (this example would allow selection of 2 return times, one at 10:30 with a fee of %1$s0%2$s00 and one at 12:30 with a fee of %1$s5%2$s00).', 'wcrp-rental-products' ), $currency_symbol, $price_decimal_separator ),
				// translators: %1$s: price decimal separator
				'placeholder'		=> sprintf( esc_html__( 'e.g. 1030:0%1$s00|1230:5%1$s00', 'wcrp-rental-products' ), $price_decimal_separator ),
				'style'				=> 'height: 70px;',
				'custom_attributes' => array(
					'data-price-decimal-separator'	=> $price_decimal_separator,
					'data-validation-type'			=> 'times_fees',
				),
			);

			$shared_field_attributes['_wcrp_rental_products_security_deposit_amount'] = array(
				'class'			=> 'wc_input_price',
				'label'			=> esc_html__( 'Security deposit amount', 'wcrp-rental-products' ),
				'description'	=> esc_html__( 'Security deposits are paid during checkout and may be manually refunded to the customer upon satisfactory return of the product. For further details on refunding a security deposit see the managing rental orders information when editing an order. Enter a monetary amount.', 'wcrp-rental-products' ),
			);

			if ( isset( $shared_field_attributes[$id][$attribute] ) ) {

				return $shared_field_attributes[$id][$attribute];

			} else {

				return false;

			}

		}

		public function pricing_tiers_data_fields( $value_days, $value_percent ) {

			?>

			<div class="options_group">
				<p class="form-field">
					<label for="<?php // Added dynamically ?>" class="wcrp-rental-products-pricing-tiers-data-days-label"><?php esc_html_e( 'Days greater than', 'wcrp-rental-products' ); ?></label>
					<input id="<?php // Added dynamically ?>" class="wcrp-rental-products-pricing-tiers-data-days" name="_wcrp_rental_products_pricing_tiers_data[days][]" type="number" step="1" min="1" value="<?php echo esc_html( $value_days ); ?>">
				</p>
				<p class="form-field">
					<label for="<?php // Added dynamically ?>" class="wcrp-rental-products-pricing-tiers-data-percent-label"><?php esc_html_e( 'Percent', 'wcrp-rental-products' ); ?></label>
					<input id="<?php // Added dynamically ?>" class="wcrp-rental-products-pricing-tiers-data-percent" name="_wcrp_rental_products_pricing_tiers_data[percent][]" type="number" step="any" value="<?php echo esc_html( $value_percent ); ?>">
				</p>
				<button class="wcrp-rental-products-pricing-tiers-data-remove-pricing-tier button button-small" data-click-text="<?php esc_html_e( 'Removing...', 'wcrp-rental-products' ); ?>" data-alert-text="<?php esc_html_e( 'Are you sure you want to remove this pricing tier?', 'wcrp-rental-products' ); ?>"><?php esc_html_e( 'Remove pricing tier', 'wcrp-rental-products' ); ?></button>
			</div>

			<?php

		}

	}

}
