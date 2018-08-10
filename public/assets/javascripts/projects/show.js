var Kora = Kora || {};
Kora.Projects = Kora.Projects || {};

Kora.Projects.Show = function() {

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

          $('.form.card').each(function() {
              $(this).removeClass('hidden');
          });
      });

      $('.search-js i, .search-js input').keyup(function() {
          var searchVal = $(this).val().toLowerCase();

          $('.form.card').each(function() {
              var name = $(this).find('.name').first().text().toLowerCase();

              if(name.includes(searchVal))
                  $(this).removeClass('hidden');
              else
                  $(this).addClass('hidden');
          });
      });
  }

  function clearFilterResults() {
    // Clear previous filter results
    $('.sort-options-js a').removeClass('active');
    $('.form-sort-js').removeClass('active');
  }

  function initializeCustomSort() {
    // Initialize Custom Sort
    $('.form-toggle-js').click(function(e) {
      e.preventDefault();

      var $this = $(this);
      var $header = $this.parent().parent();
      var $form = $header.parent();
      var $content = $header.next();

      $this.children().toggleClass('active');
      $form.toggleClass('active');
      if ($form.hasClass('active')) {
        $header.addClass('active');
        $form.animate({
          height: $form.height() + $content.outerHeight(true) + 'px'
        }, 230);
        $content.effect('slide', {
          direction: 'up',
          mode: 'show',
          duration: 240
        });
      } else {
        $form.animate({
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

    $(".form-custom-js").sortable({
      helper: 'clone',
      revert: true,
      containment: ".project-show",
      update: function(event, ui) {
        fidsArray = $(".form-custom-js").sortable("toArray");

        $.ajax({
          url: saveCustomOrderUrl,
          type: 'POST',
          data: {
            "_token": CSRFToken,
            "fids": fidsArray,

          },
          success: function(result) {}
        });
      }
    });

    $('.move-action-js').click(function(e) {
      e.preventDefault();

      var $this = $(this);
      var $headerInnerWrapper = $this.parent().parent();
      var $header = $headerInnerWrapper.parent();
      var $form = $header.parent();
      // $form.prev().before(current);
      if ($this.hasClass('up-js')) {
        var $previousForm = $form.prev();
        if ($previousForm.length == 0) {
          return;
        }

        $previousForm.css('z-index', 999)
          .css('position', 'relative')
          .animate({
            top: $form.height()
          }, 300);
        $form.css('z-index', 1000)
          .css('position', 'relative')
          .animate({
            top: '-' + $previousForm.height()
          }, 300, function() {
            $previousForm.css('z-index', '')
              .css('top', '')
              .css('position', '');
            $form.css('z-index', '')
              .css('top', '')
              .css('position', '')
              .insertBefore($previousForm);

              fidsArray = $(".form-custom-js").sortable("toArray");

              $.ajax({
                  url: saveCustomOrderUrl,
                  type: 'POST',
                  data: {
                      "_token": CSRFToken,
                      "fids": fidsArray,

                  },
                  success: function(result) {}
              });
          });
      } else {
        var $nextForm = $form.next();
        if ($nextForm.length == 0) {
          return;
        }

        $nextForm.css('z-index', 999)
          .css('position', 'relative')
          .animate({
            top: '-' + $form.height()
          }, 300);
        $form.css('z-index', 1000)
          .css('position', 'relative')
          .animate({
            top: $nextForm.height()
          }, 300, function() {
            $nextForm.css('z-index', '')
              .css('top', '')
              .css('position', '');
            $form.css('z-index', '')
              .css('top', '')
              .css('position', '')
              .insertAfter($nextForm);

              fidsArray = $(".form-custom-js").sortable("toArray");

              $.ajax({
                  url: saveCustomOrderUrl,
                  type: 'POST',
                  data: {
                      "_token": CSRFToken,
                      "fids": fidsArray,

                  },
                  success: function(result) {}
              });
          });
      }
    });
  }

  function initializeFilters() {
    $('.sort-options-js a').click(function(e) {
      e.preventDefault();

      var $this = $(this);
      var $content = $('.form-' + $this.attr('href').substring(1) + '-js');

      clearSearch();
      clearFilterResults();

      // Toggle self animation and display corresponding content
      $this.addClass('active');
      $content.addClass('active');
    });
  }
  
  function initializeFormCardEllipsifying()
  {
    function adjustFormCardTitle()
    {
		var alphabetical = false;
		var custom = false;
		
		if ($(".active-forms").hasClass("active"))
		{
			alphabetical = true;
			var cards = $($(".active-forms").find(".form.card"));
		}
		else if ($(".custom-forms").hasClass("active"))
		{
			custom = true;
			var cards = $($(".custom-forms").find(".form.card"));
		}
    	
    	for (i = 0; i < cards.length; i++)
    	{	
    	  var card = $(cards[i]);
    	  var name_span = $(card.find($(".name")));
    	  var arrow = $(card.find($(".icon-arrow-right"))); // all form card types have arrow
    	  var chevron = $(card.find($(".icon-chevron"))); // all form card types have chevron
		  var up_arrow = $(card.find($(".move-action-js.up-js")));
		  var down_arrow = $(card.find($(".move-action-js.down-js")));
    	  
    	  var card_width = card.width();
    	  var arrow_width = arrow.outerWidth();
    	  var chevron_width = chevron.outerWidth();
		  var up_arrow_width = up_arrow.length ? up_arrow.outerWidth() : 0;
		  var down_arrow_width = down_arrow.length ? down_arrow.outerWidth() : 0;
    	  var left_padding = custom ? 0 : 20; // padding within card
		  var extra_padding = 10;
    	  
    	  var title_width = (card_width - left_padding) - (arrow_width + chevron_width + up_arrow_width + down_arrow_width + extra_padding);
    	  if (title_width < 0) {title_width = 0;}
    	  
    	  name_span.css("text-overflow", "ellipsis");
    	  name_span.css("white-space", "nowrap");
    	  name_span.css("overflow", "hidden");
    	  name_span.css("max-width", title_width + "px");
    	}
    }
  	
    $(window).resize(function()
    {
      adjustFormCardTitle();
    });
	
    $(document).ready(function()
    {
      adjustFormCardTitle();
    });
	
	$("[href='#custom'], [href='#active']").click(function() { adjustFormCardTitle(); });
  }

  function initializeNotification() {
    var $noteBody = $('.notification');
    var $note = $('.note').children();

    setTimeout(function(){
      if ($note.text() != '') {
        $noteBody.removeClass('dismiss');

        if (!$noteBody.hasClass('warning')) {
          setTimeout(function(){
            $noteBody.addClass('dismiss');
          }, 6000);
        }
      }
    }, 200);

    $('.toggle-notification-js').click(function(e) {
      e.preventDefault();

      $noteBody.addClass('dismiss');
    });
  }

  initializeCustomSort();
  initializeFilters();
  initializeSearch();
  initializeFormCardEllipsifying();
  initializeNotification();
}
