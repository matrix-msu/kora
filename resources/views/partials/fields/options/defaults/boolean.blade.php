@php
    if(isset($seq))
        $seq = '_' . $seq;
    else
        $seq = '';
@endphp
<div class="form-group mt-xl">
    {!! Form::label('default','Default') !!}
    <div class="check-box-half">
        <input type="checkbox" value="1" id="preset" class="check-box-input" name="default{{$seq}}"
                {{ ((!is_null($field['default']) && $field['default']) ? 'checked' : '') }}>
        <span class="check"></span>
        <span class="placeholder"></span>
    </div>
</div>
