<?php
$args = array(
    'post_type' => 'team-member',
    'post_status' => array('publish'),
    'posts_per_page' => -1,  
    'order' => 'ASC',
);
$morgen_tvs_posts  = get_posts($args);
?>

<?php if($morgen_tvs_posts) : ?>
    <div class="morgen-tvs-wrap">
        <div class="morgen-tvs-inner">
            <?php 
            foreach($morgen_tvs_posts as $morgen_tvs_post) :
                $tvs_post_ID        = $morgen_tvs_post->ID;
                $tvs_post_title     = $morgen_tvs_post->post_title;
                $tvs_post_position  = $morgen_tvs_post->post_content;
                $tvs_post_video     = get_field('video_url', $tvs_post_ID);
                $tvs_post_thumb     = get_the_post_thumbnail_url( $tvs_post_ID,'full' );
            ?>
                <div class="morgen-tvs-wrap">
                    <div class="morgen-single-tvs-inner">
                        <div class="morgen-tvs-vid-wrap">
                            <?php if($tvs_post_video) :  ?>
                                <video width="564" controls>
                                    <source src="<?php echo $tvs_post_video; ?>" type="video/mp4">
                                </video>
                            <?php else: ?>
                                <img src="<?php echo $tvs_post_thumb; ?>" alt="Team Member">
                            <?php endif; ?>
                        </div>
                        <div class="single-tvs-card-body-wrap">
                            <div class="single-tvs-card-body-inner">
                                <div class="morgen-tvs-title">
                                    <h2><?php echo $tvs_post_title; ?></h2>
                                </div>
                                <div class="morgen-tvs-subtitle">
                                    <h4><?php echo $tvs_post_position; ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>