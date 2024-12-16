<?php
/**
 * Vendor profile data panel.
 *
 * @package Multi-Vendor for WooCommerce\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<ul class="order_actions submitbox">
	<?php
	/**
	 * Staff Action Start
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_vendor_actions_start', $post->ID );
	?>

	<li class="wide">
		<select name="_status">
			<option value=""><?php esc_html_e( 'Choose a Status', 'multi-vendor-marketplace' ); ?></option>
			<?php foreach ( mvr_get_vendor_statuses() as $status_name => $status_label ) { ?>
				<option value="<?php echo esc_attr( $status_name ); ?>" <?php selected( 'mvr-' . $vendor_obj->get_status( 'edit' ), $status_name, true ); ?>><?php echo esc_html( $status_label ); ?></option>
			<?php } ?>
		</select>
	</li>

	<li class="wide">
		<div id="delete-action">
			<?php
			if ( current_user_can( 'delete_post', $post->ID ) ) {
				if ( ! EMPTY_TRASH_DAYS ) {
					$delete_text = __( 'Delete permanently', 'multi-vendor-marketplace' );
				} else {
					$delete_text = __( 'Move to Trash', 'multi-vendor-marketplace' );
				}
				?>
				<a class="submitdelete deletion" href="<?php echo esc_url( get_delete_post_link( $post->ID ) ); ?>"><?php echo esc_html( $delete_text ); ?></a>
				<?php
			}

			$action_btn_label = 'auto-draft' === $post->post_status ? esc_html__( 'Create', 'multi-vendor-marketplace' ) : esc_html__( 'Update', 'multi-vendor-marketplace' );
			?>
		</div>
		<button type="submit" class="button save_order button-primary" name="save" value="<?php echo esc_html( $action_btn_label ); ?>"><?php echo esc_html( $action_btn_label ); ?></button>
	</li>

	<?php
	/**
	 * Staff Action End
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_vendor_actions_end', $post->ID );
	?>
</ul>
<?php
