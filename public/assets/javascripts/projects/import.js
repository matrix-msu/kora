var Kora = Kora || {};
Kora.Projects = Kora.Projects || {};

Kora.Projects.Import = function() {

    function initializeFormProgression() {
        $('.file-input-js').change(function() {
            $('.upload-file-btn-js').removeClass('disabled');
        });

        $('.upload-file-btn-js').click(function(e) {
            $('.projectfile-link').removeClass('active');
            $('.projectinfo-link').addClass('active');
            $('.projectinfo-link').addClass('underline-middle');

            $('.projectfile-section').addClass('hidden');
            $('.projectinfo-section').removeClass('hidden');
        });
    }

    function initializeForm() {
        Kora.Inputs.File();
    }

    initializeFormProgression();
    initializeForm();
}