var Kora = Kora || {};
Kora.Fields = Kora.Fields || {};

Kora.Fields.Create = function() {

    $('.single-select').chosen({
        width: '100%',
    });

    //Global variable for whether advanced field creation is active
    var advCreation = false;
    var previousType = '';
    var currentType = '';

    function initializeComboListFields() {
        //In the case of returning to page for errors, show the CLF if applicable
        $(document).ready(function () {
            if ($('.field-types-js').val() == 'Combo List')
                $('.combo-list-form-js').removeClass('hidden');
        });
        //The one that matters during execution
        $('.field-types-js').change(function() {
            if ($('.field-types-js').val() == 'Combo List')
                $('.combo-list-form-js').show();
            else
                $('.combo-list-form-js').hide();
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

    function initializeAdvancedOptions() {
        Kora.Modal.initialize();

        //opens advanced options page for selected type
        function openAdvancedOptions() {
            $.ajax({
                url: advanceCreateURL,
                type: 'POST',
                data: {
                    "_token": csrfToken,
                    type: $(".field-types-js").val()
                },
                success: function (result) {
                    $('.advance-options-section-js').html(result);

                    advCreation = true;
                    $('.advanced-options-show').addClass('hidden');
                    $('.advanced-options-hide').removeClass('hidden');
					
					$('.number-default-js, .number-min-js, .number-max-js').blur(function(){
						console.log("BLURRED");
						validateAdvancedOptions('Number');
					});

                    Kora.Fields.TypedFieldInputs.Initialize();
                }
            });
        }

        //Closes the advanced options page and resets btn
        function closeAdvancedOptions() {
            $('.advance-options-section-js').html('');
            advCreation = false;
            $('.advanced-options-show').removeClass('hidden');
            $('.advanced-options-hide').addClass('hidden');
        }

        //Sets the field type and checks if new type should allow advanced
        function setFieldType() {
            $('.field-types-js').val(currentType);
            $('.field-types-js').trigger('chosen:updated');
            if(currentType == 'Combo List') {
                $('.advanced-options-btn-js').addClass('disabled');
                $('.combo-list-form-js').show();
            } else {
                $('.advanced-options-btn-js').removeClass('disabled');
                $('.combo-list-form-js').hide();
            }
        }

        //Handles the click of the advanced creation btn
        $('.advanced-options-btn-js').click(function(e) {
            e.preventDefault();

            if(!advCreation)
                openAdvancedOptions();
            else
                closeAdvancedOptions();
        });

        //Handles modal submission of advanced options change
        $('.change-field-type-js').click(function(e) {
            setFieldType();
            closeAdvancedOptions();
            Kora.Modal.close($('.change-advanced-field-modal-js'));
        });

        //Special chosen js method for capturing the focus event
        $('.field-types-js').on('chosen:showing_dropdown', function () {
            // Store the current value on focus and on change
            previousType = $(this).val();
        }).on('change', function(e) {
            currentType = $(this).val();

            //if adv is true
            if(advCreation) {
                //Change back to previous value until change is confirmed by user
                $(this).val(previousType);
                $(this).trigger('chosen:updated');
                Kora.Modal.open($('.change-advanced-field-modal-js'));
            } else {
                //User input not needed since advanced options is not open
                setFieldType();
            }
        });
    }

    function initializeDescriptionModal() {
      Kora.Modal.initialize();

      $('.desc-modal').click(function(e) {
        e.preventDefault();

        Kora.Modal.open($('.field-type-description-modal-js'));
      });
    }

    function initializeValidation() {
        $('.validate-field-js').on('click', function(e) {
            var $this = $(this);

            e.preventDefault();

            values = {};
            $.each($('.create-form').serializeArray(), function(i, field) {
                values[field.name] = field.value;
            });
			
			console.log("entered");

            $.ajax({
                url: validationUrl,
                method: 'POST',
                data: values,
                success: function(data) {
                    var advValid = true;
                    if(advCreation)
                        advValid = validateAdvancedOptions($('.field-types-js').val());
                    if(advValid)
                        $('.create-form').submit();
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
            values['_token'] = csrfToken;

            //For combo list
            if(field=='cfname1' || field=='cfname2')
                values['type'] = $('#type').val();
			
			console.log("entered 2");

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
	
	function validateAdvancedOptions(currType) {
        var valid = true;

        switch(currType) {
            case 'Text':
                var regexDiv = $('.text-regex-js');
                var defDiv = $('.text-default-js');

                var regex = regexDiv.val();
                var def = defDiv.val();

                if(regex!='' && def!='') {
                    regex = new RegExp(regex);
                    var match = regex.test(def);

                    if(!match) {
                        defDiv.addClass('error');
                        defDiv.siblings('.error-message').text("Default value must match the regular expression pattern.");
                        valid = false;
                    } else {
                        defDiv.removeClass('error');
                        defDiv.siblings('.error-message').text('');
                    }
                } else {
                    defDiv.removeClass('error');
                    defDiv.siblings('.error-message').text('');
                }
                break;
            case 'Number':
                var defDiv = $('.number-default-js');
                var minDiv = $('.number-min-js');
                var maxDiv = $('.number-max-js');
                var stepDiv = $('.number-step-js');

                var def = parseInt(defDiv.val());
                var min = parseInt(minDiv.val());
                var max = parseInt(maxDiv.val());
                var step = parseInt(stepDiv.val());

                if(min!='' && max!='') {
                    if(min >= max) {
                        minDiv.addClass('error');
                        minDiv.parent().siblings('.error-message').text('The minimum must be less than the max.');
                        valid = false;
                    } else {
                        minDiv.removeClass('error');
                        minDiv.parent().siblings('.error-message').text('');
                    }

                    if(step > (max-min)) {
                        stepDiv.addClass('error');
                        stepDiv.parent().siblings('.error-message').text('The increment cannot be bigger than the gap between min and max.');
                        valid = false;
                    } else {
                        stepDiv.removeClass('error');
                        stepDiv.parent().siblings('.error-message').text('');
                    }
                } else {
                    minDiv.removeClass('error');
                    minDiv.parent().siblings('.error-message').text('');
                    stepDiv.removeClass('error');
                    stepDiv.parent().siblings('.error-message').text('');
                }

                if(def!='') {
                    if(min!='' && def<min) {
                        defDiv.addClass('error');
                        defDiv.parent().siblings('.error-message').text('Default value must be greater than the minimum.');
                        valid = false;
                    } else if(max!='' && def>max) {
                        defDiv.addClass('error');
                        defDiv.parent().siblings('.error-message').text('Default value must be smaller than the maximum.');
                        valid = false;
                    } else {
                        defDiv.removeClass('error');
                        defDiv.parent().siblings('.error-message').text('');
                    }
                } else {
                    defDiv.removeClass('error');
                    defDiv.parent().siblings('.error-message').text('');
                }
                break;
            case 'Date':
            case 'Schedule':
                var startDiv = $('.start-year-js');
                var endDiv = $('.end-year-js');

                var start = startDiv.val();
                var end = endDiv.val();

                if(start=='') {
                    startDiv.addClass('error');
                    startDiv.siblings('.error-message').text('A start year is required.');
                    valid = false;
                } else if(start<0 || start>9999) {
                    startDiv.addClass('error');
                    startDiv.siblings('.error-message').text('A year must be between 0 and 9999.');
                    valid = false;
                } else {
                    startDiv.removeClass('error');
                    startDiv.siblings('.error-message').text('');
                }

                if(end=='') {
                    endDiv.addClass('error');
                    endDiv.siblings('.error-message').text('An end year is required.');
                    valid = false;
                } else if(end<0 || end>9999) {
                    endDiv.addClass('error');
                    endDiv.siblings('.error-message').text('A year must be between 0 and 9999.');
                    valid = false;
                } else {
                    endDiv.removeClass('error');
                    endDiv.siblings('.error-message').text('');
                }

                if(valid) {
                    if(start > end) {
                        startDiv.addClass('error');
                        startDiv.siblings('.error-message').text('The start year must be less than the end year.');
                        valid = false;
                    } else {
                        startDiv.removeClass('error');
                        startDiv.siblings('.error-message').text('');
                    }
                }
                break;
            default:
                break;
        }

        return valid;
    }

    initializeDescriptionModal();
    initializeAdvancedOptions();
    initializeComboListFields();
    initializeValidation();
    Kora.Inputs.Number();
}
