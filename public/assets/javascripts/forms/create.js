var Kora = Kora || {};
Kora.Forms = Kora.Forms || {};

Kora.Forms.Create = function() {

  $('.multi-select').chosen({
    width: '100%',
  });

  $('.single-select').chosen({
    width: '100%',
  });

  $('.preset-input-js').change(function() {
    if (this.checked) {
      $('.preset-select-container-js').animate({
        height: 75
      }, function() {
        $('.preset-select-js').fadeIn();
      });
    } else {
      $('.preset-select-js').fadeOut(function() {
        $('.preset-select-container-js').animate({
          height: 0
        });
      });
    }
  });
}
