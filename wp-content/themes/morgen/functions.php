<?php


function morgen_register_styles_and_scripts(){
    wp_register_style('google_montserrat', 'https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap', array(), null, 'all');
    wp_enqueue_style( 'google_montserrat');
    wp_register_style('morgen_css', get_stylesheet_directory_uri().'/assets/css/morgen.css', array(), rand(1,1000), 'all');
    wp_enqueue_style( 'morgen_css');
    wp_register_style('bootstrap_css', get_stylesheet_directory_uri().'/assets/css/bootstrap.min.css', array(), null, 'all');
    wp_enqueue_style( 'bootstrap_css');
    wp_register_script('bootstrap_js', get_stylesheet_directory_uri().'/assets/js/bootstrap.bundle.min.js', array(), null, 'all');
    wp_enqueue_script( 'bootstrap_js');
    wp_register_script('jquery_js', get_stylesheet_directory_uri().'/assets/js/jquery.min.js', array(), null, 'all');
    wp_enqueue_script( 'jquery_js');
    wp_register_script('morgen_js', get_stylesheet_directory_uri().'/assets/js/morgen.js', array(), rand(1,1000), 'all');
    wp_enqueue_script( 'morgen_js');

    wp_enqueue_style('slick-css', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.css');
    wp_enqueue_style('slick-theme-css', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.css');
    wp_enqueue_script('slick-js', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js', array('jquery'), null, true);

    // Custom script for News tabs shortcode
    wp_register_script('shortcode-script', get_stylesheet_directory_uri().'/assets/js/shortcode-script.js', array(), rand(1,1000), 'all');
    wp_enqueue_script( 'shortcode-script');
    wp_localize_script( 'shortcode-script', 'morgen', 
        [ 
            'ajaxurl'   => admin_url( 'admin-ajax.php' ),
            'security'  => wp_create_nonce( 'morgen-nonce' ) 
        ]
    );
	
	wp_enqueue_script('team-tailor', 'https://scripts.teamtailor-cdn.com/widgets/eu-pink/jobs.js', array(), '1.0.0', array('strategy' => 'async'));

    // POPUP LIBRARY
    wp_enqueue_style('magnificpopup-css', 'https://cdn.jsdelivr.net/npm/magnific-popup@1.1.0/dist/magnific-popup.css');
    wp_register_script('magnificpopup-js', 'https://cdn.jsdelivr.net/npm/magnific-popup@1.1.0/dist/jquery.magnific-popup.min.js', array('jquery'), null, true);
    wp_enqueue_script( 'magnificpopup-js');

    // Animate.css
    wp_enqueue_style('animate-css', 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css');
    // Scroll reveal
    wp_register_script('scroll-reveal-js', 'https://unpkg.com/scrollreveal', array(), '1.0.0', false);
    wp_enqueue_script( 'scroll-reveal-js');

}

wp_oembed_add_provider('https://*.wistia.com/*', 'https://fast.wistia.com/oembed', false);

function add_file_types_to_uploads($file_types){
    $new_filetypes = array();
    $new_filetypes['svg'] = 'image/svg+xml';
    $file_types = array_merge($file_types, $new_filetypes );
    return $file_types;
    }
    add_filter('upload_mimes', 'add_file_types_to_uploads');

add_action('wp_enqueue_scripts', 'morgen_register_styles_and_scripts');
function morgen_register_acf_blocks() {
    /**
     * We register our block's with WordPress's handy
     * register_block_type();
     *
     * @link https://developer.wordpress.org/reference/functions/register_block_type/
     */
    register_block_type( __DIR__ . '/blocks/hero/block.json' );
    register_block_type( __DIR__ . '/blocks/about/block.json' );
    register_block_type( __DIR__ . '/blocks/about-2/block.json' );
    register_block_type( __DIR__ . '/blocks/companies-slider/block.json' );
    register_block_type( __DIR__ . '/blocks/fog-animated-section/block.json' );
    register_block_type( __DIR__ . '/blocks/post-tab/block.json' );
    register_block_type( __DIR__ . '/blocks/cta-section/block.json' );
    
    // About Us Template
    register_block_type( __DIR__ . '/blocks/hero-about-us/block.json' );   
    register_block_type( __DIR__ . '/blocks/intro-about-us/block.json' );    
    register_block_type( __DIR__ . '/blocks/image-with-text-about-us/block.json' ); 
    //register_block_type( __DIR__ . '/blocks/cta-about-us/block.json' ); 
    //register_block_type( __DIR__ . '/blocks/companies-slider-about-us/block.json' ); 
    register_block_type( __DIR__ . '/blocks/post-tab-about-us/block.json' ); 
    register_block_type( __DIR__ . '/blocks/text-with-image-box-about-us/block.json' ); 
    register_block_type( __DIR__ . '/blocks/team-about-us/block.json' );   
    
    // Project Detail Template
    register_block_type( __DIR__ . '/blocks/hero-project-detail/block.json' );   
    register_block_type( __DIR__ . '/blocks/intro-project-detail/block.json' );    
    register_block_type( __DIR__ . '/blocks/companies-project-detail/block.json' );        
    register_block_type( __DIR__ . '/blocks/cta-project-detail/block.json' ); 
    
    register_block_type( __DIR__ . '/blocks/contact-project-detail/block.json' );
    register_block_type( __DIR__ . '/blocks/image-with-text-project-detail/block.json' );	
    register_block_type( __DIR__ . '/blocks/post-tab-project-detail/block.json' );		
    register_block_type( __DIR__ . '/blocks/horizontal-accordion-project-detail/block.json' );		

    // Industries Template
    register_block_type( __DIR__ . '/blocks/Industry/two-col-content/block.json' );
    register_block_type( __DIR__ . '/blocks/Industry/four-col-content/block.json' );
    register_block_type( __DIR__ . '/blocks/Industry/infinite-loop-slider/block.json' );
    register_block_type( __DIR__ . '/blocks/Industry/solution-section/block.json' );
    register_block_type( __DIR__ . '/blocks/Industry/industry-cta-section/block.json' );
    register_block_type( __DIR__ . '/blocks/Industry/develop-section/block.json' );
	
	// Career Template
    register_block_type( __DIR__ . '/blocks/career-video/block.json' );
}
// Here we call our morgen_register_acf_blocks() function on init.
add_action( 'init', 'morgen_register_acf_blocks' );

function register_morgen_theme_setup(){
    require_once get_template_directory() . '/class_nav_walker.php';
    add_theme_support( 'title-tag' );
    add_theme_support( 'custom-logo' );
    add_theme_support('post-thumbnails');
    register_nav_menus( array(
        'header'   => 'Display this menu in Header',
        'footer'   => 'Display this menu in Footer',
    ) );
}
add_action( 'after_setup_theme', 'register_morgen_theme_setup' );

function prefix_nav_description( $item_output, $item, $depth, $args ) {
    if ( !empty( $item->description ) ) {
        $item_output = str_replace( $args->link_after . '</a>', '<p class="menu-item-description">' . $item->description . '</p>' . $args->link_after . '</a>', $item_output );
    }
 
    return $item_output;
}
add_filter( 'walker_nav_menu_start_el', 'prefix_nav_description', 10, 4 );

function team_members_shortcode() {
    ob_start(); // Start output buffering to capture the HTML output
    
    // Query for team members
    $args = array(
        'post_type' => 'team-member', // Your custom post type
        'posts_per_page' => -1,
        'orderby' => 'date', // Sort by date
        'order' => 'ASC', // Ascending order
    );
    
    $team_members = new WP_Query($args);
    
    if ($team_members->have_posts()) : ?>
        <section class="post-container team-about">
            <div class="wrapper">
				<div class="col-md-12 col-sm-12">
					<h4 class="heading-main-team">Meet our <br>management team</h4>
				</div>
                <div class="team-slider">
                    <?php while ($team_members->have_posts()) : $team_members->the_post(); ?>
                        <div class="team-member">
                            <?php if (has_post_thumbnail()) : ?>
                                <img src="<?php the_post_thumbnail_url(); ?>" alt="<?php the_title(); ?>" class="member-image">
                            <?php endif; ?>
                            
                            <h3><?php the_title(); ?></h3>
                            
                            <?php 
                            $designation = get_field('designation');
                            if ($designation) : ?>
                                <p class="designation"><?php echo esc_html($designation); // Display ACF designation ?></p>
                            <?php endif; ?>
                            
						<?php 
							$social_link_url = get_field('social_link_url');
							$social_link_image = get_field('social_link_image');
							$social_link_text = get_field('social_link_text'); // Fetch social link text

							if ($social_link_image && $social_link_url) : ?>
								<a href="<?php echo esc_url($social_link_url); ?>" target="_blank" tabindex="-1" style="display: flex; align-items: center; text-decoration: none;">
									<img src="<?php echo esc_url($social_link_image['url']); ?>" alt="<?php the_title(); ?> LinkedIn" class="social-link-image" style="margin-right: 10px;">
									<span class="social-link-text" style="color: #1E3E37;"><?php echo esc_html($social_link_text); ?></span>
								</a>
						<?php endif; ?>

                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </section>
    <?php else :
        echo '<p>No team members found.</p>';
    endif;
    
    wp_reset_postdata(); // Reset the post data
    
    return ob_get_clean(); // Return the buffered content
}

// Register the shortcode
add_shortcode('team_members', 'team_members_shortcode');
// Add this code to your theme's functions.php file

function display_milestones_timeline() {
    // Output buffering to capture the HTML content
    ob_start(); 

    // Define the query arguments
    $args = array(
        'post_type' => 'milestones', // Change to your custom post type
        'posts_per_page' => -1,
        'orderby' => 'date', // Sort by date
        'order' => 'DESC', // Descending order (latest first)
    );

    $milestones_query = new WP_Query($args);
    // Start the main container with Flexbox
    echo '<div id="timeline-wrapper" class="milestones-container">'; // Flexbox container

    // Left Column with Heading
    echo '<div class="milestones-left">'; // Left side column
    echo '<h4 class="heading-main">The Morgen Energy<br> story so far</h2>'; // Heading
    echo '</div>'; // End left side column

    // Right Column with Timeline
    echo '<div class="milestones-right">'; // Right side column
    echo '<div class="timeline">';
    echo '<ul>';
    echo '<span class="default-line"></span>';
    echo '<span class="draw-line"></span>';

    if ($milestones_query->have_posts()) :
        while ($milestones_query->have_posts()) : $milestones_query->the_post(); ?>
            <li>
              <div class="timeline-container">
                  <h3 class="timeline-title"><?php the_title(); ?></h3>
                  <p class="timeline-desc"><?php echo wp_trim_words(get_the_content(), 20, '...'); ?></p>
                  <?php if (has_post_thumbnail()) : ?>
                      <?php $thumbnail_id = get_post_thumbnail_id(); ?>
                      <?php $thumbnail_url = get_the_post_thumbnail_url(); ?>
                      <?php $thumbnail_alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true); ?>
                      <img src="<?php echo esc_url($thumbnail_url); ?>" 
                           alt="<?php echo esc_attr($thumbnail_alt); ?>" class="timeline-image"> <!-- Adjust width and height as needed -->
                  <?php endif; ?>
                </div>
            </li>
        <?php endwhile;
        wp_reset_postdata();
    else : ?>
        <li>
          <div>
            <h3>No milestones found.</h3>
          </div>
        </li>
    <?php endif; 

    echo '</ul>';
    echo '</div>'; // End timeline
    echo '</div>'; // End right side column
    echo '</div>'; // End main container

    return ob_get_clean(); // Return the buffered content
}

// Enqueue the CSS and JS files
function enqueue_milestones_assets() {
       // Enqueue WordPress's built-in jQuery
    wp_enqueue_script('jquery'); // This loads the built-in version of jQuery

    // Register your additional scripts (if necessary)
    wp_enqueue_script('milestones-script-1', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js', array('jquery'), null, true);
    wp_enqueue_script('milestones-script-2', 'https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js', array('jquery'), null, true);
	
	// Register CSS file
    wp_enqueue_style('milestones-style', get_template_directory_uri() . '/assets/css/timeline.css', array(), rand(1,1000), 'all'); // Adjust the path as necessary

    // Register JS file
    wp_enqueue_script('milestones-stickonscroll-script', get_template_directory_uri() . '/assets/js/jquery.stickOnScroll.js', array('jquery'), null, true); // Adjust the path as necessary
    wp_enqueue_script('milestones-script', get_template_directory_uri() . '/assets/js/timeline.js', array('jquery', 'milestones-stickonscroll-script'), null, true); // Adjust the path as necessary
}

// Hook to enqueue scripts and styles
add_action('wp_enqueue_scripts', 'enqueue_milestones_assets');

// Register the shortcode
add_shortcode('milestones_timeline', 'display_milestones_timeline');

function in_the_news_posts_callback() {
    ob_start();
    include( get_stylesheet_directory() .'/shortcodes/in-the-news-posts-sc.php' );
    return ob_get_clean();
}
add_shortcode('in_the_news_posts_sc', 'in_the_news_posts_callback');

function team_video_slider_callback() {
    ob_start();
    include( get_stylesheet_directory() .'/shortcodes/team-video-slider.php' );
    return ob_get_clean();
}
add_shortcode('team-video-slider', 'team_video_slider_callback');

function news_cat_tabs_callback( $atts ) {
    $atts = shortcode_atts( array(
		'cat' => '3,4,6',
		'ppage' => 7,
	), $atts );
    $cat_ids    = explode(",", $atts['cat']);
    $ppage      = $atts['ppage'];
    $categories = !empty( $cat_ids ) ? get_categories( array('include' => $cat_ids ) ) : [] ;
    ob_start();
    include( get_stylesheet_directory() .'/shortcodes/news-cat-tabs-sc.php' );
    return ob_get_clean();
}
add_shortcode('news_cat_tabs_sc', 'news_cat_tabs_callback');
// Shortcode tabs AJAX handler
function morgen_paginate_tab_handler(){
    if ( ! wp_verify_nonce( $_POST['security'], 'morgen-nonce' ) ) {
        die ( 'Busted!');
    }
    $data           = $_POST['data'];
    $page           = (int)$_POST['page'];
    $paged          = $page ? $page : 1;
    $category__in   = array_key_exists( 'category__in', $data ) ? array_map( 'intval', $data['category__in'] ) : [] ;
    $args           = array( "category__in" => $category__in, "posts_per_page" => (int)$data['posts_per_page'], 'paged' => $paged ); 
    $the_query = new WP_Query( $args );
    ob_start();
    include( get_stylesheet_directory() .'/shortcodes/tabs-content.php' );
    echo ob_get_clean();
    wp_die();
}
add_action( 'wp_ajax_morgen_paginate_tab', 'morgen_paginate_tab_handler' );
add_action( 'wp_ajax_nopriv_morgen_paginate_tab', 'morgen_paginate_tab_handler' );

function morgen_map_callback() {
    ob_start();
    include( get_stylesheet_directory() .'/shortcodes/morgen-map-sc.php' );
    return ob_get_clean();
}
add_shortcode('morgen_map_sc', 'morgen_map_callback');

function morgen_teamtailor_callback() {
    ob_start();
    include( get_stylesheet_directory() .'/shortcodes/career-team-tailor.php' );
    return ob_get_clean();
}
add_shortcode('morgen_teamtailor', 'morgen_teamtailor_callback');

/*=============================================
=            BREADCRUMBS			            =
=============================================*/

//  to include in functions.php
function the_breadcrumb() {

    $sep = ' > ';

    if (!is_front_page()) {
	
	// Start the breadcrumb with a link to your homepage
        echo '<div class="breadcrumbs">';
        // echo '<a href="';
        // echo get_option('home');
        // echo '">';
        // bloginfo('name');
        // echo '</a>' . $sep;
	
	// Check if the current page is a category, an archive or a single page. If so show the category or archive name.
        if (is_category() || is_single() ){
            the_category('title_li=');
        } elseif (is_archive() || is_single()){
            if ( is_day() ) {
                printf( __( '%s', 'text_domain' ), get_the_date() );
            } elseif ( is_month() ) {
                printf( __( '%s', 'text_domain' ), get_the_date( _x( 'F Y', 'monthly archives date format', 'text_domain' ) ) );
            } elseif ( is_year() ) {
                printf( __( '%s', 'text_domain' ), get_the_date( _x( 'Y', 'yearly archives date format', 'text_domain' ) ) );
            } else {
                _e( 'Blog Archives', 'text_domain' );
            }
        }
	
	// If the current page is a single post, show its title with the separator
        if (is_single()) {
            echo $sep;
            the_title();
        }
	
	// If the current page is a static page, show its title.
        // if (is_page()) {
        //     echo the_title();
        // }
	
	// if you have a static page assigned to be you posts list page. It will find the title of the static page and display it. i.e Home >> Blog
        if (is_home()){
            global $post;
            $page_for_posts_id = get_option('page_for_posts');
            if ( $page_for_posts_id ) { 
                $post = get_post($page_for_posts_id);
                setup_postdata($post);
                the_title();
                rewind_posts();
            }
        }

        echo '</div>';
    }
}

function my_display_gravatar() { 
    global $author;
    get_the_author_meta();
    // Get User Email Address
    $getuseremail = $author->user_email;
    // Convert email into md5 hash and set image size to 200 px
    $usergravatar = 'http://www.gravatar.com/avatar/' . md5($getuseremail) . '?s=200';
    echo '<img src="' . $usergravatar . '" class="wpb_gravatar" />';
} 

if ( ! function_exists( 'add_priority_styles_func' ) ) {
	function add_priority_styles_func() {
        echo '<style>
            .navbar .navbar-brand, .navbar .navbar-collapse,
            .hero-container .hero-title, .hero-container .hero-description,
            .hero-container .anchors {
                visibility: hidden;
            }
        </style>';
	}
}
add_action( 'wp_head', 'add_priority_styles_func' );

function about_mobile_accordion() {
    ob_start();
    include( get_stylesheet_directory() .'/shortcodes/about-mobile-accordion.php' );
    return ob_get_clean();
}
add_shortcode('about_mobile_accordion_sc', 'about_mobile_accordion');

// Remove adminbar on frontend
add_filter('show_admin_bar', '__return_false');