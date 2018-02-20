<div class="form-group mt-xxxl">
    <label>@if($field->required==1)<span class="oval-icon"></span> @endif{{$field->name}}: </label>
    {!! Form::select($field->flid.'[]',\App\ScheduleField::getDateList($field),
        explode('[!]',$field->default),['class' => 'multi-select  '.$field->flid.'-event-js', 'Multiple', 'data-placeholder' => "Add Events Below"]) !!}
</div>

<section class="new-object-button form-group mt-xl">
    <input flid="{{$field->flid}}" type="button" class="add-new-default-event-js" value="Create New Event"
        start="{{ \App\Http\Controllers\FieldController::getFieldOption($field, 'Start') }}"
        end="{{ \App\Http\Controllers\FieldController::getFieldOption($field, 'End') }}">
</section>