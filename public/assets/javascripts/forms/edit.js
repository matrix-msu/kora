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
			display_loader();
        });

		$('.create-test-js').click(function(e) {
			e.preventDefault();

            var $cleanupModal = $('.create-test-records-js');

            $numberInputs = $('input[name="test_records_num"]');
            $('.num-arrows-js').remove();

            // Add on arrows to number inputs
            $numberInputs.after('<div class="num-arrows num-arrows-js"><div class="arrow arrow-js arrow-up arrow-up-js"><i class="icon icon-chevron"></i></div><div class="spacer"></div><div class="arrow arrow-js arrow-down arrow-down-js"><i class="icon icon-chevron"></i></div></div>');

            $numberInputs.each(function() {
              var $input = $(this);
              var val = ($input.val() && $.isNumeric($input.val()) ? parseFloat($input.val()) : 0);
              var step = ($input.attr('step') && $.isNumeric($input.attr('step')) ? parseFloat($input.attr('step')) : 1);

              // Set decimal places for val
              $input.val(val.toFixed(getDecimalPlaces(step)));

              updateArrows($input);
            });

			Kora.Modal.open($cleanupModal);
        });

        $('.delete-test-js').click(function(e) {
			e.preventDefault();

			var $cleanupModal = $('.delete-test-records-js');

			Kora.Modal.open($cleanupModal);
        });
  }

  function updateArrows($input) {
    var $arrowsContainer = $input.siblings('.num-arrows');
    var $arrows = $arrowsContainer.find('.arrow-js');

    var num = ($input.val() && $.isNumeric($input.val()) ? parseFloat($input.val()) : 0);
    var min = ($input.attr('min') ? parseInt($input.attr('min')) : 'unlimited');
    var max = ($input.attr('max') ? parseInt($input.attr('max')) : 'unlimited');
    var step = ($input.attr('step') && $.isNumeric($input.attr('step')) ? parseFloat($input.attr('step')) : 1);
    var decimalPlaces = getDecimalPlaces(step);

    $arrows.click(function() {
      var $arrow = $(this);

      if ($arrow.hasClass('arrow-up-js')) {
        num = num + step;
        if (max != 'unlimited' && num > max) {
          num = max;
        }
      } else if ($arrow.hasClass('arrow-down-js')) {
        num = num - step;
        if (min != 'unlimited' && num < min) {
          num = min;
        }
      }

      $input.val(num.toFixed(decimalPlaces));
    });
  }

  function getDecimalPlaces(num) {
    var numStr = num.toString();
    var decIndex = numStr.indexOf('.') + 1;

    return !decIndex ? 0 : numStr.length - decIndex;
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
