<?php
/**
 * About Intro Block template.
 *
 * @param array $block The block settings and attributes.
 */

// Load values and assign defaults.
$logos   = get_field( 'company_images' );
$bg = get_field( 'background_color' );
$title = get_field( 'title' );

// Create class attribute allowing for custom "className" and "align" values.
$class_name = 'companies';
if ( ! empty( $block['className'] ) ) {
    $class_name .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
    $class_name .= ' align' . $block['align'];
}

// echo "<pre>";
// print_r($logos);
// exit();
// Build a valid style attribute for background and text colors.
// $style  = implode( '; ', $styles );
?>


<section class="company-slider-main section-4 ctm-border-b ctm-border-t" style="background-color:<?php echo $bg; ?> !important;">
    <h2 class="company-slider-heading"><?php echo $title; ?></h2>
    <div class="section-3" style="background-color:<?php echo $bg; ?> !important;">
        <div class="marquee">
        <ul class="marquee-content">
            <?php if(count($logos) > 0): ?>
                <?php foreach($logos as $key => $logo): ?>
                    <li><div class="slide-img-wrap"><img class="slide-img" src="<?php echo wp_get_attachment_url($logo['ID']) ?>" class="slider-image"></div></li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
        </div>
    </div>
</section>
