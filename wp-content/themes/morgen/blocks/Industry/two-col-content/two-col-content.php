<?php

$title             = get_field( 'content_title' );
$description       = get_field( 'content_description' );


$class_name = 'two-col';
if ( ! empty( $block['className'] ) ) {
    $class_name .= ' ' . $block['className'];
}
?>


<section class="container-fluid my-8 two-col-sec <?= $class_name; ?>">
    <div class="container">
    <div class="row">
        <?php if(!empty($description)) : ?>
        <div class="col-md-6 col-12">
            <h4 class="col-content-heading animate-scroll"><?= $title; ?></h4>
        </div>
        <div class="col-md-6 col-12">
            <p class="col-content-description animate-scroll"><?= $description; ?></p>
        </div>
        <?php else: ?>
            <div class="col-md-10 col-12">
                <h4 class="col-content-heading animate-scroll"><?= $title; ?></h4>
            </div>
        <?php endif; ?>
    </div>
    </div>
</section>