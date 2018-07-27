var Kora = Kora || {};
Kora.Forms = Kora.Forms || {};

Kora.Forms.Create = function() {

  $('.multi-select').chosen({
    width: '100%',
  });

  $('.single-select').chosen({
    width: '100%',
  });

  $('.preset-input-js').change(function() {
    if (this.checked) {
      $('.preset-select-container-js').animate({
        height: 75
      }, function() {
        $('.preset-select-js').fadeIn();
      });
    } else {
      $('.preset-select-js').fadeOut(function() {
        $('.preset-select-container-js').animate({
          height: 0
        });
      });
    }
  });

    function initializeValidation() {
        $('.validate-form-js').on('click', function(e) {
            var $this = $(this);

            e.preventDefault();

            values = {};
            $.each($('.create-form').serializeArray(), function(i, field) {
                values[field.name] = field.value;
            });

            $.ajax({
                url: validationUrl,
                method: 'POST',
                data: values,
                success: function(data) {
                    $('.create-form').submit();
                },
                error: function(err) {
                    $('.error-message').text('');
                    $('.text-input, .text-area').removeClass('error');

                    $.each(err.responseJSON, function(fieldName, errors) {
                        var $field = $('#'+fieldName);
                        $field.addClass('error');
                        $field.siblings('.error-message').text(errors[0]);
                    });
                }
            });
        });

        $('.text-input, .text-area').on('blur', function(e) {
            var field = this.id;
            var values = {};
            values[field] = this.value;
            values['_token'] = CSRFToken;

            $.ajax({
                url: validationUrl,
                method: 'POST',
                data: values,
                error: function(err) {
                    if (err.responseJSON[field] !== undefined) {
                        $('#'+field).addClass('error');
                        $('#'+field).siblings('.error-message').text(err.responseJSON[field][0]);
                    } else {
                        $('#'+field).removeClass('error');
                        $('#'+field).siblings('.error-message').text('');
                    }
                }
            });
        });
    }

  function multiSelectPlaceholders () {
	  var inputDef = $('.chosen-container').children('.chosen-choices');
    var childCheck = inputDef.siblings('.chosen-drop').children('.chosen-results');

	  inputDef.on('click', function() {
		  if (childCheck.children().length === 0) {
			  childCheck.append('<li class="no-results">No options to select!</li>');
		  } else if (childCheck.children('.active-result').length === 0 && childCheck.children('.no-results').length === 0) {
			  childCheck.append('<li class="no-results">No more options to select!</li>');
		  }
	  });
  }

    initializeValidation();
	multiSelectPlaceholders();
}
