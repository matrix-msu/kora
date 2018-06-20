var Kora = Kora || {};
Kora.Auth = Kora.Auth || {};

Kora.Auth.Register = function() {
  function initializeChosen() {
    $(".chosen-select").chosen({
      disable_search_threshold: 10,
      width: '100%'
    });
  }
  
  function initializeForm() {
    Kora.Inputs.File();
  }

  function initializeValidation() {
      $('.validate-user-js').on('click', function(e) {
          var $this = $(this);

          e.preventDefault();

          values = {};
          $.each($('.user-form').serializeArray(), function(i, field) {
              values[field.name] = field.value;
          });

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
  initializeForm();
  initializeValidation();
}