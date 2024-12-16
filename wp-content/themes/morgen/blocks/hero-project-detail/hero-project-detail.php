<?php
/**
 * Hero About Us Template.
 *
 * @param array $block The block settings and attributes.
 */

// Load values and assign defaults.
$title             = get_field( 'project_title' );
$description       = get_field( 'project_description' );
$subtitle             = get_field( 'project_subtitle' );
$quote_attribution = '';

// Create class attribute allowing for custom "className" and "align" values.
$class_name = 'project-detail';
if ( ! empty( $block['className'] ) ) {
    $class_name .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
    $class_name .= ' align' . $block['align'];
}


?>

<div class="hero-container <?php echo esc_attr( $class_name ); ?>" style="">
    <div class="hero-row-project-detail">
        <div class="hero-col-6 hero-sec-section">
            <div class="testimonial__col">
                    <p class="hero-description animate subtitle animate-scroll"><?php echo wp_kses_post( $subtitle ); ?></p>                    
                    <h1 class="hero-title animate animate-scroll"><?php echo esc_html( $title ); ?></h1>

            </div>
        </div>
        <div class="hero-col-6">
                    <p class="hero-description animate animate-scroll"><?php echo wp_kses_post( $description ); ?></p>
        </div>
    </div>
</div>