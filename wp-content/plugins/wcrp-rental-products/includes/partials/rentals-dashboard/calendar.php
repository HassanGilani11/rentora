<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

$events = WCRP_Rental_Products_Rentals_Calendar::events();
$order_statuses = wc_get_order_statuses();
$calendar_feed = get_option( 'wcrp_rental_products_calendar_feed' );

if ( isset( $order_statuses['wc-checkout-draft'] ) ) {

	unset( $order_statuses['wc-checkout-draft'] ); // Checkout drafts are excluded as not used for rental orders, unless unrestricted in settings, but even then they wouldn't have any rentals in the calendar as don't get reserved for this order status (no rentals db data for them)

}

$order_statuses['trash'] = __( 'Trash', 'default' ); // Trash added as not a WooCommerce order status but can be trashed orders with rentals reserved, default textdomain as WordPress string

?>

<script>
	document.addEventListener( 'DOMContentLoaded', function() {

		var rentalsCalendarEl = document.getElementById( 'wcrp-rental-products-rentals-calendar' );
		var rentalsCalendarDayMaxEventsInitial = 10;
		var rentalsCalendarDayMaxEventsAlertOnce = true;

		var rentalsCalendar = new FullCalendar.Calendar( rentalsCalendarEl, {
			dayMaxEvents: rentalsCalendarDayMaxEventsInitial, <?php // Maximum rows in the grid based calendar view before a more link to access the additional rows appears ?>
			height: 'auto',
			navLinks: true, <?php // Can click day/week names to navigate views ?>
			noEventsText: '<?php esc_html_e( 'No rentals', 'wcrp-rental-products' ); ?>',
			weekNumbers: true,
			weekText: '<?php esc_html_e( 'Week', 'wcrp-rental-products' ); ?> ', <?php // Space intentionally added so reads Week[space]1 ?>
			customButtons: {
				toggleRentals: {
					text: '<?php esc_html_e( 'Toggle rentals', 'wcrp-rental-products' ); ?>',
				},
				toggleReturnsExpected: {
					text: '<?php esc_html_e( 'Toggle returns expected', 'wcrp-rental-products' ); ?>',
				},
				toggleRowsLimit: {
					text: '<?php esc_html_e( 'Toggle rows limit', 'wcrp-rental-products' ); ?>',
					click: function() {
						if ( rentalsCalendarDayMaxEventsAlertOnce == true ) {
							alert( '<?php esc_html_e( 'Calendar based views will now show all rental rows in each day without the need to click the more link to reveal more rows than the limit, click this button again to re-apply the limit of', 'wcrp-rental-products' ); ?> ' + rentalsCalendarDayMaxEventsInitial + '<?php esc_html_e( '.', 'wcrp-rental-products' ); ?>' + "\n\n" + '<?php esc_html_e( 'Any days with less rows than the limit are unaffected and list based views always show all rows.', 'wcrp-rental-products' ); ?>' );
							rentalsCalendarDayMaxEventsAlertOnce = false;
						}
						if ( rentalsCalendar.getOption( 'dayMaxEvents' ) !== <?php echo esc_html( PHP_INT_MAX ); ?> ) {
							rentalsCalendar.setOption('dayMaxEvents', <?php echo esc_html( PHP_INT_MAX ); ?> );
						} else {
							rentalsCalendar.setOption('dayMaxEvents', rentalsCalendarDayMaxEventsInitial );
						}
					}
				},
			},
			views: {
				dayGridMonth: { buttonText: '<?php esc_html_e( 'Month calendar', 'wcrp-rental-products' ); ?>' },
				listMonth: { buttonText: '<?php esc_html_e( 'Month list', 'wcrp-rental-products' ); ?>' },
				dayGridWeek: { buttonText: '<?php esc_html_e( 'Week calendar', 'wcrp-rental-products' ); ?>' },
				listWeek: { buttonText: '<?php esc_html_e( 'Week list', 'wcrp-rental-products' ); ?>' },
				dayGridDay: { buttonText: '<?php esc_html_e( 'Day calendar', 'wcrp-rental-products' ); ?>' },
				listDay: { buttonText: '<?php esc_html_e( 'Day list', 'wcrp-rental-products' ); ?>' },
			},
			viewDidMount: function ( arg ) {
				listViews = [ 'listMonth', 'listWeek', 'listDay' ];
				if ( listViews.includes( arg.view.type ) ) { <?php // Does not contain toggleRowsLimit as list views always shows all rows, no view more links ?>
					rentalsCalendar.setOption( 'headerToolbar', {
						left: 'dayGridMonth,listMonth,dayGridWeek,listWeek,dayGridDay,listDay',
						center: 'title',
						right: 'toggleRentals,toggleReturnsExpected,prev,next,today',
					});
					rentalsCalendar.setOption( 'footerToolbar', {
						left: 'dayGridMonth,listMonth,dayGridWeek,listWeek,dayGridDay,listDay',
						center: 'title',
						right: 'toggleRentals,toggleReturnsExpected,prev,next,today',
					});
				}
				else {
					rentalsCalendar.setOption( 'headerToolbar', {
						left: 'dayGridMonth,listMonth,dayGridWeek,listWeek,dayGridDay,listDay',
						center: 'title',
						right: 'toggleRentals,toggleReturnsExpected,toggleRowsLimit,prev,next,today',
					});
					rentalsCalendar.setOption( 'footerToolbar', {
						left: 'dayGridMonth,listMonth,dayGridWeek,listWeek,dayGridDay,listDay',
						center: 'title',
						right: 'toggleRentals,toggleReturnsExpected,toggleRowsLimit,prev,next,today',
					});
				}
			},
			events: [
				<?php
				if ( !empty( $events ) ) {
					foreach ( $events as $event ) {
						?>
						{
							className: "<?php echo esc_html( $event['class'] ); ?>",
							color: "<?php echo esc_html( $event['color'] ); ?>",
							title: "<?php echo wp_kses_post( WCRP_Rental_Products_Rentals_Calendar::event_name( $event, false ) ); ?>",
							url: "<?php echo esc_url( get_admin_url() ) . 'post.php?post=' . esc_html( $event['order_id'] ) . '&action=edit'; // We specifically do not use get_edit_post_link or $order->get_edit_order_url (latter used to cover both non and HPOS, as get_edit_post_link doesn't seem to work for unsynced HPOS orders), this is because the URL from those would require html_entity_decode to get into correct URL in JS but then no way to escape it for WPCS, the URL we build here works in non and HPOS ?>",
							start: "<?php echo esc_html( $event['start'] ); ?>",
							end: "<?php echo esc_html( $event['end'] ); ?>",
						},
						<?php
					}
				}
				?>
			],
			eventClick: function( info ) {
				info.jsEvent.preventDefault();
				if ( info.event.url ) {
					window.open( info.event.url ); <?php // Open in new tab so calendar remains available to get back to ?>
				}
			},
			eventDidMount: function( data ) {
				data.el.setAttribute( 'title', data.event.title ); <?php // Sets the title attribute so on hover the full event title can be seen (as long titles in calendar get cut off on some views), this attribute is also used for searching ?>
			},
		});

		document.getElementById( 'wcrp-rental-products-rentals-calendar' ).innerHTML = ''; <?php // Removes loading notice ?>

		try {

			rentalsCalendar.setOption( 'locale', '<?php echo esc_html( strtolower( str_replace( '_', '-', get_locale() ) ) ); ?>' );

		} catch ( e ) {

			<?php // Default locale, try and catch must be used as if the locale is not found it throws an Uncaught RangeError ?>

		}

		rentalsCalendar.render();

	});
</script>

<div id="wcrp-rental-products-rentals-calendar-actions" class="wcrp-rental-products-rentals-actions">
	<div>
		<label>
			<?php esc_html_e( 'Color key', 'wcrp-rental-products' ); ?>
			<select id="wcrp-rental-products-rentals-calendar-filter-color-key">
				<option value="all"><?php esc_html_e( 'All', 'wcrp-rental-products' ); ?></option>
				<option value="current"><?php esc_html_e( 'Current', 'wcrp-rental-products' ); ?></option>
				<option value="future"><?php esc_html_e( 'Future', 'wcrp-rental-products' ); ?></option>
				<option value="returned"><?php esc_html_e( 'Returned', 'wcrp-rental-products' ); ?></option>
				<option value="not-returned"><?php esc_html_e( 'Not returned', 'wcrp-rental-products' ); ?></option>
			</select>
		</label>
		<label>
			<?php esc_html_e( 'Order status', 'wcrp-rental-products' ); ?>
			<select id="wcrp-rental-products-rentals-calendar-filter-order-status">
				<option value="all"><?php esc_html_e( 'All', 'wcrp-rental-products' ); ?></option>
				<?php
				foreach ( $order_statuses as $order_status_id => $order_status_name ) {
					?>
					<option value="<?php echo esc_attr( str_replace( 'wc-', '', $order_status_id ) ); ?>"><?php echo esc_html( $order_status_name ); ?></option>
					<?php
				}
				?>
			</select>
		</label>
		<label>
			<?php esc_html_e( 'Search', 'wcrp-rental-products' ); ?>
			<input type="text" id="wcrp-rental-products-rentals-calendar-search" placeholder="<?php esc_html_e( 'e.g. product, order, customer, etc', 'wcrp-rental-products' ); ?>">
		</label>
		<div id="wcrp-rental-products-rentals-calendar-filter-color-key-inline-css"></div>
		<div id="wcrp-rental-products-rentals-calendar-filter-order-status-inline-css"></div>
		<div id="wcrp-rental-products-rentals-calendar-search-inline-css"></div>
		<div id="wcrp-rental-products-rentals-calendar-toggle-rentals-inline-css"></div>
		<div id="wcrp-rental-products-rentals-calendar-toggle-returns-expected-inline-css"></div>
	</div>
	<div>
		<?php
		// Include/exclude archived rentals button - we check archive table total rows not simply checking if archive is disabled, this is because even if archive is disabled there can still be archived data there from when previously enabled that a user may want to access
		$archived_rentals_total = $wpdb->get_results(
			"SELECT COUNT( * ) AS `total` FROM `{$wpdb->prefix}wcrp_rental_products_rentals_archive`;"
		);
		if ( (int) $archived_rentals_total[0]->total > 0 ) {
			if ( isset( $_GET['calendar_archive'] ) && 'include' == $_GET['calendar_archive'] ) {
				echo '<a href="' . esc_url( remove_query_arg( 'calendar_archive' ) ) . '" class="button"><span class="dashicons dashicons-archive"></span>' . esc_html__( 'Exclude archived rentals', 'wcrp-rental-products' ) . '</a>';
			} else {
				echo '<a href="' . esc_url( add_query_arg( 'calendar_archive', 'include' ) ) . '" class="button"><span class="dashicons dashicons-archive"></span>' . esc_html__( 'Include archived rentals', 'wcrp-rental-products' ) . '</a>';
			}
		} else {
			echo '<a class="button" disabled><span class="dashicons dashicons-archive"></span>' . esc_html__( 'No archived rentals', 'wcrp-rental-products' ) . '</a>';
		}
		// Calendar feed button
		if ( 'yes' == $calendar_feed ) {
			echo '<a id="wcrp-rental-products-rentals-calendar-calendar-feed" class="button" data-feed-url="' . esc_url( WCRP_Rental_Products_Feeds::feed_url_calendar() ) . '"><span class="dashicons dashicons-calendar-alt"></span>' . esc_html__( 'Calendar feed', 'wcrp-rental-products' ) . '</a>';
		}
		// Refresh calendar button
		echo '<a href="javascript:location.reload();" class="button"><span class="dashicons dashicons-update"></span>' . esc_html__( 'Refresh calendar', 'wcrp-rental-products' ) . '</a>';
		?>
	</div>
</div>
<div id="wcrp-rental-products-rentals-calendar"><?php esc_html_e( 'Loading rentals...', 'wcrp-rental-products' ); ?></div>
