<?php
/**
 * Payout Batch Data
 *
 * Displays the Payout Batch data box, tabbed, with several panels covering Notes, payout etc.
 *
 * @package  Multi-Vendor\Admin\Meta Boxes
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'MVR_Meta_Box_Payout_Batch_Data' ) ) {
	/**
	 * MVR_Meta_Box_Payout_Batch_Data Class.
	 */
	class MVR_Meta_Box_Payout_Batch_Data {

		/**
		 * Output the metabox.
		 *
		 * @since 1.0.0
		 * @param WP_Post $post Post object.
		 */
		public static function output( $post ) {
			global $post, $payout_batch_obj;

			if ( ! is_object( $payout_batch_obj ) ) {
				$payout_batch_obj = new MVR_Payout_Batch( $post->ID );
			}

			$pb_table_columns = mvr_get_payout_batch_item_table_labels();

			wp_nonce_field( 'mvr_save_data', 'mvr_save_meta_nonce' );

			include __DIR__ . '/views/html-payout-batch-data-panel.php';
		}

		/**
		 * Output the metabox.
		 *
		 * @since 1.0.0
		 * @param WP_Post $post Post object.
		 */
		public static function output_note( $post ) {
			global $post, $payout_batch_obj;

			if ( ! is_object( $payout_batch_obj ) ) {
				$payout_batch_obj = new MVR_Payout_Batch( $post->ID );
			}

			$notes = ( 'auto-draft' !== $post->post_status ) ? mvr_get_payout_batch_notes( array( 'payout_batch_id' => $payout_batch_obj->get_id() ) ) : array();

			include 'views/html-payout-batch-notes.php';
			?>
			<div class="mvr-add-note">
				<p class="mvr-note-area">
					<label for="mvr_add_payout_batch_note"><?php esc_html_e( 'Add Log: ', 'multi-vendor-marketplace' ); ?> <?php echo wp_kses_post( wc_help_tip( esc_html__( 'Add a log for your reference.', 'multi-vendor-marketplace' ) ) ); ?></label>
					<textarea type="text" name="_payout_batch_note" id="mvr_add_payout_batch_note" class="input-text" cols="20" rows="10"></textarea>
				</p>
				<p class="mvr-note-actions">
					<button type="button" class="mvr-add-payout-batch-note button"><?php esc_html_e( 'Add', 'multi-vendor-marketplace' ); ?></button>
				</p>
			</div>
			<?php
		}
	}
}
