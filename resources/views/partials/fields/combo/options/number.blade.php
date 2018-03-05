<div class="form-group mt-xl">
    {!! Form::label('min_'.$fnum,'Minimum Value: ') !!}
    <input type="number" name="min_{{$fnum}}" class="text-input" step="any" id="min"
           value="{{ \App\ComboListField::getComboFieldOption($field, "Min", $fnum) }}">
</div>

<div class="form-group mt-xl">
    {!! Form::label('max_'.$fnum,'Max Value: ') !!}
    <input type="number" name="max_{{$fnum}}" class="text-input" step="any" id="max"
           value="{{ \App\ComboListField::getComboFieldOption($field, "Max", $fnum) }}">
</div>

<div class="form-group mt-xl">
    {!! Form::label('inc_'.$fnum,'Value Increment: ') !!}
    <input type="number" name="inc_{{$fnum}}" class="text-input" step="any" id="inc"
           value="{{ \App\ComboListField::getComboFieldOption($field, "Increment", $fnum) }}">
</div>

<div class="form-group mt-xl">
    {!! Form::label('unit_'.$fnum,'Unit of Measurement: ') !!}
    {!! Form::text('unit_'.$fnum, \App\ComboListField::getComboFieldOption($field, "Unit", $fnum), ['class' => 'text-input']) !!}
</div>