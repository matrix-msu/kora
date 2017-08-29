var touchMoving = false;
var mobile = navigator.userAgent.toLowerCase().indexOf('iphone') >= 0 ||
  navigator.userAgent.toLowerCase().indexOf('ipad') >= 0 ||
  navigator.userAgent.toLowerCase().indexOf('mobile') >= 0;

if (mobile) {
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

    if (touchMoving) {
      touchMoving = false;
      return false;
    }

    if (link.charAt(0) !== "#") {
      window.location = link;
    }
  });
});
