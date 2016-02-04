@extends('fields.show')

@section('fieldOptions')

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['OptionController@updateDocument', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    <div class="form-group">
        {!! Form::label('required',trans('fields_options_documents.req').': ') !!}
        {!! Form::select('required',['false', 'true'], $field->required, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('filesize',trans('fields_options_documents.maxsize').' (kb): ') !!}
        <input type="number" name="filesize" class="form-control" step="1"
               value="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "FieldSize") }}" min="0">
    </div>

    <div class="form-group">
        {!! Form::label('maxfiles',trans('fields_options_documents.maxamount').': ') !!}
        <input type="number" name="maxfiles" class="form-control" step="1"
               value="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "MaxFiles") }}" min="0">
    </div>

    <div class="form-group">
        {!! Form::label('filetype',trans('fields_options_documents.types').' (MIME): ') !!}
        <?php
            $values = array();
            foreach(explode('[!]',\App\Http\Controllers\FieldController::getFieldOption($field, "FileTypes")) as $opt){
                $values[$opt] = $opt;
            }
        ?>
        {!! Form::select('filetype'.'[]',$values,
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
        $('.filetypes').select2({
            tags: true
        });
    </script>
@stop