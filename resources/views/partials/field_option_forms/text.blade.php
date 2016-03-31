<hr>
{!! Form::hidden('advance','true') !!}
<div class="form-group">
    {!! Form::label('default',trans('fields_options_text.def').': ') !!}
    {!! Form::text('default', '', ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('regex',trans('fields_options_text.regex').': ') !!}
    {!! Form::text('regex', '', ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('multi',trans('fields_options_text.multi').': ') !!}
    {!! Form::select('multi', [0=>trans('fields_options_text.no'),1=>trans('fields_options_text.yes')], 'no', ['class' => 'form-control']) !!}
</div>