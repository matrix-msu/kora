var Kora = Kora || {};
Kora.Records = Kora.Records || {};

Kora.Records.Validate = function() {
  var errorList = [];
  
    function initializeValidationModal() {
        Kora.Modal.initialize();

        var uniquePages = Array.from(new Set(errorList));

        $('ul.error-pages li').remove();
        $('.error-count-js').text(errorList.length);

        uniquePages.forEach(function(page){
          page = page.substring(1);
          $('ul.error-pages').append('<li>' + page + '</li>');
        });

        Kora.Modal.open($('.record-validation-modal-js'));
    }

    function initializeRecordValidation() {
        $('.record-validate-js').click(function(e) {
            var $this = $(this);

            e.preventDefault();

            values = {};
            //We need to make sure all CKEDITOR data is actually in the form to validate it
            for(var instanceName in CKEDITOR.instances){
                CKEDITOR.instances[instanceName].updateElement();
            }
            $.each($('.record-form').serializeArray(), function(i, field) {
                if(field.name in values)
                    if(Array.isArray(values[field.name]))
                        values[field.name].push(field.value);
                    else
                        values[field.name] = [values[field.name], field.value];
                else
                    values[field.name] = field.value;
            });
            values['_method'] = 'POST';

            $.ajax({
                url: validationUrl,
                method: 'POST',
                data: values,
                success: function(err) {
                    $('.error-message').text('');
                    $('.text-input, .text-area, .cke, .chosen-container').removeClass('error');

                    if(err.errors.length==0) {
                        $('.record-form').submit();
                    } else {
                        $.each(err.errors, function(fieldName, error) {
                            var $field = $('#'+fieldName);
                            var $page = $field.parents('section').attr('id');

                            $field.addClass('error');
                            $field.siblings('.error-message').text(error);

                            if ($page === undefined) {
                              $page = $('[name="' + fieldName + '"]').parents('section').attr('id');
                              errorList.push($page);
                            } else {
                              errorList.push($page); 
                            }
                        });
                    initializeValidationModal();
                    }
                }
            });
        });
    }

    initializeRecordValidation();
    Kora.Records.Modal();
}