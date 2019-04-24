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
          let searchVal = $(this).val().toLowerCase();

          $('.user.card').each(function() {
              let fname = $(this).find('.firstname').first().text().toLowerCase();
              let lname = $(this).find('.lastname').first().text().toLowerCase();
              let uname = $(this).find('.username').first().text().toLowerCase();
			  let email = $(this).find('.email').first().text().toLowerCase();

              if(fname.includes(searchVal) | lname.includes(searchVal) | uname.includes(searchVal) | email.includes(searchVal))
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
	
	$(window).resize(); // fixes name disappearing bug
  }

  /**
    * Modal for deleting a user
    */
  function initializeCleanUpModals() {
    Kora.Modal.initialize();

    // Deleting user via cards
    $('.user-trash-js').click(function(e) {
      e.preventDefault();

      var $cleanupModal = $(".users-cleanup-modal-js");
      var card = $(this).parent().parent().parent();
      var id = card.attr('id').substring(5);
      var selfDelete = (adminId == id);
      var validated = false;
      var $deleteValInput = $cleanupModal.find('.delete-validation-js');
      var $deleteValErrorMsg = $deleteValInput.parent().find('.error-message');

      $cleanupModal.find('.modal-content-js').hide();
      if (selfDelete) {
        // Admin deleting themselves
        $cleanupModal.find('.delete-self-1-content-js').show();

        $cleanupModal.find('.user-self-delete-1-submit-js').click(function(e) {
          e.preventDefault();

          $cleanupModal.find('.modal-content-js').hide();
          $cleanupModal.find('.delete-self-2-content-js').show();

          // Validate when unfocusing from delete text input
          $deleteValInput.on('blur', function() {
            validated = SelfDeleteModalValidation($deleteValInput, $deleteValErrorMsg);
          });
        });
      } else {
        // Admin deleting someone else
        $cleanupModal.find('.delete-content-js').show();
      }
      $cleanupModal.find('.content').addClass('small');
      $cleanupModal.find('.title-js').html((selfDelete ? 'Delete Your Account?' : 'Delete User?'));

      // Unbind any click events to prevent other users from being deleted
      $('.user-cleanup-submit').unbind("click");

      // Submitting delete form with ajax to reload page at same position
      $('.user-cleanup-submit').click(function(e) {
        e.preventDefault();

        var deleteForm = $(this).parent();
        var actionURL = deleteForm.attr("action");
        var method = deleteForm.attr("method");

        if (selfDelete) {
          // Deleting self, need to check "DELETE" text input

          // Validate when attempting to delete self
          $cleanupModal.find('.user-self-delete-2-submit-js').click(function(e) {
            e.preventDefault();

            validated = SelfDeleteModalValidation($deleteValInput, $deleteValErrorMsg);
          });
        } else {
          // Deleting someone else
          validated = true;
        }

        if (validated) {
          $.ajax({
            url: actionURL + "/" + id,
            type: method,
            data: deleteForm.serialize(),
            datatype: 'json',
            success: function(data) {
              // TODO: Handle messages sent back from controller
              if (selfDelete) {
                window.location = loginUrl;
              } else {
                window.localStorage.setItem('message', 'User Successfully Deleted');
                location.reload();
              }
            }
          });
        }
      });
      
      Kora.Modal.open();
    });

    // Inviting new users
    $('.new-object-button-js').click(function(e) {
      e.preventDefault();

      var cleanupModal = $(".users-cleanup-modal-js");
      cleanupModal.find('.modal-content-js').hide();
      cleanupModal.find('.invite-content-js').show();
      cleanupModal.find('.content').removeClass('small');
      cleanupModal.find('.title-js').html('Invite User(s)');

      Kora.Modal.open();
    });

    function setError (err) {
	  $('.error-message.emails').html('');
      $('.text-input#emails').addClass('error');
      for (let i = 0; i < err.length; i++) {
        if (i == err.length - 1) {
          $('.error-message.emails').html($('.error-message.emails').html() + err[i] + ' already exist!');
        } else if (i == 0) {
          $('.error-message.emails').html($('.error-message.emails').html() + 'Emails: ' + err[i] + ', ');
        } else {
          $('.error-message.emails').html($('.error-message.emails').html() + err[i] + ' ');
        }
      }

	  $('.invite-content-js .btn-primary').addClass('disabled');
    }

    // check if any entered emails already exist in the system
    $('.text-input#emails').on('blur', function (e) {
      e.preventDefault();

      let $formData = $('.invite-content-js :not(input[name="_method"])').serialize();
      $.ajax({
        url: validateEmailsUrl,
        type: 'POST',
        data: $formData,
        success: function (data) {
          if (data.message.length >= 1) {
            setError(data.message);
          } else {
            $('.text-input#emails').removeClass('error');
            $('.invite-content-js .error-message.emails').text('');
            $('.invite-content-js .btn-primary').removeClass('disabled');
          }
        },
        error: function (err) {
          console.log('Error:');
          console.log(err);
        }
      });
    });

	$('.invite-content-js .btn-primary').click(function(e) {
		e.preventDefault();

		let $form = $('.invite-content-js');
		let $formUrl = $form.prop('action');
		let $formData = $('.invite-content-js').serialize();

		$.ajax({
			url: $formUrl,
			type: 'POST',
			data: $formData,
			success: function (data) {
				$form.submit();
			},
			error: function (err) {
				console.warn(err)
			}
		});
	});
  }

  /**
   * Self delete validation
   */
  function SelfDeleteModalValidation($input, $errorMsg) {
    if ($input.val() != "DELETE") {
      $input.addClass('error');
      $errorMsg.html('Close, try again');
      return false;
    } else {
      $input.removeClass('error');
      $errorMsg.html('');
      return true;
    }
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

  function initializeUserCardEllipsifying() {
    function adjustCardTitle() {
      var alphabetical = false;
      var custom = false;
      var archived = false;

      if ($(".user-sort-js").hasClass("active")) {
        alphabetical = true;
        var cards = $($(".user-sort-js").find(".user.card"));
      }

      for (i = 0; i < cards.length; i++) {	
        var card = $(cards[i]);
        var name_span = $(card.find($(".name")));
        var chevron = $(card.find($(".icon-chevron")));

        var card_width = card.width();
        var chevron_width = chevron.outerWidth(); // all types of project cards have chevrons
        var left_padding = custom ? 0 : 20; // custom projects provide padding from element other than name_span
        var extra_padding = 10;

        var title_width = (card_width - left_padding) - (chevron_width + extra_padding);
        if (title_width < 0) {title_width = 0;}

        name_span.css("white-space", "nowrap");
        name_span.css("overflow", "hidden");
        name_span.css("max-width", title_width + "px");
      }
    }

    $(window).resize(function() {
      adjustCardTitle();
    });

    $(document).ready(function() {
      adjustCardTitle();
    });

    // Recalculate ellipses when switching project types
    $("[href='#custom'], [href='#active'], [href='#inactive']").click(function() { adjustProjectCardTitle(); });
  }
  
  function initializeInviteUserValidation()
  {
	function error(input, error_message) {
	  $(input).prev().text(error_message);
	  $(input).addClass("error"); // applies the error border styling
	}
	
	function success(input) { // when validation is passed on an input
	  $(input).prev().text("");
	  $(input).removeClass("error");
	}
	  
	function validateEmails() {
	  var email_input = $("#emails.text-input");
	  var emails_string = email_input.val();
	  
	  if (emails_string != null && emails_string != "") {
		emails_string = emails_string.replace(/,/g, " ");
		
		var emails = emails_string.split(" ");
		var has_malformed = false;
		var has_valid = false;
		
		for (i = 0; i < emails.length; i++) {
		  var email = emails[i];
		  
		  if (email.length > 3) {
			if (!validateEmail(email)) {
			  error(email_input, "Email: " + email + " is not valid");
			  return false;
			} else {
			  has_valid = true;
			}
		  } else {
			has_malformed = true;
		  }
		}
		
		if (has_malformed && !has_valid) {
		  error(email_input, "Malformed email separation - use commas and/or spaces");
		  return false;
		}
		
		success(email_input);
	    return true; // passed validation
	  } else {
		error(email_input, "This field is required");
	    return false;
	  }
	}
	
	function validateEmail(email) {
      var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	  return re.test(String(email).toLowerCase());
	}
	  
    $("#emails.text-input").blur(function() {
	  validateEmails();
	});
	
	$( "input[name='sendButton']" ).click(function(e) {
	  if (!validateEmails()) {
	    e.preventDefault();
	  }
	})
  }
  
  initializeOptionDropdowns();
  initializeFilters();
  initializeCards();
  initializeSearch();
  initializeCleanUpModals();
  initializeCardEvents();
  initializeUserCardEllipsifying();
  initializeInviteUserValidation();
};
