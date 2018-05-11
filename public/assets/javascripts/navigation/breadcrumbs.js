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

var result
window.setInterval(function() {
  result = collision($('.navigation-left .navigation-item:last-child'), $('.navigation-search'));
  console.log('' + result)
  if (result === true) {
    $('.navigation-left').addClass('collapsed');
  }
  unsetBreadCrumbs ()
}, 200);

// can't use the above function to unset breadcrumbs because immediately on collapse the above function returns false and would unset and reset recursively
function unsetBreadCrumbs () {
  if (window.innerWidth >= 700 && $('.navigation-left').hasClass('collapsed')) {
    $('.collapsed').removeClass('collapsed');
  }
}

// this script is meant to detect when the left section of the nav (breadcrumbs) collide with the right side of the nav (nav-search)
// needs to be done this way because the width of left-nav is variable
// when collision = true, apply .collapsed to the <ul.nav-left>, and css should handle the rest
