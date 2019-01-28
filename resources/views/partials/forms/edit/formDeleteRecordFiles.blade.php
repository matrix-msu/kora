<div class="modal modal-js modal-mask delete-files-modal-js">
    <div class="content small">
        <div class="header">
            <span class="title">Delete Old Record Files?</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body">
            {!! Form::open([
              'method' => 'POST',
              'action' => ['RecordController@cleanUp', 'pid' => $form->project_id, 'fid' => $form->id]
            ]) !!}
            <span class="description">
                This will delete all files from records that no longer exist
              </span>

            <div class="form-group">
                {!! Form::submit('Delete Old Record Files',['class' => 'btn warning']) !!}
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
