<div class="form-group date-input-form-group date-input-form-group-js mt-xxxl">
    <label>@if($field->required==1)<span class="oval-icon"></span> @endif{{$field->name}}</label>
    <input type="hidden" name={{$field->flid}} value="{{$field->flid}}">

    <div class="form-input-container">
        <div class="form-group">
            <label>Select Date</label>

            <div class="date-inputs-container">
                <?php
                    if($editRecord && $hasData) {
                        $defMonth = $typedField->month;
                    } else if($editRecord) {
                        $defMonth = null;
                    } else {
                        $defMonth = $field->default=='' ? null : explode('[M]',$field->default)[1];
                        if($defMonth=='0')
                            $defMonth = \Carbon\Carbon::now()->month;
                    }
                ?>
                {!! Form::select('month_'.$field->flid,['' => '',
                    '1' => '01 - '.date("F", mktime(0, 0, 0, 1, 10)), '2' => '02 - '.date("F", mktime(0, 0, 0, 2, 10)),
                    '3' => '03 - '.date("F", mktime(0, 0, 0, 3, 10)), '4' => '04 - '.date("F", mktime(0, 0, 0, 4, 10)),
                    '5' => '05 - '.date("F", mktime(0, 0, 0, 5, 10)), '6' => '06 - '.date("F", mktime(0, 0, 0, 6, 10)),
                    '7' => '07 - '.date("F", mktime(0, 0, 0, 7, 10)), '8' => '08 - '.date("F", mktime(0, 0, 0, 8, 10)),
                    '9' => '09 - '.date("F", mktime(0, 0, 0, 9, 10)), '10' => '10 - '.date("F", mktime(0, 0, 0, 10, 10)),
                    '11' => '11 - '.date("F", mktime(0, 0, 0, 11, 10)), '12' => '12 - '.date("F", mktime(0, 0, 0, 12, 10))],
                    $defMonth, ['class' => 'single-select preset-clear-chosen-js', 'data-placeholder'=>"Select a Month", 'id' => 'month_'.$field->flid]) !!}


                <select id="day_{{$field->flid}}" name="day_{{$field->flid}}" class="single-select preset-clear-chosen-js" data-placeholder="Select a Day">
                    <option value=""></option>
                    <?php
                        $currDay=0;
                        if($field->default!='' && explode('[D]',$field->default)[1]=='0'){
                            $currDay=\Carbon\Carbon::now()->day;
                        }
                        $i = 1;
                        while ($i <= 31)
                        {
                            if($editRecord && $hasData) {
                                if($i==$typedField->day)
                                    echo "<option value=" . $i . " selected>" . $i . "</option>";
                                else
                                    echo "<option value=" . $i . ">" . $i . "</option>";
                            } else if($editRecord) {
                                echo "<option value=" . $i . ">" . $i . "</option>";
                            } else {
                                if(($field->default!='' && explode('[D]',$field->default)[1]==$i) | $i==$currDay)
                                    echo "<option value=" . $i . " selected>" . $i . "</option>";
                                else
                                    echo "<option value=" . $i . ">" . $i . "</option>";
                            }
                            $i++;
                        }
                    ?>
                </select>

                <select id="year_{{$field->flid}}" name="year_{{$field->flid}}" class="single-select preset-clear-chosen-js" data-placeholder="Select a Year">
                    <option value=""></option>
                    <?php
                        $currYear=0;
                        if($field->default!='' && explode('[Y]',$field->default)[1]=='0'){
                            $currYear=\Carbon\Carbon::now()->year;
                        }
                        $i = \App\Http\Controllers\FieldController::getFieldOption($field, 'Start');
                        $j = \App\Http\Controllers\FieldController::getFieldOption($field, 'End');
                        while ($i <= $j)
                        {
                            if($editRecord && $hasData) {
                                if($i==$typedField->year)
                                    echo "<option value=" . $i . " selected>" . $i . "</option>";
                                else
                                    echo "<option value=" . $i . ">" . $i . "</option>";
                            } else if($editRecord) {
                                echo "<option value=" . $i . ">" . $i . "</option>";
                            } else {
                                if(($field->default!='' && explode('[Y]',$field->default)[1]==$i) | $i==$currYear)
                                    echo "<option value=" . $i . " selected>" . $i . "</option>";
                                else
                                    echo "<option value=" . $i . ">" . $i . "</option>";
                            }
                            $i++;
                        }
                    ?>
                </select>
            </div>
        </div>

        @if(\App\Http\Controllers\FieldController::getFieldOption($field,'Circa') == 'Yes')
            <div class="form-group mt-xl">
                <?php
                    $isCirca = ($editRecord && $hasData ? $typedField->circa : 0);
                ?>
                <div class="check-box-half">
                    <input type="checkbox" value="1" id="preset" class="check-box-input" name="{{'circa_'.$field->flid}}" {{ ($isCirca ? 'checked' : '') }}>
                    <span class="check"></span>
                    <span class="placeholder">Mark this date as an approximate (Circa)?</span>
                </div>
            </div>
        @endif

        @if(\App\Http\Controllers\FieldController::getFieldOption($field,'Era') == 'Yes')
            <div class="form-group mt-xl">
                <?php
                    $era = ($editRecord && $hasData ? $typedField->era : 'CE');
                ?>
                <label>Select Calendar/Date Notation</label>
                <div class="check-box-half mr-m">
                    <input type="checkbox" value="CE" class="check-box-input era-check-js" name="era_{{$field->flid}}" {{ ($era == 'CE' ? 'checked' : '') }} flid="{{$field->flid}}">
                    <span class="check"></span>
                    <span class="placeholder">CE</span>
                </div>

                <div class="check-box-half mr-m">
                    <input type="checkbox" value="BCE" class="check-box-input era-check-js" name="era_{{$field->flid}}" {{ ($era == 'BCE' ? 'checked' : '') }} flid="{{$field->flid}}">
                    <span class="check"></span>
                    <span class="placeholder">BCE</span>
                </div>

                <div class="check-box-half mr-m">
                    <input type="checkbox" value="BP" class="check-box-input era-check-js" name="era_{{$field->flid}}" {{ ($era == 'BP' ? 'checked' : '') }} flid="{{$field->flid}}">
                    <span class="check"></span>
                    <span class="placeholder">BP</span>
                </div>

                <div class="check-box-half">
                    <input type="checkbox" value="KYA BP" class="check-box-input era-check-js" name="era_{{$field->flid}}" {{ ($era == 'KYA BP' ? 'checked' : '') }} flid="{{$field->flid}}">
                    <span class="check"></span>
                    <span class="placeholder">KYA BP</span>
                </div>
            </div>
        @endif
    </div>
</div>
