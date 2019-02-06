<?php
    if($editRecord)
        $numVal = $record->{$flid};
    else
        $numVal = $field['default'];
?>
<div class="form-group mt-xxxl">
    <label>
        @if($field['required'])
            <span class="oval-icon"></span>
        @endif
        <?php
            $unit = $field['options']['Unit'];
			echo (strlen($unit) > 0 ? $field['name'] . ' (' . $unit . ')' : $field['name']);
		?> </label>
    <span class="error-message"></span>
    <div class="number-input-container number-input-container-js">
        <input type="number" id="{{ $flid }}" name="{{ $flid }}" class="text-input preset-clear-text-js" value="{{ $numVal }}" placeholder="Enter number here"
              step="{{ $field['options']['Increment'] }}"
              max="{{ $field['options']['Max'] }}"
              min="{{ $field['options']['Min'] }}">
    </div>
</div>
