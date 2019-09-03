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

<div class="form-group inline-checkbox-form-group mt-m">
    <label>Select Prefix (Optional)</label>
    <div class="check-box-half mr-m">
        <input type="checkbox" value="circa" class="check-box-input prefix-check-js" name="default_prefix">
        <span class="check"></span>
        <span class="placeholder">Circa</span>
    </div>

    <div class="check-box-half mr-m">
        <input type="checkbox" value="pre" class="check-box-input prefix-check-js" name="default_prefix">
        <span class="check"></span>
        <span class="placeholder">Pre</span>
    </div>

    <div class="check-box-half mr-m">
        <input type="checkbox" value="post" class="check-box-input prefix-check-js" name="default_prefix">
        <span class="check"></span>
        <span class="placeholder">Post</span>
    </div>
</div>

<div class="form-group inline-checkbox-form-group mt-xl">
    <label>Select Calendar/Date Notation</label>
    <div class="check-box-half mr-m">
        <input type="checkbox" value="CE" class="check-box-input era-check-js" name="default_era" checked>
        <span class="check"></span>
        <span class="placeholder">CE</span>
    </div>

    <div class="check-box-half mr-m">
        <input type="checkbox" value="BCE" class="check-box-input era-check-js" name="default_era">
        <span class="check"></span>
        <span class="placeholder">BCE</span>
    </div>

    <div class="check-box-half mr-m">
        <input type="checkbox" value="BP" class="check-box-input era-check-js" name="default_era">
        <span class="check"></span>
        <span class="placeholder">BP</span>
    </div>

    <div class="check-box-half">
        <input type="checkbox" value="KYA BP" class="check-box-input era-check-js" name="default_era">
        <span class="check"></span>
        <span class="placeholder">KYA BP</span>
    </div>
</div>

<div class="form-group mt-xl">
    {!! Form::label('format','Date Format') !!}
    {!! Form::select('format', ['YYYYMMDD' => 'YYYY-MM-DD','MMDDYYYY' => 'MM-DD-YYYY','DDMMYYYY' => 'DD-MM-YYYY'], 'YYYYMMDD', ['class' => 'single-select']) !!}
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
        {!! Form::input('checkbox', 'start-current-year', null, ['class' => 'check-box-input current-year-js', 'data-current-year-id' => 'end']) !!}
        <span class="check"></span>
        <span class="placeholder">Use Current Year as End Year</span>
    </div>
</div>

<div class="form-group mt-xl">
    {!! Form::label('prefix','Show Prefixes?') !!}
    {!! Form::select('prefix', [0 => 'No', 1 => 'Yes'], 0, ['class' => 'single-select']) !!}
</div>

<div class="form-group mt-xl">
    {!! Form::label('era','Show Calendar/Date Notation?') !!}
    {!! Form::select('era', [0 => 'No', 1 => 'Yes'], 0, ['class' => 'single-select']) !!}
</div>

@include("partials.fields.modals.changeDefaultYearModal")

<script>
    Kora.Inputs.Number();
    Kora.Fields.Options('Date');
</script>
