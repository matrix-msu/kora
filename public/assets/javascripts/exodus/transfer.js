var Kora = Kora || {};
Kora.Exodus = Kora.Exodus || {};

Kora.Exodus.Transfer = function() {

    var stopThePress = false; //Variable to show whether we are still in progress
    var progressText = $(".progress-text-js");
    var progressFill = $(".progress-fill-js");

    function initializeMigrateProjects() {
        //Begin the process
        $.ajax({
            url: startExodusUrl,
            method:'POST',
            data: {
                "_token": CSRFToken,
                "host": host, "user": user,
                "name": name, "pass": pass,
                "migrateUsers": migrateUsers,
                "migrateTokens": migrateTokens,
                "projects": projects,
                "filePath": filePath
            },
            success: function() {
                //Main part of process is over, all that's left is associations
                stopThePress = true;
                progressFill.css('width', "99%");
                progressText.text('Converting record associations. This may take a while ...');
                $.ajax({
                    url: finishExodusUrl,
                    method: 'POST',
                    data: { "_token": CSRFToken },
                    success: function() {
                        //Associations are finished!!!!
                        progressFill.removeClass('warning'); //In case check progress added it
                        progressFill.css('width', "100%");
                        //Add link to return to projects page
                        progressText.html('Exodus transfer complete! Click <a class="success-link" href="'+projectsUrl+
                            '">here to go to the projects page</a> and see your new projects.');
                        unlockUsers();
                    }
                });
            },
            error: function(data) {
                //Migration failed :(
                stopThePress = true;
                progressText.html(data.responseJSON.message+'. Click here to <a class="success-link unlock-users-js" href="#">unlock users</a>');
                progressFill.addClass('warning');
            }
        });
    }

    function initializeCheckProgress() {
        //This will start after 10 seconds
        setTimeout(function () {
            checkProgress();
        }, 10000);

        function checkProgress() {
            $.ajax({
                url: checkProgressUrl,
                method: 'GET',
                data: {
                    "_token": CSRFToken
                },
                success: function (data) {
                    console.log(data);
                    if(data == 'inprogress') { //Exodus is running but progress tables are not built yet
                        setTimeout(function () {
                            checkProgress();
                        }, 5000);
                    } else if(!stopThePress) { //Update progress of exodus
                        //Update bar
                        var totalProgress = 0;
                        var totalOverall = 0;
                        for (var id in data.partial) {
                            totalProgress += data.partial[id]['progress'];
                            totalOverall += data.partial[id]['overall'];
                        }
                        var progressValue = ((totalProgress / totalOverall) * 100);
                        if(progressValue < 7)
                            progressValue = 7;

                        progressFill.removeClass('warning'); //Remove in case of failed checked earlier
                        progressFill.css('width', (progressValue + "%"));

                        setTimeout(function () {
                            checkProgress();
                        }, 5000);
                    }
                },
                error: function (data) {
                    //If progress check fails and we are still going, don't stop the whole thing but prompt user to wait
                    if(!stopThePress) {
                        progressText.text('Error checking progress. Please wait ...');
                        $(".progress-fill-js").addClass('warning');
                        setTimeout(function () {
                            checkProgress();
                        }, 5000);
                    }
                }
            });
        }
    }

    function initializeUnlockUsers() {
        $(".progress-text-js").on('click', '.unlock-users-js', function (e) {
            e.preventDefault();

            unlockUsers();
        });
    }

    function unlockUsers() {
        var unlockURL = unlockUsersUrl;
        $.ajax({
            url: unlockURL,
            method: 'POST',
            data: {
                "_token": CSRFToken
            },
            success: function (data) {
                progressText.text('Users are now able to access Kora 3');
            },
            error: function (data) {
                progressText.text('Unable to restore access to all users. You may have to manually unlock them via the database');
            }
        })
    }

    initializeMigrateProjects();
    initializeCheckProgress();
    initializeUnlockUsers();
}