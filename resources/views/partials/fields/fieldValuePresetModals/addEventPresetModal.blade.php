<?php
$presetFormatted = array(''=>'');
foreach($presets as $index => $sets) {
    if($index=="Stock") {
        foreach($sets as $preset) {
            $presetFormatted[$preset->preset] = $preset->name." [Stock]";
        }
    } else if($index=="Project") {
        foreach($sets as $preset) {
            $presetFormatted[$preset->preset] = $preset->name;
        }
    } else if($index=="Shared") {
        foreach($sets as $preset) {
            $presetFormatted[$preset->preset] = $preset->name." [PID:".$preset->pid."]";
        }
    }
}
?>

<div class="modal modal-js modal-mask add-event-preset-modal-js">
    <div class="content">
        <div class="header">
            <span class="title title-js">Use a Field Value Preset for these Events</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body">
            <div class="form-group">
                {!! Form::label('event_preset','Event Field Value Preset') !!}
                {!! Form::select('event_preset', $presetFormatted, null, ['class' => 'single-select', 'data-placeholder' => 'Select the event field value preset here']) !!}
            </div>
            <div class="form-group mt-xxxl">
                <a href="#" class="btn add-event-preset-js">Use Event Preset Value</a>
            </div>
        </div>
    </div>
</div>