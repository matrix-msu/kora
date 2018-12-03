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
    // Remove any existing arrows
    $('.num-arrows-js').remove();

    // Add on arrows to number inputs
    $numberInputs.after('<div class="num-arrows num-arrows-js"><div class="arrow arrow-js arrow-up arrow-up-js"><i class="icon icon-chevron"></i></div><div class="spacer"></div><div class="arrow arrow-js arrow-down arrow-down-js"><i class="icon icon-chevron"></i></div></div>');

    $numberInputs.each(function() {
      updateArrows($(this));
    });
  }

  function updateArrows($input) {
    var $arrowsContainer = $input.siblings('.num-arrows');
    var $arrows = $arrowsContainer.find('.arrow-js');

    var num = ($input.val() && $.isNumeric($input.val()) ? parseInt($input.val()) : 0);
    var min = ($input.attr('min') ? parseInt($input.attr('min')) : 'unlimited');
    var max = ($input.attr('max') ? parseInt($input.attr('max')) : 'unlimited');
    var step = ($input.attr('step') && $.isNumeric($input.attr('step')) ? parseFloat($input.attr('step')) : 1);
    var decimalPlaces = getDecimalPlaces(step);

    $arrows.click(function() {
      var $arrow = $(this);

      if ($arrow.hasClass('arrow-up-js')) {
        num = num + step;
        if (max != 'unlimited' && num > max) {
          num = max;
        }
      } else if ($arrow.hasClass('arrow-down-js')) {
        num = num - step;
        if (min != 'unlimited' && num < min) {
          num = min;
        }
      }

      $input.val(num.toFixed(decimalPlaces));
    });
  }

  function getDecimalPlaces(num) {
    var numStr = num.toString();
    var decIndex = numStr.indexOf('.') + 1;

    return !decIndex ? 0 : numStr.length - decIndex;
  }
}
