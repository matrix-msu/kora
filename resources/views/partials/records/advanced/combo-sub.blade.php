@switch($cftype)
    @case('Text')
    <div class="form-group {{ $cfnum != 'one' ? 'mt-sm' : null }}">
        {!! Form::label($field[$cfnum]['flid']."_".$cfnum."_input",$cftitle) !!}
        {!! Form::text($field[$cfnum]['flid']."_".$cfnum."_input", null, ['class' => 'text-input', 'placeholder' => 'Enter search text']) !!}
    </div>
    @break
    @case('Float')
    @case('Integer')
    <div class="form-group {{ $cfnum != 'one' ? 'mt-sm' : null }}">
        {!! Form::label($field[$cfnum]['flid']."_".$cfnum."_left",$cftitle) !!}
        <input class="text-input" type="number" name="{{$field[$cfnum]['flid']}}_{{$cfnum}}_left" placeholder="Enter left bound (leave blank for -infinity)">
    </div>
    <div class="form-group {{ $cfnum != 'one' ? 'mt-sm' : null }}">
        <input class="text-input" type="number" name="{{$field[$cfnum]['flid']}}_{{$cfnum}}_right" placeholder="Enter right bound (leave blank for infinity)">
    </div>
    <div class="form-group {{ $cfnum != 'one' ? 'mt-sm' : null }}">
        <div class="check-box-half">
            <input type="checkbox" value="1" id="active" class="check-box-input" name="{{$field[$cfnum]['flid']}}_{{$cfnum}}_invert" />
            <span class="check"></span>
            <span class="placeholder">Searches outside the given range</span>
        </div>
    </div>
    @break
    @case('Date')
    <div class="form-group {{ $cfnum != 'one' ? 'mt-sm' : null }}">
        {!! Form::label($field[$cfnum]['flid']."_".$cfnum."_month",$cftitle) !!}
        {!! Form::select($field[$cfnum]['flid']."_".$cfnum."_month",['' => '',
            '01' => '01 - '.date("F", mktime(0, 0, 0, 1, 10)), '02' => '02 - '.date("F", mktime(0, 0, 0, 2, 10)),
            '03' => '03 - '.date("F", mktime(0, 0, 0, 3, 10)), '04' => '04 - '.date("F", mktime(0, 0, 0, 4, 10)),
            '05' => '05 - '.date("F", mktime(0, 0, 0, 5, 10)), '06' => '06 - '.date("F", mktime(0, 0, 0, 6, 10)),
            '07' => '07 - '.date("F", mktime(0, 0, 0, 7, 10)), '08' => '08 - '.date("F", mktime(0, 0, 0, 8, 10)),
            '09' => '09 - '.date("F", mktime(0, 0, 0, 9, 10)), '10' => '10 - '.date("F", mktime(0, 0, 0, 10, 10)),
            '11' => '11 - '.date("F", mktime(0, 0, 0, 11, 10)), '12' => '12 - '.date("F", mktime(0, 0, 0, 12, 10))],
            "", ['class' => 'single-select', 'data-placeholder'=>"Select a Start Month"])
        !!}
    </div>

    <div class="form-group {{ $cfnum != 'one' ? 'mt-sm' : null }}">
        <select name="{{$field[$cfnum]['flid']}}_{{$cfnum}}_day" class="single-select" data-placeholder="Select a Start Day">
            <option value=""></option>
            @php
            $i = 1;
            while ($i <= 31) {
                echo "<option value=" . $i . ">" . $i . "</option>";
                $i++;
            }
            @endphp
        </select>
    </div>

    <div class="form-group {{ $cfnum != 'one' ? 'mt-sm' : null }}">
        <select name="{{$field[$cfnum]['flid']}}_{{$cfnum}}_year" class="single-select" data-placeholder="Select a Start Year">
            <option value=""></option>
            @php
            $i = \App\KoraFields\ComboListField::getComboFieldOption($field,'Start',$cfnum);
            $j = \App\KoraFields\ComboListField::getComboFieldOption($field,'End',$cfnum);
            while ($i <= $j) {
                echo "<option value=" . $i . ">" . $i . "</option>";
                $i++;
            }
            @endphp
        </select>
    </div>
    @break
    @case('List')
    <div class="form-group {{ $cfnum != 'one' ? 'mt-sm' : null }}">
        {!! Form::label($field[$cfnum]['flid']."_".$cfnum."_input",$cftitle) !!}
        {!! Form::select( $field[$cfnum]['flid'] . "_".$cfnum."_input", [null=>'']+\App\KoraFields\ComboListField::getComboList($field,true,$cfnum), '', ["class" => "single-select"]) !!}
    </div>
    @break
    @case('Multi-Select List')
    <div class="form-group {{ $cfnum != 'one' ? 'mt-sm' : null }}">
        {!! Form::label($field[$cfnum]['flid']."_".$cfnum."_input[]",$cftitle) !!}
        {!! Form::select( $field[$cfnum]['flid'] . "_".$cfnum."_input[]", \App\KoraFields\ComboListField::getComboList($field,true,$cfnum), '', ["class" => "multi-select", "Multiple"]) !!}
    </div>
    @break
    @case('Generated List')
    <div class="form-group {{ $cfnum != 'one' ? 'mt-sm' : null }}">
        {!! Form::label($field[$cfnum]['flid']."_".$cfnum."_input[]",$cftitle) !!}
        {!! Form::select( $field[$cfnum]['flid'] . "_".$cfnum."_input[]", \App\KoraFields\ComboListField::getComboList($field,true,$cfnum), '', ["class" => "multi-select modify-select", "Multiple"]) !!}
    </div>
    @break
    @case('Associator')
    <div class="form-group {{ $cfnum != 'one' ? 'mt-sm' : null }}">
        {!! Form::label($field[$cfnum]['flid']."_".$cfnum."_input[]",$cftitle) !!}
        @php
        $asc = new \App\Http\Controllers\AssociatorSearchController();
        $request = new \Illuminate\Http\Request();
        $request->replace(['keyword' => '']);

        $results = $asc->assocSearch($field[$cfnum]['flid'],$request);
        $rids = array();
        foreach($results as $kid => $prevArray) {
            $preview = implode(" | ", $prevArray);
            $rids[$kid] = "$kid: $preview";
        }
        @endphp
        {!! Form::select($field[$cfnum]['flid'] . "_".$cfnum."_input[]", $rids, '', ["class" => "multi-select", "Multiple"]) !!}
    </div>
    @break
    @case('Boolean')
    <div class="form-group {{ $cfnum != 'one' ? 'mt-sm' : null }}">
        {!! Form::label($field[$cfnum]['flid']."_".$cfnum."_input[]",$cftitle) !!}
        <div class="check-box-half">
            <input type="checkbox" value="1" id="preset" class="check-box-input" name="{{$field[$cfnum]['flid'] . "_".$cfnum."_input[]"}}">
            <span class="check"></span>
            <span class="placeholder"></span>
        </div>
    </div>
    @break
    @default
@endswitch
