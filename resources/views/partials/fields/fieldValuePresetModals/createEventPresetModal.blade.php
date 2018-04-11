<div class="modal modal-js modal-mask create-event-preset-modal-js">
    <div class="content">
        <div class="header">
            <span class="title title-js">Create a New Field Value Preset from these Events</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body">
            <div class="form-group">
                {!! Form::label('preset_title','Event Field Value Preset Name') !!}
                {!! Form::text('preset_title', null, ['class' => 'text-input', 'placeholder' => 'Enter the name of the new event field value preset here']) !!}
            </div>
            <div class="form-group mt-xl">
                <div class="check-box-half">
                    <input type="checkbox" value="1" id="active" class="check-box-input" name="preset_shared" />
                    <span class="check"></span>
                    <span class="placeholder">Shared Event Field Value Preset with All Projects</span>
                </div>
            </div>
            <div class="form-group mt-xxxl">
                <a href="#" class="btn create-event-preset-js">Create Event Value Preset</a>
            </div>
        </div>
    </div>
</div>