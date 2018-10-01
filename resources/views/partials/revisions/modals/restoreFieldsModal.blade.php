<?php $url = action('RevisionController@rollback'); ?>
<div class="modal modal-js modal-mask restore-fields-modal-js">
    <div class="content small">
        <div class="header">
            <span class="title">Restore Fields to Before?</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body">
            <p>
                Are you sure you want to restore these fields back to the edits made on <span class="date-time"></span>? 
                Don't worry, you can always restore them back to their current state as well.
            </p>
            <div class="form-group mt-m">
                <a href="{{$url}}" class="btn restore-fields-button-js">Restore Fields to Before</a>
            </div>
        </div>
    </div>
</div>