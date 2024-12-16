<?php
/**
 * Vendor data meta box.
 *
 * @package Multi-Vendor for WooCommerce\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // exit if accessed directly.
}
?>
<table class="widefat striped fixed mvr-get-payout-batch-receivers">
	<thead>
		<tr>
			<?php foreach ( $pb_table_columns as $key => $value ) : ?>
				<th><?php echo esc_html( $value ); ?></th>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php
		$i = 0;
		if ( mvr_check_is_array( $payout_batch_obj->get_items() ) ) :
			foreach ( $payout_batch_obj->get_items() as $receiver_email => $payout_data ) :
				?>
				<tr>
					<?php
					$vendor_id  = isset( $payout_data['sender_item_id'] ) ? $payout_data['sender_item_id'] : '';
					$vendor_obj = mvr_get_vendor( $vendor_id );
					$i++;
					foreach ( mvr_get_payout_batch_item_table_labels() as $column_id => $column_name ) :
						?>
						<td data-title="<?php echo esc_attr( $column_name ); ?>">
							<?php
							switch ( $column_id ) :
								case 'no':
									echo esc_html( $i );
									break;
								case 'vendor':
									if ( mvr_is_vendor( $vendor_obj ) ) {
										echo '<a href="' . esc_url( $vendor_obj->get_admin_edit_url() ) . '">' . esc_attr( $vendor_obj->get_name() ) . '</a>';
									} else {
										esc_html_e( 'Not Found', 'multi-vendor-marketplace' );
									}
									break;
								case 'email':
									echo isset( $payout_data['receiver'] ) ? esc_html( $payout_data['receiver'] ) : '';
									break;
								case 'amount':
									$amount   = isset( $payout_data['amount']['value'] ) ? $payout_data['amount']['value'] : 0;
									$currency = isset( $payout_data['amount']['currency'] ) ? $payout_data['amount']['currency'] : '';

									echo wp_kses_post( wc_price( $amount, array( 'currency' => $currency ) ) );
									break;
								case 'fee':
									$amount   = isset( $payout_data['fee']['value'] ) ? $payout_data['fee']['value'] : 0;
									$currency = isset( $payout_data['fee']['currency'] ) ? $payout_data['fee']['currency'] : '';

									echo wp_kses_post( wc_price( $amount, array( 'currency' => $currency ) ) );
									break;
								case 'status':
									$transaction_status = isset( $payout_data['transaction_status'] ) ? $payout_data['transaction_status'] : mvr_get_payout_batch_status_name( $payout_batch_obj->get_status() );

									printf( '<mark class="mvr-post-status %s"><span>%s</span></mark>', esc_attr( sanitize_html_class( 'status-' . $transaction_status ) ), esc_html( ucfirst( strtolower( $transaction_status ) ) ) );
									break;
							endswitch;
							?>
						</td>
						<?php
					endforeach;
					?>
				</tr>
				<?php
			endforeach;
		endif;
		?>
	</tbody>
	<tfoot>
		<tr>
			<th colspan="4">
				<a class="button-primary mvr-check-payout-status" href="
						<?php
						$url = wp_nonce_url(
							add_query_arg(
								array(
									'action' => 'check_payout_status',
									'post'   => $payout_batch_obj->get_id(),
								),
								$payout_batch_obj->get_admin_edit_url()
							),
							"mvr-check_payout_status-post-{$payout_batch_obj->get_id()}"
						);

						echo esc_url_raw( $url );
						?>
						"><?php esc_html_e( 'Check Payout Status', 'multi-vendor-marketplace' ); ?>
				</a>
			</th>
			<?php
			if ( $payout_batch_obj->get_batch_amount() ) {
				?>
				<th colspan="2">
					<div style="float:right">
						<p class="mvr-total-fee-payout-field">
							<?php esc_html_e( 'Total Payout Fee: ', 'multi-vendor-marketplace' ); ?>
							<strong><?php echo wp_kses_post( wc_price( $payout_batch_obj->get_batch_fee()['value'], array( 'currency' => $payout_batch_obj->get_batch_amount()['currency'] ) ) ); ?></strong>
						</p>
						<p class="mvr-total-amount-payout-field">
							<?php esc_html_e( 'Total Amount Paid: ', 'multi-vendor-marketplace' ); ?>
							<strong><?php echo wp_kses_post( wc_price( (float) $payout_batch_obj->get_batch_amount()['value'] + (float) $payout_batch_obj->get_batch_fee()['value'] ), array( 'currency' => $payout_batch_obj->get_batch_fee()['currency'] ) ); ?></strong>
						</p>
					</div>
				</th>
				<?php
			}
			?>
		</tr>
	</tfoot>
</table>
