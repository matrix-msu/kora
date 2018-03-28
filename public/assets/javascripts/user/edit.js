var Kora = Kora || {};
Kora.User = Kora.User || {};

Kora.User.Edit = function() {
  function initializeChosen() {
    $(".chosen-select").chosen({
      disable_search_threshold: 10,
      width: '100%'
    });
  }

  function initializePasswordChange() {
    var newPassword = $("#new_password");
    var confirmPassword = $("#confirm");

    // Initially disable confirm password
    confirmPassword.prop("disabled", true);

    // Determine if confirm password is enabled or disabled
    newPassword.keyup(function(e) {
      if ($(this).val().length) {
        // Enable confirm
        confirmPassword.prop("disabled", false);
      } else {
        // Disabled confirm
        confirmPassword.val("");
        confirmPassword.prop("disabled", true);
      }
    });
  }

  /**
    * Modal for deleting a user
    */
  function initializeCleanUpModals() {
    Kora.Modal.initialize();

    $('.user-trash-js').click(function(e) {
      e.preventDefault();

      Kora.Modal.open();

      $('.user-cleanup-submit').click(function(e) {
        e.preventDefault();

        //var deleteForm = $(".modal form");
        //var actionURL = deleteForm.attr("action");

        /*$.ajax({
          url: actionURL + "/" + id,
          type: 'DELETE',
          data: deleteForm.serialize(),
          success: function(data) {
            // TODO: Handle messages sent back from controller
            location.reload();
          }
        });*/
      });
    });
  }

  initializeChosen();
  initializePasswordChange();
  initializeCleanUpModals();
}
