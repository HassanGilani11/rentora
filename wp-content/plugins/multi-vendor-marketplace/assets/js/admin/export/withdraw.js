;/*global ajaxurl, mvr_withdraw_export_params */ (function ($, window) {

	'use strict';

	if (typeof mvr_withdraw_export_params === 'undefined') {
		return false;
	}

	/**
	 * Withdraw ExportForm handles the export process.
	 */
	var withdrawExportForm = function ($form) {
		this.$form = $form;
		this.xhr   = false;

		// Initial state.
		this.$form.find( '.mvr-exporter-progress' ).val( 0 );

		// Methods.
		this.processStep = this.processStep.bind( this );

		// Events.
		$form.on( 'submit', { withdrawExportForm: this }, this.onSubmit );
	};

	/**
	 * Handle export form submission.
	 */
	withdrawExportForm.prototype.onSubmit = function (e) {
		e.preventDefault();

		var currentDate = new Date(),
			day         = currentDate.getDate(),
			month       = currentDate.getMonth() + 1,
			year        = currentDate.getFullYear(),
			timestamp   = currentDate.getTime(),
			filename    = 'mvr-withdraw-export-' + day + '-' + month + '-' + year + '-' + timestamp + '.csv';

		e.data.withdrawExportForm.$form.addClass( 'woocommerce-exporter__exporting' );
		e.data.withdrawExportForm.$form.find( '.woocommerce-exporter-progress' ).val( 0 );
		e.data.withdrawExportForm.$form.find( '.woocommerce-exporter-button' ).prop( 'disabled', true );
		e.data.withdrawExportForm.processStep( 1, $( this ).serialize(), '', filename );
	};

	/**
	 * Process the current export step.
	 */
	withdrawExportForm.prototype.processStep = function (step, data, columns, filename) {
		var $this                = this,
			selected_columns     = $( '.mvr-exporter-columns' ).val(),
			export_payment_types = $( '.mvr-exporter-payment-types' ).val(),
			export_statuses      = $( '.mvr-exporter-statuses' ).val();

		$.ajax(
			{
				type: 'POST',
				url: ajaxurl,
				data: {
					form: data,
					action: 'mvr_withdraw_export',
					step: step,
					columns: columns,
					selected_columns: selected_columns,
					export_payment_types: export_payment_types,
					export_statuses: export_statuses,
					file_name: filename,
					security: mvr_withdraw_export_params.export_nonce
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
	 * Function to call withdrawExportForm on jquery selector.
	 */
	$.fn.mvr_withdraw_export_form = function () {
		new withdrawExportForm( this );
		return this;
	};

	$( '.mvr-exporter' ).mvr_withdraw_export_form();

})( jQuery, window );
