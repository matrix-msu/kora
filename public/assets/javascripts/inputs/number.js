var Kora = Kora || {};
Kora.Inputs = Kora.Inputs || {};

Kora.Inputs.Number = function() {
  var $numberInputContainers = $('.number-input-container-js');
  var $numberInputs = $numberInputContainers.find('input[type=number]');

  initializeNumberArrows();

  $numberInputs.change(function() {
    updateArrows($(this));
  });

  function initializeNumberArrows() {
    // Add on arrows to number inputs
    $numberInputs.after('<div class="num-arrows"><div class="arrow arrow-js arrow-up arrow-up-js"><i class="icon icon-chevron"></i></div><div class="spacer"></div><div class="arrow arrow-js arrow-down arrow-down-js"><i class="icon icon-chevron"></i></div></div>');

    $numberInputs.each(function() {
      updateArrows($(this));
    });
  }

  function updateArrows($input) {
    var $arrowsContainer = $input.siblings('.num-arrows');
    var $arrows = $arrowsContainer.find('.arrow-js');

    var num = parseInt($input.val());
    var min = ($input.attr('min') ? parseInt($input.attr('min')) : '0');
    var max = ($input.attr('max') ? parseInt($input.attr('max')) : 'unlimited');

    $arrows.click(function() {
      var $arrow = $(this);

      if ($arrow.hasClass('arrow-up-js')) {
        num = num + 1;
        if (max != 'unlimited' && num > max) {
          num = max;
        }
      } else if ($arrow.hasClass('arrow-down-js')) {
        num = num - 1;
        if (num < min) {
          num = min;
        }
      }

      $input.val(num);
    });
  }
}
