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
        {!! Form::label('circa'.$field->flid,'Circa: ') !!}
        {!! Form::checkbox('circa_'.$field->flid,1,$circa, ['class' => 'form-control']) !!}
    @endif
    <input type="hidden" name={{$field->flid}} value="{{$field->flid}}">
    {!! Form::label('month_'.$field->flid,'Month: ') !!}
    {!! Form::select('month_'.$field->flid,['' => '','1' => '01 - January', '2' => '02 - February',
        '3' => '03 - March', '4' => '04 - April', '5' => '05 - May', '6' => '06 - June',
        '7' => '07 - July', '8' => '08 - August', '9' => '09 - September',
        '10' => '10 - October', '11' => '11 - November', '12' => '12 - December'],
        $month, ['class' => 'form-control']) !!}
    {!! Form::label('day_'.$field->flid,'Day: ') !!}
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
    {!! Form::label('year_'.$field->flid,'Year: ') !!}
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
        {!! Form::label('era'.$field->flid,'Era: ') !!}
        {!! Form::select('era_'.$field->flid,['CE'=>'CE','BCE'=>'BCE'],$era, ['class' => 'form-control']) !!}
    @endif
</div>