<?php
/**
 * Vendor Notes
 *
 * @package Multi-Vendor for WooCommerce
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<ul class="mvr-vendor-notes">
	<?php
	if ( $notes ) {
		foreach ( $notes as $note ) {
			$css_class   = array( 'note' );
			$css_class[] = $note->customer_note ? 'customer-note' : '';
			$css_class[] = 'system' === $note->added_by ? 'system-note' : '';
			/**
			 * Vendor Class
			 *
			 * @since 1.0.0
			 */
			$css_class = apply_filters( 'mvr_vendor_note_class', array_filter( $css_class ), $note );

			include 'html-vendor-note.php';
		}
	} else {
		?>
		<li class="no-items"><?php esc_html_e( 'There are no notes yet.', 'multi-vendor-marketplace' ); ?></li>
		<?php } ?>
</ul>
