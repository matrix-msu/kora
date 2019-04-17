@extends('fields.show')

@section('fieldOptions')
    <div class="form-group date-input-form-group date-input-form-group-js mt-xxxl">
        <label>Default DateTime</label>
        <div class="form-input-container">
            <div class="form-group">
                <label>Select DateTime</label>
                <div class="date-inputs-container date-inputs-container-js">
                    {!! Form::select('default_month',['' => '', '0' => 'Current Month',
                        '01' => '01 - '.date("F", mktime(0, 0, 0, 1, 10)), '02' => '02 - '.date("F", mktime(0, 0, 0, 2, 10)),
                        '03' => '03 - '.date("F", mktime(0, 0, 0, 3, 10)), '04' => '04 - '.date("F", mktime(0, 0, 0, 4, 10)),
                        '05' => '05 - '.date("F", mktime(0, 0, 0, 5, 10)), '06' => '06 - '.date("F", mktime(0, 0, 0, 6, 10)),
                        '07' => '07 - '.date("F", mktime(0, 0, 0, 7, 10)), '08' => '08 - '.date("F", mktime(0, 0, 0, 8, 10)),
                        '09' => '09 - '.date("F", mktime(0, 0, 0, 9, 10)), '10' => '10 - '.date("F", mktime(0, 0, 0, 10, 10)),
                        '11' => '11 - '.date("F", mktime(0, 0, 0, 11, 10)), '12' => '12 - '.date("F", mktime(0, 0, 0, 12, 10))],
                        (!is_null($field['default']) ? $field['default']['month'] : null), ['class' => 'single-select', 'data-placeholder'=>"Select a Month", 'id' => 'default_month']) !!}

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

                    <select name="default_year" class="single-select default-year-js" data-placeholder="Select a Year">
                        <option value=""></option>
                        @php
                            if(!is_null($field['default']) && $field['default']['year'] === 0)
                                echo "<option value=" . 0 . " selected>Current Year</option>";
                            else
                                echo "<option value=" . 0 . ">Current Year</option>";

                            $i = $field['options']['Start'];
                            $j = $field['options']['End'];
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

                <div class="date-inputs-container date-inputs-container-js">
                    <select name="default_hour" id='default_hour' class="single-select" data-placeholder="Select an Hour">
                        @php
                            for($i=0;$i<24;$i++) {
                                if(!is_null($field['default']) && $field['default']['hour'] == $i)
                                    echo "<option value=" . $i . " selected>" . $i . " hours</option>";
                                else
                                    echo "<option value=" . $i . ">" . $i . " hours</option>";
                            }
                        @endphp
                    </select>

                    <select name="default_minute" id='default_minute' class="single-select" data-placeholder="Select a Minute">
                        @php
                            for($i=0;$i<60;$i++) {
                                if(!is_null($field['default']) && $field['default']['minute'] == $i)
                                    echo "<option value=" . $i . " selected>" . $i . " minutes</option>";
                                else
                                    echo "<option value=" . $i . ">" . $i . " minutes</option>";
                            }
                        @endphp
                    </select>

                    <select name="default_second" id='default_second' class="single-select" data-placeholder="Select a Second">
                        @php
                            for($i=0;$i<60;$i++) {
                                if(!is_null($field['default']) && $field['default']['second'] == $i)
                                    echo "<option value=" . $i . " selected>" . $i . " seconds</option>";
                                else
                                    echo "<option value=" . $i . ">" . $i . " seconds</option>";
                            }
                        @endphp
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('format','Date Format') !!}
        {!! Form::select('format', ['MMDDYYYY' => 'MM DD, YYYY','DDMMYYYY' => 'DD MM YYYY','YYYYMMDD' => 'YYYY MM DD'], $field['options']['Format'], ['class' => 'single-select']) !!}
    </div>

    <div class="form-group mt-xl half pr-m">
        {!! Form::label('start','Start Year') !!}
        <span class="error-message"></span>
        <div class="number-input-container number-input-container-js">
            {!! Form::input('number', 'start', $field['options']['Start'], ['class' => 'text-input start-year-js', 'placeholder' => 'Enter start year here']) !!}
        </div>
    </div>

    <div class="form-group mt-xl half pl-m">
        {!! Form::label('end','End Year') !!}
        <span class="error-message"></span>
        <div class="number-input-container number-input-container-js">
            {!! Form::input('number', 'end', $field['options']['End'], ['class' => 'text-input end-year-js', 'placeholder' => 'Enter end year here']) !!}
        </div>
    </div>
@stop

@section('fieldOptionsJS')
    Kora.Fields.Options('Date');
    Kora.Inputs.Number();
@stop
