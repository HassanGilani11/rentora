<?php
/**
 * About Intro Block template.
 *
 * @param array $block The block settings and attributes.
 */

// Load values and assign defaults.
$title             = get_field( 'heading' );
$description       = get_field( 'description' );
$image             = get_field( 'side_image' );
$anchor1  = get_field( 'button_url' );
$anchor1name = get_field('button_text');

// Create class attribute allowing for custom "className" and "align" values.
$class_name = 'about';
if ( ! empty( $block['className'] ) ) {
    $class_name .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
    $class_name .= ' align' . $block['align'];
}

// Build a valid style attribute for background and text colors.
// $style  = implode( '; ', $styles );
?>



<section class="section-5 r-border-t">
    <div id="w-node-_9342ce6d-22a1-941b-a87a-983f78d4ee9f-fc02dae8" class="custom-stack-2 flex-layout-layout">
    <div id="w-node-_9342ce6d-22a1-941b-a87a-983f78d4eea0-fc02dae8" class="custom-layout">
        <img src="<?php echo wp_get_attachment_url($image['ID']) ?>" loading="lazy" alt="<?php echo $title; ?>" class="image">
    </div>
    <div id="w-node-_9342ce6d-22a1-941b-a87a-983f78d4eea1-fc02dae8" class="custom-layout w-layout-cell1">
        <div class="div-block-8">
        <h2 class="heading-6 animate-scroll"><?php echo $title; ?></h2>
        <p class="paragraph-2 animate-scroll"><?php echo $description; ?></p>
        <?php if(!is_null($anchor1name )) : ?>
        <a href="<?php echo $anchor1; ?>" class="button-2 w-button animate-scroll"><?php echo $anchor1name; ?></a>
        <?php endif; ?>
        </div>
    </div>
    </div>
</section>