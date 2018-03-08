<div class="modal modal-js modal-mask delete-record-modal-js">
    <div class="content small">
        <div class="header">
            <span class="title">Delete Record?</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body">
            @if(!is_null($record))
                {!! Form::open([
                  'method' => 'DELETE',
                  'action' => ['RecordController@destroy', 'pid' => $form->pid, 'fid' => $form->fid, 'rid' => $record->rid]
                ]) !!}
            @else
                {!! Form::open([
                  'method' => 'DELETE',
                  'action' => ['RecordController@destroy', 'pid' => $form->pid, 'fid' => $form->fid, 'rid' => ''],
                  'class' => 'delete-record-form-js'
                ]) !!}
            @endif
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