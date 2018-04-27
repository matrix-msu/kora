var Kora = Kora || {};
Kora.Projects = Kora.Projects || {};

Kora.Projects.Create = function() {

  $('.multi-select').chosen({
    width: '100%',
  });

  function initializeValidation() {
    $('form input.btn').on('click', function(e) {
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

  initializeValidation();
}
