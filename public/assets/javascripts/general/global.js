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

$(document).ready(function() {
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
