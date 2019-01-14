var Kora = Kora || {};
Kora.Forms = Kora.Forms || {};

Kora.Forms.Show = function() {
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

          $('.field.card').each(function() {
              $(this).removeClass('hidden');
          });
      });

      $('.search-js i, .search-js input').keyup(function() {
          var searchVal = $(this).val().toLowerCase();

          $('.field.card').each(function() {
              var name = $(this).find('.name').first().text().toLowerCase();

              if(name.includes(searchVal))
                  $(this).removeClass('hidden');
              else
                  $(this).addClass('hidden');
          });
      });
  }

  function initializeFieldSort() {
    // Initialize Custom Sort
    $('.field-toggle-js').click(function(e) {
      e.preventDefault();

      var $this = $(this);
      var $header = $this.parent().parent();
      var $field = $header.parent();
      var $content = $header.next();

      $this.children('.icon').toggleClass('active');
      $field.toggleClass('active');
      if ($field.hasClass('active')) {
        $header.addClass('active');
        $field.animate({
          height: $field.height() + $content.outerHeight(true) + 'px'
        }, 230);
        $content.effect('slide', {
          direction: 'up',
          mode: 'show',
          duration: 240
        });
      } else {
        $field.animate({
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

    $('.field-sort-js').sortable({
      helper: 'clone',
      // revert: true,
      containment: '.form-show',
      connectWith: '.field-sort-js',
      items: '.field-container',
      update: function(event, ui) {
        var layout = {};
        var $pages = $('.page').map(function() { return $(this).attr('page-id') }).get();
        $.each($pages, function(i, page) {
          layout[page] = $('.page[page-id="'+page+'"]').find('.field.card').map(function() { return this.id }).get();

          if (layout[page].length == 0)
              $('.page[page-id="'+page+'"]').find('.no-fields').removeClass('hidden');
          else
              $('.page[page-id="'+page+'"]').find('.no-fields').addClass('hidden');
        });

        $.ajax({
          url: saveFullFormLayoutRoute,
          type: 'POST',
          data: {
            "_token": CSRFToken,
            "layout": JSON.stringify(layout),
          }
        });
      }
    });

    $('.move-action-js').click(function(e) {
      e.preventDefault();

      var $this = $(this);
      var $headerInnerWrapper = $this.parent().parent();
      var $header = $headerInnerWrapper.parent();
      var $field = $header.parent();
      var $fieldContainer = $field.parent();
      var $page = $fieldContainer.parent().parent();
      var url = $field.attr('move-url');
      var seq = $field.attr('sequence');
      // $field.prev().before(current);
      if ($this.hasClass('up-js')) {
        var $previousField = $fieldContainer.prev();
        if ($previousField.length == 0 || !$previousField.hasClass('field-container')) {
          var $previousPage = $page.prev().prev();
          if ($previousPage.length !== 0) {
            var $previousFieldContainer = $previousPage.children('.field-sort-js');
            var $createPageButton = $previousPage.next();
            var $createFieldButton = $previousPage.children('form');

            $page.css('z-index', 999)
              .css('position', 'relative');
            $previousPage.css('z-index', 999)
              .css('position', 'relative');
            $createPageButton.css('z-index', 999)
              .css('position', 'relative');
            $createFieldButton.css('z-index', 999)
              .css('position', 'relative');
            $previousFieldContainer.css('z-index', 999)
              .css('position', 'relative')
              .animate({
                height: $previousFieldContainer.height() + $fieldContainer.height() + 60
              }, 300).css('overflow', '');
            $fieldContainer.css('z-index', 1000)
              .css('position', 'relative')
              .animate({
                top: '-' + ($fieldContainer.height() + $createFieldButton.height() + $createPageButton.height() + 240),
                height: 0
              }, 300, function() {
                $page.css('z-index', '')
                  .css('position', '');
                $previousPage.css('z-index', '')
                  .css('position', '');
                $createPageButton.css('z-index', '')
                  .css('position', '');
                $createFieldButton.css('z-index', '')
                  .css('position', '');
                $fieldContainer.css('z-index', '')
                  .css('top', '')
                  .css('position', '')
                  .css('height', '');
                $previousFieldContainer.css('z-index', '')
                  .css('position', '')
                  .css('height', '')
                  .append($fieldContainer);
              }).css('overflow', '');

            $.ajax({

            });
          }
        } else {
          $previousField.css('z-index', 999)
          .css('position', 'relative')
          .animate({
            top: $field.height()
          }, 300);
          $fieldContainer.css('z-index', 1000)
            .css('position', 'relative')
            .animate({
              top: '-' + $previousField.height()
            }, 300, function() {
              $previousField.css('z-index', '')
                .css('top', '')
                .css('position', '');
              $fieldContainer.css('z-index', '')
                .css('top', '')
                .css('position', '')
                .insertBefore($previousField);
            });
        }
        
        $.ajax({
          url: url,
          type: 'POST',
          data: {
            '_token': CSRFToken,
            'direction': upMethod,
            'sequence': seq
          }
        });
      } else {
        var $nextField = $fieldContainer.next();
        if ($nextField.length == 0 || !$nextField.hasClass('field-container')) {
          var $nextPage = $fieldContainer.parent().parent().next().next();
          if ($nextPage.length !== 0) {
            var $nextFieldContainer = $nextPage.children('.field-sort-js');
            var $createPageButton = $page.next();
            var $createFieldButton = $nextPage.children('form');

            $nextPage.css('z-index', 999)
              .css('position', 'relative');
            $createPageButton.css('z-index', 999)
              .css('position', 'relative');
            $createFieldButton.css('z-index', 999)
              .css('position', 'relative');
            $nextFieldContainer.css('z-index', 999)
              .css('position', 'relative')
              .animate({
                'margin-top': $fieldContainer.height() + 60
              }, 300).css('overflow', '');
            $fieldContainer.css('z-index', 1000)
              .css('position', 'relative')
              .animate({
                top: ($createFieldButton.height() + $createPageButton.height() + 240),
                height: 0,
                margin: 0
              }, 300, function() {
                $nextPage.css('z-index', '')
                  .css('position', '');
                $createPageButton.css('z-index', '')
                  .css('position', '');
                $createFieldButton.css('z-index', '')
                  .css('position', '');
                $fieldContainer.css('z-index', '')
                  .css('top', '')
                  .css('height', '')
                  .css('margin', '')
                  .css('position', '');
                $nextFieldContainer.css('z-index', '')
                  .css('position', '')
                  .css('height', '')
                  .css('margin-top', '')
                  .prepend($fieldContainer);
              }).css('overflow', '');
          }
        } else {
          $nextField.css('z-index', 999)
            .css('position', 'relative')
            .animate({
              top: '-' + $field.height()
            }, 300);
          $fieldContainer.css('z-index', 1000)
            .css('position', 'relative')
            .animate({
              top: $nextField.height()
            }, 300, function() {
              $nextField.css('z-index', '')
                .css('top', '')
                .css('position', '');
              $fieldContainer.css('z-index', '')
                .css('top', '')
                .css('position', '')
                .insertAfter($nextField);
            });
        }

        $.ajax({
          url: url,
          type: 'POST',
          data: {
            '_token': CSRFToken,
            'direction': downMethod,
            'sequence': seq
          }
        })
      }
    });

	// As of jQuery 1.9, all handlers for .ajaxComplete MUST be attached to $(document)
	// http://api.jquery.com/ajaxcomplete/
    $(document).ajaxComplete(function () {
	  setTimeout(function () {
	    $.each($('.field-sort-js'), function () {
		  if ($(this).children().length > 0) {
		    $(this).siblings('.no-fields').addClass('hidden');
		  } else {
		    $(this).siblings('.no-fields').removeClass('hidden');
		  }
		});
	  }, 500)
	});
  }

  function initializePage() {
    Kora.Modal.initialize(); // Initialize modals

    pageTitles = document.getElementsByClassName('page-title-js');
    for (var i = 0; i < pageTitles.length; i++) {
      $pageTitle = $(pageTitles[i]);
      window.localStorage.setItem('pageTitle', pageTitles[i].placeholder);
      $pageTitle.attr('size',
        $pageTitle.attr('placeholder').length);
      
      $pageTitle.on('keyup blur', function(e) {
        $this = $(this);
        if ((e.key === "Enter" || e.type === "blur") && $this.val() !== '') {
          $.ajax({
            url: modifyFormPageRoute,
            type: 'POST',
            data: {
              '_token': CSRFToken,
              'method': renameMethod,
              'pageID': $this.attr('pageid'),
              'updatedName': $this.val()
            },
            success: function () {
              newTitle = window.localStorage.getItem('pageTitle');
              if (newTitle !== $this[0].placeholder) {
                $('.note').children('p').text('Form Page Name Changed Successfully!');
                $('.notification').removeClass('dismiss');
                setTimeout(function(){
                  $('.notification').addClass('dismiss');
                }, 4000);
              }
            }
          });
          $this.attr('placeholder', $this.val());
          $this.attr('size', $this.val().length)
          $this.val('');
        }

        if (e.key === "Enter") {
          $this.blur()
        }
      });

      $pageTitle.on('click focus', function(e) {
        $(this).val($(this).attr('placeholder'));
      });
    }

    $('.delete-page-js').on('click', function(e) {
      e.preventDefault();
      if ($(".delete-page-js").length == 1) return;
	  
      var page = $(this).data('page');

      var $deleteModal = $('.page-delete-modal-js');
      $deleteModal.find('.delete-page-confirm-js').data('page', page);

      Kora.Modal.open($deleteModal);
    });

    $('.delete-field-js').on('click', function(e) {
      e.preventDefault();
      var field = $(this).parents('.field').attr('id');
      var url = $(this).parents('.field').attr('delete-url');

      var $deleteModal = $('.field-delete-modal-js');
      $deleteModal.find('.delete-field-confirm-js').attr('field', field).attr('delete-url', url);

      Kora.Modal.open($deleteModal);
    })

    $('.move-action-page-js').on('click', function(e) {
      e.preventDefault();

      var $this = $(this);
      var $pageID = $this.attr('page_id');
      var $page = $this.parent().parent().parent();
      var $pageAdd = $page.next();
      
      if ($this.hasClass('up-js')) {
        var $previousPageAdd = $page.prev();
        var $previousPage = $previousPageAdd.prev();
        if ($previousPageAdd.length == 0) {
          // move to previous page if exists
          return;
        }

        $previousPage.css('z-index', 999)
          .css('position', 'relative')
          .animate({
            top: $page.height() + $previousPageAdd.height() + 120
          }, 300);
        $previousPageAdd.css('z-index', 999)
          .css('position', 'relative')
          .animate({
            top: $page.height() + $previousPageAdd.height() + 120
          }, 300);
        $pageAdd.css('z-index', 1000)
          .css('position', 'relative')
          .animate({
            top: '-' + ($previousPage.height() + $previousPageAdd.height() + 120)
          }, 300);
        $page.css('z-index', 1000)
          .css('position', 'relative')
          .animate({
            top: '-' + ($previousPage.height() + $previousPageAdd.height() + 120)
          }, 300, function() {
            $previousPage.css('z-index', '')
              .css('top', '')
              .css('position', '');
            $page.css('z-index', '')
              .css('top', '')
              .css('position', '')
              .insertBefore($previousPage);
            $previousPageAdd.css('z-index', '')
              .css('top', '')
              .css('position', '')
              .insertAfter($previousPage);
            $pageAdd.css('z-index', '')
              .css('top', '')
              .css('position', '')
              .insertAfter($page);
          });

        $.ajax({
          url: modifyFormPageRoute,
          type: 'POST',
          data: {
            '_token': CSRFToken,
            'method': upMethod,
            'pageID': $pageID
          }
        });
      } else {
        var $nextPage = $pageAdd.next();
        var $nextPageAdd = $nextPage.next();
        if ($nextPage.length == 0) {
          return;
        }

        $nextPage.css('z-index', 999)
          .css('position', 'relative')
          .animate({
            top: '-' + ($page.height() + $pageAdd.height() + 120)
          }, 300);
        $nextPageAdd.css('z-index', 999)
          .css('position', 'relative')
          .animate({
            top: '-' + ($page.height() + $pageAdd.height() + 120)
          }, 300);

        $pageAdd.css('z-index', 1000)
          .css('position', 'relative')
          .animate({
            top: $nextPage.height() + $pageAdd.height() + 120
          }, 300);
        $page.css('z-index', 1000)
          .css('position', 'relative')
          .animate({
            top: $nextPage.height() + $pageAdd.height() + 120
          }, 300, function() {
            $nextPage.css('z-index', '')
              .css('top', '')
              .css('position', '');
            $page.css('z-index', '')
              .css('top', '')
              .css('position', '')
              .insertAfter($nextPage);
            $pageAdd.css('z-index', '')
              .css('top', '')
              .css('position', '')
              .insertAfter($page);
            $nextPageAdd.css('z-index', '')
              .css('top', '')
              .css('position', '')
              .insertAfter($nextPage);
          });

        $.ajax({
          url: modifyFormPageRoute,
          type: 'POST',
          data: {
            '_token': CSRFToken,
            'method': downMethod,
            'pageID': $pageID
          }
        });
      }
    });
  }

  function initializePages() {
    initializePage();

    $('.pages-js').on('click', '.new-page-js', function(e) {
      e.preventDefault();
      var newPageNumber = $(this).data('new-page');
      var title = 'Page #' + newPageNumber;

      $.ajax({
        //We manually create the link in a cheap way because our JS isn't aware of the fid until runtime
        //We pass in a blank project to the action array and then manually add the id
        url: modifyFormPageRoute,
        type: 'POST',
        data: {
          '_token': CSRFToken,
          'method': addMethod,
          'pageID': newPageNumber,
          'newPageName': title
        },
        success: function(result) {
          window.localStorage.setItem('newPageCheck', 'true');
          location.reload();
        }
      });
    });

    $('.page-delete-modal-js').on('click', '.delete-page-confirm-js', function(e) {
      e.preventDefault();
      var pageID = $(this).data('page');

      $.ajax({
        //We manually create the link in a cheap way because our JS isn't aware of the fid until runtime
        //We pass in a blank project to the action array and then manually add the id
        url: modifyFormPageRoute,
        type: 'POST',
        data: {
          '_token': CSRFToken,
          'method': delMethod,
          'pageID': pageID
        },
        success: function(result) {
          window.localStorage.setItem('message', 'Form Page Successfully Deleted');
          location.reload();
        }
      });
    });

    $('.field-delete-modal-js').on('click', '.delete-field-confirm-js', function(e) {
      e.preventDefault();
      var fieldID = $(this).attr('field');
      var url = $(this).attr('delete-url');
      
      $.ajax({
        url: url,
        type: 'POST',
        data: {
          '_token': CSRFToken,
          '_method': 'delete'
        },
        success: function(result) {
          window.localStorage.setItem('message', 'Field Successfully Deleted');
          location.reload();
        }
      })
    });
  }

  function initializeFieldToggles() {
    $('.expand-fields-js').on('click', function(e) {
      e.preventDefault();
      $('.card:not(.active) .field-toggle-js').click();
    });

    $('.collapse-fields-js').on('click', function(e) {
      e.preventDefault();
      $('.card.active .field-toggle-js').click();
    });
  }

  function initializeCheckboxes() {
    $('.preset-input-js').on('change', function(e) {
      var url = $(this).parents('.allowed-actions').attr('update-flag-url');

      $.ajax({
        url: url,
        type: 'POST',
        data: {
          '_token': CSRFToken,
          "_method": 'patch',
          'flag': this.name,
          'value': this.checked ? 1 : 0
        }
      });
    });
  }
  
  function initializeFieldCardEllipsifying()
  {
    function adjustFieldCardTitle()
	{
		var cards = $($(".field-sort-js").find(".field.card"));
		
		for (i = 0; i < cards.length; i++)
		{	
			var card = $(cards[i]);
			var name_span = $(card.find($(".name")));
			var arrow = $(card.find($(".icon-arrow-right")));
			var chevron_text = $(card.find($(".chevron-text")));
			var chevron_icon = $(card.find($(".icon-chevron")));
			var move_up_icon = $(card.find($(".up-js")));
			var move_down_icon = $(card.find($(".down-js")));
			
			var card_width = card.width();
			var arrow_width = arrow.outerWidth();
			var chevron_text_width = chevron_text.outerWidth();
			var chevron_icon_width = chevron_icon.outerWidth();
			var move_up_icon_width = move_up_icon.outerWidth();
			var move_down_icon_width = move_down_icon.outerWidth();
			var extra_padding = 15;
			
			
			var title_width = (card_width) - (arrow_width + chevron_text_width + chevron_icon_width + move_up_icon_width + move_down_icon_width + extra_padding);
			if (title_width < 0) {title_width = 0;}
			
			name_span.css("text-overflow", "ellipsis");
			name_span.css("white-space", "nowrap");
			name_span.css("overflow", "hidden");
			name_span.css("max-width", title_width + "px");
		}
	}
  	
    $(window).resize(function()
    {
    	adjustFieldCardTitle();
    });
    $(document).ready(function()
    {
    	adjustFieldCardTitle();
		setTimeout(function(){ adjustFieldCardTitle(); adjustFieldCardTitle(); }, 1); // necessary for some reason
    });
  }

  initializeSearch();
  initializePages();
  initializeFieldSort();
  initializeFieldToggles();
  initializeCheckboxes();
  initializeFieldCardEllipsifying();
}

// scroll to new page if new page was created
$(document).ready(function () {
  try {
    var check = window.localStorage.getItem('newPageCheck')
    if (check) {
      window.localStorage.removeItem('newPageCheck')
      var pageIDs = []
      var pages = $('.page');
      for (var i = 0; i < pages.length; i++) {
        pageIDs.push('' + pages[i].getAttribute('page-id'))
      }
      var max = Math.max.apply(null, pageIDs)
      var scrollTo = $('.page[page-id=' + max + ']').offset().top
      $('html, body').animate({
        scrollTop: scrollTo
      }, 2500);
    } 
  }
  catch (err) {
    console.log('' + err.message)
  }
});
