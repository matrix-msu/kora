@extends('fields.show')

@section('presetModal')
    @include('partials.fields.fieldValuePresetModals.addListPresetModal', ['presets' => $presets])
    @include('partials.fields.fieldValuePresetModals.createListPresetModal')
@stop

@section('fieldOptions')
    <div class="form-group">
        {!! Form::label('options','List Options: ') !!}
        <select multiple class="multi-select modify-select genlist-options-js" name="options[]" data-placeholder="Select or Add Some Options">
            @foreach(\App\GeneratedListField::getList($field,false) as $opt)
                <option value="{{$opt}}">{{$opt}}</option>
            @endforeach
        </select>
        <div><a href="#" class="field-preset-link open-list-modal-js">Use a Value Preset for these List Options</a></div>
        <div><a href="#" class="field-preset-link open-create-list-modal-js right
            @if(empty(\App\ListField::getList($field,false))) disabled @endif">
                Create a New Value Preset from these List Options</a></div>
    </div>

    <div class="form-group mt-xxxl">
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