@extends('fields.show')

@section('fieldOptions')
    <div class="form-group">
        {!! Form::label('default','Default') !!}
        <textarea id="default" name="default" class="ckeditor-js">{{$field['default']}}</textarea>
    </div>
@stop

@section('fieldOptionsJS')
    Kora.Fields.Options('Rich Text');
@stop
