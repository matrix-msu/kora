<div class="modal modal-js modal-mask delete-test-records-js">
    <div class="content small">
        <div class="header">
            <span class="title">Delete All Test Records?</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body">
            {!! Form::open([
              'method' => 'DELETE',
              'action' => ['RecordController@deleteTestRecords', 'pid' => $form->pid, 'fid' => $form->fid]
            ]) !!}
            <span class="description">
                Are you sure you wish to delete all the test records within this form?
              </span>

            <div class="form-group">
                {!! Form::submit('Delete All Test Records',['class' => 'btn warning']) !!}
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
