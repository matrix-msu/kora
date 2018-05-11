<div class="form-group mt-xl">
    {!! Form::label($field->flid.'_input',$field->name.': ') !!}
    {!! Form::select($field->flid."_begin_month",['' => '',
        '1' => '01 - '.date("F", mktime(0, 0, 0, 1, 10)), '2' => '02 - '.date("F", mktime(0, 0, 0, 2, 10)),
        '3' => '03 - '.date("F", mktime(0, 0, 0, 3, 10)), '4' => '04 - '.date("F", mktime(0, 0, 0, 4, 10)),
        '5' => '05 - '.date("F", mktime(0, 0, 0, 5, 10)), '6' => '06 - '.date("F", mktime(0, 0, 0, 6, 10)),
        '7' => '07 - '.date("F", mktime(0, 0, 0, 7, 10)), '8' => '08 - '.date("F", mktime(0, 0, 0, 8, 10)),
        '9' => '09 - '.date("F", mktime(0, 0, 0, 9, 10)), '10' => '10 - '.date("F", mktime(0, 0, 0, 10, 10)),
        '11' => '11 - '.date("F", mktime(0, 0, 0, 11, 10)), '12' => '12 - '.date("F", mktime(0, 0, 0, 12, 10))],
        "", ['class' => 'single-select', 'data-placeholder'=>"Select a Start Month"])
    !!}
</div>

<div class="form-group mt-sm">
    <select name="{{$field->flid}}_begin_day" class="single-select" data-placeholder="Select a Start Day">
        <option value=""></option>
        <?php
        $i = 1;
        while ($i <= 31) {
            echo "<option value=" . $i . ">" . $i . "</option>";
            $i++;
        }
        ?>
    </select>
</div>

<div class="form-group mt-sm">
    <select name="{{$field->flid}}_begin_year" class="single-select" data-placeholder="Select a Start Year">
        <option value=""></option>
        <?php
        $i = \App\Http\Controllers\FieldController::getFieldOption($field, 'Start');
        $j = \App\Http\Controllers\FieldController::getFieldOption($field, 'End');
        while ($i <= $j) {
            echo "<option value=" . $i . ">" . $i . "</option>";
            $i++;
        }
        ?>
    </select>
</div>

<div class="form-group mt-sm">
    {!! Form::select($field->flid."_end_month",['' => '',
        '1' => '01 - '.date("F", mktime(0, 0, 0, 1, 10)), '2' => '02 - '.date("F", mktime(0, 0, 0, 2, 10)),
        '3' => '03 - '.date("F", mktime(0, 0, 0, 3, 10)), '4' => '04 - '.date("F", mktime(0, 0, 0, 4, 10)),
        '5' => '05 - '.date("F", mktime(0, 0, 0, 5, 10)), '6' => '06 - '.date("F", mktime(0, 0, 0, 6, 10)),
        '7' => '07 - '.date("F", mktime(0, 0, 0, 7, 10)), '8' => '08 - '.date("F", mktime(0, 0, 0, 8, 10)),
        '9' => '09 - '.date("F", mktime(0, 0, 0, 9, 10)), '10' => '10 - '.date("F", mktime(0, 0, 0, 10, 10)),
        '11' => '11 - '.date("F", mktime(0, 0, 0, 11, 10)), '12' => '12 - '.date("F", mktime(0, 0, 0, 12, 10))],
        "", ['class' => 'single-select', 'data-placeholder'=>"Select a End Month"])
    !!}
</div>

<div class="form-group mt-sm">
    <select name="{{$field->flid}}_end_day" class="single-select" data-placeholder="Select a End Day">
        <option value=""></option>
        <?php
        $i = 1;
        while ($i <= 31) {
            echo "<option value=" . $i . ">" . $i . "</option>";
            $i++;
        }
        ?>
    </select>
</div>

<div class="form-group mt-sm">
    <select name="{{$field->flid}}_end_year" class="single-select" data-placeholder="Select a End Year">
        <option value=""></option>
        <?php
        $i = \App\Http\Controllers\FieldController::getFieldOption($field, 'Start');
        $j = \App\Http\Controllers\FieldController::getFieldOption($field, 'End');
        while ($i <= $j) {
            echo "<option value=" . $i . ">" . $i . "</option>";
            $i++;
        }
        ?>
    </select>
</div>