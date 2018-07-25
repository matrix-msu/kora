<div class="modal modal-js modal-mask change-preset-name-modal-js">
    <div class="content">
        <div class="header">
            <span class="title">Change Preset Name</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body">
            <div class="form-group">
                {!! Form::label('preset_name', 'Preset Name') !!}
                <input type="text" class="text-input preset-name-js" placeholder="Enter the new name for the preset here">
            </div>

            <div class="form-group mt-xxl">
                <a href="#" class="btn change-preset-name-js">Change Preset Name</a>
            </div>
        </div>
    </div>
</div>