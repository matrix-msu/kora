@php
    if(isset($seq)) {
        $seq = '_' . $seq;
    } else
        $seq = '';
@endphp
<div class="form-group mt-xxxl">
    {!! Form::label('regex' . $seq,'Regex') !!}
    {!! Form::text('regex' . $seq, $field['options']['Regex'], ['class' => 'text-input', 'placeholder' => 'Enter regular expression pattern here']) !!}
    @if($seq)

    @else
        <div><a href="#" class="field-preset-link open-regex-modal-js">Use a Value Preset for this Regex</a></div>
        <div class="open-create-regex"><a href="#" class="field-preset-link open-create-regex-modal-js right
        @if($field['options']['Regex']=='') disabled tooltip @endif" tooltip="You must submit or update the field before creating a New Value Preset">
                Create a New Value Preset from this Regex</a></div>
    @endif
</div>
