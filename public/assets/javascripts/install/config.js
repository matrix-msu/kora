var Kora = Kora || {};
Kora.Install = Kora.Install || {};

Kora.Install.Config = function() {

    function initializeValidation() {
        $('.validate-config-js').on('click', function(e) {
            e.preventDefault();

            values = {};
            valid = true;
            $('.error-message').text('');
            $('.text-input').removeClass('error');

            $.each($('.config-form').serializeArray(), function(i, field) {
                values[field.name] = field.value;
                if(field.value == '') {
                    valid = false;
                    $("#"+field.name).addClass('error');
                    $("#"+field.name).siblings('.error-message').text(field.name+' is required');
                }
            });

            if(valid)
                $('.config-form').submit();
        });

        $('.text-input').on('blur', function(e) {
            value = $(this).val();
            attr = $(this).attr('name');

            if (value == "") {
                $(this).addClass('error');
                $(this).siblings('.error-message').text(attr+' is required');
            } else {
                $(this).removeClass('error');
                $(this).siblings('.error-message').text('');
            }
        });
    }

    initializeValidation();
}