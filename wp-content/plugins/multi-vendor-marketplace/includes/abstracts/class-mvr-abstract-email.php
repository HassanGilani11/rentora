<?php
/**
 * Abstract Email
 *
 * @package Multi Vendor\Classes
 * @version 1.0.0
 */

if ( ! class_exists( 'MVR_Abstract_Email' ) ) {
	/**
	 * Email Class.
	 *
	 * @abstract MVR_Abstract_Email
	 * @extends WC_Email
	 */
	abstract class MVR_Abstract_Email extends WC_Email {

		/**
		 * Email supports.
		 *
		 * @var array Supports
		 */
		public $supports = array();

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			$this->template_base = mvr()->plugin_path() . '/templates/';

			// Call WC_Email constructor.
			parent::__construct();
		}

		/**
		 * Check email supports the given type.
		 *
		 * @since 1.0.0
		 * @param String $type Email Type.
		 * @return Boolean
		 */
		public function supports( $type ) {
			return in_array( $type, $this->supports, true );
		}

		/**
		 * Maybe trigger the sending of this email.
		 *
		 * @since 1.0.0
		 */
		public function maybe_trigger() {
			if ( ! $this->is_enabled() ) {
				return;
			}

			$this->setup_locale();

			$recipient = $this->get_recipient();

			if ( $recipient ) {
				$sent = $this->send( $recipient, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

				if ( $sent ) {
					/**
					 * Email sent with success.
					 *
					 * @since 1.0.0
					 */
					do_action( 'mvr_email_sent', $this );
				} else {
					/**
					 * Email failed to sent.
					 *
					 * @since 1.0
					 */
					do_action( 'mvr_email_failed_to_sent', $this );
				}
			}

			$this->restore_locale();
		}

		/**
		 * Get valid recipients.
		 *
		 * @since 1.0.0
		 * @return String
		 */
		public function get_recipient() {
			$recipient = '';

			if ( $this->supports( 'recipient' ) ) {
				$recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
			} elseif ( $this->supports( 'mail_to_admin' ) && 'yes' === $this->get_option( 'mail_to_admin' ) ) {
				$recipient = get_option( 'admin_email' );
			}

			if ( is_null( $this->recipient ) || '' === $this->recipient ) {
				$this->recipient = $recipient;
			} elseif ( '' !== $recipient ) {
				$this->recipient .= ', ';
				$this->recipient .= $recipient;
			}

			return parent::get_recipient();
		}

		/**
		 * Get email type.
		 *
		 * @since 1.0.0
		 * @return String
		 */
		public function get_email_type() {
			return class_exists( 'DOMDocument' ) ? 'html' : '';
		}

		/**
		 * Format date to display.
		 *
		 * @since 1.0.0
		 * @param Integer|String $date Date.
		 * @return String
		 */
		public function format_date( $date = '' ) {
			return mvr_format_datetime( $date );
		}

		/**
		 * Get content args.
		 *
		 * @since 1.0.0
		 * @return Array
		 */
		public function get_content_args() {
			return array(
				'blogname'      => $this->get_blogname(),
				'site_url'      => home_url(),
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => false,
				'plain_text'    => false,
				'email'         => $this,
			);
		}

		/**
		 * Get content HTMl.
		 *
		 * @since 1.0.0
		 * @return String
		 */
		public function get_content_html() {
			return mvr_get_template_html( $this->template_html, $this->get_content_args() );
		}

		/**
		 * Get content plain.
		 *
		 * @since 1.0.0
		 * @return String
		 */
		public function get_content_plain() {
			return mvr_get_template_html( $this->template_plain, $this->get_content_args() );
		}

		/**
		 * Display form fields
		 *
		 * @since 1.0.0
		 */
		public function init_form_fields() {
			$this->form_fields            = array();
			$this->form_fields['enabled'] = array(
				'title'   => __( 'Enable/Disable', 'multi-vendor-marketplace' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'multi-vendor-marketplace' ),
				'default' => 'yes',
			);

			if ( $this->supports( 'recipient' ) ) {
				$this->form_fields['recipient'] = array(
					'title'       => __( 'Recipient(s)', 'multi-vendor-marketplace' ),
					'type'        => 'text',
					/* translators: 1: admin email */
					'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to %1$s.', 'multi-vendor-marketplace' ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ),
					'placeholder' => '',
					'default'     => '',
					'desc_tip'    => true,
				);
			}

			$this->form_fields['subject'] = array(
				'title'       => __( 'Email Subject', 'multi-vendor-marketplace' ),
				'type'        => 'text',
				/* translators: 1: email subject */
				'description' => sprintf( __( 'Defaults to <code>%1$s</code>', 'multi-vendor-marketplace' ), $this->subject ),
				'placeholder' => '',
				'default'     => '',
			);

			$this->form_fields['heading'] = array(
				'title'       => __( 'Email Heading', 'multi-vendor-marketplace' ),
				'type'        => 'text',
				/* translators: 1: email heading */
				'description' => sprintf( __( 'Defaults to <code>%1$s</code>', 'multi-vendor-marketplace' ), $this->heading ),
				'placeholder' => '',
				'default'     => '',
			);

			if ( $this->supports( 'mail_to_admin' ) ) {
				$this->form_fields['mail_to_admin'] = array(
					'title'   => __( 'Send Email to Admin', 'multi-vendor-marketplace' ),
					'type'    => 'checkbox',
					'default' => 'no',
				);
			}
		}
	}
}
