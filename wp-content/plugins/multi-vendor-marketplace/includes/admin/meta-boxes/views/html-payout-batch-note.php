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
<li rel="<?php echo absint( $note->id ); ?>" class="<?php echo esc_attr( implode( ' ', $css_class ) ); ?>">
	<div class="note_content">
		<?php echo wp_kses_post( wpautop( wptexturize( $note->content ) ) ); ?>
	</div>
	<p class="meta">
		<abbr class="exact-date" title="<?php echo esc_attr( $note->date_created->date( wc_date_format() . ' ' . wc_time_format() ) ); ?>">
			<?php
			/* translators: %1$s: note date %2$s: note time */
			echo esc_html( sprintf( __( '%1$s at %2$s', 'multi-vendor-marketplace' ), $note->date_created->date_i18n( wc_date_format() ), $note->date_created->date_i18n( wc_time_format() ) ) );
			?>
		</abbr>
		<?php
		if ( 'system' !== $note->added_by ) :
			/* translators: %s: note author */
			echo esc_html( sprintf( ' ' . __( 'by %s', 'multi-vendor-marketplace' ), $note->added_by ) );
		endif;
		?>
		<a href="#" class="mvr-delete-payout-batch-note delete-note" role="button"><?php esc_html_e( 'Delete note', 'multi-vendor-marketplace' ); ?></a>
	</p>
</li>
