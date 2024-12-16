;/*global ajaxurl, mvr_generate_payout_params */ (function ($, window) {

	'use strict';

	if (typeof mvr_generate_payout_params === 'undefined') {
		return false;
	}

	/**
	 * Generate Payout handles the export process.
	 */
	var generatePayoutForm = function ($form) {
		this.$form = $form;
		this.xhr   = false;

		// Initial state.
		this.$form.find( '.mvr-payout-progress' ).val( 0 );

		// Methods.
		this.processStep = this.processStep.bind( this );

		// Events.
		$form.on( 'submit', { generatePayoutForm: this }, this.onSubmit );
	};

	/**
	 * Handle export form submission.
	 */
	generatePayoutForm.prototype.onSubmit = function (e) {
		e.preventDefault();

		var currentDate = new Date(),
			day         = currentDate.getDate(),
			month       = currentDate.getMonth() + 1,
			year        = currentDate.getFullYear(),
			timestamp   = currentDate.getTime();

		e.data.generatePayoutForm.$form.addClass( 'woocommerce-exporter__exporting' );
		e.data.generatePayoutForm.$form.find( '.woocommerce-exporter-progress' ).val( 0 );
		e.data.generatePayoutForm.$form.find( '.woocommerce-exporter-button' ).prop( 'disabled', true );
		e.data.generatePayoutForm.processStep( 1, $( this ).serialize() );
	};

	/**
	 * Process the current export step.
	 */
	generatePayoutForm.prototype.processStep = function (step, data) {
		var $this                = this,
			export_payment_types = $( '.mvr-payout-payment-types' ).val(),
			payout_vendors       = $( '.mvr-payout-vendors' ).val();

		$.ajax(
			{
				type: 'POST',
				url: ajaxurl,
				data: {
					form: data,
					action: 'mvr_generate_vendor_payout',
					step: step,
					payout_payment_types: export_payment_types,
					payout_vendors: payout_vendors,
					security: mvr_generate_payout_params.payout_nonce
				},
				dataType: 'json',
				success: function (response) {
					if (response.success) {
						if ('done' === response.data.step) {
							$this.$form.find( '.woocommerce-exporter-progress' ).val( response.data.percentage );
							window.location = response.data.url;
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
	 * Function to call generatePayoutForm on jquery selector.
	 */
	$.fn.mvr_generate_payout_form = function () {
		new generatePayoutForm( this );
		return this;
	};

	$( '.mvr-payout' ).mvr_generate_payout_form();

})( jQuery, window );
