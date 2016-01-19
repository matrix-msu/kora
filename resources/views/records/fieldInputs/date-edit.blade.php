<div class="form-group form-inline">
    <?php
    if($date==null){
        $month = '';
        $day = '';
        $year = '';
        $circa=0;
        $era = 'CE';
    }else{
        $month = $date->month;
        $day = $date->day;
        $year = $date->year;
        $circa = $date->circa;
        $era = $date->era;
    }
    ?>
    {!! Form::label($field->flid, $field->name.': ') !!}
    @if($field->required==1)
        <b style="color:red;font-size:20px">*</b>
    @endif
    @if(\App\Http\Controllers\FieldController::getFieldOption($field, 'Circa')=='Yes')
        {!! Form::label('circa'.$field->flid,trans('record_fieldInputs.circa').': ') !!}
        {!! Form::checkbox('circa_'.$field->flid,1,$circa, ['class' => 'form-control']) !!}
    @endif
    <input type="hidden" name={{$field->flid}} value="{{$field->flid}}">
    {!! Form::label('month_'.$field->flid,trans('record_fieldInputs.month').': ') !!}
    {!! Form::select('month_'.$field->flid,['' => '',
            '1' => '01 - '.trans('record_fieldInputs.jan'), '2' => '02 - '.trans('record_fieldInputs.feb'),
            '3' => '03 - '.trans('record_fieldInputs.mar'), '4' => '04 - '.trans('record_fieldInputs.apr'),
            '5' => '05 - '.trans('record_fieldInputs.may'), '6' => '06 - '.trans('record_fieldInputs.june'),
            '7' => '07 - '.trans('record_fieldInputs.july'), '8' => '08 - '.trans('record_fieldInputs.aug'),
            '9' => '09 - '.trans('record_fieldInputs.sep'), '10' => '10 - '.trans('record_fieldInputs.oct'),
            '11' => '11 - '.trans('record_fieldInputs.nov'), '12' => '12 - '.trans('record_fieldInputs.dec')],
        $month, ['class' => 'form-control']) !!}
    {!! Form::label('day_'.$field->flid,trans('record_fieldInputs.day').': ') !!}
    <select name="day_{{$field->flid}}" class="form-control">
        <option value=""></option>
        <?php
        $i = 1;
        while ($i <= 31)
        {
            if($day==$i){
                echo "<option value=" . $i . " selected>" . $i . "</option>";
            }else{
                echo "<option value=" . $i . ">" . $i . "</option>";
            }
            $i++;
        }
        ?>
    </select>
    {!! Form::label('year_'.$field->flid,trans('record_fieldInputs.year').': ') !!}
    <select name="year_{{$field->flid}}" class="form-control">
        <option value=""></option>
        <?php
        $i = \App\Http\Controllers\FieldController::getFieldOption($field, 'Start');
        $j = \App\Http\Controllers\FieldController::getFieldOption($field, 'End');
        while ($i <= $j+1)
        {
            if($year==$i){
                echo "<option value=" . $i . " selected>" . $i . "</option>";
            }else{
                echo "<option value=" . $i . ">" . $i . "</option>";
            }
            $i++;
        }
        ?>
    </select>
    @if(\App\Http\Controllers\FieldController::getFieldOption($field, 'Era')=='Yes')
        {!! Form::label('era'.$field->flid,trans('record_fieldInputs.era').': ') !!}
        {!! Form::select('era_'.$field->flid,['CE'=>'CE','BCE'=>'BCE'],$era, ['class' => 'form-control']) !!}
    @endif
</div>