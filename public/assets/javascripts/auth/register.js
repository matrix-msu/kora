var Kora = Kora || {};
Kora.Auth = Kora.Auth || {};

Kora.Auth.Register = function() {
    var form = $(".form-file-input");
    var fileInput = $(".profile-input");
    var button = $(".profile-label");
    var picCont = $(".profile-label .icon-user-cont");
    var filename = $(".filename");
    var instruction = $(".instruction");
    var droppedFile = false;
    var droppedPicFile = false;

    function initializeChosen() {
      $(".chosen-select").chosen({
        disable_search_threshold: 10,
        width: '100%'
      });
    }

    // Remove selected profile pic
    function resetFileInput() {
        fileInput.replaceWith(fileInput.val('').clone(true));
        instruction.removeClass("photo-selected");
        picCont.html("<i class='icon icon-user'></i>");
        droppedFile = false;
        filename.html("Add a photo to help others identify you");
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

    function initializeValidation() {
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

        // Check for Drag and Drop Support on the browser
        var isAdvancedUpload = function() {
        var div = document.createElement('div');
        return (('draggable' in div) || ('ondragstart' in div && 'ondrop' in div)) && 'FormData' in window && 'FileReader' in window;
        }();

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
                droppedPicFile = droppedFile;
            });
        }
      
        //form.submit(function(e) {
		$('.validate-user-js').click(function (e) {
            var $this = $(this);

            e.preventDefault();

            var $this = $(this);

            if (!droppedPicFile) {
                values = {};
                $.each($('.user-form').serializeArray(), function(i, field) {
                    values[field.name] = field.value;
                });

                // console.log(values)
                // for ( var pair of values.entries() ) {
                //     console.log(pair[0] + ', ' + pair[1]);
                //     //console.log(typeof pair[1]);
                //     if (typeof pair[1] === 'object') {
                //         console.log(pair[1]);
                //     }
                // }

				$.ajax({
					url: validationUrl,
					method: 'POST',
					data: values,
					success: function(data) {
						display_loader();
						$('.user-form').submit();
					},
					error: function(err) {
						console.log(err);
						console.log(err.message);
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
                values = new FormData(form.get(0));
                values.delete('profile');
                values.append('profile', droppedPicFile);

				$.ajax({
					url: validationUrl,
					method: 'POST',
					data: values,
					processData: false,
					contentType: false,
					success: function(data) {
						display_loader();
						$('.user-form').submit();
					},
					error: function(err) {
						console.log(err);
						console.log(err.message);
						$('.error-message').text('');
						$('.text-input').removeClass('error');

						$.each(err.responseJSON.errors, function(fieldName, errors) {
							var $field = $('#'+fieldName);
							$field.addClass('error');
							$field.siblings('.error-message').text(errors[0]);
						});
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

            $.ajax({
                url: validationUrl,
                method: 'POST',
                data: values,
                error: function(err) {
                    console.log(err.responseJSON.errors[field])
                    if (err.responseJSON.errors[field] !== undefined) {
                        $('#'+field).addClass('error');
                        $('#'+field).siblings('.error-message').text(err.responseJSON.errors[field][0]);
                    } else {
                        $('#'+field).removeClass('error');
                        $('#'+field).siblings('.error-message').text('');
                    }

                    if(second) {
                        if (err.responseJSON[field2] !== undefined) {
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
  
  initializeChosen();
  initializeValidation();
}