<?php
$args = array(
    'post_type' => 'milestones', // Change to your custom post type
    'posts_per_page' => -1,
    'orderby' => 'date', // Sort by date
    'order' => 'DESC', // Descending order (latest first)
);

$accordion_query = new WP_Query($args); ?>
<div class="about-mobile-accordion">
    <div class="am-title-wrapper">
        <h4 class="heading-main">The Morgen Energy story so far</h4>
    </div>
    <div class="about-mobile-accordion-items">
        <?php if ($accordion_query->have_posts()) :
            while ($accordion_query->have_posts()) : $accordion_query->the_post(); ?>
                <div class="about-mobile-accordion-item">
                    <div class="am-accordion-header"><?php the_title(); ?></div>
                    <div class="am-accordion-content">
                        <div class="am-accordion-desc"><?php echo wp_trim_words(get_the_content(), 20, '...'); ?></div>
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="am-accordion-img-wrap">
                                <?php $thumbnail_id = get_post_thumbnail_id(); ?>
                                <?php $thumbnail_url = get_the_post_thumbnail_url(); ?>
                                <?php $thumbnail_alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true); ?>
                                <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr($thumbnail_alt); ?>" class="timeline-image"> <!-- Adjust width and height as needed -->
                            </div>
                    <?php endif; ?>

                    </div>
                </div>
            <?php endwhile; ?>
            <?php wp_reset_postdata(); ?>
        <?php endif; ?>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('.am-accordion-header').click(function() {
            $(this).next().slideToggle();
            $(this).toggleClass('active');
        });
    }); 
</script>
<style>
   /* Accordion styles */
   .am-title-wrapper .heading-main {
        font-size: 36px;
        font-weight: 400;
        line-height: 32.4px;
        padding: 64px 20px 48px;
        margin-bottom: 0;
        max-width: 300px;
    }
    .am-title-wrapper{
        border-top: 2px solid #1E3E37;
    }
    .am-accordion-img-wrap .timeline-image {
        width: 100%;
    }
   .about-mobile-accordion-wrap{
        display: none;
    }
    @media screen and (max-width: 991px){
        .about-mobile-accordion-wrap{
            display: block;
        }
    }
    .about-mobile-accordion-item {
        border-bottom: 2px solid #1E3E37;
        border-radius: 0;
    }
    .about-mobile-accordion-item:first-child {
        border-top: 2px solid #1E3E37;
    }
    .am-accordion-header {
        background-color: #D7E6FF;
        padding: 20px 21px;
        cursor: pointer;
        color: #1E3E37;
        font-family: 'TroisMilleRegular';
        font-size: 20px;
        font-weight: 400;
        line-height: 21.6px;
    }
    .am-accordion-content {
        display: none;
        padding: 16px 20px 0;
        background-color: #fff;
    }
    .am-accordion-header:hover,
    .am-accordion-header.active {
        background-color: #fff;
    }
    .am-accordion-desc {
        font-family: "Montserrat", sans-serif;
        font-size: 18px;
        font-weight: 500;
        color: #1E3E37;
        line-height: 25.2px;
        margin-bottom: 32px;
    }
    .am-accordion-header:before {
        content: '';
        width: 14px;
        height: 2px;
        display: block;
        background-color: #000;
        position: absolute;
        right: 22px;
        top: 30px;
    }
    .am-accordion-header {
        position: relative;
    }
    .am-accordion-header:after {
        content: '';
        width: 2px;
        height: 14px;
        display: block;
        background-color: #000;
        position: absolute;
        right: 28px;
        top: 24px;
    }
    .am-accordion-header.active:after {
        display: none;
    }
</style>