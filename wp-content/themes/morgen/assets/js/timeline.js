jQuery(document).ready(function( $ ) {
  // Timeline Scroll Section
  // --------------------------------------------------------------
  var items = $(".timeline li"),
      greyLine = $('.default-line'),
      lineToDraw = $('.draw-line'),
      timelineContainer = $('.timeline');

  var timelineMainContainer = $('.about-timeline-wrap');
  var nextDivs    = timelineMainContainer.nextAll('div');
  var offset      = timelineMainContainer.offset().top; // Get the initial top offset of the element
  var lastScrollTop = 0;

  const disableDOMScroll = ( $elem, $nextElem  ) => {
    if( $elem.hasClass( 'scroll-active' ) ){
        $elem.on('scroll mousewheel', function(e){
            e.preventDefault();
            return false;
        });
      
        $elem.find('.milestones-right').on('scroll touchmove mousewheel', function(e){
          e.stopPropagation(); // Prevent bubbling up to the parent
        });

        $nextElem.each(function(index) {
          $(this).on('scroll mousewheel', function(e){
              e.preventDefault();
              return false;
          });
        })
    }else{
      $elem.on('scroll mousewheel', function(e){
        e.stopPropagation(); 
      });
      $nextElem.each(function(index) {
        $(this).on('scroll mousewheel', function(e){
          e.stopPropagation(); 
        });
      })
    }
  }

  

  if( items.length ){
    let greyLineHeight  = 0;
    items.each(function(index) {
      greyLineHeight += $(this).outerHeight(true);
    });
    greyLine.css( 'height', greyLineHeight+'px' );
  }

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
          'height': lineHeight + 200 + 'px' // Add extra space to the line
        });
      }

      // This takes care of adding the class in-view to the li:before items
      var bottom = lineToDraw.offset().top + lineToDraw.outerHeight(true);

      items.each(function(index) {
        var circlePosition = $(this).offset();

        // Check if the circle position is within the viewable area of the timeline
        if ( 
            ( bottom >= circlePosition.top && circlePosition.top + $(this).outerHeight() > timelineContainer.scrollTop() ) || 
            ( timelineContainer.scrollTop() + containerHeight >= Math.floor( greyLineHeight ) - 20 )
          ) {
          $(this).addClass('in-view');
        }else {
          $(this).removeClass('in-view');
        }

        // if( timelineContainer.scrollTop() + containerHeight >= Math.floor( greyLineHeight ) - 20 ){
        //   timelineMainContainer.removeClass('scroll-active');
        //   disableDOMScroll( timelineMainContainer, nextDivs );
        // }

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



  // $(window).scroll(function() { 
    
  //   if ( $(window).scrollTop() > offset ) {
  //     timelineMainContainer.addClass('scroll-active');
  //   } else {
  //     timelineMainContainer.removeClass('scroll-active');
  //   }

  //   var st = $(this).scrollTop();
  //   if (st > lastScrollTop) {
  //       // Scrolling down
  //       console.log("Scrolling down");
  //       disableDOMScroll( timelineMainContainer, nextDivs );
  //   } else {
  //       // Scrolling up
  //       console.log("Scrolling up");
  //       timelineMainContainer.removeClass('scroll-active');
  //   }

  //   // Update the last scroll position
  //   lastScrollTop = st;
  // });

});
