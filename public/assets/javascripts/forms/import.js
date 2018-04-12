var Kora = Kora || {};
Kora.Forms = Kora.Forms || {};

Kora.Forms.Import = function() {

    function initializeFormProgression() {
        $('.file-input-js').change(function() {
            $('.upload-file-btn-js').removeClass('disabled');
        });

        $('.upload-file-btn-js').click(function(e) {
            $('.formfile-link').removeClass('active');
            $('.forminfo-link').addClass('active');
            $('.forminfo-link').addClass('underline-middle');

            $('.formfile-section').addClass('hidden');
            $('.forminfo-section').removeClass('hidden');
        });
    }

    function initializeForm() {
        Kora.Inputs.File();
    }

    initializeFormProgression();
    initializeForm();
}