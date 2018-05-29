/*window.setInterval(function() {
  if (window.innerWidth <= 768) {
    $('.navigation-left').addClass('collapsed');
  } else if (window.innerWidth > 768) {
    $('.navigation-left').removeClass('collapsed');
  }
});*/

function collision($div1, $div2) {
  var x1 = $div1.offset().left;
  var y1 = $div1.offset().top;
  var h1 = $div1.outerHeight(true);
  var w1 = $div1.outerWidth(true);
  var b1 = y1 + h1;
  var r1 = x1 + w1;
  var x2 = $div2.offset().left;
  var y2 = $div2.offset().top;
  var h2 = $div2.outerHeight(true);
  var w2 = $div2.outerWidth(true);
  var b2 = y2 + h2;
  var r2 = x2 + w2;

  if (b1 < y2 || y1 > b2 || r1 < x2 || x1 > r2) return false;
  return true;
}

window.setInterval(function() {
    var result = collision($('.navigation-right-wrap'), $('.navigation-left'));
    if (result === true) {
      $('.navigation-left').addClass('collapsed');
    } else {
      unsetBreadCrumbs ()
    }
}, 200);

function unsetBreadCrumbs () {
  if (window.innerWidth > 900) {
    // this value needs to be one so large that nav-left will never be wide enough to touch the right-nav above this browser width
    // currently, the largest width for .nav-left I could get was 846.31px
    $('.navigation-left').removeClass('collapsed');
  }
}