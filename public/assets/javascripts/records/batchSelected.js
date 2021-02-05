var Kora = Kora || {};
Kora.Records = Kora.Records || {};

Kora.Records.BatchSelected = function() {

    $('.single-select').chosen({
        allow_single_deselect: true,
        width: '100%',
    });

    $('.multi-select').chosen({
        width: '100%',
    });

    function initializePage () {
      let count = window.localStorage.getItem('count');
      let rids = window.localStorage.getItem('selectedRecords');
      let $form = $('.batch-form');

      if (rids) {
          rids = rids.split(',');
          $form.append('<input type="hidden" name="rids" value="' + rids + '">');
      }

      if (count) {
        $('span.count').text('' + count + '');
        Kora.Records.Batch();
      }
    }

    $('.batch-selected-submit-js').click( function() {
        window.localStorage.removeItem('selectedRecords');
    });

    initializePage();
}
