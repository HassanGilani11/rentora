<?php

$title = get_field('title');
$side_image = get_field('side_image');

$latestpost_arg = array(
    'post_type' => 'industry_project',
    'posts_per_page' => 6,
    'orderby' => 'date',
    'order' => 'ASC',
);
$posts = get_posts($latestpost_arg);

$class_name = 'industry_project';
if ( ! empty( $block['className'] ) ) {
    $class_name .= ' ' . $block['className'];
}

?>
<section class="container-fluid p-0 overflow-hidden <?= $class_name; ?>">
    <div class="row">
        <div class="col-md-6 border border-dark p-0">
            <img width="100%" height="100%" src="<?= wp_get_attachment_url($side_image['id']) ?>" alt="<?= $side_image['id']?>">
        </div>
        <div class="col-md-6 industry-second-column border border-dark background-change-light">
            <h6 class="industry-title animate-scroll"><?= $title ?></h6>
            <div class="row pt-4">
                <?php foreach($posts as $key => $post): ?>
                    <div class="col-md-6 pt-4">
                        <h6 class="industry-card-count animate-scroll"><?= '0'.$key+1 ?></h6>
                        <h6 class="industry-card-title animate-scroll"><?= $post->title ?></h6>
                        <p class="industry-card-description animate-scroll"><?= $post->description ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>