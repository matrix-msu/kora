<div class="panel panel-default">
    <div class="panel-heading">
        <div class="checkbox">
            <label style="font-size:1.25em;"><input type="checkbox" name="{{$field->flid}}_dropdown"> {{$field->name}}</label>
        </div>
    </div>
    <div id="input_collapse_{{$field->flid}}" style="display: none;">
        <div class="panel-body">
            @foreach(["begin", "end"] as $index)
            <label for="{{$field->flid}}_begin">{{trans('advanced_search.date_'.$index)}}:</label>
            <div class="form-group form-inline">
                {!! Form::label($field->flid."_". $index ."_month",trans('records_fieldInput.month').': ') !!}
                {!! Form::select($field->flid."_". $index ."_month",['' => '',
                        '1' => '01 - '.trans('records_fieldInput.jan'), '2' => '02 - '.trans('records_fieldInput.feb'),
                        '3' => '03 - '.trans('records_fieldInput.mar'), '4' => '04 - '.trans('records_fieldInput.apr'),
                        '5' => '05 - '.trans('records_fieldInput.may'), '6' => '06 - '.trans('records_fieldInput.june'),
                        '7' => '07 - '.trans('records_fieldInput.july'), '8' => '08 - '.trans('records_fieldInput.aug'),
                        '9' => '09 - '.trans('records_fieldInput.sep'), '10' => '10 - '.trans('records_fieldInput.oct'),
                        '11' => '11 - '.trans('records_fieldInput.nov'), '12' => '12 - '.trans('records_fieldInput.dec')]
                        ,"", ['class' => 'form-control'])
                !!}

                {!! Form::label($field->flid . "_". $index ."_day", trans('records_fieldInput.day').': ') !!}
                <select name="{{$field->flid}}_{{$index}}_day" class="form-control">
                    <option value=""></option>
                    <?php
                    $i = 1;
                    while ($i <= 31) {
                        echo "<option value=" . $i . ">" . $i . "</option>";
                        $i++;
                    }
                    ?>
                </select>

                {!! Form::label($field->flid . "_". $index ."_year",trans('records_fieldInput.year').': ') !!}
                <select name="{{$field->flid}}_{{$index}}_year" class="form-control">
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

                @if(\App\Http\Controllers\FieldController::getFieldOption($field, 'Era')=='Yes')
                    {!! Form::label($field->flid . "_". $index ."_era", trans('records_fieldInput.era').': ') !!}
                    {!! Form::select($field->flid . "_". $index ."_era", ['CE'=>'CE','BCE'=>'BCE'],'CE', ['class' => 'form-control']) !!}
                @endif
            </div>
            @endforeach
            {{trans('advanced_search.range_text')}}: <span id="{{$field->flid}}_range">{{trans('advanced_search.invalid')}}</span>.
        </div>
        <input type="hidden" id="{{$field->flid}}_valid" name="{{$field->flid}}_valid" value="0">
    </div>
</div>

<script>
    $("[name^={{$field->flid}}_]").change(function() {
        if(validDates_{{$field->flid}}()) {
            $("#{{$field->flid}}_range").html("{{trans('advanced_search.valid')}}");
            $("#{{$field->flid}}_valid").val("1")
        }
        else {
            $("#{{$field->flid}}_range").html("{{trans('advanced_search.invalid')}}");
            $("#{{$field->flid}}_valid").val("0")
        }
    });

    /**
     * Checks to see if the dates are in chronological order.
     */
    function validDates_{{$field->flid}}() {
        var begin_month = parseInt($("[name={{$field->flid}}_begin_month]").val()) - 1; // Account for 0 indexing
        var begin_day = $("[name={{$field->flid}}_begin_day]").val();
        var begin_year = $("[name={{$field->flid}}_begin_year]").val();
        var begin_era = $("[name={{$field->flid}}_begin_era]").val();

        var end_month = parseInt($("[name={{$field->flid}}_end_month]").val()) - 1;
        var end_day = $("[name={{$field->flid}}_end_day]").val();
        var end_year = $("[name={{$field->flid}}_end_year]").val();
        var end_era = $("[name={{$field->flid}}_end_era]").val();

        if (begin_day == "" && isNaN(begin_month) && begin_year == "") return false;
        if (end_day == "" && isNaN(end_month) && end_year == "") return false;

        if ((begin_day != "" && isNaN(begin_month)&& begin_year == "") ||
                (end_day != "" && isNaN(end_month) && end_year == "")) { // Only day selected.
            return false;
        }
        if ((isNaN(begin_month) && begin_day != "") ||
                    (isNaN(end_month) && end_day != "")) { // Day selected and month not selected.
            return false;
        }

        if (end_era == "BCE" && begin_era == "CE") {
            return false;
        }
        if (begin_era == "BCE" && end_era == "CE") {
            return true;
        }

        // Blank years are replaced with low-end of field default.
        begin_year = (begin_year == "") ? {{\App\Http\Controllers\FieldController::getFieldOption($field, 'Start')}} : begin_year;
        end_year = (end_year == "") ? {{\App\Http\Controllers\FieldController::getFieldOption($field, 'Start')}} : end_year;

        // Blank months and days are replaced with January and the 1st respectively.
        begin_month = (isNaN(begin_month)) ? 0 : begin_month;
        begin_day = (begin_day == "") ? 0 : begin_day;

        end_month = (isNaN(end_month)) ? 0 : end_month;
        end_day = (end_day == "") ? 0 : end_day;

        var begin = new Date(begin_year, begin_month, begin_day);
        var end = new Date(end_year, end_month, end_day);

        begin.setFullYear(begin_year);
        end.setFullYear(end_year);

        if (begin_era == "BCE" && end_era == "BCE") { // Dates are decreasing in BCE.
            return begin >= end;
        }
        return begin <= end;
    }
</script>