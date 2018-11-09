var Kora = Kora || {};
Kora.ProjectGroups = Kora.ProjectGroups || {};

Kora.ProjectGroups.Index = function() {
  var self = Kora.ProjectGroups.Index;


  /**
   * Remove a user from a project's project group.
   *
   * @param projectGroup {int} The project group id.
   * @param userId {int} The user id.
   * @param pid {int} The project id.
   */
  self.removeUser = function(projectGroup, userID, pid) {
    $.ajax({
      url: removeUserPath,
      type: 'POST',
      data: {
        "_token": CSRFToken,
        "_method": 'patch',
        "userId": userID,
        "projectGroup": projectGroup,
        "pid": pid
      },
      success: function() {
        $('.note').children('p').text('User Successfully Removed from Group');
        $('.notification').removeClass('dismiss');
        setTimeout(function(){
          $('.notification').addClass('dismiss');
        }, 4000);
        var $user = $("#list-element" + projectGroup + userID);
        var $parent = $user.parent();
        var userName = $user.children('.view-user-js').html();

        $user.fadeOut(function() {
          // Remove the user from the list of users currently in the group.
          $(this).remove();

          // If was the last user of the group display no-users text

          if ($parent.children('.user-js').length == 0) {
            self.showNoUsersText($parent, projectGroup);
          }
        });

        // Add the user to the users that can be added to the group.
        var option = '<option value="' + userID + '">' + userName + '</option>';
        var $select = $('#select-' + projectGroup);
        var canAddToSelect = true;

        $select.children('option').each(function() {
          if ($(this).val() == userID) {
            canAddToSelect = false;
            return;
          }
        });

        if (canAddToSelect) {
          $select.append(option);
        }

        // Close the modal.
        Kora.Modal.close();
      }
    });
  }


  /**
   * Helper function for displaying a user in the project group.
   *
   * @param isUserMove {int} Whether or not the user was moved from a group
   * @param pid {int} The project id
   * @param userIDs {array} The list of userIDs
   * @param userMap {object} The userMap for the function.
   * @param $groupCard {jQuery} The parent element to append user.
   */
  self.showUser = function(isUserMove, pid, projectGroup, userIDs, userMap, $groupCard) {
    // Add the user to the users currently in the group.
    for (userID of userIDs) {
      if (isUserMove.length > 0) {
        var element = '<div style="display:none" class="user user-js" ';
        element += 'id="list-element' + projectGroup + userID + '">';
        element += userMap[userID];
        element += '</div>';
        $groupCard.append(element).children('.user-js').fadeIn();
      } else {
        var element = '<div style="display:none" class="user user-js" ';
        element += 'id="list-element' + projectGroup + userID;
        element += '"><a href="#" class="name view-user-js">' + userMap[userID] + '</a>';
        element += '<a href="#" class="cancel remove-user-js" data-value="[';
        element += projectGroup + ", " + userID + ", " + pid + ']">';
        element += '<i class="icon icon-cancel"></i></a></div>';
        $groupCard.append(element).children('.user-js').fadeIn();
      }
    }
  }

  self.showNoUsersText = function($groupCard, groupID) {
    // Is the last user of the group so display no-users text
    var element = '<p style="display: none" class="no-users no-users-js">';
    element += '<span>No users in this group, select</span><a href="#" class="user-add ';
    element += 'add-users-js underline-middle-hover" data-select="add_user_select' + groupID;
    element += '" data-group="' + groupID + '">';
    element += '<i class="icon icon-user-add"></i><span>Add User(s) to Group</span></a>';
    element += '<span>to add some!</span></p>';
    $groupCard.append(element).children('.no-users-js').fadeIn();
  }

  /**
   * Add users to a project's project group.
   *
   * @param projectGroup {int} The project group id.
   * @param userIDs {array} The array of user ids.
   * @param $select {jQuery} The selector for removing.
   */
  self.addUsers = function(projectGroup, userIDs, invited_users_emails, invited_personal_msg, $select) {

    $.ajax({
      url: addUsersPath,
      type: 'POST',
      data: {
        "_token": CSRFToken,
        "_method": 'patch',
        "userIDs": userIDs,
		"emails": invited_users_emails,
		"message": invited_personal_msg,
        "projectGroup": projectGroup
      },
      success: function(data) {
		window.localStorage.setItem('message', "User(s) Successfully Added to Permissions Group!");
		//console.log('data: ' + data);
		location.reload();
      }
    });
  }

  /**
   * Edit project group name.
   *
   * @param gid {int} The project group id.
   * @param newName {string} The new name of the group.
   */
  self.editGroupName = function(gid, newName) {
    if (newName == '') {
      // Validation: no blank name
      $('.edit-group-name-button-js input').prop('disabled', true);
      return;
    } else {
      $.ajax({
        url: editNamePath,
        type: 'POST',
        data: {
          "_token": CSRFToken,
          "_method": 'patch',
          "gid": gid,
          "name": newName
        },
        success: function(response) {
          $('#' + gid).find('.name-js').html(newName);
          Kora.Modal.close();
        }
      });
    }
  }

  /**
   * Edit project group name.
   *
   * @param gid {int} The project group id
   */
  self.deletePermissionsGroup = function(gid) {
    $.ajax({
      url: deletePermissionsPath,
      type: 'POST',
      data: {
        "_token": CSRFToken,
        "_method": 'delete',
        "projectGroup": gid
      },
      success: function() {
        Kora.Modal.close();

        // Allow for Modal to close before page reload.
        setTimeout(function() {
          location.reload();
        }, 500);
      }
    });
  }

  /**
   * Update the permissions of a particular project group.
   *
   * @param projectGroup {int} The project group id
   */
  self.updatePermissions = function(projectGroup) {
    // If the box is checked, allow users in the project group to do that action within the project.
    var permCreate = ($('#create-' + projectGroup).is(':checked') ? 1 : 0);
    var permEdit = ($('#edit-' + projectGroup).is(':checked') ? 1 : 0);
    var permDelete = ($('#delete-' + projectGroup).is(':checked') ? 1 : 0);

    $.ajax({
      url: updatePermissionsPath,
      type: 'POST',
      data: {
        "_token": CSRFToken,
        "_method": 'patch',
        "projectGroup": projectGroup,
        "permCreate": permCreate,
        "permEdit": permEdit,
        "permDelete": permDelete
      }
    });
  }

  function initializePermissionsToggles() {



    $('.permission-toggle-by-name-js').click(function(e) {
      e.preventDefault();

      var $this = $(this);
      var $cardToggle = $this.parent().next();
      $cardToggle.children().click()
    });

    $('.permission-toggle-js').click(function(e) {
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

  function initializeNewPermissionModal() {
    $('.new-permission-js').click(function(e) {
      e.preventDefault();

      $newPermissionsModal = $('.new-permission-modal-js');
      $newPermissionsModal.find('.multi-select').chosen({
        width: '100%',
      });
      
      var selectUsers = $newPermissionsModal.find('.chosen-container-multi .chosen-choices');
      var childCheck = selectUsers.siblings('.chosen-drop').children('.chosen-results');

      selectUsers.click(function () {
        if (childCheck.children().length === 0) {
          childCheck.append('<li class="no-results">No options to select!</li>');
        } else if (childCheck.children('.active-result').length === 0 && childCheck.children('.no-results').length === 0) {
          childCheck.append('<li class="no-results">No more options to select!</li>');
        }
      });

      Kora.Modal.open($newPermissionsModal);
    });
  }

  function initializeEditGroupNameModal() {
    $('.edit-group-name-js').click(function(e) {
      e.preventDefault();

      // Initialization of Modal with Name editable
      var groupName = $(this).data('name');
      var gid = $(this).data('group');

      var $editNameModal = $('.edit-group-name-modal-js');
      var $editNameModalInput = $editNameModal.find('.create-group-name-js');
      $editNameModalInput.val(groupName);

      // Submission of Editing a Name
      var submitNameChange = function(gid) {
        return function(e) {
          e.preventDefault();

          var groupName = $('.edit-group-name-modal-js').find('.create-group-name-js').val();
          self.editGroupName(gid, groupName);
        }
      }
      $('.edit-group-name-submit-js').on('click', submitNameChange(gid));

      Kora.Modal.open($('.edit-group-name-modal-js'));
    });
  }

  function setError (err) {
    $('.add-users-modal-js .emails').html('');
    $('.add-users-modal-js .emails').addClass('error');

    for (let i = 0; i < err.length; i++) {
      if (i == err.length - 1) {
        $('.add-users-modal-js .error-message.emails').html($('.add-users-modal-js .error-message.emails').html() + err[i] + ' already exist!');
      } else if (i == 0) {
        $('.add-users-modal-js .error-message.emails').html($('.add-users-modal-js .error-message.emails').html() + 'Emails: ' + err[i] + ', ');
      } else {
        $('.add-users-modal-js .error-message.emails').html($('.add-users-modal-js .error-message.emails').html() + err[i] + ' ');
      }
    }

    $('.add-users-modal-js .btn').addClass('disabled');
  }

  function initializeValidateEmails () {
    let token = '_token='+CSRFToken;
    let values = token + '&' + $(this).serialize();

    $.ajax({
      url: validateEmailsUrl,
      type: 'POST',
      data: values,
      success: function (data) {
        if (data.message.length >= 1) {
          setError(data.message);
        } else {
          $('.add-users-modal-js .emails').removeClass('error');
          $('.add-users-modal-js .error-message.emails').text('');
          $('.add-users-modal-js .btn').removeClass('disabled');
        }
      },
      error: function (err) {
        console.log('Error:');
        console.log(err);
      }
    });
  }

  function initializeAddUsersModal() {
    $(document).on('click', '.add-users-js', function(e) {
      e.preventDefault();

      // Initialization of Modal with Users selectable
      var selectID = $(this).data('select');
      var groupID = $(this).data('group');
      var $select = $("#" + selectID);
      var $addUserModal = $('.add-users-modal-js');
      var $addUserModalBody = $addUserModal.find('.body');
      $addUserModalBody.html($select.html());

      $addUserModalBody.find('.multi-select').chosen({
        width: '100%',
      });

      var selectUsers = $addUserModalBody.find('.chosen-container-multi .chosen-choices');
      var childCheck = selectUsers.siblings('.chosen-drop').children('.chosen-results');

      selectUsers.click(function () {
        if (childCheck.children().length === 0) {
          childCheck.append('<li class="no-results">No options to select!</li>');
        } else if (childCheck.children('.active-result').length === 0 && childCheck.children('.no-results').length === 0) {
          childCheck.append('<li class="no-results">No more options to select!</li>');
        }
      });

      // Submission of Adding a User
      var submitUsers = function(groupID, $addUserModal, $select) {
        return function(e) {
          e.preventDefault();

          var users_to_add = $("#select-" + groupID).chosen().val();
          var invited_users_emails = $("#emails-" + groupID).val();
          var invited_personal_msg = $("#message-" + groupID).val();

          // Validation: at least one selected
          self.addUsers(groupID, users_to_add, invited_users_emails, invited_personal_msg, $select);
          Kora.Modal.close($addUserModal);

          // Kill the chosen element after Modal Close.
          setTimeout(function() {
            $(".multi-select").chosen('destroy');
            $addUserModal.find('.body').html('');
          }, 500);
        };
      }
	  
      $('.add-users-submit-js').on('click', submitUsers(groupID, $addUserModal, $select));
		
      Kora.Modal.open($addUserModal);
      $('.emails').on('blur', initializeValidateEmails);
    });
  }

  function initializeRemoveUserModal() {
    $(document).on('click', '.remove-user-js', function(e) {
      e.preventDefault();

      var data = $(this).data('value');
      var removeUser = function() {
        self.removeUser(data[0], data[1], data[2]);
      };

      $('.user-remove-submit-js').on('click', '.user-remove-btn-js', removeUser);
      Kora.Modal.open($('.remove-user-modal-js'));
    });
  }

  function initializeViewUserModal() {
    $('.view-user-js').click(function(e) {
      e.preventDefault();

      $this = $(this);
      // Check if profile picture exists
      $modal.find('.profile-js').html("").css("top", "-63px");
      $.get($this.data('profile'))
          .done(function() {
            $modal.find('.profile-js').html('<img src="' + $this.data('profile') + '" alt="Profile Pic">');
          })
          .fail(function() {
            $modal.find('.profile-js').html('<i class="icon icon-user">').css("top", "-23px");;
          });
      $modal.find('.name-attr-js').html($this.data('name'));
      $modal.find('.username-attr-js').html($this.data('username'));
      $modal.find('.email-attr-js').html($this.data('email'));
      $modal.find('.organization-attr-js').html($this.data('organization'));
      $modal.find('.profile-link-js').attr('href', $this.data('profile-url'));

      Kora.Modal.open($('.user-profile-modal-js'));
    });
  }

  function initializeDeletePermissionModal() {
    $('.delete-permission-group-js').click(function(e) {
      e.preventDefault();

      Kora.Modal.open($('.delete-permission-group-modal-js'));

      var gid = $(this).data('group');
      var deletePermissionsGroup = function(gid) {
        return function() {
          self.deletePermissionsGroup(gid);
        }
      };

      $('.permissions-delete-submit-js').on('click', '.permissions-delete-btn-js', deletePermissionsGroup(gid));
      Kora.Modal.open($('.delete-permission-group-modal-js'));
    });
  }

  function initializeUserCardEllipsifying() {
    function adjustCardTitle() {
      var cards = $($(".permission-group-js").find(".group-js.card"));

      for (i = 0; i < cards.length; i++) {	
        var card = $(cards[i]);
        var name_span = $(card.find($(".name")));
        var chevron = $(card.find($(".icon-chevron")));

        var card_width = card.width();
        var chevron_width = chevron.outerWidth(); // all types of project cards have chevrons
        var extra_padding = 10;

        var title_width = card_width - (chevron_width + extra_padding);
        if (title_width < 0) {title_width = 0;}

        name_span.css("text-overflow", "ellipsis");
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
  }
  
  function initializeValidation() {
	var checkbox_names = {"create": true, "edit": true, "delete": true};
	
	function error(input, error_message) {
	  $(input).prev().text(error_message);
	  $(input).addClass("error"); // applies the error border styling
	}
	
	function success(input) { // when validation is passed on a text input
	  $(input).prev().text("");
	  $(input).removeClass("error");
	}
	
	function validateGroupName() {
	  var name_input = $(".create-group-name-js");
	  var name = name_input.val();
	  
	  if (name == null || name == "") {
		error(name_input, "This field is required");
		return false;
	  } else {
		success(name_input);
		return true;
	  }
	}
	
	function validateGroupOptions() {
	  var check_create = $("input[name='create'].check-box-input");
	  var check_edit = $("input[name='edit'].check-box-input");
	  var check_delete = $("input[name='delete'].check-box-input");
	  var error_msg = $(".group-options-error-message");
	  
	  if (check_create && check_edit && check_delete &&
	  (check_create.prop("checked") || check_edit.prop("checked") || check_delete.prop("checked"))) {
		error_msg.text("");
		return true;
	  } else {
	    error_msg.text("Select at least one permission");
		return false;
	  }
	}
	
	// validates a comma and/or space separated list of emails
	function validateEmails() {
	  var email_input = $("#emails-new-perm-group");
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
	  }
	  
	  success(email_input);
	  return true;
	}
	
	function validateEmail(email) {
      var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	  return re.test(String(email).toLowerCase());
	}
	
    $(".create-group-name-js").blur(function() {
	  validateGroupName();
    });
	
	$("#emails-new-perm-group").blur(function() {
		validateEmails();
	});
	
	$(".check-box-input").click(function() {
	  var name = $(this).attr("name");
	  if (name !== null && checkbox_names[name] != null) {
	    validateGroupOptions();
	  }
	});
	
	$(".create-submit-js").click(function(e) {
	  var valid_name = validateGroupName();
	  var valid_options = validateGroupOptions();
	  var valid_emails = validateEmails();
	  
	  if (!valid_name || !valid_options || !valid_emails) {
		e.preventDefault();
	  }
	});
  }

  Kora.Modal.initialize();
  initializePermissionsToggles();
  initializeNewPermissionModal();
  initializeDeletePermissionModal();
  initializeEditGroupNameModal();
  initializeAddUsersModal();
  // initializeValidateEmails();
  initializeRemoveUserModal();
  initializeViewUserModal();
  initializeUserCardEllipsifying();
  initializeValidation();
}
