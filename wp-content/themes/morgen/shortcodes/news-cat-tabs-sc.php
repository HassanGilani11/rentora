
<section class="post-container news-posts-tab">
    <div class="d-flex align-items-start is_tab w-100 flex-sm-row flex-column">
        <!-- News Category tab buttons column -->
        <div class="col-md-3 col-sm-12 news-title-tab">
            <div class="nav flex-md-column flex-sm-row nav-pills me-3" id="v-pills-tab" role="tablist" aria-orientation="vertical">

                <!-- Default All Posts button, first key 0 -->
                <button class="nav-link py-3 animate-scroll active" id="v-pills-0-tab" data-bs-toggle="pill" 
                data-bs-target="#v-pills-0" type="button" role="tab" 
                aria-controls="v-pills-0" aria-selected="true" data-cat="<?php echo esc_attr( $atts['cat'] ); ?>">
                    All posts
                </button>
                <?php if( !empty( $categories ) ): ?>
                    <!-- Show category buttons (Articles, Press, and Videos) -->
                    <?php foreach($categories as $key => $category): ?>
                        <button class="nav-link py-3 animate-scroll" id="v-pills-<?php echo $category->term_id; ?>-tab" data-bs-toggle="pill" 
                        data-bs-target="#v-pills-<?php echo $category->term_id; ?>" type="button" role="tab" 
                        aria-controls="v-pills-<?php echo $category->term_id; ?>" aria-selected="true" data-cat="<?php echo esc_attr( $category->term_id ); ?>">
                            <?php echo $category->name; ?>
                        </button>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- News Category tab content column -->
        <div class="col-md-9 col-sm-12">
            <div class="tab-content" id="v-pills-tabContent">
                <?php 
                    $args = array( "category__in" => [], "posts_per_page" => $ppage ); 
                    $the_query = new WP_Query( $args );
                ?>
                <!-- Default show all posts tab content, has the "active" class by default,
                more or less should have the same design as other tabs, first key 0 -->
                <div class="sc-tab-content tab-pane fade show flex-wrap active" id="v-pills-0" role="tabpanel" aria-labelledby="v-pills-0-tab" data-args="<?php echo htmlentities(json_encode( $args )); ?>" data-page="1" data-maxpage="<?php echo ceil( $the_query->found_posts / $ppage ); ?>">
                    <div class="tab-content-wrapper">
                        <?php include( get_stylesheet_directory() .'/shortcodes/tabs-content.php' ); ?>
                    </div>
                    <?php if( $the_query->found_posts && $the_query->found_posts > $ppage ): ?>
                        <div class="view-more-posts-btn-wrap">
                            <div class="view-more-posts-btn-inner">
                                <a href="#" class="tabs-view-more-posts view-more-posts-btn animated-btn">View more articles</a>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php wp_reset_postdata(); ?>
                </div>
                <?php if( !empty( $categories ) ): ?>
                    <!-- Show other category tabs content posts, more or less should have the same design as default all posts tab -->
                    <?php foreach($categories as $key => $category): ?>
                        <?php 
                            $args = array( 'category__in' => [ $category->term_id ], 'posts_per_page' => $ppage ); 
                            $the_query = new WP_Query( $args );
                        ?>
                        <div class="sc-tab-content tab-pane fade show flex-wrap" id="v-pills-<?php echo $category->term_id; ?>" role="tabpanel" aria-labelledby="v-pills-<?php echo $category->term_id; ?>-tab" data-args="<?php echo htmlentities(json_encode( $args )); ?>" data-page="1" data-maxpage="<?php echo ceil( $the_query->found_posts / $ppage ); ?>">
                            <div class="tab-content-wrapper">
                                <?php include( get_stylesheet_directory() .'/shortcodes/tabs-content.php' ); ?>
                            </div>
                            <?php if( $the_query->found_posts && $the_query->found_posts > $ppage ): ?>
                                <div class="view-more-posts-btn-wrap">
                                    <div class="view-more-posts-btn-inner">
                                        <a href="#" class="tabs-view-more-posts view-more-posts-btn animated-btn">View more articles</a>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php wp_reset_postdata(); ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

