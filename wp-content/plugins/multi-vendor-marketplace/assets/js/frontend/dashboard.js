/* global mvr_dashboard_params */

jQuery(
	function ($) {
		'use strict';

		// mvr_dashboard_params is required to continue, ensure the object exists.
		if (typeof mvr_dashboard_params === 'undefined') {
			return false;
		}

		$( '.mvr-edit-post' ).load(
			function () {
				$( '.mvr-edit-post' ).contents().find( '#wpadminbar' ).remove();
				$( '.mvr-edit-post' ).contents().find( '.woocommerce-layout' ).remove();
				$( '.mvr-edit-post' ).contents().find( '#screen-meta-links' ).remove();

				$( '.mvr-edit-post' ).contents().on(
					'click',
					'.mvr-admin-save-btn',
					function (e) {
						block( $( this ).closest( '#wpbody' ) );
					}
				);
			}
		);

		function is_blocked($node) {
			return $node.is( '.processing' ) || $node.parents( '.processing' ).length;
		}

		function block($node) {
			$.blockUI.defaults.overlayCSS.cursor = 'wait';

			if ( ! is_blocked( $node )) {
				$node.addClass( 'processing' ).block(
					{
						message: null,
						overlayCSS: {
							background: '#fff',
							opacity: 0.6
						}
					}
				);
			}
		}

		function unblock($node) {
			$node.removeClass( 'processing' ).unblock();
		}

		function formatUrl(url) {
			if (-1 === url.indexOf( 'https://' ) || -1 === url.indexOf( 'http://' )) {
				return url;
			} else {
				return decodeURI( url );
			}
		}

		function wc_number_format(number) {
			var decimals  = mvr_dashboard_params.currency_format_num_decimals,
			decimal_sep   = mvr_dashboard_params.decimal_separator,
			thousands_sep = mvr_dashboard_params.currency_format_thousand_sep,
			n             = number,
			c             = isNaN( decimals = Math.abs( decimals ) ) ? 2 : decimals,
			d             = decimal_sep == undefined ? ',' : decimal_sep,
			t             = thousands_sep == undefined ? '.' : thousands_sep, s = n < 0 ? '-' : '',
			i             = parseInt( n = Math.abs( +n || 0 ).toFixed( c ), 10 ) + '',
			j             = 0;

			j = (j = i.length) > 3 ? j % 3 : 0;

			return s + (j ? i.substr( 0, j ) + t : '') + i.substr( j ).replace( /(\d{3})(?=\d)/g, '$1' + t ) + (c ? d + Math.abs( n - i ).toFixed( c ).slice( 2 ) : '');
		}

		function wc_woocommerce_number_format(price, plain) {
			let remove = mvr_dashboard_params.decimal_separator,
			position   = mvr_dashboard_params.currency_position,
			symbol     = mvr_dashboard_params.wc_currency_symbol,
			trim_zeros = mvr_dashboard_params.currency_format_trim_zeros,
			decimals   = mvr_dashboard_params.currency_format_num_decimals;

			plain = (typeof (plain) === 'undefined') ? false : plain;

			if (trim_zeros == 'yes' && decimals > 0) {
				for (var i = 0; i < decimals; i++) {
					remove = remove + '0';
				}
				price = price.replace( remove, '' );
			}

			let formatted_price = String( price ),
			formatted_symbol    = plain ? symbol : '<span class="woocommerce-Price-currencySymbol">' + symbol + '</span>';

			if ('left' === position) {
				formatted_price = formatted_symbol + formatted_price;
			} else if ('right' === position) {
				formatted_price = formatted_price + formatted_symbol;
			} else if ('left_space' === position) {
				formatted_price = formatted_symbol + ' ' + formatted_price;
			} else if ('right_space' === position) {
				formatted_price = formatted_price + ' ' + formatted_symbol;
			}

			formatted_price = plain ? formatted_price : '<span class="woocommerce-Price-amount amount">' + formatted_price + '</span>';

			return formatted_price;
		}

		function wc_price_format(price, plain) {
			plain = ('undefined' === typeof (plain)) ? false : plain;

			return wc_woocommerce_number_format( wc_number_format( price ), plain );
		}

		var dashboard = {
			states: null,
			wrapper: $( 'div.mvr-dashboard-content' ),
			products: $( 'div.mvr-dashboard-content' ).find( '.mvr-products-table' ),
			duplicate: $( 'div.mvr-dashboard-content' ).find( '.mvr-duplicate-products-table' ),
			payment: $( 'div.mvr-dashboard-content' ).find( '.mvr-payment-form-wrapper' ),
			payout: $( 'div.mvr-dashboard-content' ).find( '.mvr-payout-form-wrapper' ),
			withdraw: $( 'div.mvr-dashboard-content' ).find( '.mvr-withdraw-form-wrapper' ),
			profile: $( 'div.mvr-dashboard-content' ).find( '.mvr-profile-form-wrapper' ),
			address: $( 'div.mvr-dashboard-content' ).find( '.mvr-address-form-wrapper' ),
			enquiry: $( 'div.mvr-dashboard-content' ).find( '.mvr-enquiry-form-wrapper' ),
			notification: $( 'div.mvr-dashboard-content' ).find( '.mvr-dashboard-notification-table' ),
			enquiryTable: $( 'div.mvr-dashboard-content' ).find( '.mvr-dashboard-enquiry-table' ),
			staff: $( 'div.mvr-dashboard-content' ).find( '.mvr-staff-form-wrapper' ),
			init() {
				if (0 === this.wrapper.length) {
					return false;
				}

				// Dashboard.
				if (this.wrapper.length) {

				}

				// Products Tab
				if (this.products.length) {
					this.products.on( 'click', '.mvr-delete', this.deleteProduct );
				}

				// Duplicate Tab.
				if (this.duplicate.length) {
					this.duplicate.on( 'click', 'a.mvr-duplicate', this.duplicateProduct );
				}

				// Payment Tab.
				if (this.payment.length) {
					this.payment
					.on( 'change', 'select#_payment_method', this.changePaymentMethod )
					.find( 'select#_payment_method' ).change();
				}

				// Payout Tab.
				if (this.payout.length) {
					this.payout
					.on( 'change', 'select.mvr-payout-type', this.changePayoutType )
					.find( 'select.mvr-payout-type' ).change();
					this.payout
					.on( 'change', 'select.mvr-payout-schedule', this.changePayoutSchedule )
					.find( 'select.mvr-payout-schedule' ).change();
				}

				// Withdraw Tab.
				if (this.withdraw.length) {
					this.withdraw
					.on( 'keyup change', '.mvr-withdraw-amount', this.withdrawAmountValidation );
				}

				// Profile Tab.
				if (this.profile.length) {
					this.profile
					.on( 'click', 'img.mvr-add-store-logo', this.addStoreLogo )
					.on( 'click', '.mvr-logo-remove', this.removeStoreLogo )
					.on( 'click', 'img.mvr-add-store-banner', this.addStoreBanner )
					.on( 'click', '.mvr-banner-remove', this.removeStoreBanner )
				}

				// Address Tab.
				if (this.address.length) {
					if (
					! (
						typeof mvr_dashboard_params === 'undefined' ||
						typeof mvr_dashboard_params.countries === 'undefined'
					)
					) {
						/* State/Country select boxes */
						this.states = JSON.parse( mvr_dashboard_params.countries.replace( /&quot;/g, '"' ) );
					}

					this.address.find( '.js_field-country' ).selectWoo()
					.on( 'change', this.changeCountry );
					this.address.find( '.js_field-country' ).trigger( 'change', [true] )
					.on( 'change', 'select.js_field-state', this.changeState );
				}

				// Enquiry.
				if (this.enquiry.length) {
					this.enquiry.on( 'click', '.mvr-enquiry-reply-send-btn', this.sendEnquiryReply );
				}

				// Notification.
				if (this.notification.length) {
					this.updateReadNotificationCount();
				}

				// Enquiry.
				if (this.enquiryTable.length) {
					this.updateReadEnquiryCount();
				}

				// Staff.
				if (this.staff.length) {
					this.staff.find( '.mvr-staff-capabilities-wrapper' ).accordion();
					this.staff.on( 'click', '.mvr-staff-capabilities-wrapper h4', this.capabilitiesSelect );

					this.staff.on( 'change', '.mvr-enable-product-management', this.enableProductManagement )
					.find( '.mvr-enable-product-management' ).change();

					this.staff.on( 'change', '.mvr-enable-order-management', this.enableOrderManagement )
					.find( '.mvr-enable-order-management' ).change();

					this.staff.on( 'change', '.mvr-enable-coupon-management', this.enableCouponManagement )
					.find( '.mvr-enable-coupon-management' ).change();
				}
			},

			capabilitiesSelect( e ) {
				let container = $( this ).closest( '.mvr-staff-capabilities-wrapper' );
				container.find( 'h4' ).removeClass( 'mvr-active' );
				$( this ).addClass( 'mvr-active' )
			},

			enableProductManagement( e ) {
				e.preventDefault();

				if (true === $( this ).is( ':checked' )) {
					$( '.mvr-product-management-field' ).closest( 'p' ).show();
				} else {
					$( '.mvr-product-management-field' ).closest( 'p' ).hide();
				}
			},

			enableOrderManagement( e ) {
				e.preventDefault();

				if (true === $( this ).is( ':checked' )) {
					$( '.mvr-order-management-field' ).closest( 'p' ).show();
				} else {
					$( '.mvr-order-management-field' ).closest( 'p' ).hide();
				}
			},

			enableCouponManagement( e ) {
				e.preventDefault();

				if (true === $( this ).is( ':checked' )) {
					$( '.mvr-coupon-management-field' ).closest( 'p' ).show();
				} else {
					$( '.mvr-coupon-management-field' ).closest( 'p' ).hide();
				}
			},

			deleteProduct( e ) {
				if ( ! confirm( mvr_dashboard_params.delete_product_confirm_msg )) {
					return false;
				}

				return true;
			},

			duplicateProduct( e ) {
				e.preventDefault();
				block( dashboard.wrapper.find( '.mvr-duplicate-products-table' ) );

				$.ajax(
					{
						type: 'POST',
						url: mvr_dashboard_params.ajax_url,
						dataType: 'json',
						data: {
							action: 'mvr_duplicate_product',
							product_id: $( this ).data( 'product_id' ),
							vendor_id: $( this ).data( 'vendor_id' ),
							source_vendor_id: $( this ).data( 'source_vendor_id' ),
							security: mvr_dashboard_params.duplicate_product_nonce,
						},
						success( response ) {
							if (response.data.redirect) {
								window.location = formatUrl( response.data.redirect );
							} else {
								window.alert( response.data.error );
							}
						},
						complete() {
							unblock( dashboard.wrapper.find( '.mvr-duplicate-products-table' ) );
						}
					}
				);
			},

			changePaymentMethod( e ) {
				e.preventDefault();

				let wrapper = dashboard.wrapper.find( '.mvr-payment-form-wrapper' );

				if ('2' === $( this ).val()) {
					wrapper.find( '.mvr-vendor-paypal-payment-field' ).closest( 'p' ).show();
					wrapper.find( '.mvr-vendor-bank-payment-field' ).closest( 'p' ).hide();
				} else {
					wrapper.find( '.mvr-vendor-paypal-payment-field' ).closest( 'p' ).hide();
					wrapper.find( '.mvr-vendor-bank-payment-field' ).closest( 'p' ).show();
				}
			},

			changePayoutType( e ) {
				e.preventDefault();

				let wrapper = dashboard.wrapper.find( '.mvr-payout-form-wrapper' );

				if ('2' === $( this ).val()) {
					wrapper.find( '.mvr-payout-schedule' ).closest( 'p' ).show();
					wrapper.find( 'select.mvr-payout-schedule' ).change();
				} else {
					wrapper.find( '.mvr-payout-schedule' ).closest( 'p' ).hide();
				}
			},

			changePayoutSchedule( e ) {
				e.preventDefault();

				let field = dashboard.wrapper.find( '.mvr-payout-form-wrapper select.mvr-payout-schedule' ),
				wrapper   = $( field ).closest( 'p' );

				$( wrapper ).find( '.mvr-description' ).hide();
				$( wrapper ).find( '.mvr-description-' + $( field ).val() ).show();
			},

			withdrawAmountValidation( e ) {
				e.preventDefault();

				var minWithdraw = parseFloat( mvr_dashboard_params.min_withdraw_amount ),
				availableAmount = parseFloat( mvr_dashboard_params.available_amount ),
				amount          = parseFloat( $( this ).val() ),
				chargeAmount    = parseFloat( mvr_dashboard_params.withdraw_charge ),
				withdrawAmount  = 0,
				withdrawDesc    = $( this ).closest( 'p' ).find( 'span.mvr-amount-desc' ),
				dispCharge      = false;

				if (minWithdraw > amount) {
					$( document.body ).triggerHandler( 'mvr_add_error_tip', [$( this ), 'min_withdraw',] );
					dashboard.withdraw.find( '.mvr-withdraw-req-submit' ).attr( 'disabled', true );
					$( this ).addClass( 'mvr-price-error' );
					dispCharge = false;
				} else {
					$( document.body ).triggerHandler( 'mvr_remove_error_tip', [$( this ), 'min_withdraw',] );
					dashboard.withdraw.find( '.mvr-withdraw-req-submit' ).attr( 'disabled', false );
					dispCharge = true;
				}

				if (availableAmount < amount) {
					$( document.body ).triggerHandler( 'mvr_add_error_tip', [$( this ), 'excess_withdraw',] );
					dashboard.withdraw.find( '.mvr-withdraw-req-submit' ).attr( 'disabled', true );
					$( this ).addClass( 'mvr-price-error' );
					dispCharge = false;
				} else {
					$( document.body ).triggerHandler( 'mvr_remove_error_tip', [$( this ), 'excess_withdraw',] );
					dashboard.withdraw.find( '.mvr-withdraw-req-submit' ).attr( 'disabled', false );
					dispCharge = (true === dispCharge) ? true : dispCharge;
				}

				if (('yes' !== mvr_dashboard_params.enable_withdraw_charge) || (chargeAmount <= 0) || (amount <= 0)) {
					withdrawDesc.html( '' );
					return;
				}

				if (dispCharge) {
					$( this ).removeClass( 'mvr-price-error' );

					if ('2' === mvr_dashboard_params.withdraw_charge_type) {
						chargeAmount = amount * (mvr_dashboard_params.withdraw_charge / 100);
					} else {
						chargeAmount = mvr_dashboard_params.withdraw_charge;
					}

					withdrawAmount = amount - chargeAmount;

					withdrawDesc.html( '' );
					withdrawDesc.html( '<span class="mvr-withdraw-amount-disp">' + mvr_dashboard_params.withdraw_amount_label + wc_price_format( withdrawAmount ) + '</span><br/><span class="mvr-withdraw-charge-disp">' + mvr_dashboard_params.withdraw_charge_label + wc_price_format( chargeAmount ) + '</span>' )
				} else {
					withdrawDesc.html( '' );
				}
			},

			addStoreLogo( e ) {
				e.preventDefault();
				var image_uploader;

				if (image_uploader) {
					image_uploader.open();
					return;
				}

				image_uploader = wp.media.frames.file_frame = wp.media(
					{
						title: mvr_dashboard_params.choose_logo_title,
						button: { text: mvr_dashboard_params.add_logo_title },
						multiple: false
					}
				);

			image_uploader.on(
				'select',
				function () {
					var attachment = image_uploader.state().get( 'selection' ).first().toJSON();

					dashboard.profile.find( 'img.mvr-store-logo' ).attr( 'src', attachment.url );
					dashboard.profile.find( '.mvr-logo-id' ).val( attachment.id );

					if (dashboard.profile.find( '.mvr-logo-id' ).val() > 0) {
						dashboard.profile.find( '.mvr-logo-remove' ).show();
					} else {
						dashboard.profile.find( '.mvr-logo-remove' ).hide();
					}
				}
			);

			image_uploader.open();
			},

			removeStoreLogo( e ) {
				e.preventDefault();

				dashboard.profile.find( 'img.mvr-store-logo' ).attr( 'src', mvr_dashboard_params.logo );
				dashboard.profile.find( '.mvr-logo-id' ).val( '' );
				$( this ).hide();
			},

			addStoreBanner( e ) {
				e.preventDefault();
				var image_uploader;

				if (image_uploader) {
					image_uploader.open();
					return;
				}

				image_uploader = wp.media.frames.file_frame = wp.media(
					{
						title: mvr_dashboard_params.choose_banner_title,
						button: { text: mvr_dashboard_params.add_banner_title },
						multiple: false
					}
				);

			image_uploader.on(
				'select',
				function () {
					var attachment = image_uploader.state().get( 'selection' ).first().toJSON();

					dashboard.profile.find( 'img.mvr-store-banner' ).attr( 'src', attachment.url );
					dashboard.profile.find( '.mvr-banner-id' ).val( attachment.id );

					if (dashboard.profile.find( '.mvr-banner-id' ).val() > 0) {
						dashboard.profile.find( '.mvr-banner-remove' ).show();
					} else {
						dashboard.profile.find( '.mvr-banner-remove' ).hide();
					}
				}
			);

			image_uploader.open();
			},

			removeStoreBanner( e ) {
				e.preventDefault();
				dashboard.profile.find( 'img.mvr-store-banner' ).attr( 'src', mvr_dashboard_params.banner );
				dashboard.profile.find( '.mvr-banner-id' ).val( '' );
				$( this ).hide();
			},

			changeCountry( e, stickValue ) {
				// Check for stickValue before using it.
				if (typeof stickValue === 'undefined') {
					stickValue = false;
				}

				// Prevent if we don't have the metabox data.
				if (dashboard.states === null) {
					return;
				}

				var $this   = $( this ),
				country     = $this.val(),
				$state      = dashboard.address.find( ':input.js_field-state' ),
				$parent     = $state.parent(),
				stateValue  = $state.val(),
				input_name  = $state.attr( 'name' ),
				input_id    = $state.attr( 'id' ),
				value       = $this.data( 'woocommerce.stickState-' + country ) ? $this.data( 'woocommerce.stickState-' + country ) : stateValue,
				placeholder = $state.attr( 'placeholder' ),
				$newState;

				if (stickValue) {
					$this.data( 'woocommerce.stickState-' + country, value );
				}

				// Remove the previous DOM element
				$parent.show().find( '.select2-container' ).remove();

				if ( ! $.isEmptyObject( dashboard.states[country] )) {
					var state      = dashboard.states[country],
					$defaultOption = $( '<option value=""></option>' )
						.text( mvr_dashboard_params.i18n_select_state_text );

					$newState = $( '<select></select>' )
					.prop( 'id', input_id )
					.prop( 'name', input_name )
					.prop( 'placeholder', placeholder )
					.addClass( 'js_field-state select short' )
					.append( $defaultOption );

					$.each(
						state,
						function (index) {
							var $option = $( '<option></option>' )
							.prop( 'value', index )
							.text( state[index] );
							if (index === stateValue) {
								$option.prop( 'selected' );
							}
							$newState.append( $option );
						}
					);

					$newState.val( value );
					$state.replaceWith( $newState );
					$newState.show().selectWoo().hide().trigger( 'change' );
				} else {
					$newState = $( '<input type="text" />' )
					.prop( 'id', input_id )
					.prop( 'name', input_name )
					.prop( 'placeholder', placeholder )
					.addClass( 'js_field-state' )
					.val( stateValue );
					$state.replaceWith( $newState );
				}

				$( document.body ).trigger( 'country-change.vendor', [country, $( this ).closest( 'div' )] );
			},

			changeState() {
				// Here we will find if state value on a select has changed and stick it to the country data
				var $this = $( this ),
				state     = $this.val(),
				$country  = dashboard.address.find( ':input.js_field-country' ),
				country   = $country.val();

				$country.data( 'woocommerce.stickState-' + country, state );
			},

			sendEnquiryReply( e ) {
				e.preventDefault();

				block( dashboard.enquiry.find( '.mvr-enquiry-action' ) );

				$.ajax(
					{
						type: 'POST',
						url: mvr_dashboard_params.ajax_url,
						dataType: 'json',
						data: {
							action: 'mvr_send_enquiry_reply',
							enquiry_id: dashboard.enquiry.find( '.mvr-enquiry-id' ).val(),
							customer_email: dashboard.enquiry.find( '.mvr-customer-enquiry-email' ).val(),
							message: tinymce.get( '_enquiry_reply_message' ).getContent(),
							security: mvr_dashboard_params.enquiry_nonce,
						},
						success( response ) {
							if (response.data.url) {
								window.alert( response.data.message );
								window.location = formatUrl( response.data.url );
							} else {
								window.alert( response.data.error );
							}
						},
						complete() {
							unblock( dashboard.wrapper.find( '.mvr-enquiry-action' ) );
						}
					}
				);
			},

			updateReadEnquiryCount() {
				$.ajax(
					{
						type: 'POST',
						url: mvr_dashboard_params.ajax_url,
						dataType: 'json',
						data: {
							action: 'mvr_update_enquiry_read_count',
							vendor_id: mvr_dashboard_params.current_vendor_id,
							security: mvr_dashboard_params.enquiry_nonce,
						},
						success( response ) {
							if (response.success) {
								let wrap = $( '.mvr-dashboard-top-navigation' ).find( 'span.mvr-enquiry-count-wrapper' );

								if (response.data.count > 0) {
									wrap.find( 'span.mvr-enquiry' ).html( response.data.count );
								} else {
									wrap.remove();
								}
							}
						},
					}
				);
			},

			updateReadNotificationCount() {
				$.ajax(
					{
						type: 'POST',
						url: mvr_dashboard_params.ajax_url,
						dataType: 'json',
						data: {
							action: 'mvr_update_notification_read_count',
							vendor_id: mvr_dashboard_params.current_vendor_id,
							security: mvr_dashboard_params.notification_nonce,
						},
						success( response ) {
							if (response.success) {
								let wrap = $( '.mvr-dashboard-top-navigation' ).find( 'span.mvr-notification-count-wrapper' );

								if (response.data.count > 0) {
									wrap.find( 'span.mvr-notification' ).html( response.data.count );
								} else {
									wrap.remove();
								}
							}
						},
					}
				);
			},
		};

		dashboard.init();
	}
);
