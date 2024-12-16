;/*global ajaxurl, mvr_generate_payout_params */ (function ($, window) {

	'use strict';

	if (typeof mvr_generate_withdraw_payout_params === 'undefined') {
		return false;
	}

	var MVR_Withdraw_Payout = {
		init() {
			$( document ).on( 'change', '.mvr-payout-vendor-type', this.changeVendorType )
				.find( '.mvr-payout-vendor-type' ).change();
			$( document ).on( 'change', '.mvr-payout-payment-type', this.changePaymentType )
				.find( '.mvr-payout-payment-type' ).change();

		},

		changeVendorType( e ) {
			e.preventDefault();

			let wrapTable = $( this ).closest( '.mvr-withdraw-payout-options' );

			wrapTable.find( '.mvr-payout-vendor-selection' ).closest( 'tr' ).hide();

			if ('2' === $( this ).val()) {
				wrapTable.find( '.mvr-payout-include-vendor' ).closest( 'tr' ).show();
			} else if ('3' === $( this ).val()) {
				wrapTable.find( '.mvr-payout-exclude-vendor' ).closest( 'tr' ).show();
			}
		},

		changePaymentType( e ) {
			e.preventDefault();

			let wrapTable = $( this ).closest( '.mvr-withdraw-payout-options' );

			if ('2' === $( this ).val()) {
				wrapTable.find( '.mvr-payout-status' ).closest( 'tr' ).hide();
			} else {
				wrapTable.find( '.mvr-payout-status' ).closest( 'tr' ).show();
			}
		},
	}

	MVR_Withdraw_Payout.init();

	/**
	 * Generate Payout handles the export process.
	 */
	var generateWithdrawPayoutForm = function ($form) {
		this.$form = $form;
		this.xhr   = false;

		// Initial state.
		this.$form.find( '.mvr-payout-progress' ).val( 0 );

		// Methods.
		this.processStep = this.processStep.bind( this );

		// Events.
		$form.on( 'submit', { generateWithdrawPayoutForm: this }, this.onSubmit );
	};

	/**
	 * Handle export form submission.
	 */
	generateWithdrawPayoutForm.prototype.onSubmit = function (e) {
		e.preventDefault();

		var currentDate = new Date(),
			day         = currentDate.getDate(),
			month       = currentDate.getMonth() + 1,
			year        = currentDate.getFullYear(),
			timestamp   = currentDate.getTime();

		e.data.generateWithdrawPayoutForm.$form.addClass( 'woocommerce-exporter__exporting' );
		e.data.generateWithdrawPayoutForm.$form.find( '.woocommerce-exporter-progress' ).val( 0 );
		e.data.generateWithdrawPayoutForm.$form.find( '.woocommerce-exporter-button' ).prop( 'disabled', true );
		e.data.generateWithdrawPayoutForm.processStep( 1, $( this ).serialize() );
	};

	/**
	 * Process the current export step.
	 */
	generateWithdrawPayoutForm.prototype.processStep = function (step, data) {
		var $this           = this,
			vendorSelection = $( '.mvr-payout-vendor-type' ).val(),
			selectedVendors = $( '.mvr-payout-include-vendor' ).val(),
			excludedVendors = $( '.mvr-payout-exclude-vendor' ).val(),
			paymentType     = $( '.mvr-payout-payment-type' ).val(),
			payoutStatus    = $( '.mvr-payout-status' ).val(),
			fromDate        = $( '.mvr-payout-from-date' ).val(),
			toDate          = $( '.mvr-payout-to-date' ).val();

		$.ajax(
			{
				type: 'POST',
				url: ajaxurl,
				data: {
					form: data,
					action: 'mvr_withdraw_payout_generate',
					step: step,
					vendor_selection: vendorSelection,
					selected_vendors: selectedVendors,
					excluded_vendors: excludedVendors,
					payment_type: paymentType,
					payout_status: payoutStatus,
					from_date: fromDate,
					to_date: toDate,
					security: mvr_generate_withdraw_payout_params.payout_nonce
				},
				dataType: 'json',
				success: function (response) {
					if (response.success) {
						if ('done' === response.data.step) {
							$this.$form.find( '.woocommerce-exporter-progress' ).val( response.data.percentage );
							alert( mvr_generate_withdraw_payout_params.success_message );
							window.location = mvr_generate_withdraw_payout_params.withdraw_page_url;
							setTimeout(
								function () {
									$this.$form.removeClass( 'woocommerce-exporter__exporting' );
									$this.$form.find( '.woocommerce-exporter-button' ).prop( 'disabled', false );
								},
								2000
							);
						} else {
							$this.$form.find( '.woocommerce-exporter-progress' ).val( response.data.percentage );
							$this.processStep( parseInt( response.data.step, 10 ), data, response.data.columns, filename );
						}
					}
				}
			}
		).fail(
			function (response) {
				window.console.log( response );
			}
		);
	};

	/**
	 * Function to call generateWithdrawPayoutForm on jquery selector.
	 */
	$.fn.mvr_generate_withdraw_payout_form = function () {
		new generateWithdrawPayoutForm( this );
		return this;
	};

	$( '.mvr-withdraw-payout' ).mvr_generate_withdraw_payout_form();

})( jQuery, window );
