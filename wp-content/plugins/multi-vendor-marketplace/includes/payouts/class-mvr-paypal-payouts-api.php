<?php
/**
 * PayPal Payouts API.
 *
 * @package Multi-Vendor for Woocommerce
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MVR_PayPal_Payouts_API' ) ) {
	/**
	 * Handle PayPal Payouts API.
	 *
	 * @class MVR_PayPal_Payouts_API
	 */
	class MVR_PayPal_Payouts_API {

		/**
		 * Is PayPal Sandbox Environment?
		 *
		 * @var Boolean
		 */
		protected static $sandbox = false;

		/**
		 * PayPal API Credentials.
		 *
		 * @var Array
		 */
		protected static $api_credentials = array();

		/**
		 * Logger instance
		 *
		 * @var WC_Logger
		 */
		protected static $log = false;

		/**
		 * PayPal API Sandbox Endpoint
		 */
		const SANDBOX_ENDPOINT = 'https://api.sandbox.paypal.com';

		/**
		 * PayPal API Production Endpoint
		 */
		const PRODUCTION_ENDPOINT = 'https://api.paypal.com';

		/**
		 * Prepare PayPal Environment.
		 *
		 * @since 1.0.0
		 */
		public static function prepare_environment() {
			self::$sandbox         = 'yes' === get_option( 'mvr_settings_paypal_payout_sandbox_mode', 'yes' );
			$environment           = self::$sandbox ? 'sandbox' : 'live';
			self::$api_credentials = array(
				'client_id'  => get_option( "mvr_settings_paypal_payouts_{$environment}_client_id" ),
				'secret_key' => get_option( "mvr_settings_paypal_payouts_{$environment}_client_secret_key" ),
			);
		}

		/**
		 * Generates the user agent we use to pass to PayPal's API request so
		 * PayPal can identify our application.
		 *
		 * @since 1.0.0
		 */
		public static function get_user_agent() {
			return array(
				'platform'    => array(
					'lang'         => 'php',
					'lang_version' => phpversion(),
					'uname'        => php_uname(),
				),
				'application' => array(
					'name'    => 'Multi Vendor Marketplace',
					'version' => MVR_VERSION,
					'url'     => site_url(),
				),
			);
		}

		/**
		 * Generates the headers to pass to PayPal's API request.
		 *
		 * @since 1.0.0
		 * @return Array
		 */
		public static function get_headers() {
			$user_agent = self::get_user_agent();
			/**
			 * PayPal payouts request headers.
			 *
			 * @since 1.0.0
			 */
			$headers = apply_filters(
				'mvr_paypal_payouts_request_headers',
				array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Basic ' . base64_encode( self::$api_credentials['client_id'] . ':' . self::$api_credentials['secret_key'] ),
				)
			);

			// These headers should not be overridden for this gateway.
			$headers['User-Agent'] = $user_agent['application']['name'] . ' ' . $user_agent['application']['version'] . ' (' . $user_agent['application']['url'] . ')(' . implode( '; ', $user_agent['platform'] ) . ')';

			return $headers;
		}

		/**
		 * Logging method.
		 *
		 * @since 1.0.0
		 * @param String $message Log message.
		 * @param String $level Optional. Default 'info'. Possible values: emergency|alert|critical|error|warning|notice|info|debug.
		 */
		public static function log( $message, $level = 'info' ) {
			if ( function_exists( 'wc_get_logger' ) ) {
				if ( empty( self::$log ) ) {
					self::$log = wc_get_logger();
				}

				self::$log->log( $level, $message, array( 'source' => 'multi-vendor-marketplace' ) );
			}
		}

		/**
		 * Send the request to PayPal's API
		 *
		 * @since 1.0.0
		 * @param Array  $request Request.
		 * @param String $api API.
		 * @return stdClass|array
		 */
		public static function request( $request, $api ) {
			self::log( "{$api} request: " . wc_print_r( $request, true ) );

			$endpoint = self::$sandbox ? self::SANDBOX_ENDPOINT : self::PRODUCTION_ENDPOINT;
			/**
			 * Filter for paypal Payout request body
			 *
			 * @since 1.0.0
			 * */
			$body = wp_json_encode( apply_filters( 'mvr_paypal_payouts_request_body', $request, $api ) );

			/**
			 * PayPal payouts request body.
			 *
			 * @since 1.0.0
			 */
			$response = wp_safe_remote_post(
				$endpoint . $api,
				array(
					'method'  => 'POST',
					'headers' => self::get_headers(),
					'body'    => $body,
					'timeout' => 70,
				)
			);

			$response_code = wp_remote_retrieve_response_code( $response );
			$response_body = json_decode( wp_remote_retrieve_body( $response ) );

			if ( is_wp_error( $response ) || 'created' !== strtolower( $response['response']['message'] ) ) {
				self::log(
					'Error Response: ' . wc_print_r( $response, true ) . PHP_EOL . PHP_EOL . 'Failed request: ' . wc_print_r(
						array(
							'api'     => $api,
							'request' => $request,
						),
						true
					),
					'error'
				);

				$err_message = isset( $response_body->message ) ? $response_body->message : __( 'There was a problem connecting to the PayPal API endpoint.', 'multi-vendor-marketplace' );

				return new WP_Error( 'mvr-paypal-payouts-error', $err_message );
			}

			return $response_body;
		}

		/**
		 * Retrieve API endpoint.
		 *
		 * @since 1.0.0
		 * @param String $api API.
		 * @return String
		 */
		public static function retrieve( $api ) {
			self::log( "{$api}" );

			$endpoint      = self::$sandbox ? self::SANDBOX_ENDPOINT : self::PRODUCTION_ENDPOINT;
			$response      = wp_safe_remote_get(
				$endpoint . $api,
				array(
					'method'  => 'GET',
					'headers' => self::get_headers(),
					'timeout' => 70,
				)
			);
			$response_code = wp_remote_retrieve_response_code( $response );
			$response_body = json_decode( wp_remote_retrieve_body( $response ) );

			if ( is_wp_error( $response ) || 'ok' !== strtolower( $response['response']['message'] ) ) {
				self::log( 'Error Response: ' . wc_print_r( $response, true ), 'error' );

				$err_message = isset( $response_body->message ) ? $response_body->message : __( 'There was a problem connecting to the PayPal API endpoint.', 'multi-vendor-marketplace' );

				return new WP_Error( 'mvr-paypal-payouts-error', $err_message );
			}

			return $response_body;
		}
	}
}
