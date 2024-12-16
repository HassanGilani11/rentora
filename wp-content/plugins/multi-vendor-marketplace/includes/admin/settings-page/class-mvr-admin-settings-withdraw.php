<?php
/**
 * Withdraw Section
 *
 * @package Multi Vendor Marketplace/Setting Tab/Withdraw Section
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Admin_Settings_Withdraw' ) ) {
	/**
	 * General Tab.
	 *
	 * @class MVR_Admin_Settings_Withdraw
	 * @package Class
	 */
	class MVR_Admin_Settings_Withdraw extends MVR_Abstract_Settings {
		/**
		 * MVR_Admin_Settings_Withdraw constructor.
		 */
		public function __construct() {
			$this->id                   = 'withdraw';
			$this->label                = __( 'Withdraw', 'multi-vendor-marketplace' );
			$this->custom_fields        = array(
				'biweekly_fields',
				'monthly_fields',
				'quarterly_fields',
			);
			$this->custom_fields_option = array(
				'mvr_settings_biweekly_settings'  => array(
					'week' => '1',
					'day'  => '1',
				),
				'mvr_settings_monthly_settings'   => array(
					'week' => '1',
					'day'  => '1',
				),
				'mvr_settings_quarterly_settings' => array(
					'month' => '1',
					'week'  => '1',
					'day'   => '1',
				),
			);
			$this->settings             = $this->get_settings();
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
			 * Withdraw Settings Fields.
			 *
			 * @since 1.0.0
			 */
			return apply_filters(
				'mvr_get_' . $this->id . '_settings',
				array(
					array(
						'type'  => 'title',
						'title' => esc_html__( 'Withdraw', 'multi-vendor-marketplace' ),
						'id'    => 'mvr_withdraw_options',
					),
					array(
						'title'   => esc_html__( 'Allow Vendors to Request for a Withdrawal', 'multi-vendor-marketplace' ),
						'type'    => 'checkbox',
						'default' => 'no',
						'id'      => $this->get_option_key( 'allow_vendor_withdraw_req' ),
						'class'   => 'mvr-withdraw-settings-allow-cb',
					),
					array(
						'title'   => esc_html__( 'Payment Method(s) for Withdrawal', 'multi-vendor-marketplace' ),
						'type'    => 'multiselect',
						'options' => mvr_get_withdraw_payment_type_options(),
						'class'   => 'mvr-select2 mvr-withdraw-settings-field',
						'id'      => $this->get_option_key( 'withdraw_allow_payment' ),
						'default' => array(),
					),
					array(
						'title'   => esc_html__( 'Enable Automatic Withdraw Method', 'multi-vendor-marketplace' ),
						'type'    => 'checkbox',
						'default' => 'no',
						'class'   => 'mvr-settings-enable-automatic-withdraw mvr-withdraw-settings-field',
						'id'      => $this->get_option_key( 'enable_automatic_withdraw' ),
					),
					array(
						'title'   => esc_html__( 'Automatic Withdrawal Options', 'multi-vendor-marketplace' ),
						'type'    => 'checkbox',
						'default' => 'no',
						'class'   => 'mvr-auto-withdraw-field mvr-withdraw-settings-field',
						'id'      => $this->get_option_key( 'enable_auto_withdraw_daily' ),
						'desc'    => esc_html__( 'Daily', 'multi-vendor-marketplace' ),
					),
					array(
						'title'   => '',
						'type'    => 'checkbox',
						'default' => 'no',
						'class'   => 'mvr-withdraw-settings-field mvr-auto-withdraw-field mvr-settings-enable-auto-withdraw-weekly',
						'id'      => $this->get_option_key( 'enable_auto_withdraw_weekly' ),
						'desc'    => esc_html__( 'Weekly', 'multi-vendor-marketplace' ),
					),
					array(
						'title'       => esc_html__( 'Select Week Start Day', 'multi-vendor-marketplace' ),
						'type'        => 'select',
						'id'          => $this->get_option_key( 'withdraw_week_start_day' ),
						'class'       => 'mvr-withdraw-settings-field mvr-auto-withdraw-field mvr-auto-withdraw-week-field',
						'options'     => mvr_week_days_options(),
						'description' => '',
						'desc_tip'    => true,
						'default'     => '1',
					),
					array(
						'title'   => '',
						'type'    => 'checkbox',
						'default' => 'no',
						'class'   => 'mvr-withdraw-settings-field mvr-auto-withdraw-field mvr-settings-enable-auto-withdraw-biweekly',
						'id'      => $this->get_option_key( 'enable_auto_withdraw_biweekly' ),
						'desc'    => esc_html__( 'Bi-Weekly (Monthly Twice)', 'multi-vendor-marketplace' ),
					),
					array( 'type' => $this->get_custom_field_type( 'biweekly_fields' ) ),
					array(
						'title'   => '',
						'type'    => 'checkbox',
						'default' => 'no',
						'class'   => 'mvr-withdraw-settings-field mvr-auto-withdraw-field mvr-settings-enable-auto-withdraw-monthly',
						'id'      => $this->get_option_key( 'enable_auto_withdraw_monthly' ),
						'desc'    => esc_html__( 'Monthly', 'multi-vendor-marketplace' ),
					),
					array( 'type' => $this->get_custom_field_type( 'monthly_fields' ) ),
					array(
						'title'   => '',
						'type'    => 'checkbox',
						'default' => 'no',
						'class'   => 'mvr-withdraw-settings-field mvr-auto-withdraw-field mvr-settings-enable-auto-withdraw-quarterly',
						'id'      => $this->get_option_key( 'enable_auto_withdraw_quarterly' ),
						'desc'    => esc_html__( 'Quarterly', 'multi-vendor-marketplace' ),
					),
					array( 'type' => $this->get_custom_field_type( 'quarterly_fields' ) ),
					array(
						'title'   => esc_html__( 'Minimum Commission Amount to Submit Withdrawal Request', 'multi-vendor-marketplace' ),
						'type'    => 'text',
						'class'   => 'mvr-withdraw-settings-field wc_input_price',
						'id'      => $this->get_option_key( 'min_withdraw_threshold' ),
						'default' => '',
					),
					array(
						'title' => esc_html__( 'Hide Withdrawal Options when the Commission is set to be Received Automatically', 'multi-vendor-marketplace' ),
						'type'  => 'checkbox',
						'id'    => $this->get_option_key( 'hide_withdraw' ),
						'class' => 'mvr-withdraw-settings-field',
					),
					array(
						'title'             => esc_html__( 'Allow Withdrawal Request After', 'multi-vendor-marketplace' ),
						'type'              => 'number',
						'id'                => $this->get_option_key( 'withdraw_available_after_days' ),
						'default'           => '7',
						'desc'              => esc_html__( 'Days of Successful Order completion', 'multi-vendor-marketplace' ),
						'custom_attributes' => array(
							'min'  => 0,
							'step' => 1,
						),
						'class'             => 'mvr-withdraw-settings-field',
					),
					array(
						'type' => 'sectionend',
						'id'   => 'mvr_withdraw_options',
					),
					array(
						'type'  => 'title',
						'title' => esc_html__( 'Withdrawal Charge', 'multi-vendor-marketplace' ),
						'id'    => 'mvr_withdraw_charge_options',
					),
					array(
						'title'   => esc_html__( 'Enable Charge for Withdraw Request', 'multi-vendor-marketplace' ),
						'type'    => 'checkbox',
						'id'      => $this->get_option_key( 'enable_withdraw_charge_req' ),
						'default' => 'no',
						'class'   => 'mvr-withdraw-charge-settings-allow-cb',
					),
					array(
						'title'   => esc_html__( 'Charging type', 'multi-vendor-marketplace' ),
						'type'    => 'select',
						'id'      => $this->get_option_key( 'withdraw_charge_type' ),
						'default' => '1',
						'options' => mvr_withdraw_charge_type_options(),
						'class'   => 'mvr-withdraw-charge-settings-field',
					),
					array(
						'title' => esc_html__( 'Charge Value', 'multi-vendor-marketplace' ),
						'type'  => 'text',
						'id'    => $this->get_option_key( 'withdraw_charge_val' ),
						'class' => 'mvr-withdraw-charge-settings-field',
					),
					array(
						'type' => 'sectionend',
						'id'   => 'mvr_withdraw_charge_options',
					),
				)
			);
		}

		/**
		 * Bi Weekly fields
		 *
		 * @since 1.0.0
		 */
		public function biweekly_fields() {
			$biweekly_settings = get_option(
				'mvr_settings_biweekly_settings',
				array(
					'week' => '1',
					'day'  => '1',
				)
			);
			?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="woocommerce_store_postcode"><?php esc_html_e( 'Select Suitable Week and Day', 'multi-vendor-marketplace' ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<select name="mvr_settings_biweekly_settings[week]" class="mvr-auto-withdraw-field mvr-auto-withdraw-biweekly-field">
						<?php
						$options = mvr_month_week_options();

						foreach ( $options as $key => $value ) :
							if ( ! in_array( $key, array( 1, 2 ), true ) ) :
								continue;
							endif;
							?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $biweekly_settings['week'], $key, true ); ?>><?php echo esc_html( $value ); ?></option>
							<?php
						endforeach;
						?>
					</select>
					<select name="mvr_settings_biweekly_settings[day]" class="mvr-auto-withdraw-field mvr-auto-withdraw-biweekly-field">
						<?php
						$options = mvr_week_days_options();

						foreach ( $options as $key => $value ) :
							?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $biweekly_settings['day'], $key, true ); ?>><?php echo esc_html( $value ); ?></option>
							<?php
						endforeach;
						?>
					</select>
				</td>
			</tr>
			<?php
		}

		/**
		 * Monthly fields
		 *
		 * @since 1.0.0
		 */
		public function monthly_fields() {
			$monthly_settings = get_option(
				'mvr_settings_monthly_settings',
				array(
					'week' => '1',
					'day'  => '1',
				)
			);
			?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="woocommerce_store_postcode"><?php esc_html_e( 'Select Suitable Week and Day', 'multi-vendor-marketplace' ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<select name="mvr_settings_monthly_settings[week]" class="mvr-auto-withdraw-field mvr-auto-withdraw-monthly-field">
						<?php
						$options = mvr_month_week_options();

						foreach ( $options as $key => $value ) :
							?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $monthly_settings['week'], $key, true ); ?>><?php echo esc_html( $value ); ?></option>
							<?php
						endforeach;
						?>
					</select>
					<select name="mvr_settings_monthly_settings[day]" class="mvr-auto-withdraw-field mvr-auto-withdraw-monthly-field">
						<?php
						$options = mvr_week_days_options();

						foreach ( $options as $key => $value ) :
							?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $monthly_settings['day'], $key, true ); ?>><?php echo esc_html( $value ); ?></option>
							<?php
						endforeach;
						?>
					</select>
				</td>
			</tr>
			<?php
		}

		/**
		 * Monthly fields
		 *
		 * @since 1.0.0
		 */
		public function quarterly_fields() {
			$quarterly_settings = get_option(
				'mvr_settings_quarterly_settings',
				array(
					'month' => '1',
					'week'  => '1',
					'day'   => '1',
				)
			);
			?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="woocommerce_store_postcode"><?php esc_html_e( 'Select Suitable Month, Week and Day', 'multi-vendor-marketplace' ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<select name="mvr_settings_quarterly_settings[month]" class="mvr-auto-withdraw-field mvr-auto-withdraw-quarterly-field" style="width:25%">
						<?php
						$options = mvr_month_options();

						foreach ( $options as $key => $value ) :
							if ( ! in_array( $key, array( 1, 2, 3 ), true ) ) :
								continue;
							endif;
							?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $quarterly_settings['month'], $key, true ); ?>><?php echo esc_html( $value ); ?></option>
							<?php
						endforeach;
						?>
					</select>
					<select name="mvr_settings_quarterly_settings[week]" class="mvr-auto-withdraw-field mvr-auto-withdraw-quarterly-field" style="width:25%">
						<?php
						$options = mvr_month_week_options();

						foreach ( $options as $key => $value ) :
							?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $quarterly_settings['week'], $key, true ); ?>><?php echo esc_html( $value ); ?></option>
							<?php
						endforeach;
						?>
					</select>
					<select name="mvr_settings_quarterly_settings[day]" class="mvr-auto-withdraw-field mvr-auto-withdraw-quarterly-field" style="width:25%">
						<?php
						$options = mvr_week_days_options();

						foreach ( $options as $key => $value ) :
							?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $quarterly_settings['day'], $key, true ); ?>><?php echo esc_html( $value ); ?></option>
							<?php
						endforeach;
						?>
					</select>
				</td>
			</tr>
			<?php
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

				update_option( "{$key}", $posted[ "{$key}" ] );
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

	return new MVR_Admin_Settings_Withdraw();
}
