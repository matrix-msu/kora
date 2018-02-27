<?php
$cmbName1 = \App\ComboListField::getComboFieldName($field,'one');
$cmbName2 = \App\ComboListField::getComboFieldName($field,'two');

$oneType = \App\ComboListField::getComboFieldType($field,'one');
$twoType = \App\ComboListField::getComboFieldType($field,'two');

$valArray = \App\ComboListField::dataToOldFormat($clf->data()->get());
?>
<div style="overflow: auto">
    <div>
        <span style="float:left;width:50%;margin-bottom:10px"><b>{{$cmbName1}}</b></span>
        <span style="float:left;width:50%;margin-bottom:10px"><b>{{$cmbName2}}</b></span>
    </div>
    @for($i=0;$i<sizeof($valArray);$i++)
        <div>
            @if($oneType=='Text' | $oneType=='List')
                <?php $value1 = explode('[!f1!]',$valArray[$i])[1]; ?>
                <span style="float:left;width:50%;margin-bottom:10px">{{$value1}}</span>
            @elseif($oneType=='Number')
                <?php
                $value1 = explode('[!f1!]',$valArray[$i])[1];
                $unit = \App\ComboListField::getComboFieldOption($field,'Unit','one');
                if($unit!=null && $unit!=''){
                    $value1 .= ' '.$unit;
                }
                ?>
                <span style="float:left;width:50%;margin-bottom:10px">{{$value1}}</span>
            @elseif($oneType=='Multi-Select List' | $oneType=='Generated List')
                <?php
                $value1 = explode('[!f1!]',$valArray[$i])[1];
                $value1Array = explode('[!]',$value1);
                ?>

                <span style="float:left;width:50%;margin-bottom:10px">
                                        @foreach($value1Array as $val)
                        <div>{{$val}}</div>
                    @endforeach
                                    </span>
            @elseif($oneType=='Associator')
                <?php
                $value1 = explode('[!f1!]',$valArray[$i])[1];
                $value1Array = explode('[!]',$value1);
                ?>

                <span style="float:left;width:50%;margin-bottom:10px">
                                        @foreach($value1Array as $val)
                        <div>{{$val}}</div>
                    @endforeach
                                    </span>
            @endif

            @if($twoType=='Text' | $twoType=='List')
                <?php $value2 = explode('[!f2!]',$valArray[$i])[1]; ?>
                <span style="float:left;width:50%;margin-bottom:10px">{{$value2}}</span>
            @elseif($twoType=='Number')
                <?php
                $value2 = explode('[!f2!]',$valArray[$i])[1];
                $unit = \App\ComboListField::getComboFieldOption($field,'Unit','two');
                if($unit!=null && $unit!=''){
                    $value2 .= ' '.$unit;
                }
                ?>
                <span style="float:left;width:50%;margin-bottom:10px">{{$value2}}</span>
            @elseif($twoType=='Multi-Select List' | $twoType=='Generated List')
                <?php
                $value2 = explode('[!f2!]',$valArray[$i])[1];
                $value2Array = explode('[!]',$value2);
                ?>

                <span style="float:left;width:50%;margin-bottom:10px">
                                        @foreach($value2Array as $val)
                        <div>{{$val}}</div>
                    @endforeach
                                    </span>
            @elseif($twoType=='Associator')
                <?php
                $value2 = explode('[!f2!]',$valArray[$i])[1];
                $value2Array = explode('[!]',$value2);
                ?>

                <span style="float:left;width:50%;margin-bottom:10px">
                                        @foreach($value2Array as $val)
                        <div>{{$val}}</div>
                    @endforeach
                                    </span>
            @endif
        </div>
    @endfor
</div>