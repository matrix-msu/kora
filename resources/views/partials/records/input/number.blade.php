<div class="form-group mt-xl">
    <label>
        @if($field->required==1)
            <span class="oval-icon"></span>
        @endif
        {{$field->name.' ('.\App\Http\Controllers\FieldController::getFieldOption($field, "Unit")}}: </label>

    <input
            type="number" name="{{ $field->flid }}" class="text-input" value="{{ $field->default }}"
            step="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "Increment") }}"
            max="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "Max") }}"
            min="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "Min") }}">
</div>