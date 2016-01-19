{!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateComboOptions', $field->pid, $field->fid, $field->flid]]) !!}
@include('fields.options.hiddens')
{!! Form::hidden('option','Regex') !!}
{!! Form::hidden('fieldnum',$fnum) !!}
<div class="form-group">
    {!! Form::label('value',trans('options_combofields_text.regex').': ') !!}
    {!! Form::text('value', \App\Http\Controllers\FieldController::getComboFieldOption($field,'Regex',$fnum), ['class' => 'form-control']) !!}
</div>
<div class="form-group">
    {!! Form::submit(trans('options_combofields_text.updateregex'),['class' => 'btn btn-primary form-control']) !!}
</div>
{!! Form::close() !!}

{!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateComboOptions', $field->pid, $field->fid, $field->flid]]) !!}
@include('fields.options.hiddens')
{!! Form::hidden('option','MultiLine') !!}
{!! Form::hidden('fieldnum',$fnum) !!}
<div class="form-group">
    {!! Form::label('value',trans('options_combofields_text.multi').': ') !!}
    {!! Form::select('value', ['no','yes'], \App\Http\Controllers\FieldController::getComboFieldOption($field,'MultiLine',$fnum), ['class' => 'form-control']) !!}
</div>
<div class="form-group">
    {!! Form::submit(trans('options_combofields_text.updatemulti'),['class' => 'btn btn-primary form-control']) !!}
</div>
{!! Form::close() !!}