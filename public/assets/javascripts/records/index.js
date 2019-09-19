var Kora = Kora || {};
Kora.Records = Kora.Records || {};

Kora.Records.Index = function() {
    var searchMade = false;
    searchMade = window.localStorage.getItem('searchMade');
    if (searchMade) {
      $('.try-another-js').parent().removeClass('hidden');
      window.localStorage.clear();
    }

    $('.single-select').chosen({
        allow_single_deselect: true,
        disable_search_threshold: 10,
        width: '100%',
    });

    $('.multi-select').chosen({
        width: '100%',
    });

    function initializeSelectAddition() {
        $('.chosen-search-input').on('keyup', function(e) {
            var container = $(this).parents('.chosen-container').first();

            if (e.which === 13 && (container.find('li.no-results').length > 0 || container.find('li.active-result').length == 0)) {
                var option = $("<option>").val(this.value.trim()).text(this.value.trim());

                var select = container.siblings('.modify-select').first();

                select.append(option);
                select.find(option).prop('selected', true);
                select.trigger("chosen:updated");
            }
        });
    }

    function initializeOptionDropdowns() {
        $('.option-dropdown-js').chosen({
            disable_search_threshold: 10,
            width: 'auto'
        }).change(function() {
            var type = $(this).attr('id');
            if(type === 'page-count-dropdown') {
                var order = getURLParameter('order');
                window.location = window.location.pathname + "?page-count=" + $(this).val() + (order ? "&order=" + order : '');
            } else if (type === 'order-dropdown') {
                var pageCount = getURLParameter('page-count');
                window.location = window.location.pathname + "?order=" + $(this).val() + (pageCount ? "&page-count=" + pageCount : '');
            }
        });

        $('.results-option-dropdown-js').chosen({
            disable_search_threshold: 10,
            width: 'auto'
        }).change(function() {
            var type = $(this).attr('id');

            var subBaseUrl = "";
            var keywords = $('.keywords-get-js').val();
            subBaseUrl += "?keywords=" + encodeURIComponent(keywords);
            var method = $('.method-get-js').val();
            subBaseUrl += "&method=" + method;
            if($('.forms-get-js').length) {
                var forms = $('.forms-get-js').val();
                for(var f=0;f<forms.length;f++) {
                    subBaseUrl += "&forms%5B%5D=" + forms[f];
                }
            }
            if($('.projects-get-js').length) {
                var projs = $('.projects-get-js').val();
                for(var p=0;p<projs.length;p++) {
                    subBaseUrl += "&projects%5B%5D=" + projs[p];
                }
            }

            if(type === 'page-count-dropdown') {
                var order = getURLParameter('order');
                window.location = window.location.pathname + subBaseUrl + "&page-count=" + $(this).val() + (order ? "&order=" + order : '');
            } else if (type === 'order-dropdown') {
                var pageCount = getURLParameter('page-count');
                window.location = window.location.pathname + subBaseUrl + "&order=" + $(this).val() + (pageCount ? "&page-count=" + pageCount : '');
            }
        });
    }

    function initializePaginationShortcut() {
        $('.page-link.active').click(function(e) {
            e.preventDefault();

            var placeholder = parseInt($('.page-link.active').next('.page-link').html()) - 1
            if (isNaN(placeholder)) {
              placeholder = parseInt($('.page-link.active').prev('.page-link').html()) + 1
              if (isNaN(placeholder)) {
                placeholder = 1
              }
            }

            var $this = $(this);
            var maxInput = $this.siblings().last().text()
            $this.html('<input class="page-input" type="number" min="1" max="'+ maxInput +'" placeholder="' + placeholder + '">');
            var $input = $('.page-input');
            $input.focus();
            //$input.on('blur keydown', function(e) {
            $input.on('keydown', function(e) {
                if (e.key !== "Enter" && e.key !== "Tab") {
                  // var get = $('.page-input').attr('placeholder');
                  // $('.page-input').remove();
                  // $('.page-link.active').text(''+get+'');
                  return;
                }
                if ($input[0].checkValidity()) {
                    var url = window.location.toString();
                    if (url.includes('page=')) {
                        window.location = url.replace(/page=\d*/, "page="+$input.val());
                    } else {
                        var queryVar = url.includes('?') ? '&' : '?';
                        window.location = url + queryVar + "page=" + $input.val();
                    }
                }
            });
            $input.blur(function () {
              var get = $('.page-input').attr('placeholder');
              $('.page-input').remove();
              $('.page-link.active').text(''+get+'');
            });
        })
    }

    function initializeSearchInteractions() {
        $('.close-advanced-js').hide();

        $('.submit-search-js').click(function(e) {
            e.preventDefault();

            keyVal = $('.keywords-get-js');
            formVal = $('.forms-get-js');

            if(formVal.length && formVal.val()==null) {
                formVal.siblings('.error-message').text('Select something to search through');
            } else {
                window.localStorage.setItem('searchMade', true);
                $('.keyword-search-js').submit();
            }
        });

        $('.keywords-get-js').on('keyup', function(e) {
            if (e.which === 13) {
                keyVal = $('.keywords-get-js');
                formVal = $('.forms-get-js');

                if(formVal.length && formVal.val()==null) {
                    formVal.siblings('.error-message').text('Select something to search through');
                } else {
                    window.localStorage.setItem('searchMade', true);
                    $('.keyword-search-js').submit();
                }
            }
        });
    }

    function initializeToggle() {
        // Initialize card toggling
        $('.record-toggle-js').click(function(e) {
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
                }, function () {
                  $token.css('height', '');
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

        $('.expand-fields-js').on('click', function(e) {
            e.preventDefault();
            $('.card:not(.active) .record-toggle-js').click();
        });

        $('.collapse-fields-js').on('click', function(e) {
            e.preventDefault();
            $('.card.active .record-toggle-js').click();
        });
    }

    function initializeDeleteRecord() {
        Kora.Modal.initialize();

        $('.delete-record-js').click(function (e) {
            e.preventDefault();

            var $modal = $('.delete-record-modal-js');

            var url = deleteRecordURL+'/'+$(this).attr('rid');
            $('.delete-record-form-js').attr('action', url);

            var revAssocCount = $(this).attr('rev-assoc-count');
            if(revAssocCount>0)
                $('.rev-assoc-warning-js').text('Are you sure you want to delete this Record?' +
                    'WARNING: There are '+revAssocCount+' other records that associate to this record!');

            Kora.Modal.open($modal);
        });
    }

    function initializeScrollTo() {
        if($('.scroll-to-here-js').length) {
            $('html, body').animate({
                scrollTop: $(".scroll-to-here-js").offset().top
            }, 1000);
        }
    }

    function initializeSearchValidation() {
        $('.forms-get-js').on('chosen:hiding_dropdown', function(e) {
            value = $(this).val();

            if(value==null) {
                $(this).siblings('.error-message').text('Select something to search through');
            } else {
                $(this).siblings('.error-message').text('');
            }
        });
    }

    function displayKeywords () {
        $('.back-to-search, .to-top, .try-another-js').click( function (e) {
            console.log("scroll back");
            e.preventDefault();
            $('html, body').animate({
                scrollTop: 0
            }, 1500);
        });

      var keywords = $('.keywords-get-js').val();
      if (keywords != null && keywords != '') {
        keywords = keywords.split(/\s+/);
        keywords.forEach(function(keyword){
          $('ul.keywords').append('<li class="keyword"><span>' + keyword + '</span><a class="keyword-close"></a></li>');
        });
        $('ul.keywords').append('<li class="back-to-search try-another-js"><span>Back to Search</span><i class="icon icon-arrow-up"></i></li>');

        $('.keyword-close').click(function(){
          $(this).parent().remove();
          var find = $(this).siblings('span').text();
          if (keywords.indexOf(find) >= 0) {
            var index = keywords.indexOf(find);
            keywords.splice(index, 1);
            newKeys = keywords.toString();
            newKeys = newKeys.replace(',',' ');
            $('.keywords-get-js').val(newKeys);
            window.localStorage.setItem('searchMade', true);
            $('.submit-search-js').trigger('click');
          }
        });
      }
    }

    function initializeAssociatorCardToggle () {
        $('.assoc-card-toggle-js').click(function (e) {
            e.preventDefault();

            let $card = $(this).parent().parent().parent();
            let $cardBody = $card.find('.body');

            $(this).children().toggleClass('active');

            if ($(this).children().hasClass('active')) {
                //$card.css('height', '');
                $card.animate({
                    height: $card.height() + $cardBody.outerHeight() + 'px'
                }, 230);
                $cardBody.effect('slide', {
                    direction: 'up',
                    mode: 'show',
                    duration: 240
                });
            } else {
                $card.animate({
                    height: '49px'
                }, 230);
                $cardBody.effect('slide', {
                    direction: 'up',
                    mode: 'hide',
                    duration: 240
                });
            }
        });
    }

    function initializeSearchTabs () {
        // handles switching tabs
        $('a.display-js').click(function (e) {
            e.preventDefault();

            if ( !$(this).hasClass('selected') ) {
                $('.display-js').removeClass('selected');
                $(this).addClass('selected');
                $('section.display-js').addClass('hidden');
                $('section.display-js')[$(this).index()].classList.remove('hidden');
                if ( $(this).children('span').text() == '0' ) {
                    $('section.display-js')[$(this).index()].children[3].classList.remove('hidden');
                }
            }
        });

        // toggles cards
        $('.project-toggle-js, .form-toggle-js, .field-toggle-js').click(function(e) {
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
    }

    initializeSelectAddition();
    initializeOptionDropdowns();
    initializePaginationShortcut();
    initializeSearchInteractions();
    initializeToggle();
    initializeDeleteRecord();
    initializeScrollTo();
    initializeSearchValidation();
    displayKeywords();
    initializeAssociatorCardToggle();
    initializeSearchTabs();
    Kora.Records.Modal();
    Kora.Fields.TypedFieldDisplays.Initialize();
}
