$(document).ready(function() {
  $('.underline-middle-hover, .underline-left-hover').on('click touchend', function(e) {
    var el = $(this);
    var link = el.attr('href');
    window.location = link;
  });
});
