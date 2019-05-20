@php
    $presetFormatted = array(''=>'');
    foreach($presets as $index => $sets) {
        foreach($sets as $preset) {
            //Need this to support multiple types for one field type
            if($preset->preset['type'] != 'Regex')
                continue;

            if($index=="Stock")
                $presetFormatted[$preset->preset['preset']] = $preset->preset['name']." [Stock]";
            else if($index=="Project")
                $presetFormatted[$preset->preset['preset']] = $preset->preset['name'];
            else if($index=="Shared")
                $presetFormatted[$preset->preset['preset']] = $preset->preset['name']." [PID:".$preset->project_id."]";
        }
    }
@endphp

<div class="modal modal-js modal-mask add-regex-preset-modal-js">
    <div class="content">
        <div class="header">
            <span class="title title-js">Use a Field Value Preset for this Regex</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
	<div class="body">
           <div class="form-group">
                {!! Form::label('regex_preset','Regex Field Value Preset') !!}
		{!! Form::select('regex_preset', $presetFormatted, null, ['class' => 'single-select', 'data-placeholder' => 'Select the regex field value preset here']) !!}
            </div>
            <div class="form-group mt-xxxl">
                <a href="#" class="btn add-regex-preset-js">Use Regex Preset Value</a>
            </div>
        </div>
    </div>
</div>
