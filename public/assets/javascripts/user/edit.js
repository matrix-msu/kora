var Kora = Kora || {};
Kora.User = Kora.User || {};

Kora.User.Edit = function() {
  var drop = false;
  var ajaxData

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

      //$('.user-cleanup-submit').click(function(e) {
      //  e.preventDefault();
      //
      //  var deleteForm = $(this).parent();
      //  var actionURL = deleteForm.attr("action");
      //  var method = deleteForm.attr("method");
      //
      //    // Insert user id into delete URL
      //  var pos = actionURL.indexOf('/delete')
      //  actionURL = [actionURL.slice(0, pos), userid, actionURL.slice(pos)].join('');
      //
      //  $.ajax({
      //    url: actionURL,
      //    type: method,
      //    data: deleteForm.serialize(),
      //    datatype: 'json',
      //    success: function(data) {
      //      window.location = redirectUrl;
      //    },
      //    error: function(data) {
      //      //location.reload();
      //    }
      //  });
      //});
    });
  }

  function initializeForm() { //TODO::drag and drop (check validation function)
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
          newProfilePic(e.target.result, name);
        };
        reader.readAsDataURL(this.files[0]);
      }
    });

    // Drag and Drop
    // check if Drag n Drop is supported, also check that we are not on Safari
    if (isAdvancedUpload && window.safari == undefined && navigator.vendor != 'Apple Computer, Inc.') {
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

        drop = true;
        droppedFile = e.originalEvent.dataTransfer.files[0];

        var reader = new FileReader();
        reader.onload = function (e) {
          newProfilePic(e.target.result, droppedFile.name);
          droppedFile = e.target.result;
        };
        reader.readAsDataURL(droppedFile);

        ajaxData = new FormData(form.get(0)); // safari does not support form.get()
        ajaxData.delete('profile'); // safari does not support this
        ajaxData.append("profile", droppedFile);

        drop = true;
      });

      // for ( var pair of ajaxData.entries() ) {
        // console.log(pair[0] + ', ' + pair[1]);
        // //console.log(typeof pair[1]);
        // if (typeof pair[1] === 'object') {
          // console.log(pair[1]);
        // }
      // }

      // form.submit(function(e) { // this has the same run condition as the initializeValidation() function below, which is probably why uncommenting this function prevents us from even uploading a picture normally - though the console.log loop of 'values' below does not log anything about an uploaded photo
        // e.preventDefault();

        // var ajaxData = new FormData(form.get(0));

        // if (droppedFile) {
          // // This solution does not work with drag and drop, possibly need to change the file type
          // ajaxData.append("profile", droppedFile);
          // //console.log('droppedFile: ' + droppedFile);
        // }

        // $.ajax({
          // url: form.attr('action'),
          // type: form.attr('method'),
          // data: ajaxData,
          // dataType: 'json',
          // cache: false,
          // contentType: false,
          // processData: false,
          // success: function(response) {
            // if (response.status) {
              // // Updated successfully
              // location.reload();
            // } else {
              // console.log('success: ' + response.message);
            // }
          // },
          // error: function(error) {
            // // TODO: Handle errors. Currently can get all errors, just need to display them

            // if (error.status == 200) {
              // //location.reload();
              // console.log(error);
              // console.log(error.status);
            // } else {
              // console.log(error);
              // var responseJson = error.responseJSON;
              // $.each(responseJson, function() {
                // console.log('error: ' + this[0]);
              // });
            // }
          // }
        // });
      // });
    }
  }

  function initializeValidation() {
      $('.validate-user-js').on('click', function(e) {
          var $this = $(this);

          e.preventDefault();

          if (drop = 0) {
            values = {};
            $.each($('.user-form').serializeArray(), function(i, field) {
                values[field.name] = field.value;
            });
            values['_method'] = 'PATCH';

            $.ajax({
                url: validationUrl,
                method: 'POST',
                data: values,
                success: function(data) {
                    $('.user-form').submit();
                },
                error: function(err) {
                    $('.error-message').text('');
                    $('.text-input').removeClass('error');

                    $.each(err.responseJSON.errors, function(fieldName, errors) {
                        var $field = $('#'+fieldName);
                        $field.addClass('error');
                        $field.siblings('.error-message').text(errors[0]);
                    });
                }
            });
          } else {
            var form = $(".form-file-input");

            $.ajax({
              url: form.attr('action'),
              method: 'POST',
              data: ajaxData,
              processData: false,
              contentType: false,
              success: function(response) {
                if (response.status) {
                  // Updated successfully
                  //location.reload();
                  //console.log(response.status);
                  $('.user-form').submit();
                } else {
                  //console.log('success: ' + response.message);
                  $('.user-form').submit();
                }
              },
              error: function(error) {
                // TODO: Handle errors. Currently can get all errors, just need to display them

                if (error.status == 200) {
                  //location.reload();
                  console.log(error);
                  console.log(error.status);
                } else {
                  console.log(error);
                  var responseJson = error.responseJSON;
                  $.each(responseJson, function() {
                    console.log('error: ' + this[0]);
                  });
                }
              }
            });
          }
      });

      $('.text-input').on('blur', function(e) {
          var field = this.id;
          var second = false;
          var field2 = '';
          if(field == 'password') {
              second = true;
              field2 = 'password_confirmation';
          } else if(field == 'password_confirmation') {
              second = true;
              field2 = 'password';
          }
          var values = {};
          values[field] = this.value;
          if(second)
              values[field2] = $('#'+field2).val();
          values['_token'] = CSRFToken;
          values['_method'] = 'PATCH';

          $.ajax({
              url: validationUrl,
              method: 'POST',
              data: values,
              error: function(err) {
                  if (err.responseJSON.errors[field] !== undefined) {
                      $('#'+field).addClass('error');
                      $('#'+field).siblings('.error-message').text(err.responseJSON.errors[field][0]);
                  } else {
                      $('#'+field).removeClass('error');
                      $('#'+field).siblings('.error-message').text('');
                  }

                  if(second) {
                      if (err.responseJSON.errors[field2] !== undefined) {
                          $('#'+field2).addClass('error');
                          $('#'+field2).siblings('.error-message').text(err.responseJSON.errors[field2][0]);
                      } else {
                          $('#'+field2).removeClass('error');
                          $('#'+field2).siblings('.error-message').text('');
                      }
                  }
              }
          });
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
                })
                .fail(function() {
                    $imgCont.html('<i class="icon icon-user">');
                });
        }
    }

  initializeChosen();
  initializePasswordChange();
  initializeCleanUpModals();
  initializeForm();
  initializeValidation();
  initializeProfilePicValidation();
}
