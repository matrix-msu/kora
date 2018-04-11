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

    function initializeRegexPresetModals() {
        Kora.Modal.initialize();

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
                success: function(result) {
                    Kora.Modal.close(createRegexModal);
                }
            });
        });
    }

    function initializeListPresetModals() {
        Kora.Modal.initialize();

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
                success: function(result) {
                    Kora.Modal.close(createListModal);
                }
            });
        });
    }

    function initializeLocationPresetModals() {
        Kora.Modal.initialize();

        var locationModal = $('.add-location-preset-modal-js');
        var createLocationModal = $('.create-location-preset-modal-js');
        var newLocation = [];

        $('.open-location-modal-js').click(function(e) {
            e.preventDefault();

            Kora.Modal.open(locationModal);
        });

        $('.add-location-preset-js').click(function(e) {
            e.preventDefault();

            var locationVal = $('[name="location_preset"]').val();
            locationValArray = locationVal.split('[!]');

            //clear old values
            var optDiv = $('[name="default\[\]"]');
            optDiv.html('');

            //Loop through results to
            for(var i = 0; i < locationValArray.length; i++) {
                var option = $("<option>").val(locationValArray[i]).text(locationValArray[i]);

                optDiv.append(option.clone());
            }

            //refresh chosen
            optDiv.find($('option')).prop('selected', true);
            optDiv.trigger("chosen:updated");

            Kora.Modal.close(locationModal);
        });

        $('.open-create-location-modal-js').click(function(e) {
            e.preventDefault();

            newLocation = $('[name="default[]"]').val();

            if(newLocation!=[])
                Kora.Modal.open(createLocationModal);
            //else
            //TODO::error out
        });

        $('.create-location-preset-js').click(function(e) {
            e.preventDefault();

            var name = $('[name="preset_title"]').val();
            var type = 'Geolocator';
            var preset = newLocation;
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
                success: function(result) {
                    Kora.Modal.close(createLocationModal);
                }
            });
        });
    }

    function initializeEventPresetModals() {
        Kora.Modal.initialize();

        var eventModal = $('.add-event-preset-modal-js');
        var createEventModal = $('.create-event-preset-modal-js');
        var newEvent = [];

        $('.open-event-modal-js').click(function(e) {
            e.preventDefault();

            Kora.Modal.open(eventModal);
        });

        $('.add-event-preset-js').click(function(e) {
            e.preventDefault();

            var eventVal = $('[name="event_preset"]').val();
            eventValArray = eventVal.split('[!]');

            //clear old values
            var optDiv = $('[name="default\[\]"]');
            optDiv.html('');

            //Loop through results to
            for(var i = 0; i < eventValArray.length; i++) {
                var option = $("<option>").val(eventValArray[i]).text(eventValArray[i]);

                optDiv.append(option.clone());
            }

            //refresh chosen
            optDiv.find($('option')).prop('selected', true);
            optDiv.trigger("chosen:updated");

            Kora.Modal.close(eventModal);
        });

        $('.open-create-event-modal-js').click(function(e) {
            e.preventDefault();

            newEvent = $('[name="default[]"]').val();

            if(newEvent!=[])
                Kora.Modal.open(createEventModal);
            //else
            //TODO::error out
        });

        $('.create-event-preset-js').click(function(e) {
            e.preventDefault();

            var name = $('[name="preset_title"]').val();
            var type = 'Schedule';
            var preset = newEvent;
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
                success: function(result) {
                    Kora.Modal.close(createEventModal);
                }
            });
        });
    }

    function initializeComboPresetModals() {
        //TODO::Allow application of presets for individual field types in a combo list
    }

    initializeCleanUpModals();
    initializeRegexPresetModals();
    initializeListPresetModals();
    initializeLocationPresetModals();
    initializeEventPresetModals();
}