<?php
/**
 * This template is used for pagination.
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/pagination.php
 *
 * To maintain compatibility, Multi Vendor Marketplace will update the template files and you have to copy the updated files to your theme
 *
 * @package Multi Vendor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<nav class="pagination pagination-centered woocommerce-pagination">
	<ul>
		<li>
			<span class="mvr-pagination mvr-first-pagination">
				<a href="<?php echo esc_url( add_query_arg( array( '_p' => 1 ), $url ) ); ?>"> << </a>
			</span>
		<li>
			<span class="mvr-pagination mvr-prev-pagination">
				<a href="<?php echo esc_url( add_query_arg( array( '_p' => $prev_page_count ), $url ) ); ?>"> < </a>
			</span>
		</li>
		<?php
		for ( $i = 1; $i <= $page_count; $i++ ) {
			$display = false;
			$classes = array( 'mvr-pagination' );

			if ( $current_page <= $page_count && $i <= $page_count ) {
				$page_no = $i;
				$display = true;
			} elseif ( $current_page > $page_count ) {
				$overall_count = $current_page - $page_count + $i;

				if ( $overall_count <= $current_page ) {
					$page_no = $overall_count;
					$display = true;
				}
			}

			if ( $current_page === $i ) {
				$classes[] = 'current';
			}

			if ( $display ) {
				?>
				<li>
					<span class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
						<a href="<?php echo esc_url( add_query_arg( array( '_p' => $page_no ), $url ) ); ?>"> <?php echo esc_html( $page_no ); ?> </a>
					</span>
				</li>
				<?php
			}
		}
		?>
		<li>
			<span class="mvr-pagination mvr-next-pagination">
				<a href="<?php echo esc_url( add_query_arg( array( '_p' => $next_page_count ), $url ) ); ?>"> > </a>
			</span>
		</li>
		<li>
			<span class="mvr-pagination mvr-last-pagination">
				<a href="<?php echo esc_url( add_query_arg( array( '_p' => $page_count ), $url ) ); ?>"> >> </a>
			</span>
		</li>
	</ul>
</nav>
<?php
