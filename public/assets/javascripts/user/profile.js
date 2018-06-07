var Kora = Kora || {};
Kora.User = Kora.User || {};

Kora.User.Profile = function() {
  function windowLocation(key, value) {
    var parameters = [];
    var order = (key == 'order' ? value : getURLParameter('order'));
    var pageCount = (key == 'page-count' ? value : getURLParameter('page-count'));
    var sec = (key == 'sec' ? value : getURLParameter('sec'));

    (order ? parameters.push("order=" + order) : '');
    (pageCount ? parameters.push("page-count=" + pageCount) : '');
    (sec ? parameters.push("sec=" + sec) : '');


    return (parameters ? window.location.pathname + "?" + parameters.join("&") : window.location.pathname);
  }

  function initializeOptionDropdowns() {
    $('.option-dropdown-js').chosen({
      disable_search_threshold: 10,
      width: 'auto'
    }).change(function() {
      var type = $(this).attr('id');
      if (type === 'page-count-dropdown') {
        //var order = getURLParameter('order');
        window.location = windowLocation('page-count', $(this).val()); // window.location.pathname + "?page-count=" + $(this).val() + (order ? "&order=" + order : '');
      } else if (type === 'order-dropdown') {
        //var pageCount = getURLParameter('page-count');
        window.location = windowLocation('order', $(this).val()); //window.location.pathname + "?order=" + $(this).val() + (pageCount ? "&page-count=" + pageCount : '');
      }
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
      $newSec = $this.attr('id');

      var order = getURLParameter('order');
      var pageCount = getURLParameter('page-count');
      var sec = $newSec;

      window.location = window.location.pathname +
          (order ? "&order=" + order : '') +
          (pageCount ? "&page-count=" + pageCount : '') +
          (sec ? "&sec=" + sec : '');

      /*
      $this.siblings().removeClass('active');
      $this.addClass('active');
      $content.removeClass('active');

      $active = $this.attr("href").replace('#', '');
      $content.each(function() {
        if ($(this).attr('id') == $active) {
          $(this).addClass('active');
        }
      });*/
    });
  }

  function initializeFilters() {
    initializeFilter('#permissions');
    initializeFilter('#recordHistory');
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

  initializeOptionDropdowns();
  initializeFilters();
  initializeProjectCards();
  initializeModals();
}
