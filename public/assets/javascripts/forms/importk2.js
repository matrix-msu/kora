var Kora = Kora || {};
Kora.Forms = Kora.Forms || {};

Kora.Forms.ImportK2 = function() {

    function initializeFormProgression() {
        $('.file-input-js').change(function() {
            $('.spacer-fade-js').fadeIn(1000);
            $('.formfile-section-2').removeClass('hidden');

            $('html, body').animate({
                scrollTop: $("#scroll-here").offset().top
            }, 1000);
        });

        $('.upload-file-btn-js').click(function(e) {
            $('.formfile-link').removeClass('active');
            $('.forminfo-link').addClass('active');
            $('.forminfo-link').addClass('underline-middle');

            $('.formfile-section').addClass('hidden');
            $('.forminfo-section').removeClass('hidden');

            $('html, body').animate({
                scrollTop: $("#top-dog").offset().top
            }, 1000);
        });
    }

    // For fiel functionatily
    var form = $('#k2_form');

    var schemeInput = $(".scheme-input");
    var recordInput = $(".record-input");
    var fileInput = $(".file-input");

    var schemeButton = $(".scheme-label");
    var recordButton = $(".record-label");
    var fileButton = $(".file-label");

    var schemeFilename = $(".scheme-filename");
    var recordFilename = $(".record-filename");
    var fileFilename = $(".file-filename");

    var schemeInstruction = $(".scheme-instruction");
    var recordInstruction = $(".record-instruction");
    var fileInstruction = $(".file-instruction");

    var schemeDroppedFile = false;
    var recordDroppedFile = false;
    var fileDroppedFile = false;

    var drop = false;
    var ajaxData

    //Resets file input
    function resetFileInput(type) {
        switch(type) {
            case "scheme":
                schemeInput.replaceWith(schemeInput.val('').clone(true));
                schemeFilename.html("Drag & Drop or Select the Kora 2 Scheme XML Below");
                schemeInstruction.removeClass("photo-selected");
                schemeDroppedFile = false;
                break;
            case "record":
                recordInput.replaceWith(recordInput.val('').clone(true));
                recordFilename.html("Drag & Drop or Select the Kora 2 Record XML Below");
                recordInstruction.removeClass("photo-selected");
                recordDroppedFile = false;
                break;
            case "file":
                fileInput.replaceWith(fileInput.val('').clone(true));
                fileFilename.html("Drag & Drop or Select the Kora 2 Record Files Zip Below");
                fileInstruction.removeClass("photo-selected");
                fileDroppedFile = false;
                break;
            default:
                break;
        }
    }

    //SImulating just for fun
    function newProfilePic(type, pic, name) {
        switch(type) {
            case "scheme":
                schemeFilename.html(name + "<span class='remove-scheme remove ml-xs'><i class='icon icon-cancel'></i></span>");
                schemeInstruction.addClass("photo-selected");
                schemeDroppedFile = pic;
                $(".remove-scheme").click(function(event) {
                    event.preventDefault();
                    resetFileInput(type);
                });
                break;
            case "record":
                recordFilename.html(name + "<span class='remove-record remove ml-xs'><i class='icon icon-cancel'></i></span>");
                recordInstruction.addClass("photo-selected");
                recordDroppedFile = pic;
                $(".remove-record").click(function(event) {
                    event.preventDefault();
                    resetFileInput(type);
                });
                break;
            case "file":
                fileFilename.html(name + "<span class='remove-file remove ml-xs'><i class='icon icon-cancel'></i></span>");
                fileInstruction.addClass("photo-selected");
                fileDroppedFile = pic;
                $(".remove-file").click(function(event) {
                    event.preventDefault();
                    resetFileInput(type);
                });
                break;
            default:
                break;
        }
    }

    // Check for Drag and Drop Support on the browser
    var isAdvancedUpload = function() {
        var div = document.createElement('div');
        return (('draggable' in div) || ('ondragstart' in div && 'ondrop' in div)) && 'FormData' in window && 'FileReader' in window;
    }();

    //We're basically replicating what profile pic does, just for 3 file inputs on a single page
    function initializeFileUpload() {
        // When hovering over input, hitting enter or space opens the menu
        schemeButton.keydown(function(event) {
            if( event.keyCode == 13 || event.keyCode == 32 )
                schemeInput.focus();
        });
        recordButton.keydown(function(event) {
            if( event.keyCode == 13 || event.keyCode == 32 )
                recordInput.focus();
        });
        fileButton.keydown(function(event) {
            if( event.keyCode == 13 || event.keyCode == 32 )
                fileInput.focus();
        });

        // Clicking input opens menu
        schemeButton.click(function(event) { schemeInput.focus(); });
        recordButton.click(function(event) { recordInput.focus(); });
        fileButton.click(function(event) { fileInput.focus(); });

        // For clicking on input to select an image
        schemeInput.change(function(event) {
            event.preventDefault();

            if (this.files && this.files[0]) {
                var name = this.value.substring(this.value.lastIndexOf('\\') + 1);
                var reader = new FileReader();
                reader.onload = function (e) { newProfilePic("scheme",e.target.result, name); };
                reader.readAsDataURL(this.files[0]);
            }
        });
        recordInput.change(function(event) {
            event.preventDefault();

            if (this.files && this.files[0]) {
                var name = this.value.substring(this.value.lastIndexOf('\\') + 1);
                var reader = new FileReader();
                reader.onload = function (e) { newProfilePic("record",e.target.result, name); };
                reader.readAsDataURL(this.files[0]);
            }
        });
        fileInput.change(function(event) {
            event.preventDefault();

            if (this.files && this.files[0]) {
                var name = this.value.substring(this.value.lastIndexOf('\\') + 1);
                var reader = new FileReader();
                reader.onload = function (e) { newProfilePic("file",e.target.result, name); };
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Drag and Drop
        // detect and disable if we are on Safari
        if (isAdvancedUpload && window.safari == undefined && navigator.vendor != 'Apple Computer, Inc.') {
            schemeButton.addClass('has-advanced-upload');
            recordButton.addClass('has-advanced-upload');
            fileButton.addClass('has-advanced-upload');

            schemeButton.on('drag dragstart dragend dragover dragenter dragleave drop', function(e) { e.preventDefault(); e.stopPropagation(); })
                .on('dragover dragenter', function() { schemeButton.addClass('is-dragover'); })
                .on('dragleave dragend drop', function() { schemeButton.removeClass('is-dragover'); })
                .on('drop', function(e) {
                    e.stopPropagation();
                    e.preventDefault();

                    schemeDroppedFile = e.originalEvent.dataTransfer.files[0];
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        newProfilePic('scheme', e.target.result, schemeDroppedFile.name);
                        schemeDroppedFile = e.target.result;
                    };
                    drop = true
                    reader.readAsDataURL(schemeDroppedFile);
                    ajaxData = new FormData(form.get(0));

                    ajaxData.delete('form'); // safari does not support this
                    ajaxData.append("form", schemeDroppedFile);

                    $('.file-input-js').trigger('change');
                });

            recordButton.on('drag dragstart dragend dragover dragenter dragleave drop', function(e) { e.preventDefault(); e.stopPropagation(); })
                .on('dragover dragenter', function() { recordButton.addClass('is-dragover'); })
                .on('dragleave dragend drop', function() { recordButton.removeClass('is-dragover'); })
                .on('drop', function(e) {
                    e.stopPropagation();
                    e.preventDefault();

                    recordDroppedFile = e.originalEvent.dataTransfer.files[0];
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        newProfilePic('record', e.target.result, recordDroppedFile.name);
                        recordDroppedFile = e.target.result;
                    };
                    drop = true
                    reader.readAsDataURL(recordDroppedFile);

                    ajaxData.delete('records'); // safari does not support this
                    ajaxData.append("records", recordDroppedFile);
                });

            fileButton.on('drag dragstart dragend dragover dragenter dragleave drop', function(e) { e.preventDefault(); e.stopPropagation(); })
                .on('dragover dragenter', function() { fileButton.addClass('is-dragover'); })
                .on('dragleave dragend drop', function() { fileButton.removeClass('is-dragover'); })
                .on('drop', function(e) {
                    e.stopPropagation();
                    e.preventDefault();

                    fileDroppedFile = e.originalEvent.dataTransfer.files[0];
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        newProfilePic('file', e.target.result, fileDroppedFile.name);
                        fileDroppedFile = e.target.result;
                    };
                    drop = true
                    reader.readAsDataURL(fileDroppedFile);

                    ajaxData.delete('files'); // safari does not support this
                    ajaxData.append("files", fileDroppedFile);
                });

            $('.btn.submit').click(function(e) {
                e.preventDefault();
                
                if (drop) {

                  ajaxData.delete('name');
                  ajaxData.append('name', $('.text-input.name').val());
                  ajaxData.delete('slug');
                  ajaxData.append('slug', $('.text-input.slug').val());
                  ajaxData.delete('description');
                  ajaxData.append('description', $('.text-area.desc').val());

                  if(!schemeDroppedFile) {
                      ajaxData.delete('form'); // safari does not support this
                  }
                  if(!recordDroppedFile) {
                      ajaxData.delete('records'); // safari does not support this
                  }
                  if(!fileDroppedFile) {
                      ajaxData.delete('files'); // safari does not support this
                  }

                  for ( var pair of ajaxData.entries() ) {
                    console.log(pair[0] + ', ' + pair[1]); 
                    // console.log(typeof pair[1]);
                    if (typeof pair[1] === 'object') {
                      console.log(pair[1]);
                    }
                  }

                  $.ajax({
                      url: form.attr('action'),
                      method: 'POST',
                      data: ajaxData,                     
                      processData: false,
                      contentType: false,
                      success: function(response) {
                          // Will never reach this point because laravel redirecting is actually an error
                          window.location = successUrl;
                      },
                      error: function(error) {
                          // TODO: Handle errors. Currently can get all errors, just need to display them
                          if (error.status == 200) {
                              console.log(error);
                          } else {
                              console.log(error);
                              console.log(error.responseJSON.message);
                              var responseJson = error.responseJSON.errors;
                              if (responseJson) {
                                  $.each(responseJson, function() {
                                      console.log(this[0]);
                                  });
                              }
                          }
                      }
                  });
                } else {
                    ajaxData = new FormData(form.get(0));

                    if(!schemeDroppedFile) {
                      ajaxData.delete('form'); // safari does not support this
                    }
                    if(!recordDroppedFile) {
                        ajaxData.delete('records'); // safari does not support this
                    }
                    if(!fileDroppedFile) {
                        ajaxData.delete('files'); // safari does not support this
                    }

                    for ( var pair of ajaxData.entries() ) {
                      console.log(pair[0] + ', ' + pair[1]);
                      if (typeof pair[1] === 'object') {
                        console.log(pair[1]);
                      }
                    }

                    $.ajax({
                      url: form.attr('action'),
                      method: 'POST',
                      data: ajaxData,
                      processData: false,
                      contentType: false,
                      success: function(response) {
                          // Will never reach this point because laravel redirecting is actually an error
                          window.location = successUrl;
                      },
                      error: function(error) {
                          // TODO: Handle errors. Currently can get all errors, just need to display them
                          if (error.status == 200) {
                              console.log(error);
                          } else {
                              console.log(error);
                              console.log(error.responseJSON.message);
                              var responseJson = error.responseJSON.errors;
                              if (responseJson) {
                                  $.each(responseJson, function() {
                                      console.log(this[0]);
                                  });
                              }
                          }
                      }
                    });
                }
            });
        }
    }

    initializeFileUpload();
    initializeFormProgression();
}