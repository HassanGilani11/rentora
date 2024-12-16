<?php
/**
 * Dashboard Top navigation
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/dashboard/top-navigation.php.
 *
 * @package Multi Vendor\Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if directly accessed.
}

/**
 * Before Dashboard top navigation
 *
 * @since 1.0.0
 */
do_action( 'mvr_before_dashboard_top_navigation' );
?>
<nav class="mvr-dashboard-top-navigation">
	<ul>
		<?php foreach ( mvr_get_dashboard_top_menu_items() as $endpoint_id => $args ) : ?>
			<li class="<?php echo esc_attr( mvr_get_dashboard_menu_item_classes( $args['endpoint'] ) ); ?>">
				<a href="<?php echo esc_url( mvr_get_dashboard_endpoint_url( $args['endpoint'] ) ); ?>"><?php echo esc_html( $args['label'] ); ?></a>
				<?php
				if ( 'mvr-notification' === $args['endpoint'] ) :
					$count = $vendor_obj->get_unread_notification_count();
				elseif ( 'mvr-enquiry' === $args['endpoint'] ) :
					$count = $vendor_obj->get_unread_enquiry_count();
				endif;

				if ( $count > 0 ) :
					?>
					<span class="<?php echo esc_attr( $endpoint_id ); ?>-count-wrapper count-<?php echo esc_attr( $count ); ?>">
						<span class="<?php echo esc_attr( $endpoint_id ); ?>"><?php echo esc_attr( $count ); ?></span>
					</span>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
	<a href="<?php echo esc_url( mvr_get_dashboard_endpoint_url( 'mvr-profile' ) ); ?>" class="mvr-header-panel-profile ">
		<?php
		mvr_template_loop_store_logo( $vendor_obj );
		echo esc_html( $vendor_obj->get_name() );
		?>
	</a>
</nav>
<?php
/**
 * After Dashboard top navigation
 *
 * @since 1.0.0
 */
do_action( 'mvr_after_dashboard_top_navigation' );
?>
