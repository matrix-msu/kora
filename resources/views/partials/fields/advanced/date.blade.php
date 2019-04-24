{!! Form::hidden('advanced',true) !!}
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
            null, ['class' => 'single-select', 'data-placeholder'=>"Select a Month", 'id' => 'default_month']) !!}
    </div>

    <div class="form-group">
        <label>Default Day</label>
        <select name="default_day" id='default_day' class="single-select" data-placeholder="Select a Day">
            <option value=""></option>
            @php
                echo "<option value=" . 0 . ">Current Day</option>";
                $i = 1;
                while($i <= 31) {
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
                echo "<option value=" . 0 . ">Current Year</option>";
                $i = 1900;
                $j = 2030;
                while($i <= $j) {
                    echo "<option value=" . $i . ">" . $i . "</option>";
                    $i++;
                }
            @endphp
        </select>
    </div>
</div>

<div class="form-group mt-xl">
    {!! Form::label('format','Date Format') !!}
    {!! Form::select('format', ['MMDDYYYY' => 'MM DD, YYYY','DDMMYYYY' => 'DD MM YYYY','YYYYMMDD' => 'YYYY MM DD'], 'MMDDYYYY', ['class' => 'single-select']) !!}
</div>

<div class="form-group mt-xl half pr-m">
    {!! Form::label('start','Start Year') !!}
    <span class="error-message"></span>
    <div class="number-input-container number-input-container-js">
        {!! Form::input('number', 'start', 1900, ['class' => 'text-input start-year-js', 'placeholder' => 'Enter start year here', 'data-current-year-id' => 'start']) !!}
        {!! Form::input('hidden', 'start', 0, ['class' => 'hidden-current-year-js', 'disabled']) !!}
    </div>

    <div class="check-box-half mt-m">
        {!! Form::input('checkbox', 'start-current-year', null, ['class' => 'check-box-input current-year-js', 'data-current-year-id' => 'start']) !!}
        <span class="check"></span>
        <span class="placeholder">Use Current Year as Start Year</span>
    </div>
</div>

<div class="form-group mt-xl half pl-m">
    {!! Form::label('end','End Year') !!}
    <span class="error-message"></span>
    <div class="number-input-container number-input-container-js">
        {!! Form::input('number', 'end', 2030, ['class' => 'text-input end-year-js', 'placeholder' => 'Enter end year here', 'data-current-year-id' => 'end']) !!}
        {!! Form::input('hidden', 'end', 0, ['class' => 'hidden-current-year-js', 'disabled']) !!}
    </div>

    <div class="check-box-half mt-m">
        {!! Form::input('checkbox', 'end-current-year', null, ['class' => 'check-box-input current-year-js', 'data-current-year-id' => 'end']) !!}
        <span class="check"></span>
        <span class="placeholder">Use Current Year as End Year</span>
    </div>
</div>

@include("partials.fields.modals.changeDefaultYearModal")

<script>
    Kora.Inputs.Number();
    Kora.Fields.Options('Date');
</script>
