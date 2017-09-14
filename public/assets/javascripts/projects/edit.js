var Kora = Kora || {};
Kora.Projects = Kora.Projects || {};

Kora.Projects.Edit = function() {

  function initializeCleanUpModals() {
    Kora.Modal.initialize();

    $('.project-trash-js').click(function(e) {
      e.preventDefault();

      var $cleanupModal = $('.project-cleanup-modal-js');

      $cleanupModal.find('.title-js').html(
        $(this).data('title')
      );

      $cleanupModal.find('.delete-content-js').show();
      $cleanupModal.find('.archive-content-js').hide();
      Kora.Modal.open();
    });

    $('.project-archive-js').click(function(e) {
      e.preventDefault();

      var $cleanupModal = $('.project-cleanup-modal-js');

      $cleanupModal.find('.title-js').html(
        $(this).data('title')
      );

      $cleanupModal.find('.archive-content-js').show();
      $cleanupModal.find('.delete-content-js').hide();
      Kora.Modal.open();
    });
  }

  initializeCleanUpModals();
}
