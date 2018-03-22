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

  initializeChosen();
  initializePasswordChange();
}
