<div class="modal modal-js modal-mask form-cleanup-modal-js">
    <div class="content small">
        <div class="header">
            <span class="title">Delete Form?</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body">
            {!! Form::open([
              'method' => 'DELETE',
              'action' => ['FormController@destroy', 'pid' => $form->project_id, 'fid' => $form->id]
            ]) !!}
              <span class="description">
                Are you sure you wish to delete this form? This cannot be undone.
              </span>

              <div class="form-group">
                {!! Form::submit('Delete Form',['class' => 'btn warning delete-form-js']) !!}
              </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
