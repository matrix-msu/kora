<div class="form-group combo-value-div-js-{{$field->flid}} mt-xxxl">
    <label>@if($field->required==1)<span class="oval-icon"></span> @endif{{$field->name}}: </label>
    {!! Form::hidden($field->flid,true) !!}

    <?php
    $oneType = \App\ComboListField::getComboFieldType($field,'one');
    $twoType = \App\ComboListField::getComboFieldType($field,'two');
    $oneName = \App\ComboListField::getComboFieldName($field,'one');
    $twoName = \App\ComboListField::getComboFieldName($field,'two');

    if($editRecord && $hasData) {
        $defArray = \App\ComboListField::dataToOldFormat($typedField->data()->get()->toArray());
    } else if($editRecord) {
        $defArray = array();
    } else {
        $defs = $field->default;
        if($defs!=null && $defs!='')
            $defArray = explode('[!def!]',$defs);
        else
            $defArray = array();
    }
    ?>

    <div class="combo-list-display preset-clear-combo-js">
        <div>
            <span class="combo-column combo-title">{{$oneName}}</span>
            <span class="combo-column combo-title">{{$twoName}}</span>
        </div>
        @if(sizeof($defArray) > 0)
            @for($i=0;$i<sizeof($defArray);$i++)
                <div class="combo-value-item-js">
                    @if($oneType=='Text' | $oneType=='List' | $oneType=='Number' | $oneType=='Date')
                        <?php $value = explode('[!f1!]',$defArray[$i])[1]; ?>
                        {!! Form::hidden($field->flid."_combo_one[]",$value) !!}
                        <span class="combo-column">{{$value}}</span>
                    @elseif($oneType=='Multi-Select List' | $oneType=='Generated List' | $oneType=='Associator')
                        <?php
                        $valPre = explode('[!f1!]',$defArray[$i])[1];
                        $value = explode('[!]',$valPre);
                        ?>
                        {!! Form::hidden($field->flid."_combo_one[]",$valPre) !!}
                        <span class="combo-column">{{implode(' | ',$value)}}</span>
                    @endif

                    @if($twoType=='Text' | $twoType=='List' | $twoType=='Number' | $twoType=='Date')
                        <?php $value = explode('[!f2!]',$defArray[$i])[1]; ?>
                        {!! Form::hidden($field->flid."_combo_two[]",$value) !!}
                        <span class="combo-column">{{$value}}</span>
                    @elseif($twoType=='Multi-Select List' | $twoType=='Generated List' | $twoType=='Associator')
                        <?php
                        $valPre = explode('[!f2!]',$defArray[$i])[1];
                        $value = explode('[!]',$valPre);
                        ?>
                        {!! Form::hidden($field->flid."_combo_two[]",$valPre) !!}
                        <span class="combo-column">{{implode(' | ',$value)}}</span>
                    @endif

                    <span class="combo-delete delete-combo-value-js"><a class="underline-middle-hover">[X]</a></span>
                </div>
            @endfor
        @else
            <div class="combo-list-empty"><span class="combo-column">Add Values to Combo List Below</span></div>
        @endif
    </div>
</div>

<section class="combo-list-input-one" cfType="{{$oneType}}">
    @include('partials.fields.combo.inputs.record',['field'=>$field, 'type'=>$oneType,'cfName'=>$oneName,  'fnum'=>'one', 'flid'=>$field->flid])
</section>
<section class="combo-list-input-two" cfType="{{$twoType}}">
    @include('partials.fields.combo.inputs.record',['field'=>$field, 'type'=>$twoType,'cfName'=>$twoName,  'fnum'=>'two', 'flid'=>$field->flid])
</section>

<section class="new-object-button form-group mt-xxxl">
    <input class="add-combo-value-js" type="button" value="Create new Combo List value" flid="{{$field->flid}}" typeOne="{{$oneType}}" typeTwo="{{$twoType}}">
</section>