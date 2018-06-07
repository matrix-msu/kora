var Kora = Kora || {};
Kora.Tokens = Kora.Tokens || {};

Kora.Tokens.Index = function() {
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

            $('.token.card').each(function() {
                if($(this).hasClass(activeSection))
                    $(this).removeClass('hidden');
            });
        });

        $('.search-js i, .search-js input').keyup(function() {
            var searchVal = $(this).val().toLowerCase();
            var activeSection = $('.option.active').first().attr('href').substring(1);

            $('.token.card').each(function() {
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

    function initializeTokenModals() {
        Kora.Modal.initialize();

        $('.create-token-js').click(function(e) {
            e.preventDefault();

            Kora.Modal.open($('.create-token-modal-js'));
        });

        $('.edit-token-js').click(function(e) {
            e.preventDefault();

            indexVal = $('#token_edit_modal_id');
            titleVal = $('#token_edit_modal_name');

            searchVal = $('#token_edit_modal_search');
            createVal = $('#token_edit_modal_create');
            editVal = $('#token_edit_modal_edit');
            deleteVal = $('#token_edit_modal_delete');

            tokenDiv = $(this).parents('.token').first();
            titleSpan = tokenDiv.find('.name').first();

            indexVal.val(tokenDiv.attr('id'));

            //TODO:: close, but not yet
            
			// add checkmark if needed, also remove if needed
			$('.search-checkbox-js').prop('checked', tokenDiv.hasClass('search'));
			$('.create-checkbox-js').prop('checked', tokenDiv.hasClass('create'));
			$('.edit-checkbox-js').prop('checked', tokenDiv.hasClass('edit'));
			$('.delete-checkbox-js').prop('checked', tokenDiv.hasClass('delete'));

            titleVal.val(titleSpan.text());

            Kora.Modal.open($('.edit-token-modal-js'));
        });

        $('.delete-token-js').click(function(e) {
            e.preventDefault();
            indexVal = $('#token_delete_modal_id');

            tokenDiv = $(this).parents('.token').first();

            indexVal.val(tokenDiv.attr('id'));

            Kora.Modal.open($('.delete-token-modal-js'));
        });

        $('.add-projects-js').click(function(e) {
            e.preventDefault();
            indexVal = $('#add_projects_modal_id');
            projDiv = $('#add_token_projects');
            projDiv.html(""); //clears old options

            tokenDiv = $(this).parents('.token').first();
            var tid = tokenDiv.attr('id')
            indexVal.val(tid);

            //GET LIST OF UNASSIGNED TOKENS
            $.ajax({
                //Same method as deleteProject
                url: unProjectUrl,
                type: 'POST',
                data: {
                    "_token": CSRFToken,
                    "token": tid
                },
                success: function(projects){
                    var phtml = '';
                    projects.forEach(function(project, index) {
                        phtml += '<option value='+project['pid']+' token="'+tid+'">'+project['name']+'</option>';
                    });
                    projDiv.html(phtml);
                    projDiv.trigger("chosen:updated"); //refresh options

                    Kora.Modal.open($('.add-projects-modal-js'));
                }
            });
        });

        $('.token-project-delete-js').click(function(e) {
            e.preventDefault();
            indexVal = $('#token_delete_project_modal_id');
            pNameVal = $('#token_delete_project_modal_name');
            projectVal = $('#token_delete_project_modal_pid');

            var pid = $(this).attr('pid');
            projectVal.val(pid);
            var token = $(this).attr('token');
            indexVal.val(token);

            var pname = $(this).attr('pname');
            pNameVal.text('Are you sure you want to remove project access for '+pname+' from this Token?');

            Kora.Modal.open($('.delete-token-project-modal-js'));
        });

        $('.multi-select').chosen({
            width: '100%',
        });
    }
	
	function initializeTokenCardEllipsifying()
	{
		function adjustTokenCardTitle()
		{
			var cards = $($(".token-selection-js").find(".token.card"));
			
			for (i = 0; i < cards.length; i++)
			{	
				var card = $(cards[i]);
				var name_span = $(card.find($(".name")));
				var chevron_text = $(card.find($(".chevron-text")));
				var chevron_icon = $(card.find($(".icon-chevron")));
				
				var card_width = card.width();
				var chevron_text_width = chevron_text.outerWidth();
				var chevron_icon_width = chevron_icon.outerWidth();
				var left_padding = 20; // padding within card
				var extra_padding = 10;
				
				var title_width = (card_width - left_padding) - (chevron_text_width + chevron_icon_width + extra_padding);
				if (title_width < 0) {title_width = 0;}
				
				name_span.css("text-overflow", "ellipsis");
				name_span.css("white-space", "nowrap");
				name_span.css("overflow", "hidden");
				name_span.css("max-width", title_width + "px");
			}
		}
		
		$(window).resize(function()
		{
			adjustTokenCardTitle();
		});
		
		$(document).ready(function()
		{
			adjustTokenCardTitle();
			setTimeout(function(){ adjustTokenCardTitle(); adjustTokenCardTitle(); }, 1); // necessary for some reason
		});
		
		// Recalculate ellipses when switching tabs
		$("[href='#all'], [href='#search'], [href='#create'], [href='#edit'], [href='#delete']").click(function() {
			adjustTokenCardTitle();
			setTimeout(function(){ 
				adjustTokenCardTitle();
				setTimeout(function(){ adjustTokenCardTitle(); }, 10);
			}, 10);
		});
	}

    initializeFilters();
    initializeSearch();
    initializeToggle();
    initializeTokenModals();
	initializeTokenCardEllipsifying();
}
