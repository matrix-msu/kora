<div class="modal modal-js modal-mask field-cleanup-modal-js">
    <div class="content small">
        <div class="header">
            <span class="title title-js"></span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body">
            {!! Form::open([
              'method' => 'DELETE',
              'action' => ['FieldController@destroy', 'pid' => $pid, 'fid' => $fid, 'flid' => $flid],
              'style' => 'display:none',
              'class' => "delete-content-js"
            ]) !!}
            <input type="hidden" name="redirect_route" value="true">
            <span class="description">
                Are you sure you wish to delete this field and any records with data in this field? This cannot be undone.
            </span>

            <div class="form-group">
                {!! Form::submit('Delete Field',['class' => 'btn warning']) !!}
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>