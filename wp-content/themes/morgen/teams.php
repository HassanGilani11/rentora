<?php
/*
Template Name: Team Slider
*/

get_header(); ?>
<section class="post-container team-about">
        <div class="team-slider">
            <?php 
            // Query for team members
            $args = array(
                'post_type' => 'team-member', // Change to your custom post type
                'posts_per_page' => -1,
                'orderby' => 'date', // Sort by date
                'order' => 'ASC', // Ascending order (first to last)
            );
            $team_members = new WP_Query($args);
    
            if ($team_members->have_posts()) :
                while ($team_members->have_posts()) : $team_members->the_post(); ?>
                    <div class="team-member">
                        <?php if (has_post_thumbnail()) : ?>
                            <img src="<?php the_post_thumbnail_url(); ?>" alt="<?php the_title(); ?>" class="member-image">
                        <?php endif; ?>
                        <h3><?php the_title(); ?></h3>
                        <p class="designation"><?php the_field('designation'); ?></p>
                        <?php 
                        $social_link_url = get_field('social_link_url'); // ACF URL field
                        $social_link_image = get_field('social_link_image'); // ACF Image field
                        
                        // Display the image as a clickable link
                        if ($social_link_image && $social_link_url) : ?>
                            <a href="<?php echo esc_url($social_link_url); ?>" target="_blank" tabindex="-1">
                                <img src="<?php echo esc_url($social_link_image['url']); ?>" alt="<?php the_title(); ?> LinkedIn" class="social-link-image">
                            </a>
                            <a href="<?php echo esc_url($social_link_url); ?>" class="social-link-text"><?php the_field('social_link_text'); ?></a>
                        <?php endif; ?>
                    </div>
                <?php endwhile;
            else :
                echo '<p>No team members found.</p>';
            endif;
    
            wp_reset_postdata(); ?>
        </div>
</section>

<?php get_footer(); ?>

<!-- Include Slick CSS and JS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
<script>
    jQuery(document).ready(function($) {
        $('.team-slider').slick({
            arrows: true,
            dots: true,
            infinite: false,
            slidesToShow: 3, // Show 3 full slides
            slidesToScroll: 1, // Scroll one at a time
            centerMode: false, // Disable centering
            variableWidth: false, // Let CSS handle the width
            responsive: [
                {
                    breakpoint: 768, // For smaller screens
                    settings: {
                        slidesToShow: 1,
                        centerMode: false, // No center mode for mobile
                        centerPadding: '0', // No padding for mobile
                    }
                }            ]
        });
    });
</script>

<style>
.post-container.team-about{
    height:100%;
    padding-left: 40px;
    padding-right: 40px;	
}
/* Center the slider and ensure full width */
.team-slider {
    margin: 40px auto; /* Center the slider */
    width: 96%; /* Full width of container */
    padding-right: 2%; /* Allow 10% of the fourth slide to be visible */
}

/* Styling for individual team members */
.team-member {
    padding: 5px;
    margin: 5px;
    text-align: center;
    box-sizing: border-box;
}
.team-member h3 {
    color: #1E3E37;
    font-family: 'TroisMilleRegular';
    font-size: 22px;
    font-weight: 400;
    line-height: 40px;
    text-align: left;
}
.member-image {
    width: 100%; /* Full width for member image */
    height: auto; /* Maintain aspect ratio */
}

/* Slick Slide Specifics */
.slick-slide {
    opacity: 1;
    transition: opacity 0.3s ease;
}



/* Active slide opacity */
.slick-active {
    opacity: 1;
}
/* Hide default content and add custom SVG for right arrow (Next) */
.slick-next {
    background-image: url('/wp-content/uploads/2024/09/right-arrow-icon.svg'); /* Add your right arrow icon here */
    background-size: 20px 20px; /* Adjust size */
    background-repeat: no-repeat;
    background-position: center;
    width: 50px;
    height: 50px;
    color: transparent;
    border: 1px solid;
    outline: none;
    cursor: pointer;
    z-index: 1;
    border-radius: 100%;
}

/* Hide the default text */
.slick-next:before {
    content: ''; /* Removes default content */
}

/* Left arrow (Prev) - mirror the next arrow (optional if you have a matching icon) */
.slick-prev {
    background-image: url('/wp-content/uploads/2024/09/left-arrow-icon.svg'); /* Add your left arrow icon */
    background-size: 20px 20px; /* Adjust size */
    background-repeat: no-repeat;
    background-position: center;
    width: 50px;
    height: 50px;
    color: transparent;
    border: 1px solid;
    outline: none;
    cursor: pointer;
    z-index: 1;
    border-radius: 100%;
}

/* Hide the default text */
.slick-prev:before {
    content: ''; /* Removes default content */
}

/* Adjust positioning for the arrows */
.slick-next {
    right: -50px; /* Position further out if needed */
}
.slick-prev {
    left: 90%; /* Position further out if needed */
}

/* Navigation arrows */
.slick-prev, .slick-next {
    font-size: 0; /* Hides text but keeps the button */
    color: #333;
    position: absolute;
    top: 100%;
    margin-top: 15px;
}
.slick-next:focus, .slick-next:hover {
    background-color: transparent;
    opacity: 1;
    background-image: url(/wp-content/uploads/2024/09/right-arrow-icon.svg);
    background-size: 20px 20px;
    background-repeat: no-repeat;
    background-position: center;
    width: 50px;
    height: 50px;
    color: transparent;
    border: 1px solid #1E3E37;
    outline: none;
    cursor: pointer;
    z-index: 1;
    border-radius: 100%;
}
.slick-prev:focus, .slick-prev:hover {
    background-color: transparent;
    opacity: 1;
    background-image: url(/wp-content/uploads/2024/09/left-arrow-icon.svg);
    background-size: 20px 20px;
    background-repeat: no-repeat;
    background-position: center;
    width: 50px;
    height: 50px;
    color: transparent;
    border: 1px solid #1E3E37;
    outline: none;
    cursor: pointer;
    z-index: 1;
    border-radius: 100%;
}

button.slick-prev.slick-arrow.slick-disabled {
    left: 90%;
}
button.slick-next.slick-arrow {
    left: 95%;
}

/* Dots customization */
.slick-dots {
    bottom: -30px;
    left: -46%;
}
.slick-dots li button:before {
    font-size: 10px;
}
.slick-dots li.slick-active button:before {
    opacity: 1;
    color: #1E3E37;
}
.slick-dots li {
    margin: 0 2px;
}

/* Adjust overflow for partial visibility */
.slick-list {
   overflow: visible; /* Show partial slides */
    padding-right: 0;
}
}

/* Social link styling */
.social-link-image {
    width: 50px; /* Adjust size of social link image */
    height: 50px; /* Consistent height */
    margin-top: 10px; /* Space above the social link image */
}

.social-link-text {
    display: inline-block; /* Inline block for the link */
    margin-top: 5px; /* Space above the text */
    text-decoration: none; /* Remove underline */
    color: #0073aa; /* Customize link color */
}

.social-link-text:hover {
    text-decoration: underline; /* Underline on hover */
}
.post-container.team-about .wrapper .col-md-12.col-sm-12 .heading-main-team {
    color: #1E3E37;
    font-family: 'TroisMilleRegular';
    font-size: 40px;
    font-weight: 400;
    line-height: 40px;
    text-align: left;
}
/* Responsive behavior for smaller screens */
@media screen and (max-width: 767px) {
	.post-container.team-about h4.heading-main{
		margin-left: 0px !important;
	}
    .slick-slide {
        width: 100%; /* Show one full team member on mobile */
        padding-right: 0;
    }
    
    .slick-list {
        overflow: hidden; /* No need for partial visibility on mobile */
    }
}


</style>

