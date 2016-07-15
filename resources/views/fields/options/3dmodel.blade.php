@extends('fields.show')

@section('fieldOptions')

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['OptionController@updateModel', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    <div class="form-group">
        {!! Form::label('required',trans('fields_options_3dmodel.req').': ') !!}
        {!! Form::select('required',['false', 'true'], $field->required, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('searchable',trans('fields_options_3dmodel.search').': ') !!}
        {!! Form::select('searchable',['false', 'true'], $field->searchable, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('filesize',trans('fields_options_3dmodel.maxsize').' (kb): ') !!}
        <input type="number" name="filesize" class="form-control" step="1"
               value="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "FieldSize") }}" min="0">
    </div>

    <div class="form-group">
        {!! Form::label('filetype',trans('fields_options_3dmodel.types').' (MIME): ') !!}
        {!! Form::select('filetype'.'[]',['obj' => 'OBJ','stl' => 'STL'],
            explode('[!]',\App\Http\Controllers\FieldController::getFieldOption($field, "FileTypes")),
            ['class' => 'form-control filetypes', 'Multiple', 'id' => 'list'.$field->flid]) !!}
    </div>

    <div class="form-group">
        {!! Form::submit(trans('field_options_generic.submit',['field'=>$field->name]),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    @include('errors.list')
@stop

@section('footer')
    <script>
        $('.filetypes').select2();
    </script>
@stop