var Kora = Kora || {};
Kora.User = Kora.User || {};

Kora.User.Preferences = function() {
    function initializeCheckboxes() {
        var $checkboxes = $(".check-box-input-js");

        $checkboxes.click(function() {
            var $this = $(this);
            var $formGroup = $this.parent().parent();
            var $formGroupCheckboxes = $formGroup.find(".check-box-input-js");

            $formGroupCheckboxes.prop("checked", false);
            $this.prop("checked", true);
        });
    }

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

    initializeCheckboxes();
    initializeFixedElementScroll();
};
