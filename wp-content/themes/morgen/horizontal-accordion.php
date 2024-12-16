<?php
/*
Template Name: Horizontal Accordion
*/

get_header(); ?>
<style>

.table {
  display: flex;
  width: 100%;
  height: 100%;
}

.table__cell {
  position: relative;
  display: flex;
  width: 25%; /* Default width for each cell */
  align-items: flex-start; /* Align items to the top */
  justify-content: center; /* Center items horizontally */
  transition: width 500ms cubic-bezier(0.4, 0, 0.2, 1);
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
  width: 70%; /* Active section takes 70% */
}

/* Non-active sections take 10% */
.table__cell:not(.active) {
  width: 10%; /* Non-active sections take 10% */
}

/* Flexbox for sub and main columns */
.sub-column {
    display: flex;
    flex-direction: column; /* Stack elements vertically */
    justify-content: space-between; /* Space between the elements */
    height: 100%; /* Ensure it takes the full height */
    width: 120px;    
    text-align: center; /* Center the text if needed */
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
  padding: 30px 0px 30px; /* Optional padding for aesthetics */
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
<body>
<div class="table">
    
	<div class="table__cell">
	    <div class="sub-column">
	        <span class="num">01</span>
	        <span class="loc">Location</span>
	    </div>
        <div class="main-column">	   
            <div class="inner-column">
        	    <h2>Strategically located</h2>
        	    <p>Njordkraft Hydrogen will source electricity from existing and future offshore wind farms with a connection point at the Endrup substation, 17 km north-east of the site. 
                   Esbjerg's strategic location optimises the plant's integration with existing pipeline networks, fostering cost-competitive hydrogen markets and enhancing cross-border trade, especially between Denmark and Germany. This placement ensures access to key markets and underpins the project's viability.</p>
            <img src="/wp-content/uploads/2024/09/Column-2.png">
            </div>
	    </div>
	</div>
	<div class="table__cell">
	    <div class="sub-column">
	        <span class="num">02</span>
	        <span class="loc">Standard</span>
	    </div>

        <div class="main-column">
            <div class="inner-column">	    
        	    <h2>Highest standards only</h2>
        	    <p>In compliance with the EU's regulatory framework for green hydrogen (RED II/RED III/RFNBO), the power supplied to the plant will adhere to stringent sustainability standards. We have measures in place to ensure compliance, safety, and sustainability in our operations. 

Working alongside expert assurance providers, we verify compliant and safe designs, conduct risk assessments, adhere to industry practices for innovative technologies, and perform sustainability impact studies.</p>
                <img src="/wp-content/uploads/2024/09/Placeholder-Image-3.png">
            </div>
	    </div>
	</div>
	<div class="table__cell">
	    <div class="sub-column">
	        <span class="num">03</span>
	        <span class="loc">Transition</span>
	    </div>
        <div class="main-column">	     
            <div class="inner-column">        
        	    <h2>Powering the European transition</h2>
        	    <p>Using a transport pipeline constructed under the joint responsibility of Denmark, Germany, Belgium and the Netherlands, the green hydrogen produced will be distributed within these countries. 

The majority is expected to be utilised by Germany, supporting its transition to net-zero emissions in sectors such as steel production, on-road mobility, heat and power generation, and industrial operations.</p>
                <img src="/wp-content/uploads/2024/09/Placeholder-Image-1-1.png">
            </div>
	    </div>
	</div>	
	<div class="table__cell">
	    <div class="sub-column">
	        <span class="num">04</span>
	        <span class="loc">Benefits</span>
	    </div>
        <div class="main-column">	    
            <div class="inner-column">
            	<h2>Benefits for the local communities</h2>
        	    <p>The project will contribute to the local economy by creating approximately 60 permanent jobs and up to 700 jobs during the construction phase. 

Additionally, the facility will produce CO2-neutral surplus heat, which offers the potential to supply the majority of households in Esbjerg with district heating.
</p>
            <img src="/wp-content/uploads/2024/09/Placeholder-Image-2-1.png">
            </div>
	    </div>
	</div>	
</div>
</body>
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
<?php

get_footer(); ?>