var Kora = Kora || {};
Kora.Revisions = Kora.Revisions || {};

Kora.Revisions.Index = function() {
	$('.multi-select').chosen({
		width: '100%'
	});

	function initializeOptionDropdowns() {
		$('.option-dropdown-js').chosen({
			disable_search_threshold: 10,
			width: 'auto'
		}).change(function() {
            var type = $(this).attr('id');
            if (type === 'page-count-dropdown') {
                var order = getURLParameter('order');
                window.location = window.location.pathname + "?page-count=" + $(this).val() + (order ? "&order=" + order : '');
            } else if (type === 'order-dropdown') {
                var pageCount = getURLParameter('page-count');
                window.location = window.location.pathname + "?order=" + $(this).val() + (pageCount ? "&page-count=" + pageCount : '');
            }
        });
    }
    
    function initializePaginationShortcut() {
        $('.page-link.active').click(function(e) {
            e.preventDefault();

            var $this = $(this);
            var maxInput = $this.siblings().last().text()
            $this.html('<input class="page-input" type="number" min="1" max="'+ maxInput +'">');
            var $input = $('.page-input');
            $input.focus();
            $input.on('blur keydown', function(e) {
                if (e.key !== "Enter" && e.key !== "Tab") return;
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
        })
    }

	function initializeToggle() {
		$('.revision-toggle-js').click(function(e) {
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

        $('.expand-fields-js').on('click', function(e) {
            e.preventDefault();
            $('.card:not(.active) .revision-toggle-js').click();
        });

        $('.collapse-fields-js').on('click', function(e) {
            e.preventDefault();
            $('.card.active .revision-toggle-js').click();
        });
	}

    initializeOptionDropdowns();
    initializePaginationShortcut();
    initializeToggle();
}

Kora.Revisions.Index();