<?php
/**
 * About Intro Block template.
 *
 * @param array $block The block settings and attributes.
 */

// Load values and assign defaults.
$logos   = get_field( 'infinite_slider_images' );

// Create class attribute allowing for custom "className" and "align" values.
$class_name = 'slider';
if ( ! empty( $block['className'] ) ) {
    $class_name .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
    $class_name .= ' align' . $block['align'];
}

?>
<!-- 
<div class="bg">
    <section class="container-fluid p-0 overflow-hidden">
    <div class="marquee2">
    <ul class="marquee2-content"> -->
<div class="gallery">
        <?php if(count($logos) > 0): ?>
            <?php foreach($logos as $key => $logo): ?>
                <img class="loop-image" src="<?php echo wp_get_attachment_url($logo['ID']) ?>">
            <?php endforeach; ?>
        <?php endif; ?>
</div>
<!--     </ul>
    </div>
    </section>
</div> -->
