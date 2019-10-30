<div class="modal modal-js modal-mask delete-mass-list-option-js">
    <div class="content small">
        <div class="header">
            <span class="title">Delete All List Options?</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body">
            <span class="description">
                Are you sure you wish to delete all list options? This cannot be undone.
            </span>

            <div class="form-group">
                {!! Form::submit('Delete List Options',['class' => 'btn warning delete-mass-options-js']) !!}
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>