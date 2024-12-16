<?php
/**
 * Address Dashboard
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/dashboard/form-edit-address.php.
 *
 * @package Multi Vendor\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$countries_obj   = new WC_Countries();
$countries       = $countries_obj->__get( 'countries' );
$default_country = $countries_obj->get_base_country();
$states          = $countries_obj->get_states( $default_country );

/**
 * Before address Form
 *
 * Hook: mvr_before_address_form
 *
 * @since 1.0.0
 */
do_action( 'mvr_before_address_form' ); ?>
<div class="mvr-address-form-wrapper">
	<form class="mvr-address-form edit-form" action="" method="post" 
	<?php
	/**
	 * Address Form Tag
	 *
	 * Hook: mvr_before_address_form
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_address_form_tag' );
	?>
	>

		<?php
		/**
		 * Address Form Start
		 *
		 * Hook: mvr_address_form_start
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_address_form_start' );
		?>

		<p class="form-row form-row-first">
			<label for="_first_name"><?php echo esc_attr( get_option( 'mvr_dashboard_vendor_fname_field_label', 'First name' ) ); ?>&nbsp;<span class="required">*</span></label>
			<span class="woocommerce-input-wrapper">
				<input type="text" class="mvr-first-name input-text" name="_first_name" id="_first_name" value="<?php echo esc_attr( $form_fields['_first_name'] ); ?>">
			</span>
		</p>

		<p class="form-row form-row-last">
			<label for="_last_name"><?php echo esc_attr( get_option( 'mvr_dashboard_vendor_lname_field_label', 'Last name' ) ); ?>&nbsp;<span class="required">*</span></label>
			<span class="woocommerce-input-wrapper">
				<input type="text" class="mvr-last-name input-text" name="_last_name" id="_last_name" value="<?php echo esc_attr( $form_fields['_last_name'] ); ?>">
			</span>
		</p>

		<p class="woocommerce-form-row">
			<label for="_address1"><?php echo esc_attr( get_option( 'mvr_dashboard_vendor_addr1_field_label', 'Address 1' ) ); ?>&nbsp;<span class="required">*</span></label>
			<input type="text" class="mvr-address1" name="_address1" id="_address1" value="<?php echo esc_attr( $form_fields['_address1'] ); ?>">
		</p>

		<p class="woocommerce-form-row">
			<label for="_address2"><?php echo esc_attr( get_option( 'mvr_dashboard_vendor_addr2_field_label', 'Address 2' ) ); ?></label>
			<input type="text" class="mvr-address2" name="_address2" id="_address2" value="<?php echo esc_attr( $form_fields['_address2'] ); ?>">
		</p>

		<p class="woocommerce-form-row">
			<label for="_city"><?php echo esc_attr( get_option( 'mvr_dashboard_vendor_city_field_label', 'City' ) ); ?>&nbsp;<span class="required">*</span></label>
			<input type="text" class="mvr-city" name="_city" id="_city" value="<?php echo esc_attr( $form_fields['_city'] ); ?>">
		</p>

		<?php
		woocommerce_form_field(
			'_country',
			array(
				'id'          => '_country',
				'type'        => 'select',
				'class'       => array( 'form-field _country-field' ),
				'input_class' => array( 'js_field-country' ),
				'label'       => get_option( 'mvr_dashboard_vendor_country_field_label', 'Country' ),
				'placeholder' => __( 'Select a Country', 'multi-vendor-marketplace' ),
				'options'     => $countries,
				'required'    => true,
			),
			$form_fields['_country']
		);

		woocommerce_form_field(
			'_state',
			array(
				'id'          => '_state',
				'type'        => 'select',
				'class'       => array( 'form-field _state-field state_select' ),
				'input_class' => array( 'js_field-state' ),
				'label'       => get_option( 'mvr_dashboard_vendor_state_field_label', 'State' ),
				'placeholder' => __( 'Select a State', 'multi-vendor-marketplace' ),
				'options'     => $states,
				'required'    => true,
			),
			$form_fields['_state']
		);
		?>

		<p class="woocommerce-form-row">
			<label for="_zip_code"><?php echo esc_attr( get_option( 'mvr_dashboard_vendor_zip_code_field_label', 'Zip Code' ) ); ?>&nbsp;<span class="required">*</span></label>
			<input type="text" class="mvr-zip-code" name="_zip_code" id="_zip_code" value="<?php echo esc_attr( $form_fields['_zip_code'] ); ?>">
		</p>

		<p class="woocommerce-form-row">
			<label for="_phone"><?php echo esc_attr( get_option( 'mvr_dashboard_vendor_phone_field_label', 'Phone' ) ); ?>&nbsp;<span class="required">*</span></label>
			<input type="tel" class="mvr-phone" name="_phone" id="_phone" value="<?php echo esc_attr( $form_fields['_phone'] ); ?>">
		</p>

		<div class="clear"></div>

		<?php
		/**
		 * Address Form
		 *
		 * Hook: mvr_address_form
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_address_form' );
		?>

		<p>
			<?php wp_nonce_field( 'save_mvr_vendor_address', '_mvr_nonce' ); ?>
			<button type="submit" class="woocommerce-Button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="save_mvr_address" value="<?php esc_attr_e( 'Save changes', 'multi-vendor-marketplace' ); ?>"><?php esc_html_e( 'Save changes', 'multi-vendor-marketplace' ); ?></button>
			<input type="hidden" name="action" value="save_mvr_vendor_address" />
			<input type="hidden" name="_vendor_id" value="<?php echo esc_attr( $vendor_obj->get_id() ); ?>" />
		</p>

		<?php
		/**
		 * Address Form End
		 *
		 * Hook: mvr_address_form_end
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_address_form_end' );
		?>
	</form>
</div>
<?php
/**
 * After Address Form
 *
 * Hook: mvr_after_address_form
 *
 * @since 1.0.0
 */
do_action( 'mvr_after_address_form' );
?>
