@extends('fields.show')

@section('fieldOptions')

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateRequired', $field->pid, $field->fid, $field->flid]]) !!}
        @include('fields.options.hiddens')
        <div class="form-group">
            {!! Form::label('required',trans('fields_options_text.req').': ') !!}
            {!! Form::select('required',['false', 'true'], $field->required, ['class' => 'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::submit(trans('fields_options_text.updatereq'),['class' => 'btn btn-primary form-control']) !!}
        </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateDefault', $field->pid, $field->fid, $field->flid]]) !!}
        @include('fields.options.hiddens')
        <div class="form-group">
            {!! Form::label('default',trans('fields_options_text.def').': ') !!}
            {!! Form::text('default', $field->default, ['class' => 'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::submit(trans('fields_options_text.updatedef'),['class' => 'btn btn-primary form-control']) !!}
        </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
        @include('fields.options.hiddens')
        {!! Form::hidden('option','Regex') !!}
        <div class="form-group">
            {!! Form::label('value',trans('fields_options_text.regex').': ') !!}
            {!! Form::text('value', \App\Http\Controllers\FieldController::getFieldOption($field,'Regex'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::submit(trans('fields_options_text.updateregex'),['class' => 'btn btn-primary form-control']) !!}
        </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
        @include('fields.options.hiddens')
        {!! Form::hidden('option','MultiLine') !!}
        <div class="form-group">
            {!! Form::label('value',trans('fields_options_text.multi').': ') !!}
            {!! Form::select('value', ['no'=>trans('fields_options_text.no'),'yes'=>trans('fields_options_text.yes')], \App\Http\Controllers\FieldController::getFieldOption($field,'MultiLine'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::submit(trans('fields_options_text.updatemulti'),['class' => 'btn btn-primary form-control']) !!}
        </div>
    {!! Form::close() !!}

    @include('partials.option_preset')

    @include('errors.list')
@stop

@section('footer')

    <script>

    </script>

@stop