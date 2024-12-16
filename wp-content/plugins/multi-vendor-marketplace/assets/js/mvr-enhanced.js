/* global mvr_enhanced_params */

jQuery(
	function ($) {
		'use strict';

		function mvr_get_enhanced_select_format_string() {
			return {
				'language': {
					errorLoading: function () {
						return mvr_enhanced_params.i18n_searching;
					},
					inputTooLong: function (args) {
						var overChars = args.input.length - args.maximum;

						if (1 === overChars) {
							return mvr_enhanced_params.i18n_input_too_long_1;
						}

						return mvr_enhanced_params.i18n_input_too_long_n.replace( '%qty%', overChars );
					},
					inputTooShort: function (args) {
						var remainingChars = args.minimum - args.input.length;

						if (1 === remainingChars) {
							return mvr_enhanced_params.i18n_input_too_short_1;
						}

						return mvr_enhanced_params.i18n_input_too_short_n.replace( '%qty%', remainingChars );
					},
					loadingMore: function () {
						return mvr_enhanced_params.i18n_load_more;
					},
					maximumSelected: function (args) {
						if (args.maximum === 1) {
							return mvr_enhanced_params.i18n_selection_too_long_1;
						}

						return mvr_enhanced_params.i18n_selection_too_long_n.replace( '%qty%', args.maximum );
					},
					noResults: function () {
						return mvr_enhanced_params.i18n_no_matches;
					},
					searching: function () {
						return mvr_enhanced_params.i18n_searching;
					}
				}
			};
		}

		try {
			$( document.body ).on(
				'mvr-enhanced-init',
				function () {
					if ($( 'select.mvr-select2' ).length) {
						// Select2 with customization
						$( 'select.mvr-select2' ).each(
							function () {
								var select2_args = {
									allowClear: $( this ).data( 'allow_clear' ) ? true : false,
									placeholder: $( this ).data( 'placeholder' ),
									minimumResultsForSearch: 10,
								};

								select2_args = $.extend( select2_args, mvr_get_enhanced_select_format_string() );
								$( this ).select2( select2_args );
							}
						);
					}
					if ($( 'select.mvr-select2-search' ).length) {
						// Multiple select with ajax search.
						$( 'select.mvr-select2-search' ).each(
							function () {
								var select2_args = {
									allowClear: $( this ).data( 'allow_clear' ) ? true : false,
									placeholder: $( this ).data( 'placeholder' ),
									minimumInputLength: $( this ).data( 'minimum_input_length' ) ? $( this ).data( 'minimum_input_length' ) : 3,
									escapeMarkup: function (m) {
										return m;
									},
									ajax: {
										type: 'GET',
										url: mvr_enhanced_params.ajaxurl,
										dataType: 'json',
										delay: 250,
										data: function (params) {
											return {
												term: params.term,
												action: $( this ).data( 'action' ) ? $( this ).data( 'action' ) : 'ppr_json_search_customers',
												exclude_global_variable: $( this ).data( 'exclude-global-variable' ) ? $( this ).data( 'exclude-global-variable' ) : 'no',
												exclude_product_type: $( this ).data( 'exclude_product_type' ) ? $( this ).data( 'exclude_product_type' ) : 'no',
												user_role_in: $( this ).data( 'user_role_in' ) ? $( this ).data( 'user_role_in' ) : '',
												exclude_out_of_stock: $( this ).data( 'exclude-out-of-stock' ) ? $( this ).data( 'exclude-out-of-stock' ) : 'no',
												mvr_product_id: $( this ).data( 'ppr-product-id' ) ? $( this ).data( 'ppr-product-id' ) : '',
												security: $( this ).data( 'nonce' ) ? $( this ).data( 'nonce' ) : mvr_enhanced_params.search_nonce,
											};
										},
										processResults: function (data) {
											var terms = [];
											if (data) {
												$.each(
													data,
													function (id, term) {
														terms.push(
															{
																id: id,
																text: term
															}
														);
													}
												);
											}
											return {
												results: terms
											};
										},
										cache: true
									}
								};

								select2_args = $.extend( select2_args, mvr_get_enhanced_select_format_string() );
								$( this ).select2( select2_args );
							}
						);
					}

					if ($( '.mvr_datepicker' ).length) {
						$( '.mvr_datepicker' ).on(
							'change',
							function () {
								if ($( this ).val() === '') {
									$( this ).prev( ".mvr_alter_datepicker_value" ).val( '' );
								}
							}
						);
						$( '.mvr_datepicker' ).each(
							function () {
								$( this ).datepicker(
									{
										altField: $( this ).prev( ".mvr_alter_datepicker_value" ),
										altFormat: 'yy-mm-dd',
										changeMonth: true,
										changeYear: true
									}
								);
							}
						);
					}

					if ($( '.mvr_datetimepicker' ).length) {
						$( '.mvr_datetimepicker' ).on(
							'change',
							function () {
								if ($( this ).val() === '') {
									$( this ).prev( ".mvr_alter_datepicker_value" ).val( '' );
								}
							}
						);
						$( '.mvr_datetimepicker' ).each(
							function () {
								$( this ).datetimepicker(
									{
										altField: $( this ).prev( ".mvr_alter_datepicker_value" ),
										altFieldTimeOnly: false,
										altFormat: 'yy-mm-dd',
										altTimeFormat: 'HH:mm',
										dateFormat: 'yy-mm-dd',
										timeFormat: 'HH:mm',
										changeMonth: true,
										changeYear: true,
										showButtonPanel: true,
										showOn: "button",
										buttonImage: mvr_enhanced_params.calendar_image,
										buttonImageOnly: true
									}
								);
							}
						);
					}

					if ($( '.colorpick' ).length) {
						// Color picker
						$( '.colorpick' )
						.iris(
							{
								change: function (event, ui) {
									$( this ).parent().find( '.colorpickpreview' ).css( { backgroundColor: ui.color.toString() } );
								},
								hide: true,
								border: true
							}
						)

							.on(
								'click focus',
								function (event) {
									event.stopPropagation();
									$( '.iris-picker' ).hide();
									$( this ).closest( 'td' ).find( '.iris-picker' ).show();
									$( this ).data( 'original-value', $( this ).val() );
								}
							)

							.on(
								'change',
								function () {
									if ($( this ).is( '.iris-error' )) {
										var original_value = $( this ).data( 'original-value' );

										if (original_value.match( /^\#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/ )) {
											$( this ).val( $( this ).data( 'original-value' ) ).change();
										} else {
											$( this ).val( '' ).change();
										}
									}
								}
							);

						$( 'body' ).on(
							'click',
							function () {
								$( '.iris-picker' ).hide();
							}
						);
					}

				}
			);
			$( document.body ).trigger( 'mvr-enhanced-init' );
		} catch (err) {
			window.console.log( err );
		}
	}
);
