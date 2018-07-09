<div class="modal modal-js modal-mask designate-record-preset-modal-js">
    <div class="content">
        <div class="header">
            <span class="title">Designate Record as Preset</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body">
            <div class="form-group">
                {!! Form::label('preset_name', 'Preset Name') !!}
                <input type="text" class="text-input preset-name-js" placeholder="Enter the name for the new preset here">
            </div>

            <div class="form-group mt-xxl">
                <a href="#" class="btn create-record-preset-js">Create Record Preset</a>
            </div>
        </div>
    </div>
</div>