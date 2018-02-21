<div class="form-group mt-xxxl">
    <label>@if($field->required==1)<span class="oval-icon"></span> @endif{{$field->name}}: </label>

    @if(\App\Http\Controllers\FieldController::getFieldOption($field,'MultiLine')==0)
        {!! Form::text($field->flid, $field->default, ['class' => 'text-input']) !!}
    @endif
    @if(\App\Http\Controllers\FieldController::getFieldOption($field,'MultiLine')==1)
        {!! Form::textarea($field->flid, $field->default, ['class' => 'text-area']) !!}
    @endif
</div>