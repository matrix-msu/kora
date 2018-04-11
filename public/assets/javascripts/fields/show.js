var Kora = Kora || {};
Kora.Fields = Kora.Fields || {};

Kora.Fields.Show = function() {

    function initializeCleanUpModals() {
        Kora.Modal.initialize();

        $('.field-trash-js').click(function(e) {
            e.preventDefault();

            var cleanupModal = $('.field-cleanup-modal-js');

            cleanupModal.find('.title-js').html(
                $(this).data('title')
            );

            cleanupModal.find('.delete-content-js').show();
            Kora.Modal.open(cleanupModal);
        });
    }

    function initializeFieldValuePresets() {
        Kora.Modal.initialize();

        //REGEX
        var regexModal = $('.add-regex-preset-modal-js');
        var createRegexModal = $('.create-regex-preset-modal-js');
        var newRegex = '';

        $('.open-regex-modal-js').click(function(e) {
            e.preventDefault();

            Kora.Modal.open(regexModal);
        });

        $('.add-regex-preset-js').click(function(e) {
            e.preventDefault();

            var regexVal = $('[name="regex_preset"]').val();
            $('[name="regex"]').val(regexVal);

            Kora.Modal.close(regexModal);
        });

        $('.open-create-regex-modal-js').click(function(e) {
            e.preventDefault();

            newRegex = $('[name="regex"]').val();

            if(newRegex!='')
                Kora.Modal.open(createRegexModal);
            //else
                //TODO::error out
        });

        $('.create-regex-preset-js').click(function(e) {
            e.preventDefault();

            var name = $('[name="preset_title"]').val();
            var type = 'Text';
            var preset = newRegex;
            var shared = $('[name="preset_shared"]').is(':checked');

            $.ajax({
                url: createFieldValuePresetURL,
                type: 'POST',
                data: {
                    "_token": CSRFToken,
                    "name": name,
                    "type": type,
                    "preset": preset,
                    "shared": shared
                },
                success: function (result) {
                    Kora.Modal.close(createRegexModal);
                }
            });
        });

        //LIST OPTIONS
        var listModal = $('.add-list-preset-modal-js');
        var createListModal = $('.create-list-preset-modal-js');
        var newList = [];

        $('.open-list-modal-js').click(function(e) {
            e.preventDefault();

            Kora.Modal.open(listModal);
        });

        $('.add-list-preset-js').click(function(e) {
            e.preventDefault();

            var listVal = $('[name="list_preset"]').val();
            listValArray = listVal.split('[!]');

            //clear old values
            var optDiv = $('[name="options\[\]"]');
            var defDiv = $('[name="default"]');
            optDiv.html('');
            defDiv.html('');

            //Loop through results to
            for(var i = 0; i < listValArray.length; i++) {
                var option = $("<option>").val(listValArray[i]).text(listValArray[i]);

                defDiv.append(option.clone());
                optDiv.append(option.clone());
            }

            //refresh chosen
            defDiv.prepend("<option value='' selected='selected'></option>");
            optDiv.find($('option')).prop('selected', true);
            optDiv.trigger("chosen:updated");
            defDiv.trigger("chosen:updated");

            Kora.Modal.close(listModal);
        });

        $('.open-create-list-modal-js').click(function(e) {
            e.preventDefault();

            newList = $('[name="options[]"]').val();

            if(newList!=[])
                Kora.Modal.open(createListModal);
            //else
            //TODO::error out
        });

        $('.create-list-preset-js').click(function(e) {
            e.preventDefault();

            var name = $('[name="preset_title"]').val();
            var type = 'List';
            var preset = newList;
            var shared = $('[name="preset_shared"]').is(':checked');

            $.ajax({
                url: createFieldValuePresetURL,
                type: 'POST',
                data: {
                    "_token": CSRFToken,
                    "name": name,
                    "type": type,
                    "preset": preset,
                    "shared": shared
                },
                success: function (result) {
                    Kora.Modal.close(createListModal);
                }
            });
        });
    }

    initializeCleanUpModals();
    initializeFieldValuePresets();
}