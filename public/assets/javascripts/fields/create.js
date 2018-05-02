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

    function initializeValidation() {
        $('.validate-field-js').on('click', function(e) {
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
            values['_token'] = csrfToken;

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

    initializeAdvancedOptions();
    initializeComboListFields();
    initializeValidation();
}