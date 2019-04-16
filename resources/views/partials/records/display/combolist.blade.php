<?php
$oneName = $field['one']['name'];
$twoName = $field['two']['name'];
$oneType = $field['one']['type'];
$twoType = $field['two']['type'];
$oneFlid = $field['one']['flid'];
$twoFlid = $field['two']['flid'];

$items = $typedField->retrieve($flid, $form->id, $value);
?>
<div class="combo-list-display">
    <div>
        <span class="combo-column combo-title">{{$oneName}}</span>
        <span class="combo-column combo-title">{{$twoName}}</span>
    </div>
    <div>
        <span class="combo-border-large"> </span>
    </div>
    @for($i=0;$i<sizeof($items);$i++)
        <div>
            @php
                $valueOne = $items[$i]->{$oneFlid};
                $valueTwo = $items[$i]->{$twoFlid};
            @endphp

            @if($i!=0)
                <span class="combo-border-small"> </span>
            @endif

            @if($oneType=='Text' | $oneType=='Date' | $oneType=='Historical Date' | $oneType=='List' | $oneType=='Boolean')
                <span class="combo-column">{{ $valueOne }}</span>
            @elseif($oneType=='Integer' | $oneType=='Float')
                <?php
                $unit = App\KoraFields\ComboListField::getComboFieldOption($field,'Unit','one');
                if($unit!=null && $unit!='')
                    $valueOne .= ' '.$unit;
                ?>
                <span class="combo-column">{{$valueOne}}</span>
            @elseif($oneType=='Multi-Select List' | $oneType=='Generated List' | $oneType=='Associator')
                <span class="combo-column">
                    @foreach(json_decode($valueOne) as $val)
                        <div>{{$val}}</div>
                    @endforeach
                </span>
            @endif

            @if($twoType=='Text' | $twoType=='Date' | $twoType=='Historical Date' | $twoType=='List' | $twoType=='Boolean')
                <span class="combo-column">{{ $valueTwo }}</span>
            @elseif($twoType=='Integer' | $twoType=='Float')
                <?php
                $unit = App\KoraFields\ComboListField::getComboFieldOption($field,'Unit','two');
                if($unit!=null && $unit!=''){
                    $valueTwo .= ' '.$unit;
                }
                ?>
                <span class="combo-column">{{$valueTwo}}</span>
            @elseif($twoType=='Multi-Select List' | $twoType=='Generated List' | $twoType=='Associator')
                <span class="combo-column">
                    @foreach(json_decode($valueTwo) as $val)
                        <div>{{$val}}</div>
                    @endforeach
                </span>
            @endif
        </div>
    @endfor
</div>
