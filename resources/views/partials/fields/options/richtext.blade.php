@extends('fields.show')

@section('fieldOptions')
    <div class="form-group">
        {!! Form::label('default','Default') !!}
        {!! Form::text('default', $field->default, ['class' => 'text-input']) !!}
    </div>
@stop

@section('fieldOptionsJS')
    Kora.Fields.Options('Rich Text');
@stop