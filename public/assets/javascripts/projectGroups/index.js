var Kora = Kora || {};
Kora.ProjectGroups = Kora.ProjectGroups || {};

Kora.ProjectGroups.Index = function() {

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

      Kora.Modal.open($('.new-permission-modal-js'));
    });

    // $('.multi-select').chosen({
    //   width: '100%',
    // });
  }

  function initializeEditGroupNameModal() {
    $('.edit-group-name-js').click(function(e) {
      e.preventDefault();

      Kora.Modal.open($('.edit-group-name-modal-js'));
    });
  }

  function initializeAddUsersModal() {
    $('.add-users-js').click(function(e) {
      e.preventDefault();

      Kora.Modal.open($('.add-users-modal-js'));
    });
  }

  function initializeRemoveUserModal() {
    $('.remove-user-js').click(function(e) {
      e.preventDefault();

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
