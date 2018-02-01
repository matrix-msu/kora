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
        $('#advance_options_div').on('click', '#adv_opt', function () {
            //opens advanced options page for selected type
            $.ajax({
                url: "{{ action('FieldAjaxController@getAdvancedOptionsPage',['pid' => $pid,'fid'=>$fid]) }}",
                type: 'GET',
                data: {
                    "_token": "{{ csrf_token() }}",
                    type: $(".field_types").val()
                },
                success: function (result) {
                    $('#advance_options_div').html(result);
                }
            });

            //set adv to true
            adv = true;
        });

        $('#field_types_div').on('focus', '.field_types', function () {
            // Store the current value on focus and on change
            previous = $(this).val();
        }).on('change', '.field_types', function () {
            if ($(this).val() == 'Combo List' | $(this).val() == 'Associator') {
                $('#adv_opt').attr('disabled', 'disabled');
            } else {
                $('#adv_opt').removeAttr('disabled');
            }


            //if adv is true
            if (adv) {
                //dialog warning
                var encode = $('<div/>').html("{{ trans('fields_form.confirmchange') }}").text();
                if (!confirm(encode)) {
                    $('.field_types').val(previous);
                    return false;
                }
                //close advanced options page
                button = '<div class="form-group">';
                button += '<button type="button" id="adv_opt" class="btn form-control">Adv Stuff</button>';
                button += '<div>';
                $('#advance_options_div').html(button);
                //set adv to false
                adv = false;
            }
        });
    }

    initializeComboListFields();
}