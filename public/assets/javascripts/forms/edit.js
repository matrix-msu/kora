var Kora = Kora || {};
Kora.Forms = Kora.Forms || {};

Kora.Forms.Edit = function() {

  function initializeCleanUpModals() {
        Kora.Modal.initialize();

        $('.form-trash-js').click(function(e) {
            e.preventDefault();

            var $cleanupModal = $('.form-cleanup-modal-js');

            $cleanupModal.find('.title-js').html( $(this).data('title') );

            Kora.Modal.open($cleanupModal);
        });
		
		$('.delete-form-js').click(function(e) {
			display_loader();
		});

        $('.delete-records-js').click(function(e) {
            e.preventDefault();

            var $cleanupModal = $('.delete-records-modal-js');

            Kora.Modal.open($cleanupModal);
        });

        $('.delete-files-js').click(function(e) {
			e.preventDefault();

			var $cleanupModal = $('.delete-files-modal-js');

			Kora.Modal.open($cleanupModal);
        });
		
		$('.create-test-records-btn-js, .delete-test-records-btn-js').click(function(e) {
			console.log("dislaying loader");
			display_loader();
        });
  }

  function scrollTop (allScrolls) {
    var scrollTo = Math.min(...allScrolls);
    var scrollTo = scrollTo - 100;
    setTimeout( function () {
      $('html, body').animate({
        scrollTop: scrollTo
      }, 500);
    });
  }

    function initializeValidation() {
        $('.validate-form-js').on('click', function(e) {
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
            values['_method'] = 'patch';

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
