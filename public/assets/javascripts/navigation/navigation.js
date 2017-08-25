//The first section handles closing/opening of menus
var $navBar = $(".navigation-js");
var $subMenu = $(".navigation-sub-menu-js");
var $deepMenu = $(".navigation-deep-menu-js");
var $menuTitle = $('.navigation-left-js .navigation-toggle-js');
var menuTitleIndex = $menuTitle.length - 1;

$menuTitle.each(function(index) {
  if (index > 0 && index != menuTitleIndex) {
    $(this).css('opacity', 0.7);
  }
});

$navBar.on("click", ".navigation-toggle-js", function() {
  var clicked = $(this).next();
  var $parent = $(this).parent();

  $deepMenu.each(function() {
    $(this).removeClass('active');
  });

  $subMenu.each(function() {
    var $this = $(this);

    if ($this.get(0) !== clicked.get(0)) {
      $this.removeClass('active');
    }
  });
  clicked.toggleClass('active');

  //SPECIAL CASE FOR SEARCH
  if ($parent.hasClass('navigation-search')) {
    gsTextInput.focus();
  }
});

$navBar.on("click", ".navigation-sub-menu-toggle-js", function() {
  $(this).next().toggle();
});

//If the nav isn't clicked, close all menus
$(document).click(function(event) {
  if (!$(event.target).closest('.navigation-js').length) {
    $deepMenu.each(function() {
      $(this).removeClass('active');
    });

    $subMenu.each(function() {
      $(this).removeClass('active');
    });
  }
});

//This section handles the global search
var gsForm = $("#kora_global_search");
var gsTextInput = $("#kora_global_search_input");
var gsRecentSearch = $("#kora_global_search_recent");
var gsQuickResult = $("#kora_global_search_result");
gsQuickResult.attr("style", "display: none;"); //INITIALIZE HERE
var gsClearRecent = $("#kora_global_search_clear");
var cmdKLink = $(".global-search-toggle");

//Quick opens global search menu
$(document).keydown(function(e) {
  //CMD K, ctrl K
  if ((e.metaKey || e.ctrlKey) && e.keyCode == 75) {
    e.preventDefault();

    cmdKLink.click();
  }
});

//Performs quick search on typing
gsTextInput.keyup(function() {
  var searchText = $(this).val();

  //We don't want to search the entire alphabet, need at least 2 characters
  if (searchText != '' && searchText.length >= 2) {
    gsRecentSearch.attr("style", "display: none;");
    gsQuickResult.attr("style", "");
    gsClearRecent.attr("style", "display: none;");

    //Perform quick search
    $.ajax({
      url: globalQuickSearchUrl,
      type: 'POST',
      data: {
        "_token": CSRFToken,
        "searchText": searchText
      },
      success: function(result) {
        var resultObj = JSON.parse(result);
        var resultStr = resultObj.join("");
        gsQuickResult.html(resultStr);
      }
    });
  } else {
    gsRecentSearch.attr("style", "");
    gsQuickResult.attr("style", "display: none;");
    gsClearRecent.attr("style", "");
  }
});

//Caches a global search before submitting the search itself
gsForm.submit(function() {
  var valToCache = gsTextInput.val();

  if (valToCache != "") {
    var html = "<li><a href=\"" + globalSearchUrl + "?gsQuery=" + encodeURI(valToCache) + "\">Search: " + valToCache + "</a></li>";
    cacheGlobalSearch(html);
  }
});

//Caches the use of a quick jump link
gsQuickResult.on("click", "a", function() {
  var uri = $(this).attr("href");
  var type = $(this).attr("type");
  var html = "<li><a href=\"" + uri + "\">" + type + ": " + $(this).text() + "</a></li>";
  cacheGlobalSearch(html);
});

//Clears the user's recent cached results
gsClearRecent.on("click", function() {
  $.ajax({
    url: clearGlobalCacheUrl,
    type: 'DELETE',
    data: {
      "_token": CSRFToken
    },
    success: function(result) {
      //remove from page
      gsRecentSearch.text("");
    }
  });
});

//The function that actually does the caching
function cacheGlobalSearch(htmlString) {
  $.ajax({
    url: cacheGlobalSearchUrl,
    type: 'POST',
    data: {
      "_token": CSRFToken,
      "html": htmlString
    },
    success: function(result) {
      var resultObj = JSON.parse(result);
      var resultStr = resultObj.join("");
      gsQuickResult.html(resultStr);
    }
  });
}
