var Kora = Kora || {};
Kora.Projects = Kora.Projects || {};

Kora.Projects.Edit = function() {

  function initializeCleanUpModals() {
    Kora.Modal.initialize();

    $('.project-trash-js').click(function(e) {
      e.preventDefault();

      var $cleanupModal = $('.project-cleanup-modal-js');

      $cleanupModal.find('.title-js').html(
        $(this).data('title')
      );

      $cleanupModal.find('.delete-content-js').show();
      $cleanupModal.find('.archive-content-js').hide();
      Kora.Modal.open();
    });

    $('.project-archive-js').click(function(e) {
      e.preventDefault();

      var $cleanupModal = $('.project-cleanup-modal-js');

      $cleanupModal.find('.title-js').html(
        $(this).data('title')
      );

      $cleanupModal.find('.archive-content-js').show();
      $cleanupModal.find('.delete-content-js').hide();
      Kora.Modal.open();
    });
  }

  function scrollTop (allScrolls) {
    var scrollTo = Math.min(...allScrolls);
    var scrollTo = scrollTo - 100;
    setTimeout( function () {
      $('html, body').animate({
        scrollTop: 0
      }, 2500);
    });
  }

  function initializeValidation() {
    $('.validate-project-js').on('click', function(e) {
      var $this = $(this);

      e.preventDefault();

      values = {};
      $.each($('.edit-form').serializeArray(), function(i, field) {
        values[field.name] = field.value;
      });
      values['_method'] = 'PATCH';

      $.ajax({
        url: validationUrl,
        method: 'POST',
        data: values,
        success: function(data) {
          $('.edit-form').submit();
        },
        error: function(err) {
          $('.error-message').text('');
          $('.text-input, .text-area').removeClass('error');
          var allScrolls = [];

          $.each(err.responseJSON.errors, function(fieldName, errors) {
            var $field = $('#'+fieldName);
            $field.addClass('error');
            $field.siblings('.error-message').text(errors[0]);
            allScrolls.push($field.offset().top);
          });

          scrollTop(allScrolls);
        }
      });
    });

    $('.text-input, .text-area').on('blur', function(e) {
      var field = this.id;
      var values = {};
      values[field] = this.value;
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
        }
      });
    });
  }

  initializeCleanUpModals();
  initializeValidation();
}
