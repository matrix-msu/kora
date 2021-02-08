var Kora = Kora || {};
Kora.Fields = Kora.Fields || {};
Kora.Fields.TypedFieldInputs = Kora.Fields.TypedFieldInputs || {};

Kora.Fields.TypedFieldInputs.Initialize = function() {
    console.log('Field Inputs Initialized');

    function sizeCardTitles($cards, extra = 0) {
        $cards.each(function() {
            var $card = $(this);
            var cardWidth = $card.outerWidth();
            var $header = $card.find('.header');
            var $title = $card.find('.title');
            var $delete = $card.find('.icon-trash').parent();
            var $moveAction = $card.find('.move-actions');

            $title.css('max-width', cardWidth - $delete.width() - $moveAction.width() - 40 - extra);
        });
    }

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
        $('.geolocator-form-group-js').each(function() {
            var $formGroup = $(this);
            var $cards = $formGroup.find('.geolocator-card-js');

            sizeCardTitles($cards, 40);

            $(window).resize(function() {
                sizeCardTitles($cards, 40);
            });
        });
    }

    function intializeAudio() {
        $('.audio-field-display').each(function() {

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

    function initializeComboList() {
        $('.clist-input-form-group').each(function() {
            var $listFormGroup = $(this);
            var $cardOptionsContainer = $listFormGroup.find('.combo-value-item-container-js');

            // Drag cards into position
            function initializeListOptionDrag() {
                $cardOptionsContainer.sortable();
            }

            initializeListOptionDrag();
        });
    }

    initializeGallery();
    initializeGeolocator();
    intializeAudio();
    initializeVideo();
    initalize3DModel();
    initializeList();
    initializeComboList();
};
