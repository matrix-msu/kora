var Kora = Kora || {};
Kora.Revisions = Kora.Revisions || {};

Kora.Revisions.Index = function() {
  $('.multi-select').chosen({
    width: '100%'
  });

  function initializeOptionDropdowns() {
    $('.option-dropdown-js').chosen({
      disable_search_threshold: 10,
      width: 'auto'
    });
  }

  initializeOptionDropdowns();
}

Kora.Revisions.Index();