<div class="modal modal-js modal-mask create-location-preset-modal-js">
    <div class="content">
        <div class="header">
            <span class="title title-js">Create a New Field Value Preset from these Locations</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body">
            <div class="form-group">
                {!! Form::label('preset_title','Location Field Value Preset Name') !!}
                {!! Form::text('preset_title', null, ['class' => 'text-input', 'placeholder' => 'Enter the name of the new location field value preset here']) !!}
            </div>
            <div class="form-group mt-xl">
                <div class="check-box-half">
                    <input type="checkbox" value="1" id="active" class="check-box-input" name="preset_shared" />
                    <span class="check"></span>
                    <span class="placeholder">Shared Location Field Value Preset with All Projects</span>
                </div>
            </div>
            <div class="form-group mt-xxxl">
                <a href="#" class="btn create-location-preset-js">Create Location Value Preset</a>
            </div>
        </div>
    </div>
</div>