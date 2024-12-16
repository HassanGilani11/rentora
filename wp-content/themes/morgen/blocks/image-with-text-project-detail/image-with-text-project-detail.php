<?php
/**
 * Image with Text Project Detail Template
 *
 * @param array $block The block settings and attributes.
 */

// Load values and assign defaults.
$title_one            = get_field( 'title_one' );
$description_one      = get_field( 'description_one' );
$image_one            = get_field( 'image_one' );
$title_two            = get_field( 'title_two' );
$description_two      = get_field( 'description_two' );
$image_two            = get_field( 'image_two' );
$url_one              = get_field( 'url_one' );
$url_two              = get_field( 'url_two' );

// Create class attribute allowing for custom "className" and "align" values.
$class_name = 'image-with-text-project-detail';
if ( ! empty( $block['className'] ) ) {
    $class_name .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
    $class_name .= ' align' . $block['align'];
}
?>

<section class="image-with-text-project-detail col-12">
    <div class="wrapper"> <!-- Row to wrap the columns -->
        <!-- First Column (Image and Content Block) -->
        <div class="col-6"> <!-- Takes 50% width on desktop, 100% on mobile -->
            <div class="image-with-text-project-detail-layout-container">
                <!-- Image Section -->
                <div class="col-12">
                     <img src="<?php echo wp_get_attachment_url($image_one['ID']) ?>" loading="lazy" alt="<?php echo esc_attr($title_one); ?>" class="image-with-text-project-detail-image">
                </div>
                <!-- Text and Button Section -->
                <div class="col-12 text-box">
                    <div class="image-with-text-project-detail-layout w-layout-cell1">
                        <div class="image-with-text-project-detail-content">
                            <h2 class="image-with-text-project-detail-heading"><?php echo esc_html( $title_one ); ?></h2>
                            <p class="image-with-text-project-detail-paragraph" id="project-description"><?php echo esc_html( $description_one ); ?></p>
                        </div>
                        <div class="image-with-text-project-detail-content">
                        <?php if (!is_null($url_one)) : ?>
                            <a href="<?php echo esc_url( $url_one ); ?>" class="image-with-text-project-detail-button w-button">
                        <?php endif; ?><i class="fas fa-arrow-right"></i>
							</a>
                        </div>						
                    </div>
                </div>
            </div>
        </div>

        <!-- Second Column (Image and Content Block) -->
        <div class="col-6"> <!-- Takes 50% width on desktop, 100% on mobile -->
            <div class="image-with-text-project-detail-layout-container">
                <!-- Image Section -->
                <div class="col-12 image-box">
                     <img src="<?php echo wp_get_attachment_url($image_two['ID']) ?>" loading="lazy" alt="<?php echo esc_attr($title_two); ?>" class="image-with-text-project-detail-image-second-box">
                </div>
                <!-- Text and Button Section -->
                <div class="col-12 text-box">
                    <div class="image-with-text-project-detail-layout w-layout-cell1">
                        <div class="image-with-text-project-detail-content">
                            <h2 class="image-with-text-project-detail-heading"><?php echo esc_html( $title_two ); ?></h2>
                            <p class="image-with-text-project-detail-paragraph" id="project-description-two"><?php echo esc_html( $description_two ); ?></p>
                        </div>
                        <div class="image-with-text-project-detail-content">
                        <?php if (!is_null($url_two)) : ?>
                            <a href="<?php echo esc_url( $url_two ); ?>" class="image-with-text-project-detail-button w-button">
                        <?php endif; ?><i class="fas fa-arrow-right"></i>
							</a>
                        </div>						
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
