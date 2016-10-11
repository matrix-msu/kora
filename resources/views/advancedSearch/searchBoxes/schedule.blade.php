<div class="panel panel-default">
    <div class="panel-heading">
        <div class="checkbox">
            <label style="font-size:1.25em;"><input type="checkbox" name="{{$field->flid}}_dropdown"> {{$field->name}}</label>
        </div>
    </div>
    <div id="input_collapse_{{$field->flid}}" style="display: none;">
        <div class="panel-body">
            @foreach(["begin", "end"] as $index)
                <label for="{{$field->flid}}_begin">{{title_case($index)}} date:</label>
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
                </div>
            @endforeach
        </div>
    </div>
</div>