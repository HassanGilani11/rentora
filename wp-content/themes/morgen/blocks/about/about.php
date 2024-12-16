<?php
/**
 * Testimonial Block template.
 *
 * @param array $block The block settings and attributes.
 */

// Load values and assign defaults.
$title             = get_field( 'about_title' );
$description       = get_field( 'about_description' );
$image             = get_field( 'side_image' );
$anchor1  = get_field( 'anchor_url' );
$backgroundImage  = get_field('background_image');
$anchor1name = get_field('anchor_title');
$quote_attribution = '';

// Create class attribute allowing for custom "className" and "align" values.
$class_name = 'about';
if ( ! empty( $block['className'] ) ) {
    $class_name .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
    $class_name .= ' align' . $block['align'];
}
if ( $backgroundImage ) {
    $class_name .= ' has-custom-acf-bg-image-about';
}

// Build a valid style attribute for background and text colors.
$styles = 'background-image: url(' . $backgroundImage['url'] .');';
// $style  = implode( '; ', $styles );
?>

<div class="about-container <?php echo esc_attr( $class_name ); ?>">
	<div class="overlay" style="<?php echo esc_attr( $styles ); ?>"></div>
    <div class="about-row">
        <div class="about-col-6 about-sec-section">
            <div class="testimonial__col">
                    <h1 class="about-title animate animate-scroll"><?php echo esc_html( $title ); ?></h1>
                    <p class="about-description animate animate-scroll"><?php echo wp_kses_post( $description ); ?></p>
                    <?php if($anchor1name): ?>
<!--                         <div class="anchors about-anchors "> -->
                            <a class="anchor-1 animate animate-scroll" href="<?php echo ($anchor1) ?>"><?php echo $anchor1name ?></a>
<!--                         </div> -->
                    <?php endif; ?>
            </div>
        </div>
        <div class="about-col-6">
        <?php if ( $image ) : ?>
            <figure class="about_side_image animate">
                <?php echo wp_get_attachment_image( $image['ID'], 'full', '', array( 'class' => 'testimonial__img' ) ); ?>
            </figure>
        <?php endif; ?>
        </div>
    </div>
</div>