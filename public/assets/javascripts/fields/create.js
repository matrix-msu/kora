var Kora = Kora || {};
Kora.Fields = Kora.Fields || {};

Kora.Fields.Create = function() {

    $('.single-select').chosen({
        width: '100%',
    });

    //Global variable for whether advanced field creation is active
    var advCreation = false;

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
        function openAdvancedOptions() {
            //opens advanced options page for selected type
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

        function closeAdvancedOptions() {
            $('.advance-options-section-js').html('');
            advCreation = false;
            $('.advanced-options-show').removeClass('hidden');
            $('.advanced-options-hide').addClass('hidden');
        }

        $('.advanced-options-btn-js').click(function(e) {
            e.preventDefault();

            if(!advCreation)
                openAdvancedOptions();
            else
                closeAdvancedOptions();
        });

        $('.field-types-js').on('focus', function(e) {
            // Store the current value on focus and on change
            previous = $(this).val();
        }).on('change', function(e) {
            if($(this).val() == 'Combo List' | $(this).val() == 'Associator')
                $('.advanced-options-btn-js').addClass('disabled');
            else
                $('.advanced-options-btn-js').removeClass('disabled');

            //if adv is true
            if(advCreation) {
                //dialog warning
                var encode = $('<div/>').html("Are you sure?").text();
                if (!confirm(encode)) {
                    $('.field_types').val(previous);
                    return false;
                }
                //close advanced options page
                closeAdvancedOptions();
            }
        });
    }

    initializeAdvancedOptions();
    initializeComboListFields();
}