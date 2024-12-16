<?php
/**
 * Companies Project Detail template.
 *
 * @param array $block The block settings and attributes.
 */

// Load values and assign defaults.
$sectiontitle = get_field('section_title'); // Getting the ACF field value

// Initialize an array to hold icon data.
$icons = [];

// Loop through icon fields and store them in the array.
for ($i = 1; $i <= 5; $i++) {
    $icons[] = [
        'image' => get_field("icon_image_$i"),
        'title' => get_field("icon_title_$i"),
        'description' => get_field("icon_description_$i"),
    ];
}

// Create class attribute allowing for custom "className" and "align" values.
$class_name = 'companies';
if (!empty($block['className'])) {
    $class_name .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $class_name .= ' align' . $block['align'];
}
?>


<section class="section-4 <?php echo esc_attr($class_name); ?>">
    <div class="text-block-4 company-heading animate-scroll"><?php echo esc_html($sectiontitle); ?></div>
    <div class="icon-container">
        <?php foreach ($icons as $icon) : ?>
            <?php if (!empty($icon['image'])) : // Check if the icon image is set ?>
                <div class="icon-item">
                    <img src="<?php echo esc_url($icon['image']['url']); ?>" alt="<?php echo esc_attr($icon['title']); ?>" class="icon-image animate-scroll" />
                    
                    <?php if (!empty($icon['title'])) : ?>
                        <h4 class="icon-title animate-scroll"><?php echo esc_html($icon['title']); ?></h4>
                    <?php endif; ?>
                    
                    <?php if (!empty($icon['description'])) : ?>
                        <p class="icon-description animate-scroll"><?php echo esc_html($icon['description']); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</section>

<div class="bg-slider">
    <section data-w-id="c993aa5a-4506-3fcb-23b7-44f4f128f59c" class="section-3">
        <!-- Slider content can be added here -->
    </section>
</div>
