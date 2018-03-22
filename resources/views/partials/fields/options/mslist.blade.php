@extends('fields.show')

@section('fieldOptions')
    <div class="form-group">
        {!! Form::label('options','List Options: ') !!}
        <select multiple class="multi-select modify-select mslist-options-js" name="options[]" data-placeholder="Select or Add Some Options">
            @foreach(\App\MultiSelectListField::getList($field,false) as $opt)
                <option value="{{$opt}}">{{$opt}}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('default','Default : ') !!}
        {!! Form::select('default[]',\App\MultiSelectListField::getList($field,false), explode('[!]',$field->default),
        ['class' => 'multi-select mslist-default-js', 'multiple']) !!}
    </div>
@stop

@section('fieldOptionsJS')
    Kora.Fields.Options('Multi-Select List');
@stop