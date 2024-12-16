<?php
/**
 * Vendor staff data panel.
 *
 * @package Multi-Vendor for WooCommerce\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div id="staff_vendor_data" class="panel woocommerce_options_panel">
	<div class="options_group mvr-vendor-staff-add">
		<h4><?php esc_html_e( 'Staff', 'multi-vendor-marketplace' ); ?></h4>
		<p class="form-field">
			<label for="_staff_id"><?php esc_html_e( 'Staff:', 'multi-vendor-marketplace' ); ?></label>
			<?php
				mvr_select2_html(
					array(
						'id'          => '_staff_id',
						'class'       => 'wc-product-search mvr-vendor-staff',
						'placeholder' => esc_html__( 'Search for a Staff', 'multi-vendor-marketplace' ),
						'type'        => 'staff',
						'action'      => 'mvr_json_search_staffs',
						'css'         => 'width:80%',
						'multiple'    => false,
					)
				);
				?>
			<input type="hidden" class="mvr-vendor-id" value="<?php echo esc_attr( $vendor_obj->get_id() ); ?>">
		</p>
		<p class="mvr-note-actions">
			<button type="button" class="mvr-add-staff button"><?php esc_html_e( 'Add', 'multi-vendor-marketplace' ); ?></button>
		</p>
		<?php
		/**
		 * Vendor Staff options.
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_vendor_options_staff' );
		?>
	</div>

	<div class="options_group mvr-vendor-staff-list">
		<h4><?php esc_html_e( 'Selected Staff', 'multi-vendor-marketplace' ); ?></h4>
		<?php
		$staffs_obj = $vendor_obj->get_staffs( array( 'status' => 'active' ) );

		if ( $staffs_obj->has_staff ) :
			foreach ( $staffs_obj->staffs as $staff_obj ) :
				if ( mvr_is_staff( $staff_obj ) ) :
					include 'html-staff-data.php';
				endif;
			endforeach;
		else :
			?>
			<div class="mvr-no-staff-data">
				<?php esc_html_e( 'No Staff Found', 'multi-vendor-marketplace' ); ?>
			</div>
			<?php
		endif;
		?>

		<?php
			/**
			 * Vendor Staff data.
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_vendor_options_staff_list' );
		?>
	</div>

	<?php
	/**
	 * Vendor Staff data.
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_vendor_options_staff_data' );
	?>
</div>
