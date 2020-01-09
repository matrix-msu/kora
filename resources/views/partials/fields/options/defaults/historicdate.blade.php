@php
    if(isset($seq)) { //Combo List
        $seq = '_' . $seq;
        $title = $cfName.' ';
        $default = null;
        $defClass = 'default-input-js';
    } else {
        $seq = '';
        $title = '';
        $default = $field['default'];
        $defClass = '';
    }
@endphp
<div class="form-group inline-form-group mt-xxxl">
    <div class="form-group">
        <label>{{$title}}Default Month</label>
        @php
            $preDisabled = (!is_null($default) && ($default['era'] == 'BP' | $default['era'] == 'KYA BP'));
        @endphp

        {!! Form::select('default_month'.$seq,['' => '', '0' => 'Current Month',
            '01' => '01 - '.date("F", mktime(0, 0, 0, 1, 10)), '02' => '02 - '.date("F", mktime(0, 0, 0, 2, 10)),
            '03' => '03 - '.date("F", mktime(0, 0, 0, 3, 10)), '04' => '04 - '.date("F", mktime(0, 0, 0, 4, 10)),
            '05' => '05 - '.date("F", mktime(0, 0, 0, 5, 10)), '06' => '06 - '.date("F", mktime(0, 0, 0, 6, 10)),
            '07' => '07 - '.date("F", mktime(0, 0, 0, 7, 10)), '08' => '08 - '.date("F", mktime(0, 0, 0, 8, 10)),
            '09' => '09 - '.date("F", mktime(0, 0, 0, 9, 10)), '10' => '10 - '.date("F", mktime(0, 0, 0, 10, 10)),
            '11' => '11 - '.date("F", mktime(0, 0, 0, 11, 10)), '12' => '12 - '.date("F", mktime(0, 0, 0, 12, 10))],
            (!is_null($default) ? sprintf('%02d', $default['month']) : null), ['class' => 'single-select '.$defClass, 'data-placeholder'=>"Select a Month", 'id' => 'default_month'.$seq, 'disabled' => $preDisabled]) !!}
    </div>

    <div class="form-group">
        <label>{{$title}}Default Day</label>
        <select name="default_day{{$seq}}" id='default_day{{$seq}}' class="single-select {{$defClass}}" data-placeholder="Select a Day" {{ $preDisabled ? 'disabled' : '' }}>
            <option value=""></option>
            @php
                if(!is_null($default) && $default['day'] === 0)
                    echo "<option value=" . 0 . " selected>Current Day</option>";
                else
                    echo "<option value=" . 0 . ">Current Day</option>";

                $i = 1;
                while($i <= 31) {
                    if(!is_null($default) && $default['day'] == $i)
                        echo "<option value=" . $i . " selected>" . $i . "</option>";
                    else
                        echo "<option value=" . $i . ">" . $i . "</option>";
                    $i++;
                }
            @endphp
        </select>
    </div>

    <div class="form-group">
        <label>{{$title}}Default Year</label>
        <select name="default_year{{$seq}}" class="single-select default-year-js {{$defClass}}" data-placeholder="Select a Year">
            <option value=""></option>
            @php
                if(!is_null($default) && $default['year'] === 0)
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
                    if(!is_null($default) && $default['year'] == $i)
                        echo "<option value=" . $i . " selected>" . $i . "</option>";
                    else
                        echo "<option value=" . $i . ">" . $i . "</option>";
                    $i++;
                }
            @endphp
        </select>
    </div>
</div>

<div class="form-group inline-checkbox-form-group mt-xl">
    <label>Select Prefix (Optional)</label>
    <div class="check-box-half mr-m">
        <input type="checkbox" value="circa" class="check-box-input prefix-check-js {{$defClass}}" name="default_prefix{{$seq}}"
                {{ ((!is_null($default) && $default['prefix']=='circa') ? 'checked' : '') }}>
        <span class="check"></span>
        <span class="placeholder">Circa</span>
    </div>

    <div class="check-box-half mr-m">
        <input type="checkbox" value="pre" class="check-box-input prefix-check-js {{$defClass}}" name="default_prefix{{$seq}}"
                {{ ((!is_null($default) && $default['prefix']=='pre') ? 'checked' : '') }}>
        <span class="check"></span>
        <span class="placeholder">Pre</span>
    </div>

    <div class="check-box-half mr-m">
        <input type="checkbox" value="post" class="check-box-input prefix-check-js {{$defClass}}" name="default_prefix{{$seq}}"
                {{ ((!is_null($default) && $default['prefix']=='post') ? 'checked' : '') }}>
        <span class="check"></span>
        <span class="placeholder">Post</span>
    </div>
</div>

<div class="form-group inline-checkbox-form-group mt-xl">
    <label>Select Calendar/Date Notation</label>
    <div class="check-box-half mr-m">
        <input type="checkbox" value="CE" class="check-box-input era-check-js {{$defClass}}" name="default_era{{$seq}}"
                {{ ((is_null($default) || $default['era'] == 'CE') ? 'checked' : '') }}>
        <span class="check"></span>
        <span class="placeholder">CE</span>
    </div>

    <div class="check-box-half mr-m">
        <input type="checkbox" value="BCE" class="check-box-input era-check-js {{$defClass}}" name="default_era{{$seq}}"
                {{ ((!is_null($default) && $default['era'] == 'BCE') ? 'checked' : '') }}>
        <span class="check"></span>
        <span class="placeholder">BCE</span>
    </div>

    <div class="check-box-half mr-m">
        <input type="checkbox" value="BP" class="check-box-input era-check-js {{$defClass}}" name="default_era{{$seq}}"
                {{ ((!is_null($default) && $default['era'] == 'BP') ? 'checked' : '') }}>
        <span class="check"></span>
        <span class="placeholder">BP</span>
    </div>

    <div class="check-box-half">
        <input type="checkbox" value="KYA BP" class="check-box-input era-check-js {{$defClass}}" name="default_era{{$seq}}"
                {{ ((!is_null($default) && $default['era'] == 'KYA BP') ? 'checked' : '') }}>
        <span class="check"></span>
        <span class="placeholder">KYA BP</span>
    </div>
</div>