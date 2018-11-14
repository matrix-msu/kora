var Kora = Kora || {};
Kora.Inputs = Kora.Inputs || {};

Kora.Inputs.Textarea = function() {
    var $autosizeTextareas = $('textarea.autosize-js');

    autosizeTextareas();

    function autosizeTextareas() {
        $autosizeTextareas.keydown(function() {
            var $textarea = $(this);

            setTimeout(function() {
                $textarea.css('height', 'auto');
                $textarea.css('height', $textarea[0].scrollHeight);
            }, 0);
        });
    }
}