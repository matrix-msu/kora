@extends('fields.show')

@section('fieldOptions')
    <div class="form-group inline-form-group mt-xxxl">
        <div class="form-group">
            <label>Default Month</label>
            {!! Form::select('default_month',['' => '', '0' => 'Current Month',
                '01' => '01 - '.date("F", mktime(0, 0, 0, 1, 10)), '02' => '02 - '.date("F", mktime(0, 0, 0, 2, 10)),
                '03' => '03 - '.date("F", mktime(0, 0, 0, 3, 10)), '04' => '04 - '.date("F", mktime(0, 0, 0, 4, 10)),
                '05' => '05 - '.date("F", mktime(0, 0, 0, 5, 10)), '06' => '06 - '.date("F", mktime(0, 0, 0, 6, 10)),
                '07' => '07 - '.date("F", mktime(0, 0, 0, 7, 10)), '08' => '08 - '.date("F", mktime(0, 0, 0, 8, 10)),
                '09' => '09 - '.date("F", mktime(0, 0, 0, 9, 10)), '10' => '10 - '.date("F", mktime(0, 0, 0, 10, 10)),
                '11' => '11 - '.date("F", mktime(0, 0, 0, 11, 10)), '12' => '12 - '.date("F", mktime(0, 0, 0, 12, 10))],
                (!is_null($field['default']) ? sprintf('%02d', $field['default']['month']) : null), ['class' => 'single-select', 'data-placeholder'=>"Select a Month", 'id' => 'default_month']) !!}
        </div>

        <div class="form-group">
            <label>Default Day</label>
            <select name="default_day" id='default_day' class="single-select" data-placeholder="Select a Day">
                <option value=""></option>
                @php
                    if(!is_null($field['default']) && $field['default']['day'] === 0)
                        echo "<option value=" . 0 . " selected>Current Day</option>";
                    else
                        echo "<option value=" . 0 . ">Current Day</option>";

                    $i = 1;
                    while($i <= 31) {
                        if(!is_null($field['default']) && $field['default']['day'] == $i)
                            echo "<option value=" . $i . " selected>" . $i . "</option>";
                        else
                            echo "<option value=" . $i . ">" . $i . "</option>";
                        $i++;
                    }
                @endphp
            </select>
        </div>

        <div class="form-group">
            <label>Default Year</label>
            <select name="default_year" class="single-select default-year-js" data-placeholder="Select a Year">
                <option value=""></option>
                @php
                    if(!is_null($field['default']) && $field['default']['year'] === 0)
                        echo "<option value=" . 0 . " selected>Current Year</option>";
                    else
                        echo "<option value=" . 0 . ">Current Year</option>";

                    $i = $field['options']['Start'];
                    if ($i == 0)
                        $i = date("Y");

                    $j = $field['options']['End'];
                    if ($j == 0)
                        $j = date("Y");

                    while($i <= $j) {
                        if(!is_null($field['default']) && $field['default']['year'] == $i)
                            echo "<option value=" . $i . " selected>" . $i . "</option>";
                        else
                            echo "<option value=" . $i . ">" . $i . "</option>";
                        $i++;
                    }
                @endphp
            </select>
        </div>
    </div>
    
    @include('partials.fields.options.defaults.date')
@stop

@section('fieldOptionsJS')
    Kora.Inputs.Number();
    Kora.Fields.Options('Date');
@stop
