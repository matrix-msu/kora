@extends('fields.show')

@section('fieldOptions')
    <div class="form-group">
        {!! Form::label('default','Default: ') !!}
        {!! Form::select('default',\App\ListField::getList($field,true), $field->default,
        ['class' => 'single-select list-default-js']) !!}
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('options','List Options: ') !!}
        <select multiple class="multi-select modify-select list-options-js" name="options[]" data-placeholder="Select or Add Some Options">
            @foreach(\App\ListField::getList($field,false) as $opt)
                <option value="{{$opt}}">{{$opt}}</option>
            @endforeach
        </select>
    </div>
@stop

@section('fieldOptionsJS')
    Kora.Fields.Options('List');
@stop