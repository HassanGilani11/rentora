jQuery( document ).ready( function( $ ) {

	// Translation

	const { __, _x, _n, _nx, sprintf } = wp.i18n;

	// Display

	var availabilityCheckerDisplayAjaxPlaceholder = $( '.wcrp-rental-products-availability-checker-ajax-placeholder' );

	if ( availabilityCheckerDisplayAjaxPlaceholder.length > 0 ) {

		var availabilityCheckerDisplayAjaxRequestData = {
			'action':		'wcrp_rental_products_availability_checker_display',
			'nonce':		availabilityCheckerAjaxVariables.nonce,
		};

		var availabilityCheckerDisplayAjaxRequest = jQuery.ajax({
			'data':			availabilityCheckerDisplayAjaxRequestData,
			'method':		'POST',
			'url':			availabilityCheckerAjaxVariables.ajax_url,
		});

		availabilityCheckerDisplayAjaxRequest.done( function( availabilityCheckerDisplayAjaxRequestResponse ) {

			var availabilityCheckerDisplayAjaxRequestResponse = JSON.parse( availabilityCheckerDisplayAjaxRequestResponse );

			$( availabilityCheckerDisplayAjaxRequestResponse ).insertAfter( availabilityCheckerDisplayAjaxPlaceholder );
			availabilityCheckerDisplayAjaxPlaceholder.remove();

		});

	}

	// Status

	var availabilityCheckerStatusAjaxPlaceholder = $( '.wcrp-rental-products-availability-checker-status-ajax-placeholder' );

	if ( availabilityCheckerStatusAjaxPlaceholder.length > 0 ) {

		var availabilityCheckerStatusProductIds = new Array();

		availabilityCheckerStatusAjaxPlaceholder.each( function( index, element ) {

			availabilityCheckerStatusProductIds.push( $( element ).attr( 'data-product-id' ) );

		});

		var availabilityCheckerStatusAjaxRequestData = {
			'action':		'wcrp_rental_products_availability_checker_statuses',
			'nonce':		availabilityCheckerAjaxVariables.nonce,
			'product_ids':	availabilityCheckerStatusProductIds,
		};

		var availabilityCheckerStatusAjaxRequest = jQuery.ajax({
			'data':			availabilityCheckerStatusAjaxRequestData,
			'method':		'POST',
			'url':			availabilityCheckerAjaxVariables.ajax_url,
		});

		availabilityCheckerStatusAjaxRequest.done( function( availabilityCheckerStatusAjaxRequestResponse ) {

			var availabilityCheckerStatusAjaxRequestResponse = JSON.parse( availabilityCheckerStatusAjaxRequestResponse );

			availabilityCheckerStatusAjaxPlaceholder.each( function( index, element ) {

				$( availabilityCheckerStatusAjaxRequestResponse[ $( element ).attr( 'data-product-id' ) ] ).insertAfter( $( element ) );
				$( element ).remove();

			});

		});

	}

});