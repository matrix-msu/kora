@extends('fields.show')

@section('fieldOptions')
    <div class="form-group">
        {!! Form::label('options','List Options: ') !!}
        <select multiple class="multi-select modify-select genlist-options-js" name="options[]" data-placeholder="Select or Add Some Options">
            @foreach(\App\GeneratedListField::getList($field,false) as $opt)
                <option value="{{$opt}}">{{$opt}}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('default','Default : ') !!}
        {!! Form::select('default[]',\App\GeneratedListField::getList($field,false), explode('[!]',$field->default),
        ['class' => 'multi-select genlist-default-js', 'multiple']) !!}
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('regex','Regex: ') !!}
        {!! Form::text('regex', \App\Http\Controllers\FieldController::getFieldOption($field,'Regex'), ['class' => 'text-input']) !!}
    </div>
@stop

@section('fieldOptionsJS')
    Kora.Fields.Options('Generated List');
@stop