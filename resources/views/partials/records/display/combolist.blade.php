<?php
$cmbName1 = $field['one']['name'];
$cmbName2 = $field['two']['name'];
$oneType = $field['one']['type'];
$twoType = $field['two']['type'];
$oneFlid = $field['one']['flid'];
$twoFlid = $field['two']['flid'];

//$valArray = \App\ComboListField::dataToOldFormat($typedField->data()->get()->toArray());
$items = $typedField->retrieve($flid, $form->id, $value);
?>
<div class="combo-list-display">
    <div>
        <span class="combo-column combo-title">{{$cmbName1}}</span>
        <span class="combo-column combo-title">{{$cmbName2}}</span>
    </div>
    <div>
        <span class="combo-border-large"> </span>
    </div>
    {{-- @for($i=0;$i<sizeof($valArray);$i++) --}}
    @foreach($items as $item)
        <div>
            {{-- @if($i!=0)
                <span class="combo-border-small"> </span>
            @endif --}}

            @if($oneType=='Text' | $oneType=='Date' | $oneType=='List')
                <span class="combo-column">{{ $item->{$oneFlid} }}</span>
            @elseif($oneType=='Integer' | $oneType=='Float')
                <?php
                $value1 = $item->{$oneFlid};
                $unit = App\KoraFields\ComboListField::getComboFieldOption($field,'Unit','one');
                if($unit!=null && $unit!='')
                    $value1 .= ' '.$unit;
                ?>
                <span class="combo-column">{{$value1}}</span>
            @elseif($oneType=='Multi-Select List' | $oneType=='Generated List' | $oneType=='Associator')
                <span class="combo-column">
                    @foreach($item->{$oneFlid} as $val)
                        <div>{{$val}}</div>
                    @endforeach
                </span>
            @endif

            @if($twoType=='Text' | $twoType=='Date' | $twoType=='List')
                <span class="combo-column">{{ $item->{$twoFlid} }}</span>
            @elseif($twoType=='Integer' | $twoType=='Float')
                <?php
                $value2 = $item->{$twoFlid};
                $unit = App\KoraFields\ComboListField::getComboFieldOption($field,'Unit','two');
                if($unit!=null && $unit!=''){
                    $value2 .= ' '.$unit;
                }
                ?>
                <span class="combo-column">{{$value2}}</span>
            @elseif($twoType=='Multi-Select List' | $twoType=='Generated List' | $twoType=='Associator')
                <span class="combo-column">
                    @foreach($$item->{$twoFlid} as $val)
                        <div>{{$val}}</div>
                    @endforeach
                </span>
            @endif
        </div>
    @endforeach
</div>
