// function animateOnTime(second, element){
//     setTimeout(function(){
//         element.classList.add('animate-first');
//         element.classList.remove('animate');
//     }, second);
// }
// function isElementInViewport (el) {

//     if (typeof jQuery === "function" && el instanceof jQuery) {
//         el = el[0];
//     }

//     var rect = el.getBoundingClientRect();

//     return (
//         rect.top >= 0 &&
//         rect.left >= 0 &&
//         rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) && /* or $(window).height() */
//         rect.right <= (window.innerWidth || document.documentElement.clientWidth) /* or $(window).width() */
//     );
// }

// function onVisibilityChange(el, callback) {
//     var old_visible;
//     return function () {
//         var visible = isElementInViewport(el);
//         if (visible != old_visible) {
//             old_visible = visible;
//             if (typeof callback == 'function') {
//                 callback();
//             }
//         }
//     }
// }
// var element = document.querySelector('.about-container');
// var handler = onVisibilityChange(element, function() {
// 	console.log('here');
//     let about_title = document.querySelector('.about-title');
//     let about_desc = document.querySelector('.about-description');
//     let about_anchor = document.querySelector('.about-anchors');
//     let about_side_image = document.querySelector('.about_side_image');
//     animateOnTime(1000, about_title);
//     animateOnTime(1000, about_side_image);
//     animateOnTime(1500, about_desc);
//     animateOnTime(2000, about_anchor);
// });

// // Non-jQuery
// if (window.addEventListener) {
//     addEventListener('scroll', handler, false);
// } else if (window.attachEvent)  {
//     attachEvent('onscroll', handler);
// }

// window.addEventListener('load', function(){
//     console.log('here');
    
//     let nav = document.getElementById('navigation_menu');
//     let hero_title = document.querySelector('.hero-title');
//     let hero_desc = document.querySelector('.hero-description');
//     let anchors = document.querySelector('.anchors');
//     let hero_side_image = document.querySelector('.hero_side_image');
//     animateOnTime(50, nav);
//     animateOnTime(70, hero_title);
//     animateOnTime(70, hero_side_image);
//     animateOnTime(500, hero_desc);
//     animateOnTime(600, anchors);
// });

jQuery(document).ready(function($) {
  const animateHeading          = $('.animate-heading');
  const animateDescription      = $('.animate-description');
  const animateButton           = $('.animate-button');
  const animateScroll           = $('.animate-scroll');

  if($('.morgen-news-main-wrap .morgen-news-inner').length){
    $('.morgen-news-main-wrap .morgen-news-inner').slick({
        arrows: true,
        dots: true,
        infinite: false,
        slidesToShow: 2,
        slidesToScroll: 1,
        centerMode: false,
        variableWidth: false,
        responsive: [
          {
            breakpoint: 767,
            settings: {
              slidesToShow: 1,
              arrows: false,
              dots: false,
            }
          }
        ]
    });
  }
  if($('.morgen-tvs-wrap .morgen-tvs-inner').length){
    $('.morgen-tvs-wrap .morgen-tvs-inner').slick({
        arrows: true,
        dots: true,
        infinite: false,
        slidesToShow: 2,
        slidesToScroll: 1,
        centerMode: false,
        variableWidth: false,
        responsive: [
          {
            breakpoint: 767,
            settings: {
              slidesToShow: 1,
              arrows: false,
              dots: false,
            }
          }
        ]
    });
  }
  if($('.news-slider-wrap').length){
    $('.news-slider-wrap').slick({
        arrows: false,
        dots: false,
        infinite: false,
        slidesToShow: 1,
        slidesToScroll: 1,
        centerMode: false,
        variableWidth: true,
    });
  }
  if($('.post-slider-wrap').length){
    $('.post-slider-wrap').slick({
        arrows: false,
        dots: false,
        infinite: false,
        slidesToShow: 1,
        slidesToScroll: 1,
        centerMode: false,
        variableWidth: true,
    });
  }

  $('body').on('click', '.share-post.copy-link', function(){
      const currElem    = $(this);
      const textContent = currElem.attr('data-attr');
      navigator.clipboard.writeText(textContent).then(() => {
          currElem.append('<span class="copied-url" style="margin-left: 4px;color: #26b99a;">Copied!<span>');
      },() => {
          console.error('Failed to copy');
      });
      setTimeout( function(  ){
          currElem.find('.copied-url').remove();
      }, 500 );
  });

	var screen = $(window);
    if (screen.width() > 991) {
		$('.dropdown-with-link .nav-link').removeAttr('data-bs-toggle');
		$('.dropdown-menu-start').hover(function() {
		  $(this).children('.dropdown-menu').addClass('show');
		}, function() {
			$(this).children('.dropdown-menu').removeClass('show');
		});
	} else {
		$('.dropdown-toggle').click(function(){
			$(this).children('.dropdown-menu').slideToggle('show');
		});
	}

  $('.popup-video').magnificPopup({
    type: 'iframe',
    mainClass: 'mfp-fade',
    removalDelay: 160,
    preloader: false,
    fixedContentPos: false
  });

  if($('.animated-btn-wrap').length){
    $('.animated-btn-wrap a').addClass('animated-btn');
  }
  if($('.contact-form-main').length){
    $('.contact-form-main #gform_submit_button_1').wrap('<div class="animated-btn"></div>');
  }
  $('.wp-block-group.not-animated-btn').each(function() {
    $(this).find('.section-5').addClass('not-animated-btn');
  });

  $(window).on('load', function() {
    if($('.navbar .navbar-brand').length){
      ScrollReveal().reveal('.navbar .navbar-brand', { 
        duration: 1000,
        origin: 'top',
        distance: '50px',
        reset: false,
        beforeReveal: function (el) {
          el.style.visibility = 'visible';
        }
      });
    }
    if($('.navbar .navbar-collapse').length){
      ScrollReveal().reveal('.navbar .navbar-collapse', { 
        duration: 1000,
        origin: 'top',
        distance: '50px',
        delay: '600',
        reset: false,
        beforeReveal: function (el) {
          el.style.visibility = 'visible';
        }
      });
    }

    if(animateHeading.length){
      ScrollReveal().reveal(animateHeading, { 
        duration: 1000,
        origin: 'bottom',
        distance: '50px',
        reset: false,
        beforeReveal: function (el) {
          el.style.visibility = 'visible';
        }
      });
    }
    if(animateScroll.length){
      ScrollReveal().reveal(animateScroll, { 
        duration: 1000,
        origin: 'bottom',
        distance: '50px',
        delay: '600',
        reset: false,
        beforeReveal: function (el) {
          el.style.visibility = 'visible';
        }
      });
    }
    if(animateDescription.length){
      ScrollReveal().reveal(animateDescription, { 
        duration: 1000,
        origin: 'bottom',
        distance: '50px',
        delay: '600',
        reset: false,
        beforeReveal: function (el) {
          el.style.visibility = 'visible';
        }
      });
    }
    if(animateButton.length){
      ScrollReveal().reveal(animateButton, { 
        duration: 1000,
        origin: 'bottom',
        distance: '50px',
        delay: '600',
        reset: false,
        beforeReveal: function (el) {
          el.style.visibility = 'visible';
        }
      });
    }
  });
	
	$('.navbar-toggler').click(function(){
		if (!$(this).hasClass('collapsed')) {
		  $('body').addClass('overflow-hidden');
		} else {
		  $('body').removeClass('overflow-hidden');
		}
	});

  const animatedButtons = [
    '.hero-container .anchor-1',
    '.hero-container .anchor-2',
    '.main-header .navbar-nav > .menu-item:last-child .nav-link',
    '.cta-section .cta-btn a',
    '.about-container .anchor-1',
    '.section-5:not(.not-animated-btn) .w-button',
    '.home-news-area .action-btn',
    '.animated-btn'
  ];
  
  $(animatedButtons.join(',')).each(function() {
    var text = $(this).text();
    $(this).attr('data-text', text);
  });

  $('.contact-form-main .animated-btn').each(function() {
    $(this).attr('data-text', 'Send message');
  });

  // Subscription form - Modal
  console.log( 'Subscription form - Modal' );
  $('.btn-subscribe').magnificPopup({
    type: 'inline',
    preloader: false,
    modal: true,
    closeOnBgClick: true
  });

  $(document).on('click', '.popup-modal-dismiss', function(e) {
    e.preventDefault();
    $.magnificPopup.close(); // Closes the modal when button is clicked
  });
  
});

const root = document.documentElement;
const marqueeElementsDisplayed = getComputedStyle(root).getPropertyValue("--marquee-elements-displayed");
const marqueeContent = document.querySelector("ul.marquee-content");

root.style.setProperty("--marquee-elements", marqueeContent.children.length);

for(let i=0; i<marqueeElementsDisplayed; i++) {
  marqueeContent.appendChild(marqueeContent.children[i].cloneNode(true));
}
