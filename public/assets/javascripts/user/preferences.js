var Kora = Kora || {};
Kora.User = Kora.User || {};

Kora.User.Preferences = function() {
    function initializeFixedElementScroll() {
        var $fixedElem = $(".pre-fixed-js");
        var $form = $fixedElem.parent().parent();

        $(window).scroll(function() {
            if ($fixedElem.hasClass('fixed-bottom')) {
                $form.css("margin-bottom", "150px");
            } else {
                $form.css("margin-bottom", "0");
            }
        });
    }

    initializeFixedElementScroll();
};
