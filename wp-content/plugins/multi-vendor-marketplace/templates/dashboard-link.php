<?php
/**
 * View Order
 *
 * @package Multi Vendor Marketplace\Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="mvr_vendor_dashboard_link">
	<a class="mvr-vendor-dashboard-btn" href="<?php echo esc_url( mvr_get_page_permalink( 'dashboard' ) ); ?>"> <?php esc_html_e( 'Vendor Dashboard', 'multi-vendor-marketplace' ); ?></a>
</div>
