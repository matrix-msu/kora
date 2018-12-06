var Kora = Kora || {};
Kora.Inputs = Kora.Inputs || {};

Kora.Inputs.Textarea = function() {
    var $autosizeTextareas = $('textarea.autosize-js');

    autosizeTextareas();

    function autosizeTextareas() {
        //$autosizeTextareas.keydown(function() {
        $autosizeTextareas.on('input', function() {
            var $textarea = $(this);
            let that = this

            let height = this.scrollHeight

            setTimeout(function() {
                $textarea.css('height', 'auto');
                $textarea.css('height', $textarea[0].scrollHeight);

                if ($textarea[0].scrollHeight > 26)
                    $textarea.css('margin-bottom', '14px');
            }, 0);
        });
    }
}