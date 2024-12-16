<?php
/**
 * Store Header.
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/stores/store-header.php.
 *
 * @package Multi Vendor Marketplace\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit accessed directly.
}
?>
<div class="mvr-single-store-header-wrapper">

	<div class="mvr-single-store-left-col">
		<div class="mvr-single-store-banner">
			<img class="mvr-store-banner" src="<?php echo esc_url( $banner ); ?>" width="300" height ="100"/>
		</div>
		<div class="mvr-single-store-profile">
			<img class="mvr-store-logo" src="<?php echo esc_url( $logo ); ?>" width="60" height ="60"/>
		</div>
	</div>

	<div class="mvr-single-store-right-col">
		<div class="mvr-single-store-profile-info">
			<ul class="mvr-single-store-info">
				<li class="mvr-single-store-vendor-info">
					<?php echo esc_attr( $vendor_obj->get_shop_name() ); ?>
				</li>
				<?php if ( 'yes' === get_option( 'mvr_settings_disp_vendor_address', 'yes' ) ) : ?>
					<li class="mvr-single-store-address">
						<?php mvr_get_formated_vendor_address( $vendor_obj ); ?>
					</li>
				<?php endif; ?>

				<?php if ( 'yes' === get_option( 'mvr_settings_disp_vendor_email', 'yes' ) ) : ?>
					<li class="mvr-single-store-email">
						<?php echo esc_attr( $vendor_obj->get_email() ); ?>
					</li>
				<?php endif; ?>

				<?php if ( 'yes' === get_option( 'mvr_settings_disp_vendor_contact', 'yes' ) ) : ?>
					<li class="mvr-single-store-phone">
						<?php echo esc_attr( $vendor_obj->get_phone() ); ?>
					</li>
				<?php endif; ?>

				<?php if ( 'yes' === get_option( 'mvr_settings_disp_vendor_review', 'yes' ) ) : ?>
						<li class="mvr-single-store-rating">
							<?php
							if ( $vendor_obj->get_average_rating() ) :
								echo esc_attr( $vendor_obj->get_average_rating() );
							else :
								esc_html_e( 'No reviews', 'multi-vendor-marketplace' );
							endif;
							?>
						</li>
				<?php endif; ?>

				<?php if ( 'yes' === get_option( 'mvr_settings_disp_vendor_social_link', 'yes' ) && $vendor_obj->has_social_link() ) : ?>
					<li class="mvr-single-store-social-info">
						<h4><?php esc_html_e( 'FOLLOW US', 'multi-vendor-marketplace' ); ?></h4>
						<?php foreach ( mvr_get_available_vendor_social_links( $vendor_obj ) as $key => $social_link ) : ?>
							<a class="mvr-social-link mvr-<?php echo esc_attr( $key ); ?>" href="<?php echo esc_url( $social_link ); ?>" target="_blank"></a>
						<?php endforeach; ?>
					</li>
				<?php endif; ?>
			</ul>
		</div>
	</div>
</div>
