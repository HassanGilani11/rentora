<?php
/**
 * CTA Project Detail template.
 *
 * @param array $block The block settings and attributes.
 */

// Load values and assign defaults.
$sectiontitle = get_field('section_title'); // Getting the ACF field value
$description  = get_field( 'cta_description' );
$anchor_label = get_field('anchor_label');
$anchor_link  = get_field('anchor_link');
$phone        = get_field('phone');

// Create class attribute allowing for custom "className" and "align" values.
$class_name = 'cta';
if (!empty($block['className'])) {
    $class_name .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $class_name .= ' align' . $block['align'];
}
?>

<section class="section-<?php echo esc_attr($class_name); ?>">
    <div class="cta company-heading animate-scroll"><?php echo esc_html($sectiontitle); ?></div>
    
    <!-- Description -->
    <p class="cta-description animate animate-scroll"><?php echo wp_kses_post($description); ?></p>
    
    <!-- Contact Details (Email and Phone) -->
    <div class="cta-contact">
        <?php if ($anchor_label && $anchor_link) : ?>
            <a class="url animate-scroll animated-btn" href="<?php echo $anchor_link; ?>"><?php echo esc_html($anchor_label); ?></a>
        <?php endif; ?>
        
        <?php if (!empty($phone)) : ?>
            <a class="url animate-scroll animated-btn" href="tel:<?php echo esc_attr($phone); ?>"><?php echo esc_html($phone); ?></a>
        <?php endif; ?>
    </div>
    
</section>
