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
    {!! Form::label('default' . $seq, $title) !!}
    <textarea id="default" name="default{{$seq}}" class="ckeditor-js {{$defClass}}">{{$default}}</textarea>
</div>