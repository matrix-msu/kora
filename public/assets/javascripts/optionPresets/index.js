var Kora = Kora || {};
Kora.OptionPresets = Kora.OptionPresets || {};

Kora.OptionPresets.Index = function() {

    var currentPreset = -1;

    function clearSearch() {
        $('.search-js .icon-cancel-js').click();
    }

    function initializeSearch() {
        var $searchInput = $('.search-js input');

        $('.search-js i, .search-js input').click(function(e) {
            e.preventDefault();

            $(this).parent().addClass('active');
            $('.search-js input').focus();
        });

        $searchInput.focusout(function() {
            if (this.value.length == 0) {
                $(this).parent().removeClass('active');
                $(this).next().removeClass('active');
            }
        });

        $searchInput.keyup(function(e) {
            if (e.keyCode === 27) {
                $(this).val('');
            }

            if (this.value.length > 0) {
                $(this).next().addClass('active');
            } else {
                $(this).next().removeClass('active');
            }
        });

        $('.search-js .icon-cancel-js').click(function() {
            $searchInput.val('').blur().parent().removeClass('active');
            var activeSection = $('.option.active').first().attr('href').substring(1);

            $('.preset.card').each(function() {
                if($(this).hasClass(activeSection))
                    $(this).removeClass('hidden');
            });
        });

        $('.search-js i, .search-js input').keyup(function() {
            var searchVal = $(this).val().toLowerCase();
            var activeSection = $('.option.active').first().attr('href').substring(1);

            $('.preset.card').each(function() {
                if($(this).hasClass(activeSection)) {
                    var name = $(this).find('.name').first().text().toLowerCase();

                    if (name.includes(searchVal))
                        $(this).removeClass('hidden');
                    else
                        $(this).addClass('hidden');
                }
            });
        });
    }

    function clearFilterResults() {
        // Clear previous filter results
        $('.sort-options-js a').removeClass('active');
        $('.preset').addClass('hidden');
    }

    function initializeFilters() {
        $('.sort-options-js a').click(function(e) {
            e.preventDefault();

            var $this = $(this);
            var $content = $('.preset.'+ $this.attr('href').substring(1));

            clearSearch();
            clearFilterResults();

            // Toggle self animation and display corresponding content
            $this.addClass('active');
            $content.removeClass('hidden');
        });
    }

    function initializeToggle() {
        // Initialize card toggling
        $('.preset-toggle-js').click(function(e) {
            e.preventDefault();

            var $this = $(this);
            var $header = $this.parent().parent();
            var $token = $header.parent();
            var $content = $header.next();

            $this.children('.icon').toggleClass('active');
            $token.toggleClass('active');
            if ($token.hasClass('active')) {
                $header.addClass('active');
                $token.animate({
                    height: $token.height() + $content.outerHeight(true) + 'px'
                }, 230);
                $content.effect('slide', {
                    direction: 'up',
                    mode: 'show',
                    duration: 240
                });
            } else {
                $token.animate({
                    height: '58px'
                }, 230, function() {
                    $header.hasClass('active') ? $header.removeClass('active') : null;
                    $content.hasClass('active') ? $content.removeClass('active') : null;
                });
                $content.effect('slide', {
                    direction: 'up',
                    mode: 'hide',
                    duration: 240
                });
            }

        });
    }

    function initializeDeletePresetModal() {
        Kora.Modal.initialize();

        $('.delete-preset-open-js').click(function(e) {
            e.preventDefault();

            currentPreset = $(this).attr('preset-id');
            Kora.Modal.open($('.delete-preset-modal-js'));
        });

        $('.delete-preset-js').click(function(e) {
            $.ajax({
                url: deletePresetURL,
                type: 'POST',
                data: {
                    "_token": CSRFToken,
                    "_method": 'delete',
                    "presetId": currentPreset
                },
                success: function (result) {
                    location.reload();
                }
            });
        });
    }
  
    function initializePresetCardEllipsifying () {
      function adjustPresetCardTitle () {
        var cards = $($(".option-presets-selection").find(".preset.card"));

        for (i = 0; i < cards.length; i++) {	
          var card = $(cards[i]);
          var name_span = $(card.find($(".name")));
          var chevron_text = $(card.find($(".chevron-text")));
          var chevron = $(card.find($(".icon-chevron")));

          var card_width = card.width();
          var chevron_text_width = chevron_text.length ? chevron_text.outerWidth() : 0; // if arrow is valid jquery object
          var chevron_width = chevron.outerWidth(); // all types of project cards have chevrons
          var extra_padding = 10;
          
          var title_width = (card_width) - (chevron_text_width + chevron_width + extra_padding);
          if (title_width < 0) {title_width = 0;}
          
          name_span.css("text-overflow", "ellipsis");
          name_span.css("white-space", "nowrap");
          name_span.css("overflow", "hidden");
          name_span.css("max-width", title_width + "px");
        }
      }
      
      $(window).resize(function() {
        adjustPresetCardTitle();
      });
    
      $(document).ready(function() {
        adjustPresetCardTitle();
      });

      // Recalculate ellipses when switching project types
      $("[href='#all'], [href='#project'], [href='#shared'], [href='#stock']").click(function() { adjustPresetCardTitle(); });
    }

    initializeSearch();
    initializeFilters();
    initializeToggle();
    initializeDeletePresetModal();
    initializePresetCardEllipsifying();
}
