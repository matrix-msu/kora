//The first section handles closing/opening of menus
var $navBar = $('.navigation-js');
var $subMenu = $('.navigation-sub-menu-js');
var $deepMenu = $('.navigation-deep-menu-js');
var $sideMenu = $('.side-menu-js');
var $sideMenuBlanket = $('.side-menu-js .blanket-js');
var $menuTitle = $('.navigation-left-js .navigation-toggle-js');
var menuTitleIndex = $menuTitle.length - 1;

$menuTitle.each(function(index) {
  if (index > 0 && index != menuTitleIndex) {
    $(this).css('opacity', 0.7);
  }
});

$navBar.on('click', '.navigation-toggle-js', function(e) {
  e.preventDefault();

  var $clicked = $(this).next();
  var $icon = $(this).children();
  var $parent = $(this).parent();

  $deepMenu.each(function() {
    $(this).removeClass('active');
  });

  $subMenu.each(function() {
    var $this = $(this);

    if ($this.get(0) !== $clicked.get(0)) {
      $this.removeClass('active');
    }
  });
  $clicked.toggleClass('active');

  $('.navigation-toggle-js .icon').removeClass('active');
  if ($clicked.hasClass('active')) {
    $icon.addClass('active');
  }

  //SPECIAL CASE FOR SEARCH
  if ($parent.hasClass('navigation-search')) {
    setTimeout(function() {
      $('.global-search-input-js').focus();
    }, 500);
  }
});

$navBar.on('click', '.navigation-sub-menu-toggle-js', function(e) {
  e.preventDefault();

  $menu = $(this).next('.navigation-deep-menu-js');

  if ($menu.hasClass('active')) {
    $menu.removeClass('active');
  } else {
    $menu.addClass('active');
  }
});

$navBar.on('click', '.side-menu-toggle-js', function() {
  var $icon = $(this).children();

  $sideMenu.toggleClass('active');

  $('.navigation-toggle-js .icon').removeClass('active');
  if ($sideMenu.hasClass('active')) {
    $icon.addClass('active');

    setCookie('sidebar', 1);
    $('.center, .floating-buttons').addClass('with-aside');
    $('.allowed-actions').addClass('with-aside'); 
    if (getBrowserWidth() > 870)
      $('.pre-fixed-js').addClass('pre-fixed-with-aside');
  } else {
    $('.center, .floating-buttons').removeClass('with-aside');
    $('.allowed-actions').removeClass('with-aside'); 
    if (getBrowserWidth() > 870)
      $('.pre-fixed-js').removeClass('pre-fixed-with-aside');

    eraseCookie('sidebar');
    $icon.removeClass('active');
  }

  if (getBrowserWidth() < 870) {
    var $body = $('body');
    var $sideMenuBlanket = $('.side-menu-js .blanket-js');

    if ($sideMenu.hasClass('active')) {
      $sideMenuBlanket.width('100vw');
      $sideMenuBlanket.animate({
        opacity: '.09'
      }, 200, function() {
        $body.css('overflow-y', 'hidden');
      });

    } else {
      $sideMenuBlanket.animate({
        opacity: '0'
      }, 200, function() {
        $body.css('overflow-y', '');
        $sideMenuBlanket.width(0);
      });
    }
  }
});

$sideMenuBlanket.on('click', function() {
  $('.side-menu-toggle-js').click();
});

//If the nav isn't clicked, close all menus
$(document).click(function(event) {
  if (!$(event.target).closest('.navigation-js').length) {
    $('.navigation-toggle-js .icon').removeClass('active');

    $deepMenu.each(function() {
      $(this).removeClass('active');
    });

    $subMenu.each(function() {
      $(this).removeClass('active');
    });
  }
});

var typewatch = (function() {
  var timer = 0;
  return function(callback, ms) {
    clearTimeout(timer);
    timer = setTimeout(callback, ms);
  };
})();

//This section handles the global search
var $searchForm = $('.global-search-form-js');
var $searchInput = $('.global-search-input-js');
var $recentSearch = $('.recent-search-results-js');
var $searchResults = $('.search-results-js');
var $clearResentSearchResults = $('.clear-search-results-js');

//Hide "clearResentSearchResults" when there is no recent searches


$searchResults.parent().attr('style', 'display: none;'); //INITIALIZE HERE
//Performs quick search on typing
$searchInput.keydown(function(e) {
    var charCode = e.which || e.keyCode;

    if(charCode == 9 ) {
        e.preventDefault();
        //Handles initial tabbing through results

        if($(this).val()=='') {
            //tab to recent search
            var tabbed = $('.recent-search-results-js a').first();
            tabbed.focus();
        } else {
            //tab to results
            var tabbed = $('.search-results-js a').first();
            tabbed.focus();
        }
    } else {
        var searchText = $(this).val();
        typewatch(function () {
            // executed only 500 ms after the last keyup event.

            //We don't want to search the entire alphabet, need at least 2 characters
            if (searchText != '' && searchText.length >= 2) {
                $clearResentSearchResults.parent().slideUp(100, function () {
                    $recentSearch.parent().slideUp(100, function () {
                        //Perform quick search
                        $.ajax({
                            url: globalQuickSearchUrl,
                            type: 'POST',
                            data: {
                                '_token': CSRFToken,
                                'searchText': searchText
                            },
                            success: function (result) {
                                var resultObj = JSON.parse(result);
                                var resultStr = resultObj.join('');

                                $searchResults.parent().slideUp(200, function () {
                                    $searchResults.html(resultStr);
                                });

                                $searchResults.parent().slideDown(100);
                            }
                        });
                    });
                });
            } else if (searchText == '') {
                $searchResults.parent().slideUp(100);
            } else {
                $clearResentSearchResults.parent().slideDown(400, function () {
                    $recentSearch.parent().slideDown(400, function () {
                        $searchResults.parent().slideUp(200);
                    });
                });
            }
        }, 500);
    }
});

//Handle tabbing otherwise
$recentSearch.on('keydown', 'a', function(e) {
    var charCode = e.which || e.keyCode;

    if (charCode == 9) {
        e.preventDefault();
        //Handles tabbing through results

        var neighbor = $(this).parent().next();
        if(neighbor.length) {
            tabbed = neighbor.find('a').first();
            tabbed.focus();
        } else {
            var tabbed = $('.recent-search-results-js a').first();
            tabbed.focus();
        }
    }
});
$searchResults.on('keydown', 'a', function(e) {
    var charCode = e.which || e.keyCode;

    if (charCode == 9) {
        e.preventDefault();
        //Handles tabbing through results

        var neighbor = $(this).parent().next();
        if(neighbor.length) {
            tabbed = neighbor.find('a').first();
            tabbed.focus();
        } else {
            var tabbed = $('.search-results-js a').first();
            tabbed.focus();
        }
    }
});

//Caches a global search before submitting the search itself
$searchForm.submit(function() {
  var valToCache = $searchInput.val();

  if (valToCache != '') {
    var html = '<li><a href="' +
      globalSearchUrl + '?keywords=' + encodeURI(valToCache) + '&method=2&projects%5B%5D=ALL">' + valToCache + '</a></li>';

    cacheGlobalSearch(html);
  }
});

//Caches the use of a quick jump link
$searchResults.on('click', 'a', function() {
  var uri = $(this).attr('href');
  var type = $(this).data('type');
  var html = '<li><a href="' + uri + '">' + $(this).text() + '</a></li>';

  cacheGlobalSearch(html);
});

//Clears the user's recent cached results
$clearResentSearchResults.on('click', function() {
  $.ajax({
    url: clearGlobalCacheUrl,
    type: 'DELETE',
    data: {
      '_token': CSRFToken
    },
    success: function(result) {
      //remove from page
      $clearResentSearchResults.parent().slideUp(400, function() {
        $recentSearch.parent().slideUp(400, function() {
          $recentSearch.text('');
        });
      });
    }
  });
});

//The function that actually does the caching
function cacheGlobalSearch(htmlString) {
  $.ajax({
    url: cacheGlobalSearchUrl,
    type: 'POST',
    data: {
      '_token': CSRFToken,
      'html': htmlString
    },
    success: function(result) {
      var resultObj = JSON.parse(result);
      var resultStr = resultObj.join('');

      $searchResults.html(resultStr);
    }
  });
}

$('.export-record-open').click(function(e) {
    e.preventDefault();
    Kora.Modal.initialize();

    //We have to manually close the menu
    $(this).parents('.navigation-sub-menu-js').first().siblings('.navigation-toggle-js').first().click();

    var $exportRecordsModal = $('.export-records-modal-js');

    Kora.Modal.open($exportRecordsModal);
});

$('.export-record-link').click(function() {
    var $exportRecordsModal = $('.export-records-modal-js');

    Kora.Modal.close($exportRecordsModal);
});

function closeSidemenuDrawers() {
  var $drawers = $('.drawer-element-js');
  $drawers.each(function() {
    $this = $(this);
    $drawerToggle = $this.children('.drawer-toggle-js');
    $drawerContent = $drawerToggle.next();

    if ($this.hasClass('active')) {
      $drawerToggle.removeClass('active');
      $drawerToggle.children().last().removeClass('active');
      $drawerContent.find('.drawer-deep-menu-js').removeClass('active');
      $drawerContent.slideToggle('fast');
      $this.removeClass('active');
    }
  });
}

$sideMenu.on('click', '.drawer-toggle-js', function(e) {
  e.preventDefault();

  var $drawerElement = $(this).parent();
  var $drawerContent = $(this).next();
  var $icon = $(this).children().last();

  if ($drawerElement.hasClass('active')) {
    closeSidemenuDrawers();
    return;
  }

  closeSidemenuDrawers();
  $drawerElement.toggleClass('active');
  $icon.toggleClass('active');
  $drawerContent.slideToggle('fast');
});

$sideMenu.on('click', '.drawer-sub-menu-toggle-js', function(e) {
  e.preventDefault();

  $menu = $(this).next('.drawer-deep-menu-js');

  if ($menu.hasClass('active')) {
    $menu.removeClass('active');
  } else {
    $menu.addClass('active');
  }
});
