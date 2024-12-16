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
$anchor1           = get_field( 'button_url' );
$anchor1name       = get_field('button_text');

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



<section class="section-image-with-text <?php echo $class_name; ?>">
    <div class="custom-stack-2 flex-layout-layout">
    <div class="custom-layout text-grp w-layout-cell1 ctm-border-r">
        <div class="div-block-8">
        <h2 class="heading-6 animate-scroll"><?php echo $title; ?></h2>
        <p class="paragraph-2 animate-scroll"><?php echo $description; ?></p>
        <?php if(!is_null($anchor1name )) : ?>
            <a class="anchor-2 animated-btn animate-scroll" href="<?php echo ($anchor1) ?>"><?php echo $anchor1name ?></a>
        <?php endif; ?>

        </div>
    </div>
    <div class="custom-layout img-wrapper">
        <img src="<?php echo wp_get_attachment_url($image['ID']) ?>" loading="lazy" alt="<?php echo $title; ?>" class="image">
    </div>
    </div>
</section>