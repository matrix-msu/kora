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
    console.log(load);
    var $elementToFix = $('.pre-fixed-js');
    var $elementFixWrapper = $('.pre-fixed-js').parent();

    if (!isScrolledIntoView($elementFixWrapper)) {
      if (load) {
        $elementToFix.addClass('fixed-bottom-slide');
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


  checkMobileDevice();

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
