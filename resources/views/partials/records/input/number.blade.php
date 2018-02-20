<div class="form-group">
    {!! Form::label($field->flid, $field->name.' ('.\App\Http\Controllers\FieldController::getFieldOption($field, "Unit").'): ') !!}
    @if($field->required==1)
        <b style="color:red;font-size:20px">*</b>
    @endif
    <input
            type="number" name="{{ $field->flid }}" class="form-control" value="{{ $field->default }}"
            step="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "Increment") }}"
            max="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "Max") }}"
            min="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "Min") }}">
</div>