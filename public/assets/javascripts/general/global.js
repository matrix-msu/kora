var iOS = navigator.userAgent.indexOf('iphone') >= 0 || navigator.userAgent.indexOf('ipad') >= 0;
var touchMoving = false;

if (iOS) {
  document.ontouchmove = function(e) {
    touchMoving = true;
  }

  document.ontouchend = function(e) {
    touchMoving = false;
  }
}

$(document).ready(function() {
  $('.underline-middle-hover, .underline-left-hover').on('click touchend', function(e) {
    var el = $(this);
    var link = el.attr('href');

    if (touchMoving) return false;

    if (link.charAt(0) !== "#") {
      window.location = link;
    }
  });
});
