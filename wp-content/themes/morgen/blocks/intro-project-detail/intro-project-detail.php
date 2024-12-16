<?php
/**
 * Project Intro Block template.
 *
 * @param array $block The block settings and attributes.
 */

// Load values and assign defaults.
$url        = get_field('video_url');
$description = get_field('description'); // Ensure this variable is defined

// Create class attribute allowing for custom "className" and "align" values.
$class_name = 'project-detail';
if (!empty($block['className'])) {
    $class_name .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $class_name .= ' align' . $block['align'];
}
?>
<?php 		
    $image = get_field('background_image');
	if( !empty($image) ): ?>
        <div class="video-container <?php echo esc_attr($class_name); ?>" style="background-image: url(<?php echo $image['url']; ?>);background-size: cover; background-position: center;">
            <div class="video-row" style="z-index: 10;">
                <div class="video-col-12">
                    <p class="video-description animate"><?php echo wp_kses_post($description); ?></p>
                    <?php if (!empty($url)) : ?>
                        <div class="video">
                            <a class="popup-video" href="https://fast.wistia.com/embed/iframe/<?php echo $url; ?>?version=v2&controlsVisibleOnLoad=true&autoPlay=true">
                                <img src="/wp-content/uploads/2024/09/Play-Button.png" alt="Play Video" class="play-icon"> <!-- Play icon -->
                            </a>
                        </div>
                    <?php endif; ?>      
                </div>
            </div>
        </div>
<?php endif; ?>
<style>
.mfp-content {
    max-width: 1440px !important;
    height: 820px !important;
}
.full-width-video {
    width: 100%;
    height: 100%;
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