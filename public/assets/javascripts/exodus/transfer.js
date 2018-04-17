var Kora = Kora || {};
Kora.Exodus = Kora.Exodus || {};

Kora.Exodus.Transfer = function() {

    var stopThePress = false; //Variable to show whether we are still in progress
    var progressTest = $(".progress-text-js");
    var progressFill = $(".progress-fill-js");

    function initializeMigrateProjects() {
        window.onbeforeunload = function() {
            //Encourage user not to leave mid-migration
            return "Do not leave this page, the kora 2 exodus process will be interrupted!";
        };

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
                progressFill.css('width', "99%");
                progressTest.text('Converting record associations. This may take a while ...');
                $.ajax({
                    url: finishExodusUrl,
                    method: 'POST',
                    data: { "_token": CSRFToken },
                    success: function() {
                        //Associations are finished!!!!
                        stopThePress = true;
                        progressFill.removeClass('warning'); //In case check progress added it
                        progressFill.css('width', "100%");
                        //Add link to return to projects page
                        progressTest.html('Exodus transfer complete! Click <a class="success-link" href="'+projectsUrl+
                            '">here to go to the projects page</a> and see your new projects.');
                        unlockUsers();

                        //User is allowed to leave
                        window.onbeforeunload = null;
                    }
                });
            },
            error: function(data) {
                //Migration failed :(
                stopThePress = true;
                progressTest.html(data.responseJSON.message+'. Click here to <a class="success-link unlock-users-js" href="#">unlock users</a>');
                progressFill.addClass('warning');

                //User is allowed to leave
                window.onbeforeunload = null;
            }
        });
    }

    function initializeCheckProgress() {
        //This will start after 30 seconds
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
                        progressTest.text('Error checking progress. Please wait ...');
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
        $(".progress-text-js").on('click', '.unlock-users-js', function () {
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
                progressTest.text('Users are now able to access Kora 3');
            },
            error: function (data) {
                progressTest.text('Unable to restore access to all users. You may have to manually unlock them via the database');
            }
        })
    }

    initializeMigrateProjects();
    initializeCheckProgress();
    initializeUnlockUsers();
}