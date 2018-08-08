var Kora = Kora || {};
Kora.RecordPresets = Kora.RecordPresets || {};

Kora.RecordPresets.Index = function() {

    function initializeToggle() {
        // Initialize card toggling
        $('.preset-toggle-js').click(function(e) {
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

    function initializeChangePresetName() {
        Kora.Modal.initialize();

        var presetid = 0;

        $('.change-preset-js').click(function (e) {
            e.preventDefault();

            presetid = $(this).attr('presetid');
            var $modal = $('.change-preset-name-modal-js');

            Kora.Modal.open($modal);
        });

        $('.change-preset-name-js').click(function(e) {
            e.preventDefault();

            var name = $('.preset-name-js').val();

            $.ajax({
                url: changePresetNameUrl,
                type: 'POST',
                data: {
                    '_token': csrfToken,
                    "_method": 'patch',
                    'id': presetid,
                    'name': name
                },
                success: function () {
                    $('.change-name-'+presetid+'-js').text(name);

                    Kora.Modal.close($modal);
                }
            });
        });
    }

    function initializeDeleteRecordPreset() {
        Kora.Modal.initialize();

        var presetid = 0;

        $('.delete-preset-js').click(function (e) {
            e.preventDefault();

            presetid = $(this).attr('presetid');
            var $modal = $('.delete-record-preset-modal-js');

            Kora.Modal.open($modal);
        });

        $('.delete-record-preset-js').click(function(e) {
            e.preventDefault();

            $.ajax({
                url: deletePresetUrl,
                type: 'POST',
                data: {
                    '_token': csrfToken,
                    "_method": 'delete',
                    'id': presetid
                },
                success: function () {
                    location.reload();
                }
            });
        });
    }

    initializeChangePresetName();
    initializeDeleteRecordPreset();
    initializeToggle();
}