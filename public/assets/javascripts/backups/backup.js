var Kora = Kora || {};
Kora.Backups = Kora.Backups || {};

Kora.Backups.Progress = function() {
    var stopThePress = false; //Variable to show whether we are still in progress
    var progressText = $(".progress-text-js");
    var progressFill = $(".progress-fill-js");

    function initializeBackups() {
        //Begin the process
        $.ajax({
            url: startBackupUrl,
            method: 'POST',
            data: {
                "_token": CSRFToken,
                "backupLabel": buLabel,
                "backupData": buData
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
                        "backupLabel": buLabel,
                        "backupData": buData,
                        "backupFiles": buFiles
                    },
                    success: function (data2) {
                        window.localStorage.setItem('message', 'Backup File Successfully Created!');
                        showNotification();

                        $('.backup-progress').addClass('hidden');
                        $('.backup-finish').removeClass('hidden');

                        $('.stop-rotation-js').removeClass('rotate-icon');
                        $('.success-title-js').text('Backup Success!');
                        $('.success-desc-js').text('The backup has successfully completed! Now you can feel at ease ' +
                            'knowing a version of your data is safe and sound.');
                        $('.download-file-js').val('Download Backup File ('+data2.totalSize+')');
                        unlockUsers();

                        if(autoDL=='1')
                            $(".download-file-js").click();
                    },
                    error: function(data) {
                        //Backup failed :(
                        stopThePress = true;
                        progressText.html('Restore failed. Click here to <a class="success-link unlock-users-js" href="#">unlock users</a>');
                        progressFill.addClass('warning');
                        $('.stop-rotation-js').removeClass('rotate-icon');
                    }
                });
            },
            error: function(data){
                //Backup failed :(
                stopThePress = true;
                progressText.html('Backup failed. Click here to <a class="success-link unlock-users-js" href="#">unlock users</a>');
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
        $(".download-file-js").click(function(e) {
            e.preventDefault();

            window.location = downloadFileUrl;
        });
    }
    
    function showNotification() {
      var $noteBody = $('.notification');
      var $note = $('.note').children('p');
      var $noteDesc = $('.note').children('span');

      var message = window.localStorage.getItem('message');

      if (message) {
        $note.text(message);
        window.localStorage.clear();
      }

      setTimeout(function(){
        if ($note.text() != '') {
          if ($noteDesc.text() != '') {
            $noteDesc.addClass('note-description');
            $note.addClass('with-description');
          }

          $noteBody.removeClass('dismiss');
          $('.welcome-body').addClass('with-notification');

          if (!$noteBody.hasClass('static-js')) {
            setTimeout(function(){
              $noteBody.addClass('dismiss');
            }, 4000);
          }
        }
      }, 200);

      $('.toggle-notification-js').click(function(e) {
        e.preventDefault();

        $noteBody.addClass('dismiss');
        $('.welcome-body').removeClass('with-notification');
      });
    }

    initializeBackups();
    initializeCheckProgress();
    initializeUnlockUsers();
    initializeDownload();
	initializeValidation();
}
