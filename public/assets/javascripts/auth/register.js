var Kora = Kora || {};
Kora.Auth = Kora.Auth || {};

Kora.Auth.Register = function() {
  function initializeChosen() {
    $(".chosen-select").chosen({
      disable_search_threshold: 10,
      width: '100%'
    });
  }
  
  function initializeForm() {
    Kora.Inputs.File();
  }
  
  initializeChosen();
  initializeForm();
}