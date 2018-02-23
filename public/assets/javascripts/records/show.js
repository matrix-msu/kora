var Kora = Kora || {};
Kora.Records = Kora.Records || {};

Kora.Records.Show = function() {

    // $('.single-select').chosen({
    //     width: '100%',
    // });
    //
    // $('.multi-select').chosen({
    //     width: '100%',
    // });

    function initializeToggle() {
        // Initialize card toggling
        $('.record-toggle-js').click(function(e) {
            e.preventDefault();

            var $this = $(this);
            var $header = $this.parent().parent();
            var $token = $header.parent();
            var $content = $header.next();

            $this.children('.icon').toggleClass('active');
            $token.toggleClass('active');
            if ($token.hasClass('active')) {
                $header.addClass('active');
                $token.animate({
                    height: $token.height() + $content.outerHeight(true) + 'px'
                }, 230);
                $content.effect('slide', {
                    direction: 'up',
                    mode: 'show',
                    duration: 240
                });
            } else {
                $token.animate({
                    height: '58px'
                }, 230, function() {
                    $header.hasClass('active') ? $header.removeClass('active') : null;
                    $content.hasClass('active') ? $content.removeClass('active') : null;
                });
                $content.effect('slide', {
                    direction: 'up',
                    mode: 'hide',
                    duration: 240
                });
            }

        });
    }

    function initializeDesignateRecordPreset() {
        Kora.Modal.initialize();

        $('.designate-preset-js').click(function (e) {
            e.preventDefault();

            var $modal = $('.designate-record-preset-modal-js');

            Kora.Modal.open($modal);
        });

        $('.create-record-preset-js').click(function (e) {
            e.preventDefault();

            var preset_name = $('.preset-name-js').val();

            if(preset_name.length > 3) {
                $.ajax({
                    url: makeRecordPresetURL,
                    type: 'POST',
                    data: {
                        "_token": csrfToken,
                        "name": preset_name,
                        "rid": ridForPreset
                    },
                    success: function () {
                        var presetLink = $('.designate-preset-js');

                        presetLink.text('Designated as Preset');
                        presetLink.addClass('already-preset-js');
                        presetLink.removeClass('designate-preset-js');

                        Kora.Modal.close($modal);
                    }
                });
            } else {
                //TODO::error
            }
        });
    }

    function initializeAlreadyRecordPreset() {
        $('.already-preset-js').click(function (e) {
            e.preventDefault();

            var $modal = $('.already-record-preset-modal-js');

            Kora.Modal.open($modal);
        });

        $('.gotchya-js').click(function (e) {
            e.preventDefault();

            var $modal = $('.already-record-preset-modal-js');

            Kora.Modal.close($modal);
        });
    }

    function initializeDeleteRecord() {
        $('.delete-record-js').click(function (e) {
            e.preventDefault();

            var $modal = $('.delete-record-modal-js');

            Kora.Modal.open($modal);
        });
    }

    initializeToggle();
    initializeDesignateRecordPreset();
    initializeAlreadyRecordPreset();
    initializeDeleteRecord();
}