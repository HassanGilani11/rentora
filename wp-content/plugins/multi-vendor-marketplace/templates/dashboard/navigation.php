<?php
/**
 * Dashboard navigation
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/dashboard/navigation.php.
 *
 * @package Multi Vendor\Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if directly accessed.
}

/**
 * Before Dashboard navigation
 *
 * @since 1.0.0
 */
do_action( 'mvr_before_dashboard_navigation' );
?>

<nav class="mvr-dashboard-navigation">
	<ul>
		<?php foreach ( mvr_get_dashboard_menu_items() as $endpoint_id => $args ) : ?>
			<li class="<?php echo esc_attr( mvr_get_dashboard_menu_item_classes( $args['endpoint'] ) ); ?>">
				<a href="<?php echo esc_url( mvr_get_dashboard_endpoint_url( $args['endpoint'] ) ); ?>"><?php echo esc_html( $args['label'] ); ?></a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>

<?php
/**
 * After Dashboard navigation
 *
 * @since 1.0.0
 */
do_action( 'mvr_after_dashboard_navigation' );
?>
