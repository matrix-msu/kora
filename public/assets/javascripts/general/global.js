$(document).ready(function() {
  $('.underline-middle-hover, .underline-left-hover').on('click touchend', function(e) {
    var el = $(this);
    var link = el.attr('href');

    if (link.charAt(0) !== "#") {
      window.location = link;
    }
  });
});
