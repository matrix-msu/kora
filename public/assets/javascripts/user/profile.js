var Kora = Kora || {};
Kora.User = Kora.User || {};

Kora.User.Profile = function() {
  function windowLocation(key, value) {
    var sec = (key == 'sec' ? value : getURLParameter('sec'));
    var rmOrder = (key == 'rm-order' ? value : getURLParameter('rm-order'));
    var mcrOrder = (key == 'mcr-order' ? value : getURLParameter('mcr-order'));
    var pageCount = (key == 'page-count' ? value : getURLParameter('page-count'));
    var page = '';
    if (key != 'sec') {
      // When switching sections, don't keep track of the page
      page = (key == 'page' ? value : getURLParameter('page'));
    }

    var parameters = [];
    if (sec) { parameters.push("sec=" + sec); }
    if (pageCount) { parameters.push("page-count=" + pageCount); }
    if (rmOrder) { parameters.push("rm-order=" + rmOrder); }
    if (mcrOrder) { parameters.push("mcr-order=" + mcrOrder); }
    if (page) { parameters.push("page=" + page); }

    return (parameters ? window.location.pathname + "?" + parameters.join("&") : window.location.pathname);
  }

  function initializeOptionDropdowns() {
    $('.option-dropdown-js').chosen({
      disable_search_threshold: 10,
      width: 'auto'
    }).change(function() {
      var type = $(this).attr('id');
      var val = $(this).val();
      if (type === 'page-count-dropdown') {
        window.location = windowLocation('page-count', val);
      } else if (type === 'order-dropdown') {
        if (getURLParameter('sec') == 'mcr') {
          window.location = windowLocation('mcr-order', val);
        } else {
          window.location = windowLocation('rm-order', val);
        }
      }
    });
  }

  function initializeHistoryFilter() {
    var $selector = $('#recordHistory .select-content-section-js');

    $selector.click(function(e) {
      e.preventDefault();

      $this = $(this);
      var newSec = $this.attr('href').replace("#", "");

      window.location = windowLocation('sec', newSec);
    });
  }

  function initializePermissionsFilter() {
    var $selector = $('#permissions .select-content-section-js');
    var $content = $('#permissions .content-section-js');

    $selector.first().addClass('active');
    $content.first().addClass('active');

    $selector.click(function(e) {
      e.preventDefault();

      var $this = $(this);

      // Active class for filters
      $selector.removeClass('active');
      $this.addClass('active');

      // Active class for content
      $content.removeClass('active');
      var newSec = $this.attr('href');
      $('#permissions ' + newSec).addClass('active');
    });
  }

  function initializeFilters() {
    initializePermissionsFilter();
    initializeHistoryFilter();
  }

  function initializeProjectCards() {
    // Initialize Custom Sort
    $('.card-toggle-js').click(function(e) {
      e.preventDefault();

      var $this = $(this);
      var $header = $this.parent().parent();
      var $card = $header.parent();
      var $content = $header.next();

      $this.children().toggleClass('active');
      $card.toggleClass('active');
      if ($card.hasClass('active')) {
        $header.addClass('active');
        $card.animate({
          height: $card.height() + $content.outerHeight(true) + 'px'
        }, 230);
        $content.effect('slide', {
          direction: 'up',
          mode: 'show',
          duration: 240
        });
      } else {
        $card.animate({
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

    // Expand all cards
    $('.expand-fields-js').click(function(e) {
      e.preventDefault();
      $('.content-section-js.active .card:not(.active) .card-toggle-js').click();
    });

    // Collapse all cards
    $('.collapse-fields-js').click(function(e) {
      e.preventDefault();
      $('.content-section-js.active .card.active .card-toggle-js').click();
    });
  }

  function initializeModals() {
    Kora.Modal.initialize();

    $('.restore-js').on('click', function(e) {
      e.preventDefault();

      var time = $(this).parents('.card').find('.time-js').text();
      var date = $(this).parents('.card').find('.date-js').text()
      var dateTime = moment(date + ' ' + time);
      var $modal = $('.restore-fields-modal-js');
      var url = $modal.find('.restore-fields-button-js').attr('href');
      var revision = $(this).data('revision');
      $modal.find('.date-time').text(dateTime.format('M.D.YYYY [at] h:mma'));

      $modal.find('.restore-fields-button-js').on('click', function(e) {
        e.preventDefault();
        $.ajax({
          url: url,
          type: 'GET',
          data: {
            revision: revision
          },
          success: function(d) {
            location.reload();
          },
          error: function(e) {
            console.log(e);
          }
        });
      });
      Kora.Modal.open($modal);
    });

    $('.reactivate-js').on('click', function(e) {
      e.preventDefault();

      var time = $(this).parents('.card').find('.time-js').text();
      var date = $(this).parents('.card').find('.date-js').text()
      var dateTime = moment(date + ' ' + time);
      var $modal = $('.reactivate-record-modal-js');
      var url = $modal.find('.reactivate-record-button-js').attr('href');
      var revision = $(this).data('revision');
      $modal.find('.date-time').text(dateTime.format('M.D.YYYY [at] h:mma'));

      $modal.find('.reactivate-record-button-js').on('click', function(e) {
        e.preventDefault();
        $.ajax({
          url: url,
          type: 'GET',
          data: {
            revision: revision
          },
          success: function(d) {
            location.reload();
          },
          error: function(e) {
            console.log(e);
          }
        });
      });
      Kora.Modal.open($modal);
    });
  }

  function initializePaginationRouting() {
    var $pagination = $('.pagination-js');
    var $pageLink = $pagination.find('.page-link-js');

    $pageLink.click(function(e) {
      e.preventDefault();

      var $this = $(this);
      var sec = getURLParameter('sec');
      var toPage = $this.attr('href').replace('#', '');

      window.location = windowLocation('page', toPage);
    });
  }

  initializeOptionDropdowns();
  initializeFilters();
  initializeProjectCards();
  initializeModals();
  initializePaginationRouting();
}
