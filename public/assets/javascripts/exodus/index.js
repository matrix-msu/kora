var Kora = Kora || {};
Kora.Exodus = Kora.Exodus || {};

Kora.Exodus.Index = function() {

    $('.multi-select').chosen({
        width: '100%',
    });

    function initializeGetProjectList() {
        $('.get-projects-js').click(function (e) {
            e.preventDefault();
			
			if (checkValidation($('.db-host-js')) & checkValidation($('.db-user-js'))
				& checkValidation($('.db-name-js')) & checkValidation($('.db-pass-js')))
			{
				var passed = true;
			}
			
			if (!passed) return;

            var databaseLink = $('.database-link');
            var databaseSection = $('.exodus-database');
            var projectsLink = $('.projects-link');
            var projectsSection = $('.exodus-projects');

            $.ajax({
                url: getProjectListUrl,
                method: 'POST',
                data: {
                    "_token": CSRFToken,
                    "host": $('.db-host-js').val(),
                    "user": $('.db-user-js').val(),
                    "name": $('.db-name-js').val(),
                    "pass": $('.db-pass-js').val()
                },
                success: function (data) {
					console.log("Exodus request success");
					console.log(data);
                    databaseLink.removeClass('active');
                    projectsLink.addClass('active');
                    projectsLink.addClass('underline-middle');

                    databaseSection.addClass('hidden');
                    projectsSection.removeClass('hidden');

                    var projectsSelector = $('.project-select-js');

                    for (var pid in data) {
                        var option = $("<option>").val(pid).text(data[pid]);

                        projectsSelector.append(option.clone());
                    }

                    projectsSelector.trigger("chosen:updated");
                },
				error: function(data) {
					console.log("Exodus request failed");
					console.log(data);
					console.log(data.responseJSON.response);
				}
            });
        });
    }

    function initializeFormSubmit() {
        $(".k2-form-js").submit(function (e) {
            //Disable btn to let user know somethings happening
            $('.set-disabled-js').addClass("disabled");
        });
    }
	
	function initializeValidation() {
		$(".db-host-js, .db-name-js, .db-user-js, .db-pass-js").blur(function(e) {
			checkValidation($(this));
		});
	}
	
	function checkValidation(input) {
		var text = input.val();
		if (text == "") {
			input.prev().text("This field is required");
			return false;
		} else {
			input.prev().text("");
			return true;
		}
	}

    initializeGetProjectList();
    initializeFormSubmit();
	initializeValidation();
}