<div class="modal modal-js modal-mask delete-multiple-records-modal-js">
    <div class="content small">
        <div class="header">
            <span class="title">Delete <span class="count"></span> Record(s)?</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body">
            @if(!is_null($record))
                {!! Form::open([
                  'method' => 'DELETE',
                  'action' => ['RecordController@deleteMultipleRecords', 'pid' => $form->pid, 'fid' => $form->fid]
                ]) !!}
            @else
                {!! Form::open([
                  'method' => 'DELETE',
                  'action' => ['RecordController@deleteMultipleRecords', 'pid' => $form->pid, 'fid' => $form->fid],
                  'class' => 'delete-multiple-records-form-js'
                ]) !!}
            @endif
                <div class="form-group">
                    Are you sure you want to delete these Records?
                </div>

                <div class="form-group record-ids"></div>

                <div class="form-group">
                    {!! Form::submit('Delete Records',['class' => 'btn warning delete-multiple-js']) !!}
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>