@php
    if(isset($seq)) { //Combo List
        $fieldLabel = 'default_'.$seq;
        $fieldDivID = 'default_'.$seq.'_'.$flid;
        $dateObj = null;
    } else if($editRecord) {
        $fieldLabel = $flid;
        $fieldDivID = $flid;
        if(!is_null($record->{$flid})) {
            $stuff = explode('-',$record->{$flid});
            $dateObj = [
                'month' => $stuff[1],
                'day' => $stuff[2],
                'year' => $stuff[0]
            ];
        } else {
            $dateObj = null;
        }
    } else {
        $fieldLabel = $flid;
        $fieldDivID = $flid;
        $dateObj = $field['default'];
    }

    if(is_null($dateObj)) {
        $dateObj = [
            'month' => '',
            'day' => '',
            'year' => ''
        ];
    }
@endphp
<div class="form-group mt-xxxl">
    <label>@if(!isset($seq) && $field['required'])<span class="oval-icon"></span> @endif{{$field['name']}}</label>
    <input type="hidden" name={{$fieldLabel}} value="{{$fieldLabel}}">

    <div class="form-input-container">
        <div class="form-group inline-form-group">
            <div class="form-group">
                <label>Select Date</label>
                {!! Form::select(!isset($seq) ? 'month_'.$fieldLabel : '',['' => '',
                    '01' => '01 - '.date("F", mktime(0, 0, 0, 1, 10)), '02' => '02 - '.date("F", mktime(0, 0, 0, 2, 10)),
                    '03' => '03 - '.date("F", mktime(0, 0, 0, 3, 10)), '04' => '04 - '.date("F", mktime(0, 0, 0, 4, 10)),
                    '05' => '05 - '.date("F", mktime(0, 0, 0, 5, 10)), '06' => '06 - '.date("F", mktime(0, 0, 0, 6, 10)),
                    '07' => '07 - '.date("F", mktime(0, 0, 0, 7, 10)), '08' => '08 - '.date("F", mktime(0, 0, 0, 8, 10)),
                    '09' => '09 - '.date("F", mktime(0, 0, 0, 9, 10)), '10' => '10 - '.date("F", mktime(0, 0, 0, 10, 10)),
                    '11' => '11 - '.date("F", mktime(0, 0, 0, 11, 10)), '12' => '12 - '.date("F", mktime(0, 0, 0, 12, 10))],
                    sprintf('%02d', $dateObj['month']), ['class' => 'single-select preset-clear-chosen-js', 'data-placeholder'=>"Select a Month", 'id' => 'month_'.$fieldDivID]) !!}
            </div>

            <div class="form-group">
                <label class="invisible">Select Day</label>
                <select id="day_{{$fieldDivID}}" @if(!isset($seq))name="day_{{$fieldLabel}}"@endif class="single-select preset-clear-chosen-js" data-placeholder="Select a Day">
                    <option value=""></option>
                    @php
                        $i = 1;
                        while ($i <= 31) {
                            if($i==$dateObj['day'])
                                echo "<option value=" . $i . " selected>" . $i . "</option>";
                            else
                                echo "<option value=" . $i . ">" . $i . "</option>";
                            $i++;
                        }
                    @endphp
                </select>
            </div>

            <div class="form-group">
                <label class="invisible">Select Year</label>
                <select id="year_{{$fieldDivID}}" @if(!isset($seq))name="year_{{$fieldLabel}}"@endif class="single-select preset-clear-chosen-js" data-placeholder="Select a Year">
                    <option value=""></option>
                    @php
                        $i = $field['options']['Start'];
                        if ($i == 0)
                            $i = date("Y");

                        $j = $field['options']['End'];
                        if ($j == 0)
                            $j = date("Y");

                        while ($i <= $j) {
                            if($i==$dateObj['year'])
                                echo "<option value=" . $i . " selected>" . $i . "</option>";
                            else
                                echo "<option value=" . $i . ">" . $i . "</option>";
                            $i++;
                        }
                    @endphp
                </select>
            </div>
        </div>
    </div>
</div>
