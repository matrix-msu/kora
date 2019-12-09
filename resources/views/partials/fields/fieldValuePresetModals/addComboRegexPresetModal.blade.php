@php
    dd($presets);
    $presetFormattedOne = array(''=>'');
    foreach($presets['one'] as $index => $sets) {
        if($index=="Stock") {
            foreach($sets as $preset) {
                $presetFormattedOne[$preset->preset] = $preset->name." [Stock]";
            }
        } else if($index=="Project") {
            foreach($sets as $preset) {
                $presetFormattedOne[$preset->preset] = $preset->name;
            }
        } else if($index=="Shared") {
            foreach($sets as $preset) {
                $presetFormattedOne[$preset->preset] = $preset->name." [PID:".$preset->project_id."]";
            }
        }
    }

    $presetFormattedTwo = array(''=>'');
    foreach($presets['two'] as $index => $sets) {
        if($index=="Stock") {
            foreach($sets as $preset) {
                $presetFormattedTwo[$preset->preset] = $preset->name." [Stock]";
            }
        } else if($index=="Project") {
            foreach($sets as $preset) {
                $presetFormattedTwo[$preset->preset] = $preset->name;
            }
        } else if ($index=="Shared") {
            foreach($sets as $preset) {
                $presetFormattedTwo[$preset->preset] = $preset->name." [PID:".$preset->project_id."]";
            }
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
		{!! Form::select('regex_preset', $presetFormattedOne, null, ['class' => 'single-select add-regex-one', 'data-placeholder' => 'Select the regex field value preset here']) !!}
		{!! Form::select('regex_preset', $presetFormattedTwo, null, ['class' => 'single-select add-regex-two', 'data-placeholder' => 'Select the regex field value preset here']) !!}

            </div>
            <div class="form-group mt-xxxl">
		<a href="#" class="btn add-combo-preset-js">Use Regex Preset Value</a>
            </div>
        </div>
    </div>
</div>
