<div class="form-group">
    {!! Form::label('min_'.$fnum,trans('partials_combofields_number.min').': ') !!}
    <input
            type="number" name="min_{{$fnum}}" class="form-control" step="any"
            value="{{ \App\Http\Controllers\FieldController::getComboFieldOption($field, "Min", $fnum) }}"
            max="{{ \App\Http\Controllers\FieldController::getComboFieldOption($field, "Max", $fnum) }}">
</div>

<div class="form-group">
    {!! Form::label('max_'.$fnum,trans('partials_combofields_number.max').': ') !!}
    <input
            type="number" name="max_{{$fnum}}" class="form-control" step="any"
            value="{{ \App\Http\Controllers\FieldController::getComboFieldOption($field, "Max", $fnum) }}"
            min="{{ \App\Http\Controllers\FieldController::getComboFieldOption($field, "Min", $fnum) }}">
</div>

<div class="form-group">
    {!! Form::label('inc_'.$fnum,trans('partials_combofields_number.inc').': ') !!}
    <input
            type="number" name="inc_{{$fnum}}" class="form-control" step="any"
            value="{{ \App\Http\Controllers\FieldController::getComboFieldOption($field, "Increment", $fnum) }}">
</div>

<div class="form-group">
    {!! Form::label('unit_'.$fnum,trans('partials_combofields_number.unit').': ') !!}
    {!! Form::text('unit_'.$fnum, \App\Http\Controllers\FieldController::getComboFieldOption($field,'Unit', $fnum), ['class' => 'form-control']) !!}
</div>