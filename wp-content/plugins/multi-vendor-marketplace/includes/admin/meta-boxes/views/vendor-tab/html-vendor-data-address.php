<?php
/**
 * Vendor profile data panel.
 *
 * @package Multi-Vendor for WooCommerce\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $woocommerce;
$countries_obj   = new WC_Countries();
$countries       = $countries_obj->__get( 'countries' );
$default_country = $countries_obj->get_base_country();
$states          = $countries_obj->get_states( $default_country );

?>
<div id="address_vendor_data" class="panel woocommerce_options_panel">
	<div class="options_group mvr-vendor-address">
		<h4><?php esc_html_e( 'Address', 'multi-vendor-marketplace' ); ?></h4>
		<?php
		woocommerce_wp_text_input(
			array(
				'id'    => '_first_name',
				'value' => $vendor_obj->get_first_name(),
				'label' => __( 'First Name', 'multi-vendor-marketplace' ),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'    => '_last_name',
				'value' => $vendor_obj->get_last_name(),
				'label' => __( 'Last Name', 'multi-vendor-marketplace' ),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'    => '_address1',
				'value' => $vendor_obj->get_address1(),
				'label' => __( 'Number', 'multi-vendor-marketplace' ),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'    => '_address2',
				'value' => $vendor_obj->get_address2(),
				'label' => __( 'Street', 'multi-vendor-marketplace' ),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'    => '_city',
				'value' => $vendor_obj->get_city(),
				'label' => __( 'City', 'multi-vendor-marketplace' ),
			)
		);

		woocommerce_form_field(
			'_country',
			array(
				'id'          => '_country',
				'type'        => 'select',
				'class'       => array( 'form-field _country-field' ),
				'input_class' => array( 'js_field-country' ),
				'label'       => __( 'Country', 'multi-vendor-marketplace' ),
				'placeholder' => __( 'Select a Country', 'multi-vendor-marketplace' ),
				'options'     => $countries,
				'required'    => true,
			),
			! empty( $vendor_obj->get_country() ) ? $vendor_obj->get_country() : WC()->countries->get_base_country()
		);

		woocommerce_form_field(
			'_state',
			array(
				'id'          => '_state',
				'type'        => 'select',
				'class'       => array( 'form-field _state-field state_select' ),
				'input_class' => array( 'js_field-state' ),
				'label'       => __( 'State', 'multi-vendor-marketplace' ),
				'placeholder' => __( 'Select a State', 'multi-vendor-marketplace' ),
				'options'     => $states,
				'required'    => true,
			),
			$vendor_obj->get_state()
		);

		woocommerce_wp_text_input(
			array(
				'id'    => '_zip_code',
				'value' => $vendor_obj->get_zip_code(),
				'label' => __( 'Zip Code', 'multi-vendor-marketplace' ),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'    => '_phone',
				'value' => $vendor_obj->get_phone(),
				'label' => __( 'Contact', 'multi-vendor-marketplace' ),
			)
		);

		/**
		 * Vendor Address Options.
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_vendor_options_address' );
		?>
	</div>

	<?php
	/**
	 * Vendor Address Data.
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_vendor_options_address_data' );
	?>
</div>
