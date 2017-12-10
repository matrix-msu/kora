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


$(document).ready(function() {
  setFixedElement(true);

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

  $('a, .underline-middle-hover, .underline-left-hover').on('click touchend', function(e) {
    var el = $(this);
    var link = el.attr('href');

    if (touchMoving) {
      touchMoving = false;
      return false;
    }

    if (link.charAt(0) !== "#" && link.length > 0) {
      window.location = link;
    } else {
      e.preventDefault();
    }
  });
});

//Quick opens global search menu
$(document).keydown(function(e) {
  //CMD K, ctrl K
  if ((e.metaKey || e.ctrlKey) && e.keyCode == 75) {
    e.preventDefault();

    $(".global-search-toggle").click();
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