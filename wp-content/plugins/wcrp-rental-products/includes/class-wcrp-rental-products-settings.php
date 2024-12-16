<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Settings' ) ) {

	class WCRP_Rental_Products_Settings {

		public function __construct() {

			add_filter( 'woocommerce_get_sections_products', array( $this, 'section' ) );
			add_filter( 'woocommerce_get_settings_products', array( $this, 'fields' ), 10, 2 );

		}

		public function section( $sections ) {

			$sections['wcrp-rental-products'] = __( 'Rental products', 'wcrp-rental-products' );
			return $sections;

		}

		public function fields( $settings, $current_section ) {

			if ( 'wcrp-rental-products' == $current_section ) {

				global $wpdb;

				// Settings - note that the id set on the fields below (excluding of type title) becomes the meta option_name, hence why not hyphenated like usual ID naming conventions

				$thickbox = '<div id="wcrp-rental-products-settings-compatibility-issues-info">';
				// Notices included require the wcrp-rental-products-settings-compatibility-issues-info-notice class as we cannot target #wcrp-rental-products-settings-compatibility-issues-info .notice because the contents of #wcrp-rental-products-settings-compatibility-issues-info get moved to #TB_ajaxContent without the ID remaining in the markup
				$thickbox .= '<div class="wcrp-rental-products-settings-compatibility-issues-info-notice notice notice-warning inline">';
				$thickbox .= '<p><strong>' . esc_html__( 'Rental functionality issues are usually caused by a caching plugin and/or caching features enabled by your web host. Try deactivating any caching plugins and/or caching features in your web hosting.', 'wcrp-rental-products' ) . '</strong></p>';
				// translators: %1$s: cache documentation link, %2$s: plugin title
				$thickbox .= '<p>' . wp_kses_post( sprintf( __( 'If you find the issues only occur when these are active, then consider using alternative caching methods, or reconfigure any existing caching methods to not perform aggressive caching methods (e.g. full page caching), or disable the caching methods entirely. It may be possible to continue using aggressive caching methods when configured in a specific way. One general example, usually to rectify issues adding to cart, is to set any cache lifespans to 10 hours or less, see this %1$s for information, note this link is for the %2$s plugin, however the principle described applies to any caching method used.', 'wcrp-rental-products' ), '<a href="' . esc_url( 'https://docs.wp-rocket.me/article/975-nonces-and-cache-lifespan' ) . '" target="_blank">' . esc_html__( 'documentation', 'wcrp-rental-products' ) . '</a>', 'WP Rocket' ) ) . '</p>';
				$thickbox .= '</div>';
				$thickbox .= '<table class="widefat fixed striped">';
				$thickbox .= '<thead><tr><th>' . esc_html__( 'Issue', 'wcrp-rental-products' ) . '</th><th>' . esc_html__( 'Cause', 'wcrp-rental-products' ) . '</th><th style="width: 35%;">' . esc_html__( 'Fix', 'wcrp-rental-products' ) . '</th></tr></thead>'; // Width on last column has to be inline CSS as wcrp-rental-products-settings-compatibility-issues-info is not used in final markup when opened
				$thickbox .= '<tbody>';
				$thickbox .= '<tr><td>' . esc_html__( 'Availability checker dates/quantities selected are lost and/or become incorrect when browsing through different pages.', 'wcrp-rental-products' ) . '</td><td>' . esc_html__( 'The availability checker dates/quantities have been cached.', 'wcrp-rental-products' ) . '</td><td>' . esc_html__( 'Try using the availability checker AJAX mode from the availability checker settings section.', 'wcrp-rental-products' ) . '</td></tr>';
				$thickbox .= '<tr><td>' . esc_html__( 'Cart/checkout displays an error stating product has recently been updated and pricing/availability may have changed, and can not checkout until product is removed from cart.', 'wcrp-rental-products' ) . '</td><td>' . esc_html__( 'Rental product was added to cart, but since then has been updated.', 'wcrp-rental-products' ) . '</td><td>' . esc_html__( 'This is expected behaviour when the product updated restrictions setting is enabled, consider disabling this in some limited scenarios, see the related information for the product updated restrictions setting from the cart and checkout settings section.', 'wcrp-rental-products' ) . '</td></tr>';
				$thickbox .= '<tr><td>' . esc_html__( 'Rental form on product pages and/or availability checker does not work, such as the calendar does not open when clicking rental dates field.', 'wcrp-rental-products' ) . '</td><td>' . esc_html__( 'jQuery is undefined and/or general JavaScript errors. jQuery can be undefined if you have enabled any optimization/performance related settings in a plugin or your hosting, these can sometimes move where jQuery is enqueued to the bottom of the website. General JavaScript errors can also cause this issue, as after an error occurs no further JavaScript can function.', 'wcrp-rental-products' ) . '</td><td>' . esc_html__( 'Disable any optimization/performance related settings, enable settings one by one to find root cause, check website for general JavaScript errors in your browser dev tools, fix any JavaScript errors, these will likely be coming from other extensions, plugins, or your theme.', 'wcrp-rental-products' ) . '</td></tr>';
				$thickbox .= '<tr><td>' . esc_html__( 'Rental form on product pages is displayed oddly or does not fit well.', 'wcrp-rental-products' ) . '</td><td>' . esc_html__( 'CSS layout rules, likely coming from the theme/page builder.', 'wcrp-rental-products' ) . '</td><td>' . esc_html__( 'Try using a different rental form layout from the rental form settings section.', 'wcrp-rental-products' ) . '</td></tr>';
				// translators: %s: error code
				$thickbox .= '<tr><td>' . wp_kses_post( sprintf( __( 'Rental form on product pages not functioning as expected, shows error code %s.', 'wcrp-rental-products' ), 'PRODRFU0' ) ) . '</td><td>' . esc_html__( 'Nonce used during rental form update is invalid or has expired (nonce is a number generated to validate form submission). Generally occurs due to the use of aggressive caching methods where expired nonces are being cached.', 'wcrp-rental-products' ) . '</td><td>' . esc_html__( 'Review the caching information at the top of this list. Potentially try setting cache lifespan to 10 hours or less via your caching methods.', 'wcrp-rental-products' ) . '</td></tr>';
				$thickbox .= '<tr><td>' . esc_html__( 'Rental or purchase toggle does not display.', 'wcrp-rental-products' ) . '</td><td>' . esc_html__( 'Some themes/page builders are missing a core WooCommerce hook on the product page.', 'wcrp-rental-products' ) . '</td><td>' . esc_html__( 'Reinstate the missing hook or use the rental or purchase toggle shortcode. See the related information linked in the rental or purchase toggle settings section.', 'wcrp-rental-products' ) . '</td></tr>';
				// translators: %s: rental product body class
				$thickbox .= '<tr><td>' . esc_html__( 'Rental order item data missing, such as rent from/to dates.', 'wcrp-rental-products' ) . '</td><td>' . esc_html__( 'Some payment methods enable quick pay functionality, such as buy now buttons on product pages. Depending on the integration, the standard cart/checkout process may get bypassed, reesulting in missing rental data, which in turn may cause further issues.', 'wcrp-rental-products' ) . '</td><td>' . wp_kses_post( sprintf( __( 'Consider disabling the functionality entirely or alternatively disable this functionality only for rental products by hiding the quick pay methods on rental products with some custom CSS, target the element based off the rental product body class of %s.', 'wcrp-rental-products' ), '<code>wcrp-rental-products-is-rental</code>' ) ) . '</td></tr>';
				// translators: %s: error code
				$thickbox .= '<tr><td>' . wp_kses_post( sprintf( __( 'Rental products cannot be added to cart, shows error code %s.', 'wcrp-rental-products' ), 'CARTADD0' ) ) . '</td><td>' . esc_html__( 'Nonce used during add to cart is invalid or has expired (nonce is a number generated to validate form submission). Generally occurs due to the use of aggressive caching methods where expired nonces are being cached.', 'wcrp-rental-products' ) . '</td><td>' . esc_html__( 'Review the caching information at the top of this list. Potentially try setting cache lifespan to 10 hours or less via your caching methods.', 'wcrp-rental-products' ) . '</td></tr>';
				// translators: %s: error code
				$thickbox .= '<tr><td>' . wp_kses_post( sprintf( __( 'Rental products cannot be added to cart, shows error code %s.', 'wcrp-rental-products' ), 'CARTADD1' ) ) . '</td><td>' . esc_html__( 'The add to cart method used does not pass all the required rental form data to the cart.', 'wcrp-rental-products' ) . '</td><td>' . esc_html__( 'If occurs when adding to cart from a product page, the standard WooCommerce add to cart form may not be being used, ask your theme/page builder support for clarity on if you are using it, and if not how to reinstate it. If there are other means of adding rental products to cart outside the product page, it is likely your theme/page builder, extension/plugin or custom development is adding add to cart links/buttons that technically should not be there as rental products cannot be added to cart outside the product page. In this scenario discuss the issue with the party responsible to see if they can be conditionally hidden/removed if the product is a rental.', 'wcrp-rental-products' ) . '</td></tr>';
				// translators: %s: error code
				$thickbox .= '<tr><td>' . wp_kses_post( sprintf( __( 'Rental products cannot be added to cart, shows error code %s, specifically when using an AJAX add to cart method.', 'wcrp-rental-products' ), 'CARTADD1' ) ) . '</td>';
				// translators: %s: recommended link
				$thickbox .= '<td>' . esc_html__( 'The AJAX add to cart method used does not pass all the required rental form data to the cart.', 'wcrp-rental-products' ) . '</td><td>' . wp_kses_post( sprintf( __( 'Consider using %s as alternative.', 'wcrp-rental-products' ), '<a href="' . esc_url( 'https://wordpress.org/plugins/woo-ajax-add-to-cart/' ) . '" target="_blank">AJAX Add to Cart for WooCommerce</a>' ) ) . '</td></tr>';
				// translators: %s: error code
				$thickbox .= '<tr><td>' . wp_kses_post( sprintf( __( 'Rental products cannot be added to cart, shows error code %s.', 'wcrp-rental-products' ), 'CARTADD2' ) ) . '</td><td>' . esc_html__( 'Rental form data was manipulated before transit.', 'wcrp-rental-products' ) . '</td><td>' . esc_html__( 'None required, this error ensures rental form data being passed to the cart has not been manipulated.', 'wcrp-rental-products' ) . '</td></tr>';
				// translators: %s: completed order status name
				$thickbox .= '<tr><td>' . esc_html__( 'Shipping integration automatically sets orders to completed causing rentals to be marked as returned.', 'wcrp-rental-products' ) . '</td><td>' . sprintf( esc_html__( 'Some shipping integrations automatically set orders to the %s status upon dispatch.', 'wcrp-rental-products' ), strtolower( wc_get_order_status_name( 'wc-completed' ) ) ) . '</td><td>' . esc_html__( 'Consider disabling the return rentals in completed orders setting or disable the automated order status change in the shipping integration, if possible only for orders containing rentals.', 'wcrp-rental-products' ) . '</td></tr>';
				$thickbox .= '</tbody>';
				$thickbox .= '</table>';
				$thickbox .= '</div>';

				// The heading/button below used to be notice markup echoed out rather than part of the settings array but was changed because upon save the notice is duplicated and gets offset behind the menu, potentially due to how WooCommerce is trying to position its saved notice under the tabs

				$rental_products_settings[] = array(
					'name'	=> '', // No heading, just used to display the notice
					'id'	=> 'wcrp-rental-products-settings-title-compatibility-issues-notice', // ID of div not an input
					'type'	=> 'title',
					'desc'	=> '<div class="notice notice-warning inline"><p><strong>' . esc_html__( 'If you are experiencing any issues with this extension, and/or are using caching via a plugin or your host, click the button below for important information to consider.', 'wcrp-rental-products' ) . '</strong></p><p><a href="#TB_inline?&width=1000&height=650&inlineId=wcrp-rental-products-settings-compatibility-issues-info" class="button button-primary thickbox" title="' . esc_html__( 'Known compatibility issues', 'wcrp-rental-products' ) . '">' . esc_html__( 'View known compatibility issues', 'wcrp-rental-products' ) . '</a></p></div>' . wp_kses_post( $thickbox ),
				);

				$rental_products_settings[] = array(
					'type'	=> 'sectionend',
				);

				$rental_products_settings[] = array(
					'name'	=> esc_html__( 'Rental date/time formats', 'wcrp-rental-products' ),
					'id'	=> 'wcrp-rental-products-settings-title-rental-date-time-formats', // ID of div not an input
					'type'	=> 'title',
					'desc'	=> esc_html__( 'Configure the rental date/time formats.', 'wcrp-rental-products' ),
				);

				$rental_products_settings[] = array(
					'name'			=> esc_html__( 'Rental date format', 'wcrp-rental-products' ),
					'id'			=> 'wcrp_rental_products_rental_date_format',
					'type'			=> 'text',
					// translators: %s: PHP date/time formats URL
					'desc'			=> esc_html__( 'Format of rental dates used throughout the frontend of your store, emails and in most areas of this dashboard, excluding the rental form and availability checker (see rental form date format setting).', 'wcrp-rental-products' ) . '<br>' . wp_kses_post( sprintf( __( 'Use a <a href="%s" target="_blank">PHP date format</a>. Day, month and year must be included. Errors in the format used may cause issues with rental functionality.', 'wcrp-rental-products' ), 'https://www.php.net/manual/en/datetime.format.php' ) ),
					// translators: %s: default
					'placeholder'	=> sprintf( esc_html__( 'If empty defaults to %s', 'wcrp-rental-products' ), 'Y-m-d' ),
				);

				$rental_products_settings[] = array(
					'name'			=> esc_html__( 'Rental time format', 'wcrp-rental-products' ),
					'id'			=> 'wcrp_rental_products_rental_time_format',
					'type'			=> 'text',
					// translators: %s: PHP date/time formats URL
					'desc'			=> esc_html__( 'Format of rental times used throughout the frontend of your store, emails and in most areas of this dashboard.', 'wcrp-rental-products' ) . '<br>' . wp_kses_post( sprintf( __( 'Use a <a href="%s" target="_blank">PHP time format</a>. Hours and minutes must be be included, seconds should not be included. Errors in the format used may cause issues with rental functionality.', 'wcrp-rental-products' ), 'https://www.php.net/manual/en/datetime.format.php' ) ),
					// translators: %s: default
					'placeholder'	=> sprintf( esc_html__( 'If empty defaults to %s', 'wcrp-rental-products' ), 'H:i' ),
				);

				$rental_products_settings[] = array(
					'type'	=> 'sectionend',
				);

				$rental_products_settings[] = array(
					'name'	=> esc_html__( 'Rental form', 'wcrp-rental-products' ),
					'id'	=> 'wcrp-rental-products-settings-title-rental-form', // ID of div not an input
					'type'	=> 'title',
					'desc'	=> esc_html__( 'Configure the rental form shown on rental product pages.', 'wcrp-rental-products' ),
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Rental form layout', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_rental_form_layout',
					'type'		=> 'select',
					'desc'		=> esc_html__( 'Choose the layout of the rental form. The theme option displays the rental form as per your theme CSS. The theme compatibility option can be used if you find the theme option is displaying the rental form oddly, this option still uses most of your theme CSS but attempts to override some rental form elements to ensure they are displayed vertically and form field sizes are consistent. The light and dark options are similar to theme compatibility with some additional styling applied.', 'wcrp-rental-products' ),
					'options'	=> array(
						'theme'					=> esc_html__( 'Theme', 'wcrp-rental-products' ),
						'theme_compatibility'	=> esc_html__( 'Theme compatibility', 'wcrp-rental-products' ),
						'light'					=> esc_html__( 'Light', 'wcrp-rental-products' ),
						'light_boxed'			=> esc_html__( 'Light boxed', 'wcrp-rental-products' ),
						'dark'					=> esc_html__( 'Dark', 'wcrp-rental-products' ),
						'dark_boxed'			=> esc_html__( 'Dark boxed', 'wcrp-rental-products' ),
					),
				);

				$rental_products_settings[] = array(
					'name'			=> esc_html__( 'Rental form date format', 'wcrp-rental-products' ),
					'id'			=> 'wcrp_rental_products_rental_form_date_format',
					'type'			=> 'text',
					// translators: %s: allowed formats
					'desc'			=> esc_html__( 'Format of rental dates displayed when a customer selects dates using the rental form on product pages.', 'wcrp-rental-products' ) . '<br>' . sprintf( esc_html__( 'Allowed formats are %s. Day, month and year must be included, if these are not included and/or if using incorrect formats then it is likely to cause issues with rental functionality.', 'wcrp-rental-products' ), '<code>D' . esc_html__( ',', 'wcrp-rental-products' ) . ' DD' . esc_html__( ',', 'wcrp-rental-products' ) . ' M' . esc_html__( ',', 'wcrp-rental-products' ) . ' MM' . esc_html__( ',', 'wcrp-rental-products' ) . ' MMM' . esc_html__( ',', 'wcrp-rental-products' ) . ' MMMM' . esc_html__( ',', 'wcrp-rental-products' ) . ' YYYY</code>' ) . '<br>' . esc_html__( 'This setting is also used for the availability checker.', 'wcrp-rental-products' ),
					// translators: %s: default
					'placeholder'	=> sprintf( esc_html__( 'If empty defaults to %s', 'wcrp-rental-products' ), 'D MMM YYYY' ),
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Rental form first day', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_rental_form_first_day',
					'type'		=> 'select',
					'desc'		=> esc_html__( 'The first day of the week in the rental form calendar. Day selected will be the first column in the calendar.', 'wcrp-rental-products' ) . '<br>' . esc_html__( 'This setting is also used for the availability checker.', 'wcrp-rental-products' ),
					'options'	=> array(
						'1'	=> esc_html__( 'Monday', 'wcrp-rental-products' ),
						'2'	=> esc_html__( 'Tuesday', 'wcrp-rental-products' ),
						'3'	=> esc_html__( 'Wednesday', 'wcrp-rental-products' ),
						'4'	=> esc_html__( 'Thursday', 'wcrp-rental-products' ),
						'5'	=> esc_html__( 'Friday', 'wcrp-rental-products' ),
						'6'	=> esc_html__( 'Saturday', 'wcrp-rental-products' ),
						'0'	=> esc_html__( 'Sunday', 'wcrp-rental-products' ),
					),
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Rental form period selection option labels', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_rental_form_period_selection_option_labels',
					'type'		=> 'select',
					'desc'		=> esc_html__( 'Set how option labels are displayed when the product is using the period selection pricing type. If weeks used then the options display as weeks e.g. if period selections are 1, 7, 14 (entered in days) it will display these as 1 day, 1 week and 2 weeks. Day periods which do not divide into weeks remain in days.', 'wcrp-rental-products' ),
					'options'	=> array(
						'days'	=> esc_html__( 'Days', 'wcrp-rental-products' ),
						'weeks'	=> esc_html__( 'Weeks', 'wcrp-rental-products' ),
					),
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Rental form after quantity', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_rental_form_after_quantity',
					'type'		=> 'checkbox',
					'desc'		=> esc_html__( 'Enable rental form after quantity', 'wcrp-rental-products' ),
					'desc_tip'	=> esc_html__( 'Displays the rental form after the quantity field on product pages, if disabled this will display the rental form before the quantity field.', 'wcrp-rental-products' ),
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Rental form auto apply', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_rental_form_auto_apply',
					'type'		=> 'checkbox',
					'desc'		=> esc_html__( 'Enable rental form auto apply', 'wcrp-rental-products' ),
					'desc_tip'	=> esc_html__( 'Automatically applies the dates selected in the rental form calendar. If disabled then cancel and apply buttons are shown instead, the apply button must be clicked to apply the dates selected.', 'wcrp-rental-products' ) . '<br>' . esc_html__( 'If you have rentals which allow selections of both single and multiple days in the same calendar you may wish to disable this setting so a double click is not required for single day rentals in this scenario.', 'wcrp-rental-products' ) . '<br>' . esc_html__( 'This setting is also used for the availability checker.', 'wcrp-rental-products' ),
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Rental form auto select end date', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_rental_form_auto_select_end_date',
					'type'		=> 'checkbox',
					'desc'		=> esc_html__( 'Enable rental form auto select end date', 'wcrp-rental-products' ),
					'desc_tip'	=> esc_html__( 'Automatically sets the end date when a start date is selected in the rental form calendar. This will only occur on rental products which allow selection of a singular range, e.g. does not occur if pricing type is period and pricing period multiplies is enabled as multiples of the minimum range can be selected.', 'wcrp-rental-products' ),
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Rental form available rental stock totals', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_rental_form_available_rental_stock_totals',
					'type'		=> 'checkbox',
					'desc'		=> esc_html__( 'Enable available rental stock totals', 'wcrp-rental-products' ),
					'desc_tip'	=> esc_html__( 'Displays the total rental stock available for the rental dates, quantity and other options selected.', 'wcrp-rental-products' ),
				);

				$litepicker_root_variables_note = esc_html__( 'Note that in the unlikely event that you are using the Litepicker library for other non-rental calendars the styling applied will effect those too.', 'wcrp-rental-products' );

				$thickbox = '<div id="wcrp-rental-products-settings-rental-form-calendar-styling-info">';
				$thickbox .= '<p><strong>' . esc_html__( 'Using the default styling is strongly recommended, however if you wish to customize the styling, see the instructions below.', 'wcrp-rental-products' ) . '</strong></p>';
				// translators: %s: root CSS variables prefix
				$thickbox .= '<p>' . wp_kses_post( sprintf( __( 'The calendars shown in the rental form and availability checker use several root CSS variables which you can override to customize the styling, they are named with the %s prefix. You can view these variables and their values when inspecting the rental form styles using the development tools in your web browser.', 'wcrp-rental-products' ), '<code>--litepicker-</code>' ) ) . '</p>';
				$thickbox .= '<p>' . esc_html__( 'It is strongly recommended you only customize the root CSS variables and not add any additional styling rules as this may cause the calendar to operate incorrectly and/or display inconsistently.', 'wcrp-rental-products' ) . '</p>';
				$thickbox .= '<p>' . esc_html__( 'Use any of the variable names you wish to change and add these overrides to your theme stylesheet or using the additional CSS section within the theme customizer.', 'wcrp-rental-products' ) . '</p>';
				$thickbox .= '<p><strong>' . esc_html__( 'Example single rule root CSS variable override:', 'wcrp-rental-products' ) . '</strong></p>';
				$thickbox .= '<code>:root { --litepicker-is-locked-color: #ff0000 !important; }</code>';
				$thickbox .= '<p><strong>' . esc_html__( 'Example multiple rules CSS variable override:', 'wcrp-rental-products' ) . '</strong></p>';
				$thickbox .= '<code>:root { --litepicker-day-color: #000000 !important; --litepicker-day-color-hover: #333333 !important; }</code>';
				$thickbox .= '<p>' . esc_html( $litepicker_root_variables_note ) . '</p>';
				$thickbox .= '</div>';

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Rental form calendar custom styling', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_rental_form_calendar_custom_styling',
					'type'		=> 'checkbox',
					'desc'		=> esc_html__( 'Enable rental form calendar custom styling', 'wcrp-rental-products' ),
					// translators: %s: Reset to defaults/styling instructions link
					'desc_tip'	=> sprintf( esc_html__( 'Set custom styling for the rental form calendar. When enabled a further setting will appear below, change the color/pixel values only. If you wish to reset these to the defaults, %s. ', 'wcrp-rental-products' ), '<a href="#" id="wcrp-rental-products-rental-form-calendar-custom-styling-reset">' . esc_html__( 'click here', 'wcrp-rental-products' ) . '</a>' ) . '<br>' . sprintf( esc_html__( 'If you prefer to apply your own custom styling via other methods, we recommend keeping this setting disabled and use your own CSS as per these %s.', 'wcrp-rental-products' ), '<a href="#TB_inline?&width=600&height=550&inlineId=wcrp-rental-products-settings-rental-form-calendar-styling-info" class="thickbox" title="' . esc_html__( 'Rental form calendar styling', 'wcrp-rental-products' ) . '">' . esc_html__( 'instructions', 'wcrp-rental-products' ) . '</a>' ) . '<br>' . esc_html( $litepicker_root_variables_note ) . '<br>' . esc_html__( 'This setting is also used for the availability checker.', 'wcrp-rental-products' ) . wp_kses_post( $thickbox ),
					'custom_attributes'	=> array(
						'data-reset' => WCRP_Rental_Products_Product_Rental_Form::rental_form_calendar_styling_defaults(),
					),
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Rental form calendar custom styling code', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_rental_form_calendar_custom_styling_code',
					'type'		=> 'textarea',
					'css'		=> 'height: 200px; width: 100%;',
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Rental form reset button', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_rental_form_reset_button',
					'type'		=> 'checkbox',
					'desc'		=> esc_html__( 'Enable rental form reset button', 'wcrp-rental-products' ),
					'desc_tip'	=> esc_html__( 'Displays a reset button within the rental form calendar used to reset previously selected dates. Disabled by default as a new date or range is easily selectable without the need to reset first.', 'wcrp-rental-products' ) . '<br>' . esc_html__( 'This setting is also used for the availability checker.', 'wcrp-rental-products' ),
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Rental form notices use WooCommerce classes', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_rental_form_notices_use_woocommerce_classes',
					'type'		=> 'checkbox',
					'desc'		=> esc_html__( 'Enable rental form notices use WooCommerce classes', 'wcrp-rental-products' ),
					'desc_tip'	=> esc_html__( 'Notices displayed in the rental form will use WooCommerce CSS classes and therefore have the standard WooCommerce notice styling when enabled. Disable this if you want to apply your own custom CSS notice styling, or if you have issues with the theme targeting WooCommerce notices and repositioning them to another location in the page.', 'wcrp-rental-products' ),
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Rental form start/end notices', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_rental_form_start_end_notices',
					'type'		=> 'checkbox',
					'desc'		=> esc_html__( 'Enable rental form start/end notices', 'wcrp-rental-products' ),
					'desc_tip'	=> esc_html__( 'Displays a notice instructing the customer that the dates selected cannot start/end on the highlighted days in the rental form calendar if a product has disabled start/end dates/days.', 'wcrp-rental-products' ),
				);

				$rental_products_settings[] = array(
					'name'				=> esc_html__( 'Rental form maximum date days', 'wcrp-rental-products' ),
					'id'				=> 'wcrp_rental_products_rental_form_maximum_date_days',
					'type'				=> 'number',
					// translators: %s: default days
					'desc'				=> sprintf( esc_html__( 'Set the maximum amount of days from today that the rental form will allow date selection up to. If empty defaults to %s.', 'wcrp-rental-products' ), '730' ) . '<br>' . esc_html__( 'This setting is also used for the availability checker.', 'wcrp-rental-products' ),
					'css'				=> 'width: 100px;',
					'custom_attributes'	=> array(
						'min'	=> 1,
						'max'	=> 7300, // 20 years
						'step'	=> 1,
					),
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Rental form maximum date specific', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_rental_form_maximum_date_specific',
					'class'		=> 'wcrp-rental-products-single-date-picker',
					'type'		=> 'text',
					'desc'		=> esc_html__( 'Set the maximum date that the rental form will allow date selection up to, if populated this setting overrides the rental form maximum date days setting above.', 'wcrp-rental-products' ) . '<br>' . esc_html__( 'This setting is also used for the availability checker.', 'wcrp-rental-products' ),
				);

				$rental_products_settings[] = array(
					'type'	=> 'sectionend',
				);

				$thickbox = '<div id="wcrp-rental-products-settings-availability-checker-info">';
				$thickbox .= '<p><strong>' . esc_html__( 'The availability checker is a form which allows customers to select their preferred rental dates and quantity, once these have been selected it will show an availability status on rental products in product category pages, search pages, most core WooCommerce product blocks, etc - excluding variable/grouped products as these require selection of options. Once the availability checker is set it also auto populates the rental dates and quantity on product pages if available. For period selection pricing type products the period selection where available is highlighted.', 'wcrp-rental-products' ) . '</strong></p>';
				// translators: %1$s block, %2$s: shortcode
				$thickbox .= '<p>' . wp_kses_post( sprintf( __( 'Use the %1$s block or %2$s shortcode to display the availability checker. We recommend including the availability checker throughout your customer journey e.g. in a sidebar so it is visible to customers as they browse products in your store and if they wish they can reset it. It is not recommended to include it on product pages as it may cause confusion with the rental form.', 'wcrp-rental-products' ), __( 'Availability Checker', 'wcrp-rental-products' ), '<code>[wcrp_rental_products_availability_checker]</code>' ) ) . '</p>';
				$thickbox .= '<p>' . __( 'The availability checker status is displayed on products in product category pages, search pages, most core WooCommerce product blocks, etc is added after the call to action button (add to cart, select options, read more, etc), therefore these buttons must not be disabled via a setting of your theme or an extension/plugin and/or custom development if you want the availability checker status to be displayed. The availability checker status is displayed automatically, it doesn\'t require a block/shortcode.', 'wcrp-rental-products' ) . '</p>';
				$thickbox .= '</div>';

				$rental_products_settings[] = array(
					'name'	=> esc_html__( 'Availability checker', 'wcrp-rental-products' ),
					'id'	=> 'wcrp-rental-products-settings-title-availability-checker', // ID of div not an input
					'type'	=> 'title',
					'desc'	=> esc_html__( 'Configure the availability checker shown by block or shortcode.', 'wcrp-rental-products' ) . ' ' . esc_html__( 'For details on how to use the availability checker', 'wcrp-rental-products' ) . esc_html__( ',', 'wcrp-rental-products' ) . ' <a href="#TB_inline?&width=600&height=550&inlineId=wcrp-rental-products-settings-availability-checker-info" class="thickbox" title="' . esc_html__( 'Availability checker', 'wcrp-rental-products' ) . '">' . esc_html__( 'click here', 'wcrp-rental-products' ) . '</a>' . esc_html__( '.', 'wcrp-rental-products' ) . wp_kses_post( $thickbox ),
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Availability checker mode', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_availability_checker_mode',
					'type'		=> 'select',
					'desc'		=> esc_html__( 'When using the standard mode the availability checker form included via block/shortcode, and the statuses shown on products (e.g. in category pages) are output immediately in the page code. However, depending on if you have caching enabled on your store and how it is configured you may notice that the form and/or statuses shown can become empty or incorrectly populated on cached pages. If you are experiencing this issue then consider using the AJAX mode and purge any caches after enabling. Note that regardless of the mode used, on product pages the auto population of dates/quantities is AJAX based.', 'wcrp-rental-products' ),
					'options'	=> array(
						'standard'		=> esc_html__( 'Standard', 'wcrp-rental-products' ),
						'ajax'			=> esc_html__( 'AJAX', 'wcrp-rental-products' ),
					),
				);

				$rental_products_settings[] = array(
					'name'				=> esc_html__( 'Availability checker minimum days', 'wcrp-rental-products' ),
					'id'				=> 'wcrp_rental_products_availability_checker_minimum_days',
					'type'				=> 'number',
					// translators: %s: no minimum value
					'desc'				=> esc_html__( 'Set the minimum amount of days that can selected on the availability checker calendar. This field and the one below can be set to the same number (greater than 1) to force a period for selection.', 'wcrp-rental-products' ),
					'css'				=> 'width: 100px;',
					'custom_attributes'	=> array(
						'min'	=> 1,
						'step'	=> 1,
					),
				);

				$rental_products_settings[] = array(
					'name'				=> esc_html__( 'Availability checker maximum days', 'wcrp-rental-products' ),
					'id'				=> 'wcrp_rental_products_availability_checker_maximum_days',
					'type'				=> 'number',
					// translators: %s: no maximum value
					'desc'				=> sprintf( esc_html__( 'Set the maximum amount of days that can selected on the availability checker calendar. This field and the one above can be set to the same number (greater than 1) to force a period for selection. Set to %s for no maximum.', 'wcrp-rental-products' ), '0' ),
					'css'				=> 'width: 100px;',
					'custom_attributes' => array(
						'min'	=> 0,
						'step'	=> 1,
					),
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Availability checker period multiples', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_availability_checker_period_multiples',
					'type'		=> 'checkbox',
					'desc'		=> esc_html__( 'Enable availability checker period multiples', 'wcrp-rental-products' ),
					'desc_tip'	=> esc_html__( 'If the minimum/maximum days above are set, greater than 1, and both the same to force a period selection, then enabling this setting will allow selection of multiples of that period e.g. if minimum/maximum days are set to 7 then enabling this setting will allow a period to be selected of 7, 14, 21 days, etc.', 'wcrp-rental-products' ),
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Availability checker quantity', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_availability_checker_quantity',
					'type'		=> 'checkbox',
					'desc'		=> esc_html__( 'Enable availability checker quantity', 'wcrp-rental-products' ),
					// translators: %s: default availability checker quantity
					'desc_tip'	=> sprintf( esc_html__( 'Allows a customer to specify the quantity required for the rental dates selected in the availability checker. If disabled defaults quantity to %s and hides the quantity.', 'wcrp-rental-products' ), '1' ),
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Availability checker status display', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_availability_checker_status_display',
					'type'		=> 'select',
					'desc'		=> esc_html__( 'Standard displays rental available/unavailable text and the dates/quantities which have been selected, minimal only displays the rental available/unavailable text.', 'wcrp-rental-products' ),
					'options'	=> array(
						'standard'	=> esc_html__( 'Standard', 'wcrp-rental-products' ),
						'minimal'	=> esc_html__( 'Minimal', 'wcrp-rental-products' ),
					),
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Availability checker status on rental only products', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_availability_checker_status_rental',
					'type'		=> 'checkbox',
					'desc'		=> esc_html__( 'Enable availability checker status on rental only products', 'wcrp-rental-products' ),
					'desc_tip'	=> esc_html__( 'Display availability checker status in loops/blocks on rental only products.', 'wcrp-rental-products' ),
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Availability checker status on rental or purchase products', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_availability_checker_status_rental_purchase',
					'type'		=> 'checkbox',
					'desc'		=> esc_html__( 'Enable availability checker status on rental or purchase products', 'wcrp-rental-products' ),
					'desc_tip'	=> esc_html__( 'Display availability checker status in loops/blocks on rental or purchase products. It is recommended this is disabled as the price/add to cart of the purchasable part of a rental or purchase product is shown on loops/blocks, if availability checker status is displayed a customer may confuse the price/add to cart as being for the rental.', 'wcrp-rental-products' ),
				);

				$rental_products_settings[] = array(
					'name'	=> esc_html__( 'Availability checker apply dates redirect', 'wcrp-rental-products' ),
					'id'	=> 'wcrp_rental_products_availability_checker_apply_dates_redirect',
					'type'	=> 'text',
					'desc'	=> esc_html__( 'If populated with an internal URL, when dates/quantity are applied in the availability checker, the user will be redirected to this URL instead of being shown the default applied dates confirmation. Note that external URLs can not be used for this redirect.', 'wcrp-rental-products' ),
				);

				$rental_products_settings[] = array(
					'type'	=> 'sectionend',
				);

				$rental_products_settings[] = array(
					'name'	=> esc_html__( 'Advanced pricing defaults', 'wcrp-rental-products' ),
					'id'	=> 'wcrp-rental-products-settings-title-advanced-pricing-defaults', // ID of div not an input
					'type'	=> 'title',
					'desc'	=> esc_html__( 'Configure the advanced pricing defaults used if not set at product level.', 'wcrp-rental-products' ),
				);

				$advanced_pricing_options = WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_advanced_pricing', 'options' );

				if ( !empty( $advanced_pricing_options ) ) {

					if ( array_key_exists( 'default', $advanced_pricing_options ) ) {

						unset( $advanced_pricing_options['default'] );

					}

				} else {

					$advanced_pricing_options = array();

				}

				$rental_products_settings[] = array(
					'name'		=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_advanced_pricing', 'label' ),
					'id'		=> 'wcrp_rental_products_advanced_pricing',
					'type'		=> 'select',
					'desc'		=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_advanced_pricing', 'description' ),
					'options'	=> $advanced_pricing_options,
				);

				$rental_products_settings[] = array(
					'type'	=> 'sectionend',
				);

				$rental_products_settings[] = array(
					'name'	=> esc_html__( 'In person pick up/return defaults', 'wcrp-rental-products' ),
					'id'	=> 'wcrp-rental-products-settings-title-in-person-pick-up-return-defaults', // ID of div not an input
					'type'	=> 'title',
					'desc'	=> esc_html__( 'Configure the in person pick up/return defaults used if not set at product level.', 'wcrp-rental-products' ),
				);

				$in_person_pick_up_return_time_restrictions_options = WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_return_time_restrictions', 'options' );

				if ( !empty( $in_person_pick_up_return_time_restrictions_options ) ) {

					if ( array_key_exists( 'default', $in_person_pick_up_return_time_restrictions_options ) ) {

						unset( $in_person_pick_up_return_time_restrictions_options['default'] );

					}

				} else {

					$in_person_pick_up_return_time_restrictions_options = array();

				}

				$in_person_pick_up_return_date_options = WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_return_date', 'options' );

				if ( !empty( $in_person_pick_up_return_date_options ) ) {

					if ( array_key_exists( 'default', $in_person_pick_up_return_date_options ) ) {

						unset( $in_person_pick_up_return_date_options['default'] );

					}

				} else {

					$in_person_pick_up_return_date_options = array();

				}

				$rental_products_settings[] = array(
					'name'		=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_return_time_restrictions', 'label' ),
					'id'		=> 'wcrp_rental_products_in_person_pick_up_return_time_restrictions',
					'type'		=> 'select',
					'desc'		=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_return_time_restrictions', 'description' ),
					'options'	=> $in_person_pick_up_return_time_restrictions_options,
				);

				$rental_products_settings[] = array(
					'name'		=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_return_date', 'label' ),
					'id'		=> 'wcrp_rental_products_in_person_return_date',
					'type'		=> 'select',
					'desc'		=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_return_date', 'description' ),
					'options'	=> $in_person_pick_up_return_date_options,
				);

				$rental_products_settings[] = array(
					'name'				=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_same_day', 'label' ),
					'id'				=> 'wcrp_rental_products_in_person_pick_up_times_fees_same_day',
					'class'				=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_same_day', 'class' ),
					'type'				=> 'textarea',
					'desc_tip'			=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_same_day', 'description' ),
					'placeholder'		=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_same_day', 'placeholder' ),
					'css'				=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_same_day', 'style' ), // Note this passes style not css, as the product fields set this via style, not css
					'custom_attributes' => WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_same_day', 'custom_attributes' ),
				);

				$rental_products_settings[] = array(
					'name'				=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day', 'label' ),
					'id'				=> 'wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day',
					'class'				=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day', 'class' ),
					'type'				=> 'textarea',
					'desc_tip'			=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day', 'description' ),
					'placeholder'		=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day', 'placeholder' ),
					'css'				=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day', 'style' ), // Note this passes style not css, as the product fields set this via style, not css
					'custom_attributes' => WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_single_day_same_day', 'custom_attributes' ),
				);

				$rental_products_settings[] = array(
					'name'				=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_same_day', 'label' ),
					'id'				=> 'wcrp_rental_products_in_person_return_times_fees_same_day',
					'class'				=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_same_day', 'class' ),
					'type'				=> 'textarea',
					'desc_tip'			=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_same_day', 'description' ),
					'placeholder'		=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_same_day', 'placeholder' ),
					'css'				=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_same_day', 'style' ), // Note this passes style not css, as the product fields set this via style, not css
					'custom_attributes' => WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_same_day', 'custom_attributes' ),
				);

				$rental_products_settings[] = array(
					'name'				=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_single_day_same_day', 'label' ),
					'id'				=> 'wcrp_rental_products_in_person_return_times_fees_single_day_same_day',
					'class'				=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_single_day_same_day', 'class' ),
					'type'				=> 'textarea',
					'desc_tip'			=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_single_day_same_day', 'description' ),
					'placeholder'		=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_single_day_same_day', 'placeholder' ),
					'css'				=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_single_day_same_day', 'style' ), // Note this passes style not css, as the product fields set this via style, not css
					'custom_attributes' => WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_single_day_same_day', 'custom_attributes' ),
				);

				$rental_products_settings[] = array(
					'name'				=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_next_day', 'label' ),
					'id'				=> 'wcrp_rental_products_in_person_pick_up_times_fees_next_day',
					'class'				=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_next_day', 'class' ),
					'type'				=> 'textarea',
					'desc_tip'			=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_next_day', 'description' ),
					'placeholder'		=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_next_day', 'placeholder' ),
					'css'				=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_next_day', 'style' ), // Note this passes style not css, as the product fields set this via style, not css
					'custom_attributes' => WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_pick_up_times_fees_next_day', 'custom_attributes' ),
				);

				$rental_products_settings[] = array(
					'name'				=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_next_day', 'label' ),
					'id'				=> 'wcrp_rental_products_in_person_return_times_fees_next_day',
					'class'				=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_next_day', 'class' ),
					'type'				=> 'textarea',
					'desc_tip'			=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_next_day', 'description' ),
					'placeholder'		=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_next_day', 'placeholder' ),
					'css'				=> WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_next_day', 'style' ), // Note this passes style not css, as the product fields set this via style, not css
					'custom_attributes' => WCRP_Rental_Products_Product_Fields::shared_field_attributes( '_wcrp_rental_products_in_person_return_times_fees_next_day', 'custom_attributes' ),
				);

				$rental_products_settings[] = array(
					'type'	=> 'sectionend',
				);

				$rental_products_settings[] = array(
					'name'	=> esc_html__( 'Disable rental dates', 'wcrp-rental-products' ),
					'id'	=> 'wcrp-rental-products-settings-title-disable-rental-dates', // ID of div not an input
					'type'	=> 'title',
					'desc'	=> esc_html__( 'Configure specific dates to be disabled for rental, used in addition to any dates automatically disabled where no availability.', 'wcrp-rental-products' ),
				);

				$rental_products_settings[] = array(
					'name'	=> esc_html__( 'Disable rental dates', 'wcrp-rental-products' ),
					'id'	=> 'wcrp_rental_products_disable_rental_dates',
					'class'	=> 'wcrp-rental-products-disable-dates-picker',
					'type'	=> 'text',
					'desc'	=> esc_html__( 'Disable rentals on specific dates. Used in addition to any disabled rental dates set at product level. Upon clicking the field above previously disabled dates appear light gray in the calendar, click date again to enable.', 'wcrp-rental-products' ),
				);

				$rental_products_settings[] = array(
					'name'	=> esc_html__( 'Disable rental start/end dates', 'wcrp-rental-products' ),
					'id'	=> 'wcrp_rental_products_disable_rental_start_end_dates',
					'class'	=> 'wcrp-rental-products-disable-dates-picker',
					'type'	=> 'text',
					'desc'	=> esc_html__( 'Disable rental start/end on specific dates. Used in addition to any disabled rental start/end dates set at product level. Upon clicking the field above previously disabled dates appear light gray in the calendar, click date again to enable.', 'wcrp-rental-products' ),
				);

				$rental_products_settings[] = array(
					'type'	=> 'sectionend',
				);

				$thickbox = '<div id="wcrp-rental-products-settings-rental-purchase-toggle-info">';
				// translators: %s: action hook
				$thickbox .= '<p><strong>' . wp_kses_post( sprintf( __( 'The rental or purchase toggle is added automatically via the %s action hook, generally it is not removed as it is a core WooCommerce hook, however if the toggle is not displayed it is likely you are using a page builder/theme that has removed it.', 'wcrp-rental-products' ), '<code>woocommerce_single_product_summary</code>' ) ) . '</strong></p>';
				// translators: %s: shortcode
				$thickbox .= '<p>' . wp_kses_post( sprintf( __( 'In this scenario seek support from your page builder/theme support team on how to reinstate this hook or alternatively use the %s shortcode on your product page template, however this should only be used if the toggle is not added automatically due to the missing hook.', 'wcrp-rental-products' ), '<code>[wcrp_rental_products_rental_purchase_toggle]</code>' ) ) . '</p>';
				$thickbox .= '</div>';

				$rental_products_settings[] = array(
					'name'	=> esc_html__( 'Rental or purchase toggle', 'wcrp-rental-products' ),
					'id'	=> 'wcrp-rental-products-settings-title-rental-purchase-toggle', // ID of div not an input
					'type'	=> 'title',
					'desc'	=> esc_html__( 'Configure the rental or purchase toggle shown on products of a rental or purchase type.', 'wcrp-rental-products' ) . ' ' . esc_html__( 'Not appearing?', 'wcrp-rental-products' ) . ' <a href="#TB_inline?&width=600&height=550&inlineId=wcrp-rental-products-settings-rental-purchase-toggle-info" class="thickbox" title="' . esc_html__( 'Rental or purchase toggle', 'wcrp-rental-products' ) . '">' . esc_html__( 'click here', 'wcrp-rental-products' ) . '</a>' . esc_html__( '.', 'wcrp-rental-products' ) . wp_kses_post( $thickbox ),

				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Rental or purchase toggle position', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_rental_purchase_toggle_position',
					'type'		=> 'select',
					'desc'		=> esc_html__( 'The position of the toggle for rental or purchase based products. Low is in the position below the add to cart button and high is in the position below the price.', 'wcrp-rental-products' ),
					'options'	=> array(
						'low'	=> esc_html__( 'Low', 'wcrp-rental-products' ),
						'high'	=> esc_html__( 'High', 'wcrp-rental-products' ),
					),
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Rental or purchase toggle type', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_rental_purchase_toggle_type',
					'type'		=> 'select',
					// translators: %s: button class
					'desc'		=> sprintf( esc_html__( 'The toggle type for rental or purchase based products. The button type uses the %s class, styling is normally included in your theme but not guaranteed.', 'wcrp-rental-products' ), '<code>button' . ( '' !== wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ) . '</code>' ),
					'options'	=> array(
						'link'		=> esc_html__( 'Link', 'wcrp-rental-products' ),
						'button'	=> esc_html__( 'Button', 'wcrp-rental-products' ),
					),
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Rental or purchase toggle loops/blocks display', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_rental_purchase_toggle_loops_blocks_display',
					'type'		=> 'checkbox',
					'desc'		=> esc_html__( 'Enable rental or purchase toggle loops/blocks display', 'wcrp-rental-products' ),
					'desc_tip'	=> esc_html__( 'Displays the view rental options toggle link/button on rental or purchase products within loops/blocks. View purchase options toggle link/button will not display as the purchasable part of a rental or purchase product is always shown on loops/blocks. Rental or purchase toggle position does not apply to this setting.', 'wcrp-rental-products' ),
				);

				$rental_products_settings[] = array(
					'type'	=> 'sectionend',
				);

				$rental_products_settings[] = array(
					'name'	=> esc_html__( 'Rental price display', 'wcrp-rental-products' ),
					'id'	=> 'wcrp-rental-products-settings-title-rental-price-display', // ID of div not an input
					'type'	=> 'title',
					'desc'	=> esc_html__( 'Configure the rental price display.', 'wcrp-rental-products' ),
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Rental price display prefix', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_rental_price_display_prefix',
					'type'		=> 'textarea', // Textarea over text as allows HTML to be used
					// translators: %1$s: rental price including tax placeholder, %2$s: rental price excluding tax placeholder, %3$s: rental price display suffix filter hook
					'desc_tip'	=> wp_kses_post( sprintf( __( 'Set text to appear before a rental product price. You can also have rental prices substituted here using %1$s and/or %2$s. Note this can also be filtered via the %3$s filter hook.', 'wcrp-rental-products' ), '<span class="wcrp-rental-products-tooltip-word-break">{rental_price_including_tax}</span>', '<span class="wcrp-rental-products-tooltip-word-break">{rental_price_excluding_tax}</span>', '<span class="wcrp-rental-products-tooltip-word-break">wcrp_rental_products_rental_price_display_prefix</span>' ) ),
					'css'		=> 'height: 70px;',
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Rental price display suffix', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_rental_price_display_suffix',
					'type'		=> 'textarea', // Textarea over text as allows HTML to be used
					// translators: %1$s: rental price including tax placeholder, %2$s: rental price excluding tax placeholder, %3$s: rental price display suffix filter hook
					'desc_tip'	=> wp_kses_post( sprintf( __( 'Set text to appear after a rental product price. You can also have rental prices substituted here using %1$s and/or %2$s. Note this can also be filtered via the %3$s filter hook.', 'wcrp-rental-products' ), '<span class="wcrp-rental-products-tooltip-word-break">{rental_price_including_tax}</span>', '<span class="wcrp-rental-products-tooltip-word-break">{rental_price_excluding_tax}</span>', '<span class="wcrp-rental-products-tooltip-word-break">wcrp_rental_products_rental_price_display_suffix</span>' ) ),
					'css'		=> 'height: 70px;',
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Rental price display override prefix/suffix', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_rental_price_display_override_prefix_suffix',
					'type'		=> 'checkbox',
					'desc'		=> esc_html__( 'Enable rental price display override prefix/suffix', 'wcrp-rental-products' ),
					'desc_tip'	=> esc_html__( 'Displays the rental price display prefix/suffix when rental price display has been overridden on the product.', 'wcrp-rental-products' ),
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Rental price display rent text', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_rental_price_display_rent_text',
					'type'		=> 'checkbox',
					'desc'		=> esc_html__( 'Enable rent for/from text in rental price display', 'wcrp-rental-products' ),
					'desc_tip'	=> esc_html__( 'Displays rent for/from text in the rental price display.', 'wcrp-rental-products' ),
				);

				$rental_products_settings[] = array(
					'type'	=> 'sectionend',
				);

				$rental_products_settings[] = array(
					'name'	=> esc_html__( 'Rental information', 'wcrp-rental-products' ),
					'id'	=> 'wcrp-rental-products-settings-title-rental-information', // ID of div not an input
					'type'	=> 'title',
					'desc'	=> esc_html__( 'Configure the rental information display.', 'wcrp-rental-products' ),
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Rental information title', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_rental_information_title',
					'type'		=> 'text',
					// translators: %1$s: filter hook, %2$s: recommended filter hook value
					'desc'		=> wp_kses_post( sprintf( __( 'Title used as the rental information tab and inner heading displayed on the product page. Note this can also be filtered via the %1$s filter hook. Recommended is "%2$s".', 'wcrp-rental-products' ), '<code>wcrp_rental_products_rental_information_title</code>', esc_html__( 'Rental information', 'wcrp-rental-products' ) ) ),
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Rental information heading', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_rental_information_heading',
					'type'		=> 'checkbox',
					'desc'		=> esc_html__( 'Enable rental information heading within the tab', 'wcrp-rental-products' ),
					'desc_tip'	=> esc_html__( 'Displays the rental information title as a heading within the rental information tab.', 'wcrp-rental-products' ),
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Rental information', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_rental_information',
					'type'		=> 'textarea', // Textarea over text as allows HTML to be used, and rental information maybe long
					'desc_tip'	=> esc_html__( 'Adds rental information to a tab on the product page. This will be used in addition to any rental information which has been set at product level.', 'wcrp-rental-products' ),
					'css'		=> 'height: 70px;',
				);

				$rental_products_settings[] = array(
					'type'	=> 'sectionend',
				);

				$rental_products_settings[] = array(
					'name'	=> esc_html__( 'Cart and checkout', 'wcrp-rental-products' ),
					'id'	=> 'wcrp-rental-products-settings-title-cart-checkout', // ID of div not an input
					'type'	=> 'title',
					'desc'	=> esc_html__( 'Configure cart and checkout related settings.', 'wcrp-rental-products' ),
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Checkout draft restrictions', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_checkout_draft_restrictions',
					'type'		=> 'checkbox',
					'desc'		=> esc_html__( 'Enable checkout draft restrictions', 'wcrp-rental-products' ),
					// translators: %1$s: checkout draft order status name
					'desc_tip'	=> sprintf( esc_html__( 'If using cart/checkout blocks (as opposed to classic cart/checkout) then orders with the %1$s status can exist (checkout drafts). By default these are restricted from view in the orders section and the option to change status to it is disabled. This is because it is possible to change the order status from %1$s to one which reserves rental stock for the items included in the order, however the stock may have already been reserved in newer orders since the draft was created. It is strongly recommended this setting remains enabled, if it is disabled and rental order statuses are changed from %1$s to a status which reserves rental stock it is highly likely overlaps in rental stock will occur.', 'wcrp-rental-products' ), esc_html( strtolower( wc_get_order_status_name( 'wc-checkout-draft' ) ) ) ),
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Product updated restrictions', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_product_updated_restrictions',
					'type'		=> 'checkbox',
					'desc'		=> esc_html__( 'Enable product updated restrictions', 'wcrp-rental-products' ),
					'desc_tip'	=> esc_html__( 'When enabled, if a product has been added to cart and since then has been updated, then the cart/checkout will display a notice that the product has been updated and pricing/availability may have changed, and checkout can not continue until the product is removed from the cart. It is recommended this is enabled, if disabled a customer might place an order with outdated pricing/availability, for the latter this may mean overlaps in availability. There are some limited scenarios where you may wish to consider disabling this, such as if using other functionality which updates product data when a product is added to cart.', 'wcrp-rental-products' ),
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Same rental dates required', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_same_rental_dates_required',
					'type'		=> 'checkbox',
					'desc'		=> esc_html__( 'Enable same rental dates required', 'wcrp-rental-products' ),
					'desc_tip'	=> esc_html__( 'Ensures that all rental products in cart have the same rental dates, only enable this setting if you want to force all rental products in cart to have the same rental dates.', 'wcrp-rental-products' ),
				);

				$rental_products_settings[] = array(
					'type'	=> 'sectionend',
				);

				$rental_products_settings[] = array(
					'name'	=> esc_html__( 'Order and rental management', 'wcrp-rental-products' ),
					'id'	=> 'wcrp-rental-products-settings-title-order-rental-management', // ID of div not an input
					'type'	=> 'title',
					'desc'	=> esc_html__( 'Configure order and rental management related settings.', 'wcrp-rental-products' ),
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Immediate rental stock replenishment', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_immediate_rental_stock_replenishment',
					'type'		=> 'checkbox',
					'desc'		=> esc_html__( 'Enable immediate rental stock replenishment', 'wcrp-rental-products' ),
					'desc_tip'	=> esc_html__( 'When enabled if rentals are marked as returned the quantity will be immediately available to rent again. If disabled the quantity will remain unavailable until the date due to be returned in customer facing areas such as the rental form on the product page, disabling does not effect rentals dashbord inventory totals. Note that if the archive rentals setting is enabled, any returned rentals archived will be immediately replenished regardless of this setting.', 'wcrp-rental-products' ),
				);

				$rental_products_settings[] = array(
					// translators: %s: failed order status name
					'name'		=> sprintf( esc_html__( 'Cancel rentals in %s orders', 'wcrp-rental-products' ), esc_html( strtolower( wc_get_order_status_name( 'wc-failed' ) ) ) ),
					'id'		=> 'wcrp_rental_products_cancel_rentals_in_failed_orders',
					'type'		=> 'checkbox',
					// translators: %s: failed order status name
					'desc'		=> sprintf( esc_html__( 'Enable cancel rentals in %s orders', 'wcrp-rental-products' ), esc_html( strtolower( wc_get_order_status_name( 'wc-failed' ) ) ) ),
					// translators: %1$s: failed order status name
					'desc_tip'	=> sprintf( esc_html__( 'When orders are set to %1$s then rental stock is still reserved as the order can still be accessed through the customer\'s account and they can make a payment at a later date. Enabling this setting means that when an order is set to %1$s then all the rentals within the order are cancelled, removing the reservation of the rental stock. The ability to make payment against a %1$s order with rental products included is disabled too.', 'wcrp-rental-products' ), esc_html( strtolower( wc_get_order_status_name( 'wc-failed' ) ) ) ),
				);

				$rental_products_settings[] = array(
					// translators: %s: completed order status name
					'name'		=> sprintf( esc_html__( 'Return rentals in %s orders', 'wcrp-rental-products' ), esc_html( strtolower( wc_get_order_status_name( 'wc-completed' ) ) ) ),
					'id'		=> 'wcrp_rental_products_return_rentals_in_completed_orders',
					'type'		=> 'checkbox',
					// translators: %s: completed order status name
					'desc'		=> sprintf( esc_html__( 'Enable return rentals in %s orders', 'wcrp-rental-products' ), esc_html( strtolower( wc_get_order_status_name( 'wc-completed' ) ) ) ),
					// translators: %1%s: completed order status name, %2%s: processing order status name
					'desc_tip'	=> sprintf( esc_html__( 'When enabled if an order is set to %1$s then all rentals within the order are automatically marked as returned, this is recommended, however you can disable this setting if you want to set an order to %1$s and manually mark each order item as returned. This is useful in some limited scenarios, such as if you are using an automated shipping integration which sets dispatched orders to %1$s. If this setting is disabled then rental return email reminders will not be sent as these check for all non-returned items in %2$s orders.', 'wcrp-rental-products' ), esc_html( strtolower( wc_get_order_status_name( 'wc-completed' ) ) ), esc_html( strtolower( wc_get_order_status_name( 'wc-processing' ) ) ) ),
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Managing rental orders information', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_managing_rental_orders_information',
					'type'		=> 'checkbox',
					'desc'		=> esc_html__( 'Enable managing rental orders information', 'wcrp-rental-products' ),
					'desc_tip'	=> esc_html__( 'Shows information on rental order management when viewing order details in the dashboard. When enabled the user has the choice of toggling this on or off via the screen options, if disabled it is removed for all users with no screen option available.', 'wcrp-rental-products' ),
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Calendar feed', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_calendar_feed',
					'type'		=> 'checkbox',
					'desc'		=> esc_html__( 'Enable calendar feed', 'wcrp-rental-products' ),
					'desc_tip'	=> esc_html__( 'Generates a calendar feed hourly in ICS format which can be used in a calendar application which allows subscription to calendars via URL. This allows you to view events from the rentals dashboard calendar in your chosen calendar application. The calendar is not navigable from the frontend of your store and the calendar URL includes a unique ID, however it is possible that someone could access this URL and therefore only non-identifiable information is included in the calendar feed. Note that although the calendar feed is regenerated hourly the refresh frequency is controlled by your calendar application. After enabling this setting it can take up to one hour for the feed to generate on the URL below, until then the URL will 404.', 'wcrp-rental-products' ) . '<br><code>' . esc_url( WCRP_Rental_Products_Feeds::feed_url_calendar() ) . '</code>',
				);

				$database_table_info_rentals = $wpdb->get_results( "SELECT ROUND( ( DATA_LENGTH + INDEX_LENGTH ) / 1024 ) AS `size`, `TABLE_ROWS` as `rows` FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA` = '{$wpdb->dbname}' AND `TABLE_NAME` = '{$wpdb->prefix}wcrp_rental_products_rentals';" );
				$database_table_info_rentals_archive = $wpdb->get_results( "SELECT ROUND( ( DATA_LENGTH + INDEX_LENGTH ) / 1024 ) AS `size`, `TABLE_ROWS` as `rows` FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA` = '{$wpdb->dbname}' AND `TABLE_NAME` = '{$wpdb->prefix}wcrp_rental_products_rentals_archive';" );

				$archive_rentals_database_table_info = '';
				$archive_rentals_database_table_info .= esc_html__( 'Rentals database table size: ', 'wcrp-rental-products' ) . esc_html( $database_table_info_rentals[0]->size ) . esc_html__( 'KB', 'wcrp-rental-products' ) . ' ' . esc_html__( '(', 'wcrp-rental-products' ) . esc_html( $database_table_info_rentals[0]->rows ) . ' ' . esc_html__( 'rows', 'wcrp-rental-products' ) . esc_html__( ')', 'wcrp-rental-products' );
				$archive_rentals_database_table_info .= esc_html__( ',', 'wcrp-rental-products' ) . ' ';
				$archive_rentals_database_table_info .= esc_html__( 'rentals archive database table size: ', 'wcrp-rental-products' ) . esc_html( $database_table_info_rentals_archive[0]->size ) . esc_html__( 'KB', 'wcrp-rental-products' ) . ' ' . esc_html__( '(', 'wcrp-rental-products' ) . esc_html( $database_table_info_rentals_archive[0]->rows ) . ' ' . esc_html__( 'rows', 'wcrp-rental-products' ) . esc_html__( ')', 'wcrp-rental-products' );
				$archive_rentals_database_table_info .= esc_html__( '.', 'wcrp-rental-products' );

				$rental_products_settings[] = array(
					'name'				=> esc_html__( 'Archive rentals (BETA)', 'wcrp-rental-products' ),
					'id'				=> 'wcrp_rental_products_archive_rentals',
					'type'				=> 'number',
					// translators: %s: no maximum value
					'desc'				=> sprintf( esc_html__( 'Orders which are older than the amount of days set (based on date order created) will have any rentals marked as returned archived. Set to %s to disable, any rentals already archived will remain so. When orders are created each rental within the order has associated database data which is queried to check availability, display the rentals dashboard calendar, etc. By archiving the data no longer needed for querying (e.g. rentals marked as returned) it can increase performance. Other than potential performance improvements, the only noticable difference is that archived rentals will no longer appear in the rentals dashboard calendar, but can be shown by clicking the include archived rentals button.', 'wcrp-rental-products' ), '0' ) . '<br>' . esc_html( $archive_rentals_database_table_info ) . '</strong>',
					'css'				=> 'width: 100px;',
					'custom_attributes' => array(
						'min'	=> 0,
						'step'	=> 1,
					),
				);

				$rental_products_settings[] = array(
					'type'	=> 'sectionend',
				);

				$rental_products_settings[] = array(
					'name'	=> esc_html__( 'Rentals dashboard', 'wcrp-rental-products' ),
					'id'	=> 'wcrp-rental-products-settings-title-rentals-dashboard', // ID of div not an input
					'type'	=> 'title',
					'desc'	=> esc_html__( 'Configure rentals dashboard related settings.', 'wcrp-rental-products' ),
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Rentals dashboard default tab', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_rentals_dashboard_default_tab',
					'type'		=> 'select',
					'desc'		=> esc_html__( 'Select which tab should be active upon accessing the rentals dashboard.', 'wcrp-rental-products' ),
					'options'	=> array(
						'summary'	=> esc_html__( 'Summary', 'wcrp-rental-products' ),
						'calendar'	=> esc_html__( 'Calendar', 'wcrp-rental-products' ),
						'inventory'	=> esc_html__( 'Inventory', 'wcrp-rental-products' ),
						'tools'		=> esc_html__( 'Tools', 'wcrp-rental-products' ),
					),
				);

				$rental_products_settings[] = array(
					'type'	=> 'sectionend',
				);

				$rental_products_settings[] = array(
					'name'	=> esc_html__( 'Misc', 'wcrp-rental-products' ),
					'id'	=> 'wcrp-rental-products-settings-title-misc', // ID of div not an input
					'type'	=> 'title',
					'desc'	=> esc_html__( 'Configure miscellaneous settings.', 'wcrp-rental-products' ),
				);

				$rental_products_settings[] = array(
					'name'	=> esc_html__( 'Advanced configuration', 'wcrp-rental-products' ),
					'id'	=> 'wcrp_rental_products_advanced_configuration',
					'type'	=> 'text',
					'desc'	=> esc_html__( 'Only populate when instructed by the developers of this extension to diagnose an issue or for specific server configurations.', 'wcrp-rental-products' ),
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Return days display', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_return_days_display',
					'type'		=> 'checkbox',
					'desc'		=> esc_html__( 'Enable return days display', 'wcrp-rental-products' ),
					// translators: %s: zero return days threshold
					'desc_tip'	=> esc_html__( 'Displays the number of days a customer has to return an item in customer facing areas such as the product rental form, cart, checkout and emails. If disabled any return days threshold applied is still used for calculating availability, the only difference is the number of days to return the item are not displayed to the customer. Disabling this setting is useful if you wish to instruct the customer to return a product within x days using your own messaging, but need to set the return days threshold higher. An example would be that you have a rental that needs to be returned within 3 days but you also want to allow an additional 3 days to service the product before it is made available for rental again. With this setting disabled you can set the return days threshold to 6, which would ensure the rental is only available after 6 days from the end rental date, and instruct the customer to return it within 3 days.', 'wcrp-rental-products' ) . ' ' . sprintf( esc_html__( 'Note that return days are not displayed in any scenario if the product has a return days threshold of %s, or if the product is an in person pick up/return.', 'wcrp-rental-products' ), '0' ),
				);

				$rental_products_settings[] = array(
					'type'	=> 'sectionend',
				);

				$emails = '<a href="' . esc_url( get_admin_url() . 'admin.php?page=wc-settings&tab=email&section=wcrp_rental_products_email_rental_return_reminder' ) . '" class="button button-small" target="_blank">' . esc_html__( 'Rental return reminder', 'wcrp-rental-products' ) . '</a>';

				$rental_products_settings[] = array(
					'name'	=> esc_html__( 'Emails', 'wcrp-rental-products' ),
					'id'	=> 'wcrp-rental-products-settings-title-emails', // ID of div not an input
					'type'	=> 'title',
					// translators: %s: email notifications link
					'desc'	=> esc_html__( 'Configure rental related emails.', 'wcrp-rental-products' ) . '<br><br>' . wp_kses_post( $emails ),
				);

				$rental_products_settings[] = array(
					'type'	=> 'sectionend',
				);

				$rental_products_settings[] = array(
					'name'	=> esc_html__( 'Text', 'wcrp-rental-products' ),
					'id'	=> 'wcrp-rental-products-settings-title-text', // ID of div not an input
					'type'	=> 'title',
					'desc'	=> esc_html__( 'Configure the most commonly used text references.', 'wcrp-rental-products' ),
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Availability checker applied text', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_text_availability_checker_applied',
					'type'		=> 'text',
					// translators: %1$s: filter hook, %2$s: recommended filter hook value
					'desc'		=> wp_kses_post( sprintf( __( 'Text used when availability checker applied. Note this can also be filtered via the %1$s filter hook. Recommended is "%2$s".', 'wcrp-rental-products' ), '<span class="wcrp-rental-products-tooltip-word-break">wcrp_rental_products_text_availability_checker_applied</span>', __( 'Rental products will now show availability for your selected dates.', 'wcrp-rental-products' ) ) ),
					'desc_tip'	=> true,
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Available rental stock totals text', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_text_available_rental_stock_totals',
					'type'		=> 'text',
					// translators: %1$s: filter hook, %2$s: recommended filter hook value
					'desc'		=> wp_kses_post( sprintf( __( 'Text used for available rental stock totals. Note this can also be filtered via the %1$s filter hook. Recommended is "%2$s".', 'wcrp-rental-products' ), '<span class="wcrp-rental-products-tooltip-word-break">wcrp_rental_products_text_available_rental_stock_totals</span>', __( 'Available', 'wcrp-rental-products' ) ) ),
					'desc_tip'	=> true,
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Check availability text', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_text_check_availability',
					'type'		=> 'text',
					// translators: %1$s: filter hook, %2$s: recommended filter hook value
					'desc'		=> wp_kses_post( sprintf( __( 'Text used for references to check availability. Note this can also be filtered via the %1$s filter hook. Recommended is "%2$s".', 'wcrp-rental-products' ), '<span class="wcrp-rental-products-tooltip-word-break">wcrp_rental_products_text_check_availability</span>', __( 'Check availability', 'wcrp-rental-products' ) ) ),
					'desc_tip'	=> true,
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Disable rental start/end notice text', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_text_disable_rental_start_end_notice',
					'type'		=> 'text',
					// translators: %1$s: filter hook, %2$s: recommended filter hook value
					'desc'		=> wp_kses_post( sprintf( __( 'Text used for disable rental start/end notice. Note this can also be filtered via the %1$s filter hook. Recommended is "%2$s".', 'wcrp-rental-products' ), '<span class="wcrp-rental-products-tooltip-word-break">wcrp_rental_products_text_disable_rental_start_end_notice</span>', __( 'Rentals cannot start/end on highlighted days.', 'wcrp-rental-products' ) ) ),
					'desc_tip'	=> true,
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Disable rental start notice text', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_text_disable_rental_start_notice',
					'type'		=> 'text',
					// translators: %1$s: filter hook, %2$s: recommended filter hook value
					'desc'		=> wp_kses_post( sprintf( __( 'Text used for disable rental start notice. Note this can also be filtered via the %1$s filter hook. Recommended is "%2$s".', 'wcrp-rental-products' ), '<span class="wcrp-rental-products-tooltip-word-break">wcrp_rental_products_text_disable_rental_start_notice</span>', __( 'Rentals cannot start on days highlighted with a dotted border.', 'wcrp-rental-products' ) ) ),
					'desc_tip'	=> true,
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Disable rental end notice text', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_text_disable_rental_end_notice',
					'type'		=> 'text',
					// translators: %1$s: filter hook, %2$s: recommended filter hook value
					'desc'		=> wp_kses_post( sprintf( __( 'Text used for disable rental end notice. Note this can also be filtered via the %1$s filter hook. Recommended is "%2$s".', 'wcrp-rental-products' ), '<span class="wcrp-rental-products-tooltip-word-break">wcrp_rental_products_text_disable_rental_end_notice</span>', __( 'Rentals cannot end on days highlighted with a dotted border.', 'wcrp-rental-products' ) ) ),
					'desc_tip'	=> true,
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'In person pick up/return text', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_text_in_person_pick_up_return',
					'type'		=> 'text',
					// translators: %1$s: filter hook, %2$s: recommended filter hook value
					'desc'		=> wp_kses_post( sprintf( __( 'Text used for references to in person pick up/return. Note this can also be filtered via the %1$s filter hook. Recommended is "%2$s".', 'wcrp-rental-products' ), '<span class="wcrp-rental-products-tooltip-word-break">wcrp_rental_products_text_in_person_pick_up_return</span>', __( 'In person pick up/return', 'wcrp-rental-products' ) ) ),
					'desc_tip'	=> true,
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Non-refundable text', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_text_non_refundable',
					'type'		=> 'text',
					// translators: %1$s: filter hook, %2$s: recommended filter hook value
					'desc'		=> wp_kses_post( sprintf( __( 'Text used for references to non-refundable. Note this can also be filtered via the %1$s filter hook. Recommended is "%2$s".', 'wcrp-rental-products' ), '<span class="wcrp-rental-products-tooltip-word-break">wcrp_rental_products_text_non_refundable</span>', __( 'Non-refundable', 'wcrp-rental-products' ) ) ),
					'desc_tip'	=> true,
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Pick up date text', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_text_pick_up_date',
					'type'		=> 'text',
					// translators: %1$s: filter hook, %2$s: recommended filter hook value
					'desc'		=> wp_kses_post( sprintf( __( 'Text used for references to pick up date. Note this can also be filtered via the %1$s filter hook. Recommended is "%2$s".', 'wcrp-rental-products' ), '<span class="wcrp-rental-products-tooltip-word-break">wcrp_rental_products_text_pick_up_date</span>', __( 'Pick up date', 'wcrp-rental-products' ) ) ),
					'desc_tip'	=> true,
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Pick up time text', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_text_pick_up_time',
					'type'		=> 'text',
					// translators: %1$s: filter hook, %2$s: recommended filter hook value
					'desc'		=> wp_kses_post( sprintf( __( 'Text used for references to pick up time. Note this can also be filtered via the %1$s filter hook. Recommended is "%2$s".', 'wcrp-rental-products' ), '<span class="wcrp-rental-products-tooltip-word-break">wcrp_rental_products_text_pick_up_time</span>', __( 'Pick up time', 'wcrp-rental-products' ) ) ),
					'desc_tip'	=> true,
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Refundable text', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_text_refundable',
					'type'		=> 'text',
					// translators: %1$s: filter hook, %2$s: recommended filter hook value
					'desc'		=> wp_kses_post( sprintf( __( 'Text used for references to refundable. Note this can also be filtered via the %1$s filter hook. Recommended is "%2$s".', 'wcrp-rental-products' ), '<span class="wcrp-rental-products-tooltip-word-break">wcrp_rental_products_text_refundable</span>', __( 'Refundable', 'wcrp-rental-products' ) ) ),
					'desc_tip'	=> true,
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Rent for text', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_text_rent_for',
					'type'		=> 'text',
					// translators: %1$s: filter hook, %2$s: recommended filter hook value
					'desc'		=> wp_kses_post( sprintf( __( 'Text used for references to rent for. Note this can also be filtered via the %1$s filter hook. Recommended is "%2$s".', 'wcrp-rental-products' ), '<span class="wcrp-rental-products-tooltip-word-break">wcrp_rental_products_text_rent_for</span>', __( 'Rent for', 'wcrp-rental-products' ) ) ),
					'desc_tip'	=> true,
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Rent from text', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_text_rent_from',
					'type'		=> 'text',
					// translators: %1$s: filter hook, %2$s: recommended filter hook value
					'desc'		=> wp_kses_post( sprintf( __( 'Text used for references to rent from. Note this can also be filtered via the %1$s filter hook. Recommended is "%2$s".', 'wcrp-rental-products' ), '<span class="wcrp-rental-products-tooltip-word-break">wcrp_rental_products_text_rent_from</span>', __( 'Rent from', 'wcrp-rental-products' ) ) ),
					'desc_tip'	=> true,
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Rent to text', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_text_rent_to',
					'type'		=> 'text',
					// translators: %1$s: filter hook, %2$s: recommended filter hook value
					'desc'		=> wp_kses_post( sprintf( __( 'Text used for references to rent to. Note this can also be filtered via the %1$s filter hook. Recommended is "%2$s".', 'wcrp-rental-products' ), '<span class="wcrp-rental-products-tooltip-word-break">wcrp_rental_products_text_rent_to</span>', __( 'Rent to', 'wcrp-rental-products' ) ) ),
					'desc_tip'	=> true,
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Rental available text', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_text_rental_available',
					'type'		=> 'text',
					// translators: %1$s: filter hook, %2$s: recommended filter hook value
					'desc'		=> wp_kses_post( sprintf( __( 'Text used for references to rental available. Note this can also be filtered via the %1$s filter hook. Recommended is "%2$s".', 'wcrp-rental-products' ), '<span class="wcrp-rental-products-tooltip-word-break">wcrp_rental_products_text_rental_available</span>', __( 'Rental available', 'wcrp-rental-products' ) ) ),
					'desc_tip'	=> true,
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Rental cancelled text', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_text_rental_cancelled',
					'type'		=> 'text',
					// translators: %1$s: filter hook, %2$s: recommended filter hook value
					'desc'		=> wp_kses_post( sprintf( __( 'Text used for references to rental cancelled. Note this can also be filtered via the %1$s filter hook. Recommended is "%2$s".', 'wcrp-rental-products' ), '<span class="wcrp-rental-products-tooltip-word-break">wcrp_rental_products_text_rental_cancelled</span>', __( 'Rental cancelled', 'wcrp-rental-products' ) ) ),
					'desc_tip'	=> true,
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Rental dates text', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_text_rental_dates',
					'type'		=> 'text',
					// translators: %1$s: filter hook, %2$s: recommended filter hook value
					'desc'		=> wp_kses_post( sprintf( __( 'Text used for references to rental dates. Note this can also be filtered via the %1$s filter hook. Recommended is "%2$s".', 'wcrp-rental-products' ), '<span class="wcrp-rental-products-tooltip-word-break">wcrp_rental_products_text_rental_dates</span>', __( 'Rental dates', 'wcrp-rental-products' ) ) ),
					'desc_tip'	=> true,
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Rental period text', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_text_rental_period',
					'type'		=> 'text',
					// translators: %1$s: filter hook, %2$s: recommended filter hook value
					'desc'		=> wp_kses_post( sprintf( __( 'Text used for references to rental period. Note this can also be filtered via the %1$s filter hook. Recommended is "%2$s".', 'wcrp-rental-products' ), '<span class="wcrp-rental-products-tooltip-word-break">wcrp_rental_products_text_rental_period</span>', __( 'Rental period', 'wcrp-rental-products' ) ) ),
					'desc_tip'	=> true,
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Rental return within text', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_text_rental_return_within',
					'type'		=> 'text',
					// translators: %1$s: filter hook, %2$s: recommended filter hook value
					'desc'		=> wp_kses_post( sprintf( __( 'Text used for references to rental return within. Note this can also be filtered via the %1$s filter hook. Recommended is "%2$s".', 'wcrp-rental-products' ), '<span class="wcrp-rental-products-tooltip-word-break">wcrp_rental_products_text_rental_return_within</span>', __( 'Rental return within', 'wcrp-rental-products' ) ) ),
					'desc_tip'	=> true,
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Rental returned text', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_text_rental_returned',
					'type'		=> 'text',
					// translators: %1$s: filter hook, %2$s: recommended filter hook value
					'desc'		=> wp_kses_post( sprintf( __( 'Text used for references to rental returned. Note this can also be filtered via the %1$s filter hook. Recommended is "%2$s".', 'wcrp-rental-products' ), '<span class="wcrp-rental-products-tooltip-word-break">wcrp_rental_products_text_rental_returned</span>', __( 'Rental returned', 'wcrp-rental-products' ) ) ),
					'desc_tip'	=> true,
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Rental unavailable text', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_text_rental_unavailable',
					'type'		=> 'text',
					// translators: %1$s: filter hook, %2$s: recommended filter hook value
					'desc'		=> wp_kses_post( sprintf( __( 'Text used for references to rental unavailable. Note this can also be filtered via the %1$s filter hook. Recommended is "%2$s".', 'wcrp-rental-products' ), '<span class="wcrp-rental-products-tooltip-word-break">wcrp_rental_products_text_rental_unavailable</span>', __( 'Rental unavailable', 'wcrp-rental-products' ) ) ),
					'desc_tip'	=> true,
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Reset dates text', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_text_reset_dates',
					'type'		=> 'text',
					// translators: %1$s: filter hook, %2$s: recommended filter hook value
					'desc'		=> wp_kses_post( sprintf( __( 'Text used for references to reset dates. Note this can also be filtered via the %1$s filter hook. Recommended is "%2$s".', 'wcrp-rental-products' ), '<span class="wcrp-rental-products-tooltip-word-break">wcrp_rental_products_text_reset_dates</span>', __( 'Reset dates', 'wcrp-rental-products' ) ) ),
					'desc_tip'	=> true,
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Return date text', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_text_return_date',
					'type'		=> 'text',
					// translators: %1$s: filter hook, %2$s: recommended filter hook value
					'desc'		=> wp_kses_post( sprintf( __( 'Text used for references to return date. Note this can also be filtered via the %1$s filter hook. Recommended is "%2$s".', 'wcrp-rental-products' ), '<span class="wcrp-rental-products-tooltip-word-break">wcrp_rental_products_text_return_date</span>', __( 'Return date', 'wcrp-rental-products' ) ) ),
					'desc_tip'	=> true,
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Return time text', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_text_return_time',
					'type'		=> 'text',
					// translators: %1$s: filter hook, %2$s: recommended filter hook value
					'desc'		=> wp_kses_post( sprintf( __( 'Text used for references to return time. Note this can also be filtered via the %1$s filter hook. Recommended is "%2$s".', 'wcrp-rental-products' ), '<span class="wcrp-rental-products-tooltip-word-break">wcrp_rental_products_text_return_time</span>', __( 'Return time', 'wcrp-rental-products' ) ) ),
					'desc_tip'	=> true,
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Security deposit text', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_text_security_deposit',
					'type'		=> 'text',
					// translators: %1$s: filter hook, %2$s: recommended filter hook value
					'desc'		=> wp_kses_post( sprintf( __( 'Text used for references to security deposit. Note this can also be filtered via the %1$s filter hook. Recommended is "%2$s".', 'wcrp-rental-products' ), '<span class="wcrp-rental-products-tooltip-word-break">wcrp_rental_products_text_security_deposit</span>', __( 'Security deposit', 'wcrp-rental-products' ) ) ),
					'desc_tip'	=> true,
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'Select dates text', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_text_select_dates',
					'type'		=> 'text',
					// translators: %1$s: filter hook, %2$s: recommended filter hook value
					'desc'		=> wp_kses_post( sprintf( __( 'Text used for references to select dates. Note this can also be filtered via the %1$s filter hook. Recommended is "%2$s".', 'wcrp-rental-products' ), '<span class="wcrp-rental-products-tooltip-word-break">wcrp_rental_products_text_select_dates</span>', __( 'Select dates', 'wcrp-rental-products' ) ) ),
					'desc_tip'	=> true,
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'View purchase options text', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_text_view_purchase_options',
					'type'		=> 'text',
					// translators: %1$s: filter hook, %2$s: recommended filter hook value
					'desc'		=> wp_kses_post( sprintf( __( 'Text used for references to view purchase options. Note this can also be filtered via the %1$s filter hook. Recommended is "%2$s".', 'wcrp-rental-products' ), '<span class="wcrp-rental-products-tooltip-word-break">wcrp_rental_products_text_view_purchase_options</span>', __( 'View purchase options', 'wcrp-rental-products' ) ) ),
					'desc_tip'	=> true,
				);

				$rental_products_settings[] = array(
					'name'		=> esc_html__( 'View rental options text', 'wcrp-rental-products' ),
					'id'		=> 'wcrp_rental_products_text_view_rental_options',
					'type'		=> 'text',
					// translators: %1$s: filter hook, %2$s: recommended filter hook value
					'desc'		=> wp_kses_post( sprintf( __( 'Text used for references to view rental options. Note this can also be filtered via the %1$s filter hook. Recommended is "%2$s".', 'wcrp-rental-products' ), '<span class="wcrp-rental-products-tooltip-word-break">wcrp_rental_products_text_view_rental_options</span>', __( 'View rental options', 'wcrp-rental-products' ) ) ),
					'desc_tip'	=> true,
				);

				$rental_products_settings[] = array(
					'type'	=> 'sectionend',
				);

				$thickbox = '<div id="wcrp-rental-products-settings-translation-info">';
				$thickbox .= '<p><strong>' . esc_html__( 'Important notice on use of translation plugins:', 'wcrp-rental-products' ) . '</strong></p>';
				$thickbox .= '<p>' . esc_html__( 'Some translation plugins create multiple product instances by cloning or duplicating products to allow you to translate for each language (sometimes in the background without you knowing), in doing this they then attempt to sync price, stock, etc between the instances, however due to the complex nature of rental price and stock data using translation plugins which do this are extremely likely to give unexpected results or not work at all - so do not use a translation plugin which does this, if you are unsure if your translation plugin does this reach out to the support team of the translation plugin.', 'wcrp-rental-products' ) . '</p>';
				$thickbox .= '<p><strong>' . esc_html__( 'Automatically translate from English to other languages:', 'wcrp-rental-products' ) . '</strong></p>';
				$thickbox .= '<p>' . esc_html__( 'If you wish to automatically translate the text in this extension from English to other languages then use an automatic translation plugin.', 'wcrp-rental-products' ) . '</p>';
				$thickbox .= '<p>' . esc_html__( 'There are various plugins available which offer automatic translation, we do not recommend a specific one.', 'wcrp-rental-products' ) . '</p>';
				$thickbox .= '<p><strong>' . esc_html__( 'Manually translate from English to a different language:', 'wcrp-rental-products' ) . '</strong></p>';
				$thickbox .= '<p>' . esc_html__( 'If you wish to manually translate the text in this extension from English to a different language you can do this by modifying the text settings shown within this page, these are the most commonly used text references, then modify any other text references you wish to translate using a translation plugin.', 'wcrp-rental-products' ) . '</p>';
				// translators: %s: recommended plugin link
				$thickbox .= '<p>' . wp_kses_post( sprintf( __( 'The %s plugin is the current recommendation at time of writing.', 'wcrp-rental-products' ), '<a href="' . esc_url( 'https://wordpress.org/plugins/loco-translate/' ) . '" target="_blank">' . __( 'Loco Translate', 'wcrp-rental-products' ) . '</a>' ) ) . '</p>';
				$thickbox .= '<p><strong>' . esc_html__( 'Manually translate from English to multiple languages with language selection:', 'wcrp-rental-products' ) . '</strong></p>';
				// translators: %1$s: text option name prefix, %2$s general option name prefix
				$thickbox .= '<p>' . wp_kses_post( sprintf( __( 'If you wish to manually translate the text in this extension and you have a multilingual store where a user can select a language to show manually added translations you will need to use a translation plugin. The translation plugin used will need to have functionality to not only translate the text included within extensions/plugins but also WordPress options stored in the database. This is because the most commonly used text references in this extension are stored as settings (WordPress options stored in the database) and therefore the translation plugin used needs to be able to translate these too. The option names for the text have the %1$s prefix, note there may be other text based settings (e.g. rental information title) with only the %2$s you wish to alter too. Some translation plugins allow you to modify any visible text, this effectively achieves the same result without translating the options directly.', 'wcrp-rental-products' ), '<code>wcrp_rental_products_text_</code>', '<code>wcrp_rental_products_</code>' ) ) . '</p>';
				// translators: %s: recommended plugin link
				$thickbox .= '<p>' . wp_kses_post( sprintf( __( 'The %s plugin is the current recommendation at time of writing.', 'wcrp-rental-products' ), '<a href="' . esc_url( 'https://wordpress.org/plugins/translatepress-multilingual/' ) . '" target="_blank">' . __( 'TranslatePress', 'wcrp-rental-products' ) . '</a>' ) ) . '</p>';
				$thickbox .= '<p><strong>' . esc_html__( 'Rental form and availability checker calendar language:', 'wcrp-rental-products' ) . '</strong></p>';
				// translators: %s: literpicker language filter hook
				$thickbox .= '<p>' . wp_kses_post( sprintf( __( 'The calendar displayed in the rental form and availability checker uses the Litepicker library, the day/days text within this can be translated using the methods above, however all the other references used within it, such as month names, are set through a language setting of the library. By default the language will be as per your WordPress language setting. In the scenario where your store has multiple languages with language selection, you may wish to use the %s filter hook to return a different ISO language string as per the user language selection.', 'wcrp-rental-products' ), '<code>wcrp_rental_products_litepicker_language</code>' ) ) . '</p>';
				$thickbox .= '</div>';

				$rental_products_settings[] = array(
					'name'	=> esc_html__( 'Translation', 'wcrp-rental-products' ),
					'id'	=> 'wcrp-rental-products-settings-title-translation', // ID of div not an input
					'type'	=> 'title',
					'desc'	=> esc_html__( 'For information on how to use translations with this extension', 'wcrp-rental-products' ) . esc_html__( ',', 'wcrp-rental-products' ) . ' <a href="#TB_inline?&width=600&height=550&inlineId=wcrp-rental-products-settings-translation-info" class="thickbox" title="' . esc_html__( 'Translation', 'wcrp-rental-products' ) . '">' . esc_html__( 'click here', 'wcrp-rental-products' ) . '</a>' . esc_html__( '.', 'wcrp-rental-products' ) . wp_kses_post( $thickbox ),
				);

				$rental_products_settings[] = array(
					'type'	=> 'sectionend',
				);

				return $rental_products_settings;

			} else {

				return $settings;

			}

		}

	}

}
