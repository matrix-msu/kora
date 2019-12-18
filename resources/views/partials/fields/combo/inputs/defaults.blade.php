{{-- TODO::COMBO_FINISH --}}
@if($type=='Date' | $type=='Historical Date')
    <div class="form-group date-input-form-group date-input-form-group-js mt-xs">
        {!! Form::label('default_'.$fnum, $cfName) !!}
        <div class="form-input-container">
            <div class="form-group">
                <label>Select Date</label>
                <div class="date-inputs-container date-inputs-container-js">
                    {!! Form::select('default_month_' . $fnum,['' => '', '0' => 'Current Month',
                        '01' => '01 - '.date("F", mktime(0, 0, 0, 1, 10)), '02' => '02 - '.date("F", mktime(0, 0, 0, 2, 10)),
                        '03' => '03 - '.date("F", mktime(0, 0, 0, 3, 10)), '04' => '04 - '.date("F", mktime(0, 0, 0, 4, 10)),
                        '05' => '05 - '.date("F", mktime(0, 0, 0, 5, 10)), '06' => '06 - '.date("F", mktime(0, 0, 0, 6, 10)),
                        '07' => '07 - '.date("F", mktime(0, 0, 0, 7, 10)), '08' => '08 - '.date("F", mktime(0, 0, 0, 8, 10)),
                        '09' => '09 - '.date("F", mktime(0, 0, 0, 9, 10)), '10' => '10 - '.date("F", mktime(0, 0, 0, 10, 10)),
                        '11' => '11 - '.date("F", mktime(0, 0, 0, 11, 10)), '12' => '12 - '.date("F", mktime(0, 0, 0, 12, 10))], null, ['class' => 'single-select', 'data-placeholder'=>"Select a Month", 'id' => 'default_month_' . $fnum]) !!}

                    <select name="default_day_{{$fnum}}" id="default_day_{{$fnum}}" class="single-select" data-placeholder="Select a Day">
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

                    <select name="default_year_{{$fnum}}" id="default_year_{{$fnum}}" class="single-select default-input-js" data-placeholder="Select a Year">
                        <option value=""></option>
                        @php
                            echo "<option value=" . 0 . ">Current Year</option>";

                            $i = $field[$fnum]['options']['Start'];
                            $j = $field[$fnum]['options']['End'];

                            while($i <= $j) {
                                echo "<option value=" . $i . ">" . $i . "</option>";
                                $i++;
                            }
                        @endphp
                    </select>
                </div>
                @if($type=='Historical Date')
                    <div class="form-group mt-xl">
                        <label>Select Prefix (Optional)</label>
                        <div class="check-box-half mr-m">
                            <input type="checkbox" value="circa" id="default_prefix_{{$fnum}}_circa" class="check-box-input prefix-check-js" name="default_prefix_{{$fnum}}_circa">
                            <span class="check"></span>
                            <span class="placeholder">Circa</span>
                        </div>

                        <div class="check-box-half mr-m">
                            <input type="checkbox" value="pre" id="default_prefix_{{$fnum}}_pre" class="check-box-input prefix-check-js" name="default_prefix_{{$fnum}}_pre">
                            <span class="check"></span>
                            <span class="placeholder">Pre</span>
                        </div>

                        <div class="check-box-half mr-m">
                            <input type="checkbox" value="post" id="default_prefix_{{$fnum}}_post" class="check-box-input prefix-check-js" name="default_prefix_{{$fnum}}_post">
                            <span class="check"></span>
                            <span class="placeholder">Post</span>
                        </div>
                    </div>

                    <div class="form-group mt-xl">
                        <label>Select Calendar/Date Notation</label>
                        <div class="check-box-half mr-m">
                            <input type="checkbox" value="CE" id="default_era_{{$fnum}}_ce" class="check-box-input era-check-js" name="default_era_{{$fnum}}_ce" checked>
                            <span class="check"></span>
                            <span class="placeholder">CE</span>
                        </div>

                        <div class="check-box-half mr-m">
                            <input type="checkbox" value="BCE" id="default_era_{{$fnum}}_bce" class="check-box-input era-check-js" name="default_era_{{$fnum}}_bce">
                            <span class="check"></span>
                            <span class="placeholder">BCE</span>
                        </div>

                        <div class="check-box-half mr-m">
                            <input type="checkbox" value="BP" id="default_era_{{$fnum}}_bp" class="check-box-input era-check-js" name="default_era_{{$fnum}}_bp">
                            <span class="check"></span>
                            <span class="placeholder">BP</span>
                        </div>

                        <div class="check-box-half">
                            <input type="checkbox" value="KYA BP" id="default_era_{{$fnum}}_kya" class="check-box-input era-check-js" name="default_era_{{$fnum}}_kya">
                            <span class="check"></span>
                            <span class="placeholder">KYA BP</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@elseif($type=='Integer' || $type=='Float')
    <div class="form-group">
        {!! Form::label('default_'.$fnum, $cfName.' ('.App\KoraFields\ComboListField::getComboFieldOption($field, "Unit", $fnum).')') !!}
        {!! Form::number('default_'.$fnum, null, ['id' => 'default_'.$fnum, 'class' => 'text-input default-input-js', 'placeholder' => 'Enter number here', 'min' => App\KoraFields\ComboListField::getComboFieldOption($field, "Min", $fnum), 'max' => App\KoraFields\ComboListField::getComboFieldOption($field, "Max", $fnum)]) !!}
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
    <div class="form-group specialty-field-group list-input-form-group">
        {!! Form::label('default_'.$fnum, $cfName) !!}
        <div class="form-input-container">
            <p class="directions">Add List Options below, and order them via drag & drop or their arrow icons.</p>

            <!-- Cards of list options -->
            <div class="genlist-record-input list-option-card-container list-option-card-container-js">
                @foreach(App\KoraFields\ComboListField::getComboList($field,false,$fnum) as $opt)
                    <div id="{{$opt}}" class="card list-option-card list-option-card-js" data-list-value="{{$opt}}">
                        {!! Form::hidden('default_'.$fnum.'[]', $opt) !!}
                        <div class="header">
                            <div class="left">
                                <div class="move-actions">
                                    <a class="action move-action-js up-js" href="">
                                        <i class="icon icon-arrow-up"></i>
                                    </a>
                                    <a class="action move-action-js down-js" href="">
                                        <i class="icon icon-arrow-down"></i>
                                    </a>
                                </div>
                                <span class="title">{{$opt}}</span>
                            </div>

                            <div class="card-toggle-wrap">
                                <a class="list-option-delete list-option-delete-js tooltip" tooltip="Delete List Option" href=""><i class="icon icon-trash"></i></a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Card to add list options -->
            <div class="card new-list-option-card new-list-option-card-js">
                <div class="header">
                    <div class="left">
                        <input class="new-list-option new-list-option-js" type="text" placeholder='Type here and hit the enter key or "Add" to add new list options' data-flid='{{'default_'.$fnum}}'>
                    </div>

                    <div class="card-toggle-wrap">
                        <a class="list-option-add list-option-add-js" href=""><span>Add</span></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@elseif($type=='Associator')
<div class="associator-input form-group">
    <div>
        {!! Form::label('search','Search Associations') !!}
        <input type="text" data-combo="{{$fnum}}" class="text-input assoc-search-records-js" placeholder="Enter search term or KID to find associated records (populated below)">
        <p class="sub-text mt-sm">
            Enter a search term or KID and hit enter to search. Results will then be populated in the "Association Results" field below.
	   </p>
    </div>

    <div class="mt-xs">
        {!! Form::label('search','Association Results') !!}
        {!! Form::select('search[]', [], null, ['class' => 'multi-select assoc-select-records-js', 'multiple', "data-placeholder" => "Select a record association to add to defaults"]) !!}
        <p class="sub-text mt-sm">
            Once records are populated, they will appear in this fields dropdown. Selecting records will then add them to the "Default Associations" field below.
	   </p>
    </div>

    <div class="mt-xs">
        {!! Form::label('default_'.$fnum, $cfName) !!}
        {!! Form::select('default_'.$fnum.'[]', [], null, ['id' => 'default_'.$fnum, 'class' => 'multi-select assoc-default-records-js default-input-js',
	    'multiple', "data-placeholder" => "Search below to add associated records"]) !!}
        <p class="sub-text mt-sm">
            To add associated records, Start a search for records in the "Search Associations" field above.
        </p>
    </div>
</div>
@elseif($type=='Boolean')
<div class="form-group">
    {!! Form::label('default_'.$fnum, $cfName) !!}
    <div class="check-box-half">
        <input type="checkbox" value="1" id="default_{{$fnum}}" class="check-box-input" name="default_{{$fnum}}"
                {{ ((!is_null($field[$fnum]['default']) && $field[$fnum]['default']) ? 'checked' : '') }}>
        <span class="check"></span>
        <span class="placeholder"></span>
    </div>
</div>
@endif
