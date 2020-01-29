@php
    if(isset($seq)) { //Combo List
        $seq = '_' . $seq;
        $title = $cfName;
        $default = null;
    } else {
        $seq = '';
        $title = 'Default';
        $default = $field['default'];
    }
@endphp
<div class="form-group">
    {!! Form::label('default' . $seq, $title) !!}
    <textarea id="default{{$seq}}" name="default{{$seq}}" class="ckeditor-js">{{$default}}</textarea>
</div>