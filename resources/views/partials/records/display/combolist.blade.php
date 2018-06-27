<?php
$cmbName1 = \App\ComboListField::getComboFieldName($field,'one');
$cmbName2 = \App\ComboListField::getComboFieldName($field,'two');

$oneType = \App\ComboListField::getComboFieldType($field,'one');
$twoType = \App\ComboListField::getComboFieldType($field,'two');

$valArray = \App\ComboListField::dataToOldFormat($typedField->data()->get()->toArray());
?>
<div class="combo-list-display">
    <div>
        <span class="combo-column combo-title">{{$cmbName1}}</span>
        <span class="combo-column combo-title">{{$cmbName2}}</span>
    </div>
    <div>
        <span class="combo-border-large"> </span>
    </div>
    @for($i=0;$i<sizeof($valArray);$i++)
        <div>
            @if($i!=0)
                <span class="combo-border-small"> </span>
            @endif

            @if($oneType=='Text' | $oneType=='Date' | $oneType=='List')
                <?php $value1 = explode('[!f1!]',$valArray[$i])[1]; ?>
                <span class="combo-column">{{$value1}}</span>
            @elseif($oneType=='Number')
                <?php
                $value1 = explode('[!f1!]',$valArray[$i])[1];
                $unit = \App\ComboListField::getComboFieldOption($field,'Unit','one');
                if($unit!=null && $unit!='')
                    $value1 .= ' '.$unit;
                ?>
                <span class="combo-column">{{$value1}}</span>
            @elseif($oneType=='Multi-Select List' | $oneType=='Generated List' | $oneType=='Associator')
                <?php
                $value1 = explode('[!f1!]',$valArray[$i])[1];
                $value1Array = explode('[!]',$value1);
                ?>
                <span class="combo-column">
                    @foreach($value1Array as $val)
                        <div>{{$val}}</div>
                    @endforeach
                </span>
            @endif

            @if($twoType=='Text' | $twoType=='Date' | $twoType=='List')
                <?php $value2 = explode('[!f2!]',$valArray[$i])[1]; ?>
                <span class="combo-column">{{$value2}}</span>
            @elseif($twoType=='Number')
                <?php
                $value2 = explode('[!f2!]',$valArray[$i])[1];
                $unit = \App\ComboListField::getComboFieldOption($field,'Unit','two');
                if($unit!=null && $unit!=''){
                    $value2 .= ' '.$unit;
                }
                ?>
                <span class="combo-column">{{$value2}}</span>
            @elseif($twoType=='Multi-Select List' | $twoType=='Generated List' | $twoType=='Associator')
                <?php
                $value2 = explode('[!f2!]',$valArray[$i])[1];
                $value2Array = explode('[!]',$value2);
                ?>
                <span class="combo-column">
                    @foreach($value2Array as $val)
                        <div>{{$val}}</div>
                    @endforeach
                </span>
            @endif
        </div>
    @endfor
</div>