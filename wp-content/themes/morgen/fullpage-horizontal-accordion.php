<?php 
/*
Template Name: Horizontal Accordion New
*/
get_header();?>
    <style>

/* List Styles */
ul {
  list-style: none;
}

/* Form Styles */
/* Hide the input */
input {
  position: absolute;
  top: -9999px;
  left: -9999px;
}

/* Label Styles */
label {
  display: block;
  float: left;
  height: 100vh;
  width: 40px; /* Tab width */
  overflow: hidden;
  background: #232632; /* $clr-bg-light */
  color: white;
  text-align: center;
  font-size: 14px;
  line-height: 50px; /* $tab-width + 10 */
  transition: background 300ms ease;
}

label:hover {
  transition-duration: 0s;
}

li:nth-child(even) > input + label {
  background: #272A37; /* $clr-bg-lighter */
}

label:hover,
li:nth-child(even) > input + label:hover {
  background: #3498db; /* $clr-primary */
  color: #fff;
  cursor: pointer;
}

/* Styles for slides when the accordion is open */
li input[type="radio"]:checked ~ .accslide {
  display: block;               /* Ensures the content is displayed */
}

input[type="radio"]:checked ~ label {
  background: #3498db; /* $clr-primary */
  color: #fff;
  cursor: default !important;
}

/* Slide Styles */
.accslide {
  display: block;
  height: 100%;
  width: 0px;
  padding: 10px 0;
  float: left;
  overflow-x: hidden;
  font-size: 12px;
  line-height: 1.5;
  white-space: nowrap; /* Prevents text squishing */
  transition: all 700ms ease;
}

.accslide * {
  padding-left: 10px;
}

.accslide img {
  margin-top: 10px;
}

input[type="radio"]:not(:checked) ~ label > * {
  padding-left: 7px;
  font-size: 1.2em;
  white-space: nowrap;
  transform: rotate(90deg);
}

input[type="radio"]:checked ~ label > * {
  display: none;
}

.content {
    display: block;                     /* Display as block element */
    white-space: normal;                /* Allow text to wrap normally */
    word-wrap: break-word;              /* Break long words */
    overflow-wrap: break-word;          /* Break long words in modern browsers */
    padding: 10px;   
}
/* Style for headings */
.content h1 {
  font-size: 24px;              /* Heading size */
  margin-bottom: 10px;         /* Space below the heading */
  color: #3498db;               /* Color for the heading */
}

/* Style for paragraphs */
.content p {
  font-size: 16px;              /* Text size for paragraphs */
  line-height: 1.6;             /* Improved line height for readability */
  margin: 0;                    /* Remove default margin */
}
/* Slide Width Adjustments for Each Tab */
li:nth-child(1):nth-last-child(8) input[type="radio"]:checked ~ .accslide,
li:nth-child(2):nth-last-child(7) input[type="radio"]:checked ~ .accslide,
li:nth-child(3):nth-last-child(6) input[type="radio"]:checked ~ .accslide,
li:nth-child(4):nth-last-child(5) input[type="radio"]:checked ~ .accslide,
li:nth-child(5):nth-last-child(4) input[type="radio"]:checked ~ .accslide,
li:nth-child(6):nth-last-child(3) input[type="radio"]:checked ~ .accslide,
li:nth-child(7):nth-last-child(2) input[type="radio"]:checked ~ .accslide,
li:nth-child(8):nth-last-child(1) input[type="radio"]:checked ~ .accslide {
  width: calc(100% - 8 * 40px);
}

li:nth-child(1):nth-last-child(7) input[type="radio"]:checked ~ .accslide,
li:nth-child(2):nth-last-child(6) input[type="radio"]:checked ~ .accslide,
li:nth-child(3):nth-last-child(5) input[type="radio"]:checked ~ .accslide,
li:nth-child(4):nth-last-child(4) input[type="radio"]:checked ~ .accslide,
li:nth-child(5):nth-last-child(3) input[type="radio"]:checked ~ .accslide,
li:nth-child(6):nth-last-child(2) input[type="radio"]:checked ~ .accslide,
li:nth-child(7):nth-last-child(1) input[type="radio"]:checked ~ .accslide {
  width: calc(100% - 7 * 40px);
}

li:nth-child(1):nth-last-child(6) input[type="radio"]:checked ~ .accslide,
li:nth-child(2):nth-last-child(5) input[type="radio"]:checked ~ .accslide,
li:nth-child(3):nth-last-child(4) input[type="radio"]:checked ~ .accslide,
li:nth-child(4):nth-last-child(3) input[type="radio"]:checked ~ .accslide,
li:nth-child(5):nth-last-child(2) input[type="radio"]:checked ~ .accslide,
li:nth-child(6):nth-last-child(1) input[type="radio"]:checked ~ .accslide {
  width: calc(100% - 6 * 40px);
}

li:nth-child(1):nth-last-child(5) input[type="radio"]:checked ~ .accslide,
li:nth-child(2):nth-last-child(4) input[type="radio"]:checked ~ .accslide,
li:nth-child(3):nth-last-child(3) input[type="radio"]:checked ~ .accslide,
li:nth-child(4):nth-last-child(2) input[type="radio"]:checked ~ .accslide,
li:nth-child(5):nth-last-child(1) input[type="radio"]:checked ~ .accslide {
  width: calc(100% - 5 * 40px);
}

li:nth-child(1):nth-last-child(4) input[type="radio"]:checked ~ .accslide,
li:nth-child(2):nth-last-child(3) input[type="radio"]:checked ~ .accslide,
li:nth-child(3):nth-last-child(2) input[type="radio"]:checked ~ .accslide,
li:nth-child(4):nth-last-child(1) input[type="radio"]:checked ~ .accslide {
  width: calc(100% - 4 * 40px);
}

li:nth-child(1):nth-last-child(3) input[type="radio"]:checked ~ .accslide,
li:nth-child(2):nth-last-child(2) input[type="radio"]:checked ~ .accslide,
li:nth-child(3):nth-last-child(1) input[type="radio"]:checked ~ .accslide {
  width: calc(100% - 3 * 40px);
}

li:nth-child(1):nth-last-child(2) input[type="radio"]:checked ~ .accslide,
li:nth-child(2):nth-last-child(1) input[type="radio"]:checked ~ .accslide {
  width: calc(100% - 2 * 40px);
}

li:nth-child(1):nth-last-child(1) input[type="radio"]:checked ~ .accslide {
  width: calc(100% - 1 * 40px);
}
        
    </style>
    <ul>
        <li>
            <input id="rad1" type="radio" name="rad" checked>
            <label for="rad1">
                <div>Strategically located</div>
            </label>
            <div class="accslide">
                <div class="content">
                    <h1>Just keep going with longer text</h1>
                    <p>Njordkraft Hydrogen will source electricity from existing and future offshore wind farms with a connection point at the Endrup substation, 17 km north-east of the site. Esbjerg's strategic location optimises the plant's integration with existing pipeline networks, fostering cost-competitive hydrogen markets and enhancing cross-border trade, especially between Denmark and Germany. This placement ensures access to key markets and underpins the project's viability.</p>
                </div>
            </div>
        </li>
        <li>
            <input id="rad2" type="radio" name="rad">
            <label for="rad2">
                <div>Second panel</div>
            </label>
            <div class="accslide">
                <div class="content">
                    <h1>Second panel</h1>
                    <p>Lorem ipsum...</p>
                </div>
            </div>
        </li>
        <li>
            <input id="rad3" type="radio" name="rad">
            <label for="rad3">
                <div>Third panel</div>
            </label>
            <div class="accslide">
                <div class="content">
                    <h1>Third panel</h1>
                    <p>Lorem ipsum...</p>
                </div>
            </div>
        </li>
        <li>
            <input id="rad4" type="radio" name="rad">
            <label for="rad4">
                <div>Fourth panel</div>
            </label>
            <div class="accslide">
                <div class="content">
                    <h1>Fourth panel</h1>
                    <p>Lorem ipsum...</p>
                </div>
            </div>
        </li>
    </ul>


