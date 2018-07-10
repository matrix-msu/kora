<?php
    if($editRecord && $hasData) {
        $selected = App\ScheduleField::eventsToOldFormat($typedField->events()->get());
        $listOpts = array();
        foreach($selected as $val){
            $listOpts[$val] = $val;
        }
    } else if($editRecord) {
        $selected = null;
        $listOpts = array();
    } else {
        $selected = explode('[!]',$field->default);
        $listOpts = \App\ScheduleField::getDateList($field);
    }
?>
<div class="form-group mt-xxxl">
    <label>@if($field->required==1)<span class="oval-icon"></span> @endif{{$field->name}}</label>
    <span class="error-message"></span>
    {!! Form::select($field->flid.'[]', $listOpts, $selected,['class' => 'multi-select  '.$field->flid.'-event-js preset-clear-chosen-js',
        'Multiple', 'data-placeholder' => "Add Events Below", 'id' => 'list'.$field->flid]) !!}
</div>

<section class="new-object-button form-group mt-xl">
    <input flid="{{$field->flid}}" type="button" class="add-new-default-event-js" value="Create New Event"
        start="{{ \App\Http\Controllers\FieldController::getFieldOption($field, 'Start') }}"
        end="{{ \App\Http\Controllers\FieldController::getFieldOption($field, 'End') }}">
</section>