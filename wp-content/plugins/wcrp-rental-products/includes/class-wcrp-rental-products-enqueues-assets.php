<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Enqueues_Assets' ) ) {

	class WCRP_Rental_Products_Enqueues_Assets {

		public static function css_admin() {

			wp_enqueue_style(
				'wcrp-rental-products-admin',
				plugins_url( 'assets/css/admin.min.css', __DIR__ ),
				array(),
				WCRP_RENTAL_PRODUCTS_VERSION,
				'all'
			);

		}

		public static function css_public() {

			wp_enqueue_style(
				'wcrp-rental-products-public',
				plugins_url( 'assets/css/public.min.css', __DIR__ ),
				array(),
				WCRP_RENTAL_PRODUCTS_VERSION,
				'all'
			);

		}

		public static function js_admin() {

			wp_enqueue_script(
				'wcrp-rental-products-admin',
				plugins_url( 'assets/js/admin.min.js', __DIR__ ),
				array(
					'jquery',
					'wp-i18n',
				),
				WCRP_RENTAL_PRODUCTS_VERSION,
				true
			);

			wp_set_script_translations(
				'wcrp-rental-products-admin',
				'wcrp-rental-products',
				plugin_dir_path( __DIR__ ) . 'languages'
			);

		}

		public static function js_public_availability_checker_ajax() {

			wp_enqueue_script(
				'wcrp-rental-products-public-availability-checker-ajax',
				plugins_url( 'assets/js/public-availability-checker-ajax.min.js', __DIR__ ),
				array(
					'jquery',
					'wp-i18n',
				),
				WCRP_RENTAL_PRODUCTS_VERSION,
				true
			);

			wp_set_script_translations(
				'wcrp-rental-products-public-availability-checker-ajax',
				'wcrp-rental-products',
				plugin_dir_path( __DIR__ ) . 'languages'
			);

			wp_localize_script(
				'wcrp-rental-products-public-availability-checker-ajax',
				'availabilityCheckerAjaxVariables',
				array(
					'ajax_url'	=> admin_url( 'admin-ajax.php' ),
					'nonce'		=> wp_create_nonce( 'wcrp_rental_products_availability_checker_ajax' ),
				)
			);

		}

		// Libraries

		public static function datatables() {

			wp_enqueue_script(
				'wcrp-rental-products-datatables',
				plugins_url( 'libraries/DataTables/datatables.min.js', __DIR__ ),
				array( 'jquery' ),
				WCRP_RENTAL_PRODUCTS_VERSION,
				true
			);

			add_action( 'admin_footer', function() {

				// Below we use $_GET['tab'] to determine which tab of the rentals dashboard it is or set it to the default tab if empty, the DataTables library is solely enqueued on the rentals dashboard currently, in future if DataTables is enqueued elsewhere to the rentals dashboard the below will need a wrapping condition to check for the rentals dashboard page

				if ( isset( $_GET['tab'] ) ) {

					$tab = sanitize_text_field( $_GET['tab'] );

				} else {

					$tab = get_option( 'wcrp_rental_products_rentals_dashboard_default_tab' );

				}

				?>

				<script>
					jQuery( document ).ready( function( $ ) {

						$( '.wcrp-rental-products-datatable' ).DataTable({
							<?php if ( 'summary' == $tab ) { ?>
								'dom': '<"top"fpB>rt<"bottom"fpB>',
								'ordering': false,
								'pageLength': <?php echo esc_html( WCRP_Rental_Products_Rentals_Summary::orders_limit() ); ?>,
							<?php } elseif ( 'inventory' == $tab ) { ?>
								'dom': '<"top"fiplB>rt<"bottom"fiplB>',
								'pageLength': 25,
							<?php } ?>
							'order': [], <?php // Initital order is as per the results from PHP ?>
							'drawCallback': function() {
								$( '.dataTables_paginate a' ).addClass( 'button' );
								$( '.dataTables_paginate a.current' ).addClass( 'button-primary' );
								$( '.dataTables_paginate .ellipsis' ).addClass( 'button' ).attr( 'disabled', true );
							},
							'buttons': [
								{
									'extend': 'print',
									'text': "<?php esc_html_e( 'Print', 'wcrp-rental-products' ); ?>",
									'className': 'button',
									'exportOptions': {
										'columns': ':visible'
									}
								},
								{
									'extend': 'csvHtml5',
									'text': "<?php esc_html_e( 'Export', 'wcrp-rental-products' ); ?>",
									'className': 'button',
									'exportOptions': {
										'columns': ':visible'
									}
								}
							],
							<?php if ( 'inventory' == $tab ) { ?>
								'columnDefs': [
									<?php // Remember hidden columns when setting targets ?>
									{
										'targets': [ 0 ],
										'render': function ( data, type, row, meta ) {

											if ( type === 'display' ) {

												if ( row[ 2 ] == '0' ) {

													var postId = row[ 1 ];

												} else {

													var postId = row[ 2 ];

												}

												data = '<a href="post.php?post=' + encodeURIComponent( postId ) + '&action=edit" target="_blank">' + data + '</a>';

											}

											return data;

										},
									},
									{
										'targets': [ 2 ], <?php // Parent id (hidden) ?>
										'visible': false,
										'searchable': false,
									},
									{
										'targets': [ 4, 5 ], <?php // Ensures sorting of rental stock total and rental stock in columns work correctly due to unlimited text use with the numbers, rental stock out does not matter because it is always a number and therefore is not targeted here ?>
										'render': function ( data, type, row ) {

											if ( type === 'type' || type === 'sort' ) {

												if ( data === "<?php esc_html_e( 'Unlimited', 'wcrp-rental-products' ); ?>" ) {

													return <?php echo esc_html( PHP_INT_MAX ); ?>; <?php // Highest number ?>

												}

												return data; <?php // Numeric value ?>

											}

											return data; <?php // All other types ?>

										}
									},
									{
										'targets': [ 7 ],
										'render': function ( data, type, row, meta ) {

											if ( type === 'display' ) {

												dataSplit = data.split('\n').filter( element => element ); <?php // Split by new lines and filter removes empty (if nothing to split dataSplit would have an empty item, meaning dataSplit.length below wouldn't be usable, as an empty would be the same as a singular order id) ?>

												data = '<div class="wcrp-rental-products-rentals-inventory-rental-stock-out-orders">';

												if ( dataSplit.length > 0 ) {

													for ( var i = 0; i < dataSplit.length; i++ ) {

														data += '<a href="post.php?post=' + encodeURIComponent( dataSplit[i].replace( '#', '' ) ) + '&action=edit" target="_blank">' + dataSplit[i] + '</a><br>'; <?php // Line break because if comma separated or similar the table expands in width ?>

													}

												} else {

													data += "<?php esc_html_e( '—', 'wcrp-rental-products' ); ?>";

												}

												data += '</div>';

											}

											return data;

										},
									},
								],
							<?php } ?>
							'language': {
								'decimal': 			'',
								'emptyTable':		"<?php esc_html_e( 'No data available', 'wcrp-rental-products' ); ?>",
								<?php // translators: %1$s: start, %2$s: end, %3$s: total ?>
								'info':				"<?php echo sprintf( esc_html__( 'Showing %1$s to %2$s of %3$s', 'wcrp-rental-products' ), '_START_', '_END_', '_TOTAL_' ); ?>",
								'infoEmpty':		"<?php esc_html_e( 'Showing 0 to 0 of 0', 'wcrp-rental-products' ); ?>",
								<?php // translators: %s: total ?>
								'infoFiltered':		"<?php echo sprintf( esc_html__( '(filtered from %s)', 'wcrp-rental-products' ), '_MAX_' ); ?>",
								'infoPostFix':		'',
								'thousands':		'',
								<?php // translators: %s: select field ?>
								'lengthMenu':		"<?php echo sprintf( esc_html__( 'Show %s', 'wcrp-rental-products' ), '_MENU_' ); ?>",
								'loadingRecords':	"<?php esc_html_e( 'Loading...', 'wcrp-rental-products' ); ?>",
								'processing':		"<?php esc_html_e( 'Processing...', 'wcrp-rental-products' ); ?>",
								'search':			"<?php esc_html_e( 'Search', 'wcrp-rental-products' ); ?>",
								'zeroRecords':		"<?php esc_html_e( 'No results found.', 'wcrp-rental-products' ); ?>",
								'paginate': {
									'first':	"<?php esc_html_e( 'First', 'wcrp-rental-products' ); ?>",
									'last':		"<?php esc_html_e( 'Last', 'wcrp-rental-products' ); ?>",
									'next':		"<?php esc_html_e( 'Next', 'wcrp-rental-products' ); ?>",
									'previous': "<?php esc_html_e( 'Previous', 'wcrp-rental-products' ); ?>"
								},
								'aria': {
									'sortAscending':	"<?php esc_html_e( ':', 'wcrp-rental-products' ); ?> <?php esc_html_e( 'activate to sort column ascending', 'wcrp-rental-products' ); ?>",
									'sortDescending':	"<?php esc_html_e( ':', 'wcrp-rental-products' ); ?> <?php esc_html_e( 'activate to sort column descending', 'wcrp-rental-products' ); ?>"
								}
							},
						});

					});
				</script>

				<?php

			} );

		}

		public static function datepicker() {

			wp_enqueue_style(
				'jquery-ui-style',
				WC()->plugin_url() . '/assets/css/jquery-ui/jquery-ui.min.css', // Styles for datepicker are not included with only wp_enqueue_script( 'jquery-ui-datepicker' ), so we enqueue them here from WooCommerce's jQuery UI library, we don't want to include jQuery UI as an asset purely for this purpose when WooCommerce already has it, WooCommerce does already enqueue this on WooCommerce admin pages and is used, however we need to enqueue using this method as we have non-WooCommerce pages such as the rentals dashboard that do not enqueue it and the datepicker is used in the rentals dashboard so needs it
				array(),
				WC_VERSION, // Uses WC_VERSION over WCRP_RENTAL_PRODUCTS_VERSION as this is a WooCommerce asset
				'all'
			);

			wp_enqueue_script( 'jquery-ui-datepicker' );

		}

		public static function fullcalendar() {

			wp_enqueue_script(
				'wcrp-rental-products-fullcalendar',
				plugins_url( 'libraries/fullcalendar/lib/main.min.js', __DIR__ ),
				array(),
				WCRP_RENTAL_PRODUCTS_VERSION,
				true
			);

			wp_enqueue_script(
				'wcrp-rental-products-fullcalendar-locales',
				plugins_url( 'libraries/fullcalendar/lib/locales-all.min.js', __DIR__ ),
				array(),
				WCRP_RENTAL_PRODUCTS_VERSION,
				true
			);

			wp_enqueue_style(
				'wcrp-rental-products-fullcalendar',
				plugins_url( 'libraries/fullcalendar/lib/main.min.css', __DIR__ ),
				array(),
				WCRP_RENTAL_PRODUCTS_VERSION,
				'all'
			);

		}

		public static function litepicker() {

			wp_enqueue_script(
				'wcrp-rental-products-litepicker',
				plugins_url( 'libraries/litepicker/litepicker.js', __DIR__ ),
				array(),
				WCRP_RENTAL_PRODUCTS_VERSION,
				true
			);

			wp_enqueue_script(
				'wcrp-rental-products-litepicker-mobile-friendly',
				plugins_url( 'libraries/litepicker/plugins/mobilefriendly.js', __DIR__ ),
				array(),
				WCRP_RENTAL_PRODUCTS_VERSION,
				true
			);

			$rental_form_calendar_custom_styling = get_option( 'wcrp_rental_products_rental_form_calendar_custom_styling' );
			$rental_form_calendar_custom_styling_code = get_option( 'wcrp_rental_products_rental_form_calendar_custom_styling_code' );

			if ( 'yes' == $rental_form_calendar_custom_styling && !empty( $rental_form_calendar_custom_styling_code ) ) {

				add_action( 'wp_footer', function() use ( $rental_form_calendar_custom_styling_code ) {
					?>

					<style>
						:root {
							<?php echo esc_html( $rental_form_calendar_custom_styling_code ); ?>
						}
					</style>

					<?php

				} );

			}

		}

		public static function select2() {

			wp_enqueue_script(
				'wcrp-rental-products-select2',
				plugins_url( 'libraries/select2/dist/js/select2.min.js', __DIR__ ),
				array( 'jquery' ),
				WCRP_RENTAL_PRODUCTS_VERSION,
				true
			);

			wp_enqueue_style(
				'wcrp-rental-products-select2',
				plugins_url( 'libraries/select2/dist/css/select2.min.css', __DIR__ ),
				array(),
				WCRP_RENTAL_PRODUCTS_VERSION,
				'all'
			);

			add_action( 'admin_footer', function() {

				?>

				<script>
					jQuery( document ).ready( function( $ ) {

						const { __, _x, _n, _nx, sprintf } = wp.i18n;

						$( '.wcrp-rental-products-select2' ).select2({ <?php // Not an ID, even if only 1 field, as the field may require a different ID than this Select2 AJAX specific ID, or if not it might in future ?>
							'width': '100%',
						});

						$( '.wcrp-rental-products-select2-ajax-rentals-tools-clone-rental-product-options-from' ).select2({ <?php // Not an ID, even if only 1 field, as the field may require a different ID than this Select2 AJAX specific ID, or if not it might in future ?>
							'ajax': {
								'cache': true,
								'data': function ( params ) {
									return {
										'action': 'wcrp_rental_products_select2_ajax_rentals_tools_clone_rental_product_options_from',
										'search_term': params.term
									};
								},
								'dataType': 'json',
								'delay': 250,
								'processResults': function ( response ) {
									return {
										'results': response,
									};
								},
								'url': ajaxurl, <?php // ajaxurl is the WordPress AJAX url variable included in dashboard ?>
							},
							'language': {
								'searching': function() {
									return __( 'Getting rental products...', 'wcrp-rental-products' );
								}
							},
							'width': '100%',
						});

						$( '.wcrp-rental-products-select2-ajax-rentals-tools-clone-rental-product-options-to-categories-select' ).select2({ <?php // Not an ID, even if only 1 field, as the field may require a different ID than this Select2 AJAX specific ID, or if not it might in future ?>
							'ajax': {
								'cache': true,
								'data': function ( params ) {
									return {
										'action': 'wcrp_rental_products_select2_ajax_rentals_tools_clone_rental_product_options_to_categories_select',
										'search_term': params.term
									};
								},
								'dataType': 'json',
								'delay': 250,
								'processResults': function ( response ) {
									return {
										'results': response,
									};
								},
								'url': ajaxurl, <?php // ajaxurl is the WordPress AJAX url variable included in dashboard ?>
							},
							'language': {
								'searching': function() {
									return __( 'Getting categories...', 'wcrp-rental-products' );
								}
							},
							'width': '100%',
						});

						$( '.wcrp-rental-products-select2-ajax-rentals-tools-clone-rental-product-options-to-products-select' ).select2({ <?php // Not an ID, even if only 1 field, as the field may require a different ID than this Select2 AJAX specific ID, or if not it might in future ?>
							'ajax': {
								'cache': true,
								'data': function ( params ) {
									return {
										'action': 'wcrp_rental_products_select2_ajax_rentals_tools_clone_rental_product_options_to_products_select',
										'search_term': params.term
									};
								},
								'dataType': 'json',
								'delay': 250,
								'processResults': function ( response ) {
									return {
										'results': response,
									};
								},
								'url': ajaxurl, <?php // ajaxurl is the WordPress AJAX url variable included in dashboard ?>
							},
							'language': {
								'searching': function() {
									return __( 'Getting products...', 'wcrp-rental-products' );
								}
							},
							'width': '100%',
						});

					});
				</script>

				<?php

			} );

		}

		public static function thickbox() {

			add_thickbox();

		}

	}

}
