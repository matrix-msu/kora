@php
    if(isset($seq)) { //Combo List
        $seq = '_' . $seq;
        $title = $cfName;
        $default = null;
        $defClass = 'default-input-js';
    } else {
        $seq = '';
        $title = 'Default';
        $default = $field['default'];
        $defClass = '';
    }
@endphp
<div class="form-group">
    <div class="number-input-container">
        {!! Form::label('default' . $seq, $title) !!}
        <span class="error-message"></span>
        <input
                type="number"
                name="default{{$seq}}"
                id="default{{$seq}}"
                class="text-input number-default-js {{$defClass}}"
                value="{{ $default }}"
                placeholder="Enter number here"
                step="1"
        >
    </div>
</div>