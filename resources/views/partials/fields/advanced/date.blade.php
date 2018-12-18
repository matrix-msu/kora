{!! Form::hidden('advanced',true) !!}
<div class="form-group date-input-form-group mt-xxxl">
    <label>Select Date</label>

    <div class="date-inputs-container date-inputs-container-js">
        {!! Form::select('default_month',['' => '', '0' => 'Current Month',
            '1' => '01 - '.date("F", mktime(0, 0, 0, 1, 10)), '2' => '02 - '.date("F", mktime(0, 0, 0, 2, 10)),
            '3' => '03 - '.date("F", mktime(0, 0, 0, 3, 10)), '4' => '04 - '.date("F", mktime(0, 0, 0, 4, 10)),
            '5' => '05 - '.date("F", mktime(0, 0, 0, 5, 10)), '6' => '06 - '.date("F", mktime(0, 0, 0, 6, 10)),
            '7' => '07 - '.date("F", mktime(0, 0, 0, 7, 10)), '8' => '08 - '.date("F", mktime(0, 0, 0, 8, 10)),
            '9' => '09 - '.date("F", mktime(0, 0, 0, 9, 10)), '10' => '10 - '.date("F", mktime(0, 0, 0, 10, 10)),
            '11' => '11 - '.date("F", mktime(0, 0, 0, 11, 10)), '12' => '12 - '.date("F", mktime(0, 0, 0, 12, 10))],
            null, ['class' => 'single-select', 'data-placeholder'=>"Select a Month"]) !!}

        <select name="default_day" class="single-select" data-placeholder="Select a Day">
            <option value=""></option>
            <?php
            echo "<option value=" . 0 . ">Current Day</option>";
            $i = 1;
            while($i <= 31) {
                echo "<option value=" . $i . ">" . $i . "</option>";
                $i++;
            }
            ?>
        </select>

        <select name="default_year" class="single-select default-year-js" data-placeholder="Select a Year">
            <option value=""></option>
            <?php
            echo "<option value=" . 0 . ">Current Year</option>";
            $i = 1900;
            $j = 2020;
            while($i <= $j) {
                echo "<option value=" . $i . ">" . $i . "</option>";
                $i++;
            }
            ?>
        </select>
    </div>
</div>

<div class="form-group mt-xl">
    {!! Form::label('format','Date Format') !!}
    {!! Form::select('format',
        ['MMDDYYYY' => 'MM DD, YYYY','DDMMYYYY' => 'DD MM YYYY','YYYYMMDD' => 'YYYY MM DD'],
        'MMDDYYYY', ['class' => 'single-select']) !!}
</div>

<div class="form-group mt-xl half pr-m">
    {!! Form::label('start','Start Year') !!}
    <span class="error-message"></span>
    <div class="number-input-container number-input-container-js">
        {!! Form::input('number', 'start', null, ['class' => 'text-input start-year-js', 'placeholder' => 'Enter start year here']) !!}
    </div>
</div>

<div class="form-group mt-xl half pl-m">
    {!! Form::label('end','End Year') !!}
    <span class="error-message"></span>
    <div class="number-input-container number-input-container-js">
        {!! Form::input('number', 'end', null, ['class' => 'text-input end-year-js', 'placeholder' => 'Enter end year here']) !!}
    </div>
</div>

<div class="form-group mt-xl">
    {!! Form::label('circa','Show Circa Approximations?') !!}
    {!! Form::select('circa', ['No' => 'No','Yes' => 'Yes'], 'No', ['class' => 'single-select']) !!}
</div>

<div class="form-group mt-xl">
    {!! Form::label('era','Show Calendar/Date Notation?') !!}
    {!! Form::select('era', ['No' => 'No','Yes' => 'Yes'], 'No', ['class' => 'single-select']) !!}
</div>

<script>
    Kora.Fields.Options('Date');
    Kora.Inputs.Number();
</script>
