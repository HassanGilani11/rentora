<?php
/**
 * This Page is used to display single posts
 */

 if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header(); 


$post_id        = get_the_ID();
$post_title     = get_the_title();
$post_thumb     = get_the_post_thumbnail_url( $tvs_post_ID,'full' );
$post_content   = get_the_content();
$post_url       = get_the_permalink();
$author_name    = get_field( 'author_name', $post_id );
//$post_date      = get_the_date('M d, Y');
//$author_image   = my_display_gravatar();

$author_image = get_field('author_image');

// Get home page CTA subscription form data
$cta_block_data     = [];
$home_pageID        = get_option('page_on_front');
$homepage_content   = get_post( $home_pageID )->post_content;
$blocks             = parse_blocks( $homepage_content );
if( $blocks ){
    foreach ($blocks as $block ) {
        if ( $block['blockName'] === 'acf/cta-section' ) {
            $cta_block_data = $block['attrs']['data'];
            break;
        }
    }
}
extract( $cta_block_data, EXTR_PREFIX_ALL, 'cta' );

?>

<div class="single-post article-wrap">
    <div class="morgen-single-inner">
        <div class="single-info-wrap morgen-inner-wrapper cont-wrap">
            <div class="morgen-breadcrumbs">
                <?php the_breadcrumb(); ?>
            </div>
            <div class="post-title">
                <h1 class="article-title"><?php echo $post_title; ?></h1>
            </div>
            <div class="single-banner-wrap mobile-banner">
                <img src="<?php echo $post_thumb; ?>" alt="Post Thumbnail">
            </div>
            <div class="post-author-main-wrap">
                <div class="post-author-wrap">
                    <?php if( $author_image ) : ?>
                        <div class="post-author-img">
                            <div class="author-avatar">
                                <img src="<?php echo $author_image; ?>" alt="Author Image" class="avatar-author">
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="post-author-info">
                        <?php if( $author_name ) :?>
                            <div class="author-name"><?php echo $author_name; ?></div>
                        <?php endif; ?>
                        <div class="post-date"><?php echo get_the_date('M d, Y'); ?><span class="read-time-wrap"><?php echo do_shortcode('[rt_reading_time postfix="mins read" postfix_singular="min read"]'); ?></span></div>
                    </div>
                </div>
                <div class="share-posts top-pos-share">
                    <span class="share-post copy-link" data-attr="<?php echo $post_url; ?>"><i class="fa-solid fa-link"></i></span>
                    <?php echo do_shortcode( '[Sassy_Social_Share]' ); ?>
                </div>
            </div>
        </div>
        <div class="single-banner-wrap cont-wrap desktop-banner">
            <img src="<?php echo $post_thumb; ?>" alt="Post Thumbnail">
        </div>
        <div class="single-content morgen-inner-wrapper cont-wrap">
            <?php echo $post_content; ?>
        </div>
        <div class="share-content morgen-inner-wrapper cont-wrap">
            <h5 class="sharepost">Share this post</h5>
            <div class="share-posts bottom-pos-share">
                <span class="share-post copy-link" data-attr="<?php echo $post_url; ?>"><i class="fa-solid fa-link"></i></span>
                <?php echo do_shortcode( '[Sassy_Social_Share]' ); ?>
            </div>
        </div>
        
    </div>
</div>
<div class="more-articles-wrap cont-wrap">
    <div class="more-articles-inner">
        <div class="ma-title-wrap">
            <h2>More Articles</h2>
            <span class="dekstop-view-btn"><a class="animated-btn" href="/news">View all news</a></span>
        </div>
        <?php
        $args = array(
            'post_type' => 'post',
            'post_status' => array('publish'),
            'posts_per_page' => 3,  
            'order' => 'ASC',
            'category' => array( 3, 4, 6 ),
        );
        $morgen_ma_posts  = get_posts($args);
        if($morgen_ma_posts) : ?>
            <div class="morgen-ma-row">
                <?php foreach($morgen_ma_posts as $post):
                    $post_date              = $post->post_date;
                    $timestamp              = strtotime($post_date);
                    $post_date_formatted    = date('F j, Y', $timestamp);
                ?>
                <div class="post-column three-col">
                    <?php if (has_post_thumbnail( $post->ID ) ): ?>
                        <div class="news-post-feat-img-wrap">
                            <div class="news-post-feat-img-inner">
                                <?php
                                    $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
                                    $post_category_obj  = array_pop(get_the_category($post->ID));
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
                    <h2 class="post-title"><?php echo $post->post_title; ?></h2>
                    <p class="article-cont"><?php echo wp_trim_words( get_the_content(), 15, $moreLink); ?></p>
                    <a href="<?php echo $post->post_name; ?>" class="post-url">Read More <i class="fas fa-arrow-right"></i></a>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="ma-mobile-title-wrap">
            <span><a href="/news">View all news</a></span>
        </div>
    </div>
</div>
<div class="container-fluid cta-section">
    <div class="row">
        <div class="col-md-4 px-0 col-sm-12">
            <div class="card" style="background-color: #f5f5f5">
                <div class="card-body">
                    <h5 class="card-title"><?php echo esc_html( $cta_heading_card_1 ); ?></h5>
                    <p class="card-text"><?php echo nl2br( $cta_description_card_1 ); ?></p>
                    <div class="cta-btn">
                    <a class="animate-scroll animated-btn" href="<?php echo !empty($cta_button_url_card_1) ? esc_url($cta_button_url_card_1) : "#"; ?>"><?php echo esc_html( $cta_button_text_card_1 ); ?></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 px-0 col-sm-12">
            <?php if(!empty($cta_image_card_1)): ?>
                <img decoding="async" class="car-image-cst" src="<?php echo wp_get_attachment_image_url( $cta_image_card_1, 'full' ); ?>" />
            <?php endif; ?>
        </div>
        <div class="col-md-4 px-0 col-sm-12">
            <div class="card" style="background-color: #ffff00">
                <div class="card-body">
                    <h5 class="card-title"><?php echo esc_html( $cta_heading_card_2 ); ?></h5>
                    <p class="card-text"><?php echo nl2br( $cta_description_card_2 ); ?></p>
                    <div class="cta-btn">
                    <a class="animate-scroll animated-btn" href="<?php echo !empty($cta_button_url_card_2) ? esc_url($cta_button_url_card_2) : "#"; ?>"><?php echo esc_html( $cta_button_text_card_2 ); ?></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 px-0 col-sm-12">
            <?php if(!empty($cta_image_card_2)): ?>
                    <img decoding="async" class="car-image-cst" src="<?php echo wp_get_attachment_image_url( $cta_image_card_2, 'full' ); ?>" />
            <?php endif; ?>
        </div>
        <div class="col-md-4 px-0 col-sm-12">
            <div class="card" style="background-color: #d7e6ff">
                <div class="card-body">
                    <h5 class="card-title"><?php echo esc_html( $cta_heading_card_3 ); ?></h5>
                    <p class="card-text"></p>
                    <div class="cta-btn">
                        <a class="animate-scroll animated-btn btn-subscribe" href="#<?php echo esc_attr( $cta_button_url_card_3 ); ?>"><?php echo esc_html( $cta_button_text_card_3 ); ?></a>
                    </div>
                </div>
			    <div id="<?php echo esc_attr( $cta_button_url_card_3 ); ?>" class="cta-form-modal-modal-wrapper white-popup mfp-hide">
                    <button class="popup-modal-dismiss">X</button>
                    <?php echo do_shortcode('[gravityform id="'. (int)$cta_button_form_id_card_3 .'" title="false" description="false" ajax="true" tabindex="49"]'); ?>
                </div>
            </div>
        </div>
        <div class="col-md-4 px-0 col-sm-12">
            <?php if(!empty($cta_image_card_3)): ?>
                <img decoding="async"class="car-image-cst" src="<?php echo wp_get_attachment_image_url( $cta_image_card_3, 'full' ); ?>" />
            <?php endif; ?>
        </div>
</div></div>
<style>

.single-post {
    border-top: 2px solid #1E3E37;
    padding: 100px 0;
    background-color: #fff;
}
.morgen-inner-wrapper {
    max-width: 768px;
}
.single-banner-wrap,
.more-articles-wrap {
    max-width: 1320px;
}
.single-banner-wrap img {
    width: 100%;
    max-width: 1320px;
    height: 600px;
    object-fit: cover;
}
.cont-wrap {
    margin: 0 auto 50px;
    padding: 0 20px;
}
.single-post h1 {
    font-size: 48px !important;
}
.single-post h2 {
    font-size: 32px !important;
}
.single-post h3 {
    font-size: 20px !important;
}
.single-post h1,
.single-post h2,
.single-post h3,
.ma-title-wrap h2 {
    font-family: 'TroisMilleRegular';
    font-weight: 400;
}
.breadcrumbs a {
    text-decoration: none;
}
.single-post,
.breadcrumbs a{
    color: #1E3E37;
}
.border-left-text {
    padding: 5px 20px 0;
    border-left: 2px solid #A884FF;
}
.post-author-main-wrap {
    display: flex;
    align-items: center;
}
.post-author-wrap,
.share-posts {
    flex: 0 0 50%;
    max-width: 50%;
}
.post-title h1 {
    margin-bottom: 30px;
}
.post-author-wrap {
    display: flex;
    align-items: center;
}
.post-author-main-wrap .share-post{
    text-align: right;
}
.post-author-img img {
    width: 55px;
    height: 55px;
    border-radius: 80px;
    margin-right: 10px;
    object-fit: cover;
}
.post-author-info .post-date {
    margin: 0;
}
.share-posts span {
    margin: 5px;
}
.ma-title-wrap {
    display: flex;
    align-items: center;
    margin-bottom: 30px;
}
.ma-title-wrap h2,
.ma-title-wrap span {
    flex: 0 0 50%;
    max-width: 50%;
}
.ma-title-wrap h2{
    font-size: 48px !important;
}
.ma-title-wrap span {
    text-align: right;
}
.ma-mobile-title-wrap a{
    display: none;
}
.ma-mobile-title-wrap {
    margin-top: 20px;
    text-align: center;
}
.ma-title-wrap a, .ma-mobile-title-wrap a {
    border: 1px solid #1E3E37;
    border-radius: 50px;
    text-decoration: none;
    font-family: Montserrat;
    font-size: 16px;
    font-weight: 500;
    line-height: 22.4px;
    text-align: center;
    background-color: transparent;
    padding: 10px 40px;
    color: #1E3E37;
    transition: all 0.3s ease;
}
.ma-title-wrap a:hover,
.ma-mobile-title-wrap a:hover {
    background-color: #A884FF;
    color: #fff;
    border: 1px solid #A884FF !important;
}
.morgen-ma-row {
    display: flex;
    flex-wrap: wrap;
    overflow: hidden;
}
.three-col {
    flex-basis: calc(100% / 3);
    overflow: hidden;
    padding: 12px;
}
.news-post-feat-img-inner img {
    width: 422px;
    height: 300px;
    object-fit: cover;
}
.more-articles-inner {
    padding: 100px 0 50px;
}
.share-posts .heateor_sss_sharing_ul a {
    float: unset !important;
}
.share-posts .copy-link:hover {
    cursor: pointer;
}
.share-posts {
    display: flex;
    align-items: center;
}
.share-posts.top-pos-share {
    justify-content: flex-end;
}
.read-time-wrap {
    padding-left: 10px;
    position: relative;
    margin-left: 12px;
}
.read-time-wrap:before {
    content: '';
    width: 4px;
    height: 4px;
    background-color: #000;
    position: absolute;
    top: 8px;
    left: 0;
    border-radius: 10px;
}
.mobile-banner{
    display: none;
}
/* Responsive */
@media screen and (max-width: 1240px){
    .three-col {
        flex-basis: calc(100% / 2);
    }
    .news-post-feat-img-inner img {
        width: 100%;
    }
}
@media screen and (max-width: 1024px){
    .single-post,
    .more-articles-inner {
        padding: 50px 0 30px;
    }
    .cont-wrap {
        margin: 0 auto 20px;
    }
}
@media screen and (max-width: 767px){
    .cta-section .card {
        padding: 50px 20px;
    }
    .three-col {
        flex-basis: 100%;
        padding: 0;
    }
    .single-post,
    .more-articles-inner {
        padding: 30px 0 10px;
    }
    .mobile-banner{
        display: block;
    }
    .desktop-banner{
        display: none;
    }
    .mobile-banner img {
        width: 100%;
        height: 319px;
        object-fit: cover;
    }
    .mobile-banner {
        margin-bottom: 20px;
    }
    .post-author-main-wrap {
        flex-direction: column;
        align-items: flex-start;
    }
    .post-author-wrap, .share-posts {
        flex: unset !important;
        max-width: 100%;
    }
    .single-post h1,
    .ma-title-wrap h2  {
        font-size: 36px !important;
    }
    .ma-mobile-title-wrap a{
        display: block;
    }
    .dekstop-view-btn {
        display: none;
    }
}
@media screen and (max-width: 600px){
    .mobile-banner img {
        height: 188px;
    }
}

</style>

<?php get_footer(); ?>