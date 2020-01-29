@php
    if(isset($seq)) { //Combo List
         $fieldName = $cTitle.' - Start Date';
         $inputID = $field[$seq]['flid']."_".$seq;
         $dateField = $field[$seq];
    } else {
         $fieldName = (array_key_exists('alt_name', $field) && $field['alt_name']!='') ? $field['name'].' ('.$field['alt_name'].') - Start Date' : $field['name'].' - Start Date';
         $inputID = $flid;
         $dateField = $field;
    }
@endphp
<div class="form-group date-input-form-group date-input-form-group-js mt-xl">
    <div class="form-input-container">
        <div class="form-group">
            {!! Form::label($inputID, $fieldName) !!}
            <div class="date-inputs-container">
                {!! Form::select($inputID."_begin_month",['' => '',
                    '01' => '01 - '.date("F", mktime(0, 0, 0, 1, 10)), '02' => '02 - '.date("F", mktime(0, 0, 0, 2, 10)),
                    '03' => '03 - '.date("F", mktime(0, 0, 0, 3, 10)), '04' => '04 - '.date("F", mktime(0, 0, 0, 4, 10)),
                    '05' => '05 - '.date("F", mktime(0, 0, 0, 5, 10)), '06' => '06 - '.date("F", mktime(0, 0, 0, 6, 10)),
                    '07' => '07 - '.date("F", mktime(0, 0, 0, 7, 10)), '08' => '08 - '.date("F", mktime(0, 0, 0, 8, 10)),
                    '09' => '09 - '.date("F", mktime(0, 0, 0, 9, 10)), '10' => '10 - '.date("F", mktime(0, 0, 0, 10, 10)),
                    '11' => '11 - '.date("F", mktime(0, 0, 0, 11, 10)), '12' => '12 - '.date("F", mktime(0, 0, 0, 12, 10))],
                    "", ['class' => 'single-select', 'data-placeholder'=>"Select a Start Month", 'id' => $inputID."_begin_month"])
                !!}

                <select id="{{$inputID}}_begin_day" name="{{$inputID}}_begin_day" class="single-select" data-placeholder="Select a Start Day">
                    <option value=""></option>
                    @php
                        $i = 1;
                        while ($i <= 31) {
                            echo "<option value=" . $i . ">" . $i . "</option>";
                            $i++;
                        }
                    @endphp
                </select>

                <select id="{{$inputID}}_begin_year" name="{{$inputID}}_begin_year" class="single-select" data-placeholder="Select a Start Year">
                    <option value=""></option>
                    @php
                        $i = $dateField['options']['Start'];
                        $j = $dateField['options']['End'];
                        while ($i <= $j) {
                            echo "<option value=" . $i . ">" . $i . "</option>";
                            $i++;
                        }
                    @endphp
                </select>
            </div>
        </div>

        @if($dateField['options']['ShowEra'])
            <div class="form-group mt-xl">
                <label>Select Calendar/Date Notation</label>
                <div class="check-box-half mr-m">
                    <input type="checkbox" value="CE" class="check-box-input era-check-js era-check-{{$inputID}}-begin-js" name="{{$inputID}}_begin_era" checked flid="{{$inputID}}" range="begin">
                    <span class="check"></span>
                    <span class="placeholder">CE</span>
                </div>

                <div class="check-box-half mr-m">
                    <input type="checkbox" value="BCE" class="check-box-input era-check-js era-check-{{$inputID}}-begin-js" name="{{$inputID}}_begin_era" flid="{{$inputID}}" range="begin">
                    <span class="check"></span>
                    <span class="placeholder">BCE</span>
                </div>

                <div class="check-box-half mr-m">
                    <input type="checkbox" value="BP" class="check-box-input era-check-js era-check-{{$inputID}}-begin-js" name="{{$inputID}}_begin_era" flid="{{$inputID}}" range="begin">
                    <span class="check"></span>
                    <span class="placeholder">BP</span>
                </div>

                <div class="check-box-half">
                    <input type="checkbox" value="KYA BP" class="check-box-input era-check-js era-check-{{$inputID}}-begin-js" name="{{$inputID}}_begin_era" flid="{{$inputID}}" range="begin">
                    <span class="check"></span>
                    <span class="placeholder">KYA BP</span>
                </div>
            </div>
        @endif

        <div class="form-group mt-xl">
            {!! Form::label($flid.'_input','End Date') !!}
            <div class="date-inputs-container">
                {!! Form::select($inputID."_end_month",['' => '',
                    '01' => '01 - '.date("F", mktime(0, 0, 0, 1, 10)), '02' => '02 - '.date("F", mktime(0, 0, 0, 2, 10)),
                    '03' => '03 - '.date("F", mktime(0, 0, 0, 3, 10)), '04' => '04 - '.date("F", mktime(0, 0, 0, 4, 10)),
                    '05' => '05 - '.date("F", mktime(0, 0, 0, 5, 10)), '06' => '06 - '.date("F", mktime(0, 0, 0, 6, 10)),
                    '07' => '07 - '.date("F", mktime(0, 0, 0, 7, 10)), '08' => '08 - '.date("F", mktime(0, 0, 0, 8, 10)),
                    '09' => '09 - '.date("F", mktime(0, 0, 0, 9, 10)), '10' => '10 - '.date("F", mktime(0, 0, 0, 10, 10)),
                    '11' => '11 - '.date("F", mktime(0, 0, 0, 11, 10)), '12' => '12 - '.date("F", mktime(0, 0, 0, 12, 10))],
                    "", ['class' => 'single-select', 'data-placeholder'=>"Select a End Month", 'id' => $inputID."_end_month"])
                !!}

                <select id="{{$inputID}}_end_day" name="{{$inputID}}_end_day" class="single-select" data-placeholder="Select a End Day">
                    <option value=""></option>
                    @php
                        $i = 1;
                        while ($i <= 31) {
                            echo "<option value=" . $i . ">" . $i . "</option>";
                            $i++;
                        }
                    @endphp
                </select>

                <select id="{{$inputID}}_end_year" name="{{$inputID}}_end_year" class="single-select" data-placeholder="Select a End Year">
                    <option value=""></option>
                    @php
                        $i = $dateField['options']['Start'];
                        $j = $dateField['options']['End'];
                        while ($i <= $j) {
                            echo "<option value=" . $i . ">" . $i . "</option>";
                            $i++;
                        }
                    @endphp
                </select>
            </div>
        </div>

        @if($dateField['options']['ShowEra'])
            <div class="form-group mt-xl">
                <label>Select Calendar/Date Notation</label>
                <div class="check-box-half mr-m">
                    <input type="checkbox" value="CE" class="check-box-input era-check-js era-check-{{$inputID}}-end-js" name="{{$inputID}}_end_era" checked flid="{{$inputID}}" range="end">
                    <span class="check"></span>
                    <span class="placeholder">CE</span>
                </div>

                <div class="check-box-half mr-m">
                    <input type="checkbox" value="BCE" class="check-box-input era-check-js era-check-{{$inputID}}-end-js" name="{{$inputID}}_end_era" flid="{{$inputID}}" range="end">
                    <span class="check"></span>
                    <span class="placeholder">BCE</span>
                </div>

                <div class="check-box-half mr-m">
                    <input type="checkbox" value="BP" class="check-box-input era-check-js era-check-{{$inputID}}-end-js" name="{{$inputID}}_end_era" flid="{{$inputID}}" range="end">
                    <span class="check"></span>
                    <span class="placeholder">BP</span>
                </div>

                <div class="check-box-half">
                    <input type="checkbox" value="KYA BP" class="check-box-input era-check-js era-check-{{$inputID}}-end-js" name="{{$inputID}}_end_era" flid="{{$inputID}}" range="end">
                    <span class="check"></span>
                    <span class="placeholder">KYA BP</span>
                </div>

                <p class="sub-text mt-m">Check your Start/End notations. Can't mix BP with KYA BP. Can't mix any BP with any CE. Can't have CE before BCE. Doing so will return no results for this advanced search.</p>
            </div>
        @endif
    </div>
</div>