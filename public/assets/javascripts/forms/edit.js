var Kora = Kora || {};
Kora.Forms = Kora.Forms || {};

Kora.Forms.Edit = function() {

  function initializeCleanUpModals() {
    Kora.Modal.initialize();

    $('.form-trash-js').click(function(e) {
      e.preventDefault();

      var $cleanupModal = $('.form-cleanup-modal-js');

      $cleanupModal.find('.title-js').html(
        $(this).data('title')
      );

      $cleanupModal.find('.delete-content-js').show();
      Kora.Modal.open();
    });
  }

  initializeCleanUpModals();
}
