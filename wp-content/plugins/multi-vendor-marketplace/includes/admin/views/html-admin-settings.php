<?php
/**
 * Vendor Admin Settings HTML
 *
 * @package  Multi-Vendor\Admin Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="wrap woocommerce mvr-admin-settings">
	<!-- Database Table Update -->
	<form method="GET" id="mvr_form_verify_db_tables" action="" enctype="multipart/form-data">
		<?php wp_nonce_field( 'mvr-verify_db_tables', '_mvr_nonce' ); ?>
		<input type="hidden" name="page" value="mvr_settings">
		<input type="hidden" name="tab" value="advanced">
		<input type="hidden" name="action" value="verify_db_tables">
	</form>

	<form method="GET" id="mvr_form_install_pages" action="" enctype="multipart/form-data">
		<?php wp_nonce_field( 'mvr-install_pages', '_mvr_nonce' ); ?>
		<input type="hidden" name="page" value="mvr_settings">
		<input type="hidden" name="tab" value="advanced">
		<input type="hidden" name="action" value="install_pages">
	</form>

	<form method="post" id="mainform" action="" enctype="multipart/form-data">
		<?php
		/**
		 * Get the tabs.
		 *
		 * @since 1.0.0
		 * @param Array $tabs Tabs.
		 */
		$_tabs     = apply_filters( 'mvr_settings_tabs_array', array() );
		$_tab_keys = array_keys( $_tabs );

		echo '<ul class="subsubsub">';

		foreach ( $_tabs as $slug => $label ) :
			echo '<li><a href="' . esc_url( admin_url( 'admin.php?page=mvr_settings&tab=' . esc_attr( $slug ) ) ) . '" class="' . esc_attr( $current_tab === $slug ? 'current' : '' ) . '">' . esc_html( $label ) . '</a> ' . esc_html( end( $_tab_keys ) === $slug ? '' : '|' ) . ' </li>';
		endforeach;

		echo '</ul><br class="clear" />';

		/**
		 * Add settings tab.
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_settings_tabs' );
		?>
		<?php
		switch ( $current_tab ) :
			default:
				/**
				 * Add settings content based on tab requested.
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_settings_' . $current_tab );
				break;
		endswitch;
		?>
		<?php
		/**
		 * Need save button?
		 *
		 * @since 1.0.0
		 */
		if ( apply_filters( 'mvr_submit_' . $current_tab, true ) ) :
			?>
			<p class="submit">
				<?php if ( ! isset( $GLOBALS['hide_save_button'] ) ) : ?>
					<input name="save" class="button-primary" type="submit" value="<?php esc_attr_e( 'Save changes', 'multi-vendor-marketplace' ); ?>" />
				<?php endif; ?>

				<input type="hidden" name="subtab" id="last_tab" />
				<?php wp_nonce_field( 'mvr-settings', 'mvr_nonce' ); ?>
			</p>
		<?php endif; ?>
	</form>
	<?php
	/**
	 * Need reset button?
	 *
	 * @since 1.0.0
	 */
	if ( apply_filters( 'mvr_reset_' . $current_tab, true ) ) :
		?>
		<form method="post" id="reset_mainform" action="" enctype="multipart/form-data">
			<input name="reset" class="button-secondary" type="submit" value="<?php esc_attr_e( 'Reset', 'multi-vendor-marketplace' ); ?>"/>
			<?php wp_nonce_field( 'mvr-reset-settings', 'mvr_nonce' ); ?>
		</form>    
	<?php endif; ?>

	<?php
	/**
	 * Extra fields after setting button
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_settings_after_setting_buttons_' . $current_tab );
	?>
</div>
