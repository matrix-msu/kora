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
  }

  function initializeForm() {
    // For profile pic functionality
    var form = $(".form-file-input");
    var fileInput = $(".profile-input");
    var button = $(".profile-label");
    var picCont = $(".profile-label .icon-user-cont");
    var filename = $(".filename");
    var instruction = $(".instruction");
    var droppedFile = false;
    var droppedFilename = false;

    // Remove selected profile pic
    function resetFileInput() {
      fileInput.replaceWith(fileInput.val('').clone(true));
      filename.html("Add a photo to help others identify you");
      instruction.removeClass("photo-selected");
      picCont.html("<i class='icon icon-user'></i>");
      droppedFile = false;
      droppedFilename = false;
    };

    // Profile pic is added, populate profile pic label, set up remove event
    function newProfilePic(pic, name) {
      picCont.html("<img src='"+pic+"' alt='Profile Picture'>");
      filename.html(name + "<span class='remove ml-xs'><i class='icon icon-cancel'></i></span>");
      instruction.addClass("photo-selected");

      droppedFile = pic;
      droppedFilename = name;

      $(".remove").click(function(event) {
        event.preventDefault();
        resetFileInput();
      });
    }

    // Check for Drag and Drop Support on the browser
    var isAdvancedUpload = function() {
      var div = document.createElement('div');
      return (('draggable' in div) || ('ondragstart' in div && 'ondrop' in div)) && 'FormData' in window && 'FileReader' in window;
    }();

    // When hovering over input, hitting enter or space opens the menu
    button.keydown(function(event) {
      if ( event.keyCode == 13 || event.keyCode == 32 ) {
          fileInput.focus();
      }
    });

    // Clicking input opens menu
    button.click(function(event) {
      fileInput.focus();
    });

    // For clicking on input to select an image
    fileInput.change(function(event) {
      event.preventDefault();

      if (this.files && this.files[0]) {
        var name = this.value.substring(this.value.lastIndexOf('\\') + 1);
        var reader = new FileReader();
        reader.onload = function (e) {
          picCont.html("<img src='"+e.target.result+"' alt='Profile Picture'>");
          newProfilePic(e.target.result, name);
        };
        reader.readAsDataURL(this.files[0]);
      }
    });

    // Drag and Drop
    if (isAdvancedUpload) {
      button.addClass('has-advanced-upload');

      button.on('drag dragstart dragend dragover dragenter dragleave drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
      })
      .on('dragover dragenter', function() {
        button.addClass('is-dragover');
      })
      .on('dragleave dragend drop', function() {
        button.removeClass('is-dragover');
      })
      .on('drop', function(e) {
        e.stopPropagation();
        e.preventDefault();

        droppedFile = e.originalEvent.dataTransfer.files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
          picCont.html("<img src='"+e.target.result+"' alt='Profile Picture'>");
          newProfilePic(e.target.result, droppedFile.name);
          droppedFile = e.target.result;
          console.log(e.target.result);
        };
        reader.readAsDataURL(droppedFile);
      });

      form.submit(function(e) {
        e.preventDefault();

        var ajaxData = new FormData(form.get(0));

        if (droppedFile) {
          // This solution does not work with drag and drop, possibly need to change the file type
          ajaxData.append("profile", droppedFile);
          console.log(droppedFile);
        }

        $.ajax({
          url: form.attr('action'),
          type: form.attr('method'),
          data: ajaxData,
          dataType: 'json',
          cache: false,
          contentType: false,
          processData: false,
          success: function(response) {
            if (response.status) {
              // Updated successfully
              location.reload();
            } else {
              console.log(response.message);
            }
          },
          error: function(error) {
            // TODO: Handle errors. Currently can get all errors, just need to display them

            if (error.status == 200) {
              location.reload();
            } else {
              console.log(error);
              var responseJson = error.responseJSON;
              $.each(responseJson, function() {
                console.log(this[0]);
              });
            }
          }
        });
      });
    }
  }

  initializeChosen();
  initializePasswordChange();
  initializeCleanUpModals();
  initializeForm();
}
