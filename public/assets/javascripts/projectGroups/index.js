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
    var $user = $("#list-element" + projectGroup + userID);
    var userName = $user.children('.view-user-js').html();

    $.ajax({
      url: removeUserPath,
      type: 'PATCH',
      data: {
        "_token": CSRFToken,
        "userId": userID,
        "projectGroup": projectGroup,
        "pid": pid
      },
      success: function() {
        // Remove the user from the list of users currently in the group.
        $user.remove();

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
   * Add users to a project's project group.
   *
   * @param projectGroup {int} The project group id.
   * @param userIDs {array} The array of user ids.
   * @param $select {jQuery} The selector for removing.
   */
  self.addUsers = function(projectGroup, userIDs, $select) {

    $.ajax({
      url: addUsersPath,
      type: 'PATCH',
      data: {
        "_token": CSRFToken,
        "userIDs": userIDs,
        "projectGroup": projectGroup
      },
      success: function(data) {
        // data is supposed to be the Old Group ID
        var userMap = {} // A map of userID to their content
        for (userID of userIDs) {
          var userContent = $('#list-element' + data + userID).html();
          userMap[userID] = userContent;
        }

        $('.multi-select').each(function(index) {
          var $this = $(this);
          var groupID = $this.data('group');

          if (groupID == projectGroup) {
            $this.find('option').each(function() {

              // Remove from select if added to projectGroup
              var val = $(this).attr('value');
              if (userIDs.includes(val)) {
                if (data.length == 0) {
                  userMap[val] = $(this).html(); // We need the name for later.
                }

                $(this).remove();
              }
            });
          } else {
            // this select needs to have options added
            for (userID of userIDs) {
              var option = '<option value="' + userID + '">' + userMap[userID] + '</option>';

              // check if if is already in this select list
              var canAddToSelect = true;
              $this.children('option').each(function() {
                if ($(this).val() == userID) {
                  canAddToSelect = false;
                  return;
                }
              });

              if (canAddToSelect) {
                $this.append(option);
              }
            }
          }
        });

        $('.group-js').each(function() {
          var $this = $(this);
          var $groupCard = $('#' + $this.attr('id') + " .users-js");
          var $groupCardAddUser = $groupCard.find('.add-users-js')
          var userContent = $('#list-element' + projectGroup + userID).html();

          if ($this.attr('id') == projectGroup) {
            // Add the user to the users currently in the group.
            for (userID of userIDs) {
              if (data.length > 0) {
                var element = '<div class="user" id="list-element' + projectGroup + userID + '">';
                element += userMap[userID];
                element += '</div>';

                $groupCardAddUser.before(element);
              } else {
                var element = '<div class="user" id="list-element' + projectGroup + userID;
                element += '"><a href="#" class="name view-user-js">' + userMap[userID] + '</a>';
                element += '<a href="#" class="cancel remove-user-js" data-value="[';
                element += projectGroup + ", " + userID + ", " + pid + ']">';
                element += '<i class="icon icon-cancel"></i></a></div>';
                $groupCardAddUser.before(element);
              }
            }
          } else {
            // Remove the user from the users currently in the group.
            for (userID of userIDs) {
              $groupCard.find('#list-element' + $this.attr('id') + userID).remove();
            }
          }
          initializeViewUserModal();

        });
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
        type: 'PATCH',
        data: {
          "_token": CSRFToken,
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
      type: 'PATCH',
      data: {
        "_token": CSRFToken,
        "projectGroup": projectGroup,
        "permCreate": permCreate,
        "permEdit": permEdit,
        "permDelete": permDelete
      }
    });
  }

  function initializePermissionsToggle() {
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
      var $editNameModalInput = $editNameModal.find('.group-name-js');
      $editNameModalInput.val(groupName);

      // Submission of Editing a Name
      var submitNameChange = function(gid) {
        return function(e) {
          e.preventDefault();

          var groupName = $('.edit-group-name-modal-js').find('.group-name-js').val();
          self.editGroupName(gid, groupName);
        }
      }
      $('.edit-group-name-submit-js').on('click', submitNameChange(gid));

      Kora.Modal.open($('.edit-group-name-modal-js'));
    });
  }

  function initializeAddUsersModal() {
    $('.add-users-js').click(function(e) {
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

      // Submission of Adding a User
      var submitUsers = function(groupID, $addUserModal, $select) {
        return function(e) {
          e.preventDefault();

          values = $("#select-" + groupID).chosen().val();

          // Validation: at least one selected
          if (values != null) {
            self.addUsers(groupID, values, $select);
            Kora.Modal.close($addUserModal);

            // Kill the chosen element after Modal Close.
            setTimeout(function() {
              $(".multi-select").chosen('destroy');
              $addUserModal.find('.body').html('');
            }, 500);
          }
        };
      }
      $('.add-users-submit-js').on('click', submitUsers(groupID, $addUserModal, $select));

      Kora.Modal.open($addUserModal);
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

      Kora.Modal.open($('.view-user-modal-js'));
    });
  }

  function initializeDeletePermissionModal() {
    $('.delete-permission-group-js').click(function(e) {
      e.preventDefault();

      Kora.Modal.open($('.delete-permission-group-modal-js'));
    });
  }

  Kora.Modal.initialize();
  initializePermissionsToggle();
  initializeNewPermissionModal();
  initializeDeletePermissionModal();
  initializeEditGroupNameModal();
  initializeAddUsersModal();
  initializeRemoveUserModal();
  initializeViewUserModal();
}
