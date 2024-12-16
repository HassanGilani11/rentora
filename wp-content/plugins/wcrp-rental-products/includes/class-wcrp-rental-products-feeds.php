<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Feeds' ) ) {

	class WCRP_Rental_Products_Feeds {

		public function __construct() {

			add_action( 'init', array( $this, 'schedule_events' ) );
			add_action( 'wcrp_rental_products_feeds_calendar', array( $this, 'update_feed_calendar' ) );

		}

		public function schedule_events() {

			if ( false == wp_get_scheduled_event( 'wcrp_rental_products_feeds_calendar' ) ) {

				wp_schedule_event( time(), 'hourly', 'wcrp_rental_products_feeds_calendar' );

			}

		}

		public function update_feed_calendar() {

			$calendar_feed = get_option( 'wcrp_rental_products_calendar_feed' );
			$calendar_feed_id = get_option( 'wcrp_rental_products_calendar_feed_id' );
			$calendar_feed_file_path = WCRP_RENTAL_PRODUCTS_UPLOADS_PATH . 'feeds/calendar-' . $calendar_feed_id . '.ics';

			if ( 'yes' == $calendar_feed ) {

				$events = WCRP_Rental_Products_Rentals_Calendar::events();

				$feed_content = '';
				$feed_content .= 'BEGIN:VCALENDAR' . "\r\n";
				$feed_content .= 'VERSION:2.0' . "\r\n";
				$feed_content .= 'PRODID:-//wcrp-rental-products' . "\r\n";
				$feed_content .= 'METHOD:PUBLISH' . "\r\n";

				if ( !empty( $events ) ) { // If no events the calendar is still generated but with no events, this is because a few customers previously reported the calendar feed was a 404, the cause being that they had no events yet as a new install due to this !empty( $events ) condition previously surrounding this entire feed content generation and the file_put_contents operation

					foreach ( $events as $event ) {

						$dtstamp = new DateTime();
						$dtstamp = $dtstamp->format( 'Ymd\THis' );

						$dtstart = new DateTime( $event['start'] );
						$dtstart = $dtstart->format( 'Ymd\THis' );

						$dtend = new DateTime( $event['end'] );
						$dtend = $dtend->format( 'Ymd\THis' );

						$summary = 'SUMMARY:' . substr( WCRP_Rental_Products_Rentals_Calendar::event_name( $event, true ), 0, 67 ) . "\r\n"; // Content line limit is 75, so 67 = 75 - 8 (8 is length of SUMMARY:)

						if ( strlen( $summary ) > 73 ) { // 73 as limit is 75 but "\r\n" is 2 as \ in this not counted

							$summary = 'SUMMARY:' . rtrim( substr( WCRP_Rental_Products_Rentals_Calendar::event_name( $event, true ), 0, 64 ) ) . '...' . "\r\n"; // 64 as limit is 75, including SUMMARY: and ..., rtrim removes last space if present incase event name ends with a space and therefore would have a space before ... without trim

						}

						$feed_content .= 'BEGIN:VEVENT' . "\r\n";
						$feed_content .= 'UID:' . wp_rand() . "\r\n";
						$feed_content .= 'DTSTAMP:' . $dtstamp . "\r\n";
						$feed_content .= 'DTSTART;VALUE=DATE-TIME:' . $dtstart . "\r\n";
						$feed_content .= 'DTEND;VALUE=DATE-TIME:' . $dtend . "\r\n";
						$feed_content .= $summary;
						$feed_content .= 'DESCRIPTION:' . rtrim( chunk_split( WCRP_Rental_Products_Rentals_Calendar::event_name( $event, true ), 20, "\r\n " ), "\r\n " ) . "\r\n"; // Split into multiple lines with a space due to content lines limit
						$feed_content .= 'END:VEVENT' . "\r\n";

					}

				}

				$feed_content .= 'END:VCALENDAR';

				file_put_contents( $calendar_feed_file_path, $feed_content );

			} else {

				if ( file_exists( $calendar_feed_file_path ) ) {

					unlink( $calendar_feed_file_path );

				}

			}

		}

		public static function feed_url_calendar() {

			return wp_get_upload_dir()['baseurl'] . '/wcrp-rental-products/feeds/calendar-' . get_option( 'wcrp_rental_products_calendar_feed_id' ) . '.ics';

		}

	}

}
