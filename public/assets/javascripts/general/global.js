function getBrowserWidth() { // this does not account for the width of the scrollbar, therefore we need to add window.innerWidth here I believe - not accounting for this was causing issues with the sidebar blanket at certain widths
  return Math.max(
    window.innerWidth,
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
    var $elementFixWrapper = $elementToFix.parent();

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

  var $sidebarPrefs = false;
  if ($('.keep-sidemenu').hasClass('true')) {
    $sidebarPrefs = true;
  }
  var $sidebarCookie = getCookie('sidebar');
  if (($sidebarCookie || $sidebarPrefs) && getBrowserWidth() > 870) {
    $('.side-menu-js').addClass('active');
    $(".center, .floating-buttons").addClass('with-aside');
    $('.field.card').addClass('with-aside');
	
	var welcome_notification = $('.welcome-body').find(".notification");
	if (welcome_notification.length == 0) {
	  $('.notification').addClass('with-aside'); // this breaks welcome page notification styling
	}
    $('.pre-fixed-js').addClass('pre-fixed-with-aside');
    $('.toolbar').addClass('with-aside');
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
  });

  checkMobileDevice();

  $(' .underline-middle-hover, .underline-left-hover').on('click touchend', function(e) {
    var el = $(this);
    var link = el.attr('href');

    if (touchMoving) {
      touchMoving = false;
      return false;
    }

    if (link.charAt(0) !== "#" && link.length > 0) {
      e.preventDefault();
      if (e.metaKey || e.ctrlKey) {
        window.open(link);
      } else {
        window.location = link;
      }
    }
  });

  //check for active sidebar drawer
  $activeDrawer = $('.drawer-toggle-js[data-drawer="1"]')
  if (typeof $activeDrawer !== 'undefined') {
    var $this = $activeDrawer
    var $drawerElement = $this.parent();
    var $drawerContent = $this.next();
    var $icon = $this.children().last();

    setTimeout(function() {
      var $headerHeight = $('.aside-content .header-elements').height();
      var $footerHeight = $('.aside-content .footer-elements').height();
      var combinedHeight = $headerHeight + $footerHeight

      if (combinedHeight > (window.innerHeight - 50)) {
        $('.aside-content .footer-elements').css('position', 'static');
      } else {
        $('.aside-content .footer-elements').css('position', 'absolute');
      }
    }, 400);

    if ($drawerElement.hasClass('active')) {
      closeSidemenuDrawers();
      return;
    }

    closeSidemenuDrawers();
    $drawerElement.toggleClass('active');
    $icon.toggleClass('active');
    $drawerContent.slideToggle('fast');
  }

  // set the active page in the sidebar drawer
  var pageName = $('body').attr('class').replace("-body", "").replace(/ /g,'');
  var $activePageLink = $('.content-link-js[data-page="' + pageName + '"]')
  if (typeof $activePageLink !== 'undefined') {
    $('.content-link-js').removeClass('head');
    $activePageLink.addClass('head');
  }

  var $noteBody = $('.notification');
  var $note = $('.note').children('p');
  var $noteDesc = $('.note').children('span');

  var message = window.localStorage.getItem('message');

  if (message) {
    $note.text(message);
    window.localStorage.clear();
  }

  setTimeout(function(){
    if ($note.text() != '') {
      if ($note.text() === 'Update Available!') {
        $('.view-updates-js').removeClass('hidden');
      }

      if ($noteDesc.text() != '') {
        $noteDesc.addClass('note-description');
        $note.addClass('with-description');
      }

      $noteBody.removeClass('dismiss');
      var welcome_notification = $('.welcome-body').find(".notification");
      if (welcome_notification.length > 0) {
        welcome_notification.addClass('welcome-align');
        
        var welcome_note = welcome_notification.find('.container').find('.note');
        if (welcome_note.length > 0) {
          welcome_note.addClass('welcome-stack-note');
        }
      }

      if (!$noteBody.hasClass('static-js')) {
        setTimeout(function(){
          $noteBody.addClass('dismiss');
          $('.view-updates-js').addClass('hidden');
        }, 4000);
      }
    }
  }, 200);

  $('.toggle-notification-js').click(function(e) {
    e.preventDefault();

    $noteBody.addClass('dismiss');
    $('.welcome-body').removeClass('with-notification');
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

// makes sure multi-select inputs have placeholders after clicking and before typing
// placeholders are stored in value attribute but otherwise disappear when clicking on the input
function multiselect_placeholder_injection()
{
	var inputs = $(".chosen-search-input");
	
	for (i = 0; i < inputs.length; i++)
	{
		var jq_input = $(inputs[i]);
		
		if (!jq_input.attr("placeholder-injected"))
		{
			jq_input.attr("placeholder-injected", 1);
			jq_input.attr("placeholder", jq_input.attr("value"));
			
		}
	}
}
multiselect_placeholder_injection();
setInterval(multiselect_placeholder_injection, 451);

function display_loader() {
	$("#preloader").css("display", "");
}

function hide_loader() {
	$("#preloader").css("display", "none");
}

$( document ).ajaxSend(function(event, xhr, options) {

  var url = options.url;
  var display = true;

  // loader exclusion cases for AJAX requests
  if (url.search("validate") != -1) // exclude validation requests
  {
	display = false;
  }

  if (display) { display_loader(); }
});

$( document ).ajaxComplete(function(event, xhr, options) {
  var url = options.url;
  var hide = true;
  
  // hide loader exclusion cases for AJAX requests
  if (url.search("validate") != -1) { // exclude validation requests
	hide = false;
  }
  
  if (hide) {
    hide_loader();
  }
});


//THIS IS FOR RECORD FILE DATA EXPORTS
$('.export-begin-files-js').click(function(e) {
    e.preventDefault();

    $(this).addClass('disabled');
    $(this).text("Generating zip file...");

    startURL = $(this).attr('startURL');
    endURL = $(this).attr('endURL');
    token = $(this).attr('token');

    //Ajax call to prep zip
    $.ajax({
        url: startURL,
        type: 'POST',
        data: {
            "_token": token
        },
        success: function (data) {
            //Change text back
            $('.export-begin-files-js').removeClass('disabled');
            $('.export-begin-files-js').text("Export Record Files");
            //Set page to download URL
            document.location.href = endURL;
        },
        error: function (error) {
            $('.export-begin-files-js').removeClass('disabled');
            $('.export-begin-files-js').text("Something went wrong :(");

            console.log(error.responseJSON.message);

            if(error.responseJSON.message == 'no_record_files') {
                $('.export-begin-files-js').text("No record files :(");
                $('.export-files-desc-js').text("There are no record files in this Form. You may still export all form records in the formats of JSON or XML.");
            }

            if(error.responseJSON.message == 'zip_too_big') {
                $('.export-begin-files-js').text("Zip too big :(");
                $('.export-files-desc-js').text("Zipped file is too big. Please use the php artisan command for exporting record files. You may still export all form records in the formats of JSON or XML.");
            }
        }
    });
});

function unsetBreadCrumbs () {
  if (window.innerWidth > 900) {
      // this value needs to be one so large that nav-left will never be wide enough to touch the right-nav above this browser width
      // currently, the largest width for .nav-left I could get was 846.31px
      $('.navigation-left').removeClass('collapsed');
  }
}

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
  if($('.navigation-right-wrap').length && $('.navigation-left').length)
      var result = collision($('.navigation-right-wrap'), $('.navigation-left'));
      if (result === true) {
          $('.navigation-left').addClass('collapsed');
      } else {
          unsetBreadCrumbs ()
      }
}, 200);
