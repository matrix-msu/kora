var Kora = Kora || {};
Kora.Tokens = Kora.Tokens || {};

Kora.Tokens.Index = function() {
    function addProject(id) {
        var selector = $('#dropdown' +id+ ' option:selected');

        var pid = selector.attr('id');
        var token = selector.attr('token');

        $.ajax({
            //Same method as deleteProject
            url: addProjectUrl,
            type: 'PATCH',
            data: {
                "_token": CSRFToken,
                "pid": pid,
                "token": token
            },
            success: function(){
                location.reload();
            }
        });
    }

    function deleteProject(pid, token) {
        $.ajax({
            //We manually create the link in a cheap way because the JS isn't aware of the pid until runtime
            //We pass in a blank project to the action array and then manually add the id
            url: deleteProjectUrl,
            type: 'PATCH',
            data: {
                "_token": CSRFToken,
                "pid": pid,
                "token": token
            },
            success: function(){
                location.reload();
            }
        });
    }

    function deleteToken(id) {
        var encode = $('<div/>').html("{{trans('tokens_index.areyousure')}}").text();
        var response = confirm(encode + '?');
        if (response) {
            $.ajax({
                url: deleteTokenUrl,
                type: 'DELETE',
                data: {
                    "_token": CSRFToken,
                    "id": id
                },
                success: function(){
                    location.reload();
                }
            });
        }
    }

    function clearSearch() {
        $('.search-js .icon-cancel-js').click();
    }

    function initializeSearch() {
        //TODO:: Make search work...
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
        });
    }

    function clearFilterResults() {
        // Clear previous filter results
        $('.sort-options-js a').removeClass('active');
        $('.token').addClass('hidden');
    }


    function initializeFilters() {
        $('.sort-options-js a').click(function(e) {
            e.preventDefault();

            var $this = $(this);
            var $content = $('.token.'+ $this.attr('href').substring(1));

            clearSearch();
            clearFilterResults();

            // Toggle self animation and display corresponding content
            $this.addClass('active');
            $content.removeClass('hidden');
        });
    }

    function initializeToggle() {
        // Initialize card toggling
        $('.token-toggle-js').click(function(e) {
            e.preventDefault();

            var $this = $(this);
            var $header = $this.parent().parent();
            var $token = $header.parent();
            var $content = $header.next();

            $this.children().toggleClass('active');
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

    // function initializePermissionsModal() {
    //     Kora.Modal.initialize();
    //
    //     $('.request-permissions-js').click(function(e) {
    //         e.preventDefault();
    //
    //         Kora.Modal.open();
    //     });
    //
    //     $('.multi-select').chosen({
    //         width: '100%',
    //     });
    // }

    initializeFilters();
    initializeSearch();
    initializeToggle();
    //initializePermissionsModal();
}
