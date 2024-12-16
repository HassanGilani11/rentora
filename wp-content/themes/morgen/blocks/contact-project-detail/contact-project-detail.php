<?php
/**
 * Testimonial Block template.
 *
 * @param array $block The block settings and attributes.
 */

// Load values and assign defaults.
$title             = get_field( 'section_title' );
$description       = get_field( 'description' );
$email           = get_field( 'email' );
$phone           = get_field( 'phone' );
$backgroundImage   = get_field('background_image');
$form_embed = get_field('form_embed');

$quote_attribution = '';

// Create class attribute allowing for custom "className" and "align" values.
$class_name = 'contact-project-detail-hero';
$bg_cover = 'contact-project-detail-hero';

if ( ! empty( $block['className'] ) ) {
    $class_name .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
    $class_name .= ' align' . $block['align'];
}
if ( $backgroundImage ) {
    $bg_cover .= ' contact-project-detail-has-custom-acf-bg-image';
}

// Build a valid style attribute for background and text colors.
$styles = 'background-image: url(' . $backgroundImage['url'] . ');';
// $style  = implode( '; ', $styles );
?>

<div class="contact-project-detail-hero-container <?php echo esc_attr( $class_name ); ?>">
    <div class="contact-project-detail-hero-row">
        <div class="contact-project-detail-hero-col-6 contact-project-detail-hero-sec-section">
            <div class="contact-project-detail-testimonial__col">
                <h1 class="contact-project-detail-hero-title animate"><?php echo esc_html( $title ); ?></h1>
                <p class="contact-project-detail-hero-description animate"><?php echo wp_kses_post( $description ); ?></p>
                    <?php if ($email || $phone): ?>
                        <div class="contact-project-detail-anchors animate col-12">
                            <?php if ($email): ?>
                                <div class="contact-project-detail-anchor-group">
                                    <span class="subtitle">Email</span>
                                    <a class="contact-project-detail-anchor-1" href="mailto:<?php echo esc_attr($email); ?>">
                                        <?php echo esc_html($email); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($phone): ?>
                                <div class="contact-project-detail-anchor-group">
                                    <span class="subtitle">Phone</span>
                                    <a class="contact-project-detail-anchor-2" href="tel:<?php echo esc_attr($phone); ?>">
                                        <?php echo esc_html($phone); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

            </div>
        </div>
        <div class="contact-project-detail-hero-col-6 <?php echo esc_attr( $bg_cover ); ?>" style="<?php echo esc_attr( $styles ); ?>">
            <?php if ($form_embed) : ?>
                <div class="contact-project-detail-form-embed animate">
                    <?php echo $form_embed; // Output the embedded form ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
