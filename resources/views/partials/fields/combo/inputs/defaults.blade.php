@if($type=='Text')
    <div class="form-group">
        {!! Form::label('default_'.$fnum, $cfName) !!}
        {!! Form::text('default_'.$fnum, null, ['id' => 'default_'.$fnum, 'class' => 'text-input default-input-js', 'placeholder' => 'Enter text value here']) !!}
    </div>
@elseif($type=='Date')
    <div class="form-group">
        {!! Form::label('default_'.$fnum, $cfName.': ') !!}
        <div class="form-group mt-sm">
            {!! Form::label('month_'.$fnum,'Month: ') !!}
            {!! Form::select('month_'.$fnum,['' => '',
                '1' => '01 - '.date("F", mktime(0, 0, 0, 1, 10)), '2' => '02 - '.date("F", mktime(0, 0, 0, 2, 10)),
                '3' => '03 - '.date("F", mktime(0, 0, 0, 3, 10)), '4' => '04 - '.date("F", mktime(0, 0, 0, 4, 10)),
                '5' => '05 - '.date("F", mktime(0, 0, 0, 5, 10)), '6' => '06 - '.date("F", mktime(0, 0, 0, 6, 10)),
                '7' => '07 - '.date("F", mktime(0, 0, 0, 7, 10)), '8' => '08 - '.date("F", mktime(0, 0, 0, 8, 10)),
                '9' => '09 - '.date("F", mktime(0, 0, 0, 9, 10)), '10' => '10 - '.date("F", mktime(0, 0, 0, 10, 10)),
                '11' => '11 - '.date("F", mktime(0, 0, 0, 11, 10)), '12' => '12 - '.date("F", mktime(0, 0, 0, 12, 10))],
                null, ['class' => 'single-select default-input-js', 'data-placeholder'=>"Select a Month"]) !!}
        </div>
        <div class="form-group mt-sm">
            {!! Form::label('day_'.$fnum,'Day: ') !!}
            <select id="day_{{$fnum}}" name="day_{{$fnum}}" class="single-select default-input-js" data-placeholder="Select a Day">
                <option value=""></option>
                <?php
                $i = 1;
                while ($i <= 31) {
                    echo "<option value=" . $i . ">" . $i . "</option>";
                    $i++;
                }
                ?>
            </select>
        </div>
        <div class="form-group mt-sm">
            {!! Form::label('year_'.$fnum,'Year: ') !!}
            <select id="year_{{$fnum}}" name="year_{{$fnum}}" class="single-select preset-clear-chosen-js default-input-js" data-placeholder="Select a Year">
                <option value=""></option>
                <?php
                //$currYear=0;
                //if($field->default!='' && explode('[Y]',$field->default)[1]=='0'){
                //    $currYear=\Carbon\Carbon::now()->year;
		//}

                $i = App\KoraFields\ComboListField::getComboFieldOption($field, "Start", $fnum);
                $j = App\KoraFields\ComboListField::getComboFieldOption($field, "End", $fnum);
                while ($i <= $j) {
                    echo "<option value=" . $i . ">" . $i . "</option>";
                    $i++;
                }
                ?>
            </select>
        </div>
    </div>
@elseif($type=='Number')
    <div class="form-group">
        {!! Form::label('default_'.$fnum, $cfName.' ('.App\KoraFieComboListField::getComboFieldOption($field, "Unit", $fnum).')') !!}
        <input type="number" id="default_{{$fnum}}" name="default_{{$fnum}}" class="text-input default-input-js" value="" placeholder="Enter number here"
               step="{{ App\KoraFields\ComboListField::getComboFieldOption($field, "Increment", $fnum) }}"
               min="{{ App\KoraFields\ComboListField::getComboFieldOption($field, "Min", $fnum) }}"
               max="{{ App\KoraFields\ComboListField::getComboFieldOption($field, "Max", $fnum) }}">
    </div>
@elseif($type=='List')
    <div class="form-group">
        {!! Form::label('default_'.$fnum, $cfName) !!}
        {!! Form::select('default_'.$fnum,App\KoraFields\ComboListField::getComboList($field,false,$fnum), null,
            ['id' => 'default_'.$fnum, 'class' => 'single-select default-input-js']) !!}
    </div>
@elseif($type=='Multi-Select List')
    <div class="form-group">
        {!! Form::label('default_'.$fnum, $cfName) !!}
        {!! Form::select('default_'.$fnum.'[]',App\KoraFields\ComboListField::getComboList($field,false,$fnum), null,
        ['id' => 'default_'.$fnum, 'class' => 'multi-select default-input-js', 'multiple']) !!}
    </div>
@elseif($type=='Generated List')
    <div class="form-group">
        {!! Form::label('default_'.$fnum, $cfName) !!}
        {!! Form::select('default_'.$fnum.'[]',App\KoraFields\ComboListField::getComboList($field,false,$fnum), null,
        ['id' => 'default_'.$fnum, 'class' => 'multi-select modify-select default-input-js', 'multiple']) !!}
    </div>
@elseif($type=='Associator')
<div class="associator-section">
    <div class="form-group mt-xl">
        {!! Form::label('search','Search Associations') !!}
        <input type="text" class="text-input assoc-search-records-js" placeholder="Enter search term or KID to find associated records (populated below)">
	<p class="sub-text mt-sm">
		Enter a search term or KID and hit enter to search. Results will then be populated in the "Association Results" field below.
	</p>
    </div>

    <div class="form-group mt-xs">
        {!! Form::label('search','Association Results') !!}
        {!! Form::select('search[]', [], null, ['class' => 'multi-select assoc-select-records-js', 'multiple', "data-placeholder" => "Select a record association to add to defaults"]) !!}
	<p class="sub-text mt-sm">
		Once records are populated, they will appear in this fields dropdown. Selecting records will then add them to the "Default Associations" field below.
	</p>
    </div>

    <div class="form-group mt-xs">
        {!! Form::label('default_'.$fnum, $cfName) !!}
        {!! Form::select('default_'.$fnum.'[]', [], null, ['id' => 'default_'.$fnum, 'class' => 'multi-select assoc-default-records-js default-input-js',
	    'multiple', "data-placeholder" => "Search below to add associated records"]) !!}
	<p class="sub-text mt-sm">
        	To add associated records, Start a search for records in the "Search Associations" field above.
        </p>
    </div>
</div>
@endif
