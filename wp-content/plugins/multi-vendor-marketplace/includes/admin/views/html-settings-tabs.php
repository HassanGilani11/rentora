<?php
/**
 * Admin HTML Settings Tab
 *
 * @package Multi Vendor Marketplace
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="wrap woocommerce">
	<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
		<?php foreach ( $tabs as $tab_id => $tab_arg ) : ?>
			<?php
			$class = $tab_id === $current_tab ? array( 'nav-tab', 'nav-tab-active' ) : array( 'nav-tab' );
			/*translators: %1$s:Tab URL %2$s:Class %3$s: Tab Title*/
			printf( '<a href="%1$s" class="%2$s">%3$s</a>', esc_url( $tab_arg['url'] ), implode( ' ', array_map( 'sanitize_html_class', $class ) ), esc_html( $tab_arg['title'] ) );
			?>
		<?php endforeach; ?>
	</h2>
</div>
<?php
