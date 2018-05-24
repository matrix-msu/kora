var Kora = Kora || {};
Kora.User = Kora.User || {};

Kora.User.Profile = function() {
  function initializeOptionDropdowns() {
    $('.option-dropdown-js').chosen({
      disable_search_threshold: 10,
      width: 'auto'
    }).change(function() {
      var type = $(this).attr('id');
      if (type === 'page-count-dropdown') {
        var order = getURLParameter('order');
        window.location = window.location.pathname + "?page-count=" + $(this).val() + (order ? "&order=" + order : '');
      } else if (type === 'order-dropdown') {
        var pageCount = getURLParameter('page-count');
        window.location = window.location.pathname + "?order=" + $(this).val() + (pageCount ? "&page-count=" + pageCount : '');
      }
    });
  }

  function initializePageNavigation() {
    $selectSection = $('.select-section-js');
    $pageSection = $('.page-section-js');

    $selectSection.click(function(e) {
      e.preventDefault();

<<<<<<< HEAD
      $selected = $(this).attr("href").replace('#', '');

      // Get all existing URL parameters
      var order = getURLParameter('order');
      var pageCount = getURLParameter('page-count');

      window.location = window.location.pathname + "?section=" + $selected + (pageCount ? "&page-count=" + pageCount : '') + (order ? "&order=" + order : '');
=======
      $this = $(this);
      $this.siblings().removeClass('active');
      $this.addClass('active');

      $('.page-section-js').removeClass('active');
      $active = $this.attr("href").replace('#', '');
      $('.page-section-js').each(function() {
          if ($(this).attr('id') == $active) {
            $(this).addClass('active');
          }
      });
>>>>>>> abdbf5e80a65ca664bddbc903fefd912bc94bfde
    });
  }

  function initializeFilter(page) {
    var $selector = $(page + ' .select-content-section-js');
    var $content = $(page + ' .content-section-js');

    $content.first().addClass('active');
    $selector.first().addClass('active');

    $selector.click(function(e) {
      e.preventDefault();

      $this = $(this);
      $this.siblings().removeClass('active');
      $this.addClass('active');
      $content.removeClass('active');

      $active = $this.attr("href").replace('#', '');
      $content.each(function() {
        if ($(this).attr('id') == $active) {
          $(this).addClass('active');
        }
      });
    });
  }

  function initializeFilters() {
    initializeFilter('.permissions');
    initializeFilter('.record-history');
  }

  function initializeProjectCards() {
    // Initialize Custom Sort
    $('.project-toggle-js').click(function(e) {
      e.preventDefault();

      var $this = $(this);
      var $header = $this.parent().parent();
      var $project = $header.parent();
      var $content = $header.next();

      $this.children().toggleClass('active');
      $project.toggleClass('active');
      if ($project.hasClass('active')) {
        $header.addClass('active');
        $project.animate({
          height: $project.height() + $content.outerHeight(true) + 'px'
        }, 230);
        $content.effect('slide', {
          direction: 'up',
          mode: 'show',
          duration: 240
        });
      } else {
        $project.animate({
          height: '58px'
        }, 230, function() {
          $header.hasClass('active') ? $header.removeClass('active') : null;
          $content.hasClass('active') ? $content.removeClass('active') : null;
        });
        $content.effect('slide', {
          direction: 'up',
          mode: 'hide',
          duration: 240
        });
      }

    });
  }

<<<<<<< HEAD
  initializeOptionDropdowns();
=======
>>>>>>> abdbf5e80a65ca664bddbc903fefd912bc94bfde
  initializePageNavigation();
  initializeFilters();
  initializeProjectCards();
}
