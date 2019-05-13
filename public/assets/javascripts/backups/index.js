var Kora = Kora || {};
Kora.Backups = Kora.Install || {};

Kora.Backups.Index = function() {

    var currentLabel = "";

    function initializeBackupToggles() {
        $('.toggle-by-name').click(function (e) {
            e.preventDefault();

            $this = $(this);
            $this.addClass('active');
            $this.siblings().removeClass('active');

            $active = $this.attr("href");
            if($active == "#backups") {
                $('.backups-section').removeClass('hidden');
                $('.filerestore-section').addClass('hidden');
            } else {
                $('.filerestore-section').removeClass('hidden');
                $('.backups-section').addClass('hidden');
            }
        });
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

            $('.backup.card').each(function() {
                $(this).removeClass('hidden');
            });
        });

        $('.search-js i, .search-js input').keyup(function() {
            var searchVal = $(this).val().toLowerCase();

            $('.backup.card').each(function() {
                var name = $(this).find('.name').first().text().toLowerCase();

                if(name.includes(searchVal))
                    $(this).removeClass('hidden');
                else
                    $(this).addClass('hidden');
            });
        });
    }

    function initializeOptionDropdowns() {
        $('.option-dropdown-js').chosen({
            disable_search_threshold: 10,
            width: 'auto'
        }).change(function() {
            window.location = window.location.pathname + "?order=" + $(this).val();
        });
    }

    function initializeModals() {
        Kora.Modal.initialize();

        $('.create-backup-js').click(function(e) {
            e.preventDefault();

            Kora.Modal.open($('.create-backup-modal-js'));
        });
    }

    function initializeToggle() {
        // Initialize card toggling
        $('.backup-toggle-js').click(function(e) {
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

    function initializeDeleteBackupModal() {
        Kora.Modal.initialize();

        $('.delete-backup-open-js').click(function(e) {
            e.preventDefault();

            currentLabel = $(this).attr('backup-label');
            Kora.Modal.open($('.delete-backup-modal-js'));
        });

        $('.delete-backup-js').click(function(e) {
            $.ajax({
                url: deleteBackupUrl,
                type: 'POST',
                data: {
                    "_token": CSRFToken,
                    "_method": 'delete',
                    "label" : currentLabel
                },
                success: function (result) {
                    location.reload();
                }
            });
        });
    }

    function initializeRestoreBackup() {
        $('.restore-backup-js').click(function(e) {
            e.preventDefault();

            var restoreForm = $(this).parent('.restore-form-js');
            restoreForm.submit();
        });
    }

    function headerTabs () {
      var isOverflow = document.querySelector('.content-sections-scroll');
      var isAppended = false
      var scrollPos

      window.setInterval(function() {
        if (isOverflow.offsetWidth < isOverflow.scrollWidth && isAppended === false) {
          $('<i class="icon icon-chevron tabs-right"></i>').appendTo('.content-sections');
          $('<i class="icon icon-chevron tabs-left hidden"></i>').appendTo('.content-sections');
          isAppended = true
        } else if (isOverflow.offsetWidth == isOverflow.scrollWidth && isAppended === true) {
          $('.tabs-right').remove();
          $('.tabs-left').remove();
          isAppended = false
        }
      }, 200);

      $('.content-sections').on('click', '.tabs-left', function (e) {
        e.stopPropagation();
        scrollPos = $('.content-sections-scroll').scrollLeft();
        scrollPos = scrollPos - 250
        scroll ()
      });

      $('.content-sections').on('click', '.tabs-right', function (e) {
        e.stopPropagation();
        scrollPos = $('.content-sections-scroll').scrollLeft();
        scrollPos = scrollPos + 250
        scroll ()
      });

      var scrollWidth
      var viewWidth
      var maxScroll
      function scroll () {
        scrollWidth = isOverflow.scrollWidth
        viewWidth = isOverflow.offsetWidth
        maxScroll = scrollWidth - viewWidth
        if (scrollPos > maxScroll) {
          scrollPos = maxScroll
        } else if (scrollPos < 0) {
          scrollPos = 0
        }
        $('.content-sections-scroll').animate({
          scrollLeft: scrollPos
        }, 80);
      }

      $('.content-sections-scroll').scroll(function () {
          var fb = $('.content-sections-scroll');
          if (fb.scrollLeft() + fb.innerWidth() >= fb[0].scrollWidth) {
              $('.tabs-right').addClass('hidden');
          } else {
              $('.tabs-right').removeClass('hidden');
          }
          if (fb.scrollLeft() <= 20) {
              $('.tabs-left').addClass('hidden');
          } else {
              $('.tabs-left').removeClass('hidden');
          }
      });
    }

	function initializeValidation() {
	  function error(input, error_message) {
	    $(input).prev().text(error_message);
	    $(input).addClass("error"); // applies the error border styling
	  }

	  function success(input) { // when validation is passed on an input
	    $(input).prev().text("");
	    $(input).removeClass("error");
	  }

	  function validateFileName() {
	    var filename_input = $("input[name='backupLabel']");
		var filename = filename_input.val();

		if (filename == "") {
		  error(filename_input, "This field is required");
		  return false;
		}

		for (var i = 0; i < filename.length; i++) {
		  var code = filename.charAt(i).charCodeAt();

		  if (!(code >= 48 && code <= 57) && !(code >= 65 && code <= 90) && !(code >= 97 && code <= 122)) {
			error(filename_input, "Invalid characters in name");
		    return false;
		  }
		}

		success(filename_input);
		return true;
	  }

	  function validateBackupOptions() {
	    var metadata_checkbox = $("input[name='backupData']");
		var files_checkbox = $("input[name='backupFiles']");
		var error_span = $("label[for=backupData]").next($("span"));

		if (!metadata_checkbox.prop("checked") && !files_checkbox.prop("checked"))
		{
			error_span.text("Select at least one backup option");
			files_checkbox.addClass("error");
			return false;
		}

		error_span.text('');
		files_checkbox.removeClass("error");
		return true;
	  }

	  $("input[name='backupLabel']").blur(function(e) {
	    validateFileName();
	  })

	  $("input[value='Start Backup']").click(function(e) {
		// evaluate before if-statement to avoid short circuit
		var valid_name = validateFileName();
		var valid_options = validateBackupOptions();

	    if (!valid_name || !valid_options) {
	      e.preventDefault();
	    }
	  })
	}


    initializeSearch();
    initializeBackupToggles();
    initializeOptionDropdowns();
    initializeModals();
    initializeToggle();
    initializeDeleteBackupModal();
    initializeRestoreBackup();
	initializeValidation();
    headerTabs();
}
