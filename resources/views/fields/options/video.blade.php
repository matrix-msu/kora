@extends('fields.show')

@section('fieldOptions')

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldAjaxController@updateOptions', $field->pid, $field->fid, $field->flid, $field->type]]) !!}
    @include('fields.options.hiddens')
    <div class="form-group">
        {!! Form::label('required',trans('fields_options_video.req').': ') !!}
        {!! Form::select('required',['false', 'true'], $field->required, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('searchable',trans('fields_options_video.search').': ') !!}
        {!! Form::select('searchable',['false', 'true'], $field->searchable, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('extsearch',trans('fields_options_video.extsearch').': ') !!}
        {!! Form::select('extsearch',['false', 'true'], $field->extsearch, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('viewable',trans('fields_options_video.viewable').': ') !!}
        {!! Form::select('viewable',['false', 'true'], $field->viewable, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('viewresults',trans('fields_options_video.viewresults').': ') !!}
        {!! Form::select('viewresults',['false', 'true'], $field->viewresults, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('extview',trans('fields_options_video.extview').': ') !!}
        {!! Form::select('extview',['false', 'true'], $field->extview, ['class' => 'form-control']) !!}
    </div>

    <hr>

    <div class="form-group">
        {!! Form::label('filesize',trans('fields_options_video.maxsize').' (kb): ') !!}
        <input type="number" name="filesize" class="form-control" step="1"
               value="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "FieldSize") }}" min="0">
    </div>

    <div class="form-group">
        {!! Form::label('maxfiles',trans('fields_options_video.maxamount').': ') !!}
        <input type="number" name="maxfiles" class="form-control" step="1"
               value="{{ \App\Http\Controllers\FieldController::getFieldOption($field, "MaxFiles") }}" min="0">
    </div>

    <div class="form-group">
        {!! Form::label('filetype',trans('fields_options_video.types').'(MIME): ') !!}
        {!! Form::select('filetype'.'[]',['video/mp4' => 'MP4','video/ogg' => 'OGV'],
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