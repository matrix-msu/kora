var Kora = Kora || {};
Kora.Fields = Kora.Fields || {};

Kora.Fields.Show = function() {

    function initializeCleanUpModals() {
        Kora.Modal.initialize();

        $('.field-trash-js').click(function(e) {
            e.preventDefault();

            var $cleanupModal = $('.field-cleanup-modal-js');

            $cleanupModal.find('.title-js').html(
                $(this).data('title')
            );

            $cleanupModal.find('.delete-content-js').show();
            Kora.Modal.open();
        });
    }

    initializeCleanUpModals();
}