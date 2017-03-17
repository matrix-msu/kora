<hr>
{!! Form::hidden('advance','true') !!}

<div class="form-group">
    {!! Form::label('default',trans('fields_options_richtext.def').': ') !!}
    {!! Form::text('default', '', ['class' => 'form-control']) !!}
</div>