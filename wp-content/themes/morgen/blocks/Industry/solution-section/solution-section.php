<?php

$title = get_field('title');
$icon1 = get_field('card_icon_1');
$heading1 = get_field('card_title_1');
$description1 = get_field('card_description_1');
$icon2 = get_field('card_icon_2');
$heading2 = get_field('card_title_2');
$description2 = get_field('card_description_2');

$class_name = 'solution_section';
if ( ! empty( $block['className'] ) ) {
    $class_name .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
    $class_name .= ' align' . $block['align'];
}

?>

<section class="container-fluid py-8 px-auto <?= $class_name ?>">
    <div class="container">
		<div class="row">
        <div class="col-md-4">
            <h4 class="solution-main-heading animate-scroll"><?= $title; ?></h4>
        </div>
        <div class="col-md-4">
            <img src="<?php echo wp_get_attachment_url($icon1['ID']) ?>" class="card-icon pt-3 animate-scroll" alt="card-icon">
            <h6 class="card-heading pt-3 animate-scroll"><?= $heading1; ?></h6>
            <p class="card-description pt-3 animate-scroll"><?= $description1; ?></p>
        </div>
        <div class="col-md-4">
            <img src="<?php echo wp_get_attachment_url($icon2['ID']) ?>" class="card-icon pt-3 animate-scroll" alt="card-icon">
            <h6 class="card-heading pt-3 animate-scroll"><?= $heading2; ?></h6>
            <p class="card-description pt-3 animate-scroll"><?= $description2; ?></p>
        </div>
    </div>
	</div>
</section>