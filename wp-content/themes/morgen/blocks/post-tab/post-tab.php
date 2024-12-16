
<?php 
$heading = get_field('heading');
?>
<section class="post-container">
    <div class="d-flex align-items-start is_tab w-100 flex-sm-row flex-column">
        <div class="col-md-3 col-sm-12 ">
            <div class="inner-wrap first-col">
                <h4 class="heading-main animate-scroll"><?php echo(!empty($heading) ? $heading : 'Latest news & insights.') ?></h4>
                <div class="action-btn-wrap">
                    <a href="/news" class="action-btn animate-scroll">View all news</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-12">
            <?php 
            $args = array(  
                'hide_empty'    => 1,    
                'exclude'       =>array(1,8),
                'orderby'       => 'include',
                'include'       => array( 3,4,5,6 )
            );             
            $categories = get_categories($args); ?>
            <div class="nav-post-sect nav flex-md-column flex-sm-row nav-pills me-3" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                <?php foreach($categories as $key => $category): ?>
                    <button class="nav-link py-3 animate-scroll <?php echo $key ==0 ? 'active' : '' ?>" id="v-pills-<?php echo $key; ?>-tab" data-bs-toggle="pill" 
                    data-bs-target="#v-pills-<?php echo $key; ?>" type="button" role="tab" 
                    aria-controls="v-pills-<?php echo $key; ?>" aria-selected="true">
                        <?php echo $category->name; ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-md-6 col-sm-12">
            <div class="tab-content" id="v-pills-tabContent">
                <?php foreach($categories as $key => $category): ?>
                    <div class="tab-pane fade show scroller animate-scroll <?php echo $key ==0 ? 'active' : '' ?>" id="v-pills-<?php echo $key; ?>" role="tabpanel" aria-labelledby="v-pills-<?php echo $key; ?>-tab">
                        <?php 
                        $args = array(
                            'category_name' => $category->slug,
                            'posts_per_page' => 3
                        ); 
                        $posts = get_posts($args);
                        ?>
                        <?php if ($category->slug === 'in-the-news'): ?>
                            <div class="desktop-news-sect news-section-wrap">
                                <?php foreach($posts as $post): 
                                    $news_post_ID        = $post->ID;
                                    $news_source        = $value = get_field('news_source', $news_post_ID);
                                    $news_post_date     = $post->post_date;
                                    // Format post date from 2024-09-12 19:58:25 to Sept 12, 2024
                                    $timestamp = strtotime($news_post_date);
                                    $news_post_date_formatted = date('F j, Y', $timestamp);
                                    ?>
                                    <div class="morgen-single-news-wrap home-news-sect">
                                        <div class="morgen-single-news-inner">
                                            <div class="single-news-header-wrap">
                                                <div class="single-news-header-inner">
                                                    <div class="single-news-date-wrap">
                                                        <p class="single-news-date"><?php echo $news_post_date_formatted; ?></p>
                                                    </div>
                                                    <div class="single-news-header-tag-wrap">
                                                        <p class="single-news-header-tag"><a href="/news">In the news</a></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="single-news-card-body-wrap">
                                                <div class="single-news-card-body-inner">
                                                    <div class="single-news-post-source-wrap">
                                                        <div class="single-news-post-source-inner">
                                                            <p class="single-news-post-source"><?php echo $news_source; ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="single-news-post-title-wrap">
                                                        <div class="single-news-post-title-inner">
                                                            <h3 class="single-news-post-title"><?php echo $post->post_title; ?></h3>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="single-news-post-url-wrap">
                                                <div class="single-news-post-url-inner">
                                                    <a href="<?php echo $post->post_name; ?>" class="post-url">Read More <i class="fas fa-arrow-right"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="mobile-news-sect news-slider-wrap">
                                <?php foreach($posts as $post): 
                                    $news_post_ID        = $post->ID;
                                    $news_source        = $value = get_field('news_source', $news_post_ID);
                                    $news_post_date     = $post->post_date;
                                    // Format post date from 2024-09-12 19:58:25 to Sept 12, 2024
                                    $timestamp = strtotime($news_post_date);
                                    $news_post_date_formatted = date('F j, Y', $timestamp);
                                    ?>
                                    <div class="morgen-single-news-wrap home-news-sect">
                                        <div class="morgen-single-news-inner">
                                            <div class="single-news-header-wrap">
                                                <div class="single-news-header-inner">
                                                    <div class="single-news-date-wrap">
                                                        <p class="single-news-date"><?php echo $news_post_date_formatted; ?></p>
                                                    </div>
                                                    <div class="single-news-header-tag-wrap">
                                                        <p class="single-news-header-tag"><a href="/news">In the news</a></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="single-news-card-body-wrap">
                                                <div class="single-news-card-body-inner">
                                                    <div class="single-news-post-source-wrap">
                                                        <div class="single-news-post-source-inner">
                                                            <p class="single-news-post-source"><?php echo $news_source; ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="single-news-post-title-wrap">
                                                        <div class="single-news-post-title-inner">
                                                            <h3 class="single-news-post-title"><?php echo $post->post_title; ?></h3>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="single-news-post-url-wrap">
                                                <div class="single-news-post-url-inner">
                                                    <a href="<?php echo $post->post_name; ?>" class="post-url">Read More <i class="fas fa-arrow-right"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="desktop-post-sect post-section-wrap">
                                <?php foreach($posts as $post): ?>
                                    <div class="post-column">
                                        <?php if (has_post_thumbnail( $post->ID ) ): ?>
                                            <?php $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' ); 
                                            $categories = get_the_terms( $post->ID, 'category' );
                                            foreach($categories as $key => $category): ?>
                                                <div class="post-cat-wrap cat-press">
                                                    <div class="post-cat-inner">
                                                        <a href="/category/<?php echo $category->slug; ?>">                           
                                                            <?php echo $category->name; ?>
                                                        </a>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                            <img src="<?php echo $image[0]; ?>" class="post-featured-image"/>
                                        <?php endif; ?>
                                        <p class="post-date"><?php echo date('n-j-Y', strtotime($post->post_date)); ?></p>
                                        <h2 class="post-title"><?php echo $post->post_title; ?></h2>
                                        <a href="<?php echo $post->post_name; ?>" class="post-url">Read More <i class="fas fa-arrow-right"></i></a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="mobile-post-sect post-slider-wrap">
                                <?php foreach($posts as $post): ?>
                                    <div class="post-column">
                                        <?php if (has_post_thumbnail( $post->ID ) ): ?>
                                            <?php $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' ); 
                                            $categories = get_the_terms( $post->ID, 'category' );
                                            foreach($categories as $key => $category): ?>
                                                <div class="post-cat-wrap cat-press">
                                                    <div class="post-cat-inner">
                                                        <a href="/category/<?php echo $category->slug; ?>">                      
                                                            <?php echo $category->name; ?>
                                                        </a>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                            <img src="<?php echo $image[0]; ?>" class="post-featured-image"/>
                                        <?php endif; ?>
                                        <p class="post-date"><?php echo date('n-j-Y', strtotime($post->post_date)); ?></p>
                                        <h2 class="post-title"><?php echo $post->post_title; ?></h2>
                                        <a href="<?php echo $post->post_name; ?>" class="post-url">Read More <i class="fas fa-arrow-right"></i></a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="action-btn-wrap mobile-view">
                <a href="/news" class="action-btn">View all news</a>
            </div>
        </div>
    </div>
</section>