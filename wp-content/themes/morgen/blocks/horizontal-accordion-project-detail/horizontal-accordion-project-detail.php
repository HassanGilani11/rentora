<?php
/*
Template Name: Horizontal Accordion
 *
 * @param array $block The block settings and attributes.
 */

// Load values and assign defaults.
$location_name_one       = get_field( 'location_name_one' );
$title_one       = get_field( 'title_one' );
$description_one = get_field( 'description_one' );
$image_one      = get_field( 'image_one' );

$location_name_two       = get_field( 'location_name_two' );
$title_two       = get_field( 'title_two' );
$description_two = get_field( 'description_two' );
$image_two      = get_field( 'image_two' );

$location_name_three       = get_field( 'location_name_three' );
$title_three     = get_field( 'title_three' );
$description_three = get_field( 'description_three' );
$image_three    = get_field( 'image_three' );

$location_name_four       = get_field( 'location_name_four' );
$title_four      = get_field( 'title_four' );
$description_four = get_field( 'description_four' );
$image_four     = get_field( 'image_four' );

// Create class attribute allowing for custom "className" and "align" values.
$class_name = 'horizontal-accordion';
if ( ! empty( $block['className'] ) ) {
    $class_name .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
    $class_name .= ' align' . $block['align'];
}

?>
<style>

.accordion-table {
  display: flex;
  width: 100%;
  height: 100%;
    border-top: 2px solid;
    border-bottom: 2px solid;  
}

.table__cell {
  position: relative;
  display: flex;
  width: 25%; /* Default width for each cell */
  align-items: flex-start; /* Align items to the top */
  justify-content: center; /* Center items horizontally */
  transition: width 500ms cubic-bezier(0.4, 0, 0.2, 1);
/* 	transition: width 500ms ease-in-out, height 500ms ease-in-out; */
}

/* Specific background colors for each section */
.table__cell:nth-child(1) {
  background-color: #F5F5F5; /* Color for first cell */
}

.table__cell:nth-child(2) {
  background-color: #FFFF00; /* Color for second cell */
}

.table__cell:nth-child(3) {
  background-color: #D7E6FF; /* Color for third cell */
}

.table__cell:nth-child(4) {
  background-color: #F5F5F5; /* Color for fourth cell */
}

/* Adjust active section styles */
.table__cell.active {
  min-width: 70%; /* Active section takes 70% */
}

/* Non-active sections take 10% */
.table__cell:not(.active) {
  width: 10%; /* Non-active sections take 10% */
	border: 1px solid;
}

/* Flexbox for sub and main columns */
.sub-column {
    display: flex;
    flex-direction: column; /* Stack elements vertically */
    justify-content: space-between; /* Space between the elements */
    height: 100%; /* Ensure it takes the full height */
    width: 120px;    
    text-align: center; /* Center the text if needed */
    padding: 20px 0px;    
}
.sub-column .num {
    margin-bottom: auto; /* Push the number span to the top */
    background: transparent;
    font-family: 'TroisMilleRegular';
    font-size: 32px;
    font-weight: 400;
    color: #1E3E37;
    text-align: center;
    margin-left: -20px;    
}

.sub-column .loc {
    margin-top: auto; /* Push the location span to the bottom */
    transform: rotate(270deg);
    transform-origin: left top;
    white-space: nowrap;
    margin-left: 20px;
    background: transparent;
    font-family: 'TroisMilleRegular';
    font-size: 32px;
    font-weight: 400;
    color: #1E3E37;
}
.main-column {
  width: 80%; /* Main-column takes 80% of the active cell */
  padding: 30px 0px 0px; /* Optional padding for aesthetics */
  display: none; /* Hide by default */
}
.main-column .inner-column {
    background: transparent;
    width: 75%;    
}
.main-column h2 {
    font-family: 'TroisMilleRegular';
    font-size: 48px;
    font-weight: 400;
    color: #1E3E37;
    background: transparent;    
}
.main-column p {
    background: transparent;
    color: #1E3E37;
    font-family: "Montserrat", system-ui;
    font-size: 18px;
    font-weight: 500;
}
.main-column img {
    background: transparent;
    width: 100%;
}
/* Show main-column only if parent is active */
.table__cell.active .main-column {
  display: block; /* Show the main column when active */
}

/* Span styles for transitions */
.table__cell span {
  opacity: 1;
  transition: opacity 300ms cubic-bezier(0.4, 0, 0.2, 1);
  transition-delay: 0;
}

.table__cell.active span {
  opacity: 1;
  transition-delay: 300ms;
}

    
</style>
<div class="accordion-table">
    <div class="table__cell">
        <div class="sub-column">
            <span class="num">01</span>
            <span class="loc"><?php echo esc_html($location_name_one); ?></span>
        </div>
        <div class="main-column">	   
            <div class="inner-column">
                <h2><?php echo esc_html($title_one); ?></h2>
                <p><?php echo esc_html($description_one); ?></p>
                <?php if ($image_one): ?>
                    <img src="<?php echo esc_url($image_one['url']); ?>" alt="<?php echo esc_attr($title_one); ?>">
                <?php endif; ?>
            </div>
	    </div>
	</div>
	<div class="table__cell">
	    <div class="sub-column">
	        <span class="num">02</span>
            <span class="loc"><?php echo esc_html($location_name_two); ?></span>
	    </div>
        <div class="main-column">
            <div class="inner-column">	    
                <h2><?php echo esc_html($title_two); ?></h2>
                <p><?php echo esc_html($description_two); ?></p>
                <?php if ($image_two): ?>
                    <img src="<?php echo esc_url($image_two['url']); ?>" alt="<?php echo esc_attr($title_two); ?>">
                <?php endif; ?>
            </div>
	    </div>
	</div>
	<div class="table__cell">
	    <div class="sub-column">
	        <span class="num">03</span>
            <span class="loc"><?php echo esc_html($location_name_three); ?></span>
	    </div>
        <div class="main-column">	     

            <div class="inner-column">        
                <h2><?php echo esc_html($title_three); ?></h2>
                <p><?php echo esc_html($description_three); ?></p>
                <?php if ($image_three): ?>
                    <img src="<?php echo esc_url($image_three['url']); ?>" alt="<?php echo esc_attr($title_three); ?>">
                <?php endif; ?>
            </div>
	    </div>
	</div>	
	<div class="table__cell">
	    <div class="sub-column">
	        <span class="num">04</span>
            <span class="loc"><?php echo esc_html($location_name_four); ?></span>
	    </div>
        <div class="main-column">	    
            <div class="inner-column">
                <h2><?php echo esc_html($title_four); ?></h2>
                <p><?php echo esc_html($description_four); ?></p>
                <?php if ($image_four): ?>
                    <img src="<?php echo esc_url($image_four['url']); ?>" alt="<?php echo esc_attr($title_four); ?>">
                <?php endif; ?>
            </div>
	    </div>
	</div>	
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.js"></script>
<script>
$(document).ready(function() {
    var section = $('div.table__cell');

    // Set the first section to active by default
    section.first().addClass('active');

    function toggleAccordion() {
        // Remove active class from all sections
        section.removeClass('active');
        // Add active class to the clicked section
        $(this).addClass('active');
    }

    // Attach click event to each section
    section.on('click', toggleAccordion);

    var tbl = $('div.table');

    tbl.on({
        mouseenter: function() {
            tbl.find('div.slogan span').html('<b>Go. Make a difference</b>');
        },
        mouseleave: function() {
            tbl.find('div.slogan span').html('Prolicht DNA <b>25 + 4 Colors</b>');
        }
    });
});


</script>
