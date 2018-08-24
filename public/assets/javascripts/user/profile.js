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

  // Ensure provided pic url matches an existing picture
  function initializeProfilePicValidation() {
    var $imgCont = $('.profile-pic-cont-js');
    var $img = $imgCont.find($('.profile-pic-js'));
    if ($img.length > 0) {
      // Profile pic url provided, check it exists in app
      $.get($img.attr('src'))
          .done(function() {
            // Image exists
            console.log("img exists");
          })
          .fail(function() {
            console.log("img does not exist");
            $imgCont.html('<i class="icon icon-user">');
          });
    }
  }

  function initializeCardEllipsifying () {
    function adjustCardTitle() {
      var cards = $($(".content-sections-scroll").find(".card"));

      for (i = 0; i < cards.length; i++) {
        var card = $(cards[i]);
        var left_sect = $(card.find($('.left')));
        var chevron = $(card.find($('.icon-chevron')));
        var title = $(left_sect.find($('.title').children()));
        var group = $(left_sect.find($('.group').children()));

        var card_width = card.width();
        var chevron_width = chevron.outerWidth();
        var extra_padding = 20;
        var left_sect_width = left_sect.outerWidth();
        var titleSpan_width = title.outerWidth();
        var groupSpan_width = group.outerWidth();

        var title_width = card_width - (chevron_width + extra_padding);
        if (title_width < 0) {title_width = 0;}

        left_sect.css("max-width", title_width + "px");

        if (left_sect_width > title_width) {
          var difference = left_sect_width - title_width;
          var set_overflow = groupSpan_width - difference + 10;
          if (set_overflow < 16) {set_overflow = 16;}
          group.css("max-width", set_overflow + "px");
          if (title_width < titleSpan_width) {
            title.css("max-width", title_width + "px");
          } else {
            title.css("max-width", "");
          }
        } else {
          title.css("max-width", "");
          group.css("max-width", "");
        }
      }
    }
  	
    $(window).resize(function() {
      adjustCardTitle();
    });
	
    $(document).ready(function() {
      adjustCardTitle();
    });
  }

  initializeOptionDropdowns();
  initializeFilters();
  initializeProjectCards();
  initializeModals();
  initializePaginationRouting();
  initializeCardEllipsifying();
  initializeProfilePicValidation();

}
