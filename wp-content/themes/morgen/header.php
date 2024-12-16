<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo get_the_title(); ?> | 
        <?php bloginfo('name') ?>
    </title>
    <?php wp_head() ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.js"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
	<script type="text/javascript" src="https://www.bugherd.com/sidebarv2.js?apikey=ypdyvvd5x4adcmrfh66dig" async="true"></script>
	</head>
<body>
<header role="banner" class="main-header">
<nav class="navbar navbar-expand-lg navbar-light mr-auto animate" id="navigation_menu" role="navigation">
  <div class="container custom-container">
    
    <!-- Brand and toggle get grouped for better mobile display -->
    <a class="navbar-brand" href="<?php echo esc_url( home_url( '/', 'https' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
    <?php if( has_custom_logo() ):  ?>
                        <?php 
                            // Get Custom Logo URL
                            $custom_logo_id = get_theme_mod( 'custom_logo' );
                            $custom_logo_data = wp_get_attachment_image_src( $custom_logo_id , 'full' );
                            $custom_logo_url = $custom_logo_data[0];
                        ?>

                            <img src="<?php echo esc_url( $custom_logo_url ); ?>" 
                                 alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"/>
                    <?php else: ?>
                        <div class="site-name"><?php bloginfo( 'name' ); ?></div>
                    <?php endif; ?>
    </a>

    <button class="navbar-toggler collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#bs-example-navbar-collapse-1" aria-controls="bs-example-navbar-collapse-1" aria-expanded="false" aria-label="<?php esc_attr_e( 'Toggle navigation', 'your-theme-slug' ); ?>">
        <span class="toggler-icon"></span>
        <span class="toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
        <?php
        wp_nav_menu( array(
            'theme_location'    => 'header',
            'menu_class'                =>  '',
            'menu-container'            =>  'false',
            'fallback_cb'               => '__return_false',
            'items_wrap'                => '<ul id="%1$s" class="navbar-nav ms-auto mb-2 mb-lg-0 text-sm %2$s">%3$s</ul>',
            'depth'                     => 2,
            'walker'                    => new class_nav_walker(),
        ) );
        ?>
    </div>
    </div>
</nav>
</header>
    