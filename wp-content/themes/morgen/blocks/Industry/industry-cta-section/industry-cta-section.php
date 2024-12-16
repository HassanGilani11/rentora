<?php



$cta_text = get_field('cta_heading');
$background_image = get_field('bg_image');

$class_name = 'industry_cta_section';
if ( ! empty( $block['className'] ) ) {
    $class_name .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
    $class_name .= ' align' . $block['align'];
}

?>

<section style="background-image: url(<?= wp_get_attachment_url($background_image['ID']) ?>);"
class="container-fluid p-0 vh-100 align-items-center d-flex justify-content-center industry_cta_section_inner">
        <h2 class="cta-industry-heading animate-scroll"><?= $cta_text; ?></h2>
</section>