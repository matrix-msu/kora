<div class="form-group form-inline">
    {!! Form::label($field->flid, $field->name.': ') !!}
    @if($field->required==1)
        <b style="color:red;font-size:20px">*</b>
    @endif
    @if(\App\Http\Controllers\FieldController::getFieldOption($field, 'Circa')=='Yes')
        {!! Form::label('circa'.$field->flid,trans('records_fieldInput.circa').': ') !!}
        {!! Form::checkbox('circa_'.$field->flid,1,null, ['class' => 'form-control']) !!}
    @endif
    <input type="hidden" name={{$field->flid}} value="{{$field->flid}}">
    {!! Form::label('month_'.$field->flid,trans('records_fieldInput.month').': ') !!}
    {!! Form::select('month_'.$field->flid,['' => '',
            '1' => '01 - '.trans('records_fieldInput.jan'), '2' => '02 - '.trans('records_fieldInput.feb'),
            '3' => '03 - '.trans('records_fieldInput.mar'), '4' => '04 - '.trans('records_fieldInput.apr'),
            '5' => '05 - '.trans('records_fieldInput.may'), '6' => '06 - '.trans('records_fieldInput.june'),
            '7' => '07 - '.trans('records_fieldInput.july'), '8' => '08 - '.trans('records_fieldInput.aug'),
            '9' => '09 - '.trans('records_fieldInput.sep'), '10' => '10 - '.trans('records_fieldInput.oct'),
            '11' => '11 - '.trans('records_fieldInput.nov'), '12' => '12 - '.trans('records_fieldInput.dec')],
        explode('[M]',$field->default)[1], ['class' => 'form-control']) !!}
    {!! Form::label('day_'.$field->flid,trans('records_fieldInput.day').': ') !!}
    <select name="day_{{$field->flid}}" class="form-control">
        <option value=""></option>
        <?php
        $i = 1;
        while ($i <= 31)
        {
            if(explode('[D]',$field->default)[1]==$i){
                echo "<option value=" . $i . " selected>" . $i . "</option>";
            }else{
                echo "<option value=" . $i . ">" . $i . "</option>";
            }
            $i++;
        }
        ?>
    </select>
    {!! Form::label('year_'.$field->flid,trans('records_fieldInput.year').': ') !!}
    <select name="year_{{$field->flid}}" class="form-control">
        <option value=""></option>
        <?php
        $i = \App\Http\Controllers\FieldController::getFieldOption($field, 'Start');
        $j = \App\Http\Controllers\FieldController::getFieldOption($field, 'End');
        while ($i <= $j+1)
        {
            if(explode('[Y]',$field->default)[1]==$i){
                echo "<option value=" . $i . " selected>" . $i . "</option>";
            }else{
                echo "<option value=" . $i . ">" . $i . "</option>";
            }
            $i++;
        }
        ?>
    </select>
    @if(\App\Http\Controllers\FieldController::getFieldOption($field, 'Era')=='Yes')
        {!! Form::label('era'.$field->flid,trans('records_fieldInput.era').': ') !!}
        {!! Form::select('era_'.$field->flid,['CE'=>'CE','BCE'=>'BCE'],'CE', ['class' => 'form-control']) !!}
    @endif
</div>