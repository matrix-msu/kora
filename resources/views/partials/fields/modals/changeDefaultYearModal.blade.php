<div class="modal modal-js modal-mask change-default-year-modal-js">
    <div class="content small">
        <div class="header">
            <span class="title title-js">Heads up! Default Year will be cleared...</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body">
            <span class="description">
                The current default year now lies outside of the set range and will be cleared. Continue?
            </span>

            <div class="form-group">
                {!! Form::submit('Continue',['class' => 'btn change-default-year-js']) !!}
            </div>
        </div>
    </div>
</div>
