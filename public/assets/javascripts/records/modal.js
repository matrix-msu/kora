var Kora = Kora || {};
Kora.Records = Kora.Records || {};

Kora.Records.Modal = function() {
  function initializeDesignateRecordPreset() {
      Kora.Modal.initialize();

      $('.designate-preset-js').click(function (e) {
          e.preventDefault();

          var $modal = $('.designate-record-preset-modal-js');

          Kora.Modal.open($modal);
      });

      $('.create-record-preset-js').click(function (e) {
          e.preventDefault();

          var preset_name = $('.preset-name-js').val();

          if(preset_name.length > 3) {
              $('.preset-error-js').text('');
              $('.preset-name-js').removeClass('error');

              $.ajax({
                  url: makeRecordPresetURL,
                  type: 'POST',
                  data: {
                      "_token": csrfToken,
                      "name": preset_name,
                      "kid": ridForPreset
                  },
                  success: function () {
                      location.reload();
                  }
              });
          } else {
              $('.preset-error-js').text('Present name must be 4+ characters');
              $('.preset-name-js').addClass('error');
          }
      });
  }

  function initializeAlreadyRecordPreset() {
      $('.already-preset-js').click(function (e) {
          e.preventDefault();

          var $modal = $('.already-record-preset-modal-js');

          Kora.Modal.open($modal);
      });

      $('.gotchya-js').click(function (e) {
          e.preventDefault();

          var $modal = $('.already-record-preset-modal-js');

          Kora.Modal.close($modal);
      });
  }

  initializeDesignateRecordPreset();
  initializeAlreadyRecordPreset();
};
