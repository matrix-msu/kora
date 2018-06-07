var Kora = Kora || {};
Kora.Admin = Kora.Admin || {};

Kora.Admin.Users = function() {
  
  function initializeOptionDropdowns() {
    $('.option-dropdown-js').chosen({
      disable_search_threshold: 10,
      width: 'auto'
    });
  }

  /**
   * Clear search results
   */
  function clearSearch() {
    $('.search-js .icon-cancel-js').click();
  }
  
  /**
   * Clear sorting 
   */
  function clearSortResults() {
    // Clear previous filter results
    $('.user-sort-js').removeClass('active');
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

          $('.user.card').each(function() {
              $(this).removeClass('hidden');
          });
      });

      $('.search-js i, .search-js input').keyup(function() {
          var searchVal = $(this).val().toLowerCase();

          $('.user.card').each(function() {
              var fname = $(this).find('.firstname').first().text().toLowerCase();
              var lname = $(this).find('.lastname').first().text().toLowerCase();
              var uname = $(this).find('.username').first().text().toLowerCase();

              if(fname.includes(searchVal) | lname.includes(searchVal) | uname.includes(searchVal))
                  $(this).removeClass('hidden');
              else
                  $(this).addClass('hidden');
          });
      });
  }

  function initializeCards() {
    // Click on a name would also open the card
    $('.title').click(function(e) {
      $(this).parent().next().find('.user-toggle-js').click();
    });

    // Toggle a card opening and closing
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
        }, 230, function() {
          $user.css('height', '');
        });
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
          $user.css('height', '');
        });
        $content.effect('slide', {
          direction: 'up',
          mode: 'hide',
          duration: 240
        });
      }

    });
  }

  function initializeFilters() {
    // Initially set it to first filter in the list
    var sortOptions = $(".option-dropdown-js")
    setFilter(sortOptions.val());

    $(sortOptions).change(function(e) {
      setFilter($(this).val());
    });
  }
  
  /**
   * Display sorted users
   */
  function setFilter(sort) {
    var content = $('.users-' + sort);

    clearSearch();
    clearSortResults();

    // Display corresponding content
    content.addClass('active');
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

      // Unbind any click events to prevent other users from being deleted
      $('.user-cleanup-submit').unbind("click");

      // Submitting delete form with ajax to reload page at same position
      $('.user-cleanup-submit').click(function(e) {
        e.preventDefault();

        var deleteForm = $(".modal form");
        var actionURL = deleteForm.attr("action");
        
        $.ajax({
          url: actionURL + "/" + id,
          type: 'POST',
          data: deleteForm.serialize(),
          success: function(data) {
            // TODO: Handle messages sent back from controller
            location.reload();
          }
        });
      });
      
      Kora.Modal.open();
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
  
  /**
   * Initialize event handling for each user for updating status or deletion
   */
  function initializeCardEvents() {
    $(".card").each(function() {
      var card = $(this);
      var form = card.find("form");
      var id = card.attr('id').substring(5);
      var name = card.find('.username').html();

      // Toggles activation for a user
      card.find('#active').click(function(e) {
        e.preventDefault();

        $.ajax({
          url: form.prop("action"),
          type: 'POST',
          data: {
            "_token": CSRFToken,
            "_method": 'patch',
            "status": "active"
          },
          success: function(data) {
            // TODO: Handle messages sent back from controller
            if (data.status) {
              // User updated successfully
              checker(card, data.action);
            }
          }
        });
      });

      // Toggles administration status for a user
      card.find('#admin').click(function(e) {
        e.preventDefault();

        $.ajax({
          url: form.prop("action"),
          type: 'POST',
          data: {
            "_token": CSRFToken,
            "_method": 'patch',
            "status": "admin"
          },
          success: function(data) {
            // TODO: Handle messages sent back from controller
            if (data.status) {
              // User updated successfully
              checker(card, data.action);
            }
          },
        });
      });
    });
  }
  
  initializeOptionDropdowns();
  initializeFilters();
  initializeCards()
  initializeSearch();
  initializeCleanUpModals();
  initializeCardEvents()
}
