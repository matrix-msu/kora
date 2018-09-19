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
      var count = window.localStorage.getItem('count');
      if (count) {
        $('span.count').text(''+count+'');
      }
    }

    initializePage();
}