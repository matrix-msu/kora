{!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateComboOptions', $field->pid, $field->fid, $field->flid]]) !!}
@include('fields.options.hiddens')
{!! Form::hidden('option','Min') !!}
{!! Form::hidden('fieldnum',$fnum) !!}
<div class="form-group">
    {!! Form::label('value',trans('partials_combofields_number.min').': ') !!}
    <input
            type="number" name="value" class="form-control" step="any"
            value="{{ \App\Http\Controllers\FieldController::getComboFieldOption($field, "Min", $fnum) }}"
            max="{{ \App\Http\Controllers\FieldController::getComboFieldOption($field, "Max", $fnum) }}">
</div>
<div class="form-group">
    {!! Form::submit(trans('partials_combofields_number.updatemin'),['class' => 'btn btn-primary form-control']) !!}
</div>
{!! Form::close() !!}

{!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateComboOptions', $field->pid, $field->fid, $field->flid]]) !!}
@include('fields.options.hiddens')
{!! Form::hidden('option','Max') !!}
{!! Form::hidden('fieldnum',$fnum) !!}
<div class="form-group">
    {!! Form::label('value',trans('partials_combofields_number.max').': ') !!}
    <input
            type="number" name="value" class="form-control" step="any"
            value="{{ \App\Http\Controllers\FieldController::getComboFieldOption($field, "Max", $fnum) }}"
            min="{{ \App\Http\Controllers\FieldController::getComboFieldOption($field, "Min", $fnum) }}">
</div>
<div class="form-group">
    {!! Form::submit(trans('partials_combofields_number.updatemax'),['class' => 'btn btn-primary form-control']) !!}
</div>
{!! Form::close() !!}

{!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateComboOptions', $field->pid, $field->fid, $field->flid]]) !!}
@include('fields.options.hiddens')
{!! Form::hidden('option','Increment') !!}
{!! Form::hidden('fieldnum',$fnum) !!}
<div class="form-group">
    {!! Form::label('value',trans('partials_combofields_number.inc').': ') !!}
    <input
            type="number" name="value" class="form-control" step="any"
            value="{{ \App\Http\Controllers\FieldController::getComboFieldOption($field, "Increment", $fnum) }}">
</div>
<div class="form-group">
    {!! Form::submit(trans('partials_combofields_number.updateinc'),['class' => 'btn btn-primary form-control']) !!}
</div>
{!! Form::close() !!}

{!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateComboOptions', $field->pid, $field->fid, $field->flid]]) !!}
@include('fields.options.hiddens')
{!! Form::hidden('option','Unit') !!}
{!! Form::hidden('fieldnum',$fnum) !!}
<div class="form-group">
    {!! Form::label('value',trans('partials_combofields_number.unit').': ') !!}
    {!! Form::text('value', \App\Http\Controllers\FieldController::getComboFieldOption($field,'Unit', $fnum), ['class' => 'form-control']) !!}
</div>
<div class="form-group">
    {!! Form::submit(trans('partials_combofields_number.updateunit'),['class' => 'btn btn-primary form-control']) !!}
</div>
{!! Form::close() !!}