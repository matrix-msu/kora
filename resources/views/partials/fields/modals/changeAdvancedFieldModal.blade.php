<div class="modal modal-js modal-mask change-advanced-field-modal-js">
    <div class="content small">
        <div class="header">
            <span class="title title-js">Change Field Type?</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body">
            <span class="description">
                Are you sure? This will remove any advanced field option settings you have applied.
            </span>

            <div class="form-group">
                {!! Form::submit('Change Field Type',['class' => 'btn warning change-field-type-js']) !!}
            </div>
        </div>
    </div>
</div>