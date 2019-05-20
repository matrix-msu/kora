@php
    $presetFormatted = array(''=>'');
    foreach($presets as $index => $sets) {
        foreach($sets as $preset) {
            //Need this to support multiple types for one field type
            if($preset->preset['type'] != 'List')
                continue;
            $preVal = implode('[!]',$preset->preset['preset']);

            if($index=="Stock")
                $presetFormatted[$preVal] = $preset->preset['name']." [Stock]";
            else if($index=="Project")
                $presetFormatted[$preVal] = $preset->preset['name'];
            else if($index=="Shared")
                $presetFormatted[$preVal] = $preset->preset['name']." [PID:".$preset->project_id."]";
        }
    }
@endphp

<div class="modal modal-js modal-mask add-list-preset-modal-js">
    <div class="content">
        <div class="header">
            <span class="title title-js">Use a Field Value Preset for these List Options</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body">
            <div class="form-group">
                {!! Form::label('list_preset','List Option Field Value Preset') !!}
                {!! Form::select('list_preset', $presetFormatted, null, ['class' => 'single-select', 'data-placeholder' => 'Select the list option field value preset here']) !!}
            </div>
            <div class="form-group mt-xxxl">
                <a href="#" class="btn add-list-preset-js">Use List Option Preset Value</a>
            </div>
        </div>
    </div>
</div>