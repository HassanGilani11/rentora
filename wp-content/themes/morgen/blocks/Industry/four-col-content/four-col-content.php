<?php

$icon1 = get_field('icon_1');
$heading1 = get_field('heading_1');
$description1 = get_field('description_1');
$bg1 = get_field('bg_1');
$icon2 = get_field('icon_2');
$heading2 = get_field('heading_2');
$description2 = get_field('description_2');
$bg2 = get_field('bg_2');
$icon3 = get_field('icon_3');
$heading3 = get_field('heading_3');
$description3 = get_field('description_3');
$bg3 = get_field('bg_3');
$icon4 = get_field('icon_4');
$heading4 = get_field('heading_4');
$description4 = get_field('description_4');
$bg4 = get_field('bg_4');

$class_name = 'four_col';
if ( ! empty( $block['className'] ) ) {
    $class_name .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
    $class_name .= ' align' . $block['align'];
}

?>

<section class="container-fluid p-0 scroll-snap-container">
    <div class="row scroll-snap">
        <div class="col-md-3 scroll-snap-child" style="background-color: <?= isset($bg1) ? $bg1 : 'transparent' ?>;">
            <div class="d-flex align-items-start flex-column card-inner">
                    <div class="mb-auto">
                    <?php if(!empty($icon1)): ?>
                        <img src="<?php echo wp_get_attachment_url($icon1['ID']) ?>" class="card-icon pt-3 animate-scroll" alt="card-icon">
                    <?php else: ?>
                        <h6 class="card-counting animate-scroll">01</h6>
                    <?php endif; ?>
                    </div>
                        <div>
                            <h6 class="card-heading pt-3 animate-scroll"><?= $heading1; ?></h6>
                            <p class="card-description animate-scroll"><?= $description1; ?></p>
                        </div>
            </div>
        </div>
        <div class="col-md-3 scroll-snap-child" style="background-color: <?= isset($bg2) ? $bg2 : 'transparent' ?>;">
            <div class="d-flex align-items-start flex-column card-inner">
                    <div class="mb-auto">
                    <?php if(!empty($icon2)): ?>
                        <img src="<?php echo wp_get_attachment_url($icon2['ID']) ?>" class="card-icon pt-3 animate-scroll" alt="card-icon">
                    <?php else: ?>
                        <h6 class="card-counting animate-scroll">02</h6>
                    <?php endif; ?>
                    </div>
                        <div>
                            <h6 class="card-heading pt-3 animate-scroll"><?= $heading2; ?></h6>
                            <p class="card-description animate-scroll"><?= $description2; ?></p>
                        </div>
            </div>
        </div>
        <div class="col-md-3 scroll-snap-child" style="background-color: <?= isset($bg3) ? $bg3 : 'transparent' ?>;">
            <div class="d-flex align-items-start flex-column card-inner">
                <div>
                <?php if(!empty($icon3)): ?>
                    <img src="<?php echo wp_get_attachment_url($icon3['ID']) ?>" class="card-icon pt-3 animate-scroll" alt="card-icon">
                    <?php else: ?>
                        <h6 class="card-counting animate-scroll">03</h6>
                        <?php endif; ?>
                </div>
                        <div>
                            <h6 class="card-heading pt-3 animate-scroll"><?= $heading3; ?></h6>
                            <p class="card-description animate-scroll"><?= $description3; ?></p>
                        </div>
            </div>
        </div>
        <div class="col-md-3 scroll-snap-child" style="background-color: <?= isset($bg4) ? $bg4 : 'transparent' ?>;">
            <div class="d-flex align-items-start flex-column card-inner">
                <div>
                <?php if(!empty($icon4)): ?>
                    <img src="<?php echo wp_get_attachment_url($icon4['ID']) ?>" class="card-icon pt-3 animate-scroll" alt="card-icon">
                    <?php else: ?>
                        <h6 class="card-counting animate-scroll">04</h6>
                        <?php endif; ?>
                </div>
                        <div>
                            <h6 class="card-heading pt-3 animate-scroll"><?= $heading4; ?></h6>
                            <p class="card-description animate-scroll"><?= $description4; ?></p>
                        </div>
            </div>
        </div>
    </div>
</section>