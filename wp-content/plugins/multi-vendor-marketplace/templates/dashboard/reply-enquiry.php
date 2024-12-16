<?php
/**
 * Enquiry
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/dashboard/reply-enquiry.php.
 *
 * @package Multi Vendor\Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="mvr-enquiry-form-wrapper">
	<div class="mvr-customer-details">
		<div class="mvr-enquiry-id-disp">
			<label><?php esc_html_e( 'Enquiry ID', 'multi-vendor-marketplace' ); ?></label>
			<?php echo '#' . esc_attr( $enquiry_obj->get_id() ); ?>
		</div>
		<div class="mvr-customer-name">
			<label><?php esc_html_e( 'Customer Name', 'multi-vendor-marketplace' ); ?></label>
			<?php echo esc_attr( $enquiry_obj->get_customer_name() ); ?>
		</div>
		<div class="mvr-customer-email">
			<label><?php esc_html_e( 'Customer Email', 'multi-vendor-marketplace' ); ?></label>
			<?php echo esc_attr( $enquiry_obj->get_customer_email() ); ?>
		</div>
		<div class="mvr-created-date">
			<label><?php esc_html_e( 'Created Date', 'multi-vendor-marketplace' ); ?></label>
			<time datetime="<?php echo esc_attr( $enquiry_obj->get_date_created()->date( 'c' ) ); ?>"><?php echo esc_html( wc_format_datetime( $enquiry_obj->get_date_created() ) ); ?></time>
		</div>
	</div>
	<div class="mvr-enquiry-replies">
		<div class="mvr-enquiry-message">
			<label><?php esc_html_e( 'Enquiry Message', 'multi-vendor-marketplace' ); ?></label>
			<?php echo wp_kses_post( $enquiry_obj->get_message() ); ?>
		</div>
		<?php
			$replies = maybe_unserialize( $enquiry_obj->get_reply() );

		if ( mvr_check_is_array( $replies ) ) :
			?>
				<div class="mvr-enquiry-message-reply">
					<label><?php esc_html_e( 'Reply', 'multi-vendor-marketplace' ); ?></label>
				<?php
				foreach ( $replies as $args ) :
					/* translators: %1$s: Strong Start %2$s: Date */
					printf( esc_html__( '%1$s Date: %2$s', 'multi-vendor-marketplace' ), '<strong>', '</strong>' . esc_attr( $args['date'] ) . '<br/>' );

					/* translators: %1$s: Strong Start %2$s: Subject */
					printf( esc_html__( '%1$s Subject: %2$s', 'multi-vendor-marketplace' ), '<strong>', '</strong>' . wp_kses_post( $args['subject'] ) . '<br/>' );

					/* translators: %1$s: Strong Start %2$s: Message */
					printf( esc_html__( '%1$s Message: %2$s', 'multi-vendor-marketplace' ), '<strong>', '</strong>' . wp_kses_post( $args['message'] ) );
					endforeach;
				?>
				</div>
		<?php endif; ?>
	</div>
	<div class="mvr-enquiry-action">
		<?php
		wp_editor(
			'',
			'_enquiry_reply_message',
			array(
				'textarea_name' => '_enquiry_reply_message',
				'editor_class'  => 'mvr-editor-reply-message-field',
			)
		);
		?>
		<input type="hidden" class="mvr-enquiry-id" value="<?php echo esc_attr( $enquiry_obj->get_id() ); ?>"/>
		<input type="hidden" class="mvr-customer-enquiry-email" value="<?php echo esc_attr( $enquiry_obj->get_customer_email() ); ?>"/>
		<input type="button" class="mvr-enquiry-reply-send-btn" value="<?php esc_html_e( 'Send', 'multi-vendor-marketplace' ); ?>" />
	</div>
</div>
