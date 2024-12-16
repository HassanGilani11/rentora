<?php
/**
 * Testimonial Block template.
 *
 * @param array $block The block settings and attributes.
 */

// Load values and assign defaults.
$title             = get_field( 'hero_title' );
$description       = get_field( 'description' );
$image             = get_field( 'hero_image' );
$anchor1  = get_field( 'anchor_1' );
$anchor2        = get_field( 'anchor_2' );
$backgroundImage  = get_field('background_image');
$anchor1name = get_field('anchor_1_name');
$anchor2name = get_field('anchor_2_name');
$quote_attribution = '';

// Create class attribute allowing for custom "className" and "align" values.
$class_name = 'hero';
if ( ! empty( $block['className'] ) ) {
    $class_name .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
    $class_name .= ' align' . $block['align'];
}
if ( $backgroundImage ) {
    $class_name .= ' has-custom-acf-bg-image';
}

// Build a valid style attribute for background and text colors.
$styles = 'background-image: url(' . $backgroundImage['url'] .');';
// $style  = implode( '; ', $styles );

$popup_intro = get_field( 'wistia_watch_intro_embed_url' );
?>
<div class="hero-container <?php echo esc_attr( $class_name ); ?>" style="<?php echo esc_attr( $styles ); ?> position: relative; z-index: 0;">
	<div class="hero-overlay" style="<?php echo esc_attr( $styles ); ?> z-index: 0;"></div>
    <div class="hero-row">
        <div class="hero-col-6 hero-sec-section">
            <div class="testimonial__col">
                    <h1 class="hero-title animate animate-heading"><?php echo $title; ?></h1>
                    <p class="hero-description animate animate-description"><?php echo wp_kses_post( $description ); ?></p>
                    <?php if($anchor1 || $anchor2): ?>
                        <div class="anchors animate animate-button">
                            <a class="anchor-1 animated-btn" href="<?php echo ($anchor1) ?>"><?php echo $anchor1name ?></a>
                            <a class="anchor-2 popup-video animated-btn" href="https://fast.wistia.com/embed/iframe/<?php echo $popup_intro; ?>?version=v2&controlsVisibleOnLoad=true&autoPlay=true"><?php echo $anchor2name ?></a>
                        </div>
                    <?php endif; ?>
            </div>
        </div>
        <div class="hero-col-6 border-left">
        <?php if ( $image ) : ?>
            <div class="full-width-video">
                <script src="https://fast.wistia.com/embed/medias/<?php echo $image; ?>.jsonp?autoPlay=true&muted=true&playbar=false&volumeControl=true" async></script>
                <script src="https://fast.wistia.com/assets/external/E-v1.js" async></script>
                <div class="wistia_embed wistia_async_<?php echo $image; ?> videoFoam=true" style="height:349px;width:620px">&nbsp;</div>
            </div>
            <script>
                // Listen for the window load event
                window.addEventListener('load', function() {
                    // Find the Wistia video player and trigger autoplay
                    wistiaEmbed = Wistia.api('<?php echo $image; ?>');
                    wistiaEmbed.play();
                });
            </script>
        <?php endif; ?>
        </div>
    </div>
</div>
<style>
.mfp-content {
    max-width: 1440px !important;
    height: 820px !important;
}
.full-width-video {
    width: 100%;
    height: 100%;
	z-index: -1;
}
.wistia_embed {
    width: 100% !important;
    height: 100% !important;
}
.hero-container .w-vulcan-v2-button.click-for-sound-btn,
.hero-container .w-bottom-bar.w-css-reset {
    display: none !important;
}
</style>