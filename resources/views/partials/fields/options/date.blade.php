@extends('fields.show')

@section('fieldOptions')
    <div class="form-group">
        {!! Form::label('default_month','Default Month: ') !!}
        {!! Form::select('default_month',['' => '', '0' => 'Current Month',
            '1' => '01 - '.date("F", mktime(0, 0, 0, 1, 10)), '2' => '02 - '.date("F", mktime(0, 0, 0, 2, 10)),
            '3' => '03 - '.date("F", mktime(0, 0, 0, 3, 10)), '4' => '04 - '.date("F", mktime(0, 0, 0, 4, 10)),
            '5' => '05 - '.date("F", mktime(0, 0, 0, 5, 10)), '6' => '06 - '.date("F", mktime(0, 0, 0, 6, 10)),
            '7' => '07 - '.date("F", mktime(0, 0, 0, 7, 10)), '8' => '08 - '.date("F", mktime(0, 0, 0, 8, 10)),
            '9' => '09 - '.date("F", mktime(0, 0, 0, 9, 10)), '10' => '10 - '.date("F", mktime(0, 0, 0, 10, 10)),
            '11' => '11 - '.date("F", mktime(0, 0, 0, 11, 10)), '12' => '12 - '.date("F", mktime(0, 0, 0, 12, 10))],
            ($field->default=='' ? null : explode('[M]',$field->default)[1]), ['class' => 'single-select', 'data-placeholder'=>"Select a Month"]) !!}
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('default_day','Default Day: ') !!}
        <select name="default_day" class="single-select" data-placeholder="Select a Day">
            <option value=""></option>
            <?php
            if($field->default!='' && explode('[D]',$field->default)[1]=='0'){
                echo "<option value=" . 0 . " selected>Current Day</option>";
            }else{
                echo "<option value=" . 0 . ">Current Day</option>";
            }
            $i = 1;
            while ($i <= 31)
            {
                if($field->default!='' && explode('[D]',$field->default)[1]==$i){
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
        {!! Form::label('default_year','Default Year: ') !!}
        <select name="default_year" class="single-select default-year-js" data-placeholder="Select a Year">
            <option value=""></option>
            <?php
            if($field->default!='' && explode('[Y]',$field->default)[1]=='0'){
                echo "<option value=" . 0 . " selected>Current Year</option>";
            }else{
                echo "<option value=" . 0 . ">Current Year</option>";
            }
            $i = \App\Http\Controllers\FieldController::getFieldOption($field, 'Start');
            $j = \App\Http\Controllers\FieldController::getFieldOption($field, 'End');
            while ($i <= $j)
            {
                if($field->default!='' && explode('[Y]',$field->default)[1]==$i){
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
        {!! Form::label('format','Date Format: ') !!}
        {!! Form::select('format',
            ['MMDDYYYY' => 'MM DD, YYYY','DDMMYYYY' => 'DD MM YYYY','YYYYMMDD' => 'YYYY MM DD'],
            \App\Http\Controllers\FieldController::getFieldOption($field,'Format'), ['class' => 'single-select']) !!}
    </div>

    <div class="form-group mt-xl half pr-m">
        {!! Form::label('start','Start Year: ') !!}
        {!! Form::input('number', 'start', \App\Http\Controllers\FieldController::getFieldOption($field,'Start'), ['class' => 'text-input start-year-js']) !!}
    </div>

    <div class="form-group mt-xl half pl-m">
        {!! Form::label('end','End Year: ') !!}
        {!! Form::input('number', 'end', \App\Http\Controllers\FieldController::getFieldOption($field,'End'), ['class' => 'text-input end-year-js']) !!}
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('circa','Show Circa Approximations?: ') !!}
        {!! Form::select('circa', ['No' => 'No','Yes' => 'Yes'], \App\Http\Controllers\FieldController::getFieldOption($field,'Circa'), ['class' => 'single-select']) !!}
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('era','Show Era?: ') !!}
        {!! Form::select('era', ['No' => 'No','Yes' => 'Yes'], \App\Http\Controllers\FieldController::getFieldOption($field,'Era'), ['class' => 'single-select']) !!}
    </div>
@stop

@section('fieldOptionsJS')
    Kora.Fields.Options('Date');
@stop