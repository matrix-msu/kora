<div class="form-group">
    {!! Form::label('regex_'.$fnum,trans('partials_combofields_text.regex').': ') !!}
    {!! Form::text('regex_'.$fnum, \App\Http\Controllers\FieldController::getComboFieldOption($field,'Regex',$fnum), ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('multi_'.$fnum,trans('partials_combofields_text.multi').': ') !!}
    {!! Form::select('multi_'.$fnum, ['no','yes'], \App\Http\Controllers\FieldController::getComboFieldOption($field,'MultiLine',$fnum), ['class' => 'form-control']) !!}
</div>