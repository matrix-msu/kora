var Kora = Kora || {};
Kora.Inputs = Kora.Inputs || {};

Kora.Inputs.File = function() {
  // For profile pic functionality
  var form = $(".form-file-input");
  var fileInput = $(".profile-input");
  var button = $(".profile-label");
  var picCont = $(".profile-label .icon-user-cont");
  var filename = $(".filename");
  var instruction = $(".instruction");
  var droppedFile = false;
  var droppedFormFile = false;

  var redirectUrl = window.location.href;
  redirectUrl = redirectUrl.slice(0, redirectUrl.indexOf('/forms'));

  // Remove selected profile pic
  function resetFileInput() {
    fileInput.replaceWith(fileInput.val('').clone(true));
    instruction.removeClass("photo-selected");
    picCont.html("<i class='icon icon-user'></i>");
    droppedFile = false;

    if (window.location.href.includes('forms')) {
      filename.html("Drag & Drop the Form File Here");
      Kora.Forms.Import();
    } else {
      filename.html("Drag & Drop the Project File Here");
      Kora.Projects.Import();
    }
  }

  // Profile pic is added, populate profile pic label, set up remove event
  function newProfilePic(pic, name) {
    picCont.html("<img src='"+pic+"' alt='Profile Picture'>");
    filename.html(name + "<span class='remove ml-xs'><i class='icon icon-cancel'></i></span>");
    instruction.addClass("photo-selected");

    droppedFile = pic;

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

  function initializeFileUpload() {
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

    // For non drag'n'drop
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
    // detect and disable if we are on Safari
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

        droppedFile = e.originalEvent.dataTransfer.files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
          picCont.html("<img src='"+e.target.result+"' alt='Profile Picture'>");
          newProfilePic(e.target.result, droppedFile.name);
          droppedFile = e.target.result;
        };
        reader.readAsDataURL(droppedFile);
        droppedFormFile = droppedFile;
      });

      form.submit(function(e) {
        e.preventDefault();

        var ajaxData = new FormData(form.get(0)); // not supported by safari

        if (droppedFormFile && window.location.href.includes('forms')) {
          ajaxData.delete('form'); // not supported by safari
          ajaxData.append('form', droppedFormFile);
        } else if (droppedFormFile) {
          ajaxData.delete('project');
          ajaxData.append('project', droppedFormFile);
        } else {
          ajaxData.append('form', $('.file-input-js')[0].files[0]);
        }

        for ( var pair of ajaxData.entries() ) {
          console.log(pair[0] + ', ' + pair[1]);
          //console.log(typeof pair[1]);
          if (typeof pair[1] === 'object') {
            console.log(pair[1]);
          }
        }

        $.ajax({
          url: form.attr('action'),
          method: 'POST',
          data: ajaxData,
          dataType: 'json',
          cache: false,
          contentType: false,
          processData: false,
          success: function(response) {
            window.location.href = redirectUrl;
          },
          error: function(error) {
            // TODO: Handle errors. Currently can get all errors, just need to display them
            console.log('error');
            console.log(error);
            if (error.status == 200) {
              window.location.href = redirectUrl;
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

  initializeFileUpload();
}