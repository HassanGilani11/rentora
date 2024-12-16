<?php
/**
 * Testimonial Block template.
 *
 * @param array $block The block settings and attributes.
 */

// Load values and assign defaults.
$background_color_card_1 = get_field( 'background_color_card_1' );
$background_color_card_2 = get_field( 'background_color_card_2' );
$background_color_card_3 = get_field( 'background_color_card_3' );
$heading_card_1 = get_field( 'heading_card_1' );
$heading_card_2 = get_field( 'heading_card_2' );
$heading_card_3 = get_field( 'heading_card_3' );
$description_card_1 = get_field( 'description_card_1' );
$description_card_2 = get_field( 'description_card_2' );
$description_card_3 = get_field( 'description_card_3' );
$button_text_card_1 = get_field( 'button_text_card_1' );
$button_text_card_2 = get_field( 'button_text_card_2' );
$button_text_card_3 = get_field( 'button_text_card_3' );
$button_url_card_1 = get_field( 'button_url_card_1' );
$button_url_card_2 = get_field( 'button_url_card_2' );
$button_url_card_3 = get_field( 'button_url_card_3' );
$image_card_1 = get_field( 'image_card_1' );
$image_card_2 = get_field( 'image_card_2' );
$image_card_3 = get_field( 'image_card_3' );


// Create class attribute allowing for custom "className" and "align" values.
$class_name = 'cta';
if ( ! empty( $block['className'] ) ) {
    $class_name .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
    $class_name .= ' align' . $block['align'];
}

?>

<div class="container-fluid cta-section">
    <div class="row">
        <div class="col-md-4 px-0 col-sm-12">
            <div class="card" style="background-color: <?php echo (!empty($background_color_card_1) ? $background_color_card_1 : "#D7E6FF"); ?>">
                <div class="card-body">
                    <h5 class="card-title animate-scroll"><?php echo $heading_card_1 ?></h5>
                    <p class="card-text animate-scroll"><?php echo $description_card_1 ?></p>
                    <div class="cta-btn">
                    <a class="animate-scroll" href="<?php echo (!empty($button_url_card_1) ? $button_url_card_1 : "#"); ?>"><?php echo $button_text_card_1 ?></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 px-0 col-sm-12">
            <?php if(!empty($image_card_1)): ?>
                <img class="car-image-cst" src="<?php echo $image_card_1; ?>" />
            <?php endif; ?>
        </div>
        <div class="col-md-4 px-0 col-sm-12">
        <div class="card" style="background-color: <?php echo (!empty($background_color_card_1) ? $background_color_card_2 : "#D7E6FF"); ?>">
                <div class="card-body">
                    <h5 class="card-title animate-scroll"><?php echo $heading_card_2 ?></h5>
                    <p class="card-text animate-scroll"><?php echo $description_card_2 ?></p>
                    <div class="cta-btn">
                    <a class="animate-scroll" href="<?php echo (!empty($button_url_card_2) ? $button_url_card_2 : "#"); ?>"><?php echo $button_text_card_2 ?></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 px-0 col-sm-12">
        <?php if(!empty($image_card_2)): ?>
                <img class="car-image-cst" src="<?php echo $image_card_2; ?>" />
            <?php endif; ?>
        </div>
        <div class="col-md-4 px-0 col-sm-12">
        <div class="card" style="background-color: <?php echo (!empty($background_color_card_1) ? $background_color_card_3 : "#D7E6FF"); ?>">
                <div class="card-body">
                    <h5 class="card-title animate-scroll"><?php echo $heading_card_3 ?></h5>
                    <p class="card-text animate-scroll"><?php echo $description_card_3 ?></p>
                    <div class="cta-btn">
                    <a class="animate-scroll" href="<?php echo (!empty($button_url_card_3) ? $button_url_card_3 : "#"); ?>"><?php echo $button_text_card_3 ?></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 px-0 col-sm-12">
        <?php if(!empty($image_card_3)): ?>
                <img class="car-image-cst" src="<?php echo $image_card_3; ?>" />
            <?php endif; ?>
        </div>
</div>