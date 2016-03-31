<hr>
{!! Form::hidden('advance','true') !!}

<div class="form-group">
    {!! Form::label('default',trans('fields_options_number.def').': ') !!}
    <input
            type="number" name="default" class="form-control" value="1" id="default">
</div>

<div class="form-group">
    {!! Form::label('min',trans('fields_options_number.min').': ') !!}
    <input
            type="number" name="min" class="form-control" step="any" id="min"
            value="1">
</div>

<div class="form-group">
    {!! Form::label('max',trans('fields_options_number.max').': ') !!}
    <input
            type="number" name="max" class="form-control" step="any" id="max"
            value="10">
</div>

<div class="form-group">
    {!! Form::label('inc',trans('fields_options_number.inc').': ') !!}
    <input
            type="number" name="inc" class="form-control" step="any" id="inc"
            value="1">
</div>

<div class="form-group">
    {!! Form::label('unit',trans('fields_options_number.unit').': ') !!}
    {!! Form::text('unit', '', ['class' => 'form-control']) !!}
</div>