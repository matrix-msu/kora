@php
    if(isset($seq)) { //Combo List
        $fieldLabel = '';
        $fieldDivID = 'default_'.$seq.'_'.$flid;
        $numVal = null;
    } else if($editRecord) {
        $fieldLabel = $flid;
        $fieldDivID = $flid;
        $numVal = $record->{$flid};
    } else {
        $fieldLabel = $flid;
        $fieldDivID = $flid;
        $numVal = $field['default'];
    }

    $unit = $field['options']['Unit'];
@endphp
<div class="form-group mt-xxxl">
    <div class="number-input-container">
        <label>
            @if(!isset($seq) && $field['required'])
                <span class="oval-icon"></span>
            @endif
            {{ strlen($unit) > 0 ? $field['name'] . ' (' . $unit . ')' : $field['name'] }}
        </label>
        <span class="error-message"></span>
        <input
            type="number"
            id="{{ $fieldDivID }}"
            name="{{ $fieldLabel }}"
            class="text-input preset-clear-text-js"
            value="{{ $numVal }}"
            placeholder="Enter number here"
            max="{{ $field['options']['Max'] }}"
            min="{{ $field['options']['Min'] }}"
        >
    </div>
</div>
