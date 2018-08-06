var Kora = Kora || {};
Kora.Projects = Kora.Projects || {};

Kora.Projects.Index = function() {

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

        $('.project.card').each(function() {
            $(this).removeClass('hidden');
        });
    });

    $('.search-js i, .search-js input').keyup(function() {
        var searchVal = $(this).val().toLowerCase();

        $('.project.card').each(function() {
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
    $('.project-sort-js').removeClass('active');
  }

  function initializeCustomSort() {
    // Initialize Custom Sort
    $('.project-toggle-js').click(function(e) {
      e.preventDefault();

      var $this = $(this);
      var $header = $this.parent().parent();
      var $project = $header.parent();
      var $content = $header.next();

      $this.children().toggleClass('active');
      $project.toggleClass('active');
      if ($project.hasClass('active')) {
        $header.addClass('active');
        $project.animate({
          height: $project.height() + $content.outerHeight(true) + 'px'
        }, 230);
        $content.effect('slide', {
          direction: 'up',
          mode: 'show',
          duration: 240
        });
      } else {
        $project.animate({
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

    $(".project-custom-js").sortable({
      helper: 'clone',
      revert: true,
      containment: ".projects",
      update: function(event, ui) {
        pidsArray = $(".project-custom-js").sortable("toArray");

        $.ajax({
          url: saveCustomOrderUrl,
          type: 'POST',
          data: {
            "_token": CSRFToken,
            "pids": pidsArray,

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
      var $project = $header.parent();
      // $project.prev().before(current);
      if ($this.hasClass('up-js')) {
        var $previousProject = $project.prev();
        if ($previousProject.length == 0) {
          return;
        }

        $previousProject.css('z-index', 999)
          .css('position', 'relative')
          .animate({
            top: $project.height()
          }, 300);
        $project.css('z-index', 1000)
          .css('position', 'relative')
          .animate({
            top: '-' + $previousProject.height()
          }, 300, function() {
            $previousProject.css('z-index', '')
              .css('top', '')
              .css('position', '');
            $project.css('z-index', '')
              .css('top', '')
              .css('position', '')
              .insertBefore($previousProject);

              pidsArray = $(".project-custom-js").sortable("toArray");

              $.ajax({
                  url: saveCustomOrderUrl,
                  type: 'POST',
                  data: {
                      "_token": CSRFToken,
                      "pids": pidsArray,

                  },
                  success: function(result) {}
              });
          });
      } else {
        var $nextProject = $project.next();
        if ($nextProject.length == 0) {
          return;
        }

        $nextProject.css('z-index', 999)
          .css('position', 'relative')
          .animate({
            top: '-' + $project.height()
          }, 300);
        $project.css('z-index', 1000)
          .css('position', 'relative')
          .animate({
            top: $nextProject.height()
          }, 300, function() {
            $nextProject.css('z-index', '')
              .css('top', '')
              .css('position', '');
            $project.css('z-index', '')
              .css('top', '')
              .css('position', '')
              .insertAfter($nextProject);

              pidsArray = $(".project-custom-js").sortable("toArray");

              $.ajax({
                  url: saveCustomOrderUrl,
                  type: 'POST',
                  data: {
                      "_token": CSRFToken,
                      "pids": pidsArray,

                  },
                  success: function(result) {}
              });
          });
      }
    });
  }
  

  function initializeFilters() {
    $('.sort-options-js a').click(function(e) { // clicked Custom, Alphabetical, or Archived
      e.preventDefault();

      var $this = $(this);
      var $content = $('.' + $this.attr('href').substring(1) + '-projects');

      clearSearch();
      clearFilterResults();

      // Toggle self animation and display corresponding content
      $this.addClass('active');
      $content.addClass('active');
    });
  }

  function initializePermissionsModal() {
    Kora.Modal.initialize();

    $('.request-permissions-js').click(function(e) {
      e.preventDefault();

      Kora.Modal.open();
    });

    $('.multi-select').chosen({
      width: '100%',
    });

    $('.submit-project-request-js').click(function(e) {
        e.preventDefault();

        $('.request-error-js').text('');

        if($('#request_project').val() != null)
            $('.request-project-form-js').submit();
        else
            $('.request-error-js').text('Please select a project');
    });
  }

  function initializeUserProfileModal() {
    Kora.Modal.initialize();
    var $modal = $('.user-profile-modal-js');

    $('.admin-name-js').click(function(e) {
      e.preventDefault();

      $('.modal-js').hide();
      $modal.show();

      $this = $(this);
      // Check if profile picture exists
      $modal.find('.profile-js').html("").css("top", "-63px");
      $.get($this.data('profile'))
          .done(function() {
            $modal.find('.profile-js').html('<img src="' + $this.data('profile') + '" alt="Profile Pic">');
          })
          .fail(function() {
            $modal.find('.profile-js').html('<i class="icon icon-user">').css("top", "-23px");;
          });
      $modal.find('.name-attr-js').html($this.data('name'));
      $modal.find('.username-attr-js').html($this.data('username'));
      $modal.find('.email-attr-js').html($this.data('email'));
      $modal.find('.organization-attr-js').html($this.data('organization'));
      $modal.find('.profile-link-js').attr('href', $this.data('profile-url'));

      Kora.Modal.open();
    });
  }
  
  function initializeUnarchive()
  {
    $(".unarchive-js").click(function() {
      // find PID
	  var a = 2;
      var active_cards = $(".project.card.active"); // unarchive button only shows on active cards
      var pid = -1;
      
      for (i = 0; i < active_cards.length; i++)
      {
        if ($.contains(active_cards[i], this)) // find which project card contains the link
        {
          pid = parseInt($(active_cards[i]).attr("id"));
          changeArchiveStatus(pid, false); // unarchive
          break;
        }
      }
    });
    
    function changeArchiveStatus(pid, archive)
    {
      var url = archiveURL.substring(0, archiveURL.length - 8) + pid.toString() + "/archive"; // get rid of /archive (last part of URL), add pid, add /archive again
      
      var myForm = document.createElement('form');
      myForm.setAttribute('action', url);
      myForm.setAttribute('method', 'post');
      myForm.setAttribute('hidden', 'true');
      
      var myInput = document.createElement('input');
      myInput.setAttribute('type', 'number');
      myInput.setAttribute('name', 'archive');
      myInput.setAttribute('value', archive ? 0 : 1); // send 0 to archive, send 1 to restore
      
      var myInput2 = document.createElement('input');
      myInput2.setAttribute('type', 'text');
      myInput2.setAttribute('name', '_token');
      myInput2.setAttribute('value', CSRFToken);
      
      myForm.appendChild(myInput);
      myForm.appendChild(myInput2);
      document.body.appendChild(myForm);
      myForm.submit();
    }
  }
  
  function initializeProjectCardEllipsifying()
  {
    function adjustProjectCardTitle()
    {
	  var alphabetical = false;
	  var custom = false;
	  var archived = false;
	  
	  if ($(".active-projects").hasClass("active"))
	  {
		alphabetical = true;
		var cards = $($(".active-projects").find(".project.card"));
	  }
	  else if ($(".custom-projects").hasClass("active"))
	  {
		custom = true;
		var cards = $($(".custom-projects").find(".project.card"));
	  }
	  else if ($(".inactive-projects").hasClass("active"))
	  {
		archived = true;
		var cards = $($(".inactive-projects").find(".project.card"));
	  }
      
      for (i = 0; i < cards.length; i++)
      {	
        var card = $(cards[i]);
        var name_span = $(card.find($(".name")));
        var arrow = $(card.find($(".icon-arrow-right")));
        var chevron = $(card.find($(".icon-chevron")));
		var up_arrow = $(card.find($(".move-action-js.up-js")));
		var down_arrow = $(card.find($(".move-action-js.down-js")));
        
        var card_width = card.width();
        var arrow_width = arrow.length ? arrow.outerWidth() : 0; // if arrow is valid jquery object
		var up_arrow_width = up_arrow.length ? up_arrow.outerWidth() : 0;
		var down_arrow_width = down_arrow.length ? down_arrow.outerWidth() : 0;
        var chevron_width = chevron.outerWidth(); // all types of project cards have chevrons
        var left_padding = custom ? 0 : 20; // custom projects provide padding from element other than name_span
		var extra_padding = 10;
        
        var title_width = (card_width - left_padding) - (arrow_width + up_arrow_width + down_arrow_width + chevron_width + extra_padding);
        if (title_width < 0) {title_width = 0;}
        
        name_span.css("text-overflow", "ellipsis");
        name_span.css("white-space", "nowrap");
        name_span.css("overflow", "hidden");
        name_span.css("max-width", title_width + "px");
      }
    }
  	
    $(window).resize(function()
    {
      adjustProjectCardTitle();
    });
	
    $(document).ready(function()
    {
      adjustProjectCardTitle();
    });
	
	// Recalculate ellipses when switching project types
	$("[href='#custom'], [href='#active'], [href='#inactive']").click(function() { adjustProjectCardTitle(); });
  }

  initializeCustomSort();
  initializeFilters();
  initializeSearch();
  initializePermissionsModal();
  initializeUserProfileModal();
  initializeUnarchive();
  initializeProjectCardEllipsifying();
}
