<div class="modal modal-js modal-mask create-backup-modal create-backup-modal-js">
    <div class="content">
        <div class="header">
            <span class="title">Create New Backup File</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body">
            <form method="post" action={{action("BackupController@startBackup")}}>
                <input type="hidden" name="_token" value="{{csrf_token()}}">

                <div class="form-group mt-xxs">
                    <label for="backupLabel">New Backup File Name</label>
                    <input type="text" class="text-input" name="backupLabel" placeholder="Enter the new backup file name here">
                </div>

                <div class="form-group mt-xl">
                    <label for="backupData">Backup Options</label>
                </div>
                <div class="actions">
                    <div class="form-group action mt-xs">
                        <div class="check-box-half check-box-rectangle">
                            <input type="checkbox"
                                   value="1"
                                   class="check-box-input"
                                   name="backupData" checked/>
                            <span class="check"></span>
                            <span class="placeholder">Backup Metadata</span>
                        </div>
                    </div>

                    <div class="form-group action">
                        <div class="check-box-half check-box-rectangle">
                            <input type="checkbox"
                                   value="0"
                                   class="check-box-input"
                                   name="backupFiles"/>
                            <span class="check"></span>
                            <span class="placeholder">Backup Files</span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="check-box-half">
                        <input type="checkbox" value="1" name="backupDownload" class="check-box-input" />
                        <span class="check"></span>
                        <span class="placeholder">Download Backup File after Backup Completion</span>
                    </div>
                </div>

                <div class="form-group mt-xxl">
                    <input type="submit" class="btn" value="Start Backup">
                </div>
            </form>
        </div>
    </div>
</div>