var Kora = Kora || {};
Kora.Backups = Kora.Backups || {};

Kora.Backups.Progress = function() {

    var stopThePress = false; //Variable to show whether we are still in progress
    var progressText = $(".progress-text-js");
    var progressFill = $(".progress-fill-js");

    function initializeBackups() {
        window.onbeforeunload = function() {
            //Encourage user not to leave mid-backup
            return "Do not leave this page, the backup process will be interrupted!";
        };

        //Begin the process
        $.ajax({
            url: startBackupUrl,
            method: 'POST',
            data: {
                "_token": CSRFToken,
                "backupLabel": buLabel
            },
            success: function() {
                //Main part of process is over, all that's left is associations
                stopThePress = true;
                progressFill.css('width', "99%");
                progressText.text('Backing up record files. This may take a while ...');
                $.ajax({
                    url: finishBackupUrl,
                    method: 'POST',
                    data: {
                        "_token": CSRFToken,
                        "backupLabel": buLabel
                    },
                    success: function (data2) {
                        progressText.html('Backup complete! Click here to <a class="success-link ' +
                            'download-file-js" href="#">download the backup file</a>. Estimated Pre-Compressed ' +
                            'Download Size: ' + data2.totalSize);
                        unlockUsers();

                        //User is allowed to leave
                        window.onbeforeunload = null;
                    }
                });
            },
            error: function(data){
                //Backup failed :(
                stopThePress = true;
                progressText.html(data.responseJSON.message+'. Click here to <a class="success-link unlock-users-js" href="#">unlock users</a>');
                progressFill.addClass('warning');

                //User is allowed to leave
                window.onbeforeunload = null;
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
                    "_token": CSRFToken,
                    "backupLabel": buLabel
                },
                success: function (data) {
                    console.log(data);
                    if(!stopThePress) { //Update progress of backup
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

    function initializeDownload() {
        $(".progress-text-js").on('click', '.download-file-js', function (e) {
            e.preventDefault();

            window.location = downloadFileUrl;
        });
    }

    initializeBackups();
    initializeCheckProgress();
    initializeUnlockUsers();
    initializeDownload();
}
