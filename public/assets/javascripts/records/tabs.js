var isOverflow = document.querySelector('.content-sections-scroll');
var isAppended = false
var scrollPos

if ( isOverflow ) {
  window.setInterval(function() {
    if (isOverflow.offsetWidth < isOverflow.scrollWidth && isAppended === false) {
      $('<i class="icon icon-chevron tabs-right"></i>').appendTo('.content-sections');
      $('<i class="icon icon-chevron tabs-left hidden"></i>').appendTo('.content-sections');
      isAppended = true
    } else if (isOverflow.offsetWidth == isOverflow.scrollWidth && isAppended === true) {
      $('.tabs-right').remove();
      $('.tabs-left').remove();
      isAppended = false
    }
  }, 200);
}

$('.content-sections').on('click', '.tabs-left', function (e) {
  e.stopPropagation();
  scrollPos = $('.content-sections-scroll').scrollLeft();
  scrollPos = scrollPos - 250
  scroll ()
});

$('.content-sections').on('click', '.tabs-right', function (e) {
  e.stopPropagation();
  scrollPos = $('.content-sections-scroll').scrollLeft();
  scrollPos = scrollPos + 250
  scroll ()
});

var scrollWidth
var viewWidth
var maxScroll
function scroll () {
  scrollWidth = isOverflow.scrollWidth
  viewWidth = isOverflow.offsetWidth
  maxScroll = scrollWidth - viewWidth
  if (scrollPos > maxScroll) {
    scrollPos = maxScroll
  } else if (scrollPos < 0) {
    scrollPos = 0
  }
  $('.content-sections-scroll').animate({
    scrollLeft: scrollPos
  }, 80);
}

$('.content-sections-scroll').scroll(function () {
    var fb = $('.content-sections-scroll');
    if (fb.scrollLeft() + fb.innerWidth() >= fb[0].scrollWidth) {
        $('.tabs-right').addClass('hidden');
    } else {
        $('.tabs-right').removeClass('hidden');
    }
    if (fb.scrollLeft() <= 20) {
        $('.tabs-left').addClass('hidden');
    } else {
        $('.tabs-left').removeClass('hidden');
    }
});
