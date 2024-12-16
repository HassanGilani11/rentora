<?php
/**
 * Add Commission
 *
 * @package Multi-Vendor for WooCommerce/Vendor
 * */

defined( 'ABSPATH' ) || exit;
?>
<div class="wc-backbone-modal mvr-add-commission-wrapper">
	<div class="wc-backbone-modal-content">
		<section class="wc-backbone-modal-main" role="main">
			<header class="wc-backbone-modal-header">
				<h1><?php esc_html_e( 'Add Commission', 'multi-vendor-marketplace' ); ?></h1>
			</header>
			<article>
				<p class="mvr-fields">
					<label for="_commission_selection_type"><?php esc_html_e( 'Add Commission from:', 'multi-vendor-marketplace' ); ?></label>
					<select class="mvr-commission-selection-type" name="_commission_selection_type">
						<?php
						$options = array(
							'1' => esc_html__( 'Existing Order', 'multi-vendor-marketplace' ),
							'2' => esc_html__( 'Add Manual Commission', 'multi-vendor-marketplace' ),
						);

						foreach ( $options as $key => $value ) :
							?>
							<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option> 
							<?php
						endforeach;
						?>
					</select>
				</p>	
				<p class="mvr-add-commission-fields mvr-manual-commission-field">
					<label for="_user_name"><?php esc_html_e( 'Select Vendor:', 'multi-vendor-marketplace' ); ?></label>
					<?php
					mvr_select2_html(
						array(
							'id'                => '_vendor_id',
							'class'             => 'mvr-select2-search wc-product-search mvr-commission-vendor-id mvr-required-field',
							'placeholder'       => esc_html__( 'Search vendor(s)', 'multi-vendor-marketplace' ),
							'type'              => 'vendor',
							'action'            => 'mvr_json_search_vendors',
							'multiple'          => false,
							'custom_attributes' => array( 'data-nonce' => wp_create_nonce( 'search-products' ) ),
						)
					);
					?>
				</p>

				<p class="mvr-add-commission-fields mvr-manual-commission-field">
					<label for="_amount"><?php esc_html_e( 'Commission Amount:', 'multi-vendor-marketplace' ); ?></label>
					<input type="text" class="mvr-commission-amount mvr-input-price input-text mvr-required-field" name="_amount" id="_amount"/>
				</p>

				<p class="mvr-add-commission-fields mvr-manual-commission-field">
					<label for="_source_id"><?php esc_html_e( 'Source ID:', 'multi-vendor-marketplace' ); ?></label>
					<input type="number" class="mvr-commission-source-id input-text mvr-required-field" name="_source_id" id="_source_id" min=0/>
				</p>

				<p class="mvr-add-commission-fields mvr-manual-commission-field">
					<label for="_source_from"><?php esc_html_e( 'Source From:', 'multi-vendor-marketplace' ); ?></label>
					<select name="_source_from" id="_status" class="mvr-commission-source-from">
						<?php foreach ( mvr_get_commission_sources() as $source_name => $source_label ) : ?>
							<option value="<?php echo esc_attr( $source_name ); ?>"><?php echo esc_html( $source_label ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>

				<p class="mvr-add-commission-fields mvr-manual-commission-field">
					<label for="_status"><?php esc_html_e( 'Status:', 'multi-vendor-marketplace' ); ?></label>
					<select name="_status" id="_status" class="mvr-commission-status">
						<?php foreach ( mvr_get_commission_statuses() as $status_name => $status_label ) : ?>
							<option value="<?php echo esc_attr( $status_name ); ?>"><?php echo esc_html( $status_label ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>

				<p class="mvr-add-commission-fields mvr-existing-order-field">
					<label for="_order_id"><?php esc_html_e( 'Order ID:', 'multi-vendor-marketplace' ); ?></label>
					<input type="number" class="mvr-commission-order-id input-text mvr-required-field" name="_order_id" id="_order_id" min=0 />
					<button class="mvr-check-order-commission"><?php esc_html_e( 'Check', 'multi-vendor-marketplace' ); ?></button>
					<input type="hidden" class="mvr-is-available-commission" value="no">
				</p>
				<span class="mvr-inside"></span>
				<span class="mvr-error" style="font-weight:bold;"></span>
			</article>
			<footer>                
				<div class="inner">
					<button class="mvr-add-commission button button-primary" style="display:none;"><?php esc_html_e( 'Add Commission', 'multi-vendor-marketplace' ); ?></button>
					<a href="<?php echo esc_url( mvr_get_commission_page_url() ); ?>" class="mvr-cancel-commission-adding button"><?php esc_html_e( 'Cancel', 'multi-vendor-marketplace' ); ?></a>
				</div>
			</footer>
		</section>
	</div>
</div>
<div class="wc-backbone-modal-backdrop"></div>
