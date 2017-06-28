@extends('fields.show')

@section('fieldOptions')

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldAjaxController@updateOptions', $field->pid, $field->fid, $field->flid, $field->type]]) !!}
        @include('fields.options.hiddens')
        <div class="form-group">
            {!! Form::label('required',trans('fields_options_text.req').': ') !!}
            {!! Form::select('required',['false', 'true'], $field->required, ['class' => 'form-control']) !!}
        </div>

        <div class="form-group">
            {!! Form::label('searchable',trans('fields_options_text.search').': ') !!}
            {!! Form::select('searchable',['false', 'true'], $field->searchable, ['class' => 'form-control']) !!}
        </div>

        <div class="form-group">
            {!! Form::label('extsearch',trans('fields_options_text.extsearch').': ') !!}
            {!! Form::select('extsearch',['false', 'true'], $field->extsearch, ['class' => 'form-control']) !!}
        </div>

        <div class="form-group">
            {!! Form::label('viewable',trans('fields_options_text.viewable').': ') !!}
            {!! Form::select('viewable',['false', 'true'], $field->viewable, ['class' => 'form-control']) !!}
        </div>

        <div class="form-group">
            {!! Form::label('viewresults',trans('fields_options_text.viewresults').': ') !!}
            {!! Form::select('viewresults',['false', 'true'], $field->viewresults, ['class' => 'form-control']) !!}
        </div>

        <div class="form-group">
            {!! Form::label('extview',trans('fields_options_text.extview').': ') !!}
            {!! Form::select('extview',['false', 'true'], $field->extview, ['class' => 'form-control']) !!}
        </div>

        <hr>

        <div class="form-group">
            {!! Form::label('default',trans('fields_options_text.def').': ') !!}
            {!! Form::text('default', $field->default, ['class' => 'form-control']) !!}
        </div>

        <div class="form-group">
            {!! Form::label('regex',trans('fields_options_text.regex').': ') !!}
            {!! Form::text('regex', \App\Http\Controllers\FieldController::getFieldOption($field,'Regex'), ['class' => 'form-control']) !!}
        </div>

        <div class="form-group">
            {!! Form::label('multi',trans('fields_options_text.multi').': ') !!}
            {!! Form::select('multi', [0=>trans('fields_options_text.no'), 1=>trans('fields_options_text.yes')], \App\Http\Controllers\FieldController::getFieldOption($field,'MultiLine'), ['class' => 'form-control']) !!}
        </div>

        <div class="form-group">
            {!! Form::submit(trans('field_options_generic.submit',['field'=>$field->name]),['class' => 'btn btn-primary form-control']) !!}
        </div>
    {!! Form::close() !!}

    @include('partials.option_preset')

    @include('errors.list')
@stop

@section('footer')

    <script>

    </script>

@stop