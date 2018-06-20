<div class="form-group mt-xl half pr-m">
    {!! Form::label('start_'.$fnum,'Start Year: ') !!}
    <span class="error-message"></span>
    {!! Form::input('number', 'start_'.$fnum, \App\ComboListField::getComboFieldOption($field,'Start',$fnum), ['class' => 'text-input']) !!}
</div>

<div class="form-group mt-xl half pl-m">
    {!! Form::label('end_'.$fnum,'End Year: ') !!}
    <span class="error-message"></span>
    {!! Form::input('number', 'end_'.$fnum, \App\ComboListField::getComboFieldOption($field,'End',$fnum), ['class' => 'text-input']) !!}
</div>