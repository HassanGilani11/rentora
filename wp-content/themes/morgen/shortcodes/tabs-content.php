<?php 
if ( $the_query->have_posts() ) :
    while ( $the_query->have_posts() ) : $the_query->the_post();
        $post_date_formatted    = get_the_date( 'F j, Y' );
        ?>
        <div class="post-column w-50">
            <?php if (has_post_thumbnail( get_the_ID() ) ): ?>
                <div class="news-post-feat-img-wrap">
                    <div class="news-post-feat-img-inner">
                        <?php
                            $image = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'single-post-thumbnail' );
                            $post_category_obj  = array_pop(get_the_category( get_the_ID() ));
                            $post_category_name = $post_category_obj->name;
                            $post_cat_slug      = $post_category_obj->slug;
                        ?>
                        <?php if ($post_category_name) : ?>
                            <div class="post-cat-wrap cat-<?php echo $post_cat_slug; ?>">
                                <div class="post-cat-inner">
                                    <?php echo $post_category_name; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <img src="<?php echo $image[0]; ?>" class="post-featured-image"/>
                    </div>
                </div>
            <?php endif; ?>
            <p class="post-date"><?php echo $post_date_formatted; ?></p>
            <h2 class="post-title"><?php echo get_the_title(); ?></h2>
            <a href="<?php echo get_the_permalink(); ?>" class="post-url">Read More <i class="fas fa-arrow-right"></i></a>
        </div>
        <?php
    endwhile;
endif;
