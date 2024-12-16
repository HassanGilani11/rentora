<?php
/**
 * Messages Tab.
 *
 * @package Multi Vendor Marketplace/Setting Tab/Message Section
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Admin_Settings_Messages' ) ) {
	/**
	 * General Tab.
	 *
	 * @class MVR_Admin_Settings_Messages
	 * @package Class
	 */
	class MVR_Admin_Settings_Messages extends MVR_Abstract_Settings {

		/**
		 * MVR_Admin_Settings_Messages constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			$this->id            = 'messages';
			$this->label         = __( 'Messages', 'multi-vendor-marketplace' );
			$this->custom_fields = array(
				'get_messages',
			);

			/**
			 * Get the admin settings messages.
			 *
			 * @since 1.0.0
			 */
			$this->custom_fields_option = apply_filters(
				'mvr_admin_settings_messages',
				array(
					'mvr_vendor_app_yet_approve_message'   => 'Vendor Application yet to be Approved',
					'mvr_vendor_app_rejected_message'      => 'Vendor Application was Rejected',
					'mvr_vendor_required_field_message'    => 'Please fill in the Required Fields to Complete the Vendor Application.',
					'mvr_vendor_only_vendor_message'       => 'This dashboard is only for Vendors',
					'mvr_vendor_user_not_eligible_message' => 'Your are not eligible to register as a vendor',
					'mvr_vendor_guest_not_eligible_message' => 'Your are not allowed to access this page',
					'mvr_vendor_subscription_notice_message' => 'You must pay {subscription_price} to access Products, Orders & Coupons.',
				)
			);

			$this->settings = $this->get_settings();
			$this->init();
		}

		/**
		 * Get settings array.
		 *
		 * @since 1.0.0
		 * @return Array
		 */
		public function get_settings() {
			/**
			 * Get the admin settings array.
			 *
			 * @since 1.0.0
			 */
			return apply_filters(
				'mvr_get_' . $this->id . '_settings',
				array(
					array(
						'name' => __( 'Messages', 'multi-vendor-marketplace' ),
						'type' => 'title',
						'id'   => 'mvr_messages_settings',
					),
					array( 'type' => $this->get_custom_field_type( 'get_messages' ) ),
					array(
						'type' => 'sectionend',
						'id'   => 'mvr_messages_settings',
					),
				)
			);
		}

		/**
		 * To display WP editor.
		 *
		 * @since 1.0.0
		 * @param String $name Name of the field.
		 */
		public function wp_editor( $name ) {
			wp_editor( htmlspecialchars_decode( get_option( $name, $this->custom_fields_option[ $name ] ), ENT_QUOTES ), $name );
		}

		/**
		 * Custom type field.
		 *
		 * @since 1.0.0
		 */
		public function get_messages() {
			$sections = array(
				'register'  => __( 'Vendor Registration', 'multi-vendor-marketplace' ),
				'dashboard' => __( 'Vendor Dashboard', 'multi-vendor-marketplace' ),
			);

			if ( class_exists( 'WC_Subscriptions' ) ) {
				$sections['subscription'] = __( 'Vendor Subscription', 'multi-vendor-marketplace' );
			}
			/**
			 * Get the admin settings sections.
			 *
			 * @since 1.0.0
			 */
			$message_sections = apply_filters( 'mvr_admin_settings_message_sections', $sections );
			?>
			<div id="mvr_settings_messages" class="postbox">
				<div class="inside">
					<div class="panel-wrap">
						<ul class="mvr_settings_messages_tabs wc-tabs">
							<?php foreach ( $message_sections as $section_key => $section_label ) { ?>
								<li class="<?php echo esc_attr( $section_key ); ?>_section active">
									<a href="#<?php echo esc_attr( $section_key ); ?>_section_messages">
										<span><?php echo esc_html( $section_label ); ?></span>
									</a>
								</li>
							<?php } ?>
						</ul>

						<?php foreach ( $message_sections as $section_key => $section_label ) { ?>
							<div id="<?php echo esc_attr( $section_key ); ?>_section_messages" class="panel woocommerce_options_panel">
								<?php
								switch ( $section_key ) {
									case 'register':
										?>
										<p class="mvr-form-field">
											<label for="mvr_vendor_user_not_eligible_message"><?php esc_html_e( 'User Role Not Eligible for Vendor Registration - Message', 'multi-vendor-marketplace' ); ?></label>
											<?php $this->wp_editor( 'mvr_vendor_user_not_eligible_message' ); ?>
										</p>
										<p class="mvr-form-field">
											<label for="mvr_vendor_guest_not_eligible_message"><?php esc_html_e( 'Guest User(s) not Allowed for Vendor Registration - Message', 'multi-vendor-marketplace' ); ?></label>
											<?php $this->wp_editor( 'mvr_vendor_guest_not_eligible_message' ); ?>
										</p>
										<?php
										break;
									case 'dashboard':
										?>
										<p class="mvr-form-field">
											<label for="mvr_vendor_app_yet_approve_message"><?php esc_html_e( 'Vendor Application yet to be Approved - Message', 'multi-vendor-marketplace' ); ?></label>
											<?php $this->wp_editor( 'mvr_vendor_app_yet_approve_message' ); ?>
										</p>
										<p class="mvr-form-field">
											<label for="mvr_vendor_app_rejected_message"><?php esc_html_e( 'Vendor Application Rejected - Message', 'multi-vendor-marketplace' ); ?></label>
											<?php $this->wp_editor( 'mvr_vendor_app_rejected_message' ); ?>
										</p>
										<p class="mvr-form-field">
											<label for="mvr_vendor_required_field_message"><?php esc_html_e( 'Vendor Application Fields Need to be Filled - Message', 'multi-vendor-marketplace' ); ?></label>
											<?php $this->wp_editor( 'mvr_vendor_required_field_message' ); ?>
										</p>
										<p class="mvr-form-field">
											<label for="mvr_vendor_only_vendor_message"><?php esc_html_e( 'Vendor Dashboard Access Restricted - Message', 'multi-vendor-marketplace' ); ?></label>
											<?php $this->wp_editor( 'mvr_vendor_only_vendor_message' ); ?>
										</p>
										<?php
										break;
									case 'subscription':
										?>
										<p class="mvr-form-field">
											<label for="mvr_vendor_subscription_notice_message"><?php esc_html_e( 'Subscription Notice Message', 'multi-vendor-marketplace' ); ?></label>
											<?php $this->wp_editor( 'mvr_vendor_subscription_notice_message' ); ?>
											<span class="description">
												<b><?php esc_html_e( 'Shortcodes supported:', 'multi-vendor-marketplace' ); ?></b><br>
												<code>{subscription_price}</code> - <?php esc_html_e( 'Display subscription price', 'multi-vendor-marketplace' ); ?><br>
											</span>
										</p>
										<?php
										break;
									default:
										/**
										 * Get the admin settings HTML.
										 *
										 * @since 1.0.0
										 */
										do_action( 'mbp_admin_settings_messages_' . $section_key . '_section_html', $this );
										break;
								}
								?>
							</div>
						<?php } ?>
					</div>
				</div>
				<div class="clear"></div>
			</div>
			<?php
		}

		/**
		 * Delete the custom options.
		 *
		 * @since 1.0.0
		 * @param Array $posted Posted Data.
		 */
		public function custom_types_delete_options( $posted = null ) {
			foreach ( $this->custom_fields_option as $key => $default_value ) {
				delete_option( $key );
			}
		}

		/**
		 * Save custom settings.
		 *
		 * @since 1.0.0
		 * @param Array $posted Posted Data.
		 */
		public function custom_types_save( $posted ) {
			foreach ( $this->custom_fields_option as $key => $default_value ) {
				if ( ! isset( $posted[ "{$key}" ] ) ) {
					continue;
				}

				update_option( "{$key}", wp_kses_post( trim( wp_unslash( $posted[ "{$key}" ] ) ) ) );
			}
		}

		/**
		 * Save the custom options once.
		 *
		 * @since 1.0.0
		 * @param Array $posted Posted Data.
		 */
		public function custom_types_add_options( $posted = null ) {
			foreach ( $this->custom_fields_option as $key => $default_value ) {
				add_option( $key, $default_value );
			}
		}
	}

	return new MVR_Admin_Settings_Messages();
}
