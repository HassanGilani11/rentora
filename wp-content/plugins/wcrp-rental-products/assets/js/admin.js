jQuery( document ).ready( function( $ ) {

	// Translation

	const { __, _x, _n, _nx, sprintf } = wp.i18n;

	// Orders > add rental product(s)

	$( document ).on( 'click', '#wcrp-rental-products-add-rental-products', function( e ) {

		if ( $( this ).attr( 'data-new-order' ) == 'yes' ) {

			// If a new order

			// Rental products cannot be added to new orders as it's possible the order window could be closed without creation and the order is then removed, orders which have not yet been created wouldn't get the stock reserved in the rentals database table (as even though an order id (post id) exists for it the rows do not get created off this, assumedly because WC_Order isn't valid as order not yet created), so if a rental with 1 stock was added to an order which has not yet been created it's possible to add another one even though the quantity is only 1

			notice = __( 'Order must be created initially before rental products can be added. You can create the order as pending initially and then change the order status as required once the order is populated.', 'wcrp-rental-products' );
			notice += '\n\n' + __( 'The order must be created because rental items added are reserved immediately which requires the order to exist, they are reserved immediately to ensure availability is correct for both this order and customers currently using the store.', 'wcrp-rental-products' );

			alert( notice );

		} else {

			// If order already exists

			notice = __( 'A popup will now open, navigate to rental products and click the add to order button from the product page. Once finished close the popup to refresh the items within this order.', 'wcrp-rental-products' );
			notice += '\n\n' + __( 'It is recommended the recalculate button is used to recalculate all totals after making changes to the order contents.', 'wcrp-rental-products' );
			notice += '\n\n' + __( 'If you were looking to add a non-rental product, cancel this prompt and use the add product(s) button instead.', 'wcrp-rental-products' );

			if ( $( this ).attr( 'data-addons' ) == 'yes' ) {

				notice += '\n\n' + sprintf( __( 'Add-ons that have been configured via the %s extension cannot be applied via the popup, if you require these to be applied you can use that extension\'s configure button on the item, however please note that this functionality is limited and unaware of the amount of days rented so manual total adjustment may be required. Alternatively, consider the use of a shop as customer extension in your order management workflow.', 'wcrp-rental-products' ), 'WooCommerce Product Add-ons' );

			}

			if ( $( this ).attr( 'data-deposits-partial-payments' ) == 'yes' ) {

				notice += '\n\n' + sprintf( __( 'Deposits and partial payments that have been configured via the %s extension cannot be applied using this method due to limitations of that extension, if you require these to be applied then consider the use of a shop as customer extension in your order management workflow.', 'wcrp-rental-products' ), 'Deposits & Partial Payments for WooCommerce' );

			}

			// If popup not already opened show a notice first

			if ( typeof addRentalProductsPopup == 'undefined' ) {

				if ( confirm( notice ) == true ) {

					addRentalProductsPopupDo = true;

				} else {

					addRentalProductsPopupDo = false;

				}

			} else {

				addRentalProductsPopupDo = true;

			}

			// Show popup or refocus it if already there

			if ( addRentalProductsPopupDo == true ) {

				var addRentalProductsPopupNew = false;
				var addRentalProductsPopupWidth = 960;
				var addRentalProductsPopupHeight = 640;
				var addRentalProductsPopupTop = window.top.outerHeight / 2 + window.top.screenY - ( addRentalProductsPopupHeight / 2 );
				var addRentalProductsPopupLeft = window.top.outerWidth / 2 + window.top.screenX - ( addRentalProductsPopupWidth / 2 );

				if ( typeof addRentalProductsPopup == 'undefined' ) {

					addRentalProductsPopupNew = true;
					addRentalProductsPopupFocus = true;

				} else {

					if ( addRentalProductsPopup.closed ) {

						addRentalProductsPopupNew = true;
						addRentalProductsPopupFocus = true;

					} else {

						addRentalProductsPopupFocus = true;

					}

				}

				if ( addRentalProductsPopupNew == true ) {

					addRentalProductsPopup = window.open( $( this ).attr( 'data-url' ), 'wcrp-rental-products-add-rental-products-popup-' + $( this ).attr( 'data-order-id' ), 'width=' + addRentalProductsPopupWidth + ', height=' + addRentalProductsPopupHeight + ', top=' + addRentalProductsPopupTop + ', left=' + addRentalProductsPopupLeft ); // addRentalProductsPopup not defined with var, let, etc so globally available e.g. if function runs if add rental products button clicked again when popup not closed, to check if popup exists to refocus

				}

				if ( addRentalProductsPopupFocus == true ) {

					addRentalProductsPopup.focus();

				}

				// If popup closed reload order items, potentially with new products if added via popup

				var addRentalProductsPopupTimer = setInterval( function() {

					if ( addRentalProductsPopup.closed ) {

						clearInterval( addRentalProductsPopupTimer );
						$( '#woocommerce-order-items' ).trigger( 'wc_order_items_reload' );

					}

				}, 250 );

			}

		}

	});

	// Orders > delete order item

	$( '#woocommerce-order-items' ).on( 'click', '#order_line_items .item.wcrp-rental-products-order-item-is-rental .wc-order-edit-line-item .wc-order-edit-line-item-actions .delete-order-item', function( e ) {

		// Has to be triggered off #woocommerce-order-items, document will not work, this alert is shown if the order item is a rental because WooCommerce shows its own alert, if that alert is on an order status of pending, failed or cancelled it appends "You may need to manually restore the item's stock." in relation to core stock which is confusing when a rental as rental stock is taken care of automatically

		alert( __( 'Any rental stock reserved for this item will be made available again, no manual action is required to make it available, regardless of any prompts to do so.', 'wcrp-rental-products' ) ); // Important - if deleting an order item which has just been added (where order has not yet been saved fully and refreshed) then this prompt can appear first before the one mentioned above, hence the regardless of any additional prompts on this message, this is for the scenario where it appends the message above

	});

	// Products > info expands

	$( 'body' ).on( 'click', '#wcrp-rental-products-panel-rental-product-info', function( e ) {

		e.preventDefault();
		$( '#wcrp-rental-products-panel-rental-product-info-expand' ).slideToggle();

	});

	$( 'body' ).on( 'click', '#wcrp-rental-products-panel-advanced-pricing-info', function( e ) {

		e.preventDefault();
		$( '#wcrp-rental-products-panel-advanced-pricing-info-expand' ).slideToggle();

	});

	$( 'body' ).on( 'click', '#wcrp-rental-products-panel-in-person-pick-up-return-info', function( e ) {

		e.preventDefault();
		$( '#wcrp-rental-products-panel-in-person-pick-up-return-info-expand' ).slideToggle();

	});

	$( 'body' ).on( 'click', '#wcrp-rental-products-panel-in-person-pick-up-return-date-info', function( e ) {

		e.preventDefault();
		$( '#wcrp-rental-products-panel-in-person-pick-up-return-date-info-expand' ).slideToggle();

	});

	// Products > pricing toggle fields

	function pricingToggleFields() {

		// Important to note that where props changed below they won't be visible in debugger but are done e.g. a checked false will uncheck the field, but the debugger will still show as checked="checked" if it was originally checked on page load

		var pricingType = $( '#_wcrp_rental_products_pricing_type' ).val();
		var pricingPeriod = $( '#_wcrp_rental_products_pricing_period' ).val();

		// If pricing period is empty set variable to the default instead, without this pricingPeriod would be empty and cause minimum/maximum days to get set as empty below when they should match the pricing period

		pricingPeriod = parseInt( ( pricingPeriod == '' ? $( '#_wcrp_rental_products_pricing_period' ).attr( 'data-default' ) : pricingPeriod ) );

		// Fixed pricing type

		if ( pricingType == 'fixed' ) {

			$( '#wcrp-rental-products-panel-field-description-pricing-type-period-selection' ).hide();

			$( '#_wcrp_rental_products_pricing_period' ).val( '1' );
			$( '._wcrp_rental_products_pricing_period_field' ).hide();

			$( '#_wcrp_rental_products_pricing_period_multiples' ).prop( 'checked', false );
			$( '._wcrp_rental_products_pricing_period_multiples_field' ).hide();

			$( '._wcrp_rental_products_pricing_period_multiples_maximum_field' ).hide();

			$( '._wcrp_rental_products_pricing_period_additional_selections_field' ).hide();

			$( '._wcrp_rental_products_pricing_tiers_field' ).show();

			$( '#_wcrp_rental_products_price_additional_periods_percent' ).prop( 'checked', false );
			$( '._wcrp_rental_products_price_additional_periods_percent_field' ).hide();
			$( '._wcrp_rental_products_price_additional_period_percent_field' ).hide();

			$( '._wcrp_rental_products_total_overrides_field' ).show();

			$( '._wcrp_rental_products_minimum_days_field' ).show();

			$( '._wcrp_rental_products_maximum_days_field' ).show();

		} else {

			// Period pricing type

			if ( pricingType == 'period' ) {

				$( '#wcrp-rental-products-panel-field-description-pricing-type-period-selection' ).hide();

				$( '._wcrp_rental_products_pricing_period_field' ).show();

				if ( pricingPeriod > 1 ) {

					if ( $( '#_wcrp_rental_products_pricing_period_multiples' ).is( ':checked' ) ) {

						$( '._wcrp_rental_products_pricing_period_multiples_maximum_field' ).show();

						$( '._wcrp_rental_products_pricing_tiers_field' ).show();

						$( '._wcrp_rental_products_price_additional_periods_percent_field' ).show();

						if ( $( '#_wcrp_rental_products_price_additional_periods_percent' ).is( ':checked' ) ) {

							$( '._wcrp_rental_products_price_additional_period_percent_field' ).show();

						} else {

							$( '._wcrp_rental_products_price_additional_period_percent_field' ).hide();

						}

					} else {

						$( '._wcrp_rental_products_pricing_period_multiples_maximum_field' ).hide();

						$( '#_wcrp_rental_products_pricing_tiers' ).prop( 'checked', false );
						$( '._wcrp_rental_products_pricing_tiers_field' ).hide();

						$( '#_wcrp_rental_products_price_additional_periods_percent' ).prop( 'checked', false );
						$( '._wcrp_rental_products_price_additional_periods_percent_field' ).hide();
						$( '._wcrp_rental_products_price_additional_period_percent_field' ).hide();

					}

					$( '._wcrp_rental_products_pricing_period_multiples_field' ).show();

					$( '._wcrp_rental_products_pricing_period_additional_selections_field' ).hide();

					$( '._wcrp_rental_products_total_overrides_field' ).show();

					$( '#_wcrp_rental_products_minimum_days' ).val( pricingPeriod );
					$( '._wcrp_rental_products_minimum_days_field' ).hide();

					$( '#_wcrp_rental_products_maximum_days' ).val( pricingPeriod );
					$( '._wcrp_rental_products_maximum_days_field' ).hide();

				} else {

					$( '#_wcrp_rental_products_pricing_period_multiples' ).prop( 'checked', false );
					$( '._wcrp_rental_products_pricing_period_multiples_field' ).hide();

					$( '._wcrp_rental_products_pricing_period_multiples_maximum_field' ).hide();

					$( '._wcrp_rental_products_pricing_period_additional_selections_field' ).hide();

					$( '._wcrp_rental_products_pricing_tiers_field' ).show();

					$( '._wcrp_rental_products_price_additional_periods_percent_field' ).show();

					if ( $( '#_wcrp_rental_products_price_additional_periods_percent' ).is( ':checked' ) ) {

						$( '._wcrp_rental_products_price_additional_period_percent_field' ).show();

					} else {

						$( '._wcrp_rental_products_price_additional_period_percent_field' ).hide();

					}

					$( '._wcrp_rental_products_total_overrides_field' ).show();

					$( '._wcrp_rental_products_minimum_days_field' ).show();

					$( '._wcrp_rental_products_maximum_days_field' ).show();

				}

			} else {

				// Period selection pricing type

				if ( pricingType == 'period_selection' ) {

					$( '#wcrp-rental-products-panel-field-description-pricing-type-period-selection' ).show();

					$( '._wcrp_rental_products_pricing_period_field' ).show();

					$( '#_wcrp_rental_products_pricing_period_multiples' ).prop( 'checked', false );
					$( '._wcrp_rental_products_pricing_period_multiples_field' ).hide();

					$( '._wcrp_rental_products_pricing_period_multiples_maximum_field' ).hide();

					$( '._wcrp_rental_products_pricing_period_additional_selections_field' ).show();

					$( '#_wcrp_rental_products_pricing_tiers' ).prop( 'checked', false );
					$( '._wcrp_rental_products_pricing_tiers_field' ).hide();

					$( '#_wcrp_rental_products_price_additional_periods_percent' ).prop( 'checked', false );
					$( '._wcrp_rental_products_price_additional_periods_percent_field' ).hide();
					$( '._wcrp_rental_products_price_additional_period_percent_field' ).hide();

					$( '._wcrp_rental_products_total_overrides_field' ).hide();

					$( '#_wcrp_rental_products_minimum_days' ).val( pricingPeriod );
					$( '._wcrp_rental_products_minimum_days_field' ).hide();

					$( '#_wcrp_rental_products_maximum_days' ).val( pricingPeriod );
					$( '._wcrp_rental_products_maximum_days_field' ).hide();

				}

			}

		}

		if ( $( '#_wcrp_rental_products_pricing_tiers' ).is( ':checked' ) ) {

			$( '#wcrp-rental-products-pricing-tiers-data-expand' ).show();

		} else {

			$( '#wcrp-rental-products-pricing-tiers-data-expand' ).hide();

		}

	}

	pricingToggleFields();

	$( '#_wcrp_rental_products_pricing_type' ).on( 'change', function( e ) {

		pricingToggleFields();

	});

	$( '#_wcrp_rental_products_pricing_period' ).on( 'change keyup keydown', function ( e ) {

		pricingToggleFields();

	});

	$( '#_wcrp_rental_products_pricing_period_multiples' ).on( 'change', function ( e ) {

		pricingToggleFields();

	});

	$( '#_wcrp_rental_products_price_additional_periods_percent' ).on( 'change', function ( e ) {

		pricingToggleFields();

	});

	// Products > pricing tiers data toggle

	function pricingTiersDataToggle() {

		if ( $( '#_wcrp_rental_products_pricing_tiers' ).is( ':checked' ) ) {

			$( '#wcrp-rental-products-pricing-tiers-data-expand' ).show();

		} else {

			$( '#wcrp-rental-products-pricing-tiers-data-expand' ).hide();

		}

	}

	$( '#_wcrp_rental_products_pricing_tiers' ).on( 'change', function ( e ) {

		pricingTiersDataToggle();

	});

	pricingTiersDataToggle();

	// Products > Pricing tiers data

	$( 'body' ).on( 'click', '#wcrp-rental-products-pricing-tiers-data-add-pricing-tier', function( e ) {

		e.preventDefault();

		var addPricingTierButton = this;
		var addPricingTierButtonOriginalText = $( addPricingTierButton ).text();

		$( this ).text( $( addPricingTierButton ).attr( 'data-click-text' ) ).attr( 'disabled', true );
		$( '#wcrp-rental-products-pricing-tiers-data div:last-of-type' ).clone().appendTo( '#wcrp-rental-products-pricing-tiers-data' );
		$( '#wcrp-rental-products-pricing-tiers-data div:last-of-type' ).hide().show();

		pricingTiersDataChanges();

		setTimeout( function() {
			$( addPricingTierButton ).text( addPricingTierButtonOriginalText ).attr( 'disabled', false );
		}, 250 );

	});

	$( 'body' ).on( 'click', '.wcrp-rental-products-pricing-tiers-data-remove-pricing-tier', function( e ) {

		e.preventDefault();

		if ( confirm( $( this ).attr( 'data-alert-text' ) ) ) {

			$( this ).text( $( this ).attr( 'data-click-text' ) ).attr( 'disabled', true );

			$( this ).closest( 'div' ).remove();

			pricingTiersDataChanges();

		}

	});

	$( 'body' ).on( 'change', '.wcrp-rental-products-pricing-tiers-data-days, .wcrp-rental-products-pricing-tiers-data-percent', function( e ) { // on change only not keyup keydown as if wanting to delete the value then type then the if empty condition below would add a leading zero to the number typed

		// The pricing tiers data days and percent fields must be populated if pricing tiers is enabled (atleast one set of fields must exist to allow the fields to be cloned, therefore these values should not be empty or these values would not be blank when got with the JSON array on product page calculations and cause the calculations to break
		// We can't just set the fields to required as user may empty the fields then not be able to see the form validation when updating product if product data tab changed

		if ( $( this ).val().trim() === "" ) { // Specifically === "" for checking if empty, trim used to catch double space, etc

			if ( $( this ).hasClass( 'wcrp-rental-products-pricing-tiers-data-days' ) ) {

				$( this ).val( $( this ).attr( 'min' ) ); // Default to the min required

			} else {

				if ( $( this ).hasClass( 'wcrp-rental-products-pricing-tiers-data-percent' ) ) {

					$( this ).val( '0' ); // Default to 0 (no change)

				}

			}

		}

	});

	function pricingTiersDataChanges() {

		$( '#wcrp-rental-products-pricing-tiers-data div' ).each( function( index ) {

			// Hide remove pricing tier if only 1 (as one set is always required)

			if ( index == 0 ) {

				$( this ).find( '.wcrp-rental-products-pricing-tiers-data-remove-pricing-tier' ).hide();

			} else {

				$( this ).find( '.wcrp-rental-products-pricing-tiers-data-remove-pricing-tier' ).show();

			}

			// Ensure pricing tiers data labels and inputs work from a unique id to ensure clickable labels focus the correct inputs, also supports the adding/removing pricing tiers

			$( this ).find( '.wcrp-rental-products-pricing-tiers-data-days' ).attr( 'id', 'wcrp-rental-products-pricing-tiers-data-days-' + index );
			$( this ).find( '.wcrp-rental-products-pricing-tiers-data-days-label' ).attr( 'for', 'wcrp-rental-products-pricing-tiers-data-days-' + index );

			$( this ).find( '.wcrp-rental-products-pricing-tiers-data-percent' ).attr( 'id', 'wcrp-rental-products-pricing-tiers-data-percent-' + index );
			$( this ).find( '.wcrp-rental-products-pricing-tiers-data-percent-label' ).attr( 'for', 'wcrp-rental-products-pricing-tiers-data-percent-' + index );

		});

	}

	pricingTiersDataChanges();

	// Products > in person pick up/return toggle fields

	function inPersonPickUpReturnToggleFields() {

		if ( $( '#_wcrp_rental_products_in_person_pick_up_return' ).is( ':checked' ) ) {

			if ( $( '#_wcrp_rental_products_in_person_return_date' ).val() == 'default' ) {

				$( '._wcrp_rental_products_in_person_pick_up_times_fees_same_day_field .wcrp-rental-products-panel-field-label-description' ).show();
				$( '._wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day_field .wcrp-rental-products-panel-field-label-description' ).show();
				$( '._wcrp_rental_products_in_person_return_times_fees_same_day_field .wcrp-rental-products-panel-field-label-description' ).show();
				$( '._wcrp_rental_products_in_person_return_times_fees_single_day_same_day_field .wcrp-rental-products-panel-field-label-description' ).show();

				$( '._wcrp_rental_products_in_person_pick_up_times_fees_next_day_field .wcrp-rental-products-panel-field-label-description' ).show();
				$( '._wcrp_rental_products_in_person_return_times_fees_next_day_field .wcrp-rental-products-panel-field-label-description' ).show();

			} else {

				$( '._wcrp_rental_products_in_person_pick_up_times_fees_same_day_field .wcrp-rental-products-panel-field-label-description' ).hide();
				$( '._wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day_field .wcrp-rental-products-panel-field-label-description' ).hide();
				$( '._wcrp_rental_products_in_person_return_times_fees_same_day_field .wcrp-rental-products-panel-field-label-description' ).hide();
				$( '._wcrp_rental_products_in_person_return_times_fees_single_day_same_day_field .wcrp-rental-products-panel-field-label-description' ).hide();

				$( '._wcrp_rental_products_in_person_pick_up_times_fees_next_day_field .wcrp-rental-products-panel-field-label-description' ).hide();
				$( '._wcrp_rental_products_in_person_return_times_fees_next_day_field .wcrp-rental-products-panel-field-label-description' ).hide();

			}

			if ( $( '#_wcrp_rental_products_in_person_return_date' ).val() == 'same_day' ) {

				// In person return date is same day

				$( '._wcrp_rental_products_in_person_pick_up_times_fees_next_day_field' ).hide();
				$( '._wcrp_rental_products_in_person_return_times_fees_next_day_field' ).hide();

				if ( $( '#_wcrp_rental_products_pricing_period' ).val() == '' || $( '#_wcrp_rental_products_minimum_days' ).val() == '' || $( '#_wcrp_rental_products_maximum_days' ).val() == '' ) {

					$( '._wcrp_rental_products_in_person_pick_up_times_fees_same_day_field' ).show();
					$( '._wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day_field' ).show();
					$( '._wcrp_rental_products_in_person_return_times_fees_same_day_field' ).show();
					$( '._wcrp_rental_products_in_person_return_times_fees_single_day_same_day_field' ).show();

				} else {

					if ( $( '#_wcrp_rental_products_pricing_type' ).val() == 'period_selection' ) {

						if ( $( '#_wcrp_rental_products_pricing_period' ).val() == '1' ) {

							// All shown as if 1 that is the minimum period, but will be additional period selections higher so need all options shown

							$( '._wcrp_rental_products_in_person_pick_up_times_fees_same_day_field' ).show();
							$( '._wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day_field' ).show();
							$( '._wcrp_rental_products_in_person_return_times_fees_same_day_field' ).show();
							$( '._wcrp_rental_products_in_person_return_times_fees_single_day_same_day_field' ).show();

						} else {

							// Single day options not shown as > 1 is the minimum period so single day options will not be used

							$( '._wcrp_rental_products_in_person_pick_up_times_fees_same_day_field' ).show();
							$( '._wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day_field' ).hide();
							$( '._wcrp_rental_products_in_person_return_times_fees_same_day_field' ).show();
							$( '._wcrp_rental_products_in_person_return_times_fees_single_day_same_day_field' ).hide();

						}

					} else {

						if ( $( '#_wcrp_rental_products_minimum_days' ).val() == '1' ) {

							if ( $( '#_wcrp_rental_products_maximum_days' ).val() !== '1' ) {

								$( '._wcrp_rental_products_in_person_pick_up_times_fees_same_day_field' ).show();
								$( '._wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day_field' ).show();
								$( '._wcrp_rental_products_in_person_return_times_fees_same_day_field' ).show();
								$( '._wcrp_rental_products_in_person_return_times_fees_single_day_same_day_field' ).show();

							} else {

								$( '._wcrp_rental_products_in_person_pick_up_times_fees_same_day_field' ).hide();
								$( '._wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day_field' ).show();
								$( '._wcrp_rental_products_in_person_return_times_fees_same_day_field' ).hide();
								$( '._wcrp_rental_products_in_person_return_times_fees_single_day_same_day_field' ).show();

							}

						} else {

							$( '._wcrp_rental_products_in_person_pick_up_times_fees_same_day_field' ).show();
							$( '._wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day_field' ).hide();
							$( '._wcrp_rental_products_in_person_return_times_fees_same_day_field' ).show();
							$( '._wcrp_rental_products_in_person_return_times_fees_single_day_same_day_field' ).hide();

						}

					}

				}

			} else {

				if ( $( '#_wcrp_rental_products_in_person_return_date' ).val() == 'next_day' ) {

					// In person return date is next day

					$( '._wcrp_rental_products_in_person_pick_up_times_fees_same_day_field' ).hide();
					$( '._wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day_field' ).hide();
					$( '._wcrp_rental_products_in_person_return_times_fees_same_day_field' ).hide();
					$( '._wcrp_rental_products_in_person_return_times_fees_single_day_same_day_field' ).hide();

					$( '._wcrp_rental_products_in_person_pick_up_times_fees_next_day_field' ).show();
					$( '._wcrp_rental_products_in_person_return_times_fees_next_day_field' ).show();

				} else {

					// In person return date is use default from rental settings so every option displayed

					$( '._wcrp_rental_products_in_person_pick_up_times_fees_same_day_field' ).show();
					$( '._wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day_field' ).show();
					$( '._wcrp_rental_products_in_person_return_times_fees_same_day_field' ).show();
					$( '._wcrp_rental_products_in_person_return_times_fees_single_day_same_day_field' ).show();

					$( '._wcrp_rental_products_in_person_pick_up_times_fees_next_day_field' ).show();
					$( '._wcrp_rental_products_in_person_return_times_fees_next_day_field' ).show();

				}

			}

			$( '#_wcrp_rental_products_return_days_threshold' ).val( '0' );
			$( '._wcrp_rental_products_return_days_threshold_field' ).hide();
			$( '#wcrp-rental-products-in-person-pick-up-return-expand' ).show();

		} else {

			$( '._wcrp_rental_products_return_days_threshold_field' ).show();
			$( '#wcrp-rental-products-in-person-pick-up-return-expand' ).hide();

		}

	}

	inPersonPickUpReturnToggleFields();

	$( document ).on( 'change', '#_wcrp_rental_products_pricing_type', function() {

		inPersonPickUpReturnToggleFields();

	});

	$( document ).on( 'change', '#_wcrp_rental_products_pricing_period', function() {

		inPersonPickUpReturnToggleFields();

	});

	$( document ).on( 'change', '#_wcrp_rental_products_in_person_pick_up_return', function() {

		inPersonPickUpReturnToggleFields();

	});

	$( document ).on( 'change', '#_wcrp_rental_products_in_person_return_date', function() {

		inPersonPickUpReturnToggleFields();

	});

	$( document ).on( 'change', '#_wcrp_rental_products_minimum_days', function() {

		inPersonPickUpReturnToggleFields();

	});

	$( document ).on( 'change', '#_wcrp_rental_products_maximum_days', function() {

		inPersonPickUpReturnToggleFields();

	});

	$( '#_wcrp_rental_products_in_person_pick_up_return' ).on( 'change', function( e ) {

		inPersonPickUpReturnToggleFields();

	});

	$( '#_wcrp_rental_products_in_person_return_date' ).on( 'change', function( e ) {

		inPersonPickUpReturnToggleFields();

	});

	// Products > Add-ons disable addons rental or purchase toggle fields

	function addonsDisableAddOnsRentalPurchaseToggleFields() {

		if ( $( '#_wcrp_rental_products_disable_addons_rental_purchase_rental' ).is( ':checked' ) || $( '#_wcrp_rental_products_disable_addons_rental_purchase_purchase' ).is( ':checked' ) ) {

			$( '#wcrp-rental-products-panel-field-notice-disable-addons-rental-purchase' ).show();

		} else {

			$( '#wcrp-rental-products-panel-field-notice-disable-addons-rental-purchase' ).hide();

		}

	}

	addonsDisableAddOnsRentalPurchaseToggleFields();

	$( '#_wcrp_rental_products_disable_addons_rental_purchase_rental' ).on( 'change', function( e ) {

		addonsDisableAddOnsRentalPurchaseToggleFields();

	});

	$( '#_wcrp_rental_products_disable_addons_rental_purchase_purchase' ).on( 'change', function( e ) {

		addonsDisableAddOnsRentalPurchaseToggleFields();

	});

	// Products > shortcuts

	function rentalPriceStockShortcut() {

		if ( $( '#product-type' ).val() !== 'simple' ) {

			$( '#wcrp-rental-products-rental-price-shortcut' ).html( '<a href="#variable" class="button button-small">' + __( 'Set in variations tab', 'wcrp-rental-products' ) + '</a>' );
			$( '#wcrp-rental-products-rental-stock-shortcut' ).html( '<a href="#variable" class="button button-small">' + __( 'Set in variations tab', 'wcrp-rental-products' ) + '</a>' );

		} else { // This is okay for all other types even if not supported, as this field isn't visible for unsupported product types

			$( '#wcrp-rental-products-rental-price-shortcut' ).html( '<a href="#general" class="button button-small">' + __( 'Set in general tab', 'wcrp-rental-products' ) + '</a>' );
			$( '#wcrp-rental-products-rental-stock-shortcut' ).html( '<a href="#inventory" class="button button-small">' + __( 'Set in inventory tab', 'wcrp-rental-products' ) + '</a>' );

		}

	}

	rentalPriceStockShortcut();

	$( 'body' ).on( 'change', '#product-type', function( e ) {

		rentalPriceStockShortcut();

	});

	$( 'body' ).on( 'click', '#wcrp-rental-products-rental-price-shortcut a, #wcrp-rental-products-rental-stock-shortcut a', function( e ) {

		e.preventDefault();

		var doScroll = false;

		if ( $( this ).attr( 'href' ) == '#variable' ) {

			$( '.wc-tabs .variations_options a' ).trigger( 'click' );
			doScroll = true;

		} else if ( $( this ).attr( 'href' ) == '#general' ) {

			$( '.wc-tabs .general_options a' ).trigger( 'click' );
			doScroll = true;

		} else if ( $( this ).attr( 'href' ) == '#inventory' ) {

			$( '.wc-tabs .inventory_options a' ).trigger( 'click' );
			doScroll = true;

		}

		if ( doScroll == true ) {

			window.scrollTo({
				top:	$( '#woocommerce-product-data' ).offset().top - ( $( '.woocommerce-layout__header' ).length ? $( '.woocommerce-layout__header' ).height() : 0 ),
				left:	0,
			})

		}

	});

	$( 'body' ).on( 'click', '#wcrp-rental-products-add-ons-shortcut', function( e ) {

		e.preventDefault();
		$( '.product_data_tabs .addons_tab a' ).trigger( 'click' );

		window.scrollTo({
			top:	$( '#woocommerce-product-data' ).offset().top - ( $( '.woocommerce-layout__header' ).length ? $( '.woocommerce-layout__header' ).height() : 0 ),
			left:	0,
		})

	});

	// Rentals dashboard > calendar > calendar feed notice

	function rentalsDashboardCalendarCalendarFeedNotice() {

		prompt( __( 'In your calendar application, subscribe to this feed URL:', 'wcrp-rental-products' ), $( '#wcrp-rental-products-rentals-calendar-calendar-feed' ).attr( 'data-feed-url' ) );

	}

	$( '#wcrp-rental-products-rentals-calendar-calendar-feed' ).on( 'click', function( e ) {

		e.preventDefault();
		rentalsDashboardCalendarCalendarFeedNotice();

	});

	// Rentals dashboard > calendar > button classes/title attribute

	function rentalsDashboardCalendarButtonClassesTitleAttribute() {

		$( '#wcrp-rental-products-rentals-calendar .fc-button' ).removeAttr( 'title' ); // Buttons get title attributes added by FullCalendar, but the casing isn't consistent with the actual button text due to how the button text markup from FullCalendar is and how we've had to compensate with CSS rules, also other rentals dashboard tabs e.g. inventory do not use title attributes for buttons so they are removed here for consistency

		$( '#wcrp-rental-products-rentals-calendar .fc-button' ).addClass( 'button' );
		$( '#wcrp-rental-products-rentals-calendar .fc-button' ).removeClass( 'fc-button' );

		$( '#wcrp-rental-products-rentals-calendar .fc-button-primary' ).removeClass( 'fc-button-primary' );

		$( '#wcrp-rental-products-rentals-calendar .fc-button-active' ).addClass( 'button-primary' );
		$( '#wcrp-rental-products-rentals-calendar .fc-button-active' ).removeClass( 'fc-button-active' );

		$( '#wcrp-rental-products-rentals-calendar .fc-today-button' ).addClass( 'button-primary' );
		$( '#wcrp-rental-products-rentals-calendar .fc-today-button' ).removeClass( 'fc-today-button' );

	}

	rentalsDashboardCalendarButtonClassesTitleAttribute();

	$( '#wcrp-rental-products-rentals-calendar' ).on( 'click', function() {

		rentalsDashboardCalendarButtonClassesTitleAttribute(); // When navigating the calendar the elements are rerendered with the default fc- based classes so need to be changed each time

	});

	// Rentals dashboard > calendar > filters

	$( '#wcrp-rental-products-rentals-calendar-filter-color-key, #wcrp-rental-products-rentals-calendar-filter-order-status' ).on( 'change', function( e ) {

		// These are added inline rather than using .show() and .hide() because events can be added to the DOM later, e.g. if the view more link clicked in a day cell

		if ( $( this ).attr( 'id' ) == 'wcrp-rental-products-rentals-calendar-filter-color-key' ) {

			// Filter color key

			if ( $( this ).val() == 'all' ) {

				$( '#wcrp-rental-products-rentals-calendar-filter-color-key-inline-css' ).html( '' );

			} else {

				$( '#wcrp-rental-products-rentals-calendar-filter-color-key-inline-css' ).html( '<style>.wcrp-rental-products-rentals-fc-event:not( .wcrp-rental-products-rentals-fc-event-key-' + $( this ).val() + ' )[class*="wcrp-rental-products-rentals-fc-event-key-"] { display: none; }</style>' );

			}

		} else {

			// Filter order status

			if ( $( this ).val() == 'all' ) {

				$( '#wcrp-rental-products-rentals-calendar-filter-order-status-inline-css' ).html( '' );

			} else {

				$( '#wcrp-rental-products-rentals-calendar-filter-order-status-inline-css' ).html( '<style>.wcrp-rental-products-rentals-fc-event:not( .wcrp-rental-products-rentals-fc-event-order-status-' + $( this ).val() + ' )[class*="wcrp-rental-products-rentals-fc-event-order-status-"] { display: none; }</style>' );

			}

		}

		// Rerender view, otherwise events can be overlapped, previously used window.dispatchEvent( new Event( 'resize' ) ); but this doesn't work in every scenario e.g. when some filter is applied and toggles used

		$( '.fc-prev-button' ).trigger( 'click' );
		$( '.fc-next-button' ).trigger( 'click' );

	});

	// Rentals dashboard > calendar > search

	$( '#wcrp-rental-products-rentals-calendar-search' ).on( 'input', function( e ) {

		$( '#wcrp-rental-products-rentals-calendar-search-inline-css' ).html( '' );

		if ( $( this ).val() !== '' ) {

			$( '#wcrp-rental-products-rentals-calendar-search-inline-css' ).html( '<style>.wcrp-rental-products-rentals-fc-event:not([title*="' + $( this ).val() + '" i]) { display: none !important; }</style>' ); // i after title ensures search is case insensitive

		}

		// Rerender view, otherwise events can be overlapped, previously used window.dispatchEvent( new Event( 'resize' ) ); but this doesn't work in every scenario e.g. when some filter is applied and toggles used

		$( '.fc-prev-button' ).trigger( 'click' );
		$( '.fc-next-button' ).trigger( 'click' );

	});

	// Rentals dashboard > calendar > toggle rentals/returns expected (toggle rows limit functionality occurs via customButtons event click as requires rentalsCalendar being available)

	$( 'body' ).on( 'click', '.fc-toggleRentals-button, .fc-toggleReturnsExpected-button', function( e ) {

		// These are added inline rather than using .show() and .hide() because events can be added to the DOM later, e.g. if the view more link clicked in a day cell

		if ( $( this ).hasClass( 'fc-toggleRentals-button' ) ) {

			// Toggle rentals

			if ( $( this ).attr( 'toggle-rentals' ) == '1' ) {

				$( '#wcrp-rental-products-rentals-calendar-toggle-rentals-inline-css' ).html( '' );
				$( this ).attr( 'toggle-rentals', '0' );

			} else {

				$( '#wcrp-rental-products-rentals-calendar-toggle-rentals-inline-css' ).html( '<style>.wcrp-rental-products-rentals-fc-event-type-rental { display: none !important; }</style>' );
				$( this ).attr( 'toggle-rentals', '1' );

			}

		} else {

			// Toggle returns expected

			if ( $( this ).attr( 'toggle-returns-expected' ) == '1' ) {

				$( '#wcrp-rental-products-rentals-calendar-toggle-returns-expected-inline-css' ).html( '' );
				$( this ).attr( 'toggle-returns-expected', '0' );

			} else {

				$( '#wcrp-rental-products-rentals-calendar-toggle-returns-expected-inline-css' ).html( '<style>.wcrp-rental-products-rentals-fc-event-type-return { display: none !important; }</style>' );
				$( this ).attr( 'toggle-returns-expected', '1' );

			}

		}

		// Rerender view, otherwise events can be overlapped, previously used window.dispatchEvent( new Event( 'resize' ) ); but this doesn't work in every scenario e.g. when some filter is applied and toggles used

		$( '.fc-prev-button' ).trigger( 'click' );
		$( '.fc-next-button' ).trigger( 'click' );

	});

	// Rentals dashboard > tools > clone rental pricing options

	$( '#wcrp-rental-products-rentals-tools-clone-rental-product-options-from' ).on( 'change', function( e ) {

		if ( $( this ).val() !== '' ) {

			$( '#wcrp-rental-products-rentals-tools-clone-rental-product-options-which' ).show();
			$( '#wcrp-rental-products-rentals-tools-clone-rental-product-options-to' ).show();

		} else {

			$( '#wcrp-rental-products-rentals-tools-clone-rental-product-options-which' ).hide();
			$( '#wcrp-rental-products-rentals-tools-clone-rental-product-options-to' ).hide();

		}

	});

	$( '#wcrp-rental-products-rentals-tools-clone-rental-product-options-which-select-deselect' ).on( 'click', function( e ) {

		e.preventDefault();

		if ( $( this ).attr( 'data-action' ) == 'select' ) {

			$( '#wcrp-rental-products-rentals-tools-clone-rental-product-options-which input[type="checkbox"]' ).prop( 'checked', true );
			$( this ).text( __( 'Deselect all', 'wcrp-rental-products' ) );
			$( this ).attr( 'data-action', 'deselect' );

		} else {

			$( '#wcrp-rental-products-rentals-tools-clone-rental-product-options-which input[type="checkbox"]' ).prop( 'checked', false );
			$( this ).text( __( 'Select all', 'wcrp-rental-products' ) );
			$( this ).attr( 'data-action', 'select' );

		}

	});

	$( '#wcrp-rental-products-rentals-tools-clone-rental-product-options-to input[type="radio"][name="wcrp_rental_products_rentals_tools_clone_rental_product_options_to"]' ).on( 'change', function( e ) {

		if ( $( this ).val() == 'all_products_in_specific_categories' || $( this ).val() == 'all_rental_products_in_specific_categories' ) {

			$( '#wcrp-rental-products-rentals-tools-clone-rental-product-options-to-categories-select' ).show();

		} else {

			$( '#wcrp-rental-products-rentals-tools-clone-rental-product-options-to-categories-select' ).hide();

		}

		if ( $( this ).val() == 'products' ) {

			$( '#wcrp-rental-products-rentals-tools-clone-rental-product-options-to-products-select' ).show();

		} else {

			$( '#wcrp-rental-products-rentals-tools-clone-rental-product-options-to-products-select' ).hide();

		}

	});

	$( 'body' ).on( 'click', '#wcrp-rental-products-rentals-tools-clone-rental-product-options-submit', function( e ) {

		if ( !confirm( $( this ).attr( 'data-alert-text' ) ) ) {

			e.preventDefault();

		}

	});

	// Rentals dashboard > tools > rental products debug confirmation

	$( 'body' ).on( 'click', '#wcrp-rental-products-rentals-tools-rental-products-debug-fix', function( e ) {

		if ( !confirm( $( this ).attr( 'data-alert-text' ) ) ) {

			e.preventDefault();

		}

	});

	// Settings > rental form calendar custom styling settings

	if ( $( 'body' ).hasClass( 'woocommerce_page_wc-settings' ) ) {

		$( 'body' ).on( 'click', '#wcrp-rental-products-rental-form-calendar-custom-styling-reset', function( e ) {

			e.preventDefault();

			if ( $( '#wcrp_rental_products_rental_form_calendar_custom_styling' ).is( ':checked' ) ) {

				var defaultRentalFormCalendarStyling = $( '#wcrp_rental_products_rental_form_calendar_custom_styling' ).attr( 'data-reset' );

				$( '#wcrp_rental_products_rental_form_calendar_custom_styling_code' ).val( defaultRentalFormCalendarStyling );

				alert( __( 'Rental form calendar custom styling code has been reset to the defaults. Remember to save settings.', 'wcrp-rental-products' ) );

			} else {

				alert( __( 'Cannot reset to defaults as rental form calendar custom styling is disabled.', 'wcrp-rental-products' ) );

			}

		});

		function rentalFormCalendarCustomStylingSettingsToggleFields() { // Function named this way so consistent with inPersonPickUpReturnSettingsToggleFields(), see comment on that function why it is named this way

			if ( $( '#wcrp_rental_products_rental_form_calendar_custom_styling' ).is( ':checked' ) ) {

				$( '#wcrp_rental_products_rental_form_calendar_custom_styling_code' ).closest( 'tr' ).show();

			} else {

				$( '#wcrp_rental_products_rental_form_calendar_custom_styling_code' ).closest( 'tr' ).hide();

			}

		}

		rentalFormCalendarCustomStylingSettingsToggleFields();

		$( '#wcrp_rental_products_rental_form_calendar_custom_styling' ).on( 'change', function( e ) {

			rentalFormCalendarCustomStylingSettingsToggleFields();

		});

	}

	// Settings > in person pick up/return settings toggle fields

	if ( $( 'body' ).hasClass( 'woocommerce_page_wc-settings' ) ) {

		function inPersonPickUpReturnSettingsToggleFields() { // Note this is includes Settings in the function name as we already have a inPersonPickUpReturnToggleFields() in the products section

			if ( $( '#wcrp_rental_products_in_person_return_date' ).val() == 'same_day' ) {

				$( '#wcrp_rental_products_in_person_pick_up_times_fees_same_day' ).closest( 'tr' ).show();
				$( '#wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day' ).closest( 'tr' ).show();
				$( '#wcrp_rental_products_in_person_return_times_fees_same_day' ).closest( 'tr' ).show();
				$( '#wcrp_rental_products_in_person_return_times_fees_single_day_same_day' ).closest( 'tr' ).show();

				$( '#wcrp_rental_products_in_person_pick_up_times_fees_next_day' ).closest( 'tr' ).hide();
				$( '#wcrp_rental_products_in_person_return_times_fees_next_day' ).closest( 'tr' ).hide();

			} else {

				$( '#wcrp_rental_products_in_person_pick_up_times_fees_same_day' ).closest( 'tr' ).hide();
				$( '#wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day' ).closest( 'tr' ).hide();
				$( '#wcrp_rental_products_in_person_return_times_fees_same_day' ).closest( 'tr' ).hide();
				$( '#wcrp_rental_products_in_person_return_times_fees_single_day_same_day' ).closest( 'tr' ).hide();

				$( '#wcrp_rental_products_in_person_pick_up_times_fees_next_day' ).closest( 'tr' ).show();
				$( '#wcrp_rental_products_in_person_return_times_fees_next_day' ).closest( 'tr' ).show();

			}

		}

		inPersonPickUpReturnSettingsToggleFields();

		$( '#wcrp_rental_products_in_person_return_date' ).on( 'change', function( e ) {

			inPersonPickUpReturnSettingsToggleFields();

		});

	}

	// Non-page specific below

	// Disable dates picker

	$( '.wcrp-rental-products-disable-dates-picker' ).each( function( index, element ) {

		$( element ).attr( 'readonly', true ).css( 'background-color', '#ffffff' ); // Makes field read only but makes background white so looks editable (edits done by datepicker itself)

		var disabledRentalDates = $( element ).val().split( ',' );

		$( element ).datepicker({

			dateFormat: 'yy-mm-dd',

			beforeShow: function() {

				$( '#ui-datepicker-div' ).addClass( 'wcrp-rental-products-disable-dates-picker-widget' ); // This adds a class to the datepicker widget itself, this is then used to add some CSS rules, see admin stylesheet

			},

			onSelect: function ( dateText ) {

				// Adds or removes the date

				var index = jQuery.inArray( dateText, disabledRentalDates );

				if ( index >= 0 ) {

					disabledRentalDates.splice( index, 1 );

				} else {

					if ( $.inArray( dateText, disabledRentalDates ) < 0 ) {

						disabledRentalDates.push( dateText );

					}

				}

				disabledRentalDates = disabledRentalDates.filter( item => item ); // Removes empty array values from array (if no previous dates were set the first element would be "" resulting in the first date having a comma before it)

				$( element ).val( disabledRentalDates.sort() );

				$( this ).data( 'datepicker' ).inline = true; // Sets to inline so datepicker remains open after selection

				$( element ).trigger( 'change' ); // Triggers a change, as this is a readonly field without this the field doesn't trigger a change, and therefore where these fields are included in settings, the save settings button doesn't activate as no change detected

			},

			onClose: function() {

				$( this ).data( 'datepicker' ).inline = false; // Closes the datepicker after close due to inline true in onSelect above

			},

			beforeShowDay: function( date ) {

				// Disable style applied to date in datepicker

				var gotDate = $.inArray( $.datepicker.formatDate( $( this ).datepicker( 'option', 'dateFormat' ), date ), disabledRentalDates );

				if ( gotDate >= 0 ) {

					return [ true, 'ui-state-disabled' ];

				} else {

					return [ true, '' ];

				}

			}

		});

	});

	// Value colon price validation

	function valueColonPriceValidation() {

		// If in value colon price validation fields exist on the page

		if ( $( '.wcrp-rental-products-value-colon-price-validation' ).length > 0 ) {

			// Remove existing value colon price validation notices that may have been previously inserted by this function after the validation fields, as will be reinserted later depending on the conditions below, stops duplicate notices

			$( '.wcrp-rental-products-value-colon-price-validation' ).next( '.notice' ).remove();

			// For each field to validate

			$( '.wcrp-rental-products-value-colon-price-validation' ).each( function( index, element ) {

				// Bail if the field is empty as nothing to validate yet

				if ( $( element ).val() !== '' ) {

					var validationFailed = false;
					var validationNotice = false;
					var validationType = $( element ).attr( 'data-validation-type' );
					var priceDecimalSeparator = $( element ).attr( 'data-price-decimal-separator' );
					var valuesPricesString = $( element ).val();
					var valuesPrices = valuesPricesString.split( '|' ); // Get the values/prices entered in an array by splitting | character

					// Validate if the values/prices entered contain multiple lines

					if ( /\r|\n/.exec( valuesPricesString ) ) {

						// Multiples lines are used, so invalid

						validationFailed = true;

						switch ( validationType ) {

							case 'pricing_period_additional_selections':

								validationNotice = sprintf( __( 'Invalid, pricing period additional selections should be entered on a single line, separate multiple pricing period additional selections with the %1$s character, e.g. 3:5%2$s00|7:10%2$s00.', 'wcrp-rental-products' ), '|', priceDecimalSeparator );

								break;

							case 'times_fees':

								validationNotice = sprintf( __( 'Invalid, times/fees should be entered on a single line, separate multiple times/fees with the %1$s character, e.g. 1000:0%2$s00|1400:10%2$s00.', 'wcrp-rental-products' ), '|', priceDecimalSeparator );

								break;

							case 'total_overrides':

								validationNotice = sprintf( __( 'Invalid, total overrides should be entered on a single line, separate multiple total overrides with the %1$s character, e.g. 1:5%2$s00|2:10%2$s00.', 'wcrp-rental-products' ), '|', priceDecimalSeparator );

								break;

							default:

								validationNotice = __( 'Invalid.', 'wcrp-rental-products' );

						}

					} else {

						// Validate if each value/price is valid

						$( valuesPrices ).each( function( index, valuePrice ) {

							var valuePrice = valuePrice.split( ':' ); // Get the value/price entered in an array, by splitting : character
							var value = valuePrice[0]; // Get the time from array key 0
							var price = valuePrice[1]; // Get the time from array key 1

							// Validate if value and price has been entered

							if ( value !== undefined && price !== undefined ) {

								switch ( validationType ) {

									case 'pricing_period_additional_selections':

										var pricingPeriodCheck = /^\d+$/.test( value ) && parseInt( value ) > 0; // Returns false if pricing period not in digits and is less than 1
										var priceCheck = new RegExp( `^\\d+\\${priceDecimalSeparator}\\d+$` ).test( price ); // Returns false if price contains anything thats not a number, except the price decimal separator, and that the price decimal separator is included, backticks ` are specifically used so can use a template literal variable for priceDecimalSeparator

										if ( pricingPeriodCheck == false || priceCheck == false ) {

											validationFailed = true;
											validationNotice = sprintf( __( 'Invalid, ensure pricing periods entered are in digits, and prices entered include the %1$s decimal separator, e.g. 3:5%1$s00.', 'wcrp-rental-products' ), priceDecimalSeparator );

										}

										break;

									case 'times_fees':

										var timeCheck = /^\d+$/.test( value ) && value.length == 4 && parseInt( value ) <= 2400; // Returns false if time not in digits, is not 4 digits and not greater than 2400
										var feeCheck = new RegExp( `^\\d+\\${priceDecimalSeparator}\\d+$` ).test( price ); // Returns false if fee contains anything thats not a number, except the price decimal separator, and that the price decimal separator is included, backticks ` are specifically used so can use a template literal variable for priceDecimalSeparator

										if ( timeCheck == false || feeCheck == false ) {

											validationFailed = true;
											validationNotice = sprintf( __( 'Invalid, ensure times entered are 4 digits, and fees entered include the %1$s decimal separator, e.g. 1000:0%1$s00.', 'wcrp-rental-products' ), priceDecimalSeparator );

										}

										break;

									case 'total_overrides':

										var dayCheck = /^\d+$/.test( value ) && parseInt( value ) > 0; // Returns false if pricing period not in digits and is less than 1
										var priceCheck = new RegExp( `^\\d+\\${priceDecimalSeparator}\\d+$` ).test( price ); // Returns false if price contains anything thats not a number, except the price decimal separator, and that the price decimal separator is included, backticks ` are specifically used so can use a template literal variable for priceDecimalSeparator

										if ( dayCheck == false || priceCheck == false ) {

											validationFailed = true;
											validationNotice = sprintf( __( 'Invalid, ensure days entered are in digits, and totals entered include the %1$s decimal separator, e.g. 3:5%1$s00.', 'wcrp-rental-products' ), priceDecimalSeparator );

										}

										break;

									default:

										// No default

									}

							} else {

								// Both the value and price have not been entered yet (might have entered on one but not both for multiples), so invalid, but may still be typing it in, so show a general notice of expected entry

								validationFailed = true;

								switch ( validationType ) {

									case 'pricing_period_additional_selections':

										validationNotice = sprintf( __( 'Invalid, enter pricing period additional selections in the format 1:5%1$s00 (single) or 3:5%1$s00|7:10%1$s00 (multiple).', 'wcrp-rental-products' ), priceDecimalSeparator );

										break;

									case 'times_fees':

										validationNotice = sprintf( __( 'Invalid, enter times/fees in the format 1000:0%1$s00 (single) or 1000:0%1$s00|1400:10%1$s00 (multiple).', 'wcrp-rental-products' ), priceDecimalSeparator );

										break;

									case 'total_overrides':

										validationNotice = sprintf( __( 'Invalid, enter total overrides in the format 1:5%1$s00 (single) or 1:5%1$s00|2:10%1$s00 (multiple).', 'wcrp-rental-products' ), priceDecimalSeparator );

										break;

									default:

										validationNotice = __( 'Invalid.', 'wcrp-rental-products' );

								}

							}

						});

					}

					// If checks didn't fail, maybe set valid notice to appear depending on validation type

					if ( validationFailed == false ) {

						if ( validationType == 'times_fees' ) {

							validationNotice = 'Remember to manually check pick up times are lower or higher than return times as per the tooltip information, except if unrestricted.';

						}

					}

					// Display notice

					if ( validationNotice !== false ) {

						$( '<div class="notice notice-' + ( validationFailed == true ? 'error' : 'success' ) + ' inline"><p>' + validationNotice + '</p></div>' ).insertAfter( element );

					}

				}

			});

		}

	}

	valueColonPriceValidation();

	$( document ).on( 'change keyup keydown', '.wcrp-rental-products-value-colon-price-validation', function () {

		valueColonPriceValidation();

	});

	$( document ).on( 'woocommerce_variations_loaded', function() {

		valueColonPriceValidation();

	});

	$( document ).on( 'woocommerce_variations_added', function() {

		valueColonPriceValidation();

	});

	// Single date picker

	$( '.wcrp-rental-products-single-date-picker' ).each( function( index, element ) {

		if ( $( element ).attr( 'data-from' ) == 'today' ) {

			$( element ).datepicker({
				dateFormat: 'yy-mm-dd',
				minDate: 0,
			});

		} else {

			$( element ).datepicker({
				dateFormat: 'yy-mm-dd',
			});

		}

	});

});