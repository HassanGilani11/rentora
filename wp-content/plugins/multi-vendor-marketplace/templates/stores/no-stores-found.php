<?php
/**
 * No Stores Found.
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/stores/no-stores-found.php.
 *
 * @package Multi Vendor Marketplace\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit accessed directly.
}

?>
<div class="mvr-no-stores-found">
	<?php wc_print_notice( esc_html__( 'No stores were found matching your selection.', 'multi-vendor-marketplace' ), 'notice' ); ?>
</div>
