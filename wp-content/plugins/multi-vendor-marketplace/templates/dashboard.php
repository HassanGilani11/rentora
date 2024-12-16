<?php
/**
 * Dashboard Page.
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/dashboard.php.
 *
 * @package Multi Vendor Marketplace\Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit accessed directly.
}

/**
 * Before Dashboard
 *
 * @since 1.0.0
 */
do_action( 'mvr_before_dashboard' );
?>
<div class="mvr-dashboard">
	<?php
	/**
	 * Dashboard navigation.
	 *
	 * @since 1.0.0
	 */
	do_action( 'mvr_dashboard_navigation' );
	?>

	<div class="mvr-dashboard-content">
		<?php
			/**
			 * Dashboard content.
			 *
			 * @since 1.0.0
			 */
			do_action( 'mvr_dashboard_content' );
		?>
	</div>
</div>
<?php
/**
 * Before Dashboard
 *
 * @since 1.0.0
 */
do_action( 'mvr_after_dashboard' );
