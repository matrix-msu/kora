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

    function initializeDeleteRecord() {
        $('.delete-record-js').click(function (e) {
            e.preventDefault();

            var $modal = $('.delete-record-modal-js');

            Kora.Modal.open($modal);
        });
    }

    function initializeAssociatorCardToggle () {
        $('.assoc-card-toggle-js').click(function (e) {
            e.preventDefault();

            let $card = $(this).parent().parent().parent();
            let $cardBody = $card.find('.body');

            $(this).children().toggleClass('active');

            if ($(this).children().hasClass('active')) {
                //$card.css('height', '');
                $card.animate({
                    height: $card.height() + $cardBody.outerHeight() + 'px'
                }, 230);
                $cardBody.effect('slide', {
                    direction: 'up',
                    mode: 'show',
                    duration: 240
                });
            } else {
                $card.animate({
                    height: '49px'
                }, 230);
                $cardBody.effect('slide', {
                    direction: 'up',
                    mode: 'hide',
                    duration: 240
                });
            }
        });
    }

    initializeToggle();
    initializeDeleteRecord();
    initializeAssociatorCardToggle();
    Kora.Records.Modal();
    Kora.Fields.TypedFieldDisplays.Initialize();
}
