<?php
/**
 * Companies Slider About Us template.
 *
 * @param array $block The block settings and attributes.
 */

// Load values and assign defaults.
$logos   = get_field( 'company_images' );

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


<section class="section-4">
      <div class="text-block-4 company-heading animate-scroll">Our Valued Memberships</div>
</section>
<div class="bg-slider">
    <section data-w-id="c993aa5a-4506-3fcb-23b7-44f4f128f59c" class="section-3">
    <div class="marquee">
    <ul class="marquee-content">
        <?php if(count($logos) > 0): ?>
            <?php foreach($logos as $key => $logo): ?>
                <li><img class="slide-img" src="<?php echo wp_get_attachment_url($logo['ID']) ?>" class="slider-image"></li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
    </div>
    </section>
</div>