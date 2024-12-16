<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Product_Bulk_Edits' ) ) {

	class WCRP_Rental_Products_Product_Bulk_Edits {

		public function __construct() {

			add_action( 'woocommerce_product_bulk_edit_end', array( $this, 'bulk_edit') );
			add_action( 'woocommerce_product_bulk_edit_save', array( $this, 'bulk_edit_save' ) );
			add_action( 'woocommerce_variable_product_bulk_edit_actions', array( $this, 'bulk_edit_variations' ) );
			add_action( 'admin_footer', array( $this, 'bulk_edit_variations_script' ) );
			add_action( 'woocommerce_bulk_edit_variations', array( $this , 'bulk_edit_variations_save' ), 10, 4 );

		}

		public function bulk_edit() {

			?>

			<div class="inline-edit-group">
				<label class="alignleft">
					<span class="title"><?php esc_html_e( 'Rental price', 'wcrp-rental-products' ); ?></span>
					<span class="input-text-wrap">
						<select class="change__wcrp_rental_products_rental_purchase_price change_to" name="change__wcrp_rental_products_rental_purchase_price">
							<?php
							$options = array(
								''  => esc_html__( '— No change —', 'wcrp-rental-products' ),
								'1' => esc_html__( 'Change to:', 'wcrp-rental-products' ),
							);
							foreach ( $options as $option_key => $option_value ) {
								echo '<option value="' . esc_attr( $option_key ) . '">' . esc_html( $option_value ) . '</option>';
							}
							?>
						</select>
					</span>
				</label>
				<label class="change-input">
					<p class="description"><?php esc_html_e( 'Used for the rental part of rental or purchase products, if a rental only product use the price option to set the rental price. Only updates if product type is simple, for bulk variable product updates consider a product import.', 'wcrp-rental-products' ); ?></p>
					<?php // translators: %s: currency symbol ?>
					<input type="text" name="_wcrp_rental_products_rental_purchase_price" class="text" placeholder="<?php printf( esc_attr__( 'Enter rental price (%s)', 'wcrp-rental-products' ), esc_html( get_woocommerce_currency_symbol() ) ); ?>" value="" />
				</label>
			</div>
			<div class="inline-edit-group">
				<label class="alignleft">
					<span class="title"><?php esc_html_e( 'Rental stock', 'wcrp-rental-products' ); ?></span>
					<span class="input-text-wrap">
						<select class="change__wcrp_rental_products_rental_stock change_to" name="change__wcrp_rental_products_rental_stock">
							<?php
							$options = array(
								''  => esc_html__( '— No change —', 'wcrp-rental-products' ),
								'1' => esc_html__( 'Change to:', 'wcrp-rental-products' ),
							);
							foreach ( $options as $option_key => $option_value ) {
								echo '<option value="' . esc_attr( $option_key ) . '">' . esc_html( $option_value ) . '</option>';
							}
							?>
						</select>
					</span>
				</label>
				<label class="change-input">
					<p class="description"><?php esc_html_e( 'Only updates if product type is simple, for bulk variable product updates consider a product import.', 'wcrp-rental-products' ); ?></p>
					<input type="text" name="_wcrp_rental_products_rental_stock" class="text" placeholder="<?php echo esc_attr__( 'Enter rental stock', 'wcrp-rental-products' ); ?>" value="" />
				</label>
			</div>

			<?php

		}

		public function bulk_edit_save( $product ) {

			$post_id = $product->get_id();
			$product_type = $product->get_type();

			if ( 'simple' == $product_type ) {

				if ( isset( $_REQUEST['_wcrp_rental_products_rental_purchase_price'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_rental_purchase_price', sanitize_text_field( $_REQUEST['_wcrp_rental_products_rental_purchase_price'] ) );

				}

				if ( isset( $_REQUEST['_wcrp_rental_products_rental_stock'] ) ) {

					update_post_meta( $post_id, '_wcrp_rental_products_rental_stock', sanitize_text_field( $_REQUEST['_wcrp_rental_products_rental_stock'] ) );

				}

			}

		}

		public function bulk_edit_variations() {

			// Values/option names below match the variation field names, some have the rental prefix added (see comments in WCRP_Rental_Products_Product_Fields::product_data_variations_tab_fields() for why), note that these options are conditionally displayed depending on product type, pricing type, etc in WCRP_Rental_Products_Product_Fields::product_data_panel(), see toggleRentalOptions() JS function

			?>

			<optgroup label="<?php esc_attr_e( 'Rental', 'wcrp-rental-products' ); ?>" id="wcrp-rental-products-bulk-edit-variations">
				<option value="variable_rental_price"><?php esc_html_e( 'Set rental price', 'wcrp-rental-products' ); ?></option>
				<option value="variable_rental_stock"><?php esc_html_e( 'Set rental stock', 'wcrp-rental-products' ); ?></option>
				<option value="variable_rental_pricing_period_additional_selections"><?php esc_html_e( 'Set rental pricing period additional selections', 'wcrp-rental-products' ); ?></option>
				<option value="variable_rental_total_overrides"><?php esc_html_e( 'Set rental total overrides', 'wcrp-rental-products' ); ?></option>
				<option value="variable_rental_security_deposit_amount"><?php esc_html_e( 'Set rental security deposit amount', 'wcrp-rental-products' ); ?></option>
			</optgroup>

			<?php

		}

		public function bulk_edit_variations_script() {

			global $pagenow;
			global $post;

			// This script shows a field entry when the options in bulk_edit_variations() are selected, it then passes the data to bulk_edit_variations_save(), this script cannot be included in bulk_edit_variations(), it previously was but we found that it didn't work on creation of products, but did after they were saved, assumedly because the bulk actions select field gets populated via AJAX and it was stripping out this script

			if ( 'product' == get_post_type( $post ) && ( 'post-new.php' == $pagenow || 'post.php' == $pagenow ) ) {

				?>

				<script>

					jQuery( document ).ready( function( $ ) {

						function rentalBulkEditVariations() {

							<?php // On the set based options below there is no value !== null conditions because the woocommerce_bulk_edit_variations hook gets run anyway and saves values even if we had a !== null condition with a return instead of sending the AJAX request, so we always send the AJAX request and bulk_edit_variations_save() deals with the data, if the prompt has no value due to cancelling the prompt or if it is empty then the AJAX data below sends an empty value but it doesn't then get that empty value set in bulk_edit_variations_save() due to !empty conditions in it, if wanting to set an empty value this can be achieved instead using the dedicated empty based bulk edit variation options ?>

							if ( 'variable_rental_price' == $( '#variable_product_options .variation_actions' ).val() ) {

								value = window.prompt( "<?php esc_html_e( 'Enter rental price', 'wcrp-rental-products' ); ?>" );

								if ( value == '' ) { <?php // If value is blank and therefore option to be set as empty, this condition must come before value == 0 condition or classed as empty ?>

									value = 'set_empty';

								} else if ( value == 0 ) { <?php // If value is 0, 0.00, etc, this does not include null which occurs if cancelling the prompt, this is passed as null and gets dealt with in bulk_edit_variations_save() ?>

									value = 'zero'; <?php // Not left as 0 as would cause it to be passed as false causing complications for bulk_edit_variations_save() seeing it as false and not zero ?>

								}

								$.ajax({
									data: {
										action:       'woocommerce_bulk_edit_variations',
										bulk_action:  'variable_rental_price',
										data:         value,
										product_id:   <?php echo esc_html( $post->ID ); ?>,
										product_type: $( '#product-type' ).val(),
										security:     woocommerce_admin_meta_boxes_variations.bulk_edit_variations_nonce,
									},
									type:	'POST',
									url:	woocommerce_admin_meta_boxes_variations.ajax_url,
									success: function( data ) {
										$( '.variations-pagenav .page-selector' ).val( 1 ).first().change();
									},
								});

							} else if ( 'variable_rental_stock' == $( '#variable_product_options .variation_actions' ).val() ) {

								value = window.prompt( "<?php esc_html_e( 'Enter rental stock', 'wcrp-rental-products' ); ?>" );

								if ( value == '' ) { <?php // See comments on this same code block in variable_rental_price above for why these conditions exists ?>

									value = 'set_empty';

								} else if ( value == 0 ) {

									value = 'zero';

								}

								$.ajax({
									data: {
										action:			'woocommerce_bulk_edit_variations',
										bulk_action:	'variable_rental_stock',
										data:			value,
										product_id:		<?php echo esc_html( $post->ID ); ?>,
										product_type:	$( '#product-type' ).val(),
										security:		woocommerce_admin_meta_boxes_variations.bulk_edit_variations_nonce,
									},
									type:	'POST',
									url:	woocommerce_admin_meta_boxes_variations.ajax_url,
									success: function( data ) {
										$( '.variations-pagenav .page-selector' ).val( 1 ).first().change();
									},
								});

							} else if ( 'variable_rental_pricing_period_additional_selections' == $( '#variable_product_options .variation_actions' ).val() ) {

								value = window.prompt( "<?php esc_html_e( 'Enter pricing period additional selections - for format see the tooltip on this field when editing a variation', 'wcrp-rental-products' ); ?>" );

								if ( value == '' ) { <?php // See comments on this same code block in variable_rental_price above for why these conditions exists, zero condition not included here as entering a 0 isn't a correct format ?>

									value = 'set_empty';

								}

								$.ajax({
									data: {
										action:			'woocommerce_bulk_edit_variations',
										bulk_action:	'variable_rental_pricing_period_additional_selections',
										data:			value,
										product_id:		<?php echo esc_html( $post->ID ); ?>,
										product_type:	$( '#product-type' ).val(),
										security:		woocommerce_admin_meta_boxes_variations.bulk_edit_variations_nonce,
									},
									type:	'POST',
									url:	woocommerce_admin_meta_boxes_variations.ajax_url,
									success: function( data ) {
										$( '.variations-pagenav .page-selector' ).val( 1 ).first().change();
									}
								});

							} else if ( 'variable_rental_total_overrides' == $( '#variable_product_options .variation_actions' ).val() ) {

								value = window.prompt( "<?php esc_html_e( 'Enter total overrides - for format see the tooltip on this field when editing a variation', 'wcrp-rental-products' ); ?>" );

								if ( value == '' ) { <?php // See comments on this same code block in variable_rental_price above for why these conditions exists, zero condition not included here as entering a 0 isn't a correct format ?>

									value = 'set_empty';

								}

								$.ajax({
									data: {
										action:			'woocommerce_bulk_edit_variations',
										bulk_action:	'variable_rental_total_overrides',
										data:			value,
										product_id:		<?php echo esc_html( $post->ID ); ?>,
										product_type:	$( '#product-type' ).val(),
										security:		woocommerce_admin_meta_boxes_variations.bulk_edit_variations_nonce,
									},
									type:	'POST',
									url:	woocommerce_admin_meta_boxes_variations.ajax_url,
									success: function( data ) {
										$( '.variations-pagenav .page-selector' ).val( 1 ).first().change();
									}
								});

							} else if ( 'variable_rental_security_deposit_amount' == $( '#variable_product_options .variation_actions' ).val() ) {

								value = window.prompt( "<?php esc_html_e( 'Enter security deposit amount', 'wcrp-rental-products' ); ?>" );

								if ( value == '' ) { <?php // See comments on this same code block in variable_rental_price above for why these conditions exists ?>

									value = 'set_empty';

								} else if ( value == 0 ) {

									value = 'zero';

								}

								$.ajax({
									data: {
										action:			'woocommerce_bulk_edit_variations',
										bulk_action:	'variable_rental_security_deposit_amount',
										data:			value,
										product_id:		<?php echo esc_html( $post->ID ); ?>,
										product_type:	$( '#product-type' ).val(),
										security:		woocommerce_admin_meta_boxes_variations.bulk_edit_variations_nonce,
									},
									type:	'POST',
									url:	woocommerce_admin_meta_boxes_variations.ajax_url,
									success: function( data ) {
										$( '.variations-pagenav .page-selector' ).val( 1 ).first().change();
									},
								});

							}

						}

						$( document ).on( 'change', '#variable_product_options .variation_actions', function( e ) {

							rentalBulkEditVariations();

						});

						$( document ).on( 'click', '#variable_product_options .do_variation_action', function( e ) {

							<?php // Somewhere around WooCommerce 7.7.0 the .do_variation_action (button next to the variations bulk action select field) was removed and replaced to show data entry on change of the .variation_actions select field instead, this condition targeting the old button remains for older versions of WooCommerce ?>

							rentalBulkEditVariations();

						});

					});

				</script>

				<?php

			}

		}

		public function bulk_edit_variations_save( $bulk_action, $data, $product_id, $variations ) {

			// There is no check done for if a rental only or rental or purchase product type for the overall product as a user could change to rental or purchase setting and attempt to use the bulk action before product saved

			if ( 'variable_rental_price' == $bulk_action && isset( $data ) && is_array( $variations ) ) {

				foreach ( $variations as $variation ) {

					if ( 'zero' == $data ) {

						update_post_meta( $variation, '_wcrp_rental_products_rental_purchase_price', number_format( 0, wc_get_price_decimals(), wc_get_price_decimal_separator(), '' ) ); // 0 in correct format

					} elseif ( 'set_empty' == $data ) {

						update_post_meta( $variation, '_wcrp_rental_products_rental_purchase_price', '' );

					} elseif ( !empty( $data ) ) { // As would be empty if value from prompt is null due to cancelling the prompt (would occur via cancelling prompt through our JS and/or as WooCommerce does its own AJAX request (action: woocommerce_bulk_edit_variations) that causes a null value when go button clicked on top of our JS)

						update_post_meta( $variation, '_wcrp_rental_products_rental_purchase_price', number_format( (float) str_replace( wc_get_price_decimal_separator(), '.', $data ), wc_get_price_decimals(), wc_get_price_decimal_separator(), '' ) ); // Basic validation that data in correct format

					}

				}

			} elseif ( 'variable_rental_stock' == $bulk_action && isset( $data ) && is_array( $variations ) ) {

				foreach ( $variations as $variation ) {

					if ( 'zero' == $data ) {

						update_post_meta( $variation, '_wcrp_rental_products_rental_stock', '0' );

					} elseif ( 'set_empty' == $data ) {

						update_post_meta( $variation, '_wcrp_rental_products_rental_stock', '' );

					} elseif ( !empty( $data ) ) { // As would be empty if value from prompt is null due to cancelling the prompt (would occur via cancelling prompt through our JS and/or as WooCommerce does its own AJAX request (action: woocommerce_bulk_edit_variations) that causes a null value when go button clicked on top of our JS)

						update_post_meta( $variation, '_wcrp_rental_products_rental_stock', (int) $data ); // Basic validation that data in correct format

					}

				}

			} elseif ( 'variable_rental_pricing_period_additional_selections' == $bulk_action && isset( $data ) && is_array( $variations ) ) {

				foreach ( $variations as $variation ) {

					if ( 'set_empty' == $data ) {

						update_post_meta( $variation, '_wcrp_rental_products_pricing_period_additional_selections', '' );

					} elseif ( !empty( $data ) ) { // As would be empty if value from prompt is null due to cancelling the prompt (would occur via cancelling prompt through our JS and/or as WooCommerce does its own AJAX request (action: woocommerce_bulk_edit_variations) that causes a null value when go button clicked on top of our JS)

						update_post_meta( $variation, '_wcrp_rental_products_pricing_period_additional_selections', WCRP_Rental_Products_Misc::string_contains( $data, ':' ) ? $data : '' ); // Basic validation that data in correct format

					}

				}

			} elseif ( 'variable_rental_total_overrides' == $bulk_action && isset( $data ) && is_array( $variations ) ) {

				foreach ( $variations as $variation ) {

					if ( 'set_empty' == $data ) {

						update_post_meta( $variation, '_wcrp_rental_products_total_overrides', '' );

					} elseif ( !empty( $data ) ) { // As would be empty if value from prompt is null due to cancelling the prompt (would occur via cancelling prompt through our JS and/or as WooCommerce does its own AJAX request (action: woocommerce_bulk_edit_variations) that causes a null value when go button clicked on top of our JS)

						update_post_meta( $variation, '_wcrp_rental_products_total_overrides', WCRP_Rental_Products_Misc::string_contains( $data, ':' ) ? $data : '' ); // Basic validation that data in correct format

					}

				}

			} elseif ( 'variable_rental_security_deposit_amount' == $bulk_action && isset( $data ) && is_array( $variations ) ) {

				foreach ( $variations as $variation ) {

					if ( 'zero' == $data ) {

						update_post_meta( $variation, '_wcrp_rental_products_security_deposit_amount', number_format( 0, wc_get_price_decimals(), wc_get_price_decimal_separator(), '' ) );

					} elseif ( 'set_empty' == $data ) {

						update_post_meta( $variation, '_wcrp_rental_products_security_deposit_amount', '' );

					} elseif ( !empty( $data ) ) { // As would be empty if value from prompt is null due to cancelling the prompt (would occur via cancelling prompt through our JS and/or as WooCommerce does its own AJAX request (action: woocommerce_bulk_edit_variations) that causes a null value when go button clicked on top of our JS)

						update_post_meta( $variation, '_wcrp_rental_products_security_deposit_amount', number_format( (float) str_replace( wc_get_price_decimal_separator(), '.', $data ), wc_get_price_decimals(), wc_get_price_decimal_separator(), '' ) ); // Basic validation that data in correct format

					}

				}

			}

		}

	}

}
