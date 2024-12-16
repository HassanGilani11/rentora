/* global mvr_frontend_params */
jQuery(
	function ($) {
		'use strict';

		function block(id) {
			jQuery( id ).block(
				{
					message: null,
					overlayCSS: {
						background: '#fff',
						opacity: 0.7
					}
				}
			);
		}

		function unblock(id) {
			jQuery( id ).unblock();
		}

		// Field validation error tips
		$( document.body )
		.on(
			'mvr_add_error_tip',
			function (e, element, error_type) {
				var offset = element.position();

				if (element.parent().find( '.mvr-error-tip' ).length === 0) {
					element.after( '<div class="mvr-error-tip ' + error_type + '">' + mvr_frontend_params[error_type] + '</div>' );
					element.parent().find( '.mvr-error-tip' )
					.css(
						'left',
						offset.left +
						element.width() -
						element.width() / 2 -
						$( '.mvr-error-tip' ).width() / 2
					)
						.css( 'top', offset.top + element.height() )
						.fadeIn( '100' );
				}
			}
		)

		.on(
			'mvr_remove_error_tip',
			function (e, element, error_type) {
				element.parent().find( '.mvr-error-tip.' + error_type )
				.fadeOut(
					'100',
					function () {
						$( this ).remove();
					}
				);
			}
		)

		.on(
			'click',
			function () {
				$( '.mvr-error-tip' ).fadeOut(
					'100',
					function () {
						$( this ).remove();
					}
				);
			}
		)

		.on(
			'blur',
			'.mvr-input-price',
			function () {
				$( '.mvr-error-tip' ).fadeOut(
					'100',
					function () {
						$( this ).remove();
					}
				);
			}
		)

		.on(
			'change',
			'.mvr-input-price',
			function () {
				var regex,
				decimalRegex,
				decimalPoint = mvr_frontend_params.decimal_point;

				regex        = new RegExp( '[^-0-9%\\' + decimalPoint + ']+', 'gi' );
				decimalRegex = new RegExp( '\\' + decimalPoint + '+', 'gi' );

				var value    = $( this ).val();
				var newValue = value.replace( regex, '' ).replace( decimalRegex, decimalPoint );

				if (value !== newValue) {
					$( this ).val( newValue );
				}
			}
		)

		.on(
			'keyup',
			'.mvr-input-price',
			function () {
				var checkDecimalNumbers = true,
				regex                   = new RegExp( '[^-0-9%\\' + mvr_frontend_params.decimal_point + ']+', 'gi' ),
				decimalRegex            = new RegExp( '[^\\' + mvr_frontend_params.decimal_point + ']', 'gi' ),
				value                   = $( this ).val(),
				newValue                = value.replace( regex, '' );

				// Check if newValue have more than one decimal point.
				if (checkDecimalNumbers && 1 < newValue.replace( decimalRegex, '' ).length) {
					newValue = newValue.replace( decimalRegex, '' );
				}

				if (value !== newValue) {
					$( document.body ).triggerHandler( 'mvr_add_error_tip', [$( this ), 'decimal_error',] );
				} else {
					$( document.body ).triggerHandler( 'mvr_remove_error_tip', [$( this ), 'decimal_error',] );
				}
			}
		)

		var MVR_Frontend = {
			review: $( '.mvr-single-store-review-wrapper' ),
			init() {
				// Products Tab
				if (this.review.length) {
					this.review.on( 'click', '.mvr-star', this.reviewSelect );
				}
			},

			reviewSelect( e ) {
				e.preventDefault();

				let wrapper = $( this ).closest( 'p.mvr-stars' );

				if ( ! wrapper.hasClass( 'mvr-selected' )) {
					wrapper.addClass( 'mvr-selected' );
				}

				wrapper.find( '.mvr-star' ).removeClass( 'active' );
				$( this ).addClass( 'active' );
				wrapper.find( '.mvr-store-rating' ).val( $( this ).html() );

				MVR_Frontend.review.find( '.mvr-vendor-review-submit' ).attr( 'disabled', false );
			},
		};

		MVR_Frontend.init();
	}
);
