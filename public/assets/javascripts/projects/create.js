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
      $.each($('form').serializeArray(), function(i, field) {
        values[field.name] = field.value;
      });

      $.ajax({
        url: validationUrl,
        method: 'POST',
        data: values,
        success: function(data) {
          $('form').submit();
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
  }

  initializeValidation();
}
