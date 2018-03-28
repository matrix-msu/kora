var Kora = Kora || {};
Kora.Admin = Kora.Admin || {};

Kora.Admin.Users = function() {

  function clearSearch() {
    $('.search-js .icon-cancel-js').click();
  }

  function initializeSearch() {
    var $searchInput = $('.search-js input');

    $('.search-js i, .search-js input').click(function(e) {
      e.preventDefault();

      $(this).parent().addClass('active');
      $('.search-js input').focus();
    });

    $searchInput.focusout(function() {
      if (this.value.length == 0) {
        $(this).parent().removeClass('active');
        $(this).next().removeClass('active');
      }
    });

    $searchInput.keyup(function(e) {
      if (e.keyCode === 27) {
        $(this).val('');
      }

      if (this.value.length > 0) {
        $(this).next().addClass('active');
      } else {
        $(this).next().removeClass('active');
      }
    });

    $('.search-js .icon-cancel-js').click(function() {
      $searchInput.val('').blur().parent().removeClass('active');
    });
  }

  function initializeCustomSort() {
    // Initialize Custom Sort
    $('.user-toggle-js').click(function(e) {
      e.preventDefault();

      var $this = $(this);
      var $header = $this.parent().parent();
      var $user = $header.parent();
      var $content = $header.next();

      $this.children().toggleClass('active');
      $user.toggleClass('active');
      if ($user.hasClass('active')) {
        $header.addClass('active');
        $user.animate({
          height: $user.height() + $content.outerHeight(true) + 'px'
        }, 230);
        $content.effect('slide', {
          direction: 'up',
          mode: 'show',
          duration: 240
        });
      } else {
        $user.animate({
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

    $(".user-custom-js").sortable({
      helper: 'clone',
      revert: true,
      containment: ".projects",
      update: function(event, ui) {
        pidsArray = $(".user-custom-js").sortable("toArray");

        $.ajax({
          url: saveCustomOrderUrl,
          type: 'POST',
          data: {
            "_token": CSRFToken,
            "pids": pidsArray,

          },
          success: function(result) {}
        });
      }
    });
  }

  function clearFilterResults() {
    // Clear previous filter results
    $('.sort-options-js ul a').removeClass('active');
    $('.user-sort-js').removeClass('active');
  }

  function initializeFilters() {
    // Initially set it to first filter in the list
    setFilter($($('.sort-options-js ul a').get(0)));

    $('.sort-options-js ul a').click(function(e) {
      e.preventDefault();

      setFilter($(this));
    });
  }

  function setFilter(that) {
    var $content = $('.users-' + that.attr('href').substring(1));

    clearSearch();
    clearFilterResults();

    // Toggle self animation and display corresponding content
    that.addClass('active');
    $content.addClass('active');
  }

  /**
    * Modal for deleting a user
    */
  function initializeCleanUpModals() {
    Kora.Modal.initialize();

    $('.user-trash-js').click(function(e) {
      e.preventDefault();

      var cleanupModal = $(".users-cleanup-modal-js");
      cleanupModal.find('.delete-content-js').show();
      cleanupModal.find('.invite-content-js').hide();
      cleanupModal.find('.content').addClass('small');
      cleanupModal.find('.title-js').html('Delete User?');

      var card = $(this).parent().parent().parent();
      var id = card.attr('id').substring(5);

      Kora.Modal.open();

      $('.user-cleanup-submit').click(function(e) {
        e.preventDefault();

        var deleteForm = $(".modal form");
        var actionURL = deleteForm.attr("action");

        $.ajax({
          url: actionURL + "/" + id,
          type: 'DELETE',
          data: deleteForm.serialize(),
          success: function(data) {
            // TODO: Handle messages sent back from controller
            location.reload();
          }
        });
      });
    });


    $('.new-object-button-js').click(function(e) {
      e.preventDefault();

      var cleanupModal = $(".users-cleanup-modal-js");
      cleanupModal.find('.delete-content-js').hide();
      cleanupModal.find('.invite-content-js').show();
      cleanupModal.find('.content').removeClass('small');
      cleanupModal.find('.title-js').html('Invite User(s)');

      Kora.Modal.open();
    });
  }

  initializeFilters();
  initializeCustomSort()
  initializeSearch();
  initializeCleanUpModals();
}
