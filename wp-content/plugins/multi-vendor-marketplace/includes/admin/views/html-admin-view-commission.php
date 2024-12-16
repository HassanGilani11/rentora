<?php
/**
 * View Commission
 *
 * @package Multi-Vendor for WooCommerce/Vendor
 * */

defined( 'ABSPATH' ) || exit;
?>
<div class="mvr-view-commission-wrapper">
	<div class="mvr-commission-details">
		<h3><?php esc_html_e( 'Commission Details', 'multi-vendor-marketplace' ); ?></h3>
		<?php
		if ( mvr_check_is_array( $overview_data ) ) :
			foreach ( $overview_data as $data ) :
				?>
					<p>
						<span class="mvr-label"><?php echo wp_kses_post( $data['label'] ); ?></span>
						<span class="mvr-separator">:</span>
						<span class="mvr-value"><?php echo wp_kses_post( $data['value'] ); ?> </span>
					</p>
				<?php
				endforeach;
			endif;
		?>
	</div>
	<div class="mvr-commission-settings">
	<h3><?php esc_html_e( 'Commission Settings', 'multi-vendor-marketplace' ); ?></h3>
	<?php
	if ( mvr_check_is_array( $settings_data ) ) :
		foreach ( $settings_data as $data ) :
			?>
				<p>
					<span class="mvr-label"><?php echo wp_kses_post( $data['label'] ); ?></span>
					<span class="mvr-separator">:</span>
					<span class="mvr-value"><?php echo wp_kses_post( $data['value'] ); ?> </span>
				</p>
			<?php
			endforeach;
		endif;
	?>
	</div>
</div>
