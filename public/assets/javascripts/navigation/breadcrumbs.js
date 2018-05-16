window.setInterval(function() {
  if (window.innerWidth <= 768) {
    $('.navigation-left').addClass('collapsed');
  } else if (window.innerWidth > 768) {
    $('.navigation-left').removeClass('collapsed');
  }
})
