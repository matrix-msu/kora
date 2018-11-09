var Kora = Kora || {};
Kora.Fields = Kora.Fields || {};
Kora.Fields.TypedFieldInputs = Kora.Fields.TypedFieldInputs || {};

Kora.Fields.TypedFieldInputs.Initialize = function() {
    console.log('Field Inputs Initialized')

    function initializeGallery() {
        $('.gallery-field-display-js').each(function() {

        });
    }

    function initializeGeolocator() {
        $('.geolocator-map-js').each(function() {

        });
    }

    function intializeAudio() {
        $('.audio-field-display').each(function() {

        });
    }

    function initalizeSchedule() {
        $('.schedule-cal-js').each(function() {

        });
    }

    function initializeVideo() {
        // Event listener for the full-screen button
        $('.video-field-display-js').each(function() {

        });
    }

    function initalize3DModel() {
        $('.model-player-div-js').each(function() {

        });
    }

    function initializeList() {
        $('.list-input-form-group').each(function() {
            var $listFormGroup = $(this);
            var $cardOptionsContainer = $listFormGroup.find('.list-option-card-container-js');
            var $newOptionCard = $('.new-list-option-card-js');
            var $newOptionInput = $newOptionCard.find('.new-list-option-js');
            var $newOptionAddButton = $newOptionCard.find('.list-option-add-js');
            var addButtonWidth = $newOptionAddButton.outerWidth() + 40;

            // Drag cards into position
            function initializeListOptionDrag() {
                $cardOptionsContainer.sortable();
            }

            // New option input doesn't push 'Add' button outside of input
            function sizeNewOptionInput() {
                var newOptionCardWidth = $newOptionCard.outerWidth();
                $newOptionInput.css('max-width', newOptionCardWidth - addButtonWidth);
            }

            initializeListOptionDrag();
            sizeNewOptionInput();

            $(window).resize(function() {
              sizeNewOptionInput();
            });
        });
    }

    initializeGallery();
    initializeGeolocator();
    intializeAudio();
    initializeVideo();
    initalizeSchedule();
    initalize3DModel();
    initializeList();
};
