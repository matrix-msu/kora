var Kora = Kora || {};
Kora.Tokens = Kora.Tokens || {};

Kora.Tokens.Index = function() {
    function clearSearch() {
        $('.search-js .icon-cancel-js').click();
    }
	
	function initializeValidation() {
      $('.validate-token-js').on('click', function(e) { // whole form validation on submit
        var $this = $(this);
        
        e.preventDefault();
		
		// start client-side js validation
		var js_validated = true;
		
		if (getTotalCreateTokenCheckboxesSelected() == 0)
		{
			$("#token-checkbox-warning").text("At least one Token Type must be selected");
			js_validated = false;
		}
		else
		{
			$("#token-checkbox-warning").text("");
		}
		
		let name_text = $("#token_name")[0].value;
		if (name_text.length < 3)
		{
			$("#token-name-warning").text(name_text.length == 0 ? "The name field is required" : "The name must be at least 3 characters");
			js_validated = false;
		}
		else
		{
			$("#token-name-warning").text("");
		}
		
		if (!js_validated)
		{
			$(".validate-token-js").addClass("btn-faded");
			return;
		}
		
		// passed client-side js validation
		
		var values = {
			_token: CSRFToken,
			token_name: document.getElementById("token_name").value,
		};
		
		var token_search = $(".search-token-create-js").prop("checked");
		if (token_search) {values["token_search"] = 1;}
		
		var token_create = $(".create-token-create-js").prop("checked");
		if (token_create) {values["token_create"] = 1;}
		
		var token_edit = $(".edit-token-create-js").prop("checked");
		if (token_edit) {values["token_edit"] = 1;}
		
		var token_delete = $(".delete-token-create-js").prop("checked");
		if (token_delete) {values["token_delete"] = 1;}
		
		var choice_ids = {};
		$("#token_projects").find("option").each(function() {
			choice_ids[$(this).text()] = this.value;
		});
		
		$($("#token_projects_chosen").find(".chosen-choices")).find(".search-choice").each(function() {
			if (values.token_projects == null) {values.token_projects = [];}
			
			values.token_projects.push(choice_ids[$(this).eq(0).text()]);
		});
		
		console.log("passed clientside validation");
      
	    // server-side whole-form validation
        $.ajax({
          url: create_url,
          method: 'POST',
          data: values,
          success: function(data) {
            alert("validation and creation successful");
			location.reload();
          },
          error: function(err) {
            alert("validation failure or creation failure");
          }
        });
      });
	  
	  $('.text-input, .text-area').on('blur', function(e) { // real-time validation
        if (this === $("#token_name")[0])
		{
			var text = document.getElementById("token_name").value;
			if (text.length < 3)
			{
				if (text.length == 0) $("#token-name-warning").text("The name field is required");
				else $("#token-name-warning").text("The name must be at least 3 characters");
			}
			else
			{
				$("#token-name-warning").text("");
			}
		}
		
		if (create_is_validated())
		{
			$(".validate-token-js").removeClass("btn-faded");
		}
		else
		{
			$(".validate-token-js").addClass("btn-faded");
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
			
			// reset all the checkboxes
			$(".search-token-create-js").prop("checked", false);
			$( ".create-token-create-js").prop("checked", false); 
			$( ".edit-token-create-js").prop("checked", false);
			$( ".delete-token-create-js").prop("checked", false);
			
			// reset token name input
			$("#token_name").val("");
			
			// reset all validation warnings
			$("#token-checkbox-warning").text("");
			$("#token-name-warning").text("");
			
			// reset create button faded
			$(".validate-token-js").removeClass("btn-faded");
			
            Kora.Modal.open($('.create-token-modal-js'));
        });
		
		// for editing token
		function getTotalEditTokenCheckboxesSelected()
		{
			return Number($( ".search-checkbox-js" ).prop("checked")) +
			Number($( ".create-checkbox-js" ).prop("checked")) + 
			Number($( ".edit-checkbox-js" ).prop("checked")) + 
			Number($( ".delete-checkbox-js" ).prop("checked"));
		}
		
		
		$(".search-checkbox-js, .create-checkbox-js, .edit-checkbox-js, .delete-checkbox-js").click(function(e)
		{
			// do not allow user to select zero token options
			if (getTotalEditTokenCheckboxesSelected() == 0 && !$(this).prop("checked"))
				e.preventDefault();
		});
		
		// Create Token stuff
		$(".search-token-create-js, .create-token-create-js, .edit-token-create-js, .delete-token-create-js").click(function(e)
		{
			let total_selected = getTotalCreateTokenCheckboxesSelected();
			
			if (total_selected == 0 && !$(this).prop("checked"))
			{
				e.preventDefault();
			}
			
			if (total_selected > 0)
			{
				$("#token-checkbox-warning").text("");
			}
			
			if (create_is_validated())
			{
				$(".validate-token-js").removeClass("btn-faded");
			}
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
            
			// apply correct checkmark state
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
	
	function getTotalCreateTokenCheckboxesSelected()
	{
		return Number($( ".search-token-create-js" ).prop("checked")) +
		Number($( ".create-token-create-js" ).prop("checked")) + 
		Number($( ".edit-token-create-js" ).prop("checked")) + 
		Number($( ".delete-token-create-js" ).prop("checked"));
	}
	
	function create_is_validated()
	{
		//console.log("checked: " + getTotalCreateTokenCheckboxesSelected());
		//console.log("text : " + $("#token_name")[0].value);
		return ($("#token_name")[0].value.length >= 3 && getTotalCreateTokenCheckboxesSelected() > 0);
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

	initializeValidation();
    initializeFilters();
    initializeSearch();
    initializeToggle();
    initializeTokenModals();
	initializeTokenCardEllipsifying();
}
