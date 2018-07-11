<?php
    if($editRecord && $hasData)
        $numVal = $typedField->number;
    else if($editRecord)
        $numVal = "";
    else
        $numVal = $field->default;
?>
<div class="form-group mt-xxxl">
    <label>
        @if($field->required==1)
            <span class="oval-icon"></span>
        @endif
        <?php 
			$unit = \App\Http\Controllers\FieldController::getFieldOption($field, "Unit");
			echo (strlen($unit) > 0 ? $field->name . ' (' . $unit . ')' : $field->name);
		?> </label>
    <span class="error-message"></span>
    <input type="number" id="{{ $field->flid }}" name="{{ $field->flid }}" class="text-input preset-clear-text-js" value="{{ $numVal }}" placeholder="Enter number here"
            step="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "Increment") }}"
            max="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "Max") }}"
            min="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "Min") }}">
</div>