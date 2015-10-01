@extends('fields.show')

@section('fieldOptions')

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateRequired', $field->pid, $field->fid, $field->flid]]) !!}
        @include('fields.options.hiddens')
        <div class="form-group">
            {!! Form::label('required','Required: ') !!}
            {!! Form::select('required',['false', 'true'], $field->required, ['class' => 'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::submit("Update Required",['class' => 'btn btn-primary form-control']) !!}
        </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateDefault', $field->pid, $field->fid, $field->flid]]) !!}
        @include('fields.options.hiddens')
        <div class="form-group">
            {!! Form::label('default','Default: ') !!}
            {!! Form::text('default', $field->default, ['class' => 'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::submit("Update Default",['class' => 'btn btn-primary form-control']) !!}
        </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
        @include('fields.options.hiddens')
        {!! Form::hidden('option','Regex') !!}
        <div class="form-group">
            {!! Form::label('value','Regex: ') !!}
            {!! Form::text('value', \App\Http\Controllers\FieldController::getFieldOption($field,'Regex'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::submit("Update Regex",['class' => 'btn btn-primary form-control']) !!}
        </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
        @include('fields.options.hiddens')
        {!! Form::hidden('option','MultiLine') !!}
        <div class="form-group">
            {!! Form::label('value','Multi-Line: ') !!}
            {!! Form::select('value', ['no','yes'], \App\Http\Controllers\FieldController::getFieldOption($field,'MultiLine'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::submit("Update Multi-Line",['class' => 'btn btn-primary form-control']) !!}
        </div>
    {!! Form::close() !!}

    @include('partials.option_preset')

    @include('errors.list')
@stop

@section('footer')

    <script>

    </script>

@stop