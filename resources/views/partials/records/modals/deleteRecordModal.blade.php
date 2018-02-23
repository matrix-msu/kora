<div class="modal modal-js modal-mask delete-record-modal-js">
    <div class="content small">
        <div class="header">
            <span class="title">Delete Record?</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body">
            {!! Form::open([
              'method' => 'DELETE',
              'action' => ['RecordController@destroy', 'pid' => $form->pid, 'fid' => $form->fid, 'rid' => $record->rid]
            ]) !!}
                <div class="form-group">
                    Are you sure you want to delete this Record?
                </div>

                <div class="form-group">
                    {!! Form::submit('Delete Record',['class' => 'btn warning']) !!}
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>