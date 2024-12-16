// initializeBlock = function() {
// 	const carousels = document.getElementsByClassName('is_tab');

// 	// Loop through all carousels.
// 	// for ( var i = 0; i < carousels.length; i++ ) {
// 	// 	var flkty = new Flickity( carousels[i], {
// 	// 		cellAlign: 'left',
// 	// 		contain: true,
// 	// 		wrapAround: true,
// 	// 		draggable: false,
// 	// 	});
// 	// }
// }

// if( window.acf ) {
// 	window.acf.addAction( 'render_block_preview/type=post-tab', initializeBlock );
// } else {
// 	initializeBlock();
    
// }

// window.addEventListener('load', function(){
//     $("#v-pills-0-tab").click();
// })

    jQuery(document).ready(function($) {
        // Function to check if the element is in the viewport
        function isInViewport($element) {
            var elementTop = $element.offset().top;
            var elementBottom = elementTop + $element.outerHeight();
            var viewportTop = $(window).scrollTop();
            var viewportBottom = viewportTop + $(window).height();

            return elementBottom > viewportTop && elementTop < viewportBottom;
        }

        // Function to add/remove active class on scroll
        function handleScroll() {
            $('.post-column').each(function() {
                if (isInViewport($(this))) {
                    $(this).addClass('active');
                } else {
                    $(this).removeClass('active');
                }
            });
        }

        // Run the function when the page is scrolled
        $(window).on('scroll', handleScroll);

        // Run on page load in case some elements are already in view
        handleScroll();
    });