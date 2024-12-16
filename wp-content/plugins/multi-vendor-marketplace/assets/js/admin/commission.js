/* global mvr_commission_params */

jQuery(
	function ($) {
		'use strict';

		var commissionPreview      = {
			init: function () {
				$( document ).on( 'click', '.mvr-commission-preview', this.commissionPreview );
			},
			commissionPreview( e ) {
				e.preventDefault();
				var $previewButton = $( this ),
				$commissionId      = $previewButton.data( 'commission_id' );

				if ($previewButton.data( 'mvr-commission-data' )) {
					$( this ).WCBackboneModal(
						{
							template: 'mvr-modal-view-commission',
							variable: $previewButton.data( 'commissionData' )
						}
					);

					return false;
				} else {
					$previewButton.addClass( 'disabled' );

					$.ajax(
						{
							type: 'GET',
							url: ajaxurl,
							dataType: 'json',
							data: {
								action: 'mvr_get_commission_details',
								commission_id: $commissionId,
								security: mvr_commission_params.commission_preview_nonce
							},
							success( response ) {
								$( '.mvr-commission-preview' ).removeClass( 'disabled' );

								if (response.success) {
									$previewButton.data( 'commissionData', response.data );

									$( this ).WCBackboneModal(
										{
											template: 'mvr-modal-view-commission',
											variable: response.data
										}
									);
								}
							}
						}
					);
				}
			}
		};

		commissionPreview.init();
	}
);
