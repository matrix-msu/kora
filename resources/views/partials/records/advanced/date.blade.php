<div class="form-group date-input-form-group date-input-form-group-js mt-xl">
    <div class="form-input-container">
        <div class="form-group">
            {!! Form::label($field->flid.'_input',$field->name.' Start Date') !!}
            <div class="date-inputs-container">
                {!! Form::select($field->flid."_begin_month",['' => '',
                    '1' => '01 - '.date("F", mktime(0, 0, 0, 1, 10)), '2' => '02 - '.date("F", mktime(0, 0, 0, 2, 10)),
                    '3' => '03 - '.date("F", mktime(0, 0, 0, 3, 10)), '4' => '04 - '.date("F", mktime(0, 0, 0, 4, 10)),
                    '5' => '05 - '.date("F", mktime(0, 0, 0, 5, 10)), '6' => '06 - '.date("F", mktime(0, 0, 0, 6, 10)),
                    '7' => '07 - '.date("F", mktime(0, 0, 0, 7, 10)), '8' => '08 - '.date("F", mktime(0, 0, 0, 8, 10)),
                    '9' => '09 - '.date("F", mktime(0, 0, 0, 9, 10)), '10' => '10 - '.date("F", mktime(0, 0, 0, 10, 10)),
                    '11' => '11 - '.date("F", mktime(0, 0, 0, 11, 10)), '12' => '12 - '.date("F", mktime(0, 0, 0, 12, 10))],
                    "", ['class' => 'single-select', 'data-placeholder'=>"Select a Start Month", 'id' => $field->flid."_begin_month"])
                !!}

                <select id="{{$field->flid}}_begin_day" name="{{$field->flid}}_begin_day" class="single-select" data-placeholder="Select a Start Day">
                    <option value=""></option>
                    <?php
                    $i = 1;
                    while ($i <= 31) {
                        echo "<option value=" . $i . ">" . $i . "</option>";
                        $i++;
                    }
                    ?>
                </select>

                <select id="{{$field->flid}}_begin_year" name="{{$field->flid}}_begin_year" class="single-select" data-placeholder="Select a Start Year">
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
        </div>

        @if(\App\Http\Controllers\FieldController::getFieldOption($field,'Era') == 'Yes')
            <div class="form-group mt-xl">
                <label>Select Calendar/Date Notation</label>
                <div class="check-box-half mr-m">
                    <input type="checkbox" value="CE" class="check-box-input era-check-js era-check-{{$field->flid}}-begin-js" name="{{$field->flid}}_begin_era" checked flid="{{$field->flid}}" range="begin">
                    <span class="check"></span>
                    <span class="placeholder">CE</span>
                </div>

                <div class="check-box-half mr-m">
                    <input type="checkbox" value="BCE" class="check-box-input era-check-js era-check-{{$field->flid}}-begin-js" name="{{$field->flid}}_begin_era" flid="{{$field->flid}}" range="begin">
                    <span class="check"></span>
                    <span class="placeholder">BCE</span>
                </div>

                <div class="check-box-half mr-m">
                    <input type="checkbox" value="BP" class="check-box-input era-check-js era-check-{{$field->flid}}-begin-js" name="{{$field->flid}}_begin_era" flid="{{$field->flid}}" range="begin">
                    <span class="check"></span>
                    <span class="placeholder">BP</span>
                </div>

                <div class="check-box-half">
                    <input type="checkbox" value="KYA BP" class="check-box-input era-check-js era-check-{{$field->flid}}-begin-js" name="{{$field->flid}}_begin_era" flid="{{$field->flid}}" range="begin">
                    <span class="check"></span>
                    <span class="placeholder">KYA BP</span>
                </div>
            </div>
        @endif

        <div class="form-group mt-xl">
            {!! Form::label($field->flid.'_input',$field->name.' End Date') !!}
            <div class="date-inputs-container">
                {!! Form::select($field->flid."_end_month",['' => '',
                    '1' => '01 - '.date("F", mktime(0, 0, 0, 1, 10)), '2' => '02 - '.date("F", mktime(0, 0, 0, 2, 10)),
                    '3' => '03 - '.date("F", mktime(0, 0, 0, 3, 10)), '4' => '04 - '.date("F", mktime(0, 0, 0, 4, 10)),
                    '5' => '05 - '.date("F", mktime(0, 0, 0, 5, 10)), '6' => '06 - '.date("F", mktime(0, 0, 0, 6, 10)),
                    '7' => '07 - '.date("F", mktime(0, 0, 0, 7, 10)), '8' => '08 - '.date("F", mktime(0, 0, 0, 8, 10)),
                    '9' => '09 - '.date("F", mktime(0, 0, 0, 9, 10)), '10' => '10 - '.date("F", mktime(0, 0, 0, 10, 10)),
                    '11' => '11 - '.date("F", mktime(0, 0, 0, 11, 10)), '12' => '12 - '.date("F", mktime(0, 0, 0, 12, 10))],
                    "", ['class' => 'single-select', 'data-placeholder'=>"Select a End Month", 'id' => $field->flid."_end_month"])
                !!}

                <select id="{{$field->flid}}_end_day" name="{{$field->flid}}_end_day" class="single-select" data-placeholder="Select a End Day">
                    <option value=""></option>
                    <?php
                    $i = 1;
                    while ($i <= 31) {
                        echo "<option value=" . $i . ">" . $i . "</option>";
                        $i++;
                    }
                    ?>
                </select>

                <select id="{{$field->flid}}_end_year" name="{{$field->flid}}_end_year" class="single-select" data-placeholder="Select a End Year">
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
        </div>

        @if(\App\Http\Controllers\FieldController::getFieldOption($field,'Era') == 'Yes')
            <div class="form-group mt-xl">
                <label>Select Calendar/Date Notation</label>
                <div class="check-box-half mr-m">
                    <input type="checkbox" value="CE" class="check-box-input era-check-js era-check-{{$field->flid}}-end-js" name="{{$field->flid}}_end_era" checked flid="{{$field->flid}}" range="end">
                    <span class="check"></span>
                    <span class="placeholder">CE</span>
                </div>

                <div class="check-box-half mr-m">
                    <input type="checkbox" value="BCE" class="check-box-input era-check-js era-check-{{$field->flid}}-end-js" name="{{$field->flid}}_end_era" flid="{{$field->flid}}" range="end">
                    <span class="check"></span>
                    <span class="placeholder">BCE</span>
                </div>

                <div class="check-box-half mr-m">
                    <input type="checkbox" value="BP" class="check-box-input era-check-js era-check-{{$field->flid}}-end-js" name="{{$field->flid}}_end_era" flid="{{$field->flid}}" range="end">
                    <span class="check"></span>
                    <span class="placeholder">BP</span>
                </div>

                <div class="check-box-half">
                    <input type="checkbox" value="KYA BP" class="check-box-input era-check-js era-check-{{$field->flid}}-end-js" name="{{$field->flid}}_end_era" flid="{{$field->flid}}" range="end">
                    <span class="check"></span>
                    <span class="placeholder">KYA BP</span>
                </div>

                <p class="sub-text mt-m">Check your Start/End notations. Can't mix BP with KYA BP. Can't mix any BP with any CE. Can't have CE before BCE. Doing so will return no results for this advanced search.</p>
            </div>
        @endif
    </div>
</div>