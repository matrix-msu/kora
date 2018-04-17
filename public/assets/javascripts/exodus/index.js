var Kora = Kora || {};
Kora.Exodus = Kora.Exodus || {};

Kora.Exodus.Index = function() {

    $('.multi-select').chosen({
        width: '100%',
    });

    function initializeGetProjectList() {
        $('.get-projects-js').click(function (e) {
            e.preventDefault();

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

    initializeGetProjectList();
    initializeFormSubmit();
}