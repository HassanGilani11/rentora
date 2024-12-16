
<?php 
$heading = get_field('heading');

?>


<section class="post-container" style="padding-top:300px;padding-bottom:300px">
<div class="d-flex align-items-start is_tab w-100 flex-sm-row flex-column">
        <!--<div class="col-md-3 col-sm-12 ">-->
        <!--    <h4 class="heading-main"><?php //echo(!empty($heading) ? $heading : 'Latest news & insights.') ?></h4>-->
        <!--</div>-->
        <div class="col-md-6 col-sm-12">
            <h4 class="heading-main"><?php echo(!empty($heading) ? $heading : 'The Morgan Energy story so far') ?></h4>
        <?php $categories = get_categories(); ?>
            <div class="nav flex-md-column flex-sm-row nav-pills me-3" id="v-pills-tab" role="tablist" aria-orientation="vertical">
            <?php /*foreach($categories as $key => $category): ?>
                <button class="nav-link py-3 <?php echo $key ==0 ? 'active' : '' ?>" id="v-pills-<?php echo $key; ?>-tab" data-bs-toggle="pill" 
                data-bs-target="#v-pills-<?php echo $key; ?>" type="button" role="tab" 
                aria-controls="v-pills-<?php echo $key; ?>" aria-selected="true">
                    <?php echo $category->name; ?>
                </button>
            <?php endforeach; */?>
            </div>
        </div>
        <div class="col-md-6 col-sm-12">
        <div class="tab-content" id="v-pills-tabContent">


<?php 
// Fetch all terms from the custom taxonomy 'milestone-category'
$categories = get_terms(array(
    'taxonomy' => 'milestone-category',
    'hide_empty' => true, // Only show categories that have posts
));

if (!empty($categories) && !is_wp_error($categories)): ?>
    <div class="timeline-container">
        <div class="timeline"></div> <!-- This is the timeline that moves -->
        <div class="timeline-dot active-dot"></div> <!-- Dot for active post -->
        <div class="timeline-dot inactive-dot"></div> <!-- Dot for inactive post -->
    </div>

    <?php foreach($categories as $key => $category): ?> <!-- Add $key -->
        <!-- Each category tab pane -->
        <div class="tab-pane fade show scroller <?php echo $key == 0 ? 'active' : ''; ?>" id="v-pills-<?php echo $key; ?>" role="tabpanel" aria-labelledby="v-pills-<?php echo $key; ?>-tab">
            <?php 
            // Query posts from custom post type 'milestones' under the specific category
            $args = array(
                'post_type' => 'milestones', // Custom post type
                'tax_query' => array( // Custom taxonomy query
                    array(
                        'taxonomy' => 'milestone-category', // Custom taxonomy name
                        'field'    => 'slug',
                        'terms'    => $category->slug, // Category slug
                    ),
                ),
                'posts_per_page' => 5, // Limit number of posts
                'orderby' => 'date', // Order by date
                'order' => 'DESC' // Sort by descending order
            ); 
            $posts = get_posts($args);

            if ($posts): ?>
                <?php foreach($posts as $post): setup_postdata($post); ?>
                    <div class="post-column" id="post-<?php echo $post->ID; ?>">
                        <h2 class="post-title"><?php echo $post->post_title; ?></h2>
                        <p class="post-description"><?php the_excerpt(); ?></p> <!-- Display post description -->

                        <?php if (has_post_thumbnail($post->ID)): ?>
                            <?php $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'single-post-thumbnail'); ?>
                            <img src="<?php echo esc_url($image[0]); ?>" class="post-featured-image" alt="<?php the_title_attribute(); ?>" /> <!-- Display featured image -->
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php wp_reset_postdata(); // Reset global post object after the loop ?>
            <?php else: ?>
                <p>No posts found in this category.</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No categories found.</p>
<?php endif; ?>


    </div>
        </div>
    </div>
        </section>
        

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
        jQuery(document).ready(function($) {
var lastPost = null;

        function isInViewport($element) {
            var elementTop = $element.offset().top;
            var elementBottom = elementTop + $element.outerHeight();
            var viewportTop = $(window).scrollTop();
            var viewportBottom = viewportTop + $(window).height();

            return elementBottom > viewportTop && elementTop < viewportBottom;
        }

        function handleScroll() {
            var activePost = null;

            $('.post-column').each(function() {
                if (isInViewport($(this))) {
                    $(this).addClass('active');
                    activePost = $(this);
                    if (lastPost) lastPost.removeClass('inactive');
                } else {
                    $(this).removeClass('active');
                    lastPost = $(this);
                }
            });

            if (activePost) {
                updateTimeline(activePost);
            }
        }

        function updateTimeline($activePost) {
            var postOffsetTop = $activePost.offset().top;
            var timelineContainerTop = $('.timeline-container').offset().top;
            var newTimelinePosition = postOffsetTop - timelineContainerTop + ($activePost.outerHeight() / 2);

            $('.timeline').css({
                'top': newTimelinePosition + 'px',
                'transition': 'top 0.5s ease-in-out'
            });
            $('.active-dot').css({
                'top': newTimelinePosition + 'px',
                'transition': 'top 0.5s ease-in-out'
            });

            if (lastPost) {
                var lastPostOffsetTop = lastPost.offset().top;
                var newInactivePosition = lastPostOffsetTop - timelineContainerTop + (lastPost.outerHeight() / 2);

                $('.inactive-dot').css({
                    'top': newInactivePosition + 'px',
                    'transition': 'top 0.5s ease-in-out'
                });
            }
        }

        $(window).on('scroll', handleScroll);
        handleScroll(); // Ensure it runs on page load
    });
</script>

