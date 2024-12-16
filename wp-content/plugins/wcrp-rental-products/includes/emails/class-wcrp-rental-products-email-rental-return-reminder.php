<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCRP_Rental_Products_Email_Rental_Return_Reminder' ) ) {

	class WCRP_Rental_Products_Email_Rental_Return_Reminder extends WC_Email {

		public $send_once;
		public $days_before;

		public function __construct() {

			$this->id				= 'wcrp_rental_products_email_rental_return_reminder';
			$this->title			= __( 'Rental return reminder', 'wcrp-rental-products' );
			// translators: %s: processing order status name
			$this->description		= sprintf( __( 'Rental return reminder emails are sent to customers before the rent to date including any return days threshold concludes for any rental products in an order. Reminders are only sent for orders with a status of %s, this is because these are orders that are deemed paid but not yet returned. In addition to the email a private note that a reminder email has been sent will also be added to the order for future reference.', 'wcrp-rental-products' ), strtolower( wc_get_order_status_name( 'wc-processing' ) ) );
			$this->heading			= __( 'Rental return reminder', 'wcrp-rental-products' );
			$this->subject			= __( 'Your {site_title} order has rentals due for return soon', 'wcrp-rental-products' );
			$this->customer_email	= true;
			$this->send_once		= $this->get_option( 'send_once', $this->get_default_send_once() );
			$this->days_before		= $this->get_option( 'days_before', $this->get_default_days_before() );
			$this->placeholders		= array(
				'{order_date}'   => '',
				'{order_number}' => '',
			);
			$this->template_base	= WCRP_RENTAL_PRODUCTS_TEMPLATES_PATH;
			$this->template_html	= 'emails/rental-return-reminder.php';
			$this->template_plain	= 'emails/plain/rental-return-reminder.php';

			parent::__construct();

		}

		public function init_form_fields() {

			// Some textdomains below are woocommerce as the strings are the same as core WooCommerce

			// translators: %s: available placeholders
			$placeholder_text  = sprintf( __( 'Available placeholders: %s', 'woocommerce' ), '<code>' . esc_html( implode( '</code>, <code>', array_keys( $this->placeholders ) ) ) . '</code>' );

			$this->form_fields = array(

				// Core WooCommerce email fields

				'enabled' => array(
					'title'		=> esc_html__( 'Enable/Disable', 'woocommerce' ),
					'type'		=> 'checkbox',
					'label'		=> esc_html__( 'Enable this email notification', 'woocommerce' ),
					'default'	=> 'no',
				),

				'subject' => array(
					'title'			=> esc_html__( 'Subject', 'woocommerce' ),
					'type'			=> 'text',
					'desc_tip'		=> true,
					'description'	=> $placeholder_text,
					'placeholder'	=> $this->get_default_subject(),
					'default'		=> '',
				),

				'heading' => array(
					'title'			=> esc_html__( 'Email heading', 'woocommerce' ),
					'type'			=> 'text',
					'desc_tip'		=> true,
					'description'	=> $placeholder_text,
					'placeholder'	=> $this->get_default_heading(),
					'default'		=> '',
				),

				'additional_content' => array(
					'title'			=> esc_html__( 'Additional content', 'woocommerce' ),
					'description'	=> esc_html__( 'Text to appear below the main email content.', 'woocommerce' ) . ' ' . $placeholder_text,
					'css'			=> 'width:400px; height: 75px;',
					'placeholder'	=> esc_html__( 'N/A', 'woocommerce' ),
					'type'			=> 'textarea',
					'default'		=> $this->get_default_additional_content(),
					'desc_tip'		=> true,
				),

				'email_type' => array(
					'title'			=> esc_html__( 'Email type', 'woocommerce' ),
					'type'			=> 'select',
					'description'	=> esc_html__( 'Choose which format of email to send.', 'woocommerce' ),
					'default'		=> 'html',
					'class'			=> 'email_type wc-enhanced-select',
					'options'		=> $this->get_email_type_options(),
					'desc_tip'		=> true,
				),

				// Custom email fields

				'send_once' => array(
					'title'			=> esc_html__( 'Send once', 'wcrp-rental-products' ),
					'type'			=> 'checkbox',
					'label'			=> esc_html__( 'Only send one reminder per order', 'wcrp-rental-products' ),
					'description'	=> esc_html__( 'Sends a reminder once that an order has returns due, after this no further reminders are sent. If this is disabled multiple reminders are sent based on the due return dates (e.g. if the order contains 3 rentals, 2 with the same return date and 1 with a different return date, it will send one reminder that rentals are due within the order before the first and second returns are due and a second reminder before the third return is due).', 'wcrp-rental-products' ),
					'default'		=> $this->get_default_send_once(),
					'desc_tip'		=> true,
				),

				'days_before' => array(
					'title'				=> esc_html__( 'Days before', 'woocommerce' ),
					'type'				=> 'number',
					'desc_tip'			=> true,
					'description'		=> esc_html__( 'Number of days before the rental return date (+ any rental return days threshold) to send the reminder', 'wcrp-rental-products' ),
					'placeholder'		=> esc_html__( 'Defaults to', 'wcrp-rental-products' ) . ' ' . $this->get_default_days_before(),
					'default'			=> $this->get_default_days_before(),
					'custom_attributes' => array(
						'min' => '1',
					),
				),

			);

		}

		public function get_default_send_once() {

			return 'yes';

		}

		public function get_default_days_before() {

			return '1';

		}

		public function get_content_html() {

			return wc_get_template_html(
				$this->template_html,
				array(
					'order'              => $this->object,
					'email_heading'      => $this->get_heading(),
					'additional_content' => $this->get_additional_content(),
					'sent_to_admin'      => false,
					'plain_text'         => false,
					'email'              => $this,
				),
				'',
				$this->template_base
			);

		}

		public function get_content_plain() {

			return wc_get_template_html(
				$this->template_plain,
				array(
					'order'              => $this->object,
					'email_heading'      => $this->get_heading(),
					'additional_content' => $this->get_additional_content(),
					'sent_to_admin'      => false,
					'plain_text'         => true,
					'email'              => $this,
				),
				'',
				$this->template_base
			);

		}

		public function trigger( $order_id, $order = false ) {

			$this->setup_locale();

			if ( !empty( $order_id ) && !is_a( $order, 'WC_Order' ) ) {

				$order = wc_get_order( $order_id );

			}

			if ( is_a( $order, 'WC_Order' ) ) {

				$this->object							= $order;
				$this->recipient						= $this->object->get_billing_email();
				$this->placeholders['{order_date}']		= wc_format_datetime( $this->object->get_date_created() );
				$this->placeholders['{order_number}']	= $this->object->get_order_number();

			}

			if ( $this->is_enabled() && $this->get_recipient() ) {

				$this->send(
					$this->get_recipient(),
					$this->get_subject(),
					$this->get_content(),
					$this->get_headers(),
					$this->get_attachments()
				);

			}

			$this->restore_locale();

		}

	}

	return new WCRP_Rental_Products_Email_Rental_Return_Reminder();

}
