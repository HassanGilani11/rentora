<?php
/**
 * Career Video template.
 *
 * @param array $block The block settings and attributes.
 */

// Load values and assign defaults.
$sectiontitle = get_field('section_title'); // Getting the ACF field value

$title = get_field('title');
$video_script = get_field('video_script');
// $name = get_field('name');
// $position = get_field('position');
// $thumbnail = get_field('thumbnail');

// Create class attribute allowing for custom "className" and "align" values.
$class_name = 'career-video';
if (!empty($block['className'])) {
    $class_name .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $class_name .= ' align' . $block['align'];
}
?>


<section class="<?php echo $class_name; ?>">
    <h2 class="wp-block-heading custom-subheading animate-scroll"><?php echo $title; ?></h2>
    <div class="cvs-inner">
        <div class="cvs-vid-wrap">
            <?php if($video_script) :  ?>
                <?php echo $video_script; ?>
            <?php else: ?>
                <img src="/wp-content/uploads/2024/08/Morgen-launches.png" alt="Placeholder">
            <?php endif; ?>
        </div>
    </div>
</section>