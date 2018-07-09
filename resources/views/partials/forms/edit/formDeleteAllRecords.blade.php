<div class="modal modal-js modal-mask delete-records-modal-js">
    <div class="content small">
        <div class="header">
            <span class="title">Delete All Form Records?</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body">
            {!! Form::open([
              'method' => 'DELETE',
              'action' => ['RecordController@deleteAllRecords', 'pid' => $form->pid, 'fid' => $form->fid]
            ]) !!}
            <span class="description">
                Are you sure you wish to delete all the records within this form?
              </span>

            <div class="form-group">
                {!! Form::submit('Delete All Form Records',['class' => 'btn warning']) !!}
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
