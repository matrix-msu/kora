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

  initializeCustomSort();
  initializeFilters();
  initializeSearch();





  // function deleteForm(formName, fid) {
  //   var encode = $('<div/>').html(areYouSure).text();
  //   var response = confirm(encode + formName + "?");
  //   if (response) {
  //     $.ajax({
  //       //We manually create the link in a cheap way because the JS isn't aware of the fid until runtime
  //       //We pass in a blank project to the action array and then manually add the id
  //       url: formDestroyUrl + '/' + fid,
  //       type: 'DELETE',
  //       data: {
  //         "_token": CSRFToken
  //       },
  //       success: function(result) {
  //         location.reload();
  //       }
  //     });
  //   }
  // }
}
