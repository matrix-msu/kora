<div class="form-group date-input-form-group date-input-form-group-js mt-xl">
    <div class="form-input-container">
        <div class="form-group">
            {!! Form::label($flid.'_input',$field['name'].' Start DateTime') !!}
            <div class="date-inputs-container">
                {!! Form::select($flid."_begin_month",['' => '',
                    '01' => '01 - '.date("F", mktime(0, 0, 0, 1, 10)), '02' => '02 - '.date("F", mktime(0, 0, 0, 2, 10)),
                    '03' => '03 - '.date("F", mktime(0, 0, 0, 3, 10)), '04' => '04 - '.date("F", mktime(0, 0, 0, 4, 10)),
                    '05' => '05 - '.date("F", mktime(0, 0, 0, 5, 10)), '06' => '06 - '.date("F", mktime(0, 0, 0, 6, 10)),
                    '07' => '07 - '.date("F", mktime(0, 0, 0, 7, 10)), '08' => '08 - '.date("F", mktime(0, 0, 0, 8, 10)),
                    '09' => '09 - '.date("F", mktime(0, 0, 0, 9, 10)), '10' => '10 - '.date("F", mktime(0, 0, 0, 10, 10)),
                    '11' => '11 - '.date("F", mktime(0, 0, 0, 11, 10)), '12' => '12 - '.date("F", mktime(0, 0, 0, 12, 10))],
                    "", ['class' => 'single-select', 'data-placeholder'=>"Select a Start Month", 'id' => $flid."_begin_month"])
                !!}

                <select id="{{$flid}}_begin_day" name="{{$flid}}_begin_day" class="single-select" data-placeholder="Select a Start Day">
                    <option value=""></option>
                    @php
                        $i = 1;
                        while ($i <= 31) {
                            echo "<option value=" . $i . ">" . $i . "</option>";
                            $i++;
                        }
                    @endphp
                </select>

                <select id="{{$flid}}_begin_year" name="{{$flid}}_begin_year" class="single-select" data-placeholder="Select a Start Year">
                    <option value=""></option>
                    @php
                        $i = $field['options']['Start'];
                        $j = $field['options']['End'];
                        while ($i <= $j) {
                            echo "<option value=" . $i . ">" . $i . "</option>";
                            $i++;
                        }
                    @endphp
                </select>
            </div>
        </div>

        <div class="date-inputs-container">
            <select id="{{$flid}}_begin_hour" name="{{$flid}}_begin_hour" class="single-select" data-placeholder="Select an Hour">
                @php
                    for($i=0;$i<24;$i++) {
                        echo "<option value=" . $i . ">" . $i . " hours</option>";
                    }
                @endphp
            </select>

            <select id="{{$flid}}_begin_minute" name="{{$flid}}_begin_minute" class="single-select" data-placeholder="Select a Minute">
                @php
                    for($i=0;$i<60;$i++) {
                        echo "<option value=" . $i . ">" . $i . " minutes</option>";
                    }
                @endphp
            </select>

            <select id="{{$flid}}_begin_second" name="{{$flid}}_begin_second" class="single-select" data-placeholder="Select a Second">
                @php
                    for($i=0;$i<60;$i++) {
                        echo "<option value=" . $i . ">" . $i . " seconds</option>";
                    }
                @endphp
            </select>
        </div>

        <div class="form-group mt-xl">
            {!! Form::label($flid.'_input',$field['name'].' End DateTime') !!}
            <div class="date-inputs-container">
                {!! Form::select($flid."_end_month",['' => '',
                    '01' => '01 - '.date("F", mktime(0, 0, 0, 1, 10)), '02' => '02 - '.date("F", mktime(0, 0, 0, 2, 10)),
                    '03' => '03 - '.date("F", mktime(0, 0, 0, 3, 10)), '04' => '04 - '.date("F", mktime(0, 0, 0, 4, 10)),
                    '05' => '05 - '.date("F", mktime(0, 0, 0, 5, 10)), '06' => '06 - '.date("F", mktime(0, 0, 0, 6, 10)),
                    '07' => '07 - '.date("F", mktime(0, 0, 0, 7, 10)), '08' => '08 - '.date("F", mktime(0, 0, 0, 8, 10)),
                    '09' => '09 - '.date("F", mktime(0, 0, 0, 9, 10)), '10' => '10 - '.date("F", mktime(0, 0, 0, 10, 10)),
                    '11' => '11 - '.date("F", mktime(0, 0, 0, 11, 10)), '12' => '12 - '.date("F", mktime(0, 0, 0, 12, 10))],
                    "", ['class' => 'single-select', 'data-placeholder'=>"Select a End Month", 'id' => $flid."_end_month"])
                !!}

                <select id="{{$flid}}_end_day" name="{{$flid}}_end_day" class="single-select" data-placeholder="Select a End Day">
                    <option value=""></option>
                    @php
                        $i = 1;
                        while ($i <= 31) {
                            echo "<option value=" . $i . ">" . $i . "</option>";
                            $i++;
                        }
                    @endphp
                </select>

                <select id="{{$flid}}_end_year" name="{{$flid}}_end_year" class="single-select" data-placeholder="Select a End Year">
                    <option value=""></option>
                    @php
                        $i = $field['options']['Start'];
                        $j = $field['options']['End'];
                        while ($i <= $j) {
                            echo "<option value=" . $i . ">" . $i . "</option>";
                            $i++;
                        }
                    @endphp
                </select>
            </div>
        </div>

        <div class="date-inputs-container">
            <select id="{{$flid}}_end_hour" name="{{$flid}}_end_hour" class="single-select" data-placeholder="Select an Hour">
                @php
                    for($i=0;$i<24;$i++) {
                        echo "<option value=" . $i . ">" . $i . " hours</option>";
                    }
                @endphp
            </select>

            <select id="{{$flid}}_end_minute" name="{{$flid}}_end_minute" class="single-select" data-placeholder="Select a Minute">
                @php
                    for($i=0;$i<60;$i++) {
                        echo "<option value=" . $i . ">" . $i . " minutes</option>";
                    }
                @endphp
            </select>

            <select id="{{$flid}}_end_second" name="{{$flid}}_end_second" class="single-select" data-placeholder="Select a Second">
                @php
                    for($i=0;$i<60;$i++) {
                        echo "<option value=" . $i . ">" . $i . " seconds</option>";
                    }
                @endphp
            </select>
        </div>
    </div>
</div>