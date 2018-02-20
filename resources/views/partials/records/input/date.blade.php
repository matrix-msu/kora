<div class="form-group mt-xl">
    <label>@if($field->required==1)<span class="oval-icon"></span> @endif{{$field->name}}: </label>
</div>
<input type="hidden" name={{$field->flid}} value="{{$field->flid}}">

@if(\App\Http\Controllers\FieldController::getFieldOption($field, 'Circa')=='Yes')
    <div class="form-group mt-xl">
        <label for="{{'circa'.$field->flid}}">Mark this date as an approximate?</label>
        <div class="check-box">
            <input type="checkbox" value="1" id="preset" class="check-box-input" name="{{'circa'.$field->flid}}"/>
            <div class="check-box-background"></div>
            <span class="check"></span>
            <span class="placeholder">Value is <strong>not</strong> approximate</span>
            <span class="placeholder-alt">Value is approximate</span>
        </div>
    </div>
@endif

<?php
    $defMonth = $field->default=='' ? null : explode('[M]',$field->default)[1];
    if($defMonth=='0')
        $defMonth = \Carbon\Carbon::now()->month;
?>
<div class="form-group mt-xl">
    {!! Form::label('month_'.$field->flid,'Month: ') !!}
    {!! Form::select('month_'.$field->flid,['' => '',
        '1' => '01 - '.date("F", mktime(0, 0, 0, 1, 10)), '2' => '02 - '.date("F", mktime(0, 0, 0, 2, 10)),
        '3' => '03 - '.date("F", mktime(0, 0, 0, 3, 10)), '4' => '04 - '.date("F", mktime(0, 0, 0, 4, 10)),
        '5' => '05 - '.date("F", mktime(0, 0, 0, 5, 10)), '6' => '06 - '.date("F", mktime(0, 0, 0, 6, 10)),
        '7' => '07 - '.date("F", mktime(0, 0, 0, 7, 10)), '8' => '08 - '.date("F", mktime(0, 0, 0, 8, 10)),
        '9' => '09 - '.date("F", mktime(0, 0, 0, 9, 10)), '10' => '10 - '.date("F", mktime(0, 0, 0, 10, 10)),
        '11' => '11 - '.date("F", mktime(0, 0, 0, 11, 10)), '12' => '12 - '.date("F", mktime(0, 0, 0, 12, 10))],
        $defMonth, ['class' => 'single-select']) !!}
</div>

<div class="form-group mt-xl">
    {!! Form::label('day_'.$field->flid,'Day: ') !!}
    <select name="day_{{$field->flid}}" class="single-select">
        <option value=""></option>
        <?php
            $currDay=0;
            if($field->default!='' && explode('[D]',$field->default)[1]=='0'){
                $currDay=\Carbon\Carbon::now()->day;
            }
            $i = 1;
            while ($i <= 31)
            {
                if(($field->default!='' && explode('[D]',$field->default)[1]==$i) | $i==$currDay){
                    echo "<option value=" . $i . " selected>" . $i . "</option>";
                }else{
                    echo "<option value=" . $i . ">" . $i . "</option>";
                }
                $i++;
            }
        ?>
    </select>
</div>

<div class="form-group mt-xl">
    {!! Form::label('year_'.$field->flid,'Year: ') !!}
    <select name="year_{{$field->flid}}" class="single-select">
        <option value=""></option>
        <?php
            $currYear=0;
            if($field->default!='' && explode('[Y]',$field->default)[1]=='0'){
                $currYear=\Carbon\Carbon::now()->year;
            }
            $i = \App\Http\Controllers\FieldController::getFieldOption($field, 'Start');
            $j = \App\Http\Controllers\FieldController::getFieldOption($field, 'End');
            while ($i <= $j)
            {
                if(($field->default!='' && explode('[Y]',$field->default)[1]==$i) | $i==$currYear){
                    echo "<option value=" . $i . " selected>" . $i . "</option>";
                }else{
                    echo "<option value=" . $i . ">" . $i . "</option>";
                }
                $i++;
            }
        ?>
    </select>
</div>

@if(\App\Http\Controllers\FieldController::getFieldOption($field, 'Era')=='Yes')
    <div class="form-group mt-xl">
        {!! Form::label('era'.$field->flid,'Era: ') !!}
        {!! Form::select('era_'.$field->flid,['CE'=>'CE','BCE'=>'BCE'],'CE', ['class' => 'single-select']) !!}
    </div>
@endif