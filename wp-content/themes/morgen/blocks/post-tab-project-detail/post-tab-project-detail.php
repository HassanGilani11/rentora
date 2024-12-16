<?php 
/**
 * Post Tab Project Detail template.
 *
 * @param array $block The block settings and attributes.
 */

// Load values and assign defaults.
$heading = get_field('heading'); // Retrieves the 'heading' field value
$button_text = get_field('button_text'); // Retrieves the 'button_text' field value
$button_url = get_field('button_url'); // Retrieves the 'button_url' field value
?>

<section class="post-tab-project-detail-container">
    <div class="post-tab-project-detail-inside-container">
        <!-- Heading and Button Section -->
        <div class="post-tab-project-detail-heading-col-12">
            <h4 class="post-tab-project-detail-heading-main animate-scroll">
                <?php echo !empty($heading) ? esc_html($heading) : 'Related Content'; ?>
            </h4>
            
            <!-- Check if both button text and URL exist before displaying the button -->
            <?php if (!empty($button_text) && !empty($button_url)) : ?>
                <a class="post-tab-project-detail-pos-tab-url animate-scroll animated-btn" href="<?php echo esc_url($button_url); ?>">
                    <?php echo esc_html($button_text); ?>
                </a>
            <?php endif; ?>
        </div>

        <!-- Content Section (Tab Content) -->
        <div class="post-tab-project-detail-content-col-12">
            <div class="post-tab-project-detail-tab-content" id="v-pills-tabContent">
            <?php
            // Define the category slug
            $category_slug = 'related-content';
            
            // Set up a new WP_Query instance
            $args = array(
                'category_name' => $category_slug, // Fetch posts from the specific category
                'posts_per_page' => -1, // -1 to show all posts, or set a specific number like 10
                'orderby' => 'date', // Sort by date
                'order' => 'ASC' // Newest posts first                
            );
            
            $query = new WP_Query($args);
            
            if ($query->have_posts()) : ?>
                <div class="post-list wrapper"> <!-- Use row for Bootstrap grid -->
                    <?php while ($query->have_posts()) : $query->the_post(); ?>
                        <div class="post-item d-flex align-items-start col-12"> <!-- Use d-flex for layout -->
                            
                            <!-- Left Column (Date, Title, Read More) -->
                            <div class="col-6 post-details"> <!-- Left side for date, title, read more -->
                                <div class="post-date animate-scroll">
                                    <?php echo get_the_date(); // Display post date ?>
                                </div>
            
                                <h2 class="post-title animate-scroll">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h2>
            
                                <div class="read-more animate-scroll">
                                    <a href="<?php the_permalink(); ?>" class="read-more-link">Read More <i class="fas fa-arrow-right"></i></a>
                                </div>
                            </div>
            
                            <!-- Right Column (Featured Image) -->
                            <div class="col-6 post-featured-image"> <!-- Right side for featured image -->
                                <?php if (has_post_thumbnail()) : ?>
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_post_thumbnail('full'); // Use 'full' for the original size ?>
                                    </a>
                                <?php endif; ?>

                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                <?php
                // Reset post data after custom query
                wp_reset_postdata();
            else :
                echo '<p>No posts found in this category.</p>';
            endif;
            ?>

    

            </div>
        </div>
    </div>
</section>
