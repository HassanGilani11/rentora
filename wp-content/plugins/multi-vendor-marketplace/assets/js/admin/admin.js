/* global mvr_admin_params */

jQuery(
	function ($) {
		'use strict';

		if (typeof mvr_admin_params === 'undefined') {
			return false;
		}

		function wc_number_format(number) {
			var decimals  = mvr_admin_params.currency_format_num_decimals,
			decimal_sep   = mvr_admin_params.decimal_point,
			thousands_sep = mvr_admin_params.currency_format_thousand_sep,
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
			let remove = mvr_admin_params.decimal_point,
			position   = mvr_admin_params.currency_position,
			symbol     = mvr_admin_params.wc_currency_symbol,
			trim_zeros = mvr_admin_params.currency_format_trim_zeros,
			decimals   = mvr_admin_params.currency_format_num_decimals;

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

		var $withdraw_screen = $( '#mvr_admin_withdraw_form' ),
		$title_action        = $withdraw_screen.find( '.page-title-action:first' );

		if (mvr_admin_params.urls.export_withdraws) {
			$title_action.after(
				'<a class="page-title-action" href="' + mvr_admin_params.urls.export_withdraws + '">' + mvr_admin_params.strings.export_withdraws + '</a>'
			);
		}

		if (mvr_admin_params.urls.generate_withdraw_payout) {
			$title_action.after(
				'<a class="page-title-action" href="' + mvr_admin_params.urls.generate_withdraw_payout + '">' + mvr_admin_params.strings.generate_withdraw_payout + '</a>'
			);
		}

		var $vendor_screen = $( '.post-type-mvr_vendor' ),
		$title_action      = $vendor_screen.find( '.page-title-action:first' );

		if (mvr_admin_params.urls.generate_payout) {
			$title_action.after(
				'<a class="page-title-action" href="' + mvr_admin_params.urls.generate_payout + '">' + mvr_admin_params.strings.generate_payout + '</a>'
			);
		}

		// Field validation error tips
		$( document.body )

		.on(
			'mvr_add_error_tip',
			function (e, element, error_type) {
				var offset = element.position();

				if (element.parent().find( '.mvr-error-tip' ).length === 0) {
					element.after( '<div class="mvr-error-tip ' + error_type + '">' + mvr_admin_params[error_type] + '</div>' );
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
				decimalPoint = mvr_admin_params.decimal_point;

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
				regex                   = new RegExp( '[^-0-9%\\' + mvr_admin_params.decimal_point + ']+', 'gi' ),
				decimalRegex            = new RegExp( '[^\\' + mvr_admin_params.decimal_point + ']', 'gi' ),
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

		var MVR_Admin = {
			init() {
				$( '#the-list' ).on( 'click', '.editinline', this.quickEditProduct );

				$( document ).on( 'change', '.mvr-commission-criteria', this.changeCommissionCriteria )
				.find( '.mvr-commission-criteria' ).change();

				$( document ).on( 'change', '.mvr-settings-allow-user-register', this.allowUserRegister )
				.find( '.mvr-settings-allow-user-register' ).change();

				$( document ).on( 'change', '.mvr-paypal-payout-mode', this.enableSandboxPaypalPayoutMode )
				.find( '.mvr-enable-paypal-payout' ).change();

				$( document ).on( 'change', '.mvr-enable-paypal-payout', this.enablePaypalPayout )
				.find( '.mvr-enable-paypal-payout' ).change();

				$( document ).on( 'change', '.mvr-withdraw-charge-settings-allow-cb', this.enableWithdrawChargeSettings )
				.find( '.mvr-withdraw-charge-settings-allow-cb' ).change();

				$( document ).on( 'change', '.mvr-settings-enable-automatic-withdraw', this.enableAutomaticWithdraw )
				.find( '.mvr-settings-enable-automatic-withdraw' ).change();

				$( document ).on( 'change', '.mvr-settings-enable-auto-withdraw-weekly', this.enableAutomaticWithdrawWeekly )
				.find( '.mvr-settings-enable-auto-withdraw-weekly' ).change();

				$( document ).on( 'change', '.mvr-settings-enable-auto-withdraw-biweekly', this.enableAutomaticWithdrawBiWeekly )
				.find( '.mvr-settings-enable-auto-withdraw-biweekly' ).change();

				$( document ).on( 'change', '.mvr-settings-enable-auto-withdraw-monthly', this.enableAutomaticWithdrawMonthly )
				.find( '.mvr-settings-enable-auto-withdraw-monthly' ).change();

				$( document ).on( 'change', '.mvr-settings-enable-auto-withdraw-quarterly', this.enableAutomaticWithdrawQuarterly )
				.find( '.mvr-settings-enable-auto-withdraw-quarterly' ).change();

				$( document ).on( 'change', '.mvr-withdraw-settings-allow-cb', this.enableWithdrawSettings )
				.find( '.mvr-withdraw-settings-allow-cb' ).change();

				$( document ).on( 'change', '.mvr-settings-enable-vendor-subscription', this.enableVendorSubscription )
				.find( '.mvr-settings-enable-vendor-subscription' ).change();

				$( document ).on( 'change', '.mvr-settings-enable-product-management', this.enableProductManagement )
				.find( '.mvr-settings-enable-product-management' ).change();

				$( document ).on( 'change', '.mvr-settings-enable-order-management', this.enableOrderManagement )
				.find( '.mvr-settings-enable-order-management' ).change();

				$( document ).on( 'change', '.mvr-settings-enable-coupon-management', this.enableCouponManagement )
				.find( '.mvr-settings-enable-coupon-management' ).change();

				$( document ).on( 'change', '.mvr-settings-enable-withdraw-management', this.enableWithdrawManagement )
				.find( '.mvr-settings-enable-withdraw-management' ).change();

				$( document ).on( 'click', 'a.mvr-vendor-delete', this.confirmDeleteVendor );
				$( document ).on( 'click', 'a.mvr-delete-commission', this.confirmDeleteCommission );
				$( document ).on( 'click', 'a.mvr-delete-withdraw', this.confirmDeleteWithdraw );
				$( document ).on( 'click', 'a.mvr-delete-transaction', this.confirmDeleteTransaction );
				$( document ).on( 'click', 'a.mvr-delete-payout', this.confirmDeletePayout );
				$( document ).on( 'click', 'a.mvr-delete-enquiry', this.confirmDeleteEnquiry );
				$( document ).on( 'click', 'a.mvr-delete-staff', this.confirmDeleteStaff );

				$( document ).on( 'click', 'a.mvr-make-payment', this.confirmWithdrawMakePayment );
				$( document ).on( 'click', 'a.mvr-reject-payment', this.confirmWithdrawRejectPayment );
			},

			confirmWithdrawMakePayment( e ) {
				if ( ! window.confirm( mvr_admin_params.withdraw_make_payment_msg )) {
					e.preventDefault();
				}
			},

			confirmWithdrawRejectPayment( e ) {
				if ( ! window.confirm( mvr_admin_params.withdraw_reject_payment_msg )) {
					e.preventDefault();
				}
			},

			confirmDeleteVendor( e ) {
				if ( ! window.confirm( mvr_admin_params.vendor_delete_msg )) {
					e.preventDefault();
				}
			},

			confirmDeleteCommission( e ) {
				if ( ! window.confirm( mvr_admin_params.commission_delete_msg )) {
					e.preventDefault();
				}
			},

			confirmDeleteWithdraw( e ) {
				if ( ! window.confirm( mvr_admin_params.withdraw_delete_msg )) {
					e.preventDefault();
				}
			},

			confirmDeleteTransaction( e ) {
				if ( ! window.confirm( mvr_admin_params.transaction_delete_msg )) {
					e.preventDefault();
				}
			},

			confirmDeletePayout( e ) {
				if ( ! window.confirm( mvr_admin_params.payout_delete_msg )) {
					e.preventDefault();
				}
			},

			confirmDeleteEnquiry( e ) {
				if ( ! window.confirm( mvr_admin_params.enquiry_delete_msg )) {
					e.preventDefault();
				}
			},

			confirmDeleteStaff( e ) {
				if ( ! window.confirm( mvr_admin_params.staff_delete_msg )) {
					e.preventDefault();
				}
			},

			quickEditProduct( e ) {
				e.preventDefault();
				var post_id   = $( this ).closest( 'tr' ).attr( 'id' );
				post_id       = post_id.replace( "post-", "" );
				let vendor_id = $( '#mvr_vendor_inline_' + post_id ).find( '#mvr_vendor_id' ).val();

				$( 'select[name="_mvr_product_vendor"] option', '.inline-edit-row' ).attr( 'selected', false ).trigger( 'change' );
				$( 'select[name="_mvr_product_vendor"] option[value="' + vendor_id + '"]' ).attr( 'selected', 'selected' ).trigger( 'change' );
			},

			allowUserRegister( e ) {
				e.preventDefault();

				if (true === $( this ).is( ':checked' )) {
					$( '.mvr-settings-become-vendor-role' ).closest( 'tr' ).show();
				} else {
					$( '.mvr-settings-become-vendor-role' ).closest( 'tr' ).hide();
				}
			},

			changeCommissionCriteria( e ) {
				e.preventDefault();

				if ('1' === $( this ).val()) {
					$( '.mvr-commission-criteria-field' ).closest( 'tr' ).hide();
				} else {
					$( '.mvr-commission-criteria-field' ).closest( 'tr' ).show();
				}
			},

			enablePaypalPayout( e ) {
				e.preventDefault();

				if (true === $( this ).is( ':checked' )) {
					$( '.mvr-paypal-payout-field' ).closest( 'tr' ).show();

					$( document ).find( '.mvr-paypal-payout-mode' ).change();
				} else {
					$( '.mvr-paypal-payout-field' ).closest( 'tr' ).hide();
				}
			},

			enableWithdrawSettings( e ) {
				if (true === $( this ).is( ':checked' )) {
					$( '.mvr-withdraw-settings-field' ).closest( 'tr' ).show();
					$( document ).find( '.mvr-settings-enable-automatic-withdraw' ).change();
				} else {
					$( '.mvr-withdraw-settings-field' ).closest( 'tr' ).hide();
				}
			},

			enableWithdrawChargeSettings( e ) {
				if (true === $( this ).is( ':checked' )) {
					$( '.mvr-withdraw-charge-settings-field' ).closest( 'tr' ).show();
				} else {
					$( '.mvr-withdraw-charge-settings-field' ).closest( 'tr' ).hide();
				}
			},

			enableAutomaticWithdraw( e ) {
				e.preventDefault();

				if (true === $( this ).is( ':checked' )) {
					$( '.mvr-auto-withdraw-field' ).closest( 'tr' ).show();
					$( document ).find( '.mvr-settings-enable-auto-withdraw-weekly' ).change();
					$( document ).find( '.mvr-settings-enable-auto-withdraw-biweekly' ).change();
					$( document ).find( '.mvr-settings-enable-auto-withdraw-monthly' ).change();
					$( document ).find( '.mvr-settings-enable-auto-withdraw-quarterly' ).change();
				} else {
					$( '.mvr-auto-withdraw-field' ).closest( 'tr' ).hide();
				}
			},

			enableAutomaticWithdrawWeekly( e ) {
				e.preventDefault();

				if (true === $( this ).is( ':checked' )) {
					$( '.mvr-auto-withdraw-week-field' ).closest( 'tr' ).show();
				} else {
					$( '.mvr-auto-withdraw-week-field' ).closest( 'tr' ).hide();
				}
			},

			enableAutomaticWithdrawBiWeekly( e ) {
				e.preventDefault();

				if (true === $( this ).is( ':checked' )) {
					$( '.mvr-auto-withdraw-biweekly-field' ).closest( 'tr' ).show();
				} else {
					$( '.mvr-auto-withdraw-biweekly-field' ).closest( 'tr' ).hide();
				}
			},

			enableAutomaticWithdrawMonthly( e ) {
				e.preventDefault();

				if (true === $( this ).is( ':checked' )) {
					$( '.mvr-auto-withdraw-monthly-field' ).closest( 'tr' ).show();
				} else {
					$( '.mvr-auto-withdraw-monthly-field' ).closest( 'tr' ).hide();
				}
			},

			enableAutomaticWithdrawQuarterly( e ) {
				e.preventDefault();

				if (true === $( this ).is( ':checked' )) {
					$( '.mvr-auto-withdraw-quarterly-field' ).closest( 'tr' ).show();
				} else {
					$( '.mvr-auto-withdraw-quarterly-field' ).closest( 'tr' ).hide();
				}
			},

			enableVendorSubscription( e ) {
				e.preventDefault();

				if (true === $( this ).is( ':checked' )) {
					$( '.mvr-settings-subscription-product' ).closest( 'tr' ).show();
				} else {
					$( '.mvr-settings-subscription-product' ).closest( 'tr' ).hide();
				}
			},

			enableProductManagement( e ) {
				e.preventDefault();

				if (true === $( this ).is( ':checked' )) {
					$( '.mvr-settings-product-management-field' ).closest( 'tr' ).show();
				} else {
					$( '.mvr-settings-product-management-field' ).closest( 'tr' ).hide();
				}
			},

			enableOrderManagement( e ) {
				e.preventDefault();

				if (true === $( this ).is( ':checked' )) {
					$( '.mvr-settings-order-management-field' ).closest( 'tr' ).show();
				} else {
					$( '.mvr-settings-order-management-field' ).closest( 'tr' ).hide();
				}
			},

			enableCouponManagement( e ) {
				e.preventDefault();

				if (true === $( this ).is( ':checked' )) {
					$( '.mvr-settings-coupon-management-field' ).closest( 'tr' ).show();
				} else {
					$( '.mvr-settings-coupon-management-field' ).closest( 'tr' ).hide();
				}
			},

			enableWithdrawManagement( e ) {
				e.preventDefault();

				if (true === $( this ).is( ':checked' )) {
					$( '.mvr-settings-withdraw-management-field' ).closest( 'tr' ).show();
				} else {
					$( '.mvr-settings-withdraw-management-field' ).closest( 'tr' ).hide();
				}
			},

			enableSandboxPaypalPayoutMode( e ) {
				e.preventDefault();

				if (true === $( this ).is( ':checked' )) {
					$( '.mvr-paypal-payout-sand-field' ).closest( 'tr' ).show();
					$( '.mvr-paypal-payout-live-field' ).closest( 'tr' ).hide();
				} else {
					$( '.mvr-paypal-payout-live-field' ).closest( 'tr' ).show();
					$( '.mvr-paypal-payout-sand-field' ).closest( 'tr' ).hide();
				}
			}
		};

		var vendorNotes = {
			$wrapper: $( '#mvr_vendor_notes' ),
			init: function () {
				this.$wrapper
				.on( 'click', 'button.mvr-add-vendor-note', this.addNote )
				.on( 'click', 'a.mvr-delete-vendor-note', this.deleteNote );

			},
			addNote: function () {
				if ( ! vendorNotes.$wrapper.find( '#mvr_add_vendor_note' ).val()) {
					return;
				}

				block( vendorNotes.$wrapper );

				$.ajax(
					{
						type: 'POST',
						url: ajaxurl,
						dataType: 'json',
						data: {
							action: 'mvr_add_vendor_note',
							post_id: mvr_admin_params.post_id,
							note: vendorNotes.$wrapper.find( '#mvr_add_vendor_note' ).val(),
							security: mvr_admin_params.add_vendor_note_nonce,
						},
						success( response ) {
							vendorNotes.$wrapper.find( 'ul.mvr-vendor-notes .no-items' ).remove();
							vendorNotes.$wrapper.find( 'ul.mvr-vendor-notes' ).prepend( response.data.html );
							vendorNotes.$wrapper.find( '#mvr_add_vendor_note' ).val( '' );

						}
					}
				);

				unblock( vendorNotes.$wrapper );
			},

			deleteNote: function () {
				if (window.confirm( mvr_admin_params.delete_note_msg )) {
					var note = $( this ).closest( 'li.note' );

					block( $( note ) );

					$.ajax(
						{
							type: 'POST',
							url: ajaxurl,
							dataType: 'json',
							data: {
								action: 'mvr_delete_vendor_note',
								note_id: $( note ).attr( 'rel' ),
								security: mvr_admin_params.delete_vendor_note_nonce
							},
							success() {
								$( note ).remove();
							}
						}
					);

					unblock( $( note ) );
				}
			}
		};

		var payoutBatchNotes = {
			$wrapper: $( '#mvr_payout_batch_notes' ),
			init: function () {
				this.$wrapper
				.on( 'click', 'button.mvr-add-payout-batch-note', this.addNote )
				.on( 'click', 'a.mvr-delete-payout-batch-note', this.deleteNote );

			},
			addNote: function () {
				if ( ! payoutBatchNotes.$wrapper.find( '#mvr_add_payout_batch_note' ).val()) {
					return;
				}

				block( payoutBatchNotes.$wrapper );

				$.ajax(
					{
						type: 'POST',
						url: ajaxurl,
						dataType: 'json',
						data: {
							action: 'mvr_add_payout_batch_note',
							post_id: mvr_admin_params.post_id,
							note: payoutBatchNotes.$wrapper.find( '#mvr_add_payout_batch_note' ).val(),
							security: mvr_admin_params.add_vendor_note_nonce,
						},
						success( response ) {
							if (response.success) {
								payoutBatchNotes.$wrapper.find( 'ul.mvr-payout-batch-notes .no-items' ).remove();
								payoutBatchNotes.$wrapper.find( 'ul.mvr-payout-batch-notes' ).prepend( response.data.html );
								payoutBatchNotes.$wrapper.find( '#mvr_add_payout_batch_note' ).val( '' );
							} else {
								window.alert( response.data.error );
							}
						}
					}
				);

				unblock( payoutBatchNotes.$wrapper );
			},

			deleteNote: function () {
				if (window.confirm( mvr_admin_params.delete_note_msg )) {
					var note = $( this ).closest( 'li.note' );

					block( $( note ) );

					$.ajax(
						{
							type: 'POST',
							url: ajaxurl,
							dataType: 'json',
							data: {
								action: 'mvr_delete_payout_batch_note',
								note_id: $( note ).attr( 'rel' ),
								security: mvr_admin_params.delete_vendor_note_nonce
							},
							success() {
								$( note ).remove();
							}
						}
					);

					unblock( $( note ) );
				}
			}
		};

		var SPMV = {
			$wrapper: $( 'div.mvr-product-spmv' ),

			init() {
				this.$wrapper
				.on( 'click', 'button.mvr-product-spmv-btn', this.addVendorSPMVProduct )
				.on( 'click', 'span.mvr-remove-spmv', this.removeVendorSPMVProduct )
				.on( 'change', 'select.mvr-spmv-search-field', this.changeVendorField );
			},

			changeVendorField( e ) {
				e.preventDefault();

				if ('' === $( this ).val() || null === $( this ).val()) {
					SPMV.$wrapper.find( 'button.mvr-product-spmv-btn' ).attr( 'disabled', true );
				} else {
					SPMV.$wrapper.find( 'button.mvr-product-spmv-btn' ).attr( 'disabled', false );
				}
			},

			addVendorSPMVProduct( e ) {
				e.preventDefault();

				let vendorSelect = SPMV.$wrapper.find( 'select.mvr-spmv-search-field' ),
				list             = SPMV.$wrapper.find( 'ul.mvr-product-spmv-list' ),
				productID        = SPMV.$wrapper.find( 'input.mvr-product-id' ).val(),
				sourceVendorID   = SPMV.$wrapper.find( 'input.mvr-source-vendor-id' ).val();

				block( SPMV.$wrapper );

				$.ajax(
					{
						type: 'POST',
						url: ajaxurl,
						dataType: 'json',
						data: {
							action: 'mvr_duplicate_product',
							product_id: productID,
							vendor_id: vendorSelect.val(),
							source_vendor_id: sourceVendorID,
							security: mvr_admin_params.add_spmv_nonce
						},
						success( response ) {
							if (response.success) {
								list.append( '<li><a href="' + response.data.vendor_url + '">' + response.data.shop_name + ' - ' + response.data.vendor_name + '</a><span class="mvr-delete-product"><span class="mvr-remove-spmv" data-spmv-id="' + response.data.spmv_id + '" data-product-id="' + response.data.product_id + '">x</span></li>' );
								vendorSelect.empty();
								vendorSelect.change();
							} else {
								alert( response.data.error );
							}
						}
					}
				);

			unblock( SPMV.$wrapper );
			},

			removeVendorSPMVProduct( e ) {
				e.preventDefault();

				if (window.confirm( mvr_admin_params.remove_spmv_msg )) {
					let spmv_id = $( this ).data( 'spmv-id' ),
					productID   = $( this ).data( 'product-id' ),
					wrapper     = $( this ).closest( 'li' );

					$.ajax(
						{
							type: 'POST',
							url: ajaxurl,
							dataType: 'json',
							data: {
								action: 'mvr_remove_spmv_product',
								spmv_id: spmv_id,
								product_id: productID,
								security: mvr_admin_params.remove_spmv_nonce
							},
							success( response ) {
								if (response.success) {
									wrapper.remove();
								} else {
									alert( response.data.error );
								}
							}
						}
					);
				}
			}
		};

		var addVendor = {
			init() {
				$( document ).on( 'click', 'body.post-type-mvr_vendor a.page-title-action:first', this.render );
				$( document.body ).on( 'wc_backbone_modal_loaded', this.backbone.init );
			},

			render( e ) {
				e.preventDefault();

				$( this ).WCBackboneModal(
					{
						template: 'mvr-modal-add-vendor'
					}
				);

			return false;
			},

			backbone: {
				init( e, target ) {
					if ('mvr-modal-add-vendor' === target) {
						$( document.body ).trigger( 'mvr-enhanced-init' );

						$( '.mvr-add-vendor-wrapper' ).on( 'input change', '.mvr-required-field', addVendor.backbone.validateRequiredField );

						$( '.mvr-add-vendor-wrapper' ).on( 'change', '.mvr-vendor-selection-type', addVendor.backbone.vendorSelectionType )
						.find( '.mvr-vendor-selection-type' ).change();

						$( '.mvr-add-vendor-wrapper' ).on( 'click', '.mvr-add-vendor', addVendor.backbone.add );

					}
				},

				validateRequiredField() {
					var display = false,
					wrapper     = $( '.mvr-add-vendor-wrapper' );

					if ('2' === wrapper.find( '.mvr-vendor-selection-type' ).val()) {
						wrapper.find( '.mvr-required-field' ).filter( ':visible' ).each(
							function () {
								if ('' === $( this ).val() || null === $( this ).val()) {
									display = false;
									return false;
								} else {
									display = true;
								}
							}
						);

						if (true === display) {
							wrapper.find( '.mvr-add-vendor' ).show();
						} else {
							wrapper.find( '.mvr-add-vendor' ).hide();
						}
					} else {
						if ('' === wrapper.find( '.mvr-selected-user' ).val() || null === wrapper.find( '.mvr-selected-user' ).val()) {
							wrapper.find( '.mvr-add-vendor' ).hide();
						} else {
							wrapper.find( '.mvr-add-vendor' ).show();
						}
					}
				},

				vendorSelectionType( e ) {
					e.preventDefault();

					$( '.mvr-add-vendor-wrapper' ).find( 'p.mvr-add-vendor-fields' ).hide();

					if ('2' === $( this ).val()) {
						$( '.mvr-add-vendor-wrapper' ).find( 'p.mvr-new-user-field' ).show();
					} else {
						$( '.mvr-add-vendor-wrapper' ).find( 'p.mvr-existing-user-field' ).show();
					}

					addVendor.backbone.validateRequiredField();
				},

				add( e ) {
					e.preventDefault();

					var $this = $( e.currentTarget ),
					wrapper   = $this.closest( '.mvr-add-vendor-wrapper' ),
					$user_id  = parseInt( wrapper.find( 'select[name="_selected_user"]' ).val() );

					block( $this.closest( '.wc-backbone-modal-content' ) );

					$.ajax(
						{
							type: 'POST',
							url: ajaxurl,
							dataType: 'json',
							data: {
								action: 'mvr_add_vendor',
								security: mvr_admin_params.add_vendor_nonce,
								user_id: $user_id,
								vendor_from: wrapper.find( '.mvr-vendor-selection-type' ).val(),
								user_name: wrapper.find( '.mvr-user-name' ).val(),
								user_email: wrapper.find( '.mvr-user-email' ).val(),
								password: wrapper.find( '.mvr-user-password' ).val(),
								confirm_password: wrapper.find( '.mvr-user-confirm-password' ).val(),
							},
							success( response ) {
								if (response.success) {
									window.location = formatUrl( response.data.redirect );
								} else {
									window.alert( response.data.error );
								}
							},
							complete() {
								unblock( $this.closest( '.wc-backbone-modal-content' ) );
							}
						}
					);
				}
			}
		};

		var payVendor = {
			init() {
				$( document ).on( 'click', '.mvr-vendor-payment', this.render );
				$( document.body ).on( 'wc_backbone_modal_loaded', this.backbone.init );
			},

			render( e ) {
				e.preventDefault();
				var $payButton = $( this ),
				$vendorId      = $payButton.data( 'vendor_id' );

				if ($payButton.data( 'mvr-vendor-data' )) {
					$( this ).WCBackboneModal(
						{
							template: 'mvr-modal-pay-vendor',
							variable: $payButton.data( 'vendorData' )
						}
					);

					return false;
				} else {
					$payButton.addClass( 'disabled' );

					$.ajax(
						{
							type: 'GET',
							url: ajaxurl,
							dataType: 'json',
							data: {
								action: 'mvr_get_vendor_details',
								vendor_id: $vendorId,
								security: mvr_admin_params.pay_vendor_nonce
							},
							success( response ) {
								$( '.mvr-vendor-payment' ).removeClass( 'disabled' );

								if (response.success) {
									$payButton.data( 'vendorData', response.data );

									$( this ).WCBackboneModal(
										{
											template: 'mvr-modal-pay-vendor',
											variable: response.data
										}
									);
								}
							}
						}
					);
				}
			},

			backbone: {
				init( e, target ) {
					if ('mvr-modal-pay-vendor' === target) {
						$( document.body ).trigger( 'mvr-enhanced-init' );

						$( '.mvr-pay-vendor-wrapper' ).on( 'input change', '.mvr-pay-amount', payVendor.backbone.validateVendorPayAmount );
						$( '.mvr-pay-vendor-wrapper' ).on( 'click', '.mvr-pay-vendor', payVendor.backbone.makePayment );
					}
				},

				validateVendorPayAmount( e ) {
					e.preventDefault();

					var wrapper    = $( '.mvr-pay-vendor-wrapper' ),
					chargeAmount   = parseFloat( mvr_admin_params.withdraw_charge ),
					withdrawAmount = $( this ).val(),
					withdrawDesc   = $( this ).closest( 'p' ).find( 'span.mvr-amount-desc' );

					withdrawDesc.html( '' );

					if ('' === $( this ).val()) {
						wrapper.find( '.mvr-pay-vendor' ).hide();
					} else if (parseFloat( $( this ).val() ) <= parseFloat( 0 )) {
						withdrawDesc.html( '<span class="mvr-error-tip">' + mvr_admin_params.valid_amount_msg + '</span>' );
						wrapper.find( '.mvr-pay-vendor' ).hide();
					} else if (parseFloat( $( this ).val() ) > parseFloat( $( this ).data( 'available_amount' ) )) {
						withdrawDesc.html( '<span class="mvr-error-tip">' + mvr_admin_params.available_amount_msg + '</span>' );
						wrapper.find( '.mvr-pay-vendor' ).hide();
					} else {
						if ('yes' === mvr_admin_params.enable_withdraw_charge) {
							if ('2' === mvr_admin_params.withdraw_charge_type) {
								chargeAmount = withdrawAmount * (mvr_admin_params.withdraw_charge / 100);
							} else {
								chargeAmount = mvr_admin_params.withdraw_charge;
							}

							withdrawAmount = withdrawAmount - chargeAmount;

							withdrawDesc.html( '<span class="mvr-withdraw-amount-disp">' + mvr_admin_params.withdraw_amount_label + wc_price_format( withdrawAmount ) + '</span><br/><span class="mvr-withdraw-charge-disp">' + mvr_admin_params.withdraw_charge_label + wc_price_format( chargeAmount ) + '</span>' )
						}

						wrapper.find( '.mvr-pay-vendor' ).show();
					}
				},

				makePayment( e ) {
					e.preventDefault();

					var $this  = $( e.currentTarget ),
					wrapper    = $this.closest( '.mvr-pay-vendor-wrapper' ),
					$vendor_id = $( this ).data( 'vendor_id' ),
					$amount    = wrapper.find( '.mvr-pay-amount' ).val();

					block( $this.closest( '.wc-backbone-modal-content' ) );

					$.ajax(
						{
							type: 'POST',
							url: ajaxurl,
							dataType: 'json',
							data: {
								action: 'mvr_pay_vendor_amount',
								security: mvr_admin_params.pay_vendor_nonce,
								vendor_id: $vendor_id,
								amount: $amount,
							},
							success( response ) {
								if (response.success) {
									window.alert( mvr_admin_params.pay_vendor_msg );
									window.location = formatUrl( response.data.redirect );
								} else {
									window.alert( response.data.error );
								}
							},
							complete() {
								unblock( $this.closest( '.wc-backbone-modal-content' ) );
							}
						}
					);
				}
			}
		};

		var addStaff = {
			init() {
				$( document ).on( 'click', 'body.post-type-mvr_staff a.page-title-action', this.render );
				$( document.body ).on( 'wc_backbone_modal_loaded', this.backbone.init );
			},

			render( e ) {
				e.preventDefault();

				$( this ).WCBackboneModal(
					{
						template: 'mvr-modal-add-staff'
					}
				);

			return false;
			},

			backbone: {
				init( e, target ) {
					if ('mvr-modal-add-staff' === target) {
						$( document.body ).trigger( 'mvr-enhanced-init' );

						$( '.mvr-add-staff-wrapper' ).on( 'input change', '.mvr-required-field', addStaff.backbone.validateRequiredField );

						$( '.mvr-add-staff-wrapper' ).on( 'change', '.mvr-staff-selection-type', addStaff.backbone.staffSelectionType )
						.find( '.mvr-staff-selection-type' ).change();

						$( '.mvr-add-staff-wrapper' ).on( 'click', '.mvr-add-staff', addStaff.backbone.add );
					}
				},

				validateRequiredField() {
					var display = false,
					wrapper     = $( '.mvr-add-staff-wrapper' );

					if ('2' === wrapper.find( '.mvr-staff-selection-type' ).val()) {
						wrapper.find( '.mvr-required-field' ).filter( ':visible' ).each(
							function () {
								if ('' === $( this ).val() || null === $( this ).val()) {
									display = false;
									return false;
								} else {
									display = true;
								}
							}
						);

						if (true === display) {
							wrapper.find( '.mvr-add-staff' ).show();
						} else {
							wrapper.find( '.mvr-add-staff' ).hide();
						}

					} else {
						if ('' === wrapper.find( '.mvr-selected-user' ).val() || null === wrapper.find( '.mvr-selected-user' ).val()) {
							wrapper.find( '.mvr-add-staff' ).hide();
						} else {
							wrapper.find( '.mvr-add-staff' ).show();
						}
					}
				},

				staffSelectionType( e ) {
					e.preventDefault();

					$( '.mvr-add-staff-wrapper' ).find( 'p.mvr-add-staff-fields' ).hide();

					if ('2' === $( this ).val()) {
						$( '.mvr-add-staff-wrapper' ).find( 'p.mvr-new-user-field' ).show();
					} else {
						$( '.mvr-add-staff-wrapper' ).find( 'p.mvr-existing-user-field' ).show();
					}

					addStaff.backbone.validateRequiredField();
				},

				add( e ) {
					e.preventDefault();

					var $this = $( e.currentTarget ),
					wrapper   = $this.closest( '.mvr-add-staff-wrapper' ),
					$user_id  = parseInt( $this.closest( '.mvr-add-staff-wrapper' ).find( 'select[name="_selected_user"]' ).val() );

					block( $this.closest( '.wc-backbone-modal-content' ) );

					$.ajax(
						{
							type: 'POST',
							url: ajaxurl,
							dataType: 'json',
							data: {
								action: 'mvr_add_staff',
								security: mvr_admin_params.add_staff_nonce,
								user_id: $user_id,
								staff_from: $this.closest( '.mvr-add-staff-wrapper' ).find( '.mvr-staff-selection-type' ).val(),
								user_name: wrapper.find( '.mvr-user-name' ).val(),
								user_email: wrapper.find( '.mvr-user-email' ).val(),
								password: wrapper.find( '.mvr-user-password' ).val(),
								confirm_password: wrapper.find( '.mvr-user-confirm-password' ).val(),
							},
							success: function (response) {
								if (response.success) {
									window.location = formatUrl( response.data.redirect );
								} else {
									window.alert( response.data.error );
								}
							},
							complete: function () {
								unblock( $this.closest( '.wc-backbone-modal-content' ) );
							}
						}
					);
				}
			}
		};

		var addCommission = {
			init() {
				$( document ).on( 'click', 'body.' + mvr_admin_params.wc_screen_id + '_page_mvr_commission a.page-title-action', this.render );
				$( document.body ).on( 'wc_backbone_modal_loaded', this.backbone.init );
			},

			render( e ) {
				e.preventDefault();

				$( this ).WCBackboneModal(
					{
						template: 'mvr-modal-add-commission'
					}
				);

			return false;
			},

			backbone: {
				init( e, target ) {
					if ('mvr-modal-add-commission' === target) {
						$( document.body ).trigger( 'mvr-enhanced-init' );

						$( '.mvr-add-commission-wrapper' ).on( 'input change', '.mvr-required-field', addCommission.backbone.validateRequiredField );

						$( '.mvr-add-commission-wrapper' ).on( 'change', '.mvr-commission-selection-type', addCommission.backbone.commissionSelectionType )
						.find( '.mvr-commission-selection-type' ).change();

						$( '.mvr-add-commission-wrapper' ).on( 'input change', '.mvr-commission-order-id', addCommission.backbone.changeOrderID );
						$( '.mvr-add-commission-wrapper' ).on( 'click', '.mvr-check-order-commission', addCommission.backbone.checkOrderCommission );
						$( '.mvr-add-commission-wrapper' ).on( 'click', '.mvr-add-commission', addCommission.backbone.add );

					}
				},

				validateRequiredField() {
					var display = false,
					wrapper     = $( '.mvr-add-commission-wrapper' );

					if ('2' === wrapper.find( '.mvr-commission-selection-type' ).val()) {

						wrapper.find( '.mvr-required-field' ).filter( ':visible' ).each(
							function () {
								if ('' === $( this ).val() || null === $( this ).val()) {
									display = false;
									return false;
								} else {
									display = true;
								}
							}
						);

						if (true === display) {
							wrapper.find( '.mvr-add-commission' ).show();
						} else {
							wrapper.find( '.mvr-add-commission' ).hide();
						}
					} else {
						if ('no' === wrapper.find( '.mvr-is-available-commission' ).val()) {
							wrapper.find( '.mvr-add-commission' ).hide();
						} else {
							wrapper.find( '.mvr-add-commission' ).show();
						}
					}
				},

				commissionSelectionType( e ) {
					e.preventDefault();

					var $this = $( e.currentTarget ),
					wrapper   = $this.closest( '.mvr-add-commission-wrapper' );

					wrapper.find( '.mvr-error' ).html( '' );

					$( '.mvr-add-commission-wrapper' ).find( 'p.mvr-add-commission-fields' ).hide();

					if ('2' === $( this ).val()) {
						$( '.mvr-add-commission-wrapper' ).find( 'p.mvr-manual-commission-field' ).show();
					} else {
						$( '.mvr-add-commission-wrapper' ).find( 'p.mvr-existing-order-field' ).show();
					}

					addCommission.backbone.validateRequiredField();
				},

				changeOrderID( e ) {
					e.preventDefault();

					var $this = $( e.currentTarget ),
					wrapper   = $this.closest( '.mvr-add-commission-wrapper' );

					wrapper.find( '.mvr-error' ).html( '' );
					wrapper.find( '.mvr-is-available-commission' ).val( 'no' );
					addCommission.backbone.validateRequiredField();
				},

				checkOrderCommission( e ) {
					e.preventDefault();

					var $this = $( e.currentTarget ),
					wrapper   = $this.closest( '.mvr-add-commission-wrapper' ),
					order_id  = parseInt( $this.closest( '.mvr-add-commission-wrapper' ).find( '.mvr-commission-order-id' ).val() );

					$.ajax(
						{
							type: 'POST',
							url: ajaxurl,
							dataType: 'json',
							data: {
								action: 'mvr_check_order_commission',
								security: mvr_admin_params.add_commission_nonce,
								order_id: order_id,
							},
							success: function (response) {
								if (response.success) {
									wrapper.find( '.mvr-error' ).html( '' );

									let vendorIds = response.data.vendor_ids,
									commissionIds = response.data.commission_ids;

									if ( ! $.isArray( vendorIds )) {
										wrapper.find( '.mvr-error' ).html( mvr_admin_params.no_vendor_order_msg );
									} else {
										if ($.isArray( commissionIds )) {
											wrapper.find( '.mvr-error' ).html( mvr_admin_params.commission_already_has_msg );
										}

										wrapper.find( '.mvr-is-available-commission' ).val( 'yes' );
									}
								} else {
									window.alert( response.data.error );
									wrapper.find( '.mvr-is-available-commission' ).val( 'no' );
								}

								addCommission.backbone.validateRequiredField();
							},
							complete: function () {
								unblock( $this.closest( '.wc-backbone-modal-content' ) );
							}
						}
					);
				},

				add( e ) {
					e.preventDefault();

					var $this       = $( e.currentTarget ),
					wrapper         = $this.closest( '.mvr-add-commission-wrapper' ),
					order_id        = parseInt( $this.closest( '.mvr-add-commission-wrapper' ).find( '.mvr-commission-order-id' ).val() ),
					commission_from = $this.closest( '.mvr-add-commission-wrapper' ).find( '.mvr-commission-selection-type' ).val();

					block( $this.closest( '.wc-backbone-modal-content' ) );

					$.ajax(
						{
							type: 'POST',
							url: ajaxurl,
							dataType: 'json',
							data: {
								action: 'mvr_add_commission',
								security: mvr_admin_params.add_commission_nonce,
								order_id: order_id,
								commission_from: commission_from,
								vendor_id: wrapper.find( '.mvr-commission-vendor-id' ).val(),
								amount: wrapper.find( '.mvr-commission-amount' ).val(),
								status: wrapper.find( '.mvr-commission-status' ).val(),
								source_id: wrapper.find( '.mvr-commission-source-id' ).val(),
								source_from: wrapper.find( '.mvr-commission-source-from' ).val(),
							},
							success: function (response) {
								if (response.success) {
									window.alert( mvr_admin_params.commission_created_msg );
									window.location = formatUrl( response.data.redirect );
								} else {
									window.alert( response.data.error );
								}
							},
							complete: function () {
								unblock( $this.closest( '.wc-backbone-modal-content' ) );
							}
						}
					);
				}
			}
		};

		var addWithdraw = {
			init() {
				$( document ).on( 'click', 'body.' + mvr_admin_params.wc_screen_id + '_page_mvr_withdraw a.mvr-admin-add-withdraw', this.render );
				$( document.body ).on( 'wc_backbone_modal_loaded', this.backbone.init );
			},

			render( e ) {
				e.preventDefault();

				$( this ).WCBackboneModal(
					{
						template: 'mvr-modal-add-withdraw'
					}
				);

			return false;
			},

			backbone: {
				init( e, target ) {
					if ('mvr-modal-add-withdraw' === target) {
						$( document.body ).trigger( 'wc-enhanced-select-init' );

						$( '.mvr-add-withdraw-wrapper' ).on( 'input change', '.mvr-required-field', addWithdraw.backbone.validateRequiredField );
						$( '.mvr-add-withdraw-wrapper' ).on( 'input change', '.mvr-withdraw-amount', addWithdraw.backbone.checkWithdrawAmount );
						$( '.mvr-add-withdraw-wrapper' ).on( 'change', '.mvr-withdraw-vendor-id', addWithdraw.backbone.getAvailableWithdrawAmount );
						$( '.mvr-add-withdraw-wrapper' ).on( 'click', '.mvr-add-withdraw', addWithdraw.backbone.add );
					}
				},

				validateRequiredField() {
					var display = false,
					wrapper     = $( '.mvr-add-withdraw-wrapper' );

					wrapper.find( '.mvr-required-field' ).filter( ':visible' ).each(
						function () {
							if ('' === $( this ).val() || null === $( this ).val()) {
								display = false;
							} else {
								display = true;
							}
						}
					);

				if (0 === parseFloat( wrapper.find( '.mvr-withdraw-amount' ).val() )) {
					display = false;
				}

				if ('' === wrapper.find( '.mvr-withdraw-vendor-id' ).val() || null === wrapper.find( '.mvr-withdraw-vendor-id' ).val()) {
					display = false;
				}

				if (true === display) {
					wrapper.find( '.mvr-add-withdraw' ).show();
				} else {
					wrapper.find( '.mvr-add-withdraw' ).hide();
				}
				},

				getAvailableWithdrawAmount( e ) {
					var $this  = $( e.currentTarget ),
					wrapper    = $this.closest( '.mvr-add-withdraw-wrapper' ),
					$vendor_id = wrapper.find( '.mvr-withdraw-vendor-id' ).val();

					if ('' === $vendor_id) {
						wrapper.find( '.mvr-available-withdraw-amount' ).val( 0 );
						wrapper.find( '.mvr-available-withdraw-amount-desc' ).html( '' );
						return false;
					}

					$.ajax(
						{
							type: 'POST',
							url: ajaxurl,
							dataType: 'json',
							data: {
								action: 'mvr_get_vendor_withdraw_amount',
								security: mvr_admin_params.add_withdraw_nonce,
								vendor_id: $vendor_id,
							},
							success: function (response) {
								if (response.success) {
									wrapper.find( '.mvr-available-withdraw-amount' ).val( response.data.amount );
									wrapper.find( '.mvr-available-withdraw-amount-desc' ).html( response.data.html );
								} else {
									wrapper.find( '.mvr-available-withdraw-amount' ).val( 0 );
									wrapper.find( '.mvr-available-withdraw-amount-desc' ).html( '' );
									window.alert( response.data.error );
								}
							},
							complete: function () {
								unblock( $this.closest( '.wc-backbone-modal-content' ) );
							}
						}
					);
				},

				checkWithdrawAmount( e ) {
					e.preventDefault();

					var $this       = $( e.currentTarget ),
					wrapper         = $this.closest( '.mvr-add-withdraw-wrapper' ),
					availableAmount = wrapper.find( '.mvr-available-withdraw-amount' ).val(),
					chargeAmount    = parseFloat( mvr_admin_params.withdraw_charge ),
					withdrawAmount  = $( this ).val(),
					withdrawDesc    = $( this ).closest( 'p' ).find( 'span.mvr-amount-desc' );

					withdrawDesc.html( '' );

					if (mvr_admin_params.min_withdraw > $( $this ).val()) {
						withdrawDesc.html( mvr_admin_params.min_withdraw_msg );
						wrapper.find( '.mvr-add-withdraw' ).hide();
					} else if (availableAmount < parseFloat( $( $this ).val() )) {
						withdrawDesc.html( mvr_admin_params.max_withdraw_msg );
						wrapper.find( '.mvr-add-withdraw' ).hide();
					} else if (parseFloat( $( this ).val() ) <= parseFloat( 0 )) {
						withdrawDesc.html( '<span class="mvr-error-tip">' + mvr_admin_params.valid_amount_msg + '</span>' );
						wrapper.find( '.mvr-add-withdraw' ).hide();
					} else {
						if ('yes' === mvr_admin_params.enable_withdraw_charge) {
							if ('2' === mvr_admin_params.withdraw_charge_type) {
								chargeAmount = withdrawAmount * (mvr_admin_params.withdraw_charge / 100);
							} else {
								chargeAmount = mvr_admin_params.withdraw_charge;
							}

							withdrawAmount = withdrawAmount - chargeAmount;

							withdrawDesc.html( '<span class="mvr-withdraw-amount-disp">' + mvr_admin_params.withdraw_amount_label + wc_price_format( withdrawAmount ) + '</span><br/><span class="mvr-withdraw-charge-disp">' + mvr_admin_params.withdraw_charge_label + wc_price_format( chargeAmount ) + '</span>' )
						}

						addWithdraw.backbone.validateRequiredField();
					}
				},

				add( e ) {
					e.preventDefault();

					var $this  = $( e.currentTarget ),
					wrapper    = $this.closest( '.mvr-add-withdraw-wrapper' ),
					$vendor_id = parseInt( wrapper.find( 'select[name="_vendor_id"]' ).val() );

					block( $this.closest( '.wc-backbone-modal-content' ) );

					$.ajax(
						{
							type: 'POST',
							url: ajaxurl,
							dataType: 'json',
							data: {
								action: 'mvr_add_withdraw',
								security: mvr_admin_params.add_withdraw_nonce,
								vendor_id: $vendor_id,
								amount: wrapper.find( '.mvr-withdraw-amount' ).val(),
								status: wrapper.find( '.mvr-withdraw-status' ).val(),
							},
							success: function (response) {
								if (response.success) {
									window.location = formatUrl( response.data.redirect );
								} else {
									window.alert( response.data.error );
								}
							},
							complete: function () {
								unblock( $this.closest( '.wc-backbone-modal-content' ) );
							}
						}
					);
				}
			}
		};

		MVR_Admin.init();
		vendorNotes.init();
		payoutBatchNotes.init();
		SPMV.init();
		addVendor.init();
		payVendor.init();
		addStaff.init();
		addCommission.init();
		addWithdraw.init();
	}
);
