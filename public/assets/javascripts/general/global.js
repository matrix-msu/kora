function getBrowserWidth() {
  return Math.max(
    document.body.scrollWidth,
    document.documentElement.scrollWidth,
    document.body.offsetWidth,
    document.documentElement.offsetWidth,
    document.documentElement.clientWidth
  );
}

function getBrowserHeight() {
  return Math.max(
    document.body.scrollHeight,
    document.documentElement.scrollHeight,
    document.body.offsetHeight,
    document.documentElement.offsetHeight,
    document.documentElement.clientHeight
  );
}

function setCookie(name, value, days) {
  var expires = "";
  if (days) {
    var date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    expires = "; expires=" + date.toUTCString();
  }
  document.cookie = name + "=" + (value || "") + expires + "; path=/";
}

function getCookie(name) {
  var nameEQ = name + "=";
  var ca = document.cookie.split(';');
  for (var i = 0; i < ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) == ' ') c = c.substring(1, c.length);
    if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
  }
  return null;
}

function eraseCookie(name) {
  document.cookie = name + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}

var touchMoving = false;

function checkMobileDevice() {
  var agent = navigator.userAgent;
  var regExpiPad = new RegExp('iPad');
  var regExpiPhone = new RegExp('iPhone');
  var regExpAndroid = new RegExp('Android');
  var regExpAndroidPhone = new RegExp('Chrome/[.0-9]* Mobile');
  var regExpAndroidTablet = new RegExp('Chrome/[.0-9]* (?!Mobile)');

  var mobile = regExpiPhone.test(agent) || regExpiPad.test(agent) ||
    (regExpAndroid.test(agent) && regExpAndroidPhone.test(agent)) ||
    (regExpAndroid.test(agent) && regExpAndroidTablet.test(agent));

  if (mobile) {
    document.ontouchmove = function(e) {
      touchMoving = true;
    }

    document.ontouchend = function(e) {
      touchMoving = false;
    }
  }
}

function isScrolledIntoView($elem) {
  var docViewTop = $(window).scrollTop();
  var docViewBottom = docViewTop + $(window).height();

  var elemTop = $elem.offset().top;
  var elemBottom = elemTop + $elem.height();

  return ((docViewTop < elemTop) && (docViewBottom > elemBottom));
}

function setFixedElement(load = false) {
  if ($('.pre-fixed-js').length > 0) {
    var $elementToFix = $('.pre-fixed-js');
    var $elementFixWrapper = $('.pre-fixed-js').parent();

    if (!isScrolledIntoView($elementFixWrapper)) {
      if (load) {
        $elementToFix.addClass('fixed-bottom fixed-bottom-slide');
      } else {
        $elementToFix.addClass('fixed-bottom');
      }
    } else if (isScrolledIntoView($elementFixWrapper)) {
      $elementToFix.removeClass('fixed-bottom').removeClass('fixed-bottom-slide');
    }
  }
}

function getURLParameter(name) {
  return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search) || [null, ''])[1].replace(/\+/g, '%20')) || null;
}

$(document).ready(function() {
  setFixedElement(true);

  var $sidebarCookie = getCookie('sidebar');
  if ($sidebarCookie && getBrowserWidth() > 870) {
    $(".center").addClass('with-aside');
    $('.pre-fixed-js').addClass('pre-fixed-with-aside');
  } else {
    // the case where we want the aside lock to still work on refresh for larger screens
    // but not on mobile.
    $('.side-menu-js').removeClass('active');
  }
  // var $status = $('.status-js');
  // var $navigation = $('.navigation-js')
  // setTimeout(function() {
  //   $status.addClass('active');
  //   $navigation.addClass('show-status');
  //
  //   // setTimeout(function() {
  //   //   $status.removeClass('active');
  //   //   $navigation.removeClass('show-status');
  //   // }, 4000);
  // }, 2000);

  var once = 0;
  $('.status-dismiss-js').on('click', function(e) {
    e.preventDefault();

    $status.removeClass('active');
    $navigation.removeClass('show-status');

    if (!once) {
      setTimeout(function() {
        $status.find('.information').html('This is an error status example');
        $status.addClass('active').addClass('error');
        $navigation.addClass('show-status');
      }, 2000);
      once = 1;
    }
  })




  checkMobileDevice();

  $(' .underline-middle-hover, .underline-left-hover').on('click touchend', function(e) {
    var el = $(this);
    var link = el.attr('href');

    if (touchMoving) {
      touchMoving = false;
      return false;
    }

    if (link.charAt(0) !== "#" && link.length > 0) {
      window.location = link;
      //} else {
      //e.preventDefault();
    }
  });
});

//Quick opens global search menu
$(document).keydown(function(e) {
  //CMD K, ctrl K
  if ((e.metaKey || e.ctrlKey) && e.keyCode == 75) {
    e.preventDefault();

    $(".global-search-toggle").click();
    setTimeout(function() {
      $('.global-search-input-js').focus()
    }, 500);
  }

  // Escape key
  if (e.keyCode == 27) {
    if ($('.modal-js').hasClass('active')) {
      Kora.Modal.close();
    }
  }
});

$(document).on('scroll', function() {
  setFixedElement();
})

var rtime;
var timeout = false;
var delta = 200;
$(window).resize(function() {
  rtime = new Date();
  if (timeout === false) {
    timeout = true;
    setTimeout(resizeend, delta);
  }
});

function resizeend() {
  if (new Date() - rtime < delta) {
    setTimeout(resizeend, delta);
  } else {
    timeout = false;

    // Handles sidebar changing from overlap to slide in.
    var $body = $('body');
    var $sideMenu = $('.side-menu-js');
    var $sideMenuBlanket = $('.side-menu-js .blanket-js');

    if ($(window).width() <= 870 && $sideMenu.hasClass('active')) {
      $sideMenuBlanket.width('100vw');
      $sideMenuBlanket.animate({
        opacity: '.09'
      }, 200, function() {
        $body.css('overflow-y', 'hidden');
      });
    } else if ($sideMenu.hasClass('active')) {
      $sideMenuBlanket.animate({
        opacity: '0'
      }, 200, function() {
        $body.css('overflow-y', '');
        $sideMenuBlanket.width(0);
      });
    }
  }
}
