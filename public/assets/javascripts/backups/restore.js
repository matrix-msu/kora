var Kora = Kora || {};
Kora.Backups = Kora.Backups || {};

Kora.Backups.Restore = function() {
    var stopThePress = false; //Variable to show whether we are still in progress
    var progressText = $(".progress-text-js");
    var progressFill = $(".progress-fill-js");

    function initializeRestore() {
        //Begin the process
        $.ajax({
            url: startRestoreUrl,
            method: 'POST',
            data: {
                "_token": CSRFToken,
                "filename": restoreLabel
            },
            success: function() {
                //Main part of process is over, all that's left is associations
                stopThePress = true;
                progressFill.css('width', "99%");
                progressText.text('Restoring record files. This may take a while ...');
                $.ajax({
                    url: finishRestoreUrl,
                    method: 'POST',
                    data: {
                        "_token": CSRFToken,
                        "filename": restoreLabel
                    },
                    success: function (data2) {
                        $('.restore-progress').addClass('hidden');
                        $('.restore-finish').removeClass('hidden');

                        $('.stop-rotation-js').removeClass('rotate-icon');
                        $('.success-title-js').text('Restore Success!');
                        $('.success-desc-js').text('The restore has successfully completed! Now you can feel at ease ' +
                            'knowing a version of your data has safely returned.');
                        unlockUsers();
                    },
                    error: function(data) {
                        //Restore failed :(
                        stopThePress = true;
                        progressText.html('Restore failed. Click here to <a class="success-link unlock-users-js" href="#">unlock users</a>');
                        progressFill.addClass('warning');
                        $('.stop-rotation-js').removeClass('rotate-icon');
                    }
                });
            },
            error: function(data) {
                //Restore failed :(
                stopThePress = true;
                progressText.html('Restore failed. Click here to <a class="success-link unlock-users-js" href="#">unlock users</a>');
                progressFill.addClass('warning');
                $('.stop-rotation-js').removeClass('rotate-icon');
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
                    if(!stopThePress) { //Update progress of restore
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
                error: function() {
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

    initializeRestore();
    initializeCheckProgress();
    initializeUnlockUsers();
}
