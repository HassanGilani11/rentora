<?php
/*
Template Name: Timeline Template
*/

get_header(); ?>
<div class="header">
  <h1>Scrolling Timeline</h1>
  <p>Scroll down on this codepen to see the timeline in action</p>
</div>

<div class="timeline">
  <ul>
    <span class="default-line"></span>
    <span class="draw-line"></span>
    <?php
    $args = array(
        'post_type' => 'milestones', // Change to your custom post type
        'posts_per_page' => -1,
        'orderby' => 'date', // Sort by date
        'order' => 'DESC', // Ascending order (first to last)
    );
    $team_members = new WP_Query($args);

    if ($team_members->have_posts()) :
        while ($team_members->have_posts()) : $team_members->the_post(); ?>
            <li>
              <div class="timeline-container">
                  <h3 class="timeline-title"><?php the_title(); ?></h3>
                  <p class="timeline-desc"><?php echo wp_trim_words(get_the_content(), 30, '...'); ?></p>
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
            <h3>No team members found.</h3>
          </div>
        </li>
    <?php endif; ?>
  </ul>
</div>



<style>


.header {
  height: 200px;
  text-align: center;
  background: linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.8)),
    url('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSwzzY9fjzAm7fIXKNZSmoU4LH-GoZOj7scpVvJm3w15M2nreWOHg');
  background-position: 50% 50%;
  background-color: light-grey;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  font-family: Droid Sans;
}

.header h1 {
  font-size: 3rem;
  color: #000000;
  font-family: 'Yatra One', cursive;
}

.header p {
  font-family: 'Yatra One', cursive;
  font-size: 1.5rem;
}

.timeline {
  position: relative;
  padding: 0;
  display: flex;
  justify-content: center;
  height: 750px; /* Fixed height for the timeline section */
  overflow-y: auto; /* Enable vertical scrolling within the timeline */
}
.timeline ul {
  list-style: none;
  padding: 0;
  margin: 0;
  position: relative;
  height: auto; /* Let the height adjust based on the content */
}

.timeline .default-line {
  content: '';
  position: absolute;
  left: 50%;
  width: 4px;
  background: #bdc3c7;
  height: 1650px;
}

.timeline .draw-line {
  width: 4px;
  height: 0;
  position: absolute;
  left: 50%;
  background: #A884FF;
}

.timeline ul li {
  position: relative;
  margin: 40px 0;
  min-height: 500px; /* Adjust height dynamically based on content */
  display: flex;
  align-items: center;
}
.timeline-container {
  flex: 1; /* Allow the container to fill the available space */
  padding: 20px; /* Add some padding for better spacing */
  box-sizing: border-box; /* Include padding in height calculations */
}
.timeline ul li.in-view {
  transition: 0.125s ease-in-out, background-color 0.2s ease-out, color 0.1s ease-out, border 0.1s ease-out;
}

.timeline ul li.in-view::before {
  content: '';
  position: absolute;
  left: 50%;
  top: 35px;
    transform: translateX(-42%);
    width: 20px;
    height: 20px;
  background-image: url('https://sg0duxoli5-flywheel.netdna-ssl.com/wp-content/themes/inspired_elearning_theme/images/check-dark.svg');
  background-color: #A884FF;
  background-size: 20px 20px;
  background-repeat: no-repeat;
  background-position: center;
  transition: 0.125s ease-in-out, background-color 0.2s ease-out, color 0.1s ease-out, border 0.1s ease-out;
}

.timeline ul li::before {
  content: '';
  position: absolute;
  left: 50%;
  top: 35px;
    transform: translateX(-42%);
    width: 20px;
    height: 20px;
  border-radius: 50%;
  background: #1E3E37;
  transition: all 0.4s ease-in-out;
}

/* Content box positioned to the right of the dot */
.timeline ul li div {
  position: absolute;
  right: 55%;
  width: 500px;
  background-color: #ffffff00;
  border-radius: 8px;
  transition: background-color 0.3s ease, border-color 0.3s ease;
  margin: 55px;
  top: -10%;
}

/* Add styles when li has the in-view class */
.timeline ul li.in-view div {
    border-color: #1E3E37;
    color: #fff;
    font-weight: bold;
    text-align: left;
}
.timeline ul li.in-view div h3 {
    color: #A884FF;
    font-family: 'TroisMilleRegular';
    font-size: 48px;
}
.timeline ul li.in-view div p {
    color: #1E3E37;
    font-weight: 500;
    font-size: 18px;
}
.timeline ul li.in-view div img.timeline-image{
    width:auto;
    height:400px;
}
.timeline-container img.timeline-image{
    width:auto;
    height:400px;
}
.timeline-container h3 {
    color: #1E3E37;
    font-family: 'TroisMilleRegular';
    font-size: 48px;
}
.timeline-container p {
    color: #1E3E37;
    font-weight: 500;
    font-size: 18px;
}
</style>





<script>
// Timeline Scroll Section
// --------------------------------------------------------------
// Timeline Scroll Section
// --------------------------------------------------------------
var items = $(".timeline li"),
    greyLine = $('.default-line'),
    lineToDraw = $('.draw-line'),
    timelineContainer = $('.timeline');

// sets the height that the greyLine (.default-line) should be according to `.timeline ul` height
// run this function only if draw line exists on the page
if(lineToDraw.length) {
  timelineContainer.on('scroll', function () {

    // Get the height of the grey line
    var greyLineHeight = greyLine.height(),
        containerScrollTop = timelineContainer.scrollTop(),
        containerHeight = timelineContainer.height(),
        timelineOffset = timelineContainer.offset().top;

    // Calculate the new height for the line based on scroll within the timeline container
    var lineHeight = containerScrollTop;

    // Limit the line height to the height of the grey line
    if(lineHeight <= greyLineHeight) {
      lineToDraw.css({
        'height': lineHeight + 20 + 'px' // Add extra space to the line
      });
    }

    // This takes care of adding the class in-view to the li:before items
    var bottom = lineToDraw.offset().top + lineToDraw.outerHeight(true);
    items.each(function(index) {
      var circlePosition = $(this).offset();

      // Check if the circle position is within the viewable area of the timeline
      if (bottom >= circlePosition.top && circlePosition.top + $(this).outerHeight() > timelineContainer.scrollTop()) {
        $(this).addClass('in-view');
      } else {
        $(this).removeClass('in-view');
      }
    });

    // Update the height of the drawing line when scrolled to the end
    var totalHeight = timelineContainer[0].scrollHeight;
    if (containerScrollTop + containerHeight >= totalHeight - 20) {
      lineToDraw.css({
        'height': greyLineHeight + 'px' // Set line to full height when at the bottom
      });
    }
  });
}

    
</script>

<?php get_footer(); ?>