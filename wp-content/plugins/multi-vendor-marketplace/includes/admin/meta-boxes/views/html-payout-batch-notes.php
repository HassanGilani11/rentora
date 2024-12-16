<?php
/**
 * Payout Batch Notes
 *
 * @package Multi-Vendor for WooCommerce
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<ul class="mvr-payout-batch-notes">
	<?php
	if ( $notes ) {
		foreach ( $notes as $note ) {
			$css_class   = array( 'note' );
			$css_class[] = $note->customer_note ? 'customer-note' : '';
			$css_class[] = 'system' === $note->added_by ? 'system-note' : '';
			/**
			 * Payout bathc Class
			 *
			 * @since 1.0.0
			 */
			$css_class = apply_filters( 'mvr_payout_batch_note_class', array_filter( $css_class ), $note );

			include 'html-payout-batch-note.php';
		}
	} else {
		?>
		<li class="no-items"><?php esc_html_e( 'There are no notes yet.', 'multi-vendor-marketplace' ); ?></li>
		<?php } ?>
</ul>
