<?php
$args = array(
    'post_type' => 'post',
    'post_status' => array('publish'),
    'posts_per_page' => -1,  
    'order' => 'ASC',
    'cat' => array(5),
);
$morgen_news_posts  = get_posts($args);
?>

<?php if($morgen_news_posts) : ?>
    <div class="morgen-news-main-wrap-outer">
        <div class="morgen-news-main-wrap">
            <div class="morgen-news-title-wrap">
                <div class="morgen-news-title-inner">
                    <h2 class="morgen-news-title animate-scroll">Morgen Energy<br/>In the news</h2>
                </div>
            </div>
            <div class="morgen-news-inner">
                <?php foreach($morgen_news_posts as $morgen_news_post) :
                    $news_post_ID       = $morgen_news_post->ID;
                    $news_post_title    = $morgen_news_post->post_title;
                    $news_post_date     = $morgen_news_post->post_date;
                    $news_source        = get_field('news_source', $news_post_ID);
                    $news_post_url      = get_permalink($news_post_ID);

                    // Format post date from 2024-09-12 19:58:25 to Sept 12, 2024
                    $timestamp = strtotime($news_post_date);
                    $news_post_date_formatted = date('F j, Y', $timestamp);
                ?>
                    <div class="morgen-single-news-wrap">
                        <div class="morgen-single-news-inner">
                            <div class="single-news-header-wrap">
                                <div class="single-news-header-inner">
                                    <div class="single-news-date-wrap">
                                        <p class="single-news-date"><?php echo $news_post_date_formatted; ?></p>
                                    </div>
                                    <div class="single-news-header-tag-wrap">
                                        <p class="single-news-header-tag">In the news</p>
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
                                            <h3 class="single-news-post-title"><?php echo $news_post_title; ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="single-news-post-url-wrap">
                                <div class="single-news-post-url-inner">
                                    <a href="<?php echo $news_post_url; ?>">Read More <i class="fa-solid fa-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>