var Kora = Kora || {};
Kora.Records = Kora.Records || {};

Kora.Records.Advanced = function() {

    function initializeDateOptions() {
        var $dateFormGroups = $('.date-input-form-group-js');
        var $dateListInputs = $dateFormGroups.find('.chosen-container');
        var scrollBarWidth = 17;

        $eraCheckboxes = $('.era-check-js');

        $eraCheckboxes.click(function() {
            var $selected = $(this);
            flid = $selected.attr('flid');
            range = $selected.attr('range');

            $('.era-check-'+flid+'-'+range+'-js').prop('checked', false);
            $selected.prop('checked', true);

            currEra = $selected.val();
            $month = $('#'+flid+'_'+range+'_month');
            $day = $('#'+flid+'_'+range+'_day');

            if(currEra=='BP' | currEra=='KYA BP') {
                $month.attr('disabled','disabled');
                $day.attr('disabled','disabled');
                $month.trigger("chosen:updated");
                $day.trigger("chosen:updated");
            } else {
                $month.removeAttr('disabled');
                $day.removeAttr('disabled');
                $month.trigger("chosen:updated");
                $day.trigger("chosen:updated");
            }
        });

        setTextInputWidth();

        $(window).resize(setTextInputWidth);

        function setTextInputWidth() {
            if ($(window).outerWidth() < 1175 - scrollBarWidth) {
                // Window is small, full width Inputs
                $dateListInputs.css('width', '100%');
                $dateListInputs.css('margin-bottom', '10px');
            } else {
                // Window is large, 1/3 width Inputs
                $dateListInputs.css('width', '33%');
                $dateListInputs.css('margin-bottom', '');
            }
        }
    }

    initializeDateOptions();
}