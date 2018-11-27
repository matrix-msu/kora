var Kora = Kora || {};
Kora.Fields = Kora.Fields || {};
Kora.Fields.TypedFieldInputs = Kora.Fields.TypedFieldInputs || {};

Kora.Fields.TypedFieldInputs.Initialize = function() {
    console.log('Field Inputs Initialized');

    function initializeGallery() {
        var $galleryFormGroup = $('.gallery-input-form-group');

        $galleryFormGroup.each(function() {
            var $cards = $(this).find('.file-card');

            // New option input doesn't push 'Add' button outside of input
            function sizeCard() {
                $cards.each(function() {
                    var $card = $(this);
                    var cardWidth = $card.outerWidth();
                    var $title = $card.find('.title');
                    var $delete = $card.find('.upload-filedelete-js');
                    var $moveAction = $card.find('.move-actions');

                    $title.css('max-width', cardWidth - $delete.width() - $moveAction.width() - 40);
                });
            }

            sizeCard();

            $(window).resize(function() {
                sizeCard();
            });
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

    function initializeComboSubLists(fnum) {
        $('.list-input-form-group-combo').each(function() {
            var $listFormGroup = $(this);
            var $cardOptionsContainer = $listFormGroup.find('.list-option-card-container-'+fnum+'-js');
            var $newOptionCard = $('.new-list-option-card-'+fnum+'-js');
            var $newOptionInput = $newOptionCard.find('.new-list-option-'+fnum+'-js');
            var $newOptionAddButton = $newOptionCard.find('.list-option-add-'+fnum+'-js');
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
    initializeComboSubLists('one');
    initializeComboSubLists('two');
};
